<?php
session_start();
include 'config.php';

// ==========================================
// 1. LOGIKA DAFTAR (REGISTRASI)
// ==========================================
if (isset($_POST['daftar_btn'])) {
    $user = mysqli_real_escape_string($koneksi, $_POST['username']);
    $mail = mysqli_real_escape_string($koneksi, $_POST['email']);
    $pass = mysqli_real_escape_string($koneksi, $_POST['password']);
    
    // Paksa role jadi huruf kecil sesuai tabel ENUM kamu
    $role = strtolower($_POST['role']); 

    // Cek apakah email sudah ada
    $cek_email = mysqli_query($koneksi, "SELECT * FROM users WHERE email='$mail'");
    if (mysqli_num_rows($cek_email) > 0) {
        echo "<script>alert('Email sudah terdaftar! Gunakan email lain.'); window.location='login.php';</script>";
        exit();
    }

    // Simpan ke database
    $sql = "INSERT INTO users (username, email, password, role) VALUES ('$user', '$mail', '$pass', '$role')";
    
    if (mysqli_query($koneksi, $sql)) {
        // Set session otomatis agar dianggap login
        $id_baru = mysqli_insert_id($koneksi);
        $_SESSION['id_user']  = $id_baru;
        $_SESSION['role']     = $role;
        $_SESSION['username'] = $user;

        // Pengarahan setelah daftar
        if ($role == 'penjual') {
            echo "<script>alert('Daftar Berhasil! Pilih kantin anda.'); window.location='pilih_kantin.php';</script>";
        } elseif ($role == 'admin') {
            echo "<script>alert('Daftar Berhasil! Masuk ke verifikasi.'); window.location='verifikasi_admin.php';</script>";
        } else {
            // Siswa/Guru (Pembeli)
            echo "<script>alert('Daftar Berhasil! Lengkapi profilmu.'); window.location='lengkapi_profil.php';</script>";
        }
    } else {
        echo "Gagal mendaftar: " . mysqli_error($koneksi);
    }
    exit();
}

// ==========================================
// 2. LOGIKA LOGIN
// ==========================================
if (isset($_POST['login_btn'])) {
    $mail = mysqli_real_escape_string($koneksi, $_POST['email']);
    $pass = mysqli_real_escape_string($koneksi, $_POST['password']);
    $role = strtolower($_POST['role']); // Paksa huruf kecil sesuai ENUM database

    // Cari user berdasarkan email, password, dan role secara presisi
    $query = mysqli_query($koneksi, "SELECT * FROM users WHERE email='$mail' AND password='$pass' AND role='$role'");

    if (mysqli_num_rows($query) > 0) {
        $data = mysqli_fetch_assoc($query);
        
        // Simpan data ke Session
        $_SESSION['id_user']   = $data['id_user'];
        $_SESSION['role']      = $data['role'];
        $_SESSION['username']  = $data['username'];
        $_SESSION['id_kantin'] = $data['id_kantin'];

        // PENGARAHAN KE LOADING DULU
        if ($data['role'] == 'penjual') {
            // Jika penjual belum pilih kantin
            if (empty($data['id_kantin'])) {
                header("Location: pilih_kantin.php");
            } else {
                header("Location: loading.php");
            }
        } elseif ($data['role'] == 'admin') {
            header("Location: verifikasi_admin.php");
        } else {
            // Siswa/Guru (Pembeli)
            header("Location: loading.php");
        }
        exit();
        
    } else {
        // Jika data tidak cocok
        echo "<script>alert('Login Gagal! Akun tidak ditemukan. Cek Email, Password, atau Role Anda.'); window.location='login.php';</script>";
        exit();
    }
}
?>