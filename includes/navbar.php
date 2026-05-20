<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($koneksi)) {
    @include_once(__DIR__ . '/../config/config.php');
}
if (!function_exists('kk_upload_url')) {
    @include_once(__DIR__ . '/pembeli_helpers.php');
}

$current_page = $current_page ?? '';
$buyerNav = [
    ['home', 'dashboard.php', 'home', 'Beranda'],
    ['orders', 'pesanan.php', 'receipt_long', 'Pesanan'],
    ['history', 'riwayat_pembeli.php', 'history', 'Riwayat'],
    ['cart', 'keranjang.php', 'shopping_bag', 'Keranjang'],
    ['profile', 'profil.php', 'person', 'Profil'],
];

$navBase = basename($_SERVER['PHP_SELF'] ?? '');
$sidebarKantin = [];
if (isset($koneksi) && $koneksi) {
    $q_sidebar_kantin = @mysqli_query($koneksi, "
        SELECT k.id_kantin, k.nama_kantin, k.logo,
               COUNT(m.id_menu) AS total_menu
        FROM kantin k
        LEFT JOIN menu m ON k.id_kantin = m.id_kantin AND COALESCE(m.status,'Tersedia') <> 'Habis'
        GROUP BY k.id_kantin
        ORDER BY k.nama_kantin ASC
    ");
    if ($q_sidebar_kantin) {
        while ($kantin = mysqli_fetch_assoc($q_sidebar_kantin)) {
            $sidebarKantin[] = $kantin;
        }
    }
}
?>
<style>
@media (min-width:1024px){
    body{padding-left:18rem!important;padding-bottom:2rem!important;}
    body>header.fixed{left:18rem!important;right:0!important;width:auto!important;max-width:none!important;}
}
.kk-buyer-sidebar{font-family:'Be Vietnam Pro',system-ui,sans-serif;}
.kk-menu-toggle{display:none;}
.kk-sidebar-overlay{display:none;}
.kk-nav-link{display:flex;align-items:center;gap:.8rem;border-radius:18px;padding:.85rem 1rem;font-size:.86rem;font-weight:800;color:#74645d;text-decoration:none;transition:.18s;background:transparent;}
.kk-nav-link:hover{background:#fff1ee;color:#b22204;transform:translateX(2px);}
.kk-nav-link.active{background:linear-gradient(135deg,#b22204,#ff6b35);color:white;box-shadow:0 12px 28px rgba(178,34,4,.18);}
.kk-nav-link .material-symbols-outlined{font-size:21px;font-variation-settings:'FILL' 1,'wght' 500,'GRAD' 0,'opsz' 24;}
.kk-kantin-link{display:flex;align-items:center;gap:.65rem;padding:.55rem;border-radius:16px;text-decoration:none;color:#3f352f;transition:.18s;}
.kk-kantin-link:hover{background:#fff1ee;color:#b22204;transform:translateX(2px);}
.kk-sidebar-scroll{scrollbar-width:thin;scrollbar-color:#fed7aa transparent;}
.kk-sidebar-scroll::-webkit-scrollbar{width:6px;}
.kk-sidebar-scroll::-webkit-scrollbar-thumb{background:#fed7aa;border-radius:999px;}
@media (max-width:1023px){
    body{padding-left:0!important;padding-bottom:2rem!important;}
    body>header{padding-left:4.75rem!important;min-height:4.25rem;}
    .kk-menu-toggle{display:flex!important;position:fixed;left:1rem;top:.72rem;z-index:90;width:2.8rem;height:2.8rem;border-radius:1rem;background:linear-gradient(135deg,#b22204,#ff6b35);color:#fff;align-items:center;justify-content:center;box-shadow:0 12px 28px rgba(178,34,4,.24);}
    .kk-buyer-sidebar{display:flex!important;transform:translateX(-105%);transition:transform .25s ease;width:min(18rem,86vw);}
    .kk-buyer-sidebar.open{transform:translateX(0);}
    .kk-sidebar-overlay{display:block;position:fixed;inset:0;background:rgba(28,22,19,.42);backdrop-filter:blur(4px);z-index:65;opacity:0;pointer-events:none;transition:opacity .2s;}
    .kk-sidebar-overlay.open{opacity:1;pointer-events:auto;}
    .kk-mobile-nav{display:none!important;}
    .kk-sidebar-scroll{max-height:none!important;}
}
@media (max-width:420px){
    body>header{padding-left:4.35rem!important;}
    .kk-menu-toggle{left:.75rem;width:2.65rem;height:2.65rem;border-radius:.95rem;}
    .kk-buyer-sidebar{width:88vw;}
}
</style>

<button type="button" class="kk-menu-toggle" onclick="kkToggleBuyerSidebar(true)" aria-label="Buka menu">
    <span class="material-symbols-outlined">menu</span>
</button>

<div id="kk-sidebar-overlay" class="kk-sidebar-overlay" onclick="kkToggleBuyerSidebar(false)"></div>

<aside id="kk-buyer-sidebar" class="kk-buyer-sidebar fixed left-0 top-0 bottom-0 z-[70] w-72 bg-white/95 backdrop-blur-xl border-r border-orange-100 px-4 py-5 hidden lg:flex flex-col">
    <div class="px-3 pb-5 border-b border-orange-100">
        <div class="flex items-center gap-3">
            <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-[#b22204] to-[#ff6b35] text-white flex items-center justify-center shadow-lg shadow-red-900/15">
                <span class="material-symbols-outlined">local_dining</span>
            </div>
            <div>
                <p class="text-[10px] font-black uppercase tracking-[0.2em] text-stone-400">Kantin Kita</p>
                <h2 class="text-lg font-black text-[#b22204] italic leading-none">Buyer Space</h2>
            </div>
            <button type="button" class="ml-auto lg:hidden w-9 h-9 rounded-2xl bg-orange-50 text-[#b22204] flex items-center justify-center" onclick="kkToggleBuyerSidebar(false)" aria-label="Tutup menu">
                <span class="material-symbols-outlined text-lg">close</span>
            </button>
        </div>
    </div>

    <nav class="py-5 space-y-2">
        <?php foreach ($buyerNav as [$key, $href, $icon, $label]):
            $active = ($current_page === $key) || ($navBase === $href);
        ?>
        <a href="<?= $href ?>" class="kk-nav-link <?= $active ? 'active' : '' ?>">
            <span class="material-symbols-outlined"><?= $icon ?></span>
            <span><?= $label ?></span>
        </a>
        <?php endforeach; ?>
    </nav>

    <div class="flex-1 min-h-0 border-t border-orange-100 pt-4">
        <div class="flex items-center justify-between px-2 mb-2">
            <p class="text-[10px] font-black uppercase tracking-[0.18em] text-stone-400">Daftar Kantin</p>
            <span class="text-[10px] font-black text-[#b22204] bg-orange-50 px-2 py-1 rounded-full"><?= count($sidebarKantin) ?></span>
        </div>
        <div class="kk-sidebar-scroll overflow-y-auto pr-1 space-y-1" style="max-height:calc(100vh - 29rem);">
            <?php if (!empty($sidebarKantin)): ?>
                <?php foreach ($sidebarKantin as $kantin):
                    $logo = function_exists('kk_upload_url') ? kk_upload_url($kantin['logo'] ?? '', 'logo') : '../../public/assets/img/default-logo.svg';
                ?>
                <a href="kantin_detail.php?id=<?= (int)$kantin['id_kantin'] ?>" class="kk-kantin-link">
                    <img src="<?= $logo ?>" class="w-10 h-10 rounded-2xl object-cover bg-orange-50 shrink-0" onerror="this.src='../../public/assets/img/default-logo.svg'">
                    <div class="min-w-0 flex-1">
                        <p class="text-xs font-black truncate"><?= htmlspecialchars($kantin['nama_kantin'] ?? 'Kantin') ?></p>
                        <p class="text-[10px] text-stone-400 font-bold"><?= (int)($kantin['total_menu'] ?? 0) ?> menu</p>
                    </div>
                </a>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="rounded-2xl bg-stone-50 p-3 text-[11px] text-stone-400 font-bold">Belum ada data kantin.</div>
            <?php endif; ?>
        </div>
    </div>

    <div class="rounded-3xl bg-orange-50 border border-orange-100 p-4">
        <p class="text-xs font-black text-stone-700">Butuh bantuan?</p>
        <p class="text-[11px] text-stone-500 mt-1 leading-relaxed">Cek FAQ, kontak kantin, atau kirim kendala pemesanan.</p>
        <a href="bantuan.php" class="mt-3 inline-flex items-center gap-1 text-xs font-black text-[#b22204]">
            Pusat Bantuan
            <span class="material-symbols-outlined text-sm">arrow_forward</span>
        </a>
    </div>
</aside>

<nav class="kk-mobile-nav fixed bottom-0 left-0 right-0 z-[70] bg-white/95 backdrop-blur-xl border-t border-orange-100 px-3 pt-2 pb-5 hidden grid-cols-5 gap-1">
    <?php foreach ($buyerNav as [$key, $href, $icon, $label]):
        $active = ($current_page === $key) || ($navBase === $href);
    ?>
    <a href="<?= $href ?>" class="flex flex-col items-center gap-1 rounded-2xl py-2 text-[10px] font-black <?= $active ? 'text-[#b22204] bg-orange-50' : 'text-stone-400' ?>">
        <span class="material-symbols-outlined text-[21px]" style="font-variation-settings:'FILL' <?= $active ? '1' : '0' ?>"><?= $icon ?></span>
        <?= $label ?>
    </a>
    <?php endforeach; ?>
</nav>

<script>
function kkToggleBuyerSidebar(open) {
    const sidebar = document.getElementById('kk-buyer-sidebar');
    const overlay = document.getElementById('kk-sidebar-overlay');
    if (!sidebar || !overlay) return;
    sidebar.classList.toggle('open', !!open);
    overlay.classList.toggle('open', !!open);
}
</script>
