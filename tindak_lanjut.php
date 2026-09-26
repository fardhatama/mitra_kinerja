<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/data.php';
requireLogin();

$pdo = getDB();
$user = currentUser();
$canEdit = in_array($user['role'], ['admin', 'pemeriksa'], true);

$errors = [];
$success = '';

// Handle create
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create') {
    if (!$canEdit) { $errors[] = 'Tidak memiliki akses.'; }
    else {
        $mitraId = (int)($_POST['mitra_id'] ?? 0);
        $tindakan = trim($_POST['tindakan'] ?? '');
        $tenggat = $_POST['tenggat'] ?? null;
        if ($mitraId <= 0 || $tindakan === '') {
            $errors[] = 'Pilih naskah dan isi tindakan.';
        } else {
            $stmt = $pdo->prepare('INSERT INTO tindak_lanjut (mitra_id, tindakan, tenggat) VALUES (?, ?, ?)');
            $stmt->execute([$mitraId, $tindakan, $tenggat ?: null]);
            logAudit($mitraId, $user['id'], 'TINDAK_LANJUT', 'Tambah tindak lanjut baru');
            $success = 'Tindak lanjut berhasil ditambahkan.';
        }
    }
}

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update_status') {
    if (!$canEdit) { $errors[] = 'Tidak memiliki akses.'; }
    else {
        $tlId = (int)($_POST['tl_id'] ?? 0);
        $newStatus = $_POST['new_status'] ?? '';
        if (in_array($newStatus, ['Belum', 'Proses', 'Selesai'], true)) {
            $stmt = $pdo->prepare('UPDATE tindak_lanjut SET status = ? WHERE id = ?');
            $stmt->execute([$newStatus, $tlId]);
            $success = 'Status tindak lanjut diperbarui.';
        }
    }
}

$filterMitraId = !empty($_GET['mitra_id']) ? (int)$_GET['mitra_id'] : null;
$filteredMitra = null;
if ($filterMitraId > 0) {
    $stmtFM = $pdo->prepare('SELECT kode, nama_mitra FROM mitra_kinerja WHERE id = ?');
    $stmtFM->execute([$filterMitraId]);
    $filteredMitra = $stmtFM->fetch();
}

$tindakLanjut = getTindakLanjut($pdo, $filterMitraId);
$allMitra = $pdo->query('SELECT id, kode, nama_mitra FROM mitra_kinerja ORDER BY kode')->fetchAll();

$pageTitle = 'Tindak Lanjut';
require __DIR__ . '/includes/header.php';
?>

<div class="flex-between" style="margin-bottom:18px;">
    <div>
        <h1 style="margin:0;font-size:20px;">Tindak Lanjut</h1>
        <div class="muted" style="font-size:13px;">
            <?php if ($filteredMitra): ?>
                Menampilkan tindak lanjut khusus naskah: <strong><?= h($filteredMitra['kode']) ?> &mdash; <?= h($filteredMitra['nama_mitra']) ?></strong>
            <?php else: ?>
                Pantau dan kelola progres perbaikan per naskah
            <?php endif; ?>
        </div>
    </div>
    <div style="display:flex;gap:8px;align-items:center;">
        <?php if ($filteredMitra): ?>
            <a href="tindak_lanjut.php" class="btn btn-outline btn-sm">Tampilkan Semua</a>
            <a href="baseline.php?id=<?= $filterMitraId ?>" class="btn btn-outline btn-sm">&larr; Kembali ke Baseline</a>
        <?php else: ?>
            <a href="dashboard.php" class="btn btn-outline btn-sm">&larr; Dashboard</a>
        <?php endif; ?>
    </div>
</div>

<?php if ($success): ?><div class="alert alert-info"><?= h($success) ?></div><?php endif; ?>
<?php foreach ($errors as $e): ?><div class="alert alert-warning"><?= h($e) ?></div><?php endforeach; ?>

<?php if ($canEdit): ?>
<div class="card">
    <h2>Tambah Tindak Lanjut</h2>
    <form method="post">
        <input type="hidden" name="action" value="create">
        <div class="form-grid">
            <div class="field">
                <label>Naskah</label>
                <select name="mitra_id" required>
                    <option value="">Pilih Naskah...</option>
                    <?php foreach ($allMitra as $m): ?>
                    <option value="<?= $m['id'] ?>" <?= $filterMitraId === (int)$m['id'] ? 'selected' : '' ?>><?= h($m['kode']) ?> — <?= h(singkat($m['nama_mitra'], 50)) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field"><label>Tindakan</label><input type="text" name="tindakan" required placeholder="Deskripsi tindakan"></div>
            <div class="field"><label>Tenggat</label><input type="date" name="tenggat"></div>
        </div>
        <div style="margin-top:14px;"><button type="submit" class="btn btn-primary">Tambah</button></div>
    </form>
</div>
<?php endif; ?>

<div class="card">
    <h2>Daftar Tindak Lanjut</h2>
    <div class="table-wrap">
    <table>
        <thead><tr><th>Naskah</th><th>Tindakan</th><th>Tenggat</th><th>Status</th><?php if ($canEdit): ?><th>Aksi</th><?php endif; ?></tr></thead>
        <tbody>
        <?php if (empty($tindakLanjut)): ?>
            <tr><td colspan="5" class="muted" style="text-align:center;padding:20px;">Belum ada tindak lanjut</td></tr>
        <?php else: foreach ($tindakLanjut as $tl):
            $dotClass = match($tl['status']) { 'Selesai' => 'dot-green', 'Proses' => 'dot-yellow', default => 'dot-red' };
        ?>
            <tr>
                <td><strong><?= h($tl['kode']) ?></strong> <span class="muted" style="font-size:11px;"><?= h(singkat($tl['nama_mitra'], 30)) ?></span></td>
                <td><?= h($tl['tindakan']) ?></td>
                <td><?= formatTanggal($tl['tenggat']) ?></td>
                <td><span class="status-dot <?= $dotClass ?>"><?= h($tl['status']) ?></span></td>
                <?php if ($canEdit): ?>
                <td>
                    <?php if ($tl['status'] !== 'Selesai'): ?>
                    <form method="post" style="display:inline;">
                        <input type="hidden" name="action" value="update_status">
                        <input type="hidden" name="tl_id" value="<?= $tl['id'] ?>">
                        <input type="hidden" name="new_status" value="<?= $tl['status'] === 'Belum' ? 'Proses' : 'Selesai' ?>">
                        <button type="submit" class="btn btn-outline btn-sm"><?= $tl['status'] === 'Belum' ? 'Mulai' : 'Selesai' ?></button>
                    </form>
                    <?php endif; ?>
                </td>
                <?php endif; ?>
            </tr>
        <?php endforeach; endif; ?>
        </tbody>
    </table>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>