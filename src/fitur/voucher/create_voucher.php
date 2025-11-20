<?php
session_start();
include '../../../aa_kon_sett.php';
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

        /* Custom scrollbar */
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
                                <h1 class="text-xl font-bold text-gray-800 mb-1">Buat Voucher Baru</h1>
                                <p class="text-xs text-gray-600">Generate kode voucher otomatis untuk banyak store.</p>
                            </div>
                        </div>
                        <a href="index.php"
                            class="btn-secondary inline-flex items-center justify-center gap-2 no-underline">
                            <i class="fa-solid fa-arrow-left"></i>
                            <span>Kembali</span>
                        </a>
                    </div>
                </div>

                <form id="formBuatVoucher">
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">

                        <div class="lg:col-span-1 space-y-6">
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

                                <div id="loading-cabang" class="hidden flex justify-center py-4">
                                    <div class="spinner-simple"></div>
                                </div>

                                <div class="multi-select-container p-2 space-y-1" id="container-cabang">
                                </div>
                                <div class="mt-2 text-right">
                                    <span id="store-counter"
                                        class="text-xs font-semibold text-gray-500 badge badge-warning">0 dipilih</span>
                                </div>
                            </div>
                        </div>

                        <div class="lg:col-span-2 space-y-6">
                            <div class="filter-card">
                                <div class="mb-4 border-b border-gray-100 pb-2">
                                    <label class="text-sm font-bold text-gray-800 flex items-center gap-2">
                                        <span
                                            class="bg-pink-100 text-pink-600 w-6 h-6 rounded-full flex items-center justify-center text-xs">2</span>
                                        Detail Voucher <span class="text-red-500">*</span>
                                    </label>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-700 mb-1">Pemilik
                                            Voucher</label>
                                        <input type="text" id="pemilik" name="pemilik"
                                            class="input-modern w-full uppercase" placeholder="CONTOH: CHIL KID"
                                            required>
                                        <p class="text-[10px] text-gray-400 mt-1">Akan dikonversi ke HURUF BESAR
                                            otomatis.</p>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-700 mb-1">Nilai Voucher
                                            (Rp)</label>
                                        <div class="relative">
                                            <input type="number" id="nilai" name="nilai"
                                                class="input-modern w-full pl-8" placeholder="0" min="0" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-700 mb-1">Tanggal Mulai
                                            Berlaku</label>
                                        <input type="date" id="tgl_awal" name="tgl_awal" class="input-modern w-full"
                                            required>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-700 mb-1">Tanggal Akhir
                                            Berlaku</label>
                                        <input type="date" id="tgl_akhir" name="tgl_akhir" class="input-modern w-full"
                                            required>
                                    </div>
                                </div>

                                <hr class="border-gray-100 my-4">

                                <div class="mb-2">
                                    <label class="text-sm font-bold text-gray-800 flex items-center gap-2">
                                        <span
                                            class="bg-pink-100 text-pink-600 w-6 h-6 rounded-full flex items-center justify-center text-xs">3</span>
                                        Konfigurasi Kode Voucher <span class="text-red-500">*</span>
                                    </label>
                                </div>

                                <div class="bg-pink-50 p-4 rounded-lg border border-pink-100 mb-6">
                                    <div class="flex flex-col gap-1">
                                        <span class="text-xs font-semibold text-pink-700">Preview Pola Kode:</span>

                                        <div class="text-lg font-mono font-bold text-gray-800 flex items-center flex-wrap gap-1"
                                            id="preview-code">

                                            <span
                                                class="bg-white px-2 py-1 rounded border border-gray-200 text-gray-500"
                                                title="Otomatis sesuai Cabang">
                                                [ALIAS]
                                            </span>

                                            <span class="text-gray-400 font-bold">-</span>

                                            <span
                                                class="bg-white px-2 py-1 rounded border border-pink-200 text-pink-600"
                                                id="prev-manual">
                                                NAMA
                                            </span>

                                            <span class="text-gray-400 font-bold">-</span>

                                            <span
                                                class="bg-white px-2 py-1 rounded border border-blue-200 text-blue-600"
                                                id="prev-number">
                                                00001
                                            </span>

                                        </div>
                                        <p class="text-[10px] text-gray-500 mt-1 italic">
                                            *Contoh hasil: <b>ADET-ASOKA-00055</b> (jika Alias=ADET, Nama=ASOKA, No=55)
                                        </p>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-700 mb-1">Input Kode
                                            Manual</label>
                                        <input type="text" id="kode_manual" name="kode_manual"
                                            class="input-modern w-full uppercase" placeholder="Ex: 20K" required>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-700 mb-1">Mulai Nomor
                                            Urut</label>
                                        <input type="number" id="start_number" name="start_number"
                                            class="input-modern w-full" value="1" min="0" required>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-700 mb-1">Jumlah
                                            Generate</label>
                                        <input type="number" id="qty" name="qty" class="input-modern w-full" value="1"
                                            min="1" required>
                                    </div>
                                </div>

                                <button type="submit" id="btn-submit"
                                    class="btn-primary w-full py-3 flex items-center justify-center gap-2 text-base shadow-lg shadow-pink-200">
                                    <i class="fa-solid fa-wand-magic-sparkles"></i>
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
    <script src="../../js/voucher/create_handler_voucher.js" type="module"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

</body>

</html>