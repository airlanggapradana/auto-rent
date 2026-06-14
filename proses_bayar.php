<?php
// proses_bayar.php
session_start();
require_once 'koneksi.php';

if (!isset($_GET['id_sewa']) || !isset($_GET['status'])) {
    header("Location: index.php");
    exit;
}

$id_sewa = (int)$_GET['id_sewa'];
$status = trim($_GET['status']);

// Ambil data transaksi sewa untuk memastikan validitas
$query = pg_query_params($conn, "SELECT status_pembayaran FROM sewa WHERE id_sewa = $1", array($id_sewa));
$sewa = pg_fetch_assoc($query);

if (!$sewa) {
    echo "<script>alert('Transaksi tidak ditemukan!'); window.location.href='index.php';</script>";
    exit;
}

// Tentukan status baru di database berdasarkan parameter status
$status_pembayaran_baru = 'Pending';
$pesan_alert = '';

if ($status === 'success') {
    $status_pembayaran_baru = 'Success';
    $pesan_alert = 'Pembayaran berhasil diselesaikan!';
} elseif ($status === 'pending') {
    $status_pembayaran_baru = 'Pending';
    $pesan_alert = 'Pembayaran Anda sedang diproses. Silakan selesaikan pembayaran.';
} else {
    $status_pembayaran_baru = 'Pending';
    $pesan_alert = 'Status pembayaran tidak dikenali atau ditunda.';
}

// Update status pembayaran di database
$update = pg_query_params($conn, "UPDATE sewa SET status_pembayaran = $1 WHERE id_sewa = $2", array($status_pembayaran_baru, $id_sewa));

if ($update) {
    echo "<script>alert('" . $pesan_alert . "'); window.location.href='bayar.php?id_sewa=" . $id_sewa . "';</script>";
} else {
    echo "<script>alert('Gagal memperbarui status transaksi: " . pg_last_error($conn) . "'); window.location.href='bayar.php?id_sewa=" . $id_sewa . "';</script>";
}
?>
