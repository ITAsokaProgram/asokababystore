<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Member</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/all.min.css">
    <link rel="stylesheet" href="../../style/header.css">
    <link rel="stylesheet" href="../../style/sidebar.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap"
        rel="stylesheet">
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap"
        rel="stylesheet">
    <link rel="icon" type="image/png" href="../../../public/images/logo1.png">
    <link rel="stylesheet" href="../../style/default-font.css">
    <link rel="stylesheet" href="../../output2.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>

    <style>
        .loading-placeholder {
            display: flex;
            align-items: center;
            color: #9ca3af;
            /* text-gray-400 */
        }

        .loading-placeholder .fa-spinner {
            margin-right: 8px;
        }
    </style>

</head>

<body class="bg-gradient-to-br from-slate-50 to-blue-50 text-gray-900">
    <?php include '../../component/navigation_report.php'; ?>
    <?php include '../../component/sidebar_report.php'; ?>

    <main id="main-content" class="flex-1 p-4 lg:p-6 transition-all duration-300 ml-64">
        <div class="max-w-7xl mx-auto space-y-6">

            <div class="bg-white/80 backdrop-blur-md rounded-2xl shadow-xl border border-blue-100 p-6 fade-in-header">
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                    <div class="flex items-center space-x-4">
                        <div class="bg-gradient-to-r from-blue-500 to-purple-600 p-3 rounded-xl shadow-lg">
                            <i class="fas fa-users text-white text-2xl"></i>
                        </div>
                        <div>
                            <h1
                                class="text-3xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">
                                Dashboard Member
                            </h1>
                            <p class="text-gray-600 mt-1">Ringkasan dan pintasan untuk manajemen member.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                <a href="management_member"
                    class="block bg-white p-6 rounded-2xl shadow-lg border border-gray-100 hover:shadow-xl hover:border-blue-300 transition-all duration-300 transform hover:-translate-y-1">
                    <div class="flex items-center space-x-4 mb-4">
                        <div class="p-3 rounded-lg bg-purple-100 text-purple-600">
                            <i class="fas fa-id-card"></i>
                        </div>
                        <h2 class="text-xl font-bold text-gray-800">Ringkasan Member</h2>
                    </div>
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-500">Total Member:</span>
                            <span id="total-member-placeholder" class="font-semibold text-gray-700 loading-placeholder">
                                <i class="fas fa-spinner fa-spin"></i> Loading...
                            </span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-500">Member Aktif:</span>
                            <span id="active-member-placeholder"
                                class="font-semibold text-gray-700 loading-placeholder">
                                <i class="fas fa-spinner fa-spin"></i> Loading...
                            </span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-500">Member Non-Aktif:</span>
                            <span id="non-active-member-placeholder"
                                class="font-semibold text-gray-700 loading-placeholder">
                                <i class="fas fa-spinner fa-spin"></i> Loading...
                            </span>
                        </div>
                    </div>
                </a>
                <a href="product_favorite"
                    class="block bg-white p-6 rounded-2xl shadow-lg border border-gray-100 hover:shadow-xl hover:border-blue-300 transition-all duration-300 transform hover:-translate-y-1">
                    <div class="flex items-center space-x-4 mb-4">
                        <div class="p-3 rounded-lg bg-blue-100 text-blue-600">
                            <i class="fas fa-heart"></i>
                        </div>
                        <h2 class="text-xl font-bold text-gray-800">Produk Favorit Member</h2>
                    </div>
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-500">Tren Penjualan Bulanan:</span>
                            <span id="tren-penjualan-placeholder"
                                class="font-semibold text-gray-700 loading-placeholder">
                                <i class="fas fa-spinner fa-spin"></i> Loading...
                            </span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-500">Produk Terlaris (Performa):</span>
                            <span id="produk-terlaris-placeholder"
                                class="font-semibold text-gray-700 loading-placeholder">
                                <i class="fas fa-spinner fa-spin"></i> Loading...
                            </span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-500">Daftar Produk Favorit:</span>
                            <span id="daftar-favorit-placeholder"
                                class="font-semibold text-gray-700 loading-placeholder">
                                <i class="fas fa-spinner fa-spin"></i> Loading...
                            </span>
                        </div>
                    </div>
                </a>

                <a href="../transaction/detail_transaksi_cabang?cabang=all"
                    class="block bg-white p-6 rounded-2xl shadow-lg border border-gray-100 hover:shadow-xl hover:border-blue-300 transition-all duration-300 transform hover:-translate-y-1">
                    <div class="flex items-center space-x-4 mb-4">
                        <div class="p-3 rounded-lg bg-green-100 text-green-600">
                            <i class="fas fa-star"></i>
                        </div>
                        <h2 class="text-xl font-bold text-gray-800">Produk Terlaris (Semua Cabang)</h2>
                    </div>
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-500">Top Barang Member:</span>
                            <span id="top-barang-member-placeholder"
                                class="font-semibold text-gray-700 truncate loading-placeholder">
                                <i class="fas fa-spinner fa-spin"></i> Loading...
                            </span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-500">Top Barang Non-Member</span>
                            <span id="top-barang-non-member-placeholder"
                                class="font-semibold text-gray-700 truncate loading-placeholder">
                                <i class="fas fa-spinner fa-spin"></i> Loading...
                            </span>
                        </div>
                    </div>
                </a>

            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="../../js/ui/navbar_toogle.js" type="module"></script>
    <script src="../../js/member/dashboard_handler.js" type="module"></script>

</body>

</html>