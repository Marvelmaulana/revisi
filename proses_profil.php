<?php
session_start();
include 'config.php';

if (isset($_POST['simpan_profil'])) {
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama_lengkap']);
    $email_user = $_SESSION['email']; 

    // Ambil data role user dari database (Penting untuk pengalihan halaman nanti)
    $ambil_user = mysqli_query($koneksi, "SELECT role FROM users WHERE email='$email_user'");
    $data_user = mysqli_fetch_assoc($ambil_user);
    $role = $data_user['role'];

    // Simpan role ke session agar bisa dipakai di halaman lain
    $_SESSION['role'] = $role;

    // Logika Upload Foto
    $nama_file = $_FILES['foto_profil']['name'];
    $error = $_FILES['foto_profil']['error'];
    $tmpName = $_FILES['foto_profil']['tmp_name'];

    if ($error === 0) {
        $ekstensiValid = ['jpg', 'jpeg', 'png'];
        $ekstensiFile = strtolower(pathinfo($nama_file, PATHINFO_EXTENSION));

        if (!in_array($ekstensiFile, $ekstensiValid)) {
            echo "<script>alert('Format gambar harus jpg, jpeg, atau png!'); window.location='lengkapi_profil.php';</script>";
            exit;
        }

        // Generate nama file baru
        $namaFileBaru = uniqid() . '.' . $ekstensiFile;

        // Pastikan folder 'uploads' sudah ada di folder htdocs/kantin/ kamu
        move_uploaded_file($tmpName, 'uploads/' . $namaFileBaru);

        $sql = "UPDATE users SET username='$nama', foto='$namaFileBaru' WHERE email='$email_user'";
    } else {
        $sql = "UPDATE users SET username='$nama' WHERE email='$email_user'";
    }

    if (mysqli_query($koneksi, $sql)) {
        $_SESSION['username'] = $nama;

        // LOGIKA PEMISAH DASHBOARD (Redirect)
        if ($role == 'penjual') {
            echo "<script>alert('Profil Penjual diperbarui!'); window.location='dashboard_penjual.php';</script>";
        } elseif ($role == 'admin') {
            echo "<script>alert('Profil Admin diperbarui!'); window.location='dashboard_admin.php';</script>";
        } else {
            // Untuk siswa atau guru
            echo "<script>alert('Profil diperbarui!'); window.location='dashboard.php';</script>";
        }
    } else {
        echo "Error: " . mysqli_error($koneksi);
    }
}
?>