<?php
require_once BASE_PATH . '/models/User.php';

function admin_users_index_controller(PDO $pdo): void
{
    require_admin();
    $currentId = (int)current_user()['usId'];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        csrf_verify();
        $usId = (int)$_POST['usId'];
        $action = $_POST['action'] ?? '';

        if ($usId === $currentId) {
            flash('error', 'Vous ne pouvez pas modifier votre propre compte depuis cette page.');
            redirect('/commandes/admin/utilisateurs.php');
        }

        if ($action === 'toggle_status') {
            $target = User::findById($pdo, $usId);
            if ($target) {
                $newStatus = $target['status'] === 'administrateur' ? 'collaborateur' : 'administrateur';
                User::updateStatus($pdo, $usId, $newStatus);
                flash('success', 'Rôle mis à jour.');
            }
        } elseif ($action === 'delete') {
            User::delete($pdo, $usId);
            flash('success', 'Utilisateur supprimé.');
        }
        redirect('/commandes/admin/utilisateurs.php');
    }

    $users = User::all($pdo);
    render('admin/users', ['pageTitle' => 'Utilisateurs', 'users' => $users, 'currentId' => $currentId]);
}
