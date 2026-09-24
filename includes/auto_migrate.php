<?php
/**
 * Auto-migration and self-healing schema helper.
 * Ensures all required V2.1 tables and columns exist automatically,
 * preventing HTTP 500 errors on newly pulled environments.
 */

function ensureDatabaseSchema(PDO $pdo): void {
    static $checked = false;
    if ($checked) return;
    $checked = true;

    try {
        // 1. Check if baseline_elemen exists
        $stmt = $pdo->query("SHOW TABLES LIKE 'baseline_elemen'");
        $hasBaseline = (bool)$stmt->fetch();

        // 2. Check if pra_pks exists
        $stmt = $pdo->query("SHOW TABLES LIKE 'pra_pks'");
        $hasPraPks = (bool)$stmt->fetch();

        // 3. Check if rencana_kerja exists
        $stmt = $pdo->query("SHOW TABLES LIKE 'rencana_kerja'");
        $hasRencanaKerja = (bool)$stmt->fetch();

        // 4. Check if siklus_monev exists
        $stmt = $pdo->query("SHOW TABLES LIKE 'siklus_monev'");
        $hasSiklusMonev = (bool)$stmt->fetch();

        // Check if mitra_kinerja has V2.1 columns
        $cols = $pdo->query("SHOW COLUMNS FROM mitra_kinerja")->fetchAll(PDO::FETCH_COLUMN);
        $hasPosisi = in_array('posisi_portofolio', $cols, true);

        if (!$hasBaseline || !$hasPraPks || !$hasRencanaKerja || !$hasSiklusMonev || !$hasPosisi) {
            // Run schema migrations safely
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS baseline_elemen (
                    id                      INT AUTO_INCREMENT PRIMARY KEY,
                    mitra_id                INT NOT NULL,
                    nomor_elemen            INT NOT NULL,
                    kelompok                VARCHAR(50) NOT NULL,
                    nama_elemen             VARCHAR(100) NOT NULL,
                    yang_diperiksa          TEXT NOT NULL,
                    sumber_bukti_minimum    TEXT NOT NULL,
                    status                  ENUM('TERVERIFIKASI', 'BELUM TERVERIFIKASI', 'BELUM TERSEDIA', 'TIDAK RELEVAN', 'BELUM DIISI') NOT NULL DEFAULT 'BELUM DIISI',
                    fakta_pemeriksaan       TEXT NULL,
                    link_sumber_bukti       TEXT NULL,
                    catatan                 TEXT NULL,
                    created_at              TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at              TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    FOREIGN KEY (mitra_id) REFERENCES mitra_kinerja(id) ON DELETE CASCADE,
                    UNIQUE KEY uq_mitra_elemen (mitra_id, nomor_elemen)
                ) ENGINE=InnoDB;

                CREATE TABLE IF NOT EXISTS pra_pks (
                    id                      INT AUTO_INCREMENT PRIMARY KEY,
                    nomor_usulan            VARCHAR(50) NOT NULL UNIQUE,
                    tipe_kerjasama          ENUM('Dalam Negeri', 'Luar Negeri') NOT NULL DEFAULT 'Dalam Negeri',
                    jenis_naskah            ENUM('MoU', 'PKS', 'Lainnya') NOT NULL DEFAULT 'PKS',
                    unit_pemrakarsa         VARCHAR(255) NOT NULL,
                    penanggung_jawab_usulan VARCHAR(255) NULL,
                    calon_mitra             VARCHAR(255) NOT NULL,
                    judul_rencana           TEXT NOT NULL,
                    tujuan_singkat          TEXT NULL,
                    ruang_lingkup           TEXT NULL,
                    penerima_manfaat        TEXT NULL,
                    perkiraan_mulai         DATE NULL,
                    perkiraan_selesai       DATE NULL,
                    k1_kesesuaian_strategis ENUM('YA', 'TIDAK') NOT NULL DEFAULT 'YA',
                    k2_kebutuhan_daya_ungkit ENUM('YA', 'TIDAK') NOT NULL DEFAULT 'YA',
                    k3_kelayakan_mitra      ENUM('YA', 'TIDAK') NOT NULL DEFAULT 'YA',
                    k4_kesiapan_sumber_daya ENUM('YA', 'TIDAK') NOT NULL DEFAULT 'YA',
                    k5_risiko_keberlanjutan ENUM('YA', 'TIDAK') NOT NULL DEFAULT 'YA',
                    pertanyaan_uji          LONGTEXT NULL,
                    trigger_khusus          LONGTEXT NULL,
                    catatan_verifikasi      TEXT NULL,
                    gap_penyempurnaan       TEXT NULL,
                    unit_review_tambahan    TEXT NULL,
                    batas_waktu_penyempurnaan DATE NULL,
                    status_rekomendasi      ENUM('Layak', 'Perlu Penyempurnaan', 'Tidak Prioritas / Tidak Layak') NOT NULL DEFAULT 'Layak',
                    status_persetujuan      ENUM('Menunggu Persetujuan Pimpinan', 'Disetujui Pimpinan', 'Dikembalikan untuk Revisi', 'Ditolak Pimpinan') NOT NULL DEFAULT 'Menunggu Persetujuan Pimpinan',
                    catatan_pimpinan        TEXT NULL,
                    tanggal_persetujuan     DATE NULL,
                    pimpinan_id             INT NULL,
                    is_promoted_to_pks      TINYINT(1) NOT NULL DEFAULT 0,
                    created_by              INT NULL,
                    created_at              TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at              TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                ) ENGINE=InnoDB;

                CREATE TABLE IF NOT EXISTS rencana_kerja (
                    id                  INT AUTO_INCREMENT PRIMARY KEY,
                    mitra_id            INT NOT NULL,
                    judul_rencana       VARCHAR(255) NOT NULL,
                    ruang_lingkup       TEXT NULL,
                    maksud_tujuan       TEXT NULL,
                    tanggal_mulai       DATE NOT NULL,
                    tanggal_selesai     DATE NOT NULL,
                    status              ENUM('Draft', 'Proses Persetujuan', 'Disetujui', 'Selesai') NOT NULL DEFAULT 'Disetujui',
                    alasan_persetujuan  TEXT NULL,
                    draft_naskah        VARCHAR(255) NULL,
                    created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (mitra_id) REFERENCES mitra_kinerja(id) ON DELETE CASCADE
                ) ENGINE=InnoDB;

                CREATE TABLE IF NOT EXISTS siklus_monev (
                    id                          INT AUTO_INCREMENT PRIMARY KEY,
                    mitra_id                    INT NOT NULL,
                    rencana_kerja_id            INT NULL,
                    siklus_ke                   INT NOT NULL,
                    nama_siklus                 VARCHAR(100) NOT NULL,
                    tanggal_target_evaluasi     DATE NOT NULL,
                    tanggal_realisasi_evaluasi  DATE NULL,
                    status_siklus               ENUM('Menunggu', 'Perlu Penilaian Segera', 'Sedang Dinilai', 'Selesai') NOT NULL DEFAULT 'Menunggu',
                    catatan_monev               TEXT NULL,
                    nilai_siklus                DECIMAL(6,2) NULL,
                    created_at                  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (mitra_id) REFERENCES mitra_kinerja(id) ON DELETE CASCADE,
                    FOREIGN KEY (rencana_kerja_id) REFERENCES rencana_kerja(id) ON DELETE SET NULL
                ) ENGINE=InnoDB;
            ");

            // Add missing columns to mitra_kinerja safely
            $alterQueries = [
                "ALTER TABLE mitra_kinerja ADD COLUMN IF NOT EXISTS pks_induk_id INT NULL AFTER jenis",
                "ALTER TABLE mitra_kinerja ADD COLUMN IF NOT EXISTS baseline_status ENUM('BELUM DIISI', 'DALAM PROSES', 'TERVERIFIKASI / DIKUNCI') NOT NULL DEFAULT 'BELUM DIISI' AFTER status_tanggal",
                "ALTER TABLE mitra_kinerja ADD COLUMN IF NOT EXISTS baseline_locked_at DATETIME NULL AFTER baseline_status",
                "ALTER TABLE mitra_kinerja ADD COLUMN IF NOT EXISTS baseline_locked_by INT NULL AFTER baseline_locked_at",
                "ALTER TABLE mitra_kinerja ADD COLUMN IF NOT EXISTS baseline_pemeriksa VARCHAR(255) NULL AFTER baseline_locked_by",
                "ALTER TABLE mitra_kinerja ADD COLUMN IF NOT EXISTS baseline_catatan_ringkasan TEXT NULL AFTER baseline_pemeriksa",
                "ALTER TABLE mitra_kinerja ADD COLUMN IF NOT EXISTS posisi_portofolio ENUM('BELUM DAPAT DITENTUKAN','AKTIF','OUTPUT TERSEDIA','OUTCOME TERBENTUK','BERDAMPAK') NOT NULL DEFAULT 'BELUM DAPAT DITENTUKAN' AFTER status_scorecard",
                "ALTER TABLE mitra_kinerja ADD COLUMN IF NOT EXISTS rekomendasi ENUM('BELUM DITENTUKAN','LANJUT','PERBAIKI','PERPANJANG','REPLIKASI','HENTIKAN') NOT NULL DEFAULT 'BELUM DITENTUKAN' AFTER posisi_portofolio",
                "ALTER TABLE indikator_skor ADD COLUMN IF NOT EXISTS kondisi_baseline TEXT NULL AFTER referensi_baseline",
                "ALTER TABLE indikator_skor ADD COLUMN IF NOT EXISTS kondisi_saat_ini TEXT NULL AFTER kondisi_baseline"
            ];
            foreach ($alterQueries as $q) {
                try { $pdo->exec($q); } catch (Throwable $e) {}
            }
        }
    } catch (Throwable $e) {
        error_log('ensureDatabaseSchema error: ' . $e->getMessage());
    }
}
