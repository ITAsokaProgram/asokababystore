<?php
session_start();
include '../../../aa_kon_sett.php';

$tgl_selesai = date('Y-m-d');
$tgl_mulai = date('Y-m-d', strtotime('-1 month'));
$page = (int) ($_GET['page'] ?? 1);

require_once __DIR__ . '/../../component/menu_handler.php';
$menuHandler = new MenuHandler('receipt_index');
if (!$menuHandler->initialize()) {
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Penerimaan (Receipt)</title>
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

                <div class="header-card p-4 rounded-2xl mb-4">
                    <div class="flex items-center justify-between flex-wrap gap-3">
                        <div class="flex items-center gap-3">
                            <div class="icon-wrapper">
                                <i class="fa-solid fa-receipt fa-lg"></i>
                            </div>
                            <div>
                                <h1 class="text-xl font-bold text-gray-800 mb-1">Data Receipt</h1>
                                <p class="text-xs text-gray-600">Laporan penerimaan faktur supplier.</p>
                            </div>
                        </div>
                        <a href="create.php"
                            class="btn-primary inline-flex items-center justify-center gap-2 no-underline">
                            <i class="fa-solid fa-plus"></i>
                            <span>Buat Receipt</span>
                        </a>
                    </div>
                </div>

                <div class="filter-card-simple mb-4">
                    <form id="filter-form" class="grid grid-cols-1 md:grid-cols-4 gap-3 items-end">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-2">Dari Tanggal</label>
                            <input type="date" name="tgl_mulai" id="tgl_mulai" class="input-modern w-full"
                                value="<?= $tgl_mulai ?>">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-2">Sampai Tanggal</label>
                            <input type="date" name="tgl_selesai" id="tgl_selesai" class="input-modern w-full"
                                value="<?= $tgl_selesai ?>">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-2">Cari No Faktur</label>
                            <input type="text" name="search" id="search" class="input-modern w-full"
                                placeholder="Ketik nomor faktur...">
                        </div>
                        <div>
                            <button type="submit" id="filter-submit-button"
                                class="btn-primary w-full inline-flex items-center justify-center gap-2">
                                <i class="fas fa-filter"></i>
                                <span>Tampilkan</span>
                            </button>
                        </div>
                        <input type="hidden" name="page" id="current_page" value="1">
                    </form>
                </div>

                <div class="filter-card">
                    <div class="table-container">
                        <table class="table-modern" id="receipt-table">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Tanggal</th>
                                    <th>Kode Supplier</th>
                                    <th>Nama Supplier</th>
                                    <th>No Faktur</th>
                                    <th>No Invoice</th>
                                    <th class="text-right">Total Penerimaan</th>
                                    <th>Keterangan</th>
                                </tr>
                            </thead>
                            <tbody id="receipt-table-body">
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
    <script src="../../js/receipt/handler.js" type="module"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

</body>

</html>