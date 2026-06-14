<?php
// sewa.php
session_start();
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}
require_once 'koneksi.php';

$pesan = '';
$selected_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Ambil daftar mobil yang tersedia
$query_available = pg_query($conn, "SELECT * FROM mobil WHERE status_mobil = 'Tersedia' ORDER BY nama_mobil ASC");
$available_cars = pg_fetch_all($query_available);
if (!$available_cars) $available_cars = [];

// Jika ada ID mobil yang dipilih, ambil detailnya
$selected_car = null;
if ($selected_id > 0) {
    $query_car = pg_query_params($conn, "SELECT * FROM mobil WHERE id_mobil = $1", array($selected_id));
    $selected_car = pg_fetch_assoc($query_car);
    
    // Validasi apakah mobil tersebut tersedia
    if ($selected_car && $selected_car['status_mobil'] !== 'Tersedia') {
        echo "<script>alert('Mobil ini sedang disewa atau tidak tersedia!'); window.location.href='index.php';</script>";
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_mobil = (int)$_POST['id_mobil'];
    $nama_peminjam = trim($_POST['nama_peminjam']);
    $nik_peminjam = trim($_POST['nik_peminjam']);
    $no_hp = trim($_POST['no_hp']);
    $tgl_sewa = $_POST['tgl_sewa'];
    $tgl_kembali = $_POST['tgl_kembali'];

    // 1. Validasi Ketersediaan Mobil di Database (Mencegah Race Condition)
    $check_query = pg_query_params($conn, "SELECT status_mobil, harga_sewa FROM mobil WHERE id_mobil = $1", array($id_mobil));
    $car_db = pg_fetch_assoc($check_query);

    if (!$car_db) {
        $pesan = "Mobil tidak ditemukan.";
    } elseif ($car_db['status_mobil'] !== 'Tersedia') {
        $pesan = "Maaf, mobil ini baru saja disewa oleh transaksi lain. Silakan pilih mobil lain.";
    } else {
        // 2. Validasi Tanggal
        $tgl_sewa_ts = strtotime($tgl_sewa);
        $tgl_kembali_ts = strtotime($tgl_kembali);
        
        if ($tgl_kembali_ts < $tgl_sewa_ts) {
            $pesan = "Tanggal kembali tidak boleh kurang dari tanggal sewa.";
        } else {
            // Hitung Durasi Sewa (minimal 1 hari)
            $diff_seconds = $tgl_kembali_ts - $tgl_sewa_ts;
            $days = ($diff_seconds / (24 * 60 * 60));
            $days = max(1, (int)$days);
            
            $harga_sewa = (int)$car_db['harga_sewa'];
            $total_harga = $days * $harga_sewa;

            // Mulai Transaksi Database
            pg_query($conn, "BEGIN");

            // Insert Sewa
            $insert_query = "INSERT INTO sewa (id_mobil, nama_peminjam, nik_peminjam, no_hp, tgl_sewa, tgl_kembali, total_harga, status_pembayaran) 
                             VALUES ($1, $2, $3, $4, $5, $6, $7, $8) RETURNING id_sewa";
            $insert_res = pg_query_params($conn, $insert_query, array(
                $id_mobil, 
                $nama_peminjam, 
                $nik_peminjam, 
                $no_hp, 
                $tgl_sewa, 
                $tgl_kembali, 
                $total_harga, 
                'Pending'
            ));

            // Update Status Mobil
            $update_car_res = pg_query_params($conn, "UPDATE mobil SET status_mobil = 'Disewa' WHERE id_mobil = $1", array($id_mobil));

            if ($insert_res && $update_car_res) {
                pg_query($conn, "COMMIT");
                $sewa_data = pg_fetch_assoc($insert_res);
                $id_sewa = $sewa_data['id_sewa'];
                
                echo "<script>alert('Penyewaan berhasil dibuat! Silakan lanjutkan ke pembayaran.'); window.location.href='bayar.php?id_sewa=" . $id_sewa . "';</script>";
                exit;
            } else {
                pg_query($conn, "ROLLBACK");
                $pesan = "Terjadi kesalahan sistem saat memproses transaksi.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sewa Mobil - AutoRent</title>
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
            <li><a href="index.php"><i class="ph ph-squares-four"></i> Dashboard</a></li>
            <li><a href="tambah.php"><i class="ph ph-plus-circle"></i> Tambah</a></li>
            <li><a href="transaksi.php"><i class="ph ph-receipt"></i> Transaksi</a></li>
            <li><a href="logout.php" class="btn-logout"><i class="ph ph-sign-out"></i> Logout</a></li>
        </ul>
    </nav>

    <div class="container" style="margin-top: 2rem;">
        <div class="form-container">
            <h2 style="margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem;">
                <i class="ph ph-key" style="color: var(--brand-primary-light)"></i> Form Penyewaan Mobil
            </h2>

            <?php if ($pesan): ?>
                <div class="alert alert-error" style="margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem; background: var(--danger-bg); border: 1px solid rgba(244,63,94,0.2); padding: 1rem; border-radius: var(--radius-md); color: #fb7185;">
                    <i class="ph ph-warning-circle"></i>
                    <?= htmlspecialchars($pesan) ?>
                </div>
            <?php endif; ?>

            <form action="" method="POST" id="form-sewa">
                
                <!-- Pilih Mobil -->
                <div class="form-group">
                    <label for="id_mobil">Pilih Mobil yang Akan Disewa</label>
                    <select id="id_mobil" name="id_mobil" class="form-control" required>
                        <option value="" data-harga="0">-- Pilih Mobil --</option>
                        
                        <?php if ($selected_car): ?>
                            <option value="<?= $selected_car['id_mobil'] ?>" data-harga="<?= $selected_car['harga_sewa'] ?>" selected>
                                <?= htmlspecialchars($selected_car['nama_mobil']) ?> (<?= htmlspecialchars($selected_car['merk']) ?>) - Rp <?= number_format($selected_car['harga_sewa'], 0, ',', '.') ?> / hari
                            </option>
                        <?php endif; ?>

                        <?php foreach ($available_cars as $car): ?>
                            <?php if ($selected_car && $car['id_mobil'] == $selected_car['id_mobil']) continue; ?>
                            <option value="<?= $car['id_mobil'] ?>" data-harga="<?= $car['harga_sewa'] ?>">
                                <?= htmlspecialchars($car['nama_mobil']) ?> (<?= htmlspecialchars($car['merk']) ?>) - Rp <?= number_format($car['harga_sewa'], 0, ',', '.') ?> / hari
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Informasi Pelanggan -->
                <div class="form-group">
                    <label for="nama_peminjam">Nama Lengkap Peminjam</label>
                    <input type="text" id="nama_peminjam" name="nama_peminjam" class="form-control" required placeholder="Contoh: Rangga Adi">
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label for="nik_peminjam">NIK / No. Identitas</label>
                        <input type="text" id="nik_peminjam" name="nik_peminjam" class="form-control" required placeholder="Contoh: 3515XXXXXXXXXXXX">
                    </div>
                    <div class="form-group">
                        <label for="no_hp">No. Handphone (Aktif)</label>
                        <input type="text" id="no_hp" name="no_hp" class="form-control" required placeholder="Contoh: 08123456789">
                    </div>
                </div>

                <!-- Tanggal Sewa & Pengembalian -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label for="tgl_sewa">Tanggal Pengambilan / Mulai Sewa</label>
                        <input type="date" id="tgl_sewa" name="tgl_sewa" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="tgl_kembali">Tanggal Pengembalian / Selesai Sewa</label>
                        <input type="date" id="tgl_kembali" name="tgl_kembali" class="form-control" required>
                    </div>
                </div>

                <!-- Estimasi Ringkasan (Interactive & Premium) -->
                <div id="cost-summary" class="stat-card" style="margin-top: 1.5rem; display: none; background: rgba(99,102,241,0.05); border: 1px dashed rgba(99,102,241,0.3); padding: 1.25rem; border-radius: var(--radius-md); animation: fadeIn 0.3s ease;">
                    <div class="stat-icon icon-blue">
                        <i class="ph ph-calculator"></i>
                    </div>
                    <div class="stat-info" style="flex-grow: 1;">
                        <h4 style="font-size: 0.95rem; font-weight: 600; color: var(--text-primary);">Estimasi Biaya Sewa</h4>
                        <p style="margin-top: 0.1rem; color: var(--text-secondary); font-size: 0.85rem;" id="calc-days">0 hari sewa</p>
                    </div>
                    <div class="car-price" id="calc-total" style="font-size: 1.4rem; color: var(--brand-primary-light);">
                        Rp 0
                    </div>
                </div>

                <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                    <button type="submit" class="btn btn-primary" style="flex: 1;"><i class="ph ph-check-square"></i> Buat Penyewaan</button>
                    <a href="index.php" class="btn btn-outline"><i class="ph ph-arrow-left"></i> Kembali</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const selectMobil = document.getElementById('id_mobil');
            const inputTglSewa = document.getElementById('tgl_sewa');
            const inputTglKembali = document.getElementById('tgl_kembali');
            const costSummary = document.getElementById('cost-summary');
            const calcDays = document.getElementById('calc-days');
            const calcTotal = document.getElementById('calc-total');

            // Set minimal tanggal hari ini untuk pengambilan
            const today = new Date().toISOString().split('T')[0];
            inputTglSewa.setAttribute('min', today);

            inputTglSewa.addEventListener('change', function() {
                // Tanggal kembali minimal adalah tanggal sewa
                inputTglKembali.setAttribute('min', this.value);
                if (inputTglKembali.value && inputTglKembali.value < this.value) {
                    inputTglKembali.value = this.value;
                }
                updateCostEstimate();
            });

            inputTglKembali.addEventListener('change', updateCostEstimate);
            selectMobil.addEventListener('change', updateCostEstimate);

            function updateCostEstimate() {
                const selectedOption = selectMobil.options[selectMobil.selectedIndex];
                const hargaPerHari = parseInt(selectedOption.getAttribute('data-harga')) || 0;
                const tglSewa = inputTglSewa.value;
                const tglKembali = inputTglKembali.value;

                if (hargaPerHari > 0 && tglSewa && tglKembali) {
                    const start = new Date(tglSewa);
                    const end = new Date(tglKembali);
                    const diffTime = Math.abs(end - start);
                    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                    
                    const days = diffDays === 0 ? 1 : diffDays;
                    const totalCost = days * hargaPerHari;

                    calcDays.textContent = `${days} hari sewa (${formatRupiah(hargaPerHari)} / hari)`;
                    calcTotal.textContent = formatRupiah(totalCost);
                    
                    costSummary.style.display = 'flex';
                } else {
                    costSummary.style.display = 'none';
                }
            }

            function formatRupiah(number) {
                return 'Rp ' + new Intl.NumberFormat('id-ID').format(number);
            }
        });
    </script>
</body>
</html>
