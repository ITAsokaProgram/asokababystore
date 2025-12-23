<?php
session_start();
include '../../../aa_kon_sett.php';
$default_tgl_mulai = date('Y-m-01');
$default_tgl_selesai = date('Y-m-d');
$default_kd_store = 'all';
$default_page = 1;

$tgl_mulai = $_GET['tgl_mulai'] ?? $default_tgl_mulai;
$tgl_selesai = $_GET['tgl_selesai'] ?? $default_tgl_selesai;
$kd_store = $_GET['kd_store'] ?? $default_kd_store;
$search_buyer = trim($_GET['search_buyer'] ?? '');

$page = (int) ($_GET['page'] ?? $default_page);
if ($page < 1) {
    $page = 1;
}
require_once __DIR__ . '/../../component/menu_handler.php';
$menuHandler = new MenuHandler('pajak_keluaran');
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
    <title>Pajak Keluaran</title>
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/exceljs/4.3.0/exceljs.min.js"></script>
</head>

<body class="bg-gray-50 theme-coretax">
    <?php include '../../component/navigation_report.php' ?>
    <?php include '../../component/sidebar_report.php' ?>

    <main id="main-content" class="flex-1 p-4 ml-64">
        <section class="min-h-screen">
            <div class="max-w-8xl mx-auto">
                <div class="header-card p-4 rounded-2xl mb-4">
                    <div class="flex items-center justify-between flex-wrap gap-3">
                        <div class="flex items-center gap-3">
                            <div class="icon-wrapper bg-blue-100 text-blue-600">
                                <i class="fa-solid fa-file-invoice fa-lg"></i>
                            </div>
                            <div>
                                <h1 id="page-title" class="text-xl font-bold text-gray-800 mb-1">Pajak Keluaran</h1>
                                <p id="page-subtitle" class="text-xs text-gray-600">Memuat data faktur pajak keluaran...
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <a href="import_faktur_keluaran.php"
                                class="btn-primary flex items-center gap-2 px-4 py-2 shadow-lg shadow-blue-500/30 rounded-lg text-white transition-transform hover:scale-105 text-sm decoration-0 bg-blue-600 hover:bg-blue-700">
                                <i class="fas fa-upload"></i> <span>Import Keluaran</span>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="filter-card-simple">
                    <form id="filter-form" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-3 items-end"
                        method="GET">

                        <div class="lg:col-span-1">
                            <label for="tgl_mulai" class="block text-xs font-semibold text-gray-700 mb-2">
                                <i class="fas fa-calendar-alt text-blue-600 mr-1"></i> Dari
                            </label>
                            <input type="date" name="tgl_mulai" id="tgl_mulai" class="input-modern w-full"
                                value="<?php echo htmlspecialchars($tgl_mulai); ?>">
                        </div>
                        <div class="lg:col-span-1">
                            <label for="tgl_selesai" class="block text-xs font-semibold text-gray-700 mb-2">
                                <i class="fas fa-calendar-alt text-blue-600 mr-1"></i> Sampai
                            </label>
                            <input type="date" name="tgl_selesai" id="tgl_selesai" class="input-modern w-full"
                                value="<?php echo htmlspecialchars($tgl_selesai); ?>">
                        </div>

                        <div class="lg:col-span-1">
                            <label for="kd_store" class="block text-xs font-semibold text-gray-700 mb-2">
                                <i class="fas fa-store text-blue-600 mr-1"></i> Cabang
                            </label>
                            <select name="kd_store" id="kd_store" class="input-modern w-full">
                                <option value="all">Seluruh Store</option>
                            </select>
                        </div>

                        <div class="lg:col-span-1">
                            <label for="search_buyer" class="block text-xs font-semibold text-gray-700 mb-2">
                                <i class="fas fa-search text-blue-600 mr-1"></i> Cari Pembeli
                            </label>
                            <input type="text" name="search_buyer" id="search_buyer" class="input-modern w-full"
                                placeholder="NSFP / NPWP / Nama Pembeli..."
                                value="<?php echo htmlspecialchars($search_buyer); ?>">
                        </div>

                        <div class="lg:col-span-1 flex gap-2">
                            <button type="submit" id="filter-submit-button"
                                class="btn-primary w-full inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700">
                                <i class="fas fa-filter"></i>
                                <span>Tampilkan</span>
                            </button>
                            <button type="button" id="export-excel-button"
                                class="w-12 bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-2 rounded-lg transition-colors shadow-sm inline-flex items-center justify-center"
                                title="Export Excel">
                                <i class="fas fa-file-excel"></i>
                            </button>
                        </div>
                        <input type="hidden" name="page" value="1">
                    </form>
                </div>

                <div class="filter-card">
                    <div class="flex flex-wrap justify-between items-center mb-3 gap-3">
                        <h3 class="text-lg font-bold text-gray-800">
                            <i class="fas fa-list text-blue-600 mr-2"></i>
                            Hasil Laporan
                        </h3>
                    </div>
                    <div class="table-container">
                        <table class="table-modern" id="coretax-table">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>NPWP Pembeli</th>
                                    <th>Nama Pembeli</th>
                                    <th>NSFP</th>
                                    <th>Tgl Faktur</th>
                                    <th>Masa</th>
                                    <th>Tahun</th>
                                    <th class="text-right">DPP</th>
                                    <th class="text-right">DPP Nilai Lain</th>
                                    <th class="text-right">PPN</th>
                                    <th>Referensi</th>
                                </tr>
                            </thead>
                            <tbody id="coretax-table-body">
                                <tr>
                                    <td colspan="11" class="text-center p-8">
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
    <script src="../../js/coretax/data_coretax_keluaran_handler.js" type="module"></script>

    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</body>

</html>