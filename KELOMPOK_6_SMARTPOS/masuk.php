<?php
// ================================================================
// SMARTPOS - Barang Masuk
// File: masuk.php
// Deskripsi: Halaman untuk mengelola riwayat barang masuk
//            Fitur:
//            - Tambah produk baru + otomatis catat barang masuk
//            - Edit data barang masuk (revisi qty, penerima, gambar)
//            - Hapus data barang masuk (stok dikurangi otomatis)
// ================================================================

include 'config/koneksi.php';
cekLogin();
onlySuperAdmin();

$page_title  = "Barang Masuk";
$active_page = "masuk";

// --- LOGIKA 1: TAMBAH PRODUK BARU ---
if (isset($_POST['add_produk'])) {
    $namaproduk  = mysqli_real_escape_string($con, $_POST['namaproduk']);
    $deskripsi   = mysqli_real_escape_string($con, $_POST['deskripsi']);
    $harga       = (int) $_POST['harga'];
    $stok        = (int) $_POST['stok'];
    $distributor = mysqli_real_escape_string($con, $_POST['distributor']);
    $penerima    = mysqli_real_escape_string($con, $_POST['penerima']);
    $tgl_exp     = $_POST['tgl_exp'];
    $id_kategori = (int) $_POST['id_kategori'];

    $exp_val = empty($tgl_exp) ? "NULL" : "'$tgl_exp'";

    // Upload gambar produk
    $gambar = null;
    if (isset($_FILES['gambar']['name']) && $_FILES['gambar']['name'] != '') {
        $ext = pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION);
        $nama_file_baru = "produk_" . time() . "." . $ext;
        if (move_uploaded_file($_FILES['gambar']['tmp_name'], "images/" . $nama_file_baru)) {
            $gambar = $nama_file_baru;
        }
    }

    // Insert produk baru ke database
    $insert_produk = mysqli_query($con, "
        INSERT INTO produk (namaproduk, id_kategori, deskripsi, harga, stok, distributor, gambar, tgl_exp) 
        VALUES ('$namaproduk', '$id_kategori', '$deskripsi', '$harga', '$stok', '$distributor', '$gambar', $exp_val)
    ");
    
    if ($insert_produk) {
        $id_baru = mysqli_insert_id($con);
        // Catat di tabel masuk
        mysqli_query($con, "
            INSERT INTO masuk (idproduk, qty, penerima, tanggalmasuk) 
            VALUES ('$id_baru', '$stok', '$penerima', NOW())
        ");
        header("location: masuk.php");
        exit;
    }
}

// --- LOGIKA 2: EDIT DATA MASUK ---
if (isset($_POST['edit_masuk'])) {
    $idmasuk  = (int) $_POST['idmasuk'];
    $idproduk = (int) $_POST['idproduk'];
    $qty_baru = (int) $_POST['qty'];
    $penerima = mysqli_real_escape_string($con, $_POST['penerima']);

    // Upload gambar baru (opsional)
    if (isset($_FILES['gambar_baru']['name']) && $_FILES['gambar_baru']['name'] != '') {
        $ext = pathinfo($_FILES['gambar_baru']['name'], PATHINFO_EXTENSION);
        $nama_file_baru = "produk_" . time() . "." . $ext;
        if (move_uploaded_file($_FILES['gambar_baru']['tmp_name'], "images/" . $nama_file_baru)) {
            mysqli_query($con, "UPDATE produk SET gambar='$nama_file_baru' WHERE idproduk='$idproduk'");
        }
    }

    // Hitung selisih qty untuk update stok
    $cek_data  = mysqli_query($con, "SELECT * FROM masuk WHERE idmasuk='$idmasuk'");
    $data_lama = mysqli_fetch_array($cek_data);
    $qty_lama  = $data_lama['qty'];
    $selisih   = $qty_baru - $qty_lama;

    // Update data masuk
    $update_masuk = mysqli_query($con, "UPDATE masuk SET qty='$qty_baru', penerima='$penerima' WHERE idmasuk='$idmasuk'");

    if ($update_masuk) {
        // Update stok produk berdasarkan selisih
        mysqli_query($con, "UPDATE produk SET stok = stok + $selisih WHERE idproduk='$idproduk'");
        header("location: masuk.php");
        exit;
    }
}

// --- LOGIKA 3: HAPUS DATA MASUK ---
if (isset($_POST['hapus_masuk'])) {
    $idmasuk  = (int) $_POST['idmasuk'];
    $idproduk = (int) $_POST['idproduk'];

    // Ambil qty lama untuk mengurangi stok
    $cek_data  = mysqli_query($con, "SELECT * FROM masuk WHERE idmasuk='$idmasuk'");
    $data_lama = mysqli_fetch_array($cek_data);
    $qty_lama  = $data_lama['qty'];

    $hapus = mysqli_query($con, "DELETE FROM masuk WHERE idmasuk='$idmasuk'");

    if ($hapus) {
        // Kurangi stok produk
        mysqli_query($con, "UPDATE produk SET stok = stok - $qty_lama WHERE idproduk='$idproduk'");
        header("location: masuk.php");
        exit;
    }
}

// Query kategori untuk dropdown
$kategori = mysqli_query($con, "SELECT * FROM kategori ORDER BY nama_kategori ASC");

include 'templates/header.php';
include 'templates/sidebar.php';
?>

<main class="container-fluid px-4 py-4">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold text-dark"><i class="fas fa-arrow-down text-secondary me-2"></i>Barang Masuk</h3>
        <button class="btn btn-primary rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#modalTambahProduk">
            <i class="fas fa-plus me-2"></i>Tambah Produk Baru
        </button>
    </div>

    <div class="card card-clean">
        <div class="card-header-clean"><i class="fas fa-table me-2"></i>Riwayat Input Barang</div>
        <div class="card-body">
            <div class="table-responsive">
            <table id="tabelMasuk" class="table table-hover w-100">
                <thead>
                    <tr>
                        <th width="10%">ID Barang</th>
                        <th width="8%">Gambar</th>
                        <th>Nama Produk</th>
                        <th>Distributor</th>
                        <th>Penerima</th>
                        <th>Jumlah</th>
                        <th>Tanggal Masuk</th>
                        <th width="10%">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $data = mysqli_query($con, "
                    SELECT m.*, p.namaproduk, p.distributor, p.gambar
                    FROM masuk m
                    JOIN produk p ON m.idproduk = p.idproduk
                    ORDER BY m.idmasuk DESC
                ");
                while ($d = mysqli_fetch_array($data)) {
                    $idmasuk    = $d['idmasuk'];
                    $idproduk   = $d['idproduk'];
                    $namaproduk = $d['namaproduk'];
                    $qty        = $d['qty'];
                    $penerima   = $d['penerima'];
                    $tanggal    = date('d M Y H:i', strtotime($d['tanggalmasuk']));
                    $gambar     = $d['gambar'];
                ?>
                    <tr>
                        <td class="fw-bold text-secondary"><span class="badge bg-light text-dark border"><?= formatIdBarang($idproduk); ?></span></td>
                        <td>
                            <?php if ($gambar && file_exists("images/" . $gambar)): ?>
                                <img src="images/<?= $gambar; ?>" width="40" height="40" style="object-fit: cover; border-radius: 5px;">
                            <?php else: ?>
                                <i class="fas fa-image text-muted"></i>
                            <?php endif; ?>
                        </td>
                        <td class="fw-bold"><?= htmlspecialchars($namaproduk); ?></td>
                        <td><?= htmlspecialchars($d['distributor'] ?? '-'); ?></td>
                        <td><span class="badge bg-secondary fw-normal"><?= htmlspecialchars($penerima); ?></span></td>
                        <td><span class="badge bg-light text-dark border px-3"><?= $qty; ?></span></td>
                        <td class="text-muted small"><?= $tanggal; ?></td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-outline-warning" data-bs-toggle="modal" data-bs-target="#modalEdit<?= $idmasuk; ?>"><i class="fas fa-pen"></i></button>
                                <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#modalDelete<?= $idmasuk; ?>"><i class="fas fa-trash"></i></button>
                            </div>
                        </td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
            </div>
        </div>
    </div>
    
    <?php
    // MODALS LOOP - Edit dan Hapus
    mysqli_data_seek($data, 0);
    while ($d = mysqli_fetch_array($data)) {
        $idmasuk    = $d['idmasuk'];
        $idproduk   = $d['idproduk'];
        $namaproduk = $d['namaproduk'];
        $qty        = $d['qty'];
        $penerima   = $d['penerima'];
        $gambar     = $d['gambar'];
    ?>
        <!-- Modal Edit -->
        <div class="modal fade" id="modalEdit<?= $idmasuk; ?>">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form method="post" enctype="multipart/form-data">
                        <div class="modal-header">
                            <h5 class="modal-title fw-bold">Edit Barang Masuk</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="idmasuk" value="<?= $idmasuk; ?>">
                            <input type="hidden" name="idproduk" value="<?= $idproduk; ?>">
                            <div class="mb-3">
                                <label class="form-label small text-muted">ID Barang</label>
                                <input type="text" class="form-control" value="<?= $idproduk; ?>" disabled>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small text-muted">Nama Produk</label>
                                <input type="text" class="form-control" value="<?= htmlspecialchars($namaproduk); ?>" disabled>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label small text-muted">Jumlah Masuk (Revisi)</label>
                                    <input type="number" name="qty" class="form-control" value="<?= $qty; ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label small text-muted">Penerima Barang</label>
                                    <input type="text" name="penerima" class="form-control" value="<?= htmlspecialchars($penerima); ?>" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small text-muted">Ganti Gambar (Opsional)</label>
                                <input type="file" name="gambar_baru" class="form-control" accept=".jpg, .jpeg, .png">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" name="edit_masuk" class="btn btn-warning w-100 rounded-pill">Simpan Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal Hapus -->
        <div class="modal fade" id="modalDelete<?= $idmasuk; ?>">
            <div class="modal-dialog modal-sm">
                <div class="modal-content">
                    <form method="post">
                        <div class="modal-header bg-danger text-white">
                            <h5 class="modal-title">Hapus Data?</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body text-center">
                            <input type="hidden" name="idmasuk" value="<?= $idmasuk; ?>">
                            <input type="hidden" name="idproduk" value="<?= $idproduk; ?>">
                            <i class="fas fa-exclamation-circle fa-3x text-danger mb-3"></i>
                            <p>Hapus riwayat masuk untuk <strong><?= htmlspecialchars($namaproduk); ?></strong>?<br>
                            <small class="text-danger">Stok akan berkurang <?= $qty; ?> unit.</small></p>
                        </div>
                        <div class="modal-footer justify-content-center">
                            <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" name="hapus_masuk" class="btn btn-danger rounded-pill px-4">Hapus</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    <?php } ?>

</main>

<!-- Modal Tambah Produk Baru -->
<div class="modal fade" id="modalTambahProduk" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Tambah Produk Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small text-muted">Nama Produk</label>
                        <input type="text" name="namaproduk" class="form-control" placeholder="Contoh: Kopi Susu" required>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label small text-muted">Kategori</label>
                            <select name="id_kategori" class="form-select" required>
                                <option value="">-- Pilih --</option>
                                <?php 
                                mysqli_data_seek($kategori, 0);
                                while ($k = mysqli_fetch_array($kategori)): ?>
                                    <option value="<?= $k['id_kategori']; ?>"><?= htmlspecialchars($k['nama_kategori']); ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label small text-muted">Harga Jual</label>
                            <input type="number" name="harga" class="form-control" placeholder="0" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label small text-muted">Stok Awal</label>
                            <input type="number" name="stok" class="form-control" placeholder="0" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label small text-muted">Distributor</label>
                            <input type="text" name="distributor" class="form-control" placeholder="Nama Supplier" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label small text-muted">Penerima Barang</label>
                            <input type="text" name="penerima" class="form-control" placeholder="Nama Staff" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label small text-muted">Deskripsi</label>
                            <input type="text" name="deskripsi" class="form-control" placeholder="Keterangan singkat">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label small text-muted">Tanggal Expired</label>
                            <input type="date" name="tgl_exp" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label small text-muted">Foto Produk</label>
                            <input type="file" name="gambar" class="form-control" accept=".jpg, .jpeg, .png">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" name="add_produk" class="btn btn-primary w-100 rounded-pill">Simpan Produk</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('#tabelMasuk').DataTable({
            "language": {
                "search": "Cari Data:",
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