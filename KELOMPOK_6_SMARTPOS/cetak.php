<?php
// ================================================================
// SMARTPOS - Cetak Struk (dari Riwayat / setelah Transaksi)
// File: cetak.php
// Deskripsi: Halaman cetak struk berdasarkan ID transaksi
//            Diakses via URL: cetak.php?id=123
//            Menampilkan: nama toko, tanggal, daftar barang,
//            total, pembayaran, kembalian, kasir
// ================================================================

// Koneksi database
include 'config/koneksi.php';

// Ambil ID transaksi dari URL
$id_transaksi = isset($_GET['id']) ? (int) $_GET['id'] : 0;

// Validasi
if ($id_transaksi <= 0) {
    die("ID Transaksi tidak valid!");
}

// Ambil data transaksi header
$transaksi = mysqli_query($con, "
    SELECT t.*, u.nama_lengkap as kasir 
    FROM transaksi t 
    JOIN users u ON t.id_user = u.id_user 
    WHERE t.id_transaksi = '$id_transaksi'
");
$t = mysqli_fetch_array($transaksi);

if (!$t) {
    die("Transaksi tidak ditemukan!");
}

// Ambil detail barang yang dibeli
$detail = mysqli_query($con, "
    SELECT p.namaproduk, dt.harga_saat_itu as harga, dt.qty
    FROM detail_transaksi dt
    JOIN produk p ON dt.idproduk = p.idproduk
    WHERE dt.id_transaksi = '$id_transaksi'
");

// Hitung total dari detail (untuk verifikasi)
$total_bayar = 0;
$items = [];
while ($d = mysqli_fetch_array($detail)) {
    $subtotal = $d['harga'] * $d['qty'];
    $total_bayar += $subtotal;
    $items[] = $d;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Pembayaran - <?= formatIdTransaksi($id_transaksi); ?></title>
    <style>
        /* Style struk thermal / POS printer */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Courier New', Courier, monospace; 
            font-size: 11px; 
            margin: 0; 
            padding: 10px; 
            background: #eee; 
        }
        .struk { 
            width: 280px; 
            background: #fff; 
            padding: 12px; 
            margin: 0 auto; 
            box-shadow: 0 0 10px rgba(0,0,0,0.15); 
        }
        .center { text-align: center; }
        .flex { display: flex; justify-content: space-between; margin: 2px 0; }
        .line { border-bottom: 1px dashed #000; margin: 6px 0; }
        .store-name { font-size: 16px; font-weight: bold; margin: 0; }
        .store-sub { font-size: 10px; margin: 2px 0; color: #333; }
        .trx-id { font-size: 11px; font-weight: bold; margin: 2px 0; }
        .item-name { font-size: 10px; margin-top: 3px; }
        .item-detail { font-size: 10px; }
        .total-label { font-size: 13px; font-weight: bold; }
        .footer-text { font-size: 9px; margin-top: 8px; line-height: 1.4; }
        .footer-note { font-size: 8px; color: #555; margin-top: 6px; line-height: 1.3; }
        .footer-time { font-size: 8px; color: #888; margin-top: 4px; }

        .btn-print { 
            display: block; width: 100%; padding: 10px; margin-top: 10px; 
            cursor: pointer; text-align: center; background: #0d6efd; 
            color: white; text-decoration: none; border: none; border-radius: 5px;
            font-size: 13px;
        }
        .btn-back {
            display: block; width: 100%; padding: 10px; margin-top: 5px;
            cursor: pointer; text-align: center; background: #6c757d;
            color: white; text-decoration: none; border: none; border-radius: 5px;
            font-size: 13px;
        }
        
        /* Setting print — struk muat 1 halaman */
        @media print { 
            @page { 
                size: 58mm auto;
                margin: 0; 
            }
            body { 
                background: #fff; 
                margin: 0; 
                padding: 2mm;
            } 
            .btn-print, .btn-back, .btn-container { display: none !important; }
            .struk { 
                box-shadow: none; 
                margin: 0; 
                width: 100%; 
                padding: 0;
                page-break-inside: avoid;
            } 
        }
    </style>
</head>
<body onload="window.print()">

<div class="struk">
    <!-- Header Toko -->
    <div class="center">
        <p class="store-name">SMARTPOS</p>
        <p class="store-sub">Sistem Kasir Digital</p>
        <p class="trx-id"><?= formatIdTransaksi($id_transaksi); ?></p>
    </div>

    <div class="line"></div>

    <!-- Info Transaksi -->
    <div class="flex">
        <span>Tanggal</span>
        <span><?= date('d/m/Y H:i', strtotime($t['tanggal'])); ?></span>
    </div>
    <div class="flex">
        <span>Kasir</span>
        <span><?= htmlspecialchars($t['kasir']); ?></span>
    </div>

    <div class="line"></div>

    <!-- Daftar Barang -->
    <?php foreach ($items as $i): ?>
        <div class="item-name"><?= htmlspecialchars($i['namaproduk']); ?></div>
        <div class="flex item-detail">
            <span><?= $i['qty']; ?> x <?= number_format($i['harga']); ?></span>
            <span><?= number_format($i['qty'] * $i['harga']); ?></span>
        </div>
    <?php endforeach; ?>

    <div class="line"></div>

    <!-- Total -->
    <div class="flex total-label">
        <span>TOTAL</span>
        <span>Rp <?= number_format($t['total']); ?></span>
    </div>

    <div class="line"></div>

    <!-- Info Pembayaran -->
    <div class="flex">
        <span>Metode Bayar</span>
        <span><?= htmlspecialchars($t['metode']); ?></span>
    </div>
    <div class="flex">
        <span>Uang Diterima</span>
        <span>Rp <?= number_format($t['bayar']); ?></span>
    </div>
    
    <?php if ($t['metode'] == 'Tunai' && $t['kembalian'] > 0): ?>
    <div class="flex" style="font-weight:bold;">
        <span>KEMBALI</span>
        <span>Rp <?= number_format($t['kembalian']); ?></span>
    </div>
    <?php endif; ?>

    <div class="line"></div>

    <!-- Footer Struk -->
    <div class="center">
        <p class="footer-text">Terima Kasih telah berbelanja!<br>
        Kami nantikan kedatangan Anda kembali.</p>
        
        <p class="footer-note">Barang yang sudah dibeli<br>tidak dapat ditukar/dikembalikan.</p>
        
        <p class="footer-time"><?= date('d/m/Y H:i'); ?> WIB</p>
    </div>
</div>

<!-- Tombol (tidak tampil saat print) -->
<div class="btn-container" style="width:280px; margin:0 auto;">
    <button onclick="window.print()" class="btn-print"><i class="fas fa-print"></i> Cetak Ulang</button>
    <a href="riwayat.php" class="btn-back">&laquo; Kembali ke Riwayat</a>
</div>

</body>
</html>