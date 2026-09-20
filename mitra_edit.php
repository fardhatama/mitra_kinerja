<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/data.php';
requireLogin();

$user = currentUser();
$canEdit = in_array($user['role'], ['admin', 'pemeriksa'], true);

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo = getDB();
$stmt = $pdo->prepare('SELECT * FROM mitra_kinerja WHERE id = ?');
$stmt->execute([$id]);
$mitra = $stmt->fetch();
if (!$mitra) {
    http_response_code(404);
    die('Naskah tidak ditemukan.');
}

$errors = [];
$saved = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$canEdit) {
        http_response_code(403);
        die('Role Anda tidak dapat mengubah data ini.');
    }

    $pdo->beginTransaction();
    try {
        // --- A. Identitas & kontrol ---
        // Status Tanggal (F9) memang wajib bisa diedit pemeriksa: dipilih TERVERIFIKASI
        // hanya setelah tanggal dicek cocok dengan sumber resmi (naskah/arsip).
        $statusTanggal = in_array($_POST['status_tanggal'] ?? '', ['TERVERIFIKASI','BELUM TERVERIFIKASI'], true)
            ? $_POST['status_tanggal'] : 'BELUM TERVERIFIKASI';
        $stmtU = $pdo->prepare('UPDATE mitra_kinerja SET pemeriksa_id = ?, tanggal_review = ?, status_tanggal = ? WHERE id = ?');
        $stmtU->execute([
            $user['id'],
            $_POST['tanggal_review'] !== '' ? $_POST['tanggal_review'] : null,
            $statusTanggal,
            $id,
        ]);

        // --- B. Enam indikator ---
        foreach (['I1','I2','I3','I4','I5','I6'] as $kode) {
            $status  = $_POST['status_' . $kode] ?? 'BELUM DIPERIKSA';
            $temuan  = trim($_POST['temuan_' . $kode] ?? '');
            $skorRaw = $_POST['skor_' . $kode] ?? '';
            $alasan  = trim($_POST['alasan_' . $kode] ?? '');

            // Aturan tegas: skor HANYA disimpan jika status = BUKTI CUKUP.
            // Ini ditegakkan di server, bukan cuma disembunyikan di UI.
            $skor = null;
            if ($status === 'BUKTI CUKUP' && $skorRaw !== '') {
                $skor = max(0, min(4, (int)$skorRaw));
            }

            $stmtI = $pdo->prepare('SELECT bobot FROM indikator_skor WHERE mitra_id = ? AND kode_indikator = ?');
            $stmtI->execute([$id, $kode]);
            $bobot = (int)($stmtI->fetchColumn() ?: BOBOT_INDIKATOR[$kode]);
            $nilai = hitungNilaiIndikator($skor, $bobot);

            $stmtU2 = $pdo->prepare(
                'UPDATE indikator_skor SET status_pemeriksaan=?, temuan_bukti=?, skor=?, alasan_skor=?, nilai=?, updated_by=? WHERE mitra_id=? AND kode_indikator=?'
            );
            $stmtU2->execute([$status, $temuan ?: null, $skor, $alasan ?: null, $nilai, $user['id'], $id, $kode]);
        }

        // --- D. Early warning (4 dimensi) ---
        // "Masa berlaku": Kondisi & Status 100% OTOMATIS (dari tanggal berakhir/cutoff/status
        // tanggal) -- tidak menerima input Kondisi/Status dari form sama sekali, hanya field
        // penanganan (Fakta, Tindakan, PIC, Tenggat, Progres) yang bisa diisi pemeriksa.
        // 3 dimensi lain: Kondisi dipilih dari daftar tetap, Status DITURUNKAN dari Kondisi
        // itu (bukan dipilih manual) -- ditegakkan di server via statusDariKondisi().
        foreach (['Masa berlaku','Aktivitas/tenggat','Data/eviden','PIC'] as $dim) {
            $key = preg_replace('/[^a-zA-Z]/', '', $dim);
            $fakta    = trim($_POST['warn_fakta_' . $key] ?? '');
            $tindakan = trim($_POST['warn_tindakan_' . $key] ?? '');
            $pic      = trim($_POST['warn_pic_' . $key] ?? '');
            $tenggat  = $_POST['warn_tenggat_' . $key] ?? '';
            $progres  = $_POST['warn_progres_' . $key] ?? 'BELUM MULAI';

            if ($dim === 'Masa berlaku') {
                // Kondisi/status dihitung ulang setiap tampil (lihat getMitraSummary);
                // tidak perlu -- dan tidak boleh -- menerima nilai dari klien.
                $stmtW = $pdo->prepare(
                    'UPDATE early_warning SET fakta_bukti=?, tindakan=?, pic=?, tenggat=?, progres=? WHERE mitra_id=? AND dimensi=?'
                );
                $stmtW->execute([$fakta ?: null, $tindakan ?: null, $pic ?: null, $tenggat !== '' ? $tenggat : null, $progres, $id, $dim]);
            } else {
                $kondisiPost = $_POST['warn_kondisi_' . $key] ?? 'BELUM DIPERIKSA';
                $allowedKondisi = array_keys(KONDISI_OPTIONS[$dim] ?? []);
                $kondisi = in_array($kondisiPost, $allowedKondisi, true) ? $kondisiPost : 'BELUM DIPERIKSA';
                $status = statusDariKondisi($dim, $kondisi); // dihitung server, bukan dari input klien

                $stmtW = $pdo->prepare(
                    'UPDATE early_warning SET status=?, kondisi=?, fakta_bukti=?, tindakan=?, pic=?, tenggat=?, progres=? WHERE mitra_id=? AND dimensi=?'
                );
                $stmtW->execute([$status, $kondisi, $fakta ?: null, $tindakan ?: null, $pic ?: null, $tenggat !== '' ? $tenggat : null, $progres, $id, $dim]);
            }
        }

        // --- E. Uji kebutuhan intervensi pimpinan (5 pemicu) ---
        for ($no = 1; $no <= 5; $no++) {
            $jawaban = $_POST['pemicu_' . $no] ?? 'BELUM DIPASTIKAN';
            $bukti   = trim($_POST['pemicu_bukti_' . $no] ?? '');
            $stmtP = $pdo->prepare('UPDATE intervensi_pimpinan SET jawaban=?, bukti_alasan=? WHERE mitra_id=? AND no_pemicu=?');
            $stmtP->execute([$jawaban, $bukti ?: null, $id, $no]);
        }

        $upaya    = trim($_POST['upaya_dilakukan'] ?? '');
        $keputusan = trim($_POST['keputusan_diminta'] ?? '');
        $stmtV = $pdo->prepare('SELECT id FROM intervensi_usulan WHERE mitra_id = ?');
        $stmtV->execute([$id]);
        if ($stmtV->fetch()) {
            $stmtU3 = $pdo->prepare('UPDATE intervensi_usulan SET upaya_dilakukan=?, keputusan_diminta=? WHERE mitra_id=?');
            $stmtU3->execute([$upaya ?: null, $keputusan ?: null, $id]);
        } else {
            $stmtU3 = $pdo->prepare('INSERT INTO intervensi_usulan (mitra_id, upaya_dilakukan, keputusan_diminta) VALUES (?,?,?)');
            $stmtU3->execute([$id, $upaya ?: null, $keputusan ?: null]);
        }

        $pdo->commit();
        syncStatusScorecard($pdo, $id);
        logAudit($id, $user['id'], 'SIMPAN_SCORECARD', 'Menyimpan penilaian naskah ' . $mitra['kode']);
        $saved = true;
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $errors[] = 'Gagal menyimpan: ' . $e->getMessage();
    }

    // Muat ulang mitra (untuk status_scorecard terbaru)
    $stmt = $pdo->prepare('SELECT * FROM mitra_kinerja WHERE id = ?');
    $stmt->execute([$id]);
    $mitra = $stmt->fetch();
}

