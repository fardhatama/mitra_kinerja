<?php
/**
 * Raw cURL Test untuk Gemini + Groq API
 * HAPUS SETELAH DEBUG!
 */
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/ai_config.php';
requireLogin();
requireRole(['admin']);

header('Content-Type: application/json; charset=utf-8');

$result = [
    'gemini' => testGemini(),
    'openrouter' => testOpenRouter(),
    'groq' => testGroq(),
];
$result['success'] = ($result['gemini']['success'] || $result['openrouter']['success'] || $result['groq']['success']);

echo json_encode($result, JSON_PRETTY_PRINT);

function testGemini(): array {
    $r = ['configured' => false, 'success' => false];
    
    $key = GEMINI_API_KEY;
    $r['configured'] = ($key !== '' && $key !== 'ISI_API_KEY_ANDA_DI_SINI');
    $r['key_preview'] = $r['configured'] ? substr($key, 0, 8) . '...' : 'NOT SET';
    
    if (!$r['configured'] || !function_exists('curl_init')) {
        $r['reason'] = !$r['configured'] ? 'API key belum diisi' : 'cURL tidak tersedia';
        return $r;
    }
    
    $url = GEMINI_API_URL . GEMINI_MODEL . ':generateContent?key=' . $key;
    $payload = json_encode([
        'contents' => [['parts' => [['text' => 'Katakan "Halo, tes berhasil" dalam satu kalimat singkat.']]]],
        'generationConfig' => ['temperature' => 0.5, 'maxOutputTokens' => 100]
    ]);
    
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $payload,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_SSL_VERIFYPEER => true,
    ]);
    $response = curl_exec($ch);
    $r['http_code'] = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $r['curl_error'] = curl_error($ch);
    curl_close($ch);
    
    if ($r['http_code'] === 200) {
        $data = json_decode($response, true);
        if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
            $r['success'] = true;
            $r['ai_response'] = $data['candidates'][0]['content']['parts'][0]['text'];
        } else {
            $r['error'] = 'No text in response';
            $r['response'] = substr($response, 0, 300);
        }
    } else {
        $r['error'] = 'HTTP ' . $r['http_code'];
        $r['response'] = substr($response, 0, 500);
    }
    
    return $r;
}

function testGroq(): array {
    $r = ['configured' => false, 'success' => false];
    
    $r['configured'] = (GROQ_API_KEY !== '');
    $r['key_preview'] = $r['configured'] ? substr(GROQ_API_KEY, 0, 8) . '...' : 'NOT SET';
    
    if (!$r['configured'] || !function_exists('curl_init')) {
        $r['reason'] = !$r['configured'] ? 'API key belum diisi (opsional, pakai free credit)' : 'cURL tidak tersedia';
        return $r;
    }
    
    $payload = json_encode([
        'model' => GROQ_MODEL,
        'messages' => [['role' => 'user', 'content' => 'Katakan "Halo, tes berhasil" dalam satu kalimat singkat.']],
        'temperature' => 0.5,
        'max_tokens' => 100,
    ]);
    
    $ch = curl_init(GROQ_API_URL);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $payload,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . GROQ_API_KEY,
        ],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_SSL_VERIFYPEER => true,
    ]);
    $response = curl_exec($ch);
    $r['http_code'] = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $r['curl_error'] = curl_error($ch);
    curl_close($ch);
    
    if ($r['http_code'] === 200) {
        $data = json_decode($response, true);
        if (isset($data['choices'][0]['message']['content'])) {
            $r['success'] = true;
            $r['ai_response'] = $data['choices'][0]['message']['content'];
        } else {
            $r['error'] = 'No text in response';
            $r['response'] = substr($response, 0, 300);
        }
    } else {
        $r['error'] = 'HTTP ' . $r['http_code'];
        $r['response'] = substr($response, 0, 500);
    }
    
    return $r;
}

function testOpenRouter(): array {
    $r = ['configured' => false, 'success' => false];
    
    $r['configured'] = (OPENROUTER_API_KEY !== '');
    $r['key_preview'] = $r['configured'] ? substr(OPENROUTER_API_KEY, 0, 8) . '...' : 'NOT SET';
    
    if (!$r['configured'] || !function_exists('curl_init')) {
        $r['reason'] = !$r['configured'] ? 'API key belum diisi' : 'cURL tidak tersedia';
        return $r;
    }
    
    $payload = json_encode([
        'model' => OPENROUTER_MODEL,
        'messages' => [['role' => 'user', 'content' => 'Katakan "Halo, tes berhasil" dalam satu kalimat singkat.']],
        'temperature' => 0.5,
        'max_tokens' => 100,
    ]);
    
    $ch = curl_init(OPENROUTER_API_URL);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $payload,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . OPENROUTER_API_KEY,
            'HTTP-Referer: https://mitra-kinerja.local',
            'X-Title: Mitra Kinerja Debug Test',
        ],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 45,
        CURLOPT_SSL_VERIFYPEER => true,
    ]);
    $response = curl_exec($ch);
    $r['http_code'] = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $r['curl_error'] = curl_error($ch);
    curl_close($ch);
    
    if ($r['http_code'] === 200) {
        $data = json_decode($response, true);
        if (isset($data['choices'][0]['message']['content'])) {
            $r['success'] = true;
            $r['ai_response'] = $data['choices'][0]['message']['content'];
        } else {
            $r['error'] = 'No text in response';
            $r['response'] = substr($response, 0, 300);
        }
    } else {
        $r['error'] = 'HTTP ' . $r['http_code'];
        $r['response'] = substr($response, 0, 500);
    }
    
    return $r;
}
