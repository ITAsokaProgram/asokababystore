<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaksi Penukaran Hadiah</title>
    <link rel="stylesheet" href="../../../output2.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" type="image/png" href="../../../../public/images/logo1.png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js@1.12.0/src/toastify.min.css">
    <link rel="stylesheet" href="../../../style/header.css">
    <link rel="stylesheet" href="../../../style/sidebar.css">
    <link rel="stylesheet" href="../../../style/animation-fade-in.css">
    <script defer src="https://cdn.jsdelivr.net/npm/toastify-js@1.12.0"></script>

    <style>
        @keyframes fade-in-up {
            0% {
                opacity: 0;
                transform: translateY(10px);
            }

            100% {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fade-in-up {
            animation: fade-in-up 0.6s ease-out;
        }

        .receipt-input:focus {
            transform: scale(1.02);
        }



        @media print {
            body * {
                visibility: hidden;
            }

            .print-area,
            .print-area * {
                visibility: visible;
            }

            .print-area {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
            }
        }
    </style>
</head>

<body class="bg-gradient-to-br from-blue-50 to-cyan-50">
    <?php include '../../../component/navigation_report.php'; ?>
    <main id="main-content" class="flex-1 p-6 lg:p-8 transition-all duration-300 mt-16>
        <div class=" max-w-full mx-auto px-4">
        <div
            class="min-h-screen bg-white/80 backdrop-blur-md rounded-2xl shadow-xl border border-blue-100 p-8 animate-fade-in-up">

            <button onclick="history.back()"
                class="rounded-xl bg-blue-500 text-white px-4 py-2 cursor-pointer hover:from-blue-600 hover:to-blue-500"><i
                    class="fa fa-arrow-left mr-2"></i>Kembali</button>
            <!-- Header Section -->
            <div class="flex flex-col items-center mb-8">
                <div
                    class="bg-gradient-to-r from-blue-500 to-blue-400 p-4 rounded-xl shadow-lg mb-2 animate-fade-in-up">
                    <i class="fas fa-receipt text-white text-4xl"></i>
                </div>
                <h1
                    class="text-3xl font-bold text-gray-800 text-center bg-gradient-to-r from-blue-400 to-blue-500 bg-clip-text text-transparent animate-fade-in-up">
                    Transaksi Penukaran Hadiah
                </h1>
                <p class="text-gray-500 text-sm mt-2 text-center">Kelola transaksi penukaran poin member menjadi
                    hadiah</p>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4 mb-8">
                <!-- <div class="flex flex-col sm:flex-row gap-3">
                    <button onclick="laporanHarian()"
                        class="px-6 py-3 bg-gradient-to-r from-purple-500 to-purple-400 text-white rounded-xl hover:from-purple-600 hover:to-purple-500 transition-all duration-200 flex items-center justify-center gap-2 shadow-lg hover:shadow-xl cursor-pointer font-semibold hover:scale-105">
                        <i class="fas fa-chart-line text-xl"></i>
                        <span>Laporan Harian</span>
                    </button>
                    <button onclick="exportTransaksi()"
                        class="px-6 py-3 bg-gradient-to-r from-orange-500 to-orange-400 text-white rounded-xl hover:from-orange-600 hover:to-orange-500 transition-all duration-200 flex items-center justify-center gap-2 shadow-lg hover:shadow-xl cursor-pointer font-semibold hover:scale-105">
                        <i class="fas fa-download text-xl"></i>
                        <span>Export Data</span>
                    </button>
                </div> -->

                <!-- Search & Filters -->
                <div class="flex flex-col sm:flex-row gap-3 w-full lg:w-auto">
                    <div class="relative">
                        <input type="text" id="searchInput" placeholder="Cari ID, member, hadiah, status, cabang..."
                            class="w-full sm:w-80 px-5 py-3 pr-20 rounded-xl border border-blue-100 shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-200 focus:border-blue-300 text-blue-600 placeholder-blue-300 transition-all duration-200" />
                        <div class="absolute right-2 top-1/2 transform -translate-y-1/2 flex items-center gap-1">
                            <button onclick="clearSearch()" id="clearSearchBtn"
                                class="hidden p-1 text-gray-400 hover:text-red-500 transition-colors duration-200"
                                title="Clear search">
                                <i class="fas fa-times text-sm"></i>
                            </button>
                            <i class="fas fa-search text-blue-400"></i>
                        </div>
                    </div>
                    <button onclick="toggleDateFilter()"
                        class="px-4 py-3 bg-white border border-blue-200 text-blue-600 rounded-xl hover:bg-blue-50 transition-all duration-200 flex items-center gap-2 shadow-sm">
                        <i class="fas fa-calendar"></i>
                        <span>Filter Tanggal</span>
                    </button>
                </div>
            </div>

            <!-- Date Filter Panel -->
            <div id="dateFilter"
                class="hidden mb-6 bg-white/90 backdrop-blur rounded-xl border border-blue-100 p-6 shadow-md">
                <h3 class="text-lg font-semibold text-blue-700 mb-4 flex items-center gap-2">
                    <i class="fas fa-calendar-alt"></i>
                    Filter Periode
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-blue-600 mb-2">Dari Tanggal</label>
                        <input type="date" id="filterDateFrom"
                            class="w-full px-4 py-2 rounded-lg border border-blue-200 focus:ring-2 focus:ring-blue-200">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-blue-600 mb-2">Sampai Tanggal</label>
                        <input type="date" id="filterDateTo"
                            class="w-full px-4 py-2 rounded-lg border border-blue-200 focus:ring-2 focus:ring-blue-200">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-blue-600 mb-2">Cabang</label>
                        <select id="filterCabang"
                            class="w-full px-4 py-2 rounded-lg border border-blue-200 focus:ring-2 focus:ring-blue-200">
                        </select>
                    </div>

                    <div class="flex items-end">
                        <button id="filterApply"
                            class="w-full px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-all duration-200">
                            <i class="fas fa-check mr-2"></i>Terapkan
                        </button>
                    </div>
                </div>

                <div class="flex justify-start gap-3 mt-4">
                    <button onclick="setToday()"
                        class="px-3 py-1 bg-blue-100 text-blue-600 rounded-lg hover:bg-blue-200 transition-all duration-200 text-sm">
                        Hari Ini
                    </button>
                    <button onclick="setThisWeek()"
                        class="px-3 py-1 bg-blue-100 text-blue-600 rounded-lg hover:bg-blue-200 transition-all duration-200 text-sm">
                        Minggu Ini
                    </button>
                    <button onclick="setThisMonth()"
                        class="px-3 py-1 bg-blue-100 text-blue-600 rounded-lg hover:bg-blue-200 transition-all duration-200 text-sm">
                        Bulan Ini
                    </button>
                    <button onclick="resetDateFilter()"
                        class="px-3 py-1 bg-gray-100 text-gray-600 rounded-lg hover:bg-gray-200 transition-all duration-200 text-sm">
                        <i class="fas fa-undo mr-1"></i>Reset
                    </button>
                </div>
            </div>

            <!-- Quick Status Filter -->
            <div id="quickStatus" class="flex flex-wrap items-center gap-2 mb-6">
                <button type="button" data-status="all" aria-pressed="true" onclick="filterByStatus('all')"
                    class="inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm font-semibold border border-blue-600 bg-gradient-to-r from-blue-600 to-blue-400 text-white shadow-md transition-all duration-200
                                     hover:from-blue-700 hover:to-blue-500 hover:scale-105 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-400/70">
                    <i class="fas fa-list"></i>
                    <span>Semua</span>
                    <span
                        class="ml-1 rounded-full px-2 py-0.5 text-xs font-bold bg-white/30 text-white shadow-sm border border-white/30"
                        id="countAll" style="min-width:2.2em;text-align:center;">0</span>
                </button>

                <button type="button" data-status="claimed" aria-pressed="false" onclick="filterByStatus('success')"
                    class="inline-flex items-center gap-2 rounded-full px-3 py-1.5 text-sm font-medium border
           text-green-700 border-green-200 hover:bg-green-50 transition
           focus:outline-none focus-visible:ring-2 focus-visible:ring-green-300">
                    <i class="fas fa-check"></i>
                    <span>Berhasil</span>
                    <span class="ml-1 rounded-full px-2 py-0.5 text-xs font-semibold bg-current/10"
                        id="countSuccess">0</span>
                </button>

                <button type="button" data-status="pending" aria-pressed="false" onclick="filterByStatus('pending')"
                    class="inline-flex items-center gap-2 rounded-full px-3 py-1.5 text-sm font-medium border
           text-amber-700 border-amber-200 hover:bg-amber-50 transition
           focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-300">
                    <i class="fas fa-clock"></i>
                    <span>Proses</span>
                    <span class="ml-1 rounded-full px-2 py-0.5 text-xs font-semibold bg-current/10"
                        id="countPending">0</span>
                </button>

                <button type="button" data-status="expired" aria-pressed="false" onclick="filterByStatus('expired')"
                    class="inline-flex items-center gap-2 rounded-full px-3 py-1.5 text-sm font-medium border
           text-rose-700 border-rose-200 hover:bg-rose-50 transition
           focus:outline-none focus-visible:ring-2 focus-visible:ring-rose-300">
                    <i class="fas fa-times"></i>
                    <span>Dibatalkan</span>
                    <span class="ml-1 rounded-full px-2 py-0.5 text-xs font-semibold bg-current/10"
                        id="countCancelled">0</span>
                </button>
            </div>


            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div
                    class="bg-gradient-to-r from-green-50 to-emerald-50 p-6 rounded-xl border border-green-100 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-green-600">Transaksi Hari Ini</p>
                            <p class="text-2xl font-bold text-green-700" id="transaksiHariIni">0</p>
                        </div>
                        <div class="bg-green-100 p-3 rounded-lg">
                            <i class="fas fa-shopping-cart text-green-600 text-xl"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-r from-blue-50 to-cyan-50 p-6 rounded-xl border border-blue-100 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-blue-600">Poin Ditukar Hari Ini</p>
                            <p class="text-2xl font-bold text-blue-700" id="poinHariIni">0</p>
                        </div>
                        <div class="bg-blue-100 p-3 rounded-lg">
                            <i class="fas fa-coins text-blue-600 text-xl"></i>
                        </div>
                    </div>
                </div>

                <div
                    class="bg-gradient-to-r from-purple-50 to-violet-50 p-6 rounded-xl border border-purple-100 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-purple-600">Hadiah Terdistribusi</p>
                            <p class="text-2xl font-bold text-purple-700" id="hadiahTerdistribusi">0</p>
                        </div>
                        <div class="bg-purple-100 p-3 rounded-lg">
                            <i class="fas fa-gift text-purple-600 text-xl"></i>
                        </div>
                    </div>
                </div>


            </div>

            <!-- Table Container -->
            <div
                class="overflow-x-auto w-full bg-white/90 backdrop-blur rounded-2xl shadow-xl border border-blue-100 animate-fade-in-up">
                <div class="min-w-full inline-block align-middle">
                    <div class="overflow-hidden">
                        <table class="min-w-full divide-y divide-blue-200">
                            <thead class="bg-gradient-to-r from-blue-400 to-blue-500 text-white">
                                <tr>
                                    <th class="w-[5%] px-4 py-3 text-left text-xs font-semibold">No</th>
                                    <th class="w-[12%] px-4 py-3 text-left text-xs font-semibold">Member</th>
                                    <th class="w-[15%] px-4 py-3 text-left text-xs font-semibold">Hadiah</th>
                                    <th class="w-[6%] px-4 py-3 text-center text-xs font-semibold">Qty</th>
                                    <th class="w-[8%] px-4 py-3 text-center text-xs font-semibold">Poin Tukar</th>
                                    <th class="w-[10%] px-4 py-3 text-left text-xs font-semibold">Tanggal</th>
                                    <th class="w-[8%] px-4 py-3 text-center text-xs font-semibold">Status</th>
                                    <th class="w-[8%] px-4 py-3 text-left text-xs font-semibold">Cabang</th>
                                    <th class="w-[10%] px-4 py-3 text-center text-xs font-semibold">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="tableBody" class="divide-y divide-blue-100 text-grey-700 text-sm">
                                <!-- Sample Data -->
                                <tr class="hover:bg-blue-50/50 transition-colors duration-200">
                                    <td class="px-4 py-4 text-center">1</td>
                                    <td class="px-4 py-4">
                                        <span class="font-mono text-xs bg-blue-100 px-2 py-1 rounded">TRX001</span>
                                    </td>
                                    <td class="px-4 py-4">
                                        <div class="flex items-center gap-2">
                                            <div class="bg-blue-100 p-2 rounded-full">
                                                <i class="fas fa-user text-blue-600 text-xs"></i>
                                            </div>
                                            <div>
                                                <p class="font-medium text-gray-800">Budi Santoso</p>
                                                <p class="text-xs text-gray-500">0812-3456-7890</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-4">
                                        <span class="font-mono text-xs">1234567890</span>
                                    </td>
                                    <td class="px-4 py-4">
                                        <div class="flex items-center gap-2">
                                            <i class="fas fa-gift text-blue-500"></i>
                                            <span class="font-medium">Voucher Diskon 50%</span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-4 text-center">
                                        <span class="bg-gray-100 px-2 py-1 rounded text-xs font-medium">1</span>
                                    </td>
                                    <td class="px-4 py-4 text-center">
                                        <span
                                            class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs font-medium">500</span>
                                    </td>
                                    <td class="px-4 py-4">
                                        <div class="text-xs">
                                            <div class="font-medium">15 Des 2024</div>
                                            <div class="text-gray-500">14:30</div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-4 text-center">
                                        <span class="status-success px-3 py-1 rounded-full text-xs font-medium">
                                            <i class="fas fa-check mr-1"></i>Berhasil
                                        </span>
                                    </td>
                                    <td class="px-4 py-4">
                                        <div class="text-xs">
                                            <div class="font-medium">Kasir 1</div>
                                            <div class="text-gray-500">KSR001</div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-4">Jakarta Pusat</td>
                                    <td class="px-4 py-4">
                                        <div class="flex items-center justify-center gap-1">
                                            <button onclick="cetakStruk(1)"
                                                class="p-2 bg-green-100 text-green-600 rounded-lg hover:bg-green-200 transition-colors duration-200"
                                                title="Cetak Struk">
                                                <i class="fas fa-receipt text-xs"></i>
                                            </button>
                                            <button onclick="viewDetail(1)"
                                                class="p-2 bg-blue-100 text-blue-600 rounded-lg hover:bg-blue-200 transition-colors duration-200"
                                                title="Detail">
                                                <i class="fas fa-eye text-xs"></i>
                                            </button>
                                            <button onclick="batalkanTransaksi(1)"
                                                class="p-2 bg-red-100 text-red-600 rounded-lg hover:bg-red-200 transition-colors duration-200"
                                                title="Batalkan">
                                                <i class="fas fa-times text-xs"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            <div class="mt-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="flex items-center gap-2">
                    <select id="pageSize"
                        class="px-4 py-2 rounded-lg border border-blue-200 text-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-200 transition-all duration-200 text-sm">
                        <option value="10" selected>10 per halaman</option>
                        <option value="25">25 per halaman</option>
                        <option value="50">50 per halaman</option>
                        <option value="100">100 per halaman</option>
                    </select>
                </div>
                <div class="text-sm text-gray-600" id="dataInfo">Menampilkan 1-10 dari 15 data</div>
                <div class="flex items-center gap-1" id="paginationContainer">
                    <button id="firstPage" class="p-2 rounded-lg hover:bg-blue-50 disabled:opacity-50"
                        title="Halaman pertama">
                        <i class="fas fa-angle-double-left text-blue-600"></i>
                    </button>
                    <button id="prevPage" class="p-2 rounded-lg hover:bg-blue-50 disabled:opacity-50"
                        title="Sebelumnya">
                        <i class="fas fa-angle-left text-blue-600"></i>
                    </button>
                    <div class="flex items-center gap-1" id="pageNumbers">
                        <button class="px-3 py-1 bg-blue-500 text-white rounded-lg text-sm">1</button>
                        <button class="px-3 py-1 hover:bg-blue-50 rounded-lg text-sm">2</button>
                    </div>
                    <button id="nextPage" class="p-2 rounded-lg hover:bg-blue-50 disabled:opacity-50"
                        title="Selanjutnya">
                        <i class="fas fa-angle-right text-blue-600"></i>
                    </button>
                    <button id="lastPage" class="p-2 rounded-lg hover:bg-blue-50 disabled:opacity-50"
                        title="Halaman terakhir">
                        <i class="fas fa-angle-double-right text-blue-600"></i>
                    </button>
                </div>
            </div>
        </div>
        </div>


        <!-- Modal Struk Digital -->
        <div id="modalStruk"
            class="fixed inset-0 bg-black/50 flex justify-center items-center z-50 hidden backdrop-blur-sm transition-all duration-300">
            <div
                class="bg-white w-full max-w-md rounded-2xl shadow-2xl border border-blue-100 relative animate-fade-in-up overflow-hidden">
                <button onclick="closeModal('modalStruk')"
                    class="absolute top-4 right-4 text-gray-500 hover:text-blue-500 text-xl bg-white/80 rounded-full p-2 shadow-md transition-all duration-200 z-10">
                    <i class="fas fa-times"></i>
                </button>

                <div class="print-area">
                    <!-- Header Struk -->
                    <div class="bg-gradient-to-r from-blue-500 to-blue-400 text-white p-6 text-center">
                        <h2 class="text-2xl font-bold mb-2">
                            <i class="fas fa-receipt mr-2"></i>
                            Struk Penukaran Hadiah
                        </h2>
                        <p class="text-blue-100 text-sm">Digital Receipt</p>
                    </div>

                    <!-- Content Struk -->
                    <div class="p-6 space-y-4">
                        <!-- Info Toko -->
                        <div class="text-center border-b pb-4">
                            <h3 class="font-bold text-lg">NAMA TOKO ANDA</h3>
                            <p class="text-sm text-gray-600">Jl. Contoh Alamat No. 123</p>
                            <p class="text-sm text-gray-600">Telp: (021) 12345678</p>
                        </div>

                        <!-- Info Transaksi -->
                        <div class="border-b pb-4">
                            <div class="grid grid-cols-2 gap-2 text-sm">
                                <div>
                                    <span class="text-gray-600">ID Transaksi:</span>
                                    <p class="font-mono font-bold" id="strukTrxId">TRX001</p>
                                </div>
                                <div>
                                    <span class="text-gray-600">Tanggal:</span>
                                    <p class="font-medium" id="strukTanggal">15 Des 2024, 14:30</p>
                                </div>
                                <div>
                                    <span class="text-gray-600">Kasir:</span>
                                    <p class="font-medium" id="strukKasir">Kasir 1</p>
                                </div>
                                <div>
                                    <span class="text-gray-600">Cabang:</span>
                                    <p class="font-medium" id="strukCabang">Jakarta Pusat</p>
                                </div>
                            </div>
                        </div>

                        <!-- Info Member -->
                        <div class="border-b pb-4">
                            <h4 class="font-semibold text-blue-600 mb-2">
                                <i class="fas fa-user mr-2"></i>Data Member
                            </h4>
                            <div class="space-y-1 text-sm">
                                <p><span class="text-gray-600">Nama:</span> <span class="font-medium"
                                        id="strukMemberNama">Budi Santoso</span></p>
                                <p><span class="text-gray-600">HP:</span> <span class="font-mono"
                                        id="strukMemberHp">0812-3456-7890</span></p>
                            </div>
                        </div>

                        <!-- Item Hadiah -->
                        <div class="border-b pb-4">
                            <h4 class="font-semibold text-green-600 mb-2">
                                <i class="fas fa-gift mr-2"></i>Hadiah yang Ditukar
                            </h4>
                            <div id="strukItems" class="space-y-2">
                                <!-- Items akan diisi dinamis -->
                            </div>
                        </div>

                        <!-- Total -->
                        <div class="border-b pb-4">
                            <div class="flex justify-between items-center text-lg font-bold">
                                <span class="text-gray-700">Total Poin Digunakan:</span>
                                <span class="text-red-600" id="strukTotalPoin">500 poin</span>
                            </div>
                            <div class="flex justify-between items-center text-sm text-gray-600 mt-2">
                                <span>Poin Sebelum:</span>
                                <span id="strukPoinSebelum">1,250 poin</span>
                            </div>
                            <div class="flex justify-between items-center text-sm text-gray-600">
                                <span>Poin Sesudah:</span>
                                <span id="strukPoinSesudah">750 poin</span>
                            </div>
                        </div>

                        <!-- Catatan -->
                        <div class="text-center text-xs text-gray-500">
                            <p class="mb-2">
                                <i class="fas fa-heart text-red-400 mr-1"></i>
                                Terima kasih atas kepercayaan Anda
                            </p>
                            <p>Simpan struk ini sebagai bukti penukaran hadiah</p>
                            <div class="mt-3 p-2 bg-yellow-50 rounded-lg">
                                <p class="font-medium text-yellow-700">
                                    <i class="fas fa-clock mr-1"></i>
                                    Ambil hadiah dalam 7 hari
                                </p>
                            </div>
                        </div>

                        <!-- QR Code Placeholder -->
                        <div class="text-center pt-4">
                            <div class="inline-block p-4 bg-gray-100 rounded-lg">
                                <div class="w-20 h-20 bg-gray-300 flex items-center justify-center rounded">
                                    <i class="fas fa-qrcode text-2xl text-gray-500"></i>
                                </div>
                                <p class="text-xs text-gray-500 mt-2">QR Code Verifikasi</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons Struk -->
                <div class="flex gap-2 p-4 bg-gray-50 border-t">
                    <button onclick="printStruk()"
                        class="flex-1 px-4 py-3 bg-green-500 text-white rounded-xl hover:bg-green-600 transition-all duration-200 flex items-center justify-center gap-2 font-semibold">
                        <i class="fas fa-print"></i>
                        <span>Print</span>
                    </button>
                    <button onclick="downloadStruk()"
                        class="flex-1 px-4 py-3 bg-blue-500 text-white rounded-xl hover:bg-blue-600 transition-all duration-200 flex items-center justify-center gap-2 font-semibold">
                        <i class="fas fa-download"></i>
                        <span>Download</span>
                    </button>
                    <button onclick="whatsappStruk()"
                        class="flex-1 px-4 py-3 bg-green-600 text-white rounded-xl hover:bg-green-700 transition-all duration-200 flex items-center justify-center gap-2 font-semibold">
                        <i class="fab fa-whatsapp"></i>
                        <span>WhatsApp</span>
                    </button>
                </div>
            </div>
        </div>

    </main>

    <script src="../../../js/rewards/management/main.js" type="module"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        document.getElementById("toggle-sidebar").addEventListener("click", function () {
            document.getElementById("sidebar").classList.toggle("open");
        });

        document.getElementById("toggle-hide").addEventListener("click", function () {
            var sidebarTexts = document.querySelectorAll(".sidebar-text");
            let mainContent = document.getElementById("main-content");
            let sidebar = document.getElementById("sidebar");
            var toggleButton = document.getElementById("toggle-hide");
            var icon = toggleButton.querySelector("i");

            if (sidebar.classList.contains("w-64")) {
                // Sidebar mengecil
                sidebar.classList.remove("w-64", "px-5");
                sidebar.classList.add("w-16", "px-2");
                sidebarTexts.forEach((text) => text.classList.add("hidden"));
                mainContent.classList.remove("ml-64");
                mainContent.classList.add("ml-16");
                toggleButton.classList.add("left-20");
                toggleButton.classList.remove("left-64");
                icon.classList.remove("fa-angle-left");
                icon.classList.add("fa-angle-right");
            } else {
                // Sidebar membesar
                sidebar.classList.remove("w-16", "px-2");
                sidebar.classList.add("w-64", "px-5");
                sidebarTexts.forEach((text) => text.classList.remove("hidden"));
                mainContent.classList.remove("ml-16");
                mainContent.classList.add("ml-64");
                toggleButton.classList.add("left-64");
                toggleButton.classList.remove("left-20");
                icon.classList.remove("fa-angle-right");
                icon.classList.add("fa-angle-left");
            }
        });

        document.addEventListener("DOMContentLoaded", function () {
            const profileImg = document.getElementById("profile-img");
            const profileCard = document.getElementById("profile-card");

            profileImg.addEventListener("click", function (event) {
                event.preventDefault();
                profileCard.classList.toggle("show");
            });

            // Tutup profile-card jika klik di luar
            document.addEventListener("click", function (event) {
                if (!profileCard.contains(event.target) && !profileImg.contains(event.target)) {
                    profileCard.classList.remove("show");
                }
            });
        });
    </script>
</body>

</html>