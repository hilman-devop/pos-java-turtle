-- SQL Schema for Restaurant POS and Management System
-- Database name: db_resto

CREATE TABLE IF NOT EXISTS `toko` (
  `id_toko` int(11) NOT NULL AUTO_INCREMENT,
  `nama_toko` varchar(255) NOT NULL,
  `alamat_toko` text NOT NULL,
  `tlp` varchar(255) NOT NULL,
  `nama_pemilik` varchar(255) NOT NULL,
  `footer_nota` text DEFAULT NULL,
  PRIMARY KEY (`id_toko`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `member` (
  `id_member` int(11) NOT NULL AUTO_INCREMENT,
  `nm_member` varchar(255) NOT NULL,
  `alamat_member` text NOT NULL,
  `telepon` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `gambar` text NOT NULL,
  `NIK` text NOT NULL,
  PRIMARY KEY (`id_member`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `login` (
  `id_login` int(11) NOT NULL AUTO_INCREMENT,
  `user` varchar(255) NOT NULL,
  `pass` char(32) NOT NULL,
  `id_member` int(11) NOT NULL,
  `role` varchar(50) NOT NULL DEFAULT 'admin',
  PRIMARY KEY (`id_login`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `kategori` (
  `id_kategori` int(11) NOT NULL AUTO_INCREMENT,
  `nama_kategori` varchar(255) NOT NULL,
  `tgl_input` varchar(255) NOT NULL,
  PRIMARY KEY (`id_kategori`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `menu_layanan` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `penjualan_temp` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `penjualan` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
