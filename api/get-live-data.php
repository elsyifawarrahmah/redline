<?php
header('Content-Type: application/json');
include __DIR__ . '/../config/koneksi.php';
try {
$latest = $conn->query("SELECT *, TIME(created_at) AS time_only FROM speed_logs ORDER BY created_at DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
$stats = $conn->query("SELECT MAX(speed) AS max_speed_today, AVG(speed) AS avg_speed_today, COUNT(*) AS total_today FROM speed_logs WHERE DATE(created_at) = CURDATE()")->fetch(PDO::FETCH_ASSOC);
$history = $conn->query("SELECT *, TIME(created_at) AS time_only FROM speed_logs ORDER BY created_at DESC LIMIT 15")->fetchAll(PDO::FETCH_ASSOC);
echo json_encode(["success"=>true,"latest"=>$latest,"stats"=>["max_speed_today"=>round($stats["max_speed_today"]??0,1),"avg_speed_today"=>round($stats["avg_speed_today"]??0,1),"total_today"=>intval($stats["total_today"]??0)],"history"=>$history]);
} catch (PDOException $e) {
echo json_encode(["success"=>false,"message"=>$e->getMessage()]);
}
?>
