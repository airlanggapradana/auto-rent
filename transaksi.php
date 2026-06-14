<?php
// transaksi.php
session_start();
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}
require_once 'koneksi.php';

// --- HANDLER AKSI ADMIN ---
if (isset($_GET['action']) && isset($_GET['id_sewa'])) {
    $action = $_GET['action'];
    $id_sewa = (int)$_GET['id_sewa'];

    // Ambil data transaksi sewa untuk validasi
    $query_sewa = pg_query_params($conn, "SELECT id_mobil, status_pembayaran FROM sewa WHERE id_sewa = $1", array($id_sewa));
    $sewa_data = pg_fetch_assoc($query_sewa);

    if ($sewa_data) {
        $id_mobil = $sewa_data['id_mobil'];

        if ($action === 'batal' && $sewa_data['status_pembayaran'] === 'Pending') {
            // BATALKAN TRANSAKSI
            pg_query($conn, "BEGIN");
            
            // Kembalikan status mobil ke Tersedia
            $update_mobil = pg_query_params($conn, "UPDATE mobil SET status_mobil = 'Tersedia' WHERE id_mobil = $1", array($id_mobil));
            
            // Tandai sewa sebagai Batal
            $update_sewa = pg_query_params($conn, "UPDATE sewa SET status_pembayaran = 'Batal' WHERE id_sewa = $1", array($id_sewa));

            if ($update_mobil && $update_sewa) {
                pg_query($conn, "COMMIT");
                echo "<script>alert('Transaksi berhasil dibatalkan!'); window.location.href='transaksi.php';</script>";
                exit;
            } else {
                pg_query($conn, "ROLLBACK");
                echo "<script>alert('Gagal membatalkan transaksi!'); window.location.href='transaksi.php';</script>";
                exit;
            }
        } 
        
        elseif ($action === 'kembali' && $sewa_data['status_pembayaran'] === 'Success') {
            // PENGEMBALIAN MOBIL (TRANSAKSI SELESAI)
            pg_query($conn, "BEGIN");
            
            // Kembalikan status mobil ke Tersedia
            $update_mobil = pg_query_params($conn, "UPDATE mobil SET status_mobil = 'Tersedia' WHERE id_mobil = $1", array($id_mobil));
            
            // Tandai sewa sebagai Selesai
            $update_sewa = pg_query_params($conn, "UPDATE sewa SET status_pembayaran = 'Selesai' WHERE id_sewa = $1", array($id_sewa));

            if ($update_mobil && $update_sewa) {
                pg_query($conn, "COMMIT");
                echo "<script>alert('Mobil telah dikembalikan. Transaksi sewa selesai!'); window.location.href='transaksi.php';</script>";
                exit;
            } else {
                pg_query($conn, "ROLLBACK");
                echo "<script>alert('Gagal memproses pengembalian mobil!'); window.location.href='transaksi.php';</script>";
                exit;
            }
        }
    }
}

// Ambil seluruh data sewa beserta relasi mobil
$query_all = "SELECT s.*, m.nama_mobil, m.merk, m.harga_sewa
              FROM sewa s
              LEFT JOIN mobil m ON s.id_mobil = m.id_mobil
              ORDER BY s.id_sewa DESC";
