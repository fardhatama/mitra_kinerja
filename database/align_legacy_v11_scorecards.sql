-- ==========================================================================
-- OPTIONAL SQL SCRIPT: INGEST LEGACY V1.1 SCORECARDS (MIDs 11-14) INTO V2.1
-- Note: Ingests I1-I6 and maps weights to V2.1 Result-Chain standards.
-- I7 remains 'BELUM DITELAAH' pending formal V2.1 Result-Chain evaluation.
-- ==========================================================================
USE mitra_kinerja;

-- Partner MID 11: C02_Scorecard_Politeknik_Bintan_Cakrawala.xlsx
UPDATE mitra_kinerja SET status_scorecard = 'BELUM LENGKAP' WHERE id = 11;
UPDATE indikator_skor SET 
    bobot = 10,
    kondisi_baseline = 'Identitas TERVERIFIKASI; arsip TERVERIFIKASI; P2MA TERVERIFIKASI; PIC internal BELUM TERVERIFIKASI; PIC mitra BELUM TERVERIFIKASI. Update terintegrasi: PIC mitra: Indah Andesta, S.Par., M.Sc | Jabatan: Kepala P3M & Pustaka | Email: indah@pbc.ac.id | Status: Tetap | PIC cadangan: Henricus Yayan Setyanto. S.TP.. M.S.. M.T.P',
    kondisi_saat_ini = 'Baseline baris 13–20: naskah/metadata dasar dapat ditelusuri. Status identitas=TERVERIFIKASI, arsip=TERVERIFIKASI, P2MA=TERVERIFIKASI, PIC internal=BELUM TERVERIFIKASI, PIC mitra=BELUM TERVERIFIKASI. Terdapat inkonsistensi pada baseline: status terstruktur pada Status arsip, Unit pengampu, Pelaksanaan dan hasil, Eviden implementasi belum sepenuhnya didukung narasi/sumber bukti; untuk Scorecard digunakan pendekatan konservatif.',
    status_pemeriksaan = 'DAPAT DINILAI',
    temuan_bukti = 'Baseline baris 13–20: naskah/metadata dasar dapat ditelusuri. Status identitas=TERVERIFIKASI, arsip=TERVERIFIKASI, P2MA=TERVERIFIKASI, PIC internal=BELUM TERVERIFIKASI, PIC mitra=BELUM TERVERIFIKASI. Terdapat inkonsistensi pada baseline: status terstruktur pada Status arsip, Unit pengampu, Pelaksanaan dan hasil, Eviden implementasi belum sepenuhnya didukung narasi/sumber bukti; untuk Scorecard digunakan pendekatan konservatif.',
    skor = 1,
    nilai = 2.50,
    alasan_skor = 'Sesuai skor 1: naskah tersedia, tetapi tata kelola operasional masih terbatas; PIC dua pihak dan/atau bukti penetapan/konfirmasi belum memadai untuk skor yang lebih tinggi.',
    referensi_baseline = 'Identitas TERVERIFIKASI; arsip TERVERIFIKASI; P2MA TERVERIFIKASI; PIC internal BELUM TERVERIFIKASI; PIC mitra BELUM TERVERIFIKASI. Update terintegrasi: PIC mitra: Indah Andesta, S.Par., M.Sc | Jabatan: Kepala P3M & Pustaka | Email: indah@pbc.ac.id | Status: Tetap | PIC cadangan: Henricus Yayan Setyanto. S.TP.. M.S.. M.T.P'
WHERE mitra_id = 11 AND kode_indikator = 'I1';
UPDATE indikator_skor SET 
    bobot = 15,
    kondisi_baseline = 'Rencana tindak lanjut BELUM TERVERIFIKASI. Jumlah usulan: 3 | Prioritas pertama: Sosialisasi dan Klinik Pengajuan Paten Sederhana | Target: Desember 2026',
    kondisi_saat_ini = 'Baseline baris 21 — Rencana tindak lanjut: BELUM TERVERIFIKASI. Ruang lingkup naskah tersedia, tetapi belum ditemukan rencana aksi atau komitmen operasional yang memuat kegiatan, periode, target, dan PIC.',
    status_pemeriksaan = 'BUKTI BELUM MEMADAI',
    temuan_bukti = 'Baseline baris 21 — Rencana tindak lanjut: BELUM TERVERIFIKASI. Ruang lingkup naskah tersedia, tetapi belum ditemukan rencana aksi atau komitmen operasional yang memuat kegiatan, periode, target, dan PIC.',
    skor = NULL,
    nilai = NULL,
    alasan_skor = 'Belum diberi skor karena informasi ''belum ditemukan/belum tersedia'' pada baseline belum selalu membuktikan secara memadai bahwa tindak lanjut memang tidak ada.',
    referensi_baseline = 'Rencana tindak lanjut BELUM TERVERIFIKASI. Jumlah usulan: 3 | Prioritas pertama: Sosialisasi dan Klinik Pengajuan Paten Sederhana | Target: Desember 2026'
