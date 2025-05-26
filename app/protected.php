<?php
session_start();
if (!isset($_SESSION['user'])) {
  die("Access denied. <a href='login.php'>Login</a>");
}

echo "Welcome, " . $_SESSION['user'] . "!<br>";
if ($_SESSION['role'] === 'admin') {
  echo "You are an admin.";
} else {
  echo "You are a user.";
}

echo "<br><a href='logout.php'>Logout</a>";

