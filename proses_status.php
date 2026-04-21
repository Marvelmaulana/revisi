<?php
session_start();
include 'config.php';

// Pastikan hanya penjual yang bisa akses
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'penjual') {
    header("Location: login.php");
    exit;
}

// Ambil data dari URL (Link)
$id_t   = $_GET['id'];
$status = $_GET['status'];

// Update status di tabel transaksi
$sql = "UPDATE transaksi SET status = '$status' WHERE id_transaksi = '$id_t'";

if (mysqli_query($koneksi, $sql)) {
    // Jika berhasil, balikkan ke halaman pesanan masuk
    echo "<script>alert('Status pesanan berhasil diperbarui!'); window.location='pesanan_masuk.php';</script>";
} else {
    echo "Gagal update: " . mysqli_error($koneksi);
}
?>