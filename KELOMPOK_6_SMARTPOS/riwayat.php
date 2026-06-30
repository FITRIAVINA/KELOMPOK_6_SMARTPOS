<?php
// ================================================================
// SMARTPOS - Riwayat Transaksi
// File: riwayat.php
// Deskripsi: Halaman untuk melihat riwayat semua transaksi
//            Fitur:
//            - Statistik pemasukan hari ini & bulan ini
//            - Tabel daftar semua transaksi
//            - Detail transaksi (modal)
//            - Filter laporan berdasarkan tanggal
//            - Grafik penjualan mingguan
//            - Tombol cetak struk per transaksi
// ================================================================

include 'config/koneksi.php';
cekLogin();

$page_title  = "Riwayat Transaksi";
$active_page = "riwayat";

// ================= QUERY STATISTIK =================
// Pemasukan hari ini
$hari = mysqli_fetch_array(mysqli_query($con, "
    SELECT COALESCE(SUM(total), 0) as total 
    FROM transaksi 
    WHERE DATE(tanggal) = CURDATE()
"));

// Pemasukan bulan ini
$bulan = mysqli_fetch_array(mysqli_query($con, "
    SELECT COALESCE(SUM(total), 0) as total 
    FROM transaksi 
    WHERE MONTH(tanggal) = MONTH(CURDATE()) 
    AND YEAR(tanggal) = YEAR(CURDATE())
"));

// Data grafik mingguan
$grafik = mysqli_query($con, "
    SELECT DATE(tanggal) as tgl, SUM(total) as total 
    FROM transaksi 
    WHERE tanggal >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
    GROUP BY DATE(tanggal) 
    ORDER BY tgl ASC
");
$tgl = []; $tot = [];
while ($g = mysqli_fetch_array($grafik)) {
    $tgl[] = date('d/m', strtotime($g['tgl']));
    $tot[] = (int)$g['total'];
}

include 'templates/header.php';
include 'templates/sidebar.php';
?>

<main class="container-fluid px-4 py-4">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold text-dark"><i class="fas fa-receipt text-secondary me-2"></i>Riwayat & Laporan</h3>
        <div class="text-muted"><i class="far fa-calendar-alt me-1"></i> <?= date('d F Y'); ?></div>
    </div>

    <!-- ================= FILTER LAPORAN ================= -->
    <div class="card card-clean mb-4" style="height: auto;">
        <div class="card-header-clean">
            <i class="fas fa-filter me-2"></i>Filter Laporan (Mingguan / Bulanan / Tahunan)
        </div>
        <div class="card-body">
            <form method="POST" action="laporan.php" target="_blank" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small fw-bold text-muted">Dari Tanggal</label>
                    <input type="date" name="tgl_mulai" class="form-control" value="<?= date('Y-m-01'); ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-bold text-muted">Sampai Tanggal</label>
                    <input type="date" name="tgl_selesai" class="form-control" value="<?= date('Y-m-d'); ?>" required>
                </div>
                <div class="col-md-4">
                    <button type="submit" name="filter" class="btn btn-primary w-100">
                        <i class="fas fa-print me-2"></i> Cetak Laporan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ================= KARTU STATISTIK ================= -->
    <div class="row mb-4">
        <div class="col-md-6 mb-3">
            <div class="card-clean p-4 d-flex flex-row align-items-center" style="height: auto;">
                <div class="stat-icon bg-icon-green">
                    <i class="fas fa-coins"></i>
                </div>
                <div>
                    <h6 class="text-muted text-uppercase small fw-bold mb-1">Pemasukan Hari Ini</h6>
                    <h3 class="fw-bold text-dark mb-0">Rp <?= number_format($hari['total']); ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-6 mb-3">
            <div class="card-clean p-4 d-flex flex-row align-items-center" style="height: auto;">
                <div class="stat-icon bg-icon-blue">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div>
                    <h6 class="text-muted text-uppercase small fw-bold mb-1">Pemasukan Bulan Ini</h6>
                    <h3 class="fw-bold text-dark mb-0">Rp <?= number_format($bulan['total']); ?></h3>
                </div>
            </div>
        </div>
    </div>

    <!-- ================= TABEL & GRAFIK ================= -->
    <div class="row">
        <!-- Tabel Transaksi -->
        <div class="col-lg-8 mb-4">
            <div class="card-clean" style="height: auto;">
                <div class="card-header-clean d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-list me-2"></i>Daftar Transaksi</span>
                </div>
                <div class="card-body p-3">
                    <div class="table-responsive">
                    <table id="tabelRiwayat" class="table table-hover w-100">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th>ID Order</th>
                                <th>Tanggal</th>
                                <th>Item</th>
                                <th>Total</th>
                                <th>Metode</th>
                                <th width="15%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                        $no = 1;
                        $data = mysqli_query($con, "
                            SELECT t.id_transaksi, t.tanggal, t.total, t.metode,
                                   SUM(dt.qty) as item
                            FROM transaksi t 
                            JOIN detail_transaksi dt ON t.id_transaksi = dt.id_transaksi 
                            GROUP BY t.id_transaksi 
                            ORDER BY t.id_transaksi DESC
                        ");
                        while ($d = mysqli_fetch_array($data)) {
                            $idorder = $d['id_transaksi'];
                        ?>
                            <tr>
                                <td><?= $no++; ?></td>
                                <td><span class="badge bg-light text-dark border"><?= formatIdTransaksi($idorder); ?></span></td>
                                <td><?= date('d/m/Y H:i', strtotime($d['tanggal'])); ?></td>
                                <td><?= $d['item']; ?> pcs</td>
                                <td class="fw-bold text-success">Rp <?= number_format($d['total']); ?></td>
                                <td>
                                    <?php if ($d['metode'] == 'Tunai'): ?>
                                        <span class="badge bg-success">Tunai</span>
                                    <?php else: ?>
                                        <span class="badge bg-primary">QRIS</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalDetail<?= $idorder; ?>">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <a href="cetak.php?id=<?= $idorder; ?>" target="_blank" class="btn btn-outline-secondary">
                                            <i class="fas fa-print"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php } ?>
                        </tbody>
                    </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Grafik Mingguan -->
        <div class="col-lg-4 mb-4">
            <div class="card-clean" style="height: auto;">
                <div class="card-header-clean">
                    <span><i class="fas fa-chart-bar me-2"></i>Grafik Mingguan</span>
                </div>
                <div class="card-body p-3">
                    <canvas id="chartPemasukan" height="250"></canvas>
                </div>
            </div>
        </div>
    </div>

    <?php
    // MODALS DETAIL TRANSAKSI
    mysqli_data_seek($data, 0);
    while ($d = mysqli_fetch_array($data)) {
        $idorder = $d['id_transaksi'];
    ?>
        <div class="modal fade" id="modalDetail<?= $idorder; ?>" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header bg-white border-bottom">
                        <h5 class="modal-title fs-6 fw-bold">Detail Transaksi <?= formatIdTransaksi($idorder); ?></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-0">
                        <table class="table table-striped mb-0">
                            <thead class="bg-light"><tr><th class="ps-4">Produk</th><th class="text-end pe-4">Subtotal</th></tr></thead>
                            <tbody>
                            <?php
                                $cekdetail = mysqli_query($con, "
                                    SELECT p.namaproduk, dt.harga_saat_itu as harga, dt.qty 
                                    FROM detail_transaksi dt 
                                    JOIN produk p ON dt.idproduk = p.idproduk 
                                    WHERE dt.id_transaksi = '$idorder'
                                ");
                                while ($det = mysqli_fetch_array($cekdetail)):
                            ?>
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-bold text-dark"><?= htmlspecialchars($det['namaproduk']); ?></div>
                                        <small class="text-muted"><?= $det['qty']; ?> x <?= number_format($det['harga']); ?></small>
                                    </td>
                                    <td class="text-end pe-4 align-middle fw-bold"><?= number_format($det['harga'] * $det['qty']); ?></td>
                                </tr>
                            <?php endwhile; ?>
                            </tbody>
                        </table>
                        <div class="p-3 text-end bg-light border-top">
                            <span class="text-muted me-2">Total Bayar:</span>
                            <span class="h4 fw-bold text-success mb-0">Rp <?= number_format($d['total']); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php } ?>

</main>

<?php include 'templates/footer.php'; ?>

<script>
    // DataTables
    $(document).ready(function() {
        $('#tabelRiwayat').DataTable({
            "language": {
                "search": "Cari:",
                "lengthMenu": "Tampilkan _MENU_",
                "info": "Hal _PAGE_ dari _PAGES_",
                "paginate": { "next": ">", "previous": "<" }
            }
        });
    });

    // Chart.js - Grafik Pemasukan (setelah library dimuat dari footer)
    document.addEventListener('DOMContentLoaded', function() {
        var ctx = document.getElementById('chartPemasukan');
        if (ctx) {
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: <?= json_encode($tgl); ?>,
                    datasets: [{
                        label: 'Omset',
                        data: <?= json_encode($tot); ?>,
                        backgroundColor: 'rgba(54, 162, 235, 0.7)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1,
                        borderRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { grid: { display: false } },
                        y: { beginAtZero: true }
                    }
                }
            });
        }
    });
</script>