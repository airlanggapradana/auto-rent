<?php
session_start();
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}
require_once 'koneksi.php';

if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$id_mobil = $_GET['id'];

// Ambil data mobil berdasarkan ID
$query = pg_query_params($conn, "SELECT * FROM mobil WHERE id_mobil = $1", array($id_mobil));
$mobil = pg_fetch_assoc($query);

if (!$mobil) {
    echo "<script>alert('Data mobil tidak ditemukan!'); window.location.href='index.php';</script>";
    exit;
}

$gambar = !empty($mobil['gambar_mobil']) && file_exists('uploads/' . $mobil['gambar_mobil']) 
    ? 'uploads/' . $mobil['gambar_mobil'] 
    : 'https://images.unsplash.com/photo-1550355291-bbee04a92027?auto=format&fit=crop&q=80&w=800'; 
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Mobil - <?= htmlspecialchars($mobil['nama_mobil']) ?></title>
    <link rel="stylesheet" href="style.css">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
</head>
<body>

    <nav class="navbar">
        <a href="index.php" class="nav-brand">
            <i class="ph ph-car-profile"></i> AutoRent
        </a>
        <ul class="nav-links">
            <li><a href="index.php">Dashboard</a></li>
            <li><a href="tambah.php">Tambah Mobil</a></li>
            <li><a href="logout.php" class="btn btn-outline" style="padding: 0.5rem 1rem; border-color: var(--danger-color); color: var(--danger-color);"><i class="ph ph-sign-out"></i> Logout</a></li>
        </ul>
    </nav>

    <div class="container" style="margin-top: 2rem;">
        <div style="margin-bottom: 2rem;">
            <a href="index.php" class="btn btn-outline"><i class="ph ph-arrow-left"></i> Kembali ke Dashboard</a>
        </div>

        <div class="detail-container">
            <img src="<?= $gambar ?>" alt="<?= htmlspecialchars($mobil['nama_mobil']) ?>" class="detail-img">
            
            <div class="detail-info">
                <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                    <div>
                        <h1 class="detail-title"><?= htmlspecialchars($mobil['nama_mobil']) ?></h1>
                        <p class="detail-meta"><?= htmlspecialchars($mobil['merk']) ?> &bull; Tahun <?= $mobil['tahun'] ?></p>
                    </div>
                    <?php if ($mobil['status_mobil'] == 'Tersedia'): ?>
                        <span class="badge badge-tersedia" style="font-size: 1rem; padding: 0.5rem 1rem;">Tersedia</span>
                    <?php else: ?>
                        <span class="badge badge-disewa" style="font-size: 1rem; padding: 0.5rem 1rem;">Disewa</span>
                    <?php endif; ?>
                </div>

                <div class="car-price" style="font-size: 2rem; margin-bottom: 2rem;">
                    Rp <?= number_format($mobil['harga_sewa'], 0, ',', '.') ?> <span style="font-size: 1rem; font-weight: normal; color: var(--text-muted)">/ hari</span>
                </div>

                <ul class="detail-list">
                    <li>
                        <span>Merk</span>
                        <span style="font-weight: 600; color: var(--text-main);"><?= htmlspecialchars($mobil['merk']) ?></span>
                    </li>
                    <li>
                        <span>Tahun Keluaran</span>
                        <span style="font-weight: 600; color: var(--text-main);"><?= $mobil['tahun'] ?></span>
                    </li>
                    <li>
                        <span>Status</span>
                        <span style="font-weight: 600; color: var(--text-main);"><?= $mobil['status_mobil'] ?></span>
                    </li>
                    <li>
                        <span>Bahan Bakar</span>
                        <span style="font-weight: 600; color: var(--text-main);">Bensin</span>
                    </li>
                    <li>
                        <span>Transmisi</span>
                        <span style="font-weight: 600; color: var(--text-main);">Automatic</span>
                    </li>
                </ul>

                <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                    <a href="edit.php?id=<?= $mobil['id_mobil'] ?>" class="btn btn-warning" style="flex: 1; justify-content: center;">
                        <i class="ph ph-pencil-simple"></i> Edit Data
                    </a>
                    <a href="hapus.php?id=<?= $mobil['id_mobil'] ?>" class="btn btn-danger btn-delete" style="flex: 1; justify-content: center;">
                        <i class="ph ph-trash"></i> Hapus
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="script.js"></script>
</body>
</html>
