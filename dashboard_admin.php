<?php
session_start();
include 'config.php';

// 1. PROTEKSI: Hanya Admin yang boleh masuk
if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// 2. LOGIKA PENCARIAN
$search = "";
if (isset($_GET['cari'])) {
    $search = mysqli_real_escape_string($koneksi, $_GET['cari']);
    $query_user = "SELECT * FROM users WHERE username LIKE '%$search%' OR email LIKE '%$search%' ORDER BY id_user DESC";
} else {
    $query_user = "SELECT * FROM users ORDER BY id_user DESC";
}

// 3. HITUNG STATISTIK
$count_user = mysqli_num_rows(mysqli_query($koneksi, "SELECT id_user FROM users"));
$count_kantin = mysqli_num_rows(mysqli_query($koneksi, "SELECT id_kantin FROM kantin"));
$count_penjual = mysqli_num_rows(mysqli_query($koneksi, "SELECT id_user FROM users WHERE role='penjual'"));
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | E-Kantin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        :root { --sidebar-width: 250px; }
        body { background-color: #f4f7f6; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .sidebar { width: var(--sidebar-width); min-height: 100vh; background: #1a1d20; color: white; position: fixed; padding: 20px; transition: 0.3s; }
        .main-content { margin-left: var(--sidebar-width); padding: 30px; }
        .card-stat { border: none; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .table-container { background: white; border-radius: 12px; padding: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .nav-link { color: #8d9498; padding: 12px 15px; border-radius: 8px; margin-bottom: 5px; }
        .nav-link:hover, .nav-link.active { background: #343a40; color: #fff; }
        .sticky-header { position: sticky; top: 0; background: white; z-index: 10; }
        .scroll-table { max-height: 500px; overflow-y: auto; }
    </style>
</head>
<body>

<div class="sidebar">
    <h4 class="text-center fw-bold mb-4 text-primary">ADMIN PANEL</h4>
    <hr class="text-secondary">
    <nav class="nav flex-column">
        <a class="nav-link active" href="dashboard_admin.php"><i class="bi bi-grid-1x2-fill me-2"></i> Dashboard</a>
        <a class="nav-link" href="kelola_kantin.php"><i class="bi bi-shop me-2"></i> Kelola Kantin</a>
        <a class="nav-link" href="laporan_transaksi.php"><i class="bi bi-receipt me-2"></i> Laporan</a>
        <div class="mt-4 small text-uppercase text-secondary fw-bold">Akun</div>
        <a class="nav-link text-danger" href="logout.php"><i class="bi bi-box-arrow-left me-2"></i> Keluar</a>
    </nav>
</div>

<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-0">Ringkasan Sistem</h2>
            <p class="text-muted">Halo Admin, berikut adalah data terbaru aplikasi kantin.</p>
        </div>
        <div class="text-end">
            <span class="badge bg-dark rounded-pill px-3 py-2">
                <i class="bi bi-person-circle me-1"></i> <?php echo $_SESSION['username']; ?>
            </span>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card card-stat p-3 border-start border-primary border-4">
                <div class="d-flex align-items-center">
                    <div class="bg-primary bg-opacity-10 p-3 rounded-3 me-3 text-primary">
                        <i class="bi bi-people fs-2"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1">Total Pengguna</h6>
                        <h3 class="fw-bold mb-0"><?php echo $count_user; ?></h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-stat p-3 border-start border-success border-4">
                <div class="d-flex align-items-center">
                    <div class="bg-success bg-opacity-10 p-3 rounded-3 me-3 text-success">
                        <i class="bi bi-shop-window fs-2"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1">Total Kantin</h6>
                        <h3 class="fw-bold mb-0"><?php echo $count_kantin; ?></h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-stat p-3 border-start border-warning border-4">
                <div class="d-flex align-items-center">
                    <div class="bg-warning bg-opacity-10 p-3 rounded-3 me-3 text-warning">
                        <i class="bi bi-person-badge fs-2"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1">Total Penjual</h6>
                        <h3 class="fw-bold mb-0"><?php echo $count_penjual; ?></h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="table-container">
        <div class="row mb-3 align-items-center">
            <div class="col-md-6">
                <h5 class="fw-bold mb-0">Manajemen Pengguna</h5>
            </div>
            <div class="col-md-6 text-md-end">
                <form action="" method="GET" class="d-inline-flex">
                    <input type="text" name="cari" class="form-control form-control-sm me-2" placeholder="Cari username/email..." value="<?php echo $search; ?>">
                    <button type="submit" class="btn btn-sm btn-primary">Cari</button>
                    <?php if($search != ""): ?>
                        <a href="dashboard_admin.php" class="btn btn-sm btn-secondary ms-1">Reset</a>
                    <?php endif; ?>
                </form>
            </div>
        </div>

        <div class="scroll-table">
            <table class="table table-hover align-middle">
                <thead class="table-light sticky-header">
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status Kantin</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $res = mysqli_query($koneksi, $query_user);
                    if (mysqli_num_rows($res) > 0) {
                        while ($row = mysqli_fetch_assoc($res)) {
                            // Warna Badge Role
                            $role_class = "bg-secondary";
                            if($row['role'] == 'admin') $role_class = "bg-danger";
                            if($row['role'] == 'penjual') $role_class = "bg-info text-dark";
                            if($row['role'] == 'siswa') $role_class = "bg-success";
                            ?>
                            <tr>
                                <td class="fw-bold"><?php echo $row['id_user']; ?></td>
                                <td><?php echo $row['username']; ?></td>
                                <td><?php echo $row['email']; ?></td>
                                <td><span class="badge <?php echo $role_class; ?>"><?php echo strtoupper($row['role']); ?></span></td>
                                <td>
                                    <?php 
                                    echo $row['id_kantin'] 
                                         ? "<span class='text-primary fw-semibold'><i class='bi bi-shop me-1'></i>Kantin " . $row['id_kantin'] . "</span>" 
                                         : "<span class='text-muted small'>Belum terhubung</span>"; 
                                    ?>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group">
                                        <a href="edit_user.php?id=<?php echo $row['id_user']; ?>" class="btn btn-sm btn-outline-primary" title="Edit">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        <a href="hapus_user.php?id=<?php echo $row['id_user']; ?>" 
                                           class="btn btn-sm btn-outline-danger" 
                                           onclick="return confirm('Yakin ingin menghapus user ini? Data tidak bisa dikembalikan.')" 
                                           title="Hapus">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php }
                    } else { ?>
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">Data user tidak ditemukan.</td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
        <div class="mt-2 text-end">
            <small class="text-muted">Total Baris: <?php echo mysqli_num_rows($res); ?></small>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>