<?php
/**
 * -------------------------------------------------------------------------
 * Database Migrator - POS Resto Java Turtle
 * -------------------------------------------------------------------------
 * Skrip inisialisasi otomatis untuk database 'db_resto'. Skrip ini akan membuat
 * database, tabel-tabel utama (toko, member, login, kategori, menu_layanan,
 * penjualan_temp, penjualan), melakukan seeder data resto/menu awal, serta
 * memastikan role kasir dihapus sesuai revisi sistem terbaru.
 */

declare(strict_types=1);
@ob_start();
session_start();

$host = '127.0.0.1';
$user = 'root';
$pass = '';

try {
    // 0. Membuat database db_resto jika belum ada
    $temp_conn = new PDO("mysql:host=$host;", $user, $pass);
    $temp_conn->exec("CREATE DATABASE IF NOT EXISTS `db_resto` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
} catch (PDOException $e) {
    // Abaikan error di sini, koneksi detail akan ditangkap config.php
}

require 'config.php';

$output = [];
$error = null;

try {
    // 0. Memastikan database sudah dipilih
    $config->exec("CREATE DATABASE IF NOT EXISTS `db_resto` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $config->exec("USE `db_resto`");

    // 1. Membuat tabel 'toko' (Pengaturan Profil Restoran)
    $config->exec("CREATE TABLE IF NOT EXISTS `toko` (
      `id_toko` int(11) NOT NULL AUTO_INCREMENT,
      `nama_toko` varchar(255) NOT NULL,
      `alamat_toko` text NOT NULL,
      `tlp` varchar(255) NOT NULL,
      `nama_pemilik` varchar(255) NOT NULL,
      `footer_nota` text DEFAULT NULL,
      PRIMARY KEY (`id_toko`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    $config->exec("ALTER TABLE `toko` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

    // Seeder data profil toko/resto jika kosong
    $stmt = $config->prepare("SELECT COUNT(*) FROM `toko` WHERE `id_toko` = 1");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $config->exec("INSERT INTO `toko` (`id_toko`, `nama_toko`, `alamat_toko`, `tlp`, `nama_pemilik`, `footer_nota`) VALUES
        (1, 'Java Turtle Resto & Cafe', 'Pantai Sukamade, Sarongan, Banyuwangi', '081234567890', 'Elz & Team', 'Thank you for your visit! Hope you enjoy our food! 🐢🍔')");
    } else {
        $config->exec("UPDATE `toko` SET 
            `nama_toko` = 'Java Turtle Resto & Cafe', 
            `alamat_toko` = 'Pantai Sukamade, Sarongan, Banyuwangi', 
            `tlp` = '081234567890', 
            `nama_pemilik` = 'Elz & Team',
            `footer_nota` = 'Thank you for your visit! Hope you enjoy our food! 🐢🍔'
            WHERE `id_toko` = 1");
    }
    $output[] = ["label" => "Memperbarui Identitas Resto: Java Turtle Resto & Cafe", "status" => "success"];

    // 2. Membuat tabel 'member' dan 'login' untuk Hak Akses Pengguna
    $config->exec("CREATE TABLE IF NOT EXISTS `member` (
      `id_member` int(11) NOT NULL AUTO_INCREMENT,
      `nm_member` varchar(255) NOT NULL,
      `alamat_member` text NOT NULL,
      `telepon` varchar(255) NOT NULL,
      `email` varchar(255) NOT NULL,
      `gambar` text NOT NULL,
      `NIK` text NOT NULL,
      PRIMARY KEY (`id_member`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    $config->exec("CREATE TABLE IF NOT EXISTS `login` (
      `id_login` int(11) NOT NULL AUTO_INCREMENT,
      `user` varchar(255) NOT NULL,
      `pass` char(32) NOT NULL,
      `id_member` int(11) NOT NULL,
      `role` varchar(50) NOT NULL DEFAULT 'admin',
      PRIMARY KEY (`id_login`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    $config->exec("ALTER TABLE `member` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $config->exec("ALTER TABLE `login` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

    // Seeder data akun admin default jika belum ada
    $stmt = $config->prepare("SELECT COUNT(*) FROM `member` WHERE `id_member` = 1");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $config->exec("INSERT INTO `member` (`id_member`, `nm_member`, `alamat_member`, `telepon`, `email`, `gambar`, `NIK`) VALUES
        (1, 'Admin Resto', 'Banyuwangi', '081234567890', 'admin@javaturtle.com', 'unnamed.jpg', '12314121')");
    }
    $stmt = $config->prepare("SELECT COUNT(*) FROM `login` WHERE `id_login` = 1");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $config->exec("INSERT INTO `login` (`id_login`, `user`, `pass`, `id_member`, `role`) VALUES
        (1, 'admin', MD5('123'), 1, 'admin')");
    }

    // REVISI: Memastikan akun kasir (id_member = 2) dihapus sesuai kebijakan satu role admin saja
    $config->exec("DELETE FROM `login` WHERE `id_member` = 2");
    $config->exec("DELETE FROM `member` WHERE `id_member` = 2");
    $output[] = ["label" => "Memperbarui Akun Pengguna (Hanya Akun Admin Uji Coba)", "status" => "success"];

    // 3. Membuat ulang tabel 'kategori' Menu Resto
    $config->exec("DROP TABLE IF EXISTS `kategori` ");
    $config->exec("CREATE TABLE `kategori` (
      `id_kategori` int(11) NOT NULL AUTO_INCREMENT,
      `nama_kategori` varchar(255) NOT NULL,
      `tgl_input` varchar(255) NOT NULL,
      PRIMARY KEY (`id_kategori`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    $tgl = date('j F Y, G:i');
    $config->exec("INSERT INTO `kategori` (`id_kategori`, `nama_kategori`, `tgl_input`) VALUES
    (1, 'Makanan Utama', '$tgl'),
    (2, 'Minuman Segar', '$tgl'),
    (3, 'Cemilan / Snack', '$tgl'),
    (4, 'Dessert / Pencuci Mulut', '$tgl')");
    $output[] = ["label" => "Membangun Kategori Menu Resto (Makanan, Minuman, Cemilan, Dessert)", "status" => "success"];

    // 4. Membuat tabel 'menu_layanan' (Master Menu Makanan dan Minuman Resto)
    $config->exec("DROP TABLE IF EXISTS `menu_layanan`");
    $config->exec("CREATE TABLE `menu_layanan` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `id_menu` varchar(255) NOT NULL,
      `id_kategori` int(11) NOT NULL,
      `nama` varchar(255) NOT NULL,
      `harga` varchar(255) NOT NULL,
      `tipe` enum('makanan','minuman','snack','dessert') NOT NULL DEFAULT 'makanan',
      `stok` int(11) NOT NULL DEFAULT 999,
      `foto` varchar(255) DEFAULT NULL,
      `tgl_input` varchar(255) NOT NULL,
      `tgl_update` varchar(255) DEFAULT NULL,
      PRIMARY KEY (`id`),
      UNIQUE KEY (`id_menu`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    
    // Seeder daftar menu awal beserta harga, tipe, dan stok awal
    $config->exec("INSERT INTO `menu_layanan` (`id_menu`, `id_kategori`, `nama`, `harga`, `tipe`, `stok`, `foto`, `tgl_input`) VALUES
    ('MN001', 1, 'Nasi Goreng Spesial Turtle', '25000', 'makanan', 100, NULL, '$tgl'),
    ('MN002', 1, 'Ayam Bakar Taliwang Meru', '30000', 'makanan', 50, NULL, '$tgl'),
    ('MN003', 2, 'Es Teh Manis Jumbo', '5000', 'minuman', 200, NULL, '$tgl'),
    ('MN004', 2, 'Kopi Susu Gula Aren', '15000', 'minuman', 100, NULL, '$tgl'),
    ('MN005', 3, 'Kentang Goreng Crispy', '12000', 'snack', 80, NULL, '$tgl'),
    ('MN006', 4, 'Pisang Bakar Cokelat Keju', '15000', 'dessert', 60, NULL, '$tgl')");
    $output[] = ["label" => "Membuat Master Menu Layanan Resto & Data Awal Makanan/Minuman", "status" => "success"];

    // 5. Membuat tabel 'penjualan_temp' (Keranjang Kasir Sementara / Belum Check Out)
    $config->exec("DROP TABLE IF EXISTS `penjualan_temp`");
    $config->exec("CREATE TABLE `penjualan_temp` (
      `id_penjualan_temp` int(11) NOT NULL AUTO_INCREMENT,
      `id_menu` varchar(255) NOT NULL,
      `id_member` int(11) NOT NULL,
      `jumlah` int(11) NOT NULL,
      `meja` varchar(50) DEFAULT NULL,
      `tipe_pesanan` enum('dine-in','take-away') NOT NULL DEFAULT 'dine-in',
      `catatan` varchar(255) DEFAULT NULL,
      `total` varchar(255) NOT NULL,
      `tanggal_input` varchar(255) NOT NULL,
      PRIMARY KEY (`id_penjualan_temp`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    $output[] = ["label" => "Membuat tabel Penjualan Sementara (Keranjang Resto)", "status" => "success"];

    // 6. Membuat tabel 'penjualan' (Riwayat Transaksi Penjualan Resto / Sudah Check Out)
    $config->exec("DROP TABLE IF EXISTS `penjualan`");
    $config->exec("CREATE TABLE `penjualan` (
      `id_penjualan` int(11) NOT NULL AUTO_INCREMENT,
      `no_invoice` varchar(50) NOT NULL,
      `id_menu` varchar(255) NOT NULL,
      `id_member` int(11) NOT NULL,
      `nama_pelanggan` varchar(255) NOT NULL,
      `meja` varchar(50) DEFAULT NULL,
      `tipe_pesanan` enum('dine-in','take-away') NOT NULL DEFAULT 'dine-in',
      `jumlah` int(11) NOT NULL,
      `total` varchar(255) NOT NULL,
      `catatan` varchar(255) DEFAULT NULL,
      `metode_pembayaran` varchar(50) NOT NULL DEFAULT 'Tunai',
      `status_pembayaran` varchar(50) NOT NULL DEFAULT 'Lunas',
      `tanggal_input` varchar(255) NOT NULL,
      `periode` varchar(255) DEFAULT NULL,
      PRIMARY KEY (`id_penjualan`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    $output[] = ["label" => "Membuat tabel Riwayat Transaksi Penjualan Resto", "status" => "success"];

} catch (PDOException $e) {
    $error = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Migrator - Java Turtle Resto</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background: radial-gradient(circle at top right, #3a757f, #1a3c42);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #fff;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .container {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 600px;
            box-sizing: border-box;
        }
        h2 {
            margin-top: 0;
            color: #4ed9c3;
            font-size: 28px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .subtitle {
            font-size: 14px;
            color: #ccc;
            margin-bottom: 30px;
        }
        .log-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            font-size: 14px;
        }
        .log-item:last-child {
            border-bottom: none;
        }
        .badge {
            background: #28a745;
            color: #fff;
            font-weight: bold;
            font-size: 10px;
            padding: 3px 8px;
            border-radius: 20px;
            text-transform: uppercase;
        }
        .error-box {
            background: rgba(220, 53, 69, 0.2);
            border: 1px solid #dc3545;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 25px;
            font-size: 13px;
            line-height: 1.5;
        }
        .btn {
            display: block;
            background: #4ed9c3;
            color: #1a3c42;
            text-align: center;
            text-decoration: none;
            padding: 12px;
            border-radius: 8px;
            font-weight: bold;
            font-size: 16px;
            margin-top: 30px;
            transition: all 0.3s ease;
        }
        .btn:hover {
            background: #39bfa9;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(78, 217, 195, 0.4);
        }
    </style>
</head>
<body>

<div class="container">
    <h2>🐢 Java Turtle Resto Migrator</h2>
    <div class="subtitle">Skrip Otomatisasi Database POS Manajemen Resto & Cafe</div>

    <?php if ($error !== null): ?>
        <div class="error-box">
            <strong>MIGRASI GAGAL:</strong><br>
            <?= htmlspecialchars($error); ?><br><br>
            <em>Petunjuk: Pastikan koneksi server MySQL/MariaDB kamu aktif!</em>
        </div>
    <?php else: ?>
        <div class="log-list">
            <?php foreach ($output as $item): ?>
                <div class="log-item">
                    <span class="badge">Sukses</span>
                    <span><?= htmlspecialchars($item['label']); ?></span>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div style="background: rgba(255, 255, 255, 0.05); padding: 15px; border-radius: 8px; margin-top: 25px; font-size: 13px;">
            <strong style="color: #4ed9c3;">🔑 Akun Resto Pengujian yang Siap Digunakan:</strong><br>
            • Admin: <b>admin</b> (Password: <b>123</b>)
        </div>

        <a href="login.php" class="btn">Masuk Ke Aplikasi Resto ➡️</a>
    <?php endif; ?>
</div>

</body>
</html>