WHERE mitra_id = 11 AND kode_indikator = 'I2';
UPDATE indikator_skor SET 
    bobot = 15,
    kondisi_baseline = 'Pelaksanaan/hasil TERVERIFIKASI. Jumlah kegiatan terdata: 2 | Kegiatan pertama: Keterlibatan dalam pencatatan 2 HKI Cipta dalam kegiatan pangayoman Kanwil Kepulauan Riau | Status: Sudah Dilaksanakan',
    kondisi_saat_ini = 'Baseline baris 22 — Pelaksanaan dan hasil: TERVERIFIKASI. Status Berlangsung pada P2MA belum membuktikan kegiatan atau output. Laporan pelaksanaan dan data hasil belum diperiksa.',
    status_pemeriksaan = 'BUKTI BELUM MEMADAI',
    temuan_bukti = 'Baseline baris 22 — Pelaksanaan dan hasil: TERVERIFIKASI. Status Berlangsung pada P2MA belum membuktikan kegiatan atau output. Laporan pelaksanaan dan data hasil belum diperiksa.',
    skor = NULL,
    nilai = NULL,
    alasan_skor = 'Belum diberi skor karena target yang jatuh tempo dan bukti realisasi belum tersedia atau belum cukup terverifikasi.',
    referensi_baseline = 'Pelaksanaan/hasil TERVERIFIKASI. Jumlah kegiatan terdata: 2 | Kegiatan pertama: Keterlibatan dalam pencatatan 2 HKI Cipta dalam kegiatan pangayoman Kanwil Kepulauan Riau | Status: Sudah Dilaksanakan'
WHERE mitra_id = 11 AND kode_indikator = 'I3';
UPDATE indikator_skor SET 
    bobot = 20,
    kondisi_baseline = 'Eviden implementasi TERVERIFIKASI. Jumlah kegiatan dengan keterangan bukti: 2 | Bukti pertama: https://drive.google.com/drive/folders/19Rr0mz0pC43dVCLqL1n9mv07fy_G5Xhe?usp=sharing',
    kondisi_saat_ini = 'Baseline baris 23 — Eviden implementasi: TERVERIFIKASI. Naskah dan entri P2MA tersedia, tetapi folder resmi, indeks, serta bukti implementasi belum diperiksa.',
    status_pemeriksaan = 'BUKTI BELUM MEMADAI',
    temuan_bukti = 'Baseline baris 23 — Eviden implementasi: TERVERIFIKASI. Naskah dan entri P2MA tersedia, tetapi folder resmi, indeks, serta bukti implementasi belum diperiksa.',
    skor = NULL,
    nilai = NULL,
    alasan_skor = 'Belum diberi skor karena kelengkapan, konteks, lokasi, indeks, dan keterlacakan eviden belum memadai untuk menentukan skor.',
    referensi_baseline = 'Eviden implementasi TERVERIFIKASI. Jumlah kegiatan dengan keterangan bukti: 2 | Bukti pertama: https://drive.google.com/drive/folders/19Rr0mz0pC43dVCLqL1n9mv07fy_G5Xhe?usp=sharing'
WHERE mitra_id = 11 AND kode_indikator = 'I4';
UPDATE indikator_skor SET 
    bobot = 20,
    kondisi_baseline = 'Pelaksanaan/hasil TERVERIFIKASI. Jumlah kegiatan terdata: 2 | Kegiatan pertama: Keterlibatan dalam pencatatan 2 HKI Cipta dalam kegiatan pangayoman Kanwil Kepulauan Riau | Status: Sudah Dilaksanakan',
    kondisi_saat_ini = 'Baseline baris 22 — Pelaksanaan dan hasil: TERVERIFIKASI. Status Berlangsung pada P2MA belum membuktikan kegiatan atau output. Laporan pelaksanaan dan data hasil belum diperiksa. Target, output, hasil, dan data manfaat terukur belum tersedia secara lengkap.',
    status_pemeriksaan = 'BUKTI BELUM MEMADAI',
    temuan_bukti = 'Baseline baris 22 — Pelaksanaan dan hasil: TERVERIFIKASI. Status Berlangsung pada P2MA belum membuktikan kegiatan atau output. Laporan pelaksanaan dan data hasil belum diperiksa. Target, output, hasil, dan data manfaat terukur belum tersedia secara lengkap.',
    skor = NULL,
    nilai = NULL,
    alasan_skor = 'Belum diberi skor karena output/hasil belum dapat dibandingkan dengan target organisasi dan data manfaat belum memadai.',
    referensi_baseline = 'Pelaksanaan/hasil TERVERIFIKASI. Jumlah kegiatan terdata: 2 | Kegiatan pertama: Keterlibatan dalam pencatatan 2 HKI Cipta dalam kegiatan pangayoman Kanwil Kepulauan Riau | Status: Sudah Dilaksanakan'
