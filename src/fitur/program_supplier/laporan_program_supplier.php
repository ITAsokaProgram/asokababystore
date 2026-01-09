<?php
session_start();
include '../../../aa_kon_sett.php';

// Default filters
$tanggal_kemarin = date('Y-m-d', strtotime('-1 day'));
$default_tgl_mulai = date('Y-m-01'); // Awal bulan ini
$default_tgl_selesai = date('Y-m-d'); // Hari ini

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
$menuHandler = new MenuHandler('laporan_program_supplier');
// if (!$menuHandler->initialize()) { exit(); }
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Program Supplier</title>
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
            <div class="max-w-full mx-auto">
                <div class="header-card p-4 rounded-2xl mb-4">
                    <div class="flex items-center justify-between flex-wrap gap-3">
                        <div class="flex items-center gap-3">
                            <div class="icon-wrapper">
                                <i class="fa-solid fa-handshake fa-lg"></i>
                            </div>
                            <div>
                                <h1 id="page-title" class="text-xl font-bold text-gray-800 mb-1">Laporan Program
                                    Supplier</h1>
                                <p id="page-subtitle" class="text-xs text-gray-600">Memuat data...</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <a href="input_program_supplier.php"
                                class="btn-primary flex items-center gap-2 px-4 py-2 shadow-lg shadow-pink-500/30 rounded-lg text-white transition-transform hover:scale-105 text-sm decoration-0">
                                <i class="fas fa-plus"></i> <span>Input Program Supplier</span>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="filter-card-simple">
                    <form id="filter-form" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3 items-end"
                        method="GET">

                        <div class="lg:col-span-1">
                            <label for="filter_type" class="block text-xs font-semibold text-gray-700 mb-2">
                                <i class="fas fa-filter text-pink-600 mr-1"></i> Periode (TOP)
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
                                <label for="bulan" class="block text-xs font-semibold text-gray-700 mb-2">Bulan</label>
                                <select name="bulan" id="bulan" class="input-modern w-full">
                                    <?php foreach ($list_bulan as $key => $val): ?>
                                        <option value="<?= $key ?>" <?= ($bulan == $key) ? 'selected' : '' ?>><?= $val ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="lg:col-span-1">
                                <label for="tahun" class="block text-xs font-semibold text-gray-700 mb-2">Tahun</label>
                                <input type="number" name="tahun" id="tahun" class="input-modern w-full"
                                    value="<?= $tahun ?>">
                            </div>
                        </div>

                        <div id="container-date-range" class="contents" style="display: none;">
                            <div class="lg:col-span-1">
                                <label for="tgl_mulai"
                                    class="block text-xs font-semibold text-gray-700 mb-2">Dari</label>
                                <input type="date" name="tgl_mulai" id="tgl_mulai" class="input-modern w-full"
                                    value="<?php echo htmlspecialchars($tgl_mulai); ?>">
                            </div>
                            <div class="lg:col-span-1">
                                <label for="tgl_selesai"
                                    class="block text-xs font-semibold text-gray-700 mb-2">Sampai</label>
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
                            <label for="pic" class="block text-xs font-semibold text-gray-700 mb-2">
                                <i class="fas fa-user-tag text-pink-600 mr-1"></i> PIC
                            </label>
                            <select name="pic" id="filter_pic" class="input-modern w-full">
                                <option value="all">Semua PIC</option>
                            </select>
                        </div>

                        <div class="lg:col-span-1">
                            <label for="search_query" class="block text-xs font-semibold text-gray-700 mb-2">
                                <i class="fas fa-search text-pink-600 mr-1"></i> Cari Data
                            </label>
                            <div class="flex gap-2">
                                <input type="text" name="search_query" id="search_query" class="input-modern w-full"
                                    style="min-width: 120px;"
                                    value="<?php echo htmlspecialchars($_GET['search_query'] ?? ''); ?>"
                                    placeholder="Cari Dok / Supplier / PIC...">

                                <button type="submit" id="filter-submit-button"
                                    class="btn-primary flex justify-center items-center rounded-lg"
                                    title="Terapkan Filter">
                                    <i class="fas fa-filter"></i>
                                </button>

                                <button type="button" id="export-excel-button"
                                    class="w-10 bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-2 rounded-lg transition-colors shadow-sm inline-flex items-center justify-center"
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
                    <div class="table-container overflow-x-auto">
                        <table class="table-modern w-full text-xs" id="program-table">
                            <thead>
                                <tr>
                                    <th class="whitespace-nowrap text-center w-20">Aksi</th>
                                    <th class="whitespace-nowrap">No</th>
                                    <th class="whitespace-nowrap">PIC</th>
                                    <th class="whitespace-nowrap">Supplier</th>
                                    <th class="whitespace-nowrap">Cabang</th>
                                    <th class="whitespace-nowrap">Periode Prg</th>
                                    <th class="whitespace-nowrap">Nama Program</th>
                                    <th class="whitespace-nowrap">No Dokumen</th>
                                    <th class="whitespace-nowrap text-right">Nilai Program</th>
                                    <th class="whitespace-nowrap text-center">MOP</th>
                                    <th class="whitespace-nowrap text-center">TOP</th>
                                    <th class="whitespace-nowrap text-right">Nilai Transfer</th>
                                    <th class="whitespace-nowrap text-center">Tgl Transfer</th>
                                    <th class="whitespace-nowrap text-center">Tgl FPK</th>
                                    <th class="whitespace-nowrap">NSFP</th>
                                    <th class="whitespace-nowrap text-right">DPP</th>
                                    <th class="whitespace-nowrap text-right">PPN</th>
                                    <th class="whitespace-nowrap text-right">PPH</th>
                                    <th class="whitespace-nowrap">Bukpot</th>
                                </tr>
                            </thead>
                            <tbody id="program-table-body">
                                <tr>
                                    <td colspan="18" class="text-center p-8">
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
    <div id="modal-finance" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title"
        role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:p-0">

            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>

            <div
                class="relative bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:max-w-md sm:w-full border border-pink-100">
                <form id="form-finance">
                    <input type="hidden" name="mode" value="finance">
                    <input type="hidden" name="nomor_dokumen" id="fin_nomor_dokumen">

                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="flex justify-between items-center mb-4 border-b pb-2">
                            <div class="flex items-center gap-2">
                                <div
                                    class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-600">
                                    <i class="fas fa-wallet"></i>
                                </div>
                                <h3 class="text-lg leading-6 font-medium text-gray-900">Update Finance</h3>
                            </div>
                            <button type="button" class="btn-close-finance text-gray-400 hover:text-gray-500">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>

                        <div class="mb-4 p-3 bg-blue-50 text-blue-800 rounded text-xs border border-blue-100">
                            Dokumen: <span id="fin_display_doc" class="font-mono font-bold"></span>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Nilai Transfer
                                    (Rp)</label>
                                <input type="text" name="nilai_transfer" id="fin_nilai_transfer"
                                    class="input-modern w-full font-mono text-right" placeholder="0">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Tanggal Transfer</label>
                                <input type="date" name="tanggal_transfer" id="fin_tanggal_transfer"
                                    class="input-modern w-full">
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-2">
                        <button type="submit"
                            class="btn-primary w-full sm:w-auto px-4 py-2 text-white rounded bg-blue-600 hover:bg-blue-700">Simpan
                        </button>
                        <button type="button"
                            class="btn-close-finance mt-3 w-full sm:w-auto px-4 py-2 border rounded bg-white text-gray-700 hover:bg-gray-50">Batal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <div id="modal-tax" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog"
        aria-modal="true">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:p-0">

            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>

            <div
                class="relative bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:max-w-lg sm:w-full border border-pink-100">
                <form id="form-tax">
                    <input type="hidden" name="mode" value="tax">
                    <input type="hidden" name="nomor_dokumen" id="tax_nomor_dokumen">

                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="flex justify-between items-center mb-4 border-b pb-2">
                            <div class="flex items-center gap-2">
                                <div
                                    class="w-8 h-8 rounded-full bg-purple-100 flex items-center justify-center text-purple-600">
                                    <i class="fas fa-file-invoice-dollar"></i>
                                </div>
                                <h3 class="text-lg leading-6 font-medium text-gray-900">Update Tax</h3>
                            </div>
                            <button type="button" class="btn-close-tax text-gray-400 hover:text-gray-500">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>

                        <div class="mb-4 p-3 bg-purple-50 text-purple-800 rounded text-xs border border-purple-100">
                            Dokumen: <span id="tax_display_doc" class="font-mono font-bold"></span>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div class="col-span-2">
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Tanggal FPK</label>
                                <input type="date" name="tgl_fpk" id="tax_tgl_fpk" class="input-modern w-full">
                            </div>
                            <div class="col-span-2">
                                <label class="block text-xs font-semibold text-gray-700 mb-1">NSFP (Faktur
                                    Pajak)</label>
                                <input type="text" name="nsfp" id="tax_nsfp" class="input-modern w-full"
                                    placeholder="No Seri Faktur Pajak">
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">DPP</label>
                                <input type="text" name="dpp" id="tax_dpp"
                                    class="input-modern w-full font-mono text-right" placeholder="0">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">PPN</label>
                                <input type="text" name="ppn" id="tax_ppn"
                                    class="input-modern w-full font-mono text-right" placeholder="0">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">PPH</label>
                                <input type="text" name="pph" id="tax_pph"
                                    class="input-modern w-full font-mono text-right" placeholder="0">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Nomor Bukpot</label>
                                <input type="text" name="nomor_bukpot" id="tax_nomor_bukpot" class="input-modern w-full"
                                    placeholder="Nomor Bukti Potong">
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-2">
                        <button type="submit"
                            class="btn-primary w-full sm:w-auto px-4 py-2 text-white rounded bg-purple-600 hover:bg-purple-700">Simpan
                        </button>
                        <button type="button"
                            class="btn-close-tax mt-3 w-full sm:w-auto px-4 py-2 border rounded bg-white text-gray-700 hover:bg-gray-50">Batal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="/src/js/middleware_auth.js"></script>
    <script src="../../js/program_supplier/laporan_program_supplier_handler.js" type="module"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>

</html>