<?php
session_start();
include 'config.php';

// 1. PROTEKSI HALAMAN
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'penjual') {
    header("Location: login.php");
    exit;
}

$id_k = $_SESSION['id_kantin'];

// 2. QUERY PESANAN AKTIF
$query = mysqli_query($koneksi, "SELECT transaksi.*, users.username, users.role 
    FROM transaksi 
    JOIN users ON transaksi.id_user = users.id_user 
    WHERE transaksi.id_kantin = '$id_k' 
    AND (transaksi.status = 'pending' OR transaksi.status = 'dibayar')
    ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="refresh" content="30"> 
    <title>Pesanan Masuk - Kantin Kita</title>
    <style>
        :root {
            --primary-blue: #50c8ff;
            --dark-blue: #3da8db;
            --accent-orange: #ff9800;
            --bg-light: #f4f7f6;
            --white: #ffffff;
            --text-dark: #333;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', sans-serif; }
        body { background-color: var(--bg-light); display: flex; }

        /* --- SIDEBAR PERBAIKAN --- */
        .sidebar { width: 260px; height: 100vh; background: var(--primary-blue); color: var(--white); padding: 25px 20px; position: fixed; z-index: 1000;}
        .sidebar h2 { text-align: center; margin-bottom: 30px; font-size: 22px; }
        .user-info { text-align: center; background: rgba(255, 255, 255, 0.2); padding: 15px; border-radius: 12px; margin-bottom: 25px; }
        
        .sidebar a { 
            display: block; 
            color: white !important; /* Paksa warna teks tetap putih */
            text-decoration: none; 
            padding: 12px 15px; 
            margin-bottom: 8px; 
            border-radius: 8px; 
            transition: all 0.3s ease; 
        }

        /* Hover: Agar tidak transparan/hilang */
        .sidebar a:hover { 
            background: var(--dark-blue); 
            padding-left: 20px;
            color: #ffffff !important;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        .sidebar a.active { 
            background: var(--dark-blue); 
            font-weight: bold;
            border-left: 4px solid white;
        }

        .sidebar a.logout { margin-top: 50px; background: #ff5e5e; }
        .sidebar a.logout:hover { background: #d94545; }

        /* --- MAIN CONTENT --- */
        .main-content { margin-left: 260px; padding: 40px; width: calc(100% - 260px); }
        .header-section { margin-bottom: 30px; }
        .header-section h1 { font-size: 28px; color: var(--text-dark); }
        
        /* --- PERBAIKAN TABEL --- */
.table-container { 
    background: var(--white); 
    border-radius: 15px; 
    overflow: hidden; 
    box-shadow: 0 10px 25px rgba(0,0,0,0.05); 
}

table { 
    width: 100%; 
    border-collapse: collapse; 
    background-color: white; /* Pastikan background tabel putih */
}

/* Kunci Header Tabel agar tetap Biru */
thead { 
    background-color: #50c8ff !important; /* Warna Biru Utama */
}

th { 
    padding: 18px; 
    text-align: left; 
    font-size: 14px; 
    text-transform: uppercase; 
    color: white !important; /* Tulisan judul harus tetap putih */
    font-weight: bold;
    border: none;
}

/* Baris data */
td { 
    padding: 15px 18px; 
    border-bottom: 1px solid #f2f2f2; 
    vertical-align: middle; 
    color: #333;
}

/* Efek Hover hanya untuk baris data (tbody), bukan judul (thead) */
tbody tr:hover { 
    background-color: #f0faff !important; /* Biru sangat muda saat diarahkan kursor */
    transition: 0.2s;
}

/* Pastikan header tidak ikut berubah saat di-hover */
thead tr:hover {
    background-color: #50c8ff !important;
}
        /* --- BADGE --- */
        .role-badge { font-size: 10px; padding: 2px 8px; border-radius: 4px; background: #eee; color: #666; font-weight: bold; margin-top: 4px; display: inline-block; }
        .role-guru { background: #fff3e0; color: #e65100; } 
        
        .badge { padding: 6px 12px; border-radius: 20px; font-size: 11px; font-weight: bold; text-transform: uppercase; display: inline-block; }
        .status-pending { background: #fff8e1; color: #ffa000; border: 1px solid #ffe082; }
        .status-dibayar { background: #e3f2fd; color: #1976d2; border: 1px solid #bbdefb; }

        .btn-detail { text-decoration: none; color: var(--primary-blue); font-weight: 600; border: 2px solid var(--primary-blue); padding: 7px 15px; border-radius: 8px; transition: 0.3s; }
        .btn-detail:hover { background: var(--primary-blue); color: white; }
        .empty-state { padding: 50px; text-align: center; color: #aaa; }
    </style>
</head>
<body>

    <div class="sidebar">
        <h2>Kantin Kita</h2>
        <div class="user-info">
            <p>Halo Penjual,</p>
            <b><?= $_SESSION['username'] ?></b>
        </div>
        <hr style="opacity: 0.3; margin-bottom: 20px;">
        <a href="dashboard_penjual.php">Dashboard</a>
        <a href="menu_penjual.php">Kelola Menu</a>
        <a href="pesanan_masuk.php" class="active">Pesanan Masuk</a>
        <a href="riwayat_penjual.php">Riwayat Pesanan</a>
        <a href="logout.php" class="logout">Logout</a>
    </div>

    <div class="main-content">
        <div class="header-section">
            <h1>Pesanan Masuk</h1>
            <p>Daftar pesanan aktif dari Guru & Siswa.</p>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Waktu</th>
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
                        <td><?= date('H:i', strtotime($p['created_at'])); ?></td>
                        <td>
                            <b><?= strtoupper($p['username']); ?></b><br>
                            <span class="role-badge <?= ($p['role'] == 'guru') ? 'role-guru' : ''; ?>">
                                <?= strtoupper($p['role']); ?>
                            </span>
                        </td>
                        <td style="color: #2e7d32; font-weight: bold;">
                            Rp <?= number_format($p['total'], 0, ',', '.'); ?>
                        </td>
                        <td>
                            <span class="badge status-<?= $p['status']; ?>">
                                <?= $p['status']; ?>
                            </span>
                        </td>
                        <td>
                            <a href="detail_pesanan_penjual.php?id=<?= $p['id_transaksi']; ?>" class="btn-detail">
                                LIHAT RINCIAN
                            </a>
                        </td>
                    </tr>
                    <?php 
                        endwhile; 
                    } else {
                    ?>
                    <tr>
                        <td colspan="6" class="empty-state">
                            <img src="https://cdn-icons-png.flaticon.com/512/7486/7486744.png" width="80" style="opacity: 0.2; margin-bottom: 10px;"><br>
                            Belum ada pesanan aktif saat ini.
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>