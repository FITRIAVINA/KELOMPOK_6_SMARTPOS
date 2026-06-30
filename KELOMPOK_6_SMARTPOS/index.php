<?php
// ================================================================
// SMARTPOS - Dashboard
// File: index.php
// Deskripsi: Halaman dashboard utama yang menampilkan statistik:
//            - Total Produk
//            - Total Transaksi
//            - Total Pendapatan
//            - Produk Stok Rendah
//            - Grafik penjualan mingguan
//            - Tabel transaksi terbaru
// ================================================================

// Include koneksi database
include 'config/koneksi.php';

// Cek apakah user sudah login
cekLogin();

// Hanya Super Admin yang bisa akses Dashboard
onlySuperAdmin();

// Set judul halaman dan menu aktif (untuk template)
$page_title  = "Dashboard";
$active_page = "dashboard";

// ================= QUERY STATISTIK DASHBOARD =================

// 1. Total Produk
$q_produk = mysqli_query($con, "SELECT COUNT(*) as total FROM produk");
$total_produk = mysqli_fetch_assoc($q_produk)['total'];

// 2. Total Transaksi
$q_transaksi = mysqli_query($con, "SELECT COUNT(*) as total FROM transaksi");
$total_transaksi = mysqli_fetch_assoc($q_transaksi)['total'];

// 3. Total Pendapatan (semua waktu)
$q_pendapatan = mysqli_query($con, "SELECT COALESCE(SUM(total), 0) as total FROM transaksi");
$total_pendapatan = mysqli_fetch_assoc($q_pendapatan)['total'];

// 4. Produk Stok Rendah (stok <= 10)
$q_stok_rendah = mysqli_query($con, "SELECT COUNT(*) as total FROM produk WHERE stok <= 10");
$total_stok_rendah = mysqli_fetch_assoc($q_stok_rendah)['total'];

// 5. Pendapatan Hari Ini
$q_hari = mysqli_query($con, "SELECT COALESCE(SUM(total), 0) as total FROM transaksi WHERE DATE(tanggal) = CURDATE()");
$pendapatan_hari = mysqli_fetch_assoc($q_hari)['total'];

// 6. Pendapatan Bulan Ini
$q_bulan = mysqli_query($con, "SELECT COALESCE(SUM(total), 0) as total FROM transaksi WHERE MONTH(tanggal) = MONTH(CURDATE()) AND YEAR(tanggal) = YEAR(CURDATE())");
$pendapatan_bulan = mysqli_fetch_assoc($q_bulan)['total'];

