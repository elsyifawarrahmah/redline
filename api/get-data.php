<?php

header('Content-Type: application/json');

include 'config/koneksi.php';

$stmt = $conn->query("
    SELECT *
    FROM speed_logs
    ORDER BY id DESC
    LIMIT 1
");

$data = $stmt->fetch(PDO::FETCH_ASSOC);

echo json_encode($data);
