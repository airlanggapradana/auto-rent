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

$pesan = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_mobil = $_POST['nama_mobil'];
    $merk = $_POST['merk'];
    $tahun = $_POST['tahun'];
    $harga_sewa = $_POST['harga_sewa'];
    $status_mobil = $_POST['status_mobil'];
    $gambar_lama = $_POST['gambar_lama'];
    
    $gambar_mobil = $gambar_lama;

    // Cek jika ada upload gambar baru
    if (isset($_FILES['gambar_mobil']) && $_FILES['gambar_mobil']['error'] == 0) {
        $ext = pathinfo($_FILES['gambar_mobil']['name'], PATHINFO_EXTENSION);
        $gambar_mobil = uniqid() . '.' . $ext;
        $target_dir = "uploads/";
        
        move_uploaded_file($_FILES['gambar_mobil']['tmp_name'], $target_dir . $gambar_mobil);
        
        // Hapus gambar lama jika bukan default
        if ($gambar_lama != 'default.jpg' && file_exists($target_dir . $gambar_lama)) {
            unlink($target_dir . $gambar_lama);
        }
    }

    $update_query = "UPDATE mobil SET nama_mobil = $1, merk = $2, tahun = $3, harga_sewa = $4, status_mobil = $5, gambar_mobil = $6 WHERE id_mobil = $7";
    $result = pg_query_params($conn, $update_query, array($nama_mobil, $merk, $tahun, $harga_sewa, $status_mobil, $gambar_mobil, $id_mobil));

    if ($result) {
        echo "<script>alert('Data mobil berhasil diperbarui!'); window.location.href='index.php';</script>";
        exit;
    } else {
        $pesan = "Gagal memperbarui data: " . pg_last_error($conn);
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Mobil - AutoRent</title>
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
        <div class="form-container">
            <h2 style="margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem;">
                <i class="ph ph-pencil-simple" style="color: var(--warning-color)"></i> Edit Data Mobil
            </h2>
            
            <?php if ($pesan): ?>
                <div style="background: var(--danger-color); color: white; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1rem;">
                    <?= $pesan ?>
                </div>
            <?php endif; ?>

            <form action="" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="gambar_lama" value="<?= htmlspecialchars($mobil['gambar_mobil']) ?>">
                
                <div class="form-group">
                    <label for="nama_mobil">Nama Mobil</label>
                    <input type="text" id="nama_mobil" name="nama_mobil" class="form-control" required value="<?= htmlspecialchars($mobil['nama_mobil']) ?>">
                </div>
                
                <div class="form-group">
                    <label for="merk">Merk</label>
                    <input type="text" id="merk" name="merk" class="form-control" required value="<?= htmlspecialchars($mobil['merk']) ?>">
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label for="tahun">Tahun Keluaran</label>
                        <input type="number" id="tahun" name="tahun" class="form-control" required min="1900" max="2099" value="<?= $mobil['tahun'] ?>">
                    </div>
                    <div class="form-group">
                        <label for="harga_sewa">Harga Sewa / Hari (Rp)</label>
                        <input type="number" id="harga_sewa" name="harga_sewa" class="form-control" required min="1" value="<?= $mobil['harga_sewa'] ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label for="status_mobil">Status Mobil</label>
                    <select id="status_mobil" name="status_mobil" class="form-control" required>
                        <option value="Tersedia" <?= $mobil['status_mobil'] == 'Tersedia' ? 'selected' : '' ?>>Tersedia</option>
                        <option value="Disewa" <?= $mobil['status_mobil'] == 'Disewa' ? 'selected' : '' ?>>Disewa</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="gambar_mobil">Foto Mobil (Biarkan kosong jika tidak ingin mengubah)</label>
                    <input type="file" id="gambar_mobil" name="gambar_mobil" class="form-control" accept="image/*">
                    <div class="image-preview-container">
                        <?php 
                        $gambar = !empty($mobil['gambar_mobil']) && file_exists('uploads/' . $mobil['gambar_mobil']) 
                            ? 'uploads/' . $mobil['gambar_mobil'] 
                            : 'https://images.unsplash.com/photo-1550355291-bbee04a92027?auto=format&fit=crop&q=80&w=400'; 
                        ?>
                        <img id="preview" src="<?= $gambar ?>" alt="Preview" style="display: block;">
                        <span class="image-preview-text" id="preview-text" style="display: none;">Preview Gambar</span>
                    </div>
                </div>

                <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                    <button type="submit" class="btn btn-warning"><i class="ph ph-pencil-simple"></i> Update Data</button>
                    <a href="index.php" class="btn btn-outline"><i class="ph ph-arrow-left"></i> Kembali</a>
                </div>
            </form>
        </div>
    </div>

    <script src="script.js"></script>
</body>
</html>
