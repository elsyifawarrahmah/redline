<?php
session_start();
include __DIR__ . '/../config/koneksi.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$user = $_SESSION['user'];

// Ambil konfigurasi MQTT dari database
try {
    $stmt = $conn->query("SELECT * FROM mqtt_config ORDER BY id DESC LIMIT 1");
    $mqttConfig = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $mqttConfig = [
        'broker' => 'broker.emqx.io',
        'port' => 1883,
        'topic' => 'redline/speed',
        'username' => '',
        'password' => ''
    ];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>MQTT Config - REDLINE</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #eef2f7 100%);
            color: #1e293b;
        }
        
        .navbar-top {
            background: white;
            box-shadow: 0 2px 20px rgba(0,0,0,0.05);
            padding: 16px 32px;
            position: sticky;
            top: 0;
            z-index: 1000;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }
        .navbar-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
        }
        .navbar-brand img { height: 45px; width: auto; }
        .navbar-brand .logo-text { font-size: 24px; font-weight: 800; color: #dc2626; }
        .navbar-menu { display: flex; gap: 8px; align-items: center; flex-wrap: wrap; }
        .nav-item {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            border-radius: 12px;
            color: #64748b;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
        }
        .nav-item:hover, .nav-item.active { background: #fef2f2; color: #dc2626; }
        .user-info {
            display: flex;
            align-items: center;
            gap: 16px;
            background: #f8fafc;
            padding: 6px 16px 6px 12px;
            border-radius: 40px;
        }
        .user-avatar {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #dc2626, #b91c1c);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
        }
        .logout-btn { color: #64748b; transition: 0.3s; }
        .logout-btn:hover { color: #dc2626; }
        
        .container-fluid { padding: 32px; max-width: 1400px; margin: 0 auto; }
        
        .card-modern {
            background: white;
            border-radius: 24px;
            padding: 28px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 12px rgba(0,0,0,0.03);
            height: 100%;
        }
        .card-title {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 24px;
            padding-bottom: 16px;
            border-bottom: 1px solid #e2e8f0;
        }
        .card-title i { color: #dc2626; margin-right: 10px; }
        
        .form-group { margin-bottom: 20px; }
        .form-label {
            font-size: 13px;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 8px;
            display: block;
        }
        .form-control, .form-select {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            font-size: 14px;
            transition: all 0.3s;
        }
        .form-control:focus, .form-select:focus {
            outline: none;
            border-color: #dc2626;
            box-shadow: 0 0 0 3px rgba(220,38,38,0.1);
        }
        
        .btn-primary-custom {
            background: #dc2626;
            color: white;
            border: none;
            padding: 14px 24px;
            border-radius: 14px;
            font-weight: 700;
            width: 100%;
            transition: all 0.3s;
        }
        .btn-primary-custom:hover {
            background: #b91c1c;
            transform: translateY(-2px);
        }
        
        .info-list { display: flex; flex-direction: column; gap: 16px; }
        .info-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #e2e8f0;
        }
        .info-label {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #64748b;
        }
        .info-value {
            font-weight: 600;
        }
        .info-value.online {
            color: #22c55e;
        }
        
        .footer {
            text-align: center;
            padding: 24px;
            margin-top: 32px;
            font-size: 12px;
            color: #64748b;
            border-top: 1px solid #e2e8f0;
        }
        .footer span { color: #dc2626; font-weight: 600; }
        
        @media (max-width: 992px) {
            .navbar-top { flex-direction: column; gap: 16px; }
            .navbar-menu { justify-content: center; }
            .container-fluid { padding: 16px; }
        }
    </style>
</head>
<body>

<nav class="navbar-top">
    <a href="dashboard.php" class="navbar-brand">
        <img src="../images/99+ REDLINE LOGO - ORI.png" alt="REDLINE" id="logoImg">
        <span class="logo-text">REDLINE</span>
    </a>
    <div class="navbar-menu">
        <a href="dashboard.php" class="nav-item"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
        <a href="live-monitor.php" class="nav-item"><i class="fas fa-video"></i> Live Monitor</a>
        <a href="riwayat.php" class="nav-item"><i class="fas fa-history"></i> Riwayat</a>
        <a href="pelanggaran.php" class="nav-item"><i class="fas fa-gavel"></i> Pelanggaran</a>
        <a href="pengaturan.php" class="nav-item"><i class="fas fa-sliders-h"></i> Pengaturan</a>
        <a href="pengguna.php" class="nav-item"><i class="fas fa-users"></i> Pengguna</a>
        <a href="profile.php" class="nav-item"><i class="fas fa-user-circle"></i> Profil</a>
        <a href="mqtt-config.php" class="nav-item active"><i class="fas fa-server"></i> MQTT</a>
        <a href="export.php" class="nav-item"><i class="fas fa-file-export"></i> Export</a>
    </div>
    <div class="user-info">
        <div class="user-avatar"><i class="fas fa-user"></i></div>
        <div>
            <div class="fw-bold" id="userName"><?php echo htmlspecialchars($user['username']); ?></div>
            <small class="text-secondary" id="userRole"><?php echo htmlspecialchars(ucfirst($user['role'] ?? 'User')); ?></small>
        </div>
        <a href="logout.php" class="logout-btn">
            <i class="fas fa-sign-out-alt"></i>
        </a>
    </div>
</nav>

<div class="container-fluid">
    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card-modern">
                <div class="card-title">
                    <i class="fas fa-server"></i> Konfigurasi MQTT Broker
                </div>
                <form id="mqttForm">
                    <div class="form-group">
                        <label class="form-label">MQTT Broker Address</label>
                        <input type="text" class="form-control" id="broker" value="<?php echo htmlspecialchars($mqttConfig['broker'] ?? 'broker.emqx.io'); ?>" placeholder="misal: broker.emqx.io">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Port</label>
                        <input type="number" class="form-control" id="port" value="<?php echo htmlspecialchars($mqttConfig['port'] ?? 1883); ?>" placeholder="1883">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Topic</label>
                        <input type="text" class="form-control" id="topic" value="<?php echo htmlspecialchars($mqttConfig['topic'] ?? 'redline/speed'); ?>" placeholder="redline/speed">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Username (Opsional)</label>
                        <input type="text" class="form-control" id="mqttUsername" value="<?php echo htmlspecialchars($mqttConfig['username'] ?? ''); ?>" placeholder="Username MQTT">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Password (Opsional)</label>
                        <input type="password" class="form-control" id="mqttPassword" value="<?php echo htmlspecialchars($mqttConfig['password'] ?? ''); ?>" placeholder="Password MQTT">
                    </div>
                    <button type="submit" class="btn-primary-custom">
                        <i class="fas fa-save me-2"></i> Simpan Konfigurasi
                    </button>
                </form>
            </div>
        </div>
        
        <div class="col-lg-6">
            <div class="card-modern">
                <div class="card-title">
                    <i class="fas fa-info-circle"></i> Status Koneksi
                </div>
                <div class="info-list">
                    <div class="info-item">
                        <div class="info-label">
                            <i class="fas fa-circle text-success"></i> Status Broker
                        </div>
                        <div class="info-value online">Terhubung</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">
                            <i class="fas fa-clock"></i> Last Connect
                        </div>
                        <div class="info-value"><?php echo date('d/m/Y H:i:s'); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">
                            <i class="fas fa-envelope"></i> Messages Received
                        </div>
                        <div class="info-value">-</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">
                            <i class="fas fa-exclamation-triangle"></i> Last Error
                        </div>
                        <div class="info-value">Tidak ada</div>
                    </div>
                </div>
                
                <div class="card-title mt-4">
                    <i class="fas fa-plug"></i> Test Koneksi
                </div>
                <button class="btn-primary-custom" onclick="testConnection()">
                    <i class="fas fa-satellite-dish me-2"></i> Test MQTT Connection
                </button>
            </div>
        </div>
    </div>
    
    <div class="footer">
        <i class="fas fa-microchip me-1"></i> <span>Kelompok 1</span> | 99+ REDLINE Smart Speed Monitor IoT | Teknik Komputer 2A
    </div>
</div>

<script>
    const logoImg = document.getElementById('logoImg');
    if (logoImg) logoImg.onerror = function() { this.style.display = 'none'; };
    
    document.getElementById('mqttForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const broker = document.getElementById('broker').value;
        const port = document.getElementById('port').value;
        const topic = document.getElementById('topic').value;
        
        // Simpan ke localStorage untuk demo
        localStorage.setItem('rl_mqtt_config', JSON.stringify({
            broker: broker,
            port: port,
            topic: topic
        }));
        
        alert('✅ Konfigurasi MQTT berhasil disimpan!');
    });
    
    function testConnection() {
        const btn = event.target;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Menguji...';
        
        setTimeout(() => {
            alert('📡 Menghubungi broker MQTT...\n\n✅ Koneksi berhasil! (Demo)');
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-satellite-dish me-2"></i> Test MQTT Connection';
        }, 1500);
    }
</script>

</body>
</html>