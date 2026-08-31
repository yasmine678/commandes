<?php
require_once __DIR__ . '/config/config.php';

if (!is_logged_in()) {
    redirect('/commandes/login.php');
}
if (is_admin()) {
    redirect('/commandes/admin/index.php');
}

require_once __DIR__ . '/controllers/home.php';
home_controller($pdo);
