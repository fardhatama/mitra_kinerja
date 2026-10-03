<?php
require_once __DIR__ . '/includes/auth.php';

// Safe logout: check CSRF token if request was POST
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $token = isset($_POST['csrf_token']) && is_string($_POST['csrf_token']) ? $_POST['csrf_token'] : null;
    if ($token === null || !verifyCsrfToken($token)) {
        http_response_code(403);
        die('Invalid CSRF token');
    }
}

doLogout();
header('Location: login.php');
exit;
