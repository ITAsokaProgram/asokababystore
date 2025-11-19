<?php
require_once __DIR__ . '/../../component/menu_handler.php';

$menuHandler = new MenuHandler('product_favorite');

if (!$menuHandler->initialize()) {
    exit();
}

$user_id = $menuHandler->getUserId();
$logger = $menuHandler->getLogger();
$token = $menuHandler->getToken();

// --- LOGIKA FILTER DARI URL ---
$filter_type = htmlspecialchars($_GET['filter_type'] ?? 'preset');
$filter = htmlspecialchars($_GET['filter'] ?? '3bulan');
$start_date = htmlspecialchars($_GET['start_date'] ?? '');
$end_date = htmlspecialchars($_GET['end_date'] ?? '');
$status = htmlspecialchars($_GET['status'] ?? 'all'); // Default status 'all' jika tidak ada

// Membangun Query String untuk keperluan link (misal tombol kembali atau refresh)
$queryParams = [
    'filter_type' => $filter_type,
    'status' => $status
];

if ($filter_type === 'custom') {
    $queryParams['start_date'] = $start_date;
    $queryParams['end_date'] = $end_date;
} else {
    $queryParams['filter'] = $filter;
}
$queryString = http_build_query($queryParams);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analisis Produk Favorit Member - Asoka Baby Store</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/all.min.css">

    <link rel="stylesheet" href="/css/header.css">
    <link rel="stylesheet" href="/css/sidebar.css">
    <link rel="stylesheet" href="/css/animation-fade-in.css">
    <link rel="icon" type="image/png" href="/images/logo1.png">
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="/src/style/default-font.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">

    <link rel="stylesheet" href="/src/output2.css">
    <style>
        .btn.active {
            background-color: transparent;
            color: #ec4899;
            outline: 2px solid #ec4899;
            outline-offset: 1px;
        }

        /* --- Enhanced Glass & Card Styles --- */
        .glass-container {
            background: rgba(255, 255, 255, 0.80);
            backdrop-filter: blur(8px);
            border-radius: 1.25rem;
            box-shadow: 0 8px 32px 0 rgba(16, 185, 129, 0.18);
            border: 1.5px solid #6ee7b7;
            padding: 2rem;
        }

        .card-glass {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(6px);
            border-radius: 1.25rem;
            box-shadow: 0 4px 24px 0 rgba(16, 185, 129, 0.10);
            border: 1.5px solid #10b981;
            transition: box-shadow 0.2s, transform 0.2s;
        }

        .card-glass:hover {
            box-shadow: 0 8px 32px 0 rgba(16, 185, 129, 0.18);
            transform: scale(1.02);
        }

        .skeleton-loader {
            background: linear-gradient(90deg, #f3f4f6 25%, #e5e7eb 50%, #f3f4f6 75%);
            background-size: 200% 100%;
            animation: skeleton 1.2s ease-in-out infinite;
        }

        @keyframes skeleton {
            0% {
                background-position: 200% 0;
            }

            100% {
                background-position: -200% 0;
            }
        }
    </style>
</head>

<body class="bg-gray-50 flex">

    <?php include '../../component/navigation_report.php'; ?>
    <?php include '../../component/sidebar_report.php'; ?>

    <main id="main-content" class="flex-1 p-8 transition-all duration-300 ml-64 mt-16 bg-gray-100">
        <div class="glass-container animate-fade-in-up">
            <div class="mb-8 flex flex-col md:flex-row items-center justify-between gap-4">
                <h1 class="text-3xl font-bold text-emerald-700 flex items-center gap-3"><i
                        class="fas fa-chart-line text-emerald-500"></i> Produk Favorit Member</h1>
                <div class="flex items-center gap-3">
                    <button id="refresh-btn"
                        class="bg-gradient-to-r from-emerald-500 to-emerald-700 hover:from-emerald-600 hover:to-emerald-800 text-white px-6 py-3 rounded-xl font-bold shadow-md transition-all duration-200 flex items-center gap-2">
                        <i class="fas fa-sync-alt"></i> Refresh Data
                    </button>
                    <div class="text-right">
                        <p class="text-sm text-gray-500">Update Terakhir</p>
                        <p class="font-semibold text-emerald-700" id="last-update">-</p>
                    </div>
                </div>
            </div>



            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
                <div class="card-glass p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-xl font-bold text-emerald-700 flex items-center gap-2">
                            <i class="fas fa-chart-bar text-emerald-500"></i> Tren Penjualan Barang Bulanan
                        </h3>
                    </div>
                    <div class="h-64">
                        <canvas id="monthlyTrendChart"></canvas>
                    </div>
                </div>
                <div class="card-glass p-6">
                    <h3 class="text-xl font-bold text-emerald-700 flex items-center gap-2 mb-6">
                        <i class="fas fa-box text-emerald-500"></i> Produk Terlaris
                    </h3>
                    <div class="space-y-4" id="product-performance">
                    </div>
                </div>
            </div>

            <div class="card-glass overflow-hidden">
                <div class="px-8 py-6 border-b border-emerald-100">
                    <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                        <h3 class="text-xl font-bold text-emerald-700 flex items-center gap-2">
                            <i class="fas fa-list text-emerald-500"></i> Daftar Produk Favorit Member
                            <span class="text-xs text-emerald-400 ml-2" id="date-range-display">
                                <i class="fas fa-info-circle text-emerald-400"></i> (Data 1 hari lalu)
                            </span>
                        </h3>
                        <div class="flex flex-wrap items-center gap-3">
                            <div class="flex items-center gap-2">
                                <div class="relative">
                                    <input type="date" id="start-date"
                                        class="border border-emerald-100 rounded-xl px-4 py-2 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-400 text-sm shadow-sm"
                                        placeholder="Start Date">
                                </div>
                                <span class="text-gray-400">-</span>
                                <div class="relative">
                                    <input type="date" id="end-date"
                                        class="border border-emerald-100 rounded-xl px-4 py-2 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-400 text-sm shadow-sm"
                                        placeholder="End Date">
                                </div>
                                <button id="apply-date-filter"
                                    class="bg-gradient-to-r from-emerald-500 to-emerald-700 hover:from-emerald-600 hover:to-emerald-800 text-white px-4 py-2 rounded-xl font-bold shadow-md transition-all duration-200 flex items-center gap-2 text-sm">
                                    <i class="fas fa-filter"></i> Filter
                                </button>
                                <button id="reset-date-filter"
                                    class="bg-gradient-to-r from-gray-400 to-gray-600 hover:from-gray-500 hover:to-gray-700 text-white px-4 py-2 rounded-xl font-bold shadow-md transition-all duration-200 flex items-center gap-2 text-sm">
                                    <i class="fas fa-times"></i> Reset
                                </button>
                            </div>
                            <div class="relative hidden">
                                <input type="text" id="search-input" placeholder="Cari member atau produk..."
                                    class="pl-10 pr-4 py-2 border border-emerald-100 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-400 shadow-sm">
                                <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                            </div>
                            <select id="sort-select"
                                class="border border-emerald-100 rounded-xl px-4 py-2 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-400 shadow-sm">
                                <option value="qty">Sort by Qty</option>
                                <option value="harga">Sort by Harga</option>
                            </select>
                            <button onclick="exportAllDataToExcel()"
                                class="bg-gradient-to-r from-emerald-500 to-emerald-700 hover:from-emerald-600 hover:to-emerald-800 text-white px-4 py-2 rounded-xl font-bold shadow-md transition-all duration-200 flex items-center gap-2">
                                <i class="fas fa-file-excel"></i> Export Excel
                            </button>
                        </div>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-emerald-100">
                        <thead class="bg-gradient-to-r from-emerald-500 to-emerald-700 text-white">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider">Member</th>
                                <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider">Produk
                                    Favorit</th>
                                <th class="px-6 py-3 text-center text-xs font-bold uppercase tracking-wider">Terjual
                                </th>
                                <th class="px-6 py-3 text-center text-xs font-bold uppercase tracking-wider">Total Harga
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-emerald-50" id="member-table-body">
                        </tbody>
                    </table>
                </div>
                <div id="loading-state" class="hidden p-8 text-center glass-container animate-fade-in-up">
                    <div class="inline-flex items-center gap-3">
                        <i class="fas fa-spinner fa-spin text-2xl text-emerald-500"></i>
                        <span class="text-emerald-700 font-bold">Memuat data...</span>
                    </div>
                </div>
                <div id="empty-state" class="hidden p-8 text-center glass-container animate-fade-in-up">
                    <i class="fas fa-inbox text-4xl text-emerald-200 mb-4"></i>
                    <h3 class="text-lg font-bold text-emerald-700 mb-2">Belum ada data produk favorit</h3>
                    <p class="text-emerald-400">Data produk favorit member akan muncul di sini</p>
                </div>
            </div>
            <div class="mt-4 flex justify-between items-center text-sm">
                <p class="text-gray-600" id="viewData"></p>
                <div class="flex flex-wrap gap-1 max-w-full overflow-x-auto" id="paginationContainer">

                </div>
            </div>
    </main>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>

    <script type="module" src="/src/js/member_internal/product/display_product_fav.js"></script>

    <script>
        document.getElementById("toggle-sidebar").addEventListener("click", function () {
            document.getElementById("sidebar").classList.toggle("open");
        });

        document.addEventListener("DOMContentLoaded", function () {
            const sidebar = document.getElementById("sidebar");
            const closeBtn = document.getElementById("closeSidebar");

            if (closeBtn) {
                closeBtn.addEventListener("click", function () {
                    sidebar.classList.remove("open");
                });
            }
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
                sidebarTexts.forEach((text) => text.classList.add("hidden"));
                mainContent.classList.remove("ml-64");
                mainContent.classList.add("ml-16");
                toggleButton.classList.add("left-20");
                toggleButton.classList.remove("left-64");
                icon.classList.remove("fa-angle-left");
                icon.classList.add("fa-angle-right");
            } else {
                // Sidebar membesar
                sidebar.classList.remove("w-16", "px-2");
                sidebar.classList.add("w-64", "px-5");
                sidebarTexts.forEach((text) => text.classList.remove("hidden"));
                mainContent.classList.remove("ml-16");
                mainContent.classList.add("ml-64");
                toggleButton.classList.add("left-64");
                toggleButton.classList.remove("left-20");
                icon.classList.remove("fa-angle-right");
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