<?php
require 'database.php';

$comment = $_POST['comment'] ?? '';

$stmt = $pdo->prepare("INSERT INTO comments (comment) VALUES (:comment)");
$stmt->execute(['comment' => $comment]);

echo "Comment saved. <a href='index.php'>Go back</a>";
?>
