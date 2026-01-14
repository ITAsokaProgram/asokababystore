<?php
// require_once __DIR__ . '/../../component/menu_handler.php';

// $menuHandler = new MenuHandler('top_margin');

// if (!$menuHandler->initialize()) {
//     exit();
// }

// $user_id = $menuHandler->getUserId();
// $logger = $menuHandler->getLogger();
// $token = $menuHandler->getToken();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Top Retur</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/all.min.css">
    <link rel="stylesheet" href="/css/header.css">
    <link rel="stylesheet" href="/css/sidebar.css">
    <link rel="stylesheet" href="/css/animation-fade-in.css">
    <link rel="icon" type="image/png" href="/images/logo1.png">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/src/style/default-font.css">
    <link rel="stylesheet" href="/src/output2.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <script src="https://cdn.jsdelivr.net/npm/toastify-js" defer></script>

    <style>
        .glass-effect {
            background: rgba(255, 255, 255, 0.25);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.18);
        }

        .card-hover {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .card-hover:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }

        .animate-fade-in {
            animation: fadeIn 0.3s ease-in-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: scale(0.95);
            }

            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        .gradient-text {
            background: linear-gradient(135deg, #8B5CF6 0%, #A855F7 50%, #C084FC 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .loading-spinner {
            border: 3px solid #f3f3f3;
            border-top: 3px solid #8B5CF6;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        .progress-bar {
            height: 6px;
            background: linear-gradient(90deg, #8B5CF6, #A855F7);
            border-radius: 3px;
            transition: width 0.3s ease;
        }

        .tooltip {
            position: absolute;
            background: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 12px;
            pointer-events: none;
            z-index: 1000;
            opacity: 0;
            transition: opacity 0.3s;
        }

        .tooltip.show {
            opacity: 1;
        }

        .filter-active {
            background: linear-gradient(135deg, #8B5CF6, #A855F7);
            color: white;
        }

        .table-stripe:nth-child(even) {
            background: rgba(139, 92, 246, 0.05);
        }

        .status-badge {
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
        }

        .status-success {
            background: #DCFCE7;
            color: #166534;
        }

        .status-warning {
            background: #FEF3C7;
            color: #92400E;
        }

        .status-error {
            background: #FEE2E2;
            color: #991B1B;
        }
    </style>
</head>

<body class="bg-gradient-to-br from-slate-50 via-purple-50 to-violet-100 min-h-screen flex">
    <?php include '../../component/navigation_report.php'; ?>
    <?php include '../../component/sidebar_report.php'; ?>

    <main id="main-content"
        class="flex-1 p-8 transition-all duration-300 ml-64 mt-16 font-sans antialiased text-gray-800">
        <div class="max-w-8xl mx-auto">

            <!-- Header Section -->
            <div class="mb-10 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                <div class="flex items-center gap-6">
                    <div class="relative">
                        <span
                            class="inline-flex items-center justify-center w-20 h-20 rounded-3xl bg-gradient-to-br from-violet-500 to-purple-600 shadow-2xl">
                            <i class="fas fa-undo text-white text-4xl"></i>
                        </span>
                        <div
                            class="absolute -top-2 -right-2 w-6 h-6 bg-green-500 rounded-full flex items-center justify-center">
                            <i class="fas fa-check text-white text-xs"></i>
                        </div>
                    </div>
                    <div>
                        <h1 class="text-5xl font-extrabold gradient-text tracking-tight leading-tight mb-2">
                            Retur Transaksi Kasir
                        </h1>
                        <p class="text-gray-600 text-lg font-medium mb-4">Laporan retur transaksi kasir</p>
                        <div class="flex items-center gap-4 text-sm text-gray-500">
                            <span class="flex items-center gap-1">
                                <i class="fas fa-clock"></i>
                                Update terakhir: <span id="last-update">Loading...</span>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center gap-4">
                    <button id="refresh-btn"
                        class="flex items-center gap-2 px-6 py-3 bg-white/80 border border-violet-200 rounded-xl hover:bg-violet-50 transition-all duration-200 shadow-lg">
                        <i class="fas fa-sync-alt"></i>
                        <span>Refresh</span>
                    </button>
                    <button id="export-btn"
                        class="flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-violet-500 to-purple-600 text-white rounded-xl hover:from-violet-600 hover:to-purple-700 transition-all duration-200 shadow-lg">
                        <i class="fas fa-download"></i>
                        <span>Export</span>
                    </button>
                </div>
            </div>



            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
                <div
                    class="relative overflow-hidden bg-white/80 backdrop-blur-md border border-violet-200 rounded-3xl p-6 shadow-lg card-hover">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 mb-1">Retur Hari Ini</p>
                            <p class="text-3xl font-bold text-violet-600" id="stat-retur-today">0</p>
                        </div>
                        <div
                            class="w-12 h-12 rounded-2xl bg-gradient-to-br from-violet-500 to-purple-600 flex items-center justify-center">
                            <i class="fas fa-undo text-white text-xl"></i>
                        </div>
                    </div>
                    <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-violet-500 to-purple-600">
                    </div>
                </div>

                <div
                    class="relative overflow-hidden bg-white/80 backdrop-blur-md border border-violet-200 rounded-3xl p-6 shadow-lg card-hover">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 mb-1">Retur Bulan Ini</p>
                            <p class="text-3xl font-bold text-violet-600" id="stat-retur-month">0</p>
                        </div>
                        <div
                            class="w-12 h-12 rounded-2xl bg-gradient-to-br from-blue-500 to-cyan-600 flex items-center justify-center">
                            <i class="fas fa-calendar text-white text-xl"></i>
                        </div>
                    </div>
                    <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-blue-500 to-cyan-600"></div>
                </div>
            </div>



            <!-- Cashier Cards Section -->
            <div class="mb-10">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-2xl font-bold text-gray-700 flex items-center gap-3">
                        <i class="fas fa-users text-violet-500"></i>
                        Retur Transaksi per Kasir
                    </h2>
                    <div class="flex items-center gap-2">
                        <button id="view-grid" class="p-2 bg-violet-500 text-white rounded-lg">
                            <i class="fas fa-th-large"></i>
                        </button>
                    </div>
                </div>

                <div id="cashier-cards-container"
                    class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    <!-- Loading skeleton -->
                    <div class="animate-pulse">
                        <div class="bg-gray-200 rounded-3xl h-48"></div>
                    </div>
                    <div class="animate-pulse">
                        <div class="bg-gray-200 rounded-3xl h-48"></div>
                    </div>
                    <div class="animate-pulse">
                        <div class="bg-gray-200 rounded-3xl h-48"></div>
                    </div>
                </div>
            </div>

            <!-- Recent Transactions Table -->
            <div class="bg-white/80 backdrop-blur-md border border-violet-200 rounded-3xl p-6 shadow-lg">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-xl font-semibold text-gray-700">Retur Terbaru</h3>
                    <button id="view-all-btn" class="text-violet-500 hover:text-violet-700 font-medium"
                        onclick="window.location.href='invalid_trans.php'">
                        Lihat Semua <i class="fas fa-arrow-right ml-1"></i>
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gradient-to-r from-violet-500 to-purple-600 text-white">
                            <tr>
                                <th class="text-left py-3 px-4 rounded-l-lg">Kode</th>
                                <th class="text-left py-3 px-4">Kasir</th>
                                <th class="text-left py-3 px-4">No. Bon</th>
                                <th class="text-left py-3 px-4">Barang</th>
                                <th class="text-left py-3 px-4">PLU</th>
                                <th class="text-left py-3 px-4">Tanggal</th>
                                <th class="text-left py-3 px-4">Jumlah</th>
                                <th class="text-left py-3 px-4">Cabang</th>
                                <th class="text-left py-3 px-4">Keterangan</th>
                                <th class="text-left py-3 px-4">Status</th>

                            </tr>
                        </thead>
                        <tbody id="recent-transactions">
                            <!-- Loading rows -->
                            <tr class="border-b border-gray-200 animate-pulse">
                                <td class="py-3 px-4">
                                    <div class="h-4 bg-gray-200 rounded"></div>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="h-4 bg-gray-200 rounded"></div>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="h-4 bg-gray-200 rounded"></div>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="h-4 bg-gray-200 rounded"></div>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="h-4 bg-gray-200 rounded"></div>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="h-4 bg-gray-200 rounded"></div>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="h-4 bg-gray-200 rounded"></div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <!-- Enhanced Modal -->
    <div id="modal-detail"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm hidden">
        <div class="bg-white rounded-3xl shadow-2xl w-full max-w-6xl max-h-[90vh] overflow-hidden animate-fade-in">
            <div
                class="bg-gradient-to-r from-violet-500 to-purple-600 text-white p-6 flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-white/20 flex items-center justify-center">
                        <i class="fas fa-info-circle text-white text-xl"></i>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold" id="modal-title">Detail Retur Transaksi</h2>
                        <p class="text-violet-100" id="modal-subtitle">Informasi lengkap retur kasir</p>
                    </div>
                </div>
                <button id="close-modal" class="text-white hover:text-red-200 text-2xl p-2">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="p-6 max-h-[calc(90vh-120px)] overflow-y-auto">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 sticky top-0">
                            <tr>
                                <th class="text-left py-3 px-4 font-semibold text-gray-700">No</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-700">No. Bon</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-700">Barang</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-700">PLU</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-700">Kode</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-700">Kasir</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-700">Tanggal</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-700">Cabang</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-700">Pemeriksa</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-700">Keterangan</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-700">Status</th>
                            </tr>
                        </thead>
                        <tbody id="modal-detail-tbody">
                            <!-- Data akan diisi via JavaScript -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Tooltip -->
    <div id="tooltip" class="tooltip"></div>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="../../js/ui/navbar_toogle.js" type="module"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script src="../../js/invalid_trans/top/retur.js" type="module"></script>
</body>

</html>