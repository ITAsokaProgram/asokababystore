<?php
session_start();
require_once __DIR__ . '/../../component/menu_handler.php';
include '../../../aa_kon_sett.php'; 
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ACC PO Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/all.min.css">
    <link rel="stylesheet" href="../../style/header.css">
    <link rel="stylesheet" href="../../style/sidebar.css">
    <link rel="stylesheet" href="../../output2.css">
    <link rel="stylesheet" href="../../style/pink-theme.css">
    
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.sheetjs.com/xlsx-latest/package/dist/xlsx.full.min.js"></script>

    <style>
        .table-input {
            background-color: transparent;
            border: 1px solid #fce7f3;
            border-radius: 4px;
            padding: 4px;
            width: 100%;
            min-width: 60px;
            text-align: right;
            font-size: 11px;
            transition: all 0.2s;
        }
        .table-input:focus {
            background-color: white;
            border-color: #ec4899;
            outline: none;
            box-shadow: 0 0 0 2px rgba(236, 72, 153, 0.1);
        }
        
        thead th {
            background: linear-gradient(180deg, #fdf2f8 0%, #fce7f3 100%);
            color: #831843;
            font-size: 11px;
            font-weight: 700;
            border: 1px solid #fbcfe8;
        }

        .scrollbar-pink::-webkit-scrollbar { width: 10px; height: 10px; }
        .scrollbar-pink::-webkit-scrollbar-thumb { background: #ec4899; border-radius: 5px; }
        .scrollbar-pink::-webkit-scrollbar-track { background: #fce7f3; }

        .autocomplete-items {
            position: absolute;
            border: 1px solid #fbcfe8;
            border-bottom: none;
            border-top: none;
            z-index: 99;
            top: 100%;
            left: 0;
            right: 0;
            border-radius: 0 0 8px 8px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            max-height: 200px;
            overflow-y: auto;
        }
        .autocomplete-item {
            padding: 10px;
            cursor: pointer;
            background-color: #fff;
            border-bottom: 1px solid #fbcfe8;
            font-size: 11px;
        }
        .autocomplete-item:hover {
            background-color: #fce7f3;
        }
        .disabled-section {
            opacity: 0.5;
            pointer-events: none;
            filter: grayscale(1);
        }
        .branch-checkbox:checked {
            background-color: #ec4899 !important; 
            border-color: #ec4899 !important;
            background-image: none !important;
        }

        .branch-checkbox:checked + i {
            opacity: 1 !important;
        }
    </style>
</head>

<body class="bg-gray-50">
    <?php include '../../component/navigation_report.php' ?>
    <?php include '../../component/sidebar_report.php' ?>

    <main id="main-content" class="flex-1 p-3 ml-64">
        <section class="max-w-[100vw]">
            <div class="header-card p-3 rounded-xl mb-3 bg-white shadow-sm border border-pink-100">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="p-2 bg-gradient-to-br from-pink-500 to-pink-600 rounded-xl text-white shadow-lg shadow-pink-200">
                            <i class="fa-solid fa-file-invoice-dollar fa-lg"></i>
                        </div>
                        <div>
                            <h1  class="text-xl  font-bold text-gray-800">ACC PO / Sisa Stok</h1>
                            <p id="dynamic-subtitle" class=" text-gray-600"></p>
                        </div>
                    </div>
                </div>
            </div>

            <style id="dynamic-column-styles"></style>

            <div class="bg-white p-3 rounded-2xl shadow-md border border-pink-100 mb-4">
                <form id="filter-form" class="space-y-2">
                    <div class="flex flex-wrap gap-2 items-end">
                        
                        <div class="flex-1 min-w-[250px] relative">
                            <label class="text-xs font-bold text-gray-700 block mb-1">Supplier</label>
                            <div class="relative">
                                <input type="text" id="input-supplier" class="w-full text-xs border border-pink-200 rounded-lg p-2 focus:ring-pink-500 focus:border-pink-500" placeholder="Ketik Kode / Nama Supplier..." autocomplete="off">
                                <input type="hidden" name="kode_supp" id="kode_supp_val">

                                <div id="supplier-list" class="autocomplete-items hidden"></div>
                            </div>
                        </div>

                        <div class="flex-1 min-w-[200px]">
                            <label class="text-xs font-bold text-gray-700 block mb-1">Filter Area</label>
                            <select name="kode_area" id="select-area" class="w-full text-xs border border-pink-200 rounded-lg p-2 focus:ring-pink-500 bg-white">
                                <option value="">-- Pilih Area --</option>
                            </select>
                        </div>

                        <div class="flex-1 min-w-[250px] relative" id="section-cabang">
                            <label class="text-xs font-bold text-gray-700 block mb-1">Filter Cabang</label>
                            
                            <div class="relative">
                                <button type="button" id="branch-dropdown-trigger" class="w-full text-left bg-white border border-pink-200 text-gray-700 rounded-lg py-2 px-3 flex justify-between items-center focus:outline-none focus:ring-2 focus:ring-pink-500 focus:border-pink-500 transition-all shadow-sm">
                                    <span id="branch-selected-label" class="text-xs font-medium truncate block">-- Pilih Cabang --</span>
                                    <i class="fas fa-chevron-down text-pink-400 text-[10px] transition-transform duration-200" id="branch-dropdown-icon"></i>
                                </button>

                                <div id="branch-dropdown-menu" class="hidden absolute z-50 w-full mt-1 bg-white border border-pink-100 rounded-lg shadow-xl ring-1 ring-black ring-opacity-5 origin-top transform transition-all">
                                    
                                    <div class="p-2 border-b border-pink-50 bg-pink-50/30 rounded-t-lg">
                                        <div class="relative mb-2">
                                            <input type="text" id="branch-search" placeholder="Cari nama cabang..." class="w-full text-[11px] border border-pink-200 rounded px-2 py-1.5 pl-7 focus:outline-none focus:border-pink-400 focus:ring-1 focus:ring-pink-200">
                                        </div>
                                        <div class="flex justify-between items-center px-1">
                                            <button type="button" id="btn-select-all" class="text-[10px] text-pink-600 font-bold hover:text-pink-800 transition-colors">
                                                <i class="fas fa-check-double mr-1"></i>Pilih Semua
                                            </button>
                                            <button type="button" id="btn-deselect-all" class="text-[10px] text-gray-400 hover:text-red-500 transition-colors">
                                                Reset
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <div class="max-h-[200px] overflow-y-auto scrollbar-pink p-1 bg-white rounded-b-lg" id="branch-container">
                                        <p class="text-[10px] text-gray-400 text-center py-4">Memuat data...</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="relative">
                            <button type="button" id="btn-col-toggle" class="text-xs bg-white border border-pink-300 text-pink-600 px-3 py-2 rounded-lg hover:bg-pink-50 transition-all whitespace-nowrap shadow-sm">
                                <i class="fas fa-columns mr-1"></i> Atur Kolom
                            </button>
                            <div id="col-menu" class="hidden absolute right-0 mt-2 w-48 bg-white border border-pink-200 rounded-lg shadow-xl z-50 max-h-[300px] overflow-y-auto scrollbar-pink p-2">
                                <div class="text-[10px] font-bold text-gray-400 mb-2 uppercase border-b pb-1">Pilih Kolom</div>
                                <div id="col-list-container" class="space-y-1">
                                    </div>
                            </div>
                        </div>

                        <div class="flex gap-2 ml-auto">
                            <button type="button" id="btn-reset-form" class="text-xs bg-gray-100 px-4 py-2 rounded-lg hover:bg-gray-200 text-gray-600 whitespace-nowrap">
                                <i class="fas fa-undo mr-1"></i> Reset
                            </button>
                            <button type="submit" id="btn-submit" class="text-xs bg-gradient-to-r from-pink-500 to-pink-600 text-white px-6 py-2 rounded-lg shadow-md hover:shadow-lg transition-all whitespace-nowrap" disabled>
                                <i class="fas fa-search mr-1"></i> Tampilkan
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <div id="result-container" class="bg-white rounded-2xl shadow-md border border-pink-100 hidden">
                <div class="p-3 border-b border-pink-100 flex justify-between items-center bg-pink-50/50">
                    <h3 class="text-xs font-bold text-pink-700">Data Sisa Stok & PO</h3>
                    <div class="flex gap-2">
                        <span id="total-badge" class="px-2 py-1 bg-pink-100 text-pink-700 text-[10px] rounded-full font-bold border border-pink-200">0 Items</span>

                    </div>
                </div>

                <div class="overflow-auto scrollbar-pink" style="max-height: 640px;">
                    <table id="acc-po-table" class="w-full border-collapse">
                        <thead class="sticky top-0 z-20 shadow-sm" id="table-head">
                        </thead>
                        <tbody id="table-body" class="bg-white divide-y divide-pink-50">
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </main>

    <script src="../../js/acc_po/index_handler.js" type="module"></script>
</body>
</html>