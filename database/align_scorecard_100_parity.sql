-- ========================================================
-- SQL SCRIPT TO ALIGN DATABASE 100% 1:1 WITH SCORECARDS
-- ========================================================
USE mitra_kinerja;

-- 1. Update deskripsi for all 18 partners to authoritative Result-Chain standard
UPDATE indikator_skor SET deskripsi = 'Kejelasan Pengelolaan & Rencana Tindak Lanjut
Cara periksa: Apakah pelaksanaan kerja sama telah memiliki penanggung jawab yang jelas, rencana tindak lanjut yang dapat dilaksanakan, dan mekanisme koordinasi untuk memastikan tindak lanjut tersebut berjalan?' WHERE kode_indikator = 'I1';
UPDATE indikator_skor SET deskripsi = 'Implementasi / Tindak Lanjut
Cara periksa: Apakah kerja sama telah ditindaklanjuti melalui kegiatan atau langkah implementasi yang relevan dan dapat dibuktikan?' WHERE kode_indikator = 'I2';
UPDATE indikator_skor SET deskripsi = 'Output
Cara periksa: Apakah implementasi menghasilkan hasil langsung/produk yang seharusnya dicapai?' WHERE kode_indikator = 'I3';
UPDATE indikator_skor SET deskripsi = 'Outcome
Cara periksa: Apakah output menghasilkan perubahan atau manfaat bagi sasaran, proses, layanan, atau organisasi?' WHERE kode_indikator = 'I4';
UPDATE indikator_skor SET deskripsi = 'Kontribusi / Dampak
Cara periksa: Apakah hasil kerja sama memberikan kontribusi yang dapat dijelaskan terhadap kinerja organisasi dan/atau pelayanan hukum?' WHERE kode_indikator = 'I5';
UPDATE indikator_skor SET deskripsi = 'Evidence & Data
Cara periksa: Apakah kondisi, pelaksanaan, dan hasil kerja sama didukung data/evidence yang dapat dipercaya dan ditelusuri?' WHERE kode_indikator = 'I6';
UPDATE indikator_skor SET deskripsi = 'Risiko & Keberlanjutan
Cara periksa: Apakah risiko pelaksanaan dikendalikan dan terdapat kondisi yang mendukung keberlanjutan manfaat kerja sama?' WHERE kode_indikator = 'I7';

-- 2. Update MIDs 1-10 indicators and metadata from 27 September workbooks
-- Partner MID 1: Scorecard_P01_DEKRANASDA_KEPRI_27_Sep_2026.xlsx
UPDATE mitra_kinerja SET status_scorecard = 'SIAP DIVALIDASI', posisi_portofolio = 'OUTCOME AWAL / IMPLEMENTASI TERBATAS', rekomendasi = 'LANJUTKAN DENGAN AKTIVASI RUANG LINGKUP DAN PENGUATAN TATA KELOLA

Temuan Utama:
Dibanding baseline, terdapat implementasi nyata pada edukasi KI dan pelayanan pendaftaran KI, beserta manfaat awal bagi UMKM/masyarakat. Namun tiga ruang lingkup belum memiliki data, PIC/rencana aksi belum jelas, dan evidence masih terbatas.

Rencana Tindak Lanjut:
1) Tetapkan PIC internal dan mitra; 2) susun rencana aksi 5 ruang lingkup; 3) aktifkan KIK, penegakan KI, dan pertukaran data; 4) konsolidasikan evidence; 5) ukur penerima layanan/UMKM dan tindak lanjut pendaftaran KI; 6) review triwulanan.' WHERE id = 1;
UPDATE indikator_skor SET 
    bobot = 10,
    kondisi_baseline = 'Unit pengampu, PIC internal, PIC mitra, dan rencana tindak lanjut: BELUM TERSEDIA pada final baseline.',
    kondisi_saat_ini = 'Belum tersedia PIC formal maupun rencana aksi terpadu pada data terbaru. Namun pelaksanaan aktual pada edukasi KI dan stand pelayanan publik menunjukkan adanya koordinasi operasional. File eviden baru yang diunggah belum terisi substantif.',
    status_pemeriksaan = 'DAPAT DINILAI',
    temuan_bukti = 'Final baseline; workbook P01 gabungan; file dekranasda.xlsx yang masih menunjukkan Total Kegiatan 0 dan placeholder PIC.',
    skor = 1,
    nilai = 2.50,
    alasan_skor = 'Pengelolaan operasional mulai terlihat dari adanya kegiatan, tetapi penanggung jawab formal, pembagian peran, mekanisme koordinasi, dan rencana tindak lanjut belum jelas. Sesuai rubrik I1, skor 1.',
    catatan_tindak_lanjut = 'Tetapkan PIC internal/mitra, susun rencana aksi per ruang lingkup, dan tetapkan mekanisme koordinasi berkala.',
    referensi_baseline = 'Unit pengampu, PIC internal, PIC mitra, dan rencana tindak lanjut: BELUM TERSEDIA pada final baseline.'
WHERE mitra_id = 1 AND kode_indikator = 'I1';
UPDATE indikator_skor SET 
    bobot = 15,
    kondisi_baseline = 'Pelaksanaan dan hasil: BELUM TERSEDIA pada final baseline.',
    kondisi_saat_ini = 'Terdapat implementasi pada 2 dari 5 ruang lingkup: edukasi/pemahaman KI melalui Kelas Kreatif UMKM Dekrafest dan pelayanan pendaftaran KI melalui stand pelayanan publik pada beberapa periode. Tiga ruang lingkup lain masih belum diperoleh datanya.',
    status_pemeriksaan = 'DAPAT DINILAI',
    temuan_bukti = 'RL10 Peningkatan Pemahaman KI; RL09 Pelayanan Pendaftaran KI; evidence berupa tautan publikasi dan laporan MIC 2025/2026.',
    skor = 2,
    nilai = 7.50,
    alasan_skor = 'Sebagian tindak lanjut telah dilaksanakan, tetapi masih terdapat gap berarti pada tiga ruang lingkup lain. Sesuai rubrik I2, skor 2.',
    catatan_tindak_lanjut = 'Aktivasi inventarisasi KIK, penegakan hukum KI, dan pertukaran data/informasi KI; catat target dan realisasinya.',
    referensi_baseline = 'Pelaksanaan dan hasil: BELUM TERSEDIA pada final baseline.'
WHERE mitra_id = 1 AND kode_indikator = 'I2';
UPDATE indikator_skor SET 
    bobot = 15,
    kondisi_baseline = 'Pelaksanaan dan eviden implementasi: BELUM TERSEDIA pada final baseline.',
    kondisi_saat_ini = 'Output tersedia pada kegiatan edukasi KI dan stand pelayanan publik: penyampaian materi, layanan informasi/pelayanan KI, serta pelaksanaan stand pada beberapa tanggal. Output pada tiga ruang lingkup lainnya belum tersedia.',
    status_pemeriksaan = 'DAPAT DINILAI',
    temuan_bukti = 'RL10 dan RL09 beserta tautan/laporan kegiatan; tiga ruang lingkup lain berstatus BELUM DIPEROLEH.',
    skor = 2,
    nilai = 7.50,
    alasan_skor = 'Sebagian output tercapai dan dapat dijelaskan, tetapi cakupannya belum mewakili keseluruhan ruang lingkup. Rubrik I3 skor 2.',
    catatan_tindak_lanjut = 'Tetapkan output minimal untuk seluruh ruang lingkup dan buat daftar output per periode.',
    referensi_baseline = 'Pelaksanaan dan eviden implementasi: BELUM TERSEDIA pada final baseline.'
WHERE mitra_id = 1 AND kode_indikator = 'I3';
UPDATE indikator_skor SET 
    bobot = 20,
    kondisi_baseline = 'Final baseline belum memiliki data outcome/manfaat terukur.',
    kondisi_saat_ini = 'Manfaat awal terlihat: meningkatnya pemahaman UMKM mengenai legalitas dan perlindungan KI serta meningkatnya akses masyarakat terhadap informasi dan layanan KI. Manfaat masih bersifat kualitatif dan belum dilengkapi ukuran penerima/manfaat.',
    status_pemeriksaan = 'DAPAT DINILAI',
    temuan_bukti = 'Outcome/manfaat pada RL10 dan RL09; belum ada metrik kuantitatif konsisten.',
    skor = 2,
    nilai = 10.00,
    alasan_skor = 'Manfaat terlihat pada sebagian sasaran tetapi belum terukur secara konsisten. Sesuai rubrik I4, skor 2.',
    catatan_tindak_lanjut = 'Ukur peserta/UMKM yang dilayani, konsultasi, permohonan KI, tindak lanjut pendaftaran, serta perubahan pemahaman.',
    referensi_baseline = 'Final baseline belum memiliki data outcome/manfaat terukur.'
WHERE mitra_id = 1 AND kode_indikator = 'I4';
UPDATE indikator_skor SET 
    bobot = 20,
    kondisi_baseline = 'Final baseline belum memiliki data kontribusi/dampak terhadap kinerja atau pelayanan.',
    kondisi_saat_ini = 'Kontribusi dapat dijelaskan pada peningkatan literasi hukum KI dan akses pelayanan KI bagi UMKM/masyarakat, tetapi belum ada pengukuran kontribusi pada sasaran kinerja secara terstruktur.',
    status_pemeriksaan = 'DAPAT DINILAI',
    temuan_bukti = 'RL10 dan RL09 menyebut dukungan pada literasi hukum, akses pelayanan, dan kualitas pelayanan publik KI.',
    skor = 2,
    nilai = 10.00,
    alasan_skor = 'Kontribusi dapat dijelaskan dan didukung sebagian evidence, tetapi belum terukur pada indikator kinerja. Rubrik I5 skor 2.',
    catatan_tindak_lanjut = 'Hubungkan outcome dengan indikator layanan KI, pendaftaran KI, dan sasaran pemberdayaan UMKM.',
    referensi_baseline = 'Final baseline belum memiliki data kontribusi/dampak terhadap kinerja atau pelayanan.'
WHERE mitra_id = 1 AND kode_indikator = 'I5';
UPDATE indikator_skor SET 
    bobot = 10,
    kondisi_baseline = 'Arsip resmi: BELUM TERSEDIA; P2MA: TERVERIFIKASI; eviden implementasi: BELUM TERSEDIA.',
    kondisi_saat_ini = 'Evidence tersedia untuk dua ruang lingkup melalui tautan publikasi serta nama laporan MIC, tetapi masih terbatas dan tersebar. File eviden baru yang diunggah belum berisi data kegiatan atau bukti tambahan.',
    status_pemeriksaan = 'DAPAT DINILAI',
    temuan_bukti = 'Tautan publikasi RL10; LAPORAN MIC 2025/2026 pada RL09; dekranasda.xlsx belum menambah evidence substantif.',
    skor = 1,
    nilai = 2.50,
    alasan_skor = 'Evidence sangat terbatas dan belum cukup tertata untuk keseluruhan kerja sama. Sesuai rubrik I6, skor 1.',
    catatan_tindak_lanjut = 'Bangun repository P01, indeks bukti per ruang lingkup, dan minta paket evidence lengkap dari mitra/PIC.',
    referensi_baseline = 'Arsip resmi: BELUM TERSEDIA; P2MA: TERVERIFIKASI; eviden implementasi: BELUM TERSEDIA.'
WHERE mitra_id = 1 AND kode_indikator = 'I6';
UPDATE indikator_skor SET 
    bobot = 10,
    kondisi_baseline = 'Masa berlaku: TERVERIFIKASI; hambatan/gap operasional: BELUM TERSEDIA.',
    kondisi_saat_ini = 'Perjanjian masih berlaku sampai 14 November 2030. Risiko utama adalah implementasi yang belum merata, tidak adanya PIC/rencana aksi formal, serta tiga ruang lingkup tanpa data. Belum terlihat mekanisme mitigasi yang terstruktur.',
    status_pemeriksaan = 'DAPAT DINILAI',
    temuan_bukti = 'Final baseline; ringkasan P01; tidak ada usulan tindak lanjut pada file eviden baru.',
    skor = 1,
    nilai = 2.50,
    alasan_skor = 'Risiko dapat dikenali tetapi penanganannya masih lemah dan belum terstruktur. Sesuai rubrik I7, skor 1.',
    catatan_tindak_lanjut = 'Tetapkan owner risiko, rencana aksi, target aktivasi tiga ruang lingkup, dan review triwulanan.',
    referensi_baseline = 'Masa berlaku: TERVERIFIKASI; hambatan/gap operasional: BELUM TERSEDIA.'
WHERE mitra_id = 1 AND kode_indikator = 'I7';

