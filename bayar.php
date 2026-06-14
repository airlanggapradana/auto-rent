<?php
// bayar.php
session_start();
// Halaman ini bisa diakses umum/pelanggan yang ingin membayar, tidak wajib login,
// tapi jika admin login bisa melihat detailnya juga.
require_once 'koneksi.php';
require_once 'config_midtrans.php';

if (!isset($_GET['id_sewa'])) {
    header("Location: index.php");
    exit;
}

$id_sewa = (int)$_GET['id_sewa'];

// Ambil data transaksi sewa dan detail mobil
$query = "SELECT s.*, m.nama_mobil, m.merk, m.harga_sewa, m.gambar_mobil, m.tahun
          FROM sewa s
          LEFT JOIN mobil m ON s.id_mobil = m.id_mobil
          WHERE s.id_sewa = $1";
$res = pg_query_params($conn, $query, array($id_sewa));
$sewa = pg_fetch_assoc($res);

if (!$sewa) {
    echo "<script>alert('Transaksi tidak ditemukan!'); window.location.href='index.php';</script>";
    exit;
}

$snap_token = $sewa['snap_token'];

// Jika status pembayaran masih Pending dan snap_token kosong, mintalah token baru dari Midtrans
if ($sewa['status_pembayaran'] === 'Pending' && empty($snap_token)) {
    $order_id = 'RENT-' . $sewa['id_sewa'] . '-' . time(); // ID unik untuk Midtrans
    $customer_details = array(
        'nama' => $sewa['nama_peminjam'],
        'nik' => $sewa['nik_peminjam'],
        'no_hp' => $sewa['no_hp']
    );
    
    // Request token dari helper config_midtrans.php
    $token = getSnapToken($order_id, $sewa['total_harga'], $customer_details);
    
    if ($token) {
        // Simpan token ke database agar tidak request ulang terus-menerus
        $update = pg_query_params($conn, "UPDATE sewa SET snap_token = $1 WHERE id_sewa = $2", array($token, $id_sewa));
        if ($update) {
            $snap_token = $token;
        }
    } else {
        $error_midtrans = "Gagal terhubung dengan layanan pembayaran Midtrans. Coba segarkan halaman.";
    }
}

// Konfigurasi gambar mobil
$gambar = !empty($sewa['gambar_mobil']) && file_exists('uploads/' . $sewa['gambar_mobil']) 
    ? 'uploads/' . $sewa['gambar_mobil'] 
    : 'https://images.unsplash.com/photo-1550355291-bbee04a92027?auto=format&fit=crop&q=80&w=600';

