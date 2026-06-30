<?php
// ================================================================
// SMARTPOS - Proses Kategori Handler
// File: proses_kategori.php
// Deskripsi: Backend handler untuk aksi Tambah & Hapus Kategori Produk
// ================================================================

include 'config/koneksi.php';
cekLogin();

// Memastikan variabel parameter aksi tersedia
if (!isset($_GET['aksi'])) {
    header("Location: produk.php");
    exit;
}

$aksi = $_GET['aksi'];

// --- AKSI TAMBAH KATEGORI ---
if ($aksi == 'tambah') {
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Menggunakan variabel koneksi global $con dari config
        $nama_kategori = mysqli_real_escape_string($con, trim($_POST['nama_kategori']));
        
        if (!empty($nama_kategori)) {
            $query = mysqli_query($con, "INSERT INTO kategori (nama_kategori) VALUES ('$nama_kategori')");
            
            if ($query) {
                echo "<script>alert('Kategori baru berhasil disimpan!'); window.location='produk.php';</script>";
            } else {
                echo "<script>alert('Gagal menyimpan data ke database.'); window.location='produk.php';</script>";
            }
        } else {
            echo "<script>alert('Nama kategori tidak boleh kosong!'); window.location='produk.php';</script>";
        }
    }

// --- AKSI HAPUS KATEGORI ---
} elseif ($aksi == 'hapus') {
    if (isset($_GET['id'])) {
        $id_kategori = mysqli_real_escape_string($con, $_GET['id']);
        
        $query = mysqli_query($con, "DELETE FROM kategori WHERE id_kategori = '$id_kategori'");
        
        if ($query) {
            echo "<script>alert('Kategori berhasil dihapus!'); window.location='produk.php';</script>";
        } else {
            echo "<script>alert('Gagal menghapus data kategori.'); window.location='produk.php';</script>";
        }
    } else {
        header("Location: produk.php");
        exit;
    }
    
} else {
    header("Location: produk.php");
    exit;
}
?>