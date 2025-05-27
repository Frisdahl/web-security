<?php
require_once 'database.php';

$users = [
    ['username' => 'alex', 'password' => 'test123', 'role' => 'admin'],
    ['username' => 'eva', 'password' => 'secret',  'role' => 'user'],
    ['username' => 'bob', 'password' => 'hunter2', 'role' => 'user'],
    ['username' => 'maria', 'password' => 'pass123', 'role' => 'admin'],
];

foreach ($users as $user) {
    $hash = password_hash($user['password'], PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
    $stmt->execute([$user['username'], $hash, $user['role']]);
}

echo "✅ Users created with hashed passwords.";
