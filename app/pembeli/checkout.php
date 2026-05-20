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

$id_menu_langsung = isset($_GET['id_menu'])
    ? mysqli_real_escape_string($koneksi, $_GET['id_menu'])
    : null;

$qty_langsung = isset($_GET['qty'])
    ? (int)$_GET['qty']
    : 1;
$opsi_langsung = isset($_GET['opsi']) ? mysqli_real_escape_string($koneksi, $_GET['opsi']) : '';
$catatan_langsung = isset($_GET['catatan']) ? mysqli_real_escape_string($koneksi, $_GET['catatan']) : '';

$query = null;

//
// =====================================
// MODE BELI LANGSUNG
// =====================================
//
if ($id_menu_langsung) {

    $query = mysqli_query($koneksi, "
        SELECT 
            menu.*, 
            '$qty_langsung' AS qty,
            '$catatan_langsung' AS catatan,
            '$opsi_langsung' AS opsi_pilihan,
            kantin.nama_kantin
        FROM menu
        JOIN kantin 
        ON menu.id_kantin = kantin.id_kantin
        WHERE menu.id_menu = '$id_menu_langsung'
    ");

} else {

//
// =====================================
// MODE DARI KERANJANG
// =====================================
//

    $selected_raw = isset($_GET['selected'])
        ? $_GET['selected']
        : '';

    $selected_ids = array_filter(
        array_map('intval', explode(',', $selected_raw))
    );

    if (!empty($selected_ids)) {

        $ids_str = implode(',', $selected_ids);

        $query = mysqli_query($koneksi, "
            SELECT 
                keranjang.*,
                menu.nama_menu,
                menu.harga,
                menu.foto,
                menu.id_kantin,
                kantin.nama_kantin
            FROM keranjang
            JOIN menu 
            ON keranjang.id_menu = menu.id_menu
            JOIN kantin 
            ON menu.id_kantin = kantin.id_kantin
            WHERE keranjang.id_user = '$id_user'
            AND keranjang.id_keranjang IN ($ids_str)
        ");
    }
}

//
// =====================================
// VALIDASI QUERY
// =====================================
//

if (!$query) {
    die("Query gagal: " . mysqli_error($koneksi));
}

$total_items = mysqli_num_rows($query);

if ($total_items == 0) {
    header("Location: dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="utf-8"/>

    <meta 
        name="viewport" 
        content="width=device-width, initial-scale=1.0"
    />

    <title>Pembayaran - Kantin Kita</title>

    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>

    <link 
        href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&family=Be+Vietnam+Pro:wght@400;500;600;700&display=swap" 
        rel="stylesheet"
    />

    <link 
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" 
        rel="stylesheet"
    />

    <style>

        body {
            font-family: 'Be Vietnam Pro', sans-serif;
            background-color: #fff8f6;
        }

        .headline-font {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

    </style>

</head>

<body class="pb-40">

<!-- HEADER -->
<header class="bg-white/80 backdrop-blur-md flex items-center justify-between px-4 h-16 sticky top-0 z-50 border-b border-zinc-100">

    <div class="flex items-center gap-3">

        <button 
            onclick="history.back()" 
            class="w-10 h-10 flex items-center justify-center rounded-full hover:bg-zinc-100"
        >

            <span class="material-symbols-outlined text-zinc-600">
                arrow_back
            </span>

        </button>

        <div>

            <h1 class="headline-font font-bold text-lg">
                Pembayaran
            </h1>

            <span class="text-[10px] font-bold text-zinc-400 uppercase tracking-widest">
                <?= $total_items ?> Items
            </span>

        </div>

    </div>

</header>

<!-- CONTENT -->
<main class="max-w-md mx-auto px-4 py-6 space-y-6">

    <!-- ORDER -->
    <div class="p-4 bg-white rounded-2xl shadow-sm border border-zinc-100">

        <p class="text-[10px] font-bold text-[#b22204] uppercase">
            Order Reference
        </p>

        <h2 class="headline-font font-extrabold text-lg">
            #INV-<?= date('Ymd') ?>-<?= $_SESSION['id_user'] ?>
        </h2>

    </div>

    <!-- DETAIL PESANAN -->
    <section class="space-y-3">

        <h3 class="headline-font font-bold text-sm px-1">
            Detail Pesanan
        </h3>

        <?php
        $total_bayar = 0;

        while ($row = mysqli_fetch_assoc($query)):

            $sub = $row['harga'] * $row['qty'];
            $total_bayar += $sub;
        ?>

        <div class="flex gap-4 p-3 bg-white rounded-2xl border border-zinc-100">

            <img
                src="<?= kk_upload_url($row['foto'] ?? '', 'menu') ?>"
                class="w-16 h-16 rounded-xl object-cover bg-zinc-100"
                onerror="this.src='../../public/assets/img/default-food.svg'"
            >

            <div class="flex-1">

                <div class="flex justify-between items-start">

                    <h4 class="font-bold text-sm">
                        <?= $row['nama_menu'] ?>
                    </h4>

                    <span class="text-xs font-bold text-zinc-400">
                        x<?= $row['qty'] ?>
                    </span>

                </div>

                <p class="text-[11px] text-[#b22204] font-bold mt-1">
                    Rp <?= number_format($sub, 0, ',', '.') ?>
                </p>
                <?php if (!empty($row['opsi_pilihan']) || !empty($row['catatan'])): ?>
                <p class="text-[11px] text-zinc-400 mt-1">
                    <?= !empty($row['opsi_pilihan']) ? htmlspecialchars($row['opsi_pilihan']) : '' ?>
                    <?= (!empty($row['opsi_pilihan']) && !empty($row['catatan'])) ? ' - ' : '' ?>
                    <?= !empty($row['catatan']) ? htmlspecialchars($row['catatan']) : '' ?>
                </p>
                <?php endif; ?>

            </div>

        </div>

        <?php endwhile; ?>

    </section>

    <!-- CATATAN -->
    <section class="space-y-3">

        <h3 class="headline-font font-bold text-sm px-1">
            Catatan Untuk Penjual
        </h3>

        <div class="bg-white rounded-2xl border border-zinc-100 p-4">

            <textarea
                id="catatan"
                placeholder="Catatan tambahan untuk semua item..."
                class="w-full h-24 resize-none border-none focus:ring-0 text-sm placeholder:text-zinc-400"
            ><?= htmlspecialchars($catatan_langsung) ?></textarea>

        </div>

    </section>

    <!-- E-WALLET -->
    <section class="space-y-4">

        <h3 class="headline-font font-bold text-sm px-1">
            Pilih E-Wallet
        </h3>

        <div class="grid grid-cols-3 gap-3">

            <!-- DANA -->
            <label class="cursor-pointer">

                <input
                    type="radio"
                    name="payment_method"
                    value="DANA"
                    class="hidden peer"
                >

                <div class="flex flex-col items-center justify-center p-4 bg-white rounded-2xl border-2 border-transparent peer-checked:border-[#b22204] shadow-sm transition-all active:scale-95">

                    <span class="material-symbols-outlined text-blue-500 text-3xl mb-2">
                        account_balance_wallet
                    </span>

                    <span class="text-xs font-bold">
                        DANA
                    </span>

                </div>

            </label>

            <!-- OVO -->
            <label class="cursor-pointer">

                <input
                    type="radio"
                    name="payment_method"
                    value="OVO"
                    class="hidden peer"
                >

                <div class="flex flex-col items-center justify-center p-4 bg-white rounded-2xl border-2 border-transparent peer-checked:border-[#b22204] shadow-sm transition-all active:scale-95">

                    <span class="material-symbols-outlined text-purple-500 text-3xl mb-2">
                        wallet
                    </span>

                    <span class="text-xs font-bold">
                        OVO
                    </span>

                </div>

            </label>

            <!-- GOPAY -->
            <label class="cursor-pointer">

                <input
                    type="radio"
                    name="payment_method"
                    value="GOPAY"
                    class="hidden peer"
                >

                <div class="flex flex-col items-center justify-center p-4 bg-white rounded-2xl border-2 border-transparent peer-checked:border-[#b22204] shadow-sm transition-all active:scale-95">

                    <span class="material-symbols-outlined text-green-500 text-3xl mb-2">
                        payments
                    </span>

                    <span class="text-xs font-bold">
                        GOPAY
                    </span>

                </div>

            </label>

        </div>

    </section>

    <!-- TOTAL -->
    <section class="p-6 bg-zinc-100/50 rounded-[32px] space-y-3">

        <div class="flex justify-between items-center text-zinc-500 text-sm">

            <span>Subtotal</span>

            <span class="font-bold">
                Rp <?= number_format($total_bayar, 0, ',', '.') ?>
            </span>

        </div>

        <div class="pt-3 border-t border-zinc-200 flex justify-between items-center">

            <span class="headline-font font-extrabold text-lg">
                Total Bayar
            </span>

            <span class="headline-font font-black text-xl text-[#b22204]">
                Rp <?= number_format($total_bayar, 0, ',', '.') ?>
            </span>

        </div>

    </section>

</main>

<!-- BUTTON -->
<div class="fixed bottom-0 left-0 w-full p-6 bg-gradient-to-t from-[#fff8f6] via-[#fff8f6] to-transparent">

    <button
        onclick="prosesBayar()"
        class="w-full h-14 bg-gradient-to-r from-[#b22204] to-[#d63c1e] rounded-full flex items-center justify-center gap-3 text-white shadow-xl shadow-red-200 active:scale-95 transition-all"
    >

        <span class="material-symbols-outlined">
            lock
        </span>

        <span class="headline-font font-extrabold text-lg tracking-tight">
            BAYAR SEKARANG
        </span>

    </button>

</div>

<script>

function prosesBayar() {

    // cek metode pembayaran
    const selectedMethod = document.querySelector('input[name="payment_method"]:checked');

    if (!selectedMethod) {

        alert('Pilih metode pembayaran dulu!');
        return;
    }

    // ambil metode pembayaran
    const method = selectedMethod.value;

    // ambil catatan
    const catatan = document.getElementById('catatan').value;

    // ambil parameter URL
    const urlParams = new URLSearchParams(window.location.search);

    const idMenu = urlParams.get('id_menu');
    const qty = urlParams.get('qty');
    const opsi = urlParams.get('opsi') || '';
    const selected = urlParams.get('selected');

    // url dasar
    let targetUrl = `proses_checkout.php?method=${method}`;

    //
    // MODE BELI LANGSUNG
    //
    if (idMenu) {

        targetUrl += `&id_menu=${idMenu}&qty=${qty}&opsi=${encodeURIComponent(opsi)}`;

    } 
    
    //
    // MODE KERANJANG
    //
    else {

        targetUrl += `&source=cart&selected=${selected}`;
    }

    // kirim catatan
    targetUrl += `&catatan=${encodeURIComponent(catatan)}`;

    // redirect
    window.location.href = targetUrl;
}

</script>

</body>
</html>
