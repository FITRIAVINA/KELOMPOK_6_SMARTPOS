<?php
// ================================================================
// SMARTPOS - Halaman Transaksi (Order / Kasir)
// File: transaksi.php
// Deskripsi: Halaman utama untuk melakukan transaksi penjualan
//            Fitur:
//            - Tampilkan daftar produk yang tersedia
//            - Tambah produk ke keranjang belanja
//            - Update quantity (tambah/kurang)
//            - Hapus item dari keranjang
//            - Hitung subtotal & total otomatis
//            - Pilih metode pembayaran
//            - Input uang bayar & hitung kembalian otomatis
//            - Proses checkout & cetak struk
// ================================================================

// Include koneksi database
include 'config/koneksi.php';

// Cek login
cekLogin();

// Hanya Admin yang bisa melakukan transaksi
onlyAdmin();

// Set judul halaman dan menu aktif
$page_title  = "Transaksi";
$active_page = "transaksi";

// Ambil ID user yang sedang login
$id_user = $_SESSION['id_user'];

// ================= INISIALISASI KERANJANG =================
// Keranjang belanja disimpan di session
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// ================= RESET KERANJANG =================
if (isset($_POST['reset_cart'])) {
    $_SESSION['cart'] = [];
    header("location: transaksi.php");
    exit;
}

// ================= TAMBAH ITEM KE KERANJANG =================
if (isset($_POST['addcart'])) {
    $idproduk = (int) $_POST['idproduk'];
    // Cek stok produk
    $cek = mysqli_query($con, "SELECT stok FROM produk WHERE idproduk='$idproduk'");
    $data = mysqli_fetch_array($cek);
    
    if (isset($_SESSION['cart'][$idproduk])) {
        // Jika sudah ada di keranjang, tambah qty (cek stok)
        if ($_SESSION['cart'][$idproduk] < $data['stok']) {
            $_SESSION['cart'][$idproduk]++;
        }
    } else {
        // Jika belum ada, tambah baru (cek stok > 0)
        if ($data['stok'] > 0) {
            $_SESSION['cart'][$idproduk] = 1;
        }
    }
    header("location: transaksi.php");
    exit;
}

// ================= KURANGI ITEM DARI KERANJANG =================
if (isset($_POST['minuscart'])) {
    $idproduk = (int) $_POST['idproduk'];
    if (isset($_SESSION['cart'][$idproduk])) {
        $_SESSION['cart'][$idproduk]--;
        // Jika qty = 0, hapus dari keranjang
        if ($_SESSION['cart'][$idproduk] <= 0) {
            unset($_SESSION['cart'][$idproduk]);
        }
    }
    header("location: transaksi.php");
    exit;
}

// ================= HAPUS ITEM DARI KERANJANG =================
if (isset($_POST['removecart'])) {
    $idproduk = (int) $_POST['idproduk'];
    unset($_SESSION['cart'][$idproduk]);
    header("location: transaksi.php");
    exit;
}

