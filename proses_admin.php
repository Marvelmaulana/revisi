<?php
session_start();
if (isset($_POST['cek_admin'])) {
    $kode = $_POST['kode_admin'];
    
    if ($kode == "9999") { // Kamu bisa ganti kodenya sesukamu
        header("Location: dashboard_admin.php");
    } else {
        echo "<script>alert('Kode Salah!'); window.location='verifikasi_admin.php';</script>";
    }
}
?>