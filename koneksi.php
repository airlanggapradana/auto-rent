<?php
// koneksi.php
$host = "localhost";
$port = "5432";
$dbname = "rental_mobil";
$user = "postgres";
$password = "admin123"; // Sesuaikan dengan password PostgreSQL di Laragon Anda

// Membuat koneksi menggunakan pg_connect
$conn = pg_connect("host=$host port=$port dbname=$dbname user=$user password=$password");

if (!$conn) {
    die("Koneksi ke PostgreSQL gagal: " . pg_last_error());
}
?>
