<?php
session_start();
include 'config.php';

// 1. KEAMANAN: Hanya Admin yang boleh menghapus
if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// 2. Validasi apakah ada ID yang dikirim melalui URL
if (isset($_GET['id'])) {
    // Menggunakan mysqli_real_escape_string untuk mencegah SQL Injection
    $id_user = mysqli_real_escape_string($koneksi, $_GET['id']);

    // 3. CEK: Jangan biarkan admin menghapus dirinya sendiri
    if ($id_user == $_SESSION['id_user']) {
        echo "<script>
                alert('Gagal! Anda tidak bisa menghapus akun Anda sendiri.');
                window.location='dashboard_admin.php';
              </script>";
        exit();
    }

    // 4. PROSES HAPUS
    $query = "DELETE FROM users WHERE id_user = '$id_user'";

    if (mysqli_query($koneksi, $query)) {
        // Berhasil dihapus
        echo "<script>
                alert('User berhasil dihapus!');
                window.location='dashboard_admin.php';
              </script>";
    } else {
        // Gagal dihapus
        echo "Gagal menghapus user: " . mysqli_error($koneksi);
    }
} else {
    // Jika tidak ada ID di URL, kembalikan ke dashboard
    header("Location: dashboard_admin.php");
    exit();
}
?>