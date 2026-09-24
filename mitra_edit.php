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
        // --- A. Identitas, kontrol, posisi & rekomendasi ---
        $statusTanggal = in_array($_POST['status_tanggal'] ?? '', ['TERVERIFIKASI','BELUM TERVERIFIKASI'], true)
            ? $_POST['status_tanggal'] : 'BELUM TERVERIFIKASI';
        $posisiPortofolio = in_array($_POST['posisi_portofolio'] ?? '', ['BELUM DAPAT DITENTUKAN','AKTIF','OUTPUT TERSEDIA','OUTCOME TERBENTUK','BERDAMPAK'], true)
            ? $_POST['posisi_portofolio'] : 'BELUM DAPAT DITENTUKAN';
        $rekomendasi = in_array($_POST['rekomendasi'] ?? '', ['BELUM DITENTUKAN','LANJUT','PERBAIKI','PERPANJANG','REPLIKASI','HENTIKAN'], true)
            ? $_POST['rekomendasi'] : 'BELUM DITENTUKAN';

        $stmtU = $pdo->prepare('UPDATE mitra_kinerja SET pemeriksa_id = ?, tanggal_review = ?, status_tanggal = ?, posisi_portofolio = ?, rekomendasi = ? WHERE id = ?');
        $stmtU->execute([
            $user['id'],
            $_POST['tanggal_review'] !== '' ? $_POST['tanggal_review'] : null,
            $statusTanggal,
            $posisiPortofolio,
            $rekomendasi,
            $id,
        ]);

        // --- B. Tujuh Indikator V2.1 (I1..I7) ---
        foreach (['I1','I2','I3','I4','I5','I6','I7'] as $kode) {
            $status  = $_POST['status_' . $kode] ?? 'BELUM DITELAAH';
            $kondisiSaatIni = trim($_POST['kondisi_saat_ini_' . $kode] ?? '');
            $temuan  = trim($_POST['temuan_' . $kode] ?? '');
            $skorRaw = $_POST['skor_' . $kode] ?? '';
            $alasan  = trim($_POST['alasan_' . $kode] ?? '');
            $catatanTl = trim($_POST['catatan_tl_' . $kode] ?? '');

            // Aturan V2.1: skor HANYA disimpan jika status = DAPAT DINILAI (atau BUKTI CUKUP/BUKTI MEMADAI).
            $skor = null;
            if (in_array($status, ['DAPAT DINILAI', 'BUKTI CUKUP', 'BUKTI MEMADAI'], true) && $skorRaw !== '') {
                $skor = max(0, min(4, (int)$skorRaw));
            }

            $stmtI = $pdo->prepare('SELECT bobot FROM indikator_skor WHERE mitra_id = ? AND kode_indikator = ?');
            $stmtI->execute([$id, $kode]);
            $bobot = (int)($stmtI->fetchColumn() ?: (BOBOT_INDIKATOR[$kode] ?? 10));
            $nilai = hitungNilaiIndikator($skor, $bobot);

            $stmtU2 = $pdo->prepare(
                'UPDATE indikator_skor SET status_pemeriksaan=?, kondisi_saat_ini=?, temuan_bukti=?, skor=?, alasan_skor=?, catatan_tindak_lanjut=?, nilai=?, updated_by=? WHERE mitra_id=? AND kode_indikator=?'
            );
            $stmtU2->execute([$status, $kondisiSaatIni ?: null, $temuan ?: null, $skor, $alasan ?: null, $catatanTl ?: null, $nilai, $user['id'], $id, $kode]);
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
    <a href="portofolio.php" class="btn btn-outline btn-sm">&larr; Portofolio</a>
</div>

<?php if ($saved): ?><div class="alert alert-info">Perubahan tersimpan.</div><?php endif; ?>
<?php foreach ($errors as $e): ?><div class="alert alert-warning"><?= h($e) ?></div><?php endforeach; ?>
<?php if (!$canEdit): ?><div class="alert alert-info">Anda melihat data ini sebagai <?= h($user['role']) ?> (mode baca saja).</div><?php endif; ?>

<div class="kpi-grid" style="margin-bottom:20px;grid-template-columns:repeat(auto-fit, minmax(130px, 1fr));">
    <div class="kpi-card">
        <div class="kpi-value"><?= number_format($summary['nilai_berjalan'], 2) ?></div>
        <div class="kpi-label">Nilai Berjalan (<?= $summary['bobot_dinilai'] ?>%)</div>
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
        <span class="badge badge-primary" style="font-size:12px;"><?= h($summary['posisi_portofolio']) ?></span>
        <div class="kpi-label">Posisi Portofolio</div>
    </div>
    <div class="kpi-card">
        <span class="badge badge-warning" style="font-size:12px;"><?= h($summary['rekomendasi']) ?></span>
        <div class="kpi-label">Rekomendasi</div>
    </div>
    <div class="kpi-card">
        <span class="badge badge-<?= warnaWarning($summary['warning']['status']) ?>" style="font-size:12px;"><?= h($summary['warning']['label']) ?></span>
        <div class="kpi-label">Warning Tertinggi</div>
    </div>
</div>

<form method="post">

<div class="card">
    <h2>Identitas Kerja Sama</h2>
    <div class="form-grid">
        <div class="field"><label>Kode</label><input value="<?= h($mitra['kode']) ?>" disabled></div>
        <div class="field"><label>Portofolio</label><input value="<?= h($mitra['portofolio']) ?>" disabled></div>
        <div class="field"><label>Jenis</label><input value="<?= h($mitra['jenis']) ?>" disabled></div>
        <div class="field"><label>Mulai</label><input value="<?= formatTanggal($mitra['tanggal_mulai']) ?>" disabled></div>
        <div class="field"><label>Berakhir</label><input value="<?= formatTanggal($mitra['tanggal_berakhir']) ?>" disabled></div>
        <div class="field">
            <label>Status Tanggal</label>
            <select name="status_tanggal" <?= $canEdit ? '' : 'disabled' ?>>
                <?php foreach (['BELUM TERVERIFIKASI','TERVERIFIKASI'] as $opt): ?>
                <option value="<?= $opt ?>" <?= $mitra['status_tanggal'] === $opt ? 'selected' : '' ?>><?= $opt ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="field"><label>Cut-off</label><input value="<?= formatTanggal($mitra['cutoff_date']) ?>" disabled></div>
        <div class="field"><label>Sumber Baseline</label><input value="<?= h($mitra['sumber_baseline']) ?>" disabled></div>
        <div class="field">
            <label>Kondisi Awal (Baseline FIX)</label>
            <div style="display:flex;gap:6px;align-items:center;">
                <input value="<?= $summary['baseline']['is_locked'] ? '🔒 Terkunci (Final)' : '⏳ ' . $summary['baseline']['terverifikasi'] . '/12 Terverifikasi' ?>" disabled style="flex:1;">
                <a href="baseline.php?id=<?= $id ?>" class="btn btn-outline btn-sm" style="font-size:11px;padding:6px 10px;">Buka &rarr;</a>
            </div>
        </div>
        <div class="field">
            <label>Sisa Masa Berlaku</label>
            <?php
            $mb = hitungMasaBerlaku($mitra['tanggal_berakhir'], $mitra['cutoff_date'], $mitra['status_tanggal']);
            ?>
            <input value="<?= $mb['sisa_hari'] !== null ? $mb['sisa_hari'] . ' hari (' . h($mb['kondisi']) . ')' : 'Belum dapat dipastikan' ?>" disabled>
        </div>
    </div>
</div>

<?php
$rencanaKerja = [];
$siklusMonev = [];
try {
    $stmtRK = $pdo->prepare('SELECT * FROM rencana_kerja WHERE mitra_id = ? ORDER BY tanggal_mulai DESC');
    $stmtRK->execute([$id]);
    $rencanaKerja = $stmtRK->fetchAll();

    $stmtSM = $pdo->prepare('SELECT * FROM siklus_monev WHERE mitra_id = ? ORDER BY siklus_ke ASC');
    $stmtSM->execute([$id]);
    $siklusMonev = $stmtSM->fetchAll();
} catch (Throwable $e) {}
$monev = $summary['monev'];
?>

<div class="card" style="margin-top:20px;">
    <div class="flex-between">
        <div>
            <h2 style="margin:0;">Jadwal Evaluasi &amp; Rencana Kerja</h2>
            <div class="muted" style="font-size:13px;">Target evaluasi berkala per triwulan selama masa berlaku kerja sama</div>
        </div>
        <div>
            <?php if ($monev['warning_1_bulan']): ?>
            <span class="badge badge-warning" style="font-size:12px;">⚠️ Evaluasi Mendekati Tenggat</span>
            <?php endif; ?>
        </div>
    </div>

    <div class="kpi-grid" style="margin:16px 0;grid-template-columns:repeat(auto-fit, minmax(140px, 1fr));">
        <div class="kpi-card">
            <div class="kpi-value"><?= $monev['durasi_bulan'] ?> Bln</div>
            <div class="kpi-label">Durasi Perjanjian</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-value"><?= $monev['total_siklus'] ?> Kali</div>
            <div class="kpi-label">Target Evaluasi</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-value" style="font-size:15px;"><?= $monev['target_evaluasi_terdekat'] ? formatTanggal($monev['target_evaluasi_terdekat']) : '-' ?></div>
            <div class="kpi-label">Evaluasi Terdekat</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-value" style="font-size:15px;"><?= $monev['hari_menuju_evaluasi'] !== null ? ($monev['hari_menuju_evaluasi'] >= 0 ? $monev['hari_menuju_evaluasi'] . ' hari lagi' : abs($monev['hari_menuju_evaluasi']) . ' hari lalu') : '-' ?></div>
            <div class="kpi-label">Sisa Waktu Penilaian</div>
        </div>
    </div>

    <?php if (!empty($rencanaKerja)): ?>
    <h3 style="font-size:15px;margin:16px 0 8px;">Rencana Kerja Terkait</h3>
    <div class="table-wrap" style="margin-bottom:16px;">
        <table>
            <thead>
                <tr><th>Judul Rencana Kerja</th><th>Periode Pelaksanaan</th><th>Ruang Lingkup</th><th>Status Persetujuan</th></tr>
            </thead>
            <tbody>
                <?php foreach ($rencanaKerja as $rk): ?>
                <tr>
                    <td><strong><?= h($rk['judul_rencana']) ?></strong></td>
                    <td><?= formatTanggal($rk['tanggal_mulai']) ?> s.d. <?= formatTanggal($rk['tanggal_selesai']) ?></td>
                    <td><?= h(singkat($rk['ruang_lingkup'] ?? '-', 60)) ?></td>
                    <td><span class="badge badge-success"><?= h($rk['status']) ?></span></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

    <h3 style="font-size:15px;margin:16px 0 8px;">Jadwal Evaluasi Berkala</h3>
    <div class="table-wrap">
        <table>
            <thead>
                <tr><th>Siklus</th><th>Nama Evaluasi</th><th>Target Tanggal</th><th>Status</th><th>Keterangan / Realisasi</th></tr>
            </thead>
            <tbody>
                <?php if (!empty($siklusMonev)): ?>
                    <?php foreach ($siklusMonev as $sm): 
                        $smBadge = match($sm['status_siklus']) {
                            'Selesai' => 'success',
                            'Perlu Penilaian Segera' => 'warning',
                            'Sedang Dinilai' => 'info',
                            default => 'secondary'
                        };
                    ?>
                    <tr>
                        <td><strong>SC-<?= $sm['siklus_ke'] ?></strong></td>
                        <td><?= h($sm['nama_siklus']) ?></td>
                        <td><?= formatTanggal($sm['tanggal_target_evaluasi']) ?></td>
                        <td><span class="badge badge-<?= $smBadge ?>"><?= h($sm['status_siklus']) ?></span></td>
                        <td><?= h($sm['catatan_monev'] ?? ($sm['tanggal_realisasi_evaluasi'] ? 'Selesai: ' . formatTanggal($sm['tanggal_realisasi_evaluasi']) : '-')) ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <?php foreach (array_slice($monev['milestones'], 0, 4) as $ms): ?>
                    <tr>
                        <td><strong>SC-<?= $ms['siklus_ke'] ?></strong></td>
                        <td><?= h($ms['nama']) ?></td>
                        <td><?= formatTanggal($ms['target_tgl']) ?></td>
                        <td>
                            <?php if ($ms['is_past']): ?>
                                <span class="badge badge-secondary">Terlewati</span>
                            <?php elseif ($ms['is_due_soon']): ?>
                                <span class="badge badge-warning">Jatuh Tempo Segera</span>
                            <?php else: ?>
                                <span class="badge badge-secondary">Menunggu</span>
                            <?php endif; ?>
                        </td>
                        <td><?= $ms['sisa_hari'] >= 0 ? $ms['sisa_hari'] . ' hari lagi' : abs($ms['sisa_hari']) . ' hari lalu' ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="card" style="margin-top:20px;">
    <h2>Kendali &amp; Rekomendasi</h2>
    <div class="form-grid">
        <div class="field">
            <label>Posisi Portofolio</label>
            <select name="posisi_portofolio" <?= $canEdit ? '' : 'disabled' ?>>
                <?php foreach (['BELUM DAPAT DITENTUKAN','AKTIF','OUTPUT TERSEDIA','OUTCOME TERBENTUK','BERDAMPAK'] as $opt): ?>
                <option value="<?= $opt ?>" <?= $summary['posisi_portofolio'] === $opt ? 'selected' : '' ?>><?= $opt ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="field">
            <label>Rekomendasi Tindak Lanjut</label>
            <select name="rekomendasi" <?= $canEdit ? '' : 'disabled' ?>>
                <?php foreach (['BELUM DITENTUKAN','LANJUT','PERBAIKI','PERPANJANG','REPLIKASI','HENTIKAN'] as $opt): ?>
                <option value="<?= $opt ?>" <?= $summary['rekomendasi'] === $opt ? 'selected' : '' ?>><?= $opt ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
</div>

<div class="section-title">Penilaian Indikator Kinerja</div>
<?php foreach ($summary['indikator'] as $row):
    $kode = $row['kode_indikator'];
    $cek = hitungCekIndikator($row);
    $cekColor = $cek === 'OK' ? 'success' : ($cek === 'PERIKSA BUKTI' ? 'secondary' : 'warning');
?>
<div class="indikator-block">
    <div class="indikator-head">
        <div><span class="kode"><?= h($kode) ?></span><span class="bobot">Bobot <?= (int)$row['bobot'] ?>%</span></div>
        <span class="cek-pill badge-<?= $cekColor ?>"><?= h($cek) ?></span>
    </div>
    <div class="indikator-body">
        <div class="indikator-desc"><?= nl2br(h($row['deskripsi'])) ?></div>
        <?php if (!empty($row['kondisi_baseline'])): ?>
        <div class="indikator-baseline"><strong>Kondisi baseline:</strong> <?= h($row['kondisi_baseline']) ?></div>
        <?php elseif (!empty($row['referensi_baseline'])): ?>
        <div class="indikator-baseline"><strong>Referensi baseline awal:</strong> <?= h($row['referensi_baseline']) ?></div>
        <?php endif; ?>
        <div class="field" style="margin: 10px 0;">
            <label>Kondisi Saat Penilaian / Update</label>
            <input type="text" name="kondisi_saat_ini_<?= $kode ?>" value="<?= h($row['kondisi_saat_ini'] ?? '') ?>" placeholder="Fakta kondisi terkini pasca-baseline" <?= $canEdit ? '' : 'disabled' ?>>
        </div>
        <div class="form-grid">
            <div class="field">
                <label>Status Penilaian</label>
                <select name="status_<?= $kode ?>" class="status-select" data-kode="<?= $kode ?>" <?= $canEdit ? '' : 'disabled' ?>>
                    <?php 
                    $v2StatusList = ['BELUM DITELAAH', 'DAPAT DINILAI', 'BUKTI BELUM MEMADAI', 'BELUM DAPAT DINILAI'];
                    foreach ($v2StatusList as $opt): 
                    ?>
                    <option value="<?= $opt ?>" <?= in_array($row['status_pemeriksaan'], [$opt, $opt === 'DAPAT DINILAI' ? 'BUKTI CUKUP' : ''], true) ? 'selected' : '' ?>><?= $opt ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label>Skor (0–4)</label>
                <?php $isScorable = in_array($row['status_pemeriksaan'], ['DAPAT DINILAI', 'BUKTI CUKUP', 'BUKTI MEMADAI'], true); ?>
                <select name="skor_<?= $kode ?>" class="skor-select" data-kode="<?= $kode ?>"
                    <?= (!$canEdit || !$isScorable) ? 'disabled' : '' ?>>
                    <option value="">-</option>
                    <?php for ($s = 0; $s <= 4; $s++): ?>
                    <option value="<?= $s ?>" <?= (int)$row['skor'] === $s && $row['skor'] !== null ? 'selected' : '' ?>><?= $s ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="field">
                <label>Nilai Bobot</label>
                <input value="<?= $row['nilai'] !== null ? number_format($row['nilai'], 2) : '-' ?>" disabled>
            </div>
        </div>
        <div class="field" style="margin-top:10px;">
            <label>Temuan dan Lokasi Bukti</label>
            <textarea name="temuan_<?= $kode ?>" placeholder="Fakta singkat dan lokasi/link/file evidence..." <?= $canEdit ? '' : 'disabled' ?>><?= h($row['temuan_bukti']) ?></textarea>
        </div>
        <div class="form-grid" style="margin-top:10px;">
            <div class="field">
                <label>Alasan Skor</label>
                <textarea name="alasan_<?= $kode ?>" placeholder="Alasan pemberian skor merujuk rubrik..." <?= $canEdit ? '' : 'disabled' ?>><?= h($row['alasan_skor']) ?></textarea>
            </div>
            <div class="field">
                <label>Catatan Tindak Lanjut</label>
                <textarea name="catatan_tl_<?= $kode ?>" placeholder="Tindak lanjut yang diperlukan dari temuan..." <?= $canEdit ? '' : 'disabled' ?>><?= h($row['catatan_tindak_lanjut'] ?? '') ?></textarea>
            </div>
        </div>
    </div>
</div>
<?php endforeach; ?>

<div class="section-title">Early Warning</div>
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

<div class="section-title">Kebutuhan Intervensi Pimpinan</div>
<div class="alert alert-info">Isi jika terdapat kendala yang memerlukan arahan atau keputusan pimpinan.</div>
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
        if (this.value === 'DAPAT DINILAI' || this.value === 'BUKTI CUKUP' || this.value === 'BUKTI MEMADAI') {
            skorSel.disabled = false;
        } else {
            skorSel.disabled = true;
            skorSel.value = '';
        }
    });
});
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>