-- Partner MID 2: Scorecard_P02_BNNP_MASA_IMPLEMENTASI_AWAL_27_Sep_2026.xlsx
UPDATE mitra_kinerja SET status_scorecard = 'MASA IMPLEMENTASI AWAL', posisi_portofolio = 'OUTPUT AWAL / IMPLEMENTASI BERJALAN', rekomendasi = 'LANJUTKAN IMPLEMENTASI DAN SIAPKAN PENGUKURAN HASIL

Temuan Utama:
Sejak baseline, telah tersedia PIC mitra, rencana tindak lanjut, integrasi materi yang sedang berjalan, dan output awal berupa konten layanan hukum pada BESTI Learning Hub. Karena PKS baru berlaku sejak 13 Juli 2026, outcome dan kontribusi/dampak belum memasuki periode penilaian yang memadai.

Rencana Tindak Lanjut:
1) Tegaskan focal point dan mekanisme update konten; 2) konsolidasikan repository evidence P02; 3) lanjutkan integrasi e-book/modul; 4) realisasikan kolaborasi narasumber/podcast/webinar; 5) mulai ukur konten, akses/pengguna, pemanfaatan dan umpan balik; 6) lakukan review outcome/kontribusi setelah periode implementasi memadai.' WHERE id = 2;
UPDATE indikator_skor SET 
    bobot = 10,
    kondisi_baseline = 'Unit pengampu, PIC internal, PIC mitra, dan rencana tindak lanjut: BELUM TERSEDIA pada final baseline.',
    kondisi_saat_ini = 'PIC internal diasumsikan telah ditetapkan melalui SK. PIC mitra utama dan cadangan sudah tersedia. Mitra juga menyampaikan 5 usulan tindak lanjut dengan target waktu. Mekanisme koordinasi pembaruan konten masih perlu diperjelas, termasuk siapa penanggung jawab di Kanwil.',
    status_pemeriksaan = 'DAPAT DINILAI',
    temuan_bukti = 'SK PIC internal (diasumsikan tersedia untuk contoh); Data Stakeholder — sheet IDENTITAS & PIC dan USULAN TINDAK LANJUT; 5 rencana tindak lanjut dan PIC mitra utama/cadangan.',
    skor = 2,
    nilai = 5.00,
    alasan_skor = 'Penanggung jawab dan rencana tindak lanjut sudah mulai jelas, tetapi mekanisme koordinasi dan pembagian peran operasional untuk pembaruan konten belum sepenuhnya tegas. Sesuai rubrik, kondisi ini berada pada skor 2.',
    catatan_tindak_lanjut = 'Tetapkan alur koordinasi pembaruan materi, PIC internal yang menjadi focal point, serta kalender tindak lanjut bersama BNNP Kepri.',
    referensi_baseline = 'Unit pengampu, PIC internal, PIC mitra, dan rencana tindak lanjut: BELUM TERSEDIA pada final baseline.'
WHERE mitra_id = 2 AND kode_indikator = 'I1';
UPDATE indikator_skor SET 
    bobot = 15,
    kondisi_baseline = 'Pelaksanaan dan hasil: BELUM TERSEDIA pada final baseline.',
    kondisi_saat_ini = 'Implementasi sudah mulai berjalan. Data mitra mencatat integrasi materi Kanwil Hukum ke BESTI Learning Hub berstatus Sedang Berjalan. Data internal memverifikasi penyediaan/penayangan materi publikasi layanan hukum melalui BESTI, sementara ruang lingkup narasumber belum dilaksanakan.',
    status_pemeriksaan = 'DAPAT DINILAI',
    temuan_bukti = 'Data Stakeholder — PELAKSANAAN KEGIATAN; RL26 Kolaborasi Kampanye; RL28 Infografis/Videografis; BESTI Learning Hub: https://bestilearninghub.id/category/literasi-hukum/; RL27 menunjukkan ruang lingkup narasumber belum dilaksanakan.',
    skor = 2,
    nilai = 7.50,
    alasan_skor = 'Sebagian tindak lanjut telah dilaksanakan dan dapat dibuktikan, tetapi implementasi belum mencakup seluruh ruang lingkup dan sebagian kegiatan masih berjalan. Sesuai rubrik I2: sebagian tindak lanjut terlaksana dengan gap yang masih berarti.',
    catatan_tindak_lanjut = 'Selesaikan integrasi materi, lanjutkan e-book/modul, dan mulai realisasi ruang lingkup narasumber/podcast/webinar sesuai prioritas.',
    referensi_baseline = 'Pelaksanaan dan hasil: BELUM TERSEDIA pada final baseline.'
WHERE mitra_id = 2 AND kode_indikator = 'I2';
UPDATE indikator_skor SET 
    bobot = 15,
    kondisi_baseline = 'Pelaksanaan dan eviden implementasi: BELUM TERSEDIA pada final baseline.',
    kondisi_saat_ini = 'Output awal sudah tersedia berupa konten/materi publikasi layanan hukum Kanwil Kemenkum Kepri pada BESTI Learning Hub. Namun output belum lengkap untuk seluruh ruang lingkup, khususnya e-book/modul dan kegiatan narasumber.',
    status_pemeriksaan = 'DAPAT DINILAI',
    temuan_bukti = 'RL26 dan RL28 mencatat output tersedianya konten/materi publikasi layanan hukum pada BESTI; https://bestilearninghub.id/category/literasi-hukum/; RL24 dan RL27 menunjukkan sebagian output belum terbentuk.',
    skor = 2,
    nilai = 7.50,
    alasan_skor = 'Output nyata sudah ada dan dapat ditelusuri, tetapi baru mencakup sebagian ruang lingkup. Rubrik I3 skor 2 sesuai dengan kondisi sebagian output tercapai.',
    catatan_tindak_lanjut = 'Inventarisasi output per ruang lingkup dan tetapkan target minimum: konten, e-book/modul, kampanye, serta kegiatan narasumber.',
    referensi_baseline = 'Pelaksanaan dan eviden implementasi: BELUM TERSEDIA pada final baseline.'
WHERE mitra_id = 2 AND kode_indikator = 'I3';
UPDATE indikator_skor SET 
    bobot = 20,
    kondisi_baseline = 'Final baseline belum memiliki data outcome/manfaat terukur.',
    kondisi_saat_ini = 'Manfaat awal sudah terindikasi secara kualitatif, antara lain bertambahnya variasi materi pembelajaran dan meluasnya kanal penyebaran informasi hukum melalui BESTI. Namun usia PKS baru sekitar 2,5 bulan, sehingga belum cukup periode observasi untuk menilai outcome secara objektif.',
    status_pemeriksaan = 'BELUM DAPAT DINILAI',
    temuan_bukti = 'Data Stakeholder — PELAKSANAAN KEGIATAN kolom Hasil/Manfaat; RL26/RL28 kolom Outcome/Manfaat; tautan BESTI Learning Hub. Data ini dicatat sebagai indikasi manfaat awal, bukan dasar pemberian skor outcome.',
    skor = NULL,
    nilai = NULL,
    alasan_skor = 'Outcome belum diberi skor bukan karena bukti tidak ada, melainkan karena jangka waktu implementasi sejak 13 Juli 2026 sampai periode penilaian 27 September 2026 belum memadai untuk menilai perubahan/manfaat secara matang.',
    catatan_tindak_lanjut = 'Tetapkan indikator outcome dan mulai kumpulkan jumlah konten, akses/pengguna, tayangan, pemanfaatan materi, serta umpan balik. Nilai I4 pada review berikutnya setelah periode observasi memadai.',
    referensi_baseline = 'Final baseline belum memiliki data outcome/manfaat terukur.'
WHERE mitra_id = 2 AND kode_indikator = 'I4';
UPDATE indikator_skor SET 
    bobot = 20,
    kondisi_baseline = 'Final baseline belum memiliki data kontribusi/dampak terhadap kinerja atau pelayanan.',
    kondisi_saat_ini = 'Arah kontribusi awal dapat dijelaskan: BESTI mendukung diseminasi informasi hukum dan penyuluhan hukum melalui kanal digital. Namun pada usia PKS sekitar 2,5 bulan, hubungan antara output dengan capaian kinerja/pelayanan belum cukup matang untuk dinilai.',
    status_pemeriksaan = 'BELUM DAPAT DINILAI',
    temuan_bukti = 'RL26/RL28 mencatat arah kontribusi terhadap diseminasi informasi hukum dan penyuluhan hukum; BESTI Learning Hub menjadi kanal pelaksanaan. Data ini diperlakukan sebagai arah kontribusi awal, belum sebagai dampak yang dinilai.',
    skor = NULL,
    nilai = NULL,
    alasan_skor = 'Kontribusi/dampak belum diberi skor karena periode implementasi masih terlalu awal. Penilaian baru dilakukan setelah outcome mulai terbentuk dan dapat dihubungkan dengan indikator kinerja atau pelayanan yang relevan.',
    catatan_tindak_lanjut = 'Pada review berikutnya, hubungkan outcome terverifikasi dengan indikator publikasi/penyuluhan, pemanfaatan layanan, atau sasaran kinerja yang relevan.',
    referensi_baseline = 'Final baseline belum memiliki data kontribusi/dampak terhadap kinerja atau pelayanan.'
WHERE mitra_id = 2 AND kode_indikator = 'I5';
UPDATE indikator_skor SET 
    bobot = 10,
    kondisi_baseline = 'Arsip resmi: BELUM TERSEDIA; P2MA: TERVERIFIKASI; eviden implementasi: BELUM TERSEDIA.',
    kondisi_saat_ini = 'Evidence pasca-baseline sudah tersedia dari data mitra, matriks internal per ruang lingkup, P2MA, dan tautan BESTI. Namun evidence masih tersebar, belum seluruh ruang lingkup memiliki bukti, dan belum ada repository/index terpadu untuk scorecard.',
    status_pemeriksaan = 'DAPAT DINILAI',
    temuan_bukti = 'Data Stakeholder; RL24, RL26, RL27, RL28; naskah/P2MA; https://bestilearninghub.id/category/literasi-hukum/; SK PIC internal diasumsikan tersedia.',
    skor = 2,
    nilai = 5.00,
    alasan_skor = 'Evidence utama sudah tersedia dan dapat ditelusuri, tetapi kelengkapan dan keteraturan antar-ruang lingkup belum konsisten. Ini sesuai rubrik I6 skor 2.',
    catatan_tindak_lanjut = 'Buat satu folder/repository evidence P02, indeks per ruang lingkup, dan log versi/tanggal bukti.',
    referensi_baseline = 'Arsip resmi: BELUM TERSEDIA; P2MA: TERVERIFIKASI; eviden implementasi: BELUM TERSEDIA.'
WHERE mitra_id = 2 AND kode_indikator = 'I6';
UPDATE indikator_skor SET 
    bobot = 10,
    kondisi_baseline = 'Masa berlaku: TERVERIFIKASI; hambatan/gap operasional: BELUM TERSEDIA.',
    kondisi_saat_ini = 'Masa berlaku masih panjang sampai 12 Juli 2029. Risiko utama saat ini adalah kebutuhan penyesuaian materi dan kejelasan koordinasi dengan narasumber/pengelola. PIC mitra tersedia, PIC internal diasumsikan telah ditetapkan, dan terdapat rencana tindak lanjut untuk pengembangan konten serta evaluasi.',
    status_pemeriksaan = 'DAPAT DINILAI',
    temuan_bukti = 'Final baseline masa berlaku; Data Stakeholder — kendala/catatan, PIC, dan usulan tindak lanjut; RL26/RL28 mencatat tidak ada hambatan material dan rencana penambahan materi.',
    skor = 2,
    nilai = 5.00,
    alasan_skor = 'Risiko utama sudah mulai dikenali dan terdapat tindak lanjut, tetapi mekanisme mitigasi, evaluasi berkala, dan pengukuran keberlanjutan belum kuat. Rubrik I7 skor 2.',
    catatan_tindak_lanjut = 'Tetapkan mitigasi risiko sederhana: owner, jadwal update materi, mekanisme persetujuan konten, dan evaluasi pemanfaatan minimal tahunan.',
    referensi_baseline = 'Masa berlaku: TERVERIFIKASI; hambatan/gap operasional: BELUM TERSEDIA.'
WHERE mitra_id = 2 AND kode_indikator = 'I7';

-- Partner MID 3: Scorecard_P03_PEMKOT_TANJUNGPINANG_FINAL_27_Sep_2026.xlsx
UPDATE mitra_kinerja SET status_scorecard = 'SIAP DIVALIDASI', posisi_portofolio = 'OUTCOME AWAL / IMPLEMENTASI AKTIF', rekomendasi = 'LANJUTKAN DENGAN PENGUATAN IMPLEMENTASI, EVIDENCE, DAN PENGUKURAN OUTCOME

