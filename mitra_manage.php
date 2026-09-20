<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/data.php';
requireRole(['admin', 'pemeriksa']);

$pdo = getDB();
$user = currentUser();
$id = (int)($_GET['id'] ?? 0);
$errors = [];
$success = '';

/* ── EDIT (ada ?id=) ──────────────────────────────── */
if ($id > 0) {
    $stmt = $pdo->prepare('SELECT * FROM mitra_kinerja WHERE id = ?');
    $stmt->execute([$id]);
    $mitra = $stmt->fetch();
    if (!$mitra) { http_response_code(404); die('Naskah tidak ditemukan.'); }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $namaMitra  = trim($_POST['nama_mitra'] ?? '');
        $judul      = trim($_POST['judul'] ?? '');
        $portofolio = $_POST['portofolio'] ?? $mitra['portofolio'];
        $jenis      = $_POST['jenis'] ?? $mitra['jenis'];
        $mulai      = $_POST['tanggal_mulai'] ?: null;
        $berakhir   = $_POST['tanggal_berakhir'] ?: null;
        $statusTgl  = $_POST['status_tanggal'] ?? $mitra['status_tanggal'];
        $cutoff     = $_POST['cutoff_date'] ?: null;
        $sumber     = trim($_POST['sumber_baseline'] ?? '');

        if ($namaMitra === '') {
            $errors[] = 'Nama Mitra wajib diisi.';
        } else {
            $stmtU = $pdo->prepare('UPDATE mitra_kinerja SET nama_mitra=?, judul=?, portofolio=?, jenis=?, tanggal_mulai=?, tanggal_berakhir=?, status_tanggal=?, cutoff_date=?, sumber_baseline=? WHERE id=?');
            $stmtU->execute([$namaMitra, $judul, $portofolio, $jenis, $mulai, $berakhir, $statusTgl, $cutoff, $sumber, $id]);
            syncStatusScorecard($pdo, $id);
            logAudit($id, $user['id'], 'UPDATE_MITRA', 'Data naskah ' . $mitra['kode'] . ' diperbarui');
            $success = 'Data naskah berhasil diperbarui.';
            $stmt = $pdo->prepare('SELECT * FROM mitra_kinerja WHERE id = ?');
            $stmt->execute([$id]);
            $mitra = $stmt->fetch();
        }
    }

    $pageTitle = 'Ubah ' . $mitra['kode'];
    require __DIR__ . '/includes/header.php';
?>

<div class="flex-between" style="margin-bottom:14px;">
    <div><h1 style="margin:0;font-size:20px;"><?= h($mitra['kode']) ?> — Edit Data Naskah</h1></div>
    <a href="mitra_manage.php" class="btn btn-outline btn-sm">&larr; Manajemen Naskah</a>
</div>
<?php if ($success): ?><div class="alert alert-info"><?= h($success) ?></div><?php endif; ?>
<?php foreach ($errors as $e): ?><div class="alert alert-warning"><?= h($e) ?></div><?php endforeach; ?>

