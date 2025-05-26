<?php
session_start();
require 'database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // ✅ Avoid warning by checking existence first
  if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    echo "⚠️ CSRF token mismatch. Request blocked.";
    exit; // stop the script here
  }

  $id = $_POST['id'] ?? null;

  if ($id) {
    $stmt = $pdo->prepare("DELETE FROM comments WHERE id = ?");
    $stmt->execute([$id]);
    echo "Comment deleted. <a href='index.php'>Go back</a>";
  } else {
    echo "Invalid request.";
  }
} else {
  echo "Invalid access.";
}
