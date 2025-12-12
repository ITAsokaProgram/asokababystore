<?php
session_start();
include '../../../aa_kon_sett.php';

$tanggal_hari_ini = date('Y-m-d');
$tanggal_awal_bulan = date('Y-m-d', strtotime('-1 month'));

$tgl_mulai = $_GET['tgl_mulai'] ?? $tanggal_awal_bulan;
$tgl_selesai = $_GET['tgl_selesai'] ?? $tanggal_hari_ini;
$search = $_GET['search'] ?? '';

// require_once __DIR__ . '/../../component/menu_handler.php';
// $menuHandler = new MenuHandler('log_password_reset');
// if (!$menuHandler->initialize()) {
//     // exit();
// }
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log Reset Password</title>
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

    <main id="main-content" class="flex-1 p-4 ml-64">
        <section class="min-h-screen">
            <div class="max-w-7xl mx-auto">

                <div class="mt-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3">

                    <div class="summary-card total flex gap-4 items-center">
                        <div class="summary-icon">
                            <i class="fas fa-history fa-lg"></i>
                        </div>
                        <div>
                            <h3 class="text-xs font-semibold text-gray-600 mb-1">Total Permintaan</h3>
                            <p id="summary-total-req" class="text-2xl font-bold truncate text-gray-900">-</p>
                        </div>
                    </div>

                    <div class="summary-card flex gap-4 items-center" style="border-left: 4px solid #8b5cf6;">
                        <div class="summary-icon" style="background-color: #f3e8ff; color: #8b5cf6;">
                            <i class="fas fa-envelope fa-lg"></i>
                        </div>
                        <div>
                            <h3 class="text-xs font-semibold text-gray-600 mb-1">Via Email</h3>
                            <p id="summary-email" class="text-2xl font-bold truncate" style="color: #6d28d9;">-</p>
                        </div>
                    </div>

                    <div class="summary-card flex gap-4 items-center" style="border-left: 4px solid #10b981;">
                        <div class="summary-icon" style="background-color: #d1fae5; color: #10b981;">
                            <i class="fas fa-phone fa-lg"></i>
                        </div>
                        <div>
                            <h3 class="text-xs font-semibold text-gray-600 mb-1">Via No HP</h3>
                            <p id="summary-hp" class="text-2xl font-bold truncate" style="color: #059669;">-</p>
                        </div>
                    </div>

                    <div class="summary-card flex gap-4 items-center success">
                        <div class="summary-icon">
                            <i class="fas fa-check-circle fa-lg"></i>
                        </div>
                        <div>
                            <h3 class="text-xs font-semibold text-gray-600 mb-1">Berhasil Ganti</h3>
                            <p id="summary-success" class="text-2xl font-bold truncate text-green-600">-</p>
                        </div>
                    </div>
                </div>

                <div class="filter-card-simple mt-4">
                    <form id="filter-form" class="grid grid-cols-1 md:grid-cols-4 gap-3 items-end">
                        <div>
                            <label for="tgl_mulai" class="block text-xs font-semibold text-gray-700 mb-2">
                                <i class="fas fa-calendar-alt text-pink-600 mr-1"></i> Dari Tanggal
                            </label>
                            <input type="date" name="tgl_mulai" id="tgl_mulai" class="input-modern w-full"
                                value="<?php echo htmlspecialchars($tgl_mulai); ?>">
                        </div>
                        <div>
                            <label for="tgl_selesai" class="block text-xs font-semibold text-gray-700 mb-2">
                                <i class="fas fa-calendar-alt text-pink-600 mr-1"></i> Sampai Tanggal
                            </label>
                            <input type="date" name="tgl_selesai" id="tgl_selesai" class="input-modern w-full"
                                value="<?php echo htmlspecialchars($tgl_selesai); ?>">
                        </div>
                        <div>
                            <label for="search" class="block text-xs font-semibold text-gray-700 mb-2">
                                <i class="fas fa-search text-pink-600 mr-1"></i> Cari User
                            </label>
                            <input type="text" name="search" id="search" placeholder="Email atau No HP..."
                                class="input-modern w-full" value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                        <div>
                            <button type="submit" id="filter-submit-button"
                                class="btn-primary inline-flex items-center justify-center gap-2 w-full md:w-auto">
                                <i class="fas fa-filter"></i>
                                <span>Tampilkan</span>
                            </button>
                            <input type="hidden" name="page" value="1">
                        </div>
                    </form>
                </div>

                <div class="filter-card mt-4">
                    <div class="flex flex-wrap justify-between items-center mb-3 gap-3">
                        <h3 class="text-lg font-bold text-gray-800">
                            <i class="fas fa-list text-pink-600 mr-2"></i>
                            Log Reset Password
                        </h3>
                    </div>

                    <div class="table-container">
                        <table class="table-modern" id="logs-table">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>User (Email / No HP)</th>
                                    <th class="text-center">Status</th>
                                    <th>Tanggal Request</th>
                                </tr>
                            </thead>
                            <tbody id="table-body">
                                <tr>
                                    <td colspan="5" class="text-center p-8">
                                        <div class="spinner-simple"></div>
                                        <p class="mt-3 text-gray-500 font-medium">Memuat data...</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div id="pagination-container" class="flex justify-between items-center mt-4">
                        <span id="pagination-info" class="text-sm text-gray-600"></span>
                        <div id="pagination-links" class="flex items-center gap-2"></div>
                    </div>
                </div>

            </div>
        </section>
    </main>

    <script src="/src/js/middleware_auth.js"></script>
    <script src="../../js/logs/password_reset_handler.js" type="module"></script>

    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>

</html>