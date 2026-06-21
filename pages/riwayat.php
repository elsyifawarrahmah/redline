<?php
session_start();
include '../config/koneksi.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$user = $_SESSION['user'];

// Ambil data riwayat dari database
try {
    $stmt = $conn->query("SELECT * FROM speed_logs ORDER BY created_at DESC LIMIT 100");
    $history = $stmt->fetchAll();
} catch (PDOException $e) {
    $history = [];
}

// Hitung statistik
$totalKendaraan = count($history);
$pelanggaranCount = 0;
$kecepatanRata = 0;

foreach ($history as $h) {
    if (($h["status"] ?? "") === "violation") {
        $pelanggaranCount++;
    }
    $kecepatanRata += ($h['speed'] ?? 0);
}
$kecepatanRata = $totalKendaraan > 0 ? round($kecepatanRata / $totalKendaraan) : 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>REDLINE | Data Pelanggaran - ESP32-CAM Evidence</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700;14..32,800&display=swap" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;600;700;800&display=swap');
        
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
        .clock {
            font-family: 'Orbitron', monospace;
            font-size: 14px;
            background: rgba(0,0,0,0.4);
            padding: 8px 20px;
            border-radius: 30px;
            border: 1px solid rgba(220,38,38,0.2);
        }

        .stat-card {
            background: rgba(12,14,22,0.7);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 20px;
            border: 1px solid rgba(220,38,38,0.15);
            transition: all 0.3s;
        }
        .stat-card:hover { transform: translateY(-3px); border-color: rgba(220,38,38,0.4); }
        .stat-icon { width: 48px; height: 48px; background: rgba(220,38,38,0.12); border-radius: 14px; display: flex; align-items: center; justify-content: center; margin-bottom: 12px; }
        .stat-icon i { font-size: 22px; color: #dc2626; }
        .stat-value { font-size: 28px; font-weight: 800; color: white; }
        .stat-label { font-size: 12px; color: rgba(255,255,255,0.5); }

        .card-glass {
            background: rgba(12,14,22,0.7);
            backdrop-filter: blur(10px);
            border-radius: 24px;
            padding: 24px;
            border: 1px solid rgba(220,38,38,0.15);
        }
        .card-header {
            font-size: 15px;
            font-weight: 600;
            margin-bottom: 20px;
            padding-bottom: 14px;
            border-bottom: 1px solid rgba(220,38,38,0.15);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .card-header i { color: #dc2626; margin-right: 10px; }

        .form-control, .form-select {
            background: rgba(13,17,23,0.9);
            border: 1px solid rgba(220,38,38,0.25);
            color: #ffffff;
            border-radius: 12px;
            padding: 12px 16px;
            font-size: 14px;
        }
        .form-control:focus, .form-select:focus {
            border-color: #dc2626;
            box-shadow: 0 0 0 3px rgba(220,38,38,0.2);
        }
        .form-label {
            font-size: 12px;
            font-weight: 600;
            color: rgba(255,255,255,0.7);
            margin-bottom: 8px;
        }

        .history-table { width: 100%; }
        .history-table th {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            color: rgba(255,255,255,0.5);
            padding: 14px 12px;
            border-bottom: 1px solid rgba(220,38,38,0.2);
            text-align: left;
        }
        .history-table td {
            padding: 14px 12px;
            border-bottom: 1px solid rgba(255,255,255,0.05);
            font-size: 13px;
        }
        .history-table tr:hover {
            background: rgba(220,38,38,0.05);
        }
        .badge-status {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
        }
        .badge-status.safe { background: rgba(34,197,94,0.15); color: #22c55e; }
        .badge-status.danger { background: rgba(220,38,38,0.15); color: #dc2626; }

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
            .main-content { margin-left: 0; padding: 20px; }
        }
    </style>
</head>
<body>

<div class="cyber-bg"></div>
<div class="cyber-grid"></div>

<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <img src="../images/99+ REDLINE LOGO - ORI.png" alt="REDLINE" id="logoImg">
        <div class="logo-text">REDLINE</div>
    </div>
    <nav class="sidebar-nav">
        <div class="nav-section">MAIN MENU</div>
        <a href="dashboard.php" class="sidebar-link"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a>
        <a href="live-monitor.php" class="sidebar-link"><i class="fas fa-video"></i><span>Live Monitor</span></a>
        <a href="riwayat.php" class="sidebar-link active"><i class="fas fa-history"></i><span>Riwayat</span></a>
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
        <div class="page-title">📊 RIWAYAT KENDARAAN</div>
        <div class="clock" id="clock"></div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-car"></i></div>
                <div class="stat-value"><?php echo $totalKendaraan; ?></div>
                <div class="stat-label">Total Kendaraan</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-exclamation-triangle"></i></div>
                <div class="stat-value"><?php echo $pelanggaranCount; ?></div>
                <div class="stat-label">Pelanggaran</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-tachometer-alt"></i></div>
                <div class="stat-value"><?php echo $kecepatanRata; ?></div>
                <div class="stat-label">Rata-rata Kecepatan (km/h)</div>
            </div>
        </div>
    </div>

    <div class="card-glass">
        <div class="card-header">
            <span><i class="fas fa-list"></i> Data Riwayat</span>
            <div class="d-flex gap-2">
                <select class="form-select" style="width: auto; background: rgba(13,17,23,0.9); border-color: rgba(220,38,38,0.25); color: white;">
                    <option>Semua</option>
                    <option>Hari Ini</option>
                    <option>Minggu Ini</option>
                    <option>Bulan Ini</option>
                </select>
            </div>
        </div>
        
        <div style="max-height: 500px; overflow-y: auto;">
            <table class="history-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Waktu</th>
                        <th>Plat Nomor</th>
                        <th>Kecepatan</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($history) > 0): ?>
                        <?php $no = 1; foreach ($history as $h): ?>
                        <tr>
                            <td><?php echo $no++; ?></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($h['created_at'])); ?></td>
                            <td><strong><?php echo htmlspecialchars($h['plate_number'] ?? '-'); ?></strong></td>
                            <td class="<?php echo ($h['speed'] ?? 0) > 60 ? 'text-danger fw-bold' : ''; ?>"><?php echo htmlspecialchars($h['speed'] ?? 0); ?> km/h</td>
                            <td>
                                <?php if (($h["status"] ?? "") === "violation"): ?>
                                <span class="badge-status danger">MELANGGAR</span>
                                <?php else: ?>
                                <span class="badge-status safe">AMAN</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center py-5">
                                <i class="fas fa-car fa-2x mb-3" style="color: rgba(255,255,255,0.3);"></i>
                                <p class="text-white-50">Belum ada data riwayat kendaraan</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="footer">
        <i class="fas fa-microchip me-1"></i> <span>Kelompok 1</span> | 99+ REDLINE Smart Speed Monitor IoT | Teknik Komputer 2A
    </div>
</main>

<script>
    setInterval(() => {
        document.getElementById('clock').innerHTML = new Date().toLocaleTimeString('id-ID');
    }, 1000);
    
    const logoImg = document.getElementById('logoImg');
    if (logoImg) logoImg.onerror = function() { this.style.display = 'none'; };
</script>

</body>
</html>