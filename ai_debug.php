<?php
/**
 * Diagnostic Tool - AI Insight Debug
 * HAPUS SETELAH SELESAI DEBUG!
 */
require_once __DIR__ . '/includes/auth.php';
requireLogin();
$user = currentUser();
if ($user['role'] !== 'admin') {
    http_response_code(403);
    die('Akses ditolak: role Anda (' . htmlspecialchars($user['role']) . ') tidak memiliki izin untuk mengakses halaman ini.');
}
require_once __DIR__ . '/includes/ai_config.php';
?>
<!DOCTYPE html><html><head><title>AI Debug</title>
<style>body{font-family:monospace;padding:20px;background:#f5f5f5}.c{padding:12px;margin:8px 0;background:#fff;border-radius:8px;border-left:4px solid #ccc}.p{border-color:#16a34a}.f{border-color:#dc2626}.w{border-color:#ca8a04}pre{background:#1e293b;color:#e2e8f0;padding:16px;border-radius:8px;overflow-x:auto;font-size:12px}</style>
</head><body>
<h1>🔍 AI Insight Debug</h1>

<h2>1. Environment</h2>
<div class="c <?= function_exists('curl_init')?'p':'f' ?>">
    cURL: <?= function_exists('curl_init')?'✅ OK':'❌ MISSING' ?>
</div>
<div class="c p">PHP: <?= phpversion() ?></div>

<h2>2. API Keys</h2>
<?php
$geminiActive = !empty(GEMINI_API_KEY) && GEMINI_API_KEY !== 'ISI_API_KEY_ANDA_DI_SINI';
$openrouterActive = !empty(OPENROUTER_API_KEY);
$groqActive = !empty(GROQ_API_KEY);
$anyProviderActive = $geminiActive || $openrouterActive || $groqActive;
?>
<div class="c <?= $geminiActive ? 'p' : 'w' ?>">
    Gemini: <?= $geminiActive ? '✅ ' . htmlspecialchars(substr(GEMINI_API_KEY, 0, 8)) . '...' : '❌ BELUM DIISI (Primary)' ?>
</div>
<div class="c <?= $openrouterActive ? 'p' : 'w' ?>">
    OpenRouter: <?= $openrouterActive ? '✅ ' . htmlspecialchars(substr(OPENROUTER_API_KEY, 0, 8)) . '...' : '❌ BELUM DIISI (Fallback #1)' ?>
</div>
<div class="c <?= $groqActive ? 'p' : 'w' ?>">
    Groq: <?= $groqActive ? '✅ ' . htmlspecialchars(substr(GROQ_API_KEY, 0, 8)) . '...' : '❌ BELUM DIISI (Fallback #2)' ?>
</div>

<h2>3. Cache</h2>
<?php $cd = dirname(AI_CACHE_FILE); ?>
<div class="c <?= is_dir($cd)?'p':'f' ?>">
    Folder cache/: <?= is_dir($cd)?'✅ Ada':'❌ Tidak ada' ?>
</div>
<div class="c <?= is_writable($cd)?'p':'f' ?>">
    Writable: <?= is_writable($cd)?'✅ Ya':'❌ Tidak' ?>
</div>

<?php if (function_exists('curl_init')): ?>
<h2>4. Test API</h2>
<button onclick="doTest()" style="padding:12px 24px;background:#2563eb;color:#fff;border:none;border-radius:8px;cursor:pointer">🚀 Test AI API</button>
<div id="res" style="margin-top:16px"></div>
<script>
function renderProviderBox(title, p) {
    if (!p) {
        return '<div class="c w"><strong>' + title + ':</strong> Tidak ada data respons</div>';
    }
    if (!p.configured) {
        return '<div class="c w"><strong>' + title + ':</strong> ⚪ Belum Dikonfigurasi (' + (p.reason || 'Key belum diisi') + ')</div>';
    }
    if (p.success) {
        var resp = p.ai_response ? '<pre>' + String(p.ai_response).replace(/</g, '&lt;') + '</pre>' : '';
        return '<div class="c p"><strong>' + title + ':</strong> ✅ BERHASIL (HTTP ' + (p.http_code || 200) + ')' + resp + '</div>';
    }
    var errText = (p.curl_error && (!p.error || p.error === 'HTTP 0')) 
        ? ('cURL Error: ' + p.curl_error) 
        : (p.error || (p.curl_error ? ('cURL Error: ' + p.curl_error) : ('HTTP ' + (p.http_code || 'Error'))));
    var extra = p.response ? '<pre>' + String(p.response).replace(/</g, '&lt;') + '</pre>' : '';
    return '<div class="c f"><strong>' + title + ':</strong> ❌ GAGAL (' + errText + ')' + extra + '</div>';
}

function doTest(){
    var el = document.getElementById('res');
    el.innerHTML = '<div class="c w">⏳ Menghubungi AI API (Gemini, OpenRouter, Groq)...</div>';
    fetch('ai_debug_raw.php').then(function(r){ return r.json(); }).then(function(d){
        var html = '';
        if (d.success) {
            html += '<div class="c p"><strong>✅ DIAGNOSTIK SUKSES:</strong> Minimal satu provider AI aktif dan berhasil merespons.</div>';
        } else {
            html += '<div class="c f"><strong>❌ DIAGNOSTIK GAGAL:</strong> Belum ada provider AI yang berhasil merespons.</div>';
        }
        html += renderProviderBox('1. Google Gemini (Primary)', d.gemini);
        html += renderProviderBox('2. OpenRouter (Fallback #1)', d.openrouter);
        html += renderProviderBox('3. Groq (Fallback #2)', d.groq);
        el.innerHTML = html;
    }).catch(function(e){
        el.innerHTML = '<div class="c f">Error: ' + e.message + '</div>';
    });
}
</script>
<?php endif; ?>

<hr><p><a href="dashboard.php">← Dashboard</a> | <strong style="color:red">⚠️ HAPUS file ini setelah debug!</strong></p>
</body></html>
