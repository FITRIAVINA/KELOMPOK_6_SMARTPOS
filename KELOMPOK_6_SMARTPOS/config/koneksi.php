<?php
// ================================================================
// SMARTPOS - Koneksi Database
// File: config/koneksi.php
// Deskripsi: File koneksi database terpusat
//            Semua file PHP harus meng-include file ini
// ================================================================

// Memulai session jika belum dimulai
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// ================= KONEKSI DATABASE =================
// Menggunakan mysqli untuk koneksi ke database smartpos
$host   = "localhost";  // Host database
$user   = "root";       // Username database (default XAMPP)
$pass   = "";           // Password database (default XAMPP kosong)
$dbname = "smartpos";   // Nama database

$con = mysqli_connect($host, $user, $pass, $dbname);

// Cek apakah koneksi berhasil
if (!$con) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

// Set karakter encoding agar tidak ada masalah dengan karakter khusus
mysqli_set_charset($con, "utf8mb4");

// ================= FUNCTION HELPER =================

/**
 * Fungsi untuk mengecek apakah user sudah login
 * Jika belum, redirect ke halaman login
 */
function cekLogin() {
    if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
        header('Location: login.php');
        exit;
    }
}

/**
 * Fungsi untuk mengecek role user
 * @param string $role - Role yang diizinkan ('superadmin' atau 'admin')
 * @return boolean
 */
function cekRole($role) {
    return (isset($_SESSION['role']) && $_SESSION['role'] === $role);
}

/**
 * Fungsi untuk mengecek apakah user adalah Super Admin
 * @return boolean
 */
function isSuperAdmin() {
    return cekRole('superadmin');
}

/**
 * Fungsi untuk mengecek apakah user adalah Admin
 * @return boolean
 */
function isAdmin() {
    return cekRole('admin');
}

/**
 * Fungsi untuk membatasi halaman hanya untuk Super Admin
 * Jika bukan Super Admin, redirect ke index.php
 */
function onlySuperAdmin() {
    if (!isSuperAdmin()) {
        echo "<script>
            alert('Akses ditolak! Halaman ini hanya untuk Super Admin.');
            window.location.href='transaksi.php';
        </script>";
        exit;
    }
}

/**
 * Fungsi untuk membatasi halaman hanya untuk Admin
 * Jika bukan Admin, redirect ke index.php
 */
function onlyAdmin() {
    if (!isAdmin()) {
        echo "<script>
            alert('Akses ditolak! Halaman ini hanya untuk Admin.');
            window.location.href='index.php';
        </script>";
        exit;
    }
}

/**
 * Fungsi untuk format angka ke Rupiah
 * @param int $angka - Angka yang akan diformat
 * @return string - Format Rupiah (contoh: Rp 10.000)
 */
function formatRupiah($angka) {
    return "Rp " . number_format($angka, 0, ',', '.');
}

/**
 * Fungsi untuk format ID Transaksi
 * Contoh: 1 → TR001, 9 → TR009, 25 → TR025
 * @param int $id - ID transaksi dari database
 * @return string - Format ID (contoh: TR001)
 */
function formatIdTransaksi($id) {
    return 'TR' . str_pad($id, 3, '0', STR_PAD_LEFT);
}

/**
 * Fungsi untuk format ID Barang/Produk
 * Contoh: 1 → BRG001, 5 → BRG005, 12 → BRG012
 * @param int $id - ID produk dari database
 * @return string - Format ID (contoh: BRG001)
 */
function formatIdBarang($id) {
    return 'BRG' . str_pad($id, 3, '0', STR_PAD_LEFT);
}
?>
