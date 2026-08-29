<?php
require_once BASE_PATH . '/models/Institution.php';

function admin_institutions_index_controller(PDO $pdo): void
{
    require_admin();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        csrf_verify();
        $action = $_POST['action'] ?? '';
        $id = (int)($_POST['insId'] ?? 0);

        if ($action === 'regenerate') {
            $code = Institution::regenerateCode($pdo, $id);
            flash('success', "Nouveau code généré : $code");
        } elseif ($action === 'delete') {
            if (Institution::memberCount($pdo, $id) > 0) {
                flash('error', 'Impossible de supprimer : des membres appartiennent encore à cette institution.');
            } else {
                Institution::delete($pdo, $id);
                flash('success', 'Institution supprimée.');
            }
        }
        redirect('/commandes/admin/institutions.php');
    }

    $institutions = Institution::all($pdo);
    foreach ($institutions as &$institution) {
        $institution['memberCount'] = Institution::memberCount($pdo, (int)$institution['insId']);
    }
    unset($institution);

    render('admin/institutions', ['pageTitle' => 'Institutions', 'institutions' => $institutions]);
}

function admin_institution_form_controller(PDO $pdo): void
{
    require_admin();

    $id = isset($_GET['id']) ? (int)$_GET['id'] : null;
    $institution = $id ? Institution::find($pdo, $id) : null;
    if ($id && !$institution) {
        flash('error', 'Institution introuvable.');
        redirect('/commandes/admin/institutions.php');
    }

    $errors = [];
    $old = $institution ?? ['name' => '', 'active' => 1];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        csrf_verify();
        $old['name'] = trim($_POST['name'] ?? '');
        $old['active'] = isset($_POST['active']) ? 1 : 0;

        if ($old['name'] === '') $errors[] = 'Le nom est obligatoire.';
        if (empty($errors) && Institution::nameExists($pdo, $old['name'], $id)) {
            $errors[] = 'Une institution avec ce nom existe déjà.';
        }

        if (empty($errors)) {
            if ($id) {
                Institution::update($pdo, $id, $old['name'], (bool)$old['active']);
                flash('success', 'Institution mise à jour.');
            } else {
                $newId = Institution::create($pdo, $old['name']);
                $institution = Institution::find($pdo, $newId);
                flash('success', "Institution créée. Code d'accès : " . $institution['access_code']);
            }
            redirect('/commandes/admin/institutions.php');
        }
    }

    render('admin/institution_form', [
        'pageTitle' => $id ? "Modifier l'institution" : 'Nouvelle institution',
        'errors' => $errors,
        'old' => $old,
        'id' => $id,
    ]);
}
