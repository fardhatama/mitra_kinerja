<?php
/**
 * Logika bisnis inti Scorecard Efektivitas Mitra Kinerja.
 * DITERJEMAHKAN LANGSUNG DARI RUMUS EXCEL ASLI (bukan dari nilai cache),
 * dibaca dari Format_Scorecard_Efektivitas_MITRA_KINERJA_V1.1.xlsx dengan
 * data_only=False untuk melihat formula J13, D27-D32, B32, B43, D46, F21, H21, dst.
 *
 * PENTING: aturan-aturan ini SENGAJA ditegakkan di layer aplikasi (bukan
 * cuma di form HTML), karena field seperti skor punya syarat yang tidak
 * bisa diandalkan hanya dari client-side.
 */

const BOBOT_INDIKATOR = [
    'I1' => 15, 'I2' => 20, 'I3' => 20, 'I4' => 15, 'I5' => 20, 'I6' => 10,
];

/**
 * Hitung status "Cek" untuk satu baris indikator.
 * Persis rumus J13: =IF(G13="","BELUM DIISI",IF(E13="BELUM DIPERIKSA","PERIKSA BUKTI",
 *   IF(OR(E13="BUKTI BELUM CUKUP",E13="BELUM DAPAT DINILAI"),"HAPUS SKOR",
 *      IF(F13="","TULIS TEMUAN/BUKTI",IF(H13="","TULIS ALASAN","OK")))))
 * Urutan asli: skor kosong dicek PALING DULU, baru status/temuan/alasan.
 */
function hitungCekIndikator(array $row): string {
    $status  = $row['status_pemeriksaan'] ?? 'BELUM DIPERIKSA';
    $temuan  = trim((string)($row['temuan_bukti'] ?? ''));
    $skor    = $row['skor'];
    $alasan  = trim((string)($row['alasan_skor'] ?? ''));

    if ($skor === null) {
        return 'BELUM DIISI';
    }
    if ($status === 'BELUM DIPERIKSA') {
        return 'PERIKSA BUKTI';
    }
    if ($status === 'BUKTI BELUM CUKUP' || $status === 'BELUM DAPAT DINILAI') {
        return 'HAPUS SKOR';
    }
    if ($temuan === '') {
        return 'TULIS TEMUAN/BUKTI';
    }
    if ($alasan === '') {
        return 'TULIS ALASAN';
    }
    return 'OK';
}

/** Nilai indikator = skor/4 * bobot (null jika skor belum ada). Persis rumus I13. */
function hitungNilaiIndikator(?int $skor, int $bobot): ?float {
    if ($skor === null) return null;
    return round(($skor / 4) * $bobot, 2);
}

/**
 * Ringkasan 6 indikator suatu naskah.
 * Persis rumus:
 *   B21 (Nilai berjalan)  = jika tak ada skor sama sekali -> kosong, else SUM(nilai)
 *   D21 (Kelengkapan)     = COUNT(skor terisi) / 6   <-- BUKAN berdasar Cek=OK
 *   F21 (Kategori)        = jika skor<6 terisi -> BELUM LENGKAP
 *                            jika Cek OK <6 -> BELUM FINAL
 *                            else kategori dari nilai
 *   H21 (Status Scorecard)= jika skor<6 terisi -> BELUM LENGKAP
 *                            jika Cek OK <6 -> PERLU DILENGKAPI
 *                            else FINAL/TERVALIDASI | PERLU PERBAIKAN | SIAP DIVALIDASI
 */
function ringkasanIndikator(array $indikatorRows): array {
    $skorTerisi = 0;
    $cekOkCount = 0;
    $nilaiBerjalan = 0.0;

    foreach ($indikatorRows as $row) {
        if ($row['skor'] !== null) {
            $skorTerisi++;
            $nilaiBerjalan += (float)($row['nilai'] ?? 0);
        }
        if (hitungCekIndikator($row) === 'OK') {
            $cekOkCount++;
        }
    }

    $n = count($indikatorRows) ?: 6;
    $kelengkapan = round(($skorTerisi / $n) * 100);
    $skorLengkap = ($skorTerisi === $n);
    $cekLengkapOk = ($cekOkCount === $n);

    if (!$skorLengkap) {
        $kategori = 'BELUM LENGKAP';
    } elseif (!$cekLengkapOk) {
        $kategori = 'BELUM FINAL';
    } else {
        $kategori = kategoriDariNilai($nilaiBerjalan);
    }

    return [
        'nilai_berjalan' => round($nilaiBerjalan, 2),
        'kelengkapan'    => $kelengkapan,
        'skor_lengkap'   => $skorLengkap,
        'cek_lengkap_ok' => $cekLengkapOk,
        'kategori'       => $kategori,
    ];
}

