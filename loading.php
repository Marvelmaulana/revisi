<?php
session_start();

// 1. Cek apakah ada session, jika tidak ada kembalikan ke login
if (!isset($_SESSION['role'])) {
    header("Location: login.php");
    exit();
}

// 2. Tentukan variabel tujuan berdasarkan role yang login
$role = $_SESSION['role'];

if ($role == 'penjual') {
    $tujuan = "dashboard_penjual.php";
} elseif ($role == 'Admin') {
    $tujuan = "dashboard_admin.php";
} else {
    // Default untuk siswa atau guru
    $tujuan = "dashboard.php";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kantin Kita</title>

    <meta http-equiv="refresh" content="3;url=<?= $tujuan; ?>">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Comic Sans MS', cursive, sans-serif; /* Sesuaikan font logo kamu */
        }

        body {
            background-color: #50c8ff; /* Warna biru sesuai gambar figma kamu */
            height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            color: white;
            text-align: center;
        }

        .logo-container {
            width: 120px;
            height: 120px;
            background-color: white;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 20px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }

        .logo-container img {
            width: 70%;
            height: auto;
        }

        h1 {
            font-size: 32px;
            font-weight: bold;
            letter-spacing: 1px;
        }

        .footer-text {
            position: absolute;
            bottom: 40px;
            font-size: 14px;
            opacity: 0.8;
        }

        /* Animasi sedikit agar logo berdenyut pelan */
        .logo-container {
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
    </style>
</head>
<body>

    <div class="logo-container">
        <!-- Ganti src dengan path logo kantin kamu -->
        <img src="https://cdn-icons-png.flaticon.com/512/3448/3448609.png" alt="Logo">
    </div>

    <h1>Kantin Kita</h1>

    <div class="footer-text">
        K2 Project
    </div>

</body>
</html>