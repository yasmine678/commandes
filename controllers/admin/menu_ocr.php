<?php
require_once BASE_PATH . '/models/Service.php';

/**
 * This feature reads a menu photo with a small vision model running locally
 * via Ollama (https://ollama.com) on the same server - self-hosted, free,
 * no external account or per-request cost. Configurable via .env:
 * OLLAMA_URL (default http://127.0.0.1:11434) and OLLAMA_VISION_MODEL
 * (default "moondream", a ~1.7GB model light enough for a small VPS).
 */
function menu_ocr_config(): array
{
    return [
        'url' => rtrim(env('OLLAMA_URL', 'http://127.0.0.1:11434'), '/'),
        'model' => env('OLLAMA_VISION_MODEL', 'moondream'),
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
    $models = array_map(fn($m) => explode(':', $m['name'] ?? '')[0], $data['models'] ?? []);
    return in_array($config['model'], $models, true);
}

/**
 * Sends the image to the local vision model and asks it to return the
 * dishes it can read as JSON directly - no separate text-extraction +
 * regex-parsing step needed, since the model understands layout well
 * enough to tell a dish name from a price from a section heading itself.
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
        . "Identifie chaque plat visible avec son nom et son prix en FCFA. "
        . "Réponds UNIQUEMENT avec un tableau JSON de la forme "
        . '[{"name": "Nom du plat", "price": 1500}]'
        . ", sans aucun texte autour. Si un plat n'a pas de prix visible, ignore-le.";

    $payload = json_encode([
        'model' => $config['model'],
        'prompt' => $prompt,
        'images' => [base64_encode($imageData)],
        'format' => 'json',
        'stream' => false,
    ]);

    $ch = curl_init($config['url'] . '/api/generate');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $payload,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        // A small CPU-bound model reading a full photo can take a while -
        // this runs once, on an admin action, not on a page a visitor waits on.
        CURLOPT_TIMEOUT => 120,
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

    $candidates = [];
    foreach ($parsed as $item) {
        $name = trim((string)($item['name'] ?? ''));
        $price = (int)($item['price'] ?? 0);
        if ($name === '' || $price <= 0) {
            continue;
        }
        $candidates[] = ['name' => $name, 'price' => $price];
    }
    return $candidates;
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
