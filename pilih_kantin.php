<?php
session_start();
if ($_SESSION['role'] !== 'penjual') { header("Location: login.php"); exit(); }
?>

<!DOCTYPE html>
<html>
<head>
    <title>Verifikasi Kantin</title>
    <style>
        body { background-color: #50c8ff; font-family: sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .card { background: white; padding: 30px; border-radius: 15px; box-shadow: 0 10px 20px rgba(0,0,0,0.1); width: 300px; text-align: center; }
        select, input, button { width: 100%; padding: 12px; margin-top: 15px; border-radius: 8px; border: 1px solid #ddd; box-sizing: border-box; }
        button { background: #0088cc; color: white; border: none; font-weight: bold; cursor: pointer; }
        button:hover { background: #005f8a; }
    </style>
</head>
<body>
    <div class="card">
        <h2>Akses Kantin</h2>
        <p>Tanya Admin untuk password kantin kamu.</p>
        
        <form action="proses_pilih_kantin.php" method="POST">
            <select name="no_kantin" required>
                <option value="">-- Pilih Kantin --</option>
                <option value="1">Kantin 1</option>
                <option value="2">Kantin 2</option>
                <option value="3">Kantin 3</option>
                <option value="4">Kantin 4</option>
                <option value="5">Kantin 5</option>
            </select>
            
            <input type="password" name="pass_kantin" placeholder="Password Kantin" required>
            
            <button type="submit" name="submit_kantin">Masuk Sekarang</button>
        </form>
    </div>
</body>
</html>