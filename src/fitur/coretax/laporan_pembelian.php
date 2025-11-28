<?php
session_start();
include '../../../aa_kon_sett.php';
$tanggal_kemarin = date('Y-m-d', strtotime('-1 day'));

$default_tgl_mulai = $tanggal_kemarin;
$default_tgl_selesai = $tanggal_kemarin;
$default_page = 1;

$tgl_mulai = $_GET['tgl_mulai'] ?? $default_tgl_mulai;
$tgl_selesai = $_GET['tgl_selesai'] ?? $default_tgl_selesai;
$page = (int) ($_GET['page'] ?? $default_page);
if ($page < 1) {
    $page = 1;
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Data Pembelian</title>
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

<body class="bg-gray-50 theme-pembelian">
    <?php include '../../component/navigation_report.php' ?>
    <?php include '../../component/sidebar_report.php' ?>

    <main id="main-content" class="flex-1 p-4 ml-64">
        <section class="min-h-screen">
            <div class="max-w-7xl mx-auto">

                <div class="header-card p-4 rounded-2xl mb-4">
                    <div class="flex items-center justify-between flex-wrap gap-3">
                        <div class="flex items-center gap-3">
                            <div class="icon-wrapper">
                                <i class="fa-solid fa-cart-shopping fa-lg"></i>
                            </div>
                            <div>
                                <h1 id="page-title" class="text-xl font-bold text-gray-800 mb-1">Laporan Data Pembelian
                                </h1>
                                <p id="page-subtitle" class="text-xs text-gray-600">Memuat data pembelian...</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="filter-card-simple">
                    <form id="filter-form" class="flex flex-wrap items-end gap-3" method="GET">
                        <div class="flex-1 min-w-[180px]">
                            <label for="tgl_mulai" class="block text-xs font-semibold text-gray-700 mb-2">
                                <i class="fas fa-calendar-alt text-pink-600 mr-1"></i> Dari
                            </label>
                            <input type="date" name="tgl_mulai" id="tgl_mulai" class="input-modern w-full"
                                value="<?php echo htmlspecialchars($tgl_mulai); ?>">
                        </div>
                        <div class="flex-1 min-w-[180px]">
                            <label for="tgl_selesai" class="block text-xs font-semibold text-gray-700 mb-2">
                                <i class="fas fa-calendar-alt text-pink-600 mr-1"></i> Sampai
                            </label>
                            <input type="date" name="tgl_selesai" id="tgl_selesai" class="input-modern w-full"
                                value="<?php echo htmlspecialchars($tgl_selesai); ?>">
                        </div>

                        <div class="flex-1 min-w-[200px]">
                            <label for="search_supplier" class="block text-xs font-semibold text-gray-700 mb-2">
                                <i class="fas fa-truck text-pink-600 mr-1"></i> Supplier
                            </label>
                            <input type="text" name="search_supplier" id="search_supplier" class="input-modern w-full"
                                placeholder="Kode/Nama Supplier">
                        </div>

                        <div class="flex-1 min-w-[150px]">
                            <button type="submit" id="filter-submit-button"
                                class="btn-primary w-full inline-flex items-center justify-center gap-2">
                                <i class="fas fa-filter"></i>
                                <span>Tampilkan</span>
                            </button>
                        </div>
                        <input type="hidden" name="page" value="1">
                    </form>
                </div>

                <div class="filter-card">
                    <div class="flex flex-wrap justify-between items-center mb-3 gap-3">
                        <h3 class="text-lg font-bold text-gray-800">
                            <i class="fas fa-list text-pink-600 mr-2"></i>
                            Hasil Laporan
                        </h3>
                    </div>
                    <div class="table-container">
                        <table class="table-modern" id="receipt-table">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Tgl Nota</th>
                                    <th>No Invoice</th>
                                    <th class="text-center">Cabang</th>
                                    <th>Nama Supplier</th>


                                    <th class="text-right">DPP</th>
                                    <th class="text-right">DPP Nilai Lain</th>
                                    <th class="text-right">PPN</th>
                                    <th class="text-right">Total</th>
                                    <th class="text-center" style="width: 180px;" colspan="2">NSFP</th>
                                </tr>
                            </thead>
                            <tbody id="receipt-table-body">
                                <tr>
                                    <td colspan="8" class="text-center p-8">
                                        <div class="spinner-simple"></div>
                                        <p class="mt-3 text-gray-500 font-medium">Memuat data...</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div id="pagination-container" class="flex justify-between items-center mt-4">
                        <span id="pagination-info" class="text-sm text-gray-600"></span>
                        <div id="pagination-links" class="flex items-center gap-2">
                        </div>
                    </div>
                </div>

            </div>
        </section>
    </main>

    <script src="/src/js/middleware_auth.js"></script>
    <script src="../../js/coretax/laporan_pembelian_handler.js" type="module"></script>
    <script src="../../js/shared/internal/sidebar-profile.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>

</html>