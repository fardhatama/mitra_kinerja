<?php
require_once __DIR__ . '/includes/auth.php';
requireRole(['admin', 'pemeriksa', 'pengampu']);
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/data.php';

$pdo = getDB();
$user = currentUser();
$pageTitle = 'Import Data Naskah';

$success = '';
$errors = [];

// Mapping file template resmi per kode naskah
$templateMap = [
    'P01' => ['file' => 'public/templates/import_naskah/01_Pilot_Utama/P01_Scorecard_Dekranasda_Kepri.xlsx', 'label' => 'Pilot Utama (P01)'],
    'P02' => ['file' => 'public/templates/import_naskah/01_Pilot_Utama/P02_Scorecard_BNNP_Kepri.xlsx', 'label' => 'Pilot Utama (P02)'],
    'P03' => ['file' => 'public/templates/import_naskah/01_Pilot_Utama/P03_Scorecard_Pemkot_Tanjungpinang.xlsx', 'label' => 'Pilot Utama (P03)'],
    'P04' => ['file' => 'public/templates/import_naskah/01_Pilot_Utama/P04_Scorecard_Bapperida_Bintan.xlsx', 'label' => 'Pilot Utama (P04)'],
    'P05' => ['file' => 'public/templates/import_naskah/01_Pilot_Utama/P05_Scorecard_STIT_Mumtaz_Karimun.xlsx', 'label' => 'Pilot Utama (P05)'],
    'P06' => ['file' => 'public/templates/import_naskah/01_Pilot_Utama/P06_Scorecard_UMRAH.xlsx', 'label' => 'Pilot Utama (P06)'],
    'P07' => ['file' => 'public/templates/import_naskah/01_Pilot_Utama/P07_Scorecard_STAI_Paduka_Anambas.xlsx', 'label' => 'Pilot Utama (P07)'],
    'P08' => ['file' => 'public/templates/import_naskah/01_Pilot_Utama/P08_Scorecard_Politeknik_Negeri_Batam.xlsx', 'label' => 'Pilot Utama (P08)'],
    'P09' => ['file' => 'public/templates/import_naskah/01_Pilot_Utama/P09_Scorecard_STAI_Natuna.xlsx', 'label' => 'Pilot Utama (P09)'],
    'P10' => ['file' => 'public/templates/import_naskah/01_Pilot_Utama/P10_Scorecard_STISIP_Bunda_Tanah_Melayu.xlsx', 'label' => 'Pilot Utama (P10)'],
    'C01' => ['file' => 'public/templates/import_naskah/02_Portofolio_Pengayaan/C01_Scorecard_STAIN_Sultan_Abdurrahman.xlsx', 'label' => 'Portofolio Pengayaan (C01)'],
    'C02' => ['file' => 'public/templates/import_naskah/02_Portofolio_Pengayaan/C02_Scorecard_Politeknik_Bintan_Cakrawala.xlsx', 'label' => 'Portofolio Pengayaan (C02)'],
    'C03' => ['file' => 'public/templates/import_naskah/02_Portofolio_Pengayaan/C03_Scorecard_Universitas_Ibnu_Sina.xlsx', 'label' => 'Portofolio Pengayaan (C03)'],
    'C04' => ['file' => 'public/templates/import_naskah/02_Portofolio_Pengayaan/C04_Scorecard_UNRIKA.xlsx', 'label' => 'Portofolio Pengayaan (C04)'],
    'C05' => ['file' => 'public/templates/import_naskah/02_Portofolio_Pengayaan/C01_Scorecard_STAIN_Sultan_Abdurrahman.xlsx', 'label' => 'Template Pengayaan (C05)'],
];

/**
 * Fungsi pembantu: Parse seluruh sheet pada file workbook .xlsx
 */