<div class="card">
<form method="post">
<div class="form-grid">
    <div class="field"><label>Kode</label><input value="<?= h($mitra['kode']) ?>" disabled></div>
    <div class="field"><label>Portofolio</label><select name="portofolio"><?php foreach (['Pilot Utama','Cadangan'] as $opt): ?><option <?= $mitra['portofolio']===$opt?'selected':'' ?>><?= $opt ?></option><?php endforeach; ?></select></div>
    <div class="field"><label>Jenis</label><select name="jenis"><?php foreach (['MoU','PKS'] as $opt): ?><option <?= $mitra['jenis']===$opt?'selected':'' ?>><?= $opt ?></option><?php endforeach; ?></select></div>
    <div class="field"><label>Nama Mitra</label><input type="text" name="nama_mitra" value="<?= h($mitra['nama_mitra']) ?>" required></div>
    <div class="field" style="grid-column:1/-1;"><label>Judul</label><input type="text" name="judul" value="<?= h($mitra['judul']) ?>"></div>
    <div class="field"><label>Tanggal Mulai</label><input type="date" name="tanggal_mulai" value="<?= h($mitra['tanggal_mulai']) ?>"></div>
    <div class="field"><label>Tanggal Berakhir</label><input type="date" name="tanggal_berakhir" value="<?= h($mitra['tanggal_berakhir']) ?>"></div>
    <div class="field"><label>Status Tanggal</label><select name="status_tanggal"><?php foreach (['BELUM TERVERIFIKASI','TERVERIFIKASI'] as $opt): ?><option <?= $mitra['status_tanggal']===$opt?'selected':'' ?>><?= $opt ?></option><?php endforeach; ?></select></div>
    <div class="field"><label>Cutoff Date</label><input type="date" name="cutoff_date" value="<?= h($mitra['cutoff_date']) ?>"></div>
    <div class="field"><label>Sumber Baseline</label><input type="text" name="sumber_baseline" value="<?= h($mitra['sumber_baseline']) ?>"></div>
</div>
<div style="margin-top:14px;">
    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
    <a href="mitra_edit.php?id=<?= $id ?>" class="btn btn-outline" style="margin-left:8px;">Buka Scorecard &rarr;</a>
</div>
</form>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
<?php exit; }

/* ── LISTING + TAMBAH (tanpa ?id=) ─────────────────── */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kode       = strtoupper(trim($_POST['kode'] ?? ''));
    $portofolio = $_POST['portofolio'] ?? 'Pilot Utama';
    $namaMitra  = trim($_POST['nama_mitra'] ?? '');
    $judul      = trim($_POST['judul'] ?? '');
    $jenis      = $_POST['jenis'] ?? 'MoU';
    $mulai      = $_POST['tanggal_mulai'] ?: null;
    $berakhir   = $_POST['tanggal_berakhir'] ?: null;
    $statusTgl  = $_POST['status_tanggal'] ?? 'BELUM TERVERIFIKASI';

    if ($kode === '' || $namaMitra === '') {
        $errors[] = 'Kode dan Nama Mitra wajib diisi.';
    } else {
        try {
            $stmt = $pdo->prepare('INSERT INTO mitra_kinerja (kode, portofolio, nama_mitra, judul, jenis, tanggal_mulai, tanggal_berakhir, status_tanggal) VALUES (?,?,?,?,?,?,?,?)');
            $stmt->execute([$kode, $portofolio, $namaMitra, $judul, $jenis, $mulai, $berakhir, $statusTgl]);
            $mid = $pdo->lastInsertId();
            $bobot = ['I1'=>15,'I2'=>20,'I3'=>20,'I4'=>15,'I5'=>20,'I6'=>10];
            $descs = ['I1'=>'Relevansi program','I2'=>'Partisipasi mitra','I3'=>'Pelaksanaan kegiatan','I4'=>'Data dan eviden','I5'=>'Kontribusi kinerja','I6'=>'Keberlanjutan dan risiko'];
            foreach ($bobot as $k => $b) { $pdo->prepare('INSERT INTO indikator_skor (mitra_id,kode_indikator,deskripsi,bobot) VALUES (?,?,?,?)')->execute([$mid,$k,$descs[$k],$b]); }
            foreach (['Masa berlaku','Aktivitas/tenggat','Data/eviden','PIC'] as $d) { $pdo->prepare("INSERT INTO early_warning (mitra_id,dimensi,status,progres) VALUES (?,?,'V0','BELUM MULAI')")->execute([$mid,$d]); }
            $pemicu = ['Keterlambatan pelaksanaan','Perubahan kebijakan','Pengurangan anggaran','Konflik kepentingan','Risiko hukum'];
            foreach ($pemicu as $i => $t) { $pdo->prepare('INSERT INTO intervensi_pimpinan (mitra_id,no_pemicu,pemicu_teks) VALUES (?,?,?)')->execute([$mid,$i+1,$t]); }
            logAudit($mid, $user['id'], 'CREATE_MITRA', 'Naskah baru: '.$kode);
            $success = 'Naskah '.$kode.' berhasil dibuat.';
        } catch (PDOException $e) {
            $errors[] = str_contains($e->getMessage(),'Duplicate') ? 'Kode sudah ada.' : 'Gagal membuat.';
        }
    }
}

