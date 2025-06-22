<?php
session_start();

// ✅ Add security headers
header("X-Frame-Options: DENY"); // DENY to prevent clickjacking
header("X-Content-Type-Options: nosniff"); // to enforce MIME-type correctness
header("Referrer-Policy: no-referrer"); // to avoid leaking URLs
header("Permissions-Policy: geolocation=(), microphone=()");  // to disable geolocation and microphone access
header("Content-Security-Policy: default-src 'self'; script-src 'self'"); // to restrict resources to the same origin


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
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register - Web Security Demo</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body>
  <div class="container">
    <div class="header">
      <h1>👤 Create Account</h1>
      <p>Secure User Registration</p>
    </div>
    
    <div class="content">
      <h2>Register New Account</h2>
      
      <form method="POST">
        <label for="username">Username:</label>
        <input name="username" id="username" type="text" required placeholder="Choose a unique username">
        
        <label for="password">Password:</label>
        <input name="password" id="password" type="password" required placeholder="Enter a secure password">
        
        <label for="role">Account Type:</label>
        <select name="role" id="role">
          <option value="user">Standard User</option>
          <option value="admin">Administrator</option>
        </select>
        
        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 1rem;">
          <a href="login.php" class="btn btn-warning">Back to Login</a>
          <button type="submit">Create Account</button>
        </div>
      </form>

      <?php if ($showSql): ?>
      <div class="security-demo">
        <h3><span class="security-icon safe"></span>SQL Query Used:</h3>
        <pre><?= htmlspecialchars($showSql) ?></pre>
        <p><strong>Note:</strong> Passwords are securely hashed using PHP's password_hash() function.</p>
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
