<?php
session_start();


// ✅ Add security headers
header("X-Frame-Options: DENY"); // DENY to prevent clickjacking
header("X-Content-Type-Options: nosniff"); // to enforce MIME-type correctness
header("Referrer-Policy: no-referrer"); // to avoid leaking URLs
header("Permissions-Policy: geolocation=(), microphone=()");  // to disable geolocation and microphone access
header("Content-Security-Policy: default-src 'self'; script-src 'self'"); // to restrict resources to the same origin


// Max 5 loginforsøg
if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
}

if ($_SESSION['login_attempts'] >= 5) {
    die("⚠️ Too many login attempts. Please wait 5 minutes.");
}

// Handle logout
if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header("Location: login.php?message=logged_out");
    exit;
}

require 'database.php';

$showSql = '';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = $_POST['username'] ?? '';
  $password = $_POST['password'] ?? '';


    // ❌ Unsafe SQL query (vulnerable to SQL injection)
    // $sql = "SELECT * FROM users WHERE username = '$username' AND password = '$password'";


    try {
      $sql = "SELECT * FROM users WHERE username = ?";
      $showSql = $sql;

      $stmt = $pdo->prepare($sql);
      $stmt->execute([$username]);
      $result = $stmt->fetch();

      if ($result && password_verify($password, $result['password'])) {
        session_regenerate_id(true);
        $_SESSION['user'] = $result['username'];
        $_SESSION['role'] = $result['role'];
        $message = "✅ Login successful! Welcome, {$result['username']} (role: {$result['role']})";
        $_SESSION['login_attempts'] = 0;

        // ✅ Redirect based on role
        if ($result['role'] === 'admin') {
          header("Location: admin.php");
          exit;
        } else {
          header("Location: index.php");
          exit;
        }

      } else {
        $_SESSION['login_attempts']++;
        $message = "❌ Invalid username or password.";
      }

    } catch (PDOException $e) {
      $message = "❌ SQL error: " . $e->getMessage();
    }
    }
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login - Web Security Demo</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body>
  <div class="container">
    <div class="header">
      <h1>🔐 Secure Login</h1>
      <p>SQL Injection Protection Demonstration</p>
    </div>
    
    <div class="content">
      <h2>Login to Your Account</h2>
        <?php if (isset($_GET['timeout'])): ?>
      <div class="message message-warning">
        <span class="security-icon warning"></span>
        Your session has expired due to inactivity. Please log in again.
      </div>
      <?php endif; ?>
      
      <?php if (isset($_GET['message']) && $_GET['message'] === 'logged_out'): ?>
      <div class="message message-success">
        <span class="security-icon safe"></span>
        You have been successfully logged out.
      </div>
      <?php endif; ?>
      
      <form method="POST">
        <label for="username">Username:</label>
        <input name="username" id="username" type="text" required>
        
        <label for="password">Password:</label>
        <input name="password" id="password" type="password" required>
        
        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 1rem;">
          <a href="register.php" class="btn btn-success">Create Account</a>
          <button type="submit">Login</button>
        </div>
      </form>

      <?php if ($showSql): ?>
      <div class="security-demo">
        <h3><span class="security-icon safe"></span>SQL Query Used (Protected):</h3>
        <pre><?= htmlspecialchars($showSql) ?></pre>
        <p><strong>Note:</strong> This query uses prepared statements to prevent SQL injection attacks.</p>
      </div>
      <?php endif; ?>

      <?php if ($message): ?>
      <div class="message <?= strpos($message, '❌') !== false ? 'message-error' : 'message-success' ?>">
        <?= $message ?>
      </div>
      <?php endif; ?>
    </div>
    
    <div class="footer">
      <p>&copy; 2025 Web Security Demo - Educational Purpose Only</p>
    </div>
  </div>
</body>
</html>
