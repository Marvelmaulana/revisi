<?php
session_start();
include 'config.php';

// 1. PENGUNCI SESSION: Supaya tidak mental kembali ke login jika sudah login
if (!isset($_SESSION['id_user'])) {
    header("Location: login.php");
    exit();
}

$id_user = $_SESSION['id_user'];

// 2. AMBIL DATA USER (Untuk nama profil)
$query_user = mysqli_query($koneksi, "SELECT * FROM users WHERE id_user = '$id_user'");
$user = mysqli_fetch_assoc($query_user);

// 3. AMBIL DATA KANTIN (Dinamis dari database kamu)
$query_kantin = mysqli_query($koneksi, "SELECT * FROM kantin ORDER BY id_kantin ASC");

// 4. AMBIL MENU TERBARU
$query_terbaru = mysqli_query($koneksi, "SELECT * FROM menu ORDER BY id_menu DESC LIMIT 4");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kantin Kita - Home</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root { --blue: #50c8ff; --orange: #ffb74d; --gray: #f2f2f2; }
        * { box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background: white; margin: 0; padding-bottom: 90px; overflow-x: hidden; }

        /* Header Biru */
        .header-top { background: var(--blue); padding: 45px 20px 65px; text-align: center; color: white; border-bottom-left-radius: 5px; border-bottom-right-radius: 5px; position: relative; }
        .logo-box { display: flex; align-items: center; justify-content: center; gap: 12px; }
        .logo-box i { background: white; color: var(--blue); padding: 10px; border-radius: 15px; font-size: 20px; }
        .logo-box h2 { font-style: italic; margin: 0; font-size: 24px; letter-spacing: 1px; }
        
        /* Nama User */
        .user-welcome { position: absolute; top: 15px; left: 20px; font-size: 12px; color: white; font-weight: bold; }

        /* Search Bar melayang */
        .search-container { margin: -30px 25px 0; position: relative; z-index: 10; }
        .search-container input { 
            width: 100%; padding: 16px 20px; border-radius: 30px; 
            border: 1px solid #ddd; box-shadow: 0 4px 12px rgba(0,0,0,0.1); outline: none; font-size: 14px;
        }
        .search-container i { position: absolute; right: 20px; top: 18px; color: #444; font-size: 18px; }

        /* Kategori Chips */
        .chip-wrapper { padding: 25px 10px 10px; text-align: center; }
        .chip-row { display: flex; justify-content: center; gap: 10px; margin-bottom: 12px; }
        .chip { background: var(--gray); border: 1px solid #e0e0e0; padding: 8px 22px; border-radius: 20px; font-size: 12px; font-weight: 500; color: #333; }

        /* Label Section */
        .section-label { padding: 10px 25px; font-weight: bold; font-size: 15px; display: flex; align-items: center; gap: 8px; color: #333; }

        /* Horizontal Scroll Menu Terbaru */
        .menu-row { display: flex; overflow-x: auto; padding: 0 25px 20px; gap: 15px; scrollbar-width: none; }
        .menu-row::-webkit-scrollbar { display: none; }
        .card-item { min-width: 165px; background: white; border-radius: 20px; box-shadow: 0 5px 15px rgba(0,0,0,0.08); overflow: hidden; position: relative; }
        .card-item img { width: 100%; height: 125px; object-fit: cover; }
        .btn-fav { position: absolute; top: 12px; right: 12px; background: white; color: #666; padding: 6px; border-radius: 50%; font-size: 11px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); text-decoration: none; }
        .card-body { background: var(--orange); padding: 10px 12px; }
        .card-body h4 { margin: 0; font-size: 13px; color: #222; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .card-footer { display: flex; justify-content: space-between; align-items: center; margin-top: 5px; font-size: 11px; font-weight: bold; }

        /* Tombol List Kantin (Dinamis) */
        .btn-kantin-list { 
            display: flex; justify-content: space-between; align-items: center;
            background: var(--orange); margin: 10px 25px; padding: 15px 25px; 
            border-radius: 35px; text-decoration: none; color: #333; font-weight: bold; font-size: 14px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.05); transition: 0.2s;
        }
        .btn-kantin-list:active { transform: scale(0.95); }

        /* Navigation Bawah */
        .footer-nav { 
            position: fixed; bottom: 0; width: 100%; height: 75px; 
            background: #e0e0e0; border-top: 4px solid var(--blue); 
            display: flex; align-items: center; justify-content: space-around; z-index: 100;
        }
        .nav-item { color: #333; font-size: 26px; text-decoration: none; width: 25%; height: 100%; display: flex; align-items: center; justify-content: center; transition: 0.3s; }
        .nav-item.active { background: var(--orange); }
    </style>
</head>
<body>

    <div class="header-top">
        <div class="user-welcome">Halo, <?= htmlspecialchars($user['username'] ?? 'User'); ?>!</div>
        <div class="logo-box">
            <i class="fas fa-home"></i>
            <h2>Kantin Kita</h2>
        </div>
    </div>

    <div class="search-container">
        <input type="text" placeholder="Cari makanan atau kantin...">
        <i class="fas fa-search"></i>
    </div>

    <div class="chip-wrapper">
        <div class="chip-row">
            <div class="chip">Favorit</div>
            <div class="chip">Kantin</div>
            <div class="chip">Makanan</div>
        </div>
        <div class="chip-row">
            <div class="chip">Minuman</div>
            <div class="chip">Camilan</div>
        </div>
    </div>

    <div class="section-label"><i class="far fa-clock"></i> Menu Terbaru</div>
    <div class="menu-row">
        <?php if(mysqli_num_rows($query_terbaru) > 0): ?>
            <?php while($m = mysqli_fetch_assoc($query_terbaru)) : ?>
            <div class="card-item">
                <a href="#" class="btn-fav"><i class="far fa-heart"></i></a>
                <img src="uploads/<?= $m['foto_menu'] ?>" onerror="this.src='https://via.placeholder.com/150'">
                <div class="card-body">
                    <h4><?= $m['nama_menu'] ?></h4>
                    <div class="card-footer">
                        <span>Rp <?= number_format($m['harga'], 0, ',', '.') ?></span>
                        <span><i class="fas fa-star"></i> 4.8</span>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p style="padding-left: 25px; font-size: 12px; color: #888;">Belum ada menu.</p>
        <?php endif; ?>
    </div>

    <div class="section-label"><i class="fas fa-store"></i> Pilih Kantin</div>
    <?php if(mysqli_num_rows($query_kantin) > 0): ?>
        <?php while($k = mysqli_fetch_assoc($query_kantin)) : ?>
        <a href="menu_kantin.php?id=<?= $k['id_kantin'] ?>" class="btn-kantin-list">
            <span><?= $k['nama_kantin'] ?></span>
            <span style="font-size: 11px; font-weight: normal;">Lihat Menu <i class="fas fa-arrow-alt-circle-right"></i></span>
        </a>
        <?php endwhile; ?>
    <?php else: ?>
        <p style="text-align: center; color: #888;">Data kantin tidak ditemukan di database.</p>
    <?php endif; ?>

    <nav class="footer-nav">
        <a href="dashboard.php" class="nav-item active"><i class="fas fa-home"></i></a>
        <a href="keranjang.php" class="nav-item"><i class="fas fa-shopping-basket"></i></a>
        <a href="riwayat_pembeli.php" class="nav-item"><i class="fas fa-comment-dots"></i></a>
        <a href="profil.php" class="nav-item"><i class="fas fa-user-circle"></i></a>
    </nav>

</body>
</html>