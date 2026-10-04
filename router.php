<?php
// router.php for PHP built-in web server
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Block direct access to protected directories
$blocked = ['/cache/', '/config/', '/database/', '/includes/'];
foreach ($blocked as $b) {
    if (str_starts_with($uri, $b)) {
        http_response_code(403);
        echo "403 Forbidden: Direct access to protected directory denied.";
        exit;
    }
}

// Serve existing static files directly
$filePath = __DIR__ . $uri;
if ($uri !== '/' && file_exists($filePath) && !is_dir($filePath)) {
    return false;
}

// Default handler
include __DIR__ . '/index.php';