// 7. Data Grafik Penjualan (7 hari terakhir)
$q_grafik = mysqli_query($con, "
    SELECT DATE(tanggal) as tgl, SUM(total) as total 
    FROM transaksi 
    WHERE tanggal >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
    GROUP BY DATE(tanggal) 
    ORDER BY tgl ASC
");
$labels_grafik = [];
$data_grafik   = [];
while ($g = mysqli_fetch_assoc($q_grafik)) {
    $labels_grafik[] = date('d/m', strtotime($g['tgl']));
    $data_grafik[]   = (int)$g['total'];
}

// 8. Transaksi Terbaru (5 terakhir)
$q_terbaru = mysqli_query($con, "
    SELECT t.id_transaksi, t.tanggal, t.total, t.metode, u.nama_lengkap as kasir
    FROM transaksi t
    JOIN users u ON t.id_user = u.id_user
    ORDER BY t.id_transaksi DESC
    LIMIT 5
");

// 9. Produk Terlaris
$q_terlaris = mysqli_query($con, "
    SELECT p.namaproduk, SUM(dt.qty) as total_terjual
    FROM detail_transaksi dt
    JOIN produk p ON dt.idproduk = p.idproduk
    GROUP BY dt.idproduk
    ORDER BY total_terjual DESC
    LIMIT 5
");

// Include template header dan sidebar
include 'templates/header.php';
include 'templates/sidebar.php';
?>

<!-- ================= KONTEN DASHBOARD ================= -->
<main class="container-fluid px-4 py-4">
    
    <!-- Judul Halaman -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold text-dark">
            <i class="fas fa-tachometer-alt text-secondary me-2"></i>Dashboard
        </h3>
        <div class="text-muted">
            <i class="far fa-calendar-alt me-1"></i> <?= date('d F Y'); ?>
        </div>
    </div>

    <!-- ================= KARTU STATISTIK ================= -->
    <div class="row mb-4">
        <!-- Total Produk -->
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card-clean p-4 d-flex flex-row align-items-center h-100">
                <div class="stat-icon bg-icon-blue">
                    <i class="fas fa-box"></i>
                </div>
                <div>
                    <h6 class="text-muted text-uppercase small fw-bold mb-1">Total Produk</h6>
                    <h3 class="fw-bold text-dark mb-0"><?= $total_produk; ?></h3>
                </div>
            </div>
        </div>
        
        <!-- Total Transaksi -->
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card-clean p-4 d-flex flex-row align-items-center h-100">
                <div class="stat-icon bg-icon-green">
                    <i class="fas fa-receipt"></i>
                </div>
                <div>
                    <h6 class="text-muted text-uppercase small fw-bold mb-1">Total Transaksi</h6>
                    <h3 class="fw-bold text-dark mb-0"><?= $total_transaksi; ?></h3>
                </div>
            </div>
        </div>
        
        <!-- Total Pendapatan -->
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card-clean p-4 d-flex flex-row align-items-center h-100">
                <div class="stat-icon bg-icon-orange">
                    <i class="fas fa-coins"></i>
                </div>
                <div>
                    <h6 class="text-muted text-uppercase small fw-bold mb-1">Total Pendapatan</h6>
                    <h3 class="fw-bold text-dark mb-0">Rp <?= number_format($total_pendapatan); ?></h3>
                </div>
            </div>
        </div>
        
        <!-- Stok Rendah -->
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card-clean p-4 d-flex flex-row align-items-center h-100">
                <div class="stat-icon bg-icon-red">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div>
                    <h6 class="text-muted text-uppercase small fw-bold mb-1">Stok Rendah</h6>
                    <h3 class="fw-bold text-dark mb-0"><?= $total_stok_rendah; ?> Produk</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- ================= PENDAPATAN HARI & BULAN ================= -->
    <div class="row mb-4">
        <div class="col-md-6 mb-3">
            <div class="card-clean p-4 d-flex flex-row align-items-center">
                <div class="stat-icon bg-icon-green">
                    <i class="fas fa-calendar-day"></i>
                </div>
                <div>
                    <h6 class="text-muted text-uppercase small fw-bold mb-1">Pendapatan Hari Ini</h6>
                    <h3 class="fw-bold text-dark mb-0">Rp <?= number_format($pendapatan_hari); ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-6 mb-3">
            <div class="card-clean p-4 d-flex flex-row align-items-center">
                <div class="stat-icon bg-icon-blue">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div>
                    <h6 class="text-muted text-uppercase small fw-bold mb-1">Pendapatan Bulan Ini</h6>
                    <h3 class="fw-bold text-dark mb-0">Rp <?= number_format($pendapatan_bulan); ?></h3>
                </div>
            </div>
        </div>
    </div>

    <!-- ================= GRAFIK & PRODUK TERLARIS ================= -->
    <div class="row mb-4">
        <!-- Grafik Penjualan -->
        <div class="col-lg-8 mb-3">
            <div class="card-clean">
                <div class="card-header-clean">
                    <i class="fas fa-chart-bar me-2"></i>Grafik Penjualan 7 Hari Terakhir
                </div>
                <div class="card-body p-3">
                    <canvas id="chartPenjualan" height="120"></canvas>
                </div>
            </div>
        </div>
        
        <!-- Produk Terlaris -->
        <div class="col-lg-4 mb-3">
            <div class="card-clean">
                <div class="card-header-clean">
                    <i class="fas fa-trophy me-2"></i>Produk Terlaris
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        <?php 
                        $rank = 1;
                        while ($t = mysqli_fetch_assoc($q_terlaris)): 
                        ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <span class="badge bg-primary rounded-pill me-2"><?= $rank++; ?></span>
                                <?= htmlspecialchars($t['namaproduk']); ?>
                            </div>
                            <span class="badge bg-success rounded-pill"><?= $t['total_terjual']; ?> terjual</span>
                        </li>
                        <?php endwhile; ?>
                        <?php if ($rank == 1): ?>
                        <li class="list-group-item text-center text-muted py-4">Belum ada data penjualan</li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- ================= TRANSAKSI TERBARU ================= -->
    <div class="card-clean mb-4">
        <div class="card-header-clean d-flex justify-content-between align-items-center">
            <span><i class="fas fa-clock me-2"></i>Transaksi Terbaru</span>
            <a href="riwayat.php" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
        </div>
        <div class="card-body p-3">
            <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tanggal</th>
                        <th>Kasir</th>
                        <th>Metode</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($tr = mysqli_fetch_assoc($q_terbaru)): ?>
                    <tr>
                        <td><span class="badge bg-light text-dark border"><?= formatIdTransaksi($tr['id_transaksi']); ?></span></td>
                        <td><?= date('d/m/Y H:i', strtotime($tr['tanggal'])); ?></td>
                        <td><?= htmlspecialchars($tr['kasir']); ?></td>
                        <td>
                            <?php if ($tr['metode'] == 'Tunai'): ?>
                                <span class="badge bg-success">💵 Tunai</span>
                            <?php else: ?>
                                <span class="badge bg-primary">📱 QRIS</span>
                            <?php endif; ?>
                        </td>
                        <td class="fw-bold text-success">Rp <?= number_format($tr['total']); ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            </div>
        </div>
    </div>

</main>

<!-- Script Grafik -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Grafik Penjualan menggunakan Chart.js
    new Chart(document.getElementById("chartPenjualan"), {
        type: 'bar',
        data: {
            labels: <?= json_encode($labels_grafik); ?>,
            datasets: [{
                label: 'Pendapatan (Rp)',
                data: <?= json_encode($data_grafik); ?>,
                backgroundColor: 'rgba(54, 162, 235, 0.7)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1,
                borderRadius: 6
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false }
            },
            scales: {
                x: { grid: { display: false } },
                y: { beginAtZero: true }
            }
        }
    });
});
</script>

<?php
// Include template footer
include 'templates/footer.php';
?>