$summary = getMitraSummary($pdo, $mitra);

$pageTitle = 'Naskah ' . $mitra['kode'];
require __DIR__ . '/includes/header.php';
?>

<div class="flex-between" style="margin-bottom:14px;">
    <div>
        <h1 style="margin:0;font-size:20px;"><?= h($mitra['kode']) ?> — <?= h($mitra['nama_mitra']) ?></h1>
        <div class="muted" style="font-size:13px;"><?= h($mitra['judul']) ?></div>
    </div>
    <a href="mitra_list.php" class="btn btn-outline btn-sm">&larr; Kembali</a>
</div>

<?php if ($saved): ?><div class="alert alert-info">Perubahan tersimpan.</div><?php endif; ?>
<?php foreach ($errors as $e): ?><div class="alert alert-warning"><?= h($e) ?></div><?php endforeach; ?>
<?php if (!$canEdit): ?><div class="alert alert-info">Anda melihat data ini sebagai <?= h($user['role']) ?> (mode baca saja).</div><?php endif; ?>

<div class="kpi-grid" style="margin-bottom:20px;">
    <div class="kpi-card">
        <div class="kpi-value"><?= number_format($summary['nilai_berjalan'], 2) ?></div>
        <div class="kpi-label">Nilai Berjalan</div>
    </div>
    <div class="kpi-card">
        <div class="kpi-value"><?= $summary['kelengkapan'] ?>%</div>
        <div class="kpi-label">Kelengkapan</div>
    </div>
    <div class="kpi-card">
        <span class="badge badge-<?= warnaKategori($summary['kategori']) ?>" style="font-size:13px;"><?= h($summary['kategori']) ?></span>
        <div class="kpi-label">Kategori</div>
    </div>
    <div class="kpi-card">
        <div style="font-size:13px;font-weight:700;"><?= h($summary['status_scorecard']) ?></div>
        <div class="kpi-label">Status Scorecard</div>
    </div>
    <div class="kpi-card">
        <span class="badge badge-<?= warnaWarning($summary['warning']['status']) ?>" style="font-size:13px;"><?= h($summary['warning']['label']) ?></span>
        <div class="kpi-label">Warning Tertinggi (<?= h($summary['warning']['tingkat_penanganan']) ?>)</div>
    </div>
