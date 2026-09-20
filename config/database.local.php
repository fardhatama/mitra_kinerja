<?php
/**
 * CONTOH konfigurasi lokal. Salin file ini menjadi "database.local.php" (tanpa
 * ".example") di folder yang sama, lalu isi kredensial sesuai server ini
 * (dev atau production). File "database.local.php" TIDAK ikut dipaketkan ulang
 * dan aman berbeda isi antara server dev dan production walau kode aplikasinya
 * sama persis.
 *
 * Kalau tidak dibuat sama sekali, aplikasi tetap jalan pakai default di
 * config/database.php (localhost/root/tanpa password) — cocok untuk dev cepat,
 * TAPI JANGAN dipakai apa adanya di production.
 */
define('DB_HOST', 'localhost');
define('DB_NAME', 'mitra_kinerja');   // contoh: cPanel biasa prefix nama DB dengan cpanel-username_
define('DB_USER', 'root');
define('DB_PASS', '');
