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

// CSRF Protection
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die("CSRF token mismatch. Request blocked.");
}

require 'database.php';

$comment = $_POST['comment'] ?? '';
$success = false;
$message = '';

if (!empty(trim($comment))) {
    try {
        // Add user_id when inserting comments
        $stmt = $pdo->prepare("INSERT INTO comments (comment, user_id) VALUES (?, ?)");
        $stmt->execute([$comment, $_SESSION['user_id']]);
        $success = true;
        $message = "Comment posted successfully!";
    } catch (PDOException $e) {
        $message = "Error posting comment: " . $e->getMessage();
    }
} else {
    $message = "Comment cannot be empty.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comment Posted - Web Security Demo</title>
    <link rel="stylesheet" href="styles.css">
    <meta http-equiv="refresh" content="3;url=index.php">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>💬 Comment Posted</h1>
            <p>Redirecting back to comments...</p>
        </div>
        
        <div class="content">
            <div class="message <?= $success ? 'message-success' : 'message-error' ?>">
                <span class="security-icon <?= $success ? 'safe' : 'danger' ?>"></span>
                <?= htmlspecialchars($message) ?>
            </div>
            
            <div class="text-center">
                <a href="index.php" class="btn">Go Back to Comments</a>
            </div>
            

        </div>
        
        <div class="footer">
            <p>&copy; 2025 Web Security Demo - Educational Purpose Only</p>
        </div>
    </div>
</body>
</html>
