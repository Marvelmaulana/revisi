<?php
session_start();
include 'config.php';

// 1. PROTEKSI HALAMAN
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'penjual') {
    header("Location: login.php");
    exit;
}

$id_k = $_SESSION['id_kantin'];

// 2. QUERY RIWAYAT PESANAN
// Kita hanya mengambil status 'selesai' dan 'dibatalkan'
$query = mysqli_query($koneksi, "SELECT transaksi.*, users.username, users.role 
    FROM transaksi 
    JOIN users ON transaksi.id_user = users.id_user 
    WHERE transaksi.id_kantin = '$id_k' 
    AND (transaksi.status = 'selesai' OR transaksi.status = 'dibatalkan')
    ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Pesanan - Kantin Kita</title>
    <style>
        :root {
            --primary-blue: #50c8ff;
            --dark-blue: #3da8db;
            --bg-light: #f4f7f6;
            --white: #ffffff;
            --text-dark: #333;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', sans-serif; }
        body { background-color: var(--bg-light); display: flex; }

        /* --- SIDEBAR (ANTI-HILANG) --- */
        .sidebar { 
            width: 260px; height: 100vh; background: var(--primary-blue); 
            color: var(--white); padding: 25px 20px; position: fixed; z-index: 1000;
        }
        .sidebar h2 { text-align: center; margin-bottom: 30px; font-size: 22px; font-weight: bold; }
        .user-info { text-align: center; background: rgba(255, 255, 255, 0.2); padding: 15px; border-radius: 12px; margin-bottom: 25px; }
        
        .sidebar a { 
            display: block; 
            color: #ffffff !important; 
            text-decoration: none !important; 
            padding: 12px 15px; 
            margin-bottom: 8px; 
            border-radius: 8px; 
            transition: 0.3s; 
        }

        .sidebar a:hover { 
            background-color: var(--dark-blue) !important; 
            padding-left: 20px;
            color: #ffffff !important;
        }

        .sidebar a.active { 
            background-color: var(--dark-blue) !important; 
            font-weight: bold;
            border-left: 4px solid white;
        }
        .sidebar a.logout { margin-top: 50px; background: #ff5e5e !important; }

        /* --- MAIN CONTENT --- */
        .main-content { margin-left: 260px; padding: 40px; width: calc(100% - 260px); }
        .header-section { margin-bottom: 30px; border-left: 5px solid #555; padding-left: 15px; }
        .header-section h1 { font-size: 28px; color: var(--text-dark); }
        
        /* --- TABEL (KUNCI HEADER ABU-ABU) --- */
        .table-container { 
            background: var(--white); border-radius: 15px; 
            overflow: hidden; box-shadow: 0 10px 25px rgba(0,0,0,0.05); 
        }
        table { width: 100%; border-collapse: collapse; background: white; }

        thead { background-color: #555 !important; } /* Warna berbeda agar penjual sadar ini riwayat */
        th { 
            padding: 18px; text-align: left; font-size: 14px; 
            text-transform: uppercase; color: white !important; font-weight: bold;
        }
        
        td { padding: 15px 18px; border-bottom: 1px solid #f2f2f2; vertical-align: middle; color: #555; }
        
        /* Hover Baris Data Saja */
        tbody tr:hover { background-color: #f9f9f9 !important; }

        /* --- BADGE --- */
        .role-badge { 
            font-size: 10px; padding: 2px 8px; border-radius: 4px; 
            background: #eee; color: #666; font-weight: bold; 
            margin-top: 4px; display: inline-block; 
        }
        
        .badge { 
            padding: 6px 12px; border-radius: 20px; font-size: 11px; 
            font-weight: bold; text-transform: uppercase; display: inline-block; 
        }
        .status-selesai { background: #e8f5e9; color: #388e3c; border: 1px solid #c8e6c9; }
        .status-dibatalkan { background: #ffebee; color: #c62828; border: 1px solid #ffcdd2; }

        .btn-detail { 
            text-decoration: none; color: #555; font-weight: 600; 
            border: 2px solid #555; padding: 7px 15px; border-radius: 8px; 
            transition: 0.3s; 
        }
        .btn-detail:hover { background: #555; color: white; }
        .empty-state { padding: 50px; text-align: center; color: #aaa; }

        @media (max-width: 768px) {
            .sidebar { width: 70px; padding: 10px; }
            .sidebar h2, .user-info { display: none; }
            .main-content { margin-left: 70px; width: calc(100% - 70px); }
        }
    </style>
</head>
<body>

    <div class="sidebar">
        <h2>Kantin Kita</h2>
        <div class="user-info">
            <p style="font-size: 12px; opacity: 0.8;">Selamat Datang,</p>
            <b><?= $_SESSION['username'] ?></b>
        </div>
        <hr style="opacity: 0.3; margin-bottom: 20px;">
        <a href="dashboard_penjual.php">Dashboard</a>
        <a href="menu_penjual.php">Kelola Menu</a>
        <a href="pesanan_masuk.php">Pesanan Masuk</a>
        <a href="riwayat_penjual.php" class="active">Riwayat Pesanan</a>
        <a href="logout.php" class="logout">Logout</a>
    </div>

    <div class="main-content">
        <div class="header-section">
            <h1>Riwayat Pesanan</h1>
            <p style="color: #888;">Daftar transaksi yang sudah selesai atau dibatalkan.</p>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Waktu Selesai</th>
                        <th>Pembeli</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if(mysqli_num_rows($query) > 0) {
                        while($p = mysqli_fetch_assoc($query)) : 
                    ?>
                    <tr>
                        <td>#<?= $p['id_transaksi']; ?></td>
                        <td><?= date('d M Y, H:i', strtotime($p['created_at'])); ?> WIB</td>
                        <td>
                            <b><?= strtoupper($p['username']); ?></b><br>
                            <span class="role-badge">
                                <?= strtoupper($p['role']); ?>
                            </span>
                        </td>
                        <td style="font-weight: bold;">
                            Rp <?= number_format($p['total'], 0, ',', '.'); ?>
                        </td>
                        <td>
                            <span class="badge status-<?= $p['status']; ?>">
                                <?= $p['status']; ?>
                            </span>
                        </td>
                        <td>
                            <a href="detail_pesanan_penjual.php?id=<?= $p['id_transaksi']; ?>" class="btn-detail">
                                DETAIL
                            </a>
                        </td>
                    </tr>
                    <?php 
                        endwhile; 
                    } else {
                    ?>
                    <tr>
                        <td colspan="6" class="empty-state">
                            <img src="https://cdn-icons-png.flaticon.com/512/7486/7486744.png" width="60" style="opacity: 0.2; margin-bottom: 10px;"><br>
                            Belum ada riwayat transaksi yang tersimpan.
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>