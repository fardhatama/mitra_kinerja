<?php
/**
 * AJAX Endpoint untuk AI Insight
 * 
 * GET  → Cek cache, return cached insight
 * POST → Force regenerate, panggil Gemini API
 */

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/data.php';
require_once __DIR__ . '/includes/ai_service.php';
requireLogin();

$user = currentUser();

// Set timezone ke WIB
date_default_timezone_set('Asia/Jakarta');

header('Content-Type: application/json; charset=utf-8');

$forceRefresh = (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST');

if ($forceRefresh) {
    $csrfToken = $_POST['csrf_token'] 
        ?? $_SERVER['HTTP_X_CSRF_TOKEN'] 
        ?? null;
    if (!$csrfToken) {
        $jsonInput = json_decode(file_get_contents('php://input'), true);
        if (is_array($jsonInput) && isset($jsonInput['csrf_token']) && is_string($jsonInput['csrf_token'])) {
            $csrfToken = $jsonInput['csrf_token'];
        }
    }

    if (!$csrfToken || !verifyCsrfToken($csrfToken)) {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'error' => 'CSRF_INVALID',
            'message' => 'Token CSRF tidak valid atau telah kedaluwarsa. Silakan muat ulang halaman.'
        ]);
        exit;
    }

    // Cooldown / throttle: minimal 5 detik jeda antar force-refresh
    $now = time();
    $lastRefresh = (int)($_SESSION['ai_last_refresh'] ?? 0);
    if (($now - $lastRefresh) < 5) {
        http_response_code(429);
        echo json_encode([
            'success' => false,
            'error' => 'RATE_LIMITED',
            'message' => 'Terlalu sering merefresh. Harap tunggu minimal 5 detik sebelum mencoba lagi.'
        ]);
        exit;
    }
    $_SESSION['ai_last_refresh'] = $now;
}

if (!defined('AI_ENABLED') || !AI_ENABLED) {
    echo json_encode([
        'success' => false,
        'error' => 'AI_DISABLED',
        'message' => 'Fitur AI Insight dinonaktifkan sementara.'
    ]);
    exit;
}

// Cek apakah minimal SATU API key sudah dikonfigurasi
$geminiOk = (GEMINI_API_KEY !== '' && GEMINI_API_KEY !== 'ISI_API_KEY_ANDA_DI_SINI');
$openrouterOk = (OPENROUTER_API_KEY !== '');
$groqOk = (GROQ_API_KEY !== '');

if (!$geminiOk && !$openrouterOk && !$groqOk) {
    echo json_encode([
        'success' => false,
        'error' => 'API_KEY_MISSING',
        'message' => 'Silakan konfigurasi minimal satu API Key (Gemini, OpenRouter, atau Groq) di includes/ai_config.php'
    ]);
    exit;
}

// Coba ambil dari cache dulu SEBELUM query database yang berat
if (!$forceRefresh) {
    $cached = getCachedInsight();
    if ($cached && isset($cached['insight'])) {
        echo json_encode([
            'success' => true,
            'cached' => true,
            'insight' => $cached['insight'],
            'provider' => $cached['provider'] ?? 'Cache',
            'generated_at' => date('d M Y H:i', $cached['timestamp']),
            'expires_in' => AI_CACHE_TTL - (time() - $cached['timestamp']),
        ]);
        exit;
    }
}

if (!function_exists('curl_init')) {
    $errorResp = [
        'success' => false,
        'error' => 'CURL_MISSING',
        'message' => 'Gagal mengambil analisis dari AI.',
        'detail' => 'cURL extension tidak tersedia di server.',
    ];
    if (($user['role'] ?? '') === 'admin') {
        $errorResp['debug_url'] = 'ai_debug.php';
    }
    echo json_encode($errorResp);
    exit;
}

// Ambil data dari database untuk generate payload
$pdo = getDB();
$all = getAllMitraSummary($pdo);
$stats = getDashboardStats($all);
$payload = generateInsightPayload($pdo, $all, $stats);

// Lepas session lock sebelum external AI network call
session_write_close();

// Panggil AI — triple fallback: Gemini → OpenRouter → Groq
$aiResult = generateNewInsight($payload);

if ($aiResult) {
    // $aiResult = ['text' => '...', 'provider' => 'Gemini']
    saveInsightCache($payload, $aiResult['text'], $aiResult['provider']);
    
    echo json_encode([
        'success' => true,
        'cached' => false,
        'insight' => $aiResult['text'],
        'provider' => $aiResult['provider'],
        'generated_at' => date('d M Y H:i'),
        'expires_in' => AI_CACHE_TTL,
    ]);
} else {
    $errorDetail = 'Semua provider gagal (Gemini, OpenRouter, Groq).';
    
    $errorResp = [
        'success' => false,
        'error' => 'API_ERROR',
        'message' => 'Gagal mengambil analisis dari AI.',
        'detail' => $errorDetail,
    ];
    if (($user['role'] ?? '') === 'admin') {
        $errorResp['debug_url'] = 'ai_debug.php';
    }
    echo json_encode($errorResp);
}
