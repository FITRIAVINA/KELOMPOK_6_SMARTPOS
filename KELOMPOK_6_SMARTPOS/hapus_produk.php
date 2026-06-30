<?php
// ================================================================
// SMARTPOS - Hapus Produk
// File: hapus_produk.php
// Deskripsi: Proses menghapus produk dari database
//            Mengambil ID produk dari parameter URL (?id=)
//            Setelah hapus, redirect ke halaman produk
// ================================================================

include 'config/koneksi.php';
cekLogin();
onlySuperAdmin();

// Ambil ID produk dari URL
$idproduk = isset($_GET['id']) ? (int) $_GET['id'] : 0;

// Validasi ID
if ($idproduk <= 0) {
    echo "<script>alert('ID Produk tidak valid!'); window.location='produk.php';</script>";
    exit;
}

// Cek apakah produk ada
$cek = mysqli_query($con, "SELECT * FROM produk WHERE idproduk='$idproduk'");
if (mysqli_num_rows($cek) == 0) {
    echo "<script>alert('Produk tidak ditemukan!'); window.location='produk.php';</script>";
    exit;
}

// Hapus gambar produk jika ada
$data = mysqli_fetch_assoc($cek);
if ($data['gambar'] && file_exists("images/" . $data['gambar'])) {
    // Jangan hapus gambar default atau gambar yang dipakai banyak produk
    // Untuk keamanan, kita hapus saja file gambarnya
    unlink("images/" . $data['gambar']);
}

// Hapus produk dari database
// Detail transaksi dan masuk yang terkait akan terhapus otomatis (CASCADE)
$hapus = mysqli_query($con, "DELETE FROM produk WHERE idproduk='$idproduk'");

if ($hapus) {
    echo "<script>
        alert('Produk berhasil dihapus!');
        window.location.href='produk.php';
    </script>";
} else {
    echo "<script>
        alert('Gagal menghapus produk! Mungkin masih terkait dengan transaksi.');
        window.location.href='produk.php';
    </script>";
}
?>
