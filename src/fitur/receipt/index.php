<?php
session_start();
include '../../../aa_kon_sett.php';

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
                                <p class="text-xs text-gray-600">Komparasi Receipt Head vs Checking Data.</p>
                            </div>
                        </div>
                        <a href="create.php"
                            class="btn-primary inline-flex items-center justify-center gap-2 no-underline">
                            <i class="fa-solid fa-plus"></i>
                            <span>Buat Receipt</span>
                        </a>
                    </div>

                    <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div id="btn-show-selisih"
                            class="summary-card flex gap-4 items-center bg-red-50 border border-red-100 p-3 rounded-xl cursor-pointer hover:bg-red-100 transition">
                            <div class="summary-icon bg-red-100 text-red-600 p-3 rounded-lg">
                                <i class="fas fa-exclamation-triangle fa-lg"></i>
                            </div>
                            <div>
                                <h3 class="text-xs font-semibold text-gray-600 mb-1">Total Selisih Data</h3>
                                <p id="summary-total-selisih" class="text-2xl font-bold truncate text-red-600">0</p>
                                <p class="text-[10px] text-gray-500">Klik untuk lihat detail</p>
                            </div>
                        </div>

                        <div id="btn-show-missing"
                            class="summary-card flex gap-4 items-center bg-orange-50 border border-orange-100 p-3 rounded-xl cursor-pointer hover:bg-orange-100 transition">
                            <div class="summary-icon bg-orange-100 text-orange-600 p-3 rounded-lg">
                                <i class="fas fa-search-minus fa-lg"></i>
                            </div>
                            <div>
                                <h3 class="text-xs font-semibold text-gray-600 mb-1">Total Belum Ada (Checking)</h3>
                                <p id="summary-total-missing" class="text-2xl font-bold truncate text-orange-600">0</p>
                                <p class="text-[10px] text-gray-500">Klik untuk lihat detail</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="filter-card-simple mb-4">
                    <form id="filter-form" class="grid grid-cols-1 md:grid-cols-4 gap-3 items-end">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-2">Pilih Cabang</label>
                            <select name="kode_store" id="kode_store_filter" class="input-modern w-full cursor-pointer">
                                <option value="">Semua Cabang</option>
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
                                class="btn-primary inline-flex items-center justify-center gap-2 px-6">
                                <i class="fas fa-filter"></i>
                                <span>Tampilkan Data</span>
                            </button>
                        </div>
                        <input type="hidden" name="page" id="current_page" value="1">
                    </form>
                </div>

                <div class="filter-card">
                    <div class="table-container">
                        <table class="table-modern" id="receipt-table">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Tgl Tiba</th>
                                    <th>Store</th>
                                    <th>Supplier</th>
                                    <th>No Faktur</th>
                                    <th class="text-right">Total Head </th>
                                    <th class="text-right">Total Checking</th>
                                    <th class="text-right">Selisih</th>
                                    <th class="text-center">Status</th>
                                    <th>Keterangan</th>
                                </tr>
                            </thead>
                            <tbody id="receipt-table-body">
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
                        <h3 class="text-lg font-bold leading-6 text-pink-700">Detail Komparasi</h3>
                        <button @click="detailModalOpen = false"
                            class="text-pink-400 hover:text-pink-600 focus:outline-none">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <div class="px-4 py-5 sm:p-6">
                        <div class="text-center pb-4 border-b border-gray-100">
                            <p class="text-sm text-gray-500">No Faktur / Order</p>
                            <p class="text-xl font-mono font-bold text-gray-800" x-text="detailData.no_faktur"></p>
                            <div class="flex justify-center gap-2 mt-1">
                                <span class="text-xs px-2 py-1 bg-gray-100 rounded text-gray-600"
                                    x-text="detailData.kode_supp"></span>
                                <span class="text-xs px-2 py-1 bg-pink-100 text-pink-700 rounded"
                                    x-text="detailData.Nm_Alias || detailData.kd_store"></span>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4 bg-gray-50 p-4 rounded-lg mt-4">
                            <div class="border-r border-gray-200 pr-4">
                                <p class="text-[10px] font-bold tracking-wider text-gray-400 uppercase mb-2">System
                                    (Head)</p>
                                <div class="flex flex-col h-full justify-between">
                                    <div>
                                        <p class="text-xs text-gray-500 mb-1">Total Nilai</p>
                                        <p class="text-lg font-bold text-gray-800"
                                            x-text="formatRupiah(detailData.total_head)"></p>
                                    </div>
                                    <div class="mt-2 text-xs text-gray-400 italic">
                                        Data dari Receipt Head
                                    </div>
                                </div>
                            </div>

                            <div class="pl-2">
                                <p class="text-[10px] font-bold tracking-wider text-gray-400 uppercase mb-2">Checking
                                </p>
                                <div class="flex flex-col h-full justify-between">
                                    <div>
                                        <p class="text-xs text-gray-500 mb-1">Total Diterima</p>
                                        <p class="text-lg font-bold text-blue-600"
                                            x-text="formatRupiah(detailData.total_check)"></p>
                                    </div>
                                    <div class="mt-2">
                                        <span x-show="!detailData.total_check || detailData.total_check == 0"
                                            class="text-xs text-orange-500 bg-orange-50 px-2 py-0.5 rounded">Belum ada
                                            data</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="text-center p-4 rounded-lg mt-4 transition-colors duration-300"
                            :class="detailData.selisih == 0 ? 'bg-green-50 border border-green-100' : 'bg-red-50 border border-red-100'">
                            <p class="text-xs font-semibold uppercase tracking-wide"
                                :class="detailData.selisih == 0 ? 'text-green-600' : 'text-red-600'">
                                Nilai Selisih
                            </p>
                            <p class="text-2xl font-bold mt-1"
                                :class="detailData.selisih == 0 ? 'text-green-700' : 'text-red-700'"
                                x-text="formatRupiah(detailData.selisih)"></p>

                            <p class="text-xs mt-2 italic text-gray-500" x-show="detailData.keterangan">
                                Note: <span x-text="detailData.keterangan"></span>
                            </p>
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
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75" @click="summaryModalOpen = false"></div>
        <div class="fixed inset-0 z-10 overflow-y-auto">
            <div class="flex min-h-screen items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <div
                    class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl sm:w-full sm:max-w-lg p-6">
                    <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4" x-text="summaryTitle"></h3>
                    <ul class="divide-y divide-gray-200 max-h-60 overflow-y-auto">
                        <template x-for="item in summaryList" :key="item.no_faktur">
                            <li class="py-2 flex justify-between text-sm">
                                <span x-text="formatDate(item.tgl_tiba)"></span>
                                <span class="font-mono"
                                    x-text="item.no_faktur + (item.nilai_selisih ? ' (' + formatRupiah(item.nilai_selisih) + ')' : '')"></span>
                            </li>
                        </template>
                        <li x-show="summaryList.length === 0" class="py-2 text-center text-gray-500">Tidak ada data.
                        </li>
                    </ul>
                    <button @click="summaryModalOpen = false"
                        class="mt-4 w-full btn-primary justify-center">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <script src="/src/js/middleware_auth.js"></script>
    <script src="../../js/receipt/handler.js" type="module"></script>

</body>

</html>