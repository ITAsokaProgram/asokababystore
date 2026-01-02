<?php
session_start();
include '../../../aa_kon_sett.php';

$default_end = $_GET['end_date'] ?? date('Y-m-d');
$default_start = $_GET['start_date'] ?? date('Y-m-d', strtotime('-1 day'));

?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Security Logs Monitor</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/all.min.css">
    <link rel="stylesheet" href="../../style/header.css">
    <link rel="stylesheet" href="../../style/sidebar.css">
    <link rel="stylesheet" href="../../style/animation-fade-in.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../style/default-font.css">
    <link rel="stylesheet" href="../../output2.css">
    <link rel="stylesheet" href="../../style/pink-theme.css">
    <link rel="icon" type="image/png" href="../../../public/images/logo1.png">
</head>

<body class="bg-gray-50">
    <?php include '../../component/navigation_report.php' ?>
    <?php include '../../component/sidebar_report.php' ?>

    <main id="main-content" class="flex-1 p-6 ml-64">
        <section class="min-h-screen">
            <div class="max-w-7xl mx-auto">

                <div class="header-card p-6 rounded-2xl mb-6">
                    <div class="flex items-center justify-between flex-wrap gap-4">
                        <div class="flex items-center gap-4">
                            <div class="icon-wrapper">
                                <i class="fas fa-shield-alt fa-lg"></i>
                            </div>
                            <div>
                                <h1 class="text-2xl font-bold text-gray-800 mb-1">Security Logs</h1>
                                <p class="text-sm text-gray-600">Monitoring akses SSH, MySQL, dan layanan lainnya.</p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="summary-card total">
                            <div class="summary-icon"><i class="fas fa-list-ul fa-lg"></i></div>
                            <h3 class="text-sm font-semibold text-gray-600 mb-1">Total Events</h3>
                            <p id="summary-total-events" class="text-3xl font-bold text-gray-900">-</p>
                        </div>
                        <div class="summary-card danger">
                            <div class="summary-icon"><i class="fas fa-network-wired fa-lg"></i></div>
                            <h3 class="text-sm font-semibold text-gray-600 mb-1">Unique IPs</h3>
                            <p id="summary-unique-ips" class="text-3xl font-bold text-red-600">-</p>
                        </div>
                        <div class="summary-card success">
                            <div class="summary-icon"><i class="fas fa-server fa-lg"></i></div>
                            <h3 class="text-sm font-semibold text-gray-600 mb-1">Top Service</h3>
                            <p id="summary-top-service" class="text-3xl font-bold text-blue-600 text-lg mt-1">-</p>
                        </div>
                    </div>
                </div>

                <div class="filter-card-simple">
                    <form id="filter-form" class="flex flex-wrap items-end gap-4">
                        <div>
                            <label for="start_date" class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-calendar-alt text-pink-600 mr-1"></i> Dari Tanggal
                            </label>
                            <input type="date" name="start_date" id="start_date" value="<?php echo $default_start; ?>"
                                class="input-modern w-full md:w-48">
                        </div>

                        <div>
                            <label for="end_date" class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-calendar-alt text-pink-600 mr-1"></i> Sampai Tanggal
                            </label>
                            <input type="date" name="end_date" id="end_date" value="<?php echo $default_end; ?>"
                                class="input-modern w-full md:w-48">
                        </div>

                        <button type="submit" id="filter-submit-button"
                            class="btn-primary inline-flex items-center gap-2 mb-0.5">
                            <i class="fas fa-filter"></i>
                            <span>Tampilkan</span>
                        </button>
                    </form>
                </div>

                <div class="filter-card">
                    <h3 class="text-lg font-bold text-gray-800 mb-4">
                        <i class="fas fa-table text-pink-600 mr-2"></i>
                        Daftar Log (<span id="periode-teks" class="text-sm font-normal text-gray-500"></span>)
                    </h3>
                    <div class="table-container">
                        <table class="table-modern" id="security-log-table">
                            <thead>
                                <tr>
                                    <th><i class="fas fa-clock mr-2"></i>Waktu</th>
                                    <th><i class="fas fa-globe mr-2"></i>IP Address</th>
                                    <th><i class="fas fa-flag mr-2"></i>Negara</th>
                                    <th><i class="fas fa-cogs mr-2"></i>Service</th>
                                </tr>
                            </thead>
                            <tbody id="log-table-body">
                                <tr>
                                    <td colspan="4" class="text-center p-8">
                                        <div class="spinner-simple"></div>
                                        <p class="mt-3 text-gray-500 font-medium">Memuat data...</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </section>
    </main>

    <script src="/src/js/middleware_auth.js"></script>
    <script src="../../js/security_logs/log_handler.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

</body>

</html>