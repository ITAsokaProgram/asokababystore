<?php
require_once __DIR__ . '/../../component/menu_handler.php';

$menuHandler = new MenuHandler('transaksi_margin');

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
    <title>Margin</title>

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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>


    <style>
        th.th-total-poin,
        th.th-tukar-poin,
        th.th-sisa-poin,
        th.th-transaksi {
            text-align: center !important;
        }
    </style>
</head>

<body class="bg-white">
    <?php include '../../component/navigation_report.php'; ?>
    <?php include '../../component/sidebar_report.php'; ?>


    <main id="main-content" class="flex-1 p-6 transition-all duration-300 ml-64 mt-10">
        <div
            class="min-h-screen bg-white/80 backdrop-blur-md rounded-2xl shadow-xl border border-white/30 p-6 animate-fade-in-up">
            <!-- Judul Halaman -->
            <div class="flex flex-col sm:flex-row justify-between items-center mb-8 gap-4">
                <div class="flex items-center gap-4">
                    <div class="bg-gradient-to-r from-pink-500 to-rose-400 p-3 rounded-xl shadow-lg">
                        <i data-lucide="trending-down" class="w-10 h-10 text-white"></i>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-800">Data Margin</h1>
                        <p class="text-gray-500 text-sm mt-1">Lihat dan filter data margin transaksi penjualan</p>
                    </div>
                </div>
            </div>

            <!-- Filter Wrapper -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-8">
                <!-- Card Filter Tanggal -->
                <div class="bg-white/70 backdrop-blur rounded-2xl shadow border border-white/30 p-6 animate-fade-in-up">
                    <h2 class="text-base font-bold text-gray-700 mb-4 flex items-center gap-2">
                        <i class="fa fa-filter text-pink-500"></i> Filter Tanggal & Cabang
                    </h2>
                    <div class="flex flex-col md:flex-row gap-4 mb-4">
                        <!-- Periode -->
                        <div class="flex flex-col w-full md:w-1/2">
                            <label for="periodeFilter" class="text-xs text-gray-600 mb-1">Cabang</label>
                            <select
                                class="border border-gray-300 rounded-xl px-4 py-3 text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-pink-400 focus:border-pink-400 transition hover:border-pink-400"
                                id="cabangFilter">
                                <option value="">Pilih Cabang</option>
                            </select>
                        </div>
                    </div>
                    <!-- Date Range Picker -->
                    <div class="flex flex-col md:flex-row items-end gap-4">
                        <div class="flex flex-col w-full md:w-1/2">
                            <label for="startDate" class="text-xs text-gray-600 mb-1">Tanggal Awal</label>
                            <input type="date" id="startDate"
                                class="border border-gray-300 rounded-xl px-4 py-3 text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-pink-400 focus:border-pink-400 transition hover:border-pink-400 w-full" />
                        </div>
                        <div class="flex flex-col w-full md:w-1/2">
                            <label for="endDate" class="text-xs text-gray-600 mb-1">Tanggal Akhir</label>
                            <input type="date" id="endDate"
                                class="border border-gray-300 rounded-xl px-4 py-3 text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-pink-400 focus:border-pink-400 transition hover:border-pink-400 w-full" />
                        </div>
                        <!-- Tombol Filter -->
                        <button id="filter"
                            class="bg-gradient-to-r from-pink-500 to-rose-400 hover:from-pink-600 hover:to-rose-500 text-white text-sm px-6 py-3 rounded-xl shadow-md flex items-center gap-2 transition-all duration-200 hover:scale-105">
                            <i class="fa fa-search mr-1"></i> Filter
                        </button>
                    </div>
                </div>
            </div>

            <!-- Tabel Data -->
            <div
                class="overflow-x-auto max-w-full bg-white/90 backdrop-blur rounded-2xl shadow-xl border border-white/30 animate-fade-in-up">
                <table class="w-full table-auto text-sm text-left min-w-[900px]" id="allTable">
                    <thead class="bg-gradient-to-r from-pink-400 to-rose-400 text-white text-xs uppercase">
                        <tr>
                            <th class="px-4 py-2 ">No</th>
                            <th class="px-4 py-2 ">Plu</th>
                            <th class="px-4 py-2 ">No Faktur</th>
                            <th class="px-4 py-2 ">Nama Barang</th>
                            <th class="px-4 py-2 text-center">Qty</th>
                            <th class="px-4 py-2 text-center ">Gross</th>
                            <th class="px-4 py-2 text-center ">Net</th>
                            <th class="px-4 py-2 text-center">Avg Cost</th>
                            <th class="px-4 py-2 text-center">PPN</th>
                            <th class="px-4 py-2 text-center  ">Margin</th>
                            <th class="px-4 py-2 ">Tanggal</th>
                            <th class="px-4 py-2 ">Cabang</th>
                            <th class="px-4 py-2 text-center">Periksa</th>
                        </tr>
                    </thead>
                    <tbody id="kategoriTable" class="kategori-body text-gray-700 text-sm">
                        <!-- Baris isi -->
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-6 flex flex-col sm:flex-row justify-between items-center text-sm gap-4">
                <p class="text-gray-600" id="viewData"></p>
                <div class="flex flex-wrap gap-2 max-w-full overflow-x-auto" id="paginationContainer">
                </div>
            </div>
        </div>
    </main>
    <!-- Modal Wrapper -->
    <div id="detailInvalid"
        class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 hidden flex items-center justify-center animate-fade-in-up">
        <div
            class="bg-white/95 backdrop-blur-md w-full max-w-3xl rounded-2xl shadow-2xl p-8 relative border border-white/30">

            <!-- Close Button -->
            <button type="button" id="closeModal"
                class="absolute top-4 right-4 text-gray-500 hover:text-red-500 text-2xl bg-white/80 rounded-full p-2 shadow-md transition-all duration-200">
                <i class="fas fa-times"></i>
            </button>

            <!-- Header -->
            <h2 class="text-2xl font-bold mb-6 text-gray-800 flex items-center gap-2">
                <i class="fa fa-info-circle text-pink-500"></i> Detail Invalid
            </h2>

            <!-- Table Detail -->
            <div class="max-h-96 w-full border bg-white/90 rounded-xl shadow overflow-x-auto">
                <table class="w-full table-auto text-sm text-left min-w-[800px]" id="tableDetail">
                    <thead class="bg-gradient-to-r from-pink-400 to-rose-400 text-white text-xs uppercase">
                        <tr class="text-center">
                            <th class="p-2 border">No</th>
                            <th class="p-2 border">Kode Kasir</th>
                            <th class="p-2 border">Nama Kasir</th>
                            <th class="p-2 border">Barcode</th>
                            <th class="p-2 border bg-pink-50 text-pink-700">No Faktur</th>
                            <th class="p-2 border">Nama Barang</th>
                            <th class="p-2 border">Keterangan</th>
                            <th class="p-2 border">Tanggal</th>
                            <th class="p-2 border">Cabang</th>
                        </tr>
                    </thead>
                    <tbody id="detailTbody" class="bg-white divide-y">
                        <!-- Diisi dari JS -->
                    </tbody>
                </table>
            </div>
            <!-- Pagination -->
            <div class="mt-6 flex flex-col sm:flex-row justify-between items-center text-sm gap-4">
                <p class="text-gray-600" id="viewDataDetail"></p>
                <div class="flex flex-wrap gap-2 max-w-full overflow-x-auto" id="paginationContainerDetail">
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div id="informasi"
        class="fixed inset-0 bg-black/50 backdrop-blur-sm hidden z-50 flex items-center justify-center animate-fade-in-up">
        <div class="bg-white/95 backdrop-blur-md rounded-2xl p-8 w-4/5 max-w-3xl shadow-2xl border border-white/30">
            <div class="mb-4 border-b pb-2 flex items-center gap-2">
                <i class="fa fa-info-circle text-pink-500 text-xl"></i>
                <h3 class="text-2xl font-bold text-gray-800">Informasi Pengecekan</h3>
            </div>

            <ul class="space-y-3 text-sm text-gray-700">
                <li>
                    <strong class="block text-gray-600">Tanggal:</strong>
                    <p id="tanggal_cek"></p>
                </li>
                <li>
                    <strong class="block text-gray-600">Nama PIC:</strong>
                    <p id="nama_pic"></p>
                </li>
                <li>
                    <strong class="block text-gray-600">Keterangan:</strong>
                    <p class="whitespace-pre-wrap break-words" id="keterangan">
                    </p>
                </li>
            </ul>

            <div class="mt-6 text-right">
                <button onclick="closeModal()"
                    class="px-6 py-3 bg-gradient-to-r from-blue-500 to-pink-500 hover:from-blue-600 hover:to-pink-600 text-white rounded-xl shadow-md transition-all duration-200 hover:scale-105">
                    Tutup
                </button>
            </div>
        </div>
    </div>
    <!-- custom js file link -->
    <!-- AlpineJS -->
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <!-- Custom JS -->
    <script src="../../js/middleware_auth.js"></script>
    <script src="../../js/margin/main.js" type="module"></script>

    <script>
        function closeModal() {
            document.getElementById("informasi").classList.add("hidden");
        }

        document.getElementById("toggle-sidebar").addEventListener("click", function () {
            document.getElementById("sidebar").classList.toggle("open");
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

    <script src="https://unpkg.com/lucide@latest"></script>
    <script>
        lucide.createIcons();
    </script>
</body>

</html>