<?php
// ================================================================
// SMARTPOS - Template Footer
// File: templates/footer.php
// Deskripsi: Template footer yang digunakan di semua halaman
//            Berisi tag penutup HTML, JavaScript libraries,
//            SweetAlert, dan fungsi logout
// ================================================================
?>
        </div> <!-- Tutup layoutSidenav_content -->
    </div> <!-- Tutup layoutSidenav -->

    <!-- ================= JAVASCRIPT LIBRARIES ================= -->
    <!-- jQuery (diperlukan oleh DataTables) -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <!-- Chart.js (untuk grafik) -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- SweetAlert2 (untuk notifikasi modern) -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Custom sidebar toggle script -->
    <script src="js/scripts.js"></script>

    <script>
        // ================= FUNGSI LOGOUT =================
        // Menampilkan konfirmasi SweetAlert sebelum logout
        function confirmLogout() {
            Swal.fire({
                title: 'Logout?',
                text: 'Apakah Anda yakin ingin keluar dari sistem?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Logout',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'Logout.php';
                }
            });
        }

        // ================= FORMAT RUPIAH =================
        // Fungsi JavaScript untuk format angka ke Rupiah
        function formatRupiah(angka) {
            return "Rp " + new Intl.NumberFormat('id-ID').format(angka);
        }

        // ================= MOBILE SIDEBAR TOGGLE =================
        document.addEventListener('DOMContentLoaded', function() {
            var toggleBtn = document.getElementById('sidebarToggle');
            var sidebarNav = document.getElementById('layoutSidenav_nav');
            var overlay = document.getElementById('sidebarOverlay');
            
            if (toggleBtn && sidebarNav) {
                toggleBtn.addEventListener('click', function() {
                    sidebarNav.classList.toggle('show');
                    if (overlay) overlay.classList.toggle('show');
                });
            }
            
            if (overlay) {
                overlay.addEventListener('click', function() {
                    if (sidebarNav) sidebarNav.classList.remove('show');
                    overlay.classList.remove('show');
                });
            }
            
            // Close sidebar when clicking a nav link on mobile
            var navLinks = document.querySelectorAll('#layoutSidenav_nav .nav-link');
            navLinks.forEach(function(link) {
                link.addEventListener('click', function() {
                    if (window.innerWidth < 992) {
                        if (sidebarNav) sidebarNav.classList.remove('show');
                        if (overlay) overlay.classList.remove('show');
                    }
                });
            });
        });
    </script>

</body>
</html>
