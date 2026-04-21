<?php
session_start();

// 1. Tangkap data dari form menu_kantin.php
if (isset($_POST['id_menu'])) {
    $id_m  = $_POST['id_menu'];
    $id_k  = $_POST['id_kantin']; // ID Kantin yang sedang dibuka
    $qty   = $_POST['jumlah'];
    $aksi  = $_POST['aksi'];      // Cek apakah tombol "BELI" atau "+KERANJANG"

    // 2. VALIDASI LINTAS KANTIN (Si Satpam)
    // Cek jika sudah ada barang di keranjang DAN id_kantin-nya beda dengan yang sekarang
    if (isset($_SESSION['id_kantin_aktif']) && $_SESSION['id_kantin_aktif'] != $id_k) {
        echo "<script>
                alert('Waduh! Kamu masih punya pesanan di kantin lain. Selesaikan atau kosongkan keranjangmu dulu sebelum pindah kantin!');
                window.location='keranjang.php';
              </script>";
        exit;
    }

    // 3. LOGIKA PEMISAH AKSI
    
    // --- JIKA KLIK "BELI SEKARANG" ---
    if ($aksi == 'beli_langsung') {
        // Kosongkan keranjang lama agar hanya berisi menu ini saja
        unset($_SESSION['keranjang']);
        
        $_SESSION['keranjang'][$id_m] = $qty;
        $_SESSION['id_kantin_aktif'] = $id_k;
        
        // Langsung lempar ke halaman keranjang untuk bayar
        header("Location: keranjang.php");
        exit;
    } 
    
    // --- JIKA KLIK "+ KERANJANG" ---
    else {
        // Jika keranjang benar-benar belum ada, buat array kosong
        if (!isset($_SESSION['keranjang'])) {
            $_SESSION['keranjang'] = [];
        }

        // Jika menu sudah ada di keranjang, tambah jumlahnya saja
        if (isset($_SESSION['keranjang'][$id_m])) {
            $_SESSION['keranjang'][$id_m] += $qty;
        } else {
            // Jika belum ada, masukkan sebagai item baru
            $_SESSION['keranjang'][$id_m] = $qty;
        }

        // Simpan ID kantin sebagai penanda "Kantin Aktif"
        $_SESSION['id_kantin_aktif'] = $id_k;

        // Balik lagi ke halaman menu kantin tadi
        header("Location: menu_kantin.php?id=" . $id_k);
        exit;
    }

} else {
    // Jika ada orang iseng akses file ini tanpa lewat form
    header("Location: dashboard_pembeli.php");
    exit;
}