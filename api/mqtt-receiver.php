<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200); exit();
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']); exit();
}

include __DIR__ . '/../config/koneksi.php';

$raw  = file_get_contents('php://input');
$data = json_decode($raw, true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Invalid JSON']); exit();
}

$speed  = floatval($data['speed']  ?? 0);
$status = trim($data['status']     ?? 'normal');
$device = trim($data['device']     ?? 'REDLINE_01');
$plate  = trim($data['plate']      ?? '') ?: null;

// Normalisasi status
$status = ($status === 'violation') ? 'violation' : 'normal';

try {
    $stmt = $conn->prepare("
        INSERT INTO speed_logs (device, speed, status, plate, created_at)
        VALUES (:device, :speed, :status, :plate, NOW())
    ");
    $stmt->execute([
        ':device' => $device,
        ':speed'  => $speed,
        ':status' => $status,
        ':plate'  => $plate,
    ]);

    echo json_encode([
        'success' => true,
        'id'      => $conn->lastInsertId(),
        'speed'   => $speed,
        'status'  => $status,
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
