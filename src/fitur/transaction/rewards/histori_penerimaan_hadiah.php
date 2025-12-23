<?php
require_once __DIR__ . '/../../../component/menu_handler.php';
$menuHandler = new MenuHandler('reward_give'); // Ganti nama menu
if (!$menuHandler->initialize()) {
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Histori Penerimaan Hadiah</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/all.min.css">
    <link rel="stylesheet" href="../../../style/header.css">
    <link rel="stylesheet" href="../../../style/sidebar.css">
    <link rel="stylesheet" href="../../../../css/cabang_selective.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="../../../../public/images/logo1.png">
    <link rel="stylesheet" href="../../../style/default-font.css">
    <link rel="stylesheet" href="../../../output2.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/litepicker/dist/css/litepicker.css"/>
    <style>
        /* Anda bisa copy-paste style dari reward_give.php ke sini */
        :root { --primary-color: #4f46e5; }
        .table-container { background: #ffffff; border: 1px solid #e5e7eb; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05); }
        .filter-section { background: linear-gradient(135deg, #ffffff 0%, #f9fafb 100%); border: 1px solid #e5e7eb; }
    </style>
</head>

<body class="bg-gray-50 text-gray-900">
    <?php include '../../../component/navigation_report.php'; ?>
    <?php include '../../../component/sidebar_report.php'; ?>

    <main id="main-content" class="flex-1 p-6 transition-all duration-300 ml-64">
        <div class="max-w-full mx-auto px-4">
            <div class="min-h-screen bg-white rounded-2xl shadow-xl p-8">
                <div class="flex flex-col items-center mb-8">
                    <div class="bg-indigo-600 p-4 rounded-2xl shadow-lg mb-4">
                        <i class="fas fa-history text-white text-3xl"></i>
                    </div>
                    <h1 class="text-3xl font-bold text-gray-800">Histori Penerimaan Hadiah</h1>
                    <p class="text-gray-600 mt-2">Lihat riwayat semua penerimaan stok hadiah di setiap cabang.</p>
                </div>

                <div class="filter-section rounded-2xl shadow-md p-6 mb-8">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 items-end">
                        <div class="space-y-2">
                            <label for="filterTanggal" class="block text-sm font-semibold text-gray-700">Rentang Tanggal</label>
                            <input type="text" id="filterTanggal" placeholder="Pilih tanggal..." class="w-full px-4 py-3 border border-gray-300 rounded-xl shadow-sm focus:ring-2 focus:ring-indigo-500 bg-white">
                        </div>
                        <div class="space-y-2">
                            <label for="filterCabang" class="block text-sm font-semibold text-gray-700">Cabang</label>
                            <select id="filterCabang" class="w-full px-4 py-3 border border-gray-300 rounded-xl shadow-sm focus:ring-2 focus:ring-indigo-500 bg-white">
                                </select>
                        </div>
                        <div class="space-y-2 md:col-span-2">
                            <label for="filterSearch" class="block text-sm font-semibold text-gray-700">Cari Data</label>
                            <div class="relative">
                                <input type="text" id="filterSearch" placeholder="Cari PLU, nama hadiah, atau karyawan..." class="w-full px-4 py-3 border border-gray-300 rounded-xl shadow-sm focus:ring-2 focus:ring-indigo-500">
                                <i class="fas fa-search absolute right-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto w-full table-container rounded-2xl">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-indigo-600 text-white">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Waktu</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">No. Bukti</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">PLU</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Nama Hadiah</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider">Jml Diterima</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider">Perubahan Poin</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Cabang</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Karyawan</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider">Jenis</th>

                            </tr>
                        </thead>
                        <tbody id="tableBody" class="divide-y divide-gray-200 text-gray-700 text-sm bg-white">
                            </tbody>
                    </table>
                </div>

                <div id="pagination-container" class="mt-8 flex flex-col lg:flex-row justify-between items-center gap-4">
                    </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/litepicker/dist/litepicker.js"></script>
    <script src="../../../js/rewards/histori_penerimaan.js" type="module"></script>
</body>

</html>