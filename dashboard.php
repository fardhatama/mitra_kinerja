<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/data.php';
requireLogin();

$pdo = getDB();
$all = getAllMitraSummary($pdo);
$stats = getDashboardStats($all);
$tindakLanjut = getTindakLanjut($pdo, null, 5);

$pageTitle = 'Dashboard';
require __DIR__ . '/includes/header.php';

$pilotOnly = array_filter($all, function ($s) {
    return isset($s['mitra']['portofolio']) && $s['mitra']['portofolio'] === 'Pilot Utama';
});
?>

<!-- KPI Row -->
<div class="kpi-row">
    <div class="kpi-card">
        <div class="kpi-icon kpi-icon-blue"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"><rect x="5" y="3" width="14" height="18" rx="2"/><path d="M9 7h6M9 11h6M9 15h4"/></svg></div>
        <div class="kpi-data">
            <div class="kpi-number"><?= $stats['total'] ?></div>
            <div class="kpi-label">Total Naskah Kerja Sama</div>
            <div class="kpi-sub"><?= $stats['pilotCount'] ?> Naskah Pilot<br><?= $stats['total'] - $stats['pilotCount'] ?> Perjanjian Kerja Sama</div>
        </div>
    </div>
    <div class="kpi-card">
        <div class="kpi-icon kpi-icon-navy"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"><circle cx="9" cy="7" r="3.5"/><path d="M2 19c0-3.5 3-6 7-6s7 2.5 7 6"/><circle cx="16" cy="7" r="2.5"/><path d="M17 13c2.5 0 5 1.5 5 4.5"/></svg></div>
        <div class="kpi-data">
            <div class="kpi-number"><?= $stats['pilotCount'] ?></div>
            <div class="kpi-label">Naskah Pilot</div>
            <div class="kpi-progress"><div class="bar" style="width:<?= $stats['total'] > 0 ? round($stats['pilotCount']/$stats['total']*100) : 0 ?>%;background:#1e40af;"></div></div>
            <div class="kpi-sub"><?= $stats['total'] > 0 ? round($stats['pilotCount']/$stats['total']*100) : 0 ?>% dari total portofolio</div>
        </div>
    </div>
    <div class="kpi-card">
        <div class="kpi-icon kpi-icon-green"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M7 13l3.5 3.5L17 9"/></svg></div>
        <div class="kpi-data">
            <div class="kpi-number"><?= $stats['efektif'] ?></div>
            <div class="kpi-label">Efektif</div>
            <div class="kpi-progress"><div class="bar" style="width:<?= $stats['pilotCount'] > 0 ? round($stats['efektif']/$stats['pilotCount']*100) : 0 ?>%;background:#16a34a;"></div></div>
            <div class="kpi-sub"><?= $stats['pilotCount'] > 0 ? round($stats['efektif']/$stats['pilotCount']*100) : 0 ?>%</div>
        </div>
    </div>
    <div class="kpi-card">
        <div class="kpi-icon kpi-icon-yellow"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><circle cx="12" cy="12" r="9"/><path d="M12 7v5.5l3.5 2"/></svg></div>
        <div class="kpi-data">
            <div class="kpi-number"><?= $stats['perluPerhatian'] ?></div>
            <div class="kpi-label">Perlu Perhatian</div>
            <div class="kpi-progress"><div class="bar" style="width:<?= $stats['pilotCount'] > 0 ? round($stats['perluPerhatian']/$stats['pilotCount']*100) : 0 ?>%;background:#ca8a04;"></div></div>
            <div class="kpi-sub"><?= $stats['pilotCount'] > 0 ? round($stats['perluPerhatian']/$stats['pilotCount']*100) : 0 ?>%</div>
        </div>
    </div>
    <div class="kpi-card">
        <div class="kpi-icon kpi-icon-red"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2L2 20h20L12 2z"/><path d="M12 10v4M12 17v.5"/></svg></div>
        <div class="kpi-data">
            <div class="kpi-number"><?= $stats['berisiko'] ?></div>
            <div class="kpi-label">Berisiko</div>
            <div class="kpi-progress"><div class="bar" style="width:<?= $stats['pilotCount'] > 0 ? round($stats['berisiko']/$stats['pilotCount']*100) : 0 ?>%;background:#dc2626;"></div></div>
            <div class="kpi-sub"><?= $stats['pilotCount'] > 0 ? round($stats['berisiko']/$stats['pilotCount']*100) : 0 ?>%</div>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="charts-row">
    <div class="chart-card">
        <h3>Status Efektivitas Naskah Pilot</h3>
        <div class="donut-wrap">
            <canvas id="donutChart" width="160" height="160"></canvas>
            <div class="donut-legend">
                <div class="leg-item"><span class="leg-dot" style="background:#16a34a;"></span> Efektif <span class="leg-count"><?= $stats['efektif'] ?></span> <span class="leg-pct"><?= $stats['pilotCount'] > 0 ? round($stats['efektif']/$stats['pilotCount']*100) : 0 ?>%</span></div>
                <div class="leg-item"><span class="leg-dot" style="background:#ca8a04;"></span> Perlu Perhatian <span class="leg-count"><?= $stats['perluPerhatian'] ?></span> <span class="leg-pct"><?= $stats['pilotCount'] > 0 ? round($stats['perluPerhatian']/$stats['pilotCount']*100) : 0 ?>%</span></div>
                <div class="leg-item"><span class="leg-dot" style="background:#dc2626;"></span> Berisiko <span class="leg-count"><?= $stats['berisiko'] ?></span> <span class="leg-pct"><?= $stats['pilotCount'] > 0 ? round($stats['berisiko']/$stats['pilotCount']*100) : 0 ?>%</span></div>
            </div>
        </div>
    </div>
    <div class="chart-card">
        <h3>Nilai Rata-rata Scorecard</h3>
        <div class="gauge-wrap">
            <canvas id="gaugeChart" width="220" height="130"></canvas>
            <div class="gauge-center"><?= number_format($stats['rataRataNilai'], 1, ',', '.') ?></div>
            <div class="gauge-subtitle"><?= $stats['rataRataNilai'] >= 75 ? 'Baik' : 'Perlu Perbaikan' ?></div>
        </div>
    </div>
    <div class="chart-card">
        <h3>Sebaran Nilai per Aspek</h3>
        <?php foreach (ASPEK_LABELS as $kode => $label): $val = $stats['aspekRataRata'][$kode] ?? 0; ?>
        <div class="hbar-item">
            <span class="hbar-label"><?= $label ?></span>
            <div class="hbar-track"><div class="hbar-fill" style="width:<?= $val ?>%;background:linear-gradient(90deg,#2563eb,#60a5fa);"></div></div>
            <span class="hbar-val"><?= $val ?></span>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Tables Row -->