Temuan Utama:
Dibanding baseline, kerja sama telah berkembang menjadi implementasi aktif: 10 kegiatan mitra seluruhnya memiliki bukti; Posbankum, harmonisasi produk hukum, dan JDIH menunjukkan output serta manfaat awal. Gap utama adalah pemerataan implementasi pada AHU/KI, penegasan PIC internal, konsolidasi evidence, dan pengukuran outcome/kontribusi.

Rencana Tindak Lanjut:
1) Tegaskan PIC/focal point internal per ruang lingkup; 2) konsolidasikan repository evidence P03; 3) lanjutkan pembinaan Posbankum, harmonisasi, dan JDIH; 4) aktivasi tindak lanjut AHU dan klarifikasi layanan KI; 5) ukur penerima layanan, jumlah harmonisasi, capaian JDIH, serta outcome layanan; 6) lakukan review triwulanan.' WHERE id = 3;
UPDATE indikator_skor SET 
    bobot = 10,
    kondisi_baseline = 'Unit pengampu, PIC internal, PIC mitra, dan rencana tindak lanjut: BELUM TERSEDIA pada final baseline.',
    kondisi_saat_ini = 'PIC mitra utama dan cadangan sudah tersedia dan aktif dalam koordinasi. Terdapat 2 usulan tindak lanjut dengan target dan pihak yang dilibatkan, serta pelaksanaan aktual menunjukkan koordinasi lintas ruang lingkup. PIC internal individual belum tercantum secara eksplisit dalam paket data.',
    status_pemeriksaan = 'DAPAT DINILAI',
    temuan_bukti = 'Data Stakeholder — IDENTITAS & PIC; USULAN TINDAK LANJUT; 10 kegiatan aktual dengan bukti; matriks internal RL14–RL18.',
    skor = 2,
    nilai = 5.00,
    alasan_skor = 'Penanggung jawab mitra dan sebagian rencana tindak lanjut sudah jelas serta koordinasi operasional terbukti berjalan. Namun pengampu/PIC internal individual dan pembagian peran lintas seluruh ruang lingkup belum terdokumentasi secara konsisten. Sesuai rubrik I1, kondisi ini berada pada skor 2.',
    catatan_tindak_lanjut = 'Tetapkan/tegaskan focal point internal per ruang lingkup, susun daftar PIC aktif, dan satukan rencana tindak lanjut dalam kalender kerja bersama.',
    referensi_baseline = 'Unit pengampu, PIC internal, PIC mitra, dan rencana tindak lanjut: BELUM TERSEDIA pada final baseline.'
WHERE mitra_id = 3 AND kode_indikator = 'I1';
UPDATE indikator_skor SET 
    bobot = 15,
    kondisi_baseline = 'Pelaksanaan dan hasil: BELUM TERSEDIA pada final baseline.',
    kondisi_saat_ini = 'Implementasi telah berjalan nyata. Mitra melaporkan 10 kegiatan yang seluruhnya sudah dilaksanakan dan memiliki bukti, meliputi layanan/pembinaan Posbankum serta harmonisasi produk hukum daerah. Data internal juga menunjukkan pelaksanaan Posbankum, JDIH, dan harmonisasi. Layanan AHU belum dilaksanakan dan data KI belum diperoleh.',
    status_pemeriksaan = 'DAPAT DINILAI',
    temuan_bukti = 'Data Stakeholder — PELAKSANAAN KEGIATAN: 10 kegiatan, 10 bukti; RL14 Produk Hukum; RL15 Posbankum; RL16 JDIH; RL17 AHU; RL18 KI.',
    skor = 3,
    nilai = 11.25,
    alasan_skor = 'Tindak lanjut utama telah terlaksana dan berjalan cukup konsisten pada beberapa ruang lingkup utama, dengan bukti kegiatan yang dapat ditelusuri. Belum mencapai skor 4 karena implementasi belum merata pada layanan AHU dan KI.',
    catatan_tindak_lanjut = 'Pertahankan kesinambungan Posbankum, harmonisasi, dan JDIH; aktivasi tindak lanjut AHU; konfirmasi dan tindak lanjuti status layanan KI.',
    referensi_baseline = 'Pelaksanaan dan hasil: BELUM TERSEDIA pada final baseline.'
WHERE mitra_id = 3 AND kode_indikator = 'I2';
UPDATE indikator_skor SET 
    bobot = 15,
    kondisi_baseline = 'Pelaksanaan dan eviden implementasi: BELUM TERSEDIA pada final baseline.',
    kondisi_saat_ini = 'Output nyata telah dihasilkan: layanan Posbankum, koordinasi/evaluasi dan pembinaan paralegal, hasil harmonisasi Ranperda/Ranperwako, serta output asistensi JDIH. Output belum merata pada seluruh ruang lingkup karena AHU belum berjalan dan KI belum terkonfirmasi.',
    status_pemeriksaan = 'DAPAT DINILAI',
    temuan_bukti = '10 kegiatan dan link bukti dari Pemkot Tanjungpinang; RL14 mencatat Surat Penyampaian Hasil/e-Harmonisasi; RL15 mencatat Posbankum; RL16 mencatat laporan/dokumentasi JDIH.',
    skor = 3,
    nilai = 11.25,
    alasan_skor = 'Sebagian besar output utama pada ruang lingkup yang aktif telah tercapai dan dapat dibuktikan. Belum skor 4 karena output belum lengkap/konsisten pada seluruh ruang lingkup kerja sama.',
    catatan_tindak_lanjut = 'Buat daftar output per ruang lingkup dan target tahunan agar capaian AHU, KI, JDIH, Posbankum, dan produk hukum dapat dibandingkan secara periodik.',
    referensi_baseline = 'Pelaksanaan dan eviden implementasi: BELUM TERSEDIA pada final baseline.'
WHERE mitra_id = 3 AND kode_indikator = 'I3';
UPDATE indikator_skor SET 
    bobot = 20,
    kondisi_baseline = 'Final baseline belum memiliki data outcome/manfaat terukur.',
    kondisi_saat_ini = 'Manfaat sudah terlihat pada beberapa sasaran: masyarakat memperoleh layanan konsultasi/informasi hukum dan pendampingan melalui Posbankum; kesiapan pengelolaan JDIH meningkat; serta harmonisasi menghasilkan rancangan produk hukum yang lebih selaras. Namun ukuran kuantitatif outcome belum konsisten.',
    status_pemeriksaan = 'DAPAT DINILAI',
    temuan_bukti = 'Data Stakeholder — hasil/manfaat 10 kegiatan; RL14 outcome harmonisasi; RL15 outcome layanan masyarakat; RL16 outcome kesiapan JDIH.',
    skor = 2,
    nilai = 10.00,
    alasan_skor = 'Outcome telah terlihat pada sebagian sasaran dan proses, tetapi belum disertai indikator kuantitatif yang cukup untuk menunjukkan outcome utama secara terukur. Sesuai rubrik I4, skor 2.',
    catatan_tindak_lanjut = 'Mulai ukur jumlah penerima layanan Posbankum, jumlah/jenis produk hukum yang diharmonisasi, tindak lanjut JDIH, serta indikator manfaat layanan lainnya.',
    referensi_baseline = 'Final baseline belum memiliki data outcome/manfaat terukur.'
WHERE mitra_id = 3 AND kode_indikator = 'I4';
UPDATE indikator_skor SET 
    bobot = 20,
    kondisi_baseline = 'Final baseline belum memiliki data kontribusi/dampak terhadap kinerja organisasi atau pelayanan hukum.',
    kondisi_saat_ini = 'Kontribusi terhadap pelayanan hukum dapat dijelaskan: memperluas akses konsultasi dan bantuan hukum, memperkuat kualitas produk hukum daerah, serta mendukung pengelolaan dan akses informasi hukum melalui JDIH. Hubungan dengan indikator kinerja organisasi belum diukur secara konsisten.',
    status_pemeriksaan = 'DAPAT DINILAI',
    temuan_bukti = 'RL14 kontribusi pada kualitas/kepastian hukum; RL15 kontribusi pada layanan masyarakat; RL16 kontribusi pada kualitas pengelolaan dan akses informasi hukum; 10 kegiatan Pemkot dengan bukti.',
    skor = 2,
    nilai = 10.00,
    alasan_skor = 'Kontribusi terhadap kinerja/pelayanan dapat dijelaskan dan didukung sebagian data, namun belum ada ukuran kontribusi yang terstruktur dan terukur untuk keseluruhan portofolio. Sesuai rubrik I5, skor 2.',
    catatan_tindak_lanjut = 'Hubungkan output/outcome dengan indikator layanan hukum, misalnya jumlah layanan Posbankum, jumlah harmonisasi, kualitas/kepatuhan produk hukum, dan indikator JDIH.',
    referensi_baseline = 'Final baseline belum memiliki data kontribusi/dampak terhadap kinerja organisasi atau pelayanan hukum.'
WHERE mitra_id = 3 AND kode_indikator = 'I5';
UPDATE indikator_skor SET 
    bobot = 10,
    kondisi_baseline = 'Arsip resmi: BELUM TERSEDIA; P2MA: TERVERIFIKASI; eviden implementasi: BELUM TERSEDIA.',
    kondisi_saat_ini = 'Evidence pasca-baseline jauh lebih kuat: 10 kegiatan dari mitra memiliki tautan bukti, ditambah bukti internal pada e-Harmonisasi, laporan Posbankum, laporan/dokumentasi JDIH, dan naskah/P2MA. Namun evidence belum terkonsolidasi dalam satu repository dan data KI masih belum diperoleh.',
    status_pemeriksaan = 'DAPAT DINILAI',
    temuan_bukti = '10 tautan Google Drive dari Data Stakeholder; e-Harmonisasi; laporan/dokumentasi Posbankum dan JDIH; P2MA; RL14–RL18.',
    skor = 2,
    nilai = 5.00,
    alasan_skor = 'Evidence utama tersedia dan dapat ditelusuri, tetapi belum konsisten dalam kelengkapan dan tata kelolanya untuk seluruh ruang lingkup. Khusus layanan KI, data/evidence masih belum diperoleh. Sesuai rubrik I6, skor 2.',
    catatan_tindak_lanjut = 'Konsolidasikan satu repository evidence P03, beri indeks per ruang lingkup dan periode, serta lengkapi data/evidence layanan KI dan status AHU.',
    referensi_baseline = 'Arsip resmi: BELUM TERSEDIA; P2MA: TERVERIFIKASI; eviden implementasi: BELUM TERSEDIA.'
WHERE mitra_id = 3 AND kode_indikator = 'I6';
UPDATE indikator_skor SET 
    bobot = 10,
    kondisi_baseline = 'Masa berlaku: TERVERIFIKASI; hambatan/gap operasional: BELUM TERSEDIA.',
    kondisi_saat_ini = 'Perjanjian masih berlaku sampai 5 Januari 2031. Risiko/gap yang terlihat adalah ketidakmerataan implementasi antar ruang lingkup, kebutuhan pembinaan Posbankum berkelanjutan, tindak lanjut penguatan JDIH, belum adanya kegiatan AHU, dan belum diperolehnya data KI. Terdapat usulan tindak lanjut untuk penguatan paralegal dan harmonisasi.',
    status_pemeriksaan = 'DAPAT DINILAI',
    temuan_bukti = 'Final baseline masa berlaku; RL15 hambatan Posbankum; RL16 kebutuhan tindak lanjut JDIH; RL17 AHU tidak ada kegiatan; RL18 KI belum diperoleh; Data Stakeholder — USULAN TINDAK LANJUT.',
    skor = 2,
    nilai = 5.00,
    alasan_skor = 'Risiko utama sudah terlihat dan sebagian mulai ditangani melalui tindak lanjut, tetapi masih ada gap penting pada pemerataan implementasi, tata kelola evidence, AHU, KI, serta pengukuran hasil. Sesuai rubrik I7, skor 2.',
    catatan_tindak_lanjut = 'Tetapkan owner dan target mitigasi per gap, termasuk aktivasi AHU/KI, pembinaan Posbankum, tindak lanjut JDIH, dan review triwulanan.',
    referensi_baseline = 'Masa berlaku: TERVERIFIKASI; hambatan/gap operasional: BELUM TERSEDIA.'
WHERE mitra_id = 3 AND kode_indikator = 'I7';

-- Partner MID 4: Scorecard_P04_BAPPERIDA_BINTAN_27_Sep_2026.xlsx
UPDATE mitra_kinerja SET status_scorecard = 'MASA IMPLEMENTASI AWAL', posisi_portofolio = 'OUTPUT AWAL / IMPLEMENTASI BERJALAN', rekomendasi = 'LANJUTKAN IMPLEMENTASI AWAL DAN BANGUN BUKTI HASIL

