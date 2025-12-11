<?php
session_start();
include '../../../aa_kon_sett.php';
require_once __DIR__ . '/../../component/menu_handler.php';
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Stock Cabang</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/all.min.css">
    <link rel="stylesheet" href="../../style/header.css">
    <link rel="stylesheet" href="../../style/sidebar.css">
    <link rel="stylesheet" href="../../style/animation-fade-in.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../style/default-font.css">
    <link rel="stylesheet" href="../../output2.css">
    <link rel="stylesheet" href="../../style/pink-theme.css">
    <link rel="icon" type="image/png" href="../../../public/images/logo1.png">

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.sheetjs.com/xlsx-latest/package/dist/xlsx.full.min.js"></script>

    <style>
        /* Enhanced Pink Theme Styling */
        .branch-checkbox:checked+div {
            background: linear-gradient(135deg, #fce7f3 0%, #fbcfe8 100%);
            border-color: #ec4899;
            box-shadow: 0 0 0 3px rgba(236, 72, 153, 0.1);
        }

        .branch-checkbox:checked+div .branch-icon {
            background: linear-gradient(135deg, #ec4899 0%, #db2777 100%);
            color: white;
        }

        .branch-card {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .branch-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(236, 72, 153, 0.1), transparent);
            transition: left 0.5s;
        }

        .branch-card:hover::before {
            left: 100%;
        }

        .branch-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 16px -4px rgba(236, 72, 153, 0.2);
            border-color: #f9a8d4;
        }

        /* Custom Scrollbar Pink Theme */
        .scrollbar-pink::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        .scrollbar-pink::-webkit-scrollbar-track {
            background: #fce7f3;
            border-radius: 10px;
        }

        .scrollbar-pink::-webkit-scrollbar-thumb {
            background: linear-gradient(180deg, #ec4899 0%, #db2777 100%);
            border-radius: 10px;
        }

        .scrollbar-pink::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(180deg, #db2777 0%, #be185d 100%);
        }

        /* Animated Gradient Background */
        .gradient-box {
            background: linear-gradient(135deg, #ffffff 0%, #fdf2f8 100%);
            position: relative;
        }

        /* Enhanced Button Styles */
        .btn-pink-gradient {
            background: linear-gradient(135deg, #ec4899 0%, #db2777 100%);
            transition: all 0.3s ease;
        }

        .btn-pink-gradient:hover {
            background: linear-gradient(135deg, #db2777 0%, #be185d 100%);
            transform: translateY(-1px);
            box-shadow: 0 10px 20px -5px rgba(236, 72, 153, 0.4);
        }

        .btn-pink-gradient:active {
            transform: translateY(0);
        }

        /* Badge Animation */
        @keyframes badgePulse {

            0%,
            100% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.05);
            }
        }

        .badge-animate {
            animation: badgePulse 2s ease-in-out infinite;
        }

        /* Section Transition */
        .section-card {
            transition: all 0.3s ease;
        }

        .section-card:hover {
            box-shadow: 0 12px 24px -8px rgba(236, 72, 153, 0.15);
        }

        /* Loading Shimmer Effect */
        @keyframes shimmer {
            0% {
                background-position: -1000px 0;
            }

            100% {
                background-position: 1000px 0;
            }
        }

        .shimmer {
            background: linear-gradient(90deg, #f3f4f6 25%, #fce7f3 50%, #f3f4f6 75%);
            background-size: 1000px 100%;
            animation: shimmer 2s infinite;
        }

        /* Enhanced Table Styles */
        #stock-table thead th {
            background: linear-gradient(180deg, #fdf2f8 0%, #fce7f3 100%);
            font-weight: 700;
        }

        #stock-table tbody tr:hover {
            background: linear-gradient(90deg, #fef3f7 0%, #fce7f3 100%);
        }

        /* Icon Rotation on Hover */
        .icon-rotate:hover i {
            transform: rotate(360deg);
            transition: transform 0.6s ease;
        }

        /* Search Input Enhanced */
        #branch-search:focus {
            box-shadow: 0 0 0 4px rgba(236, 72, 153, 0.1);
        }

        /* Number Badge Style */
        .number-badge {
            background: linear-gradient(135deg, #ec4899 0%, #db2777 100%);
            box-shadow: 0 2px 8px rgba(236, 72, 153, 0.3);
        }
    </style>
</head>

<body class="bg-gray-50">
    <?php include '../../component/navigation_report.php' ?>
    <?php include '../../component/sidebar_report.php' ?>

    <main id="main-content" class="flex-1 p-4 ml-64">
        <section class="">
            <div class="max-w-7xl mx-auto">

                <div class="header-card p-4 rounded-2xl mb-4">
                    <div class="flex items-center justify-between flex-wrap gap-3">
                        <div class="flex items-center gap-3">
                            <div
                                class="p-4 bg-gradient-to-br from-pink-500 to-pink-600 rounded-2xl text-white shadow-xl shadow-pink-200 icon-rotate">
                                <i class="fa-solid fa-boxes-stacked fa-xl"></i>
                            </div>
                            <div>
                                <h1 id="page-title" class="text-xl font-bold text-gray-800 mb-1">Stok Harian
                                </h1>
                                <p id="page-subtitle" class="text-xs text-gray-600">Master Backup</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Enhanced Header -->


                <!-- Enhanced Filter Form -->
                <div class="gradient-box p-6 rounded-3xl shadow-lg border border-pink-100 mb-6 section-card">
                    <form id="filter-form" class="flex flex-col gap-6">
                        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

                            <!-- Branch Selection - Enhanced -->
                            <div class="lg:col-span-7 bg-white rounded-2xl border-2 border-pink-100 p-5 transition-all section-card"
                                id="branch-section">
                                <div
                                    class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-4 gap-3">
                                    <label class="flex items-center gap-2 text-sm font-bold text-gray-800">
                                        <span
                                            class="number-badge text-white w-6 h-6 flex items-center justify-center rounded-full text-xs">1</span>
                                        Pilih Cabang
                                        <span class="text-[10px] font-normal text-gray-400">(Klik untuk memilih)</span>
                                    </label>

                                    <div class="flex gap-2 w-full sm:w-auto">
                                        <div class="relative flex-1 sm:flex-initial">
                                            <i
                                                class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                                            <input type="text" id="branch-search" placeholder="Cari cabang..."
                                                class="pl-10 pr-4 py-4 text-xs border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-pink-500 focus:border-pink-500 w-full sm:w-40 transition-all focus:outline-none">
                                        </div>
                                        <button type="button" id="btn-select-all"
                                            class="text-xs bg-gradient-to-r from-pink-50 to-pink-100 hover:from-pink-100 hover:to-pink-200 text-pink-700 font-medium px-4 py-2.5 rounded-xl transition-all border border-pink-200 whitespace-nowrap">
                                            <i class="fas fa-check-double mr-1"></i> Semua
                                        </button>
                                        <button type="button" id="btn-deselect-all"
                                            class="text-xs bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium px-4 py-2.5 rounded-xl transition-all whitespace-nowrap">
                                            <i class="fas fa-times mr-1"></i> Reset
                                        </button>
                                    </div>
                                </div>

                                <div class="relative border-2 border-gray-200 rounded-xl bg-gradient-to-br from-gray-50 to-white overflow-y-auto scrollbar-pink p-3"
                                    style="max-height: 240px;">
                                    <div id="branch-container"
                                        class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-2">
                                        <div class="col-span-full text-center py-12">
                                            <div class="inline-block shimmer rounded-lg px-8 py-4">
                                                <i class="fas fa-spinner fa-spin mr-2 text-pink-500"></i>
                                                <span class="text-gray-500 text-sm font-medium">Memuat Cabang...</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex justify-between items-center mt-3">
                                    <p class="text-[10px] text-pink-600 italic flex items-center gap-1">
                                        <i class="fas fa-info-circle"></i> Wajib dipilih minimal 1 cabang
                                    </p>
                                    <span id="selected-count"
                                        class="text-xs font-bold text-pink-600 bg-pink-50 px-3 py-1 rounded-full border border-pink-200 badge-animate">
                                        0 Dipilih
                                    </span>
                                </div>
                            </div>

                            <!-- Right Side Actions -->
                            <div class="lg:col-span-5 flex flex-col gap-4 h-full">
                                <!-- Supplier Filter - Enhanced -->
                                <div class="bg-white rounded-2xl border-2 border-pink-100 p-5 flex-1 transition-all section-card"
                                    id="supplier-section">
                                    <label class="flex items-center gap-2 text-sm font-bold text-gray-800 mb-3">
                                        <span
                                            class="number-badge text-white w-6 h-6 flex items-center justify-center rounded-full text-xs">2</span>
                                        Filter Supplier
                                    </label>
                                    <div class="relative">

                                        <select name="kode_supp" id="kode_supp" disabled
                                            class="w-full px-4 py-3 text-sm border-2 border-gray-200 rounded-xl bg-gray-50 cursor-not-allowed transition-all focus:ring-2 focus:ring-pink-500 focus:border-pink-500 focus:outline-none">
                                            <option value="">-- Pilih Cabang Dulu --</option>
                                        </select>
                                    </div>
                                    <p class="text-[11px] text-gray-500 mt-3 flex items-center gap-2"
                                        id="supp-loading-text">
                                        <i class="fas fa-arrow-up text-pink-400"></i>
                                        Pilih cabang untuk memuat supplier
                                    </p>
                                </div>

                                <!-- Action Buttons - Enhanced -->
                                <div class="bg-white rounded-2xl border-2 border-pink-100 p-5 section-card">
                                    <div class="flex flex-col gap-3">
                                        <button type="submit" id="filter-submit-button" disabled
                                            class="w-full btn-pink-gradient text-white font-bold rounded-xl text-sm px-6 py-4 text-center inline-flex items-center justify-center gap-2 shadow-lg disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none">
                                            <i class="fas fa-search"></i>
                                            <span>Cari Stock</span>
                                        </button>

                                        <button type="button" id="reset-button"
                                            class="w-full bg-white text-gray-600 border-2 border-gray-200 hover:bg-gray-50 hover:text-pink-600 hover:border-pink-200 font-medium rounded-xl text-sm px-6 py-3 text-center inline-flex items-center justify-center gap-2 transition-all">
                                            <i class="fas fa-undo-alt"></i>
                                            <span>Reset Filter</span>
                                        </button>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </form>
                </div>

                <!-- Enhanced Results Table -->
                <div class="bg-white rounded-3xl shadow-lg border-2 border-pink-100 hidden section-card"
                    id="result-container">

                    <div
                        class="p-5 border-b-2 border-pink-100 flex justify-between items-center bg-gradient-to-r from-pink-50 to-white">
                        <div class="flex items-center gap-3">
                            <div class="p-2 bg-pink-100 rounded-lg">
                                <i class="fas fa-list text-pink-600"></i>
                            </div>
                            <h3 class="text-sm font-bold text-gray-800">Hasil Pencarian</h3>
                            <span id="total-records-badge"
                                class="hidden px-3 py-1 rounded-full text-xs font-bold bg-gradient-to-r from-pink-100 to-pink-200 text-pink-800 border-2 border-pink-300 badge-animate">
                                0 Item
                            </span>
                        </div>
                        <button onclick="exportToExcel()"
                            class="text-xs bg-gradient-to-r from-green-500 to-green-600 text-white font-bold px-4 py-2 rounded-xl hover:from-green-600 hover:to-green-700 transition-all shadow-md hover:shadow-lg">
                            <i class="fas fa-file-excel mr-1.5"></i> Export Excel
                        </button>
                    </div>

                    <div id="table-scroll-area" class="overflow-x-auto relative overflow-y-auto scrollbar-pink"
                        style="max-height:500px;">
                        <table class="w-full text-sm text-left text-gray-500" id="stock-table">
                            <thead class="text-xs text-gray-700 uppercase sticky top-0 z-20">
                                <tr id="table-headers">
                                </tr>
                            </thead>
                            <tbody id="table-body">
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </section>
    </main>

    <script src="/src/js/middleware_auth.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <script src="../../js/stok/index_handler.js" type="module"></script>
</body>

</html>