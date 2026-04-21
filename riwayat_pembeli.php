<?php
session_start();
include 'config.php';

// 1. PROTEKSI HALAMAN
if (!isset($_SESSION['role']) || ($_SESSION['role'] != 'siswa' && $_SESSION['role'] != 'guru')) {
    header("Location: login.php");
    exit;
}

$id_user = $_SESSION['id_user'];

// 2. QUERY AMBIL RIWAYAT (Gabung dengan tabel users untuk tahu nama kantinnya)
$query = mysqli_query($koneksi, "SELECT transaksi.*, users.username as nama_kantin 
    FROM transaksi 
    JOIN users ON transaksi.id_kantin = users.id_user 
    WHERE transaksi.id_user = '$id_user' 
    ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Belanja - Kantin Kita</title>
    <style>
        :root {
            --primary-blue: #50c8ff;
            --dark-blue: #3da8db;
            --bg-light: #f4f7f6;
            --white: #ffffff;
            --text-dark: #333;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', sans-serif; }
        body { background-color: var(--bg-light); }

        /* Navbar */
        .navbar { 
            background: var(--primary-blue); color: white; padding: 15px 5%; 
            display: flex; justify-content: space-between; align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .navbar a { color: white; text-decoration: none; font-weight: bold; margin-left: 20px; }

        .container { padding: 40px 5%; max-width: 1000px; margin: auto; }
        
        .header-section { margin-bottom: 30px; }
        .header-section h1 { font-size: 28px; color: var(--text-dark); }

        /* Table Style */
        .table-container { 
            background: var(--white); border-radius: 15px; 
            overflow: hidden; box-shadow: 0 10px 25px rgba(0,0,0,0.05); 
        }
        table { width: 100%; border-collapse: collapse; }
        thead { background: var(--primary-blue); color: white; }
        th { padding: 18px; text-align: left; font-size: 14px; text-transform: uppercase; }
        td { padding: 15px 18px; border-bottom: 1px solid #f2f2f2; color: #555; }

        /* Status Badges */
        .badge { 
            padding: 6px 12px; border-radius: 20px; font-size: 11px; 
            font-weight: bold; text-transform: uppercase; display: inline-block; 
        }
        .status-pending { background: #fff3e0; color: #ef6c00; }   /* Oranye */
        .status-diproses { background: #e3f2fd; color: #1565c0; }  /* Biru */
        .status-selesai { background: #e8f5e9; color: #2e7d32; }   /* Hijau */
        .status-dibatalkan { background: #ffebee; color: #c62828; } /* Merah */

        .btn-detail { 
            text-decoration: none; color: var(--dark-blue); font-weight: bold; 
            border: 1px solid var(--dark-blue); padding: 5px 12px; 
            border-radius: 6px; font-size: 12px; transition: 0.3s;
        }
        .btn-detail:hover { background: var(--dark-blue); color: white; }

        .empty-state { text-align: center; padding: 60px; color: #aaa; }
    </style>
</head>
<body>

<div class="navbar">
    <h2>🛍️ Riwayat Kita</h2>
    <div>
        <a href="dashboard_pembeli.php">Home</a>
        <a href="keranjang.php">🛒 Keranjang</a>
        <a href="logout.php">Logout</a>
    </div>
</div>

<div class="container">
    <div class="header-section">
        <h1>Pesanan Saya</h1>
        <p>Pantau status jajananmu di sini.</p>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Waktu</th>
                    <th>Kantin</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                if(mysqli_num_rows($query) > 0) {
                    while($row = mysqli_fetch_assoc($query)) : 
                ?>
                <tr>
                    <td><?= date('d/m/Y H:i', strtotime($row['created_at'])) ?></td>
                    <td><b><?= strtoupper($row['nama_kantin']) ?></b></td>
                    <td style="font-weight: bold;">Rp <?= number_format($row['total'], 0, ',', '.') ?></td>
                    <td>
                        <span class="badge status-<?= $row['status'] ?>">
                            <?= $row['status'] ?>
                        </span>
                    </td>
                    <td>
                        <a href="detail_pesanan_pembeli.php?id=<?= $row['id_transaksi'] ?>" class="btn-detail">CEK DETAIL</a>
                    </td>
                </tr>
                <?php 
                    endwhile; 
                } else {
                ?>
                <tr>
                    <td colspan="5" class="empty-state">
                        Belum ada riwayat pesanan. <br>
                        <a href="dashboard_pembeli.php" style="color: var(--dark-blue); text-decoration: none;">Ayo jajan sekarang!</a>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>