<?php
date_default_timezone_set('Europe/Paris');

if (session_status() === PHP_SESSION_NONE) {
    // Distinct name/path so this app's session never collides with another
    // PHP project also running under localhost (they'd otherwise share the
    // default PHPSESSID cookie for the whole domain).
    session_name('commandes_sess');
    session_set_cookie_params(['path' => '/commandes/']);
    session_start();
}

define('BASE_PATH', dirname(__DIR__));

require_once BASE_PATH . '/config/connexion.php';
require_once BASE_PATH . '/includes/helpers.php';
require_once BASE_PATH . '/includes/auth.php';
