<?php
/**
 * Autentikasi & otorisasi berbasis role.
 * Role: admin, pemeriksa, validator, pimpinan
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function currentUser(): ?array {
    return $_SESSION['user'] ?? null;
}

function requireLogin(): void {
    if (!currentUser()) {
        header('Location: login.php');
        exit;
    }
}

/**
 * Wajibkan salah satu role dari daftar yang diizinkan.
 * Contoh: requireRole(['admin','pemeriksa']);
 */
function requireRole(array $roles): void {
    requireLogin();
    $user = currentUser();
    if (!in_array($user['role'], $roles, true)) {
        http_response_code(403);
        die('Akses ditolak: role Anda (' . htmlspecialchars($user['role']) . ') tidak memiliki izin untuk halaman ini.');
    }
}

function attemptLogin(string $username, string $password): bool {
    $pdo = getDB();
    $stmt = $pdo->prepare('SELECT * FROM users WHERE username = ? AND aktif = 1 LIMIT 1');
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        unset($user['password_hash']);
        $_SESSION['user'] = $user;
        logAudit(null, $user['id'], 'LOGIN', 'Login berhasil');
        return true;
    }
    return false;
}

function doLogout(): void {
    $user = currentUser();
    if ($user) {
        logAudit(null, $user['id'], 'LOGOUT', 'Logout');
    }
    $_SESSION = [];
    session_destroy();
}

function logAudit(?int $mitraId, ?int $userId, string $aksi, string $detail = ''): void {
    try {
        $pdo = getDB();
        $stmt = $pdo->prepare('INSERT INTO audit_log (mitra_id, user_id, aksi, detail) VALUES (?, ?, ?, ?)');
        $stmt->execute([$mitraId, $userId, $aksi, $detail]);
    } catch (Throwable $e) {
        // Jangan sampai kegagalan audit log mengganggu alur utama aplikasi.
    }
}
