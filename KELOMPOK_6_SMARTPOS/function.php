<?php
session_start();

/* ================= KONEKSI ================= */
$con = mysqli_connect("localhost","root","","kasir");
if(!$con){
    die("Koneksi database gagal");
}

/* ================= LOGIN (SUDAH DIAMANKAN) ================= */
if(isset($_POST['login'])){
    // Menggunakan mysqli_real_escape_string untuk mencegah hacker
    $username = mysqli_real_escape_string($con, $_POST['username']);
    $password = mysqli_real_escape_string($con, $_POST['password']);

    // Cek user di database
    $query = mysqli_query($con,"
        SELECT * FROM user 
        WHERE username='$username' 
        AND password='$password'
    ");

    // Hitung jumlah data yang ditemukan
    $cek = mysqli_num_rows($query);

    if($cek > 0){
        // Jika berhasil login
        $_SESSION['login'] = true;
        // Opsional: Simpan nama user ke session biar bisa dipanggil
        // $_SESSION['username'] = $username; 
        
        header("Location: index.php");
        exit;
    } else {
        // Jika gagal
        echo "<script>
            alert('Username atau Password salah');
            window.location='login.php';
        </script>";
    }
}

?>