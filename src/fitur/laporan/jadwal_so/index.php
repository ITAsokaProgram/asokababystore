<?php
session_start();
include '../../../../aa_kon_sett.php';
$tanggal_hari_ini = date('Y-m-d');

$default_tgl_mulai = $tanggal_hari_ini;
$default_tgl_selesai = $tanggal_hari_ini;
$default_kd_store = 'all';
$default_status = 'all';
$default_sync = 'all';

$tgl_mulai = $_GET['tgl_mulai'] ?? $default_tgl_mulai;
$tgl_selesai = $_GET['tgl_selesai'] ?? $default_tgl_selesai;
$kd_store = $_GET['kd_store'] ?? $default_kd_store;
$status = $_GET['status'] ?? $default_status;
$sync = $_GET['sync'] ?? $default_sync;
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Jadwal SO</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/all.min.css">
    <link rel="stylesheet" href="../../../style/header.css">
    <link rel="stylesheet" href="../../../style/sidebar.css">
    <link rel="stylesheet" href="../../../style/animation-fade-in.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../../style/default-font.css">
    <link rel="stylesheet" href="../../../output2.css">
    <link rel="stylesheet" href="../../../style/pink-theme.css">
    <link rel="icon" type="image/png" href="../../../../public/images/logo1.png">

    <script src="https://cdn.sheetjs.com/xlsx-latest/package/dist/xlsx.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js"></script>
</head>

