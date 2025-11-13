<?php
$filter = $_GET['filter'] ?? 'semua';
$age_group = $_GET['age_group'] ?? 'Tidak diketahui';

$filter_display = ($filter === 'kemarin') ? 'Kemarin' :
    (($filter === '1minggu') ? '1 Minggu Terakhir' :
        (($filter === '1bulan') ? '1 Bulan Terakhir' :
            (($filter === '3bulan') ? '3 Bulan Terakhir' :
                (($filter === '6bulan') ? '6 Bulan Terakhir' :
                    (($filter === '9bulan') ? '9 Bulan Terakhir' :
                        (($filter === '12bulan') ? '1 Tahun Terakhir' : 'Semua Waktu'))))));

$age_group_display = "Kelompok Umur: " . htmlspecialchars($age_group);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barang Terlaris per Umur</title>

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
                        <i class="fa-solid fa-trophy mr-2"></i>
                        Barang Terlaris
                    </h1>
                    <p class="page-subtitle">
                        Menampilkan produk terlaris berdasarkan
                        Kelompok Umur: <strong><?php echo $age_group; ?></strong> dan
                        Filter Waktu: <strong><?php echo $filter_display; ?></strong>.
                    </p>
                </div>
                <a href="javascript:history.back()" class="btn-back">
                    <i class="fa-solid fa-arrow-left"></i>
                    Kembali
                </a>
            </div>

            <!-- Update Table Section -->
            <div class="member-card slide-up p-4">
                <div class="page-header mb-6">
                    <h2 class="text-xl font-semibold gradient-text">
                        <i class="fa-solid fa-ranking-star mr-2"></i>
                        Top Produk Terlaris
                    </h2>
                </div>

                <div id="loading-spinner" class="loading-spinner">
                    <i class="fa-solid fa-spinner fa-spin"></i>
                    <p class="loading-text">Memuat data produk...</p>
                </div>

                <div id="error-message" class="error-message hidden"></div>

                <div id="product-table-container" class="member-table-container overflow-x-auto hidden">
                    <table class="member-table">
                        <thead>
                            <tr>
                                <th>
                                    No
                                </th>
                                <th>
                                    PLU
                                </th>
                                <th>
                                    Nama Produk
                                </th>
                                <th>
                                    Total Terjual
                                </th>
                            </tr>
                        </thead>
                        <tbody id="product-table-body"></tbody>
                    </table>
                </div>

                <div id="pagination-container" class="pagination-container hidden">
                    <span id="pagination-info" class="pagination-info"></span>
                    <div id="pagination-buttons" class="pagination-buttons"></div>
                </div>
            </div>
        </div>
    </main>

    <script src="../../js/ui/navbar_toogle.js" type="module"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="../../js/member/umur_handler.js" type="module"></script>

</body>

</html>