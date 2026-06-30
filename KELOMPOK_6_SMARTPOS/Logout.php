<?php
// ================================================================
// SMARTPOS - Logout
// File: Logout.php
// Deskripsi: Menghapus semua session dan redirect ke login
// ================================================================

session_start();

// Hapus semua session
$_SESSION = [];
session_unset();
session_destroy();

// Redirect ke halaman login
header("Location: login.php");
exit;
