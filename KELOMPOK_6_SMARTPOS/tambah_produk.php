<?php
// ================================================================
// SMARTPOS - Tambah Produk Baru
// File: tambah_produk.php
// Deskripsi: Form untuk menambahkan produk baru ke database
//            Data yang diinput: nama, kategori, harga, stok,
//            distributor, deskripsi, tanggal expired, gambar
// ================================================================

include 'config/koneksi.php';
cekLogin();
onlySuperAdmin();

$page_title  = "Tambah Produk";
$active_page = "produk";

// ================= PROSES TAMBAH PRODUK =================
if (isset($_POST['simpan'])) {
    // Ambil data dari form
    $namaproduk  = mysqli_real_escape_string($con, $_POST['namaproduk']);
    $id_kategori = (int) $_POST['id_kategori'];
    $harga       = (int) $_POST['harga'];
    $stok        = (int) $_POST['stok'];
    $distributor = mysqli_real_escape_string($con, $_POST['distributor']);
    $deskripsi   = mysqli_real_escape_string($con, $_POST['deskripsi']);
    $tgl_exp     = $_POST['tgl_exp'];

    // Atur nilai expired (bisa kosong)
    $exp_val = empty($tgl_exp) ? "NULL" : "'$tgl_exp'";

    // Proses upload gambar (opsional)
    $gambar = null;
    if (isset($_FILES['gambar']['name']) && $_FILES['gambar']['name'] != '') {
        $ext = pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION);
        $nama_file_baru = "produk_" . time() . "." . $ext;
        if (move_uploaded_file($_FILES['gambar']['tmp_name'], "images/" . $nama_file_baru)) {
            $gambar = $nama_file_baru;
        }
    }

    // Insert ke database
    $query = mysqli_query($con, "
        INSERT INTO produk (namaproduk, id_kategori, harga, stok, distributor, deskripsi, tgl_exp, gambar) 
        VALUES ('$namaproduk', '$id_kategori', '$harga', '$stok', '$distributor', '$deskripsi', $exp_val, '$gambar')
    ");

    if ($query) {
        // Catat juga di tabel masuk (barang masuk)
        $id_baru = mysqli_insert_id($con);
        $penerima = $_SESSION['nama_lengkap'];
        mysqli_query($con, "
            INSERT INTO masuk (idproduk, qty, penerima, tanggalmasuk) 
            VALUES ('$id_baru', '$stok', '$penerima', NOW())
        ");

        echo "<script>
            alert('Produk berhasil ditambahkan!');
            window.location.href='produk.php';
        </script>";
    } else {
        echo "<script>alert('Gagal menambahkan produk!');</script>";
    }
}

// Query kategori untuk dropdown
$kategori = mysqli_query($con, "SELECT * FROM kategori ORDER BY nama_kategori ASC");

include 'templates/header.php';
include 'templates/sidebar.php';
?>

<main class="container-fluid px-4 py-4">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold text-dark">
            <i class="fas fa-plus-circle text-secondary me-2"></i>Tambah Produk Baru
        </h3>
        <a href="produk.php" class="btn btn-outline-secondary rounded-pill px-4">
            <i class="fas fa-arrow-left me-2"></i>Kembali
        </a>
    </div>

    <div class="card card-clean">
        <div class="card-header-clean">
            <i class="fas fa-edit me-2"></i>Form Tambah Produk
        </div>
        <div class="card-body">
            <form method="post" enctype="multipart/form-data">
                <!-- Nama Produk -->
                <div class="mb-3">
                    <label class="form-label small text-muted fw-bold">Nama Produk <span class="text-danger">*</span></label>
                    <input type="text" name="namaproduk" class="form-control" placeholder="Contoh: Indomie Goreng" required>
                </div>

                <div class="row">
                    <!-- Kategori -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label small text-muted fw-bold">Kategori <span class="text-danger">*</span></label>
                        <select name="id_kategori" class="form-select" required>
                            <option value="">-- Pilih Kategori --</option>
                            <?php while ($k = mysqli_fetch_array($kategori)): ?>
                                <option value="<?= $k['id_kategori']; ?>"><?= htmlspecialchars($k['nama_kategori']); ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <!-- Harga -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label small text-muted fw-bold">Harga Jual (Rp) <span class="text-danger">*</span></label>
                        <input type="number" name="harga" class="form-control" placeholder="0" required>
                    </div>
                </div>

                <div class="row">
                    <!-- Stok Awal -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label small text-muted fw-bold">Stok Awal <span class="text-danger">*</span></label>
                        <input type="number" name="stok" class="form-control" placeholder="0" required>
                    </div>
                    <!-- Distributor -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label small text-muted fw-bold">Distributor</label>
                        <input type="text" name="distributor" class="form-control" placeholder="Nama Supplier">
                    </div>
                </div>

                <!-- Deskripsi -->
                <div class="mb-3">
                    <label class="form-label small text-muted fw-bold">Deskripsi</label>
                    <input type="text" name="deskripsi" class="form-control" placeholder="Keterangan singkat produk">
                </div>

                <div class="row">
                    <!-- Tanggal Expired -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label small text-muted fw-bold">Tanggal Expired</label>
                        <input type="date" name="tgl_exp" class="form-control">
                    </div>
                    <!-- Gambar -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label small text-muted fw-bold">Foto Produk</label>
                        <input type="file" name="gambar" class="form-control" accept=".jpg,.jpeg,.png">
                    </div>
                </div>

                <hr>
                <button type="submit" name="simpan" class="btn btn-primary rounded-pill px-5">
                    <i class="fas fa-save me-2"></i>Simpan Produk
                </button>
            </form>
        </div>
    </div>
</main>

<?php include 'templates/footer.php'; ?>
