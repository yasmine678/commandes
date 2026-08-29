<?php
require_once BASE_PATH . '/models/OrderModel.php';

function admin_orders_index_controller(PDO $pdo): void
{
    require_admin();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        csrf_verify();
        OrderModel::updateStatus($pdo, (int)$_POST['ordId'], $_POST['status']);
        flash('success', 'Statut mis à jour.');
        redirect('/commandes/admin/commandes.php' . (isset($_GET['jour']) ? '?jour=' . urlencode($_GET['jour']) : ''));
    }

    $day = $_GET['jour'] ?? null;
    $orders = OrderModel::listAll($pdo, $day ?: null);
    foreach ($orders as &$order) {
        $order['lines'] = OrderModel::linesForOrder($pdo, (int)$order['ordId']);
    }
    unset($order);

    render('admin/orders', ['pageTitle' => 'Commandes', 'orders' => $orders, 'day' => $day]);
}
