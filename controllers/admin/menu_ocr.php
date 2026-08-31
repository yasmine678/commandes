<?php
require_once BASE_PATH . '/models/Service.php';

/**
 * Locates the Tesseract OCR executable. It's a native binary, not a PHP
 * dependency - it must be installed separately with the French language
 * pack: on Windows, the UB Mannheim build
 * (https://github.com/UB-Mannheim/tesseract/wiki), installed at its default
 * path; on Linux, `apt install tesseract-ocr tesseract-ocr-fra`, which puts
 * `tesseract` on the PATH rather than at a fixed location.
 */
function menu_ocr_binary_path(): ?string
{
    $candidates = PHP_OS_FAMILY === 'Windows'
        ? ['C:\\Program Files\\Tesseract-OCR\\tesseract.exe']
        : ['/usr/bin/tesseract', '/usr/local/bin/tesseract'];

    foreach ($candidates as $path) {
        if (is_file($path)) {
            return $path;
        }
    }

    // Not at a known fixed location - fall back to PATH lookup (covers most
    // Linux installs).
    $found = trim((string)shell_exec(PHP_OS_FAMILY === 'Windows' ? 'where tesseract 2>NUL' : 'command -v tesseract 2>/dev/null'));
    return $found !== '' ? strtok($found, "\r\n") : null;
}

function menu_ocr_available(): bool
{
    return menu_ocr_binary_path() !== null;
}

/**
 * Runs Tesseract on an image and returns the raw extracted text, or null if
 * the binary failed or produced nothing.
 */
function menu_ocr_extract_text(string $imagePath): ?string
{
    $tesseract = menu_ocr_binary_path();
    if ($tesseract === null) {
        return null;
    }

    $outputBase = tempnam(sys_get_temp_dir(), 'ocr_');
    @unlink($outputBase); // tesseract appends ".txt" itself - the base must not exist yet.
    $cmd = escapeshellarg($tesseract) . ' ' . escapeshellarg($imagePath) . ' ' . escapeshellarg($outputBase) . ' -l fra 2>&1';
    exec($cmd, $output, $exitCode);
    $textFile = $outputBase . '.txt';
    if (!is_file($textFile)) {
        return null;
    }
    $text = file_get_contents($textFile);
    @unlink($textFile);
    return $text !== false ? trim($text) : null;
}

/**
 * Heuristic parse of OCR'd menu text into dish candidates: a line counts as
 * a dish only if it contains a price-looking number, which becomes the
 * price while the rest of the line becomes the name. Lines without a price
 * are dropped rather than guessed at - menu photo layouts vary too much to
 * reliably tell a dish name from a section heading otherwise, and every row
 * that IS kept still goes to the admin for review/editing before anything
 * is saved.
 *
 * @return array<int, array{name:string, price:int}>
 */
function menu_ocr_parse_candidates(string $text): array
{
    $candidates = [];
    foreach (preg_split('/\r\n|\r|\n/', $text) as $line) {
        $line = trim($line);
        if ($line === '' || !preg_match('/\d[\d .,]{1,9}\d|\d{2,}/u', $line, $m)) {
            continue;
        }
        $price = (int)preg_replace('/[^\d]/', '', $m[0]);
        if ($price <= 0) {
            continue;
        }
        $name = trim(str_replace($m[0], '', $line));
        $name = trim($name, " \t\n\r\0\x0B-–—:.·");
        if ($name === '') {
            $name = $line;
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
            'rawText' => '',
        ]);
        return;
    }

    $errors = [];
    $candidates = [];
    $rawText = '';
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
            $rawText = menu_ocr_extract_text($file['tmp_name']) ?? '';
            if ($rawText === '') {
                $errors[] = "Impossible de lire du texte sur cette photo. Essayez une photo plus nette ou mieux cadrée.";
            } else {
                $candidates = menu_ocr_parse_candidates($rawText);
                if (empty($candidates)) {
                    $errors[] = "Aucun prix n'a été reconnu sur cette photo - le texte brut lu est affiché ci-dessous.";
                }
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
        'rawText' => $rawText,
    ]);
}