WHERE mitra_id = 11 AND kode_indikator = 'I5';
UPDATE indikator_skor SET 
    bobot = 10,
    kondisi_baseline = 'Masa berlaku TERVERIFIKASI; PIC internal BELUM TERVERIFIKASI; PIC mitra BELUM TERVERIFIKASI; hambatan/gap BELUM TERVERIFIKASI. Kendala/catatan pertama:',
    kondisi_saat_ini = 'Masa berlaku TERVERIFIKASI. PIC internal BELUM TERVERIFIKASI; PIC mitra BELUM TERVERIFIKASI; rencana tindak lanjut BELUM TERVERIFIKASI; hambatan/gap BELUM TERVERIFIKASI. Hambatan operasional belum dikonfirmasi. Gap verifikasi sementara mencakup arsip internal, unit pengampu, PIC, rencana tindak lanjut, pelaksanaan, dan eviden. Nomor para pihak masih perlu ditranskripsikan dari naskah.',
    status_pemeriksaan = 'BUKTI BELUM MEMADAI',
    temuan_bukti = 'Masa berlaku TERVERIFIKASI. PIC internal BELUM TERVERIFIKASI; PIC mitra BELUM TERVERIFIKASI; rencana tindak lanjut BELUM TERVERIFIKASI; hambatan/gap BELUM TERVERIFIKASI. Hambatan operasional belum dikonfirmasi. Gap verifikasi sementara mencakup arsip internal, unit pengampu, PIC, rencana tindak lanjut, pelaksanaan, dan eviden. Nomor para pihak masih perlu ditranskripsikan dari naskah.',
    skor = NULL,
    nilai = NULL,
    alasan_skor = 'Belum diberi skor karena risiko, dampak, mitigasi, pemantauan, dan keputusan keberlanjutan belum dinilai dengan bukti yang memadai.',
    referensi_baseline = 'Masa berlaku TERVERIFIKASI; PIC internal BELUM TERVERIFIKASI; PIC mitra BELUM TERVERIFIKASI; hambatan/gap BELUM TERVERIFIKASI. Kendala/catatan pertama:'
WHERE mitra_id = 11 AND kode_indikator = 'I6';

-- Partner MID 12: P05_Scorecard_STIT_Mumtaz_Karimun.xlsx
UPDATE mitra_kinerja SET status_scorecard = 'BELUM LENGKAP' WHERE id = 12;
UPDATE indikator_skor SET 
    bobot = 10,
    kondisi_baseline = 'Identitas BELUM TERVERIFIKASI; arsip BELUM TERVERIFIKASI; P2MA TERVERIFIKASI; PIC internal BELUM TERVERIFIKASI; PIC mitra BELUM TERVERIFIKASI. Update terintegrasi: Belum ada PIC mitra yang diisi',
    kondisi_saat_ini = 'Baseline baris 13–20: naskah/metadata dasar dapat ditelusuri. Status identitas=BELUM TERVERIFIKASI, arsip=BELUM TERVERIFIKASI, P2MA=TERVERIFIKASI, PIC internal=BELUM TERVERIFIKASI, PIC mitra=BELUM TERVERIFIKASI.',
    status_pemeriksaan = 'DAPAT DINILAI',
    temuan_bukti = 'Baseline baris 13–20: naskah/metadata dasar dapat ditelusuri. Status identitas=BELUM TERVERIFIKASI, arsip=BELUM TERVERIFIKASI, P2MA=TERVERIFIKASI, PIC internal=BELUM TERVERIFIKASI, PIC mitra=BELUM TERVERIFIKASI.',
    skor = 1,
    nilai = 2.50,
    alasan_skor = 'Sesuai skor 1: naskah tersedia, tetapi tata kelola operasional masih terbatas; PIC dua pihak dan/atau bukti penetapan/konfirmasi belum memadai untuk skor yang lebih tinggi.',
    referensi_baseline = 'Identitas BELUM TERVERIFIKASI; arsip BELUM TERVERIFIKASI; P2MA TERVERIFIKASI; PIC internal BELUM TERVERIFIKASI; PIC mitra BELUM TERVERIFIKASI. Update terintegrasi: Belum ada PIC mitra yang diisi'
WHERE mitra_id = 12 AND kode_indikator = 'I1';
UPDATE indikator_skor SET 
    bobot = 15,
    kondisi_baseline = 'Rencana tindak lanjut BELUM TERVERIFIKASI. Belum ada usulan tindak lanjut terisi',
    kondisi_saat_ini = 'Baseline baris 21 — Rencana tindak lanjut: BELUM TERVERIFIKASI. Ruang lingkup naskah tersedia, tetapi belum ditemukan rencana aksi atau komitmen operasional yang memuat kegiatan, periode, target, dan PIC.',
    status_pemeriksaan = 'BUKTI BELUM MEMADAI',
    temuan_bukti = 'Baseline baris 21 — Rencana tindak lanjut: BELUM TERVERIFIKASI. Ruang lingkup naskah tersedia, tetapi belum ditemukan rencana aksi atau komitmen operasional yang memuat kegiatan, periode, target, dan PIC.',
    skor = NULL,
    nilai = NULL,
    alasan_skor = 'Belum diberi skor karena informasi ''belum ditemukan/belum tersedia'' pada baseline belum selalu membuktikan secara memadai bahwa tindak lanjut memang tidak ada.',
    referensi_baseline = 'Rencana tindak lanjut BELUM TERVERIFIKASI. Belum ada usulan tindak lanjut terisi'
