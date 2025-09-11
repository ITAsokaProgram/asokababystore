<?php
require_once __DIR__ . '/../../component/menu_handler.php';

$menuHandler = new MenuHandler('upload_banner');

if (!$menuHandler->initialize()) {
    exit();
}

// If we reach here, user has access to the menu
$user_id = $menuHandler->getUserId();
$logger = $menuHandler->getLogger();
$token = $menuHandler->getToken();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Banner Promo</title>

    <!-- font awesome cdn link  -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/all.min.css">

    <!-- Penjelasan: Link ke CSS eksternal dengan cache busting query string -->
    <link rel="stylesheet" href="../../style/header.css">
    <link rel="stylesheet" href="../../style/sidebar.css">
    <link rel="stylesheet" href="../../style/animation-fade-in.css">
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="../../style/default-font.css">
    <!-- <link rel="stylesheet" href="../../style/output.css"> -->
    <link rel="stylesheet" href="../../output2.css">
    <!-- Setting logo pada tab di website Anda / Favicon -->
    <link rel="icon" type="image/png" href="../../../public/images/logo1.png">

    <style>
        .btn.active {
            background-color: transparent;
            color: #ec4899;
            outline: 2px solid #ec4899;
            outline-offset: 1px;
        }

        /* Enhanced animations */
        .upload-area-hover {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .upload-area-hover:hover {
            transform: translateY(-2px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }

        .card-hover {
            transition: all 0.3s ease;
        }

        .card-hover:hover {
            transform: translateY(-4px);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }


        @keyframes bounce {

            0%,
            20%,
            50%,
            80%,
            100% {
                transform: translateY(0);
            }

            40% {
                transform: translateY(-10px);
            }

            60% {
                transform: translateY(-5px);
            }
        }

        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .glass-effect {
            background: rgba(255, 255, 255, 0.25);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.18);
        }
    </style>
</head>

<body class="bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50 min-h-screen flex">
    <?php include '../../component/navigation_report.php'; ?>
    <?php include '../../component/sidebar_report.php'; ?>

    <main id="main-content" class="flex-1 p-6 lg:p-8 transition-all duration-300 ml-64 mt-16">
        <div class="max-w-7xl mx-auto space-y-8">
            <!-- Header Section -->
            <div class="text-center mb-8">
                <h1
                    class="text-4xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent mb-3">
                    <i class="fa-solid fa-image mr-2"></i>Banner Management
                </h1>
                <p class="text-gray-600 text-lg font-medium">Upload dan kelola banner promo dengan mudah</p>
            </div>

            <!-- Upload Section -->
            <div class="glass-effect rounded-3xl shadow-2xl p-8 animate-fade-in-up">
                <div class="flex items-center gap-3 mb-6">
                    <div
                        class="w-12 h-12 bg-gradient-to-r from-blue-500 to-purple-600 rounded-xl flex items-center justify-center">
                        <i class="fas fa-cloud-upload-alt text-white text-xl"></i>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold text-gray-800">Upload Banner Promo</h2>
                        <p class="text-gray-600">Pilih gambar dan atur jadwal penayangan</p>
                    </div>
                </div>

                <!-- Enhanced Upload Form -->
                <form id="uploadForm" class="space-y-6">
                    <!-- Drag & Drop Area -->
                    <div id="drop-area"
                        class="relative flex flex-col items-center justify-center border-3 border-dashed border-gray-300 rounded-2xl p-12 bg-gradient-to-br from-white to-gray-50 upload-area-hover cursor-pointer group">
                        <input type="file" id="promoImage" name="promoImage[]" accept="image/*" multiple
                            class="hidden" />

                        <!-- Upload Icon with Animation -->
                        <div
                            class="w-20 h-20 bg-gradient-to-r from-blue-500 to-purple-600 rounded-full flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
                            <i class="fas fa-cloud-arrow-up text-white text-3xl"></i>
                        </div>

                        <!-- Upload Text -->
                        <div class="text-center space-y-2">
                            <h3 class="text-xl font-semibold text-gray-800">Drag & Drop Gambar</h3>
                            <p class="text-gray-500">atau <span
                                    class="text-blue-600 underline cursor-pointer font-semibold hover:text-blue-700 transition-colors"
                                    id="selectFile">pilih file</span> dari komputer Anda</p>
                            <p class="text-sm text-gray-400">Mendukung format: JPG, PNG, GIF (Max 5MB per file)</p>
                        </div>

                        <!-- Preview Grid -->
                        <div id="preview-list" class="w-full grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 mt-6">
                        </div>
                    </div>

                    <!-- Date Selection -->
                    <div class="grid md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label for="mainPromoName"
                                class="block text-sm font-semibold text-gray-700 flex items-center gap-2">
                                <i class="fas fa-tag text-blue-500"></i>
                                Nama Promo (Default)
                            </label>
                            <input type="text" name="mainPromoName" id="mainPromoName"
                                class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 bg-white"
                                placeholder="Contoh: Promo Ramadhan" />
                            <p class="text-xs text-gray-500">Nama promo yang berlaku bila tidak diisi per gambar</p>
                        </div>

                        <div class="space-y-2">
                            <label for="mainPromoDate"
                                class="block text-sm font-semibold text-gray-700 flex items-center gap-2">
                                <i class="fas fa-calendar-alt text-blue-500"></i>
                                Tanggal Tayang Utama
                            </label>
                            <input type="date" name="mainPromoDate" id="mainPromoDate"
                                class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 bg-white" />
                            <p class="text-xs text-gray-500">Default untuk semua gambar jika tidak diisi individual</p>
                        </div>

                        <div class="space-y-2">
                            <label for="mainPromoDateEnd"
                                class="block text-sm font-semibold text-gray-700 flex items-center gap-2">
                                <i class="fas fa-calendar-check text-green-500"></i>
                                Tanggal Selesai Penayangan
                            </label>
                            <input type="date" name="mainPromoDateEnd" id="mainPromoDateEnd"
                                class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 bg-white" />
                            <p class="text-xs text-gray-500">Default untuk semua gambar jika tidak diisi individual</p>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex justify-center pt-4">
                        <button type="submit"
                            class="gradient-bg hover:from-blue-600 hover:to-purple-700 text-white px-8 py-4 rounded-2xl font-bold shadow-lg transition-all duration-300 flex items-center gap-3 transform hover:scale-105 hover:shadow-xl">
                            <i class="fas fa-upload text-xl"></i>
                            Upload Banner Promo
                        </button>
                    </div>
                </form>
            </div>

            <!-- Divider -->
            <div class="relative">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-gray-300"></div>
                </div>
                <div class="relative flex justify-center text-sm">
                    <span class="px-4 bg-gradient-to-br from-blue-50 to-purple-50 text-gray-500 font-semibold">
                        <i class="fas fa-images mr-2"></i>Daftar Banner
                    </span>
                </div>
            </div>

            <!-- Gallery Section -->
            <div class="glass-effect rounded-3xl shadow-2xl p-8 animate-fade-in-up">
                <!-- Header with Actions -->
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between mb-8 gap-4">
                    <div class="flex items-center gap-3">
                        <div
                            class="w-10 h-10 bg-gradient-to-r from-green-500 to-teal-600 rounded-xl flex items-center justify-center">
                            <i class="fas fa-images text-white"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-800">Daftar Banner Promo</h3>
                            <p class="text-gray-600 text-sm">Kelola semua banner yang telah diupload</p>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-3">
                        <button id="cleanExpiredBtn"
                            class="bg-gradient-to-r from-red-500 to-pink-600 hover:from-red-600 hover:to-pink-700 text-white px-6 py-3 rounded-xl shadow-lg transition-all duration-300 flex items-center gap-2 font-semibold transform hover:scale-105">
                            <i class="fas fa-broom"></i>
                            Bersihkan Expired
                        </button>
                        <button id="cleanOrphanedBtn"
                            class="bg-gradient-to-r from-orange-500 to-red-500 hover:from-orange-600 hover:to-red-600 text-white px-6 py-3 rounded-xl shadow-lg transition-all duration-300 flex items-center gap-2 font-semibold transform hover:scale-105">
                            <i class="fas fa-trash-alt"></i>
                            Bersihkan Orphaned
                        </button>
                    </div>
                </div>

                <!-- Loading Skeleton -->
                <div id="gallery-loading"
                    class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-6 mb-6">
                    <div class="bg-gray-200 rounded-2xl h-48 animate-pulse"></div>
                    <div class="bg-gray-200 rounded-2xl h-48 animate-pulse"></div>
                    <div class="bg-gray-200 rounded-2xl h-48 animate-pulse"></div>
                    <div class="bg-gray-200 rounded-2xl h-48 animate-pulse"></div>
                </div>

                <!-- Gallery Grid -->
                <div id="promoGallery" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-6">
                </div>
            </div>
        </div>
    </main>
    <!-- Image Preview Modal -->
    <div id="imagePreviewModal"
        class="hidden fixed inset-0 z-50 bg-black bg-opacity-80 flex items-center justify-center">
        <div class="relative max-w-4xl w-full px-4">
            <!-- Close button -->
            <button id="closeImagePreview"
                class="absolute top-4 right-4 text-white text-2xl font-bold hover:text-gray-300">
                &times;
            </button>

            <!-- Image -->
            <img id="imagePreviewImg" src="" alt="" class="mx-auto max-h-[80vh] rounded-lg shadow-lg" />

            <!-- Caption -->
            <p id="imagePreviewCaption" class="text-center text-white mt-4 text-sm"></p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="../../js/middleware_auth.js"></script>
    <script src="../../js/ui/navbar_toogle.js" type="module"></script>
    <script>
        // Enhanced Drag & Drop Upload Multiple
        const dropArea = document.getElementById('drop-area');
        const fileInput = document.getElementById('promoImage');
        const selectFile = document.getElementById('selectFile');
        const previewList = document.getElementById('preview-list');
        const mainPromoDate = document.getElementById('mainPromoDate');
        let allFiles = [];
        selectFile.addEventListener('click', (e) => {
            e.stopPropagation();
            fileInput.click();
        });

        dropArea.addEventListener('click', (e) => {
            if (
                e.target.tagName === 'INPUT' ||
                e.target.tagName === 'BUTTON' ||
                e.target.tagName === 'LABEL' ||
                e.target.closest('input')
            ) {
                return;
            }
            fileInput.click();
        });

        dropArea.addEventListener('dragover', e => {
            e.preventDefault();
            dropArea.classList.add('border-blue-400', 'bg-blue-50');
        });

        dropArea.addEventListener('dragleave', e => {
            e.preventDefault();
            dropArea.classList.remove('border-blue-400', 'bg-blue-50');
        });

        let droppedFiles = null;
        dropArea.addEventListener('drop', e => {
            e.preventDefault();
            dropArea.classList.remove('border-blue-400', 'bg-blue-50');
            if (e.dataTransfer.files.length) {
                Array.from(e.dataTransfer.files).forEach(f => allFiles.push(f)); // simpan semua
                showPreviewList(e.dataTransfer.files);
            }
        });

        fileInput.addEventListener('change', e => {
            if (fileInput.files.length) {
                Array.from(fileInput.files).forEach(f => allFiles.push(f)); // simpan semua
                showPreviewList(fileInput.files);
            }
        });

        function showPreviewList(files) {
            Array.from(files).forEach((file, idx) => {
                const reader = new FileReader();
                reader.onload = e => {
                    const wrapper = document.createElement('div');
                    wrapper.className = 'bg-white rounded-2xl p-4 shadow-lg card-hover';
                    wrapper.innerHTML = `
                        <div class="relative mb-3">
                            <img src="${e.target.result}" class="w-full h-32 object-cover rounded-xl shadow-md" alt="Preview">
                            <div class="absolute top-2 right-2 bg-black bg-opacity-50 text-white text-xs px-2 py-1 rounded-full">
                                ${(file.size / 1024 / 1024).toFixed(1)}MB
                            </div>
                        </div>
                        <div class="space-y-2">
                            <input type="text" name="promoName[]" 
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200" 
                                placeholder="Nama Promo (opsional, kosong = default)" />
                            <input type="date" name="promoDateStart[]" 
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200" 
                                placeholder="Tanggal Tayang" required>
                            <input type="date" name="promoDateEnd[]" 
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200" 
                                placeholder="Tanggal Selesai" required>
                            <p class="text-xs text-gray-500 text-center">Atur jadwal untuk gambar ini</p>
                        </div>
                    `;
                    previewList.appendChild(wrapper);
                };
                reader.readAsDataURL(file);
            });
        }

        // Enhanced Upload Form
        document.getElementById('uploadForm').addEventListener('submit', function (e) {
            e.preventDefault();
            const formData = new FormData();
            const files = allFiles;

            if (!files.length) {
                Swal.fire({
                    title: 'Pilih Gambar',
                    text: 'Silakan pilih gambar terlebih dahulu!',
                    icon: 'warning',
                    confirmButtonColor: '#3B82F6'
                });
                return;
            }

            const nameInputs = previewList.querySelectorAll('input[name="promoName[]"]');
            const dateStartInputs = previewList.querySelectorAll('input[name="promoDateStart[]"]');
            const dateEndInputs = previewList.querySelectorAll('input[name="promoDateEnd[]"]');
            const mainPromoDateEnd = document.getElementById('mainPromoDateEnd');
            const mainPromoName = document.getElementById('mainPromoName');

            for (let i = 0; i < files.length; i++) {
                const startVal = dateStartInputs[i].value || mainPromoDate.value;
                const endVal = dateEndInputs[i].value || mainPromoDateEnd.value;

                if (!startVal || !endVal) {
                    Swal.fire({
                        title: 'Tanggal Diperlukan',
                        text: 'Isi tanggal tayang dan tanggal selesai penayangan untuk semua gambar, atau isi default utama!',
                        icon: 'warning',
                        confirmButtonColor: '#3B82F6'
                    });
                    return;
                }

                formData.append('promoImage[]', files[i]);
                formData.append('promoDateStart[]', startVal);
                formData.append('promoDateEnd[]', endVal);
                // per-file promo name (optional). If empty, server can fallback to mainPromoName
                const perName = (nameInputs[i] && nameInputs[i].value) ? nameInputs[i].value.trim() : '';
                formData.append('promoName[]', perName);
            }

            formData.append('mainPromoName', mainPromoName.value || '');
            formData.append('mainPromoDate', mainPromoDate.value);
            formData.append('mainPromoDateEnd', mainPromoDateEnd.value);

            Swal.fire({
                title: 'Mengupload...',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading(),
                background: '#f8fafc',
                backdrop: 'rgba(0,0,0,0.4)'
            });

            fetch('/src/api/image/put_image.php', { method: 'POST', body: formData })
                .then(res => res.json())
                .then(result => {
                    if (result.success) {
                        Swal.fire({
                            title: 'Berhasil!',
                            text: 'Banner berhasil diupload.',
                            icon: 'success',
                            confirmButtonColor: '#10B981'
                        }).then(() => location.reload());
                    } else {
                        Swal.fire({
                            title: 'Gagal',
                            text: result.error || 'Gagal upload',
                            icon: 'error',
                            confirmButtonColor: '#EF4444'
                        });
                    }
                }).catch(() => Swal.fire({
                    title: 'Gagal',
                    text: 'Terjadi kesalahan',
                    icon: 'error',
                    confirmButtonColor: '#EF4444'
                }));
        });

        // Enhanced Gallery List
        function loadGallery() {
            fetch('/public/slider.json?ts=' + Date.now())
                .then(res => res.json())
                .then(data => {
                    document.getElementById('gallery-loading').style.display = 'none';
                    const gallery = document.getElementById('promoGallery');
                    gallery.innerHTML = '';

                    if (!data.length) {
                        gallery.innerHTML = `
                            <div class="col-span-full text-center py-16">
                                <div class="w-24 h-24 bg-gray-200 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <i class="fas fa-images text-gray-400 text-3xl"></i>
                                </div>
                                <h3 class="text-xl font-semibold text-gray-600 mb-2">Belum ada banner promo</h3>
                                <p class="text-gray-500">Upload banner pertama Anda di atas</p>
                            </div>
                        `;
                        return;
                    }

                    data.forEach((item, index) => {
                        const div = document.createElement('div');
                        div.className = 'bg-white rounded-2xl shadow-lg overflow-hidden card-hover group';
                        const domain = window.location.origin;

                        div.innerHTML = `
                            <div class="relative overflow-hidden">
                                <img src="${domain + item.path}" alt="${item.filename}" 
                                    data-full-src="${domain + item.path}" data-caption="${item.promo_name || ''}"
                                    class="promo-img cursor-pointer w-full h-48 object-cover transition-transform duration-300 group-hover:scale-110 cursor-pointer">
                                <button data-index="${index}" 
                                    class="deleteBtn absolute top-3 right-3 bg-red-500 hover:bg-red-600 text-white p-2 rounded-full shadow-lg transition-all duration-300 opacity-0 group-hover:opacity-100 transform scale-90 group-hover:scale-100 z-10" 
                                    title="Hapus Banner">
                                    <i class="fas fa-trash text-sm"></i>
                                </button>
                            </div>
                            <div class="p-4 space-y-2">
                             <div class="flex items-center justify-between text-sm">
                                    <span class="font-semibold text-gray-800 truncate block" title=${item.promo_name}>${item.promo_name || '-'}</span>
                                    </div>
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-gray-500">Mulai:</span>
                                    <span class="font-semibold text-gray-800">${item.tanggal_mulai || '-'}</span>
                                </div>
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-gray-500">Selesai:</span>
                                    <span class="font-semibold text-gray-800">${item.tanggal_selesai || '-'}</span>
                                </div>
                                <div class="pt-2 border-t border-gray-100">
                                    <p class="text-xs text-gray-400 truncate">${item.filename}</p>
                                </div>
                            </div>
                        `;
                        gallery.appendChild(div);
                    });

                    // Image preview handlers: clicking any .promo-img opens a fullscreen modal
                    function openImagePreview(url, caption) {
                        const modal = document.getElementById('imagePreviewModal');
                        const img = document.getElementById('imagePreviewImg');
                        const cap = document.getElementById('imagePreviewCaption');
                        if (!modal || !img) return;
                        img.src = url;
                        img.alt = caption || '';
                        cap.textContent = caption || '';
                        modal.classList.remove('hidden');
                        document.body.classList.add('overflow-hidden');
                    }

                    function closeImagePreview() {
                        const modal = document.getElementById('imagePreviewModal');
                        const img = document.getElementById('imagePreviewImg');
                        if (!modal || !img) return;
                        modal.classList.add('hidden');
                        img.src = '';
                        document.body.classList.remove('overflow-hidden');
                    }

                    document.querySelectorAll('.promo-img').forEach(img => {
                        img.addEventListener('click', () => openImagePreview(img.dataset.fullSrc, img.dataset.caption || img.alt));
                    });

                    // Close modal handlers
                    const modalEl = document.getElementById('imagePreviewModal');
                    if (modalEl) {
                        modalEl.addEventListener('click', (ev) => {
                            if (ev.target === modalEl) closeImagePreview();
                        });
                    }
                    const closeBtn = document.getElementById('closeImagePreview');
                    if (closeBtn) closeBtn.addEventListener('click', closeImagePreview);
                    document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeImagePreview(); });

                    // Enhanced Delete Buttons
                    document.querySelectorAll('.deleteBtn').forEach(button => {
                        button.addEventListener('click', function () {
                            const idx = this.dataset.index;
                            const filename = data[idx].filename;

                            Swal.fire({
                                title: 'Hapus Banner?',
                                text: `"${filename}" akan dihapus permanen`,
                                icon: 'warning',
                                showCancelButton: true,
                                confirmButtonText: 'Ya, Hapus',
                                cancelButtonText: 'Batal',
                                confirmButtonColor: '#EF4444',
                                cancelButtonColor: '#6B7280'
                            }).then(result => {
                                if (result.isConfirmed) {
                                    fetch('/src/api/image/delete_image.php', {
                                        method: 'POST',
                                        headers: { 'Content-Type': 'application/json' },
                                        body: JSON.stringify({ filename })
                                    }).then(res => res.json()).then(result => {
                                        if (result.success) {
                                            Swal.fire({
                                                title: 'Berhasil',
                                                text: 'Banner dihapus',
                                                icon: 'success',
                                                confirmButtonColor: '#10B981'
                                            }).then(() => location.reload());
                                        } else {
                                            Swal.fire({
                                                title: 'Gagal',
                                                text: result.error || 'Gagal menghapus',
                                                icon: 'error',
                                                confirmButtonColor: '#EF4444'
                                            });
                                        }
                                    });
                                }
                            });
                        });
                    });
                });
        }

        document.addEventListener('DOMContentLoaded', loadGallery);

        // Enhanced Clean Expired Button
        document.getElementById('cleanExpiredBtn').addEventListener('click', function () {
            Swal.fire({
                title: 'Membersihkan...',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading(),
                background: '#f8fafc'
            });

            fetch('/src/api/image/clean_expired_banner.php')
                .then(res => res.json())
                .then(result => {
                    if (result.success) {
                        Swal.fire({
                            title: 'Selesai',
                            text: `Banner expired dihapus: ${result.deleted}`,
                            icon: 'success',
                            confirmButtonColor: '#10B981'
                        }).then(() => location.reload());
                    } else {
                        Swal.fire({
                            title: 'Gagal',
                            text: result.error || 'Gagal membersihkan',
                            icon: 'error',
                            confirmButtonColor: '#EF4444'
                        });
                    }
                });
        });

        // Enhanced Clean Orphaned Button
        document.getElementById('cleanOrphanedBtn').addEventListener('click', function () {
            Swal.fire({
                title: 'Bersihkan File Orphaned?',
                text: 'File yang ada di folder tapi tidak ada di JSON akan dihapus',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, Bersihkan',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#F59E0B',
                cancelButtonColor: '#6B7280'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Membersihkan...',
                        allowOutsideClick: false,
                        didOpen: () => Swal.showLoading(),
                        background: '#f8fafc'
                    });

                    fetch('/src/api/image/clean_orphaned_files.php')
                        .then(res => res.json())
                        .then(result => {
                            if (result.success) {
                                Swal.fire({
                                    title: 'Selesai',
                                    text: `File orphaned dihapus: ${result.deleted_files}`,
                                    icon: 'success',
                                    confirmButtonColor: '#10B981'
                                }).then(() => location.reload());
                            } else {
                                Swal.fire({
                                    title: 'Gagal',
                                    text: result.error || 'Gagal membersihkan',
                                    icon: 'error',
                                    confirmButtonColor: '#EF4444'
                                });
                            }
                        });
                }
            });
        });
    </script>

</body>

</html>