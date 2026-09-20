<?php
/**
 * Helper akses data: mengambil baris dari DB lalu menjalankan business logic
 * di functions.php supaya PHP dan tampilan selalu konsisten dengan aturan
 * yang sama (tidak ada logika ganda antara form dan dashboard).
 */
require_once __DIR__ . '/functions.php';

/** Ambil ringkasan lengkap SEMUA naskah untuk dashboard & daftar. */
function getAllMitraSummary(PDO $pdo): array {
    $mitraRows = $pdo->query('SELECT * FROM mitra_kinerja ORDER BY kode')->fetchAll();
    $result = [];
    foreach ($mitraRows as $m) {
        $result[] = getMitraSummary($pdo, $m);
    }
    return $result;
}

/** Ambil ringkasan lengkap SATU naskah (dipakai juga oleh mitra_list & dashboard). */
function getMitraSummary(PDO $pdo, array $mitra): array {
    $stmt = $pdo->prepare('SELECT * FROM indikator_skor WHERE mitra_id = ? ORDER BY kode_indikator');
    $stmt->execute([$mitra['id']]);
    $indikatorRows = $stmt->fetchAll();

    $stmt = $pdo->prepare('SELECT * FROM early_warning WHERE mitra_id = ? ORDER BY dimensi');
    $stmt->execute([$mitra['id']]);
    $warningRows = $stmt->fetchAll();

    $stmt = $pdo->prepare('SELECT * FROM intervensi_pimpinan WHERE mitra_id = ? ORDER BY no_pemicu');
    $stmt->execute([$mitra['id']]);
    $pemicuRows = $stmt->fetchAll();

    $stmt = $pdo->prepare('SELECT * FROM validasi WHERE mitra_id = ?');
    $stmt->execute([$mitra['id']]);
    $validasi = $stmt->fetch() ?: ['status' => 'BELUM'];

    $stmt = $pdo->prepare('SELECT * FROM intervensi_usulan WHERE mitra_id = ?');
    $stmt->execute([$mitra['id']]);
    $usulan = $stmt->fetch() ?: ['upaya_dilakukan' => null, 'keputusan_diminta' => null];

    // --- Early warning: Masa berlaku 100% otomatis; 3 dimensi lain: status diturunkan dari kondisi ---
    $statusList = [];
    foreach ($warningRows as &$w) {
        if ($w['dimensi'] === 'Masa berlaku') {
            $mb = hitungMasaBerlaku($mitra['tanggal_berakhir'], $mitra['cutoff_date'], $mitra['status_tanggal']);
            $w['kondisi'] = $mb['kondisi'];
            $w['status']  = $mb['status'];
            $w['sisa_hari'] = $mb['sisa_hari'];
        } else {
            $w['status'] = statusDariKondisi($w['dimensi'], $w['kondisi'] ?? 'BELUM DIPERIKSA');
        }
        $statusList[] = $w['status'];
    }
    unset($w);

    $ringkasan = ringkasanIndikator($indikatorRows);
    $warning = warningTertinggi($statusList);
    $hasilUji = hasilUjiIntervensi($pemicuRows, $warning['status']);
    $cekUsulan = cekUsulanIntervensi($pemicuRows, $hasilUji, $usulan['upaya_dilakukan'], $usulan['keputusan_diminta']);
    $statusScorecard = hitungStatusScorecard($ringkasan['skor_lengkap'], $ringkasan['cek_lengkap_ok'], $validasi['status']);

    return [
        'mitra'            => $mitra,
        'indikator'        => $indikatorRows,
        'warning_rows'     => $warningRows,
        'pemicu_rows'      => $pemicuRows,
        'validasi'         => $validasi,
        'usulan'           => $usulan,
        'nilai_berjalan'   => $ringkasan['nilai_berjalan'],
        'kelengkapan'      => $ringkasan['kelengkapan'],
        'skor_lengkap'     => $ringkasan['skor_lengkap'],
        'cek_lengkap_ok'   => $ringkasan['cek_lengkap_ok'],
        'kategori'         => $ringkasan['kategori'],
        'status_scorecard' => $statusScorecard,
        'warning'          => $warning,
        'hasil_uji'        => $hasilUji,
        'cek_usulan'       => $cekUsulan,
    ];
}

/** Sinkronkan status_scorecard yang tersimpan di tabel mitra_kinerja (dipanggil setiap kali data disimpan). */
function syncStatusScorecard(PDO $pdo, int $mitraId): void {
    $stmt = $pdo->prepare('SELECT * FROM mitra_kinerja WHERE id = ?');
    $stmt->execute([$mitraId]);
    $mitra = $stmt->fetch();
    if (!$mitra) return;

    $summary = getMitraSummary($pdo, $mitra);
    $stmt = $pdo->prepare('UPDATE mitra_kinerja SET status_scorecard = ? WHERE id = ?');
    $stmt->execute([$summary['status_scorecard'], $mitraId]);
}

/* ============================================================
 * TINDAK LANJUT
 * ============================================================ */

/** Ambil data tindak lanjut, opsional difilter per mitra. */
function getTindakLanjut(PDO $pdo, ?int $mitraId = null, int $limit = 0): array {
    $sql = 'SELECT tl.*, mk.kode, mk.nama_mitra
            FROM tindak_lanjut tl
            JOIN mitra_kinerja mk ON tl.mitra_id = mk.id';
    $params = [];
    if ($mitraId) {
        $sql .= ' WHERE tl.mitra_id = ?';
        $params[] = $mitraId;
    }
    $sql .= ' ORDER BY tl.tenggat ASC';
    if ($limit > 0) {
        $sql .= ' LIMIT ' . (int)$limit;
    }
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

/* ============================================================
 * DASHBOARD STATISTICS
 * ============================================================ */

/** Statistik lengkap untuk dashboard. */
function getDashboardStats(array $all): array {
    $total = count($all);
    $pilotCount = 0;
    $efektif = 0;
    $perluPerhatian = 0;
    $berisiko = 0;
    $totalNilai = 0;
    $nilaiCount = 0;
    $aspekScores = ['I1' => [], 'I2' => [], 'I3' => [], 'I4' => [], 'I5' => [], 'I6' => []];

    foreach ($all as $s) {
        $m = $s['mitra'];
        if ($m['portofolio'] === 'Pilot Utama') $pilotCount++;

        $efektivitas = statusEfektivitas($s['kategori']);
        if ($efektivitas === 'Efektif') $efektif++;
        elseif ($efektivitas === 'Berisiko') $berisiko++;
        else $perluPerhatian++;

        if ($s['nilai_berjalan'] > 0) {
            $totalNilai += $s['nilai_berjalan'];
            $nilaiCount++;
        }

        foreach ($s['indikator'] as $ind) {
            if ($ind['nilai'] !== null) {
                $aspekScores[$ind['kode_indikator']][] = (float)$ind['nilai'];
            }
        }
    }

    $rataRataNilai = $nilaiCount > 0 ? round($totalNilai / $nilaiCount, 1) : 0;
    $aspekRataRata = [];
    foreach ($aspekScores as $kode => $scores) {
        $aspekRataRata[$kode] = count($scores) > 0 ? round(array_sum($scores) / count($scores), 0) : 0;
    }

    return compact('total', 'pilotCount', 'efektif', 'perluPerhatian', 'berisiko', 'rataRataNilai', 'aspekRataRata');
}
