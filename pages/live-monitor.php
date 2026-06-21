<?php
session_start();
include '../config/koneksi.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$user = $_SESSION['user'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>REDLINE | Live Monitor - ESP32-CAM Integration</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700;14..32,800&display=swap" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;600;700;800;900&display=swap');
        
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: #0a0a0f;
            color: #ffffff;
            overflow-x: hidden;
        }

        .cyber-bg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -3;
            background: radial-gradient(ellipse at 30% 40%, rgba(220,38,38,0.15) 0%, transparent 60%),
                        linear-gradient(135deg, #0a0a0f 0%, #0d111a 100%);
        }
        .cyber-grid {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -2;
            background-image: linear-gradient(rgba(220,38,38,0.06) 1px, transparent 1px),
                              linear-gradient(90deg, rgba(220,38,38,0.06) 1px, transparent 1px);
            background-size: 50px 50px;
            animation: gridMove 20s linear infinite;
        }
        @keyframes gridMove {
            0% { transform: translate(0, 0); }
            100% { transform: translate(50px, 50px); }
        }
        .neon-orb {
            position: fixed;
            border-radius: 50%;
            filter: blur(80px);
            z-index: -1;
            animation: orbFloat 15s infinite ease-in-out;
        }
        .orb-1 { width: 400px; height: 400px; background: radial-gradient(circle, rgba(220,38,38,0.2), transparent); top: -150px; left: -150px; }
        .orb-2 { width: 500px; height: 500px; background: radial-gradient(circle, rgba(220,38,38,0.15), transparent); bottom: -200px; right: -200px; animation-delay: -5s; }
        @keyframes orbFloat {
            0%,100% { transform: translate(0, 0) scale(1); opacity: 0.4; }
            50% { transform: translate(30px, -30px) scale(1.1); opacity: 0.7; }
        }

        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            bottom: 0;
            width: 280px;
            background: rgba(8,10,18,0.95);
            backdrop-filter: blur(20px);
            border-right: 1px solid rgba(220,38,38,0.3);
            z-index: 100;
            transition: all 0.3s;
            overflow-y: auto;
        }
        .sidebar::-webkit-scrollbar { width: 4px; }
        .sidebar::-webkit-scrollbar-track { background: rgba(255,255,255,0.03); }
        .sidebar::-webkit-scrollbar-thumb { background: #dc2626; border-radius: 4px; }
        .sidebar-header {
            padding: 28px 24px;
            border-bottom: 1px solid rgba(220,38,38,0.2);
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .sidebar-header img { height: 45px; width: auto; filter: drop-shadow(0 0 12px rgba(220,38,38,0.5)); }
        .sidebar-header .logo-text {
            font-family: 'Orbitron', monospace;
            font-size: 22px;
            font-weight: 800;
            background: linear-gradient(135deg, #ffffff, #dc2626);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }
        .sidebar-nav { padding: 24px 16px; }
        .nav-section {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: rgba(255,255,255,0.35);
            margin: 20px 0 12px 12px;
        }
        .sidebar-link {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 12px 16px;
            margin: 6px 0;
            border-radius: 14px;
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s;
        }
        .sidebar-link:hover, .sidebar-link.active {
            background: rgba(220,38,38,0.12);
            color: #dc2626;
            transform: translateX(5px);
        }
        .sidebar-link i { width: 24px; font-size: 18px; }
        .live-badge {
            background: linear-gradient(135deg, #dc2626, #ef4444);
            color: white;
            font-size: 9px;
            padding: 2px 10px;
            border-radius: 20px;
            margin-left: auto;
            animation: badgePulse 1s infinite;
        }
        @keyframes badgePulse {
            0%,100% { opacity: 0.8; }
            50% { opacity: 1; transform: scale(1.05); }
        }
        .sidebar-user {
            margin: 24px 16px;
            padding: 16px;
            background: rgba(255,255,255,0.03);
            border-radius: 18px;
            display: flex;
            align-items: center;
            gap: 12px;
            border: 1px solid rgba(220,38,38,0.15);
        }
        .user-avatar {
            width: 46px;
            height: 46px;
            background: linear-gradient(135deg, #dc2626, #7f1d1d);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
        }
        .user-info h6 { font-size: 14px; font-weight: 600; margin: 0; }
        .user-info p { font-size: 10px; color: rgba(255,255,255,0.5); margin: 0; }
        .logout-btn { color: rgba(255,255,255,0.35); transition: 0.3s; }
        .logout-btn:hover { color: #dc2626; }

        .main-content { margin-left: 280px; padding: 28px 32px; min-height: 100vh; }

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 28px;
            padding-bottom: 16px;
            border-bottom: 1px solid rgba(220,38,38,0.2);
        }
        .page-title {
            font-family: 'Orbitron', monospace;
            font-size: 26px;
            font-weight: 800;
            background: linear-gradient(135deg, #ffffff, #dc2626);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }
        .conn-status {
            display: flex;
            align-items: center;
            gap: 10px;
            background: rgba(0,0,0,0.4);
            padding: 8px 24px;
            border-radius: 40px;
            border: 1px solid rgba(220,38,38,0.3);
        }
        .conn-dot {
            width: 10px;
            height: 10px;
            background: #22c55e;
            border-radius: 50%;
            animation: pulseDot 1.5s infinite;
        }
        @keyframes pulseDot {
            0%,100% { opacity: 0.5; transform: scale(1); }
            50% { opacity: 1; transform: scale(1.2); }
        }

        .camera-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 24px;
        }
        .camera-card {
            background: rgba(12,14,22,0.8);
            backdrop-filter: blur(12px);
            border-radius: 24px;
            padding: 20px;
            border: 1px solid rgba(220,38,38,0.2);
        }
        .camera-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
        }
        .camera-title {
            font-family: 'Orbitron', monospace;
            font-size: 14px;
            font-weight: 600;
            color: #dc2626;
        }
        .camera-status {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 12px;
            color: #22c55e;
        }
        .camera-frame {
            width: 100%;
            aspect-ratio: 16/9;
            background: #000;
            border-radius: 16px;
            overflow: hidden;
            position: relative;
        }
        .camera-frame img, .camera-frame video {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .camera-placeholder {
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #0a0a0f 0%, #1a1a2e 100%);
            color: rgba(255,255,255,0.5);
        }
        .camera-placeholder i { font-size: 48px; margin-bottom: 12px; }
        .camera-info {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
            margin-top: 16px;
        }
        .info-item {
            text-align: center;
            padding: 12px;
            background: rgba(255,255,255,0.03);
            border-radius: 12px;
        }
        .info-item .value {
            font-family: 'Orbitron', monospace;
            font-size: 18px;
            font-weight: 700;
            color: #dc2626;
        }
        .info-item .label {
            font-size: 10px;
            color: rgba(255,255,255,0.5);
            margin-top: 4px;
        }

        .footer {
            text-align: center;
            padding: 24px;
            margin-top: 32px;
            font-size: 12px;
            color: rgba(255,255,255,0.35);
            border-top: 1px solid rgba(220,38,38,0.15);
        }
        .footer span { color: #dc2626; font-weight: 600; }

        @media (max-width: 992px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.mobile-open { transform: translateX(0); }
            .main-content { margin-left: 0; padding: 20px; }
            .camera-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<div class="cyber-bg"></div>
<div class="cyber-grid"></div>
<div class="neon-orb orb-1"></div>
<div class="neon-orb orb-2"></div>

<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <img src="../images/99+ REDLINE LOGO - ORI.png" alt="REDLINE" id="logoImg">
        <div class="logo-text">REDLINE</div>
    </div>
    <nav class="sidebar-nav">
        <div class="nav-section">MAIN MENU</div>
        <a href="dashboard.php" class="sidebar-link"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a>
        <a href="live-monitor.php" class="sidebar-link active"><i class="fas fa-video"></i><span>Live Monitor</span><span class="live-badge">LIVE</span></a>
        <a href="riwayat.php" class="sidebar-link"><i class="fas fa-history"></i><span>Riwayat</span></a>
        <a href="pelanggaran.php" class="sidebar-link"><i class="fas fa-gavel"></i><span>Pelanggaran</span></a>
        <div class="nav-section">MANAGEMENT</div>
        <a href="pengaturan.php" class="sidebar-link"><i class="fas fa-sliders-h"></i><span>Batas Kecepatan</span></a>
        <a href="pengguna.php" class="sidebar-link"><i class="fas fa-users"></i><span>Pengguna</span></a>
        <a href="profile.php" class="sidebar-link"><i class="fas fa-user-cog"></i><span>Profil Saya</span></a>
        <div class="nav-section">REPORTS</div>
        <a href="export.php" class="sidebar-link"><i class="fas fa-file-export"></i><span>Export Data</span></a>
    </nav>
    <div class="sidebar-user">
        <div class="user-avatar"><i class="fas fa-user"></i></div>
        <div class="user-info">
            <h6><?php echo htmlspecialchars($user['username']); ?></h6>
            <p><?php echo htmlspecialchars(ucfirst($user['role'] ?? 'User')); ?></p>
        </div>
        <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i></a>
    </div>
</aside>

<main class="main-content" id="mainContent">
    <div class="topbar">
        <div class="page-title">📹 LIVE MONITOR</div>
        <div class="conn-status">
            <div class="conn-dot"></div>
            <span>Terhubung ke ESP32-CAM</span>
        </div>
    </div>

    <?php
    // Ambil data kamera dari database
    try {
        $stmt = $conn->query("SELECT * FROM cameras ORDER BY id ASC");
        $cameras = $stmt->fetchAll();
    } catch (PDOException $e) {
        $cameras = [];
    }
    
    // Jika tidak ada kamera, gunakan data dummy
    if (empty($cameras)) {
        $cameras = [
            ['id' => 1, 'name' => 'Kamera 1 - Masuk', 'location' => 'Gerbang Utama', 'status' => 'active'],
            ['id' => 2, 'name' => 'Kamera 2 - Keluar', 'location' => 'Gerbang Belakang', 'status' => 'active']
        ];
    }
    ?>

    <div class="camera-grid">
        <?php foreach ($cameras as $cam): ?>
        <div class="camera-card">
            <div class="camera-header">
                <div class="camera-title"><?php echo htmlspecialchars($cam['name'] ?? 'Kamera ' . $cam['id']); ?></div>
                <div class="camera-status">
                    <div class="conn-dot"></div>
                    <?php echo ($cam['status'] ?? 'active') === 'active' ? 'Online' : 'Offline'; ?>
                </div>
            </div>
            <div class="camera-frame">
                <div class="camera-placeholder">
                    <i class="fas fa-video"></i>
                    <span>Stream Tidak Tersedia</span>
                    <small><?php echo htmlspecialchars($cam['location'] ?? 'Lokasi: -'); ?></small>
                </div>
            </div>
            <div class="camera-info">
                <div class="info-item">
                    <div class="value">-</div>
                    <div class="label">FPS</div>
                </div>
                <div class="info-item">
                    <div class="value">-</div>
                    <div class="label">Resolusi</div>
                </div>
                <div class="info-item">
                    <div class="value">-</div>
                    <div class="label">Koneksi</div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="footer">
        <i class="fas fa-microchip me-1"></i> <span>Kelompok 1</span> | 99+ REDLINE Smart Speed Monitor IoT | Teknik Komputer 2A
    </div>
</main>

<script>
    const logoImg = document.getElementById('logoImg');
    if (logoImg) logoImg.onerror = function() { this.style.display = 'none'; };
</script>

</body>
</html>