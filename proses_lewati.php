<?php
// 1. Memulai session agar data login tidak hilang
session_start();

// 2. Sertakan koneksi (jika nanti kamu ingin mencatat siapa yang melewati profil di database)
include 'config.php';

// 3. KEAMANAN: Jika user belum login tapi nekat buka file ini, tendang ke login.php
if (!isset($_SESSION['id_user'])) {
    header("Location: login.php");
    exit();
}

// 4. AMBIL DATA DARI SESSION
$id_user = $_SESSION['id_user'];
$role    = $_SESSION['role'];

/** * 5. LOGIKA PENGALIHAN BERDASARKAN ROLE
 * Kita pakai huruf kecil sesuai struktur ENUM di database kamu (siswa, guru, penjual, admin)
 */

if ($role == 'penjual') {
    // Jika penjual belum setting kantin, arahkan ke pilih_kantin.php
    header("Location: pilih_kantin.php");
} elseif ($role == 'admin') {
    // Jika admin, arahkan ke dashboard admin
    header("Location: dashboard_admin.php");
} else {
    /** * UNTUK SISWA / GURU (PEMBELI)
     * Langsung diarahkan ke dashboard.php (Pastikan nama file di folder benar)
     */
    header("Location: loading.php");
}

// 6. WAJIB EXIT: Agar sisa script di bawah tidak dijalankan oleh server
exit();
?>