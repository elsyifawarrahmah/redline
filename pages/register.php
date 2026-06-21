<?php
session_start();
if (isset($_SESSION['user'])) { header('Location: ../index.php'); exit(); }
include __DIR__ . '/../config/koneksi.php';
$error = '';
$success = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'viewer';
    if (empty($username) || empty($email) || empty($password)) {
        $error = 'Semua field wajib diisi!';
    } else {
        try {
            $stmt = $conn->prepare('SELECT id FROM users WHERE username = :u LIMIT 1');
            $stmt->execute(['u' => $username]);
            if ($stmt->fetch()) {
                $error = 'Username sudah digunakan!';
            } else {
                $stmt = $conn->prepare('INSERT INTO users (username, email, password, role) VALUES (:u, :e, :p, :r)');
                $stmt->execute(['u'=>$username,'e'=>$email,'p'=>password_hash($password, PASSWORD_DEFAULT),'r'=>$role]);
                $success = 'Registrasi berhasil! Silakan login.';
            }
        } catch (PDOException $ex) {
            $error = 'Error: ' . $ex->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang='id'>
<head>
<meta charset='UTF-8'>
<meta name='viewport' content='width=device-width, initial-scale=1.0'>
<title>REDLINE | Register</title>
<link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
<style>
body{background:#0a0a0f;color:white;font-family:Inter,sans-serif;min-height:100vh;display:flex;align-items:center;justify-content:center;}
.card{background:rgba(15,15,28,0.9);border:1px solid rgba(220,38,38,0.3);border-radius:24px;padding:40px;width:100%;max-width:450px;}
.form-control{background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);color:white;border-radius:12px;padding:12px 16px;}
.form-control:focus{background:rgba(220,38,38,0.05);border-color:#dc2626;color:white;box-shadow:none;}
.btn-red{background:linear-gradient(135deg,#dc2626,#b91c1c);border:none;border-radius:12px;color:white;padding:14px;font-weight:700;width:100%;}
.btn-red:hover{opacity:0.9;color:white;}
label{font-size:12px;color:rgba(255,255,255,0.5);text-transform:uppercase;letter-spacing:1px;margin-bottom:6px;}
h2{font-size:28px;font-weight:800;background:linear-gradient(135deg,#fff,#dc2626);-webkit-background-clip:text;background-clip:text;color:transparent;text-align:center;margin-bottom:28px;}
select.form-control option{background:#1a1a2e;color:white;}
</style>
</head>
<body>
<div class='card'>
<h2>DAFTAR AKUN</h2>
<?php if($error): ?><div class='alert alert-danger'><?= htmlspecialchars($error) ?></div><?php endif; ?>
<?php if($success): ?><div class='alert alert-success'><?= htmlspecialchars($success) ?></div><?php endif; ?>
<form method='POST'>
<div class='mb-3'><label>Username</label><input type='text' name='username' class='form-control' required></div>
<div class='mb-3'><label>Email</label><input type='email' name='email' class='form-control' required></div>
<div class='mb-3'><label>Password</label><input type='password' name='password' class='form-control' required></div>
<div class='mb-4'><label>Role</label>
<select name='role' class='form-control'>
<option value='viewer'>Viewer</option>
<option value='operator'>Operator</option>
<option value='admin'>Admin</option>
</select></div>
<button type='submit' class='btn-red'>DAFTAR SEKARANG</button>
</form>
<div class='text-center mt-3' style='font-size:13px;color:rgba(255,255,255,0.4)'>
Sudah punya akun? <a href='login.php' style='color:#dc2626'>Login di sini</a>
</div>
</div>
</body>
</html>