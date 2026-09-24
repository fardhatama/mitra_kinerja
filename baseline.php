<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/data.php';
requireLogin();

$pdo = getDB();
$user = currentUser();
$role = $user['role'];
$canEdit = in_array($role, ['admin', 'pemeriksa'], true);
$isAdmin = ($role === 'admin');

$id = (int)($_GET['id'] ?? 0);
$view = $_GET['view'] ?? 'ledger';

$errors = [];
$success = '';

/* ── DEFINISI 12 ELEMEN BASELINE FIX (BAB IV PEDOMAN) ─────── */
const BASELINE_12_DEFS = [
    1 => [
        'kelompok' => 'IDENTITAS',
        'nama' => 'Identitas naskah',
        'yang_diperiksa' => 'Jenis, seluruh nomor para pihak, judul, dan nama resmi mitra sesuai naskah.',
        'sumber_minimum' => 'Naskah bertanda tangan; P2MA sebagai pembanding.'
    ],
    2 => [
        'kelompok' => 'MASA BERLAKU',
        'nama' => 'Masa berlaku',
        'yang_diperiksa' => 'Tanggal efektif, durasi, dan tanggal berakhir sesuai klausul naskah.',
        'sumber_minimum' => 'Klausul jangka waktu; halaman tanda tangan; P2MA.'
    ],
    3 => [
        'kelompok' => 'SUBSTANSI',
        'nama' => 'Ruang lingkup',
        'yang_diperiksa' => 'Ruang kerja, kewajiban, atau kegiatan utama yang disepakati.',
        'sumber_minimum' => 'Pasal ruang lingkup/hak-kewajiban; lampiran.'
    ],
    4 => [
        'kelompok' => 'TATA KELOLA',
        'nama' => 'Status arsip',
        'yang_diperiksa' => 'Ketersediaan naskah lengkap pada lokasi arsip resmi dan dapat ditemukan kembali.',
        'sumber_minimum' => 'Arsip resmi; register; folder organisasi.'
    ],
    5 => [
        'kelompok' => 'TATA KELOLA',
        'nama' => 'Status P2MA',
        'yang_diperiksa' => 'Keberadaan entri dan kesesuaian metadata P2MA dengan naskah resmi.',
        'sumber_minimum' => 'P2MA dan naskah bertanda tangan.'
    ],
    6 => [
        'kelompok' => 'PENGAMPU',
        'nama' => 'Unit pengampu',
        'yang_diperiksa' => 'Unit internal yang bertanggung jawab atas substansi dan implementasi kerja sama.',
        'sumber_minimum' => 'ND/SK/pembagian tugas; konfirmasi tertulis unit.'
    ],
    7 => [
        'kelompok' => 'PIC',
        'nama' => 'PIC internal',
        'yang_diperiksa' => 'PIC utama dan cadangan yang aktif, lengkap dengan jabatan, kontak, dan dasar penetapan.',
        'sumber_minimum' => 'ND/SK/daftar PIC; konfirmasi tertulis unit.'
    ],
    8 => [
        'kelompok' => 'PIC',
        'nama' => 'PIC mitra',
        'yang_diperiksa' => 'Penghubung operasional pihak mitra yang telah dikonfirmasi.',
        'sumber_minimum' => 'Surat/email/form konfirmasi resmi dari mitra.'
    ],
    9 => [
        'kelompok' => 'TINDAK LANJUT',
        'nama' => 'Rencana tindak lanjut',
        'yang_diperiksa' => 'Dokumen atau komitmen operasional yang memuat kegiatan, periode, target, dan/atau PIC.',
        'sumber_minimum' => 'Rencana aksi; matriks kerja; kalender; notula.'
    ],
    10 => [
        'kelompok' => 'PELAKSANAAN',
        'nama' => 'Pelaksanaan dan hasil',
        'yang_diperiksa' => 'Kegiatan aktual, realisasi terhadap target jatuh tempo, serta output yang dihasilkan.',
        'sumber_minimum' => 'Laporan; undangan; notula; daftar hadir; data hasil.'
    ],
    11 => [
        'kelompok' => 'EVIDEN',
        'nama' => 'Eviden implementasi',
        'yang_diperiksa' => 'Bukti pelaksanaan/output, lokasi penyimpanan, dan tingkat keteraturannya.',
        'sumber_minimum' => 'Folder resmi; indeks bukti; dokumen/data kegiatan.'
    ],
    12 => [
        'kelompok' => 'HAMBATAN',
        'nama' => 'Hambatan/gap',
        'yang_diperiksa' => 'Kendala faktual atau kekosongan data yang memengaruhi implementasi dan sudah dikonfirmasi.',
        'sumber_minimum' => 'Konfirmasi unit/PIC/mitra; notula; laporan; bukti keterlambatan.'
    ]
];