<div class="dash-tables">
    <div class="dash-table-card">
        <div class="dtc-head"><h3>Daftar Naskah Pilot</h3><a href="portofolio.php">Lihat Semua &rarr;</a></div>
        <div class="table-wrap">
        <table>
            <thead><tr><th>No</th><th>Nama Naskah</th><th>Mitra</th><th>Berlaku s.d.</th><th>Nilai</th><th>Status</th><th>Aksi</th></tr></thead>
            <tbody>
            <?php $pilot5 = array_slice($pilotOnly, 0, 5);
            $no = 0; foreach ($pilot5 as $s): $m = $s['mitra']; $no++; $ef = statusEfektivitas($s['kategori']); ?>
                <tr>
                    <td><?= $no ?></td>
                    <td><?= h(singkat($m['judul'] ?? '-', 40)) ?></td>
                    <td><?= h($m['nama_mitra']) ?></td>
                    <td><?= formatTanggal($m['tanggal_berakhir']) ?></td>
                    <td><strong><?= $s['nilai_berjalan'] > 0 ? number_format($s['nilai_berjalan'], 0) : '-' ?></strong></td>
                    <td><span class="badge badge-<?= warnaEfektivitas($ef) ?>"><?= $ef ?></span></td>
                    <td><a href="mitra_edit.php?id=<?= $m['id'] ?>" class="btn btn-outline btn-sm">Detail</a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    </div>
    <div class="dash-table-card">
        <div class="dtc-head"><h3>Tindak Lanjut (5 Terbaru)</h3><a href="tindak_lanjut.php">Lihat Semua &rarr;</a></div>
        <div class="table-wrap">
        <table>
            <thead><tr><th>Naskah</th><th>Tindakan</th><th>Tenggat</th><th>Status</th></tr></thead>
            <tbody>
            <?php if (empty($tindakLanjut)): ?>
                <tr><td colspan="4" class="muted" style="text-align:center;padding:20px;">Belum ada tindak lanjut</td></tr>
            <?php else: foreach ($tindakLanjut as $tl):
                $dotClass = ($tl['status'] === 'Selesai')
            ? 'dot-green'
            : (($tl['status'] === 'Proses') ? 'dot-yellow' : 'dot-red');
            ?>
                <tr>
                    <td><?= h($tl['kode']) ?></td>
                    <td><?= h(singkat($tl['tindakan'], 40)) ?></td>
                    <td><?= formatTanggal($tl['tenggat']) ?></td>
                    <td><span class="status-dot <?= $dotClass ?>"><?= h($tl['status']) ?></span></td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<?php
