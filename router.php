<?php
// router.php for PHP built-in web server
$rawUri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';

// Normalize path separators (\ to /)
$uri = str_replace('\\', '/', $rawUri);
$decodedUri = str_replace('\\', '/', rawurldecode($rawUri));

// Block any URI containing '/../' or '..' path traversal
if (str_contains($uri, '..') || str_contains($decodedUri, '..')) {
    http_response_code(403);
    echo "403 Forbidden: Path traversal detected.";
    exit;
}

// Block direct access to protected directories
$blocked = [
    '/cache/',
    '/config/',
    '/database/',
    '/includes/',
    '/backups/',
    '/.git/',
    '/scripts/',
    '/docs/',
];

$normalizedUri = '/' . ltrim($uri, '/');
$lowerUri = strtolower($normalizedUri);
$decodedLowerUri = strtolower('/' . ltrim($decodedUri, '/'));

foreach ($blocked as $b) {
    $bLower = strtolower($b);
    $bTrimmed = rtrim($bLower, '/');
    if (
        str_starts_with($lowerUri, $bLower) ||
        $lowerUri === $bTrimmed ||
        str_starts_with($decodedLowerUri, $bLower) ||
        $decodedLowerUri === $bTrimmed
    ) {
        http_response_code(403);
        echo "403 Forbidden: Direct access to protected directory denied.";
        exit;
    }
}

// Canonical / realpath checking
$baseDir = realpath(__DIR__);
$targetPath = realpath(__DIR__ . $normalizedUri);

if ($targetPath !== false) {
    $normTarget = str_replace('\\', '/', $targetPath);
    $normBase = str_replace('\\', '/', $baseDir);

    // Prevent traversing outside project root
    if (!str_starts_with($normTarget, $normBase)) {
        http_response_code(403);
        echo "403 Forbidden: Access denied.";
        exit;
    }

    // Check if canonical path falls inside any blocked directory
    foreach ($blocked as $b) {
        $blockedDir = realpath(__DIR__ . $b);
        if ($blockedDir !== false) {
            $normBlocked = str_replace('\\', '/', $blockedDir);
            if (str_starts_with($normTarget, $normBlocked)) {
                http_response_code(403);
                echo "403 Forbidden: Direct access to protected directory denied.";
                exit;
            }
        }
    }

    // Serve existing static files directly
    if ($normalizedUri !== '/' && !is_dir($targetPath)) {
        return false;
    }
}

// Default handler
include __DIR__ . '/index.php';

