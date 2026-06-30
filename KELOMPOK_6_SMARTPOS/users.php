<?php
// ================================================================
// SMARTPOS - Manajemen User
// File: users.php
// Deskripsi: Halaman untuk mengelola akun pengguna
//            KHUSUS SUPER ADMIN - admin tidak bisa akses
//            Fitur: Tambah, Edit, dan Hapus user
//            Role: superadmin atau admin
// ================================================================

include 'config/koneksi.php';
cekLogin();

// Hanya Super Admin yang bisa akses halaman ini
onlySuperAdmin();

$page_title  = "Manajemen User";
$active_page = "users";

// ================= PROSES TAMBAH USER =================
if (isset($_POST['tambah_user'])) {
    $nama     = mysqli_real_escape_string($con, $_POST['nama_lengkap']);
    $username = mysqli_real_escape_string($con, $_POST['username']);
    $password = mysqli_real_escape_string($con, $_POST['password']);
    $role     = mysqli_real_escape_string($con, $_POST['role']);

    // Cek apakah username sudah dipakai
    $cek = mysqli_query($con, "SELECT * FROM users WHERE username='$username'");
    if (mysqli_num_rows($cek) > 0) {
        echo "<script>alert('Username sudah digunakan!'); window.location='users.php';</script>";
    } else {
        mysqli_query($con, "
            INSERT INTO users (nama_lengkap, username, password, role) 
            VALUES ('$nama', '$username', '$password', '$role')
        ");
        echo "<script>alert('User berhasil ditambahkan!'); window.location='users.php';</script>";
    }
}

// ================= PROSES EDIT USER =================
if (isset($_POST['edit_user'])) {
    $id_user  = (int) $_POST['id_user'];
    $nama     = mysqli_real_escape_string($con, $_POST['nama_lengkap']);
    $username = mysqli_real_escape_string($con, $_POST['username']);
    $role     = mysqli_real_escape_string($con, $_POST['role']);
    $password = mysqli_real_escape_string($con, $_POST['password']);

    // Cek duplikat username (kecuali milik sendiri)
    $cek = mysqli_query($con, "SELECT * FROM users WHERE username='$username' AND id_user != '$id_user'");
    if (mysqli_num_rows($cek) > 0) {
        echo "<script>alert('Username sudah digunakan user lain!'); window.location='users.php';</script>";
    } else {
        // Jika password diisi, update password juga
        $pass_query = "";
        if (!empty($password)) {
            $pass_query = ", password='$password'";
        }

        mysqli_query($con, "
            UPDATE users SET 
                nama_lengkap = '$nama',
                username = '$username',
                role = '$role'
                $pass_query
            WHERE id_user = '$id_user'
        ");
        echo "<script>alert('User berhasil diupdate!'); window.location='users.php';</script>";
    }
}

// ================= PROSES HAPUS USER =================
if (isset($_POST['hapus_user'])) {
    $id_user = (int) $_POST['id_user'];
    
    // Jangan hapus diri sendiri
    if ($id_user == $_SESSION['id_user']) {
        echo "<script>alert('Tidak bisa menghapus akun sendiri!'); window.location='users.php';</script>";
    } else {
        mysqli_query($con, "DELETE FROM users WHERE id_user='$id_user'");
        echo "<script>alert('User berhasil dihapus!'); window.location='users.php';</script>";
    }
}

// Query semua user
$data = mysqli_query($con, "SELECT * FROM users ORDER BY id_user ASC");

include 'templates/header.php';
include 'templates/sidebar.php';
?>

<main class="container-fluid px-4 py-4">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold text-dark">
            <i class="fas fa-users-cog text-secondary me-2"></i>Manajemen User
        </h3>
        <button class="btn btn-primary rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#modalTambahUser">
            <i class="fas fa-user-plus me-2"></i>Tambah User
        </button>
    </div>

    <!-- Info: Hanya Super Admin -->
    <div class="alert alert-info mb-4">
        <i class="fas fa-info-circle me-2"></i>
        Halaman ini hanya dapat diakses oleh <strong>Super Admin</strong>. 
        Kelola akun pengguna yang memiliki akses ke sistem SMARTPOS.
    </div>

    <!-- Tabel User -->
    <div class="card card-clean">
        <div class="card-header-clean">
            <i class="fas fa-table me-2"></i>Daftar User
        </div>
        <div class="card-body">
            <div class="table-responsive">
            <table id="tabelUser" class="table table-hover w-100">
                <thead>
                    <tr>
                        <th width="5%">No</th>
                        <th>Nama Lengkap</th>
                        <th>Username</th>
                        <th>Role</th>
                        <th>Terdaftar</th>
                        <th width="12%">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php 
                $no = 1;
                while ($d = mysqli_fetch_array($data)): 
                ?>
                    <tr>
                        <td><?= $no++; ?></td>
                        <td class="fw-bold"><?= htmlspecialchars($d['nama_lengkap']); ?></td>
                        <td><?= htmlspecialchars($d['username']); ?></td>
                        <td>
                            <?php if ($d['role'] == 'superadmin'): ?>
                                <span class="badge bg-danger">Super Admin</span>
                            <?php else: ?>
                                <span class="badge bg-primary">Admin</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-muted small"><?= date('d M Y', strtotime($d['created_at'])); ?></td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-outline-warning" data-bs-toggle="modal" data-bs-target="#modalEdit<?= $d['id_user']; ?>">
                                    <i class="fas fa-pen"></i>
                                </button>
                                <?php if ($d['id_user'] != $_SESSION['id_user']): ?>
                                <button class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#modalHapus<?= $d['id_user']; ?>">
                                    <i class="fas fa-trash"></i>
                                </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>

                    <!-- Modal Edit User -->
                    <div class="modal fade" id="modalEdit<?= $d['id_user']; ?>" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form method="post">
                                    <div class="modal-header">
                                        <h5 class="modal-title fw-bold">Edit User</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <input type="hidden" name="id_user" value="<?= $d['id_user']; ?>">
                                        <div class="mb-3">
                                            <label class="form-label small text-muted">Nama Lengkap</label>
                                            <input type="text" name="nama_lengkap" class="form-control" value="<?= htmlspecialchars($d['nama_lengkap']); ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label small text-muted">Username</label>
                                            <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($d['username']); ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label small text-muted">Password Baru (kosongkan jika tidak diubah)</label>
                                            <input type="text" name="password" class="form-control" placeholder="Isi jika ingin ganti password">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label small text-muted">Role</label>
                                            <select name="role" class="form-select" required>
                                                <option value="superadmin" <?= ($d['role'] == 'superadmin') ? 'selected' : ''; ?>>Super Admin</option>
                                                <option value="admin" <?= ($d['role'] == 'admin') ? 'selected' : ''; ?>>Admin</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="submit" name="edit_user" class="btn btn-warning w-100 rounded-pill">Simpan Perubahan</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Hapus User -->
                    <?php if ($d['id_user'] != $_SESSION['id_user']): ?>
                    <div class="modal fade" id="modalHapus<?= $d['id_user']; ?>" tabindex="-1">
                        <div class="modal-dialog modal-sm">
                            <div class="modal-content">
                                <form method="post">
                                    <div class="modal-header bg-danger text-white">
                                        <h5 class="modal-title">Hapus User?</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body text-center">
                                        <input type="hidden" name="id_user" value="<?= $d['id_user']; ?>">
                                        <i class="fas fa-exclamation-circle fa-3x text-danger mb-3"></i>
                                        <p>Hapus user <strong><?= htmlspecialchars($d['nama_lengkap']); ?></strong>?</p>
                                        <small class="text-danger">Tindakan ini tidak dapat dibatalkan!</small>
                                    </div>
                                    <div class="modal-footer justify-content-center">
                                        <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                                        <button type="submit" name="hapus_user" class="btn btn-danger rounded-pill px-4">Hapus</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                <?php endwhile; ?>
                </tbody>
            </table>
            </div>
        </div>
    </div>
</main>

<!-- Modal Tambah User -->
<div class="modal fade" id="modalTambahUser" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Tambah User Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small text-muted">Nama Lengkap</label>
                        <input type="text" name="nama_lengkap" class="form-control" placeholder="Contoh: John Doe" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small text-muted">Username</label>
                        <input type="text" name="username" class="form-control" placeholder="Username untuk login" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small text-muted">Password</label>
                        <input type="text" name="password" class="form-control" placeholder="Password" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small text-muted">Role</label>
                        <select name="role" class="form-select" required>
                            <option value="admin">Admin</option>
                            <option value="superadmin">Super Admin</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" name="tambah_user" class="btn btn-primary w-100 rounded-pill">Simpan User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('#tabelUser').DataTable({
            "language": {
                "search": "Cari User:",
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
