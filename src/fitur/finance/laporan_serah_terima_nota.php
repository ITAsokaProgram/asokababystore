<?php
session_start();
include '../../../aa_kon_sett.php';

// Default Tanggal
$tanggal_kemarin = date('Y-m-d', strtotime('-1 day'));
$default_tgl_mulai = $tanggal_kemarin;
$default_tgl_selesai = $tanggal_kemarin;

$default_filter_type = 'month';
$default_bulan = date('m');
$default_tahun = date('Y');

$tgl_mulai = $_GET['tgl_mulai'] ?? $default_tgl_mulai;
$tgl_selesai = $_GET['tgl_selesai'] ?? $default_tgl_selesai;

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
// Pastikan permission key ini sesuai dengan database Anda, atau ganti 'finance_laporan' jika perlu
// $menuHandler = new MenuHandler('finance_laporan_surat_terima');
// if (!$menuHandler->initialize()) {
//     exit();
// }
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Serah Terima Nota</title>
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
                                <i class="fa-solid fa-file-invoice fa-lg"></i>
                            </div>
                            <div>
                                <h1 id="page-title" class="text-xl font-bold text-gray-800 mb-1">Laporan Serah Terima
                                    Nota
                                </h1>
                                <p id="page-subtitle" class="text-xs text-gray-600">Memuat data...</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <a href="input_serah_terima_nota.php"
                                class="btn-primary flex items-center gap-2 px-4 py-2 shadow-lg shadow-pink-500/30 rounded-lg text-white transition-transform hover:scale-105 text-sm decoration-0">
                                <i class="fas fa-plus"></i> <span>Input Baru</span>
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
                            <label class="block text-xs font-semibold text-gray-700 mb-2">Kontra?</label>
                            <select name="status_kontra" id="filter_status_kontra" class="input-modern w-full">
                                <option value="">Semua</option>
                                <option value="Sudah" <?= ($_GET['status_kontra'] ?? '') == 'Sudah' ? 'selected' : '' ?>>
                                    Sudah</option>
                                <option value="Belum" <?= ($_GET['status_kontra'] ?? '') == 'Belum' ? 'selected' : '' ?>>
                                    Belum</option>
                            </select>
                        </div>

                        <div class="lg:col-span-1">
                            <label class="block text-xs font-semibold text-gray-700 mb-2">Bayar?</label>
                            <select name="status_bayar" id="filter_status_bayar" class="input-modern w-full">
                                <option value="">Semua</option>
                                <option value="Sudah" <?= ($_GET['status_bayar'] ?? '') == 'Sudah' ? 'selected' : '' ?>>
                                    Sudah</option>
                                <option value="Belum" <?= ($_GET['status_bayar'] ?? '') == 'Belum' ? 'selected' : '' ?>>
                                    Belum</option>
                            </select>
                        </div>

                        <div class="lg:col-span-1">
                            <label class="block text-xs font-semibold text-gray-700 mb-2">Pinjam?</label>
                            <select name="status_pinjam" id="filter_status_pinjam" class="input-modern w-full">
                                <option value="">Semua</option>
                                <option value="Pinjam" <?= ($_GET['status_pinjam'] ?? '') == 'Pinjam' ? 'selected' : '' ?>>
                                    Pinjam</option>
                                <option value="Tidak" <?= ($_GET['status_pinjam'] ?? '') == 'Tidak' ? 'selected' : '' ?>>
                                    Tidak</option>
                            </select>
                        </div>
                        <div class="lg:col-span-2 lg:col-start-4 lg:row-start-1">
                            <label for="search_supplier" class="block text-xs font-semibold text-gray-700 mb-2">
                                <i class="fas fa-search text-pink-600 mr-1"></i> Cari Data
                            </label>
                            <div class="flex gap-2">
                                <input type="text" name="search_supplier" id="search_supplier"
                                    class="input-modern w-full"
                                    value="<?php echo htmlspecialchars($_GET['search_supplier'] ?? ''); ?>"
                                    placeholder="Supplier / No Nota / Faktur...">

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
                                    <th class="whitespace-nowrap">No</th>
                                    <th class="whitespace-nowrap">Tgl Invoice</th>
                                    <th class="whitespace-nowrap">Nama Supplier</th>
                                    <th class="whitespace-nowrap">No Faktur</th>
                                    <th class="text-right whitespace-nowrap">Nominal Awal</th>
                                    <th class="text-right whitespace-nowrap">Nominal Revisi</th>
                                    <th class="text-right whitespace-nowrap">Selisih</th>
                                    <th class="whitespace-nowrap">Tgl Diserahkan</th>
                                    <th class="whitespace-nowrap">Tgl Diterima</th>

                                    <th class="text-center whitespace-nowrap">Status Terima</th>

                                    <th class="text-center whitespace-nowrap">Kontra</th>
                                    <th class="text-center whitespace-nowrap">Bayar</th>
                                    <th class="text-center whitespace-nowrap">Pinjam</th>

                                    <th class="whitespace-nowrap">Diberikan</th>
                                    <th class="whitespace-nowrap">Penerima</th>

                                    <th class="text-center whitespace-nowrap">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="receipt-table-body">
                                <tr>
                                    <td colspan="14" class="text-center p-8">
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
    <div id="modal-otorisasi" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title"
        role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div
                class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full">
                <form id="form-otorisasi">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="flex justify-between items-center mb-4 border-b pb-2">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">Update Status Nota</h3>
                            <button type="button" class="btn-close-auth text-gray-400 hover:text-gray-500">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>

                        <input type="hidden" id="auth_nota_id" name="no_faktur">

                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Status Terima</label>
                                <select name="status" id="auth_status_baru" class="input-modern w-full">
                                    <option value="Belum Terima">Belum Terima</option>
                                    <option value="Sudah Terima">Sudah Terima</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Status Kontra</label>
                                <select name="status_kontra" id="auth_status_kontra" class="input-modern w-full">
                                    <option value="Belum">Belum</option>
                                    <option value="Sudah">Sudah</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Status Bayar</label>
                                <select name="status_bayar" id="auth_status_bayar" class="input-modern w-full">
                                    <option value="Belum">Belum</option>
                                    <option value="Sudah">Sudah</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Status Pinjam</label>
                                <select name="status_pinjam" id="auth_status_pinjam" class="input-modern w-full">
                                    <option value="Tidak">Tidak</option>
                                    <option value="Pinjam">Pinjam</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Tanggal Terima</label>
                            <input type="date" name="tgl_diterima" id="auth_tgl_diterima" class="input-modern w-full">
                        </div>
                        <div class="mb-4">
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Penerima</label>
                            <input type="text" name="penerima" id="auth_penerima" class="input-modern w-full"
                                placeholder="Nama Penerima Nota" autocomplete="off">
                        </div>

                        <div class="p-4 border border-red-200 rounded-lg bg-red-50">
                            <div class="mb-3">
                                <label class="block text-xs font-semibold text-gray-700 mb-1">User (Inisial)</label>
                                <input type="text" name="nama_user_cek" class="input-modern w-full"
                                    placeholder="Contoh: ADM" required autocomplete="off">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Kode Otorisasi</label>
                                <input type="password" name="kode_otorisasi" class="input-modern w-full"
                                    placeholder="Password Otorisasi" required>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-2">
                        <button type="submit" class="btn-primary w-full sm:w-auto px-4 py-2 text-white rounded">Simpan
                            Perubahan</button>
                        <button type="button"
                            class="btn-close-auth mt-3 w-full sm:w-auto px-4 py-2 border rounded">Batal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="/src/js/middleware_auth.js"></script>
    <script src="../../js/finance/laporan_serah_terima_nota_handler.js" type="module"></script>

    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>

</html>