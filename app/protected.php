<?php
session_start();

// Security headers
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");
header("Referrer-Policy: no-referrer");
header("Permissions-Policy: geolocation=(), microphone=()");
header("Content-Security-Policy: default-src 'self'; script-src 'self'");

// Session timeout check
$timeout = 600;
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY']) > $timeout) {
    session_unset();
    session_destroy();
    header("Location: login.php?timeout=1");
    exit;
}
$_SESSION['LAST_ACTIVITY'] = time();

if (!isset($_SESSION['user'])) {
  header("Location: login.php");
  exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Protected Area - Web Security Demo</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body>
  <div class="container">
    <div class="header">
      <h1>🔒 Protected Area</h1>
      <p>Authenticated Users Only</p>
    </div>
    
    <nav class="nav">
      <ul>
        <li><a href="index.php">Comments</a></li>
        <li><a href="protected.php">Protected Area</a></li>
        <?php if ($_SESSION['role'] === 'admin'): ?>
        <li><a href="admin.php">Admin Panel</a></li>
        <?php endif; ?>
        <li><a href="login.php?logout=1">Logout</a></li>
      </ul>
    </nav>
    
    <div class="content">
      <div class="message message-success">
        <span class="security-icon safe"></span>
        <strong>Access Granted!</strong> You have successfully accessed the protected area.
      </div>
      
      <h2>User Information</h2>
      <div class="admin-stats">
        <div class="stat-card">
          <div class="stat-label">Username</div>
          <div class="stat-number" style="font-size: 1.2rem;"><?= htmlspecialchars($_SESSION['user']) ?></div>
        </div>
        <div class="stat-card">
          <div class="stat-label">Role</div>
          <div class="stat-number" style="font-size: 1.2rem; color: <?= $_SESSION['role'] === 'admin' ? '#e74c3c' : '#27ae60' ?>">
            <?= ucfirst(htmlspecialchars($_SESSION['role'])) ?>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-label">Session Status</div>
          <div class="stat-number" style="font-size: 1.2rem; color: #27ae60">Active</div>
        </div>
      </div>
      
      <div class="security-demo">
        <h3><span class="security-icon safe"></span>Security Features Active</h3>
        <ul>
          <li>✅ Session-based authentication</li>
          <li>✅ Session timeout protection (10 minutes)</li>
          <li>✅ Role-based access control</li>
          <li>✅ CSRF token protection</li>
          <li>✅ Security headers implemented</li>
        </ul>
      </div>
      
      <?php if ($_SESSION['role'] === 'admin'): ?>
      <div class="message message-warning">
        <span class="security-icon warning"></span>
        <strong>Administrator Privileges:</strong> You have access to administrative functions.
        <a href="admin.php" class="btn" style="margin-left: 1rem;">Go to Admin Panel</a>
      </div>
      <?php else: ?>
      <div class="message message-info">
        <span class="security-icon"></span>
        <strong>Standard User:</strong> You have standard user privileges.
      </div>
      <?php endif; ?>
    </div>
    
    <div class="footer">
      <p>&copy; 2025 Web Security Demo - Educational Purpose Only</p>
    </div>
  </div>
</body>
</html>

