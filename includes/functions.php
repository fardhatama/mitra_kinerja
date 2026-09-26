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
    'I1' => 10, 'I2' => 15, 'I3' => 15, 'I4' => 20, 'I5' => 20, 'I6' => 10, 'I7' => 10,
];

if (!defined('BASELINE_12_DEFS')) {
    define('BASELINE_12_DEFS', [
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
    ]);
}

/**
 * Hitung status "Cek" untuk satu baris indikator V2.1.
 * Mendukung: DAPAT DINILAI / BUKTI MEMADAI, BUKTI BELUM MEMADAI, BELUM DAPAT DINILAI, BELUM DITELAAH.
 */
function hitungCekIndikator(array $row): string {
    $status  = $row['status_pemeriksaan'] ?? 'BELUM DITELAAH';
    $temuan  = trim((string)($row['temuan_bukti'] ?? ''));
    $skor    = $row['skor'];
    $alasan  = trim((string)($row['alasan_skor'] ?? ''));

    $isDapatDinilai = in_array($status, ['DAPAT DINILAI', 'BUKTI CUKUP', 'BUKTI MEMADAI'], true);
    $isBdnOrKurang  = in_array($status, ['BUKTI BELUM CUKUP', 'BUKTI BELUM MEMADAI', 'BELUM DAPAT DINILAI'], true);
    $isBelumTelaah  = in_array($status, ['BELUM DIPERIKSA', 'BELUM DITELAAH'], true);

    if ($isDapatDinilai) {
        if ($skor === null) return 'BELUM DIISI';
        if ($temuan === '') return 'TULIS TEMUAN/BUKTI';
        if ($alasan === '') return 'TULIS ALASAN';
        return 'OK';
    }

    if ($isBdnOrKurang) {
        if ($skor !== null) return 'HAPUS SKOR';
        return 'OK';
    }

    if ($isBelumTelaah) {
        if ($skor !== null) return 'HAPUS SKOR';
        return 'PERIKSA BUKTI';
    }

    return 'OK';
}

/** Nilai indikator = skor/4 * bobot (null jika skor belum ada). */
function hitungNilaiIndikator(?int $skor, int $bobot): ?float {
    if ($skor === null) return null;
    return round(($skor / 4) * $bobot, 2);
}

/**
 * Ringkasan 7 indikator suatu naskah V2.1.
 */
function ringkasanIndikator(array $indikatorRows): array {
    $skorTerisi = 0;
    $cekOkCount = 0;
    $nilaiBerjalan = 0.0;
    $bobotDinilai = 0;
    $dapatDinilaiCount = 0;
    $bdnCount = 0;
    $buktiKurangCount = 0;

    foreach ($indikatorRows as $row) {
        $st = $row['status_pemeriksaan'] ?? 'BELUM DITELAAH';
        if (in_array($st, ['DAPAT DINILAI', 'BUKTI CUKUP', 'BUKTI MEMADAI'], true)) {
            $dapatDinilaiCount++;
            $bobotDinilai += (int)($row['bobot'] ?? BOBOT_INDIKATOR[$row['kode_indikator']] ?? 0);
        } elseif ($st === 'BELUM DAPAT DINILAI') {
            $bdnCount++;
        } elseif (in_array($st, ['BUKTI BELUM CUKUP', 'BUKTI BELUM MEMADAI'], true)) {
            $buktiKurangCount++;
        }

        if ($row['skor'] !== null) {
            $skorTerisi++;
            $nilaiBerjalan += (float)($row['nilai'] ?? 0);
        }
        if (hitungCekIndikator($row) === 'OK') {
            $cekOkCount++;
        }
    }

    $n = count($indikatorRows) ?: 7;
    $kelengkapan = round(($cekOkCount / $n) * 100);
    $skorLengkap = ($cekOkCount === $n);
    $cekLengkapOk = ($cekOkCount === $n);

    if ($kelengkapan < 100) {
        $kategori = 'BELUM LENGKAP';
    } else {
        $kategori = kategoriDariNilai($nilaiBerjalan);
    }

    return [
        'nilai_berjalan'    => round($nilaiBerjalan, 2),
        'bobot_dinilai'     => $bobotDinilai,
        'kelengkapan'       => $kelengkapan,
        'skor_lengkap'      => $skorLengkap,
        'cek_lengkap_ok'    => $cekLengkapOk,
        'kategori'          => $kategori,
        'dapat_dinilai_n'   => $dapatDinilaiCount,
        'bdn_n'             => $bdnCount,
        'bukti_kurang_n'    => $buktiKurangCount,
    ];
}

/** Menentukan posisi portofolio kerja sama dalam tangga hasil V2.1. */
function hitungPosisiPortofolio(array $indikatorRows): string {
    $scores = [];
    foreach ($indikatorRows as $r) {
        $scores[$r['kode_indikator']] = $r['skor'];
    }

    if (($scores['I5'] ?? null) !== null && $scores['I5'] >= 3) {
        return 'BERDAMPAK';
    }
    if (($scores['I4'] ?? null) !== null && $scores['I4'] >= 3) {
        return 'OUTCOME TERBENTUK';
    }
    if (($scores['I3'] ?? null) !== null && $scores['I3'] >= 3) {
        return 'OUTPUT TERSEDIA';
    }
    if (($scores['I2'] ?? null) !== null && $scores['I2'] >= 2) {
        return 'AKTIF';
    }
    return 'BELUM DAPAT DITENTUKAN';
}

