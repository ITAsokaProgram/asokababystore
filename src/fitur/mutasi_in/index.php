<?php
session_start();
include '../../../aa_kon_sett.php';
$tanggal_kemarin = date('Y-m-d', strtotime('-1 day'));

$default_tgl_mulai = $tanggal_kemarin;
$default_tgl_selesai = $tanggal_kemarin;
$default_kd_store = 'all';

$tgl_mulai = $_GET['tgl_mulai'] ?? $default_tgl_mulai;
$tgl_selesai = $_GET['tgl_selesai'] ?? $default_tgl_selesai;
$kd_store = $_GET['kd_store'] ?? $default_kd_store;
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Mutasi In</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/all.min.css">
    <link rel="stylesheet" href="../../style/header.css">
    <link rel="stylesheet" href="../../style/sidebar.css">
    <link rel="stylesheet" href="../../style/animation-fade-in.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../style/default-font.css">
    <link rel="stylesheet" href="../../output2.css">
    <link rel="stylesheet" href="../../style/pink-theme.css">
    <link rel="icon" type="image/png" href="../../../public/images/logo1.png">

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.sheetjs.com/xlsx-latest/package/dist/xlsx.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/exceljs/4.3.0/exceljs.min.js"></script>
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
                                <i class="fa-solid fa-right-to-bracket fa-lg"></i>
                            </div>
                            <div>
                                <h1 id="page-title" class="text-xl font-bold text-gray-800 mb-1">Laporan Mutasi In</h1>
                                <p id="page-subtitle" class="text-xs text-gray-600">Periode...</p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3">
                        <div class="summary-card total flex gap-4 items-center">
                            <div class="summary-icon">
                                <i class="fas fa-boxes-packing fa-lg"></i>
                            </div>
                            <div>
                                <h3 class="text-xs font-semibold text-gray-600 mb-1">Total Qty</h3>
                                <p id="summary-qty" class="text-2xl font-bold truncate text-gray-900">-</p>
                            </div>
                        </div>
                        <div class="summary-card flex gap-4 items-center total">
                            <div class="summary-icon">
                                <i class="fas fa-money-bill-wave fa-lg"></i>
                            </div>
                            <div>
                                <h3 class="text-xs font-semibold text-gray-600 mb-1">Total Rp Netto</h3>
                                <p id="summary-netto" class="text-2xl font-bold truncate text-gray-900">-</p>
                            </div>
                        </div>
                        <div class="summary-card flex gap-4 items-center total">
                            <div class="summary-icon">
                                <i class="fas fa-file-invoice-dollar fa-lg"></i>
                            </div>
                            <div>
                                <h3 class="text-xs font-semibold text-gray-600 mb-1">Total Rp PPN</h3>
                                <p id="summary-ppn" class="text-2xl font-bold truncate text-gray-900">-</p>
                            </div>
                        </div>
                        <div class="summary-card flex gap-4 items-center warning">
                            <div class="summary-icon">
                                <i class="fa-solid fa-calculator fa-lg"></i>
                            </div>
                            <div>
                                <h3 class="text-xs font-semibold text-gray-600 mb-1">Grand Total</h3>
                                <p id="summary-total" class="text-2xl font-bold truncate text-yellow-600">-</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="filter-card-simple mb-4">
                    <form id="filter-form" class="grid grid-cols-1 md:grid-cols-5 gap-3 items-end">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-2">Dari Tanggal</label>
                            <input type="date" name="tgl_mulai" id="tgl_mulai" class="input-modern w-full"
                                value="<?php echo htmlspecialchars($tgl_mulai); ?>">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-2">Sampai Tanggal</label>
                            <input type="date" name="tgl_selesai" id="tgl_selesai" class="input-modern w-full"
                                value="<?php echo htmlspecialchars($tgl_selesai); ?>">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-2">Cabang</label>
                            <select name="kd_store" id="kd_store" class="input-modern w-full">
                                <option value="all">Seluruh Store</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-2">Status</label>
                            <div class="flex gap-2">
                                <select name="status_cetak" id="status_cetak" class="input-modern w-1/2 text-xs">
                                    <option value="all">Semua Cetak</option>
                                    <option value="True">Sudah Cetak</option>
                                    <option value="False">Belum Cetak</option>
                                </select>
                                <select name="status_terima" id="status_terima" class="input-modern w-1/2 text-xs">
                                    <option value="all">Semua Terima</option>
                                    <option value="True">Sudah Terima</option>
                                    <option value="False">Belum Terima</option>
                                </select>
                            </div>
                        </div>

                        <button type="submit" id="filter-submit-button"
                            class="btn-primary inline-flex items-center justify-center gap-2 h-10">
                            <i class="fas fa-filter"></i> <span>Tampilkan</span>
                        </button>
                        <input type="hidden" name="page" value="1">
                    </form>
                </div>

                <div class="filter-card">
                    <div class="flex flex-wrap justify-between items-center mb-3 gap-3">
                        <h3 class="text-lg font-bold text-gray-800">
                            <i class="fas fa-list text-pink-600 mr-2"></i>
                            Hasil Mutasi
                        </h3>
                        <div class="flex items-center gap-2">
                            <button id="export-excel-btn" class="btn-secondary-outline px-3 py-1.5 rounded-md"
                                style="background-color: #E6F7F0; border-color: #107C41; color: #107C41;">
                                <i class="fas fa-file-excel"></i> <span>Export Excel</span>
                            </button>
                        </div>
                    </div>

                    <div class="table-container">
                        <table class="table-modern w-full" id="mutasi-table">
                            <thead>
                                <tr>
                                    <th>Tgl Mutasi</th>
                                    <th>No Faktur</th>
                                    <th>Kode Supp</th>
                                    <th>Dari (Asal)</th>
                                    <th>Tujuan</th>
                                    <th class="text-right">Total</th>
                                    <th class="text-right">PPN</th>
                                    <th class="text-right">Grand Total</th>
                                    <th>Acc Mutasi</th>
                                    <th class="text-center">Rec</th>
                                    <th class="text-center">Cetak</th>
                                </tr>
                            </thead>
                            <tbody id="mutasi-table-body">
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
    <script src="../../js/mutasi_in/index_handler.js" type="module"></script>
    <script src="../../js/shared/internal/sidebar-profile.js" defer></script>
</body>

</html>