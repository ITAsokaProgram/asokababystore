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

    <main id="main-content" class="flex-1 p-3 ml-64">
        <section class="">
            <div class="max-w-7xl mx-auto">

                <div class="header-card p-3 rounded-xl mb-3">
                    <div class="flex items-center justify-between flex-wrap gap-2">
                        <div class="flex items-center gap-2">
                            <div
                                class="p-2.5 bg-gradient-to-br from-pink-500 to-pink-600 rounded-xl text-white shadow-lg shadow-pink-200 icon-rotate">
                                <i class="fa-solid fa-boxes-stacked fa-lg"></i>
                            </div>
                            <div>
                                <h1 id="page-title" class="text-lg font-bold text-gray-800 leading-tight">Stok Harian
                                </h1>
                                <p id="page-subtitle" class="text-[10px] text-gray-600">Master Backup</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="gradient-box p-4 rounded-2xl shadow-md border border-pink-100 mb-4 section-card">
                    <form id="filter-form" class="flex flex-col gap-4">
                        <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 items-start">

                            <div class="lg:col-span-7 bg-white rounded-xl border border-pink-100 p-3 transition-all section-card"
                                id="branch-section">
                                <div
                                    class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-2 gap-2">
                                    <label class="flex items-center gap-2 text-xs font-bold text-gray-800">
                                        <span
                                            class="number-badge text-white w-5 h-5 flex items-center justify-center rounded-full text-[10px]">1</span>
                                        Pilih Cabang
                                    </label>

                                    <div class="flex gap-1 w-full sm:w-auto">
                                        <div class="relative flex-1 sm:flex-initial">
                                            <i
                                                class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-[10px]"></i>
                                            <input type="text" id="branch-search" placeholder="Cari..."
                                                class="pl-8 pr-3 py-1.5 text-[11px] border border-gray-200 rounded-lg focus:ring-1 focus:ring-pink-500 w-full sm:w-32 transition-all focus:outline-none">
                                        </div>
                                        <button type="button" id="btn-select-all"
                                            class="text-[10px] bg-pink-50 text-pink-700 font-medium px-3 py-1.5 rounded-lg border border-pink-200">
                                            Semua
                                        </button>
                                        <button type="button" id="btn-deselect-all"
                                            class="text-[10px] bg-gray-100 text-gray-700 font-medium px-3 py-1.5 rounded-lg border border-gray-200">
                                            Reset
                                        </button>
                                    </div>
                                </div>

                                <div class="relative border border-gray-200 rounded-lg bg-gray-50 overflow-y-auto scrollbar-pink p-2"
                                    style="max-height: 180px;">
                                    <div id="branch-container"
                                        class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-1.5">
                                    </div>
                                </div>
                                <div class="flex justify-between items-center mt-2">
                                    <span id="selected-count"
                                        class="text-[10px] font-bold text-pink-600 bg-pink-50 px-2 py-0.5 rounded-full border border-pink-200">0
                                        Dipilih</span>
                                </div>
                            </div>

                            <div class="lg:col-span-5 flex flex-col gap-3 h-full">
                                <div class="bg-white rounded-xl border border-pink-100 p-3 flex-1 section-card"
                                    id="supplier-section">
                                    <label class="flex items-center gap-2 text-xs font-bold text-gray-800 mb-2">
                                        <span
                                            class="number-badge text-white w-5 h-5 flex items-center justify-center rounded-full text-[10px]">2</span>
                                        Filter Supplier
                                    </label>
                                    <div class="relative">
                                        <select name="kode_supp" id="kode_supp" disabled
                                            class="w-full px-3 py-2 text-xs border border-gray-200 rounded-lg bg-gray-50 cursor-not-allowed focus:ring-1 focus:ring-pink-500 focus:outline-none">
                                            <option value="">-- Pilih Cabang Dulu --</option>
                                        </select>
                                    </div>
                                    <p class="text-[10px] text-gray-500 mt-1 flex items-center gap-1"
                                        id="supp-loading-text">
                                        <i class="fas fa-arrow-up text-pink-400"></i> Pilih cabang
                                    </p>
                                </div>

                                <div class="bg-white rounded-xl border border-pink-100 p-3 section-card">
                                    <div class="flex gap-2"> <button type="submit" id="filter-submit-button" disabled
                                            class="flex-1 btn-pink-gradient text-white font-bold rounded-lg text-xs px-4 py-2.5 shadow-md disabled:opacity-50">
                                            <i class="fas fa-search mr-1"></i> Cari
                                        </button>

                                        <button type="button" id="reset-button"
                                            class="flex-1 bg-white text-gray-600 border border-gray-200 hover:bg-gray-50 font-medium rounded-lg text-xs px-4 py-2.5 transition-all">
                                            <i class="fas fa-undo-alt mr-1"></i> Reset
                                        </button>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </form>
                </div>

                <div class="bg-white rounded-2xl shadow-md border border-pink-100 hidden section-card"
                    id="result-container">
                    <div
                        class="p-3 border-b border-pink-100 flex justify-between items-center bg-gradient-to-r from-pink-50 to-white">
                        <div class="flex items-center gap-2">
                            <div class="p-1.5 bg-pink-100 rounded-md">
                                <i class="fas fa-list text-pink-600 text-xs"></i>
                            </div>
                            <h3 class="text-xs font-bold text-gray-800">Hasil</h3>
                            <span id="total-records-badge"
                                class="hidden px-2 py-0.5 rounded-full text-[10px] font-bold bg-pink-100 text-pink-800 border border-pink-300">0
                                Item</span>
                        </div>
                        <button onclick="exportToExcel()"
                            class="text-[10px] bg-green-500 text-white font-bold px-3 py-1.5 rounded-lg shadow-sm hover:bg-green-600">
                            <i class="fas fa-file-excel mr-1"></i> Excel
                        </button>
                    </div>

                    <div id="table-scroll-area" class="overflow-x-auto relative overflow-y-auto scrollbar-pink"
                        style="max-height:450px;">
                        <table class="w-full text-xs text-left text-gray-500" id="stock-table">
                            <thead class="text-[10px] text-gray-700 uppercase sticky top-0 z-20">
                                <tr id="table-headers"></tr>
                            </thead>
                            <tbody id="table-body"></tbody>
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