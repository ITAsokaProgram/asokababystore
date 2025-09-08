<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Member Nonaktif</title>
    <link rel="stylesheet" href="/src/output2.css">
    <link rel="stylesheet" href="/src/style/default-font.css">
    <link rel="icon" type="image/png" href="/images/logo1.png">

    <script src="https://cdn.jsdelivr.net/npm/echarts@5/dist/echarts.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../../js/middleware_auth.js"></script>

</head>

<body>
    <div class="space-y-6 px-4 sm:px-6 lg:px-8 py-4">
        <!-- Page Title & Back Button -->
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <button onclick="history.back()" class="bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-full p-2 shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </button>
                <h1 class="text-xl font-semibold text-gray-800">Detail Member Nonaktif</h1>
            </div>
        </div>
        <!-- Filter per Cabang -->
        <!-- <div class="flex items-center justify-between">
            <div class="flex flex-col sm:flex-row sm:items-center gap-3 w-full sm:w-auto">
                <label for="filterCabang" class="text-sm font-medium text-gray-700">Pilih Cabang:</label>
                <select id="filterCabang" class="border border-gray-300 rounded-md px-3 py-2 text-sm shadow-sm w-full sm:w-64">
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
        </div> -->
        <!-- KPI Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Total Keseluruhan -->
            <div class="bg-blue-50 rounded-xl p-4 shadow-sm hover:shadow-md transition">
                <div class="flex items-center gap-2 text-blue-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3 10h4l3 8 4-16 3 8h4" />
                    </svg>
                    <h3 class="text-sm font-medium">Total Keseluruhan</h3>
                </div>
                <p id="totalSeluruh" class="text-2xl font-bold text-blue-800 mt-1"></p>
            </div>

            <!-- Total Member Nonaktif -->
            <div class="bg-rose-50 rounded-xl p-4 shadow-sm hover:shadow-md transition">
                <div class="flex items-center gap-2 text-rose-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M18.364 5.636l-1.414 1.414M6.343 17.657l-1.414 1.414M2 12h2m16 0h2M4.222 4.222l1.414 1.414M19.778 19.778l-1.414-1.414M12 2v2m0 16v2" />
                    </svg>
                    <h3 class="text-sm font-medium">Total Member Nonaktif</h3>
                </div>
                <p id="totalNonaktif" class="text-2xl font-bold text-rose-800 mt-1"></p>
            </div>

            <!-- Rata-rata Tidak Aktif -->
            <div class="bg-yellow-50 rounded-xl p-4 shadow-sm hover:shadow-md transition">
                <div class="flex items-center gap-2 text-yellow-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <h3 class="text-sm font-medium">Rata-rata Tidak Aktif</h3>
                </div>
                <p id="rataNonaktif" class="text-2xl font-bold text-yellow-800 mt-1"></p>
            </div>

            <!-- Belum Pernah Transaksi -->
            <div class="bg-gray-50 rounded-xl p-4 shadow-sm hover:shadow-md transition">
                <div class="flex items-center gap-2 text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M15 17h5l-1.405-1.405M4 4v16h16V4H4zm4 4h8v8H8V8z" />
                    </svg>
                    <h3 class="text-sm font-medium">Belum Pernah Transaksi</h3>
                </div>
                <p id="belumTrans" class="text-2xl font-bold text-gray-800 mt-1"></p>
            </div>

            <!-- <div class="bg-blue-50 rounded-xl p-4 shadow-sm hover:shadow-md transition">
                <h3 class="text-sm text-blue-600 font-medium">Pernah Belanja > 3x</h3>
                <p class="text-2xl font-bold text-blue-800 mt-1">25.000</p>
            </div>
            <div class="bg-emerald-50 rounded-xl p-4 shadow-sm hover:shadow-md transition">
                <h3 class="text-sm text-emerald-600 font-medium">Berpotensi Aktif Kembali</h3>
                <p class="text-2xl font-bold text-emerald-800 mt-1">12.000</p>
            </div> -->
        </div>

        <!-- Chart Area -->
        <div class="bg-white p-4 rounded-xl shadow-sm">
            <h3 class="text-base font-semibold text-gray-700 mb-2">Tren Member Nonaktif per Bulan</h3>
            <div id="nonactiveTrendChart" class="w-full h-64"></div>
        </div>

        <!-- Segmented Table -->
        <div class="bg-white p-4 rounded-xl shadow-sm max-w-4xl ">
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-2 mb-3">
                <h3 class="text-base font-semibold text-gray-700">Segmentasi Member Nonaktif</h3>
                <!-- <input type="text" placeholder="Cari nama atau cabang..." class="text-sm border border-gray-300 px-3 py-2 rounded-md shadow-sm w-full sm:w-64" /> -->
            </div>
            <div class="overflow-x-auto">
                <table class="w-full table-auto text-sm">
                    <thead class="bg-gray-100 text-gray-700">
                        <tr>
                            <th class="px-3 py-2 text-left whitespace-nowrap">No Hp</th>
                            <th class="px-3 py-2 text-left whitespace-nowrap">Nama</th>
                            <th class="px-3 py-2 text-left whitespace-nowrap">Terakhir Belanja</th>
                            <th class="px-3 py-2 text-left whitespace-nowrap">Cabang</th>
                        </tr>
                    </thead>
                    <tbody id="nonactiveMemberTableBody" class="divide-y divide-gray-200 text-gray-800">
                        <!-- Data dynamically inserted here -->
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Rekomendasi Aksi -->
        <div class="bg-white p-4 rounded-xl shadow-sm border-l-4 border-blue-400">
            <h3 class="text-base font-semibold text-gray-700 mb-3">Insight & Rekomendasi</h3>
            <ul class="list-disc pl-5 text-sm text-gray-600 space-y-1">
                <!-- <li>13.450 member terakhir belanja saat promo. Pertimbangkan kampanye promo ulang.</li>
                <li>Cabang ABIN memiliki 20.000 member nonaktif – buat event khusus di cabang ini.</li>
                <li>Segmentasikan member dengan frekuensi tinggi untuk program loyalitas terbatas.</li> -->
            </ul>
        </div>

        <!-- Action Section -->
        <div class="bg-white p-4 rounded-xl shadow-sm">
            <h3 class="text-base font-semibold text-gray-700 mb-3">Tindakan Massal</h3>
            <div class="flex flex-wrap gap-3">
                <button class="bg-sky-600 hover:bg-sky-700 text-white px-4 py-2 rounded-md text-sm shadow">Kirim Promo</button>
                <button class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md text-sm shadow">Broadcast WhatsApp</button>
                <button class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-md text-sm shadow">Tandai Retarget</button>
                <button class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-md text-sm shadow">Arsipkan</button>
            </div>
        </div>
    </div>
    <div id="loadingOverlay" class="fixed inset-0 bg-white bg-opacity-70 flex items-center justify-center z-50 hidden min-h-screen">
        <div class="text-center">
            <div class="animate-spin rounded-full h-12 w-12 border-t-4 border-blue-500 border-opacity-50"></div>
        </div>
    </div>
    <script type="module">
        import {
            fetchDataNon,
            loadDataFetchNon
        } from "/src/js/member_internal/non_active.js"

        loadDataFetchNon();
    </script>
</body>

</html>