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

if (isset($_POST['ajax_favorit'])) {
    header('Content-Type: application/json');
    $id_menu = (int)$_POST['id_menu'];
    $cek = mysqli_query($koneksi, "SELECT id_favorit FROM favorit WHERE id_user=$id_user AND id_menu=$id_menu");
    if ($cek && mysqli_num_rows($cek) > 0) {
        mysqli_query($koneksi, "DELETE FROM favorit WHERE id_user=$id_user AND id_menu=$id_menu");
        echo json_encode(['status' => 'removed']);
    } else {
        mysqli_query($koneksi, "INSERT INTO favorit (id_user, id_menu) VALUES ($id_user, $id_menu)");
        echo json_encode(['status' => 'added']);
    }
    exit();
}

$favorit_ids = [];
$q_fav = mysqli_query($koneksi, "SELECT id_menu FROM favorit WHERE id_user=$id_user");
while ($f = mysqli_fetch_assoc($q_fav)) $favorit_ids[] = (int)$f['id_menu'];

$tab = $_GET['tab'] ?? 'home';
$kategoriMenu = $_GET['category'] ?? '';
$kategoriKantin = $_GET['kantin_cat'] ?? 'Semua';
$allowedKategori = ['Semua', 'Makanan', 'Minuman', 'Camilan'];
if (!in_array($kategoriKantin, $allowedKategori, true)) $kategoriKantin = 'Semua';

$selectMenu = "
    SELECT m.*, k.nama_kantin,
           COALESCE(AVG(rm.nilai_rating),0) AS avg_rating,
           COUNT(rm.id_rating) AS jml_rating,
           COUNT(u.id_ulasan) AS jml_ulasan
    FROM menu m
    JOIN kantin k ON m.id_kantin = k.id_kantin
    LEFT JOIN rating_menu rm ON m.id_menu = rm.id_menu
    LEFT JOIN ulasan u ON m.id_menu = u.id_menu
";

$whereAvailable = " WHERE COALESCE(m.status,'Tersedia') <> 'Habis' ";
$katSql = mysqli_real_escape_string($koneksi, $kategoriMenu);

if ($tab === 'favorit' && !empty($favorit_ids)) {
    $favIn = implode(',', $favorit_ids);
    $query_menu = mysqli_query($koneksi, "$selectMenu $whereAvailable AND m.id_menu IN ($favIn) GROUP BY m.id_menu ORDER BY m.id_menu DESC");
} elseif ($tab === 'favorit') {
    $query_menu = null;
} elseif ($kategoriMenu !== '') {
    $query_menu = mysqli_query($koneksi, "$selectMenu $whereAvailable AND m.kategori='$katSql' GROUP BY m.id_menu ORDER BY m.id_menu DESC LIMIT 20");
} else {
    $query_menu = mysqli_query($koneksi, "$selectMenu $whereAvailable GROUP BY m.id_menu ORDER BY m.id_menu DESC LIMIT 24");
}

$query_terbaru = mysqli_query($koneksi, "$selectMenu $whereAvailable GROUP BY m.id_menu ORDER BY m.id_menu DESC LIMIT 10");
$query_terlaris = mysqli_query($koneksi, "$selectMenu $whereAvailable GROUP BY m.id_menu ORDER BY avg_rating DESC, jml_rating DESC, m.id_menu DESC LIMIT 10");

