<?php
session_start();
require 'database.php';

$showSql = '';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = $_POST['username'] ?? '';
  $password = $_POST['password'] ?? '';

  // ❌ VULNERABLE SQL (for demonstration)
  $sql = "SELECT * FROM users WHERE username = '$username' AND password = '$password'";
  $showSql = $sql;

  try {
    $result = $pdo->query($sql)->fetch();

    if ($result) {
      $_SESSION['user'] = $result['username'];
      $_SESSION['role'] = $result['role'];
      $message = "✅ Login successful! Welcome, {$result['username']} (role: {$result['role']})";
    } else {
      $message = "❌ Login failed.";
    }
  } catch (PDOException $e) {
    $message = "❌ SQL error: " . $e->getMessage();
  }
}
?>

<!DOCTYPE html>
<html>
<head><title>Login Demo</title></head>
<body>
  <h2>Login (SQLi Demonstration)</h2>
  <form method="POST">
    <label>Username: <input name="username" type="text"></label><br>
    <label>Password: <input name="password" type="password"></label><br>
    <button type="submit">Login</button>
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
