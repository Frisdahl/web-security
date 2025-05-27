<?php
session_start();

// ✅ Add security headers
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");
header("Referrer-Policy: no-referrer");
header("Permissions-Policy: geolocation=(), microphone=()");
header("Content-Security-Policy: default-src 'self'; script-src 'self'");


require 'database.php';

$message = '';
$showSql = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = $_POST['username'] ?? '';
  $password = $_POST['password'] ?? '';
  $role = $_POST['role'] ?? 'user';

  // Basic validation
  if (empty($username) || empty($password)) {
    $message = '❌ Please fill out all fields.';
  } else {
    try {
      // Check if username already exists
      $checkStmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
      $checkStmt->execute([$username]);

      if ($checkStmt->fetch()) {
        $message = '❌ Username already taken.';
      } else {
        // Hash the password securely
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Insert the new user
        $sql = "INSERT INTO users (username, password, role) VALUES (?, ?, ?)";
        $showSql = $sql;

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$username, $hashedPassword, $role]);

        $message = "✅ Registration successful! You can now <a href='login.php'>log in</a>.";
      }

    } catch (PDOException $e) {
      $message = "❌ SQL error: " . $e->getMessage();
    }
  }
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Register</title>
  <link rel="stylesheet" href="/css/styles.css">
</head>
<body>
  <h2>User Registration</h2>
  <form method="POST">
    <label>Username: <input name="username" type="text" required></label><br>
    <label>Password: <input name="password" type="password" required></label><br>
    <label>Role:
      <select name="role">
        <option value="user">User</option>
        <option value="admin">Admin</option>
      </select>
    </label><br>
    <button type="submit">Register</button>
  </form>

  <?php if ($showSql): ?>
    <h3>SQL Query Used:</h3>
    <pre><?= htmlspecialchars($showSql) ?></pre>
  <?php endif; ?>

  <?php if ($message): ?>
    <p><strong><?= $message ?></strong></p>
  <?php endif; ?>
</body>
</html>
