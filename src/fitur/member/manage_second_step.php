<?php
$filter_type = htmlspecialchars($_GET['filter_type'] ?? 'preset');
$filter = htmlspecialchars($_GET['filter'] ?? '3bulan');
$start_date = htmlspecialchars($_GET['start_date'] ?? '');
$end_date = htmlspecialchars($_GET['end_date'] ?? '');
$status = htmlspecialchars($_GET['status'] ?? 'unknown');

$status_display = ($status === 'active') ? 'Aktif' : (($status === 'inactive') ? 'Inaktif' : 'Tidak Diketahui');

// --- OPTIMASI DI SINI ---
// Gunakan map untuk filter display agar lebih mudah dibaca dan dikelola
$filterDisplayMap = [
    'kemarin' => 'Kemarin',
    '1minggu' => '1 Minggu Terakhir',
    '1bulan' => '1 Bulan Terakhir',
    '3bulan' => '3 Bulan Terakhir',
    '6bulan' => '6 Bulan Terakhir',
    '9bulan' => '9 Bulan Terakhir',
    '12bulan' => '1 Tahun Terakhir',
    'semua' => 'Semua Waktu'
];

$filterDisplay = '';
if ($filter_type === 'custom' && $start_date && $end_date) {
    $filterDisplay = htmlspecialchars($start_date) . " s/d " . htmlspecialchars($end_date);
} else {
    // Ambil dari map, jika tidak ada, gunakan default 'Semua Waktu' atau '3 Bulan Terakhir'
    $filterDisplay = $filterDisplayMap[$filter] ?? 'Semua Waktu';
}
// --- AKHIR OPTIMASI ---

$queryParams = [
    'filter_type' => $filter_type,
    'status' => $status
];

if ($filter_type === 'custom') {
    $queryParams['start_date'] = $start_date;
    $queryParams['end_date'] = $end_date;
} else {
    $queryParams['filter'] = $filter;
}

$queryString = http_build_query($queryParams);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Member</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/all.min.css">
    <link rel="stylesheet" href="../../style/header.css">
    <link rel="stylesheet" href="../../style/sidebar.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="icon" type="image/png" href="../../../public/images/logo1.png">
    <link rel="stylesheet" href="../../style/default-font.css">
    <link rel="stylesheet" href="../../style/member/style.css">

    <link rel="stylesheet" href="../../output2.css">
    <script src="https://cdn.jsdelivr.net/npm/echarts/dist/echarts.min.js"></script>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>


</head>

