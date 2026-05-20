<?php
session_start();
include(__DIR__ . '/../../config/config.php');
include(__DIR__ . '/../../includes/pembeli_helpers.php');
kk_ensure_buyer_schema($koneksi);

if (!isset($_SESSION['id_kantin'])) {
    header("Location: ../auth/login.php");
    exit();
}

$id_k = (int)$_SESSION['id_kantin'];
$id = (int)($_GET['id'] ?? 0);
$q = mysqli_query($koneksi, "SELECT * FROM menu WHERE id_menu=$id AND id_kantin=$id_k LIMIT 1");
$m = mysqli_fetch_assoc($q);
if (!$m) {
    header("Location: kelola_menu_penjual.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Menu</title>
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@700;800;900&family=Be+Vietnam+Pro:wght@400;600;700&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet"/>
<style>body{font-family:'Be Vietnam Pro',sans-serif;background:#fff8f6}.headline{font-family:'Plus Jakarta Sans',sans-serif}</style>
</head>
<body class="p-5 md:p-8">
<div class="max-w-xl mx-auto">
    <a href="kelola_menu_penjual.php" class="inline-flex items-center gap-2 text-stone-400 hover:text-[#b22204] font-bold mb-6">
        <span class="material-symbols-outlined">arrow_back</span> Kembali
    </a>
    <div class="bg-white rounded-[2rem] p-6 md:p-8 shadow-xl shadow-red-900/5 border border-orange-100">
        <h1 class="headline text-2xl font-black mb-6">Edit Menu</h1>
        <form action="proses_menu.php?aksi=edit" method="POST" enctype="multipart/form-data" class="space-y-5">
            <input type="hidden" name="id_menu" value="<?= (int)$m['id_menu'] ?>">
            <div>
                <label class="block text-[10px] font-black uppercase tracking-widest text-stone-400 mb-2">Nama Menu</label>
                <input name="nama_menu" value="<?= htmlspecialchars($m['nama_menu']) ?>" required class="w-full bg-stone-50 border-none rounded-2xl px-5 py-4">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-[10px] font-black uppercase tracking-widest text-stone-400 mb-2">Harga</label>
                    <input type="number" name="harga" value="<?= htmlspecialchars($m['harga']) ?>" required class="w-full bg-stone-50 border-none rounded-2xl px-5 py-4">
                </div>
                <div>
                    <label class="block text-[10px] font-black uppercase tracking-widest text-stone-400 mb-2">Kategori</label>
                    <select name="kategori" class="w-full bg-stone-50 border-none rounded-2xl px-5 py-4">
                        <?php foreach (['Makanan','Minuman','Camilan'] as $kat): ?>
                        <option value="<?= $kat ?>" <?= ($m['kategori'] ?? '') === $kat ? 'selected' : '' ?>><?= $kat ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div>
                <label class="block text-[10px] font-black uppercase tracking-widest text-stone-400 mb-2">Deskripsi</label>
                <textarea name="deskripsi" rows="3" class="w-full bg-stone-50 border-none rounded-2xl px-5 py-4"><?= htmlspecialchars($m['deskripsi'] ?? '') ?></textarea>
            </div>
            <div>
                <label class="block text-[10px] font-black uppercase tracking-widest text-stone-400 mb-2">Opsi Level / Pilihan</label>
                <textarea name="opsi_pilihan" rows="3" class="w-full bg-stone-50 border-none rounded-2xl px-5 py-4"><?= htmlspecialchars($m['opsi_pilihan'] ?? '') ?></textarea>
            </div>
            <div class="grid grid-cols-[90px_1fr] gap-4 items-center">
                <img src="../../uploads/<?= htmlspecialchars($m['foto']) ?>" class="w-20 h-20 rounded-2xl object-cover bg-stone-100" onerror="this.src='../../assets/img/default-food.jpg'">
                <div>
                    <label class="block text-[10px] font-black uppercase tracking-widest text-stone-400 mb-2">Ganti Foto</label>
                    <input type="file" name="foto" class="w-full text-sm">
                </div>
            </div>
            <div>
                <label class="block text-[10px] font-black uppercase tracking-widest text-stone-400 mb-2">Status</label>
                <select name="status" class="w-full bg-stone-50 border-none rounded-2xl px-5 py-4">
                    <option value="Tersedia" <?= ($m['status'] ?? '') === 'Tersedia' ? 'selected' : '' ?>>Tersedia</option>
                    <option value="Habis" <?= ($m['status'] ?? '') === 'Habis' ? 'selected' : '' ?>>Habis</option>
                </select>
            </div>
            <button class="w-full py-4 rounded-2xl bg-[#b22204] text-white headline font-black">Simpan Perubahan</button>
        </form>
    </div>
</div>
</body>
</html>
