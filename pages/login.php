<?php
session_start();
include __DIR__ . '/../config/koneksi.php';

if (isset($_SESSION['user'])) {
    header("Location: dashboard.php");
    exit();
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        $error = "Username dan password wajib diisi!";
    } else {
        try {
            $stmt = $conn->prepare("SELECT * FROM users WHERE username = :username LIMIT 1");
            $stmt->execute(['username' => $username]);
            $userData = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($userData && password_verify($password, $userData['password'])) {
                $_SESSION['user'] = [
                    'id'       => $userData['id'],
                    'username' => $userData['username'],
                    'role'     => $userData['role'] ?? 'user'
                ];
                header("Location: dashboard.php");
                exit();
            } else {
                $error = "Username atau password salah!";
            }
        } catch (PDOException $e) {
            $error = "Koneksi database gagal. Cek server.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>REDLINE | Login Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@400;500;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --red: #dc2626;
            --red-dark: #b91c1c;
            --red-glow: rgba(220,38,38,0.4);
            --bg: #0a0a0f;
            --card: rgba(15,15,25,0.9);
            --border: rgba(220,38,38,0.25);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            position: relative;
        }

        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background-image:
                linear-gradient(rgba(220,38,38,0.05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(220,38,38,0.05) 1px, transparent 1px);
            background-size: 50px 50px;
            z-index: 0;
        }

        body::after {
            content: '';
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 800px;
            height: 800px;
            background: radial-gradient(circle, rgba(220,38,38,0.12) 0%, transparent 60%);
            z-index: 0;
        }

        .particles { position: fixed; inset: 0; z-index: 0; overflow: hidden; }
        .particle {
            position: absolute;
            background: var(--red);
            border-radius: 50%;
            animation: drift linear infinite;
            opacity: 0;
        }
        @keyframes drift {
            0%   { transform: translateY(100vh) translateX(0); opacity: 0; }
            10%  { opacity: 0.6; }
            90%  { opacity: 0.4; }
            100% { transform: translateY(-100px) translateX(80px); opacity: 0; }
        }

        .login-wrapper {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 460px;
            padding: 20px;
        }

        .login-card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 28px;
            padding: 50px 44px;
            backdrop-filter: blur(30px);
            box-shadow:
                0 0 0 1px rgba(255,255,255,0.04) inset,
                0 40px 80px rgba(0,0,0,0.6),
                0 0 60px var(--red-glow);
            animation: cardIn 0.7s cubic-bezier(0.16, 1, 0.3, 1) both;
        }
        @keyframes cardIn {
            from { opacity: 0; transform: translateY(30px) scale(0.96); }
            to   { opacity: 1; transform: translateY(0) scale(1); }
        }

        .login-logo { text-align: center; margin-bottom: 36px; }
        .logo-icon {
            width: 72px; height: 72px;
            background: linear-gradient(135deg, var(--red), var(--red-dark));
            border-radius: 20px;
            display: inline-flex; align-items: center; justify-content: center;
            font-size: 30px; color: white; margin-bottom: 16px;
            box-shadow: 0 0 30px var(--red-glow);
            position: relative;
        }
        .logo-img {
            width: 72px; height: 72px; object-fit: contain; margin-bottom: 16px;
            filter: drop-shadow(0 0 20px var(--red-glow));
            display: block; margin-left: auto; margin-right: auto;
        }
        .login-title {
            font-family: 'Rajdhani', sans-serif;
            font-size: 32px; font-weight: 700; letter-spacing: 3px;
            background: linear-gradient(135deg, #fff 40%, var(--red));
            -webkit-background-clip: text; background-clip: text; color: transparent;
        }
        .login-subtitle {
            font-size: 12px; color: rgba(255,255,255,0.35);
            letter-spacing: 2px; text-transform: uppercase; margin-top: 6px;
        }

        .form-group { margin-bottom: 22px; }
        .form-label {
            font-size: 11px; font-weight: 600; letter-spacing: 2px;
            text-transform: uppercase; color: rgba(255,255,255,0.5);
            margin-bottom: 10px; display: block;
        }
        .input-wrapper { position: relative; }
        .input-wrapper i {
            position: absolute; left: 18px; top: 50%;
            transform: translateY(-50%); color: var(--red); font-size: 14px; z-index: 2;
        }
        .form-control {
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 14px; color: white;
            padding: 14px 18px 14px 48px;
            font-size: 15px; width: 100%; transition: all 0.3s; outline: none;
        }
        .form-control::placeholder { color: rgba(255,255,255,0.2); }
        .form-control:focus {
            border-color: var(--red);
            background: rgba(220,38,38,0.06);
            box-shadow: 0 0 0 3px rgba(220,38,38,0.12);
            color: white;
        }

        .toggle-pw {
            position: absolute; right: 16px; top: 50%;
            transform: translateY(-50%);
            background: none; border: none; color: rgba(255,255,255,0.3);
            cursor: pointer; font-size: 14px; transition: color 0.2s; z-index: 2;
        }
        .toggle-pw:hover { color: var(--red); }

        .alert-error {
            background: rgba(220,38,38,0.12);
            border: 1px solid rgba(220,38,38,0.3);
            border-radius: 12px; color: #fca5a5;
            padding: 12px 16px; font-size: 13px; margin-bottom: 22px;
            display: flex; align-items: center; gap: 10px;
        }

        .btn-submit {
            width: 100%;
            background: linear-gradient(135deg, var(--red), var(--red-dark));
            border: none; border-radius: 14px; color: white;
            padding: 15px; font-size: 15px; font-weight: 600;
            font-family: 'Rajdhani', sans-serif; letter-spacing: 2px;
            text-transform: uppercase; cursor: pointer; transition: all 0.3s;
            position: relative; overflow: hidden;
            box-shadow: 0 4px 20px rgba(220,38,38,0.35); margin-top: 8px;
        }
        .btn-submit::before {
            content: ''; position: absolute; top: 0; left: -100%;
            width: 100%; height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent);
            transition: left 0.5s;
        }
        .btn-submit:hover::before { left: 100%; }
        .btn-submit:hover { transform: translateY(-2px); box-shadow: 0 8px 30px rgba(220,38,38,0.5); }
        .btn-submit:active { transform: translateY(0); }
        .btn-submit.loading { pointer-events: none; opacity: 0.8; }
        .btn-submit .spinner { display: none; }
        .btn-submit.loading .spinner { display: inline-block; }
        .btn-submit.loading .btn-text { display: none; }

        .login-footer {
            text-align: center; margin-top: 28px; font-size: 13px;
            color: rgba(255,255,255,0.35);
        }
        .login-footer a { color: var(--red); text-decoration: none; font-weight: 500; transition: opacity 0.2s; }
        .login-footer a:hover { opacity: 0.7; }

        .status-badge {
            display: inline-flex; align-items: center; gap: 8px;
            background: rgba(34,197,94,0.1);
            border: 1px solid rgba(34,197,94,0.2);
            border-radius: 50px; padding: 5px 14px; font-size: 11px;
            color: #4ade80; letter-spacing: 1px; margin-top: 8px;
        }
        .status-badge span {
            width: 6px; height: 6px; background: #4ade80;
            border-radius: 50%; animation: pulse 1.5s infinite; box-shadow: 0 0 6px #4ade80;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.5; transform: scale(0.8); }
        }
    </style>
