<?php
header('Content-Type: application/json');

if ($_FILES['photo']['error'] === UPLOAD_ERR_OK) {
    $dir = __DIR__ . '/../images/captures/';
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    
    $filename = date('Y-m-d_H-i-s') . '.jpg';
    move_uploaded_file($_FILES['photo']['tmp_name'], $dir . $filename);
    
    include __DIR__ . '/../config/koneksi.php';
    $pdo->prepare("UPDATE speed_logs SET photo_url = ? ORDER BY id DESC LIMIT 1")
        ->execute(['/redline/images/captures/' . $filename]);
    
    echo json_encode(['success' => true, 'file' => $filename]);
} else {
    echo json_encode(['success' => false, 'error' => $_FILES['photo']['error']]);
}
?>
