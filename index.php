<?php
/**
 * REDLINE - Landing Page
 * Halaman ini hanya berisi landing page.
 * Login ada di: pages/login.php
 * Dashboard ada di: pages/dashboard.php
 */
session_start();

// Jika sudah login, redirect ke dashboard langsung
if (isset($_SESSION['user'])) {
    header("Location: pages/dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>REDLINE | Smart Speed Monitor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@400;500;600;700&family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Inter',sans-serif; background:#0a0a0a; color:#fff; overflow-x:hidden; }
        .animated-bg { position:fixed; inset:0; z-index:-2;
            background: radial-gradient(circle at 20% 50%, rgba(220,38,38,0.15) 0%, transparent 50%),
                        radial-gradient(circle at 80% 80%, rgba(220,38,38,0.08) 0%, transparent 50%),
                        linear-gradient(135deg, #0a0a0a 0%, #110a0a 100%); }
        .grid-bg { position:fixed; inset:0; z-index:-1;
            background-image: linear-gradient(rgba(220,38,38,0.04) 1px, transparent 1px),
                              linear-gradient(90deg, rgba(220,38,38,0.04) 1px, transparent 1px);
            background-size:60px 60px; }

        /* NAV */
        .navbar { background:rgba(10,10,10,0.85); backdrop-filter:blur(20px);
            border-bottom:1px solid rgba(220,38,38,0.2); padding:16px 0; position:sticky; top:0; z-index:100; }
        .nav-inner { max-width:1300px; margin:0 auto; padding:0 40px;
            display:flex; justify-content:space-between; align-items:center; }
        .nav-logo { display:flex; align-items:center; gap:12px; text-decoration:none; }
        .nav-logo img { height:44px; filter:drop-shadow(0 0 12px rgba(220,38,38,0.5)); }
        .nav-logo .name { font-family:'Rajdhani',sans-serif; font-size:26px; font-weight:700;
            background:linear-gradient(135deg,#fff,#dc2626); -webkit-background-clip:text; background-clip:text; color:transparent; }
        .nav-links { display:flex; gap:32px; align-items:center; }
        .nav-links a { color:rgba(255,255,255,0.6); text-decoration:none; font-size:14px; font-weight:500; transition:.3s; }
        .nav-links a:hover { color:#dc2626; }
        .btn-nav-login { background:linear-gradient(135deg,#dc2626,#b91c1c); color:#fff !important;
            padding:10px 28px; border-radius:40px; font-weight:600 !important;
            box-shadow:0 0 15px rgba(220,38,38,0.3); transition:all .3s !important; }
        .btn-nav-login:hover { transform:translateY(-2px); box-shadow:0 0 25px rgba(220,38,38,0.5) !important; }

        /* HERO */
        .hero { min-height:90vh; display:flex; align-items:center; padding:80px 0; }
        .hero-inner { max-width:1300px; margin:0 auto; padding:0 40px;
            display:grid; grid-template-columns:1fr 1fr; gap:80px; align-items:center; }
        .hero-badge { display:inline-flex; align-items:center; gap:10px;
            background:rgba(220,38,38,0.12); border:1px solid rgba(220,38,38,0.3);
            padding:8px 20px; border-radius:50px; margin-bottom:28px; }
        .hero-badge .dot { width:8px; height:8px; background:#22c55e; border-radius:50%;
            animation:pulse 1.5s infinite; box-shadow:0 0 6px #22c55e; }
        .hero-badge span { font-size:12px; font-weight:600; color:#dc2626; letter-spacing:1px; }
        @keyframes pulse { 0%,100%{opacity:1}50%{opacity:.5} }
        .hero-title { font-size:64px; font-weight:800; line-height:1.1; margin-bottom:24px; letter-spacing:-2px; }
        .hero-title .red { background:linear-gradient(135deg,#dc2626,#ef4444,#f97316);
            -webkit-background-clip:text; background-clip:text; color:transparent; }
        .hero-desc { font-size:17px; color:rgba(255,255,255,0.6); line-height:1.7; margin-bottom:36px; max-width:480px; }
        .hero-btns { display:flex; gap:16px; flex-wrap:wrap; margin-bottom:48px; }
        .btn-red { background:linear-gradient(135deg,#dc2626,#b91c1c); color:#fff;
            padding:14px 34px; border-radius:50px; text-decoration:none; font-weight:600;
            display:inline-flex; align-items:center; gap:10px; transition:all .3s;
            box-shadow:0 0 20px rgba(220,38,38,0.3); }
        .btn-red:hover { transform:translateY(-3px); box-shadow:0 0 30px rgba(220,38,38,0.5); color:#fff; }
        .btn-ghost { border:2px solid rgba(220,38,38,0.5); color:#dc2626;
            padding:14px 34px; border-radius:50px; text-decoration:none; font-weight:600;
            display:inline-flex; align-items:center; gap:10px; transition:all .3s; }
        .btn-ghost:hover { background:#dc2626; color:#fff; transform:translateY(-3px); }
        .hero-stats { display:flex; gap:48px; }
        .stat-num { font-size:38px; font-weight:800;
            background:linear-gradient(135deg,#fff,#dc2626); -webkit-background-clip:text; background-clip:text; color:transparent; }
        .stat-lbl { font-size:12px; color:rgba(255,255,255,0.45); letter-spacing:1px; margin-top:4px; }

        /* PREVIEW */
        .preview-box { background:rgba(15,10,10,0.7); backdrop-filter:blur(20px);
            border-radius:28px; border:1px solid rgba(220,38,38,0.25); overflow:hidden;
            box-shadow:0 0 60px rgba(220,38,38,0.08); transition:.4s; }
        .preview-box:hover { transform:translateY(-8px); border-color:rgba(220,38,38,0.5); }
        .preview-bar { background:rgba(0,0,0,0.4); padding:14px 20px;
            display:flex; gap:10px; align-items:center; border-bottom:1px solid rgba(255,255,255,0.06); }
        .dot-r{width:12px;height:12px;border-radius:50%;background:#ff5f56;}
        .dot-y{width:12px;height:12px;border-radius:50%;background:#ffbd2e;}
        .dot-g{width:12px;height:12px;border-radius:50%;background:#27c93f;}
        .preview-bar span { font-size:11px; color:rgba(255,255,255,0.3); margin-left:8px; }
        .preview-body { padding:44px; text-align:center; }
        .speed-big { font-size:80px; font-weight:800; font-family:'Rajdhani',sans-serif; line-height:1;
            background:linear-gradient(135deg,#fff,#dc2626); -webkit-background-clip:text; background-clip:text; color:transparent; }
        .speed-unit { font-size:18px; color:rgba(255,255,255,0.4); }
        .status-pill { display:inline-block; padding:10px 28px; border-radius:50px;
            font-weight:700; font-size:13px; letter-spacing:1px; margin:20px 0; }
        .pill-safe { background:rgba(34,197,94,.12); color:#22c55e; border:1px solid rgba(34,197,94,.25); }
        .pill-danger { background:rgba(220,38,38,.12); color:#dc2626; border:1px solid rgba(220,38,38,.25); animation:flash .8s infinite; }
        @keyframes flash { 0%,100%{opacity:1}50%{opacity:.6} }
        .preview-icons { display:flex; justify-content:center; gap:28px; color:rgba(255,255,255,0.25); font-size:22px; }
        .preview-icons i:hover { color:#dc2626; transform:translateY(-4px); transition:.3s; }

        /* FEATURES */
        .features { padding:100px 0; background:rgba(0,0,0,0.25); }
        .section-wrap { max-width:1300px; margin:0 auto; padding:0 40px; }
        .sec-tag { color:#dc2626; font-size:12px; font-weight:600; letter-spacing:3px; text-transform:uppercase; margin-bottom:12px; }
        .sec-title { font-size:44px; font-weight:800; margin-bottom:16px; letter-spacing:-1px; }
        .sec-title .red { background:linear-gradient(135deg,#dc2626,#ef4444);
            -webkit-background-clip:text; background-clip:text; color:transparent; }
        .sec-desc { color:rgba(255,255,255,0.5); font-size:16px; max-width:560px; }
        .feat-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:24px; margin-top:60px; }
        .feat-card { background:rgba(15,10,10,0.5); border:1px solid rgba(255,255,255,0.06);
            border-radius:20px; padding:32px; transition:.4s; position:relative; overflow:hidden; }
        .feat-card::before { content:''; position:absolute; top:0; left:0; right:0; height:2px;
            background:linear-gradient(90deg,#dc2626,transparent); transform:scaleX(0); transition:.4s; }
        .feat-card:hover::before { transform:scaleX(1); }
        .feat-card:hover { border-color:rgba(220,38,38,0.25); transform:translateY(-6px); }
        .feat-icon { width:56px; height:56px; background:rgba(220,38,38,0.1);
            border-radius:16px; display:flex; align-items:center; justify-content:center;
            margin-bottom:24px; border:1px solid rgba(220,38,38,0.2); }
        .feat-icon i { font-size:26px; color:#dc2626; }
        .feat-card h3 { font-size:19px; font-weight:700; margin-bottom:10px; }
        .feat-card p { font-size:14px; color:rgba(255,255,255,0.55); line-height:1.6; }

        /* CTA */
        .cta-wrap { padding:80px 0; }
        .cta-box { text-align:center; background:linear-gradient(135deg,rgba(220,38,38,0.08),rgba(0,0,0,0.4));
            border:1px solid rgba(220,38,38,0.25); border-radius:40px; padding:60px;
            max-width:700px; margin:0 auto; }
        .cta-box h2 { font-size:38px; font-weight:800; margin-bottom:16px; }
        .cta-box p { color:rgba(255,255,255,0.55); margin-bottom:32px; }

        /* FOOTER */
        footer { background:#050505; border-top:1px solid rgba(220,38,38,0.15); padding:40px 0; text-align:center; }
        footer p { color:rgba(255,255,255,0.35); font-size:13px; }
        footer span { color:#dc2626; }

        @media(max-width:992px){
            .hero-inner{grid-template-columns:1fr;gap:50px;text-align:center;}
            .hero-desc,.hero-btns{margin-left:auto;margin-right:auto;}
            .hero-btns{justify-content:center;}
            .hero-stats{justify-content:center;}
            .hero-title{font-size:42px;}
            .feat-grid{grid-template-columns:1fr;}
            .nav-links{display:none;}
        }
    </style>
</head>
<body>
<div class="animated-bg"></div>
<div class="grid-bg"></div>

<!-- NAV -->
<nav class="navbar">
  <div class="nav-inner">
    <a href="index.php" class="nav-logo">
      <img src="images/99+ REDLINE LOGO - ORI.png" alt="REDLINE" onerror="this.style.display='none'">
      <span class="name">REDLINE</span>
    </a>
    <div class="nav-links">
      <a href="#features">Features</a>
      <a href="#how-it-works">How It Works</a>
      <a href="pages/login.php" class="btn-nav-login"><i class="fas fa-sign-in-alt"></i> Login Dashboard</a>
    </div>
  </div>
</nav>

<!-- HERO -->
<section class="hero">
  <div class="hero-inner">
    <div>
      <div class="hero-badge"><span class="dot"></span><span>⚡ IOT MONITORING AKTIF</span></div>
      <h1 class="hero-title">Smart Speed<br><span class="red">Monitor System</span></h1>
      <p class="hero-desc">Pantau kecepatan kendaraan real-time dengan ESP32-CAM, MQTT, dan notifikasi Telegram otomatis. Kelompok 1 — TEKOM 2A.</p>
      <div class="hero-btns">
        <a href="pages/login.php" class="btn-red"><i class="fas fa-tachometer-alt"></i> Buka Dashboard</a>
        <a href="#features" class="btn-ghost"><i class="fas fa-info-circle"></i> Pelajari Lebih</a>
      </div>
      <div class="hero-stats">
        <div><div class="stat-num">99.9%</div><div class="stat-lbl">AKURASI</div></div>
        <div><div class="stat-num">&lt;3s</div><div class="stat-lbl">NOTIFIKASI</div></div>
        <div><div class="stat-num">24/7</div><div class="stat-lbl">MONITORING</div></div>
      </div>
    </div>
    <div>
      <div class="preview-box">
        <div class="preview-bar">
          <span class="dot-r"></span><span class="dot-y"></span><span class="dot-g"></span>
          <span>REDLINE Live Dashboard</span>
        </div>
        <div class="preview-body">
          <div class="speed-big" id="spd">68</div>
          <div class="speed-unit">km/jam</div>
          <div class="status-pill pill-safe" id="stt">✓ AMAN</div>
          <div class="preview-icons">
            <i class="fas fa-camera"></i><i class="fab fa-telegram"></i>
            <i class="fas fa-database"></i><i class="fas fa-chart-line"></i>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- FEATURES -->
<section class="features" id="features">
  <div class="section-wrap">
    <div class="sec-tag">FITUR UTAMA</div>
    <h2 class="sec-title">Kenapa Pilih <span class="red">REDLINE?</span></h2>
    <p class="sec-desc">Teknologi terkini untuk keamanan maksimal di lingkungan Anda.</p>
    <div class="feat-grid">
      <div class="feat-card"><div class="feat-icon"><i class="fas fa-tachometer-alt"></i></div>
        <h3>Real-time Speed</h3><p>Data kecepatan langsung dari sensor HC-SR04 via MQTT. Latensi sub-detik ke dashboard.</p></div>
      <div class="feat-card"><div class="feat-icon"><i class="fas fa-camera"></i></div>
        <h3>ESP32-CAM</h3><p>Foto otomatis saat pelanggaran terdeteksi. Bukti tersimpan di database PostgreSQL.</p></div>
      <div class="feat-card"><div class="feat-icon"><i class="fab fa-telegram"></i></div>
        <h3>Notif Telegram</h3><p>Kirim foto + data kecepatan ke HP admin dalam waktu kurang dari 3 detik.</p></div>
      <div class="feat-card"><div class="feat-icon"><i class="fas fa-history"></i></div>
        <h3>Riwayat Lengkap</h3><p>Filter data berdasarkan tanggal, plat nomor, dan status pelanggaran. Export CSV/PDF.</p></div>
      <div class="feat-card"><div class="feat-icon"><i class="fas fa-users"></i></div>
        <h3>Multi User Role</h3><p>Admin, Operator, dan Viewer. Autentikasi session PHP yang aman.</p></div>
      <div class="feat-card"><div class="feat-icon"><i class="fas fa-cloud"></i></div>
        <h3>Cloud Ready</h3><p>Deploy ke VPS dengan Docker + Nginx HTTPS/TLS. Uptime monitoring 24 jam.</p></div>
    </div>
  </div>
</section>

<!-- HOW IT WORKS -->
<section class="features" id="how-it-works" style="background:transparent">
  <div class="section-wrap">
    <div class="sec-tag">CARA KERJA</div>
    <h2 class="sec-title">4 Langkah <span class="red">Otomatis</span></h2>
    <div class="feat-grid" style="margin-top:50px">
      <div class="feat-card" style="text-align:center"><div class="feat-icon" style="margin:0 auto 20px"><i class="fas fa-microchip"></i></div><h3>01. Deteksi</h3><p>HC-SR04 mendeteksi kecepatan kendaraan</p></div>
      <div class="feat-card" style="text-align:center"><div class="feat-icon" style="margin:0 auto 20px"><i class="fas fa-wifi"></i></div><h3>02. Kirim</h3><p>Data dikirim via MQTT ke server VPS</p></div>
      <div class="feat-card" style="text-align:center"><div class="feat-icon" style="margin:0 auto 20px"><i class="fas fa-brain"></i></div><h3>03. Analisis</h3><p>Sistem bandingkan dengan batas kecepatan</p></div>
      <div class="feat-card" style="text-align:center"><div class="feat-icon" style="margin:0 auto 20px"><i class="fab fa-telegram"></i></div><h3>04. Notifikasi</h3><p>Alert langsung ke Telegram admin</p></div>
    </div>
  </div>
</section>

<!-- CTA -->
<div class="cta-wrap">
  <div class="section-wrap">
    <div class="cta-box">
      <h2>Siap Amankan <span style="color:#dc2626">Area Anda?</span></h2>
      <p>Login ke dashboard dan mulai monitoring kecepatan sekarang.</p>
      <a href="pages/login.php" class="btn-red" style="display:inline-flex"><i class="fas fa-sign-in-alt"></i> Masuk Dashboard</a>
    </div>
  </div>
</div>

<footer>
  <p>© 2025 <span>REDLINE</span> Smart Speed Monitor · Kelompok 1 · TEKOM 2A · Politeknik Negeri Padang</p>
</footer>

<script>
let speeds = [28,35,42,55,68,72,80,22,90,48];
let i = 0;
setInterval(() => {
  let s = speeds[i++ % speeds.length];
  document.getElementById('spd').textContent = s;
  let el = document.getElementById('stt');
  if (s > 40) { el.textContent = '⚠ PELANGGARAN'; el.className = 'status-pill pill-danger'; }
  else { el.textContent = '✓ AMAN'; el.className = 'status-pill pill-safe'; }
}, 2500);
</script>
</body>
</html>
