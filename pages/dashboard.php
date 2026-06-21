<?php
session_start();
require_once __DIR__ . '/../config/koneksi.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$user = $_SESSION['user'];

$totalKendaraan = 0;
$totalPelanggaran = 0;
$kendaraanHariIni = 0;
$pelanggaranHariIni = 0;
$riwayatTerbaru = [];

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

} catch (Exception $e) {
    // Tabel belum ada, biarkan default 0
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>REDLINE | Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@400;500;600;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --red: #dc2626; --red-dark: #b91c1c; --red-glow: rgba(220,38,38,0.3);
            --bg: #0a0a0f; --sidebar: #0d0d18; --card: rgba(15,15,28,0.8);
            --border: rgba(220,38,38,0.2); --text-dim: rgba(255,255,255,0.45);
            --green: #22c55e; --sidebar-w: 260px;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: var(--bg); color: white; min-height: 100vh; overflow-x: hidden; }

        .sidebar {
            position: fixed; left: 0; top: 0; bottom: 0; width: var(--sidebar-w);
            background: var(--sidebar); border-right: 1px solid var(--border);
            display: flex; flex-direction: column; z-index: 100; overflow-y: auto;
        }
        .sidebar-brand {
            padding: 28px 24px 24px; border-bottom: 1px solid var(--border);
            display: flex; align-items: center; gap: 14px;
        }
        .sidebar-brand img { height: 42px; filter: drop-shadow(0 0 10px var(--red-glow)); }
        .brand-icon {
            width: 42px; height: 42px;
            background: linear-gradient(135deg, var(--red), var(--red-dark));
            border-radius: 12px; display: flex; align-items: center; justify-content: center;
            font-size: 18px; color: white; box-shadow: 0 0 20px var(--red-glow); flex-shrink: 0;
        }
        .brand-name {
            font-family: 'Rajdhani', sans-serif; font-size: 22px; font-weight: 700; letter-spacing: 2px;
            background: linear-gradient(135deg, #fff, var(--red)); -webkit-background-clip: text;
            background-clip: text; color: transparent;
        }
        .brand-sub { font-size: 10px; color: var(--text-dim); letter-spacing: 1px; text-transform: uppercase; }

        .sidebar-user {
            margin: 16px 16px 8px;
            background: rgba(220,38,38,0.08); border: 1px solid var(--border);
            border-radius: 14px; padding: 14px 16px;
            display: flex; align-items: center; gap: 12px;
        }
        .user-avatar {
            width: 38px; height: 38px;
            background: linear-gradient(135deg, var(--red), var(--red-dark));
            border-radius: 10px; display: flex; align-items: center; justify-content: center;
            font-size: 16px; color: white; flex-shrink: 0;
            font-family: 'Rajdhani', sans-serif; font-weight: 700;
        }
        .user-name { font-size: 14px; font-weight: 600; color: white; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .user-role { font-size: 11px; color: var(--red); text-transform: uppercase; letter-spacing: 1px; }

        .sidebar-nav { flex: 1; padding: 8px 12px; }
        .nav-section { font-size: 10px; color: var(--text-dim); letter-spacing: 2px; text-transform: uppercase; padding: 16px 12px 8px; }
        .nav-item {
            display: flex; align-items: center; gap: 12px;
            padding: 11px 14px; border-radius: 12px; text-decoration: none;
            color: var(--text-dim); font-size: 14px; font-weight: 500;
            transition: all 0.25s; margin-bottom: 2px;
        }
        .nav-item i { width: 18px; text-align: center; font-size: 15px; transition: color 0.25s; }
        .nav-item:hover, .nav-item.active {
            background: rgba(220,38,38,0.12); color: white; border: 1px solid rgba(220,38,38,0.15);
        }
        .nav-item.active { color: var(--red); font-weight: 600; }
        .nav-item.active i, .nav-item:hover i { color: var(--red); }

        .sidebar-footer { padding: 16px 12px; border-top: 1px solid var(--border); }
        .btn-logout {
            display: flex; align-items: center; gap: 12px;
            padding: 11px 14px; border-radius: 12px; text-decoration: none;
            color: var(--text-dim); font-size: 14px; font-weight: 500;
            transition: all 0.25s; width: 100%;
        }
        .btn-logout:hover { background: rgba(220,38,38,0.15); color: var(--red); }
        .btn-logout i { width: 18px; text-align: center; }

        .main { margin-left: var(--sidebar-w); min-height: 100vh; display: flex; flex-direction: column; }
        .topbar {
            display: flex; align-items: center; justify-content: space-between;
            padding: 20px 32px; border-bottom: 1px solid var(--border);
            background: rgba(10,10,15,0.6); backdrop-filter: blur(10px);
            position: sticky; top: 0; z-index: 50;
        }
        .topbar-title h1 { font-family: 'Rajdhani', sans-serif; font-size: 26px; font-weight: 700; letter-spacing: 1px; }
        .topbar-title p { font-size: 12px; color: var(--text-dim); margin-top: 2px; }
        .topbar-right { display: flex; align-items: center; gap: 16px; }
        .live-badge {
            display: inline-flex; align-items: center; gap: 8px;
            background: rgba(34,197,94,0.1); border: 1px solid rgba(34,197,94,0.2);
            border-radius: 50px; padding: 6px 16px; font-size: 12px; color: var(--green); font-weight: 600; letter-spacing: 1px;
        }
        .live-dot { width: 7px; height: 7px; background: var(--green); border-radius: 50%; animation: pulse 1.5s infinite; box-shadow: 0 0 6px var(--green); }
        @keyframes pulse { 0%,100%{opacity:1;transform:scale(1);}50%{opacity:0.5;transform:scale(0.8);} }
        .topbar-time { font-family: 'Rajdhani', sans-serif; font-size: 18px; font-weight: 600; color: rgba(255,255,255,0.7); letter-spacing: 1px; }

        .page-content { padding: 28px 32px; flex: 1; }

        .stats-row { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 28px; }
        .stat-card {
            background: var(--card); border: 1px solid rgba(255,255,255,0.07);
            border-radius: 20px; padding: 24px; position: relative; overflow: hidden;
            transition: all 0.3s; backdrop-filter: blur(10px);
        }
        .stat-card::before { content:''; position:absolute; top:0;left:0;right:0;height:2px; background:linear-gradient(90deg,var(--red),transparent); }
        .stat-card:hover { border-color: var(--border); transform: translateY(-3px); box-shadow: 0 12px 30px rgba(0,0,0,0.3); }
        .stat-card.green::before { background:linear-gradient(90deg,var(--green),transparent); }
        .stat-card.yellow::before { background:linear-gradient(90deg,#f59e0b,transparent); }
        .stat-icon { width:48px;height:48px;border-radius:14px;display:flex;align-items:center;justify-content:center;font-size:20px;margin-bottom:18px; }
        .stat-icon.red   { background:rgba(220,38,38,0.15);color:var(--red); }
        .stat-icon.green { background:rgba(34,197,94,0.15);color:var(--green); }
        .stat-icon.yellow{ background:rgba(245,158,11,0.15);color:#f59e0b; }
        .stat-value { font-family:'Rajdhani',sans-serif;font-size:38px;font-weight:700;line-height:1;margin-bottom:6px; }
        .stat-label { font-size:12px;color:var(--text-dim);text-transform:uppercase;letter-spacing:1px; }
        .stat-change { position:absolute;top:20px;right:20px;font-size:11px;color:var(--green);display:flex;align-items:center;gap:4px; }

        .live-section { display: grid; grid-template-columns: 340px 1fr; gap: 20px; margin-bottom: 28px; }
        .speed-card {
            background: var(--card); border: 1px solid var(--border);
            border-radius: 24px; padding: 32px; text-align: center;
            backdrop-filter: blur(10px); position: relative; overflow: hidden;
        }
        .speed-card::after { content:'';position:absolute;inset:0;background:radial-gradient(circle at center bottom,rgba(220,38,38,0.08) 0%,transparent 60%);pointer-events:none; }
        .speed-label { font-size:11px;color:var(--text-dim);text-transform:uppercase;letter-spacing:3px;margin-bottom:20px; }
        .speed-gauge { position:relative;display:inline-block;margin-bottom:20px; }
        .speed-ring {
            width:180px;height:180px;border-radius:50%;border:3px solid rgba(255,255,255,0.05);
            display:flex;align-items:center;justify-content:center;position:relative;
            box-shadow:0 0 40px rgba(220,38,38,0.15) inset;
        }
        .speed-ring::before { content:'';position:absolute;inset:-3px;border-radius:50%;border:3px solid transparent;border-top-color:var(--red);border-right-color:var(--red);animation:spin 3s linear infinite; }
        @keyframes spin { to{transform:rotate(360deg);} }
        .speed-number { font-family:'Rajdhani',sans-serif;font-size:64px;font-weight:700;line-height:1;transition:all 0.4s; }
        .speed-unit-text { font-size:16px;color:var(--text-dim);margin-top:-4px; }
        .speed-status-badge { display:inline-block;padding:8px 28px;border-radius:50px;font-size:13px;font-weight:700;letter-spacing:2px;text-transform:uppercase;margin-bottom:24px;font-family:'Rajdhani',sans-serif; }
        .speed-status-badge.safe { background:rgba(34,197,94,0.12);color:var(--green);border:1px solid rgba(34,197,94,0.25); }
        .speed-status-badge.danger { background:rgba(220,38,38,0.12);color:var(--red);border:1px solid rgba(220,38,38,0.3);animation:flashBorder 0.5s infinite alternate; }
        @keyframes flashBorder { from{border-color:rgba(220,38,38,0.3);}to{border-color:rgba(220,38,38,0.8);} }
        .speed-meta { display:flex;justify-content:space-around;padding-top:20px;border-top:1px solid rgba(255,255,255,0.06); }
        .speed-meta-item { text-align:center; }
        .speed-meta-value { font-family:'Rajdhani',sans-serif;font-size:20px;font-weight:600; }
        .speed-meta-label { font-size:10px;color:var(--text-dim);text-transform:uppercase;letter-spacing:1px; }

        .chart-card { background:var(--card);border:1px solid rgba(255,255,255,0.07);border-radius:24px;padding:28px;backdrop-filter:blur(10px); }
        .card-header-row { display:flex;justify-content:space-between;align-items:center;margin-bottom:24px; }
        .card-title { font-family:'Rajdhani',sans-serif;font-size:18px;font-weight:700;letter-spacing:1px; }
        .card-subtitle { font-size:12px;color:var(--text-dim);margin-top:2px; }

        .table-card { background:var(--card);border:1px solid rgba(255,255,255,0.07);border-radius:24px;overflow:hidden;backdrop-filter:blur(10px); }
        .table-card-header { padding:22px 28px;border-bottom:1px solid rgba(255,255,255,0.06);display:flex;justify-content:space-between;align-items:center; }
        .table-redline { width:100%;border-collapse:collapse; }
        .table-redline thead th { font-size:11px;text-transform:uppercase;letter-spacing:2px;color:var(--text-dim);padding:14px 20px;border-bottom:1px solid rgba(255,255,255,0.06);background:rgba(0,0,0,0.2);font-weight:500; }
        .table-redline tbody td { padding:14px 20px;font-size:14px;border-bottom:1px solid rgba(255,255,255,0.04);color:rgba(255,255,255,0.8);vertical-align:middle; }
        .table-redline tbody tr:hover td { background:rgba(220,38,38,0.04); }
        .table-redline tbody tr:last-child td { border-bottom:none; }
        .badge-safe { background:rgba(34,197,94,0.12);color:var(--green);border:1px solid rgba(34,197,94,0.2);padding:4px 14px;border-radius:50px;font-size:11px;font-weight:600;letter-spacing:1px; }
        .badge-violation { background:rgba(220,38,38,0.12);color:var(--red);border:1px solid rgba(220,38,38,0.25);padding:4px 14px;border-radius:50px;font-size:11px;font-weight:600;letter-spacing:1px; }
        .speed-cell { font-family:'Rajdhani',sans-serif;font-size:18px;font-weight:700; }
        .speed-cell.high { color:var(--red); }
        .speed-cell.normal { color:var(--green); }
        .btn-red {
            background:linear-gradient(135deg,var(--red),var(--red-dark));border:none;border-radius:10px;color:white;
            padding:8px 18px;font-size:13px;font-weight:600;font-family:'Rajdhani',sans-serif;letter-spacing:1px;
            cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;gap:8px;transition:all 0.3s;
        }
        .btn-red:hover { box-shadow:0 0 20px var(--red-glow);color:white;transform:translateY(-1px); }
        .two-col { display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:28px; }
        ::-webkit-scrollbar{width:5px;} ::-webkit-scrollbar-track{background:transparent;} ::-webkit-scrollbar-thumb{background:rgba(220,38,38,0.3);border-radius:10px;}
        @media(max-width:1024px){.stats-row{grid-template-columns:repeat(2,1fr);}.live-section{grid-template-columns:1fr;}.two-col{grid-template-columns:1fr;}}
        @media(max-width:768px){.sidebar{transform:translateX(-100%);transition:transform 0.3s;}.sidebar.open{transform:translateX(0);}.main{margin-left:0;}.page-content{padding:20px 16px;}}
    </style>
</head>
<body>

<aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <img src="../images/99+ REDLINE LOGO - ORI.png" alt="REDLINE"
             onerror="this.style.display='none'; document.getElementById('brandIcon').style.display='flex';">
        <div class="brand-icon" id="brandIcon" style="display:none;"><i class="fas fa-tachometer-alt"></i></div>
        <div>
            <div class="brand-name">REDLINE</div>
            <div class="brand-sub">Speed Monitor</div>
        </div>
    </div>

    <div class="sidebar-user">
        <div class="user-avatar"><?= strtoupper(substr($user['username'], 0, 1)) ?></div>
        <div>
            <div class="user-name"><?= htmlspecialchars($user['username']) ?></div>
            <div class="user-role"><?= htmlspecialchars($user['role']) ?></div>
        </div>
    </div>

    <nav class="sidebar-nav">
        <div class="nav-section">Main Menu</div>
        <a href="dashboard.php" class="nav-item active"><i class="fas fa-chart-pie"></i> Dashboard</a>
        <a href="live-monitor.php" class="nav-item"><i class="fas fa-satellite-dish"></i> Live Monitor</a>
        <a href="riwayat.php" class="nav-item"><i class="fas fa-history"></i> Riwayat</a>
        <a href="pelanggaran.php" class="nav-item"><i class="fas fa-exclamation-triangle"></i> Pelanggaran</a>

        <?php if (in_array($user['role'], ['admin', 'operator'])): ?>
        <div class="nav-section">Management</div>
        <a href="pengguna.php" class="nav-item"><i class="fas fa-users"></i> Pengguna</a>
        <a href="pengaturan.php" class="nav-item"><i class="fas fa-sliders-h"></i> Pengaturan</a>
        <a href="export.php" class="nav-item"><i class="fas fa-file-export"></i> Export Data</a>
        <?php endif; ?>

        <div class="nav-section">Account</div>
        <a href="profile.php" class="nav-item"><i class="fas fa-user-circle"></i> Profile</a>
    </nav>

    <div class="sidebar-footer">
        <a href="logout.php" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
</aside>

<div class="main">
    <div class="topbar">
        <div class="topbar-title">
            <h1>Dashboard Overview</h1>
            <p>Selamat datang, <strong><?= htmlspecialchars($user['username']) ?></strong> — <?= date('l, d F Y') ?></p>
        </div>
        <div class="topbar-right">
            <div class="live-badge"><div class="live-dot"></div> LIVE</div>
            <div class="topbar-time" id="clock">--:--:--</div>
        </div>
    </div>

    <div class="page-content">
        <div class="stats-row">
            <div class="stat-card">
                <div class="stat-icon red"><i class="fas fa-car"></i></div>
                <div class="stat-value"><?= number_format($totalKendaraan) ?></div>
                <div class="stat-label">Total Kendaraan</div>
                <div class="stat-change"><i class="fas fa-arrow-up"></i> Semua waktu</div>
            </div>
            <div class="stat-card red">
                <div class="stat-icon red"><i class="fas fa-exclamation-triangle"></i></div>
                <div class="stat-value" style="color:var(--red)"><?= number_format($totalPelanggaran) ?></div>
                <div class="stat-label">Total Pelanggaran</div>
            </div>
            <div class="stat-card green">
                <div class="stat-icon green"><i class="fas fa-calendar-day"></i></div>
                <div class="stat-value" style="color:var(--green)"><?= number_format($kendaraanHariIni) ?></div>
                <div class="stat-label">Kendaraan Hari Ini</div>
            </div>
            <div class="stat-card yellow">
                <div class="stat-icon yellow"><i class="fas fa-bell"></i></div>
                <div class="stat-value" style="color:#f59e0b"><?= number_format($pelanggaranHariIni) ?></div>
                <div class="stat-label">Pelanggaran Hari Ini</div>
            </div>
        </div>

        <div class="live-section">
            <div class="speed-card">
                <div class="speed-label">⚡ Live Speed Detection</div>
                <div class="speed-gauge">
                    <div class="speed-ring">
                        <div>
                            <div class="speed-number" id="liveSpeed">0</div>
                            <div class="speed-unit-text">km/h</div>
                        </div>
                    </div>
                </div>
                <div><span class="speed-status-badge safe" id="liveStatus">SAFE</span></div>
                <div class="speed-meta">
                    <div class="speed-meta-item">
                        <div class="speed-meta-value" id="maxSpeed">0</div>
                        <div class="speed-meta-label">Max km/h</div>
                    </div>
                    <div class="speed-meta-item">
                        <div class="speed-meta-value" id="avgSpeed">0</div>
                        <div class="speed-meta-label">Avg km/h</div>
                    </div>
                    <div class="speed-meta-item">
                        <div class="speed-meta-value" id="countSession">0</div>
                        <div class="speed-meta-label">Deteksi</div>
                    </div>
                </div>
            </div>

            <div class="chart-card">
                <div class="card-header-row">
                    <div>
                        <div class="card-title">Grafik Kecepatan Real-time</div>
                        <div class="card-subtitle">Update setiap 2 detik via MQTT</div>
                    </div>
                    <div class="live-badge"><div class="live-dot"></div> STREAMING</div>
                </div>
                <canvas id="speedChart" height="200"></canvas>
            </div>
        </div>

        <div class="table-card">
            <div class="table-card-header">
                <div>
                    <div class="card-title">Riwayat Terakhir</div>
                    <div class="card-subtitle">10 data terbaru dari database</div>
                </div>
                <a href="riwayat.php" class="btn-red"><i class="fas fa-list"></i> Lihat Semua</a>
            </div>
            <div style="overflow-x:auto;">
                <table class="table-redline">
                    <thead>
                        <tr><th>#</th><th>Waktu</th><th>Kecepatan</th><th>Status</th><th>Plat</th><th>Aksi</th></tr>
                    </thead>
                    <tbody id="recentTable">
                        <?php if (empty($riwayatTerbaru)): ?>
                        <tr><td colspan="6" style="text-align:center;padding:30px;color:var(--text-dim);">
                            <i class="fas fa-database me-2"></i> Menunggu data dari sensor...
                        </td></tr>
                        <?php else: ?>
                        <?php foreach ($riwayatTerbaru as $i => $row): ?>
                        <tr>
                            <td style="color:var(--text-dim);"><?= $i + 1 ?></td>
                            <td><?= date('d/m H:i:s', strtotime($row['created_at'])) ?></td>
                            <td class="speed-cell <?= $row['speed'] > 60 ? 'high' : 'normal' ?>"><?= $row['speed'] ?> km/h</td>
                            <td>
                                <?php if ($row['status'] === 'violation'): ?>
                                    <span class="badge-violation"><i class="fas fa-times me-1"></i>VIOLATION</span>
                                <?php else: ?>
                                    <span class="badge-safe"><i class="fas fa-check me-1"></i>SAFE</span>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($row['plate'] ?? '-') ?></td>
                            <td><a href="pelanggaran.php" class="btn-red" style="padding:5px 12px;font-size:11px;"><i class="fas fa-eye"></i></a></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
function updateClock() { document.getElementById('clock').textContent = new Date().toLocaleTimeString('id-ID'); }
setInterval(updateClock, 1000); updateClock();

const ctx = document.getElementById('speedChart').getContext('2d');
const speedChart = new Chart(ctx, {
    type: 'line',
    data: { labels: [], datasets: [{ label:'Speed (km/h)', data:[], borderColor:'#dc2626', backgroundColor:'rgba(220,38,38,0.08)', borderWidth:2, pointBackgroundColor:'#dc2626', pointRadius:3, fill:true, tension:0.4 }] },
    options: {
        responsive:true, maintainAspectRatio:false, animation:{duration:400},
        plugins:{ legend:{display:false}, tooltip:{backgroundColor:'rgba(15,15,28,0.95)',borderColor:'rgba(220,38,38,0.4)',borderWidth:1,titleColor:'#dc2626',bodyColor:'white'} },
        scales: {
            x:{grid:{color:'rgba(255,255,255,0.04)'},ticks:{color:'rgba(255,255,255,0.35)',font:{size:11}}},
            y:{grid:{color:'rgba(255,255,255,0.04)'},ticks:{color:'rgba(255,255,255,0.35)',font:{size:11}},min:0,suggestedMax:120}
        }
    }
});

let lastId = 0;

async function fetchLiveData() {
    try {
        const res  = await fetch('../api/get-live-data.php');
        const data = await res.json();

        if (!data.success) return;

        // ── Update live speed gauge ──
        if (data.latest) {
            const speed    = parseFloat(data.latest.speed);
            const statusEl = document.getElementById('liveStatus');
            const speedEl  = document.getElementById('liveSpeed');

            speedEl.textContent = speed.toFixed(1);

            if (speed > 60) {
                statusEl.textContent = 'VIOLATION!';
                statusEl.className   = 'speed-status-badge danger';
                speedEl.style.color  = '#dc2626';
            } else {
                statusEl.textContent = 'SAFE';
                statusEl.className   = 'speed-status-badge safe';
                speedEl.style.color  = '#22c55e';
            }

            // Update chart hanya kalau ada data baru
            if (data.latest.id && data.latest.id !== lastId) {
                lastId = data.latest.id;
                const time = data.latest.time_only || new Date().toLocaleTimeString('id-ID');
                speedChart.data.labels.push(time);
                speedChart.data.datasets[0].data.push(speed);
                if (speedChart.data.labels.length > 20) {
                    speedChart.data.labels.shift();
                    speedChart.data.datasets[0].data.shift();
                }
                speedChart.update('none');
            }
        }

        // ── Update statistik ──
        if (data.stats) {
            document.getElementById('maxSpeed').textContent    = data.stats.max_speed_today  || 0;
            document.getElementById('avgSpeed').textContent    = data.stats.avg_speed_today  || 0;
            document.getElementById('countSession').textContent = data.stats.total_today      || 0;
        }

        // ── Update tabel riwayat ──
        if (data.history && data.history.length > 0) {
            const tbody = document.getElementById('recentTable');
            tbody.innerHTML = '';
            data.history.forEach((row, idx) => {
                const spd       = parseFloat(row.speed);
                const isOver    = spd > 60;
                const statusHTML = isOver
                    ? '<span class="badge-violation"><i class="fas fa-times me-1"></i>VIOLATION</span>'
                    : '<span class="badge-safe"><i class="fas fa-check me-1"></i>SAFE</span>';
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td style="color:var(--text-dim)">${idx + 1}</td>
                    <td>${row.time_only || '-'}</td>
                    <td class="speed-cell ${isOver ? 'high' : 'normal'}">${spd.toFixed(1)} km/h</td>
                    <td>${statusHTML}</td>
                    <td>${row.plate || '-'}</td>
                    <td><a href="pelanggaran.php" class="btn-red" style="padding:5px 12px;font-size:11px;"><i class="fas fa-eye"></i></a></td>
                `;
                tbody.appendChild(tr);
            });
        }

    } catch (err) {
        console.error('Fetch error:', err);
    }
}

fetchLiveData();
setInterval(fetchLiveData, 2000);
</script>
</body>
</html>
