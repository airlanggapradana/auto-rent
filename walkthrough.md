# Walkthrough - Implementasi Fitur Penyewaan Mobil & Midtrans Snap

Fitur penyewaan mobil lengkap dengan alur integrasi pembayaran Midtrans Snap dan manajemen transaksi telah berhasil diimplementasikan.

## Perubahan yang Dilakukan

1. **Database Schema & Migrasi**:
   - Memodifikasi [database.sql](file:///c:/laragon/www/Tugas_Akhir/rental-mobil/database.sql) untuk menyertakan struktur tabel `sewa`.
   - Membuat tabel `sewa` pada database PostgreSQL `rental_mobil` lokal via PHP CLI.

2. **Konfigurasi Midtrans**:
   - Membuat [config_midtrans.php](file:///c:/laragon/www/Tugas_Akhir/rental-mobil/config_midtrans.php) yang menyimpan Server Key dan Client Key Sandbox Midtrans, serta fungsi cURL untuk meng-generate Snap Token.

3. **Form Penyewaan Mobil**:
   - Membuat [sewa.php](file:///c:/laragon/www/Tugas_Akhir/rental-mobil/sewa.php) untuk menginput data diri penyewa, tanggal sewa, dan tanggal pengembalian, lengkap dengan validasi ketersediaan mobil (status harus `'Tersedia'`) dan kalkulator estimasi biaya sewa real-time di frontend.

4. **Halaman Pembayaran**:
   - Membuat [bayar.php](file:///c:/laragon/www/Tugas_Akhir/rental-mobil/bayar.php) untuk memicu popup Midtrans Snap JS SDK bagi transaksi pending, serta merender tanda terima/invoice premium yang siap dicetak ketika status transaksi sudah sukses (`'Success'`).

5. **Callback Pemrosesan Pembayaran**:
   - Membuat [proses_bayar.php](file:///c:/laragon/www/Tugas_Akhir/rental-mobil/proses_bayar.php) yang menerima callback sukses/pending dari Snap JS dan memperbarui status pembayaran di database secara aman.

6. **Manajemen Transaksi**:
   - Membuat [transaksi.php](file:///c:/laragon/www/Tugas_Akhir/rental-mobil/transaksi.php) untuk melihat seluruh daftar riwayat transaksi, melakukan pembayaran untuk transaksi pending, membatalkan transaksi pending, serta mengembalikan mobil (menyelesaikan siklus sewa dan mengubah status mobil kembali ke `'Tersedia'`).

7. **Dashboard & Navigasi**:
   - Memodifikasi [index.php](file:///c:/laragon/www/Tugas_Akhir/rental-mobil/index.php) untuk mengintegrasikan link **"Transaksi"** ke navbar, serta menampilkan tombol **"Sewa"** dinamis hanya pada mobil yang berstatus `'Tersedia'`.

8. **Styling & Tampilan Premium**:
   - Memodifikasi [style.css](file:///c:/laragon/www/Tugas_Akhir/rental-mobil/style.css) untuk menyelaraskan tampilan tabel transaksi, alert, invoice, serta menambahkan stylesheet print `@media print` agar invoice tercetak dengan bersih.

---

## Petunjuk Pengujian Manual

Karena kendala sistem browser otomatis (CDP port resolution error), Anda dapat melakukan pengujian langsung di browser lokal Anda dengan langkah-langkah berikut:

1. **Akses Dashboard**:
   - Jalankan Laragon dan buka link dashboard: `http://localhost/Tugas_Akhir/rental-mobil/` atau `http://rental-mobil.test/`.
   - Log in dengan username `admin` dan password `admin`.

2. **Mulai Penyewaan**:
   - Di dashboard utama, cari mobil yang berstatus **Tersedia** (misal Avanza Veloz).
   - Klik tombol **Sewa** pada card mobil tersebut.
   - Isi form data peminjam dan pilih tanggal sewa (misal hari ini) serta tanggal pengembalian (misal besok).
   - Perhatikan kalkulasi harga otomatis di bagian bawah form, lalu klik **Buat Penyewaan**.

3. **Simulasi Pembayaran**:
   - Anda akan diarahkan ke halaman invoice `bayar.php`.
   - Klik **Bayar Sekarang** untuk memunculkan popup pembayaran Midtrans Snap.
   - Pilih metode pembayaran **Kartu Kredit/Debit**.
   - Masukkan nomor kartu uji coba sandbox: `4811 1111 1111 1111`, Expiry: `12/28` (atau bulan/tahun masa depan), CVV: `123`.
   - Klik bayar, masukkan OTP simulasi `112233` saat diminta.
   - Setelah sukses, tunggu hingga popup tertutup secara otomatis dan halaman diarahkan ke invoice sukses yang menampilkan tulisan **Pembayaran Berhasil!**.
   - Coba klik tombol **Cetak Bukti** untuk mengetes cetak print invoice.

4. **Pengembalian Mobil**:
   - Masuk ke menu **Transaksi** melalui navbar.
   - Cari transaksi Budi Santoso yang baru dibuat (statusnya sekarang harus **Success**).
   - Klik tombol **Kembalikan Mobil** pada kolom aksi.
   - Terima konfirmasi popup browser.
   - Status transaksi akan berubah menjadi **Selesai**, dan mobil tersebut kini sudah bisa disewa kembali (kembali berstatus **Tersedia** di dashboard utama).
