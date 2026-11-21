<?php
/**
 * -------------------------------------------------------------------------
 * Konfigurasi Database & Timezone - POS Resto Java Turtle
 * -------------------------------------------------------------------------
 * File ini digunakan untuk mengatur zona waktu aplikasi dan membuat koneksi
 * database ke MySQL menggunakan PDO. Di sini juga terdapat skrip auto-patch
 * untuk menambahkan kolom jika database belum mutakhir.
 */

// Mengatur zona waktu aplikasi ke Waktu Indonesia Barat (WIB)
date_default_timezone_set("Asia/Jakarta");

// Menonaktifkan pelaporan error langsung ke browser agar aman saat produksi
error_reporting(0);

// Parameter koneksi database server MySQL/MariaDB
$host   = '127.0.0.1'; // Alamat server database (localhost)
$user   = 'root';      // Username database (default XAMPP adalah root)
$pass   = '';          // Password database (default XAMPP adalah kosong)
$dbname = 'db_resto';  // Nama database yang digunakan aplikasi resto

try {
    // Membuat instansi PDO untuk koneksi database yang aman dengan encoding utf8mb4
    $config = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    
    // Skrip Patch Otomatis: Memeriksa apakah kolom 'no_invoice' sudah ada di tabel 'penjualan'
    $checkCol = $config->query("SHOW COLUMNS FROM `penjualan` LIKE 'no_invoice'");
    if ($checkCol && $checkCol->rowCount() == 0) {
        // Jika belum ada, otomatis tambahkan kolom 'no_invoice' setelah kolom 'id_penjualan'
        $config->exec("ALTER TABLE `penjualan` ADD COLUMN `no_invoice` VARCHAR(50) NOT NULL AFTER `id_penjualan`");
    }
} catch (PDOException $e) {
    // Jika koneksi gagal, tampilkan pesan kesalahan ke layar
    echo 'KONEKSI GAGAL: ' . $e->getMessage();
}

// Menyimpan letak file helper view data untuk mempermudah pemanggilan fungsi SELECT di halaman lain
$view = 'fungsi/view/view.php';
?>


