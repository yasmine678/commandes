<?php
$isAdminSection = is_admin() && str_starts_with($_SERVER['REQUEST_URI'], '/commandes/admin/');
$currentPage = basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

$adminLinks = [
    'index.php' => ['label' => 'Tableau de bord', 'icon' => 'dashboard'],
    'menus.php' => ['label' => 'Menus', 'icon' => 'calendar'],
    'services.php' => ['label' => 'Prestations', 'icon' => 'tag'],
    'commandes.php' => ['label' => 'Commandes', 'icon' => 'clipboard'],
    'utilisateurs.php' => ['label' => 'Utilisateurs', 'icon' => 'users'],
    'institutions.php' => ['label' => 'Institutions', 'icon' => 'building'],
];
$adminActiveOverrides = [
    'menu-form.php' => 'menus.php',
    'service-form.php' => 'services.php',
    'institution-form.php' => 'institutions.php',
];
$adminActivePage = $adminActiveOverrides[$currentPage] ?? $currentPage;

$currentUser = current_user();
$initials = $currentUser ? mb_strtoupper(mb_substr($currentUser['firstName'] ?? '', 0, 1) . mb_substr($currentUser['lastName'] ?? '', 0, 1)) : '';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= isset($pageTitle) ? h($pageTitle) . ' — ' : '' ?>Commandes</title>
<link rel="stylesheet" href="/commandes/assets/css/style.css?v=<?= filemtime(BASE_PATH . '/assets/css/style.css') ?>">
</head>
<body>
<header class="site-header">
    <div class="container header-inner">
        <a class="brand" href="/commandes/index.php">
            <svg class="brand-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <rect x="6" y="3" width="12" height="18" rx="2"></rect>
                <path d="M9 3v2a1 1 0 0 0 1 1h4a1 1 0 0 0 1-1V3"></path>
                <path d="m9 13 2 2 4-4"></path>
            </svg>
            DEKA EDITIONS
        </a>
        <nav>
            <?php if (is_logged_in()): ?>
                <?php if (is_admin()): ?>
                    <a href="/commandes/admin/index.php">Administration</a>
                <?php else: ?>
                    <a href="/commandes/menu.php">Menu de la semaine</a>
                    <a href="/commandes/mes-commandes.php">Mes commandes</a>
                <?php endif; ?>
                <details class="profile-menu">
                    <summary class="profile-chip">
                        <span class="avatar"><?= h($initials) ?></span>
                        <span class="profile-text">
                            <span class="profile-name"><?= h($currentUser['firstName'] . ' ' . $currentUser['lastName']) ?></span>
                            <span class="profile-role"><?= is_admin() ? 'Administrateur' : 'Collaborateur' ?></span>
                        </span>
                        <?= icon('chevron-down') ?>
                    </summary>
                    <div class="profile-dropdown">
                        <a href="/commandes/logout.php">Déconnexion</a>
                    </div>
                </details>
            <?php endif; ?>
        </nav>
    </div>
</header>
<?php if ($isAdminSection): ?>
<div class="admin-layout container">
    <aside class="admin-sidebar">
        <p class="sidebar-title">Gestion</p>
        <nav class="sidebar-nav">
            <?php foreach ($adminLinks as $file => $link): $active = $adminActivePage === $file; ?>
                <a href="/commandes/admin/<?= $file ?>" class="<?= $active ? 'active' : '' ?>">
                    <?= icon($link['icon']) ?>
                    <span><?= h($link['label']) ?></span>
                    <?php if ($active): ?><span class="nav-dot"></span><?php endif; ?>
                </a>
            <?php endforeach; ?>
        </nav>
    </aside>
    <main class="admin-content">
<?php else: ?>
<main class="container">
<?php endif; ?>
    <?php foreach (get_flashes() as $flash): ?>
        <div class="flash flash-<?= h($flash['type']) ?>"><?= h($flash['message']) ?></div>
    <?php endforeach; ?>
