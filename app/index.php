<?php
session_start();
require 'database.php';

// Generate CSRF token if not set
if (!isset($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die("⚠️ CSRF token mismatch. Request blocked.");
  }

  // Your POST logic here
  $id = $_POST['id'] ?? null;

  if ($id) {
    $stmt = $pdo->prepare("DELETE FROM comments WHERE id = ?");
    $stmt->execute([$id]);
    echo "Comment deleted. <a href='index.php'>Go back</a>";
  } else {
    echo "Invalid request.";
  }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Secure Comment App</title>
</head>
<body>
  
  <h2>Leave a Comment</h2>
  <form action="post_comment.php" method="POST">
    <textarea name="comment" rows="4" cols="50" placeholder="Type your comment..."></textarea><br>
    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
    <button type="submit">Post</button>
  </form>

  <h3>Comments</h3>
<?php
require 'database.php';


// Fetch comments from DB
$comments = $pdo->query("SELECT id, comment, created_at FROM comments ORDER BY created_at DESC")->fetchAll();
?>

<h3>❌ Vulnerable to XSS (raw output)</h3>
<!-- Try submitting: <script>alert('XSS works')</script> -->
<?php foreach ($comments as $row): ?>
  <p><?= $row['created_at'] ?>: <?= $row['comment'] ?></p>
<?php endforeach; ?>

</body>
</html>
