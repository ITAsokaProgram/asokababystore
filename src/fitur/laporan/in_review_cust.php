<?php
include '../../../aa_kon_sett.php';


require_once __DIR__ . '/../../component/menu_handler.php';

$menuHandler = new MenuHandler('laporan_pelanggan_review');

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
    <title>Review Pelanggan</title>

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
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <script src="https://cdn.jsdelivr.net/npm/medium-zoom@1.0.6/dist/medium-zoom.min.js"></script>

    <style>
        .btn.active {
            background-color: transparent;
            /* background tidak diisi */
            color: #ec4899;
            /* warna teks bisa disesuaikan */
            outline: 2px solid #ec4899;
            outline-offset: 1px;
        }

        .medium-zoom-image--opened {
            max-width: 80vw !important;
            max-height: 80vh !important;
            margin-top: 40px !important;
            margin-bottom: 40px !important;
            object-fit: contain;
        }

        /* Custom Animation Classes */
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

        /* Floating action button animation */
        @keyframes float {

            0%,
            100% {
                transform: translateY(0px);
            }

            50% {
                transform: translateY(-10px);
            }
        }

        .float-animation {
            animation: float 3s ease-in-out infinite;
        }

        /* Modal content scrollbar styling */
        #issueHandlingModal .overflow-y-auto::-webkit-scrollbar {
            width: 6px;
        }

        #issueHandlingModal .overflow-y-auto::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 3px;
        }

        #issueHandlingModal .overflow-y-auto::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 3px;
        }

        #issueHandlingModal .overflow-y-auto::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
    </style>
</head>