$arrowSvg = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>';
$acIcons = [
    'baseline' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><rect x="5" y="3" width="14" height="18" rx="2"/><path d="M9 7h6M9 11h6M9 15h4"/></svg>',
    'scorecard' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="13" width="3.5" height="7" rx=".5"/><rect x="10" y="9" width="3.5" height="11" rx=".5"/><rect x="16" y="5" width="3.5" height="15" rx=".5"/></svg>',
    'early_warning' => '<svg viewBox="0 0 24 24" fill="currentColor" stroke="none"><path d="M12 2a6 6 0 0 1 6 6c0 4 1.8 5.4 2.4 6H3.6C4.2 13.4 6 12 6 8a6 6 0 0 1 6-6z"/><path d="M9.8 17a2.2 2.2 0 0 0 4.4 0z"/></svg>',
    'tindak_lanjut' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="4" width="16" height="16" rx="3"/><path d="M8.5 12.5l3 3 5-6"/></svg>',
];
?>
<div class="actions-row">
    <a href="baseline.php" class="action-card ac-blue">
        <div class="ac-icon"><?= $acIcons['baseline'] ?></div>
        <div class="ac-text"><h4>Input / Update Baseline</h4><p>Data naskah, PIC, rencana, dan eviden</p></div>
        <div class="ac-arrow"><?= $arrowSvg ?></div>
    </a>
    <a href="scorecard.php" class="action-card ac-gold">
        <div class="ac-icon"><?= $acIcons['scorecard'] ?></div>
        <div class="ac-text"><h4>Lihat Scorecard</h4><p>Penilaian efektivitas berbasis bukti</p></div>
        <div class="ac-arrow"><?= $arrowSvg ?></div>
    </a>
    <a href="early_warning.php" class="action-card ac-navy">
        <div class="ac-icon"><?= $acIcons['early_warning'] ?></div>
        <div class="ac-text"><h4>Cek Early Warning</h4><p>Naskah yang perlu perhatian</p></div>
        <div class="ac-arrow"><?= $arrowSvg ?></div>
    </a>
    <a href="tindak_lanjut.php" class="action-card ac-gold">
        <div class="ac-icon"><?= $acIcons['tindak_lanjut'] ?></div>
        <div class="ac-text"><h4>Kelola Tindak Lanjut</h4><p>Pantau progres perbaikan</p></div>
        <div class="ac-arrow"><?= $arrowSvg ?></div>
    </a>
</div>

<!-- AI Floating Toast -->
<div id="aiToast" class="ai-toast" style="display:none">
    <div class="ai-toast-header">
        <div class="ai-header-left">
            <span class="ai-sparkle">✨</span>
            <span class="ai-title">Analisis AI</span>
            <span class="ai-provider"></span>
        </div>
        <div class="ai-header-right">
            <span class="ai-timestamp"></span>
            <button class="ai-refresh" title="Analisis Ulang" onclick="refreshAI()">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M1 4v6h6M23 20v-6h-6"/><path d="M20.49 9A9 9 0 0 0 5.64 5.64L1 10m22 4l-4.64 4.36A9 9 0 0 1 3.51 15"/></svg>
            </button>
            <button class="ai-close" title="Tutup" onclick="closeAIToast()">×</button>
        </div>
    </div>
    <div class="ai-toast-body">
        <div class="ai-loading" id="aiLoading">
            <div class="ai-spinner"></div>
            <span>Menganalisis data portofolio...</span>
        </div>
        <div class="ai-content" id="aiContent" style="display:none"></div>
        <div class="ai-error" id="aiError" style="display:none">
            <span class="ai-error-icon">⚠️</span>
            <span class="ai-error-msg"></span>
        </div>
    </div>
</div>

<button id="aiTriggerBtn" class="ai-trigger-btn" onclick="showAIToast()" title="Analisis AI">
    <img src="/public/img/logo.png" alt="Kementerian Hukum">