WHERE mitra_id = 12 AND kode_indikator = 'I2';
UPDATE indikator_skor SET 
    bobot = 15,
    kondisi_baseline = 'Pelaksanaan/hasil BELUM TERVERIFIKASI. Belum ada kegiatan pada sheet pelaksanaan',
    kondisi_saat_ini = 'Baseline baris 22 — Pelaksanaan dan hasil: BELUM TERVERIFIKASI. Status Berlangsung pada P2MA belum membuktikan kegiatan atau output. Laporan pelaksanaan dan data hasil belum diperiksa.',
    status_pemeriksaan = 'BUKTI BELUM MEMADAI',
    temuan_bukti = 'Baseline baris 22 — Pelaksanaan dan hasil: BELUM TERVERIFIKASI. Status Berlangsung pada P2MA belum membuktikan kegiatan atau output. Laporan pelaksanaan dan data hasil belum diperiksa.',
    skor = NULL,
    nilai = NULL,
    alasan_skor = 'Belum diberi skor karena target yang jatuh tempo dan bukti realisasi belum tersedia atau belum cukup terverifikasi.',
    referensi_baseline = 'Pelaksanaan/hasil BELUM TERVERIFIKASI. Belum ada kegiatan pada sheet pelaksanaan'
WHERE mitra_id = 12 AND kode_indikator = 'I3';
UPDATE indikator_skor SET 
    bobot = 20,
    kondisi_baseline = 'Eviden implementasi BELUM TERVERIFIKASI. Belum ada keterangan bukti pada sheet pelaksanaan',
    kondisi_saat_ini = 'Baseline baris 23 — Eviden implementasi: BELUM TERVERIFIKASI. Naskah dan entri P2MA tersedia, tetapi folder resmi, indeks, serta bukti implementasi belum diperiksa.',
    status_pemeriksaan = 'BUKTI BELUM MEMADAI',
    temuan_bukti = 'Baseline baris 23 — Eviden implementasi: BELUM TERVERIFIKASI. Naskah dan entri P2MA tersedia, tetapi folder resmi, indeks, serta bukti implementasi belum diperiksa.',
    skor = NULL,
    nilai = NULL,
    alasan_skor = 'Belum diberi skor karena kelengkapan, konteks, lokasi, indeks, dan keterlacakan eviden belum memadai untuk menentukan skor.',
    referensi_baseline = 'Eviden implementasi BELUM TERVERIFIKASI. Belum ada keterangan bukti pada sheet pelaksanaan'
WHERE mitra_id = 12 AND kode_indikator = 'I4';
UPDATE indikator_skor SET 
    bobot = 20,
    kondisi_baseline = 'Pelaksanaan/hasil BELUM TERVERIFIKASI. Belum ada kegiatan pada sheet pelaksanaan',
    kondisi_saat_ini = 'Baseline baris 22 — Pelaksanaan dan hasil: BELUM TERVERIFIKASI. Status Berlangsung pada P2MA belum membuktikan kegiatan atau output. Laporan pelaksanaan dan data hasil belum diperiksa. Target, output, hasil, dan data manfaat terukur belum tersedia secara lengkap.',
    status_pemeriksaan = 'BUKTI BELUM MEMADAI',
    temuan_bukti = 'Baseline baris 22 — Pelaksanaan dan hasil: BELUM TERVERIFIKASI. Status Berlangsung pada P2MA belum membuktikan kegiatan atau output. Laporan pelaksanaan dan data hasil belum diperiksa. Target, output, hasil, dan data manfaat terukur belum tersedia secara lengkap.',
    skor = NULL,
    nilai = NULL,
    alasan_skor = 'Belum diberi skor karena output/hasil belum dapat dibandingkan dengan target organisasi dan data manfaat belum memadai.',
    referensi_baseline = 'Pelaksanaan/hasil BELUM TERVERIFIKASI. Belum ada kegiatan pada sheet pelaksanaan'