Temuan Utama:
Dibanding baseline, kini telah tersedia PIC mitra, 3 usulan tindak lanjut, 2 kegiatan dengan bukti, dan output awal. Namun kerja sama masih dalam masa implementasi awal dan implementasi inti KI belum merata.

Rencana Tindak Lanjut:
1) Konfirmasi PIC internal; 2) lengkapi target 3 usulan tindak lanjut; 3) laksanakan sosialisasi KI dan fasilitasi pendaftaran; 4) siapkan arah pembentukan/penguatan Sentra KI; 5) konsolidasikan evidence; 6) kumpulkan indikator outcome untuk review berikutnya.' WHERE id = 4;
UPDATE indikator_skor SET 
    bobot = 10,
    kondisi_baseline = 'Unit pengampu, PIC internal/mitra, dan rencana tindak lanjut belum lengkap pada final baseline.',
    kondisi_saat_ini = 'PIC mitra utama dan cadangan sudah jelas dan hadir dalam forum penguatan. Terdapat 3 usulan tindak lanjut, termasuk sosialisasi HAKI dan fasilitasi pendaftaran KI. Indikasi PIC internal ada pada baseline, tetapi dasar formal belum tersedia.',
    status_pemeriksaan = 'DAPAT DINILAI',
    temuan_bukti = 'baperida.xlsx — IDENTITAS & PIC dan USULAN TINDAK LANJUT; final baseline P04.',
    skor = 2,
    nilai = 5.00,
    alasan_skor = 'Penanggung jawab mitra dan sebagian rencana tindak lanjut sudah jelas, tetapi pembagian peran dan mekanisme koordinasi internal belum konsisten. Rubrik I1 skor 2.',
    catatan_tindak_lanjut = 'Konfirmasi PIC internal secara formal, tetapkan owner per ruang lingkup, dan lengkapi target waktu usulan 2–3.',
    referensi_baseline = 'Unit pengampu, PIC internal/mitra, dan rencana tindak lanjut belum lengkap pada final baseline.'
WHERE mitra_id = 4 AND kode_indikator = 'I1';
UPDATE indikator_skor SET 
    bobot = 15,
    kondisi_baseline = 'Pelaksanaan dan hasil: BELUM TERSEDIA pada final baseline.',
    kondisi_saat_ini = 'Terdapat 2 kegiatan pasca-baseline: Rapat Koordinasi Kelitbangan Kabupaten Bintan 2026 dan penyediaan data HAKI terdaftar untuk kebutuhan IGA 2026. Keterkaitan keduanya dengan ruang lingkup PKS tersedia secara umum, namun implementasi inti KI masih sangat awal.',
    status_pemeriksaan = 'DAPAT DINILAI',
    temuan_bukti = 'baperida.xlsx — 2 kegiatan berstatus Sudah Dilaksanakan, masing-masing memiliki tautan bukti.',
    skor = 1,
    nilai = 3.75,
    alasan_skor = 'Tindak lanjut sudah mulai terjadi tetapi masih sangat terbatas dibanding lima ruang lingkup utama. Rubrik I2 skor 1.',
    catatan_tindak_lanjut = 'Prioritaskan sosialisasi KI, fasilitasi pendaftaran, dan pembentukan/penguatan Sentra KI.',
    referensi_baseline = 'Pelaksanaan dan hasil: BELUM TERSEDIA pada final baseline.'
WHERE mitra_id = 4 AND kode_indikator = 'I2';
UPDATE indikator_skor SET 
    bobot = 15,
    kondisi_baseline = 'Pelaksanaan dan eviden implementasi: BELUM TERSEDIA pada final baseline.',
    kondisi_saat_ini = 'Output awal berupa koordinasi kelitbangan dan tersedianya data HAKI terdaftar. Output ini berguna sebagai fondasi, tetapi belum merupakan output utama yang lengkap dari ruang lingkup sosialisasi, kapasitas SDM, fasilitasi pendaftaran, dan Sentra KI.',
    status_pemeriksaan = 'DAPAT DINILAI',
    temuan_bukti = 'baperida.xlsx — hasil/manfaat 2 kegiatan dan tautan bukti.',
    skor = 1,
    nilai = 3.75,
    alasan_skor = 'Output masih sangat terbatas dibanding keluaran utama yang diharapkan. Rubrik I3 skor 1.',
    catatan_tindak_lanjut = 'Tetapkan output minimum untuk sosialisasi, pendaftaran KI, peningkatan kapasitas, dan Sentra KI.',
    referensi_baseline = 'Pelaksanaan dan eviden implementasi: BELUM TERSEDIA pada final baseline.'
WHERE mitra_id = 4 AND kode_indikator = 'I3';
UPDATE indikator_skor SET 
    bobot = 20,
    kondisi_baseline = 'Final baseline belum memiliki data outcome/manfaat terukur.',
    kondisi_saat_ini = 'Manfaat awal disebutkan berupa peningkatan koordinasi/sinergi kelitbangan dan pemenuhan data untuk IGA. Namun usia PKS baru 89 hari, sehingga outcome KI belum cukup matang untuk dinilai.',
    status_pemeriksaan = 'BELUM DAPAT DINILAI',
    temuan_bukti = 'baperida.xlsx — hasil/manfaat awal dicatat sebagai indikasi, bukan dasar pemberian skor outcome.',
    skor = NULL,
    nilai = NULL,
    alasan_skor = 'Outcome belum diberi skor karena periode implementasi belum memadai (<6 bulan), bukan karena bukti tidak ada.',
    catatan_tindak_lanjut = 'Mulai kumpulkan indikator outcome: jumlah peserta, usulan/pendaftaran KI, layanan Sentra KI, dan pemanfaatan hasil.',
    referensi_baseline = 'Final baseline belum memiliki data outcome/manfaat terukur.'
WHERE mitra_id = 4 AND kode_indikator = 'I4';
UPDATE indikator_skor SET 
    bobot = 20,
    kondisi_baseline = 'Final baseline belum memiliki data kontribusi/dampak.',
    kondisi_saat_ini = 'Arah kontribusi pada inovasi daerah dan pemanfaatan data KI mulai terlihat, tetapi hubungan dengan capaian kinerja/pelayanan KI belum cukup matang pada usia PKS 89 hari.',
    status_pemeriksaan = 'BELUM DAPAT DINILAI',
    temuan_bukti = 'baperida.xlsx — manfaat awal dan usulan tindak lanjut; belum ada outcome matang.',
    skor = NULL,
    nilai = NULL,
    alasan_skor = 'Kontribusi/dampak belum dinilai karena masa implementasi masih awal.',
    catatan_tindak_lanjut = 'Nilai kontribusi setelah outcome pendaftaran, kapasitas SDM, atau Sentra KI mulai terbentuk.',
    referensi_baseline = 'Final baseline belum memiliki data kontribusi/dampak.'
WHERE mitra_id = 4 AND kode_indikator = 'I5';
UPDATE indikator_skor SET 
    bobot = 10,
    kondisi_baseline = 'Arsip dan P2MA: TERVERIFIKASI; eviden implementasi: BELUM TERSEDIA.',
    kondisi_saat_ini = 'Evidence pasca-baseline tersedia untuk 2 kegiatan melalui tautan Google Drive, dan data PIC/identitas mitra terisi. Namun data internal per 5 ruang lingkup masih berstatus BELUM DIPEROLEH dan evidence belum terstruktur.',
    status_pemeriksaan = 'DAPAT DINILAI',
    temuan_bukti = 'baperida.xlsx; workbook P04 gabungan; final baseline P04.',
    skor = 2,
    nilai = 5.00,
    alasan_skor = 'Evidence utama awal tersedia tetapi kelengkapan dan keteraturan antar ruang lingkup belum konsisten. Rubrik I6 skor 2.',
    catatan_tindak_lanjut = 'Buat repository P04 dan indeks bukti per kegiatan/ruang lingkup; sinkronkan data internal dengan bukti mitra.',
    referensi_baseline = 'Arsip dan P2MA: TERVERIFIKASI; eviden implementasi: BELUM TERSEDIA.'
WHERE mitra_id = 4 AND kode_indikator = 'I6';
UPDATE indikator_skor SET 
    bobot = 10,
    kondisi_baseline = 'Masa berlaku: TERVERIFIKASI; hambatan/gap operasional: BELUM TERSEDIA.',
    kondisi_saat_ini = 'Risiko utama pada masa awal adalah belum jelasnya PIC internal formal, tindak lanjut yang belum memiliki target lengkap, dan implementasi inti KI yang masih terbatas. Terdapat 3 usulan tindak lanjut sebagai dasar keberlanjutan.',
    status_pemeriksaan = 'DAPAT DINILAI',
    temuan_bukti = 'baperida.xlsx — PIC, 3 usulan tindak lanjut, 2 kegiatan; final baseline.',
    skor = 2,
    nilai = 5.00,
    alasan_skor = 'Risiko utama mulai dikenali dan terdapat arah tindak lanjut, tetapi mitigasi dan pengendalian belum matang. Rubrik I7 skor 2.',
    catatan_tindak_lanjut = 'Tetapkan target waktu, owner, dan review bulanan selama masa implementasi awal.',
    referensi_baseline = 'Masa berlaku: TERVERIFIKASI; hambatan/gap operasional: BELUM TERSEDIA.'
WHERE mitra_id = 4 AND kode_indikator = 'I7';

-- Partner MID 5: Scorecard_P05_STAIN_SAR_KEPRI_27_Sep_2026.xlsx
UPDATE mitra_kinerja SET status_scorecard = 'SIAP DIVALIDASI', posisi_portofolio = 'OUTCOME AWAL / IMPLEMENTASI TERBATAS', rekomendasi = 'LANJUTKAN DENGAN PERLUASAN IMPLEMENTASI DAN PENGUATAN TATA KELOLA

Temuan Utama:
Dibanding baseline, sudah terdapat implementasi pada edukasi KI dan fasilitasi pendaftaran KI, termasuk 4 ciptaan tercatat dan satu pendaftaran baru yang sedang berjalan. Namun implementasi belum merata, tidak ada rencana tindak lanjut dari mitra, dan PIC internal/evidence lintas ruang lingkup masih perlu diperkuat.

Rencana Tindak Lanjut:
1) Tetapkan PIC internal; 2) susun rencana aksi tahunan; 3) aktivasi penelitian, pengabdian, MBKM, kompetensi SDM dan JDIH; 4) lanjutkan fasilitasi KI dan pantau pendaftaran EC002026157468; 5) konsolidasikan evidence; 6) ukur outcome dan review triwulanan.' WHERE id = 5;
UPDATE indikator_skor SET 
    bobot = 10,
    kondisi_baseline = 'Unit pengampu, PIC internal, PIC mitra, dan rencana tindak lanjut: BELUM TERSEDIA pada final baseline.',
    kondisi_saat_ini = 'PIC mitra utama dan cadangan sudah jelas dan PIC utama hadir dalam forum penguatan. Pelaksanaan kegiatan menunjukkan koordinasi, tetapi PIC internal dan rencana aksi terpadu belum tersedia; file mitra tidak memuat usulan tindak lanjut.',
    status_pemeriksaan = 'DAPAT DINILAI',
    temuan_bukti = 'stain sar.xlsx — IDENTITAS & PIC; RINGKASAN usulan 0; workbook P05 gabungan.',
    skor = 1,
    nilai = 2.50,
    alasan_skor = 'Penanggung jawab mitra sudah diketahui, tetapi pembagian peran internal dan rencana tindak lanjut belum jelas. Rubrik I1 skor 1.',
    catatan_tindak_lanjut = 'Tetapkan PIC internal dan susun rencana aksi seluruh ruang lingkup dengan target waktu.',
    referensi_baseline = 'Unit pengampu, PIC internal, PIC mitra, dan rencana tindak lanjut: BELUM TERSEDIA pada final baseline.'
WHERE mitra_id = 5 AND kode_indikator = 'I1';
UPDATE indikator_skor SET 
    bobot = 15,
    kondisi_baseline = 'Pelaksanaan dan hasil: BELUM TERSEDIA pada final baseline.',
    kondisi_saat_ini = 'Implementasi terbukti pada pendidikan/pengajaran melalui narasumber KI, fasilitasi pencatatan 4 ciptaan, dan satu fasilitasi pendaftaran KI yang sedang berjalan dengan nomor pendaftaran EC002026157468. Sebagian besar ruang lingkup lain belum menunjukkan pelaksanaan.',
    status_pemeriksaan = 'DAPAT DINILAI',
    temuan_bukti = 'RL01 Pendidikan; RL05 KI; stain sar.xlsx — PELAKSANAAN KEGIATAN.',
    skor = 2,
    nilai = 7.50,
    alasan_skor = 'Sebagian tindak lanjut telah dilaksanakan, tetapi gap antar ruang lingkup masih berarti. Rubrik I2 skor 2.',
    catatan_tindak_lanjut = 'Aktifkan penelitian, pengabdian, MBKM, kompetensi SDM, dan JDIH melalui kalender kegiatan bersama.',
    referensi_baseline = 'Pelaksanaan dan hasil: BELUM TERSEDIA pada final baseline.'
