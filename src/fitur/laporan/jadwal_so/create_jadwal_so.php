<?php
session_start();
include '../../../../aa_kon_sett.php';
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buat Jadwal SO</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/all.min.css">
    <link rel="stylesheet" href="../../../style/header.css">
    <link rel="stylesheet" href="../../../style/sidebar.css">
    <link rel="stylesheet" href="../../../style/animation-fade-in.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../../style/default-font.css">
    <link rel="stylesheet" href="../../../output2.css">
    <link rel="stylesheet" href="../../../style/pink-theme.css">
    <link rel="icon" type="image/png" href="../../../../public/images/logo1.png">

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>

    <style>
        .multi-select-container {
            height: 250px;
            overflow-y: auto;
            background: white;
            border: 2px solid #e5e7eb;
            border-radius: 0.5rem;
            transition: all 0.3s ease;
        }

        .multi-select-container:focus-within {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px rgba(236, 72, 153, 0.1);
        }

        /* Custom scrollbar untuk container */
        .multi-select-container::-webkit-scrollbar {
            width: 8px;
        }

        .multi-select-container::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        .multi-select-container::-webkit-scrollbar-thumb {
            background: #f472b6;
            border-radius: 4px;
        }

        .multi-select-container::-webkit-scrollbar-thumb:hover {
            background: #ec4899;
        }
    </style>
</head>

