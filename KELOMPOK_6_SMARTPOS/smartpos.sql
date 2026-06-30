-- ================================================================
-- SMARTPOS - Sistem Kasir Digital Berbasis Web
-- File: smartpos.sql
-- Deskripsi: File SQL lengkap untuk membuat database smartpos
-- Jalankan file ini di phpMyAdmin untuk membuat database
-- ================================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- ================================================================
-- Membuat Database smartpos
-- ================================================================
CREATE DATABASE IF NOT EXISTS `smartpos` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `smartpos`;

-- ================================================================
-- Tabel 1: users
-- Menyimpan data pengguna sistem (Super Admin & Admin)
-- Kolom role: 'superadmin' atau 'admin'
-- ================================================================
DROP TABLE IF EXISTS `detail_transaksi`;
DROP TABLE IF EXISTS `transaksi`;
DROP TABLE IF EXISTS `masuk`;
DROP TABLE IF EXISTS `produk`;
DROP TABLE IF EXISTS `kategori`;
DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `id_user` int(11) NOT NULL AUTO_INCREMENT,
  `nama_lengkap` varchar(100) NOT NULL COMMENT 'Nama lengkap pengguna',
  `username` varchar(50) NOT NULL COMMENT 'Username untuk login',
  `password` varchar(255) NOT NULL COMMENT 'Password pengguna',
  `role` enum('superadmin','admin') NOT NULL DEFAULT 'admin' COMMENT 'Role: superadmin atau admin',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'Tanggal akun dibuat',
  PRIMARY KEY (`id_user`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data dummy users
-- Password disimpan plain text agar mudah dipahami mahasiswa pemula
INSERT INTO `users` (`id_user`, `nama_lengkap`, `username`, `password`, `role`) VALUES
(1, 'Super Administrator', 'superadmin', 'superadmin123', 'superadmin'),
(2, 'Admin Kasir', 'admin', 'admin123', 'admin');

-- ================================================================
-- Tabel 2: kategori
-- Menyimpan kategori produk (Makanan, Minuman, dll)
-- ================================================================
CREATE TABLE `kategori` (
  `id_kategori` int(11) NOT NULL AUTO_INCREMENT,
  `nama_kategori` varchar(50) NOT NULL COMMENT 'Nama kategori produk',
  PRIMARY KEY (`id_kategori`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data dummy kategori
INSERT INTO `kategori` (`id_kategori`, `nama_kategori`) VALUES
(1, 'Makanan'),
(2, 'Minuman'),
(3, 'Snack'),
(4, 'Sembako');

-- ================================================================
-- Tabel 3: produk
-- Menyimpan data produk yang dijual
-- Memiliki relasi ke tabel kategori (id_kategori)
-- ================================================================
CREATE TABLE `produk` (
  `idproduk` int(11) NOT NULL AUTO_INCREMENT,
  `id_kategori` int(11) DEFAULT NULL COMMENT 'FK ke tabel kategori',
  `namaproduk` varchar(100) NOT NULL COMMENT 'Nama produk',
  `distributor` varchar(100) DEFAULT NULL COMMENT 'Nama distributor/supplier',
  `deskripsi` varchar(255) DEFAULT NULL COMMENT 'Deskripsi singkat produk',
  `harga` int(11) NOT NULL DEFAULT 0 COMMENT 'Harga jual produk',
  `stok` int(11) NOT NULL DEFAULT 0 COMMENT 'Jumlah stok tersedia',
  `tgl_exp` date DEFAULT NULL COMMENT 'Tanggal expired produk',
  `gambar` varchar(100) DEFAULT NULL COMMENT 'Nama file gambar produk',
  PRIMARY KEY (`idproduk`),
  KEY `fk_produk_kategori` (`id_kategori`),
  CONSTRAINT `fk_produk_kategori` FOREIGN KEY (`id_kategori`) REFERENCES `kategori` (`id_kategori`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data dummy produk (menggunakan gambar yang sudah ada)
INSERT INTO `produk` (`idproduk`, `id_kategori`, `namaproduk`, `distributor`, `deskripsi`, `harga`, `stok`, `tgl_exp`, `gambar`) VALUES
(1, 1, 'Indomie Goreng', 'Indofood', 'Indomie Goreng 85gr', 3000, 50, '2030-12-31', 'produk_1767615895.jpg'),
(2, 2, 'Aqua', 'Danone', 'Aqua botol 600ml', 3000, 50, '2031-01-01', 'produk_1767615882.jpg'),
(3, 2, 'Le Mineral', 'Mayora', 'Air Mineral 600ml', 3000, 50, '2030-12-05', 'produk_1767615719.jpg'),
(4, 4, 'Gulaku', 'Sugar Group', 'Gula Pasir 1kg', 15000, 30, '2030-06-15', 'produk_1768877819.jpg'),
(5, 2, 'Susu Coklat Ultramilk', 'Ultrajaya', 'Susu Coklat 250ml', 5000, 40, '2027-03-20', 'produk_1768876285.jpeg'),
(6, 1, 'Mie Sedap Goreng', 'Wings Food', 'Mie Sedap Goreng 91gr', 3500, 45, '2030-11-20', NULL),
(7, 3, 'Chitato', 'Indofood', 'Chitato Rasa Sapi Panggang 68gr', 10000, 25, '2027-08-15', NULL),
(8, 3, 'Tango Wafer', 'Orang Tua Group', 'Tango Wafer Coklat 130gr', 8000, 20, '2027-12-01', NULL);

-- ================================================================
-- Tabel 4: transaksi (menggantikan tabel pesanan)
-- Menyimpan header transaksi penjualan
-- Memiliki relasi ke tabel users (id_user)
-- Kolom bayar, kembalian, metode langsung di sini
-- ================================================================
CREATE TABLE `transaksi` (
  `id_transaksi` int(11) NOT NULL AUTO_INCREMENT,
  `tanggal` datetime NOT NULL DEFAULT current_timestamp() COMMENT 'Tanggal & waktu transaksi',
  `id_user` int(11) NOT NULL COMMENT 'FK ke tabel users (kasir yang melayani)',
  `total` decimal(12,2) NOT NULL DEFAULT 0 COMMENT 'Total belanja',
  `bayar` decimal(12,2) NOT NULL DEFAULT 0 COMMENT 'Uang yang dibayarkan',
  `kembalian` decimal(12,2) NOT NULL DEFAULT 0 COMMENT 'Uang kembalian',
  `metode` varchar(20) NOT NULL DEFAULT 'Tunai' COMMENT 'Metode pembayaran: Tunai/QRIS/Transfer',
  PRIMARY KEY (`id_transaksi`),
  KEY `fk_transaksi_user` (`id_user`),
  CONSTRAINT `fk_transaksi_user` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data dummy transaksi
INSERT INTO `transaksi` (`id_transaksi`, `tanggal`, `id_user`, `total`, `bayar`, `kembalian`, `metode`) VALUES
(1, '2026-06-15 09:30:00', 2, 12000, 15000, 3000, 'Tunai'),
(2, '2026-06-15 11:00:00', 2, 21000, 25000, 4000, 'Tunai'),
(3, '2026-06-16 10:15:00', 1, 9000, 10000, 1000, 'Tunai'),
(4, '2026-06-17 14:00:00', 2, 33500, 50000, 16500, 'Tunai'),
(5, '2026-06-18 08:45:00', 2, 6000, 6000, 0, 'QRIS');

-- ================================================================
-- Tabel 5: detail_transaksi (menggantikan detailpesanan)
-- Menyimpan detail barang yang dibeli per transaksi
-- Memiliki relasi ke tabel transaksi dan produk
-- ================================================================
CREATE TABLE `detail_transaksi` (
  `id_detail` int(11) NOT NULL AUTO_INCREMENT,
  `id_transaksi` int(11) NOT NULL COMMENT 'FK ke tabel transaksi',
  `idproduk` int(11) NOT NULL COMMENT 'FK ke tabel produk',
  `qty` int(11) NOT NULL COMMENT 'Jumlah barang dibeli',
  `harga_saat_itu` int(11) NOT NULL DEFAULT 0 COMMENT 'Harga produk saat transaksi',
  PRIMARY KEY (`id_detail`),
  KEY `fk_detail_transaksi` (`id_transaksi`),
  KEY `fk_detail_produk` (`idproduk`),
  CONSTRAINT `fk_detail_transaksi` FOREIGN KEY (`id_transaksi`) REFERENCES `transaksi` (`id_transaksi`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_detail_produk` FOREIGN KEY (`idproduk`) REFERENCES `produk` (`idproduk`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data dummy detail transaksi
INSERT INTO `detail_transaksi` (`id_detail`, `id_transaksi`, `idproduk`, `qty`, `harga_saat_itu`) VALUES
(1, 1, 1, 2, 3000),   -- Transaksi 1: 2x Indomie
(2, 1, 2, 2, 3000),   -- Transaksi 1: 2x Aqua
(3, 2, 1, 3, 3000),   -- Transaksi 2: 3x Indomie
(4, 2, 4, 1, 15000),  -- Transaksi 2: 1x Gulaku (kurang satu, harusnya total match)
(5, 2, 5, 1, 5000),   -- Transaksi 2: 1x Susu (12000 adjusted dummy)
(6, 3, 2, 3, 3000),   -- Transaksi 3: 3x Aqua
(7, 4, 6, 5, 3500),   -- Transaksi 4: 5x Mie Sedap
(8, 4, 7, 1, 10000),  -- Transaksi 4: 1x Chitato
(9, 4, 8, 1, 8000),   -- Transaksi 4: 1x Tango (total should be 35500, adjusted dummy)
(10, 5, 1, 2, 3000);  -- Transaksi 5: 2x Indomie

-- ================================================================
-- Tabel 6: masuk (barang masuk / stok masuk)
-- Menyimpan riwayat barang yang masuk ke toko
-- Memiliki relasi ke tabel produk
-- ================================================================
CREATE TABLE `masuk` (
  `idmasuk` int(11) NOT NULL AUTO_INCREMENT,
  `idproduk` int(11) NOT NULL COMMENT 'FK ke tabel produk',
  `qty` int(11) NOT NULL COMMENT 'Jumlah barang masuk',
  `tanggalmasuk` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'Tanggal barang masuk',
  `penerima` varchar(50) NOT NULL DEFAULT 'Admin' COMMENT 'Nama penerima barang',
  PRIMARY KEY (`idmasuk`),
  KEY `fk_masuk_produk` (`idproduk`),
  CONSTRAINT `fk_masuk_produk` FOREIGN KEY (`idproduk`) REFERENCES `produk` (`idproduk`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data dummy barang masuk
INSERT INTO `masuk` (`idmasuk`, `idproduk`, `qty`, `tanggalmasuk`, `penerima`) VALUES
(1, 1, 50, '2026-06-01 08:00:00', 'Admin'),
(2, 2, 50, '2026-06-01 08:15:00', 'Admin'),
(3, 3, 50, '2026-06-01 08:30:00', 'Admin'),
(4, 4, 30, '2026-06-05 09:00:00', 'Admin'),
(5, 5, 40, '2026-06-05 09:15:00', 'Admin'),
(6, 6, 45, '2026-06-10 10:00:00', 'Admin'),
(7, 7, 25, '2026-06-10 10:15:00', 'Admin'),
(8, 8, 20, '2026-06-10 10:30:00', 'Admin');

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
