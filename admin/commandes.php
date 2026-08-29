<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../controllers/admin/orders.php';

admin_orders_index_controller($pdo);
