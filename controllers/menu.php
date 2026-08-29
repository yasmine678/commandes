<?php
require_once BASE_PATH . '/models/Menu.php';
require_once BASE_PATH . '/models/OrderModel.php';

function menu_controller(PDO $pdo): void
{
    require_login();
    $usId = (int)current_user()['usId'];

    $menu = Menu::latestPublished($pdo);

    if (!$menu) {
        render('menu/index', ['pageTitle' => 'Menu de la semaine', 'menu' => null]);
        return;
    }

    $isOpen = Menu::isOrderWindowOpen($menu);
    $deliveryDays = Menu::deliveryDays($menu);
    $services = Menu::servicesForMenu($pdo, (int)$menu['meId']);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        csrf_verify();

        if (!$isOpen) {
            flash('error', 'Les commandes sont fermées pour ce menu.');
            redirect('/commandes/menu.php');
        }

        $dateLivraison = $_POST['dateLivraison'] ?? '';
        $items = $_POST['qty'] ?? [];

        if (!in_array($dateLivraison, $deliveryDays, true)) {
            flash('error', 'Jour de livraison invalide.');
            redirect('/commandes/menu.php');
        }

        try {
            OrderModel::placeOrder($pdo, $usId, $dateLivraison, $items);
            flash('success', 'Votre commande pour le ' . format_date_fr($dateLivraison) . ' a bien été enregistrée.');
        } catch (InvalidArgumentException $e) {
            flash('error', $e->getMessage());
        }

        redirect('/commandes/menu.php');
    }

    $existingOrders = [];
    foreach ($deliveryDays as $day) {
        $order = OrderModel::findForUserAndDay($pdo, $usId, $day);
        if ($order) {
            $lines = OrderModel::linesForOrder($pdo, (int)$order['ordId']);
            $existingOrders[$day] = ['order' => $order, 'lines' => $lines];
        }
    }

    render('menu/index', [
        'pageTitle' => 'Menu de la semaine',
        'menu' => $menu,
        'isOpen' => $isOpen,
        'deliveryDays' => $deliveryDays,
        'services' => $services,
        'existingOrders' => $existingOrders,
    ]);
}