<body class="bg-gray-50">
    <?php include '../../../component/navigation_report.php' ?>
    <?php include '../../../component/sidebar_report.php' ?>

    <main id="main-content" class="flex-1 p-4 ml-64">
        <section class="min-h-screen">
            <div class="max-w-7xl mx-auto">

                <div class="header-card p-4 rounded-2xl mb-4">
                    <div class="flex items-center justify-between flex-wrap gap-3">
                        <div class="flex items-center gap-3">
                            <div class="icon-wrapper">
                                <i class="fa-solid fa-calendar-check fa-lg"></i>
                            </div>
                            <div>
                                <h1 id="page-title" class="text-xl font-bold text-gray-800 mb-1">Laporan Jadwal SO</h1>
                                <p id="page-subtitle" class="text-xs text-gray-600">Memuat data jadwal...</p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 grid grid-cols-1 md:grid-cols-4 gap-3">
                        <div class="summary-card total flex gap-4 items-center">
                            <div class="summary-icon">
                                <i class="fas fa-list-ul fa-lg"></i>
                            </div>
                            <div>
                                <h3 class="text-xs font-semibold text-gray-600 mb-1">Total Jadwal</h3>
                                <p id="summary-total" class="text-2xl font-bold truncate text-gray-900">-</p>
                            </div>
                        </div>

                        <div class="summary-card flex gap-4 items-center" style="border-left: 4px solid #3B82F6;">
                            <div class="summary-icon" style="background-color: #DBEAFE; color: #1D4ED8;">
                                <i class="fas fa-cog fa-spin fa-lg"></i>
                            </div>
                            <div>
                                <h3 class="text-xs font-semibold text-gray-600 mb-1">Proses</h3>
                                <p id="summary-proses" class="text-2xl font-bold truncate text-gray-900">-</p>
                            </div>
                        </div>

                        <div class="summary-card flex gap-4 items-center" style="border-left: 4px solid #10B981;">
                            <div class="summary-icon" style="background-color: #D1FAE5; color: #059669;">
                                <i class="fas fa-check-circle fa-lg"></i>
                            </div>
                            <div>
                                <h3 class="text-xs font-semibold text-gray-600 mb-1">Selesai</h3>
                                <p id="summary-selesai" class="text-2xl font-bold truncate text-gray-900">-</p>
                            </div>
                        </div>

                        <div class="summary-card flex gap-4 items-center warning">
                            <div class="summary-icon">
                                <i class="fas fa-clock fa-lg"></i>
                            </div>
                            <div>
                                <h3 class="text-xs font-semibold text-gray-600 mb-1">Menunggu</h3>
                                <p id="summary-tunggu" class="text-2xl font-bold truncate text-gray-900">-</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="filter-card-simple">
                    <form id="filter-form" class="grid grid-cols-1 md:grid-cols-6 gap-3 items-end">
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
                            <label for="kd_store" class="block text-xs font-semibold text-gray-700 mb-2">
                                <i class="fas fa-store text-pink-600 mr-1"></i> Pilih Cabang
                            </label>
                            <select name="kd_store" id="kd_store" class="input-modern w-full">
                                <option value="all">Seluruh Store</option>
                            </select>
                        </div>
                        <div>
                            <label for="status" class="block text-xs font-semibold text-gray-700 mb-2">
                                <i class="fas fa-tasks text-pink-600 mr-1"></i> Status
                            </label>
                            <select name="status" id="status" class="input-modern w-full">
                                <option value="all" <?php echo $status == 'all' ? 'selected' : ''; ?>>Semua Status
                                </option>
                                <option value="Proses" <?php echo $status == 'Proses' ? 'selected' : ''; ?>>Proses
                                </option>
                                <option value="Selesai" <?php echo $status == 'Selesai' ? 'selected' : ''; ?>>Selesai
                                </option>
                                <option value="Tunggu" <?php echo $status == 'Tunggu' ? 'selected' : ''; ?>>Tunggu
                                </option>
                            </select>
                        </div>

                        <div>
                            <label for="sync" class="block text-xs font-semibold text-gray-700 mb-2">
                                <i class="fas fa-sync text-pink-600 mr-1"></i> Status Sync
                            </label>
                            <select name="sync" id="sync" class="input-modern w-full">
                                <option value="all" <?php echo $sync == 'all' ? 'selected' : ''; ?>>Semua</option>
                                <option value="False" <?php echo $sync == 'False' ? 'selected' : ''; ?>>Pending (False)
                                </option>
                                <option value="True" <?php echo $sync == 'True' ? 'selected' : ''; ?>>Synced (True)
                                </option>
                            </select>
                        </div>

                        <button type="submit" id="filter-submit-button"
                            class="btn-primary inline-flex items-center justify-center gap-2">
                            <i class="fas fa-filter"></i>
                            <span>Tampilkan</span>
                        </button>
                        <input type="hidden" name="page" value="1">
                    </form>
                </div>

                <div class="filter-card">
                    <div class="flex flex-wrap justify-between items-center mb-3 gap-3">
                        <h3 class="text-lg font-bold text-gray-800">
                            <i class="fas fa-list text-pink-600 mr-2"></i>
                            Hasil Laporan
                        </h3>
                        <div class="flex items-center gap-2">
                            <button id="export-excel-btn" class="btn-secondary-outline px-3 py-1.5 rounded-md"
                                style="background-color: #E6F7F0; border-color: #107C41; color: #107C41;">
                                <i class="fas fa-file-excel"></i>
                                <span>Export Excel</span>
                            </button>
                            <button id="export-pdf-btn" class="btn-secondary-outline px-3 py-1.5 rounded-md"
                                style="background-color: #FFF0F0; border-color: #D93025; color: #D93025;">
                                <i class="fas fa-file-pdf"></i>
                                <span>Export PDF</span>
                            </button>
                        </div>
                    </div>
                    <div class="table-container">
                        <table class="table-modern" id="jadwal-table">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Tanggal Schedule</th>
                                    <th>Cabang</th>
                                    <th>Kode Supplier</th>
                                    <th>Nama Supplier</th>
                                    <th>Status</th>
                                    <th>Sync</th>
                                </tr>
                            </thead>
                            <tbody id="jadwal-table-body">
                                <tr>
                                    <td colspan="7" class="text-center p-8">
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
    <script src="../../../../src/js/laporan/jadwal_so/handler.js" type="module"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</body>

</html>