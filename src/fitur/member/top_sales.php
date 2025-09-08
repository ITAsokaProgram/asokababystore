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

    <!-- font awesome cdn link  -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/all.min.css">

    <!-- Penjelasan: Link ke CSS eksternal dengan cache busting query string -->
    <link rel="stylesheet" href="/css/header.css">
    <link rel="stylesheet" href="/css/sidebar.css">
    <link rel="stylesheet" href="/css/animation-fade-in.css">
    <!-- Setting logo pada tab di website Anda / Favicon -->
    <link rel="icon" type="image/png" href="/images/logo1.png">
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="/src/style/default-font.css">
    <link rel="stylesheet" href="/src/output2.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">

    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <style>
        .btn.active {
            background-color: transparent;
            color: #eab308;
            outline: 2px solid #eab308;
            outline-offset: 1px;
        }

        .glass-container {
            background: rgba(255, 255, 255, 0.80);
            backdrop-filter: blur(8px);
            border-radius: 1.25rem;
            box-shadow: 0 8px 32px 0 rgba(250, 204, 21, 0.18);
            border: 1.5px solid #fde68a;
            padding: 2rem;
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

    <main id="main-content" class="flex-1 p-8 transition-all duration-300 ml-64 mt-16 bg-gray-100">
        <div class="glass-container animate-fade-in-up">

            <!-- Header Section -->
            <div class="mb-8 flex flex-col md:flex-row items-center justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-yellow-700 flex items-center gap-3">
                        <i class="fas fa-trophy text-yellow-500"></i> Top Sales by Member
                    </h1>
                    <p class="text-gray-600 mt-2">Analisis performa penjualan terbaik berdasarkan member</p>
                </div>
                <div class="flex items-center gap-3">
                    <button id="refresh-btn"
                        class="bg-gradient-to-r from-yellow-400 to-yellow-600 hover:from-yellow-500 hover:to-yellow-700 text-white px-6 py-3 rounded-xl font-bold shadow-md transition-all duration-200 flex items-center gap-2">
                        <i class="fas fa-sync-alt"></i> Refresh Data
                    </button>
                    <div class="text-right">
                        <p class="text-sm text-gray-500">Update Terakhir</p>
                        <p class="font-semibold text-yellow-700" id="last-update">-</p>
                    </div>
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8" id="card-summary">
                <!-- Isi Di Js -->
            </div>

            <!-- Chart Section -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
                <!-- Top 5 Members Performance -->
                <div class="card-glass p-6">
                    <h3 class="text-xl font-bold text-yellow-700 flex items-center gap-2 mb-6">
                        <i class="fas fa-medal text-yellow-500"></i> Top 50 Member
                    </h3>
                    <div class="space-y-4 overflow-x-hidden" id="top-members-performance">
                        <!-- Data will be populated by JavaScript -->
                    </div>
                </div>
                <div class="card-glass p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-xl font-bold text-yellow-700 flex items-center gap-2">
                            <i class="fas fa-chart-bar text-yellow-500"></i> Top 50 Non Member
                        </h3>
                    </div>
                    <div class="space-y-4 overflow-x-hidden" id="top-non-member-performance">
                        <!-- Data will be populated by JavaScript -->
                    </div>
                </div>
            </div>

            <!-- Top Sales Members Table -->
            <div class="card-glass overflow-hidden">
                <div class="px-8 py-6 border-b border-yellow-100">
                    <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                        <h3 class="text-xl font-bold text-yellow-700 flex items-center gap-2">
                            <i class="fas fa-list text-yellow-500"></i> Daftar Top Sales Produk
                            <span class="text-xs text-yellow-400 ml-2" id="info-data">
                                <i class="fas fa-info-circle text-yellow-400"></i> Data diambil dari kemarin vs lusa
                            </span>
                        </h3>
                        <div class="flex flex-wrap items-center gap-3">
                            <div class="relative">
                                <input type="text" id="search-input" placeholder="Cari produk..."
                                    class="pl-10 pr-4 py-2 border border-yellow-100 rounded-xl focus:ring-2 focus:ring-yellow-500 focus:border-yellow-400 shadow-sm">
                                <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                            </div>
                            <select id="sort-select"
                                class="border border-yellow-100 rounded-xl px-4 py-2 focus:ring-2 focus:ring-yellow-500 focus:border-yellow-400 shadow-sm">
                                <option value="terjual">Sort by Terjual</option>
                                <option value="persen">Sort by Persentase</option>
                                <option value="plu">Sort by PLU</option>
                            </select>
                            <button onclick="exportTopSalesData()"
                                class="bg-gradient-to-r from-yellow-400 to-yellow-600 hover:from-yellow-500 hover:to-yellow-700 text-white px-4 py-2 rounded-xl font-bold shadow-md transition-all duration-200 flex items-center gap-2">
                                <i class="fas fa-file-excel"></i> Export Excel
                            </button>
                        </div>
                    </div>
                </div>
                <div>
                    <table class="min-w-full divide-y divide-yellow-100">
                        <thead class="bg-gradient-to-r from-yellow-400 to-yellow-600 text-white">
                            <tr>
                                <th class="px-6 py-3 text-center text-xs font-bold uppercase tracking-wider">No</th>
                                <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider">PLU</th>
                                <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider">Nama Barang
                                </th>
                                <th class="px-6 py-3 text-center text-xs font-bold uppercase tracking-wider">Terjual
                                </th>
                                <th class="px-6 py-3 text-center text-xs font-bold uppercase tracking-wider">Persen</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-yellow-50" id="top-sales-table-body">
                            <!-- Data will be populated by JavaScript -->
                        </tbody>
                    </table>
                </div>
                <!-- Loading State -->
                <div id="loading-state" class="hidden p-8 text-center glass-container animate-fade-in-up">
                    <div class="inline-flex items-center gap-3">
                        <i class="fas fa-spinner fa-spin text-2xl text-yellow-500"></i>
                        <span class="text-yellow-700 font-bold">Memuat data...</span>
                    </div>
                </div>
                <!-- Empty State -->
                <div id="empty-state" class="hidden p-8 text-center glass-container animate-fade-in-up">
                    <i class="fas fa-trophy text-4xl text-yellow-200 mb-4"></i>
                    <h3 class="text-lg font-bold text-yellow-700 mb-2">Belum ada data top sales</h3>
                    <p class="text-yellow-400">Data top sales produk akan muncul di sini</p>
                </div>
            </div>

            <!-- Pagination -->
            <div class="mt-4 flex justify-between items-center text-sm">
                <p class="text-gray-600" id="viewData"></p>
                <div class="flex flex-wrap gap-1 max-w-full overflow-x-auto" id="paginationContainer">
                </div>
            </div>
    </main>

    <!-- Enhanced Modal -->
    <div id="detail-modal"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 hidden animate-fade-in">
        <div
            class="bg-white rounded-2xl shadow-2xl w-full max-w-4xl mx-4 overflow-hidden animate-slide-up max-h-[90vh]">
            <!-- Header -->
            <div class="bg-gradient-to-r from-blue-600 to-purple-600 text-white p-6 relative">
                <button id="close-modal"
                    class="absolute top-4 right-4 text-white hover:text-gray-300 text-3xl font-bold transition-colors duration-200 transform hover:scale-110">
                    &times;
                </button>
                <div class="flex items-center space-x-3">
                    <div id="modal-icon"
                        class="w-10 h-10 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <h2 id="modal-title" class="text-2xl font-bold"></h2>
                        <p id="modal-subtitle" class="text-blue-100 text-sm"></p>
                    </div>
                </div>
            </div>

            <!-- Content -->
            <div class="p-6 max-h-96 overflow-y-auto">
                <div id="modal-content" class="space-y-4"></div>
            </div>

            <!-- Footer -->
            <div class="bg-gray-50 px-6 py-4 border-t">
                <div class="flex justify-between items-center">
                    <div id="modal-timestamp" class="text-sm text-gray-500"></div>
                </div>
            </div>
        </div>
    </div>
    <!-- custom js file link -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>

    <!-- Custom Scripts -->
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