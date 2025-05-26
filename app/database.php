<?php
$pdo = new PDO("mysql:host=db;dbname=security_demo", "root", "root");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
?>
