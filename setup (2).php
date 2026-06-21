<?php
/**
 * REDLINE - Setup & Config Helper
 * Buka halaman ini di browser: http://192.168.6.10/redline/setup.php
 * HAPUS FILE INI setelah selesai setup!
 */

$step = $_GET['step'] ?? 'check';
$msg = '';

// Test koneksi dengan berbagai kombinasi
function testConn($host, $db, $user, $pass) {
    try {
        $pdo = new PDO("mysql:host={$host};dbname={$db};charset=utf8mb4", $user, $pass);
        return ['ok' => true, 'pdo' => $pdo];
    } catch (PDOException $e) {
        return ['ok' => false, 'err' => $e->getMessage()];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_config'])) {
    $h = $_POST['host'] ?? 'localhost';
    $d = $_POST['db'] ?? 'redline_db';
    $u = $_POST['user'] ?? 'root';
    $p = $_POST['pass'] ?? '';
    $res = testConn($h, $d, $u, $p);
    if ($res['ok']) {
        // Tulis ke koneksi.php
        $content = "<?php\n\$host = " . var_export($h, true) . ";\n\$db   = " . var_export($d, true) . ";\n\$user = " . var_export($u, true) . ";\n\$pass = " . var_export($p, true) . ";\ntry {\n    \$conn = new PDO(\"mysql:host={\$host};dbname={\$db};charset=utf8mb4\", \$user, \$pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, PDO::ATTR_EMULATE_PREPARES => false]);\n} catch (PDOException \$e) {\n    die('DB Error: ' . \$e->getMessage());\n}\n?>";
        file_put_contents(__DIR__ . '/config/koneksi.php', $content);
        $msg = 'success';
    } else {
        $msg = 'fail:' . $res['err'];
    }
}

// Auto-detect
$candidates = [
    ['localhost','redline_db','root',''],
    ['localhost','redline_db','root','root'],
    ['localhost','redline_db','root','1234'],
    ['localhost','redline_db','root','password'],
    ['127.0.0.1','redline_db','root',''],
];
$detected = null;
foreach ($candidates as $c) {
    $r = testConn($c[0], $c[1], $c[2], $c[3]);
    if ($r['ok']) { $detected = $c; break; }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>REDLINE Setup</title>
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:monospace;background:#0a0a0a;color:#e0e0e0;min-height:100vh;padding:40px 20px}
.wrap{max-width:700px;margin:0 auto}
h1{color:#dc2626;font-size:28px;margin-bottom:6px}
.sub{color:#666;font-size:13px;margin-bottom:30px}
.card{background:#1a1a1a;border:1px solid #333;border-radius:12px;padding:24px;margin-bottom:20px}
.card.ok{border-color:#22c55e}
.card.err{border-color:#dc2626}
h2{font-size:16px;margin-bottom:16px;color:#fff}
label{display:block;font-size:12px;color:#999;margin-bottom:6px;margin-top:14px}
input{width:100%;background:#111;border:1px solid #333;border-radius:8px;padding:10px 14px;color:#fff;font-family:monospace;font-size:14px;outline:none}
input:focus{border-color:#dc2626}
.btn{background:#dc2626;color:white;border:none;padding:12px 28px;border-radius:8px;font-size:14px;font-weight:700;cursor:pointer;margin-top:20px;width:100%}
.alert{padding:12px 16px;border-radius:8px;margin-bottom:16px;font-size:13px}
.alert.ok{background:#003a00;border:1px solid #22c55e;color:#86efac}
.alert.fail{background:#2a0000;border:1px solid #dc2626;color:#fca5a5}
.badge{display:inline-block;padding:2px 10px;border-radius:20px;font-size:11px;font-weight:700}
.badge.ok{background:#003a00;color:#22c55e}
.badge.fail{background:#2a0000;color:#dc2626}
code{background:#222;padding:2px 8px;border-radius:4px;color:#fcd34d}
.info-row{display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid #222;font-size:13px}
.info-row:last-child{border-bottom:none}
a.btn-link{display:inline-block;margin-top:16px;text-decoration:none;background:#222;color:#fff;padding:10px 20px;border-radius:8px;font-size:13px;border:1px solid #444}
a.btn-link:hover{background:#333}
</style>
</head>
<body>
<div class="wrap">
<h1>⚙️ REDLINE Setup</h1>
<div class="sub">Konfigurasi database dan cek sistem — <strong style="color:#dc2626">hapus file ini setelah selesai!</strong></div>

<?php if ($msg === 'success'): ?>
<div class="alert ok">✅ Koneksi berhasil! File koneksi.php telah diperbarui. <a href="index.php" style="color:#22c55e">→ Buka aplikasi</a></div>
<?php elseif (str_starts_with($msg, 'fail:')): ?>
<div class="alert fail">❌ Koneksi gagal: <?= htmlspecialchars(substr($msg, 5)) ?></div>
<?php endif; ?>

<!-- Auto detect -->
<div class="card <?= $detected ? 'ok' : 'err' ?>">
<h2>🔍 Auto-detect Database</h2>
<?php if ($detected): ?>
<div class="alert ok">✅ Koneksi ditemukan! Host: <code><?= $detected[0] ?></code> | DB: <code><?= $detected[1] ?></code> | User: <code><?= $detected[2] ?></code> | Pass: <code><?= $detected[3] === '' ? '(kosong)' : '***' ?></code></div>
<form method="POST">
  <input type="hidden" name="host" value="<?= $detected[0] ?>">
  <input type="hidden" name="db" value="<?= $detected[1] ?>">
  <input type="hidden" name="user" value="<?= $detected[2] ?>">
  <input type="hidden" name="pass" value="<?= $detected[3] ?>">
  <input type="hidden" name="save_config" value="1">
  <button class="btn" type="submit">✅ Pakai Konfigurasi Ini & Simpan</button>
</form>
<?php else: ?>
<div class="alert fail">❌ Tidak bisa auto-detect. Isi manual di bawah.</div>
<?php endif; ?>
</div>

<!-- Manual config -->
<div class="card">
<h2>🔧 Konfigurasi Manual</h2>
<form method="POST">
  <input type="hidden" name="save_config" value="1">
  <label>Host Database</label>
  <input name="host" value="localhost" placeholder="localhost">
  <label>Nama Database</label>
  <input name="db" value="redline_db" placeholder="redline_db">
  <label>Username MySQL</label>
  <input name="user" value="root" placeholder="root">
  <label>Password MySQL (kosongkan jika tidak ada)</label>
  <input name="pass" value="" placeholder="(biarkan kosong jika tidak ada password)">
  <button class="btn" type="submit">💾 Test & Simpan Koneksi</button>
</form>
</div>

<!-- System info -->
<div class="card">
<h2>📊 Info Sistem</h2>
<div class="info-row"><span>PHP Version</span><span><?= phpversion() ?></span></div>
<div class="info-row"><span>PDO MySQL</span><span class="badge <?= extension_loaded('pdo_mysql') ? 'ok' : 'fail' ?>"><?= extension_loaded('pdo_mysql') ? 'OK' : 'MISSING' ?></span></div>
<div class="info-row"><span>Session Status</span><span class="badge ok"><?= session_status() === PHP_SESSION_ACTIVE ? 'ACTIVE' : 'OK' ?></span></div>
<div class="info-row"><span>koneksi.php Writable</span><span class="badge <?= is_writable(__DIR__ . '/config/koneksi.php') ? 'ok' : 'fail' ?>"><?= is_writable(__DIR__ . '/config/koneksi.php') ? 'YES' : 'NO' ?></span></div>
<div class="info-row"><span>Server</span><span><?= $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown' ?></span></div>
</div>

<!-- SQL -->
<div class="card">
<h2>🗄️ Setup Database (Import SQL)</h2>
<p style="font-size:13px;color:#999;margin-bottom:12px">Import file <code>config/db_setup.sql</code> di phpMyAdmin untuk membuat semua tabel + akun default.</p>
<div class="alert ok">
  <strong>Default Login:</strong><br>
  Username: <code>admin</code> | Password: <code>password</code><br>
  Username: <code>operator</code> | Password: <code>password</code>
</div>
<a class="btn-link" href="index.php">→ Buka Aplikasi</a>
<a class="btn-link" href="pages/login.php">→ Langsung ke Login</a>
</div>

</div>
</body>
</html>
