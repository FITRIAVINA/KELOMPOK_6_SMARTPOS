<?php
session_start();
$con = mysqli_connect("localhost","root","","kasir");

if(!$con){
    die("Koneksi database gagal: " . mysqli_connect_error());
}

// Cek apakah ada order
if(!isset($_SESSION['last_order'])){
    header("location:index.php");
    exit;
}

$idpesanan = $_SESSION['last_order'];

/* ================= PROSES BAYAR ================= */
if(isset($_POST['bayar'])){
    $bayar = $_POST['bayar'];
    $_SESSION['bayar'] = $bayar;

    // Redirect ke halaman cetak struk
    header("location:struk.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Checkout | SmartSell</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <link href="css/styles.css" rel="stylesheet">
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js"></script>
    <style>
        .qris-container {
            border: 2px dashed #ddd;
            border-radius: 10px;
            padding: 15px;
            background-color: #f9f9f9;
        }
    </style>
</head>

<body class="sb-nav-fixed">

    <nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">
        <a class="navbar-brand ps-3" href="#">
            <i class="fas fa-cash-register me-2"></i>SmartSell
        </a>
    </nav>

    <div id="layoutSidenav">

        <div id="layoutSidenav_nav">
            <nav class="sb-sidenav sb-sidenav-dark">
                <div class="sb-sidenav-menu">
                    <div class="nav">
                        <div class="sb-sidenav-menu-heading">Menu</div>
                        <a class="nav-link" href="index.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-shopping-cart"></i></div>
                            Order
                        </a>
                        <a class="nav-link" href="stok.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-boxes"></i></div>
                            Stok Barang
                        </a>
                        <a class="nav-link" href="masuk.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-arrow-down"></i></div>
                            Barang Masuk
                        </a>
                        <a class="nav-link" href="logout.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-right-from-bracket"></i></div>
                            Logout
                        </a>
                    </div>
                </div>
            </nav>
        </div>

        <div id="layoutSidenav_content">
            <main class="container-fluid px-4">

                <h1 class="mt-4">Checkout</h1>
                <ol class="breadcrumb mb-4">
                    <li class="breadcrumb-item active">Pembayaran</li>
                </ol>

                <div class="card mb-4 shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <i class="fas fa-receipt me-1"></i> Ringkasan & Pembayaran
                    </div>
                    
                    <div class="card-body">
                        
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Produk</th>
                                        <th>Harga</th>
                                        <th>Qty</th>
                                        <th>Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>

                                <?php
                                $total = 0;
                                $data = mysqli_query($con,"
                                    SELECT p.namaproduk, p.harga, dp.qty 
                                    FROM detailpesanan dp
                                    JOIN produk p ON dp.idproduk = p.idproduk
                                    WHERE dp.idpesanan = '$idpesanan'
                                ");

                                while($d = mysqli_fetch_array($data)){
                                    $subtotal = $d['harga'] * $d['qty'];
                                    $total += $subtotal;
                                ?>
                                <tr>
                                    <td><?= $d['namaproduk']; ?></td>
                                    <td>Rp <?= number_format($d['harga']); ?></td>
                                    <td><?= $d['qty']; ?></td>
                                    <td>Rp <?= number_format($subtotal); ?></td>
                                </tr>
                                <?php } ?>

                                <tr class="fw-bold" style="font-size: 1.1em;">
                                    <td colspan="3" class="text-end">TOTAL TAGIHAN</td>
                                    <td class="bg-warning">Rp <?= number_format($total); ?></td>
                                </tr>

                                </tbody>
                            </table>
                        </div>

                        <hr class="my-4">

                        <div class="row">
                            
                            <div class="col-lg-6 mb-4">
                                <div class="card h-100 border-0">
                                    <div class="card-body">
                                        <h4 class="mb-3"><i class="fas fa-money-bill-wave text-success"></i> Pembayaran Tunai</h4>
                                        <p class="text-muted">Masukkan jumlah uang yang diterima dari pelanggan.</p>
                                        
                                        <form method="post">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Nominal Uang (Rp)</label>
                                                <input type="number" name="bayar" id="inputBayar" class="form-control form-control-lg" placeholder="Contoh: 50000" required>
                                            </div>

                                            <div class="d-grid gap-2">
                                                <button class="btn btn-success btn-lg">
                                                    <i class="fas fa-print"></i> Proses & Cetak Struk
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-6 mb-4">
                                <div class="card h-100 border-0">
                                    <div class="card-body text-center">
                                        <h4 class="mb-3"><i class="fas fa-qrcode text-primary"></i> Pembayaran QRIS</h4>
                                        
                                        <div class="qris-container d-inline-block">
                                            <img src="images/qris.jpg" onerror="this.src='images/qris.jpeg'" alt="Scan QRIS Disini" class="img-fluid mb-2" style="max-width: 200px;">
                                            
                                            <div class="fw-bold">SITI ALFIATUL MAKIAH</div>
                                            <small class="text-muted">ShopeePay / All Payment</small>
                                        </div>

                                        <div class="alert alert-info mt-3 mb-0">
                                            <small><i class="fas fa-info-circle"></i> Jika pelanggan membayar via QRIS, klik tombol di bawah untuk mengisi nominal pas.</small>
                                        </div>

                                        <button type="button" onclick="bayarPas()" class="btn btn-outline-primary mt-3 w-100">
                                            <i class="fas fa-check-circle"></i> Konfirmasi Lunas (QRIS)
                                        </button>
                                    </div>
                                </div>
                            </div>

                        </div> 
                    </div> 
                </div>

            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function bayarPas() {
            var totalTagihan = <?php echo $total; ?>;
            document.getElementById('inputBayar').value = totalTagihan;
        }
    </script>

</body>
</html>