/** Menentukan rekomendasi tindak lanjut berdasarkan skor, risiko, dan sisa hari. */
function hitungRekomendasi(float $nilai, string $warningStatus, string $posisiPortofolio, ?int $sisaHari = null): string {
    if ($warningStatus === 'E3') {
        return 'HENTIKAN';
    }
    if ($warningStatus === 'E2' || $nilai < 50 || $posisiPortofolio === 'BELUM DAPAT DITENTUKAN') {
        return 'PERBAIKI';
    }
    if ($sisaHari !== null && $sisaHari <= 90 && $nilai >= 75) {
        return 'PERPANJANG';
    }
    if ($nilai >= 85 && $posisiPortofolio === 'BERDAMPAK') {
        return 'REPLIKASI';
    }
    if ($nilai >= 50) {
        return 'LANJUT';
    }
    return 'PERBAIKI';
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
 *   - skor belum 7/7 terisi            -> BELUM LENGKAP
 *   - skor 7/7 tapi Cek belum semua OK -> PERLU DILENGKAPI
 *   - Cek 7/7 OK, validasi DISETUJUI   -> FINAL/TERVALIDASI
 *   - Cek 7/7 OK, validasi PERLU PERBAIKAN -> PERLU PERBAIKAN
 *   - Cek 7/7 OK, validasi lainnya     -> SIAP DIVALIDASI
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
function hitungMasaBerlaku(?string $tanggalBerakhir, ?string $cutoffDate, ?string $statusTanggal = null): array {
    if (($statusTanggal ?? '') !== 'TERVERIFIKASI' || !$tanggalBerakhir || !$cutoffDate || $tanggalBerakhir === '0000-00-00' || $cutoffDate === '0000-00-00') {
        return ['sisa_hari' => null, 'kondisi' => 'BELUM DAPAT DIPASTIKAN', 'status' => 'V0'];
    }
    $timeBerakhir = strtotime($tanggalBerakhir);
    $timeCutoff = strtotime($cutoffDate);
    if ($timeBerakhir === false || $timeCutoff === false) {
        return ['sisa_hari' => null, 'kondisi' => 'BELUM DAPAT DIPASTIKAN', 'status' => 'V0'];
    }
    $sisaHari = (int) round(($timeBerakhir - $timeCutoff) / 86400);

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

/**
 * Menghitung kebutuhan siklus scorecard selama masa berlaku kerja sama (flow.pdf & SOP 5).
 * Cadence default: 4 kali per tahun (setiap 3 bulan).
 */
function hitungKebutuhanScorecard(?string $tanggalMulai, ?string $tanggalBerakhir, int $cadenceBulan = 3): array {
    $fallback = [
        'total_siklus'             => 0,
        'durasi_bulan'             => 0,
        'warning_1_bulan'          => false,
        'hari_menuju_evaluasi'     => null,
        'target_evaluasi_terdekat' => null,
        'milestones'               => [],
    ];

    if (!$tanggalMulai || !$tanggalBerakhir || $tanggalMulai === '0000-00-00' || $tanggalBerakhir === '0000-00-00') {
        return $fallback;
    }

    try {
        $start = new DateTime($tanggalMulai);
        $end   = new DateTime($tanggalBerakhir);
    } catch (Throwable $e) {
        return $fallback;
    }

    if ($start >= $end) {
        return [
            'total_siklus'             => 1,
            'durasi_bulan'             => 0,
            'warning_1_bulan'          => false,
            'hari_menuju_evaluasi'     => null,
            'target_evaluasi_terdekat' => null,
            'milestones'               => [],
        ];
    }

    $diff = $start->diff($end);
    $durasiBulan = ($diff->y * 12) + $diff->m + ($diff->d > 15 ? 1 : 0);
    $totalSiklus = max(1, (int)ceil($durasiBulan / $cadenceBulan));

    $today = new DateTime('now');
    $milestones = [];
    $warning1Bulan = false;
    $hariMenujuEvaluasi = null;
    $targetEvaluasiTerdekat = null;

    for ($i = 1; $i <= $totalSiklus; $i++) {
        $targetDate = clone $start;
        $targetDate->modify('+' . ($i * $cadenceBulan) . ' months');
        if ($targetDate > $end) $targetDate = clone $end;

        $targetStr = $targetDate->format('Y-m-d');
        $diffDays = (int)round((strtotime($targetStr) - $today->getTimestamp()) / 86400);

        $milestones[] = [
            'siklus_ke'   => $i,
            'nama'        => $i === 1 ? 'SC-1: Baseline / Awal' : 'SC-' . $i . ': Evaluasi Triwulan ' . ($i - 1),
            'target_tgl'  => $targetStr,
            'sisa_hari'   => $diffDays,
            'is_due_soon' => ($diffDays >= 0 && $diffDays <= 30),
            'is_past'     => ($diffDays < 0),
        ];

        if ($targetEvaluasiTerdekat === null && $diffDays >= -15) {
            $targetEvaluasiTerdekat = $targetStr;
            $hariMenujuEvaluasi = $diffDays;
            if ($diffDays >= 0 && $diffDays <= 30) {
                $warning1Bulan = true;
            }
        }
    }

    return [
        'total_siklus'             => $totalSiklus,
        'durasi_bulan'             => $durasiBulan,
        'warning_1_bulan'          => $warning1Bulan,
        'hari_menuju_evaluasi'     => $hariMenujuEvaluasi,
        'target_evaluasi_terdekat' => $targetEvaluasiTerdekat,
        'milestones'               => $milestones,
    ];
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

/** Label display name untuk ketujuh aspek indikator V2.1 (dipakai di chart dashboard). */
const ASPEK_LABELS = [
    'I1' => 'Tata Kelola',
    'I2' => 'Implementasi',
    'I3' => 'Output',
    'I4' => 'Outcome',
    'I5' => 'Dampak',
    'I6' => 'Eviden',
    'I7' => 'Risiko',
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