/** Kategori nilai: 75-100 PRODUKTIF, 50-<75 BERJALAN, 25-<50 PERLU AKTIVASI, <25 KRITIS. */
function kategoriDariNilai(float $nilai): string {
    if ($nilai >= 75) return 'PRODUKTIF';
    if ($nilai >= 50) return 'BERJALAN';
    if ($nilai >= 25) return 'PERLU AKTIVASI';
    return 'KRITIS';
}

/**
 * Status Scorecard. Persis rumus H21.
 *   - skor belum 6/6 terisi          -> BELUM LENGKAP
 *   - skor 6/6 tapi Cek belum semua OK -> PERLU DILENGKAPI
 *   - Cek 6/6 OK, validasi DISETUJUI  -> FINAL/TERVALIDASI
 *   - Cek 6/6 OK, validasi PERLU PERBAIKAN -> PERLU PERBAIKAN
 *   - Cek 6/6 OK, validasi lainnya    -> SIAP DIVALIDASI
 */
function hitungStatusScorecard(bool $skorLengkap, bool $cekLengkapOk, string $statusValidasi): string {
    if (!$skorLengkap) return 'BELUM LENGKAP';
    if (!$cekLengkapOk) return 'PERLU DILENGKAPI';
    if ($statusValidasi === 'DISETUJUI') return 'FINAL/TERVALIDASI';
    if ($statusValidasi === 'PERLU PERBAIKAN') return 'PERLU PERBAIKAN';
    return 'SIAP DIVALIDASI';
}

/* ============================================================
 * EARLY WARNING
 * ============================================================ */

/** Pilihan Kondisi tetap (yellow cell) untuk 3 dimensi selain Masa berlaku, dan pemetaannya ke Status. */
const KONDISI_OPTIONS = [
    'Aktivitas/tenggat' => [
        'BELUM DIPERIKSA' => 'V0',
        'NORMAL' => 'E0',
        'MELEWATI TENGGAT' => 'E1',
        'TIDAK ADA AKTIVITAS >180 HARI' => 'E3',
    ],
    'Data/eviden' => [
        'BELUM DIPERIKSA' => 'V0',
        'LENGKAP' => 'E0',
        'BELUM LENGKAP MASIH DALAM TENGGAT' => 'E1',
        'BELUM LENGKAP SETELAH TENGGAT' => 'E3',
    ],
    'PIC' => [
        'BELUM DIPERIKSA' => 'V0',
        'AKTIF' => 'E0',
        'TIDAK AKTIF DENGAN PENGGANTI' => 'E1',
        'TIDAK AKTIF TANPA PENGGANTI' => 'E3',
    ],
];

/**
 * Status (E0-E3/V0) untuk dimensi Aktivitas/tenggat, Data/eviden, atau PIC,
 * diturunkan otomatis dari Kondisi yang dipilih pemeriksa. Persis rumus D28/D29/D30.
 */
function statusDariKondisi(string $dimensi, ?string $kondisi): string {
    $map = KONDISI_OPTIONS[$dimensi] ?? [];
    return $map[$kondisi] ?? 'V0';
}

/**
 * Dimensi "Masa berlaku": Kondisi & Status 100% OTOMATIS, TIDAK diisi manual.
 * Persis rumus:
 *   H9 (Sisa hari) = jika tanggal_berakhir kosong / status_tanggal != TERVERIFIKASI -> null
 *                     else tanggal_berakhir - cutoff_date (dalam hari)
 *   C27 (Kondisi)  = jika status_tanggal != TERVERIFIKASI -> BELUM DAPAT DIPASTIKAN
 *                     elseif sisa_hari<0 -> SUDAH BERAKHIR
 *                     elseif sisa_hari<=30 -> H-30
 *                     elseif sisa_hari<=90 -> H-90
 *                     elseif sisa_hari<=180 -> H-180
 *                     else -> > H-180
 *   D27 (Status)   = BELUM DAPAT DIPASTIKAN->V0 ; SUDAH BERAKHIR/H-30->E3 ; H-90->E2 ; H-180->E1 ; else E0
 */
