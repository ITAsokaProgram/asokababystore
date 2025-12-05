<?php
require_once __DIR__ . '/../../component/menu_handler.php';

$menuHandler = new MenuHandler('top_sales');

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
    <title>Top Sales by Member - Asoka Baby Store</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/all.min.css">

    <link rel="stylesheet" href="/css/header.css">
    <link rel="stylesheet" href="/css/sidebar.css">
    <link rel="stylesheet" href="/css/animation-fade-in.css">
    <link rel="icon" type="image/png" href="/images/logo1.png">
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="/src/style/default-font.css">
    <link rel="stylesheet" href="/src/output2.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">

    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <style>
        .glass-container {
            background: rgba(255, 255, 255, 0.80);
            backdrop-filter: blur(8px);
            border-radius: 1.25rem;
            box-shadow: 0 8px 32px 0 rgba(250, 204, 21, 0.18);
            border: 1.5px solid #fde68a;
            padding: 1rem;
            /* Diubah dari 2rem */
        }

        .card-glass {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(6px);
            border-radius: 1.25rem;
            box-shadow: 0 4px 24px 0 rgba(250, 204, 21, 0.10);
            border: 1.5px solid #f59e0b;
            transition: box-shadow 0.2s, transform 0.2s;
        }

        .card-glass:hover {
            box-shadow: 0 8px 32px 0 rgba(250, 204, 21, 0.18);
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

        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .animate-fade-in-up {
            animation: fadeInUp 0.6s ease-out forwards;
        }

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

        /* CSS UNTUK GLOBAL LOADER BARU */
        #global-loader {
            position: fixed;
            inset: 0;
            background-color: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(4px);
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: opacity 0.3s ease-in-out;
        }

        #global-loader .spinner {
            width: 56px;
            height: 56px;
            border: 8px solid #f3f3f3;
            border-top: 8px solid #eab308;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        #global-loader.hidden {
            opacity: 0;
            pointer-events: none;
        }


        @media (max-width: 640px) {
            .group {
                margin-bottom: 1rem;
            }
        }
    </style>
</head>

