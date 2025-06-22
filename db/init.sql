CREATE DATABASE IF NOT EXISTS security_demo;
USE security_demo;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    profile_image VARCHAR(255) DEFAULT NULL,
    role ENUM('user', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    comment TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


-- Insert test users
-- Password for all users is "password123" (hashed with PASSWORD_DEFAULT)
INSERT INTO users (username, password, role) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
('alice', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user'),
('bob', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user'),
('charlie', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user'),
('testuser', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user')
ON DUPLICATE KEY UPDATE username=username;

-- Insert test comments (including some with XSS attempts for testing)
INSERT INTO comments (comment) VALUES 
('Welcome to our secure comment system! This is a normal comment.'),
('This is a test comment to demonstrate basic functionality.'),
('Here is an XSS attempt: <script>alert("XSS Test!")</script>'),
('Another XSS test: <img src="x" onerror="alert(\'XSS via img tag\')">'),
('SQL injection attempt: \'; DROP TABLE users; --'),
('Normal comment: I really like this web security demo!'),
('<b>Bold text</b> and <i>italic text</i> - testing HTML'),
('<script>document.cookie="hacked=true"</script>'),
('Testing some special characters: & < > " \' / \\'),
('This comment contains a link: <a href="http://evil.com">Click me</a>'),
('CSS injection test: <style>body{background:red}</style>'),
('JavaScript in href: <a href="javascript:alert(\'XSS\')">Link</a>'),
('iframe test: <iframe src="http://evil.com"></iframe>'),
('Regular user feedback: The security features work well!'),
('Testing unicode: 你好 🔒 Security Demo 🛡️')
ON DUPLICATE KEY UPDATE comment=comment;