/* ── POST HANDLERS UNTUK DETAIL NASKAH ───────────────────── */
if ($id > 0 && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // Ambil data mitra saat ini
    $stmt = $pdo->prepare('SELECT * FROM mitra_kinerja WHERE id = ?');
    $stmt->execute([$id]);
    $mitra = $stmt->fetch();

    if (!$mitra) {
        header('Location: baseline.php');
        exit;
    }

    $isLocked = ($mitra['baseline_status'] === 'TERVERIFIKASI / DIKUNCI');

    // 1. Simpan Perubahan Elemen Baseline
    if ($action === 'save_baseline') {
        if (!$canEdit) {
            $errors[] = 'Akses ditolak: Hanya admin dan pemeriksa yang dapat mengubah baseline.';
        } elseif ($isLocked) {
            $errors[] = 'Baseline FIX ini telah dikunci. Buka kunci terlebih dahulu untuk melakukan koreksi.';
        } else {
            $pdo->beginTransaction();
            try {
                // Update kontrol naskah
                $cutoffDate = $_POST['cutoff_date'] ?: null;
                $statusTanggal = $_POST['status_tanggal'] ?? $mitra['status_tanggal'];
                $sumberBaseline = trim($_POST['sumber_baseline'] ?? '');
                $pemeriksa = trim($_POST['baseline_pemeriksa'] ?? '');
                $catatanRingkasan = trim($_POST['baseline_catatan_ringkasan'] ?? '');

                $stmtM = $pdo->prepare('UPDATE mitra_kinerja SET
                    cutoff_date = ?,
                    status_tanggal = ?,
                    sumber_baseline = ?,
                    baseline_pemeriksa = ?,
                    baseline_catatan_ringkasan = ?,
                    baseline_status = \'DALAM PROSES\'
                    WHERE id = ?');
                $stmtM->execute([$cutoffDate, $statusTanggal, $sumberBaseline, $pemeriksa, $catatanRingkasan, $id]);

                // Simpan 12 elemen
                for ($num = 1; $num <= 12; $num++) {
                    $st = $_POST["status_{$num}"] ?? 'BELUM DIISI';
                    $fakta = trim($_POST["fakta_{$num}"] ?? '');
                    $bukti = trim($_POST["bukti_{$num}"] ?? '');

                    $stmtE = $pdo->prepare('INSERT INTO baseline_elemen (
                        mitra_id, nomor_elemen, kelompok, nama_elemen, yang_diperiksa, sumber_bukti_minimum,
                        status, fakta_pemeriksaan, link_sumber_bukti
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE
                        status = VALUES(status),
                        fakta_pemeriksaan = VALUES(fakta_pemeriksaan),
                        link_sumber_bukti = VALUES(link_sumber_bukti)');
                    $stmtE->execute([
                        $id, $num,
                        BASELINE_12_DEFS[$num]['kelompok'],
                        BASELINE_12_DEFS[$num]['nama'],
                        BASELINE_12_DEFS[$num]['yang_diperiksa'],
                        BASELINE_12_DEFS[$num]['sumber_minimum'],
                        $st, $fakta, $bukti
                    ]);
                }

                $pdo->commit();
                logAudit($id, $user['id'], 'UPDATE_BASELINE', 'Pembaruan 12 elemen Baseline FIX ' . $mitra['kode']);
                $success = 'Data verifikasi 12 elemen Baseline FIX berhasil disimpan.';
            } catch (Throwable $e) {
                $pdo->rollBack();
                $errors[] = 'Gagal menyimpan: ' . $e->getMessage();
            }
        }
    }

    // 2. Kunci Baseline (Lock / Finalisasi)
    elseif ($action === 'lock_baseline') {
        if (!$canEdit) {
            $errors[] = 'Akses ditolak.';
        } else {
            // Periksa apakah semua 12 elemen sudah terisi (tidak ada yang BELUM DIISI)
            $stmtC = $pdo->prepare('SELECT COUNT(*) FROM baseline_elemen WHERE mitra_id = ? AND status != \'BELUM DIISI\'');
            $stmtC->execute([$id]);
            $filledCount = (int)$stmtC->fetchColumn();

            if ($filledCount < 12) {
                $errors[] = 'Gagal mengunci: Seluruh 12 elemen wajib diisi dan diklasifikasikan statusnya (saat ini baru ' . $filledCount . '/12 terisi).';
            } else {
                $pemeriksa = trim($_POST['baseline_pemeriksa'] ?? ($mitra['baseline_pemeriksa'] ?: $user['nama']));
                $stmtL = $pdo->prepare('UPDATE mitra_kinerja SET
                    baseline_status = \'TERVERIFIKASI / DIKUNCI\',
                    baseline_locked_at = NOW(),
                    baseline_locked_by = ?,
                    baseline_pemeriksa = ?
                    WHERE id = ?');
                $stmtL->execute([$user['id'], $pemeriksa, $id]);
                logAudit($id, $user['id'], 'LOCK_BASELINE', 'Kunci final Baseline FIX ' . $mitra['kode']);
                $success = 'Baseline FIX berhasil DIFINALISASI & DIKUNCI. Kondisi awal resmi menjadi titik pembanding Scorecard.';
            }
        }
    }

    // 3. Buka Kunci (Admin Only)
    elseif ($action === 'unlock_baseline') {
        if (!$isAdmin) {
            $errors[] = 'Hanya administrator yang berhak membuka kunci Baseline FIX.';
        } else {
            $stmtU = $pdo->prepare('UPDATE mitra_kinerja SET
                baseline_status = \'DALAM PROSES\',
                baseline_locked_at = NULL,
                baseline_locked_by = NULL
                WHERE id = ?');
            $stmtU->execute([$id]);
            logAudit($id, $user['id'], 'UNLOCK_BASELINE', 'Membuka kunci Baseline FIX ' . $mitra['kode']);
            $success = 'Kunci Baseline FIX dibuka kembali untuk penyesuaian administratif.';
        }
    }
}

/* ── VIEW ROUTER ─────────────────────────────────────────── */

// ═════════════════════════════════════════════════════════════
// DETAIL / PRINT FORM RESMI (Ketik ?id=X)
// ═════════════════════════════════════════════════════════════
if ($id > 0) {
    $stmt = $pdo->prepare('SELECT * FROM mitra_kinerja WHERE id = ?');
    $stmt->execute([$id]);
    $mitra = $stmt->fetch();

    if (!$mitra) {
        header('Location: baseline.php');
        exit;
    }

    // Ambil 12 elemen tersimpan
    $stmtE = $pdo->prepare('SELECT * FROM baseline_elemen WHERE mitra_id = ? ORDER BY nomor_elemen ASC');
    $stmtE->execute([$id]);
    $rowsRaw = $stmtE->fetchAll();

    $elemenData = [];
    foreach ($rowsRaw as $r) {
        $elemenData[(int)$r['nomor_elemen']] = $r;
    }

    // Hitung status kelengkapan
    $countVerified = 0;
    $countFilled = 0;
    for ($i = 1; $i <= 12; $i++) {
        $st = $elemenData[$i]['status'] ?? 'BELUM DIISI';
        if ($st === 'TERVERIFIKASI') $countVerified++;
        if ($st !== 'BELUM DIISI') $countFilled++;
    }

    $isLocked = ($mitra['baseline_status'] === 'TERVERIFIKASI / DIKUNCI');
    $pageTitle = 'Baseline FIX — ' . $mitra['kode'];

    if ($view === 'print') {
        // Standalone Printable View
        ?><!DOCTYPE html>
        <html lang="id">
        <head>
            <meta charset="UTF-8">
            <title><?= h($pageTitle) ?></title>
            <link rel="stylesheet" href="public/css/style.css">
            <style>
                body { background: #fff; color: #000; font-family: 'Calibri', 'Segoe UI', sans-serif; line-height: 1.35; padding: 20px; font-size: 12px; }
                .print-container { max-width: 950px; margin: 0 auto; }
                table { width: 100%; border-collapse: collapse; margin-bottom: 14px; font-size: 11.5px; }
                th, td { border: 1px solid #333; padding: 5px 8px; vertical-align: top; }
                th { background: #f1f5f9; text-align: left; font-weight: 700; }
                .kop-surat { display: flex; align-items: center; border-bottom: 3px double #000; padding-bottom: 10px; margin-bottom: 16px; }
                .kop-surat img { height: 70px; margin-right: 16px; }
                .kop-text { text-align: center; flex: 1; }
                .kop-text h2 { margin: 0; font-size: 15px; font-weight: 800; letter-spacing: 0.5px; }
                .kop-text h1 { margin: 2px 0; font-size: 17px; font-weight: 900; letter-spacing: 1px; }
                .badge { padding: 2px 5px; font-weight: 700; border-radius: 4px; font-size: 10.5px; display: inline-block; }
                .badge-success { background: #dcfce7; color: #166534; border: 1px solid #86efac; }
                .badge-warning { background: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
                .badge-secondary { background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; }
                .sig-grid { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; margin-top: 30px; text-align: center; font-size: 11.5px; }
                @media print {
                    .no-print { display: none !important; }
                    body { padding: 0; }
                }
            </style>
        </head>
        <body>
        <div class="print-container">
            <div class="no-print" style="margin-bottom:14px;display:flex;justify-content:space-between;">
                <a href="baseline.php?id=<?= $id ?>" class="btn btn-outline btn-sm">&larr; Kembali ke Form</a>
                <button onclick="window.print()" class="btn btn-primary btn-sm">🖨️ Cetak / Simpan PDF</button>
            </div>
            
            <div class="kop-surat">
                <img src="public/img/logo-hukum.png" alt="Logo">
                <div class="kop-text">
                    <div style="font-size:14px;font-weight:700;">KEMENTERIAN HUKUM REPUBLIK INDONESIA</div>
                    <div style="font-size:16px;font-weight:900;">KANTOR WILAYAH KEPULAUAN RIAU</div>
                    <div style="font-size:11px;color:#333;">Jalan Daeng Celak, Senggarang, Tanjungpinang, Kepulauan Riau</div>
                    <div style="font-size:13px;font-weight:800;margin-top:4px;text-decoration:underline;">FORMULIR BASELINE FIX: VERIFIKASI &amp; PENGUNCIAN KONDISI AWAL (12 ELEMEN)</div>
                </div>
            </div>

            <table style="margin-bottom:14px;">
                <tr><th style="width:25%;">Kode &amp; Mitra</th><td><strong><?= h($mitra['kode']) ?></strong> &mdash; <?= h($mitra['nama_mitra']) ?></td><th style="width:20%;">Portofolio</th><td><?= h($mitra['portofolio']) ?></td></tr>
                <tr><th>Judul Kerja Sama</th><td colspan="3"><?= h($mitra['judul']) ?></td></tr>
                <tr><th>Jenis &amp; Masa Berlaku</th><td><?= h($mitra['jenis']) ?> (<?= formatTanggal($mitra['tanggal_mulai']) ?> s.d. <?= formatTanggal($mitra['tanggal_berakhir']) ?>)</td><th>Status Tanggal</th><td><?= h($mitra['status_tanggal']) ?></td></tr>
                <tr><th>Tanggal Cut-off Baseline</th><td><strong><?= formatTanggal($mitra['cutoff_date']) ?></strong></td><th>Status Kunci</th><td><strong><?= h($mitra['baseline_status']) ?></strong> <?= $mitra['baseline_locked_at'] ? '(' . formatTanggal($mitra['baseline_locked_at']) . ')' : '' ?></td></tr>
                <tr><th>Pemeriksa Baseline</th><td><?= h($mitra['baseline_pemeriksa'] ?? 'Tim Penilai') ?></td><th>Sumber Rujukan</th><td><?= h($mitra['sumber_baseline'] ?? 'P2MA Kemenkumham') ?></td></tr>
            </table>

            <table>
                <thead>
                    <tr>
                        <th style="width:4%;text-align:center;">No</th>
                        <th style="width:14%;">Kelompok</th>
                        <th style="width:20%;">Elemen &amp; Standar Bukti</th>
                        <th style="width:14%;text-align:center;">Status Baseline</th>
                        <th style="width:28%;">Fakta / Hasil Pemeriksaan</th>
                        <th style="width:20%;">Sumber / Bukti</th>
                    </tr>
                </thead>
                <tbody>
                    <?php for ($num = 1; $num <= 12; $num++):
                        $def = BASELINE_12_DEFS[$num];
                        $el = $elemenData[$num] ?? [];
                        $st = $el['status'] ?? 'BELUM DIISI';
                        $bClass = match($st) {
                            'TERVERIFIKASI' => 'success',
                            'BELUM TERVERIFIKASI' => 'warning',
                            default => 'secondary'
                        };
                    ?>
                    <tr>
                        <td style="text-align:center;"><?= $num ?></td>
                        <td><strong><?= h($def['kelompok']) ?></strong></td>
                        <td>
                            <strong><?= h($def['nama']) ?></strong>
                            <div style="font-size:10.5px;color:#555;margin-top:2px;"><?= h($def['sumber_minimum']) ?></div>
                        </td>
                        <td style="text-align:center;"><span class="badge badge-<?= $bClass ?>"><?= h($st) ?></span></td>
                        <td><?= nl2br(h($el['fakta_pemeriksaan'] ?? '-')) ?></td>
                        <td style="font-size:10.5px;"><?= nl2br(h($el['link_sumber_bukti'] ?? '-')) ?></td>
                    </tr>
                    <?php endfor; ?>
                </tbody>
            </table>

            <div class="sig-grid">
                <div>
                    <div>Unit Pengampu / PIC,</div>
                    <div style="height:60px;"></div>
                    <div style="font-weight:700;text-decoration:underline;">Pejabat Pemangku Kegiatan</div>
                    <div class="muted">Kanwil Kemenkumham Kepri</div>
                </div>
                <div>
                    <div>Pemeriksa / Verifikator,</div>
                    <div style="height:60px;"></div>
                    <div style="font-weight:700;text-decoration:underline;"><?= h($mitra['baseline_pemeriksa'] ?: 'Bagian Tata Usaha dan Umum') ?></div>
                    <div class="muted">Tim Pengelola Kerja Sama</div>
                </div>
                <div>
                    <div>Mengetahui,</div>
                    <div style="height:60px;"></div>
                    <div style="font-weight:700;text-decoration:underline;">Edison Manik, S.H., M.H.</div>
                    <div class="muted">Kepala Kantor Wilayah</div>
                </div>
            </div>
        </div>
        </body>
        </html>
        <?php
        exit;
    }

    // Standard Interactive Form View
    require __DIR__ . '/includes/header.php';
    ?>

    <div class="flex-between" style="margin-bottom:14px;">
        <div>
            <h1 style="margin:0;font-size:20px;"><?= h($mitra['kode']) ?> — Baseline FIX (Kondisi Awal)</h1>
            <div class="muted" style="font-size:13px;"><?= h($mitra['nama_mitra']) ?> &bull; <?= h($mitra['judul']) ?></div>
        </div>
        <div style="display:flex;gap:8px;">
            <a href="baseline.php" class="btn btn-outline btn-sm">&larr; Daftar Baseline</a>
            <a href="mitra_edit.php?id=<?= $id ?>" class="btn btn-outline btn-sm">Buka Scorecard &rarr;</a>
            <a href="baseline.php?view=print&id=<?= $id ?>" target="_blank" class="btn btn-primary btn-sm">🖨️ Cetak / PDF</a>
        </div>
    </div>

    <?php if ($success): ?><div class="alert alert-info"><?= h($success) ?></div><?php endif; ?>
    <?php foreach ($errors as $e): ?><div class="alert alert-warning"><?= h($e) ?></div><?php endforeach; ?>

    <!-- Lock Status Notification -->
    <?php if ($isLocked): ?>
    <div class="alert alert-info" style="display:flex;align-items:center;justify-content:space-between;background:#f0fdf4;border-left:4px solid #16a34a;color:#166534;margin-bottom:18px;">
        <div>
            <strong>🔒 BASELINE FIX DIKUNCI (FINAL)</strong> &mdash; 
            Kondisi awal telah resmi dikunci pada <strong><?= formatTanggal($mitra['baseline_locked_at']) ?></strong> oleh <strong><?= h($mitra['baseline_pemeriksa'] ?? 'Pemeriksa') ?></strong>.
            <div style="font-size:12px;margin-top:2px;color:#15803d;">
                Perubahan setelah tanggal cut-off wajib dicatat pada kolom Scorecard V2.1 (tidak mengubah baseline awal).
            </div>
        </div>
        <?php if ($isAdmin): ?>
        <form method="post" style="margin:0;" onsubmit="return confirm('Buka kunci Baseline FIX ini untuk melakukan perbaikan administratif?');">
            <input type="hidden" name="action" value="unlock_baseline">
            <button type="submit" class="btn btn-outline btn-sm" style="font-size:11px;background:#fff;">🔓 Buka Kunci (Admin)</button>
        </form>
        <?php endif; ?>
    </div>
    <?php else: ?>
    <div class="alert alert-warning" style="display:flex;align-items:center;justify-content:space-between;background:#fffbeb;border-left:4px solid #ca8a04;color:#854d0e;margin-bottom:18px;">
        <div>
            <strong>⚠️ BASELINE FIX DALAM PROSES VERIFIKASI</strong> &mdash;
            Status saat ini: <strong><?= $countVerified ?> / 12</strong> elemen terverifikasi (<?= $countFilled ?>/12 terisi).
            <div style="font-size:12px;margin-top:2px;">
                Lengkapi 12 elemen faktual kondisi awal, lalu lakukan penguncian baseline sebelum penilaian berkala berjalan.
            </div>
        </div>
        <?php if ($canEdit && $countFilled >= 12): ?>
        <form method="post" style="margin:0;" onsubmit="return confirm('Kunci Baseline FIX ini? Setelah dikunci, data kondisi awal akan menjadi titik pembanding permanen.');">
            <input type="hidden" name="action" value="lock_baseline">
            <button type="submit" class="btn btn-success btn-sm" style="font-size:11px;">🔒 Kunci Baseline (Finalisasi)</button>
        </form>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- KPI Summary Row -->
    <div class="kpi-grid" style="margin-bottom:20px;grid-template-columns:repeat(auto-fit, minmax(140px, 1fr));">
        <div class="kpi-card">
            <div class="kpi-value" style="color:#2563eb;"><?= $countVerified ?> / 12</div>
            <div class="kpi-label">Elemen Terverifikasi</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-value" style="color:<?= $countFilled >= 12 ? '#16a34a' : '#ca8a04' ?>;"><?= round(($countFilled/12)*100) ?>%</div>
            <div class="kpi-label">Kelengkapan Pemeriksaan</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-value" style="font-size:15px;"><?= $mitra['cutoff_date'] ? formatTanggal($mitra['cutoff_date']) : '-' ?></div>
            <div class="kpi-label">Tanggal Cut-off</div>
        </div>
        <div class="kpi-card">
            <span class="badge badge-<?= $isLocked ? 'success' : 'warning' ?>" style="font-size:12px;margin-top:6px;display:inline-block;">
                <?= $isLocked ? '🔒 DIKUNCI' : '⏳ DALAM PROSES' ?>
            </span>
            <div class="kpi-label">Status Baseline</div>
        </div>
    </div>

    <!-- Main Verification Form -->
    <form method="post">
        <input type="hidden" name="action" value="save_baseline">

        <!-- Card A: Kontrol Identitas & Cut-off -->
        <div class="card" style="margin-bottom:20px;">
            <h2>Kontrol Tanggal &amp; Pemeriksa Baseline</h2>
            <div class="form-grid">
                <div class="field">
                    <label>Tanggal Cut-off Baseline *</label>
                    <input type="date" name="cutoff_date" value="<?= h($mitra['cutoff_date']) ?>" <?= $isLocked || !$canEdit ? 'disabled' : 'required' ?>>
                </div>
                <div class="field">
                    <label>Status Tanggal Naskah</label>
                    <select name="status_tanggal" <?= $isLocked || !$canEdit ? 'disabled' : '' ?>>
                        <option value="TERVERIFIKASI" <?= $mitra['status_tanggal'] === 'TERVERIFIKASI' ? 'selected' : '' ?>>TERVERIFIKASI</option>
                        <option value="BELUM TERVERIFIKASI" <?= $mitra['status_tanggal'] === 'BELUM TERVERIFIKASI' ? 'selected' : '' ?>>BELUM TERVERIFIKASI</option>
                    </select>
                </div>
                <div class="field">
                    <label>Pemeriksa / Verifikator</label>
                    <input type="text" name="baseline_pemeriksa" value="<?= h($mitra['baseline_pemeriksa'] ?? 'Tim Pengelola Kerja Sama') ?>" placeholder="Nama pejabat / unit pemeriksa" <?= $isLocked || !$canEdit ? 'disabled' : '' ?>>
                </div>
                <div class="field">
                    <label>Sumber Baseline Utama</label>
                    <input type="text" name="sumber_baseline" value="<?= h($mitra['sumber_baseline'] ?? 'P2MA Kemenkumham RI') ?>" placeholder="P2MA / Berkas Fisik" <?= $isLocked || !$canEdit ? 'disabled' : '' ?>>
                </div>
                <div class="field" style="grid-column:1/-1;">
                    <label>Catatan Ringkasan Kondisi Awal</label>
                    <textarea name="baseline_catatan_ringkasan" rows="2" placeholder="Ringkasan temuan kondisi awal saat cut-off..." <?= $isLocked || !$canEdit ? 'disabled' : '' ?>><?= h($mitra['baseline_catatan_ringkasan'] ?? '') ?></textarea>
                </div>
            </div>
        </div>

        <!-- Card B: 12 Elemen Baseline FIX -->
        <div class="card">
            <div class="flex-between" style="margin-bottom:14px;">
                <div>
                    <h2 style="margin:0;">12 Elemen Baseline FIX</h2>
                    <div class="muted" style="font-size:12px;">Pengecekan faktual 12 elemen kondisi awal sesuai standar Bab IV Pedoman Teknis</div>
                </div>
                <?php if (!$isLocked && $canEdit): ?>
                <button type="submit" class="btn btn-primary btn-sm">💾 Simpan Perubahan</button>
                <?php endif; ?>
            </div>

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th style="width:4%;text-align:center;">No</th>
                            <th style="width:12%;">Kelompok</th>
                            <th style="width:24%;">Elemen &amp; Standar Bukti</th>
                            <th style="width:15%;">Status Baseline</th>
                            <th style="width:25%;">Fakta / Hasil Pemeriksaan</th>
                            <th style="width:20%;">Sumber / Bukti Minimum</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php for ($num = 1; $num <= 12; $num++):
                            $def = BASELINE_12_DEFS[$num];
                            $el = $elemenData[$num] ?? [];
                            $currStatus = $el['status'] ?? 'BELUM DIISI';
                            $statusOptions = ['TERVERIFIKASI', 'BELUM TERVERIFIKASI', 'BELUM TERSEDIA', 'TIDAK RELEVAN', 'BELUM DIISI'];
                        ?>
                        <tr>
                            <td style="text-align:center;font-weight:700;"><?= $num ?></td>
                            <td><span class="badge badge-secondary" style="font-size:10px;"><?= h($def['kelompok']) ?></span></td>
                            <td>
                                <strong><?= h($def['nama']) ?></strong>
                                <div style="font-size:11px;color:#475569;margin-top:2px;"><?= h($def['yang_diperiksa']) ?></div>
                                <div class="muted" style="font-size:10.5px;margin-top:2px;"><em>Rujukan: <?= h($def['sumber_minimum']) ?></em></div>
                            </td>
                            <td>
                                <?php if ($isLocked || !$canEdit): ?>
                                    <span class="badge badge-<?= $currStatus === 'TERVERIFIKASI' ? 'success' : ($currStatus === 'BELUM TERVERIFIKASI' ? 'warning' : 'secondary') ?>">
                                        <?= h($currStatus) ?>
                                    </span>
                                <?php else: ?>
                                    <select name="status_<?= $num ?>" style="font-size:12px;padding:4px 6px;">
                                        <?php foreach ($statusOptions as $opt): ?>
                                        <option value="<?= $opt ?>" <?= $currStatus === $opt ? 'selected' : '' ?>><?= $opt ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($isLocked || !$canEdit): ?>
                                    <div style="font-size:11.5px;color:#1e293b;"><?= nl2br(h($el['fakta_pemeriksaan'] ?? '-')) ?></div>
                                <?php else: ?>
                                    <textarea name="fakta_<?= $num ?>" rows="2" style="width:100%;font-size:11.5px;" placeholder="Tuliskan fakta hasil pemeriksaan..."><?= h($el['fakta_pemeriksaan'] ?? '') ?></textarea>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($isLocked || !$canEdit): ?>
                                    <div style="font-size:11px;color:#475569;"><?= nl2br(h($el['link_sumber_bukti'] ?? '-')) ?></div>
                                <?php else: ?>
                                    <textarea name="bukti_<?= $num ?>" rows="2" style="width:100%;font-size:11px;" placeholder="Tautan P2MA / surat / nomor arsip..."><?= h($el['link_sumber_bukti'] ?? '') ?></textarea>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endfor; ?>
                    </tbody>
                </table>
            </div>

            <?php if (!$isLocked && $canEdit): ?>
            <div style="margin-top:16px;display:flex;justify-content:space-between;align-items:center;">
                <button type="submit" class="btn btn-primary">💾 Simpan Perubahan Baseline</button>
                <span class="muted" style="font-size:12px;">Pastikan seluruh 12 elemen terisi sebelum mengunci baseline.</span>
            </div>
            <?php endif; ?>
        </div>
    </form>

    <?php
    require __DIR__ . '/includes/footer.php';
    exit;
}

// ═════════════════════════════════════════════════════════════
// OVERVIEW / LEDGER VIEW (Default tanpa ?id=)
// ═════════════════════════════════════════════════════════════
$all = getAllMitraSummary($pdo);

// Statistik Keseluruhan
$totalCount = count($all);
$lockedCount = 0;
$inProgressCount = 0;
$totalVerifiedElements = 0;

foreach ($all as $s) {
    $b = $s['baseline'];
    if ($b['is_locked']) {
        $lockedCount++;
    } elseif ($b['terisi'] > 0) {
        $inProgressCount++;
    }
    $totalVerifiedElements += $b['terverifikasi'];
}
$avgVerified = $totalCount > 0 ? round($totalVerifiedElements / $totalCount, 1) : 0;

$pageTitle = 'Baseline Kerja Sama';
require __DIR__ . '/includes/header.php';
?>

<div class="flex-between" style="margin-bottom:18px;">
    <div>
        <h1 style="margin:0;font-size:20px;">Baseline Kerja Sama</h1>
        <div class="muted" style="font-size:13px;">Verifikasi dan penguncian kondisi awal 12 elemen (Baseline FIX)</div>
    </div>
    <a href="dashboard.php" class="btn btn-outline btn-sm">&larr; Dashboard</a>
</div>

<!-- KPI Cards -->
<div class="kpi-grid" style="margin-bottom:20px;grid-template-columns:repeat(auto-fit, minmax(140px, 1fr));">
    <div class="kpi-card">
        <div class="kpi-value"><?= $totalCount ?></div>
        <div class="kpi-label">Total Naskah</div>
    </div>
    <div class="kpi-card">
        <div class="kpi-value" style="color:#16a34a;"><?= $lockedCount ?></div>
        <div class="kpi-label">Baseline Dikunci (Final)</div>
    </div>
    <div class="kpi-card">
        <div class="kpi-value" style="color:#ca8a04;"><?= $inProgressCount ?></div>
        <div class="kpi-label">Dalam Verifikasi</div>
    </div>
    <div class="kpi-card">
        <div class="kpi-value" style="color:#2563eb;"><?= $avgVerified ?> / 12</div>
        <div class="kpi-label">Rata-rata Terverifikasi</div>
    </div>
</div>

<div class="card">
    <div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th>Kode</th>
                <th>Mitra &amp; Judul</th>
                <th>Jenis</th>
                <th>Masa Berlaku</th>
                <th>Cut-off</th>
                <th>Status Baseline</th>
                <th>Kelengkapan 12 Elemen</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($all as $s): 
            $m = $s['mitra'];
            $b = $s['baseline'];
            $bBadge = match($b['status']) {
                'TERVERIFIKASI / DIKUNCI' => 'success',
                'DALAM PROSES' => 'warning',
                default => 'secondary'
            };
        ?>
            <tr>
                <td>
                    <strong><?= h($m['kode']) ?></strong><br>
                    <span class="muted" style="font-size:10px;"><?= h($m['portofolio']) ?></span>
                </td>
                <td>
                    <strong><?= h($m['nama_mitra']) ?></strong>
                    <div style="font-size:12px;color:#334155;margin-top:2px;"><?= h(singkat($m['judul'] ?? '-', 50)) ?></div>
                </td>
                <td><span class="badge badge-primary" style="font-size:10px;"><?= h($m['jenis']) ?></span></td>
                <td><?= formatTanggal($m['tanggal_mulai']) ?> s.d.<br><?= formatTanggal($m['tanggal_berakhir']) ?></td>
                <td><?= $m['cutoff_date'] ? formatTanggal($m['cutoff_date']) : '-' ?></td>
                <td>
                    <span class="badge badge-<?= $bBadge ?>" style="font-size:11px;">
                        <?= $b['is_locked'] ? '🔒 Dikunci' : h($b['status']) ?>
                    </span>
                </td>
                <td>
                    <div style="font-size:11.5px;font-weight:600;margin-bottom:2px;">
                        <?= $b['terverifikasi'] ?> / 12 Terverifikasi
                    </div>
                    <div class="hbar-track" style="width:90px;height:6px;display:inline-block;">
                        <div class="hbar-fill" style="width:<?= $b['persentase'] ?>%;background:<?= $b['is_locked'] ? '#16a34a' : '#ca8a04' ?>;"></div>
                    </div>
                </td>
                <td>
                    <div style="display:flex;gap:4px;">
                        <a href="baseline.php?id=<?= $m['id'] ?>" class="btn btn-outline btn-sm" style="font-size:11px;padding:3px 7px;">🔍 Periksa</a>
                        <a href="baseline.php?view=print&id=<?= $m['id'] ?>" target="_blank" class="btn btn-outline btn-sm" style="font-size:11px;padding:3px 7px;">🖨️ PDF</a>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
