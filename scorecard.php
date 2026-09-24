<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/data.php';
requireLogin();

$pdo = getDB();
$all = getAllMitraSummary($pdo);

$pageTitle = 'Scorecard';
require __DIR__ . '/includes/header.php';
?>

<div class="flex-between" style="margin-bottom:18px;">
    <div>
        <h1 style="margin:0;font-size:20px;">Scorecard Kerja Sama</h1>
        <div class="muted" style="font-size:13px;">Ringkasan hasil penilaian per naskah</div>
    </div>
    <a href="dashboard.php" class="btn btn-outline btn-sm">&larr; Dashboard</a>
</div>

<div class="card">
    <div class="table-wrap">
    <table>
        <thead>
            <tr><th>Kode</th><th>Mitra</th><th>Nilai</th><th>Kelengkapan</th><th>Kategori</th><th>Posisi</th><th>Rekomendasi</th><th>Status</th><th>Validasi</th><th>Aksi</th></tr>
        </thead>
        <tbody>
        <?php foreach ($all as $s): $m = $s['mitra']; ?>
            <tr>
                <td><strong><?= h($m['kode']) ?></strong></td>
                <td><?= h($m['nama_mitra']) ?></td>
                <td><strong><?= $s['nilai_berjalan'] > 0 ? number_format($s['nilai_berjalan'], 2) : '-' ?></strong></td>
                <td><?= $s['kelengkapan'] ?>%</td>
                <td><span class="badge badge-<?= warnaKategori($s['kategori']) ?>"><?= h($s['kategori']) ?></span></td>
                <td><span class="badge badge-secondary" style="font-size:11px;"><?= h($s['posisi_portofolio']) ?></span></td>
                <td><span class="badge badge-warning" style="font-size:11px;"><?= h($s['rekomendasi']) ?></span></td>
                <td><?= h($s['status_scorecard']) ?></td>
                <td>
                    <?php $vBadge = match($s['validasi']['status']) { 'DISETUJUI' => 'success', 'PERLU PERBAIKAN' => 'danger', default => 'secondary' }; ?>
                    <span class="badge badge-<?= $vBadge ?>"><?= h($s['validasi']['status']) ?></span>
                </td>
                <td><a href="mitra_edit.php?id=<?= $m['id'] ?>" class="btn btn-outline btn-sm">Detail</a></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>