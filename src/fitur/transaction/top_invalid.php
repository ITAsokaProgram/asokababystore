<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Top Invalid</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/all.min.css">
    <link rel="stylesheet" href="/css/header.css">
    <link rel="stylesheet" href="/css/sidebar.css">
    <link rel="stylesheet" href="/css/animation-fade-in.css">
    <link rel="icon" type="image/png" href="/images/logo1.png">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/src/style/default-font.css">
    <link rel="stylesheet" href="/src/output2.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <script src="https://cdn.jsdelivr.net/npm/toastify-js" defer></script>

    <style>
        body,
        .font-inter {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        }

        .scrollbar-thin::-webkit-scrollbar {
            height: 6px;
            width: 6px;
        }

        .scrollbar-thin::-webkit-scrollbar-thumb {
            background: #d1d5db;
            border-radius: 6px;
        }

        .scrollbar-thin::-webkit-scrollbar-track {
            background: #f9fafb;
        }

        .sticky-header th {
            position: sticky;
            top: 0;
            z-index: 10;
            background: linear-gradient(to right, #f8fafc, #f1f5f9);
            color: #475569;
            backdrop-filter: blur(10px);
            border-bottom: 2px solid #e2e8f0;
        }

        .table-hover tr:hover {
            background-color: rgba(248, 250, 252, 0.8);
            transition: background-color 0.15s ease-in-out;
        }

        .stat-card {
            transition: all 0.2s ease-in-out;
        }

        .stat-card:hover {
            transform: translateY(-1px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
        }
    </style>
</head>

<body class="bg-gray-50 min-h-screen flex font-inter">
    <!-- Navigation and Sidebar would be included here -->
    <?php include '../../component/navigation_report.php'; ?>
    <?php include '../../component/sidebar_report.php'; ?>
    <main id="main-content" class="flex-1 p-6 transition-all duration-300 ml-64 mt-5 antialiased text-gray-800">
        <div class="max-w-7xl mx-auto">
            <!-- HEADER & FILTER -->
            <div class="mb-8">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8">
                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                        <div class="flex items-center gap-4">
                            <div class="flex-shrink-0">
                                <div
                                    class="w-16 h-16 rounded-2xl bg-gradient-to-br from-gray-700 to-gray-600 shadow-lg flex items-center justify-center">
                                    <i class="fas fa-ban text-white text-2xl"></i>
                                </div>
                            </div>
                            <div>
                                <h1 class="text-3xl font-bold text-gray-900 tracking-tight">TOP Invalid VOID Transaction
                                </h1>
                                <p class="text-gray-600 text-base mt-1">Laporan data hari ini dan kemarin</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-3">
                            <div class="relative">
                                <select name="cabang" id="cabang-select"
                                    class="appearance-none bg-white border border-gray-300 rounded-xl px-4 py-3 pr-10 text-sm font-medium focus:ring-2 focus:ring-gray-500 focus:border-transparent transition-all duration-200 hover:border-gray-400 min-w-[160px]">
                                    <option value="">Pilih Cabang</option>
                                </select>
                                <i
                                    class="fas fa-chevron-down absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 pointer-events-none text-xs"></i>
                            </div>

                            <button id="btn-export-excel"
                                class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-3 rounded-xl shadow-sm font-semibold flex items-center gap-2 transition-all duration-200 hover:shadow-md"
                                type="button" title="Export Excel">
                                <i class="fas fa-file-excel text-sm"></i>
                                <span class="hidden sm:inline">Export</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- RINGKASAN STATISTIK -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                <div class="stat-card bg-white border border-gray-200 rounded-2xl p-6 shadow-sm">
                    <div class="flex items-center gap-4">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 rounded-xl bg-orange-100 flex items-center justify-center">
                                <i class="fas fa-ban text-orange-600 text-xl"></i>
                            </div>
                        </div>
                        <div class="flex-1">
                            <div class="text-xs text-gray-500 font-medium uppercase tracking-wider mb-1">Total VOID
                                Invalid</div>
                            <div class="text-3xl font-bold text-gray-900" id="total-void">0</div>
                        </div>
                    </div>
                </div>

                <div class="stat-card bg-white border border-gray-200 rounded-2xl p-6 shadow-sm">
                    <div class="flex items-center gap-4">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 rounded-xl bg-blue-100 flex items-center justify-center">
                                <i class="fas fa-store text-blue-600 text-xl"></i>
                            </div>
                        </div>
                        <div class="flex-1">
                            <div class="text-xs text-gray-500 font-medium uppercase tracking-wider mb-1">Cabang
                                Terbanyak</div>
                            <div class="text-3xl font-bold text-gray-900" id="top-cabang">-</div>
                        </div>
                    </div>
                </div>

                <div
                    class="stat-card bg-white border border-gray-200 rounded-2xl p-6 shadow-sm md:col-span-2 lg:col-span-1">
                    <div class="flex items-center gap-4">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 rounded-xl bg-green-100 flex items-center justify-center">
                                <i class="fas fa-chart-line text-green-600 text-xl"></i>
                            </div>
                        </div>
                        <div class="flex-1">
                            <div class="text-xs text-gray-500 font-medium uppercase tracking-wider mb-1">Status Monitor
                            </div>
                            <div class="text-3xl font-bold text-gray-900">Active</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TABEL DATA -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                        <i class="fas fa-table text-gray-500"></i>
                        Data Transaksi Invalid
                    </h3>
                </div>

                <div id="table-scroll-container" class="overflow-x-auto overflow-y-auto max-h-[500px] scrollbar-thin">
                    <table class="w-full table-fixed text-sm">
                        <thead class="sticky-header">
                            <tr>
                                <th class="w-16 px-4 py-4 text-left font-semibold">No</th>
                                <th class="w-32 px-4 py-4 text-left font-semibold">Tanggal</th>
                                <th class="w-32 px-4 py-4 text-left font-semibold">Kategori</th>
                                <th class="w-40 px-4 py-4 text-left font-semibold">Cabang</th>
                                <th class="w-40 px-4 py-4 text-left font-semibold">Kasir</th>
                                <th class="w-32 px-4 py-4 text-center font-semibold">Total VOID</th>
                            </tr>
                        </thead>
                        <tbody id="void-table-body" class="divide-y divide-gray-100 table-hover">
                            <!-- Data diisi via JS -->
                        </tbody>
                    </table>

                    <!-- Loading State -->
                    <div id="loading-row" class="hidden p-8 text-center">
                        <div class="flex items-center justify-center gap-3">
                            <div
                                class="w-5 h-5 border-2 border-t-transparent border-gray-400 border-solid rounded-full animate-spin">
                            </div>
                            <span class="text-gray-600 font-medium">Memuat data...</span>
                        </div>
                    </div>

                    <!-- End State -->
                    <div id="end-row" class="hidden p-8 text-center">
                        <span class="text-gray-500">Semua data sudah ditampilkan</span>
                    </div>
                </div>
            </div>

            <!-- MODAL DETAIL -->
            <div id="modal-detail"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm hidden transition-all duration-300">
                <div
                    class="bg-white rounded-3xl shadow-2xl border border-gray-100 w-full max-w-7xl mx-4 overflow-hidden max-h-[90vh] flex flex-col">
                    <!-- Modal Header -->
                    <div class="bg-gradient-to-r from-gray-800 to-gray-700 px-8 py-6 relative">
                        <button onclick="closeModal()"
                            class="absolute top-4 right-4 text-white/80 hover:text-white text-xl bg-white/10 rounded-full p-2 backdrop-blur-sm transition-all duration-200 hover:bg-white/20">
                            <i class="fas fa-times"></i>
                        </button>

                        <h2 class="text-2xl font-bold text-white flex items-center gap-3">
                            <div class="bg-white/20 p-3 rounded-xl backdrop-blur-sm">
                                <i class="fas fa-ban text-xl"></i>
                            </div>
                            Detail Transaksi Invalid VOID
                        </h2>
                    </div>

                    <!-- Modal Content -->
                    <div class="flex-1 p-8 overflow-hidden">
                        <div class="bg-gray-50/50 rounded-2xl p-6 border border-gray-100 h-full flex flex-col">
                            <h3 class="text-lg font-semibold text-gray-800 mb-6 flex items-center gap-2">
                                <i class="fas fa-list-alt text-gray-500"></i>
                                Detail Transaksi
                            </h3>

                            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden flex-1">
                                <div class="overflow-x-auto overflow-y-auto max-h-[400px] scrollbar-thin">
                                    <table class="w-full text-sm">
                                        <thead class="bg-gray-50 sticky top-0 z-10">
                                            <tr>
                                                <th class="px-4 py-3 text-left font-semibold text-gray-700">No</th>
                                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Nama Produk
                                                </th>
                                                <th class="px-4 py-3 text-left font-semibold text-gray-700">No Bon</th>
                                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Jam</th>
                                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Cabang</th>
                                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Kasir</th>
                                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Kode Kasir
                                                </th>
                                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Tanggal</th>
                                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Keterangan
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody id="modal-detail-tbody" class="divide-y divide-gray-100">
                                            <!-- Data diisi via JS -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div class="bg-gray-50/50 border-t border-gray-100 px-8 py-4">
                        <div class="flex justify-center">
                            <button type="button" onclick="closeModal()"
                                class="px-6 py-3 bg-white text-gray-600 border border-gray-300 rounded-xl hover:bg-gray-50 transition-all duration-200 flex items-center gap-2 text-sm font-medium">
                                <i class="fas fa-times text-sm"></i>
                                Tutup
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="../../js/ui/navbar_toogle.js" type="module"></script>
    <script type="module" src="../../js/invalid_trans/top/best.js"></script>
</body>

</html>