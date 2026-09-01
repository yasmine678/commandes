<?php
require_once BASE_PATH . '/models/Service.php';

/**
 * This feature reads a menu photo with a small vision model running locally
 * via Ollama (https://ollama.com) on the same server - self-hosted, free,
 * no external account or per-request cost. Configurable via .env:
 * OLLAMA_URL (default http://127.0.0.1:11434) and OLLAMA_VISION_MODEL
 * (default "qwen2.5vl:3b" - the smaller "moondream" model was tried first
 * but proved too weak to follow the extraction instructions reliably,
 * falling into repetition loops instead of returning valid dish names).
 */
function menu_ocr_config(): array
{
    return [
        'url' => rtrim(env('OLLAMA_URL', 'http://127.0.0.1:11434'), '/'),
        'model' => env('OLLAMA_VISION_MODEL', 'qwen2.5vl:3b'),
    ];
}

function menu_ocr_available(): bool
{
    $config = menu_ocr_config();
    $ch = curl_init($config['url'] . '/api/tags');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 3,
    ]);
    $response = curl_exec($ch);
    $failed = curl_errno($ch) !== 0;
    curl_close($ch);

    if ($failed || $response === false) {
        return false;
    }

    $data = json_decode($response, true);
    foreach ($data['models'] ?? [] as $m) {
        $installedName = $m['name'] ?? '';
        // Matches whether OLLAMA_VISION_MODEL is set with a tag ("qwen2.5vl:3b")
        // or without one ("qwen2.5vl", matching any tag of that model).
        if ($installedName === $config['model'] || explode(':', $installedName)[0] === $config['model']) {
            return true;
        }
    }
    return false;
}

/**
 * Sends the image to the local vision model and asks it to return the dish
 * names it can read, as JSON. Price is deliberately NOT asked for: these
 * menus price by portion size (e.g. "1500f / 2000f" once for the whole
 * week), never per dish, so there is no per-dish price to extract - the
 * admin fills that in by hand when reviewing the candidates. Asking only
 * for names also keeps the task within reach of a small (1B-2B) local
 * model: the same model asked for a structured name+price JSON in testing
 * fell into a degenerate repetition loop instead of producing valid JSON.
 *
 * @return array<int, array{name:string, price:int}>|null null on failure
 */
function menu_ocr_extract_candidates(string $imagePath): ?array
{
    $config = menu_ocr_config();
    $imageData = @file_get_contents($imagePath);
    if ($imageData === false) {
        return null;
    }

    $prompt = "Voici une photo du menu de la semaine d'un service de restauration en entreprise. "
        . "Identifie le nom de chaque plat visible sur cette photo (un plat par jour en général). "
        . 'Réponds UNIQUEMENT avec un tableau JSON de la forme ["Nom du plat 1", "Nom du plat 2"]'
        . ', sans aucun texte autour.';

    $payload = json_encode([
        'model' => $config['model'],
        'prompt' => $prompt,
        'images' => [base64_encode($imageData)],
        'format' => 'json',
        'stream' => false,
        // Caps how long a stuck/repeating model can run for - a real menu
        // has a handful of dishes, so a bounded list is plenty, and this
        // keeps a bad response fast to detect instead of a multi-minute hang.
        'options' => ['num_predict' => 500],
    ]);

    // A small CPU-bound model reading a busy photo can take a while - this
    // runs once, on an admin action, not on a page a visitor waits on. PHP's
    // own script timeout must be raised too, or it kills the request before
    // curl's does.
    set_time_limit(180);

    $ch = curl_init($config['url'] . '/api/generate');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $payload,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_TIMEOUT => 170,
    ]);
    $response = curl_exec($ch);
    $failed = curl_errno($ch) !== 0;
    curl_close($ch);

    if ($failed || $response === false) {
        return null;
    }

    $data = json_decode($response, true);
    $text = $data['response'] ?? null;
    if (!is_string($text)) {
        return null;
    }

    // The model sometimes wraps the JSON in a ```json fence despite
    // instructions - strip that before decoding.
    $text = trim($text);
    $text = preg_replace('/^```(?:json)?|```$/m', '', $text);
    $parsed = json_decode(trim($text), true);
    if (!is_array($parsed)) {
        return null;
    }

    $names = [];
    menu_ocr_collect_dish_names($parsed, $names);

    $candidates = [];
    foreach (array_unique($names) as $name) {
        $candidates[] = ['name' => $name, 'price' => 0];
    }
    return $candidates;
}

