<?php
session_start();
include '../../../aa_kon_sett.php';

// Tentukan nilai default
$default_tgl = ''; // Kosongkan default tanggal
$default_kd_store = ''; // Default 'Semua Cabang'
$default_page = 1;

// Ambil parameter dari URL
$kd_store = $_GET['kd_store'] ?? $default_kd_store;
$status_aset = $_GET['status_aset'] ?? '';
$search = $_GET['search'] ?? '';
$group_aset = $_GET['group_aset'] ?? ''; // <-- Filter group_aset baru
$page = (int) ($_GET['page'] ?? $default_page);
if ($page < 1) {
    $page = 1;
}

// Ambil parameter tanggal
$tanggal_beli_from = $_GET['tanggal_beli_from'] ?? $default_tgl;
$tanggal_beli_to = $_GET['tanggal_beli_to'] ?? $default_tgl;
$tanggal_perbaikan_from = $_GET['tanggal_perbaikan_from'] ?? $default_tgl;
$tanggal_perbaikan_to = $_GET['tanggal_perbaikan_to'] ?? $default_tgl;
$tanggal_rusak_from = $_GET['tanggal_rusak_from'] ?? $default_tgl;
$tanggal_rusak_to = $_GET['tanggal_rusak_to'] ?? $default_tgl;
$tanggal_mutasi_from = $_GET['tanggal_mutasi_from'] ?? $default_tgl;
$tanggal_mutasi_to = $_GET['tanggal_mutasi_to'] ?? $default_tgl;

// Tentukan colspan untuk tabel
$colspan = ($kd_store == '') ? 20 : 19; // 19 kolom default + 1 (jika semua cabang)

require_once __DIR__ . '/../../component/menu_handler.php';
$menuHandler = new MenuHandler('history_aset');