WHERE mitra_id = 12 AND kode_indikator = 'I5';
UPDATE indikator_skor SET 
    bobot = 10,
    kondisi_baseline = 'Masa berlaku TERVERIFIKASI; PIC internal BELUM TERVERIFIKASI; PIC mitra BELUM TERVERIFIKASI; hambatan/gap BELUM TERVERIFIKASI. Belum ada kendala/catatan pada sheet pelaksanaan',
    kondisi_saat_ini = 'Masa berlaku TERVERIFIKASI. PIC internal BELUM TERVERIFIKASI; PIC mitra BELUM TERVERIFIKASI; rencana tindak lanjut BELUM TERVERIFIKASI; hambatan/gap BELUM TERVERIFIKASI. Hambatan operasional belum dikonfirmasi. Gap verifikasi sementara mencakup arsip internal, unit pengampu, PIC, rencana tindak lanjut, pelaksanaan, dan eviden. Urutan judul, ketercarian nama lengkap, dan nomor para pihak perlu dikonfirmasi.',
    status_pemeriksaan = 'BUKTI BELUM MEMADAI',
    temuan_bukti = 'Masa berlaku TERVERIFIKASI. PIC internal BELUM TERVERIFIKASI; PIC mitra BELUM TERVERIFIKASI; rencana tindak lanjut BELUM TERVERIFIKASI; hambatan/gap BELUM TERVERIFIKASI. Hambatan operasional belum dikonfirmasi. Gap verifikasi sementara mencakup arsip internal, unit pengampu, PIC, rencana tindak lanjut, pelaksanaan, dan eviden. Urutan judul, ketercarian nama lengkap, dan nomor para pihak perlu dikonfirmasi.',
    skor = NULL,
    nilai = NULL,
    alasan_skor = 'Belum diberi skor karena risiko, dampak, mitigasi, pemantauan, dan keputusan keberlanjutan belum dinilai dengan bukti yang memadai.',
    referensi_baseline = 'Masa berlaku TERVERIFIKASI; PIC internal BELUM TERVERIFIKASI; PIC mitra BELUM TERVERIFIKASI; hambatan/gap BELUM TERVERIFIKASI. Belum ada kendala/catatan pada sheet pelaksanaan'
WHERE mitra_id = 12 AND kode_indikator = 'I6';

-- Partner MID 13: C03_Scorecard_Universitas_Ibnu_Sina.xlsx
UPDATE mitra_kinerja SET status_scorecard = 'SIAP DIVALIDASI' WHERE id = 13;
UPDATE indikator_skor SET 
    bobot = 10,
    kondisi_baseline = 'Identitas TERVERIFIKASI; arsip TERVERIFIKASI; P2MA TERVERIFIKASI; PIC internal TIDAK RELEVAN; PIC mitra BELUM TERSEDIA. Update terintegrasi: PIC mitra: Amirullah | Jabatan: Kepala Bidang Kerja Sama | Email: info@uis.ac.id | Status: Pelaksana Tugas | PIC cadangan: Nanda Jarti',
    kondisi_saat_ini = 'Baseline baris 13–20: naskah/metadata dasar dapat ditelusuri. Status identitas=TERVERIFIKASI, arsip=TERVERIFIKASI, P2MA=TERVERIFIKASI, PIC internal=TIDAK RELEVAN, PIC mitra=BELUM TERSEDIA.',
    status_pemeriksaan = 'DAPAT DINILAI',
    temuan_bukti = 'Baseline baris 13–20: naskah/metadata dasar dapat ditelusuri. Status identitas=TERVERIFIKASI, arsip=TERVERIFIKASI, P2MA=TERVERIFIKASI, PIC internal=TIDAK RELEVAN, PIC mitra=BELUM TERSEDIA.',
    skor = 1,
    nilai = 2.50,
    alasan_skor = 'Sesuai skor 1: naskah tersedia, tetapi tata kelola operasional masih terbatas; PIC dua pihak dan/atau bukti penetapan/konfirmasi belum memadai untuk skor yang lebih tinggi.',
    referensi_baseline = 'Identitas TERVERIFIKASI; arsip TERVERIFIKASI; P2MA TERVERIFIKASI; PIC internal TIDAK RELEVAN; PIC mitra BELUM TERSEDIA. Update terintegrasi: PIC mitra: Amirullah | Jabatan: Kepala Bidang Kerja Sama | Email: info@uis.ac.id | Status: Pelaksana Tugas | PIC cadangan: Nanda Jarti'
WHERE mitra_id = 13 AND kode_indikator = 'I1';
UPDATE indikator_skor SET 
    bobot = 15,
    kondisi_baseline = 'Rencana tindak lanjut BELUM TERSEDIA. Jumlah usulan: 4 | Prioritas pertama: kegiatan pertemuan  dan evaluasi dengan kumham  | Target: persemester',
    kondisi_saat_ini = 'Baseline baris 21 — Rencana tindak lanjut: BELUM TERSEDIA. Ruang lingkup naskah tersedia, tetapi belum ditemukan rencana aksi atau komitmen operasional yang memuat kegiatan, periode, target, dan PIC.',
    status_pemeriksaan = 'DAPAT DINILAI',
    temuan_bukti = 'Baseline baris 21 — Rencana tindak lanjut: BELUM TERSEDIA. Ruang lingkup naskah tersedia, tetapi belum ditemukan rencana aksi atau komitmen operasional yang memuat kegiatan, periode, target, dan PIC.',
    skor = 0,
    nilai = 0.00,
    alasan_skor = 'Sesuai skor 0: hasil konfirmasi baseline mendukung kesimpulan bahwa belum ada rencana tindak lanjut/komitmen operasional kedua pihak.',
    referensi_baseline = 'Rencana tindak lanjut BELUM TERSEDIA. Jumlah usulan: 4 | Prioritas pertama: kegiatan pertemuan  dan evaluasi dengan kumham  | Target: persemester'
