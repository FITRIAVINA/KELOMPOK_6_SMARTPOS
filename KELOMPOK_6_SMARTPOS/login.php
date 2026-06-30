<?php
// ================================================================
// SMARTPOS - Halaman Login
// File: login.php
// Deskripsi: Halaman login untuk masuk ke sistem SMARTPOS
//            Mendukung 2 role: Super Admin dan Admin
//            Session menyimpan: login, id_user, username, 
//            nama_lengkap, role
// ================================================================

session_start();

// 1. Koneksi ke database smartpos
$con = mysqli_connect("localhost", "root", "", "smartpos");
if (!$con) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

// 2. Jika user sudah login, redirect sesuai role
if (isset($_SESSION['login'])) {
    if ($_SESSION['role'] === 'admin') {
        header('location: transaksi.php');
    } else {
        header('location: index.php');
    }
    exit;
}

// 3. Proses Login
$error = ""; // Variabel untuk menyimpan pesan error
if (isset($_POST['login'])) {
    // Ambil data dari form dan amankan dari SQL Injection
    $username = mysqli_real_escape_string($con, $_POST['username']);
    $password = mysqli_real_escape_string($con, $_POST['password']);

    // Validasi: pastikan tidak kosong
    if (empty($username) || empty($password)) {
        $error = "Username dan Password harus diisi!";
    } else {
        // Cek username dan password di database
        $query = mysqli_query($con, "
            SELECT * FROM users 
            WHERE username = '$username' 
            AND password = '$password'
        ");
        $jumlah = mysqli_num_rows($query);

        if ($jumlah > 0) {
            // Login berhasil - simpan data user ke session
            $data = mysqli_fetch_assoc($query);
            $_SESSION['login']        = true;
            $_SESSION['id_user']      = $data['id_user'];
            $_SESSION['username']     = $data['username'];
            $_SESSION['nama_lengkap'] = $data['nama_lengkap'];
            $_SESSION['role']         = $data['role'];

            // Redirect sesuai role
            if ($data['role'] === 'admin') {
                header('location: transaksi.php');
            } else {
                header('location: index.php');
            }
            exit;
        } else {
            // Login gagal
            $error = "Username atau Password salah!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Login - SMARTPOS</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        body {
            /* Background Gradient Modern (Biru ke Ungu) */
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .login-card {
            width: 100%;
            max-width: 400px;
            background: rgba(255, 255, 255, 0.95);
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
            animation: fadeInDown 0.8s ease;
        }

        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .login-header .icon-circle {
            width: 80px;
            height: 80px;
            background: #1e3c72;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            color: white;
            font-size: 32px;
            box-shadow: 0 5px 15px rgba(30, 60, 114, 0.3);
        }

        .login-header h3 {
            font-weight: 700;
            color: #333;
            margin-bottom: 5px;
        }

        .login-header p {
            color: #777;
            font-size: 14px;
        }

        .form-floating > label {
            color: #666;
        }

        .form-control:focus {
            border-color: #1e3c72;
            box-shadow: 0 0 0 0.25rem rgba(30, 60, 114, 0.25);
        }

        .btn-login {
            background: linear-gradient(to right, #1e3c72, #2a5298);
            border: none;
            padding: 12px;
            font-weight: 600;
            letter-spacing: 1px;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(30, 60, 114, 0.4);
            background: linear-gradient(to right, #2a5298, #1e3c72);
        }

        /* Styling Khusus untuk Penempatan Tombol Mata di Form Floating */
        .password-container {
            position: relative;
        }
        
        .password-container .form-control {
            padding-right: 50px;
        }

        .toggle-password-btn {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #6c757d;
            cursor: pointer;
            z-index: 10;
            padding: 0;
        }

        .toggle-password-btn:hover {
            color: #1e3c72;
        }

        /* Animasi Masuk */
        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>

    <div class="login-card">
        <div class="login-header">
            <div class="icon-circle">
                <i class="fas fa-cash-register"></i>
            </div>
            <h3>SMARTPOS</h3>
            <p>Sistem Kasir Digital Berbasis Web</p>
        </div>

        <form method="post">
            <div class="form-floating mb-3">
                <input class="form-control" id="inputUsername" name="username" type="text" placeholder="Username" required />
                <label for="inputUsername"><i class="fas fa-user me-2"></i>Username</label>
            </div>
            
            <div class="form-floating mb-4 password-container">
                <input class="form-control" id="inputPassword" name="password" type="password" placeholder="Password" required />
                <label for="inputPassword"><i class="fas fa-lock me-2"></i>Password</label>
                <button type="button" id="togglePassword" class="toggle-password-btn">
                    <i class="fas fa-eye" id="eyeIcon"></i>
                </button>
            </div>
            
            <div class="d-grid gap-2">
                <button type="submit" name="login" class="btn btn-primary btn-lg btn-login">
                    LOGIN
                </button>
            </div>
        </form>
        
        <div class="text-center mt-4">
            <small class="text-muted">&copy; 2026 SMARTPOS</small>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    
    <script>
        const togglePassword = document.querySelector('#togglePassword');
        const passwordInput = document.querySelector('#inputPassword');
        const eyeIcon = document.querySelector('#eyeIcon');

        togglePassword.addEventListener('click', function () {
            // Cek tipe input saat ini dan balikkan kondisinya
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            
            // Ubah ikon mata sesuai dengan status tampilan teks
            if (type === 'text') {
                eyeIcon.classList.remove('fa-eye');
                eyeIcon.classList.add('fa-eye-slash');
            } else {
                eyeIcon.classList.remove('fa-eye-slash');
                eyeIcon.classList.add('fa-eye');
            }
        });
    </script>
    
    <?php if (!empty($error)): ?>
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Login Gagal!',
            text: '<?= $error; ?>',
            confirmButtonColor: '#1e3c72'
        });
    </script>
    <?php endif; ?>
</body>
</html>