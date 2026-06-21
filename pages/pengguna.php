<?php
session_start();
include '../config/koneksi.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$user = $_SESSION['user'];

// Ambil daftar pengguna dari database
try {
    $stmt = $conn->query("SELECT id, username, role, created_at FROM users ORDER BY id DESC");
    $users = $stmt->fetchAll();
} catch (PDOException $e) {
    $users = [];
}

// Proses tambah pengguna
$message = '';
if (isset($_POST['add_user'])) {
    $newUsername = $_POST['username'] ?? '';
    $newPassword = $_POST['password'] ?? '';
    $newRole = $_POST['role'] ?? 'operator';
    
    if (!empty($newUsername) && !empty($newPassword)) {
        try {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
            $stmt->execute([$newUsername, $hashedPassword, $newRole]);
            $message = '✅ Pengguna baru berhasil ditambahkan!';
            
            // Refresh data
            $stmt = $conn->query("SELECT id, username, role, created_at FROM users ORDER BY id DESC");
            $users = $stmt->fetchAll();
        } catch (PDOException $e) {
            $message = '❌ Gagal menambahkan pengguna: ' . $e->getMessage();
        }
    } else {
        $message = '⚠️ Username dan password wajib diisi!';
    }
}

// Proses hapus pengguna
if (isset($_POST['delete_user'])) {
    $userId = $_POST['user_id'] ?? 0;
    if ($userId > 0 && $userId != $user['id']) {
        try {
            $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $message = '✅ Pengguna berhasil dihapus!';
            
            // Refresh data
            $stmt = $conn->query("SELECT id, username, role, created_at FROM users ORDER BY id DESC");
            $users = $stmt->fetchAll();
        } catch (PDOException $e) {
            $message = '❌ Gagal menghapus pengguna!';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengguna - REDLINE Smart Speed Monitor</title>
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
        }
        .card-header {
            font-size: 16px;
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
            background: #0d1117;
        }
        .form-control::placeholder { color: rgba(255,255,255,0.4); }
        .form-label {
            font-size: 12px;
            font-weight: 600;
            color: rgba(255,255,255,0.7);
            margin-bottom: 8px;
            display: block;
        }
        .btn-danger-custom {
            background: linear-gradient(135deg, #dc2626, #b91c1c);
            color: white;
            border: none;
            padding: 12px;
            border-radius: 12px;
            font-weight: 600;
            width: 100%;
            transition: all 0.3s;
        }
        .btn-danger-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 0 15px rgba(220,38,38,0.4);
        }

        .users-table { width: 100%; }
        .users-table th {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            color: rgba(255,255,255,0.5);
            padding: 14px 12px;
            border-bottom: 1px solid rgba(220,38,38,0.2);
            text-align: left;
        }
        .users-table td {
            padding: 14px 12px;
            border-bottom: 1px solid rgba(255,255,255,0.05);
            font-size: 13px;
        }
        .users-table tr:hover {
            background: rgba(220,38,38,0.05);
        }
        .badge-role {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
        }
        .badge-role.admin { background: rgba(220,38,38,0.2); color: #dc2626; }
        .badge-role.operator { background: rgba(59,130,246,0.2); color: #3b82f6; }
        .badge-role.viewer { background: rgba(107,114,128,0.2); color: #9ca3af; }

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
        <a href="pengaturan.php" class="sidebar-link"><i class="fas fa-sliders-h"></i><span>Batas Kecepatan</span></a>
        <a href="pengguna.php" class="sidebar-link active"><i class="fas fa-users"></i><span>Pengguna</span></a>
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
        <div class="page-title">👥 KELOLA PENGGUNA</div>
        <div class="clock" id="clock"></div>
    </div>

    <?php if ($message): ?>
    <div class="alert alert-info mb-4" style="background: rgba(220,38,38,0.2); border: 1px solid rgba(220,38,38,0.3); color: white;">
        <?php echo $message; ?>
    </div>
    <?php endif; ?>

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card-glass">
                <div class="card-header">
                    <i class="fas fa-user-plus"></i> Tambah Pengguna
                </div>
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" class="form-control" name="username" placeholder="Masukkan username" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" class="form-control" name="password" placeholder="Masukkan password" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Role</label>
                        <select class="form-select" name="role">
                            <option value="admin">Administrator</option>
                            <option value="operator" selected>Operator</option>
                            <option value="viewer">Viewer</option>
                        </select>
                    </div>
                    <button type="submit" name="add_user" class="btn-danger-custom">
                        <i class="fas fa-plus me-2"></i> Tambah Pengguna
                    </button>
                </form>
            </div>
        </div>
        
        <div class="col-lg-8">
            <div class="card-glass">
                <div class="card-header">
                    <i class="fas fa-users"></i> Daftar Pengguna
                    <span class="badge bg-secondary"><?php echo count($users); ?> pengguna</span>
                </div>
                
                <div style="max-height: 400px; overflow-y: auto;">
                    <table class="users-table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Username</th>
                                <th>Role</th>
                                <th>Tanggal Daftar</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($users) > 0): ?>
                                <?php $no = 1; foreach ($users as $u): ?>
                                <tr>
                                    <td><?php echo $no++; ?></td>
                                    <td><strong><?php echo htmlspecialchars($u['username']); ?></strong></td>
                                    <td>
                                        <span class="badge-role <?php echo $u['role']; ?>">
                                            <?php echo ucfirst($u['role']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('d/m/Y', strtotime($u['created_at'])); ?></td>
                                    <td>
                                        <?php if ($u['id'] != $user['id']): ?>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                            <button type="submit" name="delete_user" class="btn btn-sm btn-outline-danger" onclick="return confirm('Yakin hapus pengguna ini?')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                        <?php else: ?>
                                        <small class="text-white-50">Anda</small>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-white-50">
                                        <i class="fas fa-users fa-2x mb-2 d-block"></i>
                                        Belum ada pengguna lain
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
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
</script>

</body>
</html>