// ================= PROSES CHECKOUT (BAYAR) =================
if (isset($_POST['checkout'])) {
    $bayar         = (int) $_POST['bayar'];
    $metode        = $_POST['metode_final'];
    $total_belanja = (int) $_POST['total_belanja'];

    // Validasi: uang bayar harus cukup
    if ($bayar < $total_belanja) {
        echo "<script>
            alert('Uang pembayaran kurang!'); 
            window.location='transaksi.php';
        </script>";
        exit;
    }

    // Hitung kembalian
    $kembalian = $bayar - $total_belanja;

    // 1. Simpan data transaksi (header)
    mysqli_query($con, "
        INSERT INTO transaksi (tanggal, id_user, total, bayar, kembalian, metode) 
        VALUES (NOW(), '$id_user', '$total_belanja', '$bayar', '$kembalian', '$metode')
    ");
    $id_transaksi = mysqli_insert_id($con);

    // 2. Simpan detail transaksi & kurangi stok
    foreach ($_SESSION['cart'] as $idproduk => $qty) {
        // Ambil data produk
        $produk = mysqli_query($con, "SELECT stok, harga FROM produk WHERE idproduk='$idproduk'");
        $p = mysqli_fetch_array($produk);

        if ($p['stok'] >= $qty) {
            // Simpan detail dengan harga saat transaksi
            mysqli_query($con, "
                INSERT INTO detail_transaksi (id_transaksi, idproduk, qty, harga_saat_itu) 
                VALUES ('$id_transaksi', '$idproduk', '$qty', '{$p['harga']}')
            ");
            // Kurangi stok produk secara otomatis
            mysqli_query($con, "UPDATE produk SET stok = stok - $qty WHERE idproduk='$idproduk'");
        }
    }

    // 3. Simpan data untuk struk (di session)
    $_SESSION['last_order'] = $id_transaksi;
    $_SESSION['bayar_struk'] = $bayar;
    $_SESSION['metode_struk'] = $metode;

    // 4. Kosongkan keranjang
    $_SESSION['cart'] = [];

    // 5. Redirect dengan pesan sukses
    header("Location: transaksi.php?success=true&id=" . $id_transaksi);
    exit;
}

// ================= PENCARIAN PRODUK =================
$search = isset($_GET['search']) ? mysqli_real_escape_string($con, $_GET['search']) : '';
$where_search = '';
if (!empty($search)) {
    $where_search = "AND (p.namaproduk LIKE '%$search%' OR k.nama_kategori LIKE '%$search%')";
}

// Include template header dan sidebar
include 'templates/header.php';
include 'templates/sidebar.php';
?>

<!-- CSS tambahan untuk halaman transaksi -->
<style>
    .product-card { transition: 0.2s; border: none; border-radius: 10px; overflow: hidden; }
    .product-card:hover { transform: translateY(-3px); box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
    .icon-box { height: 120px; background: #e9ecef; display: flex; align-items: center; justify-content: center; font-size: 3rem; color: #6c757d; }
    .icon-box img { width: 100%; height: 100%; object-fit: cover; }
    .cart-area { position: sticky; top: 70px; max-height: calc(100vh - 90px); overflow-y: auto; }
    .qty-btn { width: 28px; height: 28px; padding: 0; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; }
</style>

<main class="container-fluid px-4">
    
    <?php if (isset($_GET['success'])): ?>
    <!-- ================= PESAN SUKSES TRANSAKSI ================= -->
    <div class="py-5 text-center">
        <div class="card shadow col-md-6 mx-auto border-top border-success border-5">
            <div class="card-body p-5">
                <i class="fas fa-check-circle fa-4x text-success mb-3"></i>
                <h2>Transaksi Berhasil!</h2>
                <p class="text-muted">ID Transaksi: <?= formatIdTransaksi(htmlspecialchars($_GET['id'])); ?></p>
                <div class="d-grid gap-2 d-md-block">
                    <a href="cetak.php?id=<?= htmlspecialchars($_GET['id']); ?>" target="_blank" class="btn btn-primary px-4">
                        <i class="fas fa-print"></i> Cetak Struk
                    </a>
                    <a href="transaksi.php" class="btn btn-outline-secondary px-4">Transaksi Baru</a>
                </div>
            </div>
        </div>
    </div>
    
    <?php else: ?>
    <!-- ================= HALAMAN TRANSAKSI ================= -->
    
    <div class="d-flex justify-content-between align-items-center mt-4 mb-4">
        <h1><i class="fas fa-cash-register text-secondary"></i> Transaksi Baru</h1>
        <span><?= date('d M Y'); ?></span>
    </div>

    <!-- Pencarian Produk -->
    <div class="mb-3">
        <form method="GET" class="d-flex">
            <input type="text" name="search" class="form-control me-2" placeholder="🔍 Cari produk atau kategori..." value="<?= htmlspecialchars($search); ?>">
            <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i></button>
            <?php if (!empty($search)): ?>
                <a href="transaksi.php" class="btn btn-outline-secondary ms-1"><i class="fas fa-times"></i></a>
            <?php endif; ?>
        </form>
    </div>

    <div class="row">
        <!-- ================= DAFTAR PRODUK (KIRI) ================= -->
        <div class="col-md-8">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body bg-light">
                    <div class="row g-3">
                        <?php
                        // Query produk yang stoknya > 0, join dengan kategori
                        $produk = mysqli_query($con, "
                            SELECT p.*, k.nama_kategori 
                            FROM produk p 
                            LEFT JOIN kategori k ON p.id_kategori = k.id_kategori 
                            WHERE p.stok > 0 $where_search
                            ORDER BY p.namaproduk ASC
                        ");
                        
                        if (mysqli_num_rows($produk) == 0) {
                            echo '<div class="col-12 text-center py-5 text-muted">
                                <i class="fas fa-search fa-3x mb-3 opacity-50"></i>
                                <p>Tidak ada produk ditemukan.</p>
                            </div>';
                        }
                        
                        while ($p = mysqli_fetch_array($produk)) {
                            $qty_in = $_SESSION['cart'][$p['idproduk']] ?? 0;
                            $img = "images/" . $p['gambar'];
                        ?>
                        <div class="col-6 col-md-4 col-lg-3">
                            <div class="card product-card h-100 shadow-sm">
                                <div class="icon-box">
                                    <?php if ($p['gambar'] && file_exists($img)): ?>
                                        <img src="<?= $img ?>">
                                    <?php else: ?>
                                        <i class="fas fa-box"></i>
                                    <?php endif; ?>
                                </div>
                                <div class="card-body p-2 d-flex flex-column">
                                    <h6 class="text-truncate small fw-bold mb-0"><?= htmlspecialchars($p['namaproduk']); ?></h6>
                                    <small class="text-muted" style="font-size:10px"><?= htmlspecialchars($p['nama_kategori'] ?? '-'); ?></small>
                                    <div class="text-success fw-bold small mb-2">Rp <?= number_format($p['harga']); ?></div>
                                    <div class="mt-auto d-flex justify-content-between align-items-center bg-white border rounded p-1">
                                        <form method="post" class="d-inline">
                                            <input type="hidden" name="idproduk" value="<?= $p['idproduk']; ?>">
                                            <button name="minuscart" class="btn btn-outline-danger qty-btn" <?= $qty_in == 0 ? 'disabled' : ''; ?>>
                                                <i class="fas fa-minus small"></i>
                                            </button>
                                        </form>
                                        <span class="fw-bold small"><?= $qty_in; ?></span>
                                        <form method="post" class="d-inline">
                                            <input type="hidden" name="idproduk" value="<?= $p['idproduk']; ?>">
                                            <button name="addcart" class="btn btn-outline-primary qty-btn">
                                                <i class="fas fa-plus small"></i>
                                            </button>
                                        </form>
                                    </div>
                                    <div class="text-center mt-1">
                                        <small class="text-muted" style="font-size:9px">Stok: <?= $p['stok']; ?></small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- ================= KERANJANG BELANJA (KANAN) ================= -->
        <div class="col-md-4">
            <div class="card shadow border-0 cart-area">
                <div class="card-header bg-primary text-white">
                    <i class="fas fa-shopping-cart me-1"></i> Keranjang Belanja
                </div>
                
                <div class="card-body p-0">
                    <?php if (empty($_SESSION['cart'])): ?>
                        <div class="text-center py-5 text-muted">
                            <i class="fas fa-basket-shopping fa-3x mb-2 opacity-50"></i>
                            <p class="small">Keranjang kosong</p>
                        </div>
                    <?php else: ?>
                        <ul class="list-group list-group-flush">
                            <?php 
                            $total = 0;
                            foreach ($_SESSION['cart'] as $id => $qty) {
                                $d = mysqli_fetch_array(mysqli_query($con, "SELECT * FROM produk WHERE idproduk='$id'"));
                                $sub = $d['harga'] * $qty;
                                $total += $sub;
                            ?>
                            <li class="list-group-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="fw-bold small"><?= htmlspecialchars($d['namaproduk']); ?></div>
                                        <small class="text-muted"><?= $qty; ?> x <?= number_format($d['harga']); ?></small>
                                    </div>
                                    <div class="text-end">
                                        <div class="fw-bold">Rp <?= number_format($sub); ?></div>
                                        <!-- Tombol hapus item -->
                                        <form method="post" class="d-inline">
                                            <input type="hidden" name="idproduk" value="<?= $id; ?>">
                                            <button name="removecart" class="btn btn-sm btn-outline-danger border-0 p-0" style="font-size:11px">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </li>
                            <?php } ?>
                        </ul>
                    <?php endif; ?>
                </div>

                <div class="card-footer bg-white">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="fw-bold">Total</span>
                        <span class="fw-bold text-primary fs-5">Rp <?= number_format($total ?? 0); ?></span>
                    </div>
                    
                    <!-- Pilih Metode Pembayaran -->
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">Metode Pembayaran</label>
                        <select id="pilihMetode" class="form-select form-select-sm">
                            <option value="Tunai">💵 Tunai (Cash)</option>
                            <option value="QRIS">📱 QRIS</option>
                        </select>
                    </div>

                    <!-- Tombol Bayar -->
                    <button onclick="bukaModalBayar(<?= $total ?? 0; ?>)" class="btn btn-success w-100 mb-2" <?= empty($_SESSION['cart']) ? 'disabled' : ''; ?>>
                        <i class="fas fa-money-bill-wave me-1"></i> Bayar Sekarang
                    </button>
                    
                    <!-- Tombol Kosongkan -->
                    <form method="post">
                        <button name="reset_cart" class="btn btn-outline-danger btn-sm w-100" <?= empty($_SESSION['cart']) ? 'disabled' : ''; ?>>
                            Kosongkan Keranjang
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</main>

<!-- ================= MODAL PEMBAYARAN ================= -->
<div class="modal fade" id="modalBayar" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">Konfirmasi Pembayaran</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="post">
                <div class="modal-body">
                    <div class="text-center mb-4">
                        <h1 class="fw-bold text-success" id="txtTotal">Rp 0</h1>
                        <small class="text-muted" id="txtMetode">Metode: Tunai</small>
                        
                        <input type="hidden" name="total_belanja" id="inputTotal">
                        <input type="hidden" name="metode_final" id="inputMetode">
                    </div>

                    <!-- Box QRIS (muncul jika pilih QRIS) -->
                    <div id="boxQRIS" class="text-center mb-3" style="display: none;">
                        <div class="p-2 border rounded d-inline-block bg-white shadow-sm">
                            <img src="images/qris.jpeg" onerror="this.src='images/qris.jpg'" alt="Scan QRIS" class="img-fluid" style="max-width: 200px;">
                        </div>
                        <p class="small text-muted mt-2">Silakan scan QRIS di atas</p>
                    </div>
                    
                    <!-- Input Uang Diterima -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Uang Diterima (Rp)</label>
                        <input type="number" name="bayar" id="inputBayar" class="form-control form-control-lg text-center" required onkeyup="hitungKembalian()">
                    </div>

                    <!-- Box Kembalian -->
                    <div class="alert alert-secondary text-center" id="boxKembalian">
                        <small>Kembalian</small>
                        <h3 class="fw-bold" id="txtKembalian">Rp 0</h3>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="checkout" class="btn btn-primary px-4" id="btnProses">Proses & Cetak</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // ================= FUNGSI MODAL PEMBAYARAN =================
    function bukaModalBayar(total) {
        let metode = document.getElementById('pilihMetode').value;
        let modal = new bootstrap.Modal(document.getElementById('modalBayar'));
        
        document.getElementById('inputTotal').value = total;
        document.getElementById('inputMetode').value = metode;
        document.getElementById('txtTotal').innerText = formatRupiah(total);
        document.getElementById('txtMetode').innerText = "Metode: " + metode;
        
        let inputBayar = document.getElementById('inputBayar');
        let boxKembalian = document.getElementById('boxKembalian');
        let btnProses = document.getElementById('btnProses');
        let boxQRIS = document.getElementById('boxQRIS');

        inputBayar.value = "";
        document.getElementById('txtKembalian').innerText = "Rp 0";
        btnProses.disabled = true;

        if (metode === 'Tunai') {
            // Tunai: user input nominal, tampilkan kembalian
            inputBayar.readOnly = false;
            boxKembalian.style.display = 'block';
            boxQRIS.style.display = 'none';
            setTimeout(() => inputBayar.focus(), 500);
        } else {
            // Non-Tunai: nominal otomatis = total
            inputBayar.value = total;
            inputBayar.readOnly = true;
            boxKembalian.style.display = 'none';
            btnProses.disabled = false;
            
            // Tampilkan QRIS jika metode QRIS
            boxQRIS.style.display = (metode === 'QRIS') ? 'block' : 'none';
        }

        modal.show();
    }

    // ================= HITUNG KEMBALIAN OTOMATIS =================
    function hitungKembalian() {
        let total = parseInt(document.getElementById('inputTotal').value);
        let bayar = parseInt(document.getElementById('inputBayar').value);
        let txtKembalian = document.getElementById('txtKembalian');
        let btnProses = document.getElementById('btnProses');

        if (isNaN(bayar)) {
            txtKembalian.innerText = "Rp 0";
            btnProses.disabled = true;
            return;
        }

        let kembali = bayar - total;

        if (kembali >= 0) {
            txtKembalian.innerText = formatRupiah(kembali);
            txtKembalian.className = "fw-bold text-success";
            btnProses.disabled = false;
        } else {
            txtKembalian.innerText = "Kurang " + formatRupiah(Math.abs(kembali));
            txtKembalian.className = "fw-bold text-danger";
            btnProses.disabled = true;
        }
    }
</script>

<?php include 'templates/footer.php'; ?>
