<?php
if (!function_exists('kk_column_exists')) {
    function kk_column_exists($koneksi, $table, $column) {
        $table = mysqli_real_escape_string($koneksi, $table);
        $column = mysqli_real_escape_string($koneksi, $column);
        $q = mysqli_query($koneksi, "SHOW COLUMNS FROM `$table` LIKE '$column'");
        return $q && mysqli_num_rows($q) > 0;
    }
}

if (!function_exists('kk_ensure_buyer_schema')) {
    function kk_ensure_buyer_schema($koneksi) {
        static $done = false;
        if ($done) return;
        $done = true;

        $alters = [
            ['users', 'foto_profil', "ALTER TABLE users ADD COLUMN foto_profil VARCHAR(255) NULL"],
            ['users', 'bahasa', "ALTER TABLE users ADD COLUMN bahasa VARCHAR(10) NOT NULL DEFAULT 'id'"],
            ['users', 'reset_token', "ALTER TABLE users ADD COLUMN reset_token VARCHAR(100) NULL"],
            ['users', 'reset_expired', "ALTER TABLE users ADD COLUMN reset_expired DATETIME NULL"],
            ['menu', 'opsi_pilihan', "ALTER TABLE menu ADD COLUMN opsi_pilihan TEXT NULL"],
            ['keranjang', 'opsi_pilihan', "ALTER TABLE keranjang ADD COLUMN opsi_pilihan VARCHAR(120) NULL"],
            ['detail_pesanan', 'harga', "ALTER TABLE detail_pesanan ADD COLUMN harga DECIMAL(12,2) NOT NULL DEFAULT 0"],
            ['detail_pesanan', 'subtotal', "ALTER TABLE detail_pesanan ADD COLUMN subtotal DECIMAL(12,2) NOT NULL DEFAULT 0"],
            ['detail_pesanan', 'catatan', "ALTER TABLE detail_pesanan ADD COLUMN catatan TEXT NULL"],
            ['detail_pesanan', 'nama_menu', "ALTER TABLE detail_pesanan ADD COLUMN nama_menu VARCHAR(150) NULL"],
            ['detail_pesanan', 'opsi_pilihan', "ALTER TABLE detail_pesanan ADD COLUMN opsi_pilihan VARCHAR(120) NULL"],
        ];

        foreach ($alters as [$table, $column, $sql]) {
            if (!kk_column_exists($koneksi, $table, $column)) {
                @mysqli_query($koneksi, $sql);
            }
        }

        @mysqli_query($koneksi, "
            CREATE TABLE IF NOT EXISTS password_resets (
                id_reset INT AUTO_INCREMENT PRIMARY KEY,
                id_user INT NOT NULL,
                email VARCHAR(150) NOT NULL,
                token VARCHAR(100) NOT NULL,
                expired_at DATETIME NOT NULL,
                used_at DATETIME NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                INDEX (token),
                INDEX (id_user)
            )
        ");
    }
}

if (!function_exists('kk_upload_image')) {
    function kk_upload_image($field, $targetDir) {
        if (empty($_FILES[$field]['name']) || ($_FILES[$field]['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return '';
        }
        if (!is_dir($targetDir)) {
            @mkdir($targetDir, 0777, true);
        }
        $ext = strtolower(pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        if (!in_array($ext, $allowed, true)) {
            return '';
        }
        $name = 'profile_' . time() . '_' . mt_rand(1000, 9999) . '.' . $ext;
        $dest = rtrim($targetDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $name;
        if (move_uploaded_file($_FILES[$field]['tmp_name'], $dest)) {
            return $name;
        }
        return '';
    }
}

if (!function_exists('kk_format_rupiah')) {
    function kk_format_rupiah($number) {
        return 'Rp ' . number_format((float)$number, 0, ',', '.');
    }
}

if (!function_exists('kk_upload_url')) {
    function kk_upload_url($filename, $kind = 'menu') {
        $filename = trim((string)$filename);
        $fallbacks = [
            'menu' => '../../public/assets/img/default-food.svg',
            'logo' => '../../public/assets/img/default-logo.svg',
            'banner' => '../../public/assets/img/default-banner.svg',
            'profile' => '../../public/assets/img/default-logo.svg',
        ];
        if ($filename === '') {
            return $fallbacks[$kind] ?? $fallbacks['menu'];
        }

        $clean = ltrim(str_replace('\\', '/', $filename), '/');
        $clean = preg_replace('#^(\.\./)+#', '', $clean);
        $candidates = [];

        if (str_starts_with($clean, 'uploads/')) {
            $candidates[] = $clean;
        } else {
            if ($kind === 'logo') $candidates[] = 'uploads/logo/' . $clean;
            if ($kind === 'banner') $candidates[] = 'uploads/banner/' . $clean;
            if ($kind === 'profile') $candidates[] = 'uploads/profil/' . $clean;
            $candidates[] = 'uploads/' . $clean;
            $candidates[] = 'uploads/logo/' . $clean;
            $candidates[] = 'uploads/banner/' . $clean;
        }

        foreach (array_unique($candidates) as $path) {
            $absolute = BASE_PATH . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $path);
            if (is_file($absolute)) {
                return '../../' . $path;
            }
        }

        return $fallbacks[$kind] ?? $fallbacks['menu'];
    }
}
?>
