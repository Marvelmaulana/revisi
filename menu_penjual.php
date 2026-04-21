<?php
session_start();
include 'config.php';

// Pastikan hanya penjual yang bisa akses
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'penjual') {
    header("Location: login.php");
    exit;
}

$id_k = $_SESSION['id_kantin'];

// Ambil data menu milik kantin ini saja
$query = mysqli_query($koneksi, "SELECT * FROM menu WHERE id_kantin = '$id_k' ORDER BY id_menu DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Menu - Kantin Kita</title>
    <style>
        :root {
            --primary-blue: #50c8ff;
            --dark-blue: #3da8db;
            --accent-orange: #ff9800;
            --bg-light: #f8fbff;
            --white: #ffffff;
            --text-dark: #333;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', sans-serif; }
        body { background-color: var(--bg-light); display: flex; }

        /* --- SIDEBAR (ANTI HILANG) --- */
        .sidebar { 
            width: 260px; height: 100vh; background: var(--primary-blue); 
            color: var(--white); padding: 25px 20px; position: fixed; z-index: 1000;
        }
        .sidebar h2 { text-align: center; margin-bottom: 30px; font-size: 22px; font-weight: bold; }
        .user-info { text-align: center; background: rgba(255, 255, 255, 0.2); padding: 15px; border-radius: 12px; margin-bottom: 25px; }

        .sidebar a { 
            display: block; 
            color: #ffffff !important; /* Paksa teks tetap putih */
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
        .main-content h1 { color: var(--text-dark); font-size: 28px; margin-bottom: 20px; border-left: 5px solid var(--accent-orange); padding-left: 15px; }

        /* BUTTON TAMBAH */
        .btn-tambah {
            display: inline-block; background: var(--accent-orange); color: white;
            padding: 12px 25px; border-radius: 30px; text-decoration: none;
            font-weight: bold; margin-bottom: 25px; box-shadow: 0 4px 15px rgba(255, 152, 0, 0.3); transition: 0.3s;
        }
        .btn-tambah:hover { transform: translateY(-3px); background: #e68a00; color: white; }

        /* --- TABEL (KUNCI HEADER BIRU) --- */
        .table-container { background: var(--white); border-radius: 15px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
        table { width: 100%; border-collapse: collapse; background: white; }

        thead { background-color: var(--primary-blue) !important; }
        th { 
            padding: 18px; text-align: left; font-weight: 600; 
            text-transform: uppercase; font-size: 13px; color: white !important; 
        }

        td { padding: 15px 18px; border-bottom: 1px solid #f0f0f0; vertical-align: middle; color: #555; }
        
        /* Hanya hover bagian isi saja */
        tbody tr:hover { background-color: #f0faff !important; }

        .img-menu { border-radius: 10px; object-fit: cover; border: 2px solid #eee; }

        .status-badge { background: #e8f5e9; color: #2e7d32; padding: 5px 12px; border-radius: 20px; font-size: 11px; font-weight: bold; }

        /* ACTION BUTTONS */
        .btn-edit { color: #3f51b5; text-decoration: none; font-weight: bold; margin-right: 15px; }
        .btn-hapus { color: #f44336; text-decoration: none; font-weight: bold; }
        .btn-edit:hover, .btn-hapus:hover { text-decoration: underline; }

        @media (max-width: 768px) {
            .sidebar { width: 70px; padding: 10px; }
            .sidebar h2, .sidebar p, .sidebar b { display: none; }
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
        <a href="menu_penjual.php" class="active">Kelola Menu</a>
        <a href="pesanan_masuk.php">Pesanan Masuk</a>
        <a href="riwayat_penjual.php">Riwayat Pesanan</a>
        <a href="logout.php" class="logout">Logout</a>
    </div>

    <div class="main-content">
        <h1>Daftar Menu Kantin</h1>
        
        <a href="tambah_menu.php" class="btn-tambah">+ Tambah Menu Baru</a>
        
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Foto</th>
                        <th>Nama Menu</th>
                        <th>Harga</th>
                        <th>Kategori</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $no = 1; 
                    if(mysqli_num_rows($query) > 0) {
                        while($m = mysqli_fetch_assoc($query)) : 
                    ?>
                    <tr>
                        <td><?= $no++; ?></td>
                        <td>
                            <?php if($m['foto_menu']) : ?>
                                <img src="uploads/<?= $m['foto_menu']; ?>" width="60" height="60" class="img-menu">
                            <?php else : ?>
                                <div style="width:60px; height:60px; background:#ddd; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:10px; color:#888;">No Image</div>
                            <?php endif; ?>
                        </td>
                        <td><b><?= htmlspecialchars($m['nama_menu']); ?></b></td>
                        <td>Rp <?= number_format($m['harga'], 0, ',', '.'); ?></td>
                        <td><?= htmlspecialchars($m['kategori']); ?></td>
                        <td>
                            <span class="status-badge">✔ <?= strtoupper($m['status']); ?></span>
                        </td>
                        <td>
                            <a href="edit_menu.php?id=<?= $m['id_menu']; ?>" class="btn-edit">Edit</a>
                            <a href="hapus_menu.php?id=<?= $m['id_menu']; ?>" class="btn-hapus" onclick="return confirm('Yakin ingin menghapus menu ini?')">Hapus</a>
                        </td>
                    </tr>
                    <?php 
                        endwhile; 
                    } else {
                    ?>
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 40px; color: #999;">
                            <img src="https://cdn-icons-png.flaticon.com/512/7486/7486744.png" width="50" style="opacity: 0.2; margin-bottom: 10px;"><br>
                            Belum ada menu yang ditambahkan.
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>