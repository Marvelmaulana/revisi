<?php
session_start();
include(__DIR__ . '/../../config/config.php');
include(__DIR__ . '/../../includes/pembeli_helpers.php');
kk_ensure_buyer_schema($koneksi);

if (!isset($_SESSION['id_user'])) {
    header("Location: ../auth/login.php");
    exit();
}

$id_user = mysqli_real_escape_string($koneksi, $_SESSION['id_user']);

$metode_bayar = isset($_GET['method'])
    ? mysqli_real_escape_string($koneksi, $_GET['method'])
    : '';

$source = isset($_GET['source'])
    ? $_GET['source']
    : '';

$catatan = isset($_GET['catatan'])
    ? mysqli_real_escape_string($koneksi, $_GET['catatan'])
    : '';

$selected_raw = isset($_GET['selected'])
    ? $_GET['selected']
    : '';

$selected_ids = array_filter(
    array_map('intval', explode(',', $selected_raw))
);

$ids_str = implode(',', $selected_ids);

$tanggal = date('Y-m-d H:i:s');

//
// =====================================
// VALIDASI METODE PEMBAYARAN
// =====================================
//

if (empty($metode_bayar)) {
    die("Metode pembayaran belum dipilih.");
}

//
// =====================================
// MODE CHECKOUT DARI KERANJANG
// =====================================
//

if ($source == 'cart') {
    if (empty($ids_str)) {
        die("Tidak ada item yang dipilih.");
    }

    $q_items = mysqli_query($koneksi, "
        SELECT
            keranjang.id_keranjang,
            keranjang.id_menu,
            keranjang.qty,
            keranjang.catatan,
            keranjang.opsi_pilihan,
            menu.nama_menu,
            menu.harga,
            menu.id_kantin
        FROM keranjang
        JOIN menu ON keranjang.id_menu = menu.id_menu
        WHERE keranjang.id_user = '$id_user'
        AND keranjang.id_keranjang IN ($ids_str)
        ORDER BY menu.id_kantin
    ");

    if (!$q_items) {
        die(mysqli_error($koneksi));
    }

    $groups = [];
    while ($item = mysqli_fetch_assoc($q_items)) {
        $id_k = (int)$item['id_kantin'];
        $item['qty'] = max(1, (int)$item['qty']);
        $item['harga'] = (float)$item['harga'];
        $item['subtotal'] = $item['harga'] * $item['qty'];
        $groups[$id_k][] = $item;
    }

    if (empty($groups)) {
        die("Item keranjang tidak ditemukan.");
    }

    $created = [];
    foreach ($groups as $id_kantin => $items) {
        $total_bayar = array_sum(array_column($items, 'subtotal'));
        $kode_pesanan = "PSN" . date('YmdHis') . $id_kantin . mt_rand(10, 99);

        $query_pesanan = mysqli_query($koneksi, "
            INSERT INTO pesanan
            (kode_pesanan, id_user, id_kantin, tanggal, total_harga, metode_pembayaran, status, catatan)
            VALUES
            ('$kode_pesanan', '$id_user', '$id_kantin', '$tanggal', '$total_bayar', '$metode_bayar', 'Pending', '$catatan')
        ");

        if (!$query_pesanan) {
            die("Gagal membuat pesanan: " . mysqli_error($koneksi));
        }

        $id_pesanan_baru = mysqli_insert_id($koneksi);
        $created[] = $id_pesanan_baru;

        foreach ($items as $item) {
            $id_m = (int)$item['id_menu'];
            $qty_m = (int)$item['qty'];
            $harga_m = (float)$item['harga'];
            $subtotal_m = (float)$item['subtotal'];
            $nama_m = mysqli_real_escape_string($koneksi, $item['nama_menu']);
            $catatan_m = mysqli_real_escape_string($koneksi, trim(($item['opsi_pilihan'] ? $item['opsi_pilihan'] . ' - ' : '') . ($item['catatan'] ?? '')));
            $opsi_m = mysqli_real_escape_string($koneksi, $item['opsi_pilihan'] ?? '');

            mysqli_query($koneksi, "
                INSERT INTO detail_pesanan
                (id_pesanan, id_menu, qty, harga, subtotal, nama_menu, catatan, opsi_pilihan)
                VALUES
                ('$id_pesanan_baru', '$id_m', '$qty_m', '$harga_m', '$subtotal_m', '$nama_m', '$catatan_m', '$opsi_m')
            ");
        }
    }

    mysqli_query($koneksi, "
        DELETE FROM keranjang
        WHERE id_user = '$id_user'
        AND id_keranjang IN ($ids_str)
    ");

    $redirectId = $created[0] ?? 0;
    if (count($created) === 1 && $redirectId > 0) {
        header("Location: pembayaran_berhasil.php?id_pesanan=$redirectId");
    } else {
        header("Location: pesanan.php?success=checkout_multi");
    }
    exit();
} else {

//
// =====================================
// MODE BELI LANGSUNG
// =====================================
//

    $id_menu = isset($_GET['id_menu'])
        ? mysqli_real_escape_string($koneksi, $_GET['id_menu'])
        : '';

    $qty = isset($_GET['qty'])
        ? (int)$_GET['qty']
        : 1;
    $opsi = isset($_GET['opsi'])
        ? mysqli_real_escape_string($koneksi, $_GET['opsi'])
        : '';

    if (empty($id_menu)) {
        die("Menu tidak ditemukan.");
    }

    // ambil data menu
    $q_menu = mysqli_query($koneksi, "
        SELECT 
            nama_menu,
            harga,
            id_kantin
        FROM menu
        WHERE id_menu = '$id_menu'
    ");

    if (!$q_menu) {
        die(mysqli_error($koneksi));
    }

    $res_menu = mysqli_fetch_assoc($q_menu);

    if (!$res_menu) {
        die("Data menu tidak ditemukan.");
    }

    $total_bayar = (float)$res_menu['harga'] * $qty;
    $id_kantin = (int)$res_menu['id_kantin'];
    $kode_pesanan = "PSN" . date('YmdHis') . mt_rand(10, 99);

    $query_pesanan = mysqli_query($koneksi, "
    INSERT INTO pesanan
    (
        kode_pesanan,
        id_user,
        id_kantin,
        tanggal,
        total_harga,
        metode_pembayaran,
        status,
        catatan
    )
    VALUES
    (
        '$kode_pesanan',
        '$id_user',
        '$id_kantin',
        '$tanggal',
        '$total_bayar',
        '$metode_bayar',
        'Pending',
        '$catatan'
    )
    ");

    if (!$query_pesanan) {
        die("Gagal membuat pesanan: " . mysqli_error($koneksi));
    }

    $id_pesanan_baru = mysqli_insert_id($koneksi);
    $harga = (float)$res_menu['harga'];
    $subtotal = $harga * $qty;
    $nama_menu = mysqli_real_escape_string($koneksi, $res_menu['nama_menu']);
    $catatan_detail = mysqli_real_escape_string($koneksi, trim(($opsi ? $opsi . ' - ' : '') . $catatan));

    mysqli_query($koneksi, "
        INSERT INTO detail_pesanan
        (
            id_pesanan,
            id_menu,
            qty,
            harga,
            subtotal,
            nama_menu,
            catatan,
            opsi_pilihan
        )
        VALUES
        (
            '$id_pesanan_baru',
            '$id_menu',
            '$qty',
            '$harga',
            '$subtotal',
            '$nama_menu',
            '$catatan_detail',
            '$opsi'
        )
    ");

    header("Location: pembayaran_berhasil.php?id_pesanan=$id_pesanan_baru");
    exit();
}
?>
