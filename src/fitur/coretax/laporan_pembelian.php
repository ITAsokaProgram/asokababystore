<?php
session_start();
include '../../../aa_kon_sett.php';

$tanggal_kemarin = date('Y-m-d', strtotime('-1 day'));
$default_tgl_mulai = $tanggal_kemarin;
$default_tgl_selesai = $tanggal_kemarin;

$default_filter_type = 'month';
$default_bulan = date('m');
$default_tahun = date('Y');

$tgl_mulai = $_GET['tgl_mulai'] ?? $default_tgl_mulai;
$tgl_selesai = $_GET['tgl_selesai'] ?? $default_tgl_selesai;
$kd_store = $_GET['kd_store'] ?? 'all';
$status_data = $_GET['status_data'] ?? 'all';

$filter_tipe_pembelian = $_GET['filter_tipe_pembelian'] ?? 'semua';
$filter_type = $_GET['filter_type'] ?? $default_filter_type;
$bulan = $_GET['bulan'] ?? $default_bulan;
$tahun = $_GET['tahun'] ?? $default_tahun;

$default_page = 1;
$page = (int) ($_GET['page'] ?? $default_page);
if ($page < 1) {
    $page = 1;
}

$list_bulan = [
    '01' => 'Januari',
    '02' => 'Februari',
    '03' => 'Maret',
    '04' => 'April',
    '05' => 'Mei',
    '06' => 'Juni',
    '07' => 'Juli',
    '08' => 'Agustus',
    '09' => 'September',
    '10' => 'Oktober',
    '11' => 'November',
    '12' => 'Desember'
];
require_once __DIR__ . '/../../component/menu_handler.php';
$menuHandler = new MenuHandler('pajak_laporan_pembelian');
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
    <title>Rekap Pembelian</title>
    <link rel="stylesheet" href="../../style/header.css">
    <link rel="stylesheet" href="../../style/sidebar.css">
    <link rel="stylesheet" href="../../style/animation-fade-in.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../style/default-font.css">
    <link rel="stylesheet" href="../../output2.css">
    <link rel="stylesheet" href="../../style/pink-theme.css">
    <link rel="icon" type="image/png" href="../../../public/images/logo1.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/exceljs/4.3.0/exceljs.min.js"></script>
</head>

