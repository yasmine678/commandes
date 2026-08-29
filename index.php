<?php
require_once __DIR__ . '/config/config.php';

redirect(is_logged_in() ? home_url() : '/commandes/login.php');