WHERE mitra_id = 5 AND kode_indikator = 'I2';
UPDATE indikator_skor SET 
    bobot = 15,
    kondisi_baseline = 'Pelaksanaan dan eviden implementasi: BELUM TERSEDIA pada final baseline.',
    kondisi_saat_ini = 'Output tersedia berupa penyampaian materi KI, terbitnya sertifikat pencatatan atas 4 ciptaan, dan proses pendaftaran baru dengan nomor EC002026157468. Output belum merata pada ruang lingkup Tri Dharma dan layanan hukum lainnya.',
    status_pemeriksaan = 'DAPAT DINILAI',
    temuan_bukti = 'Laporan Kegiatan 18 Desember 2025; data fasilitasi pencatatan cipta; stain sar.xlsx nomor pendaftaran EC002026157468.',
    skor = 2,
    nilai = 7.50,
    alasan_skor = 'Sebagian output tercapai dan dapat dibuktikan, namun belum mewakili seluruh output utama kerja sama. Rubrik I3 skor 2.',
    catatan_tindak_lanjut = 'Susun target output per ruang lingkup dan dokumentasikan capaian secara periodik.',
    referensi_baseline = 'Pelaksanaan dan eviden implementasi: BELUM TERSEDIA pada final baseline.'
WHERE mitra_id = 5 AND kode_indikator = 'I3';
UPDATE indikator_skor SET 
    bobot = 20,
    kondisi_baseline = 'Final baseline belum memiliki data outcome/manfaat terukur.',
    kondisi_saat_ini = 'Manfaat awal terlihat berupa meningkatnya pemahaman peserta tentang pelindungan KI serta tersedianya bukti administratif pencatatan karya dosen/mahasiswa. Manfaat belum diukur secara kuantitatif dan cakupannya masih terbatas.',
    status_pemeriksaan = 'DAPAT DINILAI',
    temuan_bukti = 'Outcome pada RL01 dan RL05; stain sar.xlsx — hasil ''Terdaftarnya karya dosen/mahasiswa''.',
    skor = 2,
    nilai = 10.00,
    alasan_skor = 'Manfaat terlihat pada sebagian sasaran tetapi belum terukur dan belum meluas ke sebagian besar ruang lingkup. Rubrik I4 skor 2.',
    catatan_tindak_lanjut = 'Ukur jumlah dosen/mahasiswa terlayani, permohonan/sertifikat KI, pemanfaatan materi, dan tindak lanjut kegiatan Tri Dharma.',
    referensi_baseline = 'Final baseline belum memiliki data outcome/manfaat terukur.'
WHERE mitra_id = 5 AND kode_indikator = 'I4';
UPDATE indikator_skor SET 
    bobot = 20,
    kondisi_baseline = 'Final baseline belum memiliki data kontribusi/dampak terhadap kinerja atau pelayanan.',
    kondisi_saat_ini = 'Kontribusi dapat dijelaskan pada edukasi KI, fasilitasi pelindungan karya akademik, dan penguatan ekosistem KI di perguruan tinggi. Namun kontribusi terhadap sasaran kinerja keseluruhan kerja sama belum diukur.',
    status_pemeriksaan = 'DAPAT DINILAI',
    temuan_bukti = 'RL01 dan RL05 mencatat kontribusi pada edukasi/diseminasi KI dan layanan fasilitasi/pelindungan KI.',
    skor = 2,
    nilai = 10.00,
    alasan_skor = 'Kontribusi dapat dijelaskan dan didukung sebagian data, tetapi belum terukur dan belum mencakup seluruh portofolio. Rubrik I5 skor 2.',
    catatan_tindak_lanjut = 'Hubungkan outcome KI dan Tri Dharma dengan indikator pelayanan hukum dan kinerja perguruan tinggi.',
    referensi_baseline = 'Final baseline belum memiliki data kontribusi/dampak terhadap kinerja atau pelayanan.'
WHERE mitra_id = 5 AND kode_indikator = 'I5';
UPDATE indikator_skor SET 
    bobot = 10,
    kondisi_baseline = 'Arsip dan P2MA: TERVERIFIKASI; eviden implementasi: BELUM TERSEDIA.',
    kondisi_saat_ini = 'Evidence sudah tersedia berupa laporan kegiatan, data/sertifikat pencatatan 4 ciptaan, dan nomor pendaftaran KI baru. Namun banyak matriks ruang lingkup masih nihil/belum diperoleh dan terdapat beberapa versi data yang perlu direkonsiliasi.',
    status_pemeriksaan = 'DAPAT DINILAI',
    temuan_bukti = 'P05 workbook gabungan; stain sar.xlsx; laporan kegiatan dan bukti pendaftaran KI.',
    skor = 2,
    nilai = 5.00,
    alasan_skor = 'Evidence utama tersedia tetapi belum konsisten dalam kelengkapan dan keteraturan antar ruang lingkup. Rubrik I6 skor 2.',
    catatan_tindak_lanjut = 'Rekonsiliasi versi data, konsolidasikan repository P05, dan indekskan bukti per ruang lingkup.',
    referensi_baseline = 'Arsip dan P2MA: TERVERIFIKASI; eviden implementasi: BELUM TERSEDIA.'
WHERE mitra_id = 5 AND kode_indikator = 'I6';
UPDATE indikator_skor SET 
    bobot = 10,
    kondisi_baseline = 'Masa berlaku: TERVERIFIKASI; hambatan/gap operasional: BELUM TERSEDIA.',
    kondisi_saat_ini = 'Perjanjian masih berlaku sampai 18 Maret 2030. Risiko utama adalah implementasi yang terkonsentrasi pada KI/pendidikan, ketiadaan rencana aksi, PIC internal yang belum jelas, dan sebagian besar ruang lingkup belum aktif.',
    status_pemeriksaan = 'DAPAT DINILAI',
    temuan_bukti = 'Final baseline; P05 gabungan; stain sar.xlsx menunjukkan usulan tindak lanjut 0.',
    skor = 1,
    nilai = 2.50,
    alasan_skor = 'Risiko sudah dapat dikenali tetapi penanganannya belum terstruktur dan dukungan keberlanjutan lintas ruang lingkup masih lemah. Rubrik I7 skor 1.',
    catatan_tindak_lanjut = 'Tetapkan rencana aksi tahunan, owner per ruang lingkup, dan review triwulanan untuk memastikan aktivasi kegiatan.',
    referensi_baseline = 'Masa berlaku: TERVERIFIKASI; hambatan/gap operasional: BELUM TERSEDIA.'
WHERE mitra_id = 5 AND kode_indikator = 'I7';

-- Partner MID 6: Scorecard_P06_UMRAH_27_Sep_2026.xlsx
UPDATE mitra_kinerja SET status_scorecard = 'SIAP DIVALIDASI', posisi_portofolio = 'IMPLEMENTASI SANGAT TERBATAS / TATA KELOLA BELUM KUAT', rekomendasi = 'SEGERA BENTUK TATA KELOLA OPERASIONAL DAN AKTIFKAN IMPLEMENTASI

Temuan Utama:
Dibanding baseline, belum terdapat tambahan evidence substantif. Jejak implementasi yang dapat ditelusuri masih terbatas pada sertifikat peserta magang, sementara PIC, RTL, outcome, dan kontribusi belum terbukti.

Rencana Tindak Lanjut:
1) Tetapkan PIC; 2) verifikasi kegiatan magang/kegiatan lain; 3) susun rencana aksi tahunan; 4) aktifkan ruang lingkup prioritas; 5) konsolidasikan evidence; 6) ukur outcome; 7) review triwulanan.' WHERE id = 6;
UPDATE indikator_skor SET 
    bobot = 10,
    kondisi_baseline = 'Unit pengampu, PIC internal, PIC mitra, dan rencana tindak lanjut: BELUM TERSEDIA pada final baseline.',
    kondisi_saat_ini = 'Tidak ada evidence baru yang menunjukkan penetapan PIC, pembagian peran, mekanisme koordinasi, atau rencana tindak lanjut operasional. Form evidence P06 masih kosong.',
    status_pemeriksaan = 'DAPAT DINILAI',
    temuan_bukti = 'Final Baseline P06; Eviden_P06_UMRAH.xlsx belum terisi substantif.',
    skor = 0,
    nilai = 0.00,
    alasan_skor = 'Setelah lebih dari 18 bulan, penanggung jawab dan tindak lanjut masih belum dapat ditentukan secara memadai. Rubrik I1 skor 0.',
    catatan_tindak_lanjut = 'Tetapkan PIC internal/mitra dan susun rencana aksi seluruh ruang lingkup dengan target waktu.',
    referensi_baseline = 'Unit pengampu, PIC internal, PIC mitra, dan rencana tindak lanjut: BELUM TERSEDIA pada final baseline.'
WHERE mitra_id = 6 AND kode_indikator = 'I1';
UPDATE indikator_skor SET 
    bobot = 15,
    kondisi_baseline = 'Pelaksanaan dan hasil: BELUM TERVERIFIKASI; baseline mencatat indikasi kegiatan bersama melalui sertifikat peserta magang.',
    kondisi_saat_ini = 'Belum ada evidence baru. Satu-satunya jejak implementasi yang dapat ditelusuri tetap berupa sertifikat peserta magang; keterkaitan kegiatan lain dengan MoU belum terverifikasi.',
    status_pemeriksaan = 'DAPAT DINILAI',
    temuan_bukti = 'Final Baseline P06 — sertifikat peserta magang; Eviden_P06_UMRAH.xlsx kosong.',
    skor = 1,
    nilai = 3.75,
    alasan_skor = 'Terdapat tindak lanjut sangat terbatas/indikatif, tetapi belum menunjukkan implementasi utama yang konsisten. Rubrik I2 skor 1.',
    catatan_tindak_lanjut = 'Verifikasi kegiatan magang dan himpun bukti kegiatan lain; aktifkan ruang lingkup yang belum berjalan.',
    referensi_baseline = 'Pelaksanaan dan hasil: BELUM TERVERIFIKASI; baseline mencatat indikasi kegiatan bersama melalui sertifikat peserta magang.'
WHERE mitra_id = 6 AND kode_indikator = 'I2';
UPDATE indikator_skor SET 
    bobot = 15,
    kondisi_baseline = 'Output implementasi belum terverifikasi; bukti yang tersedia hanya sertifikat peserta magang.',
    kondisi_saat_ini = 'Output yang dapat ditelusuri masih sangat terbatas pada bukti sertifikat peserta magang; belum ada paket output lain yang dapat diverifikasi.',
    status_pemeriksaan = 'DAPAT DINILAI',
    temuan_bukti = 'Sertifikat peserta magang pada baseline; belum ada evidence baru.',
    skor = 1,
    nilai = 3.75,
    alasan_skor = 'Output sangat terbatas. Rubrik I3 skor 1.',
    catatan_tindak_lanjut = 'Tetapkan output minimum per ruang lingkup dan dokumentasikan hasil kegiatan.',
    referensi_baseline = 'Output implementasi belum terverifikasi; bukti yang tersedia hanya sertifikat peserta magang.'
WHERE mitra_id = 6 AND kode_indikator = 'I3';
UPDATE indikator_skor SET 
    bobot = 20,
    kondisi_baseline = 'Final baseline belum memiliki data outcome/manfaat terukur.',
    kondisi_saat_ini = 'Setelah lebih dari 18 bulan, belum ditemukan bukti manfaat/perubahan yang dapat ditelusuri secara memadai dari implementasi kerja sama.',
    status_pemeriksaan = 'DAPAT DINILAI',
    temuan_bukti = 'Final Baseline P06; tidak ada data outcome baru pada form evidence.',
    skor = 0,
    nilai = 0.00,
    alasan_skor = 'Sudah cukup waktu untuk menilai, tetapi manfaat/perubahan belum dapat dibuktikan. Rubrik I4 skor 0.',
    catatan_tindak_lanjut = 'Mulai ukur manfaat kegiatan: peserta, kompetensi, layanan hukum, hasil Tri Dharma, dan pemanfaatan KI.',
    referensi_baseline = 'Final baseline belum memiliki data outcome/manfaat terukur.'
