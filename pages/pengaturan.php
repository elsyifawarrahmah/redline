<?php
session_start();
include '../config/koneksi.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$user = $_SESSION['user'];

// Ambil pengaturan batas kecepatan dari database
try {
    $stmt = $conn->query("SELECT * FROM settings WHERE setting_key = 'speed_limit' LIMIT 1");
    $speedSetting = $stmt->fetch(PDO::FETCH_ASSOC);
    $currentSpeedLimit = $speedSetting['setting_value'] ?? 60;
} catch (PDOException $e) {
    $currentSpeedLimit = 60;
}

// Ambil semua pengaturan
try {
    $stmt = $conn->query("SELECT * FROM settings");
    $allSettings = $stmt->fetchAll();
} catch (PDOException $e) {
    $allSettings = [];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan - REDLINE</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700;14..32,800&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: #0d1117;
            color: #e6edf3;
        }

        .bg-glow {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -2;
            background: radial-gradient(ellipse at 30% 40%, rgba(220,38,38,0.15) 0%, transparent 60%),
                        linear-gradient(135deg, #0d1117 0%, #161b22 100%);
        }

        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            bottom: 0;
            width: 280px;
            background: rgba(13,17,23,0.95);
            backdrop-filter: blur(16px);
            border-right: 1px solid rgba(220,38,38,0.3);
            z-index: 100;
            transition: all 0.3s;
            overflow-y: auto;
        }
        .sidebar::-webkit-scrollbar { width: 4px; }
        .sidebar::-webkit-scrollbar-track { background: rgba(255,255,255,0.05); }
        .sidebar::-webkit-scrollbar-thumb { background: #dc2626; border-radius: 4px; }
        .sidebar-header {
            padding: 28px 24px;
            border-bottom: 1px solid rgba(220,38,38,0.25);
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .sidebar-header img { height: 45px; width: auto; filter: drop-shadow(0 0 12px rgba(220,38,38,0.4)); }
        .sidebar-header .logo-text {
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
            letter-spacing: 1.5px;
            color: rgba(255,255,255,0.4);
            margin: 20px 0 12px 12px;
        }
        .sidebar-link {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 12px 16px;
            margin: 6px 0;
            border-radius: 12px;
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s;
        }
        .sidebar-link:hover, .sidebar-link.active {
            background: rgba(220,38,38,0.15);
            color: #dc2626;
            transform: translateX(5px);
        }
        .sidebar-link i { width: 24px; font-size: 18px; }
        .sidebar-user {
            margin: 24px 16px;
            padding: 16px;
            background: rgba(255,255,255,0.05);
            border-radius: 16px;
            display: flex;
            align-items: center;
            gap: 12px;
            border: 1px solid rgba(220,38,38,0.15);
        }
        .user-avatar {
            width: 46px;
            height: 46px;
            background: linear-gradient(135deg, #dc2626, #991b1b);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
        }
        .user-info h6 { font-size: 14px; font-weight: 600; margin: 0; }
        .user-info p { font-size: 10px; color: rgba(255,255,255,0.5); margin: 0; }
        .logout-btn { color: rgba(255,255,255,0.4); transition: 0.3s; }
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
            font-size: 26px;
            font-weight: 700;
            background: linear-gradient(135deg, #ffffff, #dc2626);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }
        .clock {
            font-family: monospace;
            font-size: 14px;
            background: rgba(0,0,0,0.4);
            padding: 8px 20px;
            border-radius: 30px;
            border: 1px solid rgba(220,38,38,0.2);
        }

        .card-glass {
            background: rgba(22,27,34,0.8);
            backdrop-filter: blur(12px);
            border-radius: 24px;
            padding: 24px;
            border: 1px solid rgba(220,38,38,0.2);
            height: 100%;
        }
        .card-header {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 20px;
            padding-bottom: 14px;
            border-bottom: 1px solid rgba(220,38,38,0.15);
        }
        .card-header i { color: #dc2626; margin-right: 10px; }

        input[type="range"] {
            width: 100%;
            height: 6px;
            -webkit-appearance: none;
            background: rgba(255,255,255,0.1);
            border-radius: 3px;
            outline: none;
        }
        input[type="range"]::-webkit-slider-thumb {
            -webkit-appearance: none;
            width: 20px;
            height: 20px;
            background: #dc2626;
            border-radius: 50%;
            cursor: pointer;
            box-shadow: 0 0 10px rgba(220,38,38,0.5);
        }
        .speed-value {
            font-size: 72px;
            font-weight: 800;
            color: #dc2626;
            text-shadow: 0 0 15px rgba(220,38,38,0.3);
        }
        .preset-btn {
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(220,38,38,0.2);
            border-radius: 16px;
            padding: 16px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }
        .preset-btn:hover, .preset-btn.active {
            background: rgba(220,38,38,0.15);
            border-color: #dc2626;
            transform: translateY(-3px);
        }
        .btn-save {
            background: linear-gradient(135deg, #dc2626, #b91c1c);
            color: white;
            border: none;
            padding: 14px 28px;
            border-radius: 14px;
            font-weight: 700;
            transition: all 0.3s;
        }
        .btn-save:hover {
            transform: translateY(-2px);
            box-shadow: 0 0 20px rgba(220,38,38,0.4);
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
            .main-content { margin-left: 0; padding: 20px; }
        }
    </style>
</head>
<body>

<div class="bg-glow"></div>

<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <img src="../images/99+ REDLINE LOGO - ORI.png" alt="REDLINE" id="logoImg">
        <div class="logo-text">REDLINE</div>
    </div>
    <nav class="sidebar-nav">
        <div class="nav-section">MAIN MENU</div>
        <a href="dashboard.php" class="sidebar-link"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a>
        <a href="live-monitor.php" class="sidebar-link"><i class="fas fa-video"></i><span>Live Monitor</span></a>
        <a href="riwayat.php" class="sidebar-link"><i class="fas fa-history"></i><span>Riwayat</span></a>
        <a href="pelanggaran.php" class="sidebar-link"><i class="fas fa-gavel"></i><span>Pelanggaran</span></a>
        <div class="nav-section">MANAGEMENT</div>
        <a href="pengaturan.php" class="sidebar-link active"><i class="fas fa-sliders-h"></i><span>Batas Kecepatan</span></a>
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
        <div class="page-title">⚙️ PENGATURAN SISTEM</div>
        <div class="clock" id="clock"></div>
    </div>

    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card-glass">
                <div class="card-header">
                    <i class="fas fa-tachometer-alt"></i> Batas Kecepatan Maksimum
                </div>
                
                <div class="text-center my-5">
                    <div class="speed-value" id="speedDisplay"><?php echo $currentSpeedLimit; ?></div>
                    <div class="text-white-50">km/h</div>
                </div>
                
                <input type="range" min="20" max="120" value="<?php echo $currentSpeedLimit; ?>" class="mb-4" id="speedSlider">
                
                <div class="row g-3 mb-4">
                    <div class="col-4">
                        <div class="preset-btn" onclick="setSpeed(40)">
                            <div class="fw-bold">40</div>
                            <small class="text-white-50">km/h</small>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="preset-btn" onclick="setSpeed(60)">
                            <div class="fw-bold">60</div>
                            <small class="text-white-50">km/h</small>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="preset-btn" onclick="setSpeed(80)">
                            <div class="fw-bold">80</div>
                            <small class="text-white-50">km/h</small>
                        </div>
                    </div>
                </div>
                
                <button class="btn-save w-100" onclick="saveSpeedLimit()">
                    <i class="fas fa-save me-2"></i> Simpan Pengaturan
                </button>
            </div>
        </div>
        
        <div class="col-lg-6">
            <div class="card-glass">
                <div class="card-header">
                    <i class="fas fa-bell"></i> Notifikasi
                </div>
                
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="notifEmail" checked>
                    <label class="form-check-label">Notifikasi Email</label>
                </div>
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="notifTelegram" checked>
                    <label class="form-check-label">Notifikasi Telegram</label>
                </div>
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="notifWhatsapp">
                    <label class="form-check-label">Notifikasi WhatsApp</label>
                </div>
                
                <div class="card-header mt-4">
                    <i class="fas fa-server"></i> Pengaturan Server
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Interval Refresh Data</label>
                    <select class="form-select">
                        <option>5 detik</option>
                        <option selected>10 detik</option>
                        <option>30 detik</option>
                        <option>1 menit</option>
                    </select>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Maksimal Data Tersimpan</label>
                    <select class="form-select">
                        <option>1000 record</option>
                        <option selected>5000 record</option>
                        <option>10000 record</option>
                    </select>
                </div>
            </div>
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
    
    const speedSlider = document.getElementById('speedSlider');
    const speedDisplay = document.getElementById('speedDisplay');
    
    speedSlider.addEventListener('input', function() {
        speedDisplay.innerHTML = this.value;
    });
    
    function setSpeed(value) {
        speedSlider.value = value;
        speedDisplay.innerHTML = value;
    }
    
    function saveSpeedLimit() {
        const speed = speedSlider.value;
        
        // Simpan ke localStorage untuk demo
        localStorage.setItem('rl_speed_limit', speed);
        
        alert('✅ Pengaturan batas kecepatan berhasil disimpan!\n\nBatas kecepatan: ' + speed + ' km/h');
    }
</script>

</body>
</html>