WHERE mitra_id = 13 AND kode_indikator = 'I2';
UPDATE indikator_skor SET 
    bobot = 15,
    kondisi_baseline = 'Pelaksanaan/hasil BELUM TERSEDIA. Jumlah kegiatan terdata: 3 | Kegiatan pertama: terlaksana tindak lanjut MoU | Status: Sudah Dilaksanakan',
    kondisi_saat_ini = 'Baseline baris 22 — Pelaksanaan dan hasil: BELUM TERSEDIA. Belum pernah dilakukan kegiatan bersama institusi terkait',
    status_pemeriksaan = 'DAPAT DINILAI',
    temuan_bukti = 'Baseline baris 22 — Pelaksanaan dan hasil: BELUM TERSEDIA. Belum pernah dilakukan kegiatan bersama institusi terkait',
    skor = 0,
    nilai = 0.00,
    alasan_skor = 'Sesuai skor 0: catatan/konfirmasi baseline mendukung bahwa belum ada kegiatan bersama yang dapat dibuktikan.',
    referensi_baseline = 'Pelaksanaan/hasil BELUM TERSEDIA. Jumlah kegiatan terdata: 3 | Kegiatan pertama: terlaksana tindak lanjut MoU | Status: Sudah Dilaksanakan'
WHERE mitra_id = 13 AND kode_indikator = 'I3';
UPDATE indikator_skor SET 
    bobot = 20,
    kondisi_baseline = 'Eviden implementasi BELUM TERSEDIA. Jumlah kegiatan dengan keterangan bukti: 3 | Bukti pertama: https://drive.google.com/file/d/107c9Qu9vUI9_jnW2fFgEPMzRZxhbDUgc/view?usp=drive_link',
    kondisi_saat_ini = 'Baseline baris 23 — Eviden implementasi: BELUM TERSEDIA. Belum pernah dilakukan kegiatan bersama institusi terkait Catatan pemeriksa: berdasarkan konfirmasi dengan masing-masing bidang di Kanwil, belum pernah dilakukan kegiatan bersama institusi dimaksud',
    status_pemeriksaan = 'DAPAT DINILAI',
    temuan_bukti = 'Baseline baris 23 — Eviden implementasi: BELUM TERSEDIA. Belum pernah dilakukan kegiatan bersama institusi terkait Catatan pemeriksa: berdasarkan konfirmasi dengan masing-masing bidang di Kanwil, belum pernah dilakukan kegiatan bersama institusi dimaksud',
    skor = 0,
    nilai = 0.00,
    alasan_skor = 'Sesuai skor 0: baseline mendukung bahwa belum ada kegiatan bersama dan belum ada eviden implementasi.',
    referensi_baseline = 'Eviden implementasi BELUM TERSEDIA. Jumlah kegiatan dengan keterangan bukti: 3 | Bukti pertama: https://drive.google.com/file/d/107c9Qu9vUI9_jnW2fFgEPMzRZxhbDUgc/view?usp=drive_link'
WHERE mitra_id = 13 AND kode_indikator = 'I4';
UPDATE indikator_skor SET 
    bobot = 20,
    kondisi_baseline = 'Pelaksanaan/hasil BELUM TERSEDIA. Jumlah kegiatan terdata: 3 | Kegiatan pertama: terlaksana tindak lanjut MoU | Status: Sudah Dilaksanakan',
    kondisi_saat_ini = 'Baseline baris 22 — Pelaksanaan dan hasil: BELUM TERSEDIA. Belum pernah dilakukan kegiatan bersama institusi terkait Target, output, hasil, dan data manfaat terukur belum tersedia secara lengkap.',
    status_pemeriksaan = 'DAPAT DINILAI',
    temuan_bukti = 'Baseline baris 22 — Pelaksanaan dan hasil: BELUM TERSEDIA. Belum pernah dilakukan kegiatan bersama institusi terkait Target, output, hasil, dan data manfaat terukur belum tersedia secara lengkap.',
    skor = 0,
    nilai = 0.00,
    alasan_skor = 'Sesuai skor 0: baseline mendukung bahwa belum ada kegiatan maupun hasil yang dapat dibuktikan.',
    referensi_baseline = 'Pelaksanaan/hasil BELUM TERSEDIA. Jumlah kegiatan terdata: 3 | Kegiatan pertama: terlaksana tindak lanjut MoU | Status: Sudah Dilaksanakan'
