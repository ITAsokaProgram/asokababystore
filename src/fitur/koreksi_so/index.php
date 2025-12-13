<?php
session_start();
include '../../../aa_kon_sett.php';

$default_mulai = date('Y-m-16', strtotime('last month'));
$default_selesai = date('Y-m-15');

$tgl_mulai = $_GET['tgl_mulai'] ?? $default_mulai;
$tgl_selesai = $_GET['tgl_selesai'] ?? $default_selesai;
$kd_store = $_GET['kd_store'] ?? 'all';

require_once __DIR__ . '/../../component/menu_handler.php';

$menuHandler = new MenuHandler('koreksi_so');

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
    <title>Laporan Koreksi SO</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/all.min.css">
    <link rel="stylesheet" href="../../style/header.css">
    <link rel="stylesheet" href="../../style/sidebar.css">
    <link rel="stylesheet" href="../../style/animation-fade-in.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../style/default-font.css">
    <link rel="stylesheet" href="../../output2.css">
    <link rel="stylesheet" href="../../style/pink-theme.css">
    <link rel="icon" type="image/png" href="../../../public/images/logo1.png">

    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/exceljs/4.3.0/exceljs.min.js"></script>
</head>

<body class="bg-gray-50" x-data="{ showDetailModal: false, modalTitle: '', modalItems: [], isLoadingModal: false }"
    @open-modal.window="showDetailModal = true; modalTitle = $event.detail.title; modalItems = []; isLoadingModal = true"
    @update-modal.window="modalItems = $event.detail.items; isLoadingModal = false">
    <?php include '../../component/navigation_report.php' ?>

    <?php include '../../component/sidebar_report.php' ?>

    <main id="main-content" class="flex-1 p-4 ml-64">
        <section class="min-h-screen">
            <div class="max-w-7xl mx-auto">

                <div class="header-card p-4 rounded-2xl mb-4">
                    <div class="flex items-center justify-between flex-wrap gap-3">
                        <div class="flex items-center gap-3">
                            <div class="icon-wrapper">
                                <i class="fa-solid fa-file-pen fa-lg"></i>
                            </div>
                            <div>
                                <h1 id="page-title" class="text-xl font-bold text-gray-800 mb-1">Laporan Koreksi SO</h1>
                                <p id="page-subtitle" class="text-xs text-gray-600">Memuat data...</p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3">
                        <div class="summary-card total flex gap-4 items-center">
                            <div class="summary-icon">
                                <i class="fas fa-boxes-packing fa-lg"></i>
                            </div>
                            <div>
                                <h3 class="text-xs font-semibold text-gray-600 mb-1">Total Selisih Qty</h3>
                                <p id="summary-qty" class="text-2xl font-bold truncate text-gray-900">-</p>
                            </div>
                        </div>
                        <div class="summary-card flex gap-4 items-center total">
                            <div class="summary-icon">
                                <i class="fas fa-money-bill-wave fa-lg"></i>
                            </div>
                            <div>
                                <h3 class="text-xs font-semibold text-gray-600 mb-1">Total Netto</h3>
                                <p id="summary-netto" class="text-2xl font-bold truncate text-gray-900">-</p>
                            </div>
                        </div>
                        <div class="summary-card flex gap-4 items-center total">
                            <div class="summary-icon">
                                <i class="fas fa-file-invoice-dollar fa-lg"></i>
                            </div>
                            <div>
                                <h3 class="text-xs font-semibold text-gray-600 mb-1">Total PPN</h3>
                                <p id="summary-ppn" class="text-2xl font-bold truncate text-gray-900">-</p>
                            </div>
                        </div>
                        <div class="summary-card flex gap-4 items-center warning">
                            <div class="summary-icon">
                                <i class="fa-solid fa-calculator fa-lg"></i>
                            </div>
                            <div>
                                <h3 class="text-xs font-semibold text-gray-600 mb-1">Grand Total (Rp)</h3>
                                <p id="summary-total" class="text-2xl font-bold truncate text-yellow-600">-</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="filter-card-simple">
                    <form id="filter-form" class="grid grid-cols-1 md:grid-cols-4 gap-3 items-end">
                        <div>
                            <label for="tgl_mulai" class="block text-xs font-semibold text-gray-700 mb-2">
                                <i class="fas fa-calendar-alt text-pink-600 mr-1"></i> Dari Tanggal
                            </label>
                            <input type="date" name="tgl_mulai" id="tgl_mulai" class="input-modern w-full"
                                value="<?php echo htmlspecialchars($tgl_mulai); ?>">
                        </div>
                        <div>
                            <label for="tgl_selesai" class="block text-xs font-semibold text-gray-700 mb-2">
                                <i class="fas fa-calendar-alt text-pink-600 mr-1"></i> Sampai Tanggal
                            </label>
                            <input type="date" name="tgl_selesai" id="tgl_selesai" class="input-modern w-full"
                                value="<?php echo htmlspecialchars($tgl_selesai); ?>">
                        </div>
                        <div>
                            <label for="kd_store" class="block text-xs font-semibold text-gray-700 mb-2">
                                <i class="fas fa-store text-pink-600 mr-1"></i> Pilih Cabang
                            </label>
                            <select name="kd_store" id="kd_store" class="input-modern w-full">
                                <option value="all">Seluruh Store</option>
                            </select>
                        </div>
                        <div class="flex items-center gap-2">
                            <button type="submit" id="filter-submit-button"
                                class="btn-primary inline-flex items-center justify-center gap-2">
                                <i class="fas fa-filter"></i>
                                <span>Tampilkan</span>
                            </button>

                            <button type="button" id="btn-export-excel"
                                class="btn-secondary-outline inline-flex items-center justify-center gap-2 bg-white text-green-600 border-green-600 hover:bg-green-50">
                                <i class="fas fa-file-excel"></i>
                                <span>Export Excel</span>
                            </button>
                        </div>
                        <input type="hidden" name="page" value="1">
                    </form>
                </div>

                <div class="filter-card">
                    <div class="flex flex-wrap justify-between items-center mb-3 gap-3">
                        <h3 class="text-lg font-bold text-gray-800">
                            <i class="fas fa-list text-pink-600 mr-2"></i> Hasil Laporan
                        </h3>
                    </div>

                    <div class="table-container">
                        <table class="table-modern" id="koreksi-table">
                            <thead>
                                <tr>
                                    <th>Supplier</th>
                                    <th>Total Selisih Qty</th>
                                    <th>Total Avg Cost (Netto)</th>
                                    <th>Total PPN</th>
                                    <th>Total (Grand)</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="koreksi-table-body">
                                <tr>
                                    <td colspan="7" class="text-center p-8">
                                        <div class="spinner-simple"></div>
                                        <p class="mt-3 text-gray-500 font-medium">Memuat data...</p>
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

        <div x-show="showDetailModal" style="display: none;"
            class="fixed inset-0 z-50 overflow-y-auto bg-gray-900 bg-opacity-50 flex items-center justify-center p-4"
            x-transition.opacity>
            <div class="bg-white rounded-xl shadow-2xl w-full max-w-7xl flex flex-col max-h-[90vh]"
                @click.away="showDetailModal = false">

                <div class="p-4 border-b flex justify-between items-center bg-gray-50 rounded-t-xl">
                    <h3 class="text-lg font-bold text-gray-800" x-text="modalTitle">Detail Item</h3>
                    <button @click="showDetailModal = false" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times fa-lg"></i>
                    </button>
                </div>

                <div class="p-4 overflow-auto flex-1">
                    <div x-show="isLoadingModal" class="text-center py-10">
                        <div class="spinner-simple mx-auto"></div>
                        <p class="mt-2 text-gray-500">Mengambil detail...</p>
                    </div>

                    <table x-show="!isLoadingModal" class="table-modern w-full">
                        <thead>
                            <tr>
                                <th>No Faktur</th>
                                <th>No Kor</th>
                                <th>Acc Kor</th>
                                <th>Nama Karyawan</th>
                                <th>PLU</th>
                                <th>Deskripsi</th>
                                <th class="text-center">Sel Qty</th>
                                <th class="text-right">Avg Cost</th>
                                <th class="text-right">PPN Kor</th>
                                <th class="text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="(item, index) in modalItems" :key="index">
                                <tr class="hover:bg-gray-50">
                                    <td x-text="item.no_faktur" class="text-xs"></td>
                                    <td x-text="item.no_kor" class="text-xs"></td>
                                    <td x-text="item.acc_kor" class="text-xs"></td>
                                    <td x-text="item.nama_kar"></td>
                                    <td x-text="item.plu" class="font-mono text-xs"></td>
                                    <td x-text="item.desc"></td>
                                    <td x-text="formatNumber(item.sel_qty)"
                                        :class="item.sel_qty < 0 ? 'text-red-600 font-bold text-center' : 'text-gray-700 text-center'">
                                    </td>
                                    <td x-text="formatRupiah(item.avg_cost)" class="text-right"></td>
                                    <td x-text="formatRupiah(item.ppn_kor)" class="text-right"></td>
                                    <td x-text="formatRupiah(item.total_row)" class="font-bold text-right"></td>
                                </tr>
                            </template>
                            <tr x-show="modalItems.length === 0">
                                <td colspan="10" class="text-center py-4 text-gray-500">
                                    Tidak ada item detail ditemukan.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="p-4 border-t bg-gray-50 rounded-b-xl text-right">
                    <button @click="showDetailModal = false" class="btn-secondary-outline px-4 py-2">Tutup</button>
                </div>
            </div>
        </div>

    </main>

    <script src="/src/js/middleware_auth.js"></script>
    <script src="../../js/koreksi_so/handler.js" type="module"></script>
</body>

</html>