WHERE mitra_id = 6 AND kode_indikator = 'I4';
UPDATE indikator_skor SET 
    bobot = 20,
    kondisi_baseline = 'Final baseline belum memiliki data kontribusi/dampak terhadap kinerja atau pelayanan.',
    kondisi_saat_ini = 'Belum ada evidence yang menghubungkan implementasi kerja sama dengan sasaran kinerja organisasi atau pelayanan hukum.',
    status_pemeriksaan = 'DAPAT DINILAI',
    temuan_bukti = 'Final Baseline P06; tidak ada evidence kontribusi baru.',
    skor = 0,
    nilai = 0.00,
    alasan_skor = 'Sudah layak dinilai tetapi kontribusi belum dapat dibuktikan. Rubrik I5 skor 0.',
    catatan_tindak_lanjut = 'Hubungkan output/outcome dengan indikator layanan hukum, Tri Dharma, kompetensi, KI, dan JDIH.',
    referensi_baseline = 'Final baseline belum memiliki data kontribusi/dampak terhadap kinerja atau pelayanan.'
WHERE mitra_id = 6 AND kode_indikator = 'I5';
UPDATE indikator_skor SET 
    bobot = 10,
    kondisi_baseline = 'P2MA terverifikasi, tetapi arsip resmi dan evidence implementasi belum tersedia; sertifikat magang menjadi satu bukti terbatas.',
    kondisi_saat_ini = 'Evidence implementasi tetap sangat terbatas dan belum terkonsolidasi; form evidence baru belum diisi.',
    status_pemeriksaan = 'DAPAT DINILAI',
    temuan_bukti = 'P2MA/naskah; sertifikat peserta magang; Eviden_P06_UMRAH.xlsx kosong.',
    skor = 1,
    nilai = 2.50,
    alasan_skor = 'Evidence sangat terbatas, tersebar, dan belum cukup untuk keseluruhan portofolio. Rubrik I6 skor 1.',
    catatan_tindak_lanjut = 'Bangun repository P06, lengkapi arsip resmi, dan indeks evidence per ruang lingkup.',
    referensi_baseline = 'P2MA terverifikasi, tetapi arsip resmi dan evidence implementasi belum tersedia; sertifikat magang menjadi satu bukti terbatas.'
WHERE mitra_id = 6 AND kode_indikator = 'I6';
UPDATE indikator_skor SET 
    bobot = 10,
    kondisi_baseline = 'Gap mencakup arsip resmi, unit pengampu, PIC, RTL, keterkaitan kegiatan dengan MoU, dan evidence implementasi.',
    kondisi_saat_ini = 'Risiko/gap tersebut masih relevan dan belum tampak penanganan yang terstruktur.',
    status_pemeriksaan = 'DAPAT DINILAI',
    temuan_bukti = 'Final Baseline P06; form evidence baru belum menambah data mitigasi.',
    skor = 1,
    nilai = 2.50,
    alasan_skor = 'Risiko diketahui tetapi penanganannya lemah. Rubrik I7 skor 1.',
    catatan_tindak_lanjut = 'Tetapkan owner risiko, target perbaikan, dan review triwulanan.',
    referensi_baseline = 'Gap mencakup arsip resmi, unit pengampu, PIC, RTL, keterkaitan kegiatan dengan MoU, dan evidence implementasi.'
WHERE mitra_id = 6 AND kode_indikator = 'I7';

-- Partner MID 7: Scorecard_P07_STAI_ANAMBAS_27_Sep_2026.xlsx
UPDATE mitra_kinerja SET status_scorecard = 'SIAP DIVALIDASI', posisi_portofolio = 'BELUM TERIMPLEMENTASI / TATA KELOLA OPERASIONAL BELUM TERBENTUK', rekomendasi = 'AKTIFKAN KERJA SAMA SEGERA DAN BENTUK TATA KELOLA OPERASIONAL

Temuan Utama:
Belum ada perkembangan substantif dari baseline: belum terdapat PIC operasional, RTL, kegiatan, output, outcome, maupun evidence implementasi.

Rencana Tindak Lanjut:
1) Tetapkan PIC internal/mitra; 2) pilih 1–2 ruang lingkup prioritas; 3) susun RTL dengan target 30–60 hari; 4) laksanakan kegiatan pertama; 5) bangun repository evidence; 6) review bulanan.' WHERE id = 7;
UPDATE indikator_skor SET 
    bobot = 10,
    kondisi_baseline = 'Unit pengampu, PIC internal, PIC mitra, dan RTL: BELUM TERSEDIA pada final baseline.',
    kondisi_saat_ini = 'Belum ada evidence baru tentang PIC, pembagian peran, mekanisme koordinasi, atau rencana tindak lanjut. Form evidence P07 masih kosong.',
    status_pemeriksaan = 'DAPAT DINILAI',
    temuan_bukti = 'Final Baseline P07; Eviden_P07_STAI_ANAMBAS.xlsx belum terisi substantif.',
    skor = 0,
    nilai = 0.00,
    alasan_skor = 'Setelah lebih dari 10 bulan, penanggung jawab dan tindak lanjut belum dapat ditentukan. Rubrik I1 skor 0.',
    catatan_tindak_lanjut = 'Tetapkan PIC internal/mitra dan susun rencana aksi seluruh ruang lingkup.',
    referensi_baseline = 'Unit pengampu, PIC internal, PIC mitra, dan RTL: BELUM TERSEDIA pada final baseline.'
WHERE mitra_id = 7 AND kode_indikator = 'I1';
UPDATE indikator_skor SET 
    bobot = 15,
    kondisi_baseline = 'Final baseline menyatakan belum pernah dilakukan kegiatan bersama institusi terkait.',
    kondisi_saat_ini = 'Belum ada evidence baru yang menunjukkan kegiatan sudah atau sedang dilaksanakan.',
    status_pemeriksaan = 'DAPAT DINILAI',
    temuan_bukti = 'Final Baseline P07; form evidence P07 kosong.',
    skor = 0,
    nilai = 0.00,
    alasan_skor = 'Sudah cukup waktu dan diverifikasi belum terdapat tindak lanjut. Rubrik I2 skor 0.',
    catatan_tindak_lanjut = 'Mulai minimal satu kegiatan prioritas dan buat kalender implementasi.',
    referensi_baseline = 'Final baseline menyatakan belum pernah dilakukan kegiatan bersama institusi terkait.'
WHERE mitra_id = 7 AND kode_indikator = 'I2';
UPDATE indikator_skor SET 
    bobot = 15,
    kondisi_baseline = 'Belum pernah dilakukan kegiatan bersama; output belum tersedia.',
    kondisi_saat_ini = 'Tidak ada output implementasi baru yang dapat dibuktikan.',
    status_pemeriksaan = 'DAPAT DINILAI',
    temuan_bukti = 'Final Baseline P07; tidak ada evidence output baru.',
    skor = 0,
    nilai = 0.00,
    alasan_skor = 'Output seharusnya sudah mulai ada setelah >6 bulan tetapi belum dihasilkan. Rubrik I3 skor 0.',
    catatan_tindak_lanjut = 'Tetapkan output konkret per kegiatan dan dokumentasikan hasil.',
    referensi_baseline = 'Belum pernah dilakukan kegiatan bersama; output belum tersedia.'
WHERE mitra_id = 7 AND kode_indikator = 'I3';
UPDATE indikator_skor SET 
    bobot = 20,
    kondisi_baseline = 'Belum ada data outcome/manfaat.',
    kondisi_saat_ini = 'Setelah lebih dari 10 bulan, belum ditemukan manfaat/perubahan karena kegiatan bersama belum berjalan.',
    status_pemeriksaan = 'DAPAT DINILAI',
    temuan_bukti = 'Final Baseline P07; form evidence kosong.',
    skor = 0,
    nilai = 0.00,
    alasan_skor = 'Sudah cukup waktu untuk menilai namun tidak ditemukan manfaat/perubahan. Rubrik I4 skor 0.',
    catatan_tindak_lanjut = 'Mulai implementasi dan tetapkan indikator manfaat sejak awal.',
    referensi_baseline = 'Belum ada data outcome/manfaat.'
WHERE mitra_id = 7 AND kode_indikator = 'I4';
UPDATE indikator_skor SET 
    bobot = 20,
    kondisi_baseline = 'Belum ada data kontribusi/dampak.',
    kondisi_saat_ini = 'Belum ada kontribusi yang dapat dibuktikan terhadap kinerja atau pelayanan hukum karena belum ada implementasi.',
    status_pemeriksaan = 'DAPAT DINILAI',
    temuan_bukti = 'Final Baseline P07; tidak ada evidence kontribusi baru.',
    skor = 0,
    nilai = 0.00,
    alasan_skor = 'Sudah layak dinilai tetapi kontribusi belum dapat dibuktikan. Rubrik I5 skor 0.',
    catatan_tindak_lanjut = 'Hubungkan output/outcome kegiatan dengan sasaran Tri Dharma dan pelayanan hukum.',
    referensi_baseline = 'Belum ada data kontribusi/dampak.'
WHERE mitra_id = 7 AND kode_indikator = 'I5';
UPDATE indikator_skor SET 
    bobot = 10,
    kondisi_baseline = 'Arsip dan P2MA terverifikasi; evidence implementasi belum tersedia.',
    kondisi_saat_ini = 'Naskah/arsip dapat ditelusuri, tetapi tidak ada evidence implementasi baru karena kegiatan belum berjalan.',
    status_pemeriksaan = 'DAPAT DINILAI',
    temuan_bukti = 'Final Baseline P07; arsip/P2MA; form evidence P07 kosong.',
    skor = 1,
    nilai = 2.50,
    alasan_skor = 'Evidence administratif dasar tersedia, tetapi evidence implementasi sangat terbatas/tidak ada. Rubrik I6 skor 1.',
    catatan_tindak_lanjut = 'Siapkan repository P07 dan dokumentasikan bukti sejak kegiatan pertama.',
    referensi_baseline = 'Arsip dan P2MA terverifikasi; evidence implementasi belum tersedia.'
WHERE mitra_id = 7 AND kode_indikator = 'I6';
UPDATE indikator_skor SET 
    bobot = 10,
    kondisi_baseline = 'Belum pernah dilakukan kegiatan bersama; hambatan/gap belum dikelola secara operasional.',
    kondisi_saat_ini = 'Risiko utama adalah tidak adanya PIC, RTL, dan implementasi setelah lebih dari 10 bulan; belum ada evidence mitigasi.',
    status_pemeriksaan = 'DAPAT DINILAI',
    temuan_bukti = 'Final Baseline P07; form evidence kosong.',
    skor = 0,
    nilai = 0.00,
    alasan_skor = 'Risiko material belum dikendalikan dan keberlanjutan implementasi terancam. Rubrik I7 skor 0.',
    catatan_tindak_lanjut = 'Tetapkan PIC, owner risiko, rencana aksi, dan review bulanan sampai kegiatan mulai berjalan.',
    referensi_baseline = 'Belum pernah dilakukan kegiatan bersama; hambatan/gap belum dikelola secara operasional.'
WHERE mitra_id = 7 AND kode_indikator = 'I7';

-- Partner MID 8: Scorecard_P08_POLIBATAM_27_Sep_2026.xlsx
UPDATE mitra_kinerja SET status_scorecard = 'SIAP DIVALIDASI', posisi_portofolio = 'IMPLEMENTASI TERBATAS / RENCANA TINDAK LANJUT KUAT', rekomendasi = 'PERCEPAT IMPLEMENTASI DAN TUNTASKAN TINDAK LANJUT PRIORITAS KI

Temuan Utama:
Dibanding baseline, kini telah tersedia PIC mitra, satu kegiatan dengan bukti, dan empat usulan tindak lanjut yang sangat spesifik pada isu KI. Namun implementasi aktual masih terbatas dibanding luasnya ruang lingkup MoU.

Rencana Tindak Lanjut:
1) Tetapkan PIC internal; 2) jadwalkan empat tindak lanjut KI; 3) pendampingan dokumen/pemeriksaan awal KI; 4) koordinasi isu tarif/prosedur dengan DJKI; 5) fasilitasi lisensi dan percepatan pemeriksaan paten; 6) mulai aktivasi ruang lingkup Tri Dharma lainnya; 7) ukur hasil dan konsolidasikan evidence.' WHERE id = 8;
UPDATE indikator_skor SET 
    bobot = 10,
    kondisi_baseline = 'Unit pengampu, PIC internal, PIC mitra, dan rencana tindak lanjut: BELUM TERSEDIA pada final baseline.',
    kondisi_saat_ini = 'PIC mitra utama/cadangan sudah jelas. Terdapat 4 usulan tindak lanjut yang rinci, masing-masing memuat target waktu, hasil yang diharapkan, bantuan yang dibutuhkan, dan pihak yang dilibatkan. PIC internal belum terdokumentasi.',
    status_pemeriksaan = 'DAPAT DINILAI',
    temuan_bukti = 'polibatam.xlsx — IDENTITAS & PIC; USULAN TINDAK LANJUT.',
    skor = 2,
    nilai = 5.00,
    alasan_skor = 'Penanggung jawab mitra dan sebagian tindak lanjut sudah jelas, tetapi koordinasi internal dan pembagian peran belum konsisten. Rubrik I1 skor 2.',
    catatan_tindak_lanjut = 'Tetapkan PIC internal/focal point Kanwil dan satukan empat usulan menjadi rencana aksi bersama dengan mekanisme koordinasi.',
    referensi_baseline = 'Unit pengampu, PIC internal, PIC mitra, dan rencana tindak lanjut: BELUM TERSEDIA pada final baseline.'
