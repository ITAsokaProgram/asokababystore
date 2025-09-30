<?php
require_once __DIR__ . '/../../component/menu_handler.php';

$menuHandler = new MenuHandler('member_poin');

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
    <title>Poin</title>

    <!-- font awesome cdn link  -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/all.min.css">

    <!-- Penjelasan: Link ke CSS eksternal dengan cache busting query string -->
    <link rel="stylesheet" href="../../style/header.css">
    <link rel="stylesheet" href="../../style/sidebar.css">
    <!-- Setting logo pada tab di website Anda / Favicon -->
    <link rel="icon" type="image/png" href="../../../public/images/logo1.png">
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="../../style/default-font.css">
    <!-- <link rel="stylesheet" href="../../style/output.css"> -->
    <link rel="stylesheet" href="../../output2.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/2.2.2/css/dataTables.dataTables.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css">
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>


    <style>
        .loading-glass {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(6px);
        }
        .btn.active {
            background-color: transparent;
            /* background tidak diisi */
            color: #ec4899;
            /* warna teks bisa disesuaikan */
            outline: 2px solid #ec4899;
            outline-offset: 1px;
        }

        th.th-total-poin,
        th.th-tukar-poin,
        th.th-sisa-poin,
        th.th-transaksi {
            text-align: center !important;
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

<body class="bg-white">
    <?php include '../../component/navigation_report.php'; ?>
    <?php include '../../component/sidebar_report.php'; ?>


    <main id="main-content" class="flex-1 p-6 ml-64">
        <div id="progressOverlay"
             class="fixed inset-0 loading-glass z-50 hidden flex flex-col items-center justify-center space-y-4 animate-fade-in-up">
            <div class="text-lg font-semibold text-blue-700 animate-pulse flex items-center gap-2">
                <i class="fa fa-spinner fa-spin"></i> Loading data...
            </div>
            <div class="w-64 h-4 bg-blue-100 rounded-full overflow-hidden">
                <div id="progressBar" class="h-full bg-blue-500 w-0 rounded-full transition-all duration-500"></div>
            </div>
        </div>
        <!-- Enhanced Container with Gradient Background -->
        <div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-indigo-50 p-6">
            <div class="max-w-7xl mx-auto">
                <!-- Header Section with Glass Effect -->
                <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-xl border border-white/20 p-8 mb-8 animate-fade-in-up hover:-translate-y-1 hover:shadow-2xl transition-all duration-300">
                    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6">
                        <div class="flex items-center space-x-4">
                            <div class="relative">
                                <div class="w-12 h-12 bg-gradient-to-r from-blue-400 to-indigo-400 rounded-xl flex items-center justify-center shadow-lg">
                                    <i class="fas fa-id-card text-white text-xl"></i>
                                </div>
                                <div class="absolute -top-1 -right-1 w-4 h-4 bg-green-400 rounded-full border-2 border-white animate-pulse"></div>
                            </div>
                            <div>
                                <h2 class="text-2xl font-bold text-gray-800">Data Member Poin</h2>
                                <p class="text-gray-600 text-sm">Kelola dan monitor poin member</p>
                                <div class="flex items-center space-x-2 mt-1">
                                    <div class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></div>
                                    <span class="text-xs text-green-600 font-medium">Sistem Aktif</span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Stats Section -->
                        <div class="flex items-center space-x-6 mt-4 lg:mt-0">
                            <div class="bg-gradient-to-r from-blue-500 to-blue-600 text-white px-4 py-2 rounded-lg text-center hover:-translate-y-1 hover:shadow-lg transition-all duration-300">
                                <div class="text-lg font-bold" id="totalMembers">0</div>
                                <div class="text-xs opacity-90">Total Members</div>
                            </div>
                            <div class="bg-gradient-to-r from-green-500 to-green-600 text-white px-4 py-2 rounded-lg text-center hover:-translate-y-1 hover:shadow-lg transition-all duration-300">
                                <div class="text-lg font-bold" id="activeMembers">0</div>
                                <div class="text-xs opacity-90">Active</div>
                            </div>

                            <div class="bg-gradient-to-r from-red-500 to-red-600 text-white px-4 py-2 rounded-lg text-center hover:-translate-y-1 hover:shadow-lg transition-all duration-300">
                                <div class="text-lg font-bold" id="totalNonActive">0</div>
                                <div class="text-xs opacity-90">Non Active</div>
                            </div>
                        </div>
                        
                        <!-- Search Section -->
                        <div class="flex flex-col sm:flex-row items-center space-y-3 sm:space-y-0 sm:space-x-4">
                            <div class="relative group">
                                <input type="text" id="search" placeholder="Cari berdasarkan nama atau no HP..." 
                                       class="pl-12 pr-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm w-80 bg-white/90 backdrop-blur-sm shadow-sm transition-all duration-300 group-hover:shadow-md">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                    <i class="fas fa-search text-gray-400 group-hover:text-blue-500 transition-colors duration-200"></i>
                                </div>
                            </div>
                            <select id="status" class="px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm bg-white/90 backdrop-blur-sm shadow-sm transition-all duration-300">
                                <option value="allStatus">Semua Status</option>
                                <option value="aktif">Aktif</option>
                                <option value="nonaktif">Nonaktif</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Table Container with Enhanced Design -->
                <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-2xl border border-white/30 overflow-hidden animate-fade-in-up hover:-translate-y-1 hover:shadow-2xl transition-all duration-300">
                    <!-- Table Header with Gradient -->
                    <div class="bg-gradient-to-r from-blue-400 via-indigo-400 to-blue-500 p-6">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-white">Daftar Member</h3>
                            <div class="flex items-center space-x-2">
                                <div class="w-2 h-2 bg-white/80 rounded-full animate-pulse"></div>
                                <span class="text-white/90 text-sm">View Data</span>
                            </div>
                        </div>
                    </div>

                    <!-- Table Content -->
                    <div class="max-h-[65vh] overflow-x-auto">
                        <table class="w-full table-auto text-sm text-left">
                            <thead class="bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
                                <tr>
                                    <th class="px-6 py-4 text-left font-semibold text-gray-700 text-xs uppercase tracking-wider">No</th>
                                    <th class="px-6 py-4 text-left font-semibold text-gray-700 text-xs uppercase tracking-wider">Nama Member</th>
                                    <th class="px-6 py-4 text-left font-semibold text-gray-700 text-xs uppercase tracking-wider">No. HP</th>
                                    <th class="px-6 py-4 text-left font-semibold text-gray-700 text-xs uppercase tracking-wider">Total Poin</th>
                                    <th class="px-6 py-4 text-left font-semibold text-gray-700 text-xs uppercase tracking-wider">Sisa Poin</th>
                                    <th class="px-6 py-4 text-left font-semibold text-gray-700 text-xs uppercase tracking-wider">Terakhir Transaksi</th>
                                    <th class="px-6 py-4 text-left font-semibold text-gray-700 text-xs uppercase tracking-wider">Cabang</th>
                                    <th class="px-6 py-4 text-left font-semibold text-gray-700 text-xs uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-4 text-left font-semibold text-gray-700 text-xs uppercase tracking-wider">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="userTableBody" class="text-gray-700">
                                <!-- Data member akan dirender di sini -->
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Enhanced Pagination Section -->
                    <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-t border-gray-200">
                        <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
                            <div class="flex items-center space-x-2">
                                <i class="fas fa-info-circle text-gray-400"></i>
                                <p class="text-gray-600 text-sm font-medium" id="viewData"></p>
                            </div>
                            <div class="flex flex-wrap gap-2 justify-center sm:justify-end" id="paginationContainer">
                                <!-- Pagination buttons will be rendered here -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Floating Action Button -->
        <div class="fixed bottom-8 right-8 z-50">
            <button class="w-14 h-14 bg-gradient-to-r from-blue-500 to-indigo-500 text-white rounded-full shadow-lg hover:shadow-xl transform hover:scale-110 transition-all duration-300 flex items-center justify-center">
                <i class="fas fa-plus text-xl"></i>
            </button>
        </div>
        
    </main>
    <!-- Enhanced Modal Wrapper -->
    <div id="memberDetailModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white/95 backdrop-blur-md rounded-2xl shadow-2xl w-full max-w-6xl relative animate-fade-in-up hover:-translate-y-1 hover:shadow-2xl transition-all duration-300 border border-white/20 max-h-[90vh] overflow-hidden">
            <!-- Modal Header with Gradient -->
            <div class="bg-gradient-to-r from-blue-400 via-indigo-400 to-blue-500 rounded-t-2xl p-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center">
                            <i class="fas fa-user-circle text-white text-lg"></i>
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-white">Detail Member</h2>
                            <p class="text-white/80 text-sm">Informasi lengkap member dan transaksi poin</p>
                        </div>
                    </div>
                    <button type="button" id="closeModal" class="text-white/80 hover:text-white transition-colors duration-200">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
            </div>

            <!-- Modal Content -->
            <div class="p-6 space-y-6 overflow-y-auto max-h-[calc(90vh-200px)]">
                <!-- Info Member Section -->
                <div class="bg-gradient-to-r from-gray-50 to-gray-100 rounded-xl p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-user-circle mr-2 text-blue-500"></i>
                        Informasi Member
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700 flex items-center">
                                <i class="fas fa-user mr-2 text-blue-500"></i>
                                Nama Member
                            </label>
                            <div class="text-gray-800 font-medium" id="namaMember">-</div>
                        </div>
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700 flex items-center">
                                <i class="fas fa-phone mr-2 text-blue-500"></i>
                                No. HP
                            </label>
                            <div class="text-gray-800 font-medium" id="noHpMember">-</div>
                        </div>
                    </div>
                </div>

                <!-- Transaction History Section -->
                <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-history mr-2 text-blue-500"></i>
                        Riwayat Transaksi Poin
                    </h3>
                    
                    <div class="max-h-72 overflow-y-auto">
                        <table class="min-w-full text-sm text-left border border-gray-200 rounded-lg overflow-hidden">
                            <thead class="bg-gradient-to-r from-gray-100 to-gray-200">
                                <tr class="text-center">
                                    <th class="p-3 border-b border-gray-200 font-semibold text-gray-700">No</th>
                                    <th class="p-3 border-b border-gray-200 font-semibold text-gray-700">No Bon</th>
                                    <th class="p-3 border-b border-gray-200 font-semibold text-gray-700">Tanggal</th>
                                    <th class="p-3 border-b border-gray-200 font-semibold text-green-600">Poin Masuk</th>
                                    <th class="p-3 border-b border-gray-200 font-semibold text-red-600">Poin Keluar</th>
                                    <th class="p-3 border-b border-gray-200 font-semibold text-blue-600">Total Poin</th>
                                    <th class="p-3 border-b border-gray-200 font-semibold text-gray-700">Cabang</th>
                                </tr>
                            </thead>
                            <tbody id="detailTbody" class="bg-white divide-y divide-gray-200">
                                <!-- Diisi dari JS -->
                            </tbody>
                            <tfoot class="bg-gradient-to-r from-blue-50 to-indigo-50">
                                <tr>
                                    <td colspan="5" class="p-3 font-semibold text-center text-gray-700">Total Poin</td>
                                    <td id="totalPoin" class="p-3 font-bold text-blue-600 text-center text-lg">0</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- custom js file link -->
    <!-- AlpineJS -->
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <!-- Custom JS -->
    <script src="../../js/loadingbar.js"></script> 
    <script src="../../js/middleware_auth.js"></script>
    <script type="module" src="/src/js/member_internal/poin.js"></script>
    <script>
        document.getElementById("toggle-sidebar").addEventListener("click", function() {
            document.getElementById("sidebar").classList.toggle("open");
        });
        document.addEventListener("DOMContentLoaded", function() {
            const sidebar = document.getElementById("sidebar");
            const closeBtn = document.getElementById("closeSidebar");

            closeBtn.addEventListener("click", function() {
                sidebar.classList.remove("open"); // Hilangkan class .open agar sidebar tertutup
            });
        });
        document.getElementById("toggle-hide").addEventListener("click", function() {
            var sidebarTexts = document.querySelectorAll(".sidebar-text");
            let mainContent = document.getElementById("main-content");
            let sidebar = document.getElementById("sidebar");
            var toggleButton = document.getElementById("toggle-hide");
            var icon = toggleButton.querySelector("i");

            if (sidebar.classList.contains("w-64")) {
                // Sidebar mengecil
                sidebar.classList.remove("w-64", "px-5");
                sidebar.classList.add("w-16", "px-2");
                sidebarTexts.forEach((text) => text.classList.add("hidden")); // Sembunyikan teks
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
                sidebarTexts.forEach((text) => text.classList.remove("hidden")); // Tampilkan teks kembali
                mainContent.classList.remove("ml-16");
                mainContent.classList.add("ml-64");
                toggleButton.classList.add("left-64"); // Geser tombol ke posisi awal
                toggleButton.classList.remove("left-20");
                icon.classList.remove("fa-angle-right"); // Ubah ikon
                icon.classList.add("fa-angle-left");
            }
        });
        document.addEventListener("DOMContentLoaded", function() {
            const profileImg = document.getElementById("profile-img");
            const profileCard = document.getElementById("profile-card");

            profileImg.addEventListener("click", function(event) {
                event.preventDefault();
                profileCard.classList.toggle("show");
            });

            // Tutup profile-card jika klik di luar
            document.addEventListener("click", function(event) {
                if (!profileCard.contains(event.target) && !profileImg.contains(event.target)) {
                    profileCard.classList.remove("show");
                }
            });
        });
    </script>


</body>

</html>