<?php
session_start();
include '../../../aa_kon_sett.php';

// Logic Default Tanggal (Cut Off 16 bulan lalu - 15 bulan ini)
$default_tgl_mulai = date('Y-m-16', strtotime('last month'));
$default_tgl_selesai = date('Y-m-15');

$default_kd_store = 'all';
$default_page = 1;

$tgl_mulai = $_GET['tgl_mulai'] ?? $default_tgl_mulai;
$tgl_selesai = $_GET['tgl_selesai'] ?? $default_tgl_selesai;
$kd_store = $_GET['kd_store'] ?? $default_kd_store;
$mode = $_GET['mode'] ?? 'jadwal';
$page = (int) ($_GET['page'] ?? $default_page);

if ($page < 1) {
    $page = 1;
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Barang Belum Scan (Koreksi SO)</title>
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
                            <div class="icon-wrapper bg-pink-100 text-pink-600 p-3 rounded-xl">
                                <i class="fa-solid fa-clipboard-list fa-lg"></i>
                            </div>
                            <div>
                                <h1 id="page-title" class="text-xl font-bold text-gray-800 mb-1">
                                    Laporan Barang Belum Scan
                                </h1>
                                <p id="page-subtitle" class="text-xs text-gray-600">
                                    Item di Master yang belum masuk Koreksi SO
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3">
                        <div
                            class="summary-card total flex gap-4 items-center bg-white p-4 rounded-xl shadow-sm border border-gray-100">
                            <div class="summary-icon bg-blue-50 text-blue-600 p-3 rounded-lg">
                                <i class="fas fa-box-open fa-lg"></i>
                            </div>
                            <div>
                                <h3 class="text-xs font-semibold text-gray-600 mb-1">Total Item Missed</h3>
                                <p id="summary-qty" class="text-2xl font-bold truncate text-gray-900">-</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="filter-card-simple bg-white p-4 rounded-2xl mb-4 shadow-sm">
                    <form id="filter-form" class="flex justify-around gap-4 flex-wrap" method="GET">
                        <div>
                            <label for="tgl_mulai" class="block text-xs font-semibold text-gray-700 mb-2">
                                <i class="fas fa-calendar-alt text-pink-600 mr-1"></i> Dari Tanggal
                            </label>
                            <input type="date" name="tgl_mulai" id="tgl_mulai"
                                class="input-modern w-full border-gray-300 rounded-lg focus:ring-pink-500 focus:border-pink-500"
                                value="<?php echo htmlspecialchars($tgl_mulai); ?>">
                        </div>
                        <div>
                            <label for="tgl_selesai" class="block text-xs font-semibold text-gray-700 mb-2">
                                <i class="fas fa-calendar-alt text-pink-600 mr-1"></i> Sampai Tanggal
                            </label>
                            <input type="date" name="tgl_selesai" id="tgl_selesai"
                                class="input-modern w-full border-gray-300 rounded-lg focus:ring-pink-500 focus:border-pink-500"
                                value="<?php echo htmlspecialchars($tgl_selesai); ?>">
                        </div>
                        <div>
                            <label for="mode" class="block text-xs font-semibold text-gray-700 mb-2">
                                <i class="fas fa-layer-group text-pink-600 mr-1"></i> Sumber Data
                            </label>
                            <select name="mode" id="mode"
                                class="input-modern w-full border-gray-300 rounded-lg focus:ring-pink-500 focus:border-pink-500">
                                <option value="jadwal" <?php echo $mode === 'jadwal' ? 'selected' : ''; ?>>Sesuai Jadwal
                                    SO</option>
                                <option value="non_jadwal" <?php echo $mode === 'non_jadwal' ? 'selected' : ''; ?>>Semua
                                    Master (Tanpa Jadwal)</option>
                            </select>
                        </div>
                        <div>
                            <label for="kd_store" class="block text-xs font-semibold text-gray-700 mb-2">
                                <i class="fas fa-store text-pink-600 mr-1"></i> Pilih Cabang
                            </label>
                            <select name="kd_store" id="kd_store"
                                class="input-modern w-full border-gray-300 rounded-lg focus:ring-pink-500 focus:border-pink-500">
                                <option value="all">Seluruh Store</option>
                            </select>
                        </div>
                        <button type="submit" id="filter-submit-button"
                            class="btn-primary inline-flex items-center justify-center gap-2 bg-pink-600 hover:bg-pink-700 text-white px-4 py-2 rounded-lg transition-colors h-[42px]">
                            <i class="fas fa-filter"></i>
                            <span>Tampilkan</span>
                        </button>
                        <input type="hidden" name="page" value="1">
                    </form>
                </div>

                <div class="filter-card bg-white p-4 rounded-2xl shadow-sm">
                    <div class="flex flex-wrap justify-between items-center mb-3 gap-3">
                        <h3 class="text-lg font-bold text-gray-800">
                            <i class="fas fa-list text-pink-600 mr-2"></i>
                            Hasil Laporan
                        </h3>
                        <div class="flex items-center gap-2">
                            <button id="export-excel-btn"
                                class="btn-secondary-outline px-3 py-1.5 rounded-md flex items-center gap-2"
                                style="background-color: #E6F7F0; border-color: #107C41; color: #107C41;">
                                <i class="fas fa-file-excel"></i>
                                <span>Export Excel</span>
                            </button>
                        </div>
                    </div>
                    <div class="table-container overflow-x-auto">
                        <table class="table-modern w-full text-sm text-left" id="missed-item-table">
                            <thead class="bg-gray-50 text-gray-700 uppercase">
                                <tr>
                                    <th class="px-4 py-3 rounded-tl-lg">No</th>
                                    <th class="px-4 py-3">PLU</th>
                                    <th class="px-4 py-3">Deskripsi Item</th>
                                    <th class="px-4 py-3">Satuan</th>
                                    <th class="px-4 py-3 text-right">Stock Komp</th>
                                    <th class="px-4 py-3 text-right rounded-tr-lg">Harga Beli</th>
                                </tr>
                            </thead>
                            <tbody id="missed-item-table-body" class="divide-y divide-gray-200">
                                <tr>
                                    <td colspan="6" class="text-center p-8">
                                        <div
                                            class="spinner-simple inline-block w-6 h-6 border-2 border-pink-600 border-t-transparent rounded-full animate-spin">
                                        </div>
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
    <script src="../../js/koreksi_so/report_missed_handler.js" type="module"></script>
    <script src="../../js/shared/internal/sidebar-profile.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</body>

</html>