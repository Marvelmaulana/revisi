<?php
session_start();
include 'config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'penjual') {
    header("Location: login.php");
    exit;
}

$id_t = $_GET['id'];
$id_k = $_SESSION['id_kantin'];

// Ambil info utama transaksi
$query_t = mysqli_query($koneksi, "SELECT transaksi.*, users.username FROM transaksi 
    JOIN users ON transaksi.id_user = users.id_user 
    WHERE transaksi.id_transaksi = '$id_t' AND transaksi.id_kantin = '$id_k'");
$data_t = mysqli_fetch_assoc($query_t);

// Ambil rincian menu yang dibeli
$query_d = mysqli_query($koneksi, "SELECT detail_transaksi.*, menu.nama_menu, menu.foto_menu 
    FROM detail_transaksi 
    JOIN menu ON detail_transaksi.id_menu = menu.id_menu 
    WHERE detail_transaksi.id_transaksi = '$id_t'");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rincian Pesanan #<?= $id_t ?></title>
    <style>
        :root { --blue: #50c8ff; --dark: #333; --bg: #f4f7f6; }
        body { font-family: 'Segoe UI', sans-serif; background: var(--bg); margin: 0; padding: 40px; }
        .container { max-width: 800px; margin: auto; background: white; padding: 30px; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
        .header { border-bottom: 2px solid #eee; padding-bottom: 20px; margin-bottom: 20px; display: flex; justify-content: space-between; }
        .info-pembeli h2 { margin: 0; color: var(--blue); }
        .item-list { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .item-list th { text-align: left; padding: 15px; border-bottom: 2px solid #eee; color: #888; }
        .item-list td { padding: 15px; border-bottom: 1px solid #eee; }
        .total-section { margin-top: 20px; text-align: right; font-size: 20px; font-weight: bold; }
        .btn-back { display: inline-block; padding: 12px 25px; background: #888; color: white; text-decoration: none; border-radius: 10px; margin-top: 20px; }
        .btn-action { display: inline-block; padding: 12px 25px; background: #4caf50; color: white; text-decoration: none; border-radius: 10px; margin-top: 20px; float: right; }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <div class="info-pembeli">
            <p style="color: #999; margin: 0;">Pesanan Dari:</p>
            <h2><?= strtoupper($data_t['username']) ?></h2>
            <small>ID Transaksi: #<?= $id_t ?> | <?= $data_t['created_at'] ?></small>
        </div>
        <div style="text-align: right;">
            <span style="padding: 8px 15px; background: #e3f2fd; color: #1976d2; border-radius: 20px; font-weight: bold;">
                <?= strtoupper($data_t['status']) ?>
            </span>
        </div>
    </div>

    <table class="item-list">
        <thead>
            <tr>
                <th>Menu</th>
                <th>Jumlah</th>
                <th>Harga Satuan</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <?php while($d = mysqli_fetch_assoc($query_d)) : ?>
            <tr>
                <td>
                    <div style="display: flex; align-items: center;">
                        <img src="uploads/<?= $d['foto_menu'] ?>" width="50" height="50" style="border-radius: 8px; margin-right: 15px; object-fit: cover;">
                        <b><?= $d['nama_menu'] ?></b>
                    </div>
                </td>
                <td><?= $d['jumlah'] ?>x</td>
                <td>Rp <?= number_format($d['subtotal']/$d['jumlah'], 0, ',', '.') ?></td>
                <td><b>Rp <?= number_format($d['subtotal'], 0, ',', '.') ?></b></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <div class="total-section">
        <span style="color: #888; font-weight: normal;">Total Bayar: </span>
        <span style="color: #2e7d32;">Rp <?= number_format($data_t['total'], 0, ',', '.') ?></span>
    </div>

    <a href="pesanan_masuk.php" class="btn-back"> Kembali</a>
    
    <?php if($data_t['status'] == 'pending') : ?>
        <a href="proses_status.php?id=<?= $id_t ?>&status=dibayar" class="btn-action">Konfirmasi Pembayaran</a>
    <?php elseif($data_t['status'] == 'dibayar') : ?>
        <a href="proses_status.php?id=<?= $id_t ?>&status=selesai" class="btn-action" style="background: #2196f3;">Selesaikan Pesanan</a>
    <?php endif; ?>
</div>

</body>
</html>