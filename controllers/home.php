<?php
require_once BASE_PATH . '/models/Menu.php';

/**
 * Home page for collaborators: hero banner, services teaser, and a
 * call-to-action toward the weekly menu / ordering page (menu.php).
 * Admins never see this - index.php sends them straight to the admin
 * dashboard instead.
 */
function home_controller(PDO $pdo): void
{
    require_login();

    $menu = Menu::latestPublished($pdo);
    $isOpen = $menu ? Menu::isOrderWindowOpen($menu) : false;

    render('home/index', [
        'pageTitle' => 'Accueil',
        'menu' => $menu,
        'isOpen' => $isOpen,
    ]);
}