WHERE mitra_id = 8 AND kode_indikator = 'I1';
UPDATE indikator_skor SET 
    bobot = 15,
    kondisi_baseline = 'Pelaksanaan dan hasil: BELUM TERSEDIA pada final baseline.',
    kondisi_saat_ini = 'Satu kegiatan telah dilaksanakan: Sosialisasi Perlindungan Hak Cipta di Era AI pada 7 Mei 2026, dengan bukti publikasi. Dibanding cakupan MoU yang luas, implementasi masih sangat terbatas dan berfokus pada KI.',
    status_pemeriksaan = 'DAPAT DINILAI',
    temuan_bukti = 'polibatam.xlsx — PELAKSANAAN KEGIATAN; tautan publikasi Polibatam.',
    skor = 1,
    nilai = 3.75,
    alasan_skor = 'Ada tindak lanjut nyata tetapi masih sangat terbatas/insidental dibanding ruang lingkup kerja sama. Rubrik I2 skor 1.',
    catatan_tindak_lanjut = 'Realisasikan tindak lanjut KI yang sudah direncanakan dan mulai aktivasi ruang lingkup Tri Dharma/kompetensi lainnya.',
    referensi_baseline = 'Pelaksanaan dan hasil: BELUM TERSEDIA pada final baseline.'
WHERE mitra_id = 8 AND kode_indikator = 'I2';
UPDATE indikator_skor SET 
    bobot = 15,
    kondisi_baseline = 'Pelaksanaan dan eviden implementasi: BELUM TERSEDIA pada final baseline.',
    kondisi_saat_ini = 'Output yang dapat dibuktikan saat ini terutama berupa pelaksanaan satu kegiatan sosialisasi hak cipta; output ruang lingkup lain belum terlihat.',
    status_pemeriksaan = 'DAPAT DINILAI',
    temuan_bukti = 'polibatam.xlsx — kegiatan sosialisasi 7 Mei 2026 dan bukti publikasi.',
    skor = 1,
    nilai = 3.75,
    alasan_skor = 'Output sangat terbatas dan belum mewakili keluaran utama kerja sama. Rubrik I3 skor 1.',
    catatan_tindak_lanjut = 'Tetapkan output per ruang lingkup, termasuk pendampingan KI, lisensi, percepatan paten, pendidikan, penelitian, dan pengabdian.',
    referensi_baseline = 'Pelaksanaan dan eviden implementasi: BELUM TERSEDIA pada final baseline.'
WHERE mitra_id = 8 AND kode_indikator = 'I3';
UPDATE indikator_skor SET 
    bobot = 20,
    kondisi_baseline = 'Final baseline belum memiliki data outcome/manfaat terukur.',
    kondisi_saat_ini = 'Manfaat awal terkait peningkatan pemahaman perlindungan hak cipta dapat diasosiasikan dengan kegiatan sosialisasi, namun belum tersedia data peserta, perubahan pemahaman, atau pemanfaatan layanan setelah kegiatan.',
    status_pemeriksaan = 'DAPAT DINILAI',
    temuan_bukti = 'polibatam.xlsx — hasil/manfaat singkat dan tautan publikasi kegiatan.',
    skor = 1,
    nilai = 5.00,
    alasan_skor = 'Manfaat awal sangat terbatas dan belum terukur. Rubrik I4 skor 1.',
    catatan_tindak_lanjut = 'Ukur peserta, perubahan pemahaman, konsultasi lanjutan, pengajuan KI, dan tindak lanjut pascakegiatan.',
    referensi_baseline = 'Final baseline belum memiliki data outcome/manfaat terukur.'
WHERE mitra_id = 8 AND kode_indikator = 'I4';
UPDATE indikator_skor SET 
    bobot = 20,
    kondisi_baseline = 'Final baseline belum memiliki data kontribusi/dampak terhadap kinerja atau pelayanan.',
    kondisi_saat_ini = 'Hubungan dengan pelayanan KI mulai terlihat melalui edukasi hak cipta dan rencana pendampingan KI, namun kontribusi terhadap sasaran kinerja kampus/Kanwil belum dapat dibuktikan secara kuat.',
    status_pemeriksaan = 'DAPAT DINILAI',
    temuan_bukti = 'polibatam.xlsx — kegiatan sosialisasi dan 4 usulan tindak lanjut KI.',
    skor = 1,
    nilai = 5.00,
    alasan_skor = 'Hubungan dengan kinerja/pelayanan masih sangat terbatas dan belum terukur. Rubrik I5 skor 1.',
    catatan_tindak_lanjut = 'Hubungkan outcome dengan jumlah KI yang diajukan/granted, lisensi, komersialisasi, dan indikator layanan KI.',
    referensi_baseline = 'Final baseline belum memiliki data kontribusi/dampak terhadap kinerja atau pelayanan.'
WHERE mitra_id = 8 AND kode_indikator = 'I5';
UPDATE indikator_skor SET 
    bobot = 10,
    kondisi_baseline = 'Arsip dan P2MA: TERVERIFIKASI; eviden implementasi: BELUM TERSEDIA.',
    kondisi_saat_ini = 'Evidence utama tersedia untuk satu kegiatan melalui tautan publikasi; identitas/PIC dan rencana tindak lanjut juga terdokumentasi. Namun evidence implementasi keseluruhan portofolio masih belum lengkap.',
    status_pemeriksaan = 'DAPAT DINILAI',
    temuan_bukti = 'Final baseline; polibatam.xlsx; tautan publikasi kegiatan.',
    skor = 2,
    nilai = 5.00,
    alasan_skor = 'Evidence utama dapat ditelusuri, tetapi belum konsisten dalam kelengkapan untuk seluruh ruang lingkup. Rubrik I6 skor 2.',
    catatan_tindak_lanjut = 'Bangun repository P08 dan indeks bukti untuk setiap kegiatan, pengajuan KI, hasil pemeriksaan, lisensi, dan output lainnya.',
    referensi_baseline = 'Arsip dan P2MA: TERVERIFIKASI; eviden implementasi: BELUM TERSEDIA.'
WHERE mitra_id = 8 AND kode_indikator = 'I6';
UPDATE indikator_skor SET 
    bobot = 10,
    kondisi_baseline = 'Masa berlaku: TERVERIFIKASI; hambatan/gap operasional: BELUM TERSEDIA.',
    kondisi_saat_ini = 'Risiko sudah teridentifikasi secara konkret: penolakan pengajuan KI, biaya perubahan data inventor/klaim, kebutuhan lisensi atas KI granted, dan keterlambatan pemeriksaan substantif paten. Empat usulan tindak lanjut diarahkan untuk menangani masalah tersebut.',
    status_pemeriksaan = 'DAPAT DINILAI',
    temuan_bukti = 'polibatam.xlsx — pertanyaan/kebutuhan awal dan 4 usulan tindak lanjut.',
    skor = 2,
    nilai = 5.00,
    alasan_skor = 'Risiko utama mulai ditangani melalui rencana tindak lanjut, tetapi realisasi mitigasi belum terbukti. Rubrik I7 skor 2.',
    catatan_tindak_lanjut = 'Tetapkan owner dan progres mitigasi untuk tiap isu KI, termasuk eskalasi ke DJKI bila diperlukan.',
    referensi_baseline = 'Masa berlaku: TERVERIFIKASI; hambatan/gap operasional: BELUM TERSEDIA.'
WHERE mitra_id = 8 AND kode_indikator = 'I7';

-- Partner MID 9: Scorecard_P09_STAI_NATUNA_27_Sep_2026.xlsx
UPDATE mitra_kinerja SET status_scorecard = 'MASA IMPLEMENTASI AWAL', posisi_portofolio = 'RENCANA IMPLEMENTASI TERSTRUKTUR / BELUM MULAI', rekomendasi = 'MULAI IMPLEMENTASI SESUAI ROADMAP DAN BANGUN EVIDENCE SEJAK AWAL

Temuan Utama:
Dibanding baseline, PIC mitra dan tujuh rencana tindak lanjut sudah tersedia dengan cukup rinci. Namun belum ada kegiatan aktual atau output, sehingga scorecard masih berada pada Masa Implementasi Awal.

Rencana Tindak Lanjut:
1) Tetapkan PIC internal; 2) mulai pembentukan/penguatan Sentra KI; 3) laksanakan sosialisasi KI; 4) inventarisasi potensi KI; 5) buka klinik pendaftaran KI; 6) mulai program Hak Cipta/Merek prioritas; 7) siapkan SOP integrasi KI dengan penelitian/PkM; 8) dokumentasikan evidence dan review bulanan.' WHERE id = 9;
UPDATE indikator_skor SET 
    bobot = 10,
    kondisi_baseline = 'Unit pengampu, PIC internal, PIC mitra, dan rencana tindak lanjut: BELUM TERSEDIA pada final baseline.',
    kondisi_saat_ini = 'PIC mitra utama/cadangan sudah jelas. Terdapat 7 usulan tindak lanjut yang rinci, lengkap dengan target waktu, hasil yang diharapkan, kebutuhan bantuan, dan pihak yang dilibatkan. PIC internal belum terdokumentasi.',
    status_pemeriksaan = 'DAPAT DINILAI',
    temuan_bukti = 'stai natuna.xlsx — IDENTITAS & PIC; USULAN TINDAK LANJUT.',
    skor = 2,
    nilai = 5.00,
    alasan_skor = 'Penanggung jawab mitra dan rencana tindak lanjut sudah cukup jelas, tetapi koordinasi internal dan kesiapan pelaksanaan belum teruji. Rubrik I1 skor 2.',
    catatan_tindak_lanjut = 'Tetapkan PIC internal/focal point dan susun kalender implementasi bersama untuk tujuh usulan prioritas.',
    referensi_baseline = 'Unit pengampu, PIC internal, PIC mitra, dan rencana tindak lanjut: BELUM TERSEDIA pada final baseline.'
WHERE mitra_id = 9 AND kode_indikator = 'I1';
UPDATE indikator_skor SET 
    bobot = 15,
    kondisi_baseline = 'Pelaksanaan dan hasil: BELUM TERSEDIA pada final baseline.',
    kondisi_saat_ini = 'Belum ada kegiatan yang tercatat sudah/sedang dilaksanakan. Namun tujuh rencana tindak lanjut baru dijadwalkan mulai September–Oktober 2026 hingga 2027, sehingga pada 27 September 2026 sebagian besar target belum jatuh tempo.',
    status_pemeriksaan = 'BELUM DAPAT DINILAI',
    temuan_bukti = 'stai natuna.xlsx — RINGKASAN menunjukkan Total Kegiatan 0 dan 7 usulan tindak lanjut.',
    skor = NULL,
    nilai = NULL,
    alasan_skor = 'Implementasi belum diberi skor karena masa pelaksanaan masih awal dan sebagian besar rencana baru mulai pada/ setelah periode penilaian.',
    catatan_tindak_lanjut = 'Pantau realisasi kegiatan pertama mulai Oktober 2026 dan nilai I2 setelah jadwal tindak lanjut mulai berjalan.',
    referensi_baseline = 'Pelaksanaan dan hasil: BELUM TERSEDIA pada final baseline.'
WHERE mitra_id = 9 AND kode_indikator = 'I2';
UPDATE indikator_skor SET 
    bobot = 15,
    kondisi_baseline = 'Pelaksanaan dan eviden implementasi: BELUM TERSEDIA pada final baseline.',
    kondisi_saat_ini = 'Belum terdapat kegiatan aktual sehingga output pelaksanaan juga belum terbentuk pada periode penilaian.',
    status_pemeriksaan = 'BELUM DAPAT DINILAI',
    temuan_bukti = 'stai natuna.xlsx — PELAKSANAAN KEGIATAN kosong; 7 usulan masih berupa rencana.',
    skor = NULL,
    nilai = NULL,
    alasan_skor = 'Output belum dinilai karena kegiatan yang direncanakan belum memasuki fase realisasi.',
    catatan_tindak_lanjut = 'Tetapkan output minimum per tindak lanjut: struktur Sentra KI, database potensi KI, kegiatan sosialisasi, klinik KI, dan sertifikat/pendaftaran.',
    referensi_baseline = 'Pelaksanaan dan eviden implementasi: BELUM TERSEDIA pada final baseline.'
