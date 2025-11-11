<?php
$filter = htmlspecialchars($_GET['filter'] ?? 'semua');
$status = htmlspecialchars($_GET['status'] ?? 'unknown');
$kd_cust = htmlspecialchars($_GET['kd_cust'] ?? 'Tidak diketahui');
$nama_cust = htmlspecialchars($_GET['nama_cust'] ?? 'Tidak diketahui');

$filter_display = 'Semua Waktu';
if ($filter === '6bulan') {
    $filter_display = '6 Bulan Terakhir';
} elseif ($filter === '3bulan') {
    $filter_display = '3 Bulan Terakhir';
} elseif ($filter === '9bulan') {
    $filter_display = '9 Bulan Terakhir';
} elseif ($filter === '12bulan') {
    $filter_display = '1 Tahun Terakhir';
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
                    <button id="btn-send-general-wa"
                        class="bg-green-500 hover:bg-green-400 text-white px-4 py-2 rounded-lg shadow-md text-sm font-medium flex items-center"
                        title="Kirim pesan WhatsApp ke <?php echo $nama_cust; ?>">
                        <i class="fa-brands fa-whatsapp mr-2"></i>
                        Kirim Pesan
                    </button>
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
    </main>

    <script src="../../js/ui/navbar_toogle.js" type="module"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../../js/member/customer_handler.js" type="module"></script>
</body>

</html>