WHERE mitra_id = 13 AND kode_indikator = 'I5';
UPDATE indikator_skor SET 
    bobot = 10,
    kondisi_baseline = 'Masa berlaku TERVERIFIKASI; PIC internal TIDAK RELEVAN; PIC mitra BELUM TERSEDIA; hambatan/gap BELUM TERSEDIA. Belum ada kendala/catatan pada sheet pelaksanaan',
    kondisi_saat_ini = 'Masa berlaku TERVERIFIKASI. PIC internal TIDAK RELEVAN; PIC mitra BELUM TERSEDIA; rencana tindak lanjut BELUM TERSEDIA; hambatan/gap BELUM TERSEDIA. Belum pernah dilakukan kegiatan bersama institusi terkait',
    status_pemeriksaan = 'DAPAT DINILAI',
    temuan_bukti = 'Masa berlaku TERVERIFIKASI. PIC internal TIDAK RELEVAN; PIC mitra BELUM TERSEDIA; rencana tindak lanjut BELUM TERSEDIA; hambatan/gap BELUM TERSEDIA. Belum pernah dilakukan kegiatan bersama institusi terkait',
    skor = 1,
    nilai = 2.50,
    alasan_skor = 'Sesuai skor 1: tidak adanya aktivitas/PIC/rencana tindak lanjut yang memadai menunjukkan risiko keberlanjutan tinggi, sementara respons penanganannya belum jelas.',
    referensi_baseline = 'Masa berlaku TERVERIFIKASI; PIC internal TIDAK RELEVAN; PIC mitra BELUM TERSEDIA; hambatan/gap BELUM TERSEDIA. Belum ada kendala/catatan pada sheet pelaksanaan'
WHERE mitra_id = 13 AND kode_indikator = 'I6';

-- Partner MID 14: C04_Scorecard_UNRIKA.xlsx
UPDATE mitra_kinerja SET status_scorecard = 'BELUM LENGKAP' WHERE id = 14;
UPDATE indikator_skor SET 
    bobot = 10,
    kondisi_baseline = 'Identitas TERVERIFIKASI; arsip TERVERIFIKASI; P2MA TERVERIFIKASI; PIC internal BELUM TERSEDIA; PIC mitra BELUM TERSEDIA. Update stakeholder: PIC mitra: Ajeng Handayani Purwaningrum, B.Ec., M.B.A | Jabatan: Kepala Bagian Kerjasama  | Email: kerjasama@unrika.ac.id | Status: Tetap',
    kondisi_saat_ini = 'Baseline baris 13–20: naskah/metadata dasar dapat ditelusuri. Status identitas=TERVERIFIKASI, arsip=TERVERIFIKASI, P2MA=TERVERIFIKASI, PIC internal=BELUM TERSEDIA, PIC mitra=BELUM TERSEDIA.',
    status_pemeriksaan = 'DAPAT DINILAI',
    temuan_bukti = 'Baseline baris 13–20: naskah/metadata dasar dapat ditelusuri. Status identitas=TERVERIFIKASI, arsip=TERVERIFIKASI, P2MA=TERVERIFIKASI, PIC internal=BELUM TERSEDIA, PIC mitra=BELUM TERSEDIA.',
    skor = 1,
    nilai = 2.50,
    alasan_skor = 'Sesuai skor 1: naskah tersedia, tetapi tata kelola operasional masih terbatas; PIC dua pihak dan/atau bukti penetapan/konfirmasi belum memadai untuk skor yang lebih tinggi.',
    referensi_baseline = 'Identitas TERVERIFIKASI; arsip TERVERIFIKASI; P2MA TERVERIFIKASI; PIC internal BELUM TERSEDIA; PIC mitra BELUM TERSEDIA. Update stakeholder: PIC mitra: Ajeng Handayani Purwaningrum, B.Ec., M.B.A | Jabatan: Kepala Bagian Kerjasama  | Email: kerjasama@unrika.ac.id | Status: Tetap'
WHERE mitra_id = 14 AND kode_indikator = 'I1';
UPDATE indikator_skor SET 
    bobot = 15,
    kondisi_baseline = 'Rencana tindak lanjut BELUM TERSEDIA. Belum ada usulan tindak lanjut dari stakeholder',
    kondisi_saat_ini = 'Baseline baris 21 — Rencana tindak lanjut: BELUM TERSEDIA. Ruang lingkup naskah tersedia, tetapi belum ditemukan rencana aksi atau komitmen operasional yang memuat kegiatan, periode, target, dan PIC.',
    status_pemeriksaan = 'BUKTI BELUM MEMADAI',
    temuan_bukti = 'Baseline baris 21 — Rencana tindak lanjut: BELUM TERSEDIA. Ruang lingkup naskah tersedia, tetapi belum ditemukan rencana aksi atau komitmen operasional yang memuat kegiatan, periode, target, dan PIC.',
    skor = NULL,
    nilai = NULL,
    alasan_skor = 'Belum diberi skor karena informasi ''belum ditemukan/belum tersedia'' pada baseline belum selalu membuktikan secara memadai bahwa tindak lanjut memang tidak ada.',
    referensi_baseline = 'Rencana tindak lanjut BELUM TERSEDIA. Belum ada usulan tindak lanjut dari stakeholder'
