<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../controllers/admin/users.php';

admin_users_index_controller($pdo);
