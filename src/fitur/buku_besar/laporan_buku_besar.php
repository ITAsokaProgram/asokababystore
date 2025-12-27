<?php
session_start();
include '../../../aa_kon_sett.php';

// Default filters
$tanggal_kemarin = date('Y-m-d', strtotime('-1 day'));
$default_tgl_mulai = $tanggal_kemarin;
$default_tgl_selesai = $tanggal_kemarin;

$default_filter_type = 'month';
$default_bulan = date('m');
$default_tahun = date('Y');

// Get params
$tgl_mulai = $_GET['tgl_mulai'] ?? $default_tgl_mulai;
$tgl_selesai = $_GET['tgl_selesai'] ?? $default_tgl_selesai;
$kd_store = $_GET['kd_store'] ?? 'all';

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
$menuHandler = new MenuHandler('laporan_buku_besar'); // Sesuaikan permission key jika ada
// if (!$menuHandler->initialize()) { exit(); } // Uncomment jika pakai permission
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Buku Besar</title>
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
                                <i class="fa-solid fa-book-open fa-lg"></i>
                            </div>
                            <div>
                                <h1 id="page-title" class="text-xl font-bold text-gray-800 mb-1">Laporan Buku Besar</h1>
                                <p id="page-subtitle" class="text-xs text-gray-600">Memuat data...</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <a href="input_buku_besar.php"
                                class="btn-primary flex items-center gap-2 px-4 py-2 shadow-lg shadow-pink-500/30 rounded-lg text-white transition-transform hover:scale-105 text-sm decoration-0">
                                <i class="fas fa-plus"></i> <span>Input Data</span>
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
                        <div class="lg:col-span-1">
                            <label for="status_bayar" class="block text-xs font-semibold text-gray-700 mb-2">
                                <i class="fas fa-money-bill-wave text-pink-600 mr-1"></i> Status Bayar
                            </label>
                            <select name="status_bayar" id="status_bayar" class="input-modern w-full">
                                <option value="all">Semua</option>
                                <option value="paid">Sudah Dibayar</option>
                                <option value="unpaid">Belum Dibayar</option>
                            </select>
                        </div>
                        <div class="lg:col-span-1">
                            <label for="filter_status" class="block text-xs font-semibold text-gray-700 mb-2">
                                <i class="fas fa-tag text-pink-600 mr-1"></i> Status Pajak
                            </label>
                            <select name="filter_status" id="filter_status" class="input-modern w-full">
                                <option value="all">Semua</option>
                                <option value="PKP">PKP</option>
                                <option value="NON PKP">NON PKP</option>
                                <option value="BTKP">BTKP</option>
                            </select>
                        </div>


                        <div class="lg:col-span-2 lg:col-start-5">
                            <label for="search_query" class="block text-xs font-semibold text-gray-700 mb-2">
                                <i class="fas fa-search text-pink-600 mr-1"></i> Cari Data
                            </label>
                            <div class="flex gap-2">
                                <input type="text" name="search_query" id="search_query" class="input-modern w-full"
                                    value="<?php echo htmlspecialchars($_GET['search_query'] ?? ''); ?>"
                                    placeholder="Supplier / Faktur / Rp...">

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

                        <table class="table-modern" id="ledger-table">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Tgl Nota</th>
                                    <th class="text-center">Cabang</th>
                                    <th>Supplier</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center">Jatuh Tempo</th>
                                    <th>MOP</th>
                                    <th class="text-right">Potongan</th>
                                    <th class="text-right">Nilai Faktur</th>
                                    <th class="text-right">Total Bayar</th>
                                    <th class="text-center">Tgl Bayar</th>
                                    <th class="text-center">Cabang Bayar</th>
                                </tr>
                            </thead>
                            <tbody id="ledger-table-body">
                                <tr>
                                    <td colspan="10" class="text-center p-8">
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

    <div x-data="detailModal()" x-show="show" x-cloak @show-detail-modal.window="openModal($event.detail)"
        style="display: none;" class="fixed inset-0 z-[9999] overflow-y-auto">
        <div x-show="show" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" @click="closeModal()"
            class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm">
        </div>
        <div class="flex min-h-screen items-center justify-center p-4">
            <div x-show="show" x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 transform scale-95"
                x-transition:enter-end="opacity-100 transform scale-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 transform scale-100"
                x-transition:leave-end="opacity-0 transform scale-95" @click.stop
                class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md transform">

                <div
                    class="flex items-center justify-between p-5 border-b border-gray-100 bg-gradient-to-r from-pink-50 to-white rounded-t-2xl">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-pink-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-info-circle text-pink-600 text-lg"></i>
                        </div>
                        <h3 class="text-lg font-bold text-gray-800" x-text="title"></h3>
                    </div>
                    <button @click="closeModal()"
                        class="text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg p-2 transition-all">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <div class="p-6">
                    <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                        <p class="text-gray-700 text-sm leading-relaxed whitespace-pre-wrap break-words"
                            x-text="content"></p>
                    </div>
                </div>

                <div class="flex justify-end gap-2 p-5 border-t border-gray-100 bg-gray-50 rounded-b-2xl">
                    <button @click="closeModal()" class="btn-secondary px-4 py-2 text-sm">
                        <i class="fas fa-times mr-2"></i> Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function detailModal() {
            return {
                show: false,
                title: '',
                content: '',
                openModal(data) {
                    this.title = data.title;
                    this.content = data.content;
                    this.show = true;
                    document.body.style.overflow = 'hidden';
                },
                closeModal() {
                    this.show = false;
                    document.body.style.overflow = 'auto';
                }
            }
        }
    </script>
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>

    <script src="/src/js/middleware_auth.js"></script>
    <script src="../../js/buku_besar/laporan_buku_besar_handler.js" type="module"></script>

    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>

</html>