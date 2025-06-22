<?php
require_once 'database.php';

$users = [
    ['username' => 'alex', 'password' => 'test123', 'role' => 'admin'],
    ['username' => 'eva', 'password' => 'secret',  'role' => 'user'],
    ['username' => 'bob', 'password' => 'hunter2', 'role' => 'user'],
    ['username' => 'maria', 'password' => 'pass123', 'role' => 'admin'],
];

$created = 0;
$skipped = 0;

foreach ($users as $user) {
    // Check if user exists first
    $check = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
    $check->execute([$user['username']]);
    
    if ($check->fetchColumn() > 0) {
        echo "⏭️ User '{$user['username']}' already exists, skipping.\n";
        $skipped++;
        continue;
    }
    
    // If user doesn't exist, create it
    $hash = password_hash($user['password'], PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
    $stmt->execute([$user['username'], $hash, $user['role']]);
    $created++;
    
    echo "✅ Created user: {$user['username']} with role: {$user['role']}\n";
}

echo "\n--- Summary ---\n";
echo "✅ Users created: $created\n";
echo "⏭️ Users skipped: $skipped\n";
echo "📊 Total users: " . ($created + $skipped) . "\n";
