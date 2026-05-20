<?php
session_start();

// Jika sudah login, redirect ke dashboard
if (isset($_SESSION['login'])) {
    header("Location: index.php");
    exit;
}

$error = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Untuk demo sederhana ini kita gunakan hardcode admin/admin
    // (Bisa dikembangkan dengan mengecek ke tabel users di database PostgreSQL nantinya)
    if ($username === 'admin' && $password === 'admin') {
        $_SESSION['login'] = true;
        $_SESSION['username'] = $username;
        
        header("Location: index.php");
        exit;
    } else {
        $error = true;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - AutoRent</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: var(--bg-color);
            margin: 0;
        }
        .login-card {
            background: var(--card-bg);
            padding: 2.5rem;
            border-radius: 1rem;
            box-shadow: var(--shadow);
            width: 100%;
            max-width: 400px;
            border: 1px solid var(--border-color);
        }
        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .login-header i {
            font-size: 3.5rem;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }
        .login-header h2 {
            margin: 0;
            color: var(--text-main);
            font-size: 1.75rem;
        }
        .alert-error {
            background-color: rgba(239, 68, 68, 0.1);
            color: var(--danger-color);
            padding: 0.75rem;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
            text-align: center;
            border: 1px solid rgba(239, 68, 68, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }
        .btn-block {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 100%;
            margin-top: 2rem;
            gap: 0.5rem;
            padding: 0.75rem;
            font-size: 1.05rem;
        }
        .info-text {
            text-align: center;
            margin-top: 1.5rem;
            color: var(--text-muted);
            font-size: 0.9rem;
            padding-top: 1.5rem;
            border-top: 1px dashed var(--border-color);
        }
    </style>
</head>
<body>

    <div class="login-card">
        <div class="login-header">
            <i class="ph ph-car-profile"></i>
            <h2>AutoRent Login</h2>
            <p style="color: var(--text-muted); margin-top: 0.5rem; font-size: 0.95rem;">Masuk untuk mengelola rental mobil</p>
        </div>

        <?php if ($error): ?>
            <div class="alert-error">
                <i class="ph ph-warning-circle" style="font-size: 1.25rem;"></i> Username atau Password salah!
            </div>
        <?php endif; ?>

        <form action="" method="POST">
            <div class="form-group">
                <label for="username">Username</label>
                <div style="position: relative;">
                    <i class="ph ph-user" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: 1.25rem;"></i>
                    <input type="text" id="username" name="username" class="form-control" required placeholder="Masukkan username" autofocus style="padding-left: 2.75rem;">
                </div>
            </div>
            
            <div class="form-group" style="margin-top: 1.25rem;">
                <label for="password">Password</label>
                <div style="position: relative;">
                    <i class="ph ph-lock-key" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: 1.25rem;"></i>
                    <input type="password" id="password" name="password" class="form-control" required placeholder="Masukkan password" style="padding-left: 2.75rem;">
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-block">
                <i class="ph ph-sign-in" style="font-size: 1.25rem;"></i> Login
            </button>
        </form>
        
        <div class="info-text">
            *Gunakan <strong>admin</strong> untuk username & password
        </div>
    </div>

</body>
</html>
