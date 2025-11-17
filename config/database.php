<?php
$host = "localhost";
$database   = "jst_industry";
$username = "jst_industry";
$password = "2N-9OZxJ8Y/PvM-b";

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$database;charset=utf8mb4",
        $username,
        $password,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (Exception $e) {
    die("Database Connection Failed");
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
