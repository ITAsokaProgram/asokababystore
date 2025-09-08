<?php
include '../../../aa_kon_sett.php';
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
require_once __DIR__ . '/../../component/menu_handler.php';

$menuHandler = new MenuHandler('laporan_penjualan_subdept');

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
    <title>Penjualan Sub Dept</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/all.min.css">
    <link rel="stylesheet" href="../../style/sidebar.css">
    <link rel="stylesheet" href="../../style/header.css">
    <link rel="stylesheet" href="../../style/animation-fade-in.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.13/flatpickr.min.css">
    <link rel="icon" type="image/png" href="../../../../public/images/logo1.png">
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="../../style/default-font.css">
    <!-- <link rel="stylesheet" href="../../style/output.css"> -->
    <link rel="stylesheet" href="../../output2.css">


    <style>
        .btn.active {
            background-color: transparent;
            /* background tidak diisi */
            color: #ec4899;
            /* warna teks bisa disesuaikan */
            outline: 2px solid #ec4899;
            outline-offset: 1px;
        }

        .w-0 {
            width: 0 !important;
            overflow: hidden;
        }

        /* --- Enhanced Table & Modal Styles --- */
        table th,
        table td {
            border: 1px solid #e0e7ef;
            padding: 1rem 1.25rem;
            text-align: left;
            vertical-align: middle;
        }

        table thead th {
            background: linear-gradient(to right, #3b82f6, #1e40af);
            color: #fff;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 0.9rem;
            border-bottom: 2px solid #bae6fd;
            padding: 1rem 1.25rem;
        }

        table tbody tr:hover {
            background: #f0f9ff;
            transition: background 0.2s;
        }

        .modal-glass {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(8px);
            border-radius: 1.25rem;
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.18);
            border: 1.5px solid #bae6fd;
            padding: 2rem;
        }

        .modal-header-flex {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .modal-x-btn {
            color: #3b82f6;
            background: rgba(255, 255, 255, 0.8);
            border-radius: 9999px;
            padding: 0.5rem 0.7rem;
            font-size: 1.5rem;
            box-shadow: 0 2px 8px 0 #bae6fd;
            transition: color 0.2s, background 0.2s;
            border: none;
        }

        .modal-x-btn:hover {
            color: #1e40af;
            background: #e0e7ef;
        }

        .modal-x-btn:focus {
            outline: 2px solid #3b82f6;
        }
    </style>
</head>

<body class="bg-gray-100 justify-center overflow-auto">
    <!-- Header Navigation -->
    <?php include '../../component/navigation_report.php'; ?>
    <!-- Sidebar -->
    <?php include '../../component/sidebar_report.php'; ?>

    <main id="main-content" class="flex-1 p-8 transition-all duration-300 ml-64 bg-gray-100">
        <div class="flex justify-center items-center">
            <form id="laporanForm" method="POST"
                class="max-w-2xl grid gap-6 p-8 bg-white/80 backdrop-blur rounded-2xl shadow-xl border border-blue-100 mt-14 animate-fade-in-up">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <input type="hidden" id="subdept" name="subdept">
                <input type="hidden" id="kode_supp" name="kode_supp">
                <input type="hidden" id="kd_store" name="kd_store">
                <h2 class="text-2xl font-bold mt-5 text-blue-700 flex items-center gap-2"><i
                        class="fa fa-chart-bar"></i> Laporan Penjualan Sub Departemen</h2>
                <!-- Grid utama -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Pilihan Cabang -->
                    <div class="flex flex-col">
                        <label for="cabang" class="text-blue-700 font-medium mb-2">Cabang:</label>
                        <select id="cabang" name="cabang"
                            class="w-full px-5 py-3 h-[50px] border border-blue-100 rounded-xl shadow-sm focus:ring-2 focus:ring-blue-300 focus:border-blue-400 transition-all duration-200">
                            
                        </select>
                    </div>

                    <!-- Input Tanggal Awal -->
                    <div class="flex flex-col">
                        <label for="date" class="text-blue-700 font-medium mb-2">Tanggal Awal:</label>
                        <input type="text" id="date" name="start_date"
                            class="w-full px-5 py-3 border border-blue-100 rounded-xl shadow-sm focus:ring-2 focus:ring-blue-300 focus:border-blue-400 transition-all duration-200"
                            placeholder="Pilih Tanggal" readonly>
                    </div>

                    <!-- Input Tanggal Akhir -->
                    <div class="flex flex-col">
                        <label for="date1" class="text-blue-700 font-medium mb-2">Tanggal Akhir:</label>
                        <input type="text" id="date1" name="end_date"
                            class="w-full px-5 py-3 border border-blue-100 rounded-xl shadow-sm focus:ring-2 focus:ring-blue-300 focus:border-blue-400 transition-all duration-200"
                            placeholder="Pilih Tanggal" readonly>
                    </div>
                </div>
                <button type="submit"
                    class="bg-gradient-to-r from-blue-500 to-blue-700 hover:from-blue-600 hover:to-blue-800 text-white font-bold py-3 px-6 rounded-xl shadow-md transition-all duration-200 cursor-pointer w-full text-lg flex items-center justify-center gap-2 hover:scale-105"
                    id="btn-submit" name="query_type" value="query1">
                    <i class="fa fa-search"></i> Cek Data
                </button>
                <!-- Tombol tersembunyi -->
                <button type="submit" style="display: none;" id="btn-sub" name="query_type" value="query2"></button>
                <button type="submit" style="display: none;" id="btn-bar" name="query_type" value="query3"></button>
                <button type="submit" style="display: none;" id="btn-promo" name="query_type" value="query4"></button>
            </form>
        </div>

        <div id="pie" class="w-full mx-auto mt-4">
            <div class="bg-white/90 backdrop-blur rounded-2xl shadow-xl border border-blue-100 p-8 animate-fade-in-up">
                <div class="flex flex-row justify-between items-center">
                    <h1 id="label-chart" class="text-xl font-bold text-blue-700 flex items-center gap-2"><i
                            class="fa fa-chart-pie"></i> <span></span></h1>
                    <div class="relative group justify-end flex items-center gap-2">
                        <label for="sort-by" class="text-blue-700 font-medium">Filter:</label>
                        <select id="sort-by"
                            class="border border-blue-100 rounded-xl px-4 py-2 shadow-sm focus:ring-2 focus:ring-blue-300 focus:border-blue-400 transition-all duration-200">
                            <option value="Total">Rupiah</option>
                            <option value="Qty" selected>Qty</option>
                        </select>
                        <button id="btn-see-data" name="query_type" value="query1"
                            class="w-14 h-14 flex items-center justify-center bg-gradient-to-r from-blue-500 to-blue-700 text-white rounded-xl shadow-lg text-2xl cursor-pointer transition-all duration-200 hover:scale-105">
                            <i class="fa fa-table"></i>
                        </button>
                        <button id="btn-see-supplier" name="query_type" value="query2"
                            class="w-14 h-14 flex items-center justify-center bg-gradient-to-r from-blue-500 to-blue-700 text-white rounded-xl shadow-lg text-2xl cursor-pointer transition-all duration-200 hover:scale-105">
                            <i class="fa fa-table"></i>
                        </button>
                        <span
                            class="absolute bottom-full mb-2 left-1/2 -translate-x-1/2 whitespace-nowrap px-2 py-1 text-sm text-white bg-gray-800 rounded opacity-0 transition-opacity duration-300 group-hover:opacity-100">
                            Lihat Tabel
                        </span>
                    </div>
                </div>
                <div class="flex flex-col justify-start mb-4">
                    <button id="btn-back" name="query_type"
                        class="w-12 h-12 flex items-center justify-center bg-white shadow-lg rounded-full text-blue-700 text-2xl cursor-pointer transition-all duration-200 hover:bg-blue-100 hover:text-blue-900"><i
                            class="fa-solid fa-angle-left pointer-events-none"></i></button>
                </div>
                <div class="flex justify-center items-center w-full">
                    <div id="chartDiagram"
                        class="w-full max-w-6xl h-[300px] sm:h-[400px] md:h-[500px] lg:h-[600px] xl:h-[700px] 2xl:h-[800px] mx-auto">
                    </div>
                </div>

            </div>
        </div>


        <div id="bar"
            class="w-full bg-white/90 backdrop-blur rounded-2xl shadow-xl border border-blue-100 mt-10 animate-fade-in-up">
            <div class="bg-white/90 backdrop-blur rounded-2xl shadow-xl p-8 overflow-x-auto">
                <div class="flex flex-row justify-between items-center p-5">
                    <h1 id="label-chart1" class="text-xl font-bold text-blue-700 flex items-center gap-2"><i
                            class="fa fa-chart-bar"></i> <span></span></h1>
                    <div class="relative group justify-end flex items-center gap-2">
                        <button id="btn-see-penjualan" name="query_type" value="query4"
                            class="w-14 h-14 flex items-center justify-center bg-gradient-to-r from-blue-500 to-blue-700 text-white rounded-xl shadow-lg text-2xl cursor-pointer transition-all duration-200 hover:scale-105">
                            <i class="fa fa-table"></i>
                        </button>
                        <span
                            class="absolute bottom-full mb-2 left-1/2 -translate-x-1/2 whitespace-nowrap px-2 py-1 text-sm text-white bg-gray-800 rounded opacity-0 transition-opacity duration-300 group-hover:opacity-100">
                            Lihat Tabel
                        </span>
                    </div>
                </div>
                <div class="flex flex-row mb-2 justify-end items-center gap-2">
                    <label for="sort-by1" class="text-blue-700 font-medium">Filter:</label>
                    <select id="sort-by1"
                        class="border border-blue-100 rounded-xl px-4 py-2 shadow-sm focus:ring-2 focus:ring-blue-300 focus:border-blue-400 transition-all duration-200">
                        <option value="Total">Rupiah</option>
                        <option value="Qty" selected>Qty</option>
                    </select>
                </div>
                <div class="flex flex-row justify-start gap-2 mb-4">
                    <button id="btn-back" name="query_type"
                        class="w-12 h-12 flex items-center justify-center bg-white shadow-lg rounded-full text-blue-700 text-2xl cursor-pointer transition-all duration-200 hover:bg-blue-100 hover:text-blue-900"><i
                            class="fa-solid fa-angle-left pointer-events-none"></i></button>
                    <div class="relative group">
                        <button id="btn-see-promo" name="query_type" value="query3"
                            class="w-12 h-12 flex items-center justify-center bg-gradient-to-r from-blue-500 to-blue-700 text-white rounded-xl shadow-lg text-xl cursor-pointer transition-all duration-200 hover:scale-105">
                            <i class="fa-solid fa-tags pointer-events-none"></i>
                        </button>
                        <span
                            class="absolute bottom-full mb-2 left-1/2 -translate-x-1/2 whitespace-nowrap px-2 py-1 text-sm text-white bg-gray-800 rounded opacity-0 transition-opacity duration-300 group-hover:opacity-100">
                            Lihat Promo
                        </span>
                    </div>
                </div>
                <div class="flex justify-center items-center min-w-[600px]">
                    <div id="barDiagram" class="w-full h-[700px] md:h-[550px] lg:h-[500px] xl:h-[600px]"></div>
                </div>
            </div>
        </div>
        <!-- Modal -->
        <div id="table-modal" tabindex="-1"
            class="fixed inset-0 bg-black/50 backdrop-blur-sm hidden flex items-center justify-center transition-all ease-in duration-500 opacity-0 scale-95 mt-10">
            <div
                class="relative p-4 w-full max-w-7xl max-h-full transform transition-all ease-in duration-1000 scale-95">
                <!-- Modal content -->
                <div class="modal-glass overflow-hidden">
                    <div class="modal-header-flex">
                        <h3 class="text-xl font-bold text-blue-700 flex items-center gap-2"><i class="fa fa-table"></i>
                            Data Details</h3>
                        <button id="close-modal" class="modal-x-btn"><i
                                class="fa-solid fa-xmark pointer-events-none"></i></button>
                    </div>
                    <!-- Modal body -->
                    <div id="container-table"
                        class="bg-white/90 shadow-md rounded-2xl overflow-hidden max-w-7xl mx-auto">
                        <div
                            class="bg-gradient-to-r from-blue-500 to-blue-700 text-white p-4 rounded-t-lg flex flex-col justify-between items-center md:flex-row">
                            <h3 id="reportHeader" class="text-lg font-semibold text-white mb-5"></h3>
                            <div class="flex items-center">
                                <input type="text" id="searchInput" placeholder="Cari data..."
                                    class="px-4 py-2 text-gray-700 rounded-md bg-white border border-gray-300 focus:ring focus:ring-blue-300"
                                    onkeyup="searchTable('salesTable')">
                                <button onclick="exportToExcel()"
                                    class="ml-4 cursor-pointer px-4 py-2 bg-green-500 text-white rounded hover:bg-green-700 transition ease-in duration-300">
                                    <i class="fa-regular fa-file-excel fa-2px"></i>
                                </button>
                                <button onclick="exportToPDF()"
                                    class="ml-2 cursor-pointer px-4 py-2 bg-red-500 text-white rounded hover:bg-red-700 transition ease-in duration-300">
                                    <i class="fa-regular fa-file-pdf fa-2px"></i>
                                </button>
                            </div>
                        </div>
                        <div class="max-h-[500px] overflow-y-auto overflow-x-auto">
                            <table id="salesTable" class="w-full  border border-collapse">
                                <thead class="text-gray-700 uppercase text-sm bg-blue-700 sticky top-0">
                                    <tr class="bg-gradient-to-r from-blue-500 to-blue-700 text-white p-4 rounded-t-lg">
                                        <th class="py-4 px-6 text-center">TOP</th>
                                        <th class="py-4 px-6 text-left">SUB DEPT</th>
                                        <th class="py-4 px-6 text-center">QTY</th>
                                        <th class="py-4 px-6 text-center">TOTAL</th>
                                    </tr>
                                </thead>
                                <tbody class="text-gray-800 leading-relaxed border border-gray-300">
                                    <!-- Isi tabel -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Modal -->
        <div id="table-modal-supplier" tabindex="-1"
            class="fixed inset-0 bg-black/50 backdrop-blur-sm hidden flex items-center justify-center transition-all ease-in duration-500 opacity-0 scale-95 mt-10">
            <div
                class="relative p-4 w-full max-w-7xl max-h-full transform transition-all ease-in duration-1000 scale-95">
                <!-- Modal content -->
                <div class="modal-glass overflow-hidden">
                    <div class="modal-header-flex">
                        <h3 class="text-xl font-bold text-blue-700 flex items-center gap-2"><i class="fa fa-table"></i>
                            Data Details</h3>
                        <button id="close-modal-supplier" class="modal-x-btn"><i
                                class="fa-solid fa-xmark pointer-events-none"></i></button>
                    </div>
                    <!-- Modal body -->
                    <div id="container-table"
                        class="bg-white/90 shadow-md rounded-2xl overflow-hidden max-w-7xl mx-auto">
                        <div
                            class="bg-gradient-to-r from-blue-500 to-blue-700 text-white p-4 rounded-t-lg flex flex-col md:flex-row  justify-between items-center">
                            <h3 id="reportHeaderSupplier" class="text-lg font-semibold text-white mb-5"></h3>
                            <div class="flex items-center">
                                <input type="text" id="searchInputSupplier" placeholder="Cari data..."
                                    class="px-4 py-2 text-gray-700 rounded-md bg-white border border-gray-300 focus:ring focus:ring-blue-300"
                                    onkeyup="searchTableSupplier('salesTableSupplier')">
                                <button onclick="exportToExcel()"
                                    class="ml-4 px-4 cursor-pointer py-2 bg-green-500 text-white rounded hover:bg-green-700 transition ease-in duration-300">
                                    <i class="fa-regular fa-file-excel fa-2px"></i>
                                </button>
                                <button onclick="exportToPDF()"
                                    class="ml-2 px-4 py-2 cursor-pointer bg-red-500 text-white rounded hover:bg-red-700 transition ease-in duration-300">
                                    <i class="fa-regular fa-file-pdf fa-2px"></i>
                                </button>
                            </div>
                        </div>
                        <div class="max-h-[500px] overflow-y-auto overflow-x-auto">
                            <table id="salesTableSupplier" class="w-full  border border-collapse">
                                <thead class="text-gray-700 uppercase text-sm bg-blue-700 sticky top-0">
                                    <tr class="bg-gradient-to-r from-blue-500 to-blue-700 text-white p-4 rounded-t-lg">
                                        <th class="py-4 px-6 text-center">TOP</th>
                                        <th class="py-4 px-6 text-left">SUPPLIER</th>
                                        <th class="py-4 px-6 text-center">QTY</th>
                                        <th class="py-4 px-6 text-center">TOTAL</th>
                                    </tr>
                                </thead>
                                <tbody class="text-gray-800 leading-relaxed border border-gray-300">
                                    <!-- Isi tabel -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Modal -->
        <div id="promo-modal" tabindex="-1"
            class="fixed inset-0 bg-black/50 backdrop-blur-sm hidden flex items-center justify-center transition-all ease-in duration-500 opacity-0 scale-95 mt-10">
            <div
                class="relative p-4 w-full max-w-7xl max-h-full transform transition-all ease-in duration-1000 scale-95">
                <!-- Modal content -->
                <div class="modal-glass overflow-hidden">
                    <div class="modal-header-flex">
                        <h3 class="text-xl font-bold text-blue-700 flex items-center gap-2"><i class="fa fa-tags"></i>
                            Promo Details</h3>
                        <button id="close-modal-promo" class="modal-x-btn"><i
                                class="fa-solid fa-xmark pointer-events-none"></i></button>
                    </div>
                    <!-- Modal body -->
                    <div id="container-table-promo"
                        class="bg-white/90 shadow-md rounded-2xl overflow-hidden max-w-7xl mx-auto">
                        <div
                            class="bg-gradient-to-r from-blue-500 to-blue-700 text-white p-4 rounded-t-lg flex flex-col md:flex-row justify-between items-center">
                            <h3 id="reportHeaderPromo" class="text-lg font-semibold text-white mb-5"></h3>
                            <div class="flex items-center">
                                <input type="text" id="searchInputPromo" placeholder="Cari data..."
                                    class="px-4 py-2 text-gray-700 bg-white rounded-md border border-gray-300 focus:ring focus:ring-blue-300"
                                    onkeyup="searchTablePromo('salesTablePromo')">
                                <button onclick="exportToExcelModal()"
                                    class="ml-4 px-4 py-2 cursor-pointer bg-green-500 text-white rounded hover:bg-green-700 transition ease-in duration-300">
                                    <i class="fa-regular fa-file-excel fa-2px"></i>
                                </button>
                                <button onclick="exportToPDFModal()"
                                    class="ml-2 px-4 py-2 cursor-pointer bg-red-500 text-white rounded hover:bg-red-700 transition ease-in duration-300">
                                    <i class="fa-regular fa-file-pdf fa-2px"></i>
                                </button>
                            </div>
                        </div>
                        <div class="max-h-[500px] overflow-y-auto overflow-x-auto">
                            <table id="salesTablePromo" class="w-full  border border-collapse">
                                <thead class="text-gray-700 uppercase text-sm bg-blue-700 sticky top-0">
                                    <tr class="bg-gradient-to-r from-blue-500 to-blue-700 text-white p-4 rounded-t-lg">
                                        <th class="py-4 px-6 text-center">TOP</th>
                                        <th class="py-4 px-6 text-left">PROMO</th>
                                        <th class="py-4 px-6 text-center">QTY</th>
                                        <th class="py-4 px-6 text-left">TOTAL</th>
                                    </tr>
                                </thead>
                                <tbody class="text-gray-800 leading-relaxed border border-gray-300">
                                    <!-- Isi tabel -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Modal -->
        <div id="penjualan-terbaik" tabindex="-1"
            class="fixed inset-0 bg-black/50 backdrop-blur-sm hidden flex items-center justify-center transition-all ease-in duration-500 opacity-0 scale-95 mt-10">
            <div
                class="relative p-4 w-full max-w-7xl max-h-full transform transition-all ease-in duration-1000 scale-95">
                <!-- Modal content -->
                <div class="modal-glass overflow-hidden">
                    <div class="modal-header-flex">
                        <h3 class="text-xl font-bold text-blue-700 flex items-center gap-2"><i class="fa fa-trophy"></i>
                            Best Penjualan</h3>
                        <button id="close-modal-penjualan" class="modal-x-btn"><i
                                class="fa-solid fa-xmark pointer-events-none"></i></button>
                    </div>
                    <!-- Modal body -->
                    <div id="container-table-penjualan"
                        class="bg-white/90 shadow-md rounded-2xl overflow-hidden max-w-7xl mx-auto">
                        <div
                            class="bg-gradient-to-r from-blue-500 to-blue-700 text-white p-4 rounded-t-lg flex flex-col md:flex-row justify-between items-center">
                            <h3 id="reportHeaderPenjualan" class="text-lg font-semibold text-white mb-5"></h3>
                            <div class="flex items-center">
                                <input type="text" id="searchInputPenjualan" placeholder="Cari data..."
                                    class="px-4 py-2 bg-white text-gray-700 rounded-md border border-gray-300 focus:ring focus:ring-blue-300"
                                    onkeyup="searchTablePenjualan('salesTablePenjualan')">
                                <button onclick="exportToExcelModalPenjualan()"
                                    class="ml-4 px-4 py-2 cursor-pointer bg-green-500 text-white rounded hover:bg-green-700 transition ease-in duration-300">
                                    <i class="fa-regular fa-file-excel fa-2px"></i>
                                </button>
                                <button onclick="exportPDFModalPenjualan()"
                                    class="ml-2 px-4 py-2 cursor-pointer bg-red-500 text-white rounded hover:bg-red-700 transition ease-in duration-300">
                                    <i class="fa-regular fa-file-pdf fa-2px"></i>
                                </button>
                            </div>
                        </div>
                        <div class="max-h-[500px] overflow-y-auto overflow-x-auto">
                            <table id="salesTablePenjualan" class="w-full  border border-collapse">
                                <thead class="text-gray-700 uppercase text-sm bg-blue-700 sticky top-0">
                                    <tr class="bg-gradient-to-r from-blue-500 to-blue-700 text-white p-4 rounded-t-lg">
                                        <th class="py-4 px-6 text-center">TOP</th>
                                        <th class="py-4 px-6 text-center">BARCODE</th>
                                        <th class="py-4 px-6 text-left" id="thHeadPenjualan"></th>
                                        <th class="py-4 px-6 text-center">QTY</th>
                                        <th class="py-4 px-6 text-center">TOTAL</th>
                                    </tr>
                                </thead>
                                <tbody class="text-gray-800 leading-relaxed border border-gray-300">
                                    <!-- Isi tabel -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <!-- custom js file link -->
    <!-- Tambahkan pustaka Bootstrap Datepicker -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.13/flatpickr.min.js"></script>
    </script>
    <script src="https://cdn.jsdelivr.net/npm/echarts/dist/echarts.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/echarts/5.4.3/echarts.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/echarts@5.4.3/dist/echarts.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="../../js/in_sc_laporan_sub_dept.js"></script>
    <!-- <script src="../../js/report/subdept/main.js" type="module"></script> -->
    <script src="../../js/middleware_auth.js"></script>
    <script src="https://unpkg.com/exceljs/dist/exceljs.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.29/jspdf.plugin.autotable.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>

        document.getElementById("btn-see-promo").addEventListener("click", function () {
            const modal = document.getElementById("promo-modal");
            modal.classList.remove("hidden");
            setTimeout(() => {
                modal.classList.remove("opacity-0", "scale-95");
                modal.classList.add("opacity-100", "scale-100");
            }, 10); // Tambahkan sedikit delay agar animasi berjalan
        });

        document.getElementById("close-modal-promo").addEventListener("click", function () {
            const modal = document.getElementById("promo-modal");
            modal.classList.remove("opacity-100", "scale-100");
            modal.classList.add("opacity-0", "scale-95");
            setTimeout(() => {
                modal.classList.add("hidden");
            }, 500);
        });

        document.getElementById("btn-see-data").addEventListener("click", function () {
            const modal = document.getElementById("table-modal");
            modal.classList.remove("hidden");
            setTimeout(() => {
                modal.classList.remove("opacity-0", "scale-95");
                modal.classList.add("opacity-100", "scale-100");
            }, 10); // Tambahkan sedikit delay agar animasi berjalan
        });

        document.getElementById("close-modal").addEventListener("click", function () {
            const modal = document.getElementById("table-modal");
            modal.classList.remove("opacity-100", "scale-100");
            modal.classList.add("opacity-0", "scale-95");
            setTimeout(() => {
                modal.classList.add("hidden");
            }, 500);
        });

        document.getElementById("btn-see-penjualan").addEventListener("click", function () {
            const modal = document.getElementById("penjualan-terbaik");
            modal.classList.remove("hidden");
            setTimeout(() => {
                modal.classList.remove("opacity-0", "scale-95");
                modal.classList.add("opacity-100", "scale-100");
            }, 10); // Tambahkan sedikit delay agar animasi berjalan
        });

        document.getElementById("close-modal-penjualan").addEventListener("click", function () {
            const modal = document.getElementById("penjualan-terbaik");
            modal.classList.remove("opacity-100", "scale-100");
            modal.classList.add("opacity-0", "scale-95");
            setTimeout(() => {
                modal.classList.add("hidden");
            }, 500);
        });
        document.getElementById("btn-see-supplier").addEventListener("click", function () {
            const modal = document.getElementById("table-modal-supplier");
            modal.classList.remove("hidden");
            setTimeout(() => {
                modal.classList.remove("opacity-0", "scale-95");
                modal.classList.add("opacity-100", "scale-100");
            }, 10); // Tambahkan sedikit delay agar animasi berjalan
        });

        document.getElementById("close-modal-supplier").addEventListener("click", function () {
            const modal = document.getElementById("table-modal-supplier");
            modal.classList.remove("opacity-100", "scale-100");
            modal.classList.add("opacity-0", "scale-95");
            setTimeout(() => {
                modal.classList.add("hidden");
            }, 500);
        });
    </script>
</body>

</html>