<?php
session_start();
include '../../../aa_kon_sett.php';

$tanggal_hari_ini = date('Y-m-d');
$tanggal_bulan_lalu = date('Y-m-d', strtotime('-1 week'));

$default_tgl_mulai = $tanggal_bulan_lalu;
$default_tgl_selesai = $tanggal_hari_ini;
$default_page = 1;

$tgl_mulai = $_GET['tgl_mulai'] ?? $default_tgl_mulai;
$tgl_selesai = $_GET['tgl_selesai'] ?? $default_tgl_selesai;
$page = (int) ($_GET['page'] ?? $default_page);

require_once __DIR__ . '/../../component/menu_handler.php';
$menuHandler = new MenuHandler('uang_brankas');

if (!$menuHandler->initialize()) {
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Uang Brangkas</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/all.min.css">
    <link rel="stylesheet" href="../../style/header.css">
    <link rel="stylesheet" href="../../style/sidebar.css">
    <link rel="stylesheet" href="../../style/animation-fade-in.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../style/default-font.css">
    <link rel="stylesheet" href="../../output2.css">
    <link rel="stylesheet" href="../../style/pink-theme.css">
    <link rel="icon" type="image/png" href="../../../public/images/logo1.png">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
                                <i class="fa-solid fa-vault fa-lg"></i>
                            </div>
                            <div>
                                <h1 id="page-title" class="text-xl font-bold text-gray-800 mb-1">Uang Brangkas</h1>
                                <p id="page-subtitle" class="text-xs text-gray-600">Pencatatan fisik uang (Cash Opname).
                                </p>
                            </div>
                        </div>
                        <button id="btn-add-data" class="btn-primary inline-flex items-center justify-center gap-2">
                            <i class="fas fa-plus"></i>
                            <span>Input Baru</span>
                        </button>
                    </div>
                </div>

                <div class="filter-card-simple mb-4">
                    <form id="filter-form" class="grid grid-cols-1 md:grid-cols-4 gap-3 items-end">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-2">Dari Tanggal</label>
                            <input type="date" name="tgl_mulai" id="tgl_mulai" class="input-modern w-full"
                                value="<?= $tgl_mulai ?>">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-2">Sampai Tanggal</label>
                            <input type="date" name="tgl_selesai" id="tgl_selesai" class="input-modern w-full"
                                value="<?= $tgl_selesai ?>">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-2">Pilih Cabang</label>
                            <select name="kd_store" id="kd_store" class="input-modern w-full">
                                <option value="" selected disabled>-- Pilih Cabang --</option>
                            </select>
                        </div>
                        <button type="submit" class="btn-primary flex items-center justify-center gap-2">
                            <i class="fas fa-filter"></i> Tampilkan
                        </button>
                    </form>
                </div>

                <div class="filter-card">
                    <div class="flex flex-wrap justify-between items-center mb-3 gap-3">
                        <h3 class="text-lg font-bold text-gray-800">
                            <i class="fas fa-list text-pink-600 mr-2"></i>
                            Riwayat Perhitungan
                        </h3>
                    </div>
                    <div class="table-container overflow-x-auto">
                        <table class="table-modern w-full" id="data-table">
                            <thead>
                                <tr>
                                    <th>Waktu Hitung</th>
                                    <th>Cabang</th>
                                    <th>User Hitung</th>
                                    <th>User Check</th>
                                    <th>Total Nominal</th>
                                    <th>Keterangan</th>
                                    <th>Detail</th>
                                </tr>
                            </thead>
                            <tbody id="table-body">
                                <tr>
                                    <td colspan="7" class="text-center p-8">
                                        <div class="text-gray-400 mb-2">
                                            <i class="fas fa-store fa-3x"></i>
                                        </div>
                                        <p class="text-gray-500 font-medium">Silahkan pilih cabang dan klik Tampilkan
                                        </p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div id="pagination-container" class="flex justify-between items-center mt-4">
                        <span id="pagination-info" class="text-sm text-gray-600"></span>
                        <div id="pagination-links" class="flex items-center gap-2"></div>
                    </div>
                </div>

            </div>
        </section>
    </main>

    <div id="modal-form" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog"
        aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"
                id="modal-backdrop"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div
                class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
                <form id="form-transaksi">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="flex justify-between items-center mb-4 border-b pb-2">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Input Uang Brangkas
                            </h3>
                            <button type="button" id="btn-close-modal" class="text-gray-400 hover:text-gray-500">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>

                        <input type="hidden" name="pk_tanggal" id="pk_tanggal">
                        <input type="hidden" name="pk_jam" id="pk_jam">
                        <input type="hidden" name="pk_user_hitung" id="pk_user_hitung">
                        <input type="hidden" name="mode" id="form_mode" value="insert">

                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                            <div class="lg:col-span-1 space-y-4">
                                <div class="p-3 border border-pink-200 rounded-lg bg-white">
                                    <label class="block text-xs font-bold text-gray-700 mb-1">Pilih Cabang <span
                                            class="text-red-500">*</span></label>
                                    <select name="kd_store" id="modal_kd_store" class="input-modern w-full" required>
                                        <option value="">-- Pilih Cabang --</option>
                                    </select>
                                </div>
                                <div class="bg-pink-50 p-4 rounded-lg border border-pink-100">
                                    <label class="block text-xs font-bold text-gray-700 mb-1">Total Nominal</label>
                                    <div class="text-2xl font-bold text-pink-600" id="display-total-nominal">Rp 0</div>
                                </div>
                                <div class="p-3 border border-red-200 rounded-lg bg-red-50">
                                    <h4 class="text-xs font-bold text-red-600 mb-2 border-b border-red-200 pb-1">
                                        Otorisasi User Check</h4>
                                    <div class="mb-2">
                                        <label class="block text-xs font-semibold text-gray-700 mb-1">User Check (Nama
                                            Pengguna)</label>
                                        <input type="text" name="nama_user_cek" id="nama_user_cek"
                                            class="input-modern w-full" placeholder="Ketik Nama Inisial" required
                                            autocomplete="off">
                                    </div>
                                    <div id="div-password">
                                        <label class="block text-xs font-semibold text-gray-700 mb-1">Kode
                                            Otorisasi</label>
                                        <input type="password" name="kode_otorisasi" id="kode_otorisasi"
                                            class="input-modern w-full" placeholder="Password Otorisasi" required>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1">Keterangan</label>
                                    <textarea name="keterangan" id="keterangan" rows="2"
                                        class="input-modern w-full"></textarea>
                                </div>
                            </div>

                            <div class="lg:col-span-2">
                                <div class="text-xs font-bold text-gray-500 uppercase tracking-wide mb-2">Uang Kertas
                                </div>
                                <div class="grid grid-cols-2 gap-4 mb-4">
                                    <div class="flex items-center justify-between bg-gray-50 p-2 rounded">
                                        <label class="text-sm font-medium w-16">100.000</label>
                                        <input type="number" name="qty_100rb"
                                            class="input-denim input-modern w-24 text-right" min="0" value="0">
                                    </div>
                                    <div class="flex items-center justify-between bg-gray-50 p-2 rounded">
                                        <label class="text-sm font-medium w-16">5.000</label>
                                        <input type="number" name="qty_5rb"
                                            class="input-denim input-modern w-24 text-right" min="0" value="0">
                                    </div>

                                    <div class="flex items-center justify-between bg-gray-50 p-2 rounded">
                                        <label class="text-sm font-medium w-16">50.000</label>
                                        <input type="number" name="qty_50rb"
                                            class="input-denim input-modern w-24 text-right" min="0" value="0">
                                    </div>
                                    <div class="flex items-center justify-between bg-gray-50 p-2 rounded">
                                        <label class="text-sm font-medium w-16">2.000</label>
                                        <input type="number" name="qty_2rb"
                                            class="input-denim input-modern w-24 text-right" min="0" value="0">
                                    </div>

                                    <div class="flex items-center justify-between bg-gray-50 p-2 rounded">
                                        <label class="text-sm font-medium w-16">20.000</label>
                                        <input type="number" name="qty_20rb"
                                            class="input-denim input-modern w-24 text-right" min="0" value="0">
                                    </div>
                                    <div class="flex items-center justify-between bg-gray-50 p-2 rounded">
                                        <label class="text-sm font-medium w-16">1.000</label>
                                        <input type="number" name="qty_1rb"
                                            class="input-denim input-modern w-24 text-right" min="0" value="0">
                                    </div>

                                    <div class="flex items-center justify-between bg-gray-50 p-2 rounded">
                                        <label class="text-sm font-medium w-16">10.000</label>
                                        <input type="number" name="qty_10rb"
                                            class="input-denim input-modern w-24 text-right" min="0" value="0">
                                    </div>
                                    <div></div>
                                </div>

                                <div class="text-xs font-bold text-gray-500 uppercase tracking-wide mb-2 mt-4">Uang Koin
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div class="flex items-center justify-between bg-gray-50 p-2 rounded">
                                        <label class="text-sm font-medium w-16">1.000</label>
                                        <input type="number" name="qty_1000_koin"
                                            class="input-denim input-modern w-24 text-right" min="0" value="0">
                                    </div>
                                    <div class="flex items-center justify-between bg-gray-50 p-2 rounded">
                                        <label class="text-sm font-medium w-16">100</label>
                                        <input type="number" name="qty_100_koin"
                                            class="input-denim input-modern w-24 text-right" min="0" value="0">
                                    </div>

                                    <div class="flex items-center justify-between bg-gray-50 p-2 rounded">
                                        <label class="text-sm font-medium w-16">500</label>
                                        <input type="number" name="qty_500_koin"
                                            class="input-denim input-modern w-24 text-right" min="0" value="0">
                                    </div>
                                    <div></div>

                                    <div class="flex items-center justify-between bg-gray-50 p-2 rounded">
                                        <label class="text-sm font-medium w-16">200</label>
                                        <input type="number" name="qty_200_koin"
                                            class="input-denim input-modern w-24 text-right" min="0" value="0">
                                    </div>
                                    <div></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-2">
                        <button type="submit" id="btn-save"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-pink-600 text-base font-medium text-white hover:bg-pink-700 focus:outline-none sm:w-auto sm:text-sm">
                            <i class="fas fa-save mr-2 mt-1"></i> Simpan Data
                        </button>
                        <button type="button" id="btn-cancel"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:w-auto sm:text-sm">
                            Tutup
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="/src/js/middleware_auth.js"></script>
    <script src="../../js/uang_brangkas/handler.js" type="module"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

</body>

</html>