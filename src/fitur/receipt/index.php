<?php
session_start();
include '../../../aa_kon_sett.php';

// Default tanggal tetap ada untuk value input, tapi data tidak diload otomatis (via JS)
$tgl_selesai = date('Y-m-d');
$tgl_mulai = date('Y-m-d', strtotime('-1 day'));
$page = (int) ($_GET['page'] ?? 1);

require_once __DIR__ . '/../../component/menu_handler.php';
$menuHandler = new MenuHandler('receipt_index');
if (!$menuHandler->initialize()) {
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checking Receipt</title>
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
</head>

<body class="bg-gray-50" x-data="{ 
    detailModalOpen: false, 
    summaryModalOpen: false,
    detailData: {},
    summaryTitle: '',
    summaryList: [],
    
    formatRupiah(number) {
        if (isNaN(number) || number === null) return '0';
        return new Intl.NumberFormat('id-ID', { style: 'decimal', minimumFractionDigits: 0, maximumFractionDigits: 0 }).format(number);
    },
    
    formatDate(dateString) {
        if (!dateString) return '-';
        return dateString.substring(0, 10);
    }
}" @open-detail-modal.window="detailModalOpen = true; detailData = $event.detail"
    @open-summary-modal.window="summaryModalOpen = true; summaryTitle = $event.detail.title; summaryList = $event.detail.list">
    <?php include '../../component/navigation_report.php' ?>
    <?php include '../../component/sidebar_report.php' ?>

    <main id="main-content" class="flex-1 p-4 ml-64">
        <section class="min-h-screen">
            <div class="max-w-7xl mx-auto">

                <div class="header-card p-4 rounded-2xl mb-4">
                    <div class="flex items-center justify-between flex-wrap gap-3">
                        <div class="flex items-center gap-3">
                            <div class="icon-wrapper">
                                <i class="fa-solid fa-receipt fa-lg"></i>
                            </div>
                            <div>
                                <h1 class="text-xl font-bold text-gray-800 mb-1">Checking Receipt</h1>
                                <p class="text-xs text-gray-600">Data Checking Receipt (c_receipt).</p>
                            </div>
                        </div>
                        <a href="create.php"
                            class="btn-primary inline-flex items-center justify-center gap-2 no-underline">
                            <i class="fa-solid fa-plus"></i>
                            <span>Buat Receipt</span>
                        </a>
                    </div>

                    <div class="mt-4 grid grid-cols-1 md:grid-cols-4 gap-3">

                        <div id="btn-show-selisih"
                            class="summary-card flex gap-4 items-center bg-red-50 border border-red-100 p-3 rounded-xl cursor-pointer hover:bg-red-100 transition">
                            <div class="summary-icon bg-red-100 text-red-600 p-3 rounded-lg">
                                <i class="fas fa-exclamation-triangle fa-lg"></i>
                            </div>
                            <div>
                                <h3 class="text-xs font-semibold text-gray-600 mb-1">Jumlah data selisih</h3>
                                <p id="summary-total-selisih" class="text-xl font-bold truncate text-red-600">0</p>
                                <p class="text-[10px] text-gray-500">Klik untuk detail</p>
                            </div>
                        </div>

                        <div id="btn-show-rupiah-selisih"
                            class="summary-card flex gap-4 items-center bg-pink-50 border border-pink-100 p-3 rounded-xl cursor-pointer hover:bg-pink-100 transition">
                            <div class="summary-icon bg-pink-100 text-pink-600 p-3 rounded-lg">
                                <i class="fas fa-money-bill-wave fa-lg"></i>
                            </div>
                            <div>
                                <h3 class="text-xs font-semibold text-gray-600 mb-1">Total Nominal Selisih</h3>
                                <p id="summary-total-rupiah-selisih" class="text-xl font-bold truncate text-pink-600">
                                    Rp 0</p>
                                <p class="text-[10px] text-gray-500">Klik untuk detail</p>
                            </div>
                        </div>

                        <div id="btn-show-missing"
                            class="summary-card flex gap-4 items-center bg-orange-50 border border-orange-100 p-3 rounded-xl cursor-pointer hover:bg-orange-100 transition">
                            <div class="summary-icon bg-orange-100 text-orange-600 p-3 rounded-lg">
                                <i class="fas fa-search-minus fa-lg"></i>
                            </div>
                            <div>
                                <h3 class="text-xs font-semibold text-gray-600 mb-1">Nominal Belum Check</h3>
                                <p id="summary-total-missing" class="text-xl font-bold truncate text-orange-600">Rp 0
                                </p>
                                <p class="text-[10px] text-gray-500">Klik untuk detail</p>
                            </div>
                        </div>

                        <div id="btn-show-notfound"
                            class="summary-card flex gap-4 items-center bg-gray-50 border border-gray-200 p-3 rounded-xl cursor-pointer hover:bg-gray-100 transition">
                            <div class="summary-icon bg-gray-200 text-gray-600 p-3 rounded-lg">
                                <i class="fas fa-question fa-lg"></i>
                            </div>
                            <div>
                                <h3 class="text-xs font-semibold text-gray-600 mb-1">Data Tidak Ditemukan</h3>
                                <p id="summary-total-notfound" class="text-xl font-bold truncate text-gray-700">0</p>
                                <p class="text-[10px] text-gray-500">Klik untuk detail</p>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="filter-card-simple mb-4">
                    <form id="filter-form" class="grid grid-cols-1 md:grid-cols-4 gap-3 items-end">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-2">Pilih Cabang</label>
                            <select name="kode_store" id="kode_store_filter" class="input-modern w-full cursor-pointer">
                                <option value="" disabled selected>Memuat...</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-2">Dari Tanggal (Tiba)</label>
                            <input type="date" name="tgl_mulai" id="tgl_mulai" class="input-modern w-full"
                                value="<?= $tgl_mulai ?>">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-2">Sampai Tanggal</label>
                            <input type="date" name="tgl_selesai" id="tgl_selesai" class="input-modern w-full"
                                value="<?= $tgl_selesai ?>">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-2">Cari No Faktur</label>
                            <input type="text" name="search" id="search" class="input-modern w-full"
                                placeholder="Ketik nomor faktur...">
                        </div>
                        <div class="md:col-span-4 flex justify-end">
                            <button type="submit" id="filter-submit-button"
                                class="btn-primary w-full inline-flex items-center justify-center gap-2 px-6">
                                <i class="fas fa-filter"></i>
                                <span>Tampilkan Data</span>
                            </button>
                        </div>
                    </form>
                </div>

                <div class="filter-card">
                    <div class="table-container">
                        <table class="table-modern" id="receipt-table">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th style="max-width: 140px;">Status</th>
                                    <th>Tgl Tiba</th>
                                    <th>Cabang</th>
                                    <th>Kd Supplier</th>
                                    <th>No Faktur</th>
                                    <th class="text-right">Total</th>
                                    <th>Keterangan</th>
                                </tr>
                            </thead>
                            <tbody id="receipt-table-body">
                                <tr>
                                    <td colspan="8" class="text-center p-8">
                                        <div
                                            class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-gray-100 mb-3">
                                            <i class="fas fa-search text-gray-400 text-xl"></i>
                                        </div>
                                        <p class="text-gray-500 font-medium">Silahkan pilih cabang dan klik "Tampilkan
                                            Data"</p>
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

    <div x-show="detailModalOpen" style="display: none;" class="relative z-50" aria-labelledby="modal-title"
        role="dialog" aria-modal="true">

        <div x-show="detailModalOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="detailModalOpen = false"></div>

        <div class="fixed inset-0 z-10 overflow-y-auto">
            <div class="flex min-h-screen items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <div x-show="detailModalOpen" x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    class="relative transform overflow-hidden rounded-xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg">

                    <div class="bg-pink-50 px-4 py-3 sm:px-6 flex justify-between items-center">
                        <h3 class="text-lg font-bold leading-6 text-pink-700">Detail Receipt</h3>
                        <button @click="detailModalOpen = false"
                            class="text-pink-400 hover:text-pink-600 focus:outline-none">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <div class="px-4 py-5 sm:p-6">
                        <div class="text-center pb-4 border-b border-gray-100">
                            <p class="text-sm text-gray-500">No Faktur</p>
                            <p class="text-xl font-mono font-bold text-gray-800" x-text="detailData.no_faktur"></p>
                            <div class="flex justify-center gap-2 mt-1">
                                <span class="text-xs px-2 py-1 bg-gray-100 rounded text-gray-600"
                                    x-text="detailData.kode_supp"></span>
                                <span class="text-xs px-2 py-1 bg-pink-100 text-pink-700 rounded"
                                    x-text="detailData.Nm_Alias || detailData.kode_store"></span>
                            </div>
                        </div>

                        <div class="mt-4">
                            <p class="text-[10px] font-bold tracking-wider text-gray-400 uppercase mb-2">Info Checking
                            </p>
                            <div class="flex justify-between items-center bg-gray-50 p-4 rounded-lg">
                                <span class="text-gray-600 text-sm">Total Diterima</span>
                                <span class="text-lg font-bold text-blue-600"
                                    x-text="formatRupiah(detailData.total_check)"></span>
                            </div>
                            <div class="mt-3">
                                <p class="text-xs text-gray-500 mb-1">Keterangan:</p>
                                <p class="text-sm text-gray-700 italic" x-text="detailData.keterangan || '-'"></p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="button" class="btn-primary w-full sm:w-auto justify-center"
                            @click="detailModalOpen = false">
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div x-show="summaryModalOpen" style="display: none;" class="relative z-50" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-gray-900 bg-opacity-60 backdrop-blur-sm transition-opacity"
            x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" @click="summaryModalOpen = false"></div>

        <div class="fixed inset-0 z-10 overflow-y-auto">
            <div class="flex min-h-screen items-center justify-center p-4 text-center">
                <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:w-full sm:max-w-md"
                    x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 scale-100">

                    <div
                        class="bg-white px-6 py-4 border-b border-gray-100 flex justify-between items-center sticky top-0 z-10">
                        <div>
                            <h3 class="text-lg font-bold text-gray-900" x-text="summaryTitle"></h3>
                            <p class="text-xs text-gray-500 mt-0.5">Daftar item berdasarkan filter tanggal.</p>
                        </div>
                        <button @click="summaryModalOpen = false"
                            class="text-gray-400 hover:text-gray-600 transition p-1">
                            <i class="fas fa-times text-lg"></i>
                        </button>
                    </div>

                    <div class="max-h-[60vh] overflow-y-auto px-2">
                        <ul class="divide-y divide-gray-50">
                            <template x-for="item in summaryList" :key="item.no_faktur">
                                <li class="p-3 hover:bg-gray-50 rounded-lg transition-colors duration-150">
                                    <div class="flex justify-between items-center">
                                        <div class="flex flex-col">
                                            <span
                                                class="text-[10px] uppercase tracking-wider text-gray-400 font-semibold mb-0.5">
                                                Tanggal
                                            </span>
                                            <span class="text-sm font-medium text-gray-700"
                                                x-text="formatDate(item.tgl_tiba)"></span>
                                        </div>

                                        <div class="flex flex-col items-end">
                                            <span
                                                class="text-[10px] uppercase tracking-wider text-gray-400 font-semibold mb-0.5">
                                                Nilai
                                            </span>
                                            <span class="text-sm font-bold font-mono text-pink-600"
                                                x-text="'Rp ' + formatRupiah(item.total)"></span>
                                        </div>
                                    </div>
                                </li>
                            </template>

                            <li x-show="summaryList.length === 0" class="py-12 text-center">
                                <div
                                    class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-gray-100 mb-3">
                                    <i class="fas fa-inbox text-gray-400 text-xl"></i>
                                </div>
                                <p class="text-sm text-gray-500">Tidak ada data untuk ditampilkan.</p>
                            </li>
                        </ul>
                    </div>

                    <div class="bg-gray-50 px-6 py-3 border-t border-gray-100">
                        <button @click="summaryModalOpen = false"
                            class="w-full btn-primary justify-center py-2.5 rounded-xl shadow-sm">
                            Tutup
                        </button>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <script src="/src/js/middleware_auth.js"></script>
    <script src="../../js/receipt/handler.js" type="module"></script>

</body>

</html>