<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Secure Comment App</title>
</head>
<body>
  <h2>Leave a Comment</h2>
<?php
session_start();
if (!isset($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<h2>Leave a Comment</h2>
<form action="post_comment.php" method="POST">
  <textarea name="comment" rows="4" cols="50" placeholder="Type your comment..."></textarea><br>
  <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
  <button type="submit">Post</button>
</form>

<h3>Comments</h3>
<?php
require 'database.php';

// Generate CSRF token if not set
if (!isset($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Fetch comments from DB
$comments = $pdo->query("SELECT id, comment, created_at FROM comments ORDER BY created_at DESC")->fetchAll();
?>

<h3>❌ Vulnerable to XSS (raw output)</h3>
<!-- Try submitting: <script>alert('XSS works')</script> -->
<?php foreach ($comments as $row): ?>
  <p><?= $row['created_at'] ?>: <?= $row['comment'] ?></p>
<?php endforeach; ?>

<hr>

<h3>✅ Protected against XSS (escaped output)</h3>
<?php foreach ($comments as $row): ?>
  <p><?= $row['created_at'] ?>: <?= htmlspecialchars($row['comment'], ENT_QUOTES, 'UTF-8') ?></p>
  <form method="POST" action="delete_comment.php" style="display:inline">
    <input type="hidden" name="id" value="<?= $row['id'] ?>">
    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
    <button type="submit">🗑 Delete</button>
  </form>
<?php endforeach; ?>








</body>
</html>
