<?php
require_once BASE_PATH . '/models/Menu.php';
require_once BASE_PATH . '/models/Service.php';

function admin_menus_index_controller(PDO $pdo): void
{
    require_admin();

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
        csrf_verify();
        Menu::delete($pdo, (int)$_POST['meId']);
        flash('success', 'Menu supprimé.');
        redirect('/commandes/admin/menus.php');
    }

    $menus = Menu::all($pdo);
    render('admin/menus', ['pageTitle' => 'Menus', 'menus' => $menus]);
}

function admin_menu_form_controller(PDO $pdo): void
{
    require_admin();

    $id = isset($_GET['id']) ? (int)$_GET['id'] : null;
    $menu = $id ? Menu::find($pdo, $id) : null;
    if ($id && !$menu) {
        flash('error', 'Menu introuvable.');
        redirect('/commandes/admin/menus.php');
    }

    $allServices = Service::all($pdo);
    $errors = [];

    $old = $menu ?? [
        'title' => '', 'description' => '', 'date_begg' => '', 'date_end' => '',
        'date_open' => '', 'date_endin' => '', 'statut' => 'brouillon',
    ];
    $selectedServiceIds = $id ? Menu::serviceIdsForMenu($pdo, $id) : [];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        csrf_verify();

        $old = [
            'title' => trim($_POST['title'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'date_begg' => $_POST['date_begg'] ?? '',
            'date_end' => $_POST['date_end'] ?? '',
            'date_open' => str_replace('T', ' ', $_POST['date_open'] ?? ''),
            'date_endin' => str_replace('T', ' ', $_POST['date_endin'] ?? ''),
            'statut' => $_POST['statut'] ?? 'brouillon',
        ];
        $selectedServiceIds = array_map('intval', $_POST['services'] ?? []);

        if ($old['title'] === '') $errors[] = 'Le titre est obligatoire.';
        if ($old['date_begg'] === '' || $old['date_end'] === '' || $old['date_begg'] > $old['date_end']) {
            $errors[] = 'La période du menu (début/fin) est invalide.';
        }
        if ($old['date_open'] === '' || $old['date_endin'] === '' || $old['date_open'] >= $old['date_endin']) {
            $errors[] = "La fenêtre de commande (ouverture/clôture) est invalide.";
        }
        if (!in_array($old['statut'], ['brouillon', 'publie', 'archive'], true)) {
            $errors[] = 'Statut invalide.';
        }

        if (empty($errors)) {
            if ($id) {
                Menu::update($pdo, $id, $old);
            } else {
                $id = Menu::create($pdo, $old);
            }
            Menu::setServices($pdo, $id, $selectedServiceIds);
            flash('success', 'Menu enregistré.');
            redirect('/commandes/admin/menus.php');
        }
    }

    render('admin/menu_form', [
        'pageTitle' => $id ? 'Modifier le menu' : 'Nouveau menu',
        'errors' => $errors,
        'old' => $old,
        'id' => $id,
        'allServices' => $allServices,
        'selectedServiceIds' => $selectedServiceIds,
    ]);
}
