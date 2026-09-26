<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/data.php';
requireLogin();

$pdo = getDB();
$all = getAllMitraSummary($pdo);

$user = currentUser();
$userRole = $user['role'] ?? 'pemeriksa';

$dueSoonMonev = [];
foreach ($all as $s) {
    if (!empty($s['monev']['warning_1_bulan'])) {
        $dueSoonMonev[] = $s;
    }
}

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

<?php if (!empty($dueSoonMonev) && ($userRole === 'pengampu' || $userRole === 'admin')): ?>
<div class="alert alert-warning" style="margin-bottom:20px;border-left:4px solid #ea580c;background:#fff7ed;color:#9a3412;">
    <div style="font-weight:700;font-size:14px;margin-bottom:4px;color:#c2410c;">
        ⚠️ Notifikasi Pengisian Jadwal Evaluasi (Akun Pengampu)
    </div>
    <div style="font-size:13px;line-height:1.5;">
        Akun <strong>Pengampu</strong> perlu untuk melakukan pengisian setiap <strong>SC1 atau SC2 atau SC3</strong> dan siklus evaluasi lainnya, sebelum <strong>30 hari</strong> dari tenggat waktu SC tersebut:
    </div>
    <ul style="margin:6px 0 0 18px;padding:0;font-size:12.5px;">
        <?php foreach ($dueSoonMonev as $ds): 
            $msLabel = 'SC-1';
            if (!empty($ds['monev']['milestones'])) {
                foreach ($ds['monev']['milestones'] as $ms) {
                    if (!empty($ms['is_due_soon'])) {
                        $msLabel = $ms['nama'];
                        break;
                    }
                }
            }
        ?>
        <li style="margin-bottom:4px;">
            <strong><?= h($ds['mitra']['kode']) ?></strong> &mdash; <?= h($ds['mitra']['nama_mitra']) ?> &bull; 
            Siklus: <span class="badge badge-warning" style="font-size:10px;font-weight:600;"><?= h($msLabel) ?></span> &bull;
            Target: <strong><?= formatTanggal($ds['monev']['target_evaluasi_terdekat']) ?></strong> 
            (<?= $ds['monev']['hari_menuju_evaluasi'] ?> hari lagi) &bull;
            <a href="mitra_edit.php?id=<?= $ds['mitra']['id'] ?>" style="color:#2563eb;text-decoration:underline;">Buka Pengisian &rarr;</a>
        </li>
        <?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>

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