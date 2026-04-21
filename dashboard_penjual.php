<?php
session_start();
include 'config.php';

// 1. PROTEKSI HALAMAN
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'penjual') {
    header("Location: login.php");
    exit;
}

$id_k = $_SESSION['id_kantin'];
$username = $_SESSION['username'];

// 2. LOGIKA LAPORAN (STATISTIK)
// Hitung Total Menu
$q_menu = mysqli_query($koneksi, "SELECT id_menu FROM menu WHERE id_kantin = '$id_k'");
$total_menu = mysqli_num_rows($q_menu);

// Hitung Pesanan Aktif (Pending & Dibayar)
$q_pending = mysqli_query($koneksi, "SELECT id_transaksi FROM transaksi WHERE id_kantin = '$id_k' AND (status = 'pending' OR status = 'dibayar')");
$total_pending = mysqli_num_rows($q_pending);

// Hitung Total Pendapatan (Hanya dari pesanan yang sudah 'selesai')
$q_duit = mysqli_query($koneksi, "SELECT SUM(total) as total_pendapatan FROM transaksi WHERE id_kantin = '$id_k' AND status = 'selesai'");
$data_duit = mysqli_fetch_assoc($q_duit);
$pendapatan = $data_duit['total_pendapatan'] ?? 0;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Penjual - Kantin Kita</title>
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

        /* --- SIDEBAR (FIXED ANTI-HILANG) --- */
        .sidebar {
            width: 260px; height: 100vh; background: var(--primary-blue);
            color: var(--white); padding: 25px 20px; position: fixed;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1); z-index: 1000;
        }
        .sidebar h2 { text-align: center; margin-bottom: 30px; font-size: 22px; font-weight: bold; }
        .user-info {
            text-align: center; background: rgba(255, 255, 255, 0.2);
            padding: 15px; border-radius: 12px; margin-bottom: 25px;
        }
        .sidebar a {
            display: block; color: #ffffff !important; text-decoration: none !important;
            padding: 12px 15px; margin-bottom: 8px; border-radius: 8px; transition: 0.3s;
        }
        .sidebar a:hover { 
            background: var(--dark-blue) !important; 
            padding-left: 20px; 
            color: #ffffff !important;
        }
        .sidebar a.active { 
            background: var(--dark-blue) !important; 
            font-weight: bold; 
            border-left: 4px solid white;
        }
        .sidebar a.logout { margin-top: 50px; background: #ff5e5e !important; font-weight: bold; }

        /* --- MAIN CONTENT --- */
        .main-content { margin-left: 260px; padding: 40px; width: calc(100% - 260px); }
        
        .welcome-msg { margin-bottom: 30px; }
        .welcome-msg h1 { font-size: 28px; color: var(--text-dark); border-left: 5px solid var(--accent-orange); padding-left: 15px; }
        .welcome-msg p { color: #666; margin-top: 5px; }

        /* --- STATISTIC CARDS --- */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }
        .card {
            background: var(--white);
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 10px 20px rgba(0,0,0,0.05);
            display: flex;
            align-items: center;
            transition: 0.3s;
        }
        .card:hover { transform: translateY(-5px); box-shadow: 0 15px 30px rgba(0,0,0,0.1); }
        
        .card-icon {
            width: 65px; height: 65px; border-radius: 15px;
            display: flex; align-items: center; justify-content: center;
            margin-right: 20px; font-size: 28px;
        }
        .icon-menu { background: #e3f2fd; color: #2196f3; }
        .icon-order { background: #fff8e1; color: #ffa000; }
        .icon-money { background: #e8f5e9; color: #2e7d32; }

        .card-info h3 { font-size: 26px; color: var(--text-dark); margin-bottom: 2px; }
        .card-info p { color: #888; font-size: 14px; font-weight: 500; }

        /* --- QUICK ACTIONS --- */
        .quick-actions h2 { margin-bottom: 20px; font-size: 20px; color: var(--text-dark); }
        .action-btns { display: flex; gap: 15px; flex-wrap: wrap; }
        .btn {
            padding: 15px 30px; border-radius: 12px; text-decoration: none;
            font-weight: bold; color: white; transition: 0.3s;
            display: inline-block; text-align: center;
        }
        .btn-blue { background: var(--primary-blue); box-shadow: 0 4px 15px rgba(80, 200, 255, 0.3); }
        .btn-orange { background: var(--accent-orange); box-shadow: 0 4px 15px rgba(255, 152, 0, 0.3); }
        .btn:hover { transform: translateY(-3px); opacity: 0.9; color: white; }

    </style>
</head>
<body>

    <div class="sidebar">
        <h2>Kantin Kita</h2>
        <div class="user-info">
            <p style="font-size: 12px; opacity: 0.8;">Selamat Datang,</p>
            <b><?= $username ?></b>
        </div>
        <hr style="opacity: 0.3; margin-bottom: 20px;">
        <a href="dashboard_penjual.php" class="active">Dashboard</a>
        <a href="menu_penjual.php">Kelola Menu</a>
        <a href="pesanan_masuk.php">Pesanan Masuk</a>
        <a href="riwayat_penjual.php">Riwayat Pesanan</a>
        <a href="logout.php" class="logout">Logout</a>
    </div>

    <div class="main-content">
        <div class="welcome-msg">
            <h1>Dashboard Penjual</h1>
            <p>Halo <b><?= $username ?></b>, berikut ringkasan kantin Anda hari ini.</p>
        </div>

        <div class="stats-grid">
            <div class="card">
                <div class="card-icon icon-menu">🍴</div>
                <div class="card-info">
                    <h3><?= $total_menu ?></h3>
                    <p>Total Menu Aktif</p>
                </div>
            </div>

            <div class="card">
                <div class="card-icon icon-order">🔔</div>
                <div class="card-info">
                    <h3><?= $total_pending ?></h3>
                    <p>Pesanan Perlu Diproses</p>
                </div>
            </div>

            <div class="card">
                <div class="card-icon icon-money">💰</div>
                <div class="card-info">
                    <h3>Rp <?= number_format($pendapatan, 0, ',', '.') ?></h3>
                    <p>Total Pendapatan (Selesai)</p>
                </div>
            </div>
        </div>

        <div class="quick-actions">
            <h2>Akses Cepat</h2>
            <div class="action-btns">
                <a href="tambah_menu.php" class="btn btn-orange">+ Tambah Menu Baru</a>
                <a href="pesanan_masuk.php" class="btn btn-blue">Cek Pesanan Masuk</a>
                <a href="riwayat_penjual.php" class="btn" style="background: #888;">Lihat Riwayat</a>
            </div>
        </div>
    </div>

</body>
</html>