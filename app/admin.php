<?php
session_start();


// security headers
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
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Panel - Web Security Demo</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body>
  <div class="container">
    <div class="header">
      <h1>⚙️ Admin Panel</h1>
      <p>Administrative Control Center</p>
    </div>
    
    <nav class="nav">
      <ul>
        <li><a href="index.php">Comments</a></li>
        <li><a href="profile.php">Profile</a></li>
        <li><a href="protected.php">Protected Area</a></li>
        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
        <li><a href="admin.php" class="active">Admin Panel</a></li>
        <?php endif; ?>
        <li><a href="login.php?logout=1">Logout</a></li>
      </ul>
    </nav>
    
    <div class="content">
      <div class="admin-panel">
        <h2>Welcome, <?= htmlspecialchars($_SESSION['user']) ?>!</h2>
        <p>You have administrator privileges.</p>
      </div>
      
      <div class="admin-stats">
        <div class="stat-card">
          <div class="stat-number"><?= count($comments) ?></div>
          <div class="stat-label">Total Comments</div>
        </div>
        <div class="stat-card">
          <div class="stat-number">1</div>
          <div class="stat-label">Active Admin</div>
        </div>
        <div class="stat-card">
          <div class="stat-number">100%</div>
          <div class="stat-label">System Health</div>
        </div>
      </div>

      <h3>Comment Management</h3>
      <div class="security-fixed">
        <span class="security-icon safe"></span>
        <strong>Security Note:</strong> Comments are properly escaped in admin view to prevent XSS attacks.
      </div>

      <div class="comments-list">
        <?php foreach ($comments as $row): ?>
        <div class="comment">
          <div class="comment-meta">
            <strong>ID:</strong> <?= $row['id'] ?> | 
            <strong>Posted:</strong> <?= htmlspecialchars($row['created_at']) ?>
          </div>
          <div class="comment-content">
            <?= htmlspecialchars($row['comment'], ENT_QUOTES, 'UTF-8') ?>
          </div>
          <div class="comment-actions">
            <form method="POST" action="delete_comment.php" style="display:inline;">
              <input type="hidden" name="id" value="<?= $row['id'] ?>">
              <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
              <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this comment?')">
                🗑️ Delete Comment
              </button>
            </form>
          </div>
        </div>
        <?php endforeach; ?>
        
        <?php if (empty($comments)): ?>
        <div class="message message-info">
          <span class="security-icon"></span>
          No comments found. Users can post comments from the main page.
        </div>
        <?php endif; ?>
      </div>
    </div>
    
    <div class="footer">
      <p>&copy; 2025 Web Security Demo - Educational Purpose Only</p>
    </div>
  </div>
</body>
</html>
