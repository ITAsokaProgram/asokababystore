<?php
include '../../../aa_kon_sett.php';
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: OPTIONS, GET, POST");
header("Access-Control-Allow-Headers: Content-Type, Depth, User-Agent, X-File-Size, X-Requested-With, If-Modified-Since, X-File-Name, Cache-Control");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
require_once __DIR__ . '/../../component/menu_handler.php';

$menuHandler = new MenuHandler('laporan_penjualan_kategori');

if (!$menuHandler->initialize()) {
    exit();
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kategori</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/all.min.css">

    <!-- Penjelasan: Link ke CSS eksternal dengan cache busting query string -->
    <link rel="stylesheet" href="../../style/header.css">
    <link rel="stylesheet" href="../../style/sidebar.css">
    <link rel="stylesheet" href="../../style/animation-fade-in.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.13/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tom-select/2.2.2/css/tom-select.css">
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="../../style/default-font.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <script defer src="https://cdn.jsdelivr.net/npm/toastify-js"></script>

    <!-- <link rel="stylesheet" href="../../style/output.css"> -->
    <link rel="stylesheet" href="../../output2.css">
    <!-- Setting logo pada tab di website Anda / Favicon -->
    <link rel="icon" type="image/png" href="../../../public/images/logo1.png">

    <style>
        .btn.active {
            background-color: transparent;
            /* background tidak diisi */
            color: #ec4899;
            /* warna teks bisa disesuaikan */
            outline: 2px solid #ec4899;
            outline-offset: 1px;
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
            background: linear-gradient(to right, #ec4899, #3b82f6);
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

        .glass-container {
            background: rgba(255, 255, 255, 0.80);
            backdrop-filter: blur(8px);
            border-radius: 1.25rem;
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.18);
            border: 1.5px solid #bae6fd;
            padding: 2rem;
        }

        .loading-glass {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(6px);
        }
    </style>
</head>

<body class="bg-gray-100 justify-center overflow-auto">
    <!-- Header Navigation -->
    <?php include '../../component/navigation_report.php'; ?>
    <?php include '../../component/sidebar_report.php'; ?>


    <main id="main-content" class="flex-1 p-8 transition-all duration-300 ml-64 bg-gray-100">
        <form id="laporanForm" method="POST">
            <div id="pie" class="w-full mx-auto ">
                <div class="glass-container animate-fade-in-up">
                    <div class="flex flex-row items-center gap-3 mb-8">
                        <i class="fa fa-layer-group text-2xl text-blue-500"></i>
                        <h2 class="text-2xl font-bold text-blue-700">Laporan Penjualan Kategori</h2>
                    </div>
                    <!-- Grid layout filter -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-6 mb-8 w-fit">
                        <button type="button" id="btn-back" title="kembali"
                            class="w-12 h-12 cursor-pointer flex items-center justify-center bg-gradient-to-r from-blue-400 via-blue-500 to-blue-600 text-white hover:brightness-110 shadow-lg rounded-xl text-2xl transition-all duration-200 hover:scale-105">
                            <i class="fa-solid fa-angle-left pointer-events-none"></i>
                        </button>
                        <select id="cabang" name="cabang"
                            class="w-full px-5 py-3 border border-blue-100 rounded-xl shadow-sm focus:ring-2 focus:ring-blue-300 focus:border-blue-400 transition-all duration-200">

                        </select>
                        <input type="text" id="date" name="start_date"
                            class="w-full px-5 py-3 border border-blue-100 rounded-xl shadow-sm focus:ring-2 focus:ring-blue-300 focus:border-blue-400 transition-all duration-200"
                            placeholder="Pilih Tanggal" title="Tanggal Awal" readonly />
                        <input type="text" id="date1" name="end_date"
                            class="w-full px-5 py-3 border border-blue-100 rounded-xl shadow-sm focus:ring-2 focus:ring-blue-300 focus:border-blue-400 transition-all duration-200"
                            placeholder="Pilih Tanggal" title="Tanggal Akhir" readonly />
                        <button id="btn-send" name="query_type" title="cek data"
                            class="w-12 h-12 cursor-pointer flex items-center justify-center bg-gradient-to-r from-green-400 via-green-500 to-green-600 text-white hover:brightness-110 shadow-lg rounded-xl text-2xl transition-all duration-200 hover:scale-105">
                            <i class="fa-solid fa-check pointer-events-none"></i>
                        </button>
                    </div>
                    <div id="sort-filter" class="flex items-center text-sm mb-2 gap-2">
                        <label for="sort-by" class="text-blue-700 font-medium">Filter :</label>
                        <select id="sort-by"
                            class="border border-blue-100 rounded-xl px-4 py-2 shadow-sm focus:ring-2 focus:ring-blue-300 focus:border-blue-400 transition-all duration-200">
                            <option value="total" selected>Rupiah</option>
                            <option value="total_qty">Qty</option>
                        </select>
                    </div>
                    <div id="sort-filter1" class="flex items-center text-sm mb-8 gap-2">
                        <label for="sort-by1" class="text-blue-700 font-medium">Filter :</label>
                        <select id="sort-by1"
                            class="border border-blue-100 rounded-xl px-4 py-2 shadow-sm focus:ring-2 focus:ring-blue-300 focus:border-blue-400 transition-all duration-200">
                            <option value="total" selected>Rupiah</option>
                            <option value="total_qty">Qty</option>
                        </select>
                    </div>
                    <!-- Chart area -->
                    <div class="flex justify-center items-center w-full mb-10">
                        <div id="chartDiagram" class="w-full aspect-[16/9] max-h-[800px] mx-auto"></div>
                    </div>
                    <!-- Export & Table -->
                    <div class="flex flex-col md:flex-row md:items-center md:justify-end mb-4 gap-4" id="wrapper-table">
                        <div class="flex items-center justify-end gap-2 mb-4">
                            <button id="exportExcel" title="excel" 
                                class="bg-gradient-to-r from-green-400 to-green-600 hover:from-green-500 hover:to-green-700 text-white px-4 py-2 rounded-xl shadow transition cursor-pointer text-lg flex items-center gap-2">
                                <i class="fa-regular fa-file-excel"></i> Excel
                            </button>
                            <button id="exportPDF" title="pdf"
                                class="bg-gradient-to-r from-red-400 to-red-600 hover:from-red-500 hover:to-red-700 text-white px-4 py-2 rounded-xl shadow transition cursor-pointer text-lg flex items-center gap-2">
                                <i class="fa-regular fa-file-pdf"></i> PDF
                            </button>
                        </div>
                        <!-- Table -->
                        <div id="container-table">
                            <table id="dataCategoryTable" class="w-full rounded-xl shadow-lg text-sm">
                                <thead class=""></thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <!-- Loading -->
        <div id="progressOverlay"
            class="fixed inset-0 loading-glass z-50 hidden flex flex-col items-center justify-center space-y-4 animate-fade-in-up">
            <!-- Teks Loading -->
            <div class="text-lg font-semibold text-blue-700 animate-pulse flex items-center gap-2">
                <i class="fa fa-spinner fa-spin"></i> Loading data...
            </div>
            <!-- Progress Bar -->
            <div class="w-64 h-4 bg-blue-100 rounded-full overflow-hidden">
                <div id="progressBar" class="h-full bg-blue-500 w-0 rounded-full transition-all duration-500"></div>
            </div>
        </div>
    </main>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.13/flatpickr.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/echarts/dist/echarts.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/echarts/5.4.3/echarts.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/echarts@5.4.3/dist/echarts.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>
    <script src="https://unpkg.com/exceljs/dist/exceljs.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.29/jspdf.plugin.autotable.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
    <script src="../../js/report/sales_category/main.js" type="module"></script>
    <!-- <script src="../../js/in_sales_category.js"></script> -->
    <script src="../../js/loadingbar.js"></script>
    <script src="../../js/middleware_auth.js"></script>

    <!-- DataTables + Buttons + dependencies -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>

</body>

</html>