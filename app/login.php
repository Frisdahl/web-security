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


//   // ✅ This is the SQL query using placeholders (question marks) to prevent SQL injection
// $sql = "SELECT * FROM users WHERE username = ? AND password = ?";

// // 🧪 Optional: This shows the raw SQL string safely (without injecting user input)
// // It’s useful for debugging or displaying the query without revealing sensitive input
// $showSql = $pdo->quote($sql); 

// // 🛡️ Prepare the SQL query using PDO (prevents SQL injection)
// // This tells MySQL to treat the next inputs as data, not executable SQL
// $stmt = $pdo->prepare($sql);

// // ▶️ Execute the query and pass the user input as an array to bind to the placeholders
// // The first ? gets $username, the second gets $password
// $stmt->execute([$username, $password]);

// // 📦 Fetch the first result (if any) as an associative array
// // Returns false if no matching user is found
// $result = $stmt->fetch();

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
