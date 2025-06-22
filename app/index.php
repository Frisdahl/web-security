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

// ✅ Redirect to register page if no one is logged in
if (!isset($_SESSION['user'])) {
  header("Location: login.php");
  exit;
}

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
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Secure Comment App</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body>
  <div class="container">
    <div class="header">
      <h1>💬 Comment System</h1>
      <p>Secure commenting with XSS demonstration</p>
    </div>
    
    <nav class="nav">
      <ul>
          <li><a href="index.php" class="active">Comments</a></li>
          <li><a href="profile.php">Profile</a></li>
          <li><a href="protected.php">Protected Area</a></li>
          <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
          <li><a href="admin.php">Admin Panel</a></li>
          <?php endif; ?>
          <li><a href="login.php?logout=1">Logout</a></li>
      </ul>
    </nav>
    
    <div class="content">
      <h2>Leave a Comment</h2>      <form action="post_comment.php" method="POST">
        <label for="comment">Your Comment:</label>
        <textarea name="comment" id="comment" rows="4" placeholder="Type your comment here..."></textarea>
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
        <button type="submit">Post Comment</button>
      </form>

      <div class="comments-section">
        <h3>Recent Comments</h3>
        <?php
        require 'database.php';
        
        // Fetch comments from DB
        $comments = $pdo->query("SELECT id, comment, created_at FROM comments ORDER BY created_at DESC")->fetchAll();
        ?>

        <div class="vulnerability-warning">
          <h3><span class="security-icon danger"></span>Vulnerable to XSS (raw output)</h3>
          <p>Try submitting: <code>&lt;script&gt;alert('XSS works')&lt;/script&gt;</code></p>
        </div>
        
        <div class="comments-list">
          <?php foreach ($comments as $row): ?>
          <div class="comment">
            <div class="comment-meta">
              Posted on: <?= htmlspecialchars($row['created_at']) ?>
            </div>
            <div class="comment-content">
              <?= $row['comment'] ?>
            </div>
            <div class="comment-actions">
              <form method="POST" style="display: inline;">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                <input type="hidden" name="id" value="<?= $row['id'] ?>">
                <button type="submit" class="btn btn-danger" onclick="return confirm('Delete this comment?')">Delete</button>
              </form>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
    
    <div class="footer">
      <p>&copy; 2025 Web Security Demo - Educational Purpose Only</p>
    </div>
  </div>
</body>
</html>
