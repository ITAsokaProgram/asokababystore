<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Detail Member Aktif</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet" />
    <link rel="icon" type="image/png" href="../../../../public/images/logo1.png">
    <script src="https://cdn.jsdelivr.net/npm/echarts@5/dist/echarts.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../../js/middleware_auth.js"></script>

</head>

<body class="bg-gray-100 text-gray-800">

    <div class="container mx-auto p-6">
        <div class="flex items-center mb-6">
            <a href="/in_beranda" class="text-blue-600 hover:text-blue-800 mr-4 flex items-center">
                <!-- Icon panah kiri dari Heroicons atau FontAwesome bisa diganti sesuai selera -->
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M7.707 14.707a1 1 0 01-1.414 0l-5-5a1 1 0 010-1.414l5-5a1 1 0 111.414 1.414L4.414 9H17a1 1 0 110 2H4.414l3.293 3.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                </svg>
                Kembali
            </a>
        </div>
        <h1 class="text-3xl font-bold mb-4">Detail Member Aktif</h1>
        <!-- Filter Section -->
        <div class="bg-white rounded-lg p-4 shadow mb-6 flex flex-wrap gap-4 items-center justify-between">
            <!-- Dropdown Cabang -->
            <div>
                <label for="branch" class="block text-sm font-medium text-gray-700">Cabang</label>
                <select id="branch" class="mt-1 block w-48 rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Pilih Cabang</option>
                    <option value="ABIN">ABIN</option>
                    <option value="ACE">ACE</option>
                    <option value="ACIB">ACIB</option>
                    <option value="ACIL">ACIL</option>
                    <option value="ACIN">ACIN</option>
                    <option value="ACSA">ACSA</option>
                    <option value="ADET">ADET</option>
                    <option value="ADMB">ADMB</option>
                    <option value="AHA">AHA</option>
                    <option value="AHIN">AHIN</option>
                    <option value="ALANG">ALANG</option>
                    <option value="ANGIN">ANGIN</option>
                    <option value="APEN">APEN</option>
                    <option value="APIK">APIK</option>
                    <option value="APRS">APRS</option>
                    <option value="ARAW">ARAW</option>
                    <option value="ARUNG">ARUNG</option>
                    <option value="ASIH">ASIH</option>
                    <option value="ATIN">ATIN</option>
                    <option value="AWIT">AWIT</option>
                    <option value="AXY">AXY</option>
                    <option value="SEMUA CABANG">SEMUA CABANG</option>
                </select>
            </div>

            <!-- Member Aktif Count -->
            <div class="ml-auto text-right">
                <p class="text-sm text-gray-600">Member Aktif:</p>
                <p id="activeMemberCount" class="text-xl font-semibold text-blue-600">0</p>
            </div>
        </div>


        <!-- Chart Section -->
        <div class="bg-white rounded-lg shadow p-6 mb-8">
            <h2 class="text-xl font-semibold mb-4">Tren Transaksi Member Aktif</h2>
            <div class="mb-4" id="divSelect">
                <label for="periodeSelect" class="mr-2 font-medium">Pilih Periode:</label>
                <select id="periodeSelect" class="border border-gray-300 rounded px-3 py-1">
                    <option value="1">1 bulan terakhir</option>
                    <option value="3" selected>3 Bulan Terakhir</option>
                    <option value="12">12 Bulan Terakhir</option>
                </select>
            </div>
            <!-- Wrapper agar loading menutupi chart -->
            <div class="relative w-full h-64" id="activeChartWrapper">
                <!-- Chart canvas akan di-render di sini -->
                <div id="activeMemberChart" class="w-full h-full"></div>

                <!-- Loading Overlay -->
                <div id="chartLoading" class="absolute inset-0 bg-white bg-opacity-75 flex items-center justify-center z-10 hidden">
                    <div class="loader border-4 border-blue-500 border-t-transparent rounded-full w-8 h-8 animate-spin"></div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-8">
            <!-- Top 10 Member Aktif -->
            <div class="bg-white rounded-xl shadow-lg p-5 border border-blue-100">
                <h2 class="text-base font-semibold text-blue-800 mb-3">👥 Top 10 Member Aktif</h2>
                <div class="overflow-x-auto">
                    <table class="table-auto w-full text-sm text-left text-gray-800">
                        <thead class="bg-blue-50 text-blue-900 font-semibold">
                            <tr>
                                <th class="px-2 py-2 whitespace-nowrap border-b border-blue-100">Nama Member</th>
                                <th class="px-2 py-2 w-1/4 whitespace-nowrap border-b border-blue-100">Cabang</th>
                                <th class="px-2 py-2 whitespace-nowrap border-b border-blue-100">Total Belanja</th>
                            </tr>
                        </thead>
                        <tbody id="memberTableBody" class="divide-y divide-gray-100">
                            <!-- JS injects content here -->
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Top 10 Barang Terlaris -->
            <div class="bg-white rounded-xl shadow-lg p-5 border border-green-100">
                <h2 class="text-base font-semibold text-green-800 mb-3">📦 Top 10 Barang Terlaris</h2>
                <div class="overflow-x-auto">
                    <table class="table-auto w-full text-sm text-left text-gray-800">
                        <thead class="bg-green-50 text-green-900 font-semibold">
                            <tr>
                                <th class="px-2 py-2 whitespace-nowrap border-b border-green-100">Barcode</th>
                                <th class="px-2 py-2 whitespace-nowrap border-b border-green-100">Nama Barang</th>
                                <th class="px-2 py-2 whitespace-nowrap border-b border-green-100">Jumlah Terjual</th>
                            </tr>
                        </thead>
                        <tbody id="topProductTableBody" class="divide-y divide-gray-100">
                            <!-- JS injects content here -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>



    </div>
    <!-- Loader overlay -->
    <div id="loadingOverlay" class="fixed inset-0 bg-white bg-opacity-70 flex items-center justify-center z-50 hidden min-h-screen">
        <div class="text-center">
            <div class="animate-spin rounded-full h-12 w-12 border-t-4 border-blue-500 border-opacity-50"></div>
        </div>
    </div>
    <script type="module">
        import {
            fetchData,
            loadAndRenderData,
            filterTrend
        } from "/src/js/member_internal/active.js";
        loadAndRenderData()
        filterTrend()
    </script>
</body>

</html>