function hitungMasaBerlaku(?string $tanggalBerakhir, ?string $cutoffDate, string $statusTanggal): array {
    if ($statusTanggal !== 'TERVERIFIKASI' || !$tanggalBerakhir || !$cutoffDate) {
        return ['sisa_hari' => null, 'kondisi' => 'BELUM DAPAT DIPASTIKAN', 'status' => 'V0'];
    }
    $sisaHari = (int) round((strtotime($tanggalBerakhir) - strtotime($cutoffDate)) / 86400);

    if ($sisaHari < 0) {
        $kondisi = 'SUDAH BERAKHIR'; $status = 'E3';
    } elseif ($sisaHari <= 30) {
        $kondisi = 'H-30'; $status = 'E3';
    } elseif ($sisaHari <= 90) {
        $kondisi = 'H-90'; $status = 'E2';
    } elseif ($sisaHari <= 180) {
        $kondisi = 'H-180'; $status = 'E1';
    } else {
        $kondisi = '> H-180'; $status = 'E0';
    }
    return ['sisa_hari' => $sisaHari, 'kondisi' => $kondisi, 'status' => $status];
}

/** Urutan keparahan warning untuk mencari yang tertinggi. Persis rumus B32: E3 > E2 > E1 > V0 > E0. */
const URUTAN_WARNING = ['E3' => 4, 'E2' => 3, 'E1' => 2, 'V0' => 1, 'E0' => 0];

/** Warning tertinggi dari 4 dimensi. Persis rumus B32 (COUNTIF berurutan E3,E2,E1,V0, baru E0). */
function warningTertinggi(array $statusList): array {
    $best = 'E0';
    $bestLevel = -1;
    foreach ($statusList as $status) {
        $level = URUTAN_WARNING[$status] ?? 0;
        if ($level > $bestLevel) {
            $bestLevel = $level;
            $best = $status;
        }
    }
    return [
        'status'             => $best,
        'label'              => labelWarning($best),
        'tingkat_penanganan' => tingkatPenanganan($best),
    ];
}

function labelWarning(string $status): string {
    return match ($status) {
        'E0' => 'E0 - HIJAU',
        'E1' => 'E1 - KUNING',
        'E2' => 'E2 - JINGGA',
        'E3' => 'E3 - MERAH',
        default => 'V0 - DATA BELUM CUKUP',
    };
}

/** Persis rumus D32: V0->LENGKAPI DATA ; E2/E3->PERLU KOORDINASI PROJECT LEADER ; else DITANGANI PIC/UNIT. */
function tingkatPenanganan(string $status): string {
    if ($status === 'V0') return 'LENGKAPI DATA';
    if ($status === 'E2' || $status === 'E3') return 'PERLU KOORDINASI PROJECT LEADER';
    return 'DITANGANI PIC/UNIT';
}

/* ============================================================
 * UJI KEBUTUHAN INTERVENSI PIMPINAN
 * ============================================================ */

/**
 * Hasil uji (persis rumus B43):
 *   ada YA               -> CALON BUTUH INTERVENSI PIMPINAN
 *   ada BELUM DIPASTIKAN -> LENGKAPI UJI INTERVENSI
 *   else                 -> ikut status warning tertinggi (V0/E2-E3/lainnya)
 */
function hasilUjiIntervensi(array $pemicuRows, string $warningStatus): string {
    foreach ($pemicuRows as $row) {
        if (($row['jawaban'] ?? '') === 'YA') return 'CALON BUTUH INTERVENSI PIMPINAN';
    }
    foreach ($pemicuRows as $row) {
        if (($row['jawaban'] ?? '') === 'BELUM DIPASTIKAN') return 'LENGKAPI UJI INTERVENSI';
    }
    if ($warningStatus === 'V0') return 'LENGKAPI DATA';
    if ($warningStatus === 'E2' || $warningStatus === 'E3') return 'PERLU KOORDINASI PROJECT LEADER';
    return 'DITANGANI PIC/UNIT';
}

