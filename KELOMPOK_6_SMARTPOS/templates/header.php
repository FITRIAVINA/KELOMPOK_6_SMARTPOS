<?php
// ================================================================
// SMARTPOS - Template Header
// File: templates/header.php
// Deskripsi: Template header yang digunakan di semua halaman
//            Berisi tag HTML head, CSS, dan Navbar atas
// ================================================================

// Variabel $page_title harus didefinisikan sebelum include file ini
// Contoh: $page_title = "Dashboard";
if (!isset($page_title)) {
    $page_title = "SMARTPOS";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $page_title; ?> - SMARTPOS</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome Icons -->
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <!-- SweetAlert2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <!-- Custom CSS (dari template asli) -->
    <link href="css/styles.css" rel="stylesheet">
    
    <style>
        body { 
            background-color: #f0f2f5; 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
        }
        
        /* Kartu Bersih Modern */
        .card-clean {
            background: #fff;
            border: none;
            border-radius: 12px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.05);
        }
        .card-header-clean {
            background: #fff;
            border-bottom: 1px solid #f0f0f0;
            padding: 15px 20px;
            font-weight: 700;
            color: #333;
            border-radius: 12px 12px 0 0;
        }
        
        /* Tabel Styling */
        .table thead th {
            background-color: #f8f9fa;
            border-bottom: none;
            color: #6c757d;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.8rem;
            text-align: center !important;
            vertical-align: middle !important;
        }
        .table tbody td {
            text-align: center !important;
            vertical-align: middle !important;
        }
        
        /* Ikon Statistik */
        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-right: 15px;
        }
        .bg-icon-green { background: #e8f5e9; color: #2e7d32; }
        .bg-icon-blue { background: #e3f2fd; color: #1565c0; }
        .bg-icon-orange { background: #fff3e0; color: #e65100; }
        .bg-icon-red { background: #ffebee; color: #c62828; }
        .bg-icon-purple { background: #f3e5f5; color: #7b1fa2; }
        
        /* Badge Stok */
        .badge-stock {
            padding: 8px 12px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.85rem;
        }
        .badge-safe { background-color: #e8f5e9; color: #2e7d32; }
        .badge-low { background-color: #ffebee; color: #c62828; }
        .badge-warning-custom { background-color: #fff3cd; color: #856404; }
        .badge-expired { background-color: #dc3545; color: #fff; }
        
        /* Modal Styling */
        .modal-content { border: none; border-radius: 12px; }
        .modal-header { border-bottom: 1px solid #f0f0f0; }
        .modal-footer { border-top: none; }

        /* ================= RESPONSIVE STYLES ================= */
        
        /* Mobile sidebar toggle button */
        .sidebar-toggle {
            display: none;
            background: none;
            border: none;
            color: #fff;
            font-size: 1.2rem;
            padding: 5px 10px;
            cursor: pointer;
        }

        /* Responsive for tablets and below */
        @media (max-width: 991.98px) {
            .sidebar-toggle {
                display: inline-block;
            }
            
            #layoutSidenav_nav {
                transform: translateX(-225px);
                transition: transform 0.3s ease;
                position: fixed;
                z-index: 1040;
            }
            
            #layoutSidenav_nav.show {
                transform: translateX(0);
            }
            
            .sidebar-overlay {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0,0,0,0.5);
                z-index: 1035;
            }
            
            .sidebar-overlay.show {
                display: block;
            }
            
            #layoutSidenav_content {
                margin-left: 0 !important;
                padding-left: 0 !important;
            }
            
            .container-fluid {
                padding-left: 12px !important;
                padding-right: 12px !important;
            }
        }

        /* Mobile phones */
        @media (max-width: 767.98px) {
            h1, .h1 { font-size: 1.5rem; }
            h3, .h3 { font-size: 1.2rem; }
            
            .card-clean { border-radius: 8px; }
            .card-header-clean { padding: 10px 15px; font-size: 0.9rem; }
            
            .stat-icon { 
                width: 40px; height: 40px; 
                font-size: 1.1rem; 
                margin-right: 10px; 
            }
            
            .card-clean h3 { font-size: 1.1rem; }
            .card-clean h6 { font-size: 0.7rem; }
            
            .table { font-size: 0.8rem; }
            .table thead th { font-size: 0.7rem; padding: 6px 4px; }
            .table tbody td { padding: 6px 4px; }
            
            .btn-group-sm > .btn { padding: 4px 6px; font-size: 0.75rem; }
            
            /* Cart area on mobile — not sticky */
            .cart-area { 
                position: static !important;
                max-height: none !important;
            }
            
            /* Navbar compact */
            .navbar-brand { font-size: 1rem; padding-left: 5px !important; }
            .navbar-nav .nav-link { font-size: 0.85rem; }
            .navbar-nav .badge { font-size: 0.65rem; }
            
            /* Filter form */
            .form-control, .form-select { font-size: 0.85rem; }
            
            /* Modal responsive */
            .modal-dialog { margin: 10px; }
        }

        /* Small phones */
        @media (max-width: 575.98px) {
            .d-flex.justify-content-between.align-items-center.mb-4 {
                flex-direction: column;
                align-items: flex-start !important;
                gap: 5px;
            }
            
            .navbar-nav .nav-link span.badge { display: none; }
            
            .btn-group { flex-wrap: nowrap; }
        }
    </style>
</head>

<body class="sb-nav-fixed">

<!-- ================= NAVBAR ATAS ================= -->
<nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark shadow">
    <button class="sidebar-toggle" id="sidebarToggle" aria-label="Toggle sidebar">
        <i class="fas fa-bars"></i>
    </button>
    <a class="navbar-brand ps-3 fw-bold" href="index.php">
        <i class="fas fa-cash-register text-white me-2"></i> SMARTPOS
    </a>
    
    <!-- Info User di Kanan -->
    <ul class="navbar-nav ms-auto me-3">
        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle text-white" href="#" role="button" data-bs-toggle="dropdown">
                <i class="fas fa-user-circle me-1"></i> 
                <?= $_SESSION['nama_lengkap'] ?? 'User'; ?>
                <span class="badge bg-primary ms-1"><?= ucfirst($_SESSION['role'] ?? 'admin'); ?></span>
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><span class="dropdown-item-text text-muted small">Login sebagai: <?= $_SESSION['username'] ?? ''; ?></span></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="#" onclick="confirmLogout()"><i class="fas fa-right-from-bracket me-2"></i>Logout</a></li>
            </ul>
        </li>
    </ul>
</nav>

<!-- Overlay for mobile sidebar -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