function parseFullWorkbookXlsx(string $filePath): array {
    $zip = new ZipArchive();
    if ($zip->open($filePath) !== true) return [];

    // 1. Shared Strings
    $sharedStrings = [];
    $ssContent = $zip->getFromName('xl/sharedStrings.xml');
    if ($ssContent) {
        $ssXml = @simplexml_load_string($ssContent);
        if ($ssXml !== false) {
            foreach ($ssXml->xpath('//si|//x:si') as $si) {
                $tParts = [];
                foreach ($si->xpath('.//t|.//x:t') as $t) {
                    $tParts[] = (string)$t;
                }
                $sharedStrings[] = implode('', $tParts);
            }
        }
    }

    // 2. Mapping Relationships & Sheets
    $relsContent = $zip->getFromName('xl/_rels/workbook.xml.rels');
    $relMap = [];
    if ($relsContent) {
        preg_match_all('/<Relationship[^>]+>/i', $relsContent, $rm);
        foreach ($rm[0] as $tag) {
            preg_match('/Id=\"([^\"]+)\"/i', $tag, $mId);
            preg_match('/Target=\"([^\"]+)\"/i', $tag, $mTgt);
            if (!empty($mId[1]) && !empty($mTgt[1])) {
                $t = ltrim($mTgt[1], '/');
                if (!str_starts_with($t, 'xl/')) $t = 'xl/' . $t;
                $relMap[$mId[1]] = $t;
            }
        }
    }

    $wbContent = $zip->getFromName('xl/workbook.xml');
    $sheetTargets = [];
    if ($wbContent) {
        preg_match_all('/<[^>]*sheet[^>]+>/i', $wbContent, $sm);
        foreach ($sm[0] as $tag) {
            preg_match('/name=\"([^\"]+)\"/i', $tag, $mName);
            preg_match('/(?:r:id|\bid)=\"([^\"]+)\"/i', $tag, $mRid);
            if (!empty($mName[1]) && !empty($mRid[1]) && isset($relMap[$mRid[1]])) {
                $sheetTargets[htmlspecialchars_decode($mName[1])] = $relMap[$mRid[1]];
            }
        }
    }

    // 3. Ekstraksi Data Tiap Sheet
    $result = [];
    foreach ($sheetTargets as $sheetName => $targetFile) {
        $sXmlContent = $zip->getFromName($targetFile);
        if (!$sXmlContent) continue;
        $sXml = @simplexml_load_string($sXmlContent);
        if ($sXml === false) continue;

        $rows = [];
        foreach ($sXml->xpath('//row|//x:row') as $r) {
            $rNum = (int)$r['r'];
            $cells = [];
            foreach ($r->xpath('./c|./x:c') as $c) {
                $ref = (string)$c['r'];
                preg_match('/^([A-Z]+)/', $ref, $m);
                $colLetters = $m[1] ?? 'A';
                $colNum = 0;
                for ($ci = 0; $ci < strlen($colLetters); $ci++) {
                    $colNum = $colNum * 26 + (ord($colLetters[$ci]) - ord('A') + 1);
                }

                $type = (string)$c['t'];
                $vNodes = $c->xpath('.//v|.//x:v');
                $rawVal = !empty($vNodes) ? (string)$vNodes[0] : '';

                if ($type === 's' && isset($sharedStrings[(int)$rawVal])) {
                    $cellVal = $sharedStrings[(int)$rawVal];
                } elseif ($type === 'inlineStr') {
                    $isNodes = $c->xpath('.//is//t|.//x:is//x:t');
                    $cellVal = !empty($isNodes) ? (string)$isNodes[0] : '';
                } else {
                    $cellVal = $rawVal;
                }
                $cells[$colNum] = trim($cellVal);
            }
            if (!empty($cells)) {
                $rows[$rNum] = $cells;
            }
        }
        $result[$sheetName] = $rows;
    }

    $zip->close();
    return $result;
}

