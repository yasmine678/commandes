<?php
require_once __DIR__ . '/../config/config.php';
require_admin();

$nbUsers = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE status = 'collaborateur'")->fetchColumn();
$activeMenu = $pdo->query("SELECT * FROM menu WHERE statut = 'publie' ORDER BY date_begg DESC LIMIT 1")->fetch();
$nbInstitutions = (int)$pdo->query("SELECT COUNT(*) FROM institution WHERE active = 1")->fetchColumn();

$orderCounts = array_fill_keys(['en attente', 'en cours', 'terminée', 'livrée', 'annulée'], 0);
$stmt = $pdo->query('SELECT status, COUNT(*) AS c FROM orders GROUP BY status');
foreach ($stmt->fetchAll() as $row) {
    $orderCounts[$row['status']] = (int)$row['c'];
}

render('admin/dashboard', [
    'pageTitle' => 'Administration',
    'nbUsers' => $nbUsers,
    'activeMenu' => $activeMenu,
    'nbInstitutions' => $nbInstitutions,
    'orderCounts' => $orderCounts,
]);
