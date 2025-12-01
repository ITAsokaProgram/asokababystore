<?php
session_start();
include '../../../aa_kon_sett.php';

$default_tgl_mulai = date('Y-m-d', strtotime('-1 month'));
$default_tgl_selesai = date('Y-m-d');
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
$menuHandler = new MenuHandler('izin');

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
    <title>Laporan Approval Izin Koreksi</title>
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
    <style>
        /* Custom style untuk checkbox agar terlihat rapi */
        .custom-checkbox {
            width: 16px;
            height: 16px;
            cursor: pointer;
            accent-color: #db2777;
            /* Pink-600 */
        }

        .faktur-header {
            background-color: #f3f4f6;
            /* Gray-100 */
            border-bottom: 1px solid #e5e7eb;
        }
    </style>
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
                                <i class="fa-solid fa-check-to-slot fa-lg"></i>
                            </div>
                            <div>
                                <h1 id="page-title" class="text-xl font-bold text-gray-800 mb-1">Laporan Approval Izin
                                    Koreksi</h1>
                                <p id="page-subtitle" class="text-xs text-gray-600">Memuat detail izin...</p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-3">
                        <div class="summary-card total flex gap-4 items-center">
                            <div class="summary-icon">
                                <i class="fas fa-box-open fa-lg"></i>
                            </div>
                            <div>
                                <h3 class="text-xs font-semibold text-gray-600 mb-1">Total Item</h3>
                                <p id="summary-qty" class="text-2xl font-bold truncate text-gray-900">-</p>
                            </div>
                        </div>
                        <div class="summary-card flex gap-4 items-center total">
                            <div class="summary-icon">
                                <i class="fas fa-scale-unbalanced fa-lg"></i>
                            </div>
                            <div>
                                <h3 class="text-xs font-semibold text-gray-600 mb-1">Total Selisih Qty</h3>
                                <p id="summary-selisih" class="text-2xl font-bold truncate text-gray-900">-</p>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="filter-card-simple">
                    <form id="filter-form" class="grid grid-cols-1 md:grid-cols-4 gap-3 items-end" method="GET"
                        action="izin.php">
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
                        <table class="table-modern" id="izin-table">
                            <thead>
                                <tr>
                                    <th class="w-10 text-center">
                                        <i class="fas fa-check-double text-gray-400"></i>
                                    </th>
                                    <th>Tgl Kor</th>
                                    <th>PLU</th>
                                    <th>Deskripsi</th>
                                    <th class="">Sel Qty</th>
                                    <th>Supp</th>
                                    <th>Status</th>
                                    <th>Status SO</th>
                                    <th class="w-20 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="izin-table-body">
                                <tr>
                                    <td colspan="9" class="text-center p-8">
                                        <div class="spinner-simple"></div>
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
                <div id="bulk-action-bar"
                    class="fixed bottom-6 left-1/2 transform -translate-x-1/2 bg-white px-6 py-3 rounded-full shadow-2xl border border-gray-200 flex items-center gap-4 z-50 transition-all duration-300 translate-y-24 opacity-0">
                    <div class="flex items-center gap-2 text-gray-700 border-r border-gray-300 pr-4">
                        <div class="bg-pink-100 text-pink-600 rounded-full w-6 h-6 flex items-center justify-center font-bold text-xs"
                            id="selected-count">0</div>
                        <span class="text-sm font-medium">Item Dipilih</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <button onclick="bulkUpdateStatus('Izinkan')"
                            class="bg-green-600 hover:bg-green-700 text-white px-4 py-1.5 rounded-full text-xs font-bold transition-colors shadow-sm flex items-center gap-2">
                            <i class="fas fa-check"></i> Izinkan
                        </button>
                        <button onclick="bulkUpdateStatus('SO_Ulang')"
                            class="bg-red-500 hover:bg-red-600 text-white px-4 py-1.5 rounded-full text-xs font-bold transition-colors shadow-sm flex items-center gap-2">
                            <i class="fas fa-redo"></i> SO Ulang
                        </button>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <script src="/src/js/middleware_auth.js"></script>
    <script src="../../js/approval/izin_handler.js" type="module"></script>
    <script src="../../js/shared/internal/sidebar-profile.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>

</html>