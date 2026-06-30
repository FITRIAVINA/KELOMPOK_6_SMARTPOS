<?php
// ================================================================
// SMARTPOS - Data Produk
// File: produk.php
// Deskripsi: Halaman untuk menampilkan daftar semua produk
//            Fitur: tabel produk, pencarian, tombol CRUD, Kelola Kategori
//            Semua role (superadmin & admin) bisa mengakses
// ================================================================

include 'config/koneksi.php';
cekLogin();
onlySuperAdmin();

$page_title  = "Data Produk";
$active_page = "produk";

// ================= QUERY DATA PRODUK =================
$data = mysqli_query($con, "
    SELECT p.*, k.nama_kategori 
    FROM produk p 
    LEFT JOIN kategori k ON p.id_kategori = k.id_kategori 
    ORDER BY p.namaproduk ASC
");

include 'templates/header.php';
include 'templates/sidebar.php';
?>

<main class="container-fluid px-4 py-4">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold text-dark">
            <i class="fas fa-box text-secondary me-2"></i>Data Produk
        </h3>
        <div>
            <button type="button" class="btn btn-primary rounded-pill px-4 me-2" data-bs-toggle="modal" data-bs-target="#modalKategori">
                <i class="fas fa-tags me-2"></i>Kelola Kategori
            </button>
            <a href="tambah_produk.php" class="btn btn-primary rounded-pill px-4">
                <i class="fas fa-plus me-2"></i>Tambah Produk
            </a>
        </div>
    </div>

    <div class="card card-clean">
        <div class="card-header-clean">
            <i class="fas fa-table me-2"></i>Daftar Produk
        </div>
        <div class="card-body">
            <div class="table-responsive">
            <table id="tabelProduk" class="table table-hover w-100">
                <thead>
                    <tr>
                        <th width="5%">No</th>
                        <th>ID Barang</th>
                        <th width="8%">Gambar</th>
                        <th>Nama Produk</th>
                        <th>Kategori</th>
                        <th>Harga</th>
                        <th>Stok</th>
                        <th>Distributor</th>
                        <th width="12%">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $no = 1;
                while ($d = mysqli_fetch_array($data)) {
                    $gambar = $d['gambar'];
                    $img = "images/" . $gambar;
                ?>
                    <tr>
                        <td><?= $no++; ?></td>
                        <td><span class="badge bg-light text-dark border"><?= formatIdBarang($d['idproduk']); ?></span></td>
                        <td>
                            <?php if ($gambar && file_exists($img)): ?>
                                <img src="<?= $img; ?>" width="40" height="40" style="object-fit: cover; border-radius: 5px;">
                            <?php else: ?>
                                <i class="fas fa-image text-muted"></i>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="fw-bold"><?= htmlspecialchars($d['namaproduk']); ?></div>
                            <small class="text-muted"><?= htmlspecialchars($d['deskripsi']); ?></small>
                        </td>
                        <td>
                            <span class="badge bg-info"><?= htmlspecialchars($d['nama_kategori'] ?? 'Tanpa Kategori'); ?></span>
                        </td>
                        <td>Rp <?= number_format($d['harga']); ?></td>
                        <td>
                            <?php if ($d['stok'] <= 5): ?>
                                <span class="badge badge-stock badge-low">Tipis: <?= $d['stok']; ?></span>
                            <?php elseif ($d['stok'] <= 10): ?>
                                <span class="badge badge-stock badge-warning-custom">Rendah: <?= $d['stok']; ?></span>
                            <?php else: ?>
                                <span class="badge badge-stock badge-safe">Aman: <?= $d['stok']; ?></span>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($d['distributor'] ?? '-'); ?></td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="edit_produk.php?id=<?= $d['idproduk']; ?>" class="btn btn-outline-warning">
                                    <i class="fas fa-pen"></i>
                                </a>
                                <a href="hapus_produk.php?id=<?= $d['idproduk']; ?>" class="btn btn-outline-danger" 
                                   onclick="return confirm('Yakin hapus produk ini?')">
                                    <i class="fas fa-trash"></i>
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
</main>

<div class="modal fade" id="modalKategori" tabindex="-1" aria-labelledby="modalKategoriLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="modalKategoriLabel">
                    <i class="fas fa-tags text-warning me-2"></i>Manajemen Kategori Produk
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-5 border-end">
                        <h6 class="fw-bold mb-3 text-secondary">Tambah Kategori Baru</h6>
                        <form action="proses_kategori.php?aksi=tambah" method="POST">
                            <div class="mb-3">
                                <label class="form-label small text-muted">Nama Kategori</label>
                                <input type="text" name="nama_kategori" class="form-control form-control-sm" placeholder="Contoh: Makanan Ringan" required>
                            </div>
                            <button type="submit" class="btn btn-success btn-sm w-100 rounded-pill">
                                <i class="fas fa-save me-1"></i>Simpan Kategori
                            </button>
                        </form>
                    </div>
                    
                    <div class="col-md-7 ps-3">
                        <h6 class="fw-bold mb-3 text-secondary">Daftar Kategori Saat Ini</h6>
                        <div class="table-responsive" style="max-height: 280px; overflow-y: auto;">
                            <table class="table table-sm table-bordered align-middle text-center">
                                <thead class="table-light">
                                    <tr>
                                        <th width="15%">No</th>
                                        <th>Nama Kategori</th>
                                        <th width="20%">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $nomor_kat = 1;
                                    $query_kat = mysqli_query($con, "SELECT * FROM kategori ORDER BY nama_kategori ASC");
                                    if(mysqli_num_rows($query_kat) > 0) {
                                        while($k = mysqli_fetch_array($query_kat)){
                                    ?>
                                    <tr>
                                        <td><?= $nomor_kat++; ?></td>
                                        <td class="text-start ps-3 fw-bold text-dark"><?= htmlspecialchars($k['nama_kategori']); ?></td>
                                        <td>
                                            <a href="proses_kategori.php?aksi=hapus&id=<?= $k['id_kategori']; ?>" 
                                               class="btn btn-sm btn-outline-danger" 
                                               onclick="return confirm('Hapus kategori ini? Produk dengan kategori terkait akan berubah menjadi Tanpa Kategori.')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php 
                                        }
                                    } else {
                                        echo "<tr><td colspan='3' class='text-muted small py-3'>Belum ada data kategori.</td></tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary btn-sm px-3 rounded-pill" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
    // Inisialisasi DataTables untuk tabel produk
    $(document).ready(function() {
        $('#tabelProduk').DataTable({
            "language": {
                "search": "Cari Produk:",
                "lengthMenu": "Tampilkan _MENU_",
                "info": "Hal _PAGE_ dari _PAGES_",
                "paginate": { "next": ">", "previous": "<" }
            },
            "columnDefs": [
                { "className": "text-center", "targets": [0, 1, 2, 4, 5, 6, 7, 8] }
            ]
        });
    });
</script>

<?php include 'templates/footer.php'; ?>