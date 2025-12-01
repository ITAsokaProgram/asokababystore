<?php
include '../../../aa_kon_sett.php';

require_once __DIR__ . '/../../component/menu_handler.php';

$menuHandler = new MenuHandler('user_management');

if (!$menuHandler->initialize()) {
    exit();
}

$user_id = $menuHandler->getUserId();
$logger = $menuHandler->getLogger();
$token = $menuHandler->getToken();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Pengguna</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/all.min.css">

    <link rel="stylesheet" href="../../style/header.css">
    <link rel="stylesheet" href="../../style/sidebar.css">
    <link rel="stylesheet" href="../../style/animation-fade-in.css">
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="../../style/default-font.css">
    <link rel="stylesheet" href="../../output2.css">
    <link rel="icon" type="image/png" href="../../../public/images/logo1.png">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <style>
        .btn.active {
            background-color: transparent;
            color: #ec4899;
            outline: 2px solid #ec4899;
            outline-offset: 1px;
        }

        /* Custom animation for fade-in-up */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fade-in-up {
            animation: fadeInUp 0.6s ease-out;
        }
    </style>
</head>

<body class="bg-gray-50 overflow-auto">
    <?php include '../../component/navigation_report.php' ?>;
    <?php include '../../component/sidebar_report.php' ?>;

    <main id="main-content" class="flex-1 p-6 ml-64">
        <div class="min-h-screen bg-gradient-to-br from-pink-50 via-white to-rose-50 p-6">
            <div class="max-w-7xl mx-auto">
                <div>
                    <div
                        class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-xl border border-white/20 p-4 sm:p-6 lg:p-8 animate-fade-in-up hover:-translate-y-1 hover:shadow-2xl transition-all duration-300">

                        <div class="flex items-center space-x-4 mb-6">
                            <div class="relative flex-shrink-0">
                                <div
                                    class="w-12 h-12 bg-gradient-to-r from-pink-400 to-rose-400 rounded-xl flex items-center justify-center shadow-lg">
                                    <i class="fas fa-users text-white text-xl"></i>
                                </div>
                                <div
                                    class="absolute -top-1 -right-1 w-4 h-4 bg-green-400 rounded-full border-2 border-white animate-pulse">
                                </div>
                            </div>
                            <div class="min-w-0 flex-1">
                                <h2 class="text-xl sm:text-2xl font-bold text-gray-800 truncate">Kelola Account Karyawan
                                </h2>
                                <p class="text-gray-600 text-sm">Manajemen pengguna dan akses sistem</p>
                                <div class="flex items-center space-x-2 mt-1">
                                    <div class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></div>
                                    <span class="text-xs text-green-600 font-medium">Sistem Aktif</span>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-3 gap-2 sm:gap-4 mb-6">
                            <div
                                class="bg-gradient-to-r from-blue-500 to-blue-600 text-white px-2 sm:px-4 py-3 rounded-lg text-center hover:-translate-y-1 hover:shadow-lg transition-all duration-300">
                                <div class="text-lg sm:text-xl font-bold" id="totalUsers"></div>
                                <div class="text-xs opacity-90">Total Users</div>
                            </div>
                            <div
                                class="bg-gradient-to-r from-green-500 to-green-600 text-white px-2 sm:px-4 py-3 rounded-lg text-center hover:-translate-y-1 hover:shadow-lg transition-all duration-300">
                                <div class="text-lg sm:text-xl font-bold" id="activeUsers"></div>
                                <div class="text-xs opacity-90">Active</div>
                            </div>
                            <div
                                class="bg-gradient-to-r from-purple-500 to-purple-600 text-white px-2 sm:px-4 py-3 rounded-lg text-center hover:-translate-y-1 hover:shadow-lg transition-all duration-300">
                                <div class="text-lg sm:text-xl font-bold" id="managers"></div>
                                <div class="text-xs opacity-90">Managers</div>
                            </div>
                        </div>

                        <div class="flex flex-col sm:flex-row gap-3">
                            <div class="relative group flex-1">
                                <input type="text" placeholder="Cari berdasarkan nama, ID, atau posisi..."
                                    id="searchInput"
                                    class="w-full pl-12 pr-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-pink-500 focus:border-pink-500 text-sm bg-white/90 backdrop-blur-sm shadow-sm transition-all duration-300 group-hover:shadow-md">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                    <i
                                        class="fas fa-search text-gray-400 group-hover:text-pink-500 transition-colors duration-200"></i>
                                </div>
                            </div>
                            <button
                                class="px-6 py-3 bg-gradient-to-r from-gray-500 to-gray-600 text-white rounded-xl hover:from-gray-600 hover:to-gray-700 text-sm font-medium transition-all duration-200 shadow-sm hover:shadow-md transform hover:scale-105 sm:flex-shrink-0">
                                <i class="fas fa-undo mr-2"></i>Reset
                            </button>
                        </div>
                    </div>
                </div>

                <div
                    class="bg-white/90 mt-4 backdrop-blur-sm rounded-2xl shadow-2xl border border-white/30 overflow-hidden animate-fade-in-up hover:-translate-y-1 hover:shadow-2xl transition-all duration-300">
                    <div class="bg-gradient-to-r from-pink-400 via-rose-400 to-pink-500 p-6">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-white">Daftar Pengguna</h3>
                            <div class="flex items-center space-x-2">
                                <div class="w-2 h-2 bg-white/80 rounded-full animate-pulse"></div>
                                <span class="text-white/90 text-sm">View Data</span>
                            </div>
                        </div>
                    </div>

                    <div class="max-h-[65vh] overflow-x-auto">
                        <table class="w-full table-auto text-sm text-left">
                            <thead class="bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
                                <tr>
                                    <th
                                        class="px-6 py-4 text-left font-semibold text-gray-700 text-xs uppercase tracking-wider">
                                        No</th>
                                    <th
                                        class="px-6 py-4 text-left font-semibold text-gray-700 text-xs uppercase tracking-wider">
                                        Nama Pengguna</th>
                                    <th
                                        class="px-6 py-4 text-left font-semibold text-gray-700 text-xs uppercase tracking-wider">
                                        Posisi</th>
                                    <th
                                        class="px-6 py-4 text-left font-semibold text-gray-700 text-xs uppercase tracking-wider">
                                        Akses Menu</th>
                                    <th
                                        class="px-6 py-4 text-left font-semibold text-gray-700 text-xs uppercase tracking-wider">
                                        Cabang</th>
                                    <th
                                        class="px-6 py-4 text-left font-semibold text-gray-700 text-xs uppercase tracking-wider">
                                        Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="userTableBody" class="text-gray-700">
                            </tbody>
                        </table>
                    </div>

                    <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-t border-gray-200">
                        <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
                            <div class="flex items-center space-x-2">
                                <i class="fas fa-info-circle text-gray-400"></i>
                                <p class="text-gray-600 text-sm font-medium" id="viewData"></p>
                            </div>
                            <div class="flex flex-wrap gap-2 justify-center sm:justify-end" id="paginationContainer">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <div id="editUserModal"
        class="fixed flex inset-0 z-50 bg-black/60 backdrop-blur-sm hidden items-center justify-center p-4">
        <div
            class="bg-white/95 backdrop-blur-md rounded-2xl shadow-2xl w-full max-w-4xl relative animate-fade-in-up hover:-translate-y-1 hover:shadow-2xl transition-all duration-300 border border-white/20 max-h-[90vh] overflow-hidden">
            <div class="bg-gradient-to-r from-pink-400 via-rose-400 to-pink-500 rounded-t-2xl p-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center">
                            <i class="fas fa-user-edit text-white text-lg"></i>
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-white">Edit Pengguna</h2>
                            <p class="text-white/80 text-sm">Update informasi pengguna</p>
                        </div>
                    </div>
                    <button class="text-white/80 hover:text-white transition-colors duration-200" id="closeEditModal">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
            </div>

            <form id="editUserForm" class="p-6 space-y-6 overflow-y-auto max-h-[calc(90vh-200px)]">
                <input type="hidden" name="id_user" id="editUserId">

                <div class="bg-gradient-to-r from-gray-50 to-gray-100 rounded-xl p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-user-circle mr-2 text-pink-500"></i>
                        Informasi Pengguna
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label for="editNama" class="block text-sm font-semibold text-gray-700 flex items-center">
                                <i class="fas fa-user mr-2 text-pink-500"></i>
                                Nama Lengkap
                            </label>
                            <input type="text" id="editNama" name="name" required
                                class="block w-full rounded-xl border border-gray-300 px-4 py-3 shadow-sm focus:ring-2 focus:ring-pink-500 focus:border-pink-500 text-sm transition-all duration-200 hover:border-pink-300">
                        </div>

                        <div class="space-y-2">
                            <label for="editUsername"
                                class="block text-sm font-semibold text-gray-700 flex items-center">
                                <i class="fas fa-at mr-2 text-pink-500"></i>
                                Username
                            </label>
                            <input type="text" id="editUsername" name="username" required
                                class="block w-full rounded-xl border border-gray-300 px-4 py-3 shadow-sm focus:ring-2 focus:ring-pink-500 focus:border-pink-500 text-sm transition-all duration-200 hover:border-pink-300">
                        </div>

                        <div class="space-y-2">
                            <label for="editPosition"
                                class="block text-sm font-semibold text-gray-700 flex items-center">
                                <i class="fas fa-briefcase mr-2 text-pink-500"></i>
                                Posisi
                            </label>
                            <select id="editPosition" name="position" required
                                class="block w-full rounded-xl border border-gray-300 px-4 py-3 shadow-sm focus:ring-2 focus:ring-pink-500 focus:border-pink-500 text-sm transition-all duration-200 hover:border-pink-300">
                                <option value="Manajer">👨‍💼 Manajer</option>
                                <option value="IT">💻 IT</option>
                                <option value="Admin">⚙️ Admin</option>
                                <option value="Superadmin">⚙️ Superadmin</option>
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label for="editCabang" class="block text-sm font-semibold text-gray-700 flex items-center">
                                <i class="fas fa-building mr-2 text-pink-500"></i>
                                Cabang
                            </label>
                            <select id="editCabang" name="cabang" required
                                class="block w-full rounded-xl border border-gray-300 px-4 py-3 shadow-sm focus:ring-2 focus:ring-pink-500 focus:border-pink-500 text-sm transition-all duration-200 hover:border-pink-300">
                            </select>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-shield-alt mr-2 text-blue-500"></i>
                        Akses Menu & Permissions
                    </h3>

                    <div class="flex flex-wrap gap-2 mb-4">
                        <button type="button"
                            class="quick-action px-3 py-1.5 bg-blue-100 text-blue-700 rounded-lg text-xs hover:bg-blue-200 transition-colors duration-200 font-medium"
                            data-action="select-all">
                            <i class="fas fa-check mr-1"></i>Pilih Semua
                        </button>
                        <button type="button"
                            class="quick-action px-3 py-1.5 bg-red-100 text-red-700 rounded-lg text-xs hover:bg-red-200 transition-colors duration-200 font-medium"
                            data-action="clear-all">
                            <i class="fas fa-times mr-1"></i>Hapus Semua
                        </button>
                    </div>

                    <div id="editMenus" class="grid grid-cols-1 lg:grid-cols-2 gap-4 text-sm text-gray-700">

                        <div
                            class="bg-white rounded-xl p-4 shadow-sm border border-blue-100 hover:shadow-md transition-shadow duration-200">
                            <div class="flex items-center mb-3">
                                <i class="fas fa-chart-line text-blue-500 mr-2"></i>
                                <p class="font-semibold text-gray-800">📊 Dashboard</p>
                            </div>
                            <div class="space-y-2">
                                <label
                                    class="flex items-center gap-3 p-2 rounded-lg hover:bg-blue-50 transition-colors duration-200">
                                    <input type="checkbox" name="menus[]" value="dashboard"
                                        class="w-4 h-4 text-blue-600 rounded focus:ring-blue-500" />
                                    <span class="text-sm">Dashboard Overview</span>
                                </label>
                            </div>
                        </div>

                        <div
                            class="bg-white rounded-xl p-4 shadow-sm border border-orange-100 hover:shadow-md transition-shadow duration-200">
                            <div class="flex items-center mb-3">
                                <i class="fas fa-shopping-bag text-orange-500 mr-2"></i>
                                <p class="font-semibold text-gray-800">🛍️ Integrasi (Shopee/WA)</p>
                            </div>
                            <div class="space-y-2">
                                <label
                                    class="flex items-center gap-3 p-2 rounded-lg hover:bg-orange-50 transition-colors duration-200">
                                    <input type="checkbox" name="menus[]" value="shopee_dashboard"
                                        class="w-4 h-4 text-orange-600 rounded focus:ring-orange-500" />
                                    <span class="text-sm">Dashboard Shopee</span>
                                </label>
                                <label
                                    class="flex items-center gap-3 p-2 rounded-lg hover:bg-orange-50 transition-colors duration-200">
                                    <input type="checkbox" name="menus[]" value="shopee_produk"
                                        class="w-4 h-4 text-orange-600 rounded focus:ring-orange-500" />
                                    <span class="text-sm">Produk Shopee</span>
                                </label>
                                <label
                                    class="flex items-center gap-3 p-2 rounded-lg hover:bg-orange-50 transition-colors duration-200">
                                    <input type="checkbox" name="menus[]" value="shopee_order"
                                        class="w-4 h-4 text-orange-600 rounded focus:ring-orange-500" />
                                    <span class="text-sm">Order Shopee</span>
                                </label>
                                <label
                                    class="flex items-center gap-3 p-2 rounded-lg hover:bg-orange-50 transition-colors duration-200">
                                    <input type="checkbox" name="menus[]" value="shopee_terima_barang"
                                        class="w-4 h-4 text-orange-600 rounded focus:ring-orange-500" />
                                    <span class="text-sm">Terima Barang Shopee</span>
                                </label>
                                <label
                                    class="flex items-center gap-3 p-2 rounded-lg hover:bg-orange-50 transition-colors duration-200">
                                    <input type="checkbox" name="menus[]" value="whatsapp_dashboard"
                                        class="w-4 h-4 text-green-600 rounded focus:ring-green-500" />
                                    <span class="text-sm">Dashboard Whatsapp</span>
                                </label>
                            </div>
                        </div>

                        <div
                            class="bg-white rounded-xl p-4 shadow-sm border border-purple-200 hover:shadow-md transition-shadow duration-200">
                            <div class="flex items-center mb-3">
                                <i class="fas fa-file-invoice-dollar text-purple-500 mr-2"></i>
                                <p class="font-semibold text-gray-800">📜 Pajak (Coretax)</p>
                            </div>
                            <div class="space-y-2">
                                <label
                                    class="flex items-center gap-3 p-2 rounded-lg hover:bg-purple-50 transition-colors duration-200">
                                    <input type="checkbox" name="menus[]" value="pajak_input_pembelian"
                                        class="w-4 h-4 text-purple-600 rounded focus:ring-purple-500" />
                                    <span class="text-sm">Form Pembelian</span>
                                </label>
                                <label
                                    class="flex items-center gap-3 p-2 rounded-lg hover:bg-purple-50 transition-colors duration-200">
                                    <input type="checkbox" name="menus[]" value="pajak_laporan_pembelian"
                                        class="w-4 h-4 text-purple-600 rounded focus:ring-purple-500" />
                                    <span class="text-sm">Laporan Pembelian</span>
                                </label>
                                <label
                                    class="flex items-center gap-3 p-2 rounded-lg hover:bg-purple-50 transition-colors duration-200">
                                    <input type="checkbox" name="menus[]" value="pajak_input_faktur"
                                        class="w-4 h-4 text-purple-600 rounded focus:ring-purple-500" />
                                    <span class="text-sm">Form Faktur</span>
                                </label>
                                <label
                                    class="flex items-center gap-3 p-2 rounded-lg hover:bg-purple-50 transition-colors duration-200">
                                    <input type="checkbox" name="menus[]" value="pajak_laporan_faktur"
                                        class="w-4 h-4 text-purple-600 rounded focus:ring-purple-500" />
                                    <span class="text-sm">Laporan Faktur</span>
                                </label>
                                <label
                                    class="flex items-center gap-3 p-2 rounded-lg hover:bg-purple-50 transition-colors duration-200">
                                    <input type="checkbox" name="menus[]" value="pajak_import"
                                        class="w-4 h-4 text-purple-600 rounded focus:ring-purple-500" />
                                    <span class="text-sm">Import Masukan</span>
                                </label>
                                <label
                                    class="flex items-center gap-3 p-2 rounded-lg hover:bg-purple-50 transition-colors duration-200">
                                    <input type="checkbox" name="menus[]" value="pajak_data"
                                        class="w-4 h-4 text-purple-600 rounded focus:ring-purple-500" />
                                    <span class="text-sm">Laporan Masukan</span>
                                </label>
                                <label
                                    class="flex items-center gap-3 p-2 rounded-lg hover:bg-purple-50 transition-colors duration-200">
                                    <input type="checkbox" name="menus[]" value="pajak_faktur_masukan"
                                        class="w-4 h-4 text-purple-600 rounded focus:ring-purple-500" />
                                    <span class="text-sm">Laporan Penerimaan</span>
                                </label>
                            </div>
                        </div>

                        <div
                            class="bg-white rounded-xl p-4 shadow-sm border border-green-100 hover:shadow-md transition-shadow duration-200">
                            <div class="flex items-center mb-3">
                                <i class="fas fa-chart-bar text-green-500 mr-2"></i>
                                <p class="font-semibold text-gray-800">📈 Laporan Penjualan</p>
                            </div>
                            <div class="space-y-2">
                                <label
                                    class="flex items-center gap-3 p-2 rounded-lg hover:bg-green-50 transition-colors duration-200">
                                    <input type="checkbox" name="menus[]" value="laporan_penjualan_subdept"
                                        class="w-4 h-4 text-green-600 rounded focus:ring-green-500" />
                                    <span class="text-sm">📊 Subdept Analysis</span>
                                </label>
                                <label
                                    class="flex items-center gap-3 p-2 rounded-lg hover:bg-green-50 transition-colors duration-200">
                                    <input type="checkbox" name="menus[]" value="laporan_penjualan_salesratio"
                                        class="w-4 h-4 text-green-600 rounded focus:ring-green-500" />
                                    <span class="text-sm">📈 Sales Ratio</span>
                                </label>
                                <label
                                    class="flex items-center gap-3 p-2 rounded-lg hover:bg-green-50 transition-colors duration-200">
                                    <input type="checkbox" name="menus[]" value="laporan_penjualan_kategori"
                                        class="w-4 h-4 text-green-600 rounded focus:ring-green-500" />
                                    <span class="text-sm">🏷️ Kategori Report</span>
                                </label>
                                <label
                                    class="flex items-center gap-3 p-2 rounded-lg hover:bg-green-50 transition-colors duration-200">
                                    <input type="checkbox" name="menus[]" value="laporan_penjualan_mnonm"
                                        class="w-4 h-4 text-green-600 rounded focus:ring-green-500" />
                                    <span class="text-sm">⚖️ M / Non M</span>
                                </label>
                            </div>
                        </div>

                        <div
                            class="bg-white rounded-xl p-4 shadow-sm border border-yellow-100 hover:shadow-md transition-shadow duration-200">
                            <div class="flex items-center mb-3">
                                <i class="fas fa-trophy text-yellow-500 mr-2"></i>
                                <p class="font-semibold text-gray-800">🏆 Top Sales</p>
                            </div>
                            <div class="space-y-2">
                                <label
                                    class="flex items-center gap-3 p-2 rounded-lg hover:bg-yellow-50 transition-colors duration-200">
                                    <input type="checkbox" name="menus[]" value="laporan_topsales_rupiah"
                                        class="w-4 h-4 text-yellow-600 rounded focus:ring-yellow-500" />
                                    <span class="text-sm">Top Sales (Rp)</span>
                                </label>
                                <label
                                    class="flex items-center gap-3 p-2 rounded-lg hover:bg-yellow-50 transition-colors duration-200">
                                    <input type="checkbox" name="menus[]" value="laporan_topsales_qty"
                                        class="w-4 h-4 text-yellow-600 rounded focus:ring-yellow-500" />
                                    <span class="text-sm">Top Sales (Qty)</span>
                                </label>
                                <label
                                    class="flex items-center gap-3 p-2 rounded-lg hover:bg-yellow-50 transition-colors duration-200">
                                    <input type="checkbox" name="menus[]" value="laporan_topsales_supplier"
                                        class="w-4 h-4 text-yellow-600 rounded focus:ring-yellow-500" />
                                    <span class="text-sm">Top Sales (Supplier)</span>
                                </label>
                                <label
                                    class="flex items-center gap-3 p-2 rounded-lg hover:bg-yellow-50 transition-colors duration-200">
                                    <input type="checkbox" name="menus[]" value="laporan_topsales_kasir"
                                        class="w-4 h-4 text-yellow-600 rounded focus:ring-yellow-500" />
                                    <span class="text-sm">Sales per Kasir</span>
                                </label>
                            </div>
                        </div>

                        <div
                            class="bg-white rounded-xl p-4 shadow-sm border border-purple-100 hover:shadow-md transition-shadow duration-200">
                            <div class="flex items-center mb-3">
                                <i class="fas fa-users text-purple-500 mr-2"></i>
                                <p class="font-semibold text-gray-800">👥 Laporan Pelanggan</p>
                            </div>
                            <div class="space-y-2">
                                <label
                                    class="flex items-center gap-3 p-2 rounded-lg hover:bg-purple-50 transition-colors duration-200">
                                    <input type="checkbox" name="menus[]" value="laporan_pelanggan_aktifitas"
                                        class="w-4 h-4 text-purple-600 rounded focus:ring-purple-500" />
                                    <span class="text-sm">🛒 Aktivitas Belanja</span>
                                </label>
                                <label
                                    class="flex items-center gap-3 p-2 rounded-lg hover:bg-purple-50 transition-colors duration-200">
                                    <input type="checkbox" name="menus[]" value="laporan_pelanggan_layanan"
                                        class="w-4 h-4 text-purple-600 rounded focus:ring-purple-500" />
                                    <span class="text-sm">🎯 Layanan</span>
                                </label>
                                <label
                                    class="flex items-center gap-3 p-2 rounded-lg hover:bg-purple-50 transition-colors duration-200">
                                    <input type="checkbox" name="menus[]" value="laporan_pelanggan_review"
                                        class="w-4 h-4 text-purple-600 rounded focus:ring-purple-500" />
                                    <span class="text-sm">⭐ Review</span>
                                </label>
                            </div>
                        </div>

                        <div
                            class="bg-white rounded-xl p-4 shadow-sm border border-orange-100 hover:shadow-md transition-shadow duration-200">
                            <div class="flex items-center mb-3">
                                <i class="fas fa-exchange-alt text-orange-500 mr-2"></i>
                                <p class="font-semibold text-gray-800">💳 Transaksi</p>
                            </div>
                            <div class="space-y-2">
                                <label
                                    class="flex items-center gap-3 p-2 rounded-lg hover:bg-orange-50 transition-colors duration-200">
                                    <input type="checkbox" name="menus[]" value="transaksi_promo"
                                        class="w-4 h-4 text-orange-600 rounded focus:ring-orange-500" />
                                    <span class="text-sm">🎁 Promo</span>
                                </label>
                                <label
                                    class="flex items-center gap-3 p-2 rounded-lg hover:bg-orange-50 transition-colors duration-200">
                                    <input type="checkbox" name="menus[]" value="reward_give"
                                        class="w-4 h-4 text-orange-600 rounded focus:ring-orange-500" />
                                    <span class="text-sm">🎁 Hadiah</span>
                                </label>
                                <label
                                    class="flex items-center gap-3 p-2 rounded-lg hover:bg-orange-50 transition-colors duration-200">
                                    <input type="checkbox" name="menus[]" value="transaksi_invalid"
                                        class="w-4 h-4 text-orange-600 rounded focus:ring-orange-500" />
                                    <span class="text-sm">❌ Invalid</span>
                                </label>
                                <label
                                    class="flex items-center gap-3 p-2 rounded-lg hover:bg-orange-50 transition-colors duration-200">
                                    <input type="checkbox" name="menus[]" value="top_invalid"
                                        class="w-4 h-4 text-orange-600 rounded focus:ring-orange-500" />
                                    <span class="text-sm">Top Invalid (Manager)</span>
                                </label>
                                <label
                                    class="flex items-center gap-3 p-2 rounded-lg hover:bg-orange-50 transition-colors duration-200">
                                    <input type="checkbox" name="menus[]" value="top_retur"
                                        class="w-4 h-4 text-orange-600 rounded focus:ring-orange-500" />
                                    <span class="text-sm">Top Retur (Manager)</span>
                                </label>
                                <label
                                    class="flex items-center gap-3 p-2 rounded-lg hover:bg-orange-50 transition-colors duration-200">
                                    <input type="checkbox" name="menus[]" value="top_margin"
                                        class="w-4 h-4 text-orange-600 rounded focus:ring-orange-500" />
                                    <span class="text-sm">Top Margin (Manager)</span>
                                </label>
                                <label
                                    class="flex items-center gap-3 p-2 rounded-lg hover:bg-orange-50 transition-colors duration-200">
                                    <input type="checkbox" name="menus[]" value="transaksi_margin"
                                        class="w-4 h-4 text-orange-600 rounded focus:ring-orange-500" />
                                    <span class="text-sm">💰 Margin</span>
                                </label>
                                <label
                                    class="flex items-center gap-3 p-2 rounded-lg hover:bg-orange-50 transition-colors duration-200">
                                    <input type="checkbox" name="menus[]" value="transaksi_cabang"
                                        class="w-4 h-4 text-orange-600 rounded focus:ring-orange-500" />
                                    <span class="text-sm">💰 Transaksi Cabang (Manager)</span>
                                </label>
                                <label
                                    class="flex items-center gap-3 p-2 rounded-lg hover:bg-orange-50 transition-colors duration-200">
                                    <input type="checkbox" name="menus[]" value="detail_transaksi_cabang"
                                        class="w-4 h-4 text-orange-600 rounded focus:ring-orange-500" />
                                    <span class="text-sm">💰 Detail Transaksi Cabang (Manager)</span>
                                </label>
                            </div>
                        </div>

                        <div
                            class="bg-white rounded-xl p-4 shadow-sm border border-indigo-100 hover:shadow-md transition-shadow duration-200">
                            <div class="flex items-center mb-3">
                                <i class="fas fa-boxes text-indigo-500 mr-2"></i>
                                <p class="font-semibold text-gray-800">📦 Mutasi & Koreksi Stok</p>
                            </div>
                            <div class="space-y-2">
                                <label
                                    class="flex items-center gap-3 p-2 rounded-lg hover:bg-indigo-50 transition-colors duration-200">
                                    <input type="checkbox" name="menus[]" value="laporan_mutasi_in"
                                        class="w-4 h-4 text-indigo-600 rounded focus:ring-indigo-500" />
                                    <span class="text-sm">Mutasi Invoice</span>
                                </label>
                                <label
                                    class="flex items-center gap-3 p-2 rounded-lg hover:bg-indigo-50 transition-colors duration-200">
                                    <input type="checkbox" name="menus[]" value="laporan_koreksi_supplier"
                                        class="w-4 h-4 text-indigo-600 rounded focus:ring-indigo-500" />
                                    <span class="text-sm">Koreksi (Supplier)</span>
                                </label>
                                <label
                                    class="flex items-center gap-3 p-2 rounded-lg hover:bg-indigo-50 transition-colors duration-200">
                                    <input type="checkbox" name="menus[]" value="laporan_koreksi_plu"
                                        class="w-4 h-4 text-indigo-600 rounded focus:ring-indigo-500" />
                                    <span class="text-sm">Koreksi (PLU)</span>
                                </label>
                                <label
                                    class="flex items-center gap-3 p-2 rounded-lg hover:bg-indigo-50 transition-colors duration-200">
                                    <input type="checkbox" name="menus[]" value="koreksi_so"
                                        class="w-4 h-4 text-indigo-600 rounded focus:ring-indigo-500" />
                                    <span class="text-sm">Koreksi SO</span>
                                </label>
                                <label
                                    class="flex items-center gap-3 p-2 rounded-lg hover:bg-indigo-50 transition-colors duration-200">
                                    <input type="checkbox" name="menus[]" value="koreksi_so_missed"
                                        class="w-4 h-4 text-indigo-600 rounded focus:ring-indigo-500" />
                                    <span class="text-sm">Belum Koreksi SO</span>
                                </label>
                                <label
                                    class="flex items-center gap-3 p-2 rounded-lg hover:bg-indigo-50 transition-colors duration-200">
                                    <input type="checkbox" name="menus[]" value="izin"
                                        class="w-4 h-4 text-indigo-600 rounded focus:ring-indigo-500" />
                                    <span class="text-sm">Approval Koreksi</span>
                                </label>
                            </div>
                        </div>

                        <div
                            class="bg-white rounded-xl p-4 shadow-sm border border-indigo-200 hover:shadow-md transition-shadow duration-200">
                            <div class="flex items-center mb-3">
                                <i class="fas fa-calendar-alt text-indigo-500 mr-2"></i>
                                <p class="font-semibold text-gray-800">📅 Jadwal SO</p>
                            </div>
                            <div class="space-y-2">
                                <label
                                    class="flex items-center gap-3 p-2 rounded-lg hover:bg-indigo-50 transition-colors duration-200">
                                    <input type="checkbox" name="menus[]" value="laporan_jadwal_so"
                                        class="w-4 h-4 text-indigo-600 rounded focus:ring-indigo-500" />
                                    <span class="text-sm">Jadwal SO</span>
                                </label>
                                <label
                                    class="flex items-center gap-3 p-2 rounded-lg hover:bg-indigo-50 transition-colors duration-200">
                                    <input type="checkbox" name="menus[]" value="laporan_jadwal_so_create"
                                        class="w-4 h-4 text-indigo-600 rounded focus:ring-indigo-500" />
                                    <span class="text-sm">Buat Jadwal SO</span>
                                </label>
                            </div>
                        </div>

                        <div
                            class="bg-white rounded-xl p-4 shadow-sm border border-red-100 hover:shadow-md transition-shadow duration-200">
                            <div class="flex items-center mb-3">
                                <i class="fas fa-undo text-red-500 mr-2"></i>
                                <p class="font-semibold text-gray-800">🔙 Retur Keluar</p>
                            </div>
                            <div class="space-y-2">
                                <label
                                    class="flex items-center gap-3 p-2 rounded-lg hover:bg-red-50 transition-colors duration-200">
                                    <input type="checkbox" name="menus[]" value="laporan_return_all"
                                        class="w-4 h-4 text-red-600 rounded focus:ring-red-500" />
                                    <span class="text-sm">Return All Item</span>
                                </label>
                                <label
                                    class="flex items-center gap-3 p-2 rounded-lg hover:bg-red-50 transition-colors duration-200">
                                    <input type="checkbox" name="menus[]" value="laporan_return_badstock"
                                        class="w-4 h-4 text-red-600 rounded focus:ring-red-500" />
                                    <span class="text-sm">Return Bad Stock</span>
                                </label>
                                <label
                                    class="flex items-center gap-3 p-2 rounded-lg hover:bg-red-50 transition-colors duration-200">
                                    <input type="checkbox" name="menus[]" value="laporan_return_exp"
                                        class="w-4 h-4 text-red-600 rounded focus:ring-red-500" />
                                    <span class="text-sm">Return Expired</span>
                                </label>
                                <label
                                    class="flex items-center gap-3 p-2 rounded-lg hover:bg-red-50 transition-colors duration-200">
                                    <input type="checkbox" name="menus[]" value="laporan_return_hilang"
                                        class="w-4 h-4 text-red-600 rounded focus:ring-red-500" />
                                    <span class="text-sm">Return Hilang Pasangan</span>
                                </label>
                            </div>
                        </div>

                        <div
                            class="bg-white rounded-xl p-4 shadow-sm border border-teal-100 hover:shadow-md transition-shadow duration-200">
                            <div class="flex items-center mb-3">
                                <i class="fas fa-truck-loading text-teal-500 mr-2"></i>
                                <p class="font-semibold text-gray-800">🚛 Penerimaan</p>
                            </div>
                            <div class="space-y-2">
                                <label
                                    class="flex items-center gap-3 p-2 rounded-lg hover:bg-teal-50 transition-colors duration-200">
                                    <input type="checkbox" name="menus[]" value="laporan_receipt_detail"
                                        class="w-4 h-4 text-teal-600 rounded focus:ring-teal-500" />
                                    <span class="text-sm">Detail Receipt</span>
                                </label>
                                <label
                                    class="flex items-center gap-3 p-2 rounded-lg hover:bg-teal-50 transition-colors duration-200">
                                    <input type="checkbox" name="menus[]" value="laporan_receipt_supplier"
                                        class="w-4 h-4 text-teal-600 rounded focus:ring-teal-500" />
                                    <span class="text-sm">Receipt by Supplier</span>
                                </label>
                            </div>
                        </div>

                        <div
                            class="bg-white rounded-xl p-4 shadow-sm border border-cyan-100 hover:shadow-md transition-shadow duration-200">
                            <div class="flex items-center mb-3">
                                <i class="fas fa-ticket-alt text-cyan-500 mr-2"></i>
                                <p class="font-semibold text-gray-800">🎟️ Voucher</p>
                            </div>
                            <div class="space-y-2">
                                <label
                                    class="flex items-center gap-3 p-2 rounded-lg hover:bg-cyan-50 transition-colors duration-200">
                                    <input type="checkbox" name="menus[]" value="voucher_index"
                                        class="w-4 h-4 text-cyan-600 rounded focus:ring-cyan-500" />
                                    <span class="text-sm">Data Voucher</span>
                                </label>
                                <label
                                    class="flex items-center gap-3 p-2 rounded-lg hover:bg-cyan-50 transition-colors duration-200">
                                    <input type="checkbox" name="menus[]" value="voucher_create"
                                        class="w-4 h-4 text-cyan-600 rounded focus:ring-cyan-500" />
                                    <span class="text-sm">Buat Voucher</span>
                                </label>
                            </div>
                        </div>

                        <div
                            class="bg-white rounded-xl p-4 shadow-sm border border-gray-200 hover:shadow-md transition-shadow duration-200">
                            <div class="flex items-center mb-3">
                                <i class="fas fa-cogs text-gray-500 mr-2"></i>
                                <p class="font-semibold text-gray-800">⚙️ Manajemen User</p>
                            </div>
                            <div class="space-y-2">
                                <label
                                    class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-50 transition-colors duration-200">
                                    <input type="checkbox" name="menus[]" value="user_management"
                                        class="w-4 h-4 text-gray-600 rounded focus:ring-gray-500" />
                                    <span class="text-sm">👥 Manajemen User</span>
                                </label>
                                <label
                                    class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-50 transition-colors duration-200">
                                    <input type="checkbox" name="menus[]" value="insert_new_user"
                                        class="w-4 h-4 text-gray-600 rounded focus:ring-gray-500" />
                                    <span class="text-sm">➕ Tambah Anggota</span>
                                </label>
                            </div>
                        </div>

                        <div
                            class="bg-white rounded-xl p-4 shadow-sm border border-pink-100 hover:shadow-md transition-shadow duration-200">
                            <div class="flex items-center mb-3">
                                <i class="fas fa-star text-pink-500 mr-2"></i>
                                <p class="font-semibold text-gray-800">⭐ Member</p>
                            </div>
                            <div class="space-y-2">
                                <label
                                    class="flex items-center gap-3 p-2 rounded-lg hover:bg-pink-50 transition-colors duration-200">
                                    <input type="checkbox" name="menus[]" value="member_poin"
                                        class="w-4 h-4 text-pink-600 rounded focus:ring-pink-500" />
                                    <span class="text-sm">Poin</span>
                                </label>
                                <label
                                    class="flex items-center gap-3 p-2 rounded-lg hover:bg-pink-50 transition-colors duration-200">
                                    <input type="checkbox" name="menus[]" value="upload_banner"
                                        class="w-4 h-4 text-pink-600 rounded focus:ring-pink-500" />
                                    <span class="text-sm">Banner</span>
                                </label>
                                <label
                                    class="flex items-center gap-3 p-2 rounded-lg hover:bg-pink-50 transition-colors duration-200">
                                    <input type="checkbox" name="menus[]" value="product_favorite"
                                        class="w-4 h-4 text-pink-600 rounded focus:ring-pink-500" />
                                    <span class="text-sm">Produk Favorit</span>
                                </label>
                                <label
                                    class="flex items-center gap-3 p-2 rounded-lg hover:bg-pink-50 transition-colors duration-200">
                                    <input type="checkbox" name="menus[]" value="product_member"
                                        class="w-4 h-4 text-pink-600 rounded focus:ring-pink-500" />
                                    <span class="text-sm">Produk Member</span>
                                </label>
                                <label
                                    class="flex items-center gap-3 p-2 rounded-lg hover:bg-pink-50 transition-colors duration-200">
                                    <input type="checkbox" name="menus[]" value="top_sales"
                                        class="w-4 h-4 text-pink-600 rounded focus:ring-pink-500" />
                                    <span class="text-sm">Top Sales</span>
                                </label>
                            </div>
                        </div>

                        <div
                            class="bg-white rounded-xl p-4 shadow-sm border border-slate-200 hover:shadow-md transition-shadow duration-200">
                            <div class="flex items-center mb-3">
                                <p class="font-semibold text-gray-800">📌 Lainnya</p>
                            </div>
                            <div class="space-y-2">
                                <label
                                    class="flex items-center gap-3 p-2 rounded-lg hover:bg-slate-50 transition-colors duration-200">
                                    <input type="checkbox" name="menus[]" value="history_aset"
                                        class="w-4 h-4 text-slate-600 rounded focus:ring-slate-500" />
                                    <span class="text-sm">🖥️ Management Aset</span>
                                </label>
                                <label
                                    class="flex items-center gap-3 p-2 rounded-lg hover:bg-slate-50 transition-colors duration-200">
                                    <input type="checkbox" name="menus[]" value="laporan_log_backup"
                                        class="w-4 h-4 text-slate-600 rounded focus:ring-slate-500" />
                                    <span class="text-sm">💾 Log Backup</span>
                                </label>
                            </div>
                        </div>

                    </div>
                    <div class="flex flex-col sm:flex-row justify-between items-center gap-4 mt-2">
                        <div class="flex items-center space-x-2 text-sm text-gray-600">
                            <i class="fas fa-info-circle text-blue-500"></i>
                            <span>Pastikan semua data telah diisi dengan benar</span>
                        </div>
                        <div class="flex items-center space-x-3">
                            <button type="button" id="cancelEditUser"
                                class="px-5 py-2.5 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-all duration-200 text-sm font-medium flex items-center">
                                <i class="fas fa-times mr-2"></i>
                                Batal
                            </button>
                            <button type="submit" form="editUserForm"
                                class="px-5 py-2.5 bg-gradient-to-r from-pink-500 to-rose-500 text-white rounded-lg hover:from-pink-600 hover:to-rose-600 transition-all duration-200 text-sm font-medium flex items-center shadow-sm hover:shadow-md">
                                <i class="fas fa-save mr-2"></i>
                                Simpan
                            </button>
                        </div>
                    </div>

                </div>
        </div>

        </form>

    </div>

    <div id="resetPassword" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6 space-y-5">
            <h2 class="text-xl font-semibold text-gray-800">Reset Password Pengguna</h2>

            <div>
                <label for="newPassword" class="block text-sm font-medium text-gray-700">Password Baru</label>
                <input type="password" id="newPassword" placeholder="Masukkan password baru"
                    class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
            </div>

            <div>
                <label for="confirmPassword" class="block text-sm font-medium text-gray-700">Konfirmasi Password</label>
                <input type="password" id="confirmPassword" placeholder="Ulangi password baru"
                    class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
                <p class="text-red-500 font-mono italic text-xs hidden" id="error-password">Password tidak sama</p>
            </div>

            <div class="flex justify-end space-x-2 pt-3">
                <button id="close-reset"
                    class="px-4 py-2 text-sm font-medium text-gray-600 bg-gray-200 rounded hover:bg-gray-300">
                    Batal
                </button>
                <button id="save-password"
                    class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded hover:bg-blue-700">
                    Simpan Password
                </button>
            </div>
        </div>
    </div>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../../js/middleware_auth.js"></script>
    <script src="../../js/account/internal/main.js" type="module"></script>
    <script>
        document.getElementById('toggle-sidebar').addEventListener('click', function () {
            document.getElementById('sidebar').classList.toggle('open');
        });
        document.addEventListener("DOMContentLoaded", function () {
            const sidebar = document.getElementById("sidebar");
            const closeBtn = document.getElementById("closeSidebar");

            closeBtn.addEventListener("click", function () {
                sidebar.classList.remove("open"); // Hilangkan class .open agar sidebar tertutup
            });
        });
        document.getElementById("toggle-hide").addEventListener("click", function () {
            var sidebarTexts = document.querySelectorAll(".sidebar-text");
            let mainContent = document.getElementById("main-content");
            let sidebar = document.getElementById("sidebar");
            var toggleButton = document.getElementById("toggle-hide");
            var icon = toggleButton.querySelector("i");

            if (sidebar.classList.contains("w-64")) {
                // Sidebar mengecil
                sidebar.classList.remove("w-64", "px-5");
                sidebar.classList.add("w-16", "px-2");
                sidebarTexts.forEach(text => text.classList.add("hidden")); // Sembunyikan teks
                mainContent.classList.remove("ml-64");
                mainContent.classList.add("ml-16"); // Main ikut mundur
                toggleButton.classList.add("left-20"); // Geser tombol lebih dekat
                toggleButton.classList.remove("left-64");
                icon.classList.remove("fa-angle-left"); // Ubah ikon
                icon.classList.add("fa-angle-right");
            } else {
                // Sidebar membesar
                sidebar.classList.remove("w-16", "px-2");
                sidebar.classList.add("w-64", "px-5");
                sidebarTexts.forEach(text => text.classList.remove("hidden")); // Tampilkan teks kembali
                mainContent.classList.remove("ml-16");
                mainContent.classList.add("ml-64");
                toggleButton.classList.add("left-64"); // Geser tombol ke posisi awal
                toggleButton.classList.remove("left-20");
                icon.classList.remove("fa-angle-right"); // Ubah ikon
                icon.classList.add("fa-angle-left");
            }
        });

        document.addEventListener("DOMContentLoaded", function () {
            const profileImg = document.getElementById("profile-img");
            const profileCard = document.getElementById("profile-card");

            profileImg.addEventListener("click", function (event) {
                event.preventDefault();
                profileCard.classList.toggle("show");
            });

            // Tutup profile-card jika klik di luar
            document.addEventListener("click", function (event) {
                if (!profileCard.contains(event.target) && !profileImg.contains(event.target)) {
                    profileCard.classList.remove("show");
                }
            });
        });
    </script>
</body>

</html>