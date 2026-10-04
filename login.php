<?php
require_once __DIR__ . '/includes/auth.php';

if (currentUser()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $lockoutTime = 60; // 60 detik lockout
    $maxAttempts = 5;  // 5 kali percobaan
    $lockUntil = (int)($_SESSION['login_lockout'] ?? 0);
    $attempts = (int)($_SESSION['login_attempts'] ?? 0);

    if ($lockUntil > time()) {
        $remaining = $lockUntil - time();
        $error = "Terlalu banyak percobaan login gagal. Silakan tunggu {$remaining} detik.";
    } else {
        $token = isset($_POST['csrf_token']) && is_string($_POST['csrf_token']) ? $_POST['csrf_token'] : null;
        if ($token === null || !verifyCsrfToken($token)) {
            $error = 'Token keamanan tidak valid atau telah kedaluwarsa. Silakan muat ulang halaman.';
        } else {
            $rawUser = isset($_POST['username']) && is_string($_POST['username']) ? $_POST['username'] : '';
            $rawPass = isset($_POST['password']) && is_string($_POST['password']) ? $_POST['password'] : '';
            $username = trim($rawUser);
            $password = $rawPass;

            if ($username === '' || $password === '') {
                $error = 'Username dan password wajib diisi.';
            } elseif (attemptLogin($username, $password)) {
                unset($_SESSION['login_attempts'], $_SESSION['login_lockout']);
                header('Location: dashboard.php');
                exit;
            } else {
                $attempts++;
                if ($attempts >= $maxAttempts) {
                    $_SESSION['login_lockout'] = time() + $lockoutTime;
                    $_SESSION['login_attempts'] = 0;
                    $error = "Terlalu banyak percobaan login gagal. Akun dikunci sementara selama {$lockoutTime} detik.";
                } else {
                    $_SESSION['login_attempts'] = $attempts;
                    $sisa = $maxAttempts - $attempts;
                    $error = "Username atau password salah. (Sisa percobaan: {$sisa})";
                }
                logAudit(null, null, 'LOGIN_FAILED', 'Username: ' . $username);
            }
        }
    }
}
?><!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Masuk — Mitra Kinerja</title>
<link rel="stylesheet" href="public/css/style.css">
</head>
<body>
<div class="login-wrap">
    <div class="login-card">
        <div class="login-brand">
            <div class="logo-circle">MK</div>
            <h1>MITRA KINERJA</h1>
            <p>Monitoring &amp; Evaluasi Kerja Sama<br>Kanwil Kementerian Hukum Kepulauan Riau</p>
        </div>
        <?php if ($error): ?><div class="error-box"><?= h($error) ?></div><?php endif; ?>
        <form method="post">
            <?= csrfField() ?>
            <label for="username">Username</label>
            <input type="text" id="username" name="username" autofocus required>
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
            <button type="submit">Masuk</button>
        </form>
        
        <?php if (defined('APP_ENV') && APP_ENV === 'development'): ?>
        <div style="margin-top:16px;padding:12px;background:#f8fafc;border:1px dashed #cbd5e1;border-radius:8px;font-size:12px;color:#475569;">
            <div style="font-weight:700;margin-bottom:8px;color:#1e293b;display:flex;align-items:center;gap:6px;">
                <span>🔑</span> Akun Demo Pengujian (Klik untuk isi cepat):
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:6px;">
                <button type="button" class="btn-demo" onclick="fillLogin('admin','admin123')">Admin</button>
                <button type="button" class="btn-demo" onclick="fillLogin('pemeriksa','pemeriksa123')">Pemeriksa</button>
                <button type="button" class="btn-demo" onclick="fillLogin('pengampu','pengampu123')">Pengampu</button>
                <button type="button" class="btn-demo" onclick="fillLogin('pic','pic123')">PIC Kerja Sama</button>
                <button type="button" class="btn-demo" onclick="fillLogin('validator','validator123')">Validator</button>
                <button type="button" class="btn-demo" onclick="fillLogin('pimpinan','pimpinan123')">Pimpinan</button>
            </div>
        </div>
        <?php endif; ?>

        <div class="login-hint" style="margin-top:10px;">Hubungi admin jika lupa kata sandi.</div>
    </div>
</div>
<?php if (defined('APP_ENV') && APP_ENV === 'development'): ?>
<script>
function fillLogin(u, p) {
    document.getElementById('username').value = u;
    document.getElementById('password').value = p;
}
</script>
<?php endif; ?>
</body>
</html>