WHERE mitra_id = 14 AND kode_indikator = 'I2';
UPDATE indikator_skor SET 
    bobot = 15,
    kondisi_baseline = 'Pelaksanaan/hasil TERVERIFIKASI. Belum ada kegiatan yang diisi stakeholder',
    kondisi_saat_ini = 'Baseline baris 22 — Pelaksanaan dan hasil: TERVERIFIKASI. Tersedia satu tautan laporan pelaksanaan; belum tersedia rencana/target tertulis sebagai pembanding realisasi.',
    status_pemeriksaan = 'DAPAT DINILAI',
    temuan_bukti = 'Baseline baris 22 — Pelaksanaan dan hasil: TERVERIFIKASI. Tersedia satu tautan laporan pelaksanaan; belum tersedia rencana/target tertulis sebagai pembanding realisasi.',
    skor = 1,
    nilai = 3.75,
    alasan_skor = 'Sesuai skor 1: terdapat kegiatan yang dapat ditelusuri, tetapi belum tersedia rencana/target tertulis sebagai pembanding realisasi; diperlakukan sebagai kegiatan ad hoc.',
    referensi_baseline = 'Pelaksanaan/hasil TERVERIFIKASI. Belum ada kegiatan yang diisi stakeholder'
WHERE mitra_id = 14 AND kode_indikator = 'I3';
UPDATE indikator_skor SET 
    bobot = 20,
    kondisi_baseline = 'Eviden implementasi BELUM TERSEDIA. Belum ada bukti kegiatan yang diisi stakeholder',
    kondisi_saat_ini = 'Baseline baris 22–23: tersedia satu tautan laporan pelaksanaan pada unsur pelaksanaan/hasil. Folder resmi, indeks bukti, dan konsolidasi eviden belum terverifikasi.',
    status_pemeriksaan = 'DAPAT DINILAI',
    temuan_bukti = 'Baseline baris 22–23: tersedia satu tautan laporan pelaksanaan pada unsur pelaksanaan/hasil. Folder resmi, indeks bukti, dan konsolidasi eviden belum terverifikasi.',
    skor = 1,
    nilai = 5.00,
    alasan_skor = 'Sesuai skor 1: eviden yang dapat ditelusuri masih sangat terbatas; folder resmi, indeks, dan konsolidasi eviden belum tersedia.',
    referensi_baseline = 'Eviden implementasi BELUM TERSEDIA. Belum ada bukti kegiatan yang diisi stakeholder'
WHERE mitra_id = 14 AND kode_indikator = 'I4';
UPDATE indikator_skor SET 
    bobot = 20,
    kondisi_baseline = 'Pelaksanaan/hasil TERVERIFIKASI. Belum ada kegiatan yang diisi stakeholder',
    kondisi_saat_ini = 'Baseline baris 22 — Pelaksanaan dan hasil: TERVERIFIKASI. Target, output, hasil, dan data manfaat terukur belum tersedia secara lengkap.',
    status_pemeriksaan = 'BUKTI BELUM MEMADAI',
    temuan_bukti = 'Baseline baris 22 — Pelaksanaan dan hasil: TERVERIFIKASI. Target, output, hasil, dan data manfaat terukur belum tersedia secara lengkap.',
    skor = NULL,
    nilai = NULL,
    alasan_skor = 'Belum diberi skor karena output/hasil belum dapat dibandingkan dengan target organisasi dan data manfaat belum memadai.',
    referensi_baseline = 'Pelaksanaan/hasil TERVERIFIKASI. Belum ada kegiatan yang diisi stakeholder'
WHERE mitra_id = 14 AND kode_indikator = 'I5';
UPDATE indikator_skor SET 
    bobot = 10,
    kondisi_baseline = 'Masa berlaku TERVERIFIKASI; PIC internal BELUM TERSEDIA; PIC mitra BELUM TERSEDIA; hambatan/gap BELUM TERSEDIA. Belum ada kendala/catatan dari stakeholder',
    kondisi_saat_ini = 'Masa berlaku TERVERIFIKASI. PIC internal BELUM TERSEDIA; PIC mitra BELUM TERSEDIA; rencana tindak lanjut BELUM TERSEDIA; hambatan/gap BELUM TERSEDIA.',
    status_pemeriksaan = 'BUKTI BELUM MEMADAI',
    temuan_bukti = 'Masa berlaku TERVERIFIKASI. PIC internal BELUM TERSEDIA; PIC mitra BELUM TERSEDIA; rencana tindak lanjut BELUM TERSEDIA; hambatan/gap BELUM TERSEDIA.',
    skor = NULL,
    nilai = NULL,
    alasan_skor = 'Belum diberi skor karena risiko, dampak, mitigasi, pemantauan, dan keputusan keberlanjutan belum dinilai dengan bukti yang memadai.',
    referensi_baseline = 'Masa berlaku TERVERIFIKASI; PIC internal BELUM TERSEDIA; PIC mitra BELUM TERSEDIA; hambatan/gap BELUM TERSEDIA. Belum ada kendala/catatan dari stakeholder'
WHERE mitra_id = 14 AND kode_indikator = 'I6';
