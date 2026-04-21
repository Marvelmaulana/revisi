<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login - Kantin Sekolah</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', sans-serif; }
        body { background: #f6f0f0; display: flex; justify-content: center; align-items: center; min-height: 100vh; }
        .card { background: white; width: 360px; border-radius: 25px; overflow: hidden; padding-bottom: 30px; box-shadow: 0 10px 25px rgba(0,0,0,0.5); }
        .header-img { width: 100%; height: 180px; background: #50c8ff; display: flex; justify-content: center; align-items: center; color: white; }
        .form-box { padding: 25px; text-align: center; }
        h2 { margin-bottom: 20px; color: #333; }
        input, select { width: 90%; padding: 14px; margin-bottom: 15px; border-radius: 30px; border: 1px solid #ddd; background: #f9f9f9; outline: none; }
        .btn { width: 90%; padding: 14px; background: #ff9800; border: none; border-radius: 30px; color: white; font-weight: bold; cursor: pointer; }
        .toggle-link { margin-top: 20px; font-size: 14px; color: #666; }
        .toggle-link a { color: #0088cc; text-decoration: none; font-weight: bold; }
    </style>
</head>
<body>
    <div class="card">
        <div class="header-img"><h1>LOGO</h1></div>
        <div class="form-box">
            <h2>Log In</h2>
            <form action="proses.php" method="POST">
                <input type="email" name="email" placeholder="E-mail" required>
                <input type="password" name="password" placeholder="Password" required>
                <select name="role" required>
                    <option value="" disabled selected>Login Sebagai</option>
                    <option value="Siswa">Siswa</option>
                    <option value="Guru">Guru</option>
                    <option value="Penjual">Penjual</option>
                    <option value="Admin">Admin</option>
                </select>
                <button type="submit" name="login_btn" class="btn">Lanjut</button>
            </form>
            <p class="toggle-link">Belum punya akun? <a href="daftar.php">Daftar</a></p>
        </div>
    </div>
</body>
</html>