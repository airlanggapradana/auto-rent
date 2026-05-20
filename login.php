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
    <title>Login — AutoRent</title>
    <meta name="description" content="Masuk ke panel admin AutoRent untuk mengelola armada rental mobil Anda.">
    <link rel="stylesheet" href="style.css">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
</head>
<body>

<div class="login-page">

    <!-- ── Left Decorative Panel ── -->
    <div class="login-panel-left">
        <div class="left-content">
            <div class="left-logo">
                <i class="ph ph-car-profile"></i>
            </div>
            <h1 class="left-title">AutoRent<br>Dashboard</h1>
            <p class="left-subtitle">Platform manajemen rental mobil modern. Kelola armada, pantau status, dan optimalkan bisnis Anda.</p>

            <div class="left-features">
                <div class="feature-item">
                    <i class="ph ph-lightning"></i>
                    <span>Manajemen armada real-time</span>
                </div>
                <div class="feature-item">
                    <i class="ph ph-chart-bar"></i>
                    <span>Dashboard statistik lengkap</span>
                </div>
                <div class="feature-item">
                    <i class="ph ph-shield-check"></i>
                    <span>Panel admin yang aman</span>
                </div>
            </div>
        </div>
    </div>

    <!-- ── Right Form Panel ── -->
    <div class="login-panel-right">
        <div class="login-card">

            <!-- Logo (mobile only) -->
            <div class="login-logo-mobile">
                <div class="login-logo-icon">
                    <i class="ph ph-car-profile"></i>
                </div>
                <span class="login-logo-text">AutoRent</span>
            </div>

            <h2 class="login-heading">Selamat Datang</h2>
            <p class="login-subheading">Masuk untuk mengakses panel admin</p>

            <?php if ($error): ?>
                <div class="alert alert-error">
                    <i class="ph ph-warning-circle"></i>
                    Username atau password salah. Silakan coba lagi.
                </div>
            <?php endif; ?>

            <form action="" method="POST" class="login-form">
                <div class="form-group">
                    <label for="username">Username</label>
                    <div class="input-wrap">
                        <i class="ph ph-user input-icon"></i>
                        <input
                            type="text"
                            id="username"
                            name="username"
                            class="login-input"
                            required
                            placeholder="Masukkan username"
                            autofocus
                            autocomplete="username"
                        >
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-wrap">
                        <i class="ph ph-lock-key input-icon"></i>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            class="login-input"
                            required
                            placeholder="Masukkan password"
                            autocomplete="current-password"
                        >
                    </div>
                </div>

                <button type="submit" id="btn-login" class="btn-login">
                    <i class="ph ph-sign-in"></i>
                    Masuk ke Dashboard
                </button>
            </form>

            <div class="login-hint">
                Gunakan <code>admin</code> untuk username &amp; password
            </div>
        </div>
    </div>

</div>

</body>
</html>
