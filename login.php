<?php
require_once __DIR__ . '/includes/auth.php';

if (currentUser()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    if ($username === '' || $password === '') {
        $error = 'Username dan password wajib diisi.';
    } elseif (attemptLogin($username, $password)) {
        header('Location: dashboard.php');
        exit;
    } else {
        $error = 'Username atau password salah.';
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
            <label for="username">Username</label>
            <input type="text" id="username" name="username" autofocus required>
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
            <button type="submit">Masuk</button>
        </form>
        
        <div style="margin-top:16px;padding:10px 12px;background:#f8fafc;border:1px dashed #cbd5e1;border-radius:6px;font-size:12px;color:#475569;">
            <div style="font-weight:600;margin-bottom:6px;color:#1e293b;">Akun Demo (Klik untuk isi):</div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:6px;">
                <button type="button" onclick="fillLogin('admin','admin123')" style="cursor:pointer;padding:5px;background:#fff;border:1px solid #cbd5e1;border-radius:4px;font-size:11px;">Admin</button>
                <button type="button" onclick="fillLogin('pemeriksa','pemeriksa123')" style="cursor:pointer;padding:5px;background:#fff;border:1px solid #cbd5e1;border-radius:4px;font-size:11px;">Pemeriksa</button>
                <button type="button" onclick="fillLogin('validator','validator123')" style="cursor:pointer;padding:5px;background:#fff;border:1px solid #cbd5e1;border-radius:4px;font-size:11px;">Validator</button>
                <button type="button" onclick="fillLogin('pimpinan','pimpinan123')" style="cursor:pointer;padding:5px;background:#fff;border:1px solid #cbd5e1;border-radius:4px;font-size:11px;">Pimpinan</button>
            </div>
        </div>

        <div class="login-hint" style="margin-top:10px;">Hubungi admin jika lupa kata sandi.</div>
    </div>
</div>
<script>
function fillLogin(u, p) {
    document.getElementById('username').value = u;
    document.getElementById('password').value = p;
}
</script>
</body>
</html>
