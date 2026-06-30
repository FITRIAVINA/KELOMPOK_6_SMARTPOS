<?php
// ================================================================
// SMARTPOS - Template Sidebar
// File: templates/sidebar.php
// Deskripsi: Sidebar navigasi yang tampil di semua halaman
//            Menu ditampilkan berdasarkan role user
//            Super Admin: semua menu + Manajemen User
//            Admin: semua menu KECUALI Manajemen User
// ================================================================

// Variabel $active_page digunakan untuk menandai menu yang sedang aktif
if (!isset($active_page)) {
    $active_page = "";
}
?>

<div id="layoutSidenav">
    <div id="layoutSidenav_nav">
        <nav class="sb-sidenav accordion sb-sidenav-dark">
            <div class="sb-sidenav-menu">
                <div class="nav">
                    
                    <div class="sb-sidenav-menu-heading">Menu Utama</div>
                    
                    <?php if (isSuperAdmin()): ?>
                    <a class="nav-link <?= ($active_page == 'dashboard') ? 'active' : ''; ?>" href="index.php">
                        <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                        Dashboard
                    </a>
                    <?php endif; ?>
                    
                    <?php if (isAdmin()): ?>
                    <a class="nav-link <?= ($active_page == 'transaksi') ? 'active' : ''; ?>" href="transaksi.php">
                        <div class="sb-nav-link-icon"><i class="fas fa-shopping-cart"></i></div>
                        Transaksi
                    </a>
                    <?php endif; ?>
                    
                    <?php if (isSuperAdmin()): ?>
                    <div class="sb-sidenav-menu-heading">Data</div>
                    
                    <a class="nav-link <?= ($active_page == 'produk') ? 'active' : ''; ?>" href="produk.php">
                        <div class="sb-nav-link-icon"><i class="fas fa-box"></i></div>
                        Data Produk
                    </a>
                    
                    <a class="nav-link <?= ($active_page == 'stok') ? 'active' : ''; ?>" href="stok.php">
                        <div class="sb-nav-link-icon"><i class="fas fa-boxes"></i></div>
                        Stok Barang
                    </a>
                    
                    <a class="nav-link <?= ($active_page == 'masuk') ? 'active' : ''; ?>" href="masuk.php">
                        <div class="sb-nav-link-icon"><i class="fas fa-arrow-down"></i></div>
                        Barang Masuk
                    </a>
                    <?php endif; ?>
                    
                    <div class="sb-sidenav-menu-heading">Laporan</div>
                    
                    <a class="nav-link <?= ($active_page == 'riwayat') ? 'active' : ''; ?>" href="riwayat.php">
                        <div class="sb-nav-link-icon"><i class="fas fa-receipt"></i></div>
                        Riwayat Transaksi
                    </a>
                    
                    <?php
                    // ================= MENU KHUSUS SUPER ADMIN =================
                    if (isSuperAdmin()):
                    ?>
                    <div class="sb-sidenav-menu-heading">Pengaturan</div>
                    
                    <a class="nav-link <?= ($active_page == 'users') ? 'active' : ''; ?>" href="users.php">
                        <div class="sb-nav-link-icon"><i class="fas fa-users-cog"></i></div>
                        Management User
                    </a>
                    <?php endif; ?>
                    
                    <div class="sb-sidenav-menu-heading">Akun</div>
                    <a class="nav-link" href="#" onclick="confirmLogout()">
                        <div class="sb-nav-link-icon"><i class="fas fa-right-from-bracket"></i></div>
                        Logout
                    </a>
                    
                </div>
            </div>
            
            <div class="sb-sidenav-footer">
                <div class="small">Login sebagai:</div>
                <?= $_SESSION['nama_lengkap'] ?? 'User'; ?>
            </div>
        </nav>
    </div>

    <div id="layoutSidenav_content">