/**
 * The model is asked for a flat array of dish names, but a small local
 * model doesn't always follow the requested shape exactly - it may nest
 * dishes under a day name instead (e.g. {"Lundi": ["Plat A", "Plat B"]}),
 * or wrap a single name in a {"name": "..."} object. Rather than reject
 * those shapes, walk whatever JSON structure came back and collect every
 * string value found - the day grouping isn't used downstream anyway,
 * since the admin still assigns dishes to specific days separately when
 * building the week's menu.
 *
 * @param mixed $node
 * @param string[] $out
 */
function menu_ocr_collect_dish_names($node, array &$out): void
{
    if (is_string($node)) {
        $name = trim($node);
        if ($name !== '') {
            $out[] = $name;
        }
        return;
    }
    if (is_array($node)) {
        foreach ($node as $child) {
            menu_ocr_collect_dish_names($child, $out);
        }
    }
}

function admin_menu_ocr_controller(PDO $pdo): void
{
    require_admin();

    if (!menu_ocr_available()) {
        render('admin/menu_ocr', [
            'pageTitle' => 'Importer un menu depuis une photo',
            'errors' => [],
            'notInstalled' => true,
            'candidates' => [],
        ]);
        return;
    }

    $errors = [];
    $candidates = [];
    $step = $_POST['step'] ?? '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $step === 'extract') {
        csrf_verify();
        $file = $_FILES['photo'] ?? [];

        if (!isset($file['error']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
            $errors[] = 'Merci de sélectionner une photo.';
        } elseif ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = "Échec de l'envoi de la photo (fichier trop volumineux ou erreur de transfert).";
        } elseif ($file['size'] > 2 * 1024 * 1024) {
            $errors[] = "L'image ne doit pas dépasser 2 Mo.";
        } elseif (!@getimagesize($file['tmp_name'])) {
            $errors[] = "Le fichier envoyé n'est pas une image valide.";
        } else {
            $candidates = menu_ocr_extract_candidates($file['tmp_name']);
            if ($candidates === null) {
                $errors[] = "L'analyse de la photo a échoué. Réessayez, ou avec une photo plus nette.";
                $candidates = [];
            } elseif (empty($candidates)) {
                $errors[] = "Aucun plat avec un prix n'a été reconnu sur cette photo.";
            }
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $step === 'create') {
        csrf_verify();
        $names = $_POST['name'] ?? [];
        $prices = $_POST['price'] ?? [];
        $included = array_map('intval', $_POST['include'] ?? []);
        $created = 0;

        foreach ($included as $i) {
            $name = trim($names[$i] ?? '');
            $price = (float)($prices[$i] ?? 0);
            if ($name === '' || $price <= 0) {
                continue;
            }
            Service::create($pdo, [
                'name' => $name,
                'description' => '',
                'image' => null,
                'price' => $price,
                'available' => 1,
            ]);
            $created++;
        }

        if ($created > 0) {
            flash('success', $created > 1
                ? "$created plats créés à partir de la photo. Ajoutez une description et une photo à chacun depuis la liste des plats."
                : "1 plat créé à partir de la photo. Ajoutez une description et une photo depuis la liste des plats.");
            redirect('/commandes/admin/services.php');
        }
        $errors[] = 'Aucun plat sélectionné.';
    }

    render('admin/menu_ocr', [
        'pageTitle' => 'Importer un menu depuis une photo',
        'errors' => $errors,
        'notInstalled' => false,
        'candidates' => $candidates,
    ]);
}
