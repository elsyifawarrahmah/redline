<?php
$url = getenv('MYSQL_URL') ?: 'mysql://root:Redline123!@hayabusa.proxy.rlwy.net:35462/railway';

$parsed = parse_url($url);
$host = $parsed['host'];
$port = $parsed['port'] ?? 3306;
$dbname = ltrim($parsed['path'], '/');
$user = $parsed['user'];
$pass = $parsed['pass'];

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
