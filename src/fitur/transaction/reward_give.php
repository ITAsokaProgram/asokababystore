<?php
require_once __DIR__ . '/../../component/menu_handler.php';

$menuHandler = new MenuHandler('reward_give');

if (!$menuHandler->initialize()) {
    exit();
}

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
    <title>Kelola Hadiah</title>

    <!-- font awesome cdn link  -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/all.min.css">

    <!-- Penjelasan: Link ke CSS eksternal dengan cache busting query string -->
    <link rel="stylesheet" href="../../style/header.css">
    <link rel="stylesheet" href="../../style/sidebar.css">
    <link rel="stylesheet" href="../../style/animation-fade-in.css">
    <link rel="stylesheet" href="../../../css/cabang_selective.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap"
        rel="stylesheet">
    <!-- Setting logo pada tab di website Anda / Favicon -->
    <link rel="icon" type="image/png" href="../../../public/images/logo1.png">
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="../../style/default-font.css">
    <link rel="stylesheet" href="../../output2.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
    <!-- CSS Tippy -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tippy.js@6/dist/tippy.css" />

    <!-- Popper.js UMD (minified) -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2/dist/umd/popper.min.js"></script>

    <!-- Tippy.js UMD (minified) -->
    <script src="https://cdn.jsdelivr.net/npm/tippy.js@6/dist/tippy-bundle.umd.min.js"></script>

    <!-- Add SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.min.css">
    <!-- Add SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>

    <style>
        :root {
            --primary-color: #4f46e5;
            --primary-light: #6366f1;
            --primary-dark: #3730a3;
            --secondary-color: #f8fafc;
            --accent-color: #0ea5e9;
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --danger-color: #ef4444;
            --gray-50: #f9fafb;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-300: #d1d5db;
            --gray-500: #6b7280;
            --gray-600: #4b5563;
            --gray-700: #374151;
            --gray-800: #1f2937;
            --gray-900: #111827;
        }

        .btn.active {
            background-color: transparent;
            color: var(--primary-color);
            outline: 2px solid var(--primary-color);
            outline-offset: 1px;
        }

        .reward-card {
            transition: all 0.3s ease;
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            border: 1px solid var(--gray-200);
        }

        .reward-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(79, 70, 229, 0.1);
            border-color: var(--primary-color);
        }

        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .status-active {
            background-color: #dcfce7;
            color: #166534;
        }

        .status-inactive {
            background-color: #fef2f2;
            color: #dc2626;
        }

        .status-expired {
            background-color: #fef3c7;
            color: #d97706;
        }

        .reward-input {
            transition: all 0.3s ease;
            background-color: #fff;
            border: 1.5px solid var(--gray-300);
        }

        .reward-input:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
            transform: scale(1.01);
        }

        .floating-label {
            transition: all 0.3s ease;
            background: #fff;
            z-index: 10;
            padding: 0 0.25rem;
            position: absolute;
            left: 1rem;
            top: 1rem;
            pointer-events: none;
        }

        .reward-input:focus+.floating-label,
        .reward-input:not(:placeholder-shown)+.floating-label {
            transform: translateY(-1.5rem) scale(0.85);
            color: var(--primary-color);
            background: #fff;
            z-index: 10;
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .primary-gradient {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-light) 100%);
        }

        .secondary-gradient {
            background: linear-gradient(135deg, var(--accent-color) 0%, #0284c7 100%);
        }

        .success-gradient {
            background: linear-gradient(135deg, var(--success-color) 0%, #059669 100%);
        }

        .warning-gradient {
            background: linear-gradient(135deg, var(--warning-color) 0%, #d97706 100%);
        }

        .stats-card {
            background: linear-gradient(135deg, #ffffff 0%, var(--gray-50) 100%);
            border: 1px solid var(--gray-200);
            transition: all 0.3s ease;
        }

        .stats-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
            border-color: var(--primary-color);
        }

        .filter-section {
            background: linear-gradient(135deg, #ffffff 0%, var(--gray-50) 100%);
            border: 1px solid var(--gray-200);
        }

        .table-container {
            background: #ffffff;
            border: 1px solid var(--gray-200);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        .pagination-container {
            background: linear-gradient(135deg, #ffffff 0%, var(--gray-50) 100%);
            border: 1px solid var(--gray-200);
        }

        .modal-backdrop {
            background: rgba(17, 24, 39, 0.5);
            backdrop-filter: blur(4px);
        }

        .modal-content {
            background: linear-gradient(135deg, #ffffff 0%, var(--gray-50) 100%);
            border: 1px solid var(--gray-200);
        }

        .section-card {
            background: linear-gradient(135deg, #ffffff 0%, var(--gray-50) 100%);
            border: 1px solid var(--gray-200);
        }
    </style>
</head>

<body class="bg-gray-50 text-gray-900">
    <?php include '../../component/navigation_report.php'; ?>
    <?php include '../../component/sidebar_report.php'; ?>

    <main id="main-content" class="flex-1 p-6 transition-all duration-300 ml-64">
        <div class="max-w-full mx-auto px-4">
            <div class="min-h-screen glass-card rounded-2xl shadow-xl p-8 animate-fade-in-up">

                <!-- Header Section -->
                <div class="flex flex-col items-center mb-8 lg:mb-10">
                    <div class="primary-gradient p-4 lg:p-5 rounded-2xl shadow-lg mb-4 animate-fade-in-up">
                        <i class="fas fa-gift text-white text-3xl lg:text-4xl"></i>
                    </div>
                    <h1 class="text-3xl lg:text-4xl font-bold text-gray-800 text-center animate-fade-in-up">
                        Kelola Hadiah
                    </h1>
                    <p class="text-gray-600 text-base mt-3 text-center max-w-md">Tambah dan kelola hadiah untuk member setia</p>
                </div>

                <!-- Action Buttons -->
                <div class="flex flex-col sm:flex-row justify-between items-center gap-6 mb-10">
                    <!-- Button Group -->
                    <div class="flex flex-col sm:flex-row gap-4 w-full sm:w-auto">
                        <button onclick="tambahHadiah()"
                            class="px-8 py-4 primary-gradient text-white rounded-xl hover:shadow-lg transition-all duration-300 flex items-center justify-center gap-3 shadow-md cursor-pointer font-semibold hover:scale-105 whitespace-nowrap">
                            <i class="fas fa-plus text-lg"></i>
                            <span>Tambah Hadiah</span>
                        </button>
                        <!-- Tombol Baru untuk Penerimaan Hadiah -->
                        <button onclick="window.location.href='/src/fitur/transaction/rewards/management_reward'"
                            class="px-8 py-4 success-gradient text-white rounded-xl hover:shadow-lg transition-all duration-300 flex items-center justify-center gap-3 shadow-md cursor-pointer font-semibold hover:scale-105 whitespace-nowrap">
                            <i class="fas fa-bars-progress text-lg"></i>
                            <span>Kelola Penerimaan</span>
                        </button>
                    </div>
                </div>

                <!-- Stats Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
                    <div class="stats-card p-6 rounded-xl shadow-sm">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-600 mb-2">Total Hadiah</p>
                                <p class="text-2xl font-bold text-gray-800" id="totalHadiah">0</p>
                            </div>
                            <div class="bg-blue-50 p-3 rounded-xl flex-shrink-0">
                                <i class="fas fa-gift text-blue-600 text-xl"></i>
                            </div>
                        </div>
                    </div>

                    <div class="stats-card p-6 rounded-xl shadow-sm">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-600 mb-2">Member Terima</p>
                                <p class="text-2xl font-bold text-gray-800" id="memberTerima">0</p>
                            </div>
                            <div class="bg-green-50 p-3 rounded-xl flex-shrink-0">
                                <i class="fas fa-users text-green-600 text-xl"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filter Block -->
                <div class="filter-section rounded-2xl shadow-md p-8 mb-10 animate-fade-in-up">
                    <h3 class="text-xl font-semibold text-gray-800 mb-6 flex items-center gap-3">
                        <i class="fas fa-filter text-indigo-600"></i>
                        Filter Data Hadiah
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                        <!-- Filter Cabang -->
                        <div class="space-y-3">
                            <label for="filterCabang" class="block text-sm font-semibold text-gray-700">Cabang</label>
                            <select id="filterCabang"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-gray-700 transition-all duration-200 bg-white">
                                <!-- opsi cabang bisa diisi via JS dari API kode_store -->
                            </select>
                        </div>

                        <!-- Filter Search -->
                        <div class="space-y-3 md:col-span-2">
                            <label for="filterSearch" class="block text-sm font-semibold text-gray-700">Cari Hadiah</label>
                            <div class="relative">
                                <input type="text" id="filterSearch" placeholder="Cari berdasarkan nama/plu/karyawan..."
                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-gray-700 placeholder-gray-400 transition-all duration-200 bg-white">
                                <i class="fas fa-search absolute right-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Table Container -->
                <div class="overflow-x-auto w-full table-container rounded-2xl animate-fade-in-up">
                    <div class="min-w-full inline-block align-middle">
                        <div class="overflow-hidden">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="primary-gradient text-white">
                                    <tr>
                                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider w-16">No</th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider w-20">PLU</th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider min-w-[200px]">Nama Hadiah</th>
                                        <th class="px-6 py-4 text-center text-xs font-semibold uppercase tracking-wider w-20">Poin</th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider min-w-[150px]">Karyawan</th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider w-24">NIK</th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider w-32">Tgl. Dibuat</th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider w-32">Tgl. Update</th>
                                        <th class="px-6 py-4 text-center text-xs font-semibold uppercase tracking-wider w-16">Qty</th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider w-28">Cabang</th>
                                        <th class="px-6 py-4 text-center text-xs font-semibold uppercase tracking-wider w-32">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="tableBody" class="divide-y divide-gray-200 text-gray-700 text-sm bg-white">

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                <div class="mt-10 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6 pagination-container rounded-xl p-6 shadow-sm">
                    <div class="flex items-center gap-4">
                        <label for="pageSize" class="text-sm font-semibold text-gray-700 whitespace-nowrap">Tampilkan:</label>
                        <select id="pageSize" class="px-4 py-2 rounded-lg border border-gray-300 text-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all duration-200 text-sm bg-white shadow-sm">
                            <option value="5">5 per halaman</option>
                            <option value="10" selected>10 per halaman</option>
                            <option value="25">25 per halaman</option>
                            <option value="50">50 per halaman</option>
                        </select>
                    </div>

                    <div class="text-sm text-gray-600 font-medium" id="dataInfo">Menampilkan data...</div>

                    <div class="flex items-center justify-center lg:justify-end">
                        <div class="flex items-center gap-1 bg-white rounded-lg shadow-sm border border-gray-200 p-1" id="paginationContainer">
                            <button id="firstPage" class="p-3 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200" title="Halaman pertama">
                                <i class="fas fa-angle-double-left text-gray-600 text-sm"></i>
                            </button>
                            <button id="prevPage" class="p-3 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200" title="Sebelumnya">
                                <i class="fas fa-angle-left text-gray-600 text-sm"></i>
                            </button>
                            <div class="flex items-center gap-1 mx-3" id="pageNumbers">
                                <!-- Page numbers will be inserted here by JavaScript -->
                            </div>
                            <button id="nextPage" class="p-3 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200" title="Selanjutnya">
                                <i class="fas fa-angle-right text-gray-600 text-sm"></i>
                            </button>
                            <button id="lastPage" class="p-3 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200" title="Halaman terakhir">
                                <i class="fas fa-angle-double-right text-gray-600 text-sm"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Tambah Hadiah -->
        <div id="modalTambahHadiah"
            class="fixed inset-0 modal-backdrop flex justify-center items-center z-50 hidden transition-all duration-300">
            <div class="modal-content w-full max-w-5xl rounded-2xl shadow-2xl relative animate-fade-in-up overflow-y-auto max-h-[90vh]"
                id="modalContent">
                <!-- Tombol Close -->
                <button onclick="closeModal('modalTambahHadiah','modalContent')"
                    class="absolute top-6 right-6 text-gray-500 hover:text-red-500 text-2xl bg-white/90 rounded-full p-3 shadow-md transition-all duration-200 z-10">
                    <i class="fas fa-times"></i>
                </button>

                <!-- Header -->
                <div class="sticky top-0 z-20 bg-white/95 backdrop-blur-sm border-b border-gray-200 p-8 rounded-t-2xl shadow">
                    <h2 class="text-3xl font-bold text-gray-800 flex items-center gap-4">
                        <div class="primary-gradient p-4 rounded-2xl">
                            <i class="fas fa-gift text-white text-xl"></i>
                        </div>
                        Tambah Hadiah Baru
                    </h2>
                    <p class="text-gray-600 mt-3 text-base">Lengkapi informasi hadiah untuk member setia Anda</p>
                </div>

                <!-- Form Content -->
                <div class="p-8">
                    <form id="formTambahHadiah" class="space-y-10">
                        <!-- Section 1: Informasi Dasar -->
                        <div class="section-card rounded-2xl p-8 relative shadow-sm">
                            <h3 class="text-xl font-semibold text-gray-800 mb-6 flex items-center gap-3">
                                <i class="fas fa-info-circle text-indigo-600"></i>
                                Informasi Dasar
                            </h3>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                <div class="space-y-3">
                                    <label for="nama_hadiah" class="block text-sm font-semibold text-gray-700">
                                        Nama Hadiah <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" name="nama_hadiah" id="nama_hadiah" required
                                        class="reward-input w-full rounded-xl px-4 py-3 text-base shadow-sm focus:outline-none transition-all duration-200"
                                        placeholder="Contoh: Voucher Diskon 50%" />
                                    <p class="text-xs text-gray-500">Masukkan nama hadiah yang menarik dan jelas</p>
                                </div>

                                <div class="space-y-3">
                                    <label for="plu" class="block text-sm font-semibold text-gray-700">
                                        Plu <span class="text-red-500">*</span>
                                    </label>
                                    <input type="number" name="plu" id="plu" required
                                        class="reward-input w-full rounded-xl px-4 py-3 text-base shadow-sm focus:outline-none transition-all duration-200"
                                        placeholder="Masukan PLU" />
                                    <p class="text-xs text-gray-500">Masukkan no plu sesuai barang</p>
                                </div>

                                <div class="space-y-3">
                                    <label for="cabang" class="block text-sm font-semibold text-gray-700">
                                        Cabang <span class="text-red-500">*</span>
                                    </label>
                                    <select name="cabang" id="cabang" required
                                        class="reward-input w-full rounded-xl px-4 py-3 text-base shadow-sm focus:outline-none transition-all duration-200">
                                    </select>
                                    <p class="text-xs text-gray-500">Pilih cabang</p>
                                </div>
                            </div>
                        </div>

                        <!-- Section 2: Nilai dan Poin -->
                        <div class="section-card shadow-sm rounded-2xl p-8 relative">
                            <h3 class="text-xl font-semibold text-gray-800 mb-6 flex items-center gap-3">
                                <i class="fas fa-coins text-green-600"></i>
                                Poin & Stok
                            </h3>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                <div class="space-y-3">
                                    <label for="poin_dibutuhkan" class="block text-sm font-semibold text-gray-700">
                                        Poin yang Dibutuhkan <span class="text-red-500">*</span>
                                    </label>
                                    <input type="number" name="poin_dibutuhkan" id="poin_dibutuhkan" required min="1"
                                        class="reward-input w-full rounded-xl px-4 py-3 text-base shadow-sm focus:outline-none transition-all duration-200"
                                        placeholder="100" />
                                    <p class="text-xs text-gray-500">Jumlah poin yang harus dikumpulkan member</p>
                                </div>
                                <div class="space-y-3">
                                    <label for="qty_hadiah" class="block text-sm font-semibold text-gray-700">
                                        Qty Hadiah <span class="text-red-500">*</span>
                                    </label>
                                    <input type="number" name="qty_hadiah" id="qty_hadiah" required min="1"
                                        class="reward-input w-full rounded-xl px-4 py-3 text-base shadow-sm focus:outline-none transition-all duration-200"
                                        placeholder="100" />
                                    <p class="text-xs text-gray-500">Jumlah hadiah yang tersedia</p>
                                </div>
                            </div>
                        </div>

                        <!-- Section 5: Gambar Hadiah -->
                        <div class="section-card shadow-sm rounded-2xl p-8 relative">
                            <h3 class="text-xl font-semibold text-gray-800 mb-6 flex items-center gap-3">
                                <i class="fas fa-image text-blue-600"></i>
                                Gambar Hadiah
                            </h3>

                            <div class="border-2 border-dashed border-gray-300 rounded-2xl p-8 text-center hover:border-indigo-400 transition-colors duration-200 bg-gray-50/50">
                                <div class="space-y-6">
                                    <i class="fas fa-cloud-upload-alt text-5xl text-gray-400"></i>
                                    <div>
                                        <p class="text-lg font-semibold text-gray-700">Upload Gambar Hadiah</p>
                                        <p class="text-sm text-gray-500 mt-2">Format: JPG, PNG, GIF (Max: 5MB)</p>
                                    </div>
                                    <div class="flex flex-col items-center justify-center w-full">
                                        <input type="file" id="gambar_hadiah" name="gambar_hadiah" accept="image/*"
                                            class="hidden" />
                                        <div class="flex flex-col items-center space-y-4 w-full">
                                            <button type="button" id="uploadContent"
                                                class="px-8 py-3 primary-gradient text-white rounded-xl hover:shadow-lg transition-all duration-200 font-semibold w-full sm:w-auto">
                                                <i class="fas fa-upload mr-2"></i>
                                                Pilih File
                                            </button>
                                            <div id="loadingIndicator"
                                                class="hidden items-center justify-center space-x-3 text-indigo-600">
                                                <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg"
                                                    fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                                        stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor"
                                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                                    </path>
                                                </svg>
                                                <span>Mengoptimalkan gambar...</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div id="imagePreview" class="mt-6 hidden">
                                        <img id="previewImg" src="" alt="Preview"
                                            class="max-w-xs mx-auto rounded-xl shadow-md" />
                                        <button type="button" id="removeImageBtn"
                                            class="mt-4 text-red-500 hover:text-red-700 text-sm font-medium">
                                            <i class="fas fa-trash mr-2"></i>Hapus Gambar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex flex-col sm:flex-row gap-6 pt-8 bg-white/80 rounded-2xl p-8 shadow-sm">
                            <button type="button" onclick="closeModal('modalTambahHadiah','modalContent')"
                                class="flex-1 px-8 py-4 bg-white text-gray-700 border-2 border-gray-300 rounded-xl hover:bg-gray-50 hover:border-gray-400 transition-all duration-200 flex items-center justify-center gap-3 font-semibold text-lg shadow-sm">
                                <i class="fas fa-times"></i>
                                <span>Batal</span>
                            </button>
                            <button type="submit" id="submitBtn"
                                class="flex-1 px-8 py-4 primary-gradient text-white rounded-xl hover:shadow-lg transition-all duration-200 flex items-center justify-center gap-3 font-semibold text-lg">
                                <i class="fas fa-save"></i>
                                <span>Simpan Hadiah</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </main>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="../../js/ui/navbar_toogle.js" type="module"></script>
    <script src="../../js/rewards/init.js" type="module"></script>
    <script>
        // Initialize GSAP animations
        gsap.from(".animate-fade-in-up", {
            duration: 0.8,
            y: 50,
            opacity: 0,
            stagger: 0.2,
            ease: "power2.out"
        });


        // Modal functions
        function tambahHadiah() {
            const modal = document.getElementById('modalTambahHadiah');
            const content = document.getElementById('modalContent');
            // clear any leftover inline styles from previous close animation
            if (content) {
                content.style.opacity = '';
                content.style.transform = '';
            }
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            // animate modal content in for a smooth entry
            if (content) {
                gsap.fromTo(content, { scale: 0.98, opacity: 0 }, { duration: 0.25, scale: 1, opacity: 1, ease: "power2.out" });
            }
        }

        function closeModal(modalId, contentId) {
            const modal = document.getElementById(modalId);
            const content = document.getElementById(contentId);

            // Add smooth closing animation
            gsap.to(content, {
                duration: 0.2,
                scale: 0.9,
                opacity: 0,
                ease: "power2.in",
                onComplete: () => {
                    modal.classList.add('hidden');
                    document.body.style.overflow = 'auto';

                    // Reset form if it's the add modal
                    if (modalId === 'modalTambahHadiah') {
                        const form = document.getElementById('formTambahHadiah');
                        if (form) form.reset();
                        const preview = document.getElementById('imagePreview');
                        if (preview) preview.classList.add('hidden');
                    }

                    // Clear inline styles so next open is fresh
                    if (content) {
                        content.style.opacity = '';
                        content.style.transform = '';
                    }
                }
            });
        }

        // Close modal when clicking outside
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('fixed') && e.target.id.includes('modal')) {
                const modalId = e.target.id;
                const contentId = e.target.querySelector('div').id;
                closeModal(modalId, contentId);
            }
        });     

        // Enhanced pagination and filtering animations
        document.addEventListener('DOMContentLoaded', function() {
            // Add smooth transitions to all interactive elements
            const interactiveElements = document.querySelectorAll('button, select, input, .stats-card, .reward-card');
            interactiveElements.forEach(element => {
                element.style.transition = 'all 0.3s ease';
            });

            // Enhanced hover effects for stats cards
            const statsCards = document.querySelectorAll('.stats-card');
            statsCards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    gsap.to(this, {duration: 0.3, y: -5, boxShadow: '0 10px 30px rgba(0,0,0,0.1)'});
                });
                
                card.addEventListener('mouseleave', function() {
                    gsap.to(this, {duration: 0.3, y: 0, boxShadow: '0 4px 12px rgba(0,0,0,0.05)'});
                });
            });
        });
    </script>
</body>

</html>