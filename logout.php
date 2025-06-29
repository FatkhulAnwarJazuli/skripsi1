<?php
define('BASE_URL', '/skripsi1/'); // Tambahkan baris ini
session_start(); // Mulai sesi
session_unset(); // Hapus semua variabel sesi
session_destroy(); // Hancurkan sesi

// Redirect ke halaman login setelah logout berhasil
header("Location: " . BASE_URL . "login.php");
exit();
?>