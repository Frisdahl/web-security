<?php
session_start();
require 'database.php';


if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
  die("⚠️ CSRF token mismatch. Request blocked.");
}

$id = $_POST['id'] ?? null;

if ($id) {
  $stmt = $pdo->prepare("DELETE FROM comments WHERE id = ?");
  $stmt->execute([$id]);
  echo "Comment deleted. <a href='index.php'>Go back</a>";
} else {
  echo "Invalid request.";
}
