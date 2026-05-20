// script.js

document.addEventListener('DOMContentLoaded', function() {
    
    // 1. Konfirmasi Hapus
    const deleteButtons = document.querySelectorAll('.btn-delete');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            if(!confirm('Apakah Anda yakin ingin menghapus data mobil ini? Tindakan ini tidak dapat dibatalkan.')) {
                e.preventDefault();
            }
        });
    });

    // 2. Real-time Search dan Filter pada Halaman Index
    const searchInput = document.getElementById('searchInput');
    const filterStatus = document.getElementById('filterStatus');
    const carCards = document.querySelectorAll('.car-card');

    function filterCars() {
        if (!searchInput || !filterStatus) return;

        const searchTerm = searchInput.value.toLowerCase();
        const statusTerm = filterStatus.value.toLowerCase();

        carCards.forEach(card => {
            const title = card.querySelector('.car-title').textContent.toLowerCase();
            const brand = card.querySelector('.car-brand').textContent.toLowerCase();
            const status = card.querySelector('.badge').textContent.toLowerCase();

            const matchSearch = title.includes(searchTerm) || brand.includes(searchTerm);
            const matchStatus = statusTerm === '' || status.includes(statusTerm);

            if (matchSearch && matchStatus) {
                card.style.display = 'flex';
            } else {
                card.style.display = 'none';
            }
        });
    }

    if (searchInput) {
        searchInput.addEventListener('keyup', filterCars);
    }
    if (filterStatus) {
        filterStatus.addEventListener('change', filterCars);
    }

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
