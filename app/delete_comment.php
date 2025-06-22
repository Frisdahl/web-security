<?php
session_start();

// Security headers
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");
header("Referrer-Policy: no-referrer");
header("Permissions-Policy: geolocation=(), microphone=()");
header("Content-Security-Policy: default-src 'self'; script-src 'self'");

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

require 'database.php';

$success = false;
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF Protection
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $message = "CSRF token mismatch. Request blocked for security.";
    } else {
        $id = $_POST['id'] ?? null;

        if ($id && is_numeric($id)) {
            try {
                $stmt = $pdo->prepare("DELETE FROM comments WHERE id = ?");
                $result = $stmt->execute([$id]);
                
                if ($stmt->rowCount() > 0) {
                    $success = true;
                    $message = "Comment deleted successfully!";
                } else {
                    $message = "Comment not found or already deleted.";
                }
            } catch (PDOException $e) {
                $message = "Error deleting comment: " . $e->getMessage();
            }
        } else {
            $message = "Invalid comment ID provided.";
        }
    }
} else {
    $message = "Invalid access method.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comment Deleted - Web Security Demo</title>
    <link rel="stylesheet" href="styles.css">
    <meta http-equiv="refresh" content="3;url=<?= $_SESSION['role'] === 'admin' ? 'admin.php' : 'index.php' ?>">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🗑️ Comment Management</h1>
            <p>Redirecting back...</p>
        </div>
        
        <div class="content">
            <div class="message <?= $success ? 'message-success' : 'message-error' ?>">
                <span class="security-icon <?= $success ? 'safe' : 'danger' ?>"></span>
                <?= htmlspecialchars($message) ?>
            </div>
            
            <div class="text-center">
                <?php if ($_SESSION['role'] === 'admin'): ?>
                <a href="admin.php" class="btn">Back to Admin Panel</a>
                <?php else: ?>
                <a href="index.php" class="btn">Back to Comments</a>
                <?php endif; ?>
            </div>
            
            <div class="text-center mt-2">
                <p>Redirecting in 3 seconds...</p>
                <div class="loading"></div>
            </div>
        </div>
        
        <div class="footer">
            <p>&copy; 2025 Web Security Demo - Educational Purpose Only</p>
        </div>
    </div>
</body>
</html>
