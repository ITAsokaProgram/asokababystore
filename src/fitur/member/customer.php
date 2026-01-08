<?php
// AMBIL SEMUA PARAMETER BARU
$filter_type = htmlspecialchars($_GET['filter_type'] ?? 'preset');
$filter = htmlspecialchars($_GET['filter'] ?? '3bulan');
$start_date = htmlspecialchars($_GET['start_date'] ?? '');
$end_date = htmlspecialchars($_GET['end_date'] ?? '');
$status = htmlspecialchars($_GET['status'] ?? 'unknown');
$kd_cust = htmlspecialchars($_GET['kd_cust'] ?? '-');
$nama_cust = htmlspecialchars($_GET['nama_cust'] ?? '-');

// LOGIKA TAMPILAN FILTER BARU
$filter_display = '';
if ($filter_type === 'custom' && $start_date && $end_date) {
    $filter_display = htmlspecialchars($start_date) . " s/d " . htmlspecialchars($end_date);
} else {
    $filter_map = [
        'kemarin' => 'Kemarin',
        '1minggu' => '1 Minggu Terakhir',
        '1bulan' => '1 Bulan Terakhir',
        '3bulan' => '3 Bulan Terakhir',
        '6bulan' => '6 Bulan Terakhir',
        '9bulan' => '9 Bulan Terakhir',
        '12bulan' => '1 Tahun Terakhir',
        'semua' => 'Semua Waktu'
    ];
    $filter_display = $filter_map[$filter] ?? 'Semua Waktu';
}

$status_display = ($status === 'active') ? 'Aktif' : (($status === 'inactive') ? 'Inaktif' : 'Tidak Diketahui');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barang Terlaris per Customer</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/all.min.css">
    <link rel="stylesheet" href="../../style/header.css">
    <link rel="stylesheet" href="../../style/sidebar.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="icon" type="image/png" href="../../../public/images/logo1.png">
    <link rel="stylesheet" href="../../style/member/style.css">

    <link rel="stylesheet" href="../../style/default-font.css">
    <link rel="stylesheet" href="../../output2.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/exceljs/4.3.0/exceljs.min.js"></script>
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
                        Customer: <strong><?php echo $nama_cust; ?> (<?php echo $kd_cust; ?>)</strong>,
                        Status: <strong><?php echo $status_display; ?></strong>,
                        dan Filter Waktu: <strong><?php echo $filter_display; ?></strong>.
                    </p>
                </div>
                <a href="javascript:history.back()" class="btn-back">
                    <i class="fa-solid fa-arrow-left"></i>
                    Kembali
                </a>
            </div>

            <div class="member-card slide-up p-4">
                <div class="page-header mb-6 flex items-center justify-between">
                    <h2 class="text-xl font-semibold gradient-text">
                        <i class="fa-solid fa-ranking-star mr-2"></i>
                        Top Produk Terlaris (Dibeli oleh <?php echo $nama_cust; ?>)
                    </h2>
                    <div class="flex space-x-2">
                        <button id="export-excel-button"
                            class="bg-emerald-600 hover:bg-emerald-500 text-white px-4 py-2 rounded-lg shadow-md text-sm font-medium flex items-center transition-colors">
                            <i class="fa-solid fa-file-excel mr-2"></i>
                            Export Excel
                        </button>

                        <button id="btn-send-general-wa"
                            class="bg-green-500 hover:bg-green-400 text-white px-4 py-2 rounded-lg shadow-md text-sm font-medium flex items-center"
                            title="Kirim pesan WhatsApp ke <?php echo $nama_cust; ?>">
                            <i class="fa-brands fa-whatsapp mr-2"></i>
                            Kirim Pesan
                        </button>
                    </div>
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
                                <th>
                                    Aksi
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

        <div id="transaction-detail-modal"
            class="hidden fixed inset-0 z-50 overflow-y-auto bg-gray-600 bg-opacity-75 transition-opacity"
            aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 text-center sm:block sm:p-0">
                <div
                    class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div
                                class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10">
                                <i class="fa-solid fa-receipt text-blue-600"></i>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left flex-1">
                                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                    Detail Transaksi
                                </h3>
                                <p class="text-sm text-gray-500 mb-4" id="modal-product-name">
                                    Memuat...</p>

                                <div class="overflow-y-auto max-h-96 border rounded-lg relative">
                                    <div id="modal-loading-spinner"
                                        class="hidden absolute inset-0 bg-white bg-opacity-75 flex items-center justify-center z-10">
                                        <i class="fa-solid fa-spinner fa-spin text-blue-500 text-2xl"></i>
                                    </div>
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-200 sticky top-0">
                                            <tr>
                                                <th scope="col"
                                                    class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Nama Produk
                                                </th>
                                                <th scope="col"
                                                    class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Qty
                                                </th>
                                                <th scope="col"
                                                    class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Harga
                                                </th>
                                                <th scope="col"
                                                    class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Total
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody id="modal-table-body" class="bg-white divide-y divide-gray-200">
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button id="modal-close-btn" type="button"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="../../js/ui/navbar_toogle.js" type="module"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../../js/member/customer_handler.js" type="module"></script>
</body>

</html>