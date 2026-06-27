<?php
$host = getenv('MYSQLHOST') ?: 'mysql.railway.internal';
$dbname = getenv('MYSQLDATABASE') ?: 'railway';
$user = 'redline';
$pass = 'Redline123!';
$port = getenv('MYSQLPORT') ?: '3306';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    $conn = $pdo;
} catch (PDOException $e) {
    die("DB Error: " . $e->getMessage());
}
?>
