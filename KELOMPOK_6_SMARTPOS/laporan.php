<?php
// ================================================================
// SMARTPOS - Laporan Penjualan (Cetak)
// File: laporan.php
// Deskripsi: Halaman cetak laporan penjualan berdasarkan
//            filter rentang tanggal (dari riwayat.php)
//            Dibuka di tab baru dan otomatis print
// ================================================================

include 'config/koneksi.php';
cekLogin();

// Ambil tanggal filter dari form POST (riwayat.php)
$tgl_mulai   = $_POST['tgl_mulai'] ?? '';
$tgl_selesai = $_POST['tgl_selesai'] ?? '';

// Validasi: pastikan tanggal tidak kosong
if (empty($tgl_mulai) || empty($tgl_selesai)) {
    header('location: riwayat.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Laporan Penjualan - SMARTPOS</title>
    <style>
        body { font-family: sans-serif; padding: 20px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #000; padding-bottom: 10px; }
        h2, h3, p { margin: 5px 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 12px; }
        th, td { border: 1px solid #000; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; text-align: center; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .grand-total { font-weight: bold; font-size: 14px; background-color: #eee; }
        
        /* Hilangkan elemen browser saat print */
        @media print {
            .no-print { display: none; }
            @page { margin: 1cm; }
        }
    </style>
</head>
<body onload="window.print()">

    <!-- Tombol navigasi (tidak tampil saat print) -->
    <div class="no-print" style="margin-bottom: 20px;">
        <a href="riwayat.php" style="text-decoration: none; background: #6c757d; color: white; padding: 8px 15px; border-radius: 5px;">&laquo; Kembali</a>
        <button onclick="window.print()" style="background: #0d6efd; color: white; padding: 8px 15px; border: none; border-radius: 5px; cursor: pointer;">Cetak Laporan</button>
    </div>

    <!-- Header Laporan -->
    <div class="header">
        <h2>SMARTPOS</h2>
        <p>Sistem Kasir Digital Berbasis Web</p>
        <h3>Laporan Penjualan</h3>
        <p>Periode: <?= date('d M Y', strtotime($tgl_mulai)); ?> s/d <?= date('d M Y', strtotime($tgl_selesai)); ?></p>
    </div>

    <!-- Tabel Laporan -->
    <table>
        <thead>
            <tr>
                <th width="5%">No</th>
                <th>ID Transaksi</th>
                <th>Tanggal</th>
                <th>Kasir</th>
                <th>Rincian Produk</th>
                <th>Metode</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $no = 1;
            $grand_total = 0;
            
            // Query laporan berdasarkan rentang tanggal
            // Join transaksi → detail_transaksi → produk → users
            $query = mysqli_query($con, "
                SELECT t.id_transaksi, t.tanggal, t.total, t.metode,
                       u.nama_lengkap as kasir,
                       GROUP_CONCAT(CONCAT(p.namaproduk, ' (', dt.qty, ')') SEPARATOR ', ') as items
                FROM transaksi t
                JOIN detail_transaksi dt ON t.id_transaksi = dt.id_transaksi
                JOIN produk p ON dt.idproduk = p.idproduk
                JOIN users u ON t.id_user = u.id_user
                WHERE DATE(t.tanggal) BETWEEN '$tgl_mulai' AND '$tgl_selesai'
                GROUP BY t.id_transaksi
                ORDER BY t.tanggal ASC
            ");

            while ($d = mysqli_fetch_array($query)) {
                $grand_total += $d['total'];
            ?>
            <tr>
                <td class="text-center"><?= $no++; ?></td>
                <td class="text-center"><?= formatIdTransaksi($d['id_transaksi']); ?></td>
                <td class="text-center"><?= date('d/m/Y H:i', strtotime($d['tanggal'])); ?></td>
                <td class="text-center"><?= htmlspecialchars($d['kasir']); ?></td>
                <td><?= htmlspecialchars($d['items']); ?></td>
                <td class="text-center"><?= $d['metode']; ?></td>
                <td class="text-right">Rp <?= number_format($d['total']); ?></td>
            </tr>
            <?php } ?>
            
            <!-- Baris Grand Total -->
            <tr class="grand-total">
                <td colspan="6" class="text-center">TOTAL PEMASUKAN PERIODE INI</td>
                <td class="text-right">Rp <?= number_format($grand_total); ?></td>
            </tr>
        </tbody>
    </table>

    <!-- Tanda tangan -->
    <div style="margin-top: 30px; text-align: right;">
        <p>Dicetak pada: <?= date('d F Y H:i'); ?> WIB</p>
        <br><br><br>
        <p>( <?= htmlspecialchars($_SESSION['nama_lengkap']); ?> )</p>
        <p><small><?= ucfirst($_SESSION['role']); ?></small></p>
    </div>

</body>
</html>