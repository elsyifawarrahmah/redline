<?php
include __DIR__ . '/../config/koneksi.php';
$stmt = $conn->query("SELECT COUNT(*) as total FROM speed_logs");
$row = $stmt->fetch(PDO::FETCH_ASSOC);
echo "Total records: " . $row['total'];
?>
