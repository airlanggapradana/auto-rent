// script.js

document.addEventListener('DOMContentLoaded', function() {

    // 1. Konfirmasi Hapus
    const deleteButtons = document.querySelectorAll('.btn-delete');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            if (!confirm('Apakah Anda yakin ingin menghapus data mobil ini?\nTindakan ini tidak dapat dibatalkan.')) {
                e.preventDefault();
            }
        });
    });

    // 2. Real-time Search & Filter (gunakan data attributes)
    const searchInput  = document.getElementById('searchInput');
    const filterStatus = document.getElementById('filterStatus');
    const carCards     = document.querySelectorAll('.car-card');

    function filterCars() {
        if (!searchInput || !filterStatus) return;

        const searchTerm = searchInput.value.toLowerCase().trim();
        const statusTerm = filterStatus.value.toLowerCase();
        let visible = 0;

        carCards.forEach(card => {
            const name   = (card.dataset.name  || '').toLowerCase();
            const merk   = (card.dataset.merk  || '').toLowerCase();
            const status = (card.dataset.status|| '').toLowerCase();

            const matchSearch = name.includes(searchTerm) || merk.includes(searchTerm);
            const matchStatus = statusTerm === '' || status.includes(statusTerm);

            if (matchSearch && matchStatus) {
                card.style.display = 'flex';
                card.style.opacity = '1';
                visible++;
            } else {
                card.style.display = 'none';
                card.style.opacity = '0';
            }
        });

        // Tampilkan pesan kosong jika tidak ada hasil
        let noResult = document.getElementById('no-result-msg');
        if (visible === 0 && carCards.length > 0) {
            if (!noResult) {
                noResult = document.createElement('div');
                noResult.id = 'no-result-msg';
                noResult.className = 'empty-state';
                noResult.style.gridColumn = '1 / -1';
                noResult.innerHTML = '<i class="ph ph-magnifying-glass"></i><h2>Tidak ada hasil</h2><p>Coba ubah kata kunci atau filter status.</p>';
                document.getElementById('carsGrid').appendChild(noResult);
            }
        } else if (noResult) {
            noResult.remove();
        }
    }

    if (searchInput)  searchInput.addEventListener('input', filterCars);
    if (filterStatus) filterStatus.addEventListener('change', filterCars);

    // 3. Image Preview pada Form Tambah/Edit
    const imageInput = document.getElementById('gambar_mobil');
    const imagePreview = document.getElementById('preview');
    const previewText = document.getElementById('preview-text');

    if (imageInput) {
        imageInput.addEventListener('change', function() {
            const file = this.files[0];
            
            if (file) {
                // Validasi tipe file
                const validTypes = ['image/jpeg', 'image/jpg', 'image/png'];
                if (!validTypes.includes(file.type)) {
                    alert('Hanya file JPG, JPEG, dan PNG yang diizinkan!');
                    this.value = ''; // Clear input
                    if (imagePreview) {
                        imagePreview.style.display = 'none';
                        previewText.style.display = 'block';
                    }
                    return;
                }

                // Validasi ukuran file (Max 2MB)
                if (file.size > 2 * 1024 * 1024) {
                    alert('Ukuran file maksimal 2MB!');
                    this.value = '';
                    if (imagePreview) {
                        imagePreview.style.display = 'none';
                        previewText.style.display = 'block';
                    }
                    return;
                }

                const reader = new FileReader();
                
                reader.addEventListener('load', function() {
                    imagePreview.setAttribute('src', this.result);
                    imagePreview.style.display = 'block';
                    if(previewText) previewText.style.display = 'none';
                });
                
                reader.readAsDataURL(file);
            } else {
                imagePreview.style.display = 'none';
                imagePreview.setAttribute('src', '');
                if(previewText) previewText.style.display = 'block';
            }
        });
    }

    // 4. Form Validation Dasar
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function(e) {
            const harga = document.getElementById('harga_sewa');
            const tahun = document.getElementById('tahun');

            if (harga && harga.value <= 0) {
                alert('Harga sewa harus lebih dari 0!');
                e.preventDefault();
            }

            if (tahun && (tahun.value < 1900 || tahun.value > new Date().getFullYear() + 1)) {
                alert('Tahun mobil tidak valid!');
                e.preventDefault();
            }
        });
    }
});
