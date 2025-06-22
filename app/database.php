<?php

try {
    $pdo = new PDO(
        'mysql:host=' . getenv('DB_HOST') .
        ';port=' . getenv('DB_PORT') .
        ';dbname=' . getenv('DB_NAME') .
        ';charset=utf8mb4',
        getenv('DB_USER'),
        getenv('DB_PASS'),
        [
            PDO::MYSQL_ATTR_SSL_CA => '/etc/ssl/certs/aiven-ca.pem',
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]
    );
} catch (PDOException $e) {
    die('Connection failed: ' . $e->getMessage());
}
