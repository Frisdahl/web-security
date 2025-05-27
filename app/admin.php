<?php
session_start();


// ✅ Add security headers
header("X-Frame-Options: DENY"); // DENY to prevent clickjacking
header("X-Content-Type-Options: nosniff"); // to enforce MIME-type correctness
header("Referrer-Policy: no-referrer"); // to avoid leaking URLs
header("Permissions-Policy: geolocation=(), microphone=()");  // to disable geolocation and microphone access
header("Content-Security-Policy: default-src 'self'; script-src 'self'"); // to restrict resources to the same origin

// Inaktivitet max 10 minutter (600 sekunder)
$timeout = 600;

if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY']) > $timeout) {
    session_unset();     // Fjern session-data
    session_destroy();   // Luk session
    header("Location: login.php?timeout=1");
    exit;
}
$_SESSION['LAST_ACTIVITY'] = time();


require 'database.php';

// ✅ Protect the page
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'admin') {
  die("🔒 Access denied. Only admins can view this page.");
}

// ✅ Generate CSRF token if not set
if (!isset($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// ✅ Fetch comments from DB
$comments = $pdo->query("SELECT id, comment, created_at FROM comments ORDER BY created_at DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin - Secure Comment App</title>
</head>
<body>

  <h1>Admin Panel</h1>
  <p>Welcome, <?= htmlspecialchars($_SESSION['user']) ?>!</p>

  <h3>All Comments</h3>

  <?php foreach ($comments as $row): ?>
    <div>
      <p><?= $row['created_at'] ?>: <?= htmlspecialchars($row['comment'], ENT_QUOTES, 'UTF-8') ?></p>

      <!-- 🗑 Delete form (admin-only) -->
      <form method="POST" action="delete_comment.php" style="display:inline;">
        <input type="hidden" name="id" value="<?= $row['id'] ?>">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
        <button type="submit">🗑 Delete</button>
      </form>
    </div>
    <hr>
  <?php endforeach; ?>

  <p><a href="index.php">Back to main site</a></p>

</body>
</html>
