<?php
$host = env('DB_HOST', 'localhost');
$dbname = env('DB_NAME', 'ordersdb');
$dbuser = env('DB_USER', 'root');
$dbpass = env('DB_PASS', '');

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $dbuser,
        $dbpass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    http_response_code(500);
    die('Erreur de connexion à la base de données : ' . $e->getMessage());
}
