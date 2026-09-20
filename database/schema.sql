-- ============================================================
-- SCORECARD EFEKTIVITAS MITRA KINERJA — Skema Database
-- Diskominfo Kabupaten Bintan
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
CREATE TABLE users (
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
CREATE TABLE mitra_kinerja (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    kode                VARCHAR(5) NOT NULL UNIQUE,          -- P01..P10, C01..C05
    portofolio          ENUM('Pilot Utama','Cadangan') NOT NULL,
    nama_mitra          VARCHAR(255) NOT NULL,
    judul               TEXT,
    jenis               ENUM('PKS','MoU') NOT NULL,
    tanggal_mulai       DATE,
    tanggal_berakhir    DATE,
    status_tanggal      ENUM('TERVERIFIKASI','BELUM TERVERIFIKASI') NOT NULL DEFAULT 'BELUM TERVERIFIKASI',
    cutoff_date         DATE,
    sumber_baseline     VARCHAR(255),
    pemeriksa_id        INT NULL,
    tanggal_review      DATE NULL,
    status_scorecard    ENUM('BELUM LENGKAP','SIAP DIVALIDASI','FINAL/TERVALIDASI','PERLU PERBAIKAN')
                            NOT NULL DEFAULT 'BELUM LENGKAP',
    created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (pemeriksa_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ----------------------------------------------------------------
-- B. Penilaian inti — enam indikator RAP (I1..I6) per naskah
-- Bobot tetap: I1=15, I2=20, I3=20, I4=15, I5=20, I6=10 (total 100)
-- Aturan penting (ditegakkan di app layer, bukan hanya di sini):
--   - skor hanya boleh diisi jika status_pemeriksaan = 'BUKTI CUKUP'
--   - skor 0 hanya sah bila memang terbukti tidak terpenuhi, bukan data kosong
--   - nilai = skor/4 * bobot
-- ----------------------------------------------------------------
CREATE TABLE indikator_skor (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    mitra_id            INT NOT NULL,
    kode_indikator      ENUM('I1','I2','I3','I4','I5','I6') NOT NULL,
    deskripsi           TEXT,                 -- nama indikator + cara periksa (master, dari template)
    bobot               TINYINT NOT NULL,     -- 15/20/20/15/20/10
    referensi_baseline  TEXT,                 -- kondisi awal baseline (master, dari template)
    status_pemeriksaan  ENUM('BELUM DIPERIKSA','BUKTI CUKUP','BUKTI BELUM CUKUP','BELUM DAPAT DINILAI')
                            NOT NULL DEFAULT 'BELUM DIPERIKSA',
    temuan_bukti        TEXT NULL,
    skor                TINYINT NULL,         -- 0-4, hanya valid jika status = BUKTI CUKUP
    alasan_skor         TEXT NULL,
    nilai               DECIMAL(6,2) NULL,    -- dihitung di app layer saat simpan: skor/4*bobot
    updated_by          INT NULL,
    updated_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_mitra_indikator (mitra_id, kode_indikator),
    FOREIGN KEY (mitra_id) REFERENCES mitra_kinerja(id) ON DELETE CASCADE,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL,
    CONSTRAINT chk_skor_range CHECK (skor IS NULL OR (skor BETWEEN 0 AND 4))
) ENGINE=InnoDB;

-- ----------------------------------------------------------------
-- C. Validasi unit (per naskah)
-- ----------------------------------------------------------------
CREATE TABLE validasi (
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
CREATE TABLE early_warning (
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
CREATE TABLE intervensi_pimpinan (
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
CREATE TABLE intervensi_usulan (
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
CREATE TABLE tindak_lanjut (
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
CREATE TABLE audit_log (
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
-- User default (password: ganti setelah instalasi!)
-- Password di bawah adalah hash bcrypt untuk 'admin123' — WAJIB diganti.
-- ----------------------------------------------------------------
INSERT INTO users (nama, username, password_hash, role) VALUES
('Administrator', 'admin', '$2b$10$dXo7TDzSDDLUtZ3drvRSIurgik2iwBW5DO4Rf0CjPePOLq8fZKEgy', 'admin');
-- Hash di atas untuk password: admin123 (SEGERA GANTI setelah login pertama)
