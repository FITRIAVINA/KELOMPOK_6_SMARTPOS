<?php
// ================================================================
// SMARTPOS - Edit Produk
// File: edit_produk.php
// Deskripsi: Form untuk mengedit data produk yang sudah ada
//            Mengambil ID produk dari parameter URL (?id=)
// ================================================================

include 'config/koneksi.php';
cekLogin();
onlySuperAdmin();

$page_title  = "Edit Produk";
$active_page = "produk";

// Ambil ID produk dari URL
$idproduk = isset($_GET['id']) ? (int) $_GET['id'] : 0;

// Cek apakah produk ada
$query = mysqli_query($con, "SELECT * FROM produk WHERE idproduk='$idproduk'");
if (mysqli_num_rows($query) == 0) {
    echo "<script>alert('Produk tidak ditemukan!'); window.location='produk.php';</script>";
    exit;
}
$produk = mysqli_fetch_assoc($query);

// ================= PROSES UPDATE PRODUK =================
if (isset($_POST['update'])) {
    $namaproduk  = mysqli_real_escape_string($con, $_POST['namaproduk']);
    $id_kategori = (int) $_POST['id_kategori'];
    $harga       = (int) $_POST['harga'];
    $stok        = (int) $_POST['stok'];
    $distributor = mysqli_real_escape_string($con, $_POST['distributor']);
    $deskripsi   = mysqli_real_escape_string($con, $_POST['deskripsi']);
    $tgl_exp     = $_POST['tgl_exp'];

    $exp_val = empty($tgl_exp) ? "NULL" : "'$tgl_exp'";

    // Proses upload gambar baru (opsional)
    $gambar_query = "";
    if (isset($_FILES['gambar']['name']) && $_FILES['gambar']['name'] != '') {
        $ext = pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION);
        $nama_file_baru = "produk_" . time() . "." . $ext;
        if (move_uploaded_file($_FILES['gambar']['tmp_name'], "images/" . $nama_file_baru)) {
            $gambar_query = ", gambar='$nama_file_baru'";
        }
    }

    // Update data produk di database
    $update = mysqli_query($con, "
        UPDATE produk SET 
            namaproduk = '$namaproduk',
            id_kategori = '$id_kategori',
            harga = '$harga',
            stok = '$stok',
            distributor = '$distributor',
            deskripsi = '$deskripsi',
            tgl_exp = $exp_val
            $gambar_query
        WHERE idproduk = '$idproduk'
    ");

    if ($update) {
        echo "<script>
            alert('Produk berhasil diupdate!');
            window.location.href='produk.php';
        </script>";
    } else {
        echo "<script>alert('Gagal mengupdate produk!');</script>";
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
            <i class="fas fa-edit text-secondary me-2"></i>Edit Produk
        </h3>
        <a href="produk.php" class="btn btn-outline-secondary rounded-pill px-4">
            <i class="fas fa-arrow-left me-2"></i>Kembali
        </a>
    </div>

    <div class="card card-clean">
        <div class="card-header-clean">
            <i class="fas fa-edit me-2"></i>Form Edit Produk - <?= htmlspecialchars($produk['namaproduk']); ?>
        </div>
        <div class="card-body">
            <form method="post" enctype="multipart/form-data">
                <!-- Nama Produk -->
                <div class="mb-3">
                    <label class="form-label small text-muted fw-bold">Nama Produk <span class="text-danger">*</span></label>
                    <input type="text" name="namaproduk" class="form-control" value="<?= htmlspecialchars($produk['namaproduk']); ?>" required>
                </div>

                <div class="row">
                    <!-- Kategori -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label small text-muted fw-bold">Kategori <span class="text-danger">*</span></label>
                        <select name="id_kategori" class="form-select" required>
                            <option value="">-- Pilih Kategori --</option>
                            <?php while ($k = mysqli_fetch_array($kategori)): ?>
                                <option value="<?= $k['id_kategori']; ?>" <?= ($k['id_kategori'] == $produk['id_kategori']) ? 'selected' : ''; ?>>
                                    <?= htmlspecialchars($k['nama_kategori']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <!-- Harga -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label small text-muted fw-bold">Harga Jual (Rp) <span class="text-danger">*</span></label>
                        <input type="number" name="harga" class="form-control" value="<?= $produk['harga']; ?>" required>
                    </div>
                </div>

                <div class="row">
                    <!-- Stok -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label small text-muted fw-bold">Stok <span class="text-danger">*</span></label>
                        <input type="number" name="stok" class="form-control" value="<?= $produk['stok']; ?>" required>
                    </div>
                    <!-- Distributor -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label small text-muted fw-bold">Distributor</label>
                        <input type="text" name="distributor" class="form-control" value="<?= htmlspecialchars($produk['distributor']); ?>">
                    </div>
                </div>

                <!-- Deskripsi -->
                <div class="mb-3">
                    <label class="form-label small text-muted fw-bold">Deskripsi</label>
                    <input type="text" name="deskripsi" class="form-control" value="<?= htmlspecialchars($produk['deskripsi']); ?>">
                </div>

                <div class="row">
                    <!-- Tanggal Expired -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label small text-muted fw-bold">Tanggal Expired</label>
                        <input type="date" name="tgl_exp" class="form-control" value="<?= $produk['tgl_exp']; ?>">
                    </div>
                    <!-- Gambar -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label small text-muted fw-bold">Ganti Foto (Opsional)</label>
                        <input type="file" name="gambar" class="form-control" accept=".jpg,.jpeg,.png">
                        <?php if ($produk['gambar'] && file_exists("images/" . $produk['gambar'])): ?>
                            <div class="mt-2">
                                <small class="text-muted">Gambar saat ini:</small><br>
                                <img src="images/<?= $produk['gambar']; ?>" width="60" height="60" style="object-fit: cover; border-radius: 5px;">
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <hr>
                <button type="submit" name="update" class="btn btn-warning rounded-pill px-5">
                    <i class="fas fa-save me-2"></i>Update Produk
                </button>
            </form>
        </div>
    </div>
</main>

<?php include 'templates/footer.php'; ?>
