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
    <button type="submit">Post</button>
  </form>

  <h3>Comments</h3>
  <?php
    require 'database.php';
    $comments = $pdo->query("SELECT comment, created_at FROM comments ORDER BY created_at DESC")->fetchAll();
    foreach ($comments as $row) {
      echo "<p>{$row['created_at']}: {$row['comment']}</p>";
    }
  ?>
</body>
</html>
