<?php
session_start();
include __DIR__ . '/../config/koneksi.php';
if (!isset($_SESSION['user'])) { header("Location: login.php"); exit(); }
$user = $_SESSION['user'];

$message = '';

// Ambil data user lengkap dari database
try {
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
    $stmt->execute([$user['id']]);
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $userData = $user;
}

// Proses update profil
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_profile'])) {
    $email    = trim($_POST['email'] ?? '');
    $phone    = trim($_POST['phone'] ?? '');
    $telegram = trim($_POST['telegram'] ?? '');

    try {
        $stmt = $conn->prepare("UPDATE users SET email = ?, phone = ?, telegram = ? WHERE id = ?");
        $stmt->execute([$email, $phone, $telegram, $user['id']]);
        $message = 'success';
        // Refresh data
        $stmt = $conn->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
        $stmt->execute([$user['id']]);
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $message = 'error';
    }
}

// Proses ganti password
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $oldPass = $_POST['old_password'] ?? '';
    $newPass = $_POST['new_password'] ?? '';
    $confPass = $_POST['confirm_password'] ?? '';

    if (empty($oldPass) || empty($newPass) || empty($confPass)) {
        $message = 'pw_empty';
    } elseif ($newPass !== $confPass) {
        $message = 'pw_mismatch';
    } elseif (!password_verify($oldPass, $userData['password'])) {
        $message = 'pw_wrong';
    } else {
        try {
            $hashed = password_hash($newPass, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$hashed, $user['id']]);
            $message = 'pw_success';
        } catch (PDOException $e) {
            $message = 'pw_error';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Saya - REDLINE</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { margin:0;padding:0;box-sizing:border-box; }
        body { font-family:'Inter',sans-serif;background:#0d1117;color:#e6edf3; }
        .bg-glow { position:fixed;top:0;left:0;width:100%;height:100%;z-index:-2;background:radial-gradient(ellipse at 30% 40%,rgba(220,38,38,0.15) 0%,transparent 60%),linear-gradient(135deg,#0d1117 0%,#161b22 100%); }
        .sidebar { position:fixed;left:0;top:0;bottom:0;width:280px;background:rgba(13,17,23,0.95);backdrop-filter:blur(16px);border-right:1px solid rgba(220,38,38,0.3);z-index:100;overflow-y:auto; }
        .sidebar::-webkit-scrollbar{width:4px;} .sidebar::-webkit-scrollbar-thumb{background:#dc2626;border-radius:4px;}
        .sidebar-header { padding:28px 24px;border-bottom:1px solid rgba(220,38,38,0.25);display:flex;align-items:center;gap:12px; }
        .sidebar-header img { height:45px;filter:drop-shadow(0 0 12px rgba(220,38,38,0.4)); }
        .sidebar-header .logo-text { font-size:22px;font-weight:800;background:linear-gradient(135deg,#fff,#dc2626);-webkit-background-clip:text;background-clip:text;color:transparent; }
        .sidebar-nav { padding:24px 16px; }
        .nav-section { font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:1.5px;color:rgba(255,255,255,0.4);margin:20px 0 12px 12px; }
        .sidebar-link { display:flex;align-items:center;gap:14px;padding:12px 16px;margin:6px 0;border-radius:12px;color:rgba(255,255,255,0.7);text-decoration:none;font-weight:500;transition:all 0.3s; }
        .sidebar-link:hover, .sidebar-link.active { background:rgba(220,38,38,0.15);color:#dc2626;transform:translateX(5px); }
        .sidebar-link i { width:24px;font-size:18px; }
        .sidebar-user { margin:24px 16px;padding:16px;background:rgba(255,255,255,0.05);border-radius:16px;display:flex;align-items:center;gap:12px;border:1px solid rgba(220,38,38,0.15); }
        .user-avatar { width:46px;height:46px;background:linear-gradient(135deg,#dc2626,#991b1b);border-radius:14px;display:flex;align-items:center;justify-content:center;color:white; }
        .user-info h6 { font-size:14px;font-weight:600;margin:0; }
        .user-info p { font-size:10px;color:rgba(255,255,255,0.5);margin:0; }
        .logout-btn { color:rgba(255,255,255,0.4);transition:0.3s; } .logout-btn:hover { color:#dc2626; }
        .main-content { margin-left:280px;padding:28px 32px;min-height:100vh; }
        .topbar { display:flex;justify-content:space-between;align-items:center;margin-bottom:28px;padding-bottom:16px;border-bottom:1px solid rgba(220,38,38,0.2); }
        .page-title { font-size:26px;font-weight:700;background:linear-gradient(135deg,#fff,#dc2626);-webkit-background-clip:text;background-clip:text;color:transparent; }
        .clock { font-family:monospace;font-size:14px;background:rgba(0,0,0,0.4);padding:8px 20px;border-radius:30px;border:1px solid rgba(220,38,38,0.2); }
        .card-glass { background:rgba(22,27,34,0.8);backdrop-filter:blur(12px);border-radius:24px;padding:24px;border:1px solid rgba(220,38,38,0.2);margin-bottom:24px; }
        .card-header-title { font-size:16px;font-weight:600;margin-bottom:20px;padding-bottom:14px;border-bottom:1px solid rgba(220,38,38,0.15); }
        .card-header-title i { color:#dc2626;margin-right:10px; }
        .form-control, .form-select { background:rgba(13,17,23,0.9);border:1px solid rgba(220,38,38,0.25);color:#fff;border-radius:12px;padding:12px 16px;font-size:14px;width:100%; }
        .form-control:focus, .form-select:focus { border-color:#dc2626;box-shadow:0 0 0 3px rgba(220,38,38,0.2);outline:none;background:#0d1117;color:#fff; }
        .form-control::placeholder { color:rgba(255,255,255,0.4); }
        .form-label { font-size:12px;font-weight:600;color:rgba(255,255,255,0.7);margin-bottom:8px;display:block; }
        .btn-save { background:linear-gradient(135deg,#dc2626,#b91c1c);color:white;border:none;padding:13px 24px;border-radius:12px;font-weight:600;width:100%;transition:all 0.3s;cursor:pointer; }
        .btn-save:hover { transform:translateY(-2px);box-shadow:0 0 15px rgba(220,38,38,0.4); }
        .profile-avatar-circle { width:90px;height:90px;border-radius:50%;background:linear-gradient(135deg,#dc2626,#7f1d1d);display:flex;align-items:center;justify-content:center;font-size:36px;font-weight:700;color:white;margin:0 auto 16px; }
        .info-row { display:flex;justify-content:space-between;padding:12px 0;border-bottom:1px solid rgba(255,255,255,0.06); }
        .info-row:last-child { border-bottom:none; }
        .info-label { color:rgba(255,255,255,0.5);font-size:13px; }
        .info-value { font-weight:600;color:#fff; }
        .badge-role { padding:3px 12px;border-radius:20px;font-size:11px;font-weight:600; }
        .badge-role.admin { background:rgba(220,38,38,0.2);color:#dc2626; }
        .badge-role.operator { background:rgba(59,130,246,0.2);color:#3b82f6; }
        .badge-role.viewer { background:rgba(107,114,128,0.2);color:#9ca3af; }
        .alert-success-custom { background:rgba(34,197,94,0.1);border:1px solid rgba(34,197,94,0.3);color:#4ade80;border-radius:12px;padding:12px 16px;margin-bottom:20px;display:flex;align-items:center;gap:10px; }
        .alert-error-custom { background:rgba(220,38,38,0.1);border:1px solid rgba(220,38,38,0.3);color:#fca5a5;border-radius:12px;padding:12px 16px;margin-bottom:20px;display:flex;align-items:center;gap:10px; }
        .footer { text-align:center;padding:24px;margin-top:12px;font-size:12px;color:rgba(255,255,255,0.35);border-top:1px solid rgba(220,38,38,0.15); }
        .footer span { color:#dc2626;font-weight:600; }
        @media(max-width:992px){.sidebar{transform:translateX(-100%);}.main-content{margin-left:0;padding:20px;}}
    </style>
</head>
<body>
<div class="bg-glow"></div>

<aside class="sidebar">
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
        <a href="pengaturan.php" class="sidebar-link"><i class="fas fa-sliders-h"></i><span>Batas Kecepatan</span></a>
        <a href="pengguna.php" class="sidebar-link"><i class="fas fa-users"></i><span>Pengguna</span></a>
        <a href="profile.php" class="sidebar-link active"><i class="fas fa-user-cog"></i><span>Profil Saya</span></a>
        <div class="nav-section">REPORTS</div>
        <a href="export.php" class="sidebar-link"><i class="fas fa-file-export"></i><span>Export Data</span></a>
    </nav>
    <div class="sidebar-user">
        <div class="user-avatar"><i class="fas fa-user"></i></div>
        <div class="user-info">
            <h6><?= htmlspecialchars($user['username']) ?></h6>
            <p><?= htmlspecialchars(ucfirst($user['role'] ?? 'User')) ?></p>
        </div>
        <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i></a>
    </div>
</aside>

<main class="main-content">
    <div class="topbar">
        <div class="page-title">👤 Profil Saya</div>
        <div class="clock" id="clock"></div>
    </div>

    <?php if ($message === 'success'): ?>
    <div class="alert-success-custom"><i class="fas fa-check-circle"></i> Profil berhasil diperbarui!</div>
    <?php elseif ($message === 'error'): ?>
    <div class="alert-error-custom"><i class="fas fa-exclamation-circle"></i> Gagal menyimpan. Cek koneksi database.</div>
    <?php elseif ($message === 'pw_success'): ?>
    <div class="alert-success-custom"><i class="fas fa-check-circle"></i> Password berhasil diubah!</div>
    <?php elseif ($message === 'pw_mismatch'): ?>
    <div class="alert-error-custom"><i class="fas fa-exclamation-circle"></i> Konfirmasi password tidak cocok!</div>
    <?php elseif ($message === 'pw_wrong'): ?>
    <div class="alert-error-custom"><i class="fas fa-exclamation-circle"></i> Password lama salah!</div>
    <?php elseif ($message === 'pw_empty'): ?>
    <div class="alert-error-custom"><i class="fas fa-exclamation-circle"></i> Semua field password wajib diisi!</div>
    <?php endif; ?>

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card-glass text-center">
                <div class="profile-avatar-circle"><?= strtoupper(substr($user['username'], 0, 1)) ?></div>
                <h4 class="fw-bold"><?= htmlspecialchars($userData['username'] ?? $user['username']) ?></h4>
                <p class="mb-2"><span class="badge-role <?= $user['role'] ?>"><?= ucfirst($user['role']) ?></span></p>
                <div class="info-row mt-3"><span class="info-label">Email</span><span class="info-value"><?= htmlspecialchars($userData['email'] ?? '-') ?></span></div>
                <div class="info-row"><span class="info-label">Telepon</span><span class="info-value"><?= htmlspecialchars($userData['phone'] ?? '-') ?></span></div>
                <div class="info-row"><span class="info-label">Telegram</span><span class="info-value"><?= htmlspecialchars($userData['telegram'] ?? '-') ?></span></div>
                <div class="info-row"><span class="info-label">Bergabung</span><span class="info-value"><?= isset($userData['created_at']) ? date('d/m/Y', strtotime($userData['created_at'])) : '-' ?></span></div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card-glass">
                <div class="card-header-title"><i class="fas fa-user-edit"></i> Edit Profil</div>
                <form method="POST" action="profile.php">
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($user['username']) ?>" readonly style="opacity:0.6;">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" placeholder="email@example.com" value="<?= htmlspecialchars($userData['email'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">No. Telepon</label>
                        <input type="tel" name="phone" class="form-control" placeholder="08123456789" value="<?= htmlspecialchars($userData['phone'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><i class="fab fa-telegram me-1 text-info"></i> Telegram Chat ID</label>
                        <input type="text" name="telegram" class="form-control" placeholder="Masukkan Chat ID Telegram" value="<?= htmlspecialchars($userData['telegram'] ?? '') ?>">
                        <small class="text-white-50">Isi untuk menerima notifikasi pelanggaran via Telegram</small>
                    </div>
                    <button type="submit" name="save_profile" class="btn-save"><i class="fas fa-save me-2"></i> Simpan Perubahan</button>
                </form>
            </div>

            <div class="card-glass">
                <div class="card-header-title"><i class="fas fa-key"></i> Ganti Password</div>
                <form method="POST" action="profile.php">
                    <div class="mb-3">
                        <label class="form-label">Password Lama</label>
                        <input type="password" name="old_password" class="form-control" placeholder="Masukkan password lama" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password Baru</label>
                        <input type="password" name="new_password" class="form-control" placeholder="Masukkan password baru" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Konfirmasi Password Baru</label>
                        <input type="password" name="confirm_password" class="form-control" placeholder="Ulangi password baru" required>
                    </div>
                    <button type="submit" name="change_password" class="btn-save"><i class="fas fa-lock me-2"></i> Ganti Password</button>
                </form>
            </div>
        </div>
    </div>

    <div class="footer">
        <i class="fas fa-microchip me-1"></i> <span>Kelompok 1</span> | 99+ REDLINE Smart Speed Monitor IoT | Teknik Komputer 2A
    </div>
</main>

<script>
    setInterval(() => { document.getElementById('clock').innerHTML = new Date().toLocaleTimeString('id-ID'); }, 1000);
    const logoImg = document.getElementById('logoImg');
    if (logoImg) logoImg.onerror = function() { this.style.display='none'; };
</script>
</body>
</html>
