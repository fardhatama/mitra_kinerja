<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/data.php';
requireRole(['admin', 'pemeriksa']);

$pdo = getDB();
$user = currentUser();
$id = (int)($_GET['id'] ?? 0);
$errors = [];
$success = '';

$mouOptions = $pdo->query("SELECT id, kode, nama_mitra, judul FROM mitra_kinerja WHERE jenis = 'MoU' ORDER BY kode")->fetchAll();

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
        $pksIndukId = !empty($_POST['pks_induk_id']) ? (int)$_POST['pks_induk_id'] : null;
        $mulai      = $_POST['tanggal_mulai'] ?: null;
        $berakhir   = $_POST['tanggal_berakhir'] ?: null;
        $statusTgl  = $_POST['status_tanggal'] ?? $mitra['status_tanggal'];
        $cutoff     = $_POST['cutoff_date'] ?: null;
        $sumber     = trim($_POST['sumber_baseline'] ?? '');

        // Upload File Naskah (PDF/DOCX)
        $fileNaskah = $mitra['file_naskah'] ?? null;
        if (isset($_FILES['file_naskah']) && $_FILES['file_naskah']['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['file_naskah']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['pdf', 'doc', 'docx'], true)) {
                $targetName = 'naskah_' . $mitra['kode'] . '_' . time() . '.' . $ext;
                $targetPath = __DIR__ . '/public/uploads/' . $targetName;
                if (move_uploaded_file($_FILES['file_naskah']['tmp_name'], $targetPath)) {
                    $fileNaskah = 'public/uploads/' . $targetName;
                }
            }
        }

        // Upload Foto Kegiatan
        $fotoKerjasama = $mitra['foto_kerjasama'] ?? null;
        if (isset($_FILES['foto_kerjasama']) && $_FILES['foto_kerjasama']['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['foto_kerjasama']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true)) {
                $targetName = 'foto_' . $mitra['kode'] . '_' . time() . '.' . $ext;
                $targetPath = __DIR__ . '/public/uploads/' . $targetName;
                if (move_uploaded_file($_FILES['foto_kerjasama']['tmp_name'], $targetPath)) {
                    $fotoKerjasama = 'public/uploads/' . $targetName;
                }
            }
        }

        if ($namaMitra === '') {
            $errors[] = 'Nama Mitra wajib diisi.';
        } else {
            $stmtU = $pdo->prepare('UPDATE mitra_kinerja SET nama_mitra=?, judul=?, portofolio=?, jenis=?, pks_induk_id=?, tanggal_mulai=?, tanggal_berakhir=?, status_tanggal=?, cutoff_date=?, sumber_baseline=?, file_naskah=?, foto_kerjasama=? WHERE id=?');
            $stmtU->execute([$namaMitra, $judul, $portofolio, $jenis, $pksIndukId, $mulai, $berakhir, $statusTgl, $cutoff, $sumber, $fileNaskah, $fotoKerjasama, $id]);
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
    <div><h1 style="margin:0;font-size:20px;"><?= h($mitra['kode']) ?> — Edit Naskah</h1></div>
    <a href="mitra_manage.php" class="btn btn-outline btn-sm">&larr; Manajemen Naskah</a>
</div>
<?php if ($success): ?><div class="alert alert-info"><?= h($success) ?></div><?php endif; ?>
<?php foreach ($errors as $e): ?><div class="alert alert-warning"><?= h($e) ?></div><?php endforeach; ?>

<div class="card">
<form method="post" enctype="multipart/form-data">
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
    <div class="field">
        <label>Upload Naskah (PDF/DOCX)</label>
        <input type="file" name="file_naskah" accept=".pdf,.doc,.docx">
        <?php if (!empty($mitra['file_naskah'])): ?>
        <div style="margin-top:4px;"><a href="<?= h($mitra['file_naskah']) ?>" target="_blank" class="btn btn-outline btn-sm">📄 Lihat Naskah</a></div>
        <?php endif; ?>
    </div>
    <div class="field">
        <label>Foto Dokumentasi (JPG/PNG)</label>
        <input type="file" name="foto_kerjasama" accept=".jpg,.jpeg,.png,.webp">
        <?php if (!empty($mitra['foto_kerjasama'])): ?>
        <div style="margin-top:4px;"><a href="<?= h($mitra['foto_kerjasama']) ?>" target="_blank" class="btn btn-outline btn-sm">🖼️ Lihat Foto</a></div>
        <?php endif; ?>
    </div>
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
            $pksInduk = !empty($_POST['pks_induk_id']) ? (int)$_POST['pks_induk_id'] : null;
            $stmt = $pdo->prepare('INSERT INTO mitra_kinerja (kode, portofolio, nama_mitra, judul, jenis, pks_induk_id, tanggal_mulai, tanggal_berakhir, status_tanggal) VALUES (?,?,?,?,?,?,?,?,?)');
            $stmt->execute([$kode, $portofolio, $namaMitra, $judul, $jenis, $pksInduk, $mulai, $berakhir, $statusTgl]);
            $mid = $pdo->lastInsertId();
            $v2Defaults = [
                ['I1', 'Kejelasan Pengelolaan & Rencana Tindak Lanjut', 10],
                ['I2', 'Implementasi / Tindak Lanjut', 15],
                ['I3', 'Output', 15],
                ['I4', 'Outcome', 20],
                ['I5', 'Kontribusi / Dampak', 20],
                ['I6', 'Evidence & Data', 10],
                ['I7', 'Risiko & Keberlanjutan', 10],
            ];
            foreach ($v2Defaults as $ind) {
                $desc = $ind[1] . "\nCara periksa: Evaluasi berkala siklus monev";
                $pdo->prepare('INSERT INTO indikator_skor (mitra_id, kode_indikator, deskripsi, bobot, referensi_baseline, status_pemeriksaan) VALUES (?, ?, ?, ?, \'Baseline awal\', \'BELUM DITELAAH\')')
                    ->execute([$mid, $ind[0], $desc, $ind[2]]);
            }
            foreach (['Masa berlaku','Aktivitas/tenggat','Data/eviden','PIC'] as $d) { $pdo->prepare("INSERT INTO early_warning (mitra_id,dimensi,status,progres) VALUES (?,?,'V0','BELUM MULAI')")->execute([$mid,$d]); }
            $pemicu = ['Keterlambatan pelaksanaan','Perubahan kebijakan','Pengurangan anggaran','Konflik kepentingan','Risiko hukum'];
            foreach ($pemicu as $i => $t) { $pdo->prepare('INSERT INTO intervensi_pimpinan (mitra_id,no_pemicu,pemicu_teks) VALUES (?,?,?)')->execute([$mid,$i+1,$t]); }
            logAudit($mid, $user['id'], 'CREATE_MITRA', 'Naskah baru: '.$kode);
            $success = 'Naskah '.$kode.' berhasil ditambahkan.';
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
    <div><h1 style="margin:0;font-size:20px;">Manajemen Naskah</h1>
    <div class="muted" style="font-size:13px;">Kelola identitas dan dokumen naskah kerja sama</div></div>
    <a href="dashboard.php" class="btn btn-outline btn-sm">&larr; Dashboard</a>
</div>

<?php if ($success): ?><div class="alert alert-info"><?= h($success) ?></div><?php endif; ?>
<?php foreach ($errors as $e): ?><div class="alert alert-warning"><?= h($e) ?></div><?php endforeach; ?>

<div class="card">
    <h2>Tambah Naskah</h2>
    <form method="post">
    <div class="form-grid">
        <div class="field"><label>Kode (P11, C06, dst)</label><input type="text" name="kode" maxlength="5" required placeholder="P11"></div>
        <div class="field"><label>Portofolio</label><select name="portofolio"><option>Pilot Utama</option><option>Cadangan</option></select></div>
        <div class="field"><label>Nama Mitra</label><input type="text" name="nama_mitra" required placeholder="Nama instansi"></div>
        <div class="field"><label>Jenis</label><select name="jenis"><option>MoU</option><option>PKS</option></select></div>
        <div class="field">
            <label>Kerja Sama Utama (Payung MoU)</label>
            <select name="pks_induk_id">
                <option value="">- Tidak ada (Bukan Turunan) -</option>
                <?php foreach ($mouOptions as $mou): ?>
                <option value="<?= $mou['id'] ?>"><?= h($mou['kode']) ?> &mdash; <?= h(singkat($mou['nama_mitra'], 35)) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
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