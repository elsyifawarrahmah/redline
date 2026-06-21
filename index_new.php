<?php
session_start();
include 'config/koneksi.php';

$page = $_GET['page'] ?? 'home';
$action = $_GET['action'] ?? '';

if ($action === 'logout') { session_destroy(); header("Location: index.php"); exit(); }

$loginError = ""; $registerError = ""; $registerSuccess = false;

if ($_SERVER["REQUEST_METHOD"] == "POST" && $page === 'login') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    if (empty($username) || empty($password)) {
        $loginError = "Username dan password wajib diisi!";
    } else {
        try {
            $stmt = $conn->prepare("SELECT * FROM users WHERE username = :username LIMIT 1");
            $stmt->execute(['username' => $username]);
            $userData = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($userData && password_verify($password, $userData['password'])) {
                $_SESSION['user'] = ['id' => $userData['id'], 'username' => $userData['username'], 'role' => $userData['role'] ?? 'user'];
                header("Location: index.php");
                exit();
            } else {
                $loginError = "Username atau password salah!";
            }
        } catch (PDOException $e) {
            $loginError = "Koneksi database gagal.";
        }
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && $page === 'register') {
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $password = password_hash($_POST['password'] ?? '', PASSWORD_DEFAULT);
    $role = $_POST['role'] ?? 'viewer';
    $telegram = $_POST['telegram'] ?? '';
    try {
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = :username LIMIT 1");
        $stmt->execute(['username' => $username]);
        if ($stmt->fetch()) {
            $registerError = "Username sudah digunakan!";
        } else {
            $stmt = $conn->prepare("INSERT INTO users (username, email, phone, password, role, telegram) VALUES (:username, :email, :phone, :password, :role, :telegram)");
            if ($stmt->execute(['username' => $username, 'email' => $email, 'phone' => $phone, 'password' => $password, 'role' => $role, 'telegram' => $telegram])) {
                $registerSuccess = true;
            } else {
                $registerError = "Gagal register!";
            }
        }
    } catch (PDOException $e) {
        $registerError = "Database error";
    }
}

$user = $_SESSION['user'] ?? null;
$totalKendaraan = 0; $totalPelanggaran = 0; $kendaraanHariIni = 0; $pelanggaranHariIni = 0; $riwayatTerbaru = [];
if ($user) {
    try {
        $stmt = $conn->query("SELECT COUNT(*) FROM speed_logs");
        $totalKendaraan = $stmt->fetchColumn();
        $stmt = $conn->query("SELECT COUNT(*) FROM speed_logs WHERE status = 'violation'");
        $totalPelanggaran = $stmt->fetchColumn();
        $stmt = $conn->query("SELECT COUNT(*) FROM speed_logs WHERE DATE(created_at) = CURDATE()");
        $kendaraanHariIni = $stmt->fetchColumn();
        $stmt = $conn->query("SELECT COUNT(*) FROM speed_logs WHERE status = 'violation' AND DATE(created_at) = CURDATE()");
        $pelanggaranHariIni = $stmt->fetchColumn();
        $stmt = $conn->query("SELECT * FROM speed_logs ORDER BY created_at DESC LIMIT 10");
        $riwayatTerbaru = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {}
}

if ($user && $page !== 'login' && $page !== 'register') {
    // DASHBOARD
    include 'pages/dashboard.php';
} elseif ($page === 'login' && !$user) {
    // LOGIN PAGE
    include 'pages/login.php';
} elseif ($page === 'register' && !$user) {
    // REGISTER PAGE
    include 'pages/register.php';
} else {
    // HOME / LANDING
    include 'pages/index_landing.php';
}
?>
