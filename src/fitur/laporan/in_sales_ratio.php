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

$menuHandler = new MenuHandler('laporan_penjualan_salesratio');

if (!$menuHandler->initialize()) {
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Ratio</title>
    <!-- font awesome cdn link  -->
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
    <!-- <link rel="stylesheet" href="../../style/output.css"> -->
    <link rel="stylesheet" href="../../output2.css">

    <!-- Setting logo pada tab di website Anda / Favicon -->
    <link rel="icon" type="image/png" href="../../../public/images/logo1.png">
    <style>
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
    <?php include '../../component/sidebar_report.php'; ?>


    <main id="main-content" class="flex-1 p-8 transition-all duration-300 ml-64 bg-gray-100">
        <div class="flex justify-center items-center">
            <form id="laporanForm" method="POST"
                class="max-w-2xl grid gap-6 p-8 bg-white/80 backdrop-blur rounded-2xl shadow-xl border border-blue-100 mt-14 animate-fade-in-up">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <input type="hidden" id="kd_store" name="kd_store">
                <h2 class="text-2xl font-bold mt-5 text-blue-700 flex items-center gap-2"><i
                        class="fa fa-chart-bar"></i> Laporan Penjualan Sales</h2>
                <!-- Grid utama -->
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-6">
                    <!-- Pilihan Cabang -->
                    <div class="flex flex-col">
                        <label for="cabang" class="text-blue-700 font-medium mb-2">Cabang:</label>
                        <select id="cabang" name="cabang"
                            class="w-full px-5 py-3 border border-blue-100 rounded-xl shadow-sm focus:ring-2 focus:ring-blue-300 focus:border-blue-400 transition-all duration-200 h-[50px]">
                            <option value="ABIN">ABIN</option>
                            <option value="ACE">ACE</option>
                            <option value="ACIB">ACIB</option>
                            <option value="ACIL">ACIL</option>
                            <option value="ACIN">ACIN</option>
                            <option value="ACSA">ACSA</option>
                            <option value="ADET">ADET</option>
                            <option value="ADMB">ADMB</option>
                            <option value="AHA">AHA</option>
                            <option value="AHIN">AHIN</option>
                            <option value="ALANG">ALANG</option>
                            <option value="ANGIN">ANGIN</option>
                            <option value="APEN">APEN</option>
                            <option value="APIK">APIK</option>
                            <option value="APRS">APRS</option>
                            <option value="ARAW">ARAW</option>
                            <option value="ARUNG">ARUNG</option>
                            <option value="ASIH">ASIH</option>
                            <option value="ATIN">ATIN</option>
                            <option value="AWIT">AWIT</option>
                            <option value="AXY">AXY</option>
                            <option value="SEMUA CABANG">SEMUA CABANG</option>
                        </select>
                    </div>

                    <!-- Input Tanggal Awal -->
                    <div class="flex flex-col">
                        <label for="date" class="text-blue-700 font-medium mb-2">Awal:</label>
                        <input type="text" id="date" name="start_date"
                            class="w-full px-5 py-3 border border-blue-100 rounded-xl shadow-sm focus:ring-2 focus:ring-blue-300 focus:border-blue-400 transition-all duration-200"
                            placeholder="Pilih Tanggal" readonly>
                    </div>

                    <!-- Input Tanggal Akhir -->
                    <div class="flex flex-col">
                        <label for="date1" class="text-blue-700 font-medium mb-2">Akhir:</label>
                        <input type="text" id="date1" name="end_date"
                            class="w-full px-5 py-3 border border-blue-100 rounded-xl shadow-sm focus:ring-2 focus:ring-blue-300 focus:border-blue-400 transition-all duration-200"
                            placeholder="Pilih Tanggal" readonly>
                    </div>
                    <div class="flex flex-col">
                        <label for="ratio" class="text-blue-700 font-medium mb-2">Ratio:</label>
                        <select name="ratio" id="ratio_number"
                            class="block w-22 px-5 py-3 h-[50px] border border-blue-100 rounded-xl shadow-sm focus:ring-2 focus:ring-blue-300 focus:border-blue-400 transition-all duration-200">
                            <option value="none">None</option>
                            <option value="1">1</option>
                            <option value="2">2</option>
                            <option value="3">3</option>
                            <option value="4">4</option>
                            <option value="5">5</option>
                        </select>
                    </div>
                    <div class="flex flex-row gap-2" id="select-supp">
                        <input type="text" id="kode_supp1" name="kode_supp1"
                            class="supplier-input w-full px-5 py-3 border border-blue-100 rounded-xl shadow-sm focus:ring-2 focus:ring-blue-300 focus:border-blue-400 transition-all duration-200"
                            placeholder="Pilih Supplier" autocomplete="off">
                    </div>
                    <div class="flex flex-row gap-2" id="select-supp">
                        <input type="text" id="kode_supp2" name="kode_supp2"
                            class="supplier-input w-full px-5 py-3 border border-blue-100 rounded-xl shadow-sm focus:ring-2 focus:ring-blue-300 focus:border-blue-400 transition-all duration-200"
                            placeholder="Pilih Supplier" autocomplete="off">
                    </div>
                    <div class="flex flex-row gap-2" id="select-supp">
                        <input type="text" id="kode_supp3" name="kode_supp3"
                            class="supplier-input w-full px-5 py-3 border border-blue-100 rounded-xl shadow-sm focus:ring-2 focus:ring-blue-300 focus:border-blue-400 transition-all duration-200"
                            placeholder="Pilih Supplier" autocomplete="off">
                    </div>
                    <div class="flex flex-row gap-2" id="select-supp">
                        <input type="text" id="kode_supp4" name="kode_supp4"
                            class="supplier-input w-full px-5 py-3 border border-blue-100 rounded-xl shadow-sm focus:ring-2 focus:ring-blue-300 focus:border-blue-400 transition-all duration-200"
                            placeholder="Pilih Supplier" autocomplete="off">
                    </div>
                    <div class="flex flex-row gap-2" id="select-supp">
                        <input type="text" id="kode_supp5" name="kode_supp5"
                            class="supplier-input w-full px-5 py-3 border border-blue-100 rounded-xl shadow-sm focus:ring-2 focus:ring-blue-300 focus:border-blue-400 transition-all duration-200"
                            placeholder="Pilih Supplier" autocomplete="off">
                    </div>
                </div>
                <button type="submit"
                    class="bg-gradient-to-r from-blue-500 to-blue-700 hover:from-blue-600 hover:to-blue-800 text-white font-bold py-3 px-6 rounded-xl shadow-md transition-all duration-200 cursor-pointer w-full text-lg flex items-center justify-center gap-2 hover:scale-105"
                    id="btn-submit" name="submit" value="query1">
                    <i class="fa fa-search"></i> Cek Data
                </button>
            </form>
        </div>
        <form method="POST">
            <div id="bar"
                class="w-full bg-white/90 backdrop-blur rounded-2xl shadow-xl border border-blue-100 mt-10 animate-fade-in-up">
                <div class="bg-white/90 backdrop-blur rounded-2xl shadow-xl p-8 overflow-x-auto">
                    <div class="flex items-center gap-4">
                        <div id="lihatTable" class="flex flex-col">
                            <label for="lihat" class="text-blue-700 font-medium mb-2">Data Tabel: </label>
                            <select id="supplierDropdown" name="selectKode"
                                class="w-40 px-4 py-2 border border-blue-100 rounded-xl shadow-sm focus:ring-2 focus:ring-blue-300 focus:border-blue-400 transition-all duration-200">
                            </select>
                        </div>
                        <div id="sort-filter" class="flex items-center text-sm">
                            <label for="sort-by" class="text-blue-700 font-medium">By:</label>
                            <select id="sort-by"
                                class="border border-blue-100 rounded-xl px-4 py-2 shadow-sm focus:ring-2 focus:ring-blue-300 focus:border-blue-400 transition-all duration-200">
                                <option value="Total">Rupiah</option>
                                <option value="Qty">Qty</option>
                            </select>
                        </div>
                        <div class="relative group">
                            <button id="sendTable"
                                class="w-14 h-14 flex items-center justify-center bg-gradient-to-r from-blue-500 to-blue-700 text-white rounded-xl shadow-lg text-2xl cursor-pointer transition-all duration-200 hover:scale-105">
                                <i class="fa fa-table"></i>
                            </button>
                            <span
                                class="absolute bottom-full left-1/2 -translate-x-1/2 whitespace-nowrap px-2 py-1 text-sm text-white bg-gray-800 rounded opacity-0 transition-opacity duration-300 group-hover:opacity-100">
                                Lihat Tabel
                            </span>
                        </div>
                    </div>
                    <div class="flex justify-center items-center min-w-[600px]">
                        <div id="barDiagram" class="w-full h-[500px] md:h-[500px] lg:h-[500px] xl:h-[600px]"></div>
                    </div>
                </div>
            </div>
        </form>
        <!-- Modal -->
        <div id="table-modal" tabindex="-1"
            class="fixed inset-0 bg-black/50 backdrop-blur-sm hidden flex items-center justify-center transition-all ease-in duration-500 opacity-0 scale-95 mt-10">
            <div class="relative p-4 w-full max-w-7xl h-[88vh] transform transition-all ease-in duration-1000 scale-95">
                <!-- Modal content -->
                <div class="modal-glass overflow-hidden">
                    <div class="modal-header-flex">
                        <h3 class="text-xl font-bold text-blue-700 flex items-center gap-2"><i class="fa fa-table"></i>
                            Table Details</h3>
                        <button id="close-modal" class="modal-x-btn"><i
                                class="fa-solid fa-xmark pointer-events-none"></i></button>
                    </div>
                    <!-- Modal body -->
                    <div id="container-table-promo"
                        class="bg-white/90 shadow-md h-full rounded-2xl overflow-hidden max-w-7xl mx-auto">
                        <div
                            class="bg-gradient-to-r from-blue-500 to-blue-700 text-white p-4 rounded-t-lg flex justify-between items-center">
                            <h3 id="reportHeader1" class="text-lg font-semibold text-white mb-5"></h3>
                            <div class="flex items-center">
                                <input type="text" id="searchInput" placeholder="Cari data..."
                                    class="px-5 py-3 text-gray-700 bg-white rounded-xl border border-blue-200 shadow focus:ring-2 focus:ring-blue-300 focus:border-blue-400 transition-all duration-200"
                                    onkeyup="searchTable()">
                                <button onclick="exportToExcel()"
                                    class="ml-4 px-4 py-2 cursor-pointer bg-gradient-to-r from-green-500 to-green-700 text-white rounded-xl shadow hover:from-green-600 hover:to-green-800 transition ease-in duration-300">
                                    <i class="fa-regular fa-file-excel fa-2px"></i>
                                </button>
                                <button onclick="exportToPDF()"
                                    class="ml-2 px-4 py-2 cursor-pointer bg-gradient-to-r from-red-500 to-red-700 text-white rounded-xl shadow hover:from-red-600 hover:to-red-800 transition ease-in duration-300">
                                    <i class="fa-regular fa-file-pdf fa-2px"></i>
                                </button>
                            </div>
                        </div>
                        <div class="max-h-[500px] overflow-y-auto overflow-x-auto">
                            <table id="tableKode1" class="w-full  border border-collapse">
                                <thead class="text-gray-700 uppercase text-sm bg-blue-700 sticky top-0">
                                    <tr class="bg-gradient-to-r from-blue-500 to-blue-700 text-white p-4 rounded-t-lg">
                                        <th class="py-4 px-6 text-center">TOP</th>
                                        <th class="py-4 px-6 text-left">NAMA BARANG</th>
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

    </main>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.13/flatpickr.min.js"></script>
    </script>
    <script src="https://cdn.jsdelivr.net/npm/echarts/dist/echarts.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/echarts/5.4.3/echarts.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/echarts@5.4.3/dist/echarts.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>
    <script src="https://unpkg.com/exceljs/dist/exceljs.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.29/jspdf.plugin.autotable.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../../js/in_sales_ratio.js"></script>
    <!-- <script src="../../js/report/sales_ratio/main.js" type="module"></script> -->
    <script src="../../js/middleware_auth.js"></script>

    <script>
        document.getElementById("sendTable").addEventListener("click", function () {
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
    </script>
</body>

</html>