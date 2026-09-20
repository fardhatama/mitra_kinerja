<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/data.php';
requireLogin();

$pdo = getDB();
$all = getAllMitraSummary($pdo);
$user = currentUser();

$pageTitle = 'Daftar Naskah';
require __DIR__ . '/includes/header.php';
?>

<div class="flex-between" style="margin-bottom:18px;">
    <div>
        <h1 style="margin:0;font-size:20px;">Daftar Naskah Kerja Sama</h1>
        <div class="muted" style="font-size:13px;">15 naskah (10 Pilot Utama + 5 Cadangan)</div>
    </div>
</div>

<div class="card">
    <div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th>Kode</th><th>Mitra</th><th>Jenis</th><th>Berakhir</th>
                <th>Kelengkapan</th><th>Status Scorecard</th><th>Validasi</th><th></th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($all as $s): $m = $s['mitra']; ?>
            <tr>
                <td><strong><?= h($m['kode']) ?></strong><br><span class="muted" style="font-size:11px;"><?= h($m['portofolio']) ?></span></td>
                <td><?= h($m['nama_mitra']) ?></td>
                <td><?= h($m['jenis']) ?></td>
                <td><?= formatTanggal($m['tanggal_berakhir']) ?></td>
                <td><?= $s['kelengkapan'] ?>%</td>
                <td><?= h($s['status_scorecard']) ?></td>
                <td>
                    <?php
                    $vBadge = match($s['validasi']['status']) {
                        'DISETUJUI' => 'success', 'PERLU PERBAIKAN' => 'danger', default => 'secondary'
                    };
                    ?>
                    <span class="badge badge-<?= $vBadge ?>"><?= h($s['validasi']['status']) ?></span>
                </td>
                <td>
                    <a href="mitra_edit.php?id=<?= $m['id'] ?>" class="btn btn-outline btn-sm">Isi/Edit</a>
                    <?php if (in_array($user['role'], ['admin','validator'], true)): ?>
                    <a href="mitra_validasi.php?id=<?= $m['id'] ?>" class="btn btn-outline btn-sm">Validasi</a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