<body class="bg-gray-50 flex">

    <?php include '../../component/navigation_report.php'; ?>
    <?php include '../../component/sidebar_report.php'; ?>

    <main id="main-content" class="flex-1 p-4 transition-all duration-300 ml-64 mt-16 bg-gray-100">
        <div class="glass-container animate-fade-in-up">

            <div class="mb-4 flex flex-col md:flex-row items-center justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-yellow-700 flex items-center gap-3">
                        <i class="fas fa-trophy text-yellow-500"></i> Top Sales by Member
                    </h1>
                    <p class="text-gray-600 mt-1">Analisis performa penjualan terbaik berdasarkan member</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3 mb-4" id="card-summary">
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-4">
                <div class="card-glass p-3">
                    <h3 class="text-xl font-bold text-yellow-700 flex items-center gap-2 mb-3">
                        <i class="fas fa-medal text-yellow-500"></i> Top 50 Member
                    </h3>
                    <div class="space-y-2 overflow-x-hidden" id="top-members-performance">
                    </div>
                </div>
                <div class="card-glass p-3">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-xl font-bold text-yellow-700 flex items-center gap-2">
                            <i class="fas fa-chart-bar text-yellow-500"></i> Top 50 Non Member
                        </h3>
                    </div>
                    <div class="space-y-2 overflow-x-hidden" id="top-non-member-performance">
                    </div>
                </div>
            </div>

            <div class="card-glass overflow-hidden">
                <div class="px-4 py-3 border-b border-yellow-100">
                    <div class="flex flex-col md:flex-row items-center justify-between gap-2 flex-wrap">
                        <h3 class="text-xl font-bold text-yellow-700 flex items-center gap-2">
                            <i class="fas fa-list text-yellow-500"></i> Daftar Top Sales Produk
                            <span class="text-xs text-yellow-600 ml-2" id="date-range-display">
                                <i class="fas fa-info-circle text-yellow-500"></i> Data kemarin
                            </span>
                        </h3>
                        <div class="flex flex-wrap items-center gap-2">
                            <div class="flex items-center gap-2">
                                <input type="date" id="start-date"
                                    class="border border-yellow-100 rounded-xl px-3 py-1 focus:ring-2 focus:ring-yellow-500 focus:border-yellow-400 shadow-sm text-sm">
                                <span class="text-gray-500">to</span>
                                <input type="date" id="end-date"
                                    class="border border-yellow-100 rounded-xl px-3 py-1 focus:ring-2 focus:ring-yellow-500 focus:border-yellow-400 shadow-sm text-sm">
                            </div>
                            <button id="apply-date-filter"
                                class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded-xl font-bold shadow-md transition-all duration-200 flex items-center gap-2 text-sm">
                                <i class="fas fa-filter"></i> Terapkan
                            </button>
                            <button id="reset-date-filter"
                                class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-3 py-1 rounded-xl font-bold shadow-md transition-all duration-200 flex items-center gap-2 text-sm">
                                <i class="fas fa-undo"></i> Reset
                            </button>
                            <div class="relative">
                                <input type="text" id="search-input" placeholder="Cari customer..."
                                    class="pl-8 pr-3 py-1 border border-yellow-100 rounded-xl focus:ring-2 focus:ring-yellow-500 focus:border-yellow-400 shadow-sm">
                            </div>
                            <select id="sort-select"
                                class="border border-yellow-100 rounded-xl px-3 py-1 focus:ring-2 focus:ring-yellow-500 focus:border-yellow-400 shadow-sm">
                                <option value="belanja">Sort by Total Belanja</option>
                                <option value="qty">Sort by Quantity</option>
                                <option value="nama">Sort by Nama</option>
                            </select>
                            <button onclick="exportTopSalesData()"
                                class="bg-gradient-to-r from-yellow-400 to-yellow-600 hover:from-yellow-500 hover:to-yellow-700 text-white px-3 py-1 rounded-xl font-bold shadow-md transition-all duration-200 flex items-center gap-2">
                                <i class="fas fa-file-excel"></i> Export Excel
                            </button>
                        </div>
                    </div>
                </div>
                <div>
                    <table class="min-w-full divide-y divide-yellow-100">
                        <thead class="bg-gradient-to-r from-yellow-400 to-yellow-600 text-white">
                            <tr>
                                <th class="px-3 py-2 text-center text-xs font-bold uppercase tracking-wider">No</th>
                                <th class="px-3 py-2 text-left text-xs font-bold uppercase tracking-wider">Nama</th>
                                <th class="px-3 py-2 text-left text-xs font-bold uppercase tracking-wider">Kode Customer
                                </th>
                                <th class="px-3 py-2 text-center text-xs font-bold uppercase tracking-wider">Total
                                    Quantity</th>
                                <th class="px-3 py-2 text-right text-xs font-bold uppercase tracking-wider">Total
                                    Belanja</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-yellow-50" id="top-sales-table-body">
                        </tbody>
                    </table>
                </div>
                <div id="loading-state" class="hidden p-4 text-center glass-container animate-fade-in-up">
                    <div class="inline-flex items-center gap-2">
                        <i class="fas fa-spinner fa-spin text-2xl text-yellow-500"></i>
                        <span class="text-yellow-700 font-bold">Memuat data...</span>
                    </div>
                </div>
                <div id="empty-state" class="hidden p-4 text-center glass-container animate-fade-in-up">
                    <i class="fas fa-trophy text-4xl text-yellow-200 mb-2"></i>
                    <h3 class="text-lg font-bold text-yellow-700 mb-1">Belum ada data</h3>
                    <p class="text-yellow-400">Data top sales akan muncul di sini</p>
                </div>
            </div>
            <div class="mt-2 flex justify-between items-center text-sm">
                <p class="text-gray-600" id="viewData"></p>
                <div class="flex flex-wrap gap-1 max-w-full overflow-x-auto" id="paginationContainer">
                </div>
            </div>
    </main>

    <div id="detail-modal"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 hidden animate-fade-in">
        <div
            class="bg-white rounded-2xl shadow-2xl w-full max-w-4xl mx-4 overflow-hidden animate-slide-up max-h-[90vh]">
            <div class="bg-gradient-to-r from-blue-600 to-purple-600 text-white p-4 relative">
                <button id="close-modal"
                    class="absolute top-2 right-2 text-white hover:text-gray-300 text-3xl font-bold transition-colors duration-200 transform hover:scale-110">
                    &times;
                </button>
                <div class="flex items-center space-x-2">

                    <div>
                        <h2 id="modal-title" class="text-2xl font-bold"></h2>
                        <p id="modal-subtitle" class="text-blue-100 text-sm"></p>
                    </div>
                </div>
            </div>

            <div class="p-4 max-h-96 overflow-y-auto">
                <div id="modal-content" class="space-y-2"></div>
            </div>

            <div class="bg-gray-50 px-4 py-2 border-t">
                <div class="flex justify-between items-center">
                    <div id="modal-timestamp" class="text-sm text-gray-500"></div>
                </div>
            </div>
        </div>
    </div>

    <div id="global-loader" class="hidden">
        <div class="spinner"></div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>

    <script type="module" src="/src/js/member_internal/product/display_top_sales.js"></script>

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