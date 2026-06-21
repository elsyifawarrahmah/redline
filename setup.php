<?php
// Setup database dan user untuk REDLINE
// Jalankan ini sekali untuk setup database

try {
    // Connect sebagai root ke MySQL di server Debian.
    $dbHost = "localhost"; // Gunakan localhost jika PHP dan MySQL di server yang sama.
    $rootPassword = ""; // Jika root MySQL punya password, ganti di sini.
    $rootConn = new PDO("mysql:host=$dbHost", "root", $rootPassword);

    // Buat database jika belum ada
    $rootConn->exec("CREATE DATABASE IF NOT EXISTS redline_db");

    // Buat user redline_user untuk koneksi aplikasi (localhost + remote)
    $rootConn->exec("CREATE USER IF NOT EXISTS 'redline_user'@'localhost' IDENTIFIED BY 'password123'");
    $rootConn->exec("CREATE USER IF NOT EXISTS 'redline_user'@'%' IDENTIFIED BY 'password123'");

    // Beri hak akses ke database
    $rootConn->exec("GRANT ALL PRIVILEGES ON redline_db.* TO 'redline_user'@'localhost'");
    $rootConn->exec("GRANT ALL PRIVILEGES ON redline_db.* TO 'redline_user'@'%'");

    // Flush privileges
    $rootConn->exec("FLUSH PRIVILEGES");

    echo "Database dan user berhasil dibuat!<br>";

    // Connect ke database redline_db
    $conn = new PDO("mysql:host=$dbHost;dbname=redline_db;charset=utf8mb4", "redline_user", "password123");

    // Buat tabel users jika belum ada
    $conn->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        phone VARCHAR(20),
        password VARCHAR(255) NOT NULL,
        role ENUM('admin', 'operator', 'viewer') DEFAULT 'viewer',
        telegram VARCHAR(100),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // Buat tabel speed_logs jika belum ada
    $conn->exec("CREATE TABLE IF NOT EXISTS speed_logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        speed INT NOT NULL,
        status ENUM('safe', 'violation') DEFAULT 'safe',
        plate VARCHAR(20),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // Buat tabel mqtt_config jika belum ada
    $conn->exec("CREATE TABLE IF NOT EXISTS mqtt_config (
        id INT AUTO_INCREMENT PRIMARY KEY,
        broker VARCHAR(255) NOT NULL DEFAULT 'broker.emqx.io',
        port INT NOT NULL DEFAULT 1883,
        topic VARCHAR(255) NOT NULL DEFAULT 'redline/speed',
        username VARCHAR(100),
        password VARCHAR(100),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // Insert konfigurasi MQTT default jika belum ada
    $stmt = $conn->prepare("SELECT id FROM mqtt_config LIMIT 1");
    $stmt->execute();
    if (!$stmt->fetch()) {
        $conn->exec("INSERT INTO mqtt_config (broker, port, topic) VALUES ('broker.emqx.io', 1883, 'redline/speed')");
        echo "Konfigurasi MQTT default dibuat!<br>";
    }
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = 'admin' LIMIT 1");
    $stmt->execute();
    if (!$stmt->fetch()) {
        $hashedPassword = password_hash('admin123', PASSWORD_DEFAULT);
        $conn->exec("INSERT INTO users (username, email, phone, password, role) VALUES ('admin', 'admin@redline.com', '08123456789', '$hashedPassword', 'admin')");
        echo "User admin default dibuat: username=admin, password=admin123<br>";
    }

    echo "Setup selesai! Sekarang Anda bisa login dengan admin/admin123";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>