<?php
session_start();
include '../../../aa_kon_sett.php';

$selected_date_get = $_GET['tanggal'] ?? date('Y-m-d');
$date_options = [];
for ($i = 0; $i < 7; $i++) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $date_options[] = $date;
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log Sinkronisasi Cabang</title>
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
                                <i class="fas fa-sync-alt fa-lg"></i>
                            </div>
                            <div>
                                <h1 class="text-2xl font-bold text-gray-800 mb-1">Log Sinkronisasi Cabang</h1>
                                <p class="text-sm text-gray-600">Monitor status sinkronisasi harian dari semua cabang
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="summary-card total">
                            <div class="summary-icon">
                                <i class="fas fa-store fa-lg"></i>
                            </div>
                            <h3 class="text-sm font-semibold text-gray-600 mb-1">Total Cabang</h3>
                            <p id="summary-total-cabang" class="text-3xl font-bold text-gray-900">-</p>
                        </div>
                        <div class="summary-card success">
                            <div class="summary-icon">
                                <i class="fas fa-check-circle fa-lg"></i>
                            </div>
                            <h3 class="text-sm font-semibold text-gray-600 mb-1">Sudah Sinkron</h3>
                            <p id="summary-sudah-sinkron" class="text-3xl font-bold text-green-600">-</p>
                        </div>
                        <div class="summary-card danger">
                            <div class="summary-icon">
                                <i class="fas fa-exclamation-circle fa-lg"></i>
                            </div>
                            <h3 class="text-sm font-semibold text-gray-600 mb-1">Belum Sinkron</h3>
                            <p id="summary-belum-sinkron" class="text-3xl font-bold text-red-600">-</p>
                        </div>
                    </div>
                </div>

                <div class="filter-card-simple">
                    <form id="filter-form" class="flex items-end gap-4">
                        <div class="flex-1">
                            <label for="tanggal" class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-calendar-alt text-pink-600 mr-1"></i>
                                Pilih Tanggal
                            </label>
                            <select name="tanggal" id="tanggal" class="input-modern w-full md:w-64">
                                <?php foreach ($date_options as $date_opt): ?>
                                    <option value="<?php echo $date_opt; ?>" <?php echo ($selected_date_get == $date_opt) ? 'selected' : ''; ?>>
                                        <?php echo date('d F Y', strtotime($date_opt)) . ($date_opt == date('Y-m-d') ? ' (Hari Ini)' : ''); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="submit" id="filter-submit-button"
                            class="btn-primary inline-flex items-center gap-2">
                            <i class="fas fa-filter"></i>
                            <span>Tampilkan</span>
                        </button>
                        <button type="button" id="show-all-logs-button"
                            class="btn-secondary inline-flex items-center gap-2">
                            <i class="fas fa-file-alt"></i>
                            <span>Lihat Semua Log</span>
                        </button>
                    </form>
                </div>

                <div class="filter-card">
                    <h3 class="text-lg font-bold text-gray-800 mb-4">
                        <i class="fas fa-list text-pink-600 mr-2"></i>
                        Status Sinkronisasi (<span
                            id="tanggal-dipilih-teks"><?php echo htmlspecialchars($selected_date_get); ?></span>)
                    </h3>
                    <div class="table-container">
                        <table class="table-modern" id="log-backup-table">
                            <thead>
                                <tr>
                                    <th><i class="fas fa-building mr-2"></i>Nama Cabang</th>
                                    <th><i class="fas fa-signal mr-2"></i>Status</th>
                                    <th><i class="fas fa-check mr-2"></i>Total Sinkron</th>
                                    <th><i class="fas fa-exclamation-triangle mr-2"></i>Total Error</th>
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

    <div id="logDetailModal" class="modal-overlay" style="display: none;">
        <div class="modal-content relative mx-4 p-6 w-full max-w-4xl">
            <div class="flex justify-between items-center border-b border-gray-200 pb-4 mb-4">
                <h3 class="text-xl font-bold text-gray-900">
                    <i class="fas fa-file-code text-pink-600 mr-2"></i>
                    Detail Log: <span id="modalCabangName" class="text-pink-600"></span>
                </h3>
                <button id="closeLogModal"
                    class="text-gray-400 hover:text-gray-600 text-3xl leading-none transition-colors">&times;</button>
            </div>
            <div id="modalBodyContent" class="log-container">
                <p class="text-center text-gray-500">Memuat data...</p>
            </div>
        </div>
    </div>

    <script src="/src/js/middleware_auth.js"></script>
    <script src="../../js/log_backup/log_handler.js" type="module"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</body>

</html>