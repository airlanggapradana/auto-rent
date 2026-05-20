<?php
session_start();
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}
require_once 'koneksi.php';

// Ambil Statistik
$queryTotal = pg_query($conn, "SELECT COUNT(*) FROM mobil");
$totalMobil = pg_fetch_result($queryTotal, 0, 0);

$queryTersedia = pg_query($conn, "SELECT COUNT(*) FROM mobil WHERE status_mobil = 'Tersedia'");
$mobilTersedia = pg_fetch_result($queryTersedia, 0, 0);

$queryDisewa = pg_query($conn, "SELECT COUNT(*) FROM mobil WHERE status_mobil = 'Disewa'");
$mobilDisewa = pg_fetch_result($queryDisewa, 0, 0);

// Ambil Data Mobil
$queryMobil = pg_query($conn, "SELECT * FROM mobil ORDER BY id_mobil DESC");
$mobilList = pg_fetch_all($queryMobil);
if (!$mobilList) $mobilList = [];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — AutoRent</title>
    <meta name="description" content="Dashboard manajemen armada rental mobil AutoRent. Lihat statistik dan kelola data kendaraan.">
    <link rel="stylesheet" href="style.css">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
</head>
<body>

    <!-- ── Navbar ── -->
    <nav class="navbar">
        <a href="index.php" class="nav-brand">
            <div class="brand-icon">
                <i class="ph ph-car-profile"></i>
            </div>
            <span class="brand-name">AutoRent</span>
        </a>
        <ul class="nav-links">
            <li>
                <a href="index.php" class="active">
                    <i class="ph ph-squares-four"></i> Dashboard
                </a>
            </li>
            <li>
                <a href="tambah.php">
                    <i class="ph ph-plus-circle"></i> Tambah
                </a>
            </li>
            <li>
                <a href="logout.php" class="btn-logout">
                    <i class="ph ph-sign-out"></i> Logout
                </a>
            </li>
        </ul>
    </nav>

    <!-- ── Hero ── -->
    <div class="hero">
        <div class="hero-eyebrow">
            <i class="ph ph-car-profile"></i>
            Panel Admin
        </div>
        <h1>Kelola Armada Rental Anda</h1>
        <p>Pantau status armada, tambah kendaraan baru, dan optimalkan operasional rental mobil Anda dari satu dashboard.</p>
    </div>

    <!-- ── Stats ── -->
    <div class="stats-container">
        <div class="stat-card">
            <div class="stat-icon icon-blue">
                <i class="ph ph-car"></i>
            </div>
            <div class="stat-info">
                <h3><?= $totalMobil ?></h3>
                <p>Total Armada</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon icon-green">
                <i class="ph ph-check-circle"></i>
            </div>
            <div class="stat-info">
                <h3><?= $mobilTersedia ?></h3>
                <p>Tersedia</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon icon-orange">
                <i class="ph ph-key"></i>
            </div>
            <div class="stat-info">
                <h3><?= $mobilDisewa ?></h3>
                <p>Sedang Disewa</p>
            </div>
        </div>
    </div>

    <!-- ── Main Content ── -->
    <div class="container">
        <div class="controls">
            <div class="controls-left">
                <span class="section-title">Daftar Kendaraan</span>
                <span class="section-count"><?= count($mobilList) ?> unit</span>
            </div>
            <div style="display:flex; gap: 0.75rem; align-items: center; flex-wrap: wrap;">
                <div class="search-box">
                    <input
                        type="text"
                        id="searchInput"
                        class="search-input"
                        placeholder="&#xe0fc; Cari nama atau merk..."
                    >
                    <select id="filterStatus" class="filter-select">
                        <option value="">Semua Status</option>
                        <option value="tersedia">Tersedia</option>
                        <option value="disewa">Disewa</option>
                    </select>
                </div>
                <a href="tambah.php" class="btn btn-primary" id="btn-tambah">
                    <i class="ph ph-plus"></i> Tambah Mobil
                </a>
            </div>
        </div>

        <div class="cars-grid" id="carsGrid">
            <?php if (count($mobilList) > 0): ?>
                <?php foreach ($mobilList as $i => $mobil): ?>
                    <?php
                    $gambar = !empty($mobil['gambar_mobil']) && file_exists('uploads/' . $mobil['gambar_mobil'])
                        ? 'uploads/' . $mobil['gambar_mobil']
                        : 'https://images.unsplash.com/photo-1550355291-bbee04a92027?auto=format&fit=crop&q=80&w=600';
                    $delay = ($i % 6) * 0.05;
                    ?>
                    <div class="car-card" style="animation-delay: <?= $delay ?>s"
                         data-name="<?= strtolower(htmlspecialchars($mobil['nama_mobil'])) ?>"
                         data-merk="<?= strtolower(htmlspecialchars($mobil['merk'])) ?>"
                         data-status="<?= strtolower($mobil['status_mobil']) ?>">

                        <div class="car-img-wrap">
                            <img
                                src="<?= $gambar ?>"
                                alt="<?= htmlspecialchars($mobil['nama_mobil']) ?>"
                                class="car-img"
                                loading="lazy"
                            >
                            <div class="car-img-overlay"></div>
                        </div>

                        <div class="car-content">
                            <div class="car-header">
                                <div>
                                    <h3 class="car-title"><?= htmlspecialchars($mobil['nama_mobil']) ?></h3>
                                    <p class="car-brand"><?= htmlspecialchars($mobil['merk']) ?></p>
                                </div>
                                <?php if ($mobil['status_mobil'] == 'Tersedia'): ?>
                                    <span class="badge badge-tersedia">Tersedia</span>
                                <?php else: ?>
                                    <span class="badge badge-disewa">Disewa</span>
                                <?php endif; ?>
                            </div>

                            <div class="car-details">
                                <span><i class="ph ph-calendar-blank"></i> <?= $mobil['tahun'] ?></span>
                                <span><i class="ph ph-gas-pump"></i> Bensin</span>
                                <span><i class="ph ph-users"></i> 4–5 kursi</span>
                            </div>

                            <div class="car-price">
                                Rp <?= number_format($mobil['harga_sewa'], 0, ',', '.') ?>
                                <small>/ hari</small>
                            </div>

                            <div class="car-actions">
                                <a href="detail.php?id=<?= $mobil['id_mobil'] ?>" class="btn btn-outline">
                                    <i class="ph ph-eye"></i> Detail
                                </a>
                                <a href="edit.php?id=<?= $mobil['id_mobil'] ?>" class="btn btn-warning">
                                    <i class="ph ph-pencil-simple"></i>
                                </a>
                                <a href="hapus.php?id=<?= $mobil['id_mobil'] ?>" class="btn btn-danger btn-delete">
                                    <i class="ph ph-trash"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state">
                    <i class="ph ph-car"></i>
                    <h2>Belum ada data kendaraan</h2>
                    <p>Silakan tambahkan data mobil pertama Anda.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="script.js"></script>
</body>
</html>