</div>

<form method="post">

<div class="card">
    <h2>A. Identitas dan Kontrol</h2>
    <div class="form-grid">
        <div class="field"><label>Kode</label><input value="<?= h($mitra['kode']) ?>" disabled></div>
        <div class="field"><label>Portofolio</label><input value="<?= h($mitra['portofolio']) ?>" disabled></div>
        <div class="field"><label>Jenis</label><input value="<?= h($mitra['jenis']) ?>" disabled></div>
        <div class="field"><label>Mulai</label><input value="<?= formatTanggal($mitra['tanggal_mulai']) ?>" disabled></div>
        <div class="field"><label>Berakhir</label><input value="<?= formatTanggal($mitra['tanggal_berakhir']) ?>" disabled></div>
        <div class="field">
            <label>Status Tanggal <span class="muted">(pilih setelah dicek dengan naskah/arsip asli)</span></label>
            <select name="status_tanggal" <?= $canEdit ? '' : 'disabled' ?>>
                <?php foreach (['BELUM TERVERIFIKASI','TERVERIFIKASI'] as $opt): ?>
                <option value="<?= $opt ?>" <?= $mitra['status_tanggal'] === $opt ? 'selected' : '' ?>><?= $opt ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="field"><label>Cut-off</label><input value="<?= formatTanggal($mitra['cutoff_date']) ?>" disabled></div>
        <div class="field"><label>Sumber Baseline</label><input value="<?= h($mitra['sumber_baseline']) ?>" disabled></div>
        <div class="field">
            <label>Sisa Hari <span class="muted">(otomatis, dari Berakhir − Cut-off)</span></label>
            <?php
            $mb = hitungMasaBerlaku($mitra['tanggal_berakhir'], $mitra['cutoff_date'], $mitra['status_tanggal']);
            ?>
            <input value="<?= $mb['sisa_hari'] !== null ? $mb['sisa_hari'] . ' hari (' . h($mb['kondisi']) . ')' : 'Belum dapat dipastikan' ?>" disabled>
        </div>
    </div>
</div>


<div class="section-title">B. Penilaian Inti — Enam Indikator RAP</div>
<?php foreach ($summary['indikator'] as $row):
    $kode = $row['kode_indikator'];
    $cek = hitungCekIndikator($row);
    $cekColor = $cek === 'OK' ? 'success' : ($cek === 'PERIKSA BUKTI' ? 'secondary' : 'warning');
