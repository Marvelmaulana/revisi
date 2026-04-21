<?php
session_start();
include 'config.php';

// Cek akses penjual
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'penjual') {
    header("Location: login.php");
    exit;
}

// Ambil ID menu yang mau diedit
$id_menu = $_GET['id'];
$query = mysqli_query($koneksi, "SELECT * FROM menu WHERE id_menu = '$id_menu'");
$data = mysqli_fetch_assoc($query);

// Jika menu tidak ditemukan atau bukan milik kantin si penjual
if (!$data || $data['id_kantin'] != $_SESSION['id_kantin']) {
    echo "<script>alert('Akses dilarang!'); window.location='menu_penjual.php';</script>";
    exit;
}

if (isset($_POST['update'])) {
    $nama     = mysqli_real_escape_string($koneksi, $_POST['nama_menu']);
    $harga    = $_POST['harga'];
    $kategori = $_POST['kategori'];
    $status   = $_POST['status']; // Tambahan Status Tersedia/Habis

    // Cek apakah ada foto baru yang diupload
    if ($_FILES['foto']['name'] != "") {
        $foto = $_FILES['foto']['name'];
        $tmp  = $_FILES['foto']['tmp_name'];
        move_uploaded_file($tmp, "uploads/" . $foto);
        
        // Update dengan foto baru
        $sql = "UPDATE menu SET nama_menu='$nama', harga='$harga', kategori='$kategori', foto_menu='$foto', status='$status' WHERE id_menu='$id_menu'";
    } else {
        // Update tanpa mengubah foto
        $sql = "UPDATE menu SET nama_menu='$nama', harga='$harga', kategori='$kategori', status='$status' WHERE id_menu='$id_menu'";
    }

    if (mysqli_query($koneksi, $sql)) {
        echo "<script>alert('Menu berhasil diperbarui!'); window.location='menu_penjual.php';</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Menu - Kantin Kita</title>
    <style>
        :root { --primary: #50c8ff; --accent: #ff9800; --bg: #f8fbff; }
        body { font-family: 'Segoe UI', sans-serif; background: var(--bg); display: flex; justify-content: center; padding: 50px; }
        .card { background: white; width: 450px; padding: 30px; border-radius: 20px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); }
        h2 { text-align: center; color: #333; margin-bottom: 25px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; color: #666; font-weight: bold; font-size: 14px; }
        input, select { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 10px; box-sizing: border-box; }
        .btn-update { width: 100%; padding: 15px; background: var(--accent); border: none; color: white; font-weight: bold; border-radius: 10px; cursor: pointer; margin-top: 10px; }
        .btn-batal { display: block; text-align: center; margin-top: 15px; color: #999; text-decoration: none; }
        .current-img { width: 100px; height: 100px; border-radius: 10px; object-fit: cover; margin-bottom: 10px; border: 2px solid #eee; }
    </style>
</head>
<body>

<div class="card">
    <h2>Edit Menu</h2>
    <form action="" method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label>Nama Menu</label>
            <input type="text" name="nama_menu" value="<?= $data['nama_menu'] ?>" required>
        </div>

        <div class="form-group">
            <label>Harga (Rp)</label>
            <input type="number" name="harga" value="<?= $data['harga'] ?>" required>
        </div>

        <div class="form-group">
            <label>Kategori</label>
            <select name="kategori">
                <option value="Makanan" <?= $data['kategori'] == 'Makanan' ? 'selected' : '' ?>>Makanan</option>
                <option value="Minuman" <?= $data['kategori'] == 'Minuman' ? 'selected' : '' ?>>Minuman</option>
                <option value="Cemilan" <?= $data['kategori'] == 'Cemilan' ? 'selected' : '' ?>>Cemilan</option>
            </select>
        </div>

        <div class="form-group">
            <label>Status Ketersediaan</label>
            <select name="status">
                <option value="Tersedia" <?= $data['status'] == 'Tersedia' ? 'selected' : '' ?>>Tersedia (Ready)</option>
                <option value="Habis" <?= $data['status'] == 'Habis' ? 'selected' : '' ?>>Habis (Sold Out)</option>
            </select>
        </div>

        <div class="form-group">
            <label>Foto Saat Ini</label>
            <img src="uploads/<?= $data['foto_menu'] ?>" class="current-img"><br>
            <label>Ganti Foto (Kosongkan jika tidak ingin ganti)</label>
            <input type="file" name="foto" accept="image/*">
        </div>

        <button type="submit" name="update" class="btn-update">Simpan Perubahan</button>
        <a href="menu_penjual.php" class="btn-batal">Batal</a>
    </form>
</div>

</body>
</html>