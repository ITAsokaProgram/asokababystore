<?php
session_start();
include '../../../aa_kon_sett.php';
$tanggal_kemarin = date('Y-m-d', strtotime('-1 day'));

$default_tgl_mulai = $tanggal_kemarin;
$default_tgl_selesai = $tanggal_kemarin;
$default_kd_store = 'all';
$default_page = 1;

$tgl_mulai = $_GET['tgl_mulai'] ?? $default_tgl_mulai;
$tgl_selesai = $_GET['tgl_selesai'] ?? $default_tgl_selesai;
$kd_store = $_GET['kd_store'] ?? $default_kd_store;
$page = (int) ($_GET['page'] ?? $default_page);
if ($page < 1) {
    $page = 1;
}

function build_pagination_url($new_page)
{
    $params = $_GET;
    $params['page'] = $new_page;
    return '?' . http_build_query($params);
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales per Kasir & Bon</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/all.min.css">
    <link rel="stylesheet" href="../../style/header.css">
    <link rel="stylesheet" href="../../style/sidebar.css">
    <link rel="stylesheet" href="../../style/animation-fade-in.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../style/default-font.css">
    <link rel="stylesheet" href="../../output2.css">
    <link rel="stylesheet" href="../../style/pink-theme.css">
    <link rel="icon" type="image/png" href="../../../public/images/logo1.png">
    <script src="https://cdn.sheetjs.com/xlsx-latest/package/dist/xlsx.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js"></script>

    <style>
        /* Sembunyikan overlay by default */
        #rotate-prompt {
            display: none;
        }

        /* Tampilkan overlay HANYA di layar HP (max-width: 767px)
           DAN HANYA jika orientasinya portrait */
        @media screen and (max-width: 767px) and (orientation: portrait) {
            #rotate-prompt {
                /* Gunakan flex untuk memunculkan (Tailwind: "flex") */
                display: flex;
            }
        }
    </style>
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
                                <i class="fas fa-cash-register fa-lg"></i>
                            </div>
                            <div>
                                <h1 id="page-title" class="text-xl font-bold text-gray-800 mb-1">Sales per Kasir & Bon
                                </h1>
                                <p id="page-subtitle" class="text-xs text-gray-600">Memuat detail periode...</p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                        <div class="summary-card total flex gap-4 items-center">
                            <div class="summary-icon">
                                <i class="fas fa-cash-register fa-lg"></i>
                            </div>
                            <div>
                                <h3 class="text-xs font-semibold text-gray-600 mb-1">Total Net Sales</h3>
                                <p id="summary-net-sales" class="text-2xl font-bold truncate text-gray-900">-</p>
                            </div>
                        </div>
                        <div class="summary-card flex gap-4 items-center success">
                            <div class="summary-icon">
                                <i class="fas fa-wallet fa-lg"></i>
                            </div>
                            <div>
                                <h3 class="text-xs font-semibold text-gray-600 mb-1">Total Gross Margin</h3>
                                <p id="summary-grs-margin" class="text-2xl font-bold truncate text-green-600">-</p>
                            </div>
                        </div>
                        <div class="summary-card flex gap-4 items-center danger">
                            <div class="summary-icon">
                                <i class="fas fa-boxes fa-lg"></i>
                            </div>
                            <div>
                                <h3 class="text-xs font-semibold text-gray-600 mb-1">Total HPP</h3>
                                <p id="summary-hpp" class="text-2xl font-bold truncate text-red-600">-</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="filter-card-simple">
                    <form id="filter-form" class="grid grid-cols-1 md:grid-cols-4 gap-3 items-end" method="GET"
                        action="sales_per_kasir_bon.php">
                        <div>
                            <label for="tgl_mulai" class="block text-xs font-semibold text-gray-700 mb-2"> <i
                                    class="fas fa-calendar-alt text-pink-600 mr-1"></i>
                                Dari Tanggal
                            </label>
                            <input type="date" name="tgl_mulai" id="tgl_mulai" class="input-modern w-full"
                                value="<?php echo htmlspecialchars($tgl_mulai); ?>">
                        </div>
                        <div>
                            <label for="tgl_selesai" class="block text-xs font-semibold text-gray-700 mb-2"> <i
                                    class="fas fa-calendar-alt text-pink-600 mr-1"></i>
                                Sampai Tanggal
                            </label>
                            <input type="date" name="tgl_selesai" id="tgl_selesai" class="input-modern w-full"
                                value="<?php echo htmlspecialchars($tgl_selesai); ?>">
                        </div>
                        <div>
                            <label for="kd_store" class="block text-xs font-semibold text-gray-700 mb-2"> <i
                                    class="fas fa-store text-pink-600 mr-1"></i>
                                Pilih Cabang
                            </label>
                            <select name="kd_store" id="kd_store" class="input-modern w-full">
                                <option value="all">Seluruh Store</option>
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
                        <table class="table-modern" id="top-sales-table">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Tanggal</th>
                                    <th>Kd Kasir</th>
                                    <th>Nama Kasir</th>
                                    <th>No Trans</th>
                                    <th>PLU</th>
                                    <th>Nama Barang</th>
                                    <th>Qty</th>
                                    <th>Harga</th>
                                    <th>Disc</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody id="top-sales-table-body">
                                <tr>
                                    <td colspan="11" class="text-center p-8">
                                        <div class="spinner-simple"></div>
                                        <p class="mt-3 text-gray-500 font-medium">Memuat data...</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div id="pagination-container" class="flex justify-between items-center mt-4"> <span
                            id="pagination-info" class="text-sm text-gray-600"></span>

                        <div id="pagination-links" class="flex items-center gap-2">
                        </div>
                    </div>
                </div>

            </div>
        </section>
    </main>

    <script src="/src/js/middleware_auth.js"></script>
    <script src="../../js/top_sales/sales_per_kasir_bon_handler.js" type="module"></script>
    <script src="../../js/shared/internal/sidebar-profile.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <div id="rotate-prompt" class="fixed inset-0 bg-black bg-opacity-80 flex items-center justify-center z-[9999]">
        <div class="text-white text-center p-4">
            <svg class="mx-auto h-12 w-12 text-white animate-pulse" fill="none" viewBox="0 0 24 24"
                stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M12 18h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2zM12 8v4l-1.172-1.172a.999.999 0 10-1.414 1.414L12 15l2.586-2.586a.999.999 0 10-1.414-1.414L12 12V8z" />
            </svg>
            <p class="text-lg font-semibold mt-4">Harap putar perangkat Anda</p>
            <p class="text-sm">Untuk tampilan laporan terbaik, gunakan mode landscape.</p>
        </div>
    </div>
</body>

</html>