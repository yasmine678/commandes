<?php

function login_user(array $user): void
{
    session_regenerate_id(true);
    $_SESSION['user'] = [
        'usId' => $user['usId'],
        'firstName' => $user['firstName'],
        'lastName' => $user['lastName'],
        'email' => $user['email'],
        'institution' => $user['institutionName'],
        'status' => $user['status'],
    ];
}

function logout_user(): void
{
    $_SESSION = [];
    session_destroy();
}

function current_user(): ?array
{
    return is_logged_in() ? $_SESSION['user'] : null;
}

function is_logged_in(): bool
{
    if (!isset($_SESSION['user']['usId'], $_SESSION['user']['status'])) {
        // Malformed/foreign session data (e.g. a stale cookie) - drop it
        // rather than let every page blow up on a missing key.
        unset($_SESSION['user']);
        return false;
    }
    return true;
}

function is_admin(): bool
{
    return is_logged_in() && $_SESSION['user']['status'] === 'administrateur';
}

function require_login(): void
{
    if (!is_logged_in()) {
        redirect('/commandes/login.php');
    }
}

function require_admin(): void
{
    require_login();
    if (!is_admin()) {
        http_response_code(403);
        die('Accès réservé aux administrateurs.');
    }
}

function home_url(): string
{
    return is_admin() ? '/commandes/admin/index.php' : '/commandes/menu.php';
}
