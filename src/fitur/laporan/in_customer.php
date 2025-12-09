<?php
include '../../../aa_kon_sett.php';

require_once __DIR__ . '/../../component/menu_handler.php';

$menuHandler = new MenuHandler('laporan_pelanggan_aktifitas');

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
    <title>Aktifitas Pelanggan</title>

    <!-- font awesome cdn link  -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/all.min.css">

    <!-- Penjelasan: Link ke CSS eksternal dengan cache busting query string -->
    <link rel="stylesheet" href="../../style/header.css">
    <link rel="stylesheet" href="../../style/sidebar.css">
    <link rel="stylesheet" href="../../style/animation-fade-in.css">
    <!-- Setting logo pada tab di website Anda / Favicon -->
    <link rel="icon" type="image/png" href="../../../public/images/logo1.png">
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="../../style/default-font.css">
    <!-- <link rel="stylesheet" href="../../style/output.css"> -->
    <link rel="stylesheet" href="../../output2.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/2.2.2/css/dataTables.dataTables.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css">
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>


    <style>
        th.th-total-poin,
        th.th-periksa,
        th.th-tukar-poin,
        th.th-sisa-poin,
        th.th-transaksi {
            text-align: center !important;
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.90);
            backdrop-filter: blur(8px);
            border-radius: 1.25rem;
            box-shadow: 0 4px 24px 0 rgba(31, 38, 135, 0.10);
            border: 1px solid rgba(255, 255, 255, 0.20);
            transition: all 0.3s ease;
        }

        .glass-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.20);
        }

        /* --- Enhanced DataTables Styling --- */
        table.dataTable thead th {
            background: linear-gradient(135deg, #ec4899 0%, #f43f5e 100%);
            position: relative;
            overflow: hidden;
        }

        table.dataTable thead th::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }

        table.dataTable thead th:hover::before {
            left: 100%;
        }

        table.dataTable tbody td {
            transition: all 0.2s ease;
        }

        table.dataTable tbody tr {
            transition: all 0.3s ease;
        }

        table.dataTable tbody tr:hover {
            border-radius: 0.75rem;
        }

        /* Enhanced Pagination */
        .dataTables_wrapper .dataTables_paginate .paginate_button {
            background: linear-gradient(90deg, #ec4899 0%, #f43f5e 100%);
            color: #fff !important;
            border: none;
            border-radius: 0.5rem;
            margin: 0 0.15rem;
            padding: 0.25rem 0.75rem;
            font-size: 0.95rem;
            font-weight: 600;
            box-shadow: 0 2px 8px rgba(236, 72, 153, 0.10);
            cursor: pointer;
            transition: background 0.2s, transform 0.2s;
            position: static;
            z-index: auto;
            overflow: visible;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button.current,
        .dataTables_wrapper .dataTables_paginate .paginate_button:active {
            background: linear-gradient(90deg, #db2777 0%, #e11d48 100%);
            color: #fff !important;
            transform: scale(1.08);
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background: linear-gradient(90deg, #f472b6 0%, #fb7185 100%);
            color: #fff !important;
            transform: scale(1.05);
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button::before {
            display: none !important;
        }

        /* Enhanced Search & Length Controls */
        .dataTables_wrapper .dataTables_filter input {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(4px);
        }

        .dataTables_wrapper .dataTables_length select {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(4px);
        }

        .dataTables_wrapper .dataTables_info {
            background: rgba(255, 255, 255, 0.8);
            padding: 0.75rem 1rem;
            border-radius: 0.75rem;
            backdrop-filter: blur(4px);
        }

        /* Enhanced Modal Styles */
        .modal-glass {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(16px);
            border-radius: 1.5rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }



        /* Enhanced Loading Animation */
        .loading-spinner {
            background: conic-gradient(from 0deg, transparent, #ec4899, transparent);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        /* Enhanced File Upload Area */
        .file-upload-area {
            background: rgba(255, 255, 255, 0.5);
            backdrop-filter: blur(4px);
        }

        .file-upload-area:hover {
            transform: scale(1.02);
        }

        /* Enhanced Image Grid */
        .image-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
            gap: 1rem;
            padding: 1rem;
        }

        .image-item {
            aspect-ratio: 1;
        }

        .image-item:hover {
            transform: scale(1.05);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        }


        /* Enhanced Status Badges */
        .status-badge {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            box-shadow: 0 2px 8px rgba(16, 185, 129, 0.3);
        }

        .status-badge.warning {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            box-shadow: 0 2px 8px rgba(245, 158, 11, 0.3);
        }

        .status-badge.danger {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            box-shadow: 0 2px 8px rgba(239, 68, 68, 0.3);
        }

        /* Enhanced Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, #ec4899 0%, #f43f5e 100%);
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(135deg, #db2777 0%, #e11d48 100%);
        }

        /* Enhanced Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fade-in-up {
            animation: fadeInUp 0.6s ease-out;
        }

        /* Enhanced Responsive Design */
        @media (max-width: 768px) {
            .glass-container {
                margin: 0.5rem;
                padding: 1rem;
            }

            .image-grid {
                grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
                gap: 0.5rem;
            }
        }
    </style>
</head>

<body class="bg-white flex">
    <?php include '../../component/navigation_report.php'; ?>
    <?php include '../../component/sidebar_report.php'; ?>


    <main id="main-content" class="flex-1 p-6 transition-all duration-300 ml-64 mt-10">
        <div
            class="min-h-screen bg-white/80 backdrop-blur-md rounded-2xl shadow-xl border border-pink-100 p-8 animate-fade-in-up">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8 gap-4">
                <div class="flex items-center gap-4">
                    <div class="bg-gradient-to-r from-pink-500 to-rose-400 p-3 rounded-xl shadow-lg">
                        <i class="fa fa-users text-white text-3xl"></i>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold text-gray-800">Aktivitas Pelanggan</h2>
                        <p class="text-gray-500 text-sm mt-1">Lihat dan filter aktivitas pelanggan Anda</p>
                    </div>

                </div>
                <div
                    class="border-pink-100 bg-white/80 backdrop-blur rounded-xl shadow px-6 py-4 flex flex-col md:flex-row md:items-end gap-4 animate-fade-in-up">
                    <div class="flex flex-col gap-1 md:mr-4">
                        <label for="filterRange" class="text-sm text-gray-600 font-semibold flex items-center gap-2">
                            <i class="fa fa-filter text-pink-400"></i> Filter
                        </label>
                        <select id="filterRange"
                            class="px-4 py-2 border border-pink-200 rounded-xl text-sm text-gray-700 shadow focus:outline-none focus:ring-2 focus:ring-pink-300 focus:border-pink-400 transition-all duration-200 bg-white/80">
                            <option value="day" selected>Per Hari</option>
                            <option value="week">Per Minggu</option>
                            <option value="month">Per Bulan</option>
                        </select>
                    </div>
                    <div class="flex flex-col gap-1 md:mr-4">
                        <label for="filterRangeDate"
                            class="text-sm text-gray-600 font-semibold flex items-center gap-2">
                            <i class="fa fa-calendar-days text-pink-400"></i> Tanggal
                        </label>
                        <div id="searchContainer" class="flex items-center relative">
                            <input type="text" id="filterRangeDate"
                                class="w-full px-4 py-2 border border-pink-200 rounded-xl text-sm text-gray-700 shadow focus:outline-none focus:ring-2 focus:ring-pink-300 focus:border-pink-400 transition-all duration-200 bg-white/80 pl-10"
                                placeholder="Pilih tanggal">
                            <i class="fa fa-calendar absolute left-3 top-1/2 -translate-y-1/2 text-pink-300"></i>
                        </div>
                    </div>
                    <div class="flex flex-col gap-1">
                        <div id="kode" class="flex items-center relative">

                        </div>
                    </div>
                </div>
            </div>
            <!-- Enhanced Table Section -->
            <div class="max-w-8xl mx-auto relative">
                <!-- Enhanced Loading State -->
                <div id="loadingTable"
                    class="absolute inset-0 flex justify-center items-center z-10 bg-white/80 backdrop-blur-sm rounded-2xl">
                    <div class="text-center">
                        <div class="loading-spinner w-16 h-16 mx-auto mb-4"></div>
                        <p class="text-gray-600 font-semibold">Memuat data aktivitas pelanggan...</p>
                        <p class="text-gray-500 text-sm mt-2">Mohon tunggu sebentar</p>
                    </div>
                </div>

                <!-- Enhanced Table Container -->
                <div class="glass-card overflow-hidden animate-fade-in-up">
                    <div class="p-6 bg-gradient-to-r from-pink-50 to-rose-50 border-b border-pink-200">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <i class="fa fa-table text-pink-600 text-xl"></i>
                                <h3 class="text-lg font-semibold text-gray-800">Data Aktivitas Pelanggan</h3>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="w-3 h-3 bg-green-500 rounded-full animate-pulse"></div>
                                <span class="text-sm text-gray-600">Real-time Data</span>
                            </div>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table id="customerTable" class="table-auto display rounded-xl min-w-full">
                            <thead class="bg-gradient-to-r from-pink-500 to-rose-500 text-white">
                            </thead>
                            <tbody>
                                <!-- Data akan di-load lewat JavaScript -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <!-- Enhanced Detail Modal -->
        <div id="modalTable"
            class="fixed inset-0 bg-black/60 backdrop-blur-md flex items-center justify-center z-50 hidden animate-fade-in-up">
            <div id="modalContent" class="modal-glass w-full max-w-4xl mx-4 animate-fade-in-up">

                <!-- Header Modal -->
                <div class="bg-gradient-to-r from-pink-500 to-rose-500 text-white p-5 rounded-t-2xl shadow-lg">
                    <div class="flex justify-between items-center">
                        <div class="flex items-center gap-4">
                            <div class="bg-white/20 p-3 rounded-xl">
                                <i class="fas fa-user-circle text-2xl"></i>
                            </div>
                            <div>
                                <h3 class="text-2xl font-bold">Detail Data Pelanggan</h3>
                                <p class="text-pink-100 text-sm">Informasi lengkap aktivitas pelanggan</p>
                            </div>
                        </div>
                        <button id="closeModal"
                            class="text-white hover:text-pink-200 text-2xl bg-white/20 rounded-full p-3 shadow-lg transition-all duration-300 focus:outline-none hover:bg-white/30 transform hover:scale-110">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
                <!-- Konten Modal: DataTables search/filter dan tabel -->
                <div class="p-4">
                    <div class="overflow-x-auto">
                        <table id="customerTableDetail" class="table-auto display min-w-full">
                            <thead
                                class="bg-gradient-to-r from-pink-500 to-rose-500 text-white font-bold uppercase text-xs">
                                <!-- Header tabel, akan diisi lewat JS -->
                            </thead>
                            <tbody class="text-gray-700 font-medium">
                                <!-- Data akan di-load lewat JavaScript -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <!-- Enhanced Receipt Modal -->
        <div id="strukModal"
            class="fixed bg-black/60 backdrop-blur-md inset-0 z-50 hidden items-center justify-center animate-fade-in-up">
            <div class="modal-glass w-full max-w-5xl relative">
                <!-- Enhanced Header -->
                <div class="bg-gradient-to-r from-pink-500 to-rose-500 text-white p-6 rounded-t-2xl">
                    <div class="flex justify-between items-center">
                        <div class="flex items-center gap-4">
                            <div class="bg-white/20 p-3 rounded-xl">
                                <i class="fa fa-receipt text-2xl"></i>
                            </div>
                            <div>
                                <h2 class="text-2xl font-bold">Detail Item Transaksi</h2>
                                <p class="text-pink-100 text-sm">Informasi lengkap item yang dibeli</p>
                            </div>
                        </div>
                        <button id="closeModal1"
                            class="text-white hover:text-pink-200 text-2xl bg-white/20 rounded-full p-3 shadow-lg transition-all duration-300 focus:outline-none hover:bg-white/30 transform hover:scale-110">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>

                <!-- Enhanced Content -->
                <div class="p-6">
                    <div class="glass-card">
                        <div id="strukContent" class="text-sm text-gray-700 max-h-[70vh] overflow-y-auto p-4">
                            <!-- Isi struk akan dimasukkan di sini -->
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Enhanced Upload Modal -->
        <div id="modalUpload"
            class="fixed inset-0 bg-black/60 backdrop-blur-md flex items-center justify-center z-50 hidden animate-fade-in-up">
            <div class="modal-glass w-full max-w-md relative" id="modalUploadContent">
                <!-- Enhanced Header -->
                <div class="bg-gradient-to-r from-green-500 to-emerald-500 text-white p-6 rounded-t-2xl">
                    <div class="flex justify-between items-center">
                        <div class="flex items-center gap-4">
                            <div class="bg-white/20 p-3 rounded-xl">
                                <i class="fa fa-upload text-2xl"></i>
                            </div>
                            <div>
                                <h2 class="text-2xl font-bold">Upload Files</h2>
                                <p class="text-green-100 text-sm">Upload dokumen pelanggan</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="p-6">
                    <form id="uploadForm" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="kd_cust" id="uploadKdCust">

                        <!-- Enhanced File Upload Area -->
                        <div class="file-upload-area mb-6">
                            <div class="flex flex-col items-center gap-4">
                                <i class="fa fa-cloud-upload-alt text-4xl text-pink-500"></i>
                                <div>
                                    <p class="text-lg font-semibold text-gray-700 mb-2">Pilih File untuk Upload</p>
                                    <p class="text-sm text-gray-500">Drag & drop atau klik untuk memilih file</p>
                                </div>
                                <input type="file" name="file_upload[]" id="fileUpload" accept="image/jpeg,image/png"
                                    multiple
                                    class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-pink-50 file:text-pink-700 hover:file:bg-pink-100 transition-all duration-300" />
                            </div>
                        </div>

                        <!-- Enhanced Preview Area -->
                        <div id="previewArea" class="image-grid mb-6"></div>

                        <!-- Enhanced Buttons -->
                        <div class="flex justify-end gap-3">
                            <button type="button" id="cancelUpload" class="btn-secondary">
                                <i class="fa fa-times mr-2"></i>Batal
                            </button>
                            <button type="submit" class="btn-primary">
                                <i class="fa fa-upload mr-2"></i>Upload
                            </button>
                        </div>
                    </form>

                    <!-- Enhanced Upload Overlay -->
                    <div id="uploadOverlay"
                        class="absolute inset-0 bg-white/90 backdrop-blur-md flex items-center justify-center rounded-2xl hidden z-20">
                        <div class="text-center">
                            <div class="loading-spinner w-12 h-12 mx-auto mb-4"></div>
                            <p class="text-gray-700 font-semibold">Uploading files...</p>
                            <p class="text-gray-500 text-sm">Mohon tunggu sebentar</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Enhanced Image Preview Modal -->
        <div id="imageModal"
            class="fixed inset-0 bg-black/60 backdrop-blur-md flex justify-center items-center hidden z-50 animate-fade-in-up">
            <div class="modal-glass max-w-6xl w-full">
                <!-- Enhanced Header -->
                <div class="bg-gradient-to-r from-blue-500 to-indigo-500 text-white p-6 rounded-t-2xl">
                    <div class="flex justify-between items-center">
                        <div class="flex items-center gap-4">
                            <div class="bg-white/20 p-3 rounded-xl">
                                <i class="fa fa-image text-2xl"></i>
                            </div>
                            <div>
                                <h2 class="text-2xl font-bold">Preview Gambar</h2>
                                <p class="text-blue-100 text-sm">Lihat dan kelola gambar pelanggan</p>
                            </div>
                        </div>
                        <button onclick="document.getElementById('imageModal').classList.add('hidden')"
                            class="text-white hover:text-blue-200 text-2xl bg-white/20 rounded-full p-3 shadow-lg transition-all duration-300 focus:outline-none hover:bg-white/30 transform hover:scale-110">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>

                <div class="p-6">
                    <!-- Enhanced Image Container -->
                    <div class="glass-card mb-6">
                        <div class="p-4 bg-gradient-to-r from-blue-50 to-indigo-50 border-b border-blue-200">
                            <div class="flex items-center gap-3">
                                <i class="fa fa-images text-blue-600"></i>
                                <h4 class="font-semibold text-gray-800">Galeri Gambar</h4>
                            </div>
                        </div>
                        <div id="modalImageContainer" class="image-grid max-h-[50vh] overflow-y-auto p-4">
                            <!-- Gambar akan dimasukkan di sini -->
                        </div>
                    </div>

                    <!-- Enhanced Add Image Section -->
                    <div class="glass-card">
                        <div class="p-4 bg-gradient-to-r from-green-50 to-emerald-50 border-b border-green-200">
                            <div class="flex items-center gap-3">
                                <i class="fa fa-plus-circle text-green-600"></i>
                                <h4 class="font-semibold text-gray-800">Tambah Gambar Baru</h4>
                            </div>
                        </div>
                        <div class="p-4">
                            <form method="POST" enctype="multipart/form-data" id="tambahFile">
                                <input type="hidden" name="kd_cust" id="tambahFileKd">

                                <div class="file-upload-area mb-4">
                                    <div class="flex flex-col items-center gap-3">
                                        <i class="fa fa-camera text-2xl text-green-500"></i>
                                        <div>
                                            <p class="font-semibold text-gray-700">Pilih Gambar Baru</p>
                                            <p class="text-sm text-gray-500">Format: JPG, PNG</p>
                                        </div>
                                        <input type="file" name="add_file[]" id="addFile" accept="image/jpeg,image/png"
                                            multiple
                                            class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-green-50 file:text-green-700 hover:file:bg-green-100 transition-all duration-300" />
                                    </div>
                                </div>

                                <div id="previewAreaTambah" class="image-grid mb-4"></div>

                                <div class="flex justify-end gap-3">
                                    <button type="submit" class="btn-primary">
                                        <i class="fa fa-upload mr-2"></i>Upload Gambar
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Enhanced Loading Overlay -->
                    <div id="uploadOverlay+"
                        class="fixed inset-0 bg-black/60 backdrop-blur-md flex items-center justify-center hidden z-50">
                        <div class="modal-glass p-8 text-center">
                            <div class="loading-spinner w-16 h-16 mx-auto mb-4"></div>
                            <p class="text-gray-700 font-semibold text-lg">Mengunggah gambar...</p>
                            <p class="text-gray-500 text-sm mt-2">Mohon tunggu sebentar</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Enhanced Large Image Modal -->
        <div id="largeImageModal"
            class="fixed inset-0 bg-black/80 backdrop-blur-sm flex items-center justify-center hidden z-50 animate-fade-in-up">
            <div class="modal-glass max-w-4xl w-full mx-4">
                <div class="relative">
                    <button onclick="document.getElementById('largeImageModal').classList.add('hidden')"
                        class="absolute -top-4 -right-4 z-10 bg-red-500 hover:bg-red-600 text-white rounded-full p-3 shadow-lg transition-all duration-300 transform hover:scale-110">
                        <i class="fas fa-times"></i>
                    </button>
                    <img id="largeImage" class="w-full h-auto max-h-[80vh] object-contain rounded-2xl shadow-2xl" />
                </div>
            </div>
        </div>

    </main>

    <!-- custom js file link -->
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <!-- AlpineJS -->
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <!-- Custom JS -->
    <script src="../../js/data_activity_cust.js" type="module"></script>
    <script src="../../js/middleware_auth.js"></script>

</body>

</html>