$all = $pdo->query('SELECT id,kode,portofolio,nama_mitra,jenis,tanggal_mulai,tanggal_berakhir,status_tanggal,status_scorecard FROM mitra_kinerja ORDER BY kode')->fetchAll();
$pageTitle = 'Manajemen Naskah';
require __DIR__ . '/includes/header.php';
?>

<div class="flex-between" style="margin-bottom:18px;">
    <div><h1 style="margin:0;font-size:20px;">Manajemen Naskah Kerja Sama</h1>
    <div class="muted" style="font-size:13px;">Tambah, ubah data identitas mitra</div></div>
    <a href="dashboard.php" class="btn btn-outline btn-sm">&larr; Dashboard</a>
</div>

<?php if ($success): ?><div class="alert alert-info"><?= h($success) ?></div><?php endif; ?>
<?php foreach ($errors as $e): ?><div class="alert alert-warning"><?= h($e) ?></div><?php endforeach; ?>

<div class="card">
    <h2>Tambah Naskah Baru</h2>
    <form method="post">
    <div class="form-grid">
        <div class="field"><label>Kode (P11, C06, dst)</label><input type="text" name="kode" maxlength="5" required placeholder="P11"></div>
        <div class="field"><label>Portofolio</label><select name="portofolio"><option>Pilot Utama</option><option>Cadangan</option></select></div>
        <div class="field"><label>Nama Mitra</label><input type="text" name="nama_mitra" required placeholder="Nama instansi"></div>
        <div class="field"><label>Jenis</label><select name="jenis"><option>MoU</option><option>PKS</option></select></div>
        <div class="field"><label>Judul</label><input type="text" name="judul" placeholder="Judul kerja sama"></div>
        <div class="field"><label>Tanggal Mulai</label><input type="date" name="tanggal_mulai"></div>
        <div class="field"><label>Tanggal Berakhir</label><input type="date" name="tanggal_berakhir"></div>
        <div class="field"><label>Status Tanggal</label><select name="status_tanggal"><option>BELUM TERVERIFIKASI</option><option>TERVERIFIKASI</option></select></div>
    </div>
    <div style="margin-top:14px;"><button type="submit" class="btn btn-primary">Tambah Naskah</button></div>
    </form>
</div>

<div class="card">
    <h2>Daftar Naskah</h2>
    <div class="table-wrap">
    <table>
        <thead><tr><th>Kode</th><th>Portofolio</th><th>Mitra</th><th>Jenis</th><th>Berakhir</th><th>Status</th><th>Aksi</th></tr></thead>
        <tbody>
        <?php foreach ($all as $m): ?>
            <tr>
                <td><strong><?= h($m['kode']) ?></strong></td>
                <td><?= h($m['portofolio']) ?></td>
                <td><?= h($m['nama_mitra']) ?></td>
                <td><span class="badge badge-primary"><?= h($m['jenis']) ?></span></td>
                <td><?= formatTanggal($m['tanggal_berakhir']) ?></td>
                <td><span class="badge badge-<?= $m['status_tanggal']==='TERVERIFIKASI'?'success':'warning' ?>"><?= h($m['status_tanggal']) ?></span></td>
                <td>
                    <a href="mitra_manage.php?id=<?= $m['id'] ?>" class="btn btn-outline btn-sm">Edit</a>
                    <a href="mitra_edit.php?id=<?= $m['id'] ?>" class="btn btn-outline btn-sm">Scorecard</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>