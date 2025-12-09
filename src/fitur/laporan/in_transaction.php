<?php
include '../../../aa_kon_sett.php';

header("Access-Control-Allow-Headers: Content-Type, Authorization");

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

require_once __DIR__ . '/../../component/menu_handler.php';

$menuHandler = new MenuHandler('laporan_penjualan_mnonm');

if (!$menuHandler->initialize()) {
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaksi</title>
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
    <!-- Sidebar -->
    <?php include '../../component/sidebar_report.php'; ?>

    <main id="main-content" class="flex-1 p-8 transition-all duration-300 ml-64 bg-gray-100">
        <div class="glass-container animate-fade-in-up mx-auto mt-10">
            <!-- Header -->
            <header class="text-center mb-8">
                <h1 class="text-3xl font-bold text-blue-700 flex items-center justify-center gap-3"><i
                        class="fa fa-users"></i> Member & Non Member</h1>
            </header>
            <!-- Filter Section -->
            <div class="border border-pink-200 bg-white/80 backdrop-blur rounded-2xl shadow-xl p-8 mb-10">
                <div class="w-full h-auto">
                    <div class="flex justify-start items-center mb-4">
                        <div id="searchContainer" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 w-full">
                            <!-- Filter Data -->
                            <div>
                                <label for="filterRange"
                                    class="text-base text-blue-700 font-medium mb-2 block">Data:</label>
                                <select id="filterRange"
                                    class="w-full px-5 py-3 border border-blue-100 rounded-xl shadow-sm text-base text-gray-700 focus:ring-2 focus:ring-pink-300 focus:border-pink-500 transition-all duration-200">
                                    <option value="allTrans" selected>Semua Transaksi</option>
                                    <option value="Member">Member</option>
                                    <option value="NonMember">Non Member</option>
                                </select>
                            </div>
                            <!-- Tanggal Awal -->
                            <div>
                                <label for="filterRangeDateStart"
                                    class="text-base text-blue-700 font-medium mb-2 block">Tanggal:</label>
                                <input type="text" id="filterRangeDateStart" readonly
                                    class="w-full px-5 py-3 border border-blue-100 rounded-xl shadow-sm text-base text-gray-700 focus:ring-2 focus:ring-pink-300 focus:border-pink-500 transition-all duration-200" />
                            </div>
                            <!-- Tanggal Akhir -->
                            <div>
                                <label for="filterRangeDateEnd"
                                    class="text-base text-blue-700 font-medium mb-2 block">Ke:</label>
                                <input type="text" id="filterRangeDateEnd" readonly
                                    class="w-full px-5 py-3 border border-blue-100 rounded-xl shadow-sm text-base text-gray-700 focus:ring-2 focus:ring-pink-300 focus:border-pink-500 transition-all duration-200" />
                            </div>
                            <!-- Cabang + Tombol -->
                            <div>
                                <label for="cabang"
                                    class="text-base text-blue-700 font-medium mb-2 block">Cabang:</label>
                                <div class="flex gap-2">
                                    <select id="cabang" name="cabang"
                                        class="flex-1 px-5 py-3 border border-blue-100 rounded-xl shadow-sm text-base text-gray-700 focus:ring-2 focus:ring-pink-300 focus:border-pink-500 transition-all duration-200">
                                        <option value="none">Loading...</option>
                                    </select>
                                    <button type="button" id="cek"
                                        class="px-6 py-3 rounded-xl bg-gradient-to-r from-blue-500 to-blue-700 text-base text-white font-bold shadow-md hover:scale-105 transition-all duration-200 flex items-center gap-2">
                                        <i class="fa fa-search"></i> Cek
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Chart Section -->
                <div class="bg-white/80 backdrop-blur rounded-2xl shadow-xl border border-blue-100 p-8 mb-8 w-full">
                    <h3
                        class="text-2xl font-bold text-blue-700 mb-6 text-center flex items-center justify-center gap-2">
                        <i class="fa fa-chart-bar"></i> Grafik Transaksi
                    </h3>
                    <!-- Grafik 1: Total Transaksi -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="p-4 bg-white/90 backdrop-blur rounded-2xl shadow-lg border border-blue-100">
                            <h4
                                class="text-xl font-bold text-blue-700 mb-4 text-center flex items-center justify-center gap-2">
                                <i class="fa fa-receipt"></i> Total Transaksi
                            </h4>
                            <div id="chart1" style="height: 400px;"></div>
                        </div>
                        <!-- Grafik 2: Barang Terlaris -->
                        <div class="p-4 bg-white/90 backdrop-blur rounded-2xl shadow-lg border border-blue-100">
                            <h4
                                class="text-xl font-bold text-blue-700 mb-4 text-center flex items-center justify-center gap-2">
                                <i class="fa fa-percentage"></i> Persentase Pembelian
                            </h4>
                            <div id="chart2" style="height: 400px;"></div>
                        </div>
                    </div>
                </div>
                <!-- Transaction Table with Tailwind & DataTables -->
                <div class="bg-white/80 backdrop-blur rounded-2xl shadow-xl border border-blue-100 p-8">
                    <h3 class="text-xl font-bold text-blue-700 mb-4 flex items-center gap-2"><i class="fa fa-star"></i>
                        Barang Terlaris</h3>
                    <div class="mt-2">
                        <table id="barangTable" class="w-full text-sm text-left text-gray-700">
                            <thead class="text-xs text-white uppercase">
                                <tr>
                                    <th scope="col">No</th>
                                    <th scope="col">Barcode</th>
                                    <th scope="col">Barang</th>
                                    <th scope="col" class="text-center">Total Terjual</th>
                                    <th scope="col" class="text-center">Harga</th>
                                    <th scope="col" class="text-center">Harga Promo</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100">
                                <!-- DataTable will fill this -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div id="progressOverlay"
            class="fixed inset-0 loading-glass z-50 hidden flex flex-col items-center justify-center space-y-4 animate-fade-in-up">
            <!-- Spinner -->
            <div class="w-12 h-12 border-4 border-blue-500 border-t-transparent rounded-full animate-spin"></div>
            <!-- Teks Loading -->
            <div class="text-lg font-semibold text-blue-700 animate-pulse flex items-center gap-2">
                <i class="fa fa-spinner fa-spin"></i> Loading data...
            </div>
        </div>
    </main>
    <!-- custom js file link -->
    <!-- Tambahkan pustaka Bootstrap Datepicker -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.13/flatpickr.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/echarts/dist/echarts.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/echarts/5.4.3/echarts.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/echarts@5.4.3/dist/echarts.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://unpkg.com/exceljs/dist/exceljs.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.29/jspdf.plugin.autotable.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="/src/js/transaction.js"></script>
    <script src="/src/js/storeCodeConvert.js"></script>
    <script src="/src/js/middleware_auth.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>

</body>

</html>