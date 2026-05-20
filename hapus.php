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

// Ambil data mobil untuk mengecek gambar yang akan dihapus
$query = pg_query_params($conn, "SELECT gambar_mobil FROM mobil WHERE id_mobil = $1", array($id_mobil));
$mobil = pg_fetch_assoc($query);

if ($mobil) {
    $gambar = $mobil['gambar_mobil'];
    
    // Hapus data dari database
    $delete_query = pg_query_params($conn, "DELETE FROM mobil WHERE id_mobil = $1", array($id_mobil));
    
    if ($delete_query) {
        // Hapus file gambar jika ada dan bukan gambar default
        if ($gambar != 'default.jpg' && file_exists('uploads/' . $gambar)) {
            unlink('uploads/' . $gambar);
        }
        echo "<script>alert('Data mobil berhasil dihapus!'); window.location.href='index.php';</script>";
    } else {
        echo "<script>alert('Gagal menghapus data!'); window.location.href='index.php';</script>";
    }
} else {
    echo "<script>alert('Data tidak ditemukan!'); window.location.href='index.php';</script>";
}
?>
