<?php
$filter = htmlspecialchars($_GET['filter'] ?? '3bulan');
$status = htmlspecialchars($_GET['status'] ?? 'unknown');

$status_display = ($status === 'active') ? 'Aktif' : (($status === 'inactive') ? 'Inaktif' : 'Tidak Diketahui');
$filterDisplay = ($filter === '3bulan') ? '3 Bulan Terakhir' :
    (($filter === '6bulan') ? '6 Bulan Terakhir' :
        (($filter === '9bulan') ? '9 Bulan Terakhir' :
            (($filter === '12bulan') ? '1 Tahun Terakhir' : 'Semua Waktu')));
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
    <main id="main-content" class="flex-1 p-2 lg:p-4 transition-all duration-300 ml-64">
        <div class="max-w-7xl mx-auto space-y-6">

            <div class="member-card fade-in p-4">
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

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="member-card slide-up p-4">
                    <div class="page-header mb-6">
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

                <div class="member-card slide-up p-4">
                    <div class="page-header mb-6">
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

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="member-card slide-up p-4">
                    <div class="page-header mb-6">
                        <h2 class="text-2xl font-semibold gradient-text">
                            <i class="fa-solid fa-map-location-dot mr-2"></i>
                            Distribusi Lokasi Member (
                            <?php echo $status_display; ?>)
                        </h2>
                    </div>

                    <div id="location-chart-header" class="mb-4 hidden">
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

                <div class="member-card slide-up p-4">
                    <div class="page-header mb-6">
                        <h2 class="text-2xl font-semibold gradient-text">
                            <i class="fa-solid fa-table-list mr-2"></i>
                            Detail Lokasi & Produk Teratas (Top 20)
                        </h2>
                    </div>

                    <div class="mb-4">
                        <a href="full_location?filter=<?php echo $filter; ?>&status=<?php echo $status; ?>"
                            id="view-all-locations-btn"
                            class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition duration-200 text-sm">
                            Lihat Semua Lokasi
                            <i class="fa-solid fa-arrow-right ml-2"></i>
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

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="member-card slide-up p-4">
                    <div class="page-header mb-6">
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

                <div class="member-card slide-up p-4">
                    <div class="page-header mb-6">
                        <h2 class="text-2xl font-semibold gradient-text">
                            <i class="fa-solid fa-list-ol mr-2"></i>
                            Detail Top 10 Member
                        </h2>
                    </div>

                    <div class="mb-4">
                        <a href="top_sales" id="view-all-top-member-btn"
                            class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition duration-200 text-sm">
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

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="member-card slide-up p-4">
                    <div class="page-header mb-6">
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

                <div class="member-card slide-up p-4">
                    <div class="page-header mb-6">
                        <h2 class="text-2xl font-semibold gradient-text">
                            <i class="fa-solid fa-list-check mr-2"></i>
                            Detail Top 10 Pembelian
                        </h2>
                    </div>
                    <div class="mb-4">
                        <a href="top_sales" id="view-all-top-member-btn"
                            class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition duration-200 text-sm">
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