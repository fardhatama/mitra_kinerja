<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/data.php';
requireLogin();

$pdo = getDB();
$all = getAllMitraSummary($pdo);

$pageTitle = 'Baseline';
require __DIR__ . '/includes/header.php';
?>

<div class="flex-between" style="margin-bottom:18px;">
    <div>
        <h1 style="margin:0;font-size:20px;">Baseline — Data Naskah &amp; PIC</h1>
        <div class="muted" style="font-size:13px;">Identitas, data mitra, PIC, rencana, dan eviden awal</div>
    </div>
    <a href="dashboard.php" class="btn btn-outline btn-sm">&larr; Dashboard</a>
</div>

<div class="card">
    <div class="table-wrap">
    <table>
        <thead>
            <tr><th>Kode</th><th>Mitra</th><th>Judul</th><th>Jenis</th><th>Mulai</th><th>Berakhir</th><th>Status Tanggal</th><th>Cutoff</th><th>Aksi</th></tr>
        </thead>
        <tbody>
        <?php foreach ($all as $s): $m = $s['mitra']; ?>
            <tr>
                <td><strong><?= h($m['kode']) ?></strong></td>
                <td><?= h($m['nama_mitra']) ?></td>
                <td><?= h(singkat($m['judul'] ?? '-', 50)) ?></td>
                <td><span class="badge badge-primary"><?= h($m['jenis']) ?></span></td>
                <td><?= formatTanggal($m['tanggal_mulai']) ?></td>
                <td><?= formatTanggal($m['tanggal_berakhir']) ?></td>
                <td>
                    <?php
                    $stBadge = $m['status_tanggal'] === 'TERVERIFIKASI' ? 'success' : 'warning';
                    ?>
                    <span class="badge badge-<?= $stBadge ?>"><?= h($m['status_tanggal']) ?></span>
                </td>
                <td><?= formatTanggal($m['cutoff_date']) ?></td>
                <td><a href="mitra_edit.php?id=<?= $m['id'] ?>" class="btn btn-outline btn-sm">Lihat/Edit</a></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>