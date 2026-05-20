<?php
session_start();
include(__DIR__ . '/../../config/config.php');
include(__DIR__ . '/../../includes/pembeli_helpers.php');
kk_ensure_buyer_schema($koneksi);

// 1. Cek Login
if (!isset($_SESSION['id_user'])) {
    echo "<script>alert('Silakan login terlebih dahulu'); window.location='../auth/login.php';</script>";
    exit();
}

$id_user = $_SESSION['id_user'];

// 2. Ambil data dari URL (GET)
$id_menu = isset($_GET['id']) ? mysqli_real_escape_string($koneksi, $_GET['id']) : '';
$qty     = isset($_GET['qty']) ? (int)$_GET['qty'] : 1;
$catatan = isset($_GET['catatan']) ? mysqli_real_escape_string($koneksi, $_GET['catatan']) : '';
$opsi    = isset($_GET['opsi']) ? mysqli_real_escape_string($koneksi, $_GET['opsi']) : '';
$action  = isset($_GET['action']) ? $_GET['action'] : 'cart'; // INI KUNCINYA

if (empty($id_menu)) {
    header("Location: dashboard.php");
    exit();
}

// 3. Cek apakah menu sudah ada di keranjang?
$cek = mysqli_query($koneksi, "SELECT * FROM keranjang WHERE id_user = '$id_user' AND id_menu = '$id_menu' AND COALESCE(opsi_pilihan,'') = '$opsi'");

if (mysqli_num_rows($cek) > 0) {
    // Jika ada, update jumlahnya
    $sql = "UPDATE keranjang SET qty = qty + $qty, catatan = '$catatan', opsi_pilihan = '$opsi'
            WHERE id_user = '$id_user' AND id_menu = '$id_menu' AND COALESCE(opsi_pilihan,'') = '$opsi'";
} else {
    // Jika belum ada, masukkan data baru
    $sql = "INSERT INTO keranjang (id_user, id_menu, qty, catatan, opsi_pilihan) 
            VALUES ('$id_user', '$id_menu', '$qty', '$catatan', '$opsi')";
}

// 4. Eksekusi dan Arahkan (Redirect)
if (mysqli_query($koneksi, $sql)) {
    if ($action === 'checkout') {
        // JIKA TOMBOL PESAN SEKARANG DIPENCET
        header("Location: checkout.php");
        exit();
    // Ganti bagian ini di kodinganmu
} else {
    // Tambahkan kembali id_menu agar halaman detail_menu tidak bingung
    echo "<script>alert('Berhasil ditambah ke keranjang!'); window.location='detail_menu.php?id=$id_menu';</script>";
    exit();
}
} else {
    echo "Error: " . mysqli_error($koneksi);
}
?>
