<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/data.php';
requireRole(['admin', 'validator']);

$pdo = getDB();
$user = currentUser();
$id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare('SELECT * FROM mitra_kinerja WHERE id = ?');
$stmt->execute([$id]);
$mitra = $stmt->fetch();
if (!$mitra) { http_response_code(404); die('Naskah tidak ditemukan.'); }

$errors = [];
$saved = false;
$summary = getMitraSummary($pdo, $mitra);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $status = $_POST['status'] ?? 'BELUM';
    $catatan = trim($_POST['catatan'] ?? '');

    if (!in_array($status, ['BELUM','DISETUJUI','PERLU PERBAIKAN'], true)) {
        $errors[] = 'Status validasi tidak valid.';
    } elseif ($status === 'DISETUJUI' && $summary['kelengkapan'] < 100) {
        $errors[] = 'Tidak dapat menyetujui: kelengkapan enam indikator belum 100%.';
    } elseif ($status === 'PERLU PERBAIKAN' && $catatan === '') {
        $errors[] = 'Tulis catatan bagian yang harus diperbaiki.';
    } else {
        $stmtV = $pdo->prepare(
            'UPDATE validasi SET status=?, validator_id=?, tanggal_validasi=CURDATE(), catatan=? WHERE mitra_id=?'
        );
        $stmtV->execute([$status, $user['id'], $catatan ?: null, $id]);
        syncStatusScorecard($pdo, $id);
        logAudit($id, $user['id'], 'VALIDASI', 'Status validasi diubah menjadi ' . $status);
        $saved = true;
        $summary = getMitraSummary($pdo, $mitra);
    }
}

$pageTitle = 'Validasi ' . $mitra['kode'];
require __DIR__ . '/includes/header.php';
?>

<div class="flex-between" style="margin-bottom:14px;">
    <div>
        <h1 style="margin:0;font-size:20px;">Validasi — <?= h($mitra['kode']) ?> <?= h($mitra['nama_mitra']) ?></h1>
    </div>
    <a href="mitra_list.php" class="btn btn-outline btn-sm">&larr; Kembali</a>
</div>

<?php if ($saved): ?><div class="alert alert-info">Status validasi tersimpan.</div><?php endif; ?>
<?php foreach ($errors as $e): ?><div class="alert alert-warning"><?= h($e) ?></div><?php endforeach; ?>

<div class="card">
    <h2>Ringkasan</h2>
    <div class="kpi-grid">
        <div class="kpi-card"><div class="kpi-value"><?= number_format($summary['nilai_berjalan'],2) ?></div><div class="kpi-label">Nilai Berjalan</div></div>
        <div class="kpi-card"><div class="kpi-value"><?= $summary['kelengkapan'] ?>%</div><div class="kpi-label">Kelengkapan</div></div>
        <div class="kpi-card"><span class="badge badge-<?= warnaKategori($summary['kategori']) ?>"><?= h($summary['kategori']) ?></span><div class="kpi-label">Kategori</div></div>
        <div class="kpi-card"><div style="font-weight:700;font-size:13px;"><?= h($summary['status_scorecard']) ?></div><div class="kpi-label">Status Scorecard</div></div>
    </div>
    <?php if ($summary['kelengkapan'] < 100): ?>
    <div class="alert alert-warning" style="margin-top:14px;">Kelengkapan indikator belum 100%. Naskah ini belum bisa disetujui — kembalikan ke pemeriksa untuk dilengkapi dulu.</div>
    <?php endif; ?>
</div>

<div class="card">
    <h2>Tinjau per Indikator</h2>
    <div class="table-wrap">
    <table>
        <thead><tr><th>Kode</th><th>Status</th><th>Skor</th><th>Nilai</th><th>Cek</th></tr></thead>
        <tbody>
        <?php foreach ($summary['indikator'] as $row): $cek = hitungCekIndikator($row); ?>
        <tr>
            <td><?= h($row['kode_indikator']) ?></td>
            <td><?= h($row['status_pemeriksaan']) ?></td>
            <td><?= $row['skor'] !== null ? $row['skor'] : '-' ?></td>
            <td><?= $row['nilai'] !== null ? number_format($row['nilai'],2) : '-' ?></td>
            <td><span class="badge badge-<?= $cek === 'OK' ? 'success' : 'warning' ?>"><?= h($cek) ?></span></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
    <p style="margin-top:10px;"><a href="mitra_edit.php?id=<?= $id ?>" class="btn btn-outline btn-sm">Lihat detail lengkap (bukti, temuan, alasan)</a></p>
</div>

<div class="card">
    <h2>Keputusan Validasi</h2>
    <form method="post">
        <div class="form-grid">
            <div class="field">
                <label>Status</label>
                <select name="status">
                    <?php foreach (['BELUM','DISETUJUI','PERLU PERBAIKAN'] as $opt): ?>
                    <option value="<?= $opt ?>" <?= $summary['validasi']['status'] === $opt ? 'selected' : '' ?>><?= $opt ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="field" style="margin-top:10px;">
            <label>Catatan Validator</label>
            <textarea name="catatan"><?= h($summary['validasi']['catatan'] ?? '') ?></textarea>
        </div>
        <div style="margin-top:14px;">
            <button type="submit" class="btn btn-primary">Simpan Validasi</button>
        </div>
    </form>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