// ── PROSES IMPORT WORKBOOK EXCEL ──────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'import_naskah') {
    $targetId = (int)($_POST['mitra_id'] ?? 0);
    $stmtM = $pdo->prepare('SELECT * FROM mitra_kinerja WHERE id = ?');
    $stmtM->execute([$targetId]);
    $targetMitra = $stmtM->fetch();

    if (!$targetMitra) {
        $errors[] = 'Data naskah kerja sama tujuan tidak ditemukan.';
    } elseif (!isset($_FILES['excel_file']) || $_FILES['excel_file']['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'Silakan pilih file Excel (.xlsx) yang valid untuk di-import.';
    } else {
        $fileInfo = $_FILES['excel_file'];
        $ext = strtolower(pathinfo($fileInfo['name'], PATHINFO_EXTENSION));

        if ($ext !== 'xlsx') {
            $errors[] = 'Format file tidak didukung. Harap unggah file spreadsheet Excel dengan ekstensi .xlsx.';
        } elseif ($fileInfo['size'] > 25 * 1024 * 1024) {
            $errors[] = 'Ukuran file melebihi batas maksimum 25 MB.';
        } else {
            $parsedWb = parseFullWorkbookXlsx($fileInfo['tmp_name']);
            if (empty($parsedWb)) {
                $errors[] = 'Gagal membaca isi file Excel. Pastikan file tidak terkunci atau rusak.';
            } else {
                // Temukan Sheet Baseline & Sheet Scorecard
                $baseSheetName = '';
                $scSheetName = '';
                $picSheetName = '';
                $kegSheetName = '';
                $utlSheetName = '';

                foreach (array_keys($parsedWb) as $sName) {
                    $upper = strtoupper($sName);
                    if (str_contains($upper, 'BASELINE')) $baseSheetName = $sName;
                    elseif (str_contains($upper, 'SCORECARD')) $scSheetName = $sName;
                    elseif (str_contains($upper, 'IDENTITAS') || str_contains($upper, 'PIC')) $picSheetName = $sName;
                    elseif (str_contains($upper, 'PELAKSANAAN') || str_contains($upper, 'KEGIATAN')) $kegSheetName = $sName;
                    elseif (str_contains($upper, 'USULAN') || str_contains($upper, 'TINDAK LANJUT')) $utlSheetName = $sName;
                }

                $updatedBaseline = 0;
                $updatedScorecard = 0;
                $fileNaskahExtracted = null;

                // 1. IMPORT DATA BASELINE
                if ($baseSheetName && !empty($parsedWb[$baseSheetName])) {
                    $baseRows = $parsedWb[$baseSheetName];

                    // Baca metadata pemeriksa & cut-off jika ada
                    $pemeriksaVal = $baseRows[5][3] ?? $baseRows[5][4] ?? '';
                    $cutoffVal = $baseRows[8][2] ?? $baseRows[8][3] ?? '';
                    if (!empty($cutoffVal) && preg_match('/(\d{4}-\d{2}-\d{2})/', $cutoffVal, $mCut)) {
                        $cutoffDate = $mCut[1];
                    } else {
                        $cutoffDate = date('Y-m-d');
                    }

                    // Loop baris 13 s.d 24 (12 Elemen Baseline)
                    for ($r = 12; $r <= 35; $r++) {
                        if (!isset($baseRows[$r])) continue;
                        $row = $baseRows[$r];
                        $col1 = $row[1] ?? '';
                        if (!is_numeric($col1)) continue;
                        $elNum = (int)$col1;
                        if ($elNum < 1 || $elNum > 12) continue;

                        $rawStatus = strtoupper(trim($row[6] ?? 'BELUM DIISI'));
                        $fakta = trim($row[7] ?? '');
                        $linkBukti = trim($row[8] ?? '');
                        $catatan = trim($row[9] ?? '');

                        // Normalisasi status
                        $validStatuses = ['TERVERIFIKASI', 'BELUM TERVERIFIKASI', 'BELUM TERSEDIA', 'TIDAK RELEVAN', 'BELUM DIISI'];
                        $finalStatus = 'BELUM DIISI';
                        foreach ($validStatuses as $vs) {
                            if (str_contains($rawStatus, $vs)) {
                                $finalStatus = $vs;
                                break;
                            }
                        }

                        // Update atau insert ke baseline_elemen
                        $stmtCheck = $pdo->prepare('SELECT id FROM baseline_elemen WHERE mitra_id = ? AND nomor_elemen = ?');
                        $stmtCheck->execute([$targetId, $elNum]);
                        if ($stmtCheck->fetch()) {
                            $stmtU = $pdo->prepare('UPDATE baseline_elemen SET status = ?, fakta_pemeriksaan = ?, link_sumber_bukti = ?, catatan = ? WHERE mitra_id = ? AND nomor_elemen = ?');
                            $stmtU->execute([$finalStatus, $fakta, $linkBukti, $catatan, $targetId, $elNum]);
                        } else {
                            $def = BASELINE_12_DEFS[$elNum] ?? ['kelompok' => 'UMUM', 'nama' => "Elemen $elNum", 'yang_diperiksa' => '', 'sumber_minimum' => ''];
                            $stmtI = $pdo->prepare('INSERT INTO baseline_elemen (mitra_id, nomor_elemen, kelompok, nama_elemen, yang_diperiksa, sumber_bukti_minimum, status, fakta_pemeriksaan, link_sumber_bukti, catatan) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
                            $stmtI->execute([$targetId, $elNum, $def['kelompok'], $def['nama'], $def['yang_diperiksa'], $def['sumber_minimum'], $finalStatus, $fakta, $linkBukti, $catatan]);
                        }
                        $updatedBaseline++;

                        // Jika elemen 1 memiliki URL naskah PDF / P2MA, simpan ke file_naskah
                        if ($elNum === 1 && !empty($linkBukti)) {
                            if (preg_match('/^https?:\/\/[^\s]+/i', $linkBukti, $mUrl)) {
                                $fileNaskahExtracted = $mUrl[0];
                            } elseif (str_starts_with($linkBukti, 'public/uploads/')) {
                                $fileNaskahExtracted = $linkBukti;
                            }
                        }
                    }

                    // Update ringkasan status baseline pada mitra_kinerja
                    $updateSql = 'UPDATE mitra_kinerja SET baseline_status = \'TERVERIFIKASI / DIKUNCI\', baseline_locked_at = NOW(), baseline_locked_by = ?';
                    $params = [$user['id']];
                    if (!empty($pemeriksaVal)) {
                        $updateSql .= ', baseline_pemeriksa = ?';
                        $params[] = $pemeriksaVal;
                    }
                    if (!empty($cutoffDate)) {
                        $updateSql .= ', cutoff_date = ?';
                        $params[] = $cutoffDate;
                    }
                    if (!empty($fileNaskahExtracted)) {
                        $updateSql .= ', file_naskah = ?';
                        $params[] = $fileNaskahExtracted;
                    }
                    $updateSql .= ' WHERE id = ?';
                    $params[] = $targetId;
                    $pdo->prepare($updateSql)->execute($params);
                }

                // 2. IMPORT DATA SCORECARD
                if ($scSheetName && !empty($parsedWb[$scSheetName])) {
                    $scRows = $parsedWb[$scSheetName];

                    // Bobot default V2.1
                    $weights = ['I1' => 10, 'I2' => 15, 'I3' => 15, 'I4' => 20, 'I5' => 20, 'I6' => 10, 'I7' => 10];

                    foreach ($scRows as $rIdx => $row) {
                        $col1 = strtoupper(trim($row[1] ?? ''));
                        if (!preg_match('/^I[1-7]$/', $col1)) continue;

                        $kodeInd = $col1;
                        $bobot = isset($weights[$kodeInd]) ? $weights[$kodeInd] : (int)($row[2] ?? 10);
                        $refBaseline = trim($row[3] ?? '');
                        $rawStatus = strtoupper(trim($row[5] ?? ''));
                        $kondisi = trim($row[6] ?? '');
                        $rawSkor = trim($row[7] ?? '');
                        $alasanSkor = trim($row[8] ?? '');
                        $catatanTl = trim($row[9] ?? '');

                        // Normalisasi status pemeriksaan
                        $statusPem = 'BELUM DITELAAH';
                        if (str_contains($rawStatus, 'MEMADAI') && !str_contains($rawStatus, 'BELUM')) $statusPem = 'BUKTI MEMADAI';
                        elseif (str_contains($rawStatus, 'CUKUP')) $statusPem = 'BUKTI CUKUP';
                        elseif (str_contains($rawStatus, 'BELUM DAPAT') || str_contains($rawStatus, 'BELUM DINILAI')) $statusPem = 'BELUM DAPAT DINILAI';
                        elseif (str_contains($rawStatus, 'BELUM MEMADAI')) $statusPem = 'BUKTI BELUM MEMADAI';
                        elseif (str_contains($rawStatus, 'DAPAT DINILAI')) $statusPem = 'DAPAT DINILAI';

                        $skor = is_numeric($rawSkor) ? (int)$rawSkor : null;
                        $nilai = $skor !== null ? round(($skor / 4.0) * $bobot, 2) : null;

                        // Cek apakah indikator sudah ada
                        $stmtCheck = $pdo->prepare('SELECT id FROM indikator_skor WHERE mitra_id = ? AND kode_indikator = ?');
                        $stmtCheck->execute([$targetId, $kodeInd]);
                        if ($stmtCheck->fetch()) {
                            $stmtU = $pdo->prepare('UPDATE indikator_skor SET status_pemeriksaan = ?, kondisi_saat_ini = ?, skor = ?, alasan_skor = ?, catatan_tindak_lanjut = ?, nilai = ? WHERE mitra_id = ? AND kode_indikator = ?');
                            $stmtU->execute([$statusPem, $kondisi, $skor, $alasanSkor, $catatanTl, $nilai, $targetId, $kodeInd]);
                        } else {
                            $stmtI = $pdo->prepare('INSERT INTO indikator_skor (mitra_id, kode_indikator, deskripsi, bobot, referensi_baseline, status_pemeriksaan, kondisi_saat_ini, skor, alasan_skor, catatan_tindak_lanjut, nilai) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
                            $stmtI->execute([$targetId, $kodeInd, "Indikator $kodeInd", $bobot, $refBaseline, $statusPem, $kondisi, $skor, $alasanSkor, $catatanTl, $nilai]);
                        }
                        $updatedScorecard++;
                    }

                    // Sinkronkan total skor dan status scorecard
                    syncStatusScorecard($pdo, $targetId);
                }

                // 3. IMPORT DATA IDENTITAS & PIC (JIKA ADA)
                if ($picSheetName && !empty($parsedWb[$picSheetName])) {
                    $picRows = $parsedWb[$picSheetName];
                    $picInternalFound = '';
                    $picMitraFound = '';
                    foreach ($picRows as $pRow) {
                        $label = strtolower(trim($pRow[1] ?? ''));
                        $val = trim($pRow[2] ?? '');
                        if (str_contains($label, 'pic mitra') || (str_contains($label, 'nama') && str_contains($label, 'mitra'))) {
                            if (!empty($val)) $picMitraFound = $val;
                        } elseif (str_contains($label, 'pic internal') || str_contains($label, 'pengampu')) {
                            if (!empty($val)) $picInternalFound = $val;
                        }
                    }
                    if ($picInternalFound || $picMitraFound) {
                        $uSql = 'UPDATE mitra_kinerja SET ';
                        $uParams = [];
                        if ($picInternalFound) { $uSql .= 'pic_internal = ?, '; $uParams[] = $picInternalFound; }
                        if ($picMitraFound) { $uSql .= 'pic_mitra = ?, '; $uParams[] = $picMitraFound; }
                        $uSql = rtrim($uSql, ', ') . ' WHERE id = ?';
                        $uParams[] = $targetId;
                        $pdo->prepare($uSql)->execute($uParams);
                    }
                }

                logAudit($targetId, $user['id'], 'IMPORT_EXCEL', "Import workbook Excel untuk {$targetMitra['kode']}: {$updatedBaseline} elemen baseline, {$updatedScorecard} indikator scorecard diperbarui.");
                $success = "Data untuk naskah <strong>{$targetMitra['kode']} - {$targetMitra['nama_mitra']}</strong> berhasil di-import!<br>"
                         . "&bull; {$updatedBaseline} elemen Baseline FIX diperbarui.<br>"
                         . "&bull; {$updatedScorecard} indikator Scorecard disinkronkan ke sistem.<br>"
                         . ($fileNaskahExtracted ? "&bull; Tautan naskah resmi P2MA terhubung secara otomatis.<br>" : "");
            }
        }
    }
}

// Ambil daftar seluruh naskah kerja sama
$stmt = $pdo->query('
    SELECT m.*, 
           (SELECT COUNT(*) FROM baseline_elemen WHERE mitra_id = m.id AND status = "TERVERIFIKASI") as total_terverifikasi,
           (SELECT COUNT(*) FROM indikator_skor WHERE mitra_id = m.id AND skor IS NOT NULL) as total_terisi_skor
    FROM mitra_kinerja m 
    ORDER BY m.portofolio DESC, m.kode ASC
');
$daftarMitra = $stmt->fetchAll();

require __DIR__ . '/includes/header.php';
?>

<div class="content-header" style="margin-bottom:20px;">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:12px;">
        <div>
            <h1 style="margin:0 0 4px 0;font-size:22px;color:#0f172a;display:flex;align-items:center;gap:8px;">
                <span>📥</span> Menu Import Data Naskah
            </h1>
            <p style="margin:0;font-size:13px;color:#64748b;">
                Perbarui data <strong>Baseline FIX (12 Elemen)</strong> dan <strong>Scorecard</strong> kerja sama secara langsung melalui file spreadsheet Excel (.xlsx).
            </p>
        </div>
        <div style="display:flex;gap:8px;flex-wrap:wrap;">
            <a href="mitra_manage.php" class="btn btn-outline" style="font-size:12px;">
                &larr; Manajemen Naskah
            </a>
            <a href="baseline.php" class="btn btn-outline" style="font-size:12px;">
                Buka Baseline &rarr;
            </a>
            <a href="scorecard.php" class="btn btn-primary" style="font-size:12px;">
                Buka Scorecard &rarr;
            </a>
        </div>
    </div>
</div>

<?php if ($success): ?>
<div class="alert alert-success" style="margin-bottom:20px;border-left:4px solid #10b981;background:#ecfdf5;color:#065f46;padding:14px 18px;border-radius:8px;">
    <div style="font-weight:700;font-size:14px;margin-bottom:4px;">Berhasil Memproses Import!</div>
    <div style="font-size:13px;line-height:1.5;"><?= $success ?></div>
</div>
<?php endif; ?>

<?php if (!empty($errors)): ?>
<div class="alert alert-danger" style="margin-bottom:20px;border-left:4px solid #ef4444;background:#fef2f2;color:#991b1b;padding:14px 18px;border-radius:8px;">
    <div style="font-weight:700;font-size:14px;margin-bottom:4px;">Terjadi Kesalahan:</div>
    <ul style="margin:4px 0 0 18px;padding:0;font-size:13px;">
        <?php foreach ($errors as $err): ?>
        <li><?= h($err) ?></li>
        <?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>

<!-- KARTU INFORMASI PANDUAN IMPORT -->
<div class="card" style="margin-bottom:24px;border:1px solid #e2e8f0;background:#ffffff;border-radius:10px;padding:18px 20px;">
    <div style="display:flex;gap:16px;align-items:flex-start;">
        <div style="font-size:28px;line-height:1;">💡</div>
        <div style="flex:1;">
            <h3 style="margin:0 0 6px 0;font-size:15px;color:#1e293b;font-weight:700;">Petunjuk Penggunaan Template & Mekanisme Import:</h3>
            <div style="font-size:13px;color:#475569;line-height:1.6;">
                1. Setiap baris naskah kerja sama di bawah memiliki <strong>Template Excel (.xlsx)</strong> resmi yang bersumber dari folder <code>01_Pilot_Utama</code> dan <code>02_Portofolio_Pengayaan</code>.<br>
                2. Unduh template kerja sama terkait dengan menekan tombol <strong>[⬇️ Unduh Template]</strong> pada kolom Template.<br>
                3. Setelah file diisi oleh tim pengampu/pemeriksa, klik tombol <strong>[📥 Import Data]</strong> pada kolom Import untuk mengunggah file tersebut.<br>
                4. Sistem akan secara otomatis membaca dan memperbarui data <strong>12 Elemen Baseline</strong>, <strong>Nilai Scorecard</strong>, serta menautkan naskah resmi P2MA.
            </div>
        </div>
    </div>
</div>

<!-- TABEL DATA NASKAH & IMPORT -->
<div class="card" style="border:1px solid #e2e8f0;background:#ffffff;border-radius:10px;overflow:hidden;">
    <div style="padding:16px 20px;border-bottom:1px solid #e2e8f0;display:flex;justify-content:space-between;align-items:center;background:#f8fafc;">
        <div>
            <h3 style="margin:0;font-size:16px;color:#0f172a;font-weight:700;">Daftar Naskah Kerja Sama & Fitur Import</h3>
            <div style="font-size:12px;color:#64748b;margin-top:2px;">Total: <?= count($daftarMitra) ?> Naskah Kerja Sama Terdaftar</div>
        </div>
    </div>

    <div class="table-responsive" style="overflow-x:auto;">
        <table class="table" style="width:100%;margin:0;border-collapse:collapse;font-size:13px;">
            <thead>
                <tr style="background:#f1f5f9;color:#334155;text-align:left;border-bottom:1px solid #cbd5e1;">
                    <th style="padding:12px 14px;width:70px;text-align:center;">Kode</th>
                    <th style="padding:12px 14px;width:110px;">Portofolio</th>
                    <th style="padding:12px 14px;">Mitra & Judul Kerja Sama</th>
                    <th style="padding:12px 14px;width:90px;text-align:center;">Bidang</th>
                    <th style="padding:12px 14px;width:120px;text-align:center;">Baseline</th>
                    <th style="padding:12px 14px;width:130px;text-align:center;">Scorecard</th>
                    <th style="padding:12px 14px;width:150px;text-align:center;">Template Excel</th>
                    <th style="padding:12px 14px;width:160px;text-align:center;">Aksi Import</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($daftarMitra as $m): 
                    $tInfo = $templateMap[$m['kode']] ?? null;
                    $isLocked = str_contains($m['baseline_status'] ?? '', 'DIKUNCI');
                    $scorecardStatus = $m['status_scorecard'] ?? 'BELUM LENGKAP';
                ?>
                <tr style="border-bottom:1px solid #f1f5f9;transition:background 0.15s ease;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'">
                    <td style="padding:12px 14px;text-align:center;font-weight:700;color:#1e40af;">
                        <?= h($m['kode']) ?>
                    </td>
                    <td style="padding:12px 14px;">
                        <?php if ($m['portofolio'] === 'PILOT'): ?>
                            <span class="badge badge-primary" style="font-size:10.5px;padding:3px 8px;">Pilot Utama</span>
                        <?php else: ?>
                            <span class="badge badge-secondary" style="font-size:10.5px;padding:3px 8px;">Cadangan</span>
                        <?php endif; ?>
                    </td>
                    <td style="padding:12px 14px;">
                        <div style="font-weight:600;color:#0f172a;margin-bottom:2px;font-size:13.5px;">
                            <?= h($m['nama_mitra']) ?>
                        </div>
                        <div style="font-size:12px;color:#64748b;line-height:1.35;">
                            <?= h($m['judul'] ?: '-') ?>
                        </div>
                        <?php if (!empty($m['file_naskah'])): ?>
                            <div style="margin-top:4px;">
                                <a href="<?= h($m['file_naskah']) ?>" target="_blank" rel="noopener noreferrer" style="font-size:11px;color:#2563eb;text-decoration:none;display:inline-flex;align-items:center;gap:3px;">
                                    <span>📄</span> Naskah P2MA Resmi &rarr;
                                </a>
                            </div>
                        <?php endif; ?>
                    </td>
                    <td style="padding:12px 14px;text-align:center;">
                        <span class="badge badge-outline" style="font-size:11px;font-weight:600;">
                            <?= h($m['bidang'] ?: ($m['jenis'] ?: 'AHU')) ?>
                        </span>
                    </td>
                    <td style="padding:12px 14px;text-align:center;">
                        <?php if ($isLocked): ?>
                            <span class="badge badge-success" style="font-size:10.5px;padding:3px 7px;display:inline-flex;align-items:center;gap:3px;">
                                <span>🔒</span> Terkunci
                            </span>
                            <div style="font-size:10.5px;color:#64748b;margin-top:2px;">(12 Elemen)</div>
                        <?php else: ?>
                            <span class="badge badge-warning" style="font-size:10.5px;padding:3px 7px;">
                                Dalam Proses
                            </span>
                            <div style="font-size:10.5px;color:#64748b;margin-top:2px;"><?= $m['total_terverifikasi'] ?>/12 Terisi</div>
                        <?php endif; ?>
                    </td>
                    <td style="padding:12px 14px;text-align:center;">
                        <?php if ($scorecardStatus === 'FINAL' || $scorecardStatus === 'FINAL/TERVALIDASI'): ?>
                            <span class="badge badge-success" style="font-size:10.5px;padding:3px 7px;">Tervalidasi</span>
                        <?php elseif ($scorecardStatus === 'SIAP_VALIDASI' || $scorecardStatus === 'SIAP DIVALIDASI'): ?>
                            <span class="badge badge-info" style="font-size:10.5px;padding:3px 7px;">Siap Validasi</span>
                        <?php else: ?>
                            <span class="badge badge-secondary" style="font-size:10.5px;padding:3px 7px;">Belum Lengkap</span>
                        <?php endif; ?>
                        <?php if ($m['total_skor'] !== null): ?>
                            <div style="font-size:11px;font-weight:700;color:#0f172a;margin-top:2px;">
                                <?= number_format((float)$m['total_skor'], 1) ?>
                            </div>
                        <?php endif; ?>
                    </td>
                    <td style="padding:12px 14px;text-align:center;">
                        <?php if ($tInfo && file_exists(__DIR__ . '/' . $tInfo['file'])): ?>
                            <a href="<?= h($tInfo['file']) ?>" download class="btn btn-outline btn-sm" style="font-size:11px;display:inline-flex;align-items:center;gap:4px;padding:5px 10px;color:#047857;border-color:#a7f3d0;background:#ecfdf5;">
                                <span>⬇️</span> Unduh Template
                            </a>
                        <?php else: ?>
                            <a href="public/templates/template_gate0_dalam_negeri.xlsx" download class="btn btn-outline btn-sm" style="font-size:11px;display:inline-flex;align-items:center;gap:4px;padding:5px 10px;">
                                <span>⬇️</span> Template Umum
                            </a>
                        <?php endif; ?>
                    </td>
                    <td style="padding:12px 14px;text-align:center;">
                        <button type="button" class="btn btn-primary btn-sm" onclick="openImportModal(<?= $m['id'] ?>, '<?= h($m['kode']) ?>', '<?= h(addslashes($m['nama_mitra'])) ?>')" style="font-size:11.5px;display:inline-flex;align-items:center;gap:4px;padding:6px 12px;font-weight:600;">
                            <span>📥</span> Import
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- MODAL POPUP UPLOAD & IMPORT EXCEL -->
<div id="modalImport" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,0.65);z-index:9999;align-items:center;justify-content:center;padding:16px;">
    <div style="background:#ffffff;border-radius:12px;width:100%;max-width:540px;box-shadow:0 20px 25px -5px rgba(0,0,0,0.2);overflow:hidden;animation:fadeIn 0.2s ease-out;">
        <div style="padding:16px 20px;border-bottom:1px solid #e2e8f0;display:flex;justify-content:space-between;align-items:center;background:#f8fafc;">
            <div style="display:flex;align-items:center;gap:8px;">
                <span style="font-size:20px;">📥</span>
                <h3 style="margin:0;font-size:16px;font-weight:700;color:#0f172a;">Import Data Naskah (.xlsx)</h3>
            </div>
            <button type="button" onclick="closeImportModal()" style="border:none;background:transparent;font-size:20px;cursor:pointer;color:#64748b;">&times;</button>
        </div>

        <form method="POST" enctype="multipart/form-data" style="margin:0;">
            <input type="hidden" name="action" value="import_naskah">
            <input type="hidden" name="mitra_id" id="modalMitraId" value="0">

            <div style="padding:20px;">
                <div style="margin-bottom:16px;padding:12px 14px;background:#eff6ff;border:1px solid #bfdbfe;border-radius:8px;">
                    <div style="font-size:11px;font-weight:700;color:#1e40af;text-transform:uppercase;letter-spacing:0.5px;">Target Naskah Kerja Sama:</div>
                    <div id="modalMitraLabel" style="font-size:14px;font-weight:700;color:#1e3a8a;margin-top:2px;">-</div>
                </div>

                <div style="margin-bottom:18px;">
                    <label style="display:block;font-size:13px;font-weight:600;color:#334155;margin-bottom:6px;">
                        Pilih Berkas Spreadsheet Excel (.xlsx) <span style="color:#ef4444;">*</span>
                    </label>
                    <input type="file" name="excel_file" accept=".xlsx" required style="width:100%;padding:10px;border:1px solid #cbd5e1;border-radius:6px;font-size:13px;background:#f8fafc;">
                    <div style="font-size:11.5px;color:#64748b;margin-top:4px;">
                        Format didukung: <strong>.xlsx</strong> (Maks. 25 MB). Gunakan template resmi dari folder <code>01_Pilot_Utama</code> atau <code>02_Portofolio_Pengayaan</code>.
                    </div>
                </div>

                <div style="font-size:12.5px;color:#475569;background:#f1f5f9;padding:12px 14px;border-radius:6px;line-height:1.5;">
                    <div style="font-weight:600;margin-bottom:4px;color:#1e293b;">Data yang akan otomatis di-update:</div>
                    &bull; <strong>Baseline FIX:</strong> Status, fakta pemeriksaan, tautan bukti pada 12 Elemen.<br>
                    &bull; <strong>Scorecard:</strong> Status pemeriksaan, kondisi saat ini, skor & alasan skor I1-I7.<br>
                    &bull; <strong>Tautan Naskah Resmi:</strong> Tautan naskah P2MA pada Elemen 1 otomatis ditautkan ke profil kerja sama.
                </div>
            </div>

            <div style="padding:14px 20px;border-top:1px solid #e2e8f0;background:#f8fafc;display:flex;justify-content:flex-end;gap:10px;">
                <button type="button" onclick="closeImportModal()" class="btn btn-outline" style="font-size:12.5px;padding:7px 14px;">
                    Batal
                </button>
                <button type="submit" class="btn btn-primary" style="font-size:12.5px;padding:7px 18px;font-weight:600;">
                    📥 Mulai Proses Import
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openImportModal(id, kode, nama) {
    document.getElementById('modalMitraId').value = id;
    document.getElementById('modalMitraLabel').innerHTML = '<strong>[' + kode + ']</strong> ' + nama;
    var modal = document.getElementById('modalImport');
    modal.style.display = 'flex';
}

function closeImportModal() {
    var modal = document.getElementById('modalImport');
    modal.style.display = 'none';
}

// Tutup modal jika klik di luar area konten
window.addEventListener('click', function(e) {
    var modal = document.getElementById('modalImport');
    if (e.target === modal) {
        closeImportModal();
    }
});
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>
