<?php
session_start();
include '../../../aa_kon_sett.php';

require_once __DIR__ . '/../../component/menu_handler.php';
// $menuHandler = new MenuHandler('return_create'); 
// if (!$menuHandler->initialize()) { exit(); }
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Input Return Baru</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/all.min.css">
    <link rel="stylesheet" href="../../style/header.css">
    <link rel="stylesheet" href="../../style/sidebar.css">
    <link rel="stylesheet" href="../../style/animation-fade-in.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../style/default-font.css">
    <link rel="stylesheet" href="../../output2.css">
    <link rel="stylesheet" href="../../style/pink-theme.css">

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">

    <style>
        /* Style untuk Autocomplete Dropdown */
        .autocomplete-items {
            position: absolute;
            border: 1px solid #d4d4d4;
            border-bottom: none;
            border-top: none;
            z-index: 99;
            top: 100%;
            left: 0;
            right: 0;
            background-color: white;
            border-radius: 0 0 0.5rem 0.5rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            max-height: 200px;
            overflow-y: auto;
        }

        .autocomplete-items div {
            padding: 10px;
            cursor: pointer;
            background-color: #fff;
            border-bottom: 1px solid #d4d4d4;
            font-size: 0.875rem;
        }

        .autocomplete-items div:hover {
            background-color: #fce7f3;
            /* Pink-100 */
        }
    </style>
</head>

<body class="bg-gray-50">

    <?php include '../../component/navigation_report.php'; ?>
    <?php include '../../component/sidebar_report.php'; ?>

    <main id="main-content" class="flex-1 p-4 ml-64">
        <section class="min-h-screen">
            <div class="max-w-4xl mx-auto">

                <div class="header-card p-4 rounded-2xl mb-6">
                    <div class="flex items-center justify-between flex-wrap gap-3">
                        <div class="flex items-center gap-3">
                            <div class="icon-wrapper">
                                <i class="fa-solid fa-rotate-left fa-lg"></i>
                            </div>
                            <div>
                                <h1 class="text-xl font-bold text-gray-800 mb-1">Input Return</h1>
                                <p class="text-xs text-gray-600">Buat data pengembalian baru.</p>
                            </div>
                        </div>
                        <a href="index.php"
                            class="btn-secondary inline-flex items-center justify-center gap-2 no-underline">
                            <i class="fa-solid fa-arrow-left"></i>
                            <span>Kembali</span>
                        </a>
                    </div>
                </div>

                <form id="formReturn" class="filter-card">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Tanggal Return <span
                                    class="text-red-500">*</span></label>
                            <input type="date" name="tgl_return" id="tgl_return" class="input-modern w-full" required
                                value="<?= date('Y-m-d') ?>">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Nomor Faktur <span
                                    class="text-red-500">*</span></label>
                            <input type="text" name="no_faktur" id="no_faktur" class="input-modern w-full uppercase"
                                placeholder="Contoh: 1900-RT..." required>
                        </div>

                        <div class="relative">
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Kode Supplier <span
                                    class="text-red-500">*</span></label>
                            <div class="relative">
                                <input type="text" name="kode_supp" id="kode_supp" class="input-modern w-full uppercase"
                                    placeholder="Ketik Kode (Misal: A046)..." required autocomplete="off">
                                <div id="kode_supp_list" class="autocomplete-items hidden"></div>
                            </div>
                            <p class="text-[10px] text-gray-500 mt-1">Ketik untuk mencari suggestion (Kode - Nama).</p>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Nama Supplier</label>
                            <input type="text" name="nama_supplier" id="nama_supplier"
                                class="input-modern w-full uppercase" placeholder="Nama PT/Supplier">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Total Return (Rp)</label>
                            <input type="text" name="total_return_display" id="total_return_display"
                                class="input-modern w-full" placeholder="0" onkeyup="formatCurrency(this)">
                            <input type="hidden" name="total_return" id="total_return">
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Keterangan</label>
                            <textarea name="keterangan" id="keterangan" class="input-modern w-full h-24"
                                placeholder="Alasan return atau keterangan tambahan..."></textarea>
                        </div>

                    </div>

                    <div class="mt-6 border-t border-gray-100 pt-4">
                        <button type="submit" id="btn-submit"
                            class="btn-primary w-full py-3 flex items-center justify-center gap-2 text-base shadow-lg shadow-pink-200">
                            <i class="fa-solid fa-save"></i>
                            <span>Simpan Return</span>
                        </button>
                    </div>
                </form>

            </div>
        </section>
    </main>

    <script src="/src/js/middleware_auth.js"></script>
    <script src="../../js/return/create_handler.js" type="module"></script>

    <script>
        // Simple currency formatter untuk UI
        function formatCurrency(input) {
            let value = input.value.replace(/[^0-9]/g, '');
            if (value) {
                let formatted = new Intl.NumberFormat('id-ID').format(value);
                input.value = formatted;
                document.getElementById('total_return').value = value;
            } else {
                input.value = '';
                document.getElementById('total_return').value = 0;
            }
        }
    </script>
</body>

</html>