<body class="bg-gray-50 overflow-auto">
    <?php include '../../component/navigation_report.php' ?>;
    <?php include '../../component/sidebar_report.php' ?>;



    <main id="main-content" class="flex-1 p-6 ml-64">
        <!-- Enhanced Container with Gradient Background -->
        <div class="min-h-screen bg-gradient-to-br from-orange-50 via-white to-yellow-50 p-4">
            <div class="max-w-full mx-auto">
                <!-- Header Section with Glass Effect -->
                <div
                    class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-xl border border-white/20 p-8 mb-8 animate-fade-in-up hover:-translate-y-1 hover:shadow-2xl transition-all duration-300">
                    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6">
                        <div class="flex items-center space-x-4">
                            <div class="relative">
                                <div
                                    class="w-12 h-12 bg-gradient-to-r from-orange-400 to-yellow-400 rounded-xl flex items-center justify-center shadow-lg">
                                    <i class="fas fa-star text-white text-xl"></i>
                                </div>
                                <div
                                    class="absolute -top-1 -right-1 w-4 h-4 bg-green-400 rounded-full border-2 border-white animate-pulse">
                                </div>
                            </div>
                            <div>
                                <h2 class="text-2xl font-bold text-gray-800">Review Pelanggan</h2>
                                <p class="text-gray-600 text-sm">Monitor dan kelola feedback pelanggan</p>
                                <div class="flex items-center space-x-2 mt-1">
                                    <div class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></div>
                                    <span class="text-xs text-green-600 font-medium">Sistem Aktif</span>
                                </div>
                            </div>
                        </div>

                        <!-- Stats Section -->
                        <div class="flex items-center space-x-6 mt-4 lg:mt-0">
                            <div
                                class="bg-gradient-to-r from-blue-500 to-blue-600 text-white px-4 py-2 rounded-lg text-center hover:-translate-y-1 hover:shadow-lg transition-all duration-300">
                                <div class="text-lg font-bold" id="totalReviews">
                                    <div class="animate-pulse h-6 bg-white/20 rounded">-</div>
                                </div>
                                <div class="text-xs opacity-90">Total Reviews</div>
                            </div>
                            <div
                                class="bg-gradient-to-r from-green-500 to-green-600 text-white px-4 py-2 rounded-lg text-center hover:-translate-y-1 hover:shadow-lg transition-all duration-300">
                                <div class="text-lg font-bold" id="avgRating">
                                    <div class="animate-pulse h-6 bg-white/20 rounded">-</div>
                                </div>
                                <div class="text-xs opacity-90">Avg Rating</div>
                            </div>
                            <div
                                class="bg-gradient-to-r from-orange-500 to-orange-600 text-white px-4 py-2 rounded-lg text-center hover:-translate-y-1 hover:shadow-lg transition-all duration-300">
                                <div class="text-lg font-bold" id="pendingIssues">
                                    <div class="animate-pulse h-6 bg-white/20 rounded">-</div>
                                </div>
                                <div class="text-xs opacity-90">Pending Issues</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Enhanced Rating Filter -->
                <div
                    class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-xl border border-white/30 p-6 mb-6 animate-fade-in-up hover:-translate-y-1 hover:shadow-2xl transition-all duration-300">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-filter mr-2 text-orange-500"></i>
                        Filter Rating
                    </h3>
                    <div id="ratingCardContainer" class="flex flex-wrap gap-3">
                        <div class="rating-card active cursor-pointer px-4 py-3 rounded-xl border border-gray-300 bg-gradient-to-r from-gray-100 to-gray-200 shadow-sm hover:shadow-md transition-all duration-200 flex items-center gap-2 hover:-translate-y-2 hover:shadow-lg"
                            data-rating="all">
                            <div class="animate-pulse flex items-center space-x-2">
                                <i class="fas fa-list text-gray-600"></i>
                                <span class="font-medium">Semua</span>
                                <span class="ml-1 text-xs text-gray-500">(-)</span>
                            </div>
                        </div>
                        <div class="rating-card cursor-pointer px-4 py-3 rounded-xl border border-gray-300 bg-white hover:bg-yellow-50 shadow-sm hover:shadow-md transition-all duration-200 flex items-center gap-2 hover:-translate-y-2 hover:shadow-lg"
                            data-rating="5">
                            <div class="animate-pulse flex items-center space-x-2">
                                <i class="fa fa-star text-yellow-400"></i>
                                <span class="font-medium">5 Bintang</span>
                                <span class="ml-1 text-xs text-gray-500">(-)</span>
                            </div>
                        </div>
                        <div class="rating-card cursor-pointer px-4 py-3 rounded-xl border border-gray-300 bg-white hover:bg-yellow-50 shadow-sm hover:shadow-md transition-all duration-200 flex items-center gap-2 hover:-translate-y-2 hover:shadow-lg"
                            data-rating="4">
                            <div class="animate-pulse flex items-center space-x-2">
                                <i class="fa fa-star text-yellow-400"></i>
                                <span class="font-medium">4 Bintang</span>
                                <span class="ml-1 text-xs text-gray-500">(-)</span>
                            </div>
                        </div>
                        <div class="rating-card cursor-pointer px-4 py-3 rounded-xl border border-gray-300 bg-white hover:bg-yellow-50 shadow-sm hover:shadow-md transition-all duration-200 flex items-center gap-2 hover:-translate-y-2 hover:shadow-lg"
                            data-rating="3">
                            <div class="animate-pulse flex items-center space-x-2">
                                <i class="fa fa-star text-yellow-400"></i>
                                <span class="font-medium">3 Bintang</span>
                                <span class="ml-1 text-xs text-gray-500">(-)</span>
                            </div>
                        </div>
                        <div class="rating-card cursor-pointer px-4 py-3 rounded-xl border border-gray-300 bg-white hover:bg-yellow-50 shadow-sm hover:shadow-md transition-all duration-200 flex items-center gap-2 hover:-translate-y-2 hover:shadow-lg"
                            data-rating="2">
                            <div class="animate-pulse flex items-center space-x-2">
                                <i class="fa fa-star text-yellow-400"></i>
                                <span class="font-medium">2 Bintang</span>
                                <span class="ml-1 text-xs text-gray-500">(-)</span>
                            </div>
                        </div>
                        <div class="rating-card cursor-pointer px-4 py-3 rounded-xl border border-gray-300 bg-white hover:bg-yellow-50 shadow-sm hover:shadow-md transition-all duration-200 flex items-center gap-2 hover:-translate-y-2 hover:shadow-lg"
                            data-rating="1">
                            <div class="animate-pulse flex items-center space-x-2">
                                <i class="fa fa-star text-yellow-400"></i>
                                <span class="font-medium">1 Bintang</span>
                                <span class="ml-1 text-xs text-gray-500">(-)</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Table Container with Enhanced Design -->
                <div
                    class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-2xl border border-white/30 overflow-hidden animate-fade-in-up hover:-translate-y-1 hover:shadow-2xl transition-all duration-300">
                    <!-- Table Header with Gradient -->
                    <div class="bg-gradient-to-r from-orange-400 via-yellow-400 to-orange-500 p-6">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-white">Daftar Review</h3>
                            <div class="flex items-center space-x-2">
                                <div class="w-2 h-2 bg-white/80 rounded-full animate-pulse"></div>
                                <span class="text-white/90 text-sm">View Data</span>
                            </div>
                        </div>
                    </div>

                    <!-- Table Content -->
                    <div class="max-h-[65vh] overflow-y-auto">
                        <!-- Loading State -->
                        <div id="tableLoading" class="hidden">
                            <div class="animate-pulse">
                                <div class="h-4 bg-gray-200 rounded mb-4"></div>
                                <div class="h-4 bg-gray-200 rounded mb-4"></div>
                                <div class="h-4 bg-gray-200 rounded mb-4"></div>
                                <div class="h-4 bg-gray-200 rounded mb-4"></div>
                                <div class="h-4 bg-gray-200 rounded mb-4"></div>
                            </div>
                        </div>

                        <!-- Table -->
                        <table class="w-full table-auto text-sm text-left">
                            <thead class="bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
                                <tr>
                                    <th
                                        class="px-4 py-3 text-left font-semibold text-gray-700 text-xs uppercase tracking-wider w-12">
                                        No</th>
                                    <th
                                        class="px-4 py-3 text-left font-semibold text-gray-700 text-xs uppercase tracking-wider w-28">
                                        Handphone</th>
                                    <th
                                        class="px-4 py-3 text-left font-semibold text-gray-700 text-xs uppercase tracking-wider w-32">
                                        Nama</th>
                                    <th
                                        class="px-4 py-3 text-left font-semibold text-gray-700 text-xs uppercase tracking-wider w-48">
                                        Komentar</th>
                                    <th
                                        class="px-4 py-3 text-left font-semibold text-gray-700 text-xs uppercase tracking-wider w-20">
                                        Rating</th>
                                    <th
                                        class="px-4 py-3 text-left font-semibold text-gray-700 text-xs uppercase tracking-wider w-24">
                                        Tanggal</th>
                                    <th
                                        class="px-4 py-3 text-left font-semibold text-gray-700 text-xs uppercase tracking-wider w-24">
                                        No Faktur</th>
                                    <th
                                        class="px-4 py-3 text-left font-semibold text-gray-700 text-xs uppercase tracking-wider w-24">
                                        Kategori</th>
                                    <th
                                        class="px-4 py-3 text-left font-semibold text-gray-700 text-xs uppercase tracking-wider w-24">
                                        Cabang</th>
                                    <th
                                        class="px-4 py-3 text-left font-semibold text-gray-700 text-xs uppercase tracking-wider w-24">
                                        Nama Kasir</th>
                                    <th
                                        class="px-4 py-3 text-left font-semibold text-gray-700 text-xs uppercase tracking-wider w-16">
                                        Foto</th>
                                    <th
                                        class="px-4 py-3 text-left font-semibold text-gray-700 text-xs uppercase tracking-wider w-28">
                                        Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="userTableBody" class="text-gray-700">
                                <tr
                                    class="hover:bg-gray-50 transition-colors duration-200 border-b border-gray-100 hover:-translate-y-0.5 hover:shadow-sm">
                                    <!-- Loading rows -->
                                <tr
                                    class="animate-pulse hover:bg-gray-50 transition-colors duration-200 border-b border-gray-100 hover:-translate-y-0.5 hover:shadow-sm">
                                    <td class="px-4 py-3 transition-all duration-200">
                                        <div class="h-4 bg-gray-200 rounded flex items-center">1</div>
                                    </td>
                                    <td class="px-4 py-3 transition-all duration-200">
                                        <div class="h-4 bg-gray-200 rounded flex items-center">08xxx</div>
                                    </td>
                                    <td class="px-4 py-3 transition-all duration-200">
                                        <div class="h-4 bg-gray-200 rounded flex items-center">Customer</div>
                                    </td>
                                    <td class="px-4 py-3 transition-all duration-200">
                                        <div class="h-4 bg-gray-200 rounded flex items-center">Loading...</div>
                                    </td>
                                    <td class="px-4 py-3 transition-all duration-200">
                                        <div class="h-4 bg-gray-200 rounded flex items-center">⭐⭐⭐⭐⭐</div>
                                    </td>
                                    <td class="px-4 py-3 transition-all duration-200">
                                        <div class="h-4 bg-gray-200 rounded flex items-center">01/01/2024</div>
                                    </td>
                                    <td class="px-4 py-3 transition-all duration-200">
                                        <div class="h-4 bg-gray-200 rounded flex items-center">INV-001</div>
                                    </td>
                                    <td class="px-4 py-3 transition-all duration-200">
                                        <div class="h-4 bg-gray-200 rounded flex items-center">Pelayanan</div>
                                    </td>
                                    <td class="px-4 py-3 transition-all duration-200">
                                        <div class="h-4 bg-gray-200 rounded flex items-center">Cabang</div>
                                    </td>
                                    <td class="px-4 py-3 transition-all duration-200">
                                        <div class="h-4 bg-gray-200 rounded flex items-center">Kasir</div>
                                    </td>
                                    <td class="px-4 py-3 transition-all duration-200">
                                        <div class="h-4 bg-gray-200 rounded flex items-center">📷</div>
                                    </td>
                                    <td class="px-4 py-3 transition-all duration-200">
                                        <div class="h-4 bg-gray-200 rounded flex items-center">⚙️</div>
                                    </td>
                                </tr>
                                <tr
                                    class="animate-pulse hover:bg-gray-50 transition-colors duration-200 border-b border-gray-100 hover:-translate-y-0.5 hover:shadow-sm">
                                    <td class="px-6 py-4 transition-all duration-200">
                                        <div class="h-4 bg-gray-200 rounded"></div>
                                    </td>
                                    <td class="px-6 py-4 transition-all duration-200">
                                        <div class="h-4 bg-gray-200 rounded"></div>
                                    </td>
                                    <td class="px-6 py-4 transition-all duration-200">
                                        <div class="h-4 bg-gray-200 rounded"></div>
                                    </td>
                                    <td class="px-6 py-4 transition-all duration-200">
                                        <div class="h-4 bg-gray-200 rounded"></div>
                                    </td>
                                    <td class="px-6 py-4 transition-all duration-200">
                                        <div class="h-4 bg-gray-200 rounded"></div>
                                    </td>
                                    <td class="px-6 py-4 transition-all duration-200">
                                        <div class="h-4 bg-gray-200 rounded"></div>
                                    </td>
                                    <td class="px-6 py-4 transition-all duration-200">
                                        <div class="h-4 bg-gray-200 rounded"></div>
                                    </td>
                                    <td class="px-6 py-4 transition-all duration-200">
                                        <div class="h-4 bg-gray-200 rounded"></div>
                                    </td>
                                    <td class="px-6 py-4 transition-all duration-200">
                                        <div class="h-4 bg-gray-200 rounded"></div>
                                    </td>
                                    <td class="px-6 py-4 transition-all duration-200">
                                        <div class="h-4 bg-gray-200 rounded"></div>
                                    </td>
                                    <td class="px-6 py-4 transition-all duration-200">
                                        <div class="h-4 bg-gray-200 rounded"></div>
                                    </td>
                                </tr>
                                <tr
                                    class="animate-pulse hover:bg-gray-50 transition-colors duration-200 border-b border-gray-100 hover:-translate-y-0.5 hover:shadow-sm">
                                    <td class="px-6 py-4 transition-all duration-200">
                                        <div class="h-4 bg-gray-200 rounded"></div>
                                    </td>
                                    <td class="px-6 py-4 transition-all duration-200">
                                        <div class="h-4 bg-gray-200 rounded"></div>
                                    </td>
                                    <td class="px-6 py-4 transition-all duration-200">
                                        <div class="h-4 bg-gray-200 rounded"></div>
                                    </td>
                                    <td class="px-6 py-4 transition-all duration-200">
                                        <div class="h-4 bg-gray-200 rounded"></div>
                                    </td>
                                    <td class="px-6 py-4 transition-all duration-200">
                                        <div class="h-4 bg-gray-200 rounded"></div>
                                    </td>
                                    <td class="px-6 py-4 transition-all duration-200">
                                        <div class="h-4 bg-gray-200 rounded"></div>
                                    </td>
                                    <td class="px-6 py-4 transition-all duration-200">
                                        <div class="h-4 bg-gray-200 rounded"></div>
                                    </td>
                                    <td class="px-6 py-4 transition-all duration-200">
                                        <div class="h-4 bg-gray-200 rounded"></div>
                                    </td>
                                    <td class="px-6 py-4 transition-all duration-200">
                                        <div class="h-4 bg-gray-200 rounded"></div>
                                    </td>
                                    <td class="px-6 py-4 transition-all duration-200">
                                        <div class="h-4 bg-gray-200 rounded"></div>
                                    </td>
                                    <td class="px-6 py-4 transition-all duration-200">
                                        <div class="h-4 bg-gray-200 rounded"></div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                </div>
                <!-- Enhanced Pagination Section -->
                <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-t border-gray-200">
                    <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
                        <div class="flex items-center space-x-2">
                            <i class="fas fa-info-circle text-gray-400"></i>
                            <p class="text-gray-600 text-sm font-medium" id="viewData">
                                <span class="animate-pulse h-4 bg-gray-200 rounded w-32 inline-block">Menampilkan
                                    data...</span>
                            </p>
                        </div>
                        <div class="flex flex-wrap gap-2 justify-center sm:justify-end" id="paginationContainer">
                            <!-- Loading pagination -->
                            <div class="animate-pulse flex space-x-2">
                                <div
                                    class="w-8 h-8 bg-gray-200 rounded flex items-center justify-center text-xs text-gray-500">
                                    1</div>
                                <div
                                    class="w-8 h-8 bg-gray-200 rounded flex items-center justify-center text-xs text-gray-500">
                                    2</div>
                                <div
                                    class="w-8 h-8 bg-gray-200 rounded flex items-center justify-center text-xs text-gray-500">
                                    3</div>
                                <div
                                    class="w-8 h-8 bg-gray-200 rounded flex items-center justify-center text-xs text-gray-500">
                                    ...</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Zoom Gambar -->
        <div id="zoomModal" class="fixed inset-0 bg-black/70 flex items-center justify-center z-50 hidden">
            <img id="zoomImage" src="" class="max-h-[90vh] max-w-[90vw] rounded shadow-lg border border-white" />
            <button class="absolute top-4 right-4 text-white text-2xl hover:text-gray-300 transition-colors duration-200"
                onclick="document.getElementById('zoomModal').classList.add('hidden')">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <!-- Modal Penanganan Masalah -->
        <div id="issueHandlingModal"
            class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
            <div
                class="bg-white/95 backdrop-blur-md rounded-2xl shadow-2xl w-full max-w-4xl relative animate-fade-in-up hover:-translate-y-1 hover:shadow-2xl transition-all duration-300 border border-white/20 max-h-[90vh] overflow-hidden">
                <!-- Modal Header with Gradient -->
                <div class="bg-gradient-to-r from-orange-400 via-yellow-400 to-orange-500 rounded-t-2xl p-6">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center">
                                <i class="fas fa-tools text-white text-lg"></i>
                            </div>
                            <div>
                                <h2 class="text-xl font-bold text-white">Penanganan Masalah</h2>
                                <p class="text-white/80 text-sm">Kelola dan tindak lanjuti masalah pelanggan</p>
                            </div>
                        </div>
                        <button type="button" id="closeIssueModal"
                            class="text-white/80 hover:text-white transition-colors duration-200">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>
                </div>

                <!-- Modal Content -->
                <div class="p-6 space-y-6 overflow-y-auto max-h-[calc(90vh-200px)]">
                    <!-- Review Info Section -->
                    <div class="bg-gradient-to-r from-gray-50 to-gray-100 rounded-xl p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                            <i class="fas fa-comment mr-2 text-orange-500"></i>
                            Informasi Review
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label class="block text-sm font-semibold text-gray-700 flex items-center">
                                    <i class="fas fa-user mr-2 text-orange-500"></i>
                                    Nama Pelanggan
                                </label>
                                <div class="text-gray-800 font-medium" id="customerName">-</div>
                            </div>
                            <div class="space-y-2">
                                <label class="block text-sm font-semibold text-gray-700 flex items-center">
                                    <i class="fas fa-phone mr-2 text-orange-500"></i>
                                    No. HP
                                </label>
                                <div class="text-gray-800 font-medium" id="customerPhone">-</div>
                            </div>
                            <div class="space-y-2">
                                <label class="block text-sm font-semibold text-gray-700 flex items-center">
                                    <i class="fas fa-star mr-2 text-orange-500"></i>
                                    Rating
                                </label>
                                <div class="text-gray-800 font-medium" id="reviewRating">-</div>
                            </div>
                            <div class="space-y-2">
                                <label class="block text-sm font-semibold text-gray-700 flex items-center">
                                    <i class="fas fa-calendar mr-2 text-orange-500"></i>
                                    Tanggal Review
                                </label>
                                <div class="text-gray-800 font-medium" id="reviewDate">-</div>
                            </div>
                        </div>
                        <div class="mt-4">
                            <label class="block text-sm font-semibold text-gray-700 flex items-center mb-2">
                                <i class="fas fa-comment-dots mr-2 text-orange-500"></i>
                                Komentar Pelanggan
                            </label>
                            <div class="bg-white p-4 rounded-lg border border-gray-200 text-gray-800" id="reviewComment">-
                            </div>
                        </div>
                    </div>

                    <!-- Issue Handling Form -->
                    <div class="bg-gradient-to-r from-orange-50 to-yellow-50 rounded-xl p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                            <i class="fas fa-clipboard-list mr-2 text-orange-500"></i>
                            Form Penanganan
                        </h3>

                        <form id="issueHandlingForm" class="space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Status Penanganan -->
                                <div class="space-y-2">
                                    <label class="block text-sm font-semibold text-gray-700 flex items-center">
                                        <i class="fas fa-tasks mr-2 text-orange-500"></i>
                                        Status Penanganan
                                    </label>
                                    <select id="handlingStatus" name="status" required
                                        class="block w-full rounded-xl border border-gray-300 px-4 py-3 shadow-sm focus:ring-2 focus:ring-orange-500 focus:border-orange-500 text-sm transition-all duration-200 hover:border-orange-300 hover:-translate-y-0.5 focus:scale-105">
                                        <option value="">Pilih Status</option>
                                        <option value="pending">⏳ Pending</option>
                                        <option value="in_progress">🔄 Sedang Diproses</option>
                                        <option value="resolved">✅ Selesai</option>
                                    </select>
                                </div>

                                <!-- Prioritas -->
                                <div class="space-y-2">
                                    <label class="block text-sm font-semibold text-gray-700 flex items-center">
                                        <i class="fas fa-exclamation-triangle mr-2 text-orange-500"></i>
                                        Prioritas
                                    </label>
                                    <select id="priority" name="priority" required
                                        class="block w-full rounded-xl border border-gray-300 px-4 py-3 shadow-sm focus:ring-2 focus:ring-orange-500 focus:border-orange-500 text-sm transition-all duration-200 hover:border-orange-300 hover:-translate-y-0.5 focus:scale-105">
                                        <option value="">Pilih Prioritas</option>
                                        <option value="low">🟢 Rendah</option>
                                        <option value="medium">🟡 Sedang</option>
                                        <option value="high">🔴 Tinggi</option>
                                        <option value="urgent">🚨 Urgent</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Kategori Masalah -->
                            <div class="space-y-2">
                                <label class="block text-sm font-semibold text-gray-700 flex items-center">
                                    <i class="fas fa-tags mr-2 text-orange-500"></i>
                                    Kategori Masalah
                                </label>
                                <select id="issueCategory" name="category" required
                                    class="block w-full rounded-xl border border-gray-300 px-4 py-3 shadow-sm focus:ring-2 focus:ring-orange-500 focus:border-orange-500 text-sm transition-all duration-200 hover:border-orange-300 hover:-translate-y-0.5 focus:scale-105">
                                    <option value="">Pilih Kategori</option>
                                    <option value="service">👥 Pelayanan</option>
                                    <option value="product">📦 Produk</option>
                                    <option value="payment">💳 Pembayaran</option>
                                    <option value="delivery">🚚 Pengiriman</option>
                                    <option value="technical">🔧 Teknis</option>
                                    <option value="other">📝 Lainnya</option>
                                </select>
                            </div>

                            <!-- Deskripsi Penanganan -->
                            <div class="space-y-2">
                                <label class="block text-sm font-semibold text-gray-700 flex items-center">
                                    <i class="fas fa-edit mr-2 text-orange-500"></i>
                                    Deskripsi Penanganan
                                </label>
                                <textarea id="handlingDescription" name="description" rows="4"
                                    placeholder="Jelaskan langkah-langkah penanganan yang telah dilakukan..."
                                    class="block w-full rounded-xl border border-gray-300 px-4 py-3 shadow-sm focus:ring-2 focus:ring-orange-500 focus:border-orange-500 text-sm transition-all duration-200 hover:border-orange-300 hover:-translate-y-0.5 focus:scale-105 resize-none"></textarea>
                            </div>
                            <!-- Modal Footer -->
                            <div class="">
                                <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
                                    <div class="flex items-center space-x-2 text-sm text-gray-600">
                                        <i class="fas fa-info-circle text-orange-500"></i>
                                        <span>Pastikan semua informasi telah diisi dengan benar</span>
                                    </div>
                                    <div class="flex items-center space-x-3">
                                        <button type="button" id="cancelIssueHandling"
                                            class="px-5 py-2.5 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-all duration-200 text-sm font-medium flex items-center hover:-translate-y-1 hover:shadow-lg">
                                            <i class="fas fa-times mr-2"></i>
                                            Batal
                                        </button>
                                        <button type="submit" form="issueHandlingForm"
                                            class="px-5 py-2.5 bg-gradient-to-r from-orange-500 to-yellow-500 text-white rounded-lg hover:from-orange-600 hover:to-yellow-600 transition-all duration-200 text-sm font-medium flex items-center shadow-sm hover:shadow-md hover:-translate-y-1">
                                            <i class="fas fa-save mr-2"></i>
                                            Simpan
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                
            </div>
        </div>
        <!-- Modal Chat dengan Customer -->
         <div id="chatModal"
            class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
            <div
                class="bg-white/95 backdrop-blur-md rounded-2xl shadow-2xl w-full max-w-2xl relative animate-fade-in-up transition-all duration-300 border border-white/20 max-h-[90vh] flex flex-col">
                <div class="bg-gradient-to-r from-blue-500 to-indigo-600 rounded-t-2xl p-5">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-4">
                            <div class="w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center">
                                <i class="fas fa-comments text-white text-lg"></i>
                            </div>
                            <div>
                                <h2 class="text-xl font-bold text-white">Chat dengan Customer</h2>
                                <p class="text-white/80 text-sm" id="chatCustomerName">-</p>
                            </div>
                        </div>
                        <button type="button" id="closeChatModal"
                            class="text-white/80 hover:text-white transition-colors duration-200">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>
                </div>

                <div id="chatScrollContainer" class="p-4 space-y-4 overflow-y-auto flex-1">
                    <div class="bg-gradient-to-r from-gray-50 to-gray-100 rounded-xl p-4 border border-gray-200">
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <span class="text-gray-600">No. HP:</span>
                                <span class="font-medium text-gray-800 ml-2" id="chatCustomerPhone">-</span>
                            </div>
                            <div>
                                <span class="text-gray-600">Rating:</span>
                                <span class="font-medium text-gray-800 ml-2" id="chatCustomerRating">-</span>
                            </div>
                        </div>
                        <div class="mt-3">
                            <span class="text-gray-600 text-sm">Komentar Awal:</span>
                            <p class="text-gray-800 text-sm mt-1 italic" id="chatCustomerComment">-</p>
                        </div>
                    </div>

                    <div id="chatConversationMessages" class="space-y-3 p-2">
                        <div class="text-center text-gray-400 text-sm py-8">
                            <i class="fas fa-comment-dots text-3xl mb-2"></i>
                            <p>Mulai percakapan dengan customer.</p>
                        </div>
                    </div>
                </div>

                <div class="p-4 border-t border-gray-200 bg-gray-50 rounded-b-2xl">
                    <div class="flex items-start space-x-3">
                        <div class="flex-1">
                            <textarea id="chatMessageInput" rows="2" placeholder="Ketik pesan untuk customer..."
                                class="block w-full rounded-lg border border-gray-300 px-4 py-3 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm resize-none"></textarea>
                        </div>
                        <button type="button" id="sendChatMessageBtn"
                            class="px-5 py-2.5 bg-gradient-to-r from-blue-500 to-indigo-500 text-white rounded-lg hover:from-blue-600 hover:to-indigo-600 transition-all duration-200 shadow-sm hover:shadow-md flex items-center h-full">
                            <i class="fas fa-paper-plane mr-2"></i> Kirim
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>
    <!-- custom js file link  -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../../js/middleware_auth.js"></script>
    <script src="../../js/review/main.js" type="module"></script>
    <script>
        // Sidebar and UI controls only - form handling moved to main.js
        document.getElementById('toggle-sidebar').addEventListener('click', function () {
            document.getElementById('sidebar').classList.toggle('open');
        });
        
        document.addEventListener("DOMContentLoaded", function () {
            const sidebar = document.getElementById("sidebar");
            const closeBtn = document.getElementById("closeSidebar");

            closeBtn.addEventListener("click", function () {
                sidebar.classList.remove("open");
            });
        });
        
        document.getElementById("toggle-hide").addEventListener("click", function () {
            var sidebarTexts = document.querySelectorAll(".sidebar-text");
            let mainContent = document.getElementById("main-content");
            let sidebar = document.getElementById("sidebar");
            var toggleButton = document.getElementById("toggle-hide");
            var icon = toggleButton.querySelector("i");

            if (sidebar.classList.contains("w-64")) {
                sidebar.classList.remove("w-64", "px-5");
                sidebar.classList.add("w-16", "px-2");
                sidebarTexts.forEach(text => text.classList.add("hidden"));
                mainContent.classList.remove("ml-64");
                mainContent.classList.add("ml-16");
                toggleButton.classList.add("left-20");
                toggleButton.classList.remove("left-64");
                icon.classList.remove("fa-angle-left");
                icon.classList.add("fa-angle-right");
            } else {
                sidebar.classList.remove("w-16", "px-2");
                sidebar.classList.add("w-64", "px-5");
                sidebarTexts.forEach(text => text.classList.remove("hidden"));
                mainContent.classList.remove("ml-16");
                mainContent.classList.add("ml-64");
                toggleButton.classList.add("left-64");
                toggleButton.classList.remove("left-20");
                icon.classList.remove("fa-angle-right");
                icon.classList.add("fa-angle-left");
            }
        });

        document.addEventListener("DOMContentLoaded", function () {
            const profileImg = document.getElementById("profile-img");
            const profileCard = document.getElementById("profile-card");

            profileImg.addEventListener("click", function (event) {
                event.preventDefault();
                profileCard.classList.toggle("show");
            });

            document.addEventListener("click", function (event) {
                if (!profileCard.contains(event.target) && !profileImg.contains(event.target)) {
                    profileCard.classList.remove("show");
                }
            });

            // Close zoom modal when clicking outside
            const zoomModal = document.getElementById('zoomModal');
            if (zoomModal) {
                zoomModal.addEventListener('click', function (e) {
                    if (e.target === zoomModal) {
                        zoomModal.classList.add('hidden');
                    }
                });
            }

            // Close zoom modal with Escape key
            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape' && !zoomModal.classList.contains('hidden')) {
                    zoomModal.classList.add('hidden');
                }
            });
        });
    </script>
</body>


</html>