<?php
session_start();
include 'config.php';

if (!isset($_SESSION['keranjang']) || empty($_SESSION['keranjang'])) {
    header("Location: dashboard_pembeli.php");
    exit;
}

$id_user = $_SESSION['id_user'];
$id_kantin = $_SESSION['id_kantin_aktif'];
$total_final = 0;

// Hitung Total
foreach ($_SESSION['keranjang'] as $id_m => $qty) {
    $res = mysqli_query($koneksi, "SELECT harga FROM menu WHERE id_menu = '$id_m'");
    $row = mysqli_fetch_assoc($res);
    $total_final += ($row['harga'] * $qty);
}

// SIMPAN KE TABEL TRANSAKSI (Sesuai Struktur Enum Kamu)
// metode = 'keranjang' karena diproses lewat sistem ini
$simpan_t = mysqli_query($koneksi, "INSERT INTO transaksi 
    (id_user, id_kantin, total, metode, status, metode_pembayaran, bukti_pembayaran, created_at) 
    VALUES 
    ('$id_user', '$id_kantin', '$total_final', 'keranjang', 'pending', 'Tunai', '', NOW())");

if ($simpan_t) {
    $id_t = mysqli_insert_id($koneksi);

    // SIMPAN KE DETAIL_TRANSAKSI
    foreach ($_SESSION['keranjang'] as $id_m => $qty) {
        $res_m = mysqli_query($koneksi, "SELECT harga FROM menu WHERE id_menu = '$id_m'");
        $m = mysqli_fetch_assoc($res_m);
        $sub = $m['harga'] * $qty;

        mysqli_query($koneksi, "INSERT INTO detail_transaksi (id_transaksi, id_menu, jumlah, subtotal) 
            VALUES ('$id_t', '$id_m', '$qty', '$sub')");
    }

    // Bersihkan Keranjang
    unset($_SESSION['keranjang']);
    unset($_SESSION['id_kantin_aktif']);

    echo "<script>alert('Pesanan Terkirim!'); window.location='riwayat_pembeli.php';</script>";
} else {
    echo "Gagal: " . mysqli_error($koneksi);
}