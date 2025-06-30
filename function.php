<?php

// Detail koneksi database utama
$host = "localhost";
$user = "root";
$pass = "";
$db   = "inventaris";

// Buat koneksi utama
$conn = mysqli_connect($host, $user, $pass, $db);

// Periksa koneksi utama
if (!$conn) {
    // Menghentikan eksekusi dan menampilkan error jika koneksi gagal
    die("Koneksi database utama gagal: " . mysqli_connect_error());
}

// Set zona waktu (WITA)
date_default_timezone_set('Asia/Makassar');

// --- Fungsi untuk menampilkan pesan dan redirect ---
function redirect_with_alert($message, $location) {
    echo "<script>alert('$message'); window.location.href='$location';</script>";
    exit();
}

function back_with_alert($message) {
    echo "<script>alert('$message'); history.go(-1);</script>";
    exit();
}