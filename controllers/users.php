<?php
require_once BASE_PATH . '/models/User.php';
require_once BASE_PATH . '/models/Institution.php';

function login_controller(PDO $pdo): void
{
    if (is_logged_in()) {
        redirect(home_url());
    }

    $errors = [];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        csrf_verify();
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        $user = User::findByEmail($pdo, $email);
        if ($user && password_verify($password, $user['password'])) {
            login_user($user);
            redirect(home_url());
        }
        $errors[] = 'Email ou mot de passe incorrect.';
    }

    render('users/login', ['pageTitle' => 'Connexion', 'errors' => $errors]);
}

function register_controller(PDO $pdo): void
{
    if (is_logged_in()) {
        redirect(home_url());
    }

    $errors = [];
    $old = ['lastName' => '', 'firstName' => '', 'email' => ''];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        csrf_verify();

        $old = [
            'lastName' => trim($_POST['lastName'] ?? ''),
            'firstName' => trim($_POST['firstName'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
        ];
        $password = $_POST['password'] ?? '';
        $passwordConfirm = $_POST['password_confirm'] ?? '';

        if ($old['lastName'] === '') $errors[] = 'Le nom est obligatoire.';
        if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Email invalide.';
        if (strlen($password) < 8) $errors[] = 'Le mot de passe doit contenir au moins 8 caractères.';
        if ($password !== $passwordConfirm) $errors[] = 'Les mots de passe ne correspondent pas.';
        if (empty($errors) && User::emailExists($pdo, $old['email'])) $errors[] = 'Cet email est déjà utilisé.';

        if (empty($errors)) {
            // The very first account ever created becomes administrator so the
            // deployment always has at least one admin without manual seeding.
            $status = User::countAdmins($pdo) === 0 ? 'administrateur' : 'collaborateur';
            $institution = Institution::defaultForSignup($pdo);

            $usId = User::create($pdo, [
                'lastName' => $old['lastName'],
                'firstName' => $old['firstName'],
                'insId' => $institution['insId'],
                'email' => $old['email'],
                'password' => $password,
                'status' => $status,
            ]);
            $user = User::findById($pdo, $usId);
            login_user($user);
            flash('success', $status === 'administrateur'
                ? 'Compte créé. Comme premier compte de la plateforme, vous êtes administrateur.'
                : 'Bienvenue ! Votre compte a été créé.');
            redirect(home_url());
        }
    }

    render('users/register', ['pageTitle' => 'Inscription', 'errors' => $errors, 'old' => $old]);
}
