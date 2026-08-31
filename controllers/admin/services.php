<?php
require_once BASE_PATH . '/models/Service.php';

/**
 * Image files available in assets/images/, offered as a picker in the
 * service form (kept simple: no upload UI yet, just pick from what's there).
 */
function admin_available_images(): array
{
    $files = glob(BASE_PATH . '/assets/images/*.{jpg,jpeg,png,webp}', GLOB_BRACE) ?: [];
    return array_map('basename', $files);
}

function admin_services_index_controller(PDO $pdo): void
{
    require_admin();

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
        csrf_verify();
        try {
            Service::delete($pdo, (int)$_POST['serId']);
            flash('success', 'Prestation supprimée.');
        } catch (PDOException $e) {
            flash('error', "Impossible de supprimer cette prestation : elle est utilisée dans une ou plusieurs commandes ou menus.");
        }
        redirect('/commandes/admin/services.php');
    }

    $services = Service::all($pdo);
    render('admin/services', ['pageTitle' => 'Prestations', 'services' => $services]);
}

function admin_service_form_controller(PDO $pdo): void
{
    require_admin();

    $id = isset($_GET['id']) ? (int)$_GET['id'] : null;
    $service = $id ? Service::find($pdo, $id) : null;
    if ($id && !$service) {
        flash('error', 'Prestation introuvable.');
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

        if ($old['name'] === '') $errors[] = 'Le nom est obligatoire.';
        if (!is_numeric($old['price']) || (float)$old['price'] < 0) $errors[] = 'Le prix doit être un nombre positif.';
        if ($old['image'] !== '' && !in_array($old['image'], $availableImages, true)) $errors[] = 'Image invalide.';

        if (empty($errors)) {
            if ($id) {
                Service::update($pdo, $id, $old);
                flash('success', 'Prestation mise à jour.');
            } else {
                Service::create($pdo, $old);
                flash('success', 'Prestation créée.');
            }
            redirect('/commandes/admin/services.php');
        }
    }

    render('admin/service_form', [
        'pageTitle' => $id ? 'Modifier la prestation' : 'Nouvelle prestation',
        'errors' => $errors,
        'old' => $old,
        'id' => $id,
        'availableImages' => $availableImages,
    ]);
}