?>
<div class="indikator-block">
    <div class="indikator-head">
        <div><span class="kode"><?= h($kode) ?></span><span class="bobot">Bobot <?= (int)$row['bobot'] ?></span></div>
        <span class="cek-pill badge-<?= $cekColor ?>"><?= h($cek) ?></span>
    </div>
    <div class="indikator-body">
        <div class="indikator-desc"><?= nl2br(h($row['deskripsi'])) ?></div>
        <?php if ($row['referensi_baseline']): ?>
        <div class="indikator-baseline"><strong>Referensi baseline awal:</strong> <?= h($row['referensi_baseline']) ?></div>
        <?php endif; ?>
        <div class="form-grid">
            <div class="field">
                <label>Status Pemeriksaan</label>
                <select name="status_<?= $kode ?>" class="status-select" data-kode="<?= $kode ?>" <?= $canEdit ? '' : 'disabled' ?>>
                    <?php foreach (['BELUM DIPERIKSA','BUKTI CUKUP','BUKTI BELUM CUKUP','BELUM DAPAT DINILAI'] as $opt): ?>
                    <option value="<?= $opt ?>" <?= $row['status_pemeriksaan'] === $opt ? 'selected' : '' ?>><?= $opt ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label>Skor 0–4 <span class="muted">(hanya jika Bukti Cukup)</span></label>
                <select name="skor_<?= $kode ?>" class="skor-select" data-kode="<?= $kode ?>"
                    <?= (!$canEdit || $row['status_pemeriksaan'] !== 'BUKTI CUKUP') ? 'disabled' : '' ?>>
                    <option value="">-</option>
                    <?php for ($s = 0; $s <= 4; $s++): ?>
                    <option value="<?= $s ?>" <?= (int)$row['skor'] === $s && $row['skor'] !== null ? 'selected' : '' ?>><?= $s ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="field">
                <label>Nilai</label>
                <input value="<?= $row['nilai'] !== null ? number_format($row['nilai'], 2) : '-' ?>" disabled>
            </div>
        </div>
        <div class="field" style="margin-top:10px;">
            <label>Temuan dan Lokasi Bukti</label>
            <textarea name="temuan_<?= $kode ?>" <?= $canEdit ? '' : 'disabled' ?>><?= h($row['temuan_bukti']) ?></textarea>
        </div>
        <div class="field" style="margin-top:10px;">
            <label>Alasan Skor</label>
            <textarea name="alasan_<?= $kode ?>" <?= $canEdit ? '' : 'disabled' ?>><?= h($row['alasan_skor']) ?></textarea>
        </div>
    </div>
</div>
<?php endforeach; ?>

<div class="section-title">D. Early Warning <span class="muted" style="font-weight:400;font-size:13px;">(tidak ditentukan oleh nilai scorecard)</span></div>
<div class="card">
    <div class="table-wrap">
    <table>
        <thead><tr><th>Dimensi</th><th>Kondisi</th><th>Status</th><th>Fakta/Bukti</th><th>Tindakan</th><th>PIC</th><th>Tenggat</th><th>Progres</th></tr></thead>
        <tbody>
        <?php foreach ($summary['warning_rows'] as $w):
            $key = preg_replace('/[^a-zA-Z]/', '', $w['dimensi']);
            $isMasaBerlaku = $w['dimensi'] === 'Masa berlaku';
        ?>
        <tr>
            <td><strong><?= h($w['dimensi']) ?></strong>
                <?php if ($isMasaBerlaku): ?><div class="muted" style="font-size:11px;">otomatis</div><?php endif; ?>
            </td>
            <td>
                <?php if ($isMasaBerlaku): ?>
                    <input value="<?= h($w['kondisi']) ?>" disabled>
                <?php else: ?>
                    <select name="warn_kondisi_<?= $key ?>" <?= $canEdit ? '' : 'disabled' ?>>
                        <?php foreach (array_keys(KONDISI_OPTIONS[$w['dimensi']]) as $opt): ?>
                        <option value="<?= h($opt) ?>" <?= $w['kondisi'] === $opt ? 'selected' : '' ?>><?= h($opt) ?></option>
                        <?php endforeach; ?>
                    </select>
                <?php endif; ?>
            </td>
            <td><span class="badge badge-<?= warnaWarning($w['status']) ?>"><?= h(labelWarning($w['status'])) ?></span></td>
            <td><input type="text" name="warn_fakta_<?= $key ?>" value="<?= h($w['fakta_bukti']) ?>" <?= $canEdit ? '' : 'disabled' ?>></td>
            <td><input type="text" name="warn_tindakan_<?= $key ?>" value="<?= h($w['tindakan']) ?>" <?= $canEdit ? '' : 'disabled' ?>></td>
            <td><input type="text" name="warn_pic_<?= $key ?>" value="<?= h($w['pic']) ?>" <?= $canEdit ? '' : 'disabled' ?>></td>
            <td><input type="date" name="warn_tenggat_<?= $key ?>" value="<?= h($w['tenggat']) ?>" <?= $canEdit ? '' : 'disabled' ?>></td>
            <td>
                <select name="warn_progres_<?= $key ?>" <?= $canEdit ? '' : 'disabled' ?>>
                    <?php foreach (['BELUM MULAI','DALAM PROSES','SELESAI'] as $pg): ?>
                    <option value="<?= $pg ?>" <?= $w['progres'] === $pg ? 'selected' : '' ?>><?= $pg ?></option>
                    <?php endforeach; ?>
                </select>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
    <p class="muted" style="font-size:12px;margin-top:10px;margin-bottom:0;">
        Warning tertinggi saat ini: <strong><?= h($summary['warning']['label']) ?></strong> &mdash;
        tingkat penanganan: <strong><?= h($summary['warning']['tingkat_penanganan']) ?></strong>
    </p>