</button>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
<script>
(function(){
    var ctx1 = document.getElementById('donutChart').getContext('2d');
    new Chart(ctx1, { type:'doughnut', data:{ labels:['Efektif','Perlu Perhatian','Berisiko'], datasets:[{ data:[<?= $stats['efektif'] ?>,<?= $stats['perluPerhatian'] ?>,<?= $stats['berisiko'] ?>], backgroundColor:['#16a34a','#ca8a04','#dc2626'], borderWidth:0, hoverOffset:6 }] }, options:{ cutout:'62%', plugins:{ legend:{display:false} }, responsive:false } });

    var ctx2 = document.getElementById('gaugeChart').getContext('2d');
    var val = <?= $stats['rataRataNilai'] ?>;
    var clr = val >= 75 ? '#16a34a' : (val >= 50 ? '#ca8a04' : '#dc2626');
    new Chart(ctx2, { type:'doughnut', data:{ labels:['Skor',''], datasets:[{ data:[val, 100-val], backgroundColor:[clr,'#e9ecef'], borderWidth:0 }] }, options:{ rotation:-90, circumference:180, cutout:'72%', plugins:{ legend:{display:false}, tooltip:{enabled:false} }, responsive:false } });
})();

/* ── AI Insight Floating Toast ─────────────────────────── */
var aiToastEl   = document.getElementById('aiToast');
var aiLoadingEl = document.getElementById('aiLoading');
var aiContentEl = document.getElementById('aiContent');
var aiErrorEl   = document.getElementById('aiError');

function showAIToast(){
    aiToastEl.style.display='flex';
    document.getElementById('aiTriggerBtn').style.display='none';
    aiLoadingEl.style.display='flex';
    aiContentEl.style.display='none';
    aiErrorEl.style.display='none';
    fetchInsight(false);
}
function closeAIToast(){
    aiToastEl.style.display='none';
    document.getElementById('aiTriggerBtn').style.display='flex';
    try{ localStorage.setItem('ai_dismissed','1'); }catch(e){}
}
function refreshAI(){
    aiLoadingEl.style.display='flex';
    aiContentEl.style.display='none';
    aiErrorEl.style.display='none';
    fetchInsight(true);
}
function fetchInsight(force){
    var opts = { headers:{'Accept':'application/json'} };
    if(force){ opts.method='POST'; }
    fetch('ai_insight.php', opts)
        .then(function(r){ return r.json(); })
        .then(function(d){
            aiLoadingEl.style.display='none';
            if(d.success){
                aiContentEl.style.display='block';
                aiContentEl.innerHTML = formatInsight(d.insight);
                var ts = d.generated_at + (d.cached ? ' (cache)' : '');
                document.querySelector('.ai-timestamp').textContent = ts;
                // Tampilkan provider badge
                var providerEl = document.querySelector('.ai-provider');
                if(providerEl){
                    var prov = d.provider || '';
                    var provClass = 'ai-prov-' + prov.toLowerCase();
                    providerEl.innerHTML = prov ? '<span class="ai-provider-badge '+provClass+'">'+prov+'</span>' : '';
                }
                try{ localStorage.setItem('ai_dismissed',''); }catch(e){}
            } else {
                var msg = d.message || 'Terjadi kesalahan';
                if(d.detail) msg += '<br><small>' + d.detail + '</small>';
                msg += '<br><small style="color:#64748b">Provider: Gemini → OpenRouter → Groq</small>';
                if(d.debug_url) msg += '<br><a href="' + d.debug_url + '" target="_blank" style="font-size:11px;color:#2563eb">🔍 Buka Diagnostic Tool</a>';
                showError(msg);
            }
        })
        .catch(function(e){
            aiLoadingEl.style.display='none';
            showError('Tidak dapat terhubung ke server AI.');
        });
}
function showError(msg){
    aiErrorEl.style.display='flex';
    aiErrorEl.querySelector('.ai-error-msg').innerHTML = msg;
}
function formatInsight(text){
    // Convert markdown-like bold **text** to <strong>
    text = text.replace(/\*\*(.+?)\*\*/g,'<strong>$1</strong>');
    // Split into paragraphs
    var paras = text.split(/\n\n|\n/).filter(function(p){ return p.trim(); });
    return paras.map(function(p){ return '<p>'+p+'</p>'; }).join('');
}

// Auto-show on load if not dismissed in this session
document.addEventListener('DOMContentLoaded', function(){
    var dismissed = false;
    try { dismissed = localStorage.getItem('ai_dismissed') === '1'; } catch(e){}
    if(!dismissed){
        setTimeout(showAIToast, 800);
    } else {
        // Already dismissed — show trigger button
        document.getElementById('aiTriggerBtn').style.display='flex';
    }
});
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>
