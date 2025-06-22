<?php
// Set to true for local development, false for cloud
$useLocalDb = true;

if ($useLocalDb) {
    $host = 'db';
    $port = '3306';
    $dbname = 'security_demo';
    $user = 'root';
    $pass = 'root';
    $ssl = [];
} else {
    $host = getenv('DB_HOST');
    $port = getenv('DB_PORT');
    $dbname = getenv('DB_NAME');
    $user = getenv('DB_USER');
    $pass = getenv('DB_PASS');
    $ssl = [PDO::MYSQL_ATTR_SSL_CA => '/etc/ssl/certs/aiven-ca.pem'];
}

try {
    $pdo = new PDO(
        "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4",
        $user,
        $pass,
        array_merge([PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION], $ssl)
    );
} catch (PDOException $e) {
    die('Connection failed: ' . $e->getMessage());
}
