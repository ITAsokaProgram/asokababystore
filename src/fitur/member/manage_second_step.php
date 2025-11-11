<?php
// Ambil parameter dari URL untuk digunakan di halaman
$filter = htmlspecialchars($_GET['filter'] ?? '3bulan');
$status = htmlspecialchars($_GET['status'] ?? 'unknown');

$status_display = ($status === 'active') ? 'Aktif' : (($status === 'inactive') ? 'Inaktif' : 'Tidak Diketahui');
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
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
                        Menampilkan produk terlaris berdasarkan Filter Waktu: <strong><?php echo $filter; ?></strong>
                        dan Status Member: <strong><?php echo $status; ?></strong>
                        .

                    </p>
                </div>
                <a href="javascript:history.back()" class="btn-back">
                    <i class="fa-solid fa-arrow-left"></i>
                    Kembali
                </a>
            </div>
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
                    <div class="chart-container"> <canvas id="memberAgeChart"></canvas>
                    </div>
                </div>

                <p id="age-chart-error" class="error-message hidden"></p>
            </div>

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
                        <canvas id="memberLocationChart"></canvas>
                    </div>
                </div>

                <p id="location-chart-error" class="error-message hidden"></p>
            </div>
        </div>
    </main>

    <script src="../../js/ui/navbar_toogle.js" type="module"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="../../js/member/manage_second_step_handler.js" type="module"></script>

</body>

</html>