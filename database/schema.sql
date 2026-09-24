-- ============================================================
-- SCORECARD EFEKTIVITAS MITRA KINERJA — Skema Database
-- Kantor Wilayah Kementerian Hukum Kepulauan Riau
-- ============================================================

CREATE DATABASE IF NOT EXISTS mitra_kinerja
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE mitra_kinerja;

-- ----------------------------------------------------------------
-- Pengguna & role
-- Role:
--   admin      : kelola user & master data
--   pemeriksa  : mengisi form penilaian (identitas, 6 indikator, warning, uji intervensi)
--   validator  : memvalidasi (approve/reject) hasil pemeriksaan
--   pimpinan   : hanya melihat dashboard eksekutif (read-only)
-- ----------------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    nama            VARCHAR(150) NOT NULL,
    username        VARCHAR(50) NOT NULL UNIQUE,
    password_hash   VARCHAR(255) NOT NULL,
    role            ENUM('admin','pemeriksa','validator','pimpinan') NOT NULL,
    aktif           TINYINT(1) NOT NULL DEFAULT 1,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ----------------------------------------------------------------
-- A. Identitas dan kontrol naskah kerja sama (1 baris = 1 naskah/mitra)
-- ----------------------------------------------------------------
CREATE TABLE IF NOT EXISTS mitra_kinerja (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    kode                VARCHAR(5) NOT NULL UNIQUE,          -- P01..P10, C01..C05
    portofolio          ENUM('Pilot Utama','Cadangan') NOT NULL,
    nama_mitra          VARCHAR(255) NOT NULL,
    judul               TEXT,
    jenis               ENUM('PKS','MoU') NOT NULL,
    pks_induk_id        INT NULL,                            -- Payung MoU (P2MA: Kerja Sama Utama)
    tanggal_mulai       DATE,
    tanggal_berakhir    DATE,
    status_tanggal      ENUM('TERVERIFIKASI','BELUM TERVERIFIKASI') NOT NULL DEFAULT 'BELUM TERVERIFIKASI',
    baseline_status     ENUM('BELUM DIISI', 'DALAM PROSES', 'TERVERIFIKASI / DIKUNCI') NOT NULL DEFAULT 'BELUM DIISI',
    baseline_locked_at  DATETIME NULL,
    baseline_locked_by  INT NULL,
    baseline_pemeriksa  VARCHAR(255) NULL,
    baseline_catatan_ringkasan TEXT NULL,
    cutoff_date         DATE,
    sumber_baseline     VARCHAR(255),
    pemeriksa_id        INT NULL,
    tanggal_review      DATE NULL,
    status_scorecard    ENUM('BELUM LENGKAP','SIAP DIVALIDASI','FINAL/TERVALIDASI','PERLU PERBAIKAN')
                            NOT NULL DEFAULT 'BELUM LENGKAP',
    posisi_portofolio   ENUM('BELUM DAPAT DITENTUKAN','AKTIF','OUTPUT TERSEDIA','OUTCOME TERBENTUK','BERDAMPAK')
                            NOT NULL DEFAULT 'BELUM DAPAT DITENTUKAN',
    rekomendasi         ENUM('BELUM DITENTUKAN','LANJUT','PERBAIKI','PERPANJANG','REPLIKASI','HENTIKAN')
                            NOT NULL DEFAULT 'BELUM DITENTUKAN',
    created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (pemeriksa_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (pks_induk_id) REFERENCES mitra_kinerja(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ----------------------------------------------------------------
-- A1. Baseline FIX — Verifikasi dan Penguncian Kondisi Awal (12 Elemen)
-- Status: TERVERIFIKASI, BELUM TERVERIFIKASI, BELUM TERSEDIA, TIDAK RELEVAN, BELUM DIISI
-- ----------------------------------------------------------------
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

-- ----------------------------------------------------------------
-- B. Penilaian inti — Tujuh Indikator Kinerja & Dampak V2.1 (I1..I7)
-- Bobot tetap: I1=10, I2=15, I3=15, I4=20, I5=20, I6=10, I7=10 (total 100)
-- Status: BELUM DITELAAH, DAPAT DINILAI, BUKTI BELUM MEMADAI, BELUM DAPAT DINILAI
-- ----------------------------------------------------------------
CREATE TABLE IF NOT EXISTS indikator_skor (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    mitra_id            INT NOT NULL,
    kode_indikator      VARCHAR(5) NOT NULL,                 -- I1..I7
    deskripsi           TEXT,                                -- nama indikator + cara periksa
    bobot               TINYINT NOT NULL,                    -- 10/15/15/20/20/10/10
    referensi_baseline  TEXT,                                -- kondisi awal baseline
    kondisi_baseline    TEXT NULL,                           -- kondisi terverifikasi saat cut-off
    kondisi_saat_ini    TEXT NULL,                           -- kondisi faktual terkini
    status_pemeriksaan  VARCHAR(50) NOT NULL DEFAULT 'BELUM DITELAAH',
    temuan_bukti        TEXT NULL,
    skor                TINYINT NULL,                        -- 0-4, hanya valid jika status = DAPAT DINILAI
    alasan_skor         TEXT NULL,
    catatan_tindak_lanjut TEXT NULL,
    nilai               DECIMAL(6,2) NULL,                   -- skor/4*bobot
    updated_by          INT NULL,
    updated_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_mitra_indikator (mitra_id, kode_indikator),
    FOREIGN KEY (mitra_id) REFERENCES mitra_kinerja(id) ON DELETE CASCADE,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL,
    CONSTRAINT chk_skor_range CHECK (skor IS NULL OR (skor BETWEEN 0 AND 4))
) ENGINE=InnoDB;

-- ----------------------------------------------------------------
-- B2. Rencana Kerja (Work Plan per PKS)
-- ----------------------------------------------------------------
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

-- ----------------------------------------------------------------
-- B3. Siklus Evaluasi / Monev Berkala (Target Multi-Cycle Scorecard)
-- ----------------------------------------------------------------
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

-- ----------------------------------------------------------------
-- C. Validasi unit (per naskah)
-- ----------------------------------------------------------------
CREATE TABLE IF NOT EXISTS validasi (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    mitra_id        INT NOT NULL UNIQUE,
    status          ENUM('BELUM','DISETUJUI','PERLU PERBAIKAN') NOT NULL DEFAULT 'BELUM',
    validator_id    INT NULL,
    tanggal_validasi DATE NULL,
    catatan         TEXT NULL,
    FOREIGN KEY (mitra_id) REFERENCES mitra_kinerja(id) ON DELETE CASCADE,
    FOREIGN KEY (validator_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ----------------------------------------------------------------
-- D. Early warning — TIDAK ditentukan oleh nilai scorecard
-- 4 dimensi tetap per naskah: Masa berlaku, Aktivitas/tenggat, Data/eviden, PIC
-- Status: E0-HIJAU, E1-KUNING, E2-JINGGA, E3-MERAH, V0-DATA BELUM CUKUP
-- ----------------------------------------------------------------
CREATE TABLE IF NOT EXISTS early_warning (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    mitra_id        INT NOT NULL,
    dimensi         ENUM('Masa berlaku','Aktivitas/tenggat','Data/eviden','PIC') NOT NULL,
    kondisi         VARCHAR(150) NULL,
    status          ENUM('E0','E1','E2','E3','V0') NOT NULL DEFAULT 'V0',
    fakta_bukti     TEXT NULL,
    tindakan        TEXT NULL,
    pic             VARCHAR(150) NULL,
    tenggat         DATE NULL,
    progres         ENUM('BELUM MULAI','DALAM PROSES','SELESAI') NOT NULL DEFAULT 'BELUM MULAI',
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_mitra_dimensi (mitra_id, dimensi),
    FOREIGN KEY (mitra_id) REFERENCES mitra_kinerja(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ----------------------------------------------------------------
-- E. Uji kebutuhan intervensi pimpinan — 5 pemicu tetap per naskah
-- Jawab YA hanya jika kondisi benar-benar terjadi & di luar kewenangan PIC/unit.
-- ----------------------------------------------------------------
CREATE TABLE IF NOT EXISTS intervensi_pimpinan (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    mitra_id        INT NOT NULL,
    no_pemicu       TINYINT NOT NULL,           -- 1..5
    pemicu_teks     VARCHAR(255) NOT NULL,      -- master, dari template
    jawaban         ENUM('BELUM DIPASTIKAN','TIDAK','YA') NOT NULL DEFAULT 'BELUM DIPASTIKAN',
    bukti_alasan    TEXT NULL,
    UNIQUE KEY uq_mitra_pemicu (mitra_id, no_pemicu),
    FOREIGN KEY (mitra_id) REFERENCES mitra_kinerja(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Usulan yang wajib diisi jika minimal 1 pemicu = YA
CREATE TABLE IF NOT EXISTS intervensi_usulan (
    id                      INT AUTO_INCREMENT PRIMARY KEY,
    mitra_id                INT NOT NULL UNIQUE,
    upaya_dilakukan         TEXT NULL,
    keputusan_diminta       TEXT NULL,
    updated_at              TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (mitra_id) REFERENCES mitra_kinerja(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ----------------------------------------------------------------
-- F. Tindak lanjut — catatan aksi per naskah
-- ----------------------------------------------------------------
CREATE TABLE IF NOT EXISTS tindak_lanjut (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    mitra_id        INT NOT NULL,
    tindakan        TEXT NOT NULL,
    tenggat         DATE NULL,
    status          ENUM('Belum','Proses','Selesai') NOT NULL DEFAULT 'Belum',
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (mitra_id) REFERENCES mitra_kinerja(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ----------------------------------------------------------------
-- Jejak audit sederhana (opsional tapi disarankan untuk data pemerintahan)
-- ----------------------------------------------------------------
CREATE TABLE IF NOT EXISTS audit_log (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    mitra_id        INT NULL,
    user_id         INT NULL,
    aksi            VARCHAR(100) NOT NULL,
    detail          TEXT NULL,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (mitra_id) REFERENCES mitra_kinerja(id) ON DELETE SET NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ----------------------------------------------------------------
-- H. Gate 0 — Uji Kelayakan & Prioritas Pra-Kerja Sama (SOP 1 & flow.pdf)
-- ----------------------------------------------------------------
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
    updated_at              TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (pimpinan_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ----------------------------------------------------------------
-- User default & Akun Demo (admin, pemeriksa, validator, pimpinan)
-- Password default: username123 (contoh: admin123, pemeriksa123, dst.)
-- ----------------------------------------------------------------
INSERT INTO users (nama, username, password_hash, role) VALUES
('Administrator', 'admin', '$2y$12$xLpaVvN5jPUXtwfCt.SNs.x2s7wf9jwKgBqZhD34SeAUxiTnohcVe', 'admin'),
('Pemeriksa Kerja Sama', 'pemeriksa', '$2y$12$OLEkLnm9GCzeGDg3rxQanOHjiN5ISTYgGrFdmzh/kuA9Zie8Xzm1m', 'pemeriksa'),
('Validator Unit', 'validator', '$2y$12$6TsHiuZTtldQLKrWEIYQEu9JahucdueRwq7OVOtSV4cr1ZypOYkx6', 'validator'),
('Pimpinan Wilayah', 'pimpinan', '$2y$12$MGKhQUi9Kxfy/c4xuMuOyOkm6FZOV8Qo5.8SR1kHsQ/TSS0QeVAR6', 'pimpinan')
ON DUPLICATE KEY UPDATE nama=VALUES(nama), role=VALUES(role);
