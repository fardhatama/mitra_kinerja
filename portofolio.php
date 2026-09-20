<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/data.php';
requireLogin();

$pdo = getDB();
$all = getAllMitraSummary($pdo);

$pageTitle = 'Portofolio';
require __DIR__ . '/includes/header.php';
?>

<div class="flex-between" style="margin-bottom:18px;">
    <div>
        <h1 style="margin:0;font-size:20px;">Portofolio Naskah Kerja Sama</h1>
        <div class="muted" style="font-size:13px;">Daftar seluruh naskah mitra kerja sama</div>
    </div>
    <a href="dashboard.php" class="btn btn-outline btn-sm">&larr; Dashboard</a>
    <?php if (in_array(currentUser()['role'], ['admin','pemeriksa'])): ?>
    <a href="mitra_manage.php" class="btn btn-primary btn-sm">+ Tambah Naskah</a>
    <?php endif; ?>
</div>

<div class="card">
    <div class="filters">
        <select id="fPortofolio">
            <option value="">Semua Portofolio</option>
            <option value="Pilot Utama">Pilot Utama</option>
            <option value="Cadangan">Cadangan</option>
        </select>
        <select id="fKategori">
            <option value="">Semua Kategori</option>
            <option value="PRODUKTIF">Produktif</option>
            <option value="BERJALAN">Berjalan</option>
            <option value="PERLU AKTIVASI">Perlu Aktivasi</option>
            <option value="KRITIS">Kritis</option>
            <option value="BELUM LENGKAP">Belum Lengkap</option>
        </select>
        <select id="fStatus">
            <option value="">Semua Status</option>
            <option value="FINAL/TERVALIDASI">Final/Tervalidasi</option>
            <option value="SIAP DIVALIDASI">Siap Divalidasi</option>
            <option value="BELUM LENGKAP">Belum Lengkap</option>
            <option value="PERLU PERBAIKAN">Perlu Perbaikan</option>
        </select>
    </div>
    <div class="table-wrap">
    <table id="tblPortofolio">
        <thead>
            <tr><th>Kode</th><th>Portofolio</th><th>Mitra</th><th>Jenis</th><th>Berlaku s.d.</th><th>Nilai</th><th>Kelengkapan</th><th>Kategori</th><th>Status</th><th>Warning</th><th></th></tr>
        </thead>
        <tbody>
        <?php foreach ($all as $s): $m = $s['mitra']; ?>
            <tr data-portofolio="<?= h($m['portofolio']) ?>" data-kategori="<?= h($s['kategori']) ?>" data-status="<?= h($s['status_scorecard']) ?>">
                <td><strong><?= h($m['kode']) ?></strong></td>
                <td><?= h($m['portofolio']) ?></td>
                <td><?= h($m['nama_mitra']) ?></td>
                <td><?= h($m['jenis']) ?></td>
                <td><?= formatTanggal($m['tanggal_berakhir']) ?></td>
                <td><?= $s['nilai_berjalan'] > 0 ? number_format($s['nilai_berjalan'], 2) : '-' ?></td>
                <td><?= $s['kelengkapan'] ?>%</td>
                <td><span class="badge badge-<?= warnaKategori($s['kategori']) ?>"><?= h($s['kategori']) ?></span></td>
                <td><?= h($s['status_scorecard']) ?></td>
                <td><span class="badge badge-<?= warnaWarning($s['warning']['status']) ?>"><?= h($s['warning']['label']) ?></span></td>
                <td>
                    <?php if (in_array(currentUser()['role'], ['admin','pemeriksa'])): ?>
                    <a href="mitra_manage.php?id=<?= $m['id'] ?>" class="btn btn-outline btn-sm">Edit</a>
                    <?php endif; ?>
                    <a href="mitra_edit.php?id=<?= $m['id'] ?>" class="btn btn-outline btn-sm">Scorecard</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
</div>

<script>
function applyFilters() {
    var p = document.getElementById('fPortofolio').value;
    var k = document.getElementById('fKategori').value;
    var s = document.getElementById('fStatus').value;
    document.querySelectorAll('#tblPortofolio tbody tr').forEach(function(tr) {
        tr.style.display = ((!p || tr.dataset.portofolio === p) && (!k || tr.dataset.kategori === k) && (!s || tr.dataset.status === s)) ? '' : 'none';
    });
}
document.getElementById('fPortofolio').addEventListener('change', applyFilters);
document.getElementById('fKategori').addEventListener('change', applyFilters);
document.getElementById('fStatus').addEventListener('change', applyFilters);
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>