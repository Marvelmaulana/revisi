<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Lengkapi Profil - Kantin Kita</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', sans-serif; }
        body { background: #111; display: flex; justify-content: center; align-items: center; min-height: 100vh; }
        .card { background: white; width: 360px; border-radius: 25px; overflow: hidden; padding: 30px; text-align: center; box-shadow: 0 10px 25px rgba(0,0,0,0.5); }
        
        .profile-upload { position: relative; width: 150px; height: 150px; margin: 0 auto 30px; }
        .circle { width: 100%; height: 100%; border-radius: 50%; border: 2px solid #ccc; overflow: hidden; background: #eee; display: flex; justify-content: center; align-items: center; }
        .circle img { width: 100%; height: 100%; object-fit: cover; }
        
        /* Tombol kamera kecil warna oranye */
        .camera-btn { position: absolute; bottom: 5px; right: 5px; background: #ff9800; width: 40px; height: 40px; border-radius: 50%; border: 3px solid white; display: flex; justify-content: center; align-items: center; cursor: pointer; }
        
        h2 { margin-bottom: 30px; color: #333; font-weight: 400; }
        input[type="text"] { width: 100%; padding: 15px; margin-bottom: 30px; border-radius: 30px; border: 1px solid #ddd; background: #e0e0e0; outline: none; text-align: center; }
        
        .btn-group { display: flex; gap: 10px; }
        .btn-lewat { flex: 1; padding: 12px; border-radius: 30px; border: 1px solid #999; background: #ccc; color: #333; text-decoration: none; font-weight: bold; font-size: 14px; }
        .btn-selesai { flex: 1; padding: 12px; border-radius: 30px; border: none; background: #ff9800; color: white; font-weight: bold; cursor: pointer; font-size: 14px; }
    </style>
</head>
<body>
    <div class="card">
        <h2>Lengkapi Profilmu</h2>
        
        <!-- Form harus pakai enctype="multipart/form-data" supaya bisa kirim file -->
        <form action="proses_profil.php" method="POST" enctype="multipart/form-data">
            <div class="profile-upload">
                <div class="circle" id="preview-box">
                    <img src="https://cdn-icons-png.flaticon.com/512/149/149071.png" id="preview-img">
                </div>
                <label for="input-foto" class="camera-btn">
                    <img src="https://cdn-icons-png.flaticon.com/512/685/685655.png" width="20">
                </label>
                <!-- Input file asli kita sembunyikan, tapi tetap berfungsi lewat label -->
                <input type="file" name="foto_profil" id="input-foto" style="display: none;" accept="image/*">
            </div>

            <input type="text" name="nama_lengkap" placeholder="Masukkan nama" required>

            <div class="btn-group">
                <a href="proses_lewati.php" class="btn-lewat">Lewati</a>
                <button type="submit" name="simpan_profil" class="btn-selesai">Selesai</button>
            </div>
        </form>
    </div>

    <!-- Script sederhana untuk melihat pratinjau foto sebelum diupload -->
    <script>
        const inputFoto = document.getElementById('input-foto');
        const previewImg = document.getElementById('preview-img');

        inputFoto.onchange = function() {
            const [file] = inputFoto.files;
            if (file) {
                previewImg.src = URL.createObjectURL(file);
            }
        }
    </script>
</body>
</html>