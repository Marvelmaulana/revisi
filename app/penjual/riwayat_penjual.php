<?php
session_start();
include(__DIR__ . '/../../config/config.php');

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'penjual') {
    header("Location: ../auth/login.php");
    exit;
}

$id_k = (int)($_SESSION['id_kantin'] ?? 0);

$query = mysqli_query($koneksi, "
    SELECT
        p.*,
        u.username,
        u.email,
        k.nama_kantin,
        k.logo
    FROM pesanan p
    JOIN users u ON p.id_user = u.id_user
    JOIN kantin k ON p.id_kantin = k.id_kantin
    WHERE p.id_kantin = $id_k
      AND p.status IN ('Selesai', 'Dibatalkan')
    ORDER BY p.id_pesanan DESC
");

$rows = [];
$total_selesai = 0;
$total_batal = 0;
$total_pendapatan = 0;

while ($r = mysqli_fetch_assoc($query)) {
    $rows[] = $r;
    if ($r['status'] === 'Selesai') {
        $total_selesai++;
        $total_pendapatan += (float)($r['total_harga'] ?? 0);
    }
    if ($r['status'] === 'Dibatalkan') {
        $total_batal++;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Riwayat Penjualan - Kantin Kita</title>

<script src="https://cdn.tailwindcss.com?plugins=forms"></script>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800;900&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet"/>

<style>
:root {
    --brand:    #b22204;
    --brand-dk: #8b1a03;
    --brand-lt: #fff1ee;
    --brand-md: #fcd5cc;
    --ink:      #1a1714;
    --muted:    #7c746e;
    --line:     #ede9e5;
    --surface:  #faf8f6;
}
* { box-sizing:border-box; }
body { font-family:'DM Sans',sans-serif; background:var(--surface); color:var(--ink); }
h1,h2,h3,h4 { font-family:'Plus Jakarta Sans',sans-serif; }

.summary-grid {
    display:grid;
    grid-template-columns:repeat(3, minmax(0, 1fr));
    gap:.65rem;
}
.history-card {
    background:#fff;
    border:1.5px solid var(--line);
    border-radius:1.1rem;
    overflow:hidden;
    min-height:100%;
    transition:box-shadow .2s, border-color .2s;
}
.history-card:hover { box-shadow:0 6px 24px rgba(178,34,4,.08); border-color:var(--brand-md); }
.badge {
    display:inline-flex;
    align-items:center;
    gap:.3rem;
    padding:.25rem .75rem;
    border-radius:999px;
    font-size:.68rem;
    font-weight:900;
    white-space:nowrap;
}
.badge-selesai { background:#f0fdf4; color:#166534; border:1px solid #bbf7d0; }
.badge-batal { background:#fef2f2; color:#b91c1c; border:1px solid #fecaca; }
.meta-chip {
    display:inline-flex;
    align-items:center;
    gap:.35rem;
    min-height:1.65rem;
}
.toast {
    position:fixed; bottom:1.5rem; right:1.5rem; z-index:99;
    background:var(--ink); color:#fff;
    padding:.7rem 1.2rem; border-radius:.9rem;
    font-size:.8rem; font-weight:700;
    display:flex; align-items:center; gap:.5rem;
    box-shadow:0 8px 24px rgba(0,0,0,.2);
}
</style>
</head>

<body class="flex min-h-screen">

<?php include '../../includes/sidebar_penjual.php'; ?>

<main class="flex-1 lg:ml-72 p-4 md:p-6 xl:p-8 w-full">

<header class="mt-14 lg:mt-0 mb-7">
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4">
        <div>
            <span class="text-[9px] font-black uppercase tracking-[.2em] text-[var(--muted)]">Arsip Transaksi</span>
            <h1 class="text-[1.9rem] font-extrabold mt-0.5 leading-none">Riwayat Penjualan</h1>
            <p class="text-[var(--muted)] text-xs mt-1.5">Pesanan selesai dan dibatalkan tersimpan di sini</p>
        </div>

        <div class="summary-grid w-full sm:w-auto sm:min-w-[430px]">
            <div class="bg-green-50 border border-green-200 rounded-xl px-4 py-2.5 text-center">
                <p class="text-2xl font-extrabold text-green-600 leading-none"><?= $total_selesai ?></p>
                <p class="text-[9px] font-black uppercase tracking-wider text-green-600 opacity-70">Selesai</p>
            </div>
            <div class="bg-red-50 border border-red-200 rounded-xl px-4 py-2.5 text-center">
                <p class="text-2xl font-extrabold text-red-600 leading-none"><?= $total_batal ?></p>
                <p class="text-[9px] font-black uppercase tracking-wider text-red-600 opacity-70">Batal</p>
            </div>
            <div class="bg-orange-50 border border-orange-200 rounded-xl px-4 py-2.5 text-center">
                <p class="text-lg font-extrabold text-[var(--brand)] leading-none">Rp <?= number_format($total_pendapatan,0,',','.') ?></p>
                <p class="text-[9px] font-black uppercase tracking-wider text-[var(--brand)] opacity-70">Pendapatan</p>
            </div>
        </div>
    </div>

    <div class="mt-3 flex flex-col lg:flex-row lg:items-center justify-between gap-3">
        <div class="flex items-center gap-1.5 bg-green-50 border border-green-100 rounded-lg px-3.5 py-2 text-xs text-green-700 font-semibold w-fit">
            <span class="material-symbols-outlined" style="font-size:14px;font-variation-settings:'FILL' 1">check_circle</span>
            Pesanan yang ditandai selesai otomatis pindah dari Pesanan Masuk ke Riwayat
        </div>

        <div class="flex flex-wrap gap-2">
            <a href="pesanan_masuk.php"
               class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-white border border-[var(--line)] rounded-xl text-xs font-bold text-[var(--ink)] hover:border-[var(--brand-md)] hover:text-[var(--brand)] transition">
                <span class="material-symbols-outlined" style="font-size:15px">arrow_back</span>
                Pesanan Masuk
            </a>
            <button onclick="location.reload()"
                    class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-[var(--brand)] rounded-xl text-xs font-bold text-white hover:bg-[var(--brand-dk)] transition">
                <span class="material-symbols-outlined" style="font-size:15px">refresh</span>
                Refresh
            </button>
        </div>
    </div>
</header>

<div class="grid grid-cols-1 xl:grid-cols-2 gap-4 max-w-none items-start">

<?php if (count($rows) > 0): ?>
<?php foreach ($rows as $p):
    $id_p = (int)$p['id_pesanan'];

    $dq = mysqli_query($koneksi, "
        SELECT
            dp.qty,
            dp.subtotal,
            dp.catatan,
            COALESCE(dp.nama_menu, m.nama_menu) AS nama_menu,
            COALESCE(NULLIF(dp.harga, 0), m.harga, 0) AS harga,
            m.foto,
            u.rating,
            u.komentar
        FROM detail_pesanan dp
        LEFT JOIN menu m ON dp.id_menu = m.id_menu
        LEFT JOIN ulasan u ON u.id_menu = dp.id_menu AND u.id_user = ".(int)$p['id_user']."
        WHERE dp.id_pesanan = $id_p
    ");

    $items = [];
    $jml = 0;
    while ($d = mysqli_fetch_assoc($dq)) {
        $d['qty'] = (int)($d['qty'] ?? 0);
        $d['harga'] = (float)($d['harga'] ?? 0);
        $d['subtotal'] = (float)($d['subtotal'] ?? 0);

        if ($d['subtotal'] <= 0 && $d['harga'] > 0 && $d['qty'] > 0) {
            $d['subtotal'] = $d['harga'] * $d['qty'];
        }
        if ($d['harga'] <= 0 && $d['subtotal'] > 0 && $d['qty'] > 0) {
            $d['harga'] = $d['subtotal'] / $d['qty'];
        }

        $jml += $d['qty'];
        $items[] = $d;
    }

    $total_tampil = (float)($p['total_harga'] ?? 0);
    if ($total_tampil <= 0) {
        $total_tampil = array_sum(array_column($items, 'subtotal'));
    }

    $is_selesai = $p['status'] === 'Selesai';
    $badge_class = $is_selesai ? 'badge-selesai' : 'badge-batal';
    $badge_icon = $is_selesai ? 'task_alt' : 'cancel';
?>

<article id="pesanan-<?= $id_p ?>" class="history-card">
    <div class="flex items-center justify-between gap-3 px-5 pt-4 pb-3 border-b border-[var(--line)]">
        <div class="flex items-center gap-3 min-w-0">
            <img src="../../uploads/logo/<?= htmlspecialchars($p['logo'] ?? '') ?>"
                 class="w-10 h-10 rounded-lg object-cover border border-stone-100 shrink-0"
                 onerror="this.src='../../uploads/logo/logo_1778890101.png'">
            <div class="min-w-0">
                <p class="text-[9px] font-black uppercase tracking-widest text-[var(--muted)] leading-none mb-0.5 truncate">
                    <?= htmlspecialchars($p['nama_kantin']) ?>
                </p>
                <p class="font-extrabold text-base leading-none truncate">
                    #<?= htmlspecialchars($p['kode_pesanan'] ?: str_pad($id_p, 4, '0', STR_PAD_LEFT)) ?>
                </p>
            </div>
        </div>

        <span class="badge <?= $badge_class ?>">
            <span class="material-symbols-outlined" style="font-size:13px;font-variation-settings:'FILL' 1"><?= $badge_icon ?></span>
            <?= htmlspecialchars($p['status']) ?>
        </span>
    </div>

    <div class="px-5 py-2.5 flex flex-wrap gap-x-3 gap-y-1.5 bg-[var(--brand-lt)] border-b border-[var(--line)]">
        <span class="meta-chip text-xs font-bold">
            <span class="material-symbols-outlined text-[var(--brand)]" style="font-size:13px;font-variation-settings:'FILL' 1">person</span>
            <?= htmlspecialchars($p['username']) ?>
        </span>
        <span class="meta-chip text-xs text-[var(--muted)]">
            <span class="material-symbols-outlined text-[var(--brand)]" style="font-size:13px">schedule</span>
            <?= date('d M Y, H:i', strtotime($p['tanggal'])) ?>
        </span>
        <?php if (!empty($p['nomor_antrean'])): ?>
        <span class="meta-chip text-xs font-bold text-[var(--brand)]">
            <span class="material-symbols-outlined" style="font-size:13px">confirmation_number</span>
            Antrean <?= htmlspecialchars($p['nomor_antrean']) ?>
        </span>
        <?php endif; ?>
        <?php if (!empty($p['metode_pembayaran'])): ?>
        <span class="meta-chip text-xs text-[var(--muted)]">
            <span class="material-symbols-outlined text-[var(--brand)]" style="font-size:13px">payments</span>
            <?= htmlspecialchars($p['metode_pembayaran']) ?>
        </span>
        <?php endif; ?>
    </div>

    <?php if (!empty($p['catatan'])): ?>
    <div class="px-5 py-2 bg-amber-50 border-b border-amber-100 flex items-start gap-1.5">
        <span class="material-symbols-outlined text-amber-500 shrink-0" style="font-size:13px;margin-top:1px">sticky_note_2</span>
        <p class="text-xs text-amber-800"><span class="font-bold">Catatan:</span> <?= htmlspecialchars($p['catatan']) ?></p>
    </div>
    <?php endif; ?>

    <div class="divide-y divide-[var(--line)]">
        <?php foreach ($items as $d): ?>
        <div class="flex items-center gap-3 px-5 py-2.5">
            <div class="relative shrink-0">
                <img src="../../uploads/<?= htmlspecialchars($d['foto'] ?? '') ?>"
                     class="w-11 h-11 rounded-xl object-cover bg-stone-100"
                     onerror="this.src='../../assets/img/default-food.jpg'">
                <span class="absolute -top-1 -right-1 bg-[var(--brand)] text-white font-black rounded-full flex items-center justify-center"
                      style="width:1rem;height:1rem;font-size:.55rem;">
                    <?= $d['qty'] ?>
                </span>
            </div>

            <div class="flex-1 min-w-0">
                <p class="font-bold text-sm truncate"><?= htmlspecialchars($d['nama_menu']) ?></p>
                <p class="text-[11px] text-[var(--muted)]">
                    <?= $d['qty'] ?><?= $d['harga'] > 0 ? ' x Rp '.number_format($d['harga'],0,',','.') : ' item' ?>
                </p>
                <?php if (!empty($d['catatan'])): ?>
                <p class="text-[10px] text-amber-600 mt-0.5">Catatan: <?= htmlspecialchars($d['catatan']) ?></p>
                <?php endif; ?>
                <?php if (!empty($d['rating'])): ?>
                <p class="text-[10px] text-yellow-700 mt-0.5">
                    <?= str_repeat('★', (int)$d['rating']) ?> <?= htmlspecialchars($d['komentar'] ?: 'Diulas pembeli') ?>
                </p>
                <?php endif; ?>
            </div>

            <?php if ($d['subtotal'] > 0): ?>
            <div class="text-sm font-extrabold text-[var(--brand)] shrink-0 tabular-nums">
                Rp <?= number_format($d['subtotal'],0,',','.') ?>
            </div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="px-5 py-3.5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 bg-stone-50/60 border-t border-[var(--line)]">
        <div>
            <p class="text-[9px] font-black uppercase tracking-widest text-[var(--muted)]">Total Pembayaran</p>
            <p class="text-xl font-extrabold text-[var(--brand)] tabular-nums">
                <?= $total_tampil > 0 ? 'Rp '.number_format($total_tampil,0,',','.') : 'Total belum tersedia' ?>
            </p>
            <p class="text-[11px] text-[var(--muted)]"><?= $jml ?> item</p>
        </div>

        <a href="#pesanan-<?= $id_p ?>"
           class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-white border border-[var(--line)] rounded-xl text-xs font-bold text-[var(--ink)] hover:border-[var(--brand-md)] hover:text-[var(--brand)] transition">
            <span class="material-symbols-outlined" style="font-size:15px">receipt_long</span>
            Detail tampil
        </a>
    </div>
</article>

<?php endforeach; ?>

<?php else: ?>
<div class="bg-white border border-[var(--line)] rounded-2xl py-14 px-8 text-center max-w-3xl xl:col-span-2">
    <div class="w-14 h-14 bg-[var(--brand-lt)] rounded-xl flex items-center justify-center mx-auto mb-4">
        <span class="material-symbols-outlined text-[var(--brand)]" style="font-size:32px;font-variation-settings:'FILL' 1">history</span>
    </div>
    <h3 class="text-lg font-extrabold">Belum Ada Riwayat</h3>
    <p class="text-[var(--muted)] text-sm mt-1">Pesanan selesai dan dibatalkan akan muncul di sini.</p>
    <a href="pesanan_masuk.php"
       class="inline-flex items-center justify-center gap-1.5 mt-5 px-5 py-2 bg-[var(--brand-lt)] text-[var(--brand)] rounded-xl text-sm font-bold hover:bg-[var(--brand-md)] transition">
        <span class="material-symbols-outlined" style="font-size:15px">arrow_back</span>
        Kembali ke Pesanan Masuk
    </a>
</div>
<?php endif; ?>

</div>
</main>

<?php if (isset($_GET['success']) && $_GET['success'] === 'selesai'): ?>
<div id="toast" class="toast">
    <span class="material-symbols-outlined text-green-400" style="font-size:16px;font-variation-settings:'FILL' 1">check_circle</span>
    Pesanan selesai dan sudah masuk riwayat.
</div>
<script>
setTimeout(() => {
    const t = document.getElementById('toast');
    if (t) {
        t.style.transition = 'opacity .4s';
        t.style.opacity = '0';
        setTimeout(() => t.remove(), 400);
    }
}, 3500);
</script>
<?php endif; ?>

</body>
</html>
