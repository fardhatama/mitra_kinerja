<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/data.php';
requireLogin();

$pdo = getDB();
$all = getAllMitraSummary($pdo);

// Collect early warning data
$warningData = [];
foreach ($all as $s) {
    $wLabel = $s['warning']['label'];
    $status = $s['warning']['status'];
    if ($status !== 'E0') {
        $warningData[] = [
            'kode' => $s['mitra']['kode'],
            'nama_mitra' => $s['mitra']['nama_mitra'],
            'warning_label' => $wLabel,
            'warning_status' => $status,
            'tingkat' => $s['warning']['tingkat_penanganan'],
            'hasil_uji' => $s['hasil_uji'],
            'rows' => $s['warning_rows'],
            'id' => $s['mitra']['id'],
        ];
    }
}
usort($warningData, fn($a, $b) => array_search($b['warning_status'], ['E3','E2','E1','V0']) <=> array_search($a['warning_status'], ['E3','E2','E1','V0']));

$pageTitle = 'Early Warning';
require __DIR__ . '/includes/header.php';
?>

<div class="flex-between" style="margin-bottom:18px;">
    <div>
        <h1 style="margin:0;font-size:20px;">Early Warning — Naskah Perlu Perhatian</h1>
        <div class="muted" style="font-size:13px;">Naskah dengan status warning selain E0 (Hijau)</div>
    </div>
    <a href="dashboard.php" class="btn btn-outline btn-sm">&larr; Dashboard</a>
</div>

<?php if (empty($warningData)): ?>
<div class="card" style="text-align:center;padding:40px;">
    <div style="font-size:48px;margin-bottom:12px;">✅</div>
    <h2 style="color:var(--green);">Semua Naskah Dalam Kondisi Aman</h2>
    <p class="muted">Tidak ada naskah dengan status warning di atas E0 (Hijau)</p>
</div>
<?php else: ?>

<!-- Ringkasan -->
<div class="kpi-row" style="grid-template-columns: repeat(4, 1fr); margin-bottom:20px;">
    <?php
    $e3Count = count(array_filter($warningData, fn($w) => $w['warning_status'] === 'E3'));
    $e2Count = count(array_filter($warningData, fn($w) => $w['warning_status'] === 'E2'));
    $e1Count = count(array_filter($warningData, fn($w) => $w['warning_status'] === 'E1'));
    $v0Count = count(array_filter($warningData, fn($w) => $w['warning_status'] === 'V0'));
    ?>
    <div class="kpi-card"><div class="kpi-icon kpi-icon-red">🔴</div><div><div class="kpi-number"><?= $e3Count ?></div><div class="kpi-label">E3 — Merah</div></div></div>
    <div class="kpi-card"><div class="kpi-icon" style="background:#ffedd5;color:#ea580c;">🟠</div><div><div class="kpi-number"><?= $e2Count ?></div><div class="kpi-label">E2 — Jingga</div></div></div>
    <div class="kpi-card"><div class="kpi-icon kpi-icon-yellow">🟡</div><div><div class="kpi-number"><?= $e1Count ?></div><div class="kpi-label">E1 — Kuning</div></div></div>
    <div class="kpi-card"><div class="kpi-icon" style="background:#f1f5f9;color:#64748b;">⚪</div><div><div class="kpi-number"><?= $v0Count ?></div><div class="kpi-label">V0 — Data Belum Cukup</div></div></div>
</div>

<!-- Detail -->
<?php foreach ($warningData as $wd): ?>
<div class="card">
    <div class="flex-between" style="margin-bottom:10px;">
        <div>
            <strong><?= h($wd['kode']) ?></strong> — <?= h($wd['nama_mitra']) ?>
            <span class="badge badge-<?= warnaWarning($wd['warning_status']) ?>" style="margin-left:8px;"><?= h($wd['warning_label']) ?></span>
        </div>
        <a href="mitra_edit.php?id=<?= $wd['id'] ?>" class="btn btn-outline btn-sm">Detail</a>
    </div>
    <p style="margin:0 0 10px;font-size:12.5px;">Penanganan: <strong><?= h($wd['tingkat']) ?></strong> &mdash; Hasil uji: <?= h($wd['hasil_uji']) ?></p>
    <div class="table-wrap">
    <table style="font-size:12px;">
        <thead><tr><th>Dimensi</th><th>Kondisi</th><th>Status</th><th>Fakta/Bukti</th><th>Tindakan</th><th>Tenggat</th><th>Progres</th></tr></thead>
        <tbody>
        <?php foreach ($wd['rows'] as $r): ?>
            <tr>
                <td><strong><?= h($r['dimensi']) ?></strong></td>
                <td><?= h($r['kondisi']) ?></td>
                <td><span class="badge badge-<?= warnaWarning($r['status']) ?>"><?= h($r['status']) ?></span></td>
                <td><?= h($r['fakta_bukti'] ?? '-') ?></td>
                <td><?= h($r['tindakan'] ?? '-') ?></td>
                <td><?= formatTanggal($r['tenggat']) ?></td>
                <td><?= h($r['progres']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
</div>
<?php endforeach; ?>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>