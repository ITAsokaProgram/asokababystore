<?php
session_start();
include '../../../aa_kon_sett.php';

// Default search params
$search_val = $_GET['search'] ?? '';
$page = (int) ($_GET['page'] ?? 1);

require_once __DIR__ . '/../../component/menu_handler.php';
// $menuHandler = new MenuHandler('user_supplier_index'); 
// if (!$menuHandler->initialize()) {
//     exit();
// }
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data User Supplier</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/all.min.css">
    <link rel="stylesheet" href="../../style/header.css">
    <link rel="stylesheet" href="../../style/sidebar.css">
    <link rel="stylesheet" href="../../style/animation-fade-in.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../style/default-font.css">
    <link rel="stylesheet" href="../../output2.css">
    <link rel="stylesheet" href="../../style/pink-theme.css">
    <link rel="icon" type="image/png" href="../../../public/images/logo1.png">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</head>

<body class="bg-gray-50" x-data="{ 
    detailModalOpen: false, 
    detailData: {}
}" @open-detail-modal.window="detailModalOpen = true; detailData = $event.detail">
    
    <?php include '../../component/navigation_report.php' ?>
    <?php include '../../component/sidebar_report.php' ?>

    <main id="main-content" class="flex-1 p-4 ml-64">
        <section class="min-h-screen">
            <div class="max-w-7xl mx-auto">

                <div class="header-card p-4 rounded-2xl mb-4">
                    <div class="flex items-center justify-between flex-wrap gap-3">
                        <div class="flex items-center gap-3">
                            <div class="icon-wrapper">
                                <i class="fa-solid fa-users-gear fa-lg"></i>
                            </div>
                            <div>
                                <h1 class="text-xl font-bold text-gray-800 mb-1">Data User Supplier</h1>
                                <p class="text-xs text-gray-500">Kelola akun supplier dan wilayah operasional.</p>
                            </div>
                        </div>
                        <a href="create.php"
                            class="btn-primary inline-flex items-center justify-center gap-2 no-underline">
                            <i class="fa-solid fa-plus"></i>
                            <span>Tambah Supplier</span>
                        </a>
                    </div>
                </div>

                <div class="filter-card-simple mb-4">
                    <form id="filter-form" class="grid grid-cols-1 md:grid-cols-4 gap-3 items-end">
                        <div class="md:col-span-3">
                            <label class="block text-xs font-semibold text-gray-700 mb-2">Cari Data</label>
                            <input type="text" name="search" id="search" class="input-modern w-full"
                                placeholder="Cari berdasarkan nama, email, atau wilayah..." value="<?= htmlspecialchars($search_val) ?>">
                        </div>
                        <div class="md:col-span-1 flex justify-end">
                            <button type="submit" id="filter-submit-button"
                                class="btn-primary w-full inline-flex items-center justify-center gap-2 px-6">
                                <i class="fas fa-search"></i>
                                <span>Cari</span>
                            </button>
                        </div>
                    </form>
                </div>

                <div class="filter-card">
                    <div class="table-container">
                        <table class="table-modern" id="supplier-table">
                            <thead>
                                <tr>
                                    <th width="5%">No</th>
                                    <th width="20%">Nama Supplier</th>
                                    <th width="15%">Kontak</th>
                                    <th width="15%">Email</th>
                                    <th width="30%">Wilayah</th>
                                    <th width="15%" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="supplier-table-body">
                                <tr>
                                    <td colspan="6" class="text-center p-8">
                                        <div class="spinner-simple"></div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div id="pagination-container" class="flex justify-between items-center mt-4">
                        <span id="pagination-info" class="text-sm text-gray-600"></span>
                        <div id="pagination-links" class="flex items-center gap-2"></div>
                    </div>
                </div>

            </div>
        </section>
    </main>

    <div x-show="detailModalOpen" style="display: none;" class="relative z-50" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div x-show="detailModalOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="detailModalOpen = false"></div>

        <div class="fixed inset-0 z-10 overflow-y-auto">
            <div class="flex min-h-screen items-center justify-center p-4 text-center sm:p-0">
                <div x-show="detailModalOpen" x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    class="relative transform overflow-hidden rounded-xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
                    
                    <div class="bg-pink-50 px-4 py-3 sm:px-6 flex justify-between items-center">
                        <h3 class="text-lg font-bold leading-6 text-pink-700">Detail Supplier</h3>
                        <button @click="detailModalOpen = false" class="text-pink-400 hover:text-pink-600 focus:outline-none">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    
                    <div class="px-4 py-5 sm:p-6 space-y-3">
                        <div>
                            <p class="text-xs text-gray-500">Nama</p>
                            <p class="font-bold text-gray-800" x-text="detailData.nama"></p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Email & Telepon</p>
                            <p class="text-gray-700"><span x-text="detailData.email"></span> | <span x-text="detailData.no_telpon"></span></p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Wilayah Cakupan</p>
                            <div class="flex flex-wrap gap-1 mt-1">
                                <template x-if="detailData.wilayah">
                                    <template x-for="w in detailData.wilayah.split(',')" :key="w">
                                        <span class="px-2 py-1 bg-gray-100 text-xs rounded text-gray-600 border border-gray-200" x-text="w.trim()"></span>
                                    </template>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="/src/js/middleware_auth.js"></script>
    <script src="../../js/user_supplier/handler.js" type="module"></script>

</body>
</html>