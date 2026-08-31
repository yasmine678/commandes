<?php
require_once BASE_PATH . '/models/Service.php';

/**
 * Image files available in assets/images/, offered as a picker in the
 * service form alongside the option to upload a brand new one.
 */
function admin_available_images(): array
{
    $files = glob(BASE_PATH . '/assets/images/*.{jpg,jpeg,png,gif,webp}', GLOB_BRACE) ?: [];
    return array_map('basename', $files);
}

/**
 * Validates and stores an uploaded service image. Returns the generated
 * filename on success, or null if nothing was uploaded / it was rejected
 * (in which case a message is appended to $errors).
 */
function admin_handle_image_upload(array $file, array &$errors): ?string
{
    if (!isset($file['error']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = "Échec de l'envoi de l'image (fichier trop volumineux ou erreur de transfert).";
        return null;
    }
    if ($file['size'] > 2 * 1024 * 1024) {
        $errors[] = "L'image ne doit pas dépasser 2 Mo.";
        return null;
    }

    // Validate it's actually an image (not just a renamed .php file) by
    // reading its real content, not the client-supplied name/MIME type.
    $imageInfo = @getimagesize($file['tmp_name']);
    $allowedTypes = [
        IMAGETYPE_JPEG => 'jpg',
        IMAGETYPE_PNG => 'png',
        IMAGETYPE_GIF => 'gif',
        IMAGETYPE_WEBP => 'webp',
    ];
    if (!$imageInfo || !isset($allowedTypes[$imageInfo[2]])) {
        $errors[] = "Le fichier envoyé n'est pas une image valide (formats acceptés : JPG, PNG, GIF, WEBP).";
        return null;
    }

    $filename = bin2hex(random_bytes(8)) . '.' . $allowedTypes[$imageInfo[2]];
    $destination = BASE_PATH . '/assets/images/' . $filename;
    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        $errors[] = "Impossible d'enregistrer l'image envoyée.";
        return null;
    }

    return $filename;
}

function admin_services_index_controller(PDO $pdo): void
{
    require_admin();

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
        csrf_verify();
        try {
            Service::delete($pdo, (int)$_POST['serId']);
            flash('success', 'Plat supprimé.');
        } catch (PDOException $e) {
            flash('error', "Impossible de supprimer ce plat : il est utilisé dans une ou plusieurs commandes ou menus.");
        }
        redirect('/commandes/admin/services.php');
    }

    $services = Service::all($pdo);
    render('admin/services', ['pageTitle' => 'Plats', 'services' => $services]);
}

function admin_service_form_controller(PDO $pdo): void
{
    require_admin();

    $id = isset($_GET['id']) ? (int)$_GET['id'] : null;
    $service = $id ? Service::find($pdo, $id) : null;
    if ($id && !$service) {
        flash('error', 'Plat introuvable.');
        redirect('/commandes/admin/services.php');
    }

    $availableImages = admin_available_images();
    $errors = [];
    $old = $service ?? ['name' => '', 'description' => '', 'image' => '', 'price' => '', 'available' => 1];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        csrf_verify();
        $old = [
            'name' => trim($_POST['name'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'image' => $_POST['image'] ?? '',
            'price' => $_POST['price'] ?? '',
            'available' => isset($_POST['available']) ? 1 : 0,
        ];

        $uploadedImage = admin_handle_image_upload($_FILES['image_upload'] ?? [], $errors);
        if ($uploadedImage) {
            $old['image'] = $uploadedImage;
            $availableImages[] = $uploadedImage;
        }

        if ($old['name'] === '') $errors[] = 'Le nom est obligatoire.';
        if (!is_numeric($old['price']) || (float)$old['price'] < 0) $errors[] = 'Le prix doit être un nombre positif.';
        if ($old['image'] !== '' && !$uploadedImage && !in_array($old['image'], $availableImages, true)) $errors[] = 'Image invalide.';

        if (empty($errors)) {
            if ($id) {
                Service::update($pdo, $id, $old);
                flash('success', 'Plat mis à jour.');
            } else {
                Service::create($pdo, $old);
                flash('success', 'Plat créé.');
            }
            redirect('/commandes/admin/services.php');
        }
    }

    render('admin/service_form', [
        'pageTitle' => $id ? 'Modifier le plat' : 'Nouveau plat',
        'errors' => $errors,
        'old' => $old,
        'id' => $id,
        'availableImages' => $availableImages,
    ]);
}