$kantinWhere = '';
if ($kategoriKantin !== 'Semua') {
    $katKantinSql = mysqli_real_escape_string($koneksi, $kategoriKantin);
    $kantinWhere = "WHERE EXISTS (SELECT 1 FROM menu m2 WHERE m2.id_kantin=k.id_kantin AND m2.kategori='$katKantinSql' AND COALESCE(m2.status,'Tersedia') <> 'Habis')";
}
$query_kantin = mysqli_query($koneksi, "
    SELECT k.*,
           COUNT(m.id_menu) AS total_menu,
           COALESCE(AVG(rm.nilai_rating),0) AS avg_rating,
           COUNT(rm.id_rating) AS total_rating
    FROM kantin k
    LEFT JOIN menu m ON k.id_kantin=m.id_kantin AND COALESCE(m.status,'Tersedia') <> 'Habis'
    LEFT JOIN rating_menu rm ON m.id_menu=rm.id_menu
    $kantinWhere
    GROUP BY k.id_kantin
    ORDER BY k.id_kantin DESC
");

$q_cart = mysqli_query($koneksi, "SELECT COALESCE(SUM(qty),0) AS total FROM keranjang WHERE id_user=$id_user");
$jml_keranjang = (int)(mysqli_fetch_assoc($q_cart)['total'] ?? 0);

$q_user = mysqli_query($koneksi, "SELECT username, foto_profil FROM users WHERE id_user=$id_user");
$d_user = mysqli_fetch_assoc($q_user);
$nama = explode(' ', $d_user['username'] ?? 'Pembeli')[0];
$fotoProfil = kk_upload_url($d_user['foto_profil'] ?? '', 'profile');

function renderMenuCard($m, $is_fav) {
    $id = (int)$m['id_menu'];
    $avg = round((float)$m['avg_rating'], 1);
    $jml = (int)max($m['jml_rating'] ?? 0, $m['jml_ulasan'] ?? 0);
    $ratingTxt = $avg > 0 ? $avg : 'Baru';
    $ulasanTxt = $jml > 0 ? $jml . ' ulasan' : 'Belum ada ulasan';
    $foto = kk_upload_url($m['foto'] ?? '', 'menu');
    $loved = $is_fav ? 'loved text-red-500' : 'text-stone-300';
    $fill = $is_fav ? '1' : '0';

    return "
    <article class='menu-card menu-search-card bg-white rounded-[1.35rem] overflow-hidden border border-orange-100 shadow-sm relative' data-card>
        <button onclick='toggleFavorit(this,$id)' class='love-btn absolute top-2 right-2 z-10 w-8 h-8 bg-white/95 rounded-full flex items-center justify-center shadow-sm $loved'>
            <span class='material-symbols-outlined text-sm' style='font-variation-settings:\"FILL\" $fill'>favorite</span>
        </button>
        <a href='detail_menu.php?id=$id' class='block'>
            <div class='relative'>
                <img class='w-full aspect-square object-cover bg-orange-50' src='$foto' alt='".htmlspecialchars($m['nama_menu'] ?? '')."'>
                <div class='absolute left-2 bottom-2 bg-white/95 backdrop-blur px-2 py-1 rounded-full flex items-center gap-1'>
                    <span class='material-symbols-outlined text-yellow-400 text-[13px]'>star</span>
                    <span class='text-[10px] font-black text-stone-700'>$ratingTxt</span>
                </div>
            </div>
            <div class='p-3'>
                <p class='text-[10px] text-stone-400 font-bold truncate'>".htmlspecialchars($m['nama_kantin'] ?? 'Kantin')."</p>
                <h4 class='headline font-black text-sm truncate mt-1'>".htmlspecialchars($m['nama_menu'] ?? 'Menu')."</h4>
                <p class='text-[10px] text-stone-400 mt-1'>$ulasanTxt</p>
                <div class='flex items-center justify-between mt-2 gap-2'>
                    <span class='text-[#b22204] font-black text-sm'>".kk_format_rupiah($m['harga'] ?? 0)."</span>
                    <span class='w-7 h-7 rounded-full bg-[#b22204] text-white flex items-center justify-center'>
                        <span class='material-symbols-outlined text-sm'>add</span>
                    </span>
                </div>
            </div>
        </a>
    </article>";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Kantin Kita</title>
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@600;700;800;900&family=Be+Vietnam+Pro:wght@400;500;600;700&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1" rel="stylesheet"/>
<style>
body{font-family:'Be Vietnam Pro',sans-serif;background:radial-gradient(circle at top left,rgba(255,107,53,.14),transparent 30rem),radial-gradient(circle at top right,rgba(56,189,248,.12),transparent 28rem),#fffdfc;padding-bottom:120px;}
.headline{font-family:'Plus Jakarta Sans',sans-serif}.hide-scrollbar::-webkit-scrollbar{display:none}
.material-symbols-outlined{font-variation-settings:'FILL' 1,'wght' 500,'GRAD' 0,'opsz' 24}
.menu-card{transition:transform .18s,box-shadow .18s,border-color .18s}.menu-card:hover{transform:translateY(-3px);box-shadow:0 16px 34px rgba(178,34,4,.12);border-color:#f7c7bd}
.love-btn{transition:transform .18s,color .18s}.love-btn:active{transform:scale(1.25)}.love-btn.loved{color:#ef4444!important}
.chip{display:inline-flex;align-items:center;gap:.35rem;padding:.65rem 1rem;border-radius:16px;font-size:12px;font-weight:900;white-space:nowrap;border:1px solid #f5ded8;text-decoration:none;transition:.18s;background:#fff;color:#74645d}
.chip:hover{background:#fff1ee;color:#b22204}.chip-active{background:linear-gradient(135deg,#b22204,#ff6b35);color:white;border-color:transparent;box-shadow:0 10px 22px rgba(178,34,4,.22)}
</style>
</head>
<body class="text-stone-800">

<header class="bg-white/90 backdrop-blur-xl sticky top-0 z-40 px-4 md:px-7 py-3 border-b border-orange-100 w-full">
    <div class="w-full flex items-center justify-between gap-4">
        <div class="min-w-0">
            <p class="text-[11px] text-stone-400 font-bold">Selamat datang, <?= htmlspecialchars($nama) ?></p>
            <h1 class="headline text-lg md:text-2xl font-black text-[#b22204] italic uppercase leading-tight">Kantin Kita</h1>
        </div>
        <div class="flex items-center gap-2">
            <a href="keranjang.php" class="relative text-[#b22204] w-11 h-11 flex items-center justify-center rounded-2xl bg-orange-50 hover:bg-orange-100">
                <span class="material-symbols-outlined">shopping_bag</span>
                <?php if ($jml_keranjang > 0): ?>
                <span class="absolute -top-1 -right-1 bg-[#b22204] text-white text-[10px] min-w-5 h-5 px-1 flex items-center justify-center rounded-full font-black"><?= $jml_keranjang ?></span>
                <?php endif; ?>
            </a>
            <a href="profil.php" class="w-11 h-11 rounded-2xl bg-orange-100 overflow-hidden flex items-center justify-center text-[#b22204] headline font-black">
                <img src="<?= $fotoProfil ?>" class="w-full h-full object-cover" onerror="this.style.display='none'">
            </a>
        </div>
    </div>
</header>

<main class="w-full px-4 md:px-7 py-5 md:py-7 space-y-8">
    <section class="grid lg:grid-cols-[1.15fr_.85fr] gap-4 items-stretch">
        <div class="rounded-[2rem] bg-gradient-to-br from-[#b22204] to-[#ff6b35] text-white p-5 md:p-7 overflow-hidden relative min-h-[190px]">
            <div class="absolute -right-10 -top-10 w-44 h-44 rounded-full bg-white/10"></div>
            <div class="absolute right-10 bottom-4 text-7xl opacity-95">🍱</div>
            <div class="relative z-10 max-w-xl">
                <p class="text-xs font-bold text-white/75">Makan cepat, bayar gampang</p>
                <h2 class="headline text-3xl md:text-5xl font-black leading-tight mt-2">Menu kantin paling fresh hari ini</h2>
                <p class="text-sm text-white/75 mt-3">Pilih menu, cek rating, lihat kantin, lalu pantau pesanan dari satu dashboard.</p>
            </div>
        </div>
        <div class="bg-white rounded-[2rem] border border-orange-100 p-4 md:p-5 shadow-sm">
            <div class="relative">
                <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-stone-400">search</span>
                <input class="w-full pl-12 pr-4 py-4 bg-stone-100 rounded-2xl text-sm border-none outline-none focus:ring-2 focus:ring-[#b22204]/25"
                       placeholder="Cari menu atau kantin..." type="text" oninput="filterSearch(this.value)"/>
            </div>
            <div class="mt-4 flex gap-2 overflow-x-auto hide-scrollbar">
                <a href="dashboard.php" class="chip <?= ($tab === 'home' && $kategoriMenu === '') ? 'chip-active' : '' ?>">Beranda</a>
                <a href="?tab=favorit" class="chip <?= $tab === 'favorit' ? 'chip-active' : '' ?>">Favorit <?= count($favorit_ids) ? '(' . count($favorit_ids) . ')' : '' ?></a>
                <a href="?category=Makanan" class="chip <?= $kategoriMenu === 'Makanan' ? 'chip-active' : '' ?>">Makanan</a>
                <a href="?category=Minuman" class="chip <?= $kategoriMenu === 'Minuman' ? 'chip-active' : '' ?>">Minuman</a>
                <a href="?category=Camilan" class="chip <?= $kategoriMenu === 'Camilan' ? 'chip-active' : '' ?>">Camilan</a>
            </div>
        </div>
    </section>

    <?php if ($tab === 'favorit'): ?>
    <section>
        <div class="flex items-center gap-2 mb-4"><span class="material-symbols-outlined text-[#b22204]">favorite</span><h2 class="headline font-black text-xl">Menu Favoritmu</h2></div>
        <?php if (!$query_menu || mysqli_num_rows($query_menu) === 0): ?>
        <div class="text-center py-16 bg-white rounded-3xl border-2 border-dashed border-orange-100"><p class="font-black text-stone-500">Belum ada menu favorit.</p></div>
        <?php else: ?>
        <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-5 2xl:grid-cols-6 gap-3 md:gap-4">
            <?php while ($m = mysqli_fetch_assoc($query_menu)) echo renderMenuCard($m, in_array((int)$m['id_menu'], $favorit_ids, true)); ?>
        </div>
        <?php endif; ?>
    </section>
    <?php else: ?>

    <?php if ($kategoriMenu === ''): ?>
    <section>
        <div class="flex items-center justify-between mb-4">
            <div><p class="text-[10px] font-black uppercase tracking-[0.2em] text-stone-400">Update terbaru</p><h2 class="headline font-black text-xl">Menu Terbaru</h2></div>
            <span class="text-xs font-black text-[#b22204] bg-orange-50 px-3 py-1.5 rounded-full">Maks 10 menu</span>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-5 2xl:grid-cols-10 gap-3 md:gap-4">
            <?php while ($m = mysqli_fetch_assoc($query_terbaru)) echo renderMenuCard($m, in_array((int)$m['id_menu'], $favorit_ids, true)); ?>
        </div>
    </section>

    <section>
        <div class="flex items-center justify-between mb-4">
            <div><p class="text-[10px] font-black uppercase tracking-[0.2em] text-stone-400">Bintang terbaik</p><h2 class="headline font-black text-xl">Menu Terlaris</h2></div>
            <span class="text-xs font-black text-yellow-700 bg-yellow-50 px-3 py-1.5 rounded-full">Rating tertinggi</span>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-5 2xl:grid-cols-10 gap-3 md:gap-4">
            <?php while ($m = mysqli_fetch_assoc($query_terlaris)) echo renderMenuCard($m, in_array((int)$m['id_menu'], $favorit_ids, true)); ?>
        </div>
    </section>
    <?php endif; ?>

    <section>
        <div class="flex items-center justify-between mb-4">
            <div><p class="text-[10px] font-black uppercase tracking-[0.2em] text-stone-400">Jelajahi semua</p><h2 class="headline font-black text-xl"><?= $kategoriMenu ? htmlspecialchars($kategoriMenu) : 'Semua Menu' ?></h2></div>
            <a href="semua_menu.php" class="text-xs font-black text-[#b22204]">Lihat Semua</a>
        </div>
        <?php if ($query_menu && mysqli_num_rows($query_menu) > 0): ?>
        <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-5 2xl:grid-cols-6 gap-3 md:gap-4">
            <?php while ($m = mysqli_fetch_assoc($query_menu)) echo renderMenuCard($m, in_array((int)$m['id_menu'], $favorit_ids, true)); ?>
        </div>
        <?php else: ?>
        <div class="text-center py-12 bg-white rounded-3xl border border-dashed border-orange-100 text-stone-400 font-bold">Belum ada menu di bagian ini.</div>
        <?php endif; ?>
    </section>
    <?php endif; ?>
</main>

<?php $current_page = 'home'; include(__DIR__ . '/../../includes/navbar.php'); ?>

<script>
async function toggleFavorit(btn, idMenu){
    const icon = btn.querySelector('.material-symbols-outlined');
    const fd = new FormData();
    fd.append('ajax_favorit','1');
    fd.append('id_menu',idMenu);
    const res = await fetch('dashboard.php',{method:'POST',body:fd});
    const data = await res.json();
    if(data.status === 'added'){
        btn.classList.add('loved','text-red-500');
        icon.style.fontVariationSettings = "'FILL' 1";
        showToast('Ditambahkan ke favorit');
    }else{
        btn.classList.remove('loved','text-red-500');
        icon.style.fontVariationSettings = "'FILL' 0";
        showToast('Dihapus dari favorit');
        if(location.search.includes('tab=favorit')) btn.closest('[data-card]')?.remove();
    }
}
function showToast(msg){
    let t=document.getElementById('toast');
    if(!t){t=document.createElement('div');t.id='toast';t.className='fixed bottom-24 left-1/2 -translate-x-1/2 bg-stone-900 text-white px-5 py-3 rounded-full text-xs font-bold z-[9999] transition-opacity';document.body.appendChild(t);}
    t.textContent=msg;t.style.opacity='1';clearTimeout(window._toastTimer);window._toastTimer=setTimeout(()=>t.style.opacity='0',1800);
}
function filterSearch(val){
    val=(val||'').toLowerCase();
    document.querySelectorAll('.menu-search-card').forEach(card=>{
        card.style.display=card.textContent.toLowerCase().includes(val)?'':'none';
    });
}
</script>
</body>
</html>
