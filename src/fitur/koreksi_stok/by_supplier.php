<?php
session_start();
include '../../../aa_kon_sett.php';
$tanggal_kemarin = date('Y-m-d', strtotime('-1 day'));

$default_tgl_mulai = $tanggal_kemarin;
$default_tgl_selesai = $tanggal_kemarin;
$default_kd_store = 'all';
$default_page = 1;

$tgl_mulai = $_GET['tgl_mulai'] ?? $default_tgl_mulai;
$tgl_selesai = $_GET['tgl_selesai'] ?? $default_tgl_selesai;
$kd_store = $_GET['kd_store'] ?? $default_kd_store;
$page = (int) ($_GET['page'] ?? $default_page);
if ($page < 1) {
    $page = 1;
}
require_once __DIR__ . '/../../component/menu_handler.php';

$menuHandler = new MenuHandler('laporan_koreksi_supplier');

if (!$menuHandler->initialize()) {
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Koreksi (Supplier)</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/all.min.css">
    <link rel="stylesheet" href="../../style/header.css">
    <link rel="stylesheet" href="../../style/sidebar.css">
    <link rel="stylesheet" href="../../style/animation-fade-in.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../style/default-font.css">
    <link rel="stylesheet" href="../../output2.css">
    <link rel="stylesheet" href="../../style/pink-theme.css">
    <link rel="icon" type="image/png" href="../../../public/images/logo1.png">

    <script src="https://cdn.sheetjs.com/xlsx-latest/package/dist/xlsx.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js"></script>

</head>

<body class="bg-gray-50">
    <?php include '../../component/navigation_report.php' ?>
    <?php include '../../component/sidebar_report.php' ?>

    <main id="main-content" class="flex-1 p-4 ml-64">
        <section class="min-h-screen">
            <div class="max-w-7xl mx-auto">

                <div class="header-card p-4 rounded-2xl mb-4">
                    <div class="flex items-center justify-between flex-wrap gap-3">
                        <div class="flex items-center gap-3">
                            <div class="icon-wrapper">
                                <i class="fas fa-truck-fast fa-lg"></i>
                            </div>
                            <div>
                                <h1 id="page-title" class="text-xl font-bold text-gray-800 mb-1">Laporan Koreksi
                                    (Supplier)
                                </h1>
                                <p id="page-subtitle" class="text-xs text-gray-600">Memuat detail koreksi...</p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                        <div class="summary-card total flex gap-4 items-center">
                            <div class="summary-icon">
                                <i class="fas fa-boxes-packing fa-lg"></i>
                            </div>
                            <div>
                                <h3 class="text-xs font-semibold text-gray-600 mb-1">Total Qty Koreksi</h3>
                                <p id="summary-qty" class="text-2xl font-bold truncate text-gray-900">-</p>
                            </div>
                        </div>
                        <div class="summary-card flex gap-4 items-center total">
                            <div class="summary-icon">
                                <i class="fas fa-calculator fa-lg"></i>
                            </div>
                            <div>
                                <h3 class="text-xs font-semibold text-gray-600 mb-1">Total Rp Koreksi</h3>
                                <p id="summary-rp" class="text-2xl font-bold truncate text-gray-900">-</p>
                            </div>
                        </div>
                        <div class="summary-card flex gap-4 items-center warning">
                            <div class="summary-icon">
                                <i class="fa-solid fa-right-left fa-lg"></i>
                            </div>
                            <div>
                                <h3 class="text-xs font-semibold text-gray-600 mb-1">Total Rp Selisih</h3>
                                <p id="summary-selisih" class="text-2xl font-bold truncate text-yellow-600">-</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="filter-card-simple">
                    <form id="filter-form" class="grid grid-cols-1 md:grid-cols-4 gap-3 items-end" method="GET"
                        action="by_supplier.php">
                        <div>
                            <label for="tgl_mulai" class="block text-xs font-semibold text-gray-700 mb-2"> <i
                                    class="fas fa-calendar-alt text-pink-600 mr-1"></i>
                                Dari Tanggal
                            </label>
                            <input type="date" name="tgl_mulai" id="tgl_mulai" class="input-modern w-full"
                                value="<?php echo htmlspecialchars($tgl_mulai); ?>">
                        </div>
                        <div>
                            <label for="tgl_selesai" class="block text-xs font-semibold text-gray-700 mb-2"> <i
                                    class="fas fa-calendar-alt text-pink-600 mr-1"></i>
                                Sampai Tanggal
                            </label>
                            <input type="date" name="tgl_selesai" id="tgl_selesai" class="input-modern w-full"
                                value="<?php echo htmlspecialchars($tgl_selesai); ?>">
                        </div>
                        <div>
                            <label for="kd_store" class="block text-xs font-semibold text-gray-700 mb-2"> <i
                                    class="fas fa-store text-pink-600 mr-1"></i>
                                Pilih Cabang
                            </label>
                            <select name="kd_store" id="kd_store" class="input-modern w-full">
                                <option value="all">Seluruh Store</option>
                            </select>
                        </div>
                        <button type="submit" id="filter-submit-button"
                            class="btn-primary inline-flex items-center justify-center gap-2">
                            <i class="fas fa-filter"></i>
                            <span>Tampilkan</span>
                        </button>
                        <input type="hidden" name="page" value="1">
                    </form>
                </div>

                <div class="filter-card">
                    <div class="flex flex-wrap justify-between items-center mb-3 gap-3">
                        <h3 class="text-lg font-bold text-gray-800">
                            <i class="fas fa-list text-pink-600 mr-2"></i>
                            Hasil Laporan
                        </h3>
                        <div class="flex items-center gap-2">
                            <button id="export-excel-btn" class="btn-secondary-outline px-3 py-1.5 rounded-md"
                                style="background-color: #E6F7F0; border-color: #107C41; color: #107C41;">
                                <i class="fas fa-file-excel"></i>
                                <span>Export Excel</span>
                            </button>
                            <button id="export-pdf-btn" class="btn-secondary-outline px-3 py-1.5 rounded-md"
                                style="background-color: #FFF0F0; border-color: #D93025; color: #D93025;">
                                <i class="fas fa-file-pdf"></i>
                                <span>Export PDF</span>
                            </button>
                        </div>
                    </div>
                    <div class="table-container">
                        <table class="table-modern" id="koreksi-supplier-table">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>PLU</th>
                                    <th>Nama Barang</th>
                                    <th>Conv1</th>
                                    <th>Conv2</th>
                                    <th>HPP</th>
                                    <th>Qty Koreksi</th>
                                    <th>Stock</th>
                                    <th>Selisih Qty</th>
                                    <th>Total Rp Koreksi</th>
                                    <th>Total Rp Selisih</th>
                                    <th>Keterangan</th>
                                </tr>
                            </thead>
                            <tbody id="koreksi-supplier-table-body">
                                <tr>
                                    <td colspan="12" class="text-center p-8">
                                        <div class="spinner-simple"></div>
                                        <p class="mt-3 text-gray-500 font-medium">Memuat data...</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div id="pagination-container" class="flex justify-between items-center mt-4">
                        <span id="pagination-info" class="text-sm text-gray-600"></span>
                        <div id="pagination-links" class="flex items-center gap-2">
                        </div>
                    </div>
                </div>

            </div>
        </section>
    </main>

    <script src="/src/js/middleware_auth.js"></script>
    <script src="../../js/koreksi_stock/by_supplier_handler.js" type="module"></script>
    <script src="../../js/shared/internal/sidebar-profile.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


</body>

</html>