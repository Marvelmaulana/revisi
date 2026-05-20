<?php
session_start();
include 'config.php';

// 1. PROTEKSI: Hanya Admin
if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// 2. LOGIKA TAMBAH KANTIN (ID OTOMATIS)
if (isset($_POST['tambah_kantin'])) {
    // Sekarang hanya menangkap Nama dan Password
    $nama_kantin = mysqli_real_escape_string($koneksi, $_POST['nama_kantin']);
    $pass_kantin = mysqli_real_escape_string($koneksi, $_POST['pass_kantin']);

    // Query INSERT tanpa menyertakan id_kantin (karena sudah Auto Increment di DB)
    // Pastikan tabel kamu punya kolom 'nama_kantin'
    $query = "INSERT INTO kantin (nama_kantin, pasword_kantin) VALUES ('$nama_kantin', '$pass_kantin')";
    
    if (mysqli_query($koneksi, $query)) {
        echo "<script>alert('Kantin Berhasil Ditambahkan!'); window.location='kelola_kantin.php';</script>";
    } else {
        echo "Gagal: " . mysqli_error($koneksi);
    }
}

// 3. LOGIKA HAPUS KANTIN
if (isset($_GET['hapus'])) {
    $id_hapus = mysqli_real_escape_string($koneksi, $_GET['hapus']);
    mysqli_query($koneksi, "DELETE FROM kantin WHERE id_kantin = '$id_hapus'");
    header("Location: kelola_kantin.php");
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Kantin | Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        :root { --sidebar-width: 250px; }
        body { background-color: #f4f7f6; }
        .sidebar { width: var(--sidebar-width); min-height: 100vh; background: #1a1d20; color: white; position: fixed; padding: 20px; }
        .main-content { margin-left: var(--sidebar-width); padding: 30px; }
        .nav-link { color: #8d9498; padding: 12px 15px; border-radius: 8px; margin-bottom: 5px; }
        .nav-link.active { background: #0d6efd; color: #fff; }
        .card-custom { border: none; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
    </style>
</head>
<body>

<div class="sidebar">
    <h4 class="text-center fw-bold mb-4 text-primary">ADMIN PANEL</h4>
    <nav class="nav flex-column">
        <a class="nav-link" href="dashboard_admin.php"><i class="bi bi-grid-1x2-fill me-2"></i> Dashboard</a>
        <a class="nav-link active" href="kelola_kantin.php"><i class="bi bi-shop me-2"></i> Kelola Kantin</a>
        <a class="nav-link text-danger" href="logout.php"><i class="bi bi-box-arrow-left me-2"></i> Keluar</a>
    </nav>
</div>

<div class="main-content">
    <div class="row">
        <div class="col-md-12 mb-4">
            <h2 class="fw-bold">Manajemen Data Kantin</h2>
            <p class="text-muted">Tambah kantin baru tanpa perlu input ID secara manual.</p>
        </div>

        <div class="col-md-4">
            <div class="card card-custom p-4 mb-4 text-white bg-dark">
                <h5 class="fw-bold mb-3">Tambah Kantin</h5>
                <form action="" method="POST">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">ID KANTIN</label>
                        <input type="text" class="form-control bg-secondary text-white border-0" value="Otomatis" readonly disabled>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Nama Kantin</label>
                        <input type="text" name="nama_kantin" class="form-control" placeholder="Misal: Kantin Sehat" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Password Verifikasi</label>
                        <input type="text" name="pass_kantin" class="form-control" placeholder="Password untuk penjual..." required>
                    </div>
                    <button type="submit" name="tambah_kantin" class="btn btn-primary w-100 fw-bold">SIMPAN KANTIN</button>
                </form>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card card-custom p-4">
                <h5 class="fw-bold mb-3 text-dark">Daftar Kantin Aktif</h5>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Nama Kantin</th>
                                <th>Password Verifikasi</th>
                                <th>Penjual</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Query menampilkan kantin & username penjual yang terhubung
                            $sql = "SELECT k.*, u.username FROM kantin k 
                                    LEFT JOIN users u ON k.id_kantin = u.id_kantin 
                                    ORDER BY k.id_kantin DESC";
                            $res = mysqli_query($koneksi, $sql);
                            
                            if (mysqli_num_rows($res) > 0) {
                                while ($row = mysqli_fetch_assoc($res)) { ?>
                                    <tr>
                                        <td class="fw-bold">#<?php echo $row['id_kantin']; ?></td>
                                        <td><span class="badge bg-light text-dark border p-2"><?php echo $row['nama_kantin']; ?></span></td>
                                        <td><code><?php echo $row['pasword_kantin']; ?></code></td>
                                        <td>
                                            <?php echo $row['username'] ? '<span class="text-success"><i class="bi bi-person-check"></i> '.$row['username'].'</span>' : '<span class="text-muted small italic">Belum Ada</span>'; ?>
                                        </td>
                                        <td class="text-center">
                                            <a href="kelola_kantin.php?hapus=<?php echo $row['id_kantin']; ?>" 
                                               class="btn btn-sm btn-outline-danger" 
                                               onclick="return confirm('Hapus kantin ini?')">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php }
                            } else {
                                echo "<tr><td colspan='5' class='text-center py-3'>Belum ada unit kantin.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>