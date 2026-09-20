<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/data.php';
requireLogin();

$pdo = getDB();
$all = getAllMitraSummary($pdo);
$stats = getDashboardStats($all);
$tindakLanjut = getTindakLanjut($pdo);

$pageTitle = 'Laporan';
require __DIR__ . '/includes/header.php';
?>

<div class="flex-between" style="margin-bottom:18px;">
    <div>
        <h1 style="margin:0;font-size:20px;">Laporan — Ringkasan Eksekutif</h1>
        <div class="muted" style="font-size:13px;">Rekap seluruh data mitra kerja sama</div>
    </div>
    <a href="dashboard.php" class="btn btn-outline btn-sm">&larr; Dashboard</a>
</div>

<!-- Ringkasan Statistik -->
<div class="kpi-row" style="grid-template-columns: repeat(4, 1fr);">
    <div class="kpi-card"><div class="kpi-icon kpi-icon-blue">📄</div><div><div class="kpi-number"><?= $stats['total'] ?></div><div class="kpi-label">Total Naskah</div></div></div>
    <div class="kpi-card"><div class="kpi-icon kpi-icon-green">✅</div><div><div class="kpi-number"><?= $stats['efektif'] ?></div><div class="kpi-label">Efektif</div></div></div>
    <div class="kpi-card"><div class="kpi-icon kpi-icon-yellow">⚠️</div><div><div class="kpi-number"><?= $stats['perluPerhatian'] ?></div><div class="kpi-label">Perlu Perhatian</div></div></div>
    <div class="kpi-card"><div class="kpi-icon kpi-icon-red">🔴</div><div><div class="kpi-number"><?= $stats['berisiko'] ?></div><div class="kpi-label">Berisiko</div></div></div>
</div>

<!-- Rekap per Naskah -->
<div class="card">
    <h2>Rekap Semua Naskah</h2>
    <div class="table-wrap">
    <table>
        <thead>
            <tr><th>Kode</th><th>Portofolio</th><th>Mitra</th><th>Jenis</th><th>Berakhir</th><th>Nilai</th><th>Kategori</th><th>Warning</th><th>Status</th></tr>
        </thead>
        <tbody>
        <?php foreach ($all as $s): $m = $s['mitra']; ?>
            <tr>
                <td><strong><?= h($m['kode']) ?></strong></td>
                <td><?= h($m['portofolio']) ?></td>
                <td><?= h($m['nama_mitra']) ?></td>
                <td><?= h($m['jenis']) ?></td>
                <td><?= formatTanggal($m['tanggal_berakhir']) ?></td>
                <td><?= $s['nilai_berjalan'] > 0 ? number_format($s['nilai_berjalan'], 2) : '-' ?></td>
                <td><span class="badge badge-<?= warnaKategori($s['kategori']) ?>"><?= h($s['kategori']) ?></span></td>
                <td><span class="badge badge-<?= warnaWarning($s['warning']['status']) ?>"><?= h($s['warning']['label']) ?></span></td>
                <td><?= h($s['status_scorecard']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
</div>

<!-- Rata-rata per Aspek -->
<div class="card">
    <h2>Rata-rata Nilai per Aspek</h2>
    <div class="table-wrap">
    <table>
        <thead><tr><th>Aspek</th><th>Rata-rata Nilai</th><th>Persentase</th></tr></thead>
        <tbody>
        <?php foreach (ASPEK_LABELS as $kode => $label):
            $val = $stats['aspekRataRata'][$kode] ?? 0;
            $maxScore = BOBOT_INDIKATOR[$kode];
            $pct = $maxScore > 0 ? round($val / $maxScore * 100) : 0;
        ?>
            <tr>
                <td><strong><?= $label ?></strong> (<?= h($kode) ?>)</td>
                <td><?= $val ?> / <?= $maxScore ?></td>
                <td>
                    <div class="hbar-track" style="display:inline-block;vertical-align:middle;width:120px;height:14px;">
                        <div class="hbar-fill" style="width:<?= $pct ?>%;background:linear-gradient(90deg,#2563eb,#60a5fa);"></div>
                    </div>
                    <span style="margin-left:8px;font-weight:600;font-size:12px;"><?= $pct ?>%</span>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
</div>

<!-- Tindak Lanjut -->
<div class="card">
    <h2>Semua Tindak Lanjut (<?= count($tindakLanjut) ?>)</h2>
    <div class="table-wrap">
    <table>
        <thead><tr><th>Naskah</th><th>Tindakan</th><th>Tenggat</th><th>Status</th></tr></thead>
        <tbody>
        <?php if (empty($tindakLanjut)): ?>
            <tr><td colspan="4" class="muted" style="text-align:center;padding:20px;">Belum ada tindak lanjut</td></tr>
        <?php else: foreach ($tindakLanjut as $tl):
            $dotClass = match($tl['status']) { 'Selesai' => 'dot-green', 'Proses' => 'dot-yellow', default => 'dot-red' };
        ?>
            <tr>
                <td><strong><?= h($tl['kode']) ?></strong></td>
                <td><?= h($tl['tindakan']) ?></td>
                <td><?= formatTanggal($tl['tenggat']) ?></td>
                <td><span class="status-dot <?= $dotClass ?>"><?= h($tl['status']) ?></span></td>
            </tr>
        <?php endforeach; endif; ?>
        </tbody>
    </table>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>