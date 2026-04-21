<?php
session_start();
include 'config.php';

/**
 * 1. PROTEKSI HALAMAN
 * Pastikan user sudah login (punya id_user dari proses pendaftaran/login)
 * Jika tidak ada, maka akan dilempar balik ke login.php
 */
if (!isset($_SESSION['id_user'])) {
    header("Location: login.php?pesan=belum_login");
    exit();
}

/**
 * 2. LOGIKA PROSES PILIH KANTIN
 */
if (isset($_POST['submit_kantin'])) {
    
    // Ambil data dari form dengan filter keamanan
    $no_kantin   = mysqli_real_escape_string($koneksi, $_POST['no_kantin']);
    $pass_kantin = mysqli_real_escape_string($koneksi, $_POST['pass_kantin']);
    
    // Ambil ID User dari session yang aktif
    $id_user_aktif = $_SESSION['id_user'];

    // 3. VALIDASI: Cek apakah password kantin sesuai di tabel kantin
    // Pastikan nama kolom di database 'pasword_kantin' (sesuai kode sebelumnya)
    $query_cek = mysqli_query($koneksi, "SELECT * FROM kantin WHERE id_kantin = '$no_kantin' AND pasword_kantin = '$pass_kantin'");

    if (mysqli_num_rows($query_cek) > 0) {
        
        // 4. UPDATE: Simpan pilihan kantin ke tabel users
        $sql_update = "UPDATE users SET id_kantin = '$no_kantin' WHERE id_user = '$id_user_aktif'";
        
        if (mysqli_query($koneksi, $sql_update)) {
            
            // 5. UPDATE SESSION: Agar id_kantin bisa langsung dipakai di halaman dashboard
            $_SESSION['id_kantin'] = $no_kantin;
            
            // Tutup session write agar data tersimpan sebelum redirect JavaScript
            session_write_close();

            echo "<script>
                    alert('Verifikasi Berhasil! Selamat datang di Kantin nomor $no_kantin.');
                    window.location='loading.php';
                  </script>";
            exit();

        } else {
            // Jika query UPDATE gagal
            echo "Error saat menyimpan data: " . mysqli_error($koneksi);
        }

    } else {
        // 6. JIKA PASSWORD KANTIN SALAH
        echo "<script>
                alert('Password Kantin Salah! Silakan cek kembali atau hubungi Admin.');
                window.location='pilih_kantin.php';
              </script>";
        exit();
    }

} else {
    // Jika mencoba akses file ini secara langsung tanpa submit form
    header("Location: pilih_kantin.php");
    exit();
}
?>