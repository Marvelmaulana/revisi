<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include(__DIR__ . '/../../config/config.php');
include(__DIR__ . '/../../includes/pembeli_helpers.php');
kk_ensure_buyer_schema($koneksi);

if (!isset($_SESSION['id_user']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'pembeli') {
    header("Location: ../auth/login.php");
    exit();
}

$id_user = (int)$_SESSION['id_user'];

$sql = "
SELECT
    p.*,
    k.nama_kantin,
    k.logo,
    (
        SELECT m.foto
        FROM detail_pesanan dp
        LEFT JOIN menu m ON dp.id_menu = m.id_menu
        WHERE dp.id_pesanan = p.id_pesanan
        LIMIT 1
    ) AS foto_menu,
    COALESCE((
        SELECT SUM(dp.qty)
        FROM detail_pesanan dp
        WHERE dp.id_pesanan = p.id_pesanan
    ), 0) AS total_item
FROM pesanan p
JOIN kantin k ON p.id_kantin = k.id_kantin
WHERE p.id_user = $id_user
  AND p.status IN ('Pending', 'Diproses', 'Siap Diambil')
ORDER BY p.id_pesanan DESC
";

$query = mysqli_query($koneksi, $sql);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Pesanan Aktif - Kantin Kita</title>

<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@700;800;900&family=Be+Vietnam+Pro:wght@400;500;600;700&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>

<style>
* { box-sizing:border-box; }
html, body { overflow-x:hidden; max-width:100%; }
body {
    font-family:'Be Vietnam Pro',sans-serif;
    background:
        radial-gradient(circle at top left, rgba(255,107,53,.13), transparent 28rem),
        radial-gradient(circle at top right, rgba(56,189,248,.10), transparent 25rem),
        #fffdfc;
}
.font-headline { font-family:'Plus Jakarta Sans',sans-serif; }
.material-symbols-outlined {
    font-variation-settings:'FILL' 1,'wght' 500,'GRAD' 0,'opsz' 24;
    display:inline-flex; align-items:center; justify-content:center; line-height:1;
}
.status-step { height:3px; border-radius:999px; background:#e7e5e4; flex:1; }
.status-step.active { background:#b22204; }
</style>
</head>

<body class="text-stone-800 pb-32">

<header class="bg-white/90 backdrop-blur-xl sticky top-0 z-40 px-5 py-4 shadow-sm">
  <div class="w-full flex items-center justify-between gap-3">
    <div class="flex items-center gap-3 min-w-0">
        <button onclick="window.location.href='dashboard.php'" class="w-10 h-10 rounded-full bg-stone-100 text-stone-500 flex items-center justify-center">
            <span class="material-symbols-outlined text-xl">arrow_back</span>
        </button>
        <div class="min-w-0">
            <h1 class="text-lg font-extrabold font-headline italic uppercase tracking-tighter text-[#b22204]">Pesanan Aktif</h1>
            <p class="text-[10px] uppercase tracking-widest text-stone-400 font-bold">Pantau pesanan yang masih berjalan</p>
        </div>
    </div>
    <a href="riwayat_pembeli.php" class="w-10 h-10 rounded-full bg-orange-50 text-[#b22204] flex items-center justify-center">
        <span class="material-symbols-outlined text-xl">history</span>
    </a>
  </div>
</header>

<main class="max-w-[1400px] mx-auto px-4 md:px-6 py-5">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-3 md:gap-4">
        <?php if (mysqli_num_rows($query) > 0): ?>
        <?php while ($row = mysqli_fetch_assoc($query)): ?>
        <?php
            $status = $row['status'];
            $statusColor = 'bg-orange-100 text-orange-700';
            $statusIcon = 'hourglass_empty';
            $step = 1;

            if ($status === 'Diproses') {
                $statusColor = 'bg-blue-100 text-blue-700';
                $statusIcon = 'skillet';
                $step = 2;
            }
            if ($status === 'Siap Diambil') {
                $statusColor = 'bg-green-100 text-green-700';
                $statusIcon = 'check_circle';
                $step = 3;
            }

            $ringkasan = mysqli_query($koneksi, "
                SELECT COALESCE(dp.nama_menu, m.nama_menu) AS nama_menu, dp.qty
                FROM detail_pesanan dp
                LEFT JOIN menu m ON dp.id_menu = m.id_menu
                WHERE dp.id_pesanan = ".(int)$row['id_pesanan']."
                LIMIT 2
            ");

            $items = [];
            while ($r = mysqli_fetch_assoc($ringkasan)) {
                $items[] = trim($r['nama_menu'] . ' x' . (int)$r['qty']);
            }
        ?>

        <article class="bg-white/95 rounded-3xl border border-stone-100 shadow-sm overflow-hidden hover:shadow-xl hover:shadow-red-900/5 transition-all">
            <div class="p-4 flex gap-3">
                <img src="<?= kk_upload_url($row['foto_menu'] ?? '', 'menu') ?>"
                     class="w-20 h-20 rounded-2xl object-cover bg-stone-100 shrink-0"
                     onerror="this.src='../../public/assets/img/default-food.svg'">

                <div class="flex-1 min-w-0">
                    <div class="flex items-start justify-between gap-2">
                        <div class="min-w-0">
                            <p class="text-[10px] font-black uppercase tracking-[0.15em] text-stone-400 truncate">
                                <?= htmlspecialchars($row['kode_pesanan'] ?: '#'.str_pad($row['id_pesanan'], 5, '0', STR_PAD_LEFT)) ?>
                            </p>
                            <h2 class="font-headline font-extrabold text-base leading-tight text-stone-800 truncate mt-1">
                                <?= htmlspecialchars($row['nama_kantin']) ?>
                            </h2>
                            <p class="text-[11px] text-stone-400 mt-1">
                                <?= (int)$row['total_item'] ?> item - <?= date('d M Y, H:i', strtotime($row['tanggal'])) ?>
                            </p>
                        </div>
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wide flex items-center gap-1 whitespace-nowrap <?= $statusColor ?>">
                            <span class="material-symbols-outlined text-xs"><?= $statusIcon ?></span>
                            <?= htmlspecialchars($status) ?>
                        </span>
                    </div>

                    <div class="mt-3 space-y-1">
                        <?php foreach ($items as $item): ?>
                        <p class="text-xs text-stone-600 truncate"><?= htmlspecialchars($item) ?></p>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="px-4 pb-3">
                <div class="flex gap-1 mb-3">
                    <div class="status-step <?= $step >= 1 ? 'active' : '' ?>"></div>
                    <div class="status-step <?= $step >= 2 ? 'active' : '' ?>"></div>
                    <div class="status-step <?= $step >= 3 ? 'active' : '' ?>"></div>
                </div>

                <?php if (!empty($row['catatan'])): ?>
                <div class="mb-3 bg-orange-50 border border-orange-100 rounded-2xl p-3 flex items-start gap-2">
                    <span class="material-symbols-outlined text-orange-500 text-lg shrink-0">edit_note</span>
                    <p class="text-xs text-stone-700 break-words"><?= htmlspecialchars($row['catatan']) ?></p>
                </div>
                <?php endif; ?>

                <div class="flex items-end justify-between gap-3 pt-3 border-t border-dashed border-stone-100">
                    <div>
                        <p class="text-[10px] uppercase font-bold text-stone-400">Total Bayar</p>
                        <p class="text-xl font-black text-[#b22204]">Rp <?= number_format($row['total_harga'],0,',','.') ?></p>
                    </div>

                    <div class="flex gap-2">
                        <a href="lacak_pesanan.php?id=<?= (int)$row['id_pesanan'] ?>"
                           class="bg-stone-900 text-white px-4 py-2.5 rounded-2xl text-xs font-black active:scale-95 transition-all flex items-center gap-2">
                            Lacak
                            <span class="material-symbols-outlined text-sm">route</span>
                        </a>

                        <?php if ($row['status'] === 'Pending'): ?>
                        <button onclick="if(confirm('Batalkan pesanan ini?')){ window.location.href='batalkan_pesanan.php?id=<?= (int)$row['id_pesanan'] ?>'; }"
                                class="bg-red-100 text-red-700 px-3 py-2.5 rounded-2xl text-xs font-black active:scale-95 transition-all flex items-center gap-1">
                            <span class="material-symbols-outlined text-sm">close</span>
                            Batal
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </article>
        <?php endwhile; ?>

        <?php else: ?>
        <div class="text-center py-24 lg:col-span-2">
            <div class="w-24 h-24 bg-stone-100 rounded-full flex items-center justify-center mx-auto mb-5">
                <span class="material-symbols-outlined text-5xl text-stone-300">receipt_long</span>
            </div>
            <h3 class="font-headline font-extrabold text-lg text-stone-500">Belum Ada Pesanan</h3>
            <p class="text-sm text-stone-400 mt-2 max-w-xs mx-auto">Yuk pesan makanan favoritmu sekarang di kantin sekolah.</p>
            <button onclick="window.location.href='dashboard.php'"
                    class="mt-7 bg-[#b22204] text-white px-8 py-4 rounded-full text-sm font-black shadow-xl shadow-red-200 active:scale-95 transition-all">
                Pesan Sekarang
            </button>
        </div>
        <?php endif; ?>
    </div>
</main>

<?php
$current_page = 'orders';
include(__DIR__ . '/../../includes/navbar.php');
?>

</body>
</html>