if (!$menuHandler->initialize()) {
    exit();
}
?><!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Management Aset</title>
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
    <link href="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.js"></script>

    <style>
        /* Override styles untuk group row */
        .group-header-row {
            background: linear-gradient(135deg, #ebf8ff 0%, #e0f2fe 100%);
            font-weight: bold;
            border-bottom: 2px solid #3b82f6;
        }

        .group-header-row td {
            padding: 0.5rem 0.75rem;
            font-size: 0.875rem;
            color: #1e40af;
        }
    </style>
</head>

<body class="bg-gray-50">
    <?php include '../../component/navigation_report.php'; ?>
    <?php include '../../component/sidebar_report.php'; ?>

    <main id="main-content" class="flex-1 p-4 ml-64">
        <section class="min-h-screen">
            <div class="max-w-7xl mx-auto">

                <div class="header-card p-4 rounded-2xl mb-4">
                    <div class="flex items-center justify-between flex-wrap gap-3">
                        <div class="flex items-center gap-3">
                            <div class="icon-wrapper">
                                <i class="fa-solid fa-boxes-stacked fa-lg"></i>
                            </div>
                            <div>
                                <h1 id="page-title" class="text-xl font-bold text-gray-800 mb-1">History Aset</h1>
                                <p id="page-subtitle" class="text-xs text-gray-600">Management Inventory Asset</p>
                            </div>
                        </div>
                        <button id="btnAdd" class="btn-primary inline-flex items-center justify-center gap-2 px-4 py-2">
                            <i class="fas fa-plus"></i>
                            <span>Tambah Produk</span>
                        </button>
                    </div>
                </div>

                <div class="filter-card-simple">
                    <form id="filter-form" method="GET" action="history_aset.php">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-3 items-end">
                            <div>
                                <label for="filterCabang" class="block text-xs font-semibold text-gray-700 mb-2">
                                    <i class="fas fa-store text-pink-600 mr-1"></i>
                                    Cabang
                                </label>
                                <select id="filterCabang" name="kd_store" class="input-modern w-full">
                                    <option value="" disabled>Memuat cabang...</option>
                                </select>
                                <script>
                                    // Skrip kecil untuk set selected value dari PHP
                                    document.getElementById('filterCabang').value = "<?php echo htmlspecialchars($kd_store); ?>";
                                </script>
                            </div>

                            <div>
                                <label for="filterStatus" class="block text-xs font-semibold text-gray-700 mb-2">
                                    <i class="fas fa-circle-check text-pink-600 mr-1"></i>
                                    Status
                                </label>
                                <select id="filterStatus" name="status_aset" class="input-modern w-full">
                                    <option value="">Semua Status</option>
                                    <option value="Baru" <?php echo ($status_aset == 'Baru') ? 'selected' : ''; ?>>Baru
                                    </option>
                                    <option value="Bekas" <?php echo ($status_aset == 'Bekas') ? 'selected' : ''; ?>>Bekas
                                    </option>
                                    <option value="Services" <?php echo ($status_aset == 'Services') ? 'selected' : ''; ?>>Services</option>
                                    <option value="Rusak" <?php echo ($status_aset == 'Rusak') ? 'selected' : ''; ?>>Rusak
                                    </option>
                                    <option value="Mutasi" <?php echo ($status_aset == 'Mutasi') ? 'selected' : ''; ?>>
                                        Mutasi</option>
                                </select>
                            </div>

                            <div>
                                <label for="filter_group_aset" class="block text-xs font-semibold text-gray-700 mb-2">
                                    <i class="fas fa-layer-group text-pink-600 mr-1"></i>
                                    Group Aset
                                </label>
                                <select id="filter_group_aset" name="group_aset" class="input-modern w-full">
                                    <option value="">Semua Group</option>
                                </select>
                                <script>
                                    document.getElementById('filter_group_aset').value = "<?php echo htmlspecialchars($group_aset); ?>";
                                </script>
                            </div>


                        </div>

                        <div class="mt-3">
                            <button type="button" id="toggleDateFilters"
                                class="w-full flex items-center justify-between px-3 py-2 bg-slate-50 hover:bg-slate-100 rounded-lg transition-colors border-2 border-slate-200">
                                <span class="text-xs font-semibold text-gray-700">
                                    <i class="fa-solid fa-calendar text-pink-600 mr-1"></i>
                                    Filter Tanggal (Opsional)
                                </span>
                                <i class="fa-solid fa-chevron-down text-slate-600 transition-transform duration-300 text-xs"
                                    id="dateFilterIcon"></i>
                            </button>

                            <div id="dateFiltersContent" class="overflow-hidden transition-all duration-300"
                                style="max-height: 0;">
                                <div class="pt-3 grid grid-cols-1 md:grid-cols-2 gap-2">
                                    <div>
                                        <label for="filter_tanggal_beli_from"
                                            class="block text-xs font-semibold text-gray-700 mb-1">
                                            <i class="fa-solid fa-calendar-plus text-pink-600 mr-1"></i>
                                            Tanggal Beli (Dari)
                                        </label>
                                        <input type="date" id="filter_tanggal_beli_from" name="tanggal_beli_from"
                                            class="input-modern w-full"
                                            value="<?php echo htmlspecialchars($tanggal_beli_from); ?>">
                                    </div>
                                    <div>
                                        <label for="filter_tanggal_beli_to"
                                            class="block text-xs font-semibold text-gray-700 mb-1">
                                            <i class="fa-solid fa-calendar-check text-pink-600 mr-1"></i>
                                            Tanggal Beli (Sampai)
                                        </label>
                                        <input type="date" id="filter_tanggal_beli_to" name="tanggal_beli_to"
                                            class="input-modern w-full"
                                            value="<?php echo htmlspecialchars($tanggal_beli_to); ?>">
                                    </div>
                                    <div>
                                        <label for="filter_tanggal_perbaikan_from"
                                            class="block text-xs font-semibold text-gray-700 mb-1">
                                            <i class="fa-solid fa-wrench text-pink-600 mr-1"></i>
                                            Tanggal Perbaikan (Dari)
                                        </label>
                                        <input type="date" id="filter_tanggal_perbaikan_from"
                                            name="tanggal_perbaikan_from" class="input-modern w-full"
                                            value="<?php echo htmlspecialchars($tanggal_perbaikan_from); ?>">
                                    </div>
                                    <div>
                                        <label for="filter_tanggal_perbaikan_to"
                                            class="block text-xs font-semibold text-gray-700 mb-1">
                                            <i class="fa-solid fa-tools text-pink-600 mr-1"></i>
                                            Tanggal Perbaikan (Sampai)
                                        </label>
                                        <input type="date" id="filter_tanggal_perbaikan_to" name="tanggal_perbaikan_to"
                                            class="input-modern w-full"
                                            value="<?php echo htmlspecialchars($tanggal_perbaikan_to); ?>">
                                    </div>
                                    <div>
                                        <label for="filter_tanggal_rusak_from"
                                            class="block text-xs font-semibold text-gray-700 mb-1">
                                            <i class="fa-solid fa-exclamation-triangle text-pink-600 mr-1"></i>
                                            Tanggal Rusak (Dari)
                                        </label>
                                        <input type="date" id="filter_tanggal_rusak_from" name="tanggal_rusak_from"
                                            class="input-modern w-full"
                                            value="<?php echo htmlspecialchars($tanggal_rusak_from); ?>">
                                    </div>
                                    <div>
                                        <label for="filter_tanggal_rusak_to"
                                            class="block text-xs font-semibold text-gray-700 mb-1">
                                            <i class="fa-solid fa-times-circle text-pink-600 mr-1"></i>
                                            Tanggal Rusak (Sampai)
                                        </label>
                                        <input type="date" id="filter_tanggal_rusak_to" name="tanggal_rusak_to"
                                            class="input-modern w-full"
                                            value="<?php echo htmlspecialchars($tanggal_rusak_to); ?>">
                                    </div>
                                    <div>
                                        <label for="filter_tanggal_mutasi_from"
                                            class="block text-xs font-semibold text-gray-700 mb-1">
                                            <i class="fa-solid fa-exchange-alt text-pink-600 mr-1"></i>
                                            Tanggal Mutasi (Dari)
                                        </label>
                                        <input type="date" id="filter_tanggal_mutasi_from" name="tanggal_mutasi_from"
                                            class="input-modern w-full"
                                            value="<?php echo htmlspecialchars($tanggal_mutasi_from); ?>">
                                    </div>
                                    <div>
                                        <label for="filter_tanggal_mutasi_to"
                                            class="block text-xs font-semibold text-gray-700 mb-1">
                                            <i class="fa-solid fa-arrow-right text-pink-600 mr-1"></i>
                                            Tanggal Mutasi (Sampai)
                                        </label>
                                        <input type="date" id="filter_tanggal_mutasi_to" name="tanggal_mutasi_to"
                                            class="input-modern w-full"
                                            value="<?php echo htmlspecialchars($tanggal_mutasi_to); ?>">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 flex items-center justify-between">
                            <button id="clearFilters" type="button" class="btn-secondary px-4 py-2">
                                <i class="fa-solid fa-eraser mr-2"></i>
                                Clear Filters
                            </button>
                            <button type="submit" id="filter-submit-button"
                                class="btn-primary inline-flex items-center justify-center gap-2">
                                <i class="fas fa-filter"></i>
                                <span>Tampilkan</span>
                            </button>
                        </div>

                        <input type="hidden" name="page" value="1">
                    </form>
                </div>
                <div class="filter-card">
                    <div class="flex flex-wrap justify-between items-center mb-3 gap-3">
                        <h3 class="text-lg font-bold text-gray-800">
                            <i class="fas fa-list text-pink-600 mr-2"></i>
                            Daftar Aset
                        </h3>
                        <div
                            class="px-4 py-2 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg border border-blue-200">
                            <span class="text-sm font-semibold text-blue-700" id="countText">0 Barang</span>
                        </div>
                    </div>

                    <div class="table-container">
                        <table class="table-modern" id="productTable">
                            <thead>
                                <tr>
                                    <th class="text-center">No</th>
                                    <th>No Seri</th>
                                    <th>Nama Barang</th>
                                    <th>Group Aset</th>
                                    <th>Merk</th>
                                    <th>Nama Cabang</th>

                                    <th>Tgl Rusak</th>
                                    <th>Tgl Perbaikan</th>
                                    <th>Tgl Ganti</th>
                                    <th>Harga Beli</th>
                                    <th>Nama Toko</th>
                                    <th>Tgl Beli</th>
                                    <th>Mutasi Dari</th>
                                    <th>Mutasi Untuk</th>
                                    <th>Tgl Mutasi</th>
                                    <th>Status</th>
                                    <th>Foto</th>
                                    <th>Keterangan</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="tbody">
                                <tr>
                                    <td colspan="19" class="text-center p-8">
                                        <div class="spinner-simple"></div>
                                        <p class="mt-3 text-gray-500 font-medium">Memuat data...</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div id="pagination-container" class="flex justify-between items-center mt-4">
                        <span id="pagination-info" class="text-sm text-gray-600"></span>
                        <div id="pagination-links" class="flex items-center gap-2">
                        </div>
                    </div>
                </div>

            </div>
        </section>
    </main>

    <script>
        // Skrip untuk toggle filter tanggal (biarkan apa adanya)
        document.addEventListener('DOMContentLoaded', function () {
            const toggleDateFilters = document.getElementById('toggleDateFilters');
            const dateFiltersContent = document.getElementById('dateFiltersContent');
            const dateFilterIcon = document.getElementById('dateFilterIcon');
            let isDateFilterOpen = false;

            // Cek jika ada filter tanggal yang aktif, buka foldernya
            const dateInputs = dateFiltersContent.querySelectorAll('input[type="date"]');
            const hasActiveDateFilter = Array.from(dateInputs).some(input => input.value !== '');

            if (hasActiveDateFilter) {
                isDateFilterOpen = true;
                dateFiltersContent.style.maxHeight = dateFiltersContent.scrollHeight + 'px';
                dateFilterIcon.style.transform = 'rotate(180deg)';
            } else {
                dateFiltersContent.style.maxHeight = '0';
                dateFilterIcon.style.transform = 'rotate(0deg)';
            }

            toggleDateFilters.addEventListener('click', () => {
                isDateFilterOpen = !isDateFilterOpen;

                if (isDateFilterOpen) {
                    dateFiltersContent.style.maxHeight = dateFiltersContent.scrollHeight + 'px';
                    dateFilterIcon.style.transform = 'rotate(180deg)';
                } else {
                    dateFiltersContent.style.maxHeight = '0';
                    dateFilterIcon.style.transform = 'rotate(0deg)';
                }
            });
        });
    </script>

    <div id="addAssetModal" class="fixed inset-0 z-50 overflow-y-auto hidden">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="fixed inset-0 bg-black opacity-50"></div>

            <div class="relative bg-white rounded-xl shadow-2xl max-w-4xl w-full p-6 overflow-hidden">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-2xl font-semibold text-gray-800">Tambah Asset</h3>
                    <button type="button" class="close-modal text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-2xl"></i>
                    </button>
                </div>

                <form id="assetForm" class="space-y-6">
                    <input type="hidden" name="idhistory_aset" id="idhistory_aset" value="">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                No Seri <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="no_seri" required
                                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Nama Barang <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="nama_barang" required
                                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Merk <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="merk" required
                                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Harga Beli <span class="text-red-500">*</span>
                            </label>
                            <input type="number" name="harga_beli" required
                                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Nama Toko Pembelian <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="nama_toko" required
                                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>


                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Kode Store <span class="text-red-500">*</span>
                            </label>
                            <select name="kd_store" required
                                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Pilih Store...</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Tanggal Beli<span class="text-red-500">*</span>
                            </label>
                            <input type="date" name="tanggal_beli"
                                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>



                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Tanggal Ganti
                            </label>
                            <input type="date" name="tanggal_ganti"
                                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>


                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Tanggal Rusak
                            </label>
                            <input type="date" name="tanggal_rusak"
                                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Tanggal Perbaikan
                            </label>
                            <input type="date" name="tanggal_perbaikan"
                                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>


                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Tanggal Mutasi
                            </label>
                            <input type="date" name="tanggal_mutasi"
                                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Group Aset</label>
                            <input type="text" name="group_aset" id="input_group_aset" placeholder="Isi group manual"
                                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Mutasi Dari
                            </label>
                            <input type="text" name="mutasi_dari"
                                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Mutasi Untuk
                            </label>
                            <input type="text" name="mutasi_untuk"
                                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>



                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Status
                            </label>
                            <select name="status"
                                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="Baru">Baru</option>
                                <option value="Bekas">Bekas</option>
                                <option value="Services">Services</option>
                                <option value="Mutasi">Mutasi</option>
                                <option value="Rusak">Rusak</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Keterangan</label>
                            <textarea name="keterangan" id="input_keterangan" placeholder="Isi keterangan"
                                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
                        </div>
                    </div>

                    <div class="mt-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Foto Aset
                        </label>
                        <div class="mt-1 flex flex-col items-center justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg"
                            id="dropzone">
                            <div class="space-y-1 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none"
                                    viewBox="0 0 48 48" aria-hidden="true">
                                    <path
                                        d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02"
                                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                <div class="flex text-sm text-gray-600">
                                    <label for="image"
                                        class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                                        <span>Upload a file</span>
                                        <input id="image" name="image" type="file" class="sr-only" accept="image/*">
                                    </label>
                                    <p class="pl-1">or drag and drop</p>
                                </div>
                                <p class="text-xs text-gray-500">PNG, JPG, GIF up to 10MB</p>
                            </div>

                            <div id="imagePreview" class="mt-4 hidden">
                                <img src="" alt="Preview" class="max-h-48 rounded-lg mx-auto shadow-md">
                            </div>
                        </div>

                    </div>

                    <div class="flex justify-end gap-4 mt-6 pt-6 border-t">
                        <button type="button"
                            class="close-modal px-6 py-2 border rounded-lg text-gray-600 hover:bg-gray-50">
                            Cancel
                        </button>
                        <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                            Save Asset
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="editAssetModal" class="fixed inset-0 z-50 overflow-y-auto hidden">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="fixed inset-0 bg-black opacity-50"></div>

            <div class="relative bg-white rounded-xl shadow-2xl max-w-4xl w-full p-6 overflow-hidden">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-2xl font-semibold text-gray-800">Edit Asset</h3>
                    <button type="button" class="close-modal-edit text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-2xl"></i>
                    </button>
                </div>

                <form id="editAssetForm" class="space-y-6">
                    <input type="hidden" name="edit_idhistory_aset" id="edit_idhistory_aset" value="">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                No Seri <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="edit_no_seri" required
                                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Nama Barang <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="edit_nama_barang" required
                                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Merk <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="edit_merk" required
                                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Harga Beli <span class="text-red-500">*</span>
                            </label>
                            <input type="number" name="edit_harga_beli" required
                                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Nama Toko Pembelian <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="edit_nama_toko" required
                                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Kode Store <span class="text-red-500">*</span>
                            </label>
                            <select name="edit_kd_store" required
                                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Pilih Store...</option>
                            </select>
                        </div>


                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Tanggal Beli<span class="text-red-500">*</span>
                            </label>
                            <input type="date" name="edit_tanggal_beli"
                                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Tanggal Ganti
                            </label>
                            <input type="date" name="edit_tanggal_ganti"
                                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>


                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Tanggal Rusak
                            </label>
                            <input type="date" name="edit_tanggal_rusak"
                                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Tanggal Perbaikan
                            </label>
                            <input type="date" name="edit_tanggal_perbaikan"
                                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>


                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Tanggal Mutasi
                            </label>
                            <input type="date" name="edit_tanggal_mutasi"
                                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Group Aset</label>
                            <input type="text" name="edit_group_aset" id="input_group_aset"
                                placeholder="Isi group manual"
                                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Mutasi Dari
                            </label>
                            <input type="text" name="edit_mutasi_dari"
                                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Mutasi Untuk
                            </label>
                            <input type="text" name="edit_mutasi_untuk"
                                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>



                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Status
                            </label>
                            <select name="edit_status"
                                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="Baru">Baru</option>
                                <option value="Bekas">Bekas</option>
                                <option value="Services">Services</option>
                                <option value="Mutasi">Mutasi</option>
                                <option value="Rusak">Rusak</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Keterangan</label>
                            <textarea name="edit_keterangan" id="input_keterangan" placeholder="Isi keterangan"
                                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
                        </div>
                    </div>

                    <div class="mt-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Foto Aset
                        </label>
                        <div class="mt-1 flex flex-col items-center justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg"
                            id="dropzone">
                            <div class="space-y-1 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none"
                                    viewBox="0 0 48 48" aria-hidden="true">
                                    <path
                                        d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02"
                                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                <div class="flex text-sm text-gray-600">
                                    <label for="edit_image"
                                        class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                                        <span>Upload a file</span>
                                        <input id="edit_image" name="edit_image" type="file" class="sr-only"
                                            accept="image/*">
                                    </label>
                                    <p class="pl-1">or drag and drop</p>
                                </div>
                                <p class="text-xs text-gray-500">PNG, JPG, GIF up to 10MB</p>
                            </div>

                            <div id="editImagePreview" class="mt-4 hidden">
                                <img src="" alt="Preview" class="max-h-48 rounded-lg mx-auto shadow-md">
                            </div>
                        </div>

                    </div>

                    <div class="flex justify-end gap-4 mt-6 pt-6 border-t">
                        <button type="button"
                            class="close-modal-edit px-6 py-2 border rounded-lg text-gray-600 hover:bg-gray-50">
                            Cancel
                        </button>
                        <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                            Save Asset
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="historyLogModal" class="fixed inset-0 z-50 overflow-y-auto hidden">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="fixed inset-0 bg-black opacity-50"></div>
            <div class="relative bg-white rounded-xl shadow-2xl max-w-4xl w-full p-6 overflow-hidden">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-semibold text-gray-800">Riwayat Perubahan Aset</h3>
                    <button id="closeHistoryModal" class="text-gray-500 hover:text-gray-800">Tutup ✕</button>
                </div>

                <div class="overflow-x-auto" style="max-height:60vh; overflow:auto;">
                    <table class="w-full table-auto border-collapse">
                        <thead class="table-header text-left"
                            style="position:sticky; top:0; background:#fff; z-index:5;">
                            <tr>
                                <th class="px-3 py-2">User</th>
                                <th class="px-3 py-2">Tanggal</th>
                                <th class="px-3 py-2">Field</th>
                                <th class="px-3 py-2">Old</th>
                                <th class="px-3 py-2">New</th>
                            </tr>
                        </thead>
                        <tbody id="historyLogBody">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Modal handling
        document.addEventListener('DOMContentLoaded', function () {
            const modal = document.getElementById('addAssetModal');
            const openModalBtn = document.getElementById('btnAdd');
            const closeModalBtns = document.querySelectorAll('.close-modal');
            const form = document.getElementById('assetForm');

            // Open modal
            openModalBtn.addEventListener('click', () => {
                modal.classList.remove('hidden');
            });

            // Close modal
            closeModalBtns.forEach(btn => {
                btn.addEventListener('click', () => {
                    modal.classList.add('hidden');
                });
            });
        });
    </script>

    <script src="/src/js/middleware_auth.js"></script>
    <script src="../../js/aset/main.js" type="module"></script>
    <script src="../../js/shared/internal/sidebar-profile.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</body>

</html>