</div>

<div class="section-title">E. Uji Kebutuhan Intervensi Pimpinan</div>
<div class="alert alert-info">Jawab YA hanya jika kondisi benar-benar terjadi dan berada di luar kewenangan PIC/unit. E3 tidak otomatis berarti perlu pimpinan.</div>
<div class="card">
    <div class="table-wrap">
    <table>
        <thead><tr><th style="width:40%;">Pemicu</th><th>Ada?</th><th>Bukti/Alasan</th></tr></thead>
        <tbody>
        <?php foreach ($summary['pemicu_rows'] as $p): ?>
        <tr>
            <td><?= h($p['pemicu_teks']) ?></td>
            <td>
                <select name="pemicu_<?= $p['no_pemicu'] ?>" <?= $canEdit ? '' : 'disabled' ?>>
                    <?php foreach (['BELUM DIPASTIKAN','TIDAK','YA'] as $opt): ?>
                    <option value="<?= $opt ?>" <?= $p['jawaban'] === $opt ? 'selected' : '' ?>><?= $opt ?></option>
                    <?php endforeach; ?>
                </select>
            </td>
            <td><input type="text" name="pemicu_bukti_<?= $p['no_pemicu'] ?>" value="<?= h($p['bukti_alasan']) ?>" <?= $canEdit ? '' : 'disabled' ?>></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>

    <?php $usulan = $summary['usulan']; ?>
    <div class="form-grid" style="margin-top:14px;">
        <div class="field">
            <label>Upaya yang Sudah Dilakukan</label>
            <textarea name="upaya_dilakukan" <?= $canEdit ? '' : 'disabled' ?>><?= h($usulan['upaya_dilakukan']) ?></textarea>
        </div>
        <div class="field">
            <label>Keputusan Spesifik yang Diminta</label>
            <textarea name="keputusan_diminta" <?= $canEdit ? '' : 'disabled' ?>><?= h($usulan['keputusan_diminta']) ?></textarea>
        </div>
    </div>
    <p style="margin-top:12px;">
        Hasil uji: <span class="badge badge-<?= $summary['hasil_uji'] === 'CALON BUTUH INTERVENSI PIMPINAN' ? 'warning' : 'secondary' ?>"><?= h($summary['hasil_uji']) ?></span>
        &nbsp; Cek usulan: <strong><?= h($summary['cek_usulan']) ?></strong>
    </p>
</div>

<div class="card">
    <h3 style="margin-top:0;">Tanggal Review</h3>
    <div class="field" style="max-width:220px;">
        <input type="date" name="tanggal_review" value="<?= h($mitra['tanggal_review']) ?>" <?= $canEdit ? '' : 'disabled' ?>>
    </div>
</div>

<?php if ($canEdit): ?>
<div style="margin:20px 0 40px;">
    <button type="submit" class="btn btn-primary">Simpan Penilaian</button>
</div>
<?php endif; ?>
</form>

<script>
// Kunci field Skor mengikuti Status Pemeriksaan — server tetap menegakkan aturan ini ulang saat simpan.
document.querySelectorAll('.status-select').forEach(function (sel) {
    sel.addEventListener('change', function () {
        var kode = this.dataset.kode;
        var skorSel = document.querySelector('.skor-select[data-kode="' + kode + '"]');
        if (this.value === 'BUKTI CUKUP') {
            skorSel.disabled = false;
        } else {
            skorSel.disabled = true;
            skorSel.value = '';
        }
    });
});
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>
