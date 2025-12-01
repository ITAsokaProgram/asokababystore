<?php
session_start();
include '../../../aa_kon_sett.php';

require_once __DIR__ . '/../../component/menu_handler.php';
$menuHandler = new MenuHandler('voucher_create');

if (!$menuHandler->initialize()) {
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buat Voucher Baru</title>

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

    <?php include '../../component/navigation_report.php'; ?>
    <?php include '../../component/sidebar_report.php'; ?>

    <main id="main-content" class="flex-1 p-4 ml-64">
        <section class="min-h-screen">
            <div class="max-w-6xl mx-auto">

                <div class="header-card p-4 rounded-2xl mb-6">
                    <div class="flex items-center justify-between flex-wrap gap-3">
                        <div class="flex items-center gap-3">
                            <div class="icon-wrapper">
                                <i class="fa-solid fa-ticket fa-lg"></i>
                            </div>
                            <div>
                                <h1 class="text-xl font-bold text-gray-800 mb-1">Generate Voucher</h1>
                                <p class="text-xs text-gray-600">Buat voucher.</p>
                            </div>
                        </div>
                        <a href="index.php"
                            class="btn-secondary inline-flex items-center justify-center gap-2 no-underline">
                            <i class="fa-solid fa-arrow-left"></i>
                            <span>Kembali</span>
                        </a>
                    </div>
                </div>

                <form id="formVoucher">
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">

                        <div class="space-y-6 lg:col-span-1">
                            <div class="filter-card h-full">
                                <div class="mb-3 flex justify-between items-center border-b border-gray-100 pb-2">
                                    <label class="text-sm font-bold text-gray-800 flex items-center gap-2">
                                        <span
                                            class="bg-pink-100 text-pink-600 w-6 h-6 rounded-full flex items-center justify-center text-xs">1</span>
                                        Target Cabang <span class="text-red-500">*</span>
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
                                <div class="mt-2 text-right">
                                    <span id="store-counter"
                                        class="text-xs font-semibold text-gray-500 badge badge-warning">0 toko
                                        dipilih</span>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-6 lg:col-span-2">
                            <div class="filter-card">
                                <div class="mb-4 border-b border-gray-100 pb-2">
                                    <label class="text-sm font-bold text-gray-800 flex items-center gap-2">
                                        <span
                                            class="bg-pink-100 text-pink-600 w-6 h-6 rounded-full flex items-center justify-center text-xs">2</span>
                                        Detail & Konfigurasi Voucher
                                    </label>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">

                                    <div class="col-span-2 grid grid-cols-3 gap-4">
                                        <div class="col-span-2">
                                            <label class="block text-xs font-semibold text-gray-700 mb-1">Nama Manual
                                                (Max 12)
                                                <span class="text-red-500">*</span></label>
                                            <input type="text" name="nama_voucher_manual" id="nama_voucher_manual"
                                                class="input-modern w-full uppercase rounded-md" maxlength="12"
                                                placeholder="CKD20K25" required>
                                        </div>
                                        <div class="col-span-1">
                                            <label class="block text-xs font-semibold text-gray-700 mb-1">No. Urut (5
                                                Digit)
                                                <span class="text-red-500">*</span></label>
                                            <input type="number" name="nomor_urut" id="nomor_urut"
                                                class="input-modern w-full text-center" max="99999" maxlength="5"
                                                placeholder="00001" required>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-700 mb-1">Jumlah Voucher
                                            (Qty) <span class="text-red-500">*</span></label>
                                        <input type="number" name="jumlah_voucher" id="jumlah_voucher"
                                            class="input-modern w-full" min="1" value="1" required>
                                    </div>

                                    <div>
                                        <label class="block text-xs font-semibold text-gray-700 mb-1">Nilai Voucher (Rp)
                                            <span class="text-red-500">*</span></label>
                                        <input type="number" name="nilai_voucher" id="nilai_voucher"
                                            class="input-modern w-full" min="0" placeholder="20000" required>
                                    </div>
                                </div>
                                <div id="preview-container"
                                    class="hidden mb-4 p-3 bg-gradient-to-r from-pink-50 to-white border border-pink-200 rounded-lg shadow-sm">
                                    <div class="flex items-start gap-3">
                                        <div class="mt-1 text-pink-500">
                                            <i class="fa-solid fa-eye"></i>
                                        </div>
                                        <div>
                                            <p class="text-xs font-bold text-gray-700">Preview Kode Voucher:</p>
                                            <p class="text-[10px] text-gray-500 mb-1">Contoh hasil berdasarkan Toko &
                                                Input saat ini:</p>
                                            <div class="font-mono text-lg font-bold text-pink-600 tracking-wider"
                                                id="preview-text">
                                                -
                                            </div>
                                            <p id="preview-store-name" class="text-[10px] text-gray-400 italic mt-1">
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-700 mb-1">Tanggal Mulai
                                            Berlaku <span class="text-red-500">*</span></label>
                                        <input type="date" name="tgl_mulai" id="tgl_mulai" class="input-modern w-full"
                                            required>
                                    </div>

                                    <div>
                                        <label class="block text-xs font-semibold text-gray-700 mb-1">Tanggal Berakhir
                                            (Expired) <span class="text-red-500">*</span></label>
                                        <input type="date" name="tgl_akhir" id="tgl_akhir" class="input-modern w-full"
                                            required>
                                    </div>
                                </div>

                                <div class="mb-6">
                                    <label class="block text-xs font-semibold text-gray-700 mb-1">Pemilik Voucher <span
                                            class="text-red-500">*</span></label>
                                    <input type="text" name="pemilik" id="pemilik" class="input-modern w-full uppercase"
                                        placeholder="" required>
                                    <p class="text-[10px] text-gray-500 mt-1">*Akan otomatis dikonversi menjadi HURUF
                                        KAPITAL.</p>
                                </div>

                                <hr class="border-gray-100 my-4">

                                <button type="submit" id="btn-submit"
                                    class="btn-primary w-full py-3 flex items-center justify-center gap-2 text-base shadow-lg shadow-pink-200">
                                    <i class="fa-solid fa-save"></i>
                                    <span>Generate Voucher</span>
                                </button>
                            </div>
                        </div>

                    </div>
                </form>

            </div>
        </section>
    </main>

    <script src="/src/js/middleware_auth.js"></script>
    <script src="../../js/shared/internal/sidebar-profile.js" defer></script>
    <script src="../../js/voucher/create_handler.js" type="module"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</body>

</html>