<body class="bg-gray-50 theme-pembelian">
    <?php include '../../component/navigation_report.php' ?>
    <?php include '../../component/sidebar_report.php' ?>

    <main id="main-content" class="flex-1 p-4 ml-64">
        <section class="min-h-screen">
            <div class="max-w-7xl mx-auto">

                <div class="header-card p-4 rounded-2xl mb-4">
                    <div class="flex items-center justify-between flex-wrap gap-3">
                        <div class="flex items-center gap-3">
                            <div class="icon-wrapper">
                                <i class="fa-solid fa-cart-shopping fa-lg"></i>
                            </div>
                            <div>
                                <h1 id="page-title" class="text-xl font-bold text-gray-800 mb-1">Rekap Data Pembelian
                                </h1>
                                <p id="page-subtitle" class="text-xs text-gray-600">Memuat data pembelian...</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <a href="input_pembelian.php"
                                class="btn-primary flex items-center gap-2 px-4 py-2 shadow-lg shadow-pink-500/30 rounded-lg text-white transition-transform hover:scale-105 text-sm decoration-0">
                                <i class="fas fa-plus"></i> <span>Input Pembelian</span>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="filter-card-simple">
                    <form id="filter-form" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-3 items-end"
                        method="GET">

                        <div class="lg:col-span-1">
                            <label for="filter_type" class="block text-xs font-semibold text-gray-700 mb-2">
                                <i class="fas fa-filter text-pink-600 mr-1"></i> Mode Periode
                            </label>
                            <select name="filter_type" id="filter_type"
                                class="input-modern w-full font-semibold text-pink-700 bg-pink-50 border-pink-200">
                                <option value="month" <?php echo ($filter_type == 'month') ? 'selected' : ''; ?>>Per Bulan
                                </option>
                                <option value="date_range" <?php echo ($filter_type == 'date_range') ? 'selected' : ''; ?>>Rentang Tanggal</option>
                            </select>
                        </div>

                        <div id="container-month" class="contents">
                            <div class="lg:col-span-1">
                                <label for="bulan" class="block text-xs font-semibold text-gray-700 mb-2">
                                    <i class="fas fa-calendar-check text-pink-600 mr-1"></i> Bulan
                                </label>
                                <select name="bulan" id="bulan" class="input-modern w-full">
                                    <?php foreach ($list_bulan as $key => $val): ?>
                                        <option value="<?= $key ?>" <?= ($bulan == $key) ? 'selected' : '' ?>><?= $val ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="lg:col-span-1">
                                <label for="tahun" class="block text-xs font-semibold text-gray-700 mb-2">
                                    <i class="fas fa-calendar text-pink-600 mr-1"></i> Tahun
                                </label>
                                <input type="number" name="tahun" id="tahun" class="input-modern w-full"
                                    value="<?= $tahun ?>" min="2000" max="2100">
                            </div>
                        </div>

                        <div id="container-date-range" class="contents" style="display: none;">
                            <div class="lg:col-span-1">
                                <label for="tgl_mulai" class="block text-xs font-semibold text-gray-700 mb-2">
                                    <i class="fas fa-calendar-alt text-pink-600 mr-1"></i> Dari
                                </label>
                                <input type="date" name="tgl_mulai" id="tgl_mulai" class="input-modern w-full"
                                    value="<?php echo htmlspecialchars($tgl_mulai); ?>">
                            </div>
                            <div class="lg:col-span-1">
                                <label for="tgl_selesai" class="block text-xs font-semibold text-gray-700 mb-2">
                                    <i class="fas fa-calendar-alt text-pink-600 mr-1"></i> Sampai
                                </label>
                                <input type="date" name="tgl_selesai" id="tgl_selesai" class="input-modern w-full"
                                    value="<?php echo htmlspecialchars($tgl_selesai); ?>">
                            </div>
                        </div>

                        <div class="lg:col-span-1">
                            <label for="kd_store" class="block text-xs font-semibold text-gray-700 mb-2">
                                <i class="fas fa-store text-pink-600 mr-1"></i> Cabang
                            </label>
                            <select name="kd_store" id="kd_store" class="input-modern w-full">
                                <option value="all">Seluruh Store</option>
                            </select>
                        </div>

                        <div class="lg:col-span-2">
                            <label for="filter_tipe_pembelian" class="block text-xs font-semibold text-gray-700 mb-2">
                                <i class="fas fa-tags text-pink-600 mr-1"></i> Status
                            </label>
                            <select name="filter_tipe_pembelian" id="filter_tipe_pembelian" class="input-modern w-full">
                                <option value="semua" <?php echo ($filter_tipe_pembelian == 'semua') ? 'selected' : ''; ?>>SEMUA</option>
                                <option value="PKP" <?php echo ($filter_tipe_pembelian == 'PKP') ? 'selected' : ''; ?>>PKP
                                </option>
                                <option value="NON PKP" <?php echo ($filter_tipe_pembelian == 'NON PKP') ? 'selected' : ''; ?>>NON PKP</option>
                                <option value="BTKP" <?php echo ($filter_tipe_pembelian == 'BTKP') ? 'selected' : ''; ?>>
                                    BTKP</option>
                            </select>
                        </div>

                        <div class="lg:col-span-2 lg:col-start-1 lg:row-start-2"> <label for="status_data"
                                class="block text-xs font-semibold text-gray-700 mb-2">
                                <i class="fas fa-link text-pink-600 mr-1"></i> Coretax / Faktur Pajak Fisik
                            </label>
                            <select name="status_data" id="status_data" class="input-modern w-full">
                                <option value="all" <?php echo ($status_data == 'all') ? 'selected' : ''; ?>>Semua Data
                                </option>
                                <option value="need_selection" <?php echo ($status_data == 'need_selection') ? 'selected' : ''; ?>>
                                    Siap Pilih NSFP
                                </option>
                                <option value="unlinked" <?php echo ($status_data == 'unlinked') ? 'selected' : ''; ?>>
                                    Belum Terhubung</option>
                                <option value="linked_any" <?php echo ($status_data == 'linked_any') ? 'selected' : ''; ?>>Sudah Terhubung (Semua)</option>
                                <option value="linked_coretax" <?php echo ($status_data == 'linked_coretax') ? 'selected' : ''; ?>>Terhubung Coretax</option>
                                <option value="linked_fisik" <?php echo ($status_data == 'linked_fisik') ? 'selected' : ''; ?>>Terhubung Fisik</option>
                                <option value="linked_both" <?php echo ($status_data == 'linked_both') ? 'selected' : ''; ?>>Terhubung Keduanya</option>
                            </select>
                        </div>

                        <div class="lg:col-span-2 lg:col-start-3 lg:row-start-2">
                            <label for="search_supplier" class="block text-xs font-semibold text-gray-700 mb-2">
                                <i class="fas fa-search text-pink-600 mr-1"></i> Cari Data
                            </label>
                            <div class="flex gap-2">
                                <input type="text" name="search_supplier" id="search_supplier"
                                    class="input-modern w-full"
                                    value="<?php echo htmlspecialchars($_GET['search_supplier'] ?? ''); ?>"
                                    placeholder="Supplier / NSFP / Rp...">

                                <button type="submit" id="filter-submit-button"
                                    class="btn-primary inline-flex items-center justify-center gap-2 px-6">
                                    <i class="fas fa-filter"></i>
                                    <span>Tampilkan</span>
                                </button>

                                <button type="button" id="export-excel-button"
                                    class="w-12 bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-2 rounded-lg transition-colors shadow-sm inline-flex items-center justify-center"
                                    title="Export Excel">
                                    <i class="fas fa-file-excel"></i>
                                </button>
                            </div>
                        </div>

                        <input type="hidden" name="page" value="1">
                    </form>
                </div>

                <div class="filter-card">
                    <div class="flex flex-wrap justify-between items-center mb-3 gap-3">
                        <h3 class="text-lg font-bold text-gray-800">
                            <i class="fas fa-list text-pink-600 mr-2"></i>
                            Hasil Laporan
                        </h3>
                    </div>
                    <div class="table-container">
                        <table class="table-modern" id="receipt-table">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Tgl Nota</th>
                                    <th>No Invoice</th>
                                    <th>Status</th>
                                    <th class="text-center">Cabang</th>
                                    <th>Nama Supplier</th>
                                    <th class="text-right">DPP</th>
                                    <th class="text-right">DPP Nilai Lain</th>
                                    <th class="text-right">PPN</th>
                                    <th class="text-right">Total</th>
                                    <th class="text-center" style="width: 150px;">Fisik</th>
                                    <th class="text-center" style="width: 150px;">Coretax</th>
                                </tr>
                            </thead>
                            <tbody id="receipt-table-body">
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
    <script src="../../js/coretax/laporan_pembelian_handler.js" type="module"></script>

    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>

</html>