<?php
// Jalankan ini di browser Debian: http://192.168.6.10/update.php

$configPath = __DIR__ . '/config/koneksi.php';
$newConfig = "<?php
\$host = '192.168.6.10';
\$db   = 'redline_db';
\$user = 'root';
\$pass = '';

try {
    \$conn = new PDO(
        \"mysql:host={\$host};dbname={\$db};charset=utf8mb4\",
        \$user,
        \$pass,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException \$e) {
    die(\"DB Error: {\$user}@{\$host}: \" . \$e->getMessage());
}
?>";

if (file_put_contents($configPath, $newConfig) !== false) {
    echo "✅ Berhasil update koneksi.php!<br>";
    echo "🔄 Refresh http://192.168.6.10/pages/login.php<br>";
    echo "🔑 Login: admin / admin123<br>";
    echo "<br><a href='pages/login.php'>Klik login</a>";
} else {
    echo "❌ Gagal update. Path: $configPath";
}
?>