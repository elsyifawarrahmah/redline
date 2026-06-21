<?php
$host = 'localhost';
$dbname = 'redline_db';
$user = 'root';
$pass = '30';
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    $conn = $pdo;
} catch (PDOException $e) {
    die("DB Error: " . $e->getMessage());
}
?>
