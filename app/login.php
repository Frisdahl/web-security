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
<html>
<head>
  <title>Login Demo</title>
</head>

<body>
  <h2>Login (SQLi Demonstration)</h2>
  <form method="POST">
    <label>Username: <input name="username" type="text"></label><br>
    <label>Password: <input name="password" type="password"></label><br>
      <a href="register.php">Register here</a>
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
