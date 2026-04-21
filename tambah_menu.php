<?php
session_start();
include 'config.php';

// Pastikan hanya penjual yang bisa akses
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'penjual') {
    header("Location: login.php");
    exit;
}

if (isset($_POST['submit'])) {
    $id_kantin = $_SESSION['id_kantin']; 
    $nama      = mysqli_real_escape_string($koneksi, $_POST['nama_menu']);
    $harga     = $_POST['harga'];
    $kategori  = $_POST['kategori'];

    // Proses Foto
    $foto = $_FILES['foto']['name'];
    $tmp  = $_FILES['foto']['tmp_name'];
    
    // Pastikan folder 'uploads' sudah ada
    if (move_uploaded_file($tmp, "uploads/".$foto)) {
        $sql = "INSERT INTO menu (id_kantin, nama_menu, harga, kategori, foto_menu, status) 
                VALUES ('$id_kantin', '$nama', '$harga', '$kategori', '$foto', 'Tersedia')";
        
        if (mysqli_query($koneksi, $sql)) {
            echo "<script>alert('Menu berhasil ditambah!'); window.location='menu_penjual.php';</script>";
        }
    } else {
        echo "<script>alert('Gagal mengunggah foto!');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Menu - Kantin Kita</title>
    <link rel="stylesheet" href="style_penjual.css">
    <style>
        /* CSS Tambahan khusus untuk Form agar rapi di tengah */
        .form-container {
            max-width: 500px;
            margin: 20px auto;
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: bold;
        }
        .form-group input, .form-group select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            outline: none;
        }
        .form-group input:focus {
            border-color: #50c8ff;
        }
        .btn-simpan {
            width: 100%;
            padding: 15px;
            background: #ff9800;
            border: none;
            color: white;
            font-weight: bold;
            border-radius: 8px;
            cursor: pointer;
            transition: 0.3s;
        }
        .btn-simpan:hover {
            background: #e68a00;
        }
        .btn-batal {
            display: block;
            text-align: center;
            margin-top: 15px;
            color: #888;
            text-decoration: none;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>Kantin Kita</h2>
        <div class="user-info">
            <p>Selamat Datang,</p>
            <b><?= $_SESSION['username'] ?></b>
        </div>
        <hr>
        <a href="dashboard_penjual.php">Dashboard</a>
        <a href="menu_penjual.php" class="active">Kelola Menu</a>
        <a href="logout.php" class="logout">Logout</a>
    </div>

    <div class="main-content">
        <h1>Tambah Menu Baru</h1>
        
        <div class="form-container">
            <form action="" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Nama Makanan/Minuman</label>
                    <input type="text" name="nama_menu" placeholder="Contoh: Nasi Goreng Spesial" required>
                </div>

                <div class="form-group">
                    <label>Harga (Rp)</label>
                    <input type="number" name="harga" placeholder="Contoh: 15000" required>
                </div>

                <div class="form-group">
                    <label>Kategori</label>
                    <select name="kategori">
                        <option value="Makanan">Makanan</option>
                        <option value="Minuman">Minuman</option>
                        <option value="Cemilan">Cemilan</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Foto Menu</label>
                    <input type="file" name="foto" accept="image/*" required>
                </div>

                <button type="submit" name="submit" class="btn-simpan">Simpan Menu</button>
                <a href="menu_penjual.php" class="btn-batal">Kembali ke Daftar Menu</a>
            </form>
        </div>
    </div>
</body>
</html>