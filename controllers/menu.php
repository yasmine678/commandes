<?php
require_once BASE_PATH . '/models/Menu.php';
require_once BASE_PATH . '/models/OrderModel.php';
require_once BASE_PATH . '/models/Institution.php';

/**
 * Showcase / home page for collaborators: hero banner, services teaser,
 * and a call-to-action toward the actual ordering page.
 */
function menu_controller(PDO $pdo): void
{
    require_login();

    $menu = Menu::latestPublished($pdo);
    $isOpen = $menu ? Menu::isOrderWindowOpen($menu) : false;

    render('menu/index', [
        'pageTitle' => 'Menu de la semaine',
        'menu' => $menu,
        'isOpen' => $isOpen,
    ]);
}

/**
 * The actual ordering page: per-day forms to select services and quantities,
 * along with the client institution and a free-text explanation of the need.
 */
function order_controller(PDO $pdo): void
{
    require_login();
    $usId = (int)current_user()['usId'];
    $institutions = Institution::activeList($pdo);

    $menu = Menu::latestPublished($pdo);

    if (!$menu) {
        render('menu/order', ['pageTitle' => 'Commander', 'menu' => null]);
        return;
    }

    $isOpen = Menu::isOrderWindowOpen($menu);
    $deliveryDays = Menu::deliveryDays($menu);
    $services = Menu::servicesForMenu($pdo, (int)$menu['meId']);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        csrf_verify();

        if (!$isOpen) {
            flash('error', 'Les commandes sont fermées pour ce menu.');
            redirect('/commandes/commander.php');
        }

        $dateLivraison = $_POST['dateLivraison'] ?? '';
        $items = $_POST['qty'] ?? [];
        $institution = trim($_POST['institution'] ?? '');
        $note = trim($_POST['note'] ?? '');

        if (!in_array($dateLivraison, $deliveryDays, true)) {
            flash('error', 'Jour de livraison invalide.');
            redirect('/commandes/commander.php');
        }

        if ($institution === '') {
            flash('error', "Merci d'indiquer l'institution pour laquelle cette commande est passée.");
            redirect('/commandes/commander.php');
        }

        if ($note === '') {
            flash('error', 'Merci de préciser ce dont vous avez besoin.');
            redirect('/commandes/commander.php');
        }

        try {
            OrderModel::placeOrder($pdo, $usId, $dateLivraison, $items, $institution, $note);
            flash('success', 'Votre commande pour le ' . format_date_fr($dateLivraison) . ' a bien été enregistrée.');
        } catch (InvalidArgumentException $e) {
            flash('error', $e->getMessage());
        }

        redirect('/commandes/commander.php');
    }

    $existingOrders = [];
    foreach ($deliveryDays as $day) {
        $order = OrderModel::findForUserAndDay($pdo, $usId, $day);
        if ($order) {
            $lines = OrderModel::linesForOrder($pdo, (int)$order['ordId']);
            $existingOrders[$day] = ['order' => $order, 'lines' => $lines];
        }
    }

    render('menu/order', [
        'pageTitle' => 'Commander',
        'menu' => $menu,
        'isOpen' => $isOpen,
        'deliveryDays' => $deliveryDays,
        'services' => $services,
        'existingOrders' => $existingOrders,
        'institutions' => $institutions,
    ]);
}
