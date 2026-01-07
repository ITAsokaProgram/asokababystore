<?php
$filter_type = htmlspecialchars($_GET['filter_type'] ?? 'preset');
$filter = htmlspecialchars($_GET['filter'] ?? '3bulan');
$start_date = htmlspecialchars($_GET['start_date'] ?? '');
$end_date = htmlspecialchars($_GET['end_date'] ?? '');

$filter_display = '';
if ($filter_type === 'custom' && $start_date && $end_date) {
    $filter_display = htmlspecialchars($start_date) . " s/d " . htmlspecialchars($end_date);
} else {
    $filter_map = [
        'kemarin' => 'Kemarin',
        '1minggu' => '1 Minggu Terakhir',
        '1bulan' => '1 Bulan Terakhir',
        '3bulan' => '3 Bulan Terakhir',
        '6bulan' => '6 Bulan Terakhir',
        '12bulan' => '1 Tahun Terakhir',
        'semua' => 'Semua Waktu'
    ];
    $filter_display = $filter_map[$filter] ?? '3 Bulan Terakhir';
}
?>
<!DOCTYPE html>
<html lang="en">


<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Per Kota</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/all.min.css">
    <link rel="stylesheet" href="../../style/header.css">
    <link rel="stylesheet" href="../../style/sidebar.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="icon" type="image/png" href="../../../public/images/logo1.png">
    <link rel="stylesheet" href="../../style/member/style.css">

    <link rel="stylesheet" href="../../style/default-font.css">
    <link rel="stylesheet" href="../../output2.css">
</head>

<body class="bg-gradient-to-br from-slate-50 to-blue-50 text-gray-900">
    <?php include '../../component/navigation_report.php'; ?>
    <?php include '../../component/sidebar_report.php'; ?>

    <main id="main-content" class="flex-1 p-4 lg:p-4 transition-all duration-300 ml-64">
        <div class="max-w-7xl mx-auto space-y-6">

            <div class="member-card fade-in p-4">
                <div class="page-header">
                    <h1 class="page-title">
                        <i class="fa-solid fa-city mr-2 text-blue-600"></i>
                        Top Customer per Kota
                    </h1>
                    <p class="page-subtitle">
                        Pilih kota untuk melihat leaderboard customer.
                        Filter Waktu: <strong><?php echo $filter_display; ?></strong>
                    </p>
                </div>

                <div class="mt-4 flex flex-wrap items-end gap-3 justify-between">
                    <a href="javascript:history.back()" class="btn-back mb-1">
                        <i class="fa-solid fa-arrow-left"></i> Kembali
                    </a>

                    <div class="flex items-end gap-2 w-full md:w-auto">
                        <div class="relative w-full md:w-64">
                            <label for="city-filter" class="block text-xs font-medium text-gray-500 mb-1">Pilih
                                Kota</label>
                            <select id="city-filter"
                                class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                                <option value="" disabled selected>-- Pilih Kota --</option>
                            </select>
                        </div>

                        <button id="btn-search"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg shadow-sm text-sm font-medium transition-colors h-[38px] flex items-center">
                            <i class="fa-solid fa-magnifying-glass mr-2"></i>
                            Cari
                        </button>
                    </div>
                </div>
            </div>

            <div class="member-card slide-up p-4">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-semibold gradient-text">
                        <i class="fa-solid fa-ranking-star mr-2"></i> Hasil Pencarian
                    </h2>
                    <span id="record-info" class="text-sm text-gray-500"></span>
                </div>

                <div id="initial-state" class="text-center py-12">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-blue-100 mb-4">
                        <i class="fa-solid fa-map-location-dot text-3xl text-blue-600"></i>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900">Belum ada data ditampilkan</h3>
                    <p class="text-gray-500 mt-1">Silakan pilih kota dan klik "Cari" untuk melihat data.</p>
                </div>

                <div id="loading-spinner" class="loading-spinner hidden">
                    <i class="fa-solid fa-spinner fa-spin"></i>
                    <p class="loading-text">Menganalisa data transaksi...</p>
                </div>

                <div id="error-message" class="error-message hidden"></div>

                <div id="table-container" class="member-table-container overflow-x-auto hidden">
                    <table class="member-table w-full">
                        <thead>
                            <tr>
                                <th class="w-16">Rank</th>
                                <th>Nama Customer</th>
                                <th>Kota Domisili</th>
                                <th class="text-center">Frekuensi Belanja</th>
                                <th class="text-right">Total Transaksi</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="table-body"></tbody>
                    </table>
                </div>

                <div id="pagination-container" class="pagination-container hidden mt-4">
                    <div id="pagination-buttons" class="pagination-buttons flex justify-center space-x-2"></div>
                </div>
            </div>
        </div>
    </main>

    <script src="../../js/ui/navbar_toogle.js" type="module"></script>
    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <script type="module" src="../../js/member/top_customer_city_handler.js"></script>
</body>

</html>