<?php
session_start();
include(__DIR__ . '/../../config/config.php');
include(__DIR__ . '/../../includes/pembeli_helpers.php');
kk_ensure_buyer_schema($koneksi);

if (!isset($_SESSION['id_user'])) {
    header("Location: ../auth/login.php");
    exit();
}

$id_user = (int)$_SESSION['id_user'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bahasa = in_array($_POST['bahasa'] ?? 'id', ['id','en'], true) ? $_POST['bahasa'] : 'id';
    mysqli_query($koneksi, "UPDATE users SET bahasa='$bahasa' WHERE id_user=$id_user");
    header("Location: pengaturan.php?success=1");
    exit();
}
$q = mysqli_query($koneksi, "SELECT bahasa FROM users WHERE id_user=$id_user");
$u = mysqli_fetch_assoc($q);
$bahasa = $u['bahasa'] ?? 'id';
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8"/><meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Pengaturan</title>
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@700;800;900&family=Be+Vietnam+Pro:wght@400;500;600;700&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1" rel="stylesheet"/>
<style>body{font-family:'Be Vietnam Pro',sans-serif;background:#fffdfc}.headline{font-family:'Plus Jakarta Sans',sans-serif}</style>
</head>
<body class="pb-28">
<header class="bg-white sticky top-0 z-40 px-5 py-4 border-b border-stone-100">
    <div class="max-w-xl mx-auto flex items-center gap-3">
        <button onclick="history.back()" class="w-10 h-10 rounded-2xl bg-stone-100 flex items-center justify-center"><span class="material-symbols-outlined">arrow_back</span></button>
        <h1 class="headline font-black text-lg text-[#b22204]">Pengaturan</h1>
    </div>
</header>
<main class="max-w-xl mx-auto px-5 py-6 space-y-4">
    <?php if (isset($_GET['success'])): ?><div class="bg-green-50 border border-green-100 text-green-700 rounded-2xl p-3 text-sm font-bold">Pengaturan disimpan.</div><?php endif; ?>
    <form method="POST" class="bg-white rounded-[2rem] border border-orange-100 p-5 shadow-sm">
        <div class="flex items-center gap-3 mb-5">
            <div class="w-11 h-11 rounded-2xl bg-sky-50 text-sky-500 flex items-center justify-center"><span class="material-symbols-outlined">translate</span></div>
            <div>
                <h2 class="headline font-black">Ubah Bahasa</h2>
                <p class="text-xs text-stone-400">Untuk saat ini tersimpan di akun dan siap dipakai halaman berikutnya.</p>
            </div>
        </div>
        <div class="grid grid-cols-2 gap-3">
            <label class="cursor-pointer">
                <input type="radio" name="bahasa" value="id" class="hidden peer" <?= $bahasa === 'id' ? 'checked' : '' ?>>
                <div class="rounded-2xl border-2 border-stone-100 peer-checked:border-[#b22204] peer-checked:bg-orange-50 p-4 font-black text-sm">Indonesia</div>
            </label>
            <label class="cursor-pointer">
                <input type="radio" name="bahasa" value="en" class="hidden peer" <?= $bahasa === 'en' ? 'checked' : '' ?>>
                <div class="rounded-2xl border-2 border-stone-100 peer-checked:border-[#b22204] peer-checked:bg-orange-50 p-4 font-black text-sm">English</div>
            </label>
        </div>
        <button class="mt-5 w-full py-4 rounded-2xl bg-[#b22204] text-white headline font-black">Simpan</button>
    </form>
</main>
<?php $current_page = 'profile'; include(__DIR__ . '/../../includes/navbar.php'); ?>
</body>
</html>
