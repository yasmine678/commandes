<?php
require_once BASE_PATH . '/models/OrderModel.php';
require_once BASE_PATH . '/models/Menu.php';

function my_orders_controller(PDO $pdo): void
{
    require_login();
    $usId = (int)current_user()['usId'];

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'cancel') {
        csrf_verify();
        $ordId = (int)($_POST['ordId'] ?? 0);
        $order = OrderModel::find($pdo, $ordId);

        if (!$order || (int)$order['usId'] !== $usId) {
            flash('error', 'Commande introuvable.');
        } else {
            $menuStmt = $pdo->prepare('SELECT * FROM menu WHERE ? BETWEEN date_begg AND date_end');
            $menuStmt->execute([$order['dateLivraison']]);
            $menu = $menuStmt->fetch();

            if ($menu && !Menu::isOrderWindowOpen($menu)) {
                flash('error', 'La période de commande est terminée, vous ne pouvez plus annuler.');
            } else {
                OrderModel::cancel($pdo, $ordId, $usId);
                flash('success', 'Commande annulée.');
            }
        }
        redirect('/commandes/mes-commandes.php');
    }

    $orders = OrderModel::listForUser($pdo, $usId);
    foreach ($orders as &$order) {
        $order['lines'] = OrderModel::linesForOrder($pdo, (int)$order['ordId']);
    }
    unset($order);

    render('orders/mine', ['pageTitle' => 'Mes commandes', 'orders' => $orders]);
}