<body class="bg-gray-50">

    <?php include '../../../component/navigation_report.php'; ?>
    <?php include '../../../component/sidebar_report.php'; ?>

    <main id="main-content" class="flex-1 p-4 ml-64">
        <section class="min-h-screen">
            <div class="max-w-5xl mx-auto">

                <div class="header-card p-4 rounded-2xl mb-6">
                    <div class="flex items-center justify-between flex-wrap gap-3">
                        <div class="flex items-center gap-3">
                            <div class="icon-wrapper">
                                <i class="fa-solid fa-calendar-plus fa-lg"></i>
                            </div>
                            <div>
                                <h1 class="text-xl font-bold text-gray-800 mb-1">Buat Jadwal SO</h1>
                                <p class="text-xs text-gray-600">Formulir pembuatan jadwal stock opname baru.</p>
                            </div>
                        </div>
                        <a href="index.php"
                            class="btn-secondary inline-flex items-center justify-center gap-2 no-underline">
                            <i class="fa-solid fa-arrow-left"></i>
                            <span>Kembali</span>
                        </a>
                    </div>
                </div>

                <form id="formJadwalSO">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">

                        <div class="space-y-6">
                            <div class="filter-card">
                                <div class="mb-3 flex justify-between items-center border-b border-gray-100 pb-2">
                                    <label class="text-sm font-bold text-gray-800 flex items-center gap-2">
                                        <span
                                            class="bg-pink-100 text-pink-600 w-6 h-6 rounded-full flex items-center justify-center text-xs">1</span>
                                        Pilih Cabang (Store) <span class="text-red-500">*</span>
                                    </label>
                                    <div class="flex gap-2 text-xs">
                                        <button type="button" id="btn-select-all-cabang"
                                            class="text-pink-600 hover:text-pink-700 font-medium">All</button>
                                        <span class="text-gray-300">|</span>
                                        <button type="button" id="btn-deselect-all-cabang"
                                            class="text-gray-500 hover:text-gray-700">None</button>
                                    </div>
                                </div>

                                <div class="relative mb-2">
                                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                        <i class="fa-solid fa-magnifying-glass text-gray-400 text-xs"></i>
                                    </div>
                                    <input type="text" id="search-cabang"
                                        class="bg-gray-50 border border-gray-300 text-gray-900 text-xs rounded-lg focus:ring-pink-500 focus:border-pink-500 block w-full pl-10 p-2"
                                        placeholder="Cari Kode, Nama, atau Alias...">
                                </div>

                                <div id="loading-cabang" class="hidden flex justify-center py-4">
                                    <div class="spinner-simple"></div>
                                </div>

                                <div class="multi-select-container p-2 space-y-1" id="container-cabang">
                                </div>
                            </div>

                            <div class="filter-card transition-all duration-300" id="step-supplier">
                                <div class="mb-3 flex justify-between items-center border-b border-gray-100 pb-2">
                                    <label class="text-sm font-bold text-gray-800 flex items-center gap-2">
                                        <span
                                            class="bg-pink-100 text-pink-600 w-6 h-6 rounded-full flex items-center justify-center text-xs">2</span>
                                        Pilih Supplier <span class="text-red-500">*</span>
                                    </label>
                                    <div class="flex gap-2 text-xs">
                                        <button type="button" id="btn-select-all-supp"
                                            class="text-pink-600 hover:text-pink-700 font-medium">All</button>
                                        <span class="text-gray-300">|</span>
                                        <button type="button" id="btn-deselect-all-supp"
                                            class="text-gray-500 hover:text-gray-700">None</button>
                                    </div>
                                </div>

                                <div class="relative mb-2">
                                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                        <i class="fa-solid fa-magnifying-glass text-gray-400 text-xs"></i>
                                    </div>
                                    <input type="text" id="search-supplier"
                                        class="bg-gray-50 border border-gray-300 text-gray-900 text-xs rounded-lg focus:ring-pink-500 focus:border-pink-500 block w-full pl-10 p-2"
                                        placeholder="Cari Kode atau Nama Supplier...">
                                </div>

                                <div id="loading-supplier" class="hidden flex justify-center py-4">
                                    <div class="spinner-simple"></div>
                                </div>

                                <div class="multi-select-container p-2 space-y-1 bg-gray-50" id="container-supplier">
                                    <div
                                        class="flex flex-col items-center justify-center h-full text-gray-400 text-xs gap-2">
                                        <i class="fas fa-store-slash fa-2x opacity-50"></i>
                                        <p>Pilih cabang terlebih dahulu</p>
                                    </div>
                                </div>
                                <div class="mt-2 text-right">
                                    <span id="supplier-counter"
                                        class="text-xs font-semibold text-gray-500 badge badge-warning">0 supplier
                                        dipilih</span>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-6">
                            <div class="filter-card sticky">
                                <div class="mb-4 border-b border-gray-100 pb-2">
                                    <label for="tgl_schedule"
                                        class="text-sm font-bold text-gray-800 flex items-center gap-2">
                                        <span
                                            class="bg-pink-100 text-pink-600 w-6 h-6 rounded-full flex items-center justify-center text-xs">3</span>
                                        Tanggal Jadwal SO <span class="text-red-500">*</span>
                                    </label>
                                </div>

                                <div class="bg-pink-50 p-4 rounded-lg border border-pink-100 mb-4">
                                    <div class="flex items-start gap-3">
                                        <i class="fas fa-info-circle text-pink-500 mt-1"></i>
                                        <div class="text-xs text-gray-700">
                                            <p class="font-semibold mb-1">Ketentuan:</p>
                                            <p>Minimal 3 hari dari tanggal hari ini.</p>
                                            <p class="mt-1">Default Minimum: <span id="default-date-display"
                                                    class="font-mono font-bold text-pink-700"></span></p>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-6">
                                    <input type="date" id="tgl_schedule" name="tgl_schedule"
                                        class="input-modern w-full text-sm" required>
                                </div>

                                <hr class="border-gray-100 my-4">

                                <button type="submit" id="btn-submit"
                                    class="btn-primary w-full py-3 flex items-center justify-center gap-2 text-base shadow-lg shadow-pink-200">
                                    <i class="fa-solid fa-save"></i>
                                    <span>Simpan Jadwal</span>
                                </button>
                            </div>
                        </div>

                    </div>
                </form>

            </div>
        </section>
    </main>

    <script src="/src/js/middleware_auth.js"></script>
    <script src="../../../js/shared/internal/sidebar-profile.js" defer></script>
    <script src="../../../js/laporan/jadwal_so/create_handler_jadwal_so.js" type="module"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>


</body>

</html>