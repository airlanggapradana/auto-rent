<?php
session_start();
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}
require_once 'koneksi.php';

$pesan = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_mobil = $_POST['nama_mobil'];
    $merk = $_POST['merk'];
    $tahun = $_POST['tahun'];
    $harga_sewa = $_POST['harga_sewa'];
    $status_mobil = $_POST['status_mobil'];
    
    // Upload Gambar
    $gambar_mobil = 'default.jpg';
    if (isset($_FILES['gambar_mobil']) && $_FILES['gambar_mobil']['error'] == 0) {
        $ext = pathinfo($_FILES['gambar_mobil']['name'], PATHINFO_EXTENSION);
        $gambar_mobil = uniqid() . '.' . $ext;
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        move_uploaded_file($_FILES['gambar_mobil']['tmp_name'], $target_dir . $gambar_mobil);
    }

    // Hindari SQL Injection dengan pg_escape_string atau prepared statements
    $query = "INSERT INTO mobil (nama_mobil, merk, tahun, harga_sewa, status_mobil, gambar_mobil) 
              VALUES ($1, $2, $3, $4, $5, $6)";
    
    $result = pg_query_params($conn, $query, array($nama_mobil, $merk, $tahun, $harga_sewa, $status_mobil, $gambar_mobil));

    if ($result) {
        echo "<script>alert('Data mobil berhasil ditambahkan!'); window.location.href='index.php';</script>";
        exit;
    } else {
        $pesan = "Gagal menambah data: " . pg_last_error($conn);
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Mobil - AutoRent</title>
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
            <li><a href="tambah.php" class="active">Tambah Mobil</a></li>
            <li><a href="logout.php" class="btn btn-outline" style="padding: 0.5rem 1rem; border-color: var(--danger-color); color: var(--danger-color);"><i class="ph ph-sign-out"></i> Logout</a></li>
        </ul>
    </nav>

    <div class="container" style="margin-top: 2rem;">
        <div class="form-container">
            <h2 style="margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem;">
                <i class="ph ph-plus-circle" style="color: var(--primary-color)"></i> Tambah Data Mobil
            </h2>
            
            <?php if ($pesan): ?>
                <div style="background: var(--danger-color); color: white; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1rem;">
                    <?= $pesan ?>
                </div>
            <?php endif; ?>

            <form action="" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="nama_mobil">Nama Mobil</label>
                    <input type="text" id="nama_mobil" name="nama_mobil" class="form-control" required placeholder="Contoh: Avanza Veloz">
                </div>
                
                <div class="form-group">
                    <label for="merk">Merk</label>
                    <input type="text" id="merk" name="merk" class="form-control" required placeholder="Contoh: Toyota">
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label for="tahun">Tahun Keluaran</label>
                        <input type="number" id="tahun" name="tahun" class="form-control" required min="1900" max="2099" placeholder="Contoh: 2022">
                    </div>
                    <div class="form-group">
                        <label for="harga_sewa">Harga Sewa / Hari (Rp)</label>
                        <input type="number" id="harga_sewa" name="harga_sewa" class="form-control" required min="1" placeholder="Contoh: 350000">
                    </div>
                </div>

                <div class="form-group">
                    <label for="status_mobil">Status Mobil</label>
                    <select id="status_mobil" name="status_mobil" class="form-control" required>
                        <option value="Tersedia">Tersedia</option>
                        <option value="Disewa">Disewa</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="gambar_mobil">Foto Mobil</label>
                    <input type="file" id="gambar_mobil" name="gambar_mobil" class="form-control" accept="image/*">
                    <div class="image-preview-container">
                        <span class="image-preview-text" id="preview-text">Preview Gambar</span>
                        <img id="preview" src="" alt="Preview">
                    </div>
                </div>

                <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                    <button type="submit" class="btn btn-primary"><i class="ph ph-floppy-disk"></i> Simpan Data</button>
                    <a href="index.php" class="btn btn-outline"><i class="ph ph-arrow-left"></i> Kembali</a>
                </div>
            </form>
        </div>
    </div>

    <script src="script.js"></script>
</body>
</html>