<body class="bg-gradient-to-br from-slate-50 to-blue-50 text-gray-900">
    <?php include '../../component/navigation_report.php'; ?>
    <?php include '../../component/sidebar_report.php'; ?>
    <main id="main-content" class="flex-1 p-2 lg:p-3 transition-all duration-300 ml-64 mt-4">
        <div class="max-w-7xl mx-auto space-y-4">

            <div class="member-card fade-in p-3">
                <div class="page-header">
                    <h1 class="page-title">
                        <i class="fa-solid fa-trophy mr-2"></i>
                        Data Member
                    </h1>
                    <p class="page-subtitle">
                        Berdasarkan Filter Waktu: <strong><?php echo $filterDisplay; ?></strong>
                        dan Status Member: <strong><?php echo $status_display; ?></strong>
                        .
                    </p>
                </div>
                <a href="javascript:history.back()" class="btn-back">
                    <i class="fa-solid fa-arrow-left"></i>
                    Kembali
                </a>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                <div class="member-card slide-up p-3">
                    <div class="page-header mb-4">
                        <h2 class="text-2xl font-semibold gradient-text">
                            <i class="fa-solid fa-chart-pie mr-2"></i>
                            Distribusi Umur Member (
                            <?php echo $status_display; ?>)
                        </h2>
                    </div>

                    <div id="loading-spinner" class="loading-spinner">
                        <i class="fa-solid fa-spinner fa-spin"></i>
                        <p class="loading-text">Memuat data chart umur...</p>
                    </div>

                    <div id="age-chart-container" class="chart-wrapper hidden">
                        <div class="chart-container">
                            <div id="memberAgeChart" style="width: 100%; height: 400px;"></div>
                        </div>
                    </div>

                    <p id="age-chart-error" class="error-message hidden"></p>
                </div>

                <div class="member-card slide-up p-3">
                    <div class="page-header mb-4">
                        <h2 class="text-2xl font-semibold gradient-text">
                            <i class="fa-solid fa-table-list mr-2"></i>
                            Detail Umur & Produk Teratas
                        </h2>
                    </div>

                    <div id="age-table-loading-spinner" class="loading-spinner">
                        <i class="fa-solid fa-spinner fa-spin"></i>
                        <p class="loading-text">Memuat data tabel umur...</p>
                    </div>

                    <div id="age-table-container" class="member-table-container overflow-y-auto hidden"
                        style="height: 400px;">
                        <table class="member-table">
                            <thead>
                                <tr>
                                    <th>Kelompok Umur</th>
                                    <th>Jml Member</th>
                                    <th>Produk Teratas</th>
                                    <th>Qty</th>
                                </tr>
                            </thead>
                            <tbody id="age-table-body">
                            </tbody>
                        </table>
                    </div>

                    <p id="age-table-error" class="error-message hidden"></p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                <div class="member-card slide-up p-3">
                    <div class="page-header mb-4">
                        <h2 class="text-2xl font-semibold gradient-text">
                            <i class="fa-solid fa-map-location-dot mr-2"></i>
                            Distribusi Lokasi Member (
                            <?php echo $status_display; ?>)
                        </h2>
                    </div>

                    <div id="location-chart-header" class="mb-3 hidden">
                        <button id="location-back-btn" class="btn-back-chart">
                            <i class="fa-solid fa-arrow-left mr-2"></i>
                            Kembali
                        </button>
                        <span id="location-breadcrumb" class="text-lg font-medium ml-4"></span>
                    </div>

                    <div id="location-loading-spinner" class="loading-spinner">
                        <i class="fa-solid fa-spinner fa-spin"></i>
                        <p class="loading-text">Memuat data chart lokasi...</p>
                    </div>

                    <div id="location-chart-container" class="chart-wrapper hidden">
                        <div class="chart-container">
                            <div id="memberLocationChart" style="width: 100%; height: 400px;"></div>
                        </div>
                    </div>

                    <p id="location-chart-error" class="error-message hidden"></p>
                </div>

                <div class="member-card slide-up p-3">
                    <div class="page-header mb-4">
                        <h2 class="text-2xl font-semibold gradient-text">
                            <i class="fa-solid fa-table-list mr-2"></i>
                            Detail Lokasi & Produk Teratas (Top 20)
                        </h2>
                    </div>

                    <div class="mb-3 flex flex-wrap gap-2">
                        <a href="full_location.php?<?php echo $queryString; ?>" id="view-all-locations-btn"
                            class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition duration-200 text-sm">
                            Lihat Semua Lokasi
                        </a>

                        <a href="top_customer_city.php?<?php echo $queryString; ?>"
                            class="inline-block bg-emerald-600 hover:bg-emerald-700 text-white font-medium py-2 px-4 rounded-lg transition duration-200 text-sm">
                            Top Customer City
                        </a>
                        <a href="top_customer_cabang.php?<?php echo $queryString; ?>"
                            class="inline-block bg-purple-600 hover:bg-purple-700 text-white font-medium py-2 px-4 rounded-lg transition duration-200 text-sm">
                            Top Customer Cabang
                        </a>
                    </div>
                    <div id="location-table-loading-spinner" class="loading-spinner">
                        <i class="fa-solid fa-spinner fa-spin"></i>
                        <p class="loading-text">Memuat data tabel lokasi...</p>
                    </div>

                    <div id="location-table-container" class="member-table-container overflow-y-auto hidden"
                        style="height: 400px;">
                        <table class="member-table">
                            <thead>
                                <tr>
                                    <th id="location-table-header">Lokasi</th>
                                    <th>Jml Member</th>
                                    <th>Produk Teratas</th>
                                    <th>Qty</th>
                                </tr>
                            </thead>
                            <tbody id="location-table-body">
                            </tbody>
                        </table>
                    </div>

                    <p id="location-table-error" class="error-message hidden"></p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                <div class="member-card slide-up p-3">
                    <div class="page-header mb-4">
                        <h2 class="text-2xl font-semibold gradient-text">
                            <i class="fa-solid fa-star mr-2"></i>
                            Top 10 Member Chart
                        </h2>
                    </div>

                    <div id="top-member-chart-loading-spinner" class="loading-spinner">
                        <i class="fa-solid fa-spinner fa-spin"></i>
                        <p class="loading-text">Memuat data top member...</p>
                    </div>

                    <div id="top-member-chart-container" class="chart-wrapper hidden">
                        <div class="chart-container">
                            <div id="topMemberChart" style="width: 100%; height: 400px;"></div>
                        </div>
                    </div>

                    <p id="top-member-chart-error" class="error-message hidden"></p>
                </div>

                <div class="member-card slide-up p-3">
                    <div class="page-header mb-4">
                        <h2 class="text-2xl font-semibold gradient-text">
                            <i class="fa-solid fa-list-ol mr-2"></i>
                            Detail Top 10 Member
                        </h2>
                    </div>

                    <div class="mb-3">
                        <a href="top_sales.php?<?php echo $queryString; ?>" id="view-all-top-member-btn"
                            class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition duration-200 text-sm hidden">
                            Lihat Semua Top Member
                            <i class="fa-solid fa-arrow-right ml-2"></i>
                        </a>
                    </div>

                    <div id="top-member-table-loading-spinner" class="loading-spinner">
                        <i class="fa-solid fa-spinner fa-spin"></i>
                        <p class="loading-text">Memuat data tabel top member...</p>
                    </div>

                    <div id="top-member-table-container" class="member-table-container overflow-y-auto hidden"
                        style="height: 400px;">
                        <table class="member-table">
                            <thead>
                                <tr>
                                    <th>Nama Customer</th>
                                    <th>Kode</th>
                                    <th>Total Belanja</th>
                                </tr>
                            </thead>
                            <tbody id="top-member-table-body">
                            </tbody>
                        </table>
                    </div>

                    <p id="top-member-table-error" class="error-message hidden"></p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                <div class="member-card slide-up p-3">
                    <div class="page-header mb-4">
                        <h2 class="text-2xl font-semibold gradient-text">
                            <i class="fa-solid fa-fire mr-2"></i>
                            Top 10 Member Chart (Frekuensi)
                        </h2>
                    </div>

                    <div id="top-member-frequency-chart-loading-spinner" class="loading-spinner">
                        <i class="fa-solid fa-spinner fa-spin"></i>
                        <p class="loading-text">Memuat data top member (frekuensi)...</p>
                    </div>

                    <div id="top-member-frequency-chart-container" class="chart-wrapper hidden">
                        <div class="chart-container">
                            <div id="topMemberFrequencyChart" style="width: 100%; height: 400px;"></div>
                        </div>
                    </div>

                    <p id="top-member-frequency-chart-error" class="error-message hidden"></p>
                </div>

                <div class="member-card slide-up p-3">
                    <div class="page-header mb-4">
                        <h2 class="text-2xl font-semibold gradient-text">
                            <i class="fa-solid fa-list-ol mr-2"></i>
                            Detail Top 10 Member (Frekuensi)
                        </h2>
                    </div>

                    <div class="mb-3">
                        <a href="top_frequency.php?<?php echo $queryString; ?>" id="view-all-top-frequency-btn"
                            class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition duration-200 text-sm hidden">
                            Lihat Semua Top Member (Frekuensi)
                            <i class="fa-solid fa-arrow-right ml-2"></i>
                        </a>
                    </div>

                    <div id="top-member-frequency-table-loading-spinner" class="loading-spinner">
                        <i class="fa-solid fa-spinner fa-spin"></i>
                        <p class="loading-text">Memuat data tabel top member (frekuensi)...</p>
                    </div>

                    <div id="top-member-frequency-table-container" class="member-table-container overflow-y-auto hidden"
                        style="height: 400px;">
                        <table class="member-table">
                            <thead>
                                <tr>
                                    <th>Nama Customer</th>
                                    <th>Kode</th>
                                    <th>Jumlah Transaksi</th>
                                </tr>
                            </thead>
                            <tbody id="top-member-frequency-table-body">
                            </tbody>
                        </table>
                    </div>

                    <p id="top-member-frequency-table-error" class="error-message hidden"></p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                <div class="member-card slide-up p-3">
                    <div class="page-header mb-4">
                        <h2 class="text-2xl font-semibold gradient-text">
                            <i class="fa-solid fa-basket-shopping mr-2"></i>
                            Top 10 Pembelian Produk Chart
                        </h2>
                    </div>



                    <div id="top-product-chart-loading-spinner" class="loading-spinner">
                        <i class="fa-solid fa-spinner fa-spin"></i>
                        <p class="loading-text">Memuat data produk terlaris...</p>
                    </div>

                    <div id="top-product-chart-container" class="chart-wrapper hidden">
                        <div class="chart-container">
                            <div id="topMemberProductChart" style="width: 100%; height: 400px;"></div>
                        </div>
                    </div>

                    <p id="top-product-chart-error" class="error-message hidden"></p>
                </div>

                <div class="member-card slide-up p-3">
                    <div class="page-header mb-4">
                        <h2 class="text-2xl font-semibold gradient-text">
                            <i class="fa-solid fa-list-check mr-2"></i>
                            Detail Top 10 Pembelian
                        </h2>
                    </div>
                    <div class="mb-3">
                        <a href="product_favorite.php?<?php echo $queryString; ?>" id="view-all-top-product-btn"
                            class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition duration-200 text-sm hidden">
                            Lihat Semua Top Pembelian
                            <i class="fa-solid fa-arrow-right ml-2"></i>
                        </a>
                    </div>

                    <div id="top-product-table-loading-spinner" class="loading-spinner">
                        <i class="fa-solid fa-spinner fa-spin"></i>
                        <p class="loading-text">Memuat data tabel produk...</p>
                    </div>

                    <div id="top-product-table-container" class="member-table-container overflow-y-auto hidden"
                        style="height: 400px;">
                        <table class="member-table">
                            <thead>
                                <tr>
                                    <th>Produk</th>
                                    <th>Customer</th>
                                    <th>Qty</th>
                                </tr>
                            </thead>
                            <tbody id="top-product-table-body">
                            </tbody>
                        </table>
                    </div>

                    <p id="top-product-table-error" class="error-message hidden"></p>
                </div>
            </div>

        </div>
    </main>

    <script src="../../js/ui/navbar_toogle.js" type="module"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="../../js/member/manage_second_step_handler.js" type="module"></script>
    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


</body>

</html>