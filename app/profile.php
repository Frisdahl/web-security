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

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

// Generate CSRF token if not set
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

require 'database.php';

$message = '';
//$messageType = '';

// Get user_id from session, or fetch from database using username
$user_id = null;
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
} else {
    // Fallback: get user_id from username
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$_SESSION['user']]);
    $userResult = $stmt->fetch();
    if ($userResult) {
        $user_id = $userResult['id'];
        $_SESSION['user_id'] = $user_id; // Store for future use
    } else {
        die("User not found. Please log in again.");
    }
}

// Fetch current user data with fallback for missing columns
try {
    $stmt = $pdo->prepare("SELECT username, email, profile_image FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    
    // If user not found, redirect to login
    if (!$user) {
        header("Location: login.php");
        exit;
    }
    
    // Set defaults for missing data
    if (!isset($user['email']) || $user['email'] === null) {
        $user['email'] = $user['username'] . '@example.com';
    }
    if (!isset($user['profile_image'])) {
        $user['profile_image'] = null;
    }
    
} catch (PDOException $e) {
    // Handle case where email column doesn't exist yet
    try {
        $stmt = $pdo->prepare("SELECT username, profile_image FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();
        
        if (!$user) {
            header("Location: login.php");
            exit;
        }
        
        // Set default email
        $user['email'] = $user['username'] . '@example.com';
        
    } catch (PDOException $e2) {
        // If even this fails, try the most basic query
        $stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();
        
        if (!$user) {
            header("Location: login.php");
            exit;
        }
        
        // Set defaults
        $user['email'] = $user['username'] . '@example.com';
        $user['profile_image'] = null;
    }
}

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_image'])) {
    // CSRF Protection
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("CSRF token mismatch. Request blocked.");
    }

    $file = $_FILES['profile_image'];
    
    // Validate file upload
    if ($file['error'] === UPLOAD_ERR_OK) {
        // Security validations
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $maxFileSize = 5 * 1024 * 1024; // 5MB
        
        $fileType = $file['type'];
        $fileSize = $file['size'];
        $fileName = $file['name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        
        // Validate file type
        if (!in_array($fileType, $allowedTypes) || !in_array($fileExtension, $allowedExtensions)) {
            $message = "Invalid file type. Only JPG, PNG, GIF, and WEBP images are allowed.";
            $messageType = 'error';
        }
        // Validate file size
        elseif ($fileSize > $maxFileSize) {
            $message = "File too large. Maximum size is 5MB.";
            $messageType = 'error';
        }
        // Additional security: Check if file is actually an image
        elseif (!getimagesize($file['tmp_name'])) {
            $message = "File is not a valid image.";
            $messageType = 'error';
        }
        else {
            // Create uploads directory if it doesn't exist
            $uploadDir = 'uploads/profiles/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            // Generate unique filename to prevent conflicts and directory traversal
            $uniqueFileName = uniqid('profile_' . $user_id . '_', true) . '.' . $fileExtension;
            $uploadPath = $uploadDir . $uniqueFileName;
            
            // Move uploaded file
            if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
                // Delete old profile image if exists
                if ($user['profile_image'] && file_exists($user['profile_image'])) {
                    unlink($user['profile_image']);
                }
                
                // Update database - check if profile_image column exists
                try {
                    $stmt = $pdo->prepare("UPDATE users SET profile_image = ? WHERE id = ?");
                    $stmt->execute([$uploadPath, $user_id]);
                    
                    $message = "Profile image uploaded successfully!";
                    $messageType = 'success';
                    
                    // Update user array
                    $user['profile_image'] = $uploadPath;
                    
                } catch (PDOException $e) {
                    $message = "Database error: " . $e->getMessage();
                    $messageType = 'error';
                    // Clean up uploaded file on database error
                    if (file_exists($uploadPath)) {
                        unlink($uploadPath);
                    }
                }
            } else {
                $message = "Failed to upload file.";
                $messageType = 'error';
            }
        }
    } else {
        $message = "Upload error: " . $file['error'];
        $messageType = 'error';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Web Security Demo</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>👤 User Profile</h1>
            <p>Manage your profile and settings</p>
        </div>
        
        <nav class="nav">
            <ul>
                <li><a href="index.php">Comments</a></li>
                <li><a href="profile.php" class="active">Profile</a></li>
                <li><a href="protected.php">Protected Area</a></li>
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                <li><a href="admin.php">Admin Panel</a></li>
                <?php endif; ?>
                <li><a href="login.php?logout=1">Logout</a></li>
            </ul>
        </nav>
        
        <div class="content">
            <?php if ($message): ?>
            <div class="message message-<?= $messageType ?>">
                <span class="security-icon <?= $messageType === 'success' ? 'safe' : 'danger' ?>"></span>
                <?= htmlspecialchars($message) ?>
            </div>
            <?php endif; ?>
            
            <div class="profile-section">
                <h2>Profile Information</h2>
                
                <div class="profile-info">
                    <div class="profile-image-section">
                        <h3>Profile Picture</h3>
                        <div class="current-image">
                            <?php if (isset($user['profile_image']) && $user['profile_image'] && file_exists($user['profile_image'])): ?>
                                <img src="<?= htmlspecialchars($user['profile_image']) ?>" alt="Profile Picture" class="profile-image">
                            <?php else: ?>
                                <div class="no-image">
                                    <span>📷</span>
                                    <p>No profile image</p>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <form method="POST" enctype="multipart/form-data" class="upload-form">
                            <label for="profile_image">Upload New Profile Picture:</label>
                            <input type="file" name="profile_image" id="profile_image" accept="image/*" required>
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                            <button type="submit" class="btn btn-primary">Upload Profile Picture</button>
                        </form>
                        
                        <div class="upload-info">
                            <h4>Upload Requirements:</h4>
                            <ul>
                                <li>✅ Formats: JPG, PNG, GIF, WEBP</li>
                                <li>✅ Maximum size: 5MB</li>
                                <li>✅ Must be a valid image file</li>
                                <li>🔒 Files are validated server-side for security</li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="user-details">
                        <h3>Account Details</h3>
                        <div class="detail-item">
                            <label>Username:</label>
                            <span><?= htmlspecialchars($user['username']) ?></span>
                        </div>
                        <div class="detail-item">
                            <label>Email:</label>
                            <span><?= htmlspecialchars($user['email']) ?></span>
                        </div>
                        <div class="detail-item">
                            <label>Role:</label>
                            <span><?= htmlspecialchars($_SESSION['role'] ?? 'user') ?></span>
                        </div>
                        <div class="detail-item">
                            <label>User ID:</label>
                            <span><?= htmlspecialchars($user_id) ?></span>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        
        <div class="footer">
            <p>&copy; 2025 Web Security Demo - Educational Purpose Only</p>
        </div>
    </div>
</body>
</html>