</head>
<body>

<div class="particles" id="particles"></div>

<div class="login-wrapper">
    <div class="login-card">

        <div class="login-logo">
            <img src="../images/99+ REDLINE LOGO - ORI.png" alt="REDLINE" class="logo-img"
                 onerror="this.style.display='none'; document.getElementById('fallbackIcon').style.display='inline-flex';">
            <div class="logo-icon" id="fallbackIcon" style="display:none;">
                <i class="fas fa-tachometer-alt"></i>
            </div>
            <div class="login-title">REDLINE</div>
            <div class="login-subtitle">Smart Speed Monitor</div>
            <div class="status-badge">
                <span></span> SYSTEM ONLINE
            </div>
        </div>

        <?php if ($error): ?>
        <div class="alert-error">
            <i class="fas fa-exclamation-circle"></i>
            <?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>

        <form method="POST" action="login.php" id="loginForm">

            <div class="form-group">
                <label class="form-label">Username</label>
                <div class="input-wrapper">
                    <i class="fas fa-user"></i>
                    <input
                        type="text"
                        name="username"
                        class="form-control"
                        placeholder="Masukkan username"
                        value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                        autocomplete="username"
                        required
                    >
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Password</label>
                <div class="input-wrapper">
                    <i class="fas fa-lock"></i>
                    <input
                        type="password"
                        name="password"
                        id="passwordInput"
                        class="form-control"
                        placeholder="Masukkan password"
                        autocomplete="current-password"
                        required
                    >
                    <button type="button" class="toggle-pw" onclick="togglePassword()">
                        <i class="fas fa-eye" id="eyeIcon"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn-submit" id="submitBtn">
                <span class="btn-text">
                    <i class="fas fa-sign-in-alt me-2"></i> ACCESS DASHBOARD
                </span>
                <span class="spinner">
                    <i class="fas fa-circle-notch fa-spin me-2"></i> AUTHENTICATING...
                </span>
            </button>

        </form>

        <div class="login-footer">
            Belum punya akun? <a href="app.php?page=register">Daftar sekarang</a>
        </div>

    </div>
</div>

<script>
(function() {
    const c = document.getElementById('particles');
    for (let i = 0; i < 30; i++) {
        const p = document.createElement('div');
        p.className = 'particle';
        const s = Math.random() * 3 + 1;
        p.style.cssText = `
            width:${s}px; height:${s}px;
            left:${Math.random()*100}%;
            animation-duration:${Math.random()*10+8}s;
            animation-delay:${Math.random()*8}s;
        `;
        c.appendChild(p);
    }
})();

function togglePassword() {
    const input = document.getElementById('passwordInput');
    const icon  = document.getElementById('eyeIcon');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.replace('fa-eye-slash', 'fa-eye');
    }
}

document.getElementById('loginForm').addEventListener('submit', function() {
    document.getElementById('submitBtn').classList.add('loading');
});
</script>
</body>
</html>