$res_all = pg_query($conn, $query_all);
$transaksi_list = pg_fetch_all($res_all);
if (!$transaksi_list) $transaksi_list = [];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Transaksi - AutoRent</title>
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
            <li><a href="transaksi.php" class="active"><i class="ph ph-receipt"></i> Transaksi</a></li>
            <li><a href="logout.php" class="btn-logout"><i class="ph ph-sign-out"></i> Logout</a></li>
        </ul>
    </nav>

    <!-- ── Hero ── -->
    <div class="hero" style="padding: 3rem 5% 4rem;">
        <div class="hero-eyebrow">
            <i class="ph ph-receipt"></i>
            Manajemen Transaksi
        </div>
        <h1>Riwayat Penyewaan Mobil</h1>
        <p>Pantau pembayaran, kelola pembatalan, dan catat pengembalian armada mobil secara efisien.</p>
    </div>

    <!-- ── Main Content ── -->
    <div class="container" style="margin-top: -2rem;">
        <div class="form-container" style="max-width: 100%; overflow-x: auto; padding: 1.5rem;">
            
            <div class="controls" style="margin-bottom: 1.25rem;">
                <div class="controls-left">
                    <span class="section-title">Semua Transaksi</span>
                    <span class="section-count"><?= count($transaksi_list) ?> entri</span>
                </div>
            </div>

            <?php if (count($transaksi_list) > 0): ?>
                <table class="table-transaksi" style="width: 100%; border-collapse: collapse; text-align: left;">
                    <thead>
                        <tr style="border-bottom: 1px solid var(--border); color: var(--text-muted); font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.05em;">
                            <th style="padding: 1rem 0.75rem;">ID</th>
                            <th style="padding: 1rem 0.75rem;">Peminjam</th>
                            <th style="padding: 1rem 0.75rem;">Mobil</th>
                            <th style="padding: 1rem 0.75rem;">Masa Sewa</th>
                            <th style="padding: 1rem 0.75rem;">Total Biaya</th>
                            <th style="padding: 1rem 0.75rem; text-align: center;">Status</th>
                            <th style="padding: 1rem 0.75rem; text-align: right;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody style="font-size: 0.92rem;">
                        <?php foreach ($transaksi_list as $trx): ?>
                            <?php 
                            $start = new DateTime($trx['tgl_sewa']);
                            $end = new DateTime($trx['tgl_kembali']);
                            $days = $start->diff($end)->days;
                            $days = max(1, $days);
                            ?>
                            <tr style="border-bottom: 1px solid var(--border); transition: var(--transition);">
                                <td style="padding: 1.1rem 0.75rem; font-weight: 600; color: var(--text-secondary);">
                                    #<?= str_pad($trx['id_sewa'], 5, '0', STR_PAD_LEFT) ?>
                                </td>
                                <td style="padding: 1.1rem 0.75rem;">
                                    <div style="font-weight: 600; color: #fff;"><?= htmlspecialchars($trx['nama_peminjam']) ?></div>
                                    <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 0.15rem;"><?= htmlspecialchars($trx['no_hp']) ?></div>
                                </td>
                                <td style="padding: 1.1rem 0.75rem;">
                                    <?php if ($trx['nama_mobil']): ?>
                                        <div style="font-weight: 500; color: #fff;"><?= htmlspecialchars($trx['nama_mobil']) ?></div>
                                        <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 0.15rem;"><?= htmlspecialchars($trx['merk']) ?></div>
                                    <?php else: ?>
                                        <em style="color: var(--text-muted);">Mobil Dihapus</em>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 1.1rem 0.75rem;">
                                    <div style="color: #fff;"><?= date('d M Y', strtotime($trx['tgl_sewa'])) ?> - <?= date('d M Y', strtotime($trx['tgl_kembali'])) ?></div>
                                    <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 0.15rem;"><?= $days ?> hari</div>
                                </td>
                                <td style="padding: 1.1rem 0.75rem; font-weight: 700; color: var(--brand-primary-light);">
                                    Rp <?= number_format($trx['total_harga'], 0, ',', '.') ?>
                                </td>
                                <td style="padding: 1.1rem 0.75rem; text-align: center;">
                                    <?php if ($trx['status_pembayaran'] === 'Pending'): ?>
                                        <span class="badge" style="background: var(--warning-bg); border: 1px solid rgba(245,158,11,0.2); color: #fbbf24;">Pending</span>
                                    <?php elseif ($trx['status_pembayaran'] === 'Success'): ?>
                                        <span class="badge" style="background: var(--success-bg); border: 1px solid rgba(16,185,129,0.2); color: #34d399;">Success</span>
                                    <?php elseif ($trx['status_pembayaran'] === 'Selesai'): ?>
                                        <span class="badge" style="background: rgba(99,102,241,0.12); border: 1px solid rgba(99,102,241,0.2); color: #818cf8;">Selesai</span>
                                    <?php else: ?>
                                        <span class="badge" style="background: var(--danger-bg); border: 1px solid rgba(244,63,94,0.2); color: #fb7185;">Dibatalkan</span>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 1.1rem 0.75rem; text-align: right; white-space: nowrap;">
                                    <div style="display: flex; gap: 0.5rem; justify-content: flex-end;">
                                        
                                        <!-- Aksi Pending -->
                                        <?php if ($trx['status_pembayaran'] === 'Pending'): ?>
                                            <a href="bayar.php?id_sewa=<?= $trx['id_sewa'] ?>" class="btn btn-warning" style="padding: 0.4rem 0.8rem; font-size: 0.8rem;">
                                                <i class="ph ph-wallet"></i> Bayar
                                            </a>
                                            <a href="transaksi.php?action=batal&id_sewa=<?= $trx['id_sewa'] ?>" 
                                               onclick="return confirm('Apakah Anda yakin ingin membatalkan transaksi penyewaan ini?')" 
                                               class="btn btn-danger" style="padding: 0.4rem 0.8rem; font-size: 0.8rem;">
                                                <i class="ph ph-x-circle"></i> Batal
                                            </a>
                                        <?php endif; ?>

                                        <!-- Aksi Success (Lunas tapi mobil sedang dibawa/jalan) -->
                                        <?php if ($trx['status_pembayaran'] === 'Success'): ?>
                                            <a href="transaksi.php?action=kembali&id_sewa=<?= $trx['id_sewa'] ?>" 
                                               onclick="return confirm('Apakah penyewa telah mengembalikan mobil?')" 
                                               class="btn btn-primary" style="padding: 0.4rem 0.8rem; font-size: 0.8rem; background: var(--success); box-shadow: none;">
                                                <i class="ph ph-arrow-bend-down-left"></i> Kembalikan Mobil
                                            </a>
                                            <a href="bayar.php?id_sewa=<?= $trx['id_sewa'] ?>" class="btn btn-outline" style="padding: 0.4rem 0.8rem; font-size: 0.8rem;">
                                                <i class="ph ph-receipt"></i> Invoice
                                            </a>
                                        <?php endif; ?>

                                        <!-- Aksi Selesai / Batal -->
                                        <?php if ($trx['status_pembayaran'] === 'Selesai' || $trx['status_pembayaran'] === 'Batal'): ?>
                                            <a href="bayar.php?id_sewa=<?= $trx['id_sewa'] ?>" class="btn btn-outline" style="padding: 0.4rem 0.8rem; font-size: 0.8rem; border-color: var(--border);">
                                                <i class="ph ph-eye"></i> Detail Trx
                                            </a>
                                        <?php endif; ?>

                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state" style="padding: 4rem 2rem;">
                    <i class="ph ph-receipt-x"></i>
                    <h2>Belum ada riwayat transaksi</h2>
                    <p>Transaksi sewa yang Anda buat akan muncul di sini.</p>
                </div>
            <?php endif; ?>

        </div>
    </div>

</body>
</html>
