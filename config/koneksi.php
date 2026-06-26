<?php
$host = getenv('MYSQLHOST') ?: 'mysql.railway.internal';
$dbname = getenv('MYSQLDATABASE') ?: 'railway';
$user = getenv('MYSQLUSER') ?: 'root';
$pass = getenv('MYSQLPASSWORD') ?: 'dGxYaGBGEMmKOlfauVaPqsFdzneeDkNP';
$port = getenv('MYSQLPORT') ?: '3306';

try {
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
        PDO::MYSQL_ATTR_SSL_CA => false,
    ];
    $pdo = new PDO($dsn, $user, $pass, $options);
    $conn = $pdo;
} catch (PDOException $e) {
    die("DB Error: " . $e->getMessage());
}
?>
