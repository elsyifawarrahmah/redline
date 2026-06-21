<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Step 1: Starting...<br>";
include __DIR__ . '/../config/koneksi.php';
echo "Step 2: Koneksi included<br>";

if ($conn) {
    echo "Step 3: Connection OK<br>";
    $stmt = $conn->query("SELECT COUNT(*) as total FROM speed_logs");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Total records: " . $row['total'];
} else {
    echo "Connection failed";
}
?>
