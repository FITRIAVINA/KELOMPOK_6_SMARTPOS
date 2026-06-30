<?php
// ================================================================
// SMARTPOS - Stok Barang
// File: stok.php
// Deskripsi: Halaman untuk melihat stok semua produk
//            Menampilkan status stok (Aman/Tipis) dan expired
//            Semua role bisa mengakses halaman ini
// ================================================================

include 'config/koneksi.php';
cekLogin();
onlySuperAdmin();

$page_title  = "Stok Barang";
$active_page = "stok";

include 'templates/header.php';
include 'templates/sidebar.php';
?>

<main class="container-fluid px-4 py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold text-dark"><i class="fas fa-boxes text-secondary me-2"></i>Manajemen Stok</h3>
    </div>

    <div class="card card-clean">
        <div class="card-header-clean">
            <i class="fas fa-table me-2"></i>Daftar Stok Produk
        </div>
        <div class="card-body">
            <div class="table-responsive">
            <table id="tabelStok" class="table table-hover w-100">
                <thead>
                    <tr>
                        <th width="5%">No</th>
                        <th>ID Barang</th>
                        <th>Nama Produk</th>
                        <th>Kategori</th>
                        <th>Harga</th>
                        <th>Distributor</th>
                        <th>Expired</th>
                        <th>Stok</th>
                    </tr>
                </thead>
                <tbody>

                <?php
                $no = 1;
                // Join dengan kategori untuk menampilkan nama kategori
                $data = mysqli_query($con, "
                    SELECT p.*, k.nama_kategori 
                    FROM produk p 
                    LEFT JOIN kategori k ON p.id_kategori = k.id_kategori 
                    ORDER BY p.namaproduk ASC
                ");
                
                // Tanggal untuk logika expired
                $tgl_sekarang = date('Y-m-d');
                $tgl_warning  = date('Y-m-d', strtotime('+1 year'));

                while ($d = mysqli_fetch_array($data)) {
                    $namaproduk  = $d['namaproduk'];
                    $deskripsi   = $d['deskripsi'];
                    $harga       = $d['harga'];
                    $distributor = $d['distributor'];
                    $stok        = $d['stok'];
                    $tgl_exp     = $d['tgl_exp'];

                    // --- LOGIKA TAMPILAN EXPIRED ---
                    if ($tgl_exp == NULL || $tgl_exp == '0000-00-00') {
                        $show_exp = "<span class='text-muted'>-</span>";
                    } else {
                        $tgl_formatted = date('d M Y', strtotime($tgl_exp));
                        
                        if ($tgl_exp < $tgl_sekarang) {
                            // Sudah Expired -> Merah Solid
                            $show_exp = "<span class='badge badge-stock badge-expired'>Expired: $tgl_formatted</span>";
                        } elseif ($tgl_exp <= $tgl_warning) {
                            // Kurang dari 1 Tahun -> Kuning
                            $show_exp = "<span class='badge badge-stock badge-warning-custom text-dark'>Hampir Exp: $tgl_formatted</span>";
                        } else {
                            // Masih Lama -> Hijau
                            $show_exp = "<span class='badge badge-stock badge-safe'>Aman: $tgl_formatted</span>";
                        }
                    }
                ?>
                    <tr>
                        <td><?= $no++; ?></td>
                        <td><span class="badge bg-light text-dark border"><?= formatIdBarang($d['idproduk']); ?></span></td>
                        <td>
                            <div class="fw-bold text-dark"><?= htmlspecialchars($namaproduk); ?></div>
                            <small class="text-muted d-block"><?= htmlspecialchars($deskripsi); ?></small>
                        </td>
                        <td><span class="badge bg-info"><?= htmlspecialchars($d['nama_kategori'] ?? '-'); ?></span></td>
                        <td>Rp <?= number_format($harga); ?></td>
                        <td><?= htmlspecialchars($distributor ?? '-'); ?></td>
                        <td><?= $show_exp; ?></td>
                        <td>
                            <?php if ($stok <= 5): ?>
                                <span class="badge badge-stock badge-low">Tipis: <?= $stok; ?></span>
                            <?php elseif ($stok <= 10): ?>
                                <span class="badge badge-stock badge-warning-custom">Rendah: <?= $stok; ?></span>
                            <?php else: ?>
                                <span class="badge badge-stock badge-safe">Aman: <?= $stok; ?></span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
            </div>
        </div>
    </div>

</main>

<script>
    $(document).ready(function() {
        $('#tabelStok').DataTable({
            "language": {
                "search": "Cari Produk:",
                "lengthMenu": "Tampilkan _MENU_",
                "info": "Hal _PAGE_ dari _PAGES_",
                "paginate": { "next": ">", "previous": "<" }
            },
            "columnDefs": [
                { "className": "text-center", "targets": "_all" }
            ]
        });
    });
</script>

<?php include 'templates/footer.php'; ?>