/**
 * Cek usulan (persis rumus D46):
 *   Hasil uji = CALON BUTUH INTERVENSI PIMPINAN:
 *      - ada pemicu YA tapi bukti/alasan kosong -> LENGKAPI BUKTI PEMICU
 *      - upaya/keputusan kosong                  -> LENGKAPI UPAYA DAN KEPUTUSAN
 *      - else                                     -> SIAP DIBAWA KE FORUM
 *   Hasil uji lainnya:
 *      - ada BELUM DIPASTIKAN -> LENGKAPI UJI INTERVENSI
 *      - else                  -> TIDAK PERLU USULAN PIMPINAN
 */
function cekUsulanIntervensi(array $pemicuRows, string $hasilUji, ?string $upaya, ?string $keputusan): string {
    if ($hasilUji === 'CALON BUTUH INTERVENSI PIMPINAN') {
        foreach ($pemicuRows as $row) {
            if (($row['jawaban'] ?? '') === 'YA' && trim((string)($row['bukti_alasan'] ?? '')) === '') {
                return 'LENGKAPI BUKTI PEMICU';
            }
        }
        if (trim((string)$upaya) === '' || trim((string)$keputusan) === '') {
            return 'LENGKAPI UPAYA DAN KEPUTUSAN';
        }
        return 'SIAP DIBAWA KE FORUM';
    }
    foreach ($pemicuRows as $row) {
        if (($row['jawaban'] ?? '') === 'BELUM DIPASTIKAN') return 'LENGKAPI UJI INTERVENSI';
    }
    return 'TIDAK PERLU USULAN PIMPINAN';
}

/** Warna badge untuk kategori nilai, dipakai di dashboard/list. */
function warnaKategori(string $kategori): string {
    return match ($kategori) {
        'PRODUKTIF' => 'success',
        'BERJALAN' => 'primary',
        'PERLU AKTIVASI' => 'warning',
        'KRITIS' => 'danger',
        'BELUM FINAL' => 'orange',
        default => 'secondary',
    };
}

/** Warna badge untuk status warning. */
function warnaWarning(string $status): string {
    return match ($status) {
        'E0' => 'success',
        'E1' => 'warning',
        'E2' => 'orange',
        'E3' => 'danger',
        default => 'secondary',
    };
}

function formatTanggal(?string $tgl): string {
    if (!$tgl) return '-';
    $t = strtotime($tgl);
    if (!$t) return '-';
    $bulan = ['','Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
    return date('d', $t) . ' ' . $bulan[(int)date('n', $t)] . ' ' . date('Y', $t);
}

function h(?string $s): string {
    return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8');
}

/** Potong teks dengan aman — pakai mb_strimwidth jika mbstring aktif, fallback ke substr. */
function singkat(?string $teks, int $panjang, string $akhiran = '…'): string {
    if ($teks === null || $teks === '') return '';
    if (function_exists('mb_strimwidth')) {
        return mb_strimwidth($teks, 0, $panjang, $akhiran, 'UTF-8');
    }
    return strlen($teks) > $panjang ? substr($teks, 0, max(1, $panjang - 1)) . $akhiran : $teks;
}

/* ============================================================
 * DASHBOARD HELPERS
 * ============================================================ */

/** Label display name untuk keenam aspek indikator (dipakai di chart dashboard). */
const ASPEK_LABELS = [
    'I1' => 'Relevansi',
    'I2' => 'Mitra',
    'I3' => 'Pelaksanaan',
    'I4' => 'Data & Eviden',
    'I5' => 'Dampak',
    'I6' => 'Keberlanjutan',
];

/** Map kategori scorecard ke status efektivitas untuk dashboard. */
function statusEfektivitas(string $kategori): string {
    return match ($kategori) {
        'PRODUKTIF', 'BERJALAN' => 'Efektif',
        'PERLU AKTIVASI', 'BELUM LENGKAP', 'BELUM FINAL' => 'Perlu Perhatian',
        'KRITIS' => 'Berisiko',
        default => 'Perlu Perhatian',
    };
}

/** Warna untuk status efektivitas. */
function warnaEfektivitas(string $status): string {
    return match ($status) {
        'Efektif' => 'success',
        'Berisiko' => 'danger',
        default => 'warning',
    };
}

