<?php

$host = $_ENV['DB_HOST'] ?? 'db';
$port = $_ENV['DB_PORT'] ?? '3306';
$dbname = $_ENV['DB_NAME'] ?? 'security_demo';
$username = $_ENV['DB_USER'] ?? 'root';
$password = $_ENV['DB_PASS'] ?? 'password';

$max_retries = 5;
$retry_delay = 2;

for ($i = 0; $i < $max_retries; $i++) {
    try {
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_TIMEOUT => 30,
        ];
        
        $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
        $pdo = new PDO($dsn, $username, $password, $options);
        
        // Test the connection
        $pdo->query("SELECT 1");
        break;
        
    } catch(PDOException $e) {
        if ($i < $max_retries - 1) {
            sleep($retry_delay);
            continue;
        }
        
        die("Connection failed after $max_retries attempts: " . $e->getMessage());
    }
}