WHERE mitra_id = 9 AND kode_indikator = 'I3';
UPDATE indikator_skor SET 
    bobot = 20,
    kondisi_baseline = 'Final baseline belum memiliki data outcome/manfaat terukur.',
    kondisi_saat_ini = 'Outcome belum dapat diamati karena belum ada kegiatan aktual pada periode penilaian.',
    status_pemeriksaan = 'BELUM DAPAT DINILAI',
    temuan_bukti = 'stai natuna.xlsx — belum ada pelaksanaan; outcome masih berupa manfaat yang diharapkan pada usulan.',
    skor = NULL,
    nilai = NULL,
    alasan_skor = 'Outcome belum memasuki periode penilaian yang memadai.',
    catatan_tindak_lanjut = 'Mulai kumpulkan indikator outcome setelah kegiatan pertama: peserta, database KI, pendaftaran, dan pemanfaatan Sentra KI.',
    referensi_baseline = 'Final baseline belum memiliki data outcome/manfaat terukur.'
WHERE mitra_id = 9 AND kode_indikator = 'I4';
UPDATE indikator_skor SET 
    bobot = 20,
    kondisi_baseline = 'Final baseline belum memiliki data kontribusi/dampak terhadap kinerja atau pelayanan.',
    kondisi_saat_ini = 'Arah kontribusi sudah dirancang pada penguatan Sentra KI, pendaftaran KI, integrasi penelitian/PkM, dan potensi KI lokal Natuna, tetapi belum terealisasi.',
    status_pemeriksaan = 'BELUM DAPAT DINILAI',
    temuan_bukti = 'stai natuna.xlsx — 7 usulan tindak lanjut dan manfaat yang diharapkan.',
    skor = NULL,
    nilai = NULL,
    alasan_skor = 'Kontribusi/dampak belum dinilai karena belum ada output/outcome aktual.',
    catatan_tindak_lanjut = 'Nilai I5 setelah outcome mulai terbentuk dan dapat dihubungkan dengan pelayanan hukum/target perguruan tinggi.',
    referensi_baseline = 'Final baseline belum memiliki data kontribusi/dampak terhadap kinerja atau pelayanan.'
WHERE mitra_id = 9 AND kode_indikator = 'I5';
UPDATE indikator_skor SET 
    bobot = 10,
    kondisi_baseline = 'Arsip dan P2MA: TERVERIFIKASI; eviden implementasi: BELUM TERSEDIA.',
    kondisi_saat_ini = 'Data identitas, PIC, dan tujuh rencana tindak lanjut tersedia cukup rinci. Evidence implementasi belum ada karena kegiatan aktual belum dimulai.',
    status_pemeriksaan = 'DAPAT DINILAI',
    temuan_bukti = 'Final baseline; stai natuna.xlsx — IDENTITAS & PIC, USULAN TINDAK LANJUT, RINGKASAN.',
    skor = 2,
    nilai = 5.00,
    alasan_skor = 'Evidence tata kelola/rencana tersedia dan dapat ditelusuri, tetapi evidence implementasi belum terbentuk. Rubrik I6 skor 2.',
    catatan_tindak_lanjut = 'Siapkan repository P09 sejak kegiatan pertama agar seluruh bukti implementasi dan output terdokumentasi sejak awal.',
    referensi_baseline = 'Arsip dan P2MA: TERVERIFIKASI; eviden implementasi: BELUM TERSEDIA.'
WHERE mitra_id = 9 AND kode_indikator = 'I6';
UPDATE indikator_skor SET 
    bobot = 10,
    kondisi_baseline = 'Masa berlaku: TERVERIFIKASI; hambatan/gap operasional: BELUM TERSEDIA.',
    kondisi_saat_ini = 'Risiko utama adalah belum dimulainya implementasi, ketergantungan pada pendampingan Kanwil/DJKI, dan kebutuhan pembentukan Sentra KI. Tujuh usulan tindak lanjut sudah memberi arah keberlanjutan, tetapi belum diuji dalam pelaksanaan.',
    status_pemeriksaan = 'DAPAT DINILAI',
    temuan_bukti = 'stai natuna.xlsx — 7 usulan tindak lanjut, kebutuhan bantuan dan pihak yang dilibatkan.',
    skor = 2,
    nilai = 5.00,
    alasan_skor = 'Risiko dan kebutuhan utama sudah mulai ditangani melalui rencana tindak lanjut, tetapi realisasi belum terbukti. Rubrik I7 skor 2.',
    catatan_tindak_lanjut = 'Tetapkan owner, target bulanan, dan mekanisme review untuk tujuh tindak lanjut selama masa implementasi awal.',
    referensi_baseline = 'Masa berlaku: TERVERIFIKASI; hambatan/gap operasional: BELUM TERSEDIA.'
WHERE mitra_id = 9 AND kode_indikator = 'I7';

-- Partner MID 10: Scorecard_P10_STISIP_BATAM_27_Sep_2026.xlsx
UPDATE mitra_kinerja SET status_scorecard = 'MASA IMPLEMENTASI AWAL', posisi_portofolio = 'MASA IMPLEMENTASI AWAL / TATA KELOLA BELUM TERBENTUK', rekomendasi = 'BENTUK TATA KELOLA DAN MULAI IMPLEMENTASI AWAL

Temuan Utama:
Belum ada perkembangan substantif dari baseline. Tata kelola operasional dan evidence implementasi belum terbentuk, tetapi usia kerja sama masih di bawah 6 bulan.

Rencana Tindak Lanjut:
1) Tetapkan PIC internal/mitra; 2) susun RTL 3–6 bulan; 3) pilih kegiatan prioritas; 4) laksanakan kegiatan pertama; 5) siapkan repository evidence; 6) review setelah melewati 6 bulan.' WHERE id = 10;
UPDATE indikator_skor SET 
    bobot = 10,
    kondisi_baseline = 'Unit pengampu BELUM TERVERIFIKASI; PIC internal/mitra dan RTL BELUM TERSEDIA pada final baseline.',
    kondisi_saat_ini = 'Belum ada evidence baru tentang PIC, pembagian peran, mekanisme koordinasi, atau rencana tindak lanjut. Form evidence P10 masih kosong.',
    status_pemeriksaan = 'DAPAT DINILAI',
    temuan_bukti = 'Final Baseline P10; Eviden_P10_STISIP_BATAM.xlsx belum terisi substantif.',
    skor = 0,
    nilai = 0.00,
    alasan_skor = 'Pengelolaan dan tindak lanjut belum dapat ditentukan meskipun aspek ini seharusnya dapat dibentuk sejak awal. Rubrik I1 skor 0.',
    catatan_tindak_lanjut = 'Tetapkan PIC internal/mitra dan rencana implementasi sebelum memasuki usia 6 bulan.',
    referensi_baseline = 'Unit pengampu BELUM TERVERIFIKASI; PIC internal/mitra dan RTL BELUM TERSEDIA pada final baseline.'
WHERE mitra_id = 10 AND kode_indikator = 'I1';
UPDATE indikator_skor SET 
    bobot = 15,
    kondisi_baseline = 'Pelaksanaan dan hasil: BELUM TERSEDIA pada final baseline.',
    kondisi_saat_ini = 'Belum ada kegiatan aktual; usia kerja sama 142 hari dan belum melewati ambang 6 bulan yang digunakan untuk penilaian implementasi penuh.',
    status_pemeriksaan = 'BELUM DAPAT DINILAI',
    temuan_bukti = 'Final Baseline P10; form evidence P10 kosong.',
    skor = NULL,
    nilai = NULL,
    alasan_skor = 'Implementasi belum diberi skor karena masa implementasi masih awal dan belum melewati 6 bulan.',
    catatan_tindak_lanjut = 'Mulai kegiatan prioritas dan kumpulkan evidence sebelum review berikutnya.',
    referensi_baseline = 'Pelaksanaan dan hasil: BELUM TERSEDIA pada final baseline.'
WHERE mitra_id = 10 AND kode_indikator = 'I2';
UPDATE indikator_skor SET 
    bobot = 15,
    kondisi_baseline = 'Output implementasi belum tersedia.',
    kondisi_saat_ini = 'Belum ada kegiatan aktual sehingga output belum terbentuk.',
    status_pemeriksaan = 'BELUM DAPAT DINILAI',
    temuan_bukti = 'Final Baseline P10; form evidence kosong.',
    skor = NULL,
    nilai = NULL,
    alasan_skor = 'Output belum dinilai karena implementasi belum cukup matang.',
    catatan_tindak_lanjut = 'Tetapkan output minimal kegiatan pertama dan dokumentasikan hasil.',
    referensi_baseline = 'Output implementasi belum tersedia.'
WHERE mitra_id = 10 AND kode_indikator = 'I3';
UPDATE indikator_skor SET 
    bobot = 20,
    kondisi_baseline = 'Outcome/manfaat belum tersedia.',
    kondisi_saat_ini = 'Belum ada output aktual dan usia kerja sama masih <6 bulan.',
    status_pemeriksaan = 'BELUM DAPAT DINILAI',
    temuan_bukti = 'Final Baseline P10.',
    skor = NULL,
    nilai = NULL,
    alasan_skor = 'Outcome belum memasuki periode penilaian yang memadai.',
    catatan_tindak_lanjut = 'Siapkan indikator manfaat sejak kegiatan pertama.',
    referensi_baseline = 'Outcome/manfaat belum tersedia.'
WHERE mitra_id = 10 AND kode_indikator = 'I4';
UPDATE indikator_skor SET 
    bobot = 20,
    kondisi_baseline = 'Kontribusi/dampak belum tersedia.',
    kondisi_saat_ini = 'Belum ada output/outcome aktual dan usia kerja sama masih <6 bulan.',
    status_pemeriksaan = 'BELUM DAPAT DINILAI',
    temuan_bukti = 'Final Baseline P10.',
    skor = NULL,
    nilai = NULL,
    alasan_skor = 'Kontribusi/dampak belum memasuki periode penilaian yang memadai.',
    catatan_tindak_lanjut = 'Nilai kontribusi setelah output dan outcome mulai terbentuk.',
    referensi_baseline = 'Kontribusi/dampak belum tersedia.'
WHERE mitra_id = 10 AND kode_indikator = 'I5';
UPDATE indikator_skor SET 
    bobot = 10,
    kondisi_baseline = 'P2MA terverifikasi, tetapi arsip internal belum terverifikasi dan evidence implementasi belum tersedia.',
    kondisi_saat_ini = 'Naskah/P2MA dapat ditelusuri, tetapi belum ada evidence implementasi baru; form evidence P10 kosong.',
    status_pemeriksaan = 'DAPAT DINILAI',
    temuan_bukti = 'Final Baseline P10; P2MA; Eviden_P10_STISIP_BATAM.xlsx kosong.',
    skor = 1,
    nilai = 2.50,
    alasan_skor = 'Evidence administratif dasar ada, tetapi evidence implementasi sangat terbatas. Rubrik I6 skor 1.',
    catatan_tindak_lanjut = 'Verifikasi arsip internal dan siapkan repository P10 sejak kegiatan pertama.',
    referensi_baseline = 'P2MA terverifikasi, tetapi arsip internal belum terverifikasi dan evidence implementasi belum tersedia.'
WHERE mitra_id = 10 AND kode_indikator = 'I6';
UPDATE indikator_skor SET 
    bobot = 10,
    kondisi_baseline = 'Hambatan/gap BELUM TERVERIFIKASI; PIC, unit pengampu, RTL, dan evidence masih lemah.',
    kondisi_saat_ini = 'Risiko utama adalah belum terbentuknya tata kelola operasional sebelum usia 6 bulan; belum ada bukti mitigasi.',
    status_pemeriksaan = 'DAPAT DINILAI',
    temuan_bukti = 'Final Baseline P10; form evidence P10 kosong.',
    skor = 1,
    nilai = 2.50,
    alasan_skor = 'Risiko diketahui tetapi penanganannya masih lemah. Rubrik I7 skor 1.',
    catatan_tindak_lanjut = 'Tetapkan owner, PIC, RTL, dan jadwal kegiatan sebelum review berikutnya.',
    referensi_baseline = 'Hambatan/gap BELUM TERVERIFIKASI; PIC, unit pengampu, RTL, dan evidence masih lemah.'
WHERE mitra_id = 10 AND kode_indikator = 'I7';
