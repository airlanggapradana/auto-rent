CREATE DATABASE rental_mobil;

-- Setelah membuat database, pastikan untuk terhubung ke database rental_mobil
-- Jika menggunakan pgAdmin atau DBeaver, pilih database rental_mobil sebelum run query di bawah ini

CREATE TABLE mobil (
    id_mobil SERIAL PRIMARY KEY,
    nama_mobil VARCHAR(100) NOT NULL,
    merk VARCHAR(50) NOT NULL,
    tahun INT NOT NULL,
    harga_sewa INT NOT NULL,
    status_mobil VARCHAR(20) NOT NULL DEFAULT 'Tersedia',
    gambar_mobil VARCHAR(255) NOT NULL
);

INSERT INTO mobil (nama_mobil, merk, tahun, harga_sewa, status_mobil, gambar_mobil) VALUES
('Avanza Veloz', 'Toyota', 2022, 350000, 'Tersedia', 'default.jpg'),
('Xpander Cross', 'Mitsubishi', 2023, 400000, 'Tersedia', 'default.jpg'),
('Honda Brio RS', 'Honda', 2021, 250000, 'Disewa', 'default.jpg'),
('Innova Reborn', 'Toyota', 2022, 500000, 'Tersedia', 'default.jpg'),
('Suzuki Ertiga', 'Suzuki', 2020, 300000, 'Tersedia', 'default.jpg'),
('Hyundai Creta', 'Hyundai', 2023, 450000, 'Disewa', 'default.jpg');

CREATE TABLE sewa (
    id_sewa SERIAL PRIMARY KEY,
    id_mobil INT REFERENCES mobil(id_mobil) ON DELETE SET NULL,
    nama_peminjam VARCHAR(100) NOT NULL,
    nik_peminjam VARCHAR(50) NOT NULL,
    no_hp VARCHAR(20) NOT NULL,
    tgl_sewa DATE NOT NULL,
    tgl_kembali DATE NOT NULL,
    total_harga INT NOT NULL,
    status_pembayaran VARCHAR(20) NOT NULL DEFAULT 'Pending',
    snap_token VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

