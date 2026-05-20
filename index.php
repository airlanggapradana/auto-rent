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
    <title>AutoRent - Rental Mobil Premium</title>
    <link rel="stylesheet" href="style.css">
    <!-- Menggunakan Phosphor Icons untuk icon (Opsional tapi mempercantik) -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar">
        <a href="index.php" class="nav-brand">
            <i class="ph ph-car-profile"></i> AutoRent
        </a>
        <ul class="nav-links">
            <li><a href="index.php" class="active">Dashboard</a></li>
            <li><a href="tambah.php">Tambah Mobil</a></li>
            <li><a href="logout.php" class="btn btn-outline" style="padding: 0.5rem 1rem; border-color: var(--danger-color); color: var(--danger-color);"><i class="ph ph-sign-out"></i> Logout</a></li>
        </ul>
    </nav>

    <!-- Hero Section -->
    <div class="hero">
        <h1>Temukan Mobil Impian Anda</h1>
        <p>Sistem manajemen rental mobil modern dengan pelayanan premium dan pilihan armada terbaik untuk perjalanan Anda.</p>
    </div>

    <!-- Stats -->
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
                <p>Mobil Tersedia</p>
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

    <!-- Main Content -->
    <div class="container">
        <div class="controls">
            <div class="search-box">
                <input type="text" id="searchInput" class="search-input" placeholder="Cari nama atau merk mobil...">
                <select id="filterStatus" class="filter-select">
                    <option value="">Semua Status</option>
                    <option value="tersedia">Tersedia</option>
                    <option value="disewa">Disewa</option>
                </select>
            </div>
            <a href="tambah.php" class="btn btn-primary">
                <i class="ph ph-plus"></i> Tambah Data Mobil
            </a>
        </div>

        <div class="cars-grid" id="carsGrid">
            <?php if (count($mobilList) > 0): ?>
                <?php foreach ($mobilList as $mobil): ?>
                    <div class="car-card">
                        <?php 
                        $gambar = !empty($mobil['gambar_mobil']) && file_exists('uploads/' . $mobil['gambar_mobil']) 
                            ? 'uploads/' . $mobil['gambar_mobil'] 
                            : 'https://images.unsplash.com/photo-1550355291-bbee04a92027?auto=format&fit=crop&q=80&w=400'; 
                        ?>
                        <img src="<?= $gambar ?>" alt="<?= htmlspecialchars($mobil['nama_mobil']) ?>" class="car-img">
                        <div class="car-content">
                            <div class="car-header">
                                <div>
                                    <h3 class="car-title"><?= htmlspecialchars($mobil['nama_mobil']) ?></h3>
                                    <span class="car-brand"><?= htmlspecialchars($mobil['merk']) ?></span>
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
                                <span><i class="ph ph-users"></i> 4/5 Kursi</span>
                            </div>

                            <div class="car-price">
                                Rp <?= number_format($mobil['harga_sewa'], 0, ',', '.') ?> <span style="font-size: 0.9rem; font-weight: normal; color: var(--text-muted)">/ hari</span>
                            </div>

                            <div class="car-actions">
                                <a href="detail.php?id=<?= $mobil['id_mobil'] ?>" class="btn btn-outline" title="Detail">
                                    <i class="ph ph-eye"></i> Detail
                                </a>
                                <a href="edit.php?id=<?= $mobil['id_mobil'] ?>" class="btn btn-warning" title="Edit">
                                    <i class="ph ph-pencil-simple"></i>
                                </a>
                                <a href="hapus.php?id=<?= $mobil['id_mobil'] ?>" class="btn btn-danger btn-delete" title="Hapus">
                                    <i class="ph ph-trash"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="grid-column: 1 / -1; text-align: center; padding: 3rem; color: var(--text-muted);">
                    <i class="ph ph-car" style="font-size: 4rem; margin-bottom: 1rem; opacity: 0.5;"></i>
                    <h2>Belum ada data mobil</h2>
                    <p>Silakan tambahkan data mobil pertama Anda.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="script.js"></script>
</body>
</html>
