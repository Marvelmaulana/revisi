<?php
session_start();
include(__DIR__ . '/../../config/config.php');
include(__DIR__ . '/../../includes/pembeli_helpers.php');
kk_ensure_buyer_schema($koneksi);

if (!isset($_SESSION['id_user']) || ($_SESSION['role'] ?? '') !== 'pembeli') {
    header("Location: ../auth/login.php");
    exit();
}

$id_user = (int)$_SESSION['id_user'];
$id_menu = (int)($_GET['id'] ?? 0);
if ($id_menu <= 0) {
    header("Location: dashboard.php");
    exit();
}

$query = mysqli_query($koneksi, "
    SELECT m.*, k.nama_kantin,
           COALESCE(AVG(rm.nilai_rating),0) AS avg_rating,
           COUNT(rm.id_rating) AS jml_rating
    FROM menu m
    JOIN kantin k ON m.id_kantin = k.id_kantin
    LEFT JOIN rating_menu rm ON m.id_menu = rm.id_menu
    WHERE m.id_menu = $id_menu
    GROUP BY m.id_menu
");
$data = mysqli_fetch_assoc($query);

if (!$data) {
    echo "<script>alert('Menu tidak ditemukan!'); window.location='dashboard.php';</script>";
    exit();
}

$opsiRaw = trim($data['opsi_pilihan'] ?? '');
$opsiList = $opsiRaw !== '' ? array_values(array_filter(array_map('trim', preg_split('/[\r\n,;|]+/', $opsiRaw)))) : ['Original', 'Pedas Level 1', 'Pedas Level 2', 'Pedas Level 3'];
$avg = round((float)$data['avg_rating'], 1);
$jml = (int)$data['jml_rating'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title><?= htmlspecialchars($data['nama_menu']); ?> - Detail Menu</title>
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@700;800;900&family=Be+Vietnam+Pro:wght@400;500;600;700&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1" rel="stylesheet"/>
<style>
body{font-family:'Be Vietnam Pro',sans-serif;background:radial-gradient(circle at top left,rgba(255,107,53,.13),transparent 28rem),radial-gradient(circle at top right,rgba(56,189,248,.12),transparent 25rem),#fffdfc;}
.headline{font-family:'Plus Jakarta Sans',sans-serif;}
.material-symbols-outlined{font-variation-settings:'FILL' 1,'wght' 500,'GRAD' 0,'opsz' 24;}
.option-chip input:checked + span{background:linear-gradient(135deg,#b22204,#ff6b35);color:white;border-color:#b22204;box-shadow:0 10px 24px rgba(178,34,4,.18);}
</style>
</head>
<body class="text-stone-900 pb-36">

<header class="bg-white/90 backdrop-blur-xl sticky top-0 z-40 px-5 py-4 border-b border-stone-100">
    <div class="max-w-[1200px] mx-auto flex items-center justify-between">
        <button class="w-11 h-11 rounded-2xl bg-stone-100 text-stone-600 flex items-center justify-center" onclick="history.back()">
            <span class="material-symbols-outlined">arrow_back</span>
        </button>
        <a href="keranjang.php" class="w-11 h-11 rounded-2xl bg-orange-50 text-[#b22204] flex items-center justify-center">
            <span class="material-symbols-outlined">shopping_bag</span>
        </a>
    </div>
</header>

<main class="max-w-[1200px] mx-auto px-4 md:px-6 py-5 lg:grid lg:grid-cols-[minmax(320px,520px)_1fr] lg:gap-8">
    <section>
        <div class="relative overflow-hidden rounded-[2rem] bg-stone-100 shadow-xl shadow-red-900/10">
            <img class="w-full aspect-square lg:aspect-[4/5] object-cover" src="<?= kk_upload_url($data['foto'] ?? '', 'menu'); ?>" onerror="this.src='../../public/assets/img/default-food.svg'"/>
            <div class="absolute left-4 bottom-4 bg-white/95 backdrop-blur px-3 py-2 rounded-2xl flex items-center gap-2">
                <span class="material-symbols-outlined text-yellow-400 text-lg">star</span>
                <span class="text-sm font-black"><?= $avg > 0 ? $avg : 'Baru' ?></span>
                <span class="text-xs text-stone-400"><?= $jml ?> ulasan</span>
            </div>
        </div>
    </section>

    <section class="pt-6 lg:pt-2">
        <span class="inline-flex items-center gap-1 bg-orange-50 text-[#b22204] px-3 py-1.5 rounded-full text-[11px] font-black uppercase tracking-wider">
            <span class="material-symbols-outlined text-sm">storefront</span>
            <?= htmlspecialchars($data['nama_kantin']); ?>
        </span>
        <h1 class="headline text-3xl md:text-5xl font-black leading-tight mt-4"><?= htmlspecialchars($data['nama_menu']); ?></h1>
        <p class="headline text-3xl font-black text-[#b22204] mt-3"><?= kk_format_rupiah($data['harga']); ?></p>

        <div class="mt-7 bg-white/95 rounded-3xl border border-stone-100 p-5 shadow-sm">
            <p class="text-[10px] font-black uppercase tracking-[0.2em] text-stone-400 mb-2">Deskripsi</p>
            <p class="text-sm leading-relaxed text-stone-600">
                <?= nl2br(htmlspecialchars($data['deskripsi'] ?: "Sajian favorit dari {$data['nama_kantin']}, dibuat fresh setelah kamu pesan.")); ?>
            </p>
        </div>

        <div class="mt-4 bg-white/95 rounded-3xl border border-stone-100 p-5 shadow-sm space-y-5">
            <div>
                <p class="text-[10px] font-black uppercase tracking-[0.2em] text-stone-400 mb-3">Level / Pilihan</p>
                <div class="flex flex-wrap gap-2">
                    <?php foreach ($opsiList as $i => $opsi): ?>
                    <label class="option-chip cursor-pointer">
                        <input type="radio" name="opsi_pilihan" value="<?= htmlspecialchars($opsi) ?>" class="hidden" <?= $i === 0 ? 'checked' : '' ?>>
                        <span class="inline-flex border border-orange-100 bg-orange-50/60 text-stone-700 rounded-2xl px-4 py-2 text-xs font-black transition-all"><?= htmlspecialchars($opsi) ?></span>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <div>
                <p class="text-[10px] font-black uppercase tracking-[0.2em] text-stone-400 mb-3">Catatan</p>
                <textarea id="catatan" class="w-full min-h-24 rounded-2xl bg-stone-100 border-none outline-none p-4 text-sm focus:ring-2 focus:ring-[#b22204]/25" placeholder="Contoh: saus dipisah, nasi setengah, tanpa bawang..."></textarea>
            </div>

            <div class="flex items-center justify-between bg-stone-100 rounded-3xl p-3">
                <span class="text-sm font-black text-stone-600 ml-2">Jumlah</span>
                <div class="flex items-center bg-white rounded-full p-1 shadow-sm">
                    <button onclick="changeQty(-1)" class="w-10 h-10 rounded-full bg-stone-100 text-stone-700 font-black active:scale-90">-</button>
                    <span id="display_qty" class="w-12 text-center headline font-black">1</span>
                    <button onclick="changeQty(1)" class="w-10 h-10 rounded-full bg-[#b22204] text-white font-black active:scale-90">+</button>
                </div>
            </div>
        </div>
    </section>
</main>

<div class="fixed bottom-0 left-0 right-0 z-50 bg-white/95 backdrop-blur-xl border-t border-stone-100 px-4 pt-3 pb-6 lg:left-72">
    <div class="max-w-[1200px] mx-auto grid grid-cols-2 gap-3">
        <button type="button" class="h-14 rounded-2xl border-2 border-[#b22204] text-[#b22204] font-black flex items-center justify-center gap-2 active:scale-95" onclick="prosesKeKeranjang()">
            <span class="material-symbols-outlined">shopping_cart</span>
            Tambah
        </button>
        <button type="button" onclick="pesanSekarang()" class="h-14 rounded-2xl bg-gradient-to-r from-[#b22204] to-[#ff6b35] text-white font-black shadow-xl shadow-red-900/15 active:scale-95">
            Pesan Sekarang
        </button>
    </div>
</div>

<?php $current_page = 'home'; include(__DIR__ . '/../../includes/navbar.php'); ?>

<script>
let currentQty = 1;
function getOpsi(){ return document.querySelector('input[name="opsi_pilihan"]:checked')?.value || ''; }
function getCatatan(){ return document.getElementById('catatan')?.value || ''; }
function changeQty(n){ currentQty = Math.max(1, currentQty + n); document.getElementById('display_qty').innerText = currentQty; }
function pesanSekarang(){
    const p = new URLSearchParams({id_menu:'<?= $id_menu ?>', qty:currentQty, opsi:getOpsi(), catatan:getCatatan()});
    location.href = 'checkout.php?' + p.toString();
}
function prosesKeKeranjang(){
    const p = new URLSearchParams({id:'<?= $id_menu ?>', qty:currentQty, opsi:getOpsi(), catatan:getCatatan()});
    location.href = 'tambah_keranjang.php?' + p.toString();
}
</script>
</body>
</html>