// Hitung durasi sewa untuk tampilan invoice
$start = new DateTIme($sewa['tgl_sewa']);
$end = new DateTime($sewa['tgl_kembali']);
$diff = $start->diff($end);
$days = $diff->days;
$days = max(1, $days);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran Rental - AutoRent</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <?php if ($sewa['status_pembayaran'] === 'Pending' && !empty($snap_token)): ?>
        <!-- Load Midtrans Snap JS SDK -->
        <script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="<?= $midtrans_client_key ?>"></script>
    <?php endif; ?>
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
            <?php if (isset($_SESSION['login'])): ?>
                <li><a href="index.php"><i class="ph ph-squares-four"></i> Dashboard</a></li>
                <li><a href="transaksi.php"><i class="ph ph-receipt"></i> Transaksi</a></li>
            <?php endif; ?>
        </ul>
    </nav>

    <div class="container" style="margin-top: 2rem; max-width: 800px;">
        
        <?php if ($sewa['status_pembayaran'] === 'Success'): ?>
            <!-- RECEIPT / INVOICE SUKSES (Premium Design) -->
            <div class="form-container" style="border-color: rgba(16, 185, 129, 0.4); background: linear-gradient(135deg, rgba(16, 185, 129, 0.05) 0%, rgba(15, 22, 41, 0.9) 100%); text-align: center; position: relative; overflow: hidden;">
                <!-- Decorative Top Border -->
                <div style="position: absolute; top: 0; left: 0; right: 0; height: 5px; background: var(--success);"></div>
                
                <div style="width: 70px; height: 70px; background: var(--success-bg); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 1rem auto 1.5rem; border: 1px solid rgba(16,185,129,0.3);">
                    <i class="ph ph-check-circle" style="font-size: 3rem; color: var(--success);"></i>
                </div>
                
                <h1 style="font-size: 1.8rem; font-weight: 800; margin-bottom: 0.5rem; color: #fff;">Pembayaran Berhasil!</h1>
                <p style="color: var(--text-secondary); font-size: 0.95rem; margin-bottom: 2rem;">Terima kasih. Pembayaran rental mobil Anda telah kami terima.</p>
                
                <!-- Invoice Details -->
                <div style="text-align: left; background: rgba(255,255,255,0.02); border: 1px solid var(--border); border-radius: var(--radius-md); padding: 1.5rem; margin-bottom: 2rem;">
                    <div style="display: flex; justify-content: space-between; border-bottom: 1px solid var(--border); padding-bottom: 0.75rem; margin-bottom: 0.75rem;">
                        <span style="color: var(--text-muted);">No. Transaksi</span>
                        <strong style="color: var(--text-primary);">#<?= str_pad($sewa['id_sewa'], 5, '0', STR_PAD_LEFT) ?></strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; border-bottom: 1px solid var(--border); padding-bottom: 0.75rem; margin-bottom: 0.75rem;">
                        <span style="color: var(--text-muted);">Mobil</span>
                        <strong style="color: var(--text-primary);"><?= htmlspecialchars($sewa['nama_mobil']) ?> (<?= htmlspecialchars($sewa['merk']) ?>)</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; border-bottom: 1px solid var(--border); padding-bottom: 0.75rem; margin-bottom: 0.75rem;">
                        <span style="color: var(--text-muted);">Peminjam</span>
                        <strong style="color: var(--text-primary);"><?= htmlspecialchars($sewa['nama_peminjam']) ?></strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; border-bottom: 1px solid var(--border); padding-bottom: 0.75rem; margin-bottom: 0.75rem;">
                        <span style="color: var(--text-muted);">Durasi Sewa</span>
                        <strong style="color: var(--text-primary);"><?= date('d M Y', strtotime($sewa['tgl_sewa'])) ?> - <?= date('d M Y', strtotime($sewa['tgl_kembali'])) ?> (<?= $days ?> hari)</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-top: 1rem; align-items: center;">
                        <span style="color: var(--text-muted); font-size: 1rem;">Total Biaya</span>
                        <strong style="color: var(--brand-primary-light); font-size: 1.6rem;">Rp <?= number_format($sewa['total_harga'], 0, ',', '.') ?></strong>
                    </div>
                </div>

                <div style="display: flex; gap: 1rem; justify-content: center;">
                    <button onclick="window.print()" class="btn btn-outline"><i class="ph ph-printer"></i> Cetak Bukti</button>
                    <?php if (isset($_SESSION['login'])): ?>
                        <a href="index.php" class="btn btn-primary"><i class="ph ph-squares-four"></i> Ke Dashboard</a>
                    <?php endif; ?>
                </div>
            </div>
            
        <?php else: ?>
            <!-- SUMMARY INVOICE & TOMBOL BAYAR -->
            <div class="detail-container" style="grid-template-columns: 1fr; border-radius: var(--radius-lg);">
                <div style="padding: 2rem;">
                    
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 2rem; border-bottom: 1px solid var(--border); padding-bottom: 1rem;">
                        <h2 style="display: flex; align-items: center; gap: 0.5rem; font-size: 1.4rem;">
                            <i class="ph ph-shield-check" style="color: var(--brand-primary-light)"></i> Detail Pembayaran Rental
                        </h2>
                        <span class="badge badge-disewa" style="font-size: 0.85rem; padding: 0.35rem 0.8rem; background: var(--warning-bg); border-color: rgba(245,158,11,0.2); color: #fbbf24;">
                            Menunggu Pembayaran
                        </span>
                    </div>

                    <?php if (isset($error_midtrans)): ?>
                        <div class="alert alert-error" style="margin-bottom: 1.5rem; background: var(--danger-bg); border: 1px solid rgba(244,63,94,0.2); padding: 1rem; border-radius: var(--radius-md); color: #fb7185;">
                            <?= htmlspecialchars($error_midtrans) ?>
                        </div>
                    <?php endif; ?>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 2rem;">
                        <!-- Kolom Detail Mobil -->
                        <div style="background: rgba(255,255,255,0.02); border: 1px solid var(--border); border-radius: var(--radius-md); padding: 1.25rem;">
                            <h3 style="font-size: 1rem; margin-bottom: 1rem; color: var(--text-secondary); display: flex; align-items: center; gap: 0.4rem;">
                                <i class="ph ph-car"></i> Informasi Mobil
                            </h3>
                            <img src="<?= $gambar ?>" alt="<?= htmlspecialchars($sewa['nama_mobil']) ?>" style="width: 100%; height: 120px; object-fit: cover; border-radius: var(--radius-sm); margin-bottom: 1rem;">
                            <h4 style="font-size: 1.1rem; color: #fff;"><?= htmlspecialchars($sewa['nama_mobil']) ?></h4>
                            <p style="color: var(--text-muted); font-size: 0.85rem;"><?= htmlspecialchars($sewa['merk']) ?> &bull; Tahun <?= $sewa['tahun'] ?></p>
                        </div>

                        <!-- Kolom Detail Peminjam & Sewa -->
                        <div style="background: rgba(255,255,255,0.02); border: 1px solid var(--border); border-radius: var(--radius-md); padding: 1.25rem; display: flex; flex-direction: column; justify-content: space-between;">
                            <div>
                                <h3 style="font-size: 1rem; margin-bottom: 1rem; color: var(--text-secondary); display: flex; align-items: center; gap: 0.4rem;">
                                    <i class="ph ph-user"></i> Data Diri &amp; Durasi
                                </h3>
                                <ul style="list-style: none; font-size: 0.9rem; display: flex; flex-direction: column; gap: 0.5rem;">
                                    <li style="display: flex; justify-content: space-between;">
                                        <span style="color: var(--text-muted);">Peminjam:</span>
                                        <span style="color: #fff; font-weight: 500;"><?= htmlspecialchars($sewa['nama_peminjam']) ?></span>
                                    </li>
                                    <li style="display: flex; justify-content: space-between;">
                                        <span style="color: var(--text-muted);">NIK:</span>
                                        <span style="color: #fff; font-weight: 500;"><?= htmlspecialchars($sewa['nik_peminjam']) ?></span>
                                    </li>
                                    <li style="display: flex; justify-content: space-between;">
                                        <span style="color: var(--text-muted);">No HP:</span>
                                        <span style="color: #fff; font-weight: 500;"><?= htmlspecialchars($sewa['no_hp']) ?></span>
                                    </li>
                                    <li style="display: flex; justify-content: space-between; border-top: 1px solid var(--border); padding-top: 0.5rem; margin-top: 0.5rem;">
                                        <span style="color: var(--text-muted);">Dari:</span>
                                        <span style="color: #fff; font-weight: 500;"><?= date('d M Y', strtotime($sewa['tgl_sewa'])) ?></span>
                                    </li>
                                    <li style="display: flex; justify-content: space-between;">
                                        <span style="color: var(--text-muted);">Sampai:</span>
                                        <span style="color: #fff; font-weight: 500;"><?= date('d M Y', strtotime($sewa['tgl_kembali'])) ?></span>
                                    </li>
                                    <li style="display: flex; justify-content: space-between;">
                                        <span style="color: var(--text-muted);">Durasi:</span>
                                        <span style="color: #fff; font-weight: 500;"><?= $days ?> hari</span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Total Tagihan -->
                    <div style="background: rgba(99,102,241,0.05); border: 1px solid rgba(99,102,241,0.15); border-radius: var(--radius-md); padding: 1.5rem; display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                        <div>
                            <span style="color: var(--text-secondary); font-size: 0.9rem;">Total Harga Sewa</span>
                            <h2 style="color: var(--brand-primary-light); font-size: 1.8rem; font-weight: 800; letter-spacing: -0.02em; margin-top: 0.2rem;">
                                Rp <?= number_format($sewa['total_harga'], 0, ',', '.') ?>
                            </h2>
                        </div>
                        <div>
                            <?php if (!empty($snap_token)): ?>
                                <button id="pay-button" class="btn btn-primary" style="padding: 0.8rem 1.8rem; font-size: 1rem; border-radius: var(--radius-md); box-shadow: 0 0 30px rgba(99,102,241,0.4);">
                                    <i class="ph ph-wallet"></i> Bayar Sekarang
                                </button>
                            <?php else: ?>
                                <button class="btn btn-primary" disabled style="opacity: 0.5; cursor: not-allowed;">
                                    Token Tidak Tersedia
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div style="display: flex; gap: 1rem;">
                        <?php if (isset($_SESSION['login'])): ?>
                            <a href="transaksi.php" class="btn btn-outline"><i class="ph ph-arrow-left"></i> Kembali ke Transaksi</a>
                        <?php endif; ?>
                    </div>

                </div>
            </div>

            <?php if (!empty($snap_token)): ?>
                <script type="text/javascript">
                    const payButton = document.getElementById('pay-button');
                    payButton.addEventListener('click', function () {
                        // Jalankan popup Midtrans Snap
                        window.snap.pay('<?= $snap_token ?>', {
                            onSuccess: function(result){
                                /* Callback jika sukses */
                                window.location.href = 'proses_bayar.php?id_sewa=<?= $id_sewa ?>&status=success&order_id=' + result.order_id;
                            },
                            onPending: function(result){
                                /* Callback jika pending */
                                window.location.href = 'proses_bayar.php?id_sewa=<?= $id_sewa ?>&status=pending&order_id=' + result.order_id;
                            },
                            onError: function(result){
                                /* Callback jika error */
                                alert("Pembayaran gagal! Silakan coba lagi.");
                            },
                            onClose: function(){
                                /* Callback jika popup ditutup tanpa bayar */
                                alert('Anda menutup pembayaran sebelum menyelesaikan transaksi.');
                            }
                        });
                    });
                </script>
            <?php endif; ?>

        <?php endif; ?>

    </div>

</body>
</html>
