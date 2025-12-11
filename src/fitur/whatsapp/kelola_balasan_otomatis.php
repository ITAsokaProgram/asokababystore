<?php
session_start();
include '../../../aa_kon_sett.php';

// Cek sesi login standar (sesuaikan dengan sistem auth kamu)
require_once __DIR__ . '/../../component/menu_handler.php';
$menuHandler = new MenuHandler('wa_balasan'); // Pastikan key menu sesuai database
// if (!$menuHandler->initialize()) { exit(); } // Uncomment jika menu handler sudah setup
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Balasan Otomatis</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/all.min.css">
    <link rel="stylesheet" href="../../style/header.css">
    <link rel="stylesheet" href="../../style/sidebar.css">
    <link rel="stylesheet" href="../../style/animation-fade-in.css">
    <link rel="stylesheet" href="../../style/default-font.css">
    <link rel="stylesheet" href="../../output2.css">
  <link rel="icon" type="image/png" href="../../../public/images/logo1.png">

    <link rel="stylesheet" href="../../style/pink-theme.css"> <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="bg-gray-50">
    <?php include '../../component/navigation_report.php' ?>
    <?php include '../../component/sidebar_report.php' ?>

    <main id="main-content" class="flex-1 p-4 ml-64">
        <section class="min-h-screen">
            <div class="max-w-7xl mx-auto">
                
                <div class="header-card p-4 rounded-2xl mb-4 bg-white shadow-sm border border-gray-200">
                    <div class="flex items-center justify-between flex-wrap gap-3">
                        <div class="flex items-center gap-3">
                            <div class="icon-wrapper bg-green-100 p-3 rounded-xl text-green-600">
                                <i class="fa-brands fa-whatsapp fa-lg"></i>
                            </div>
                            <div>
                                <h1 class="text-xl font-bold text-gray-800 mb-1">Balasan Otomatis WA</h1>
                                <p class="text-xs text-gray-600">Kelola keyword dan respon otomatis bot.</p>
                            </div>
                        </div>
                        <button id="btn-add-data" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium inline-flex items-center justify-center gap-2 transition-all shadow-sm">
                            <i class="fas fa-plus"></i>
                            <span>Tambah Keyword</span>
                        </button>
                    </div>
                </div>

                <div class="filter-card bg-white p-4 rounded-xl shadow-sm border border-gray-200 mb-4">
                    <form id="filter-form" class="flex flex-col md:flex-row gap-3 items-end">
                        <div class="w-full md:w-1/3">
                            <label class="block text-xs font-semibold text-gray-700 mb-2">Cari Keyword</label>
                            <div class="relative">
                                <input type="text" name="search_keyword" class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500" placeholder="Ketik kata kunci...">
                                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                            </div>
                        </div>
                        <button type="submit" class="bg-gray-800 hover:bg-gray-900 text-white px-4 py-2 rounded-lg text-sm font-medium transition-all">
                            <i class="fas fa-filter mr-1"></i> Terapkan
                        </button>
                    </form>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="p-4 border-b border-gray-100 flex justify-between items-center">
                        <h3 class="text-lg font-bold text-gray-800">Daftar Balasan</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-center w-16">No</th>
                                    <th class="px-6 py-3 w-1/4">Kata Kunci</th>
                                    <th class="px-6 py-3">Isi Balasan</th>
                                    <th class="px-6 py-3 text-center w-24">Status</th>
                                    <th class="px-6 py-3 text-center w-32">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="table-body">
                                </tbody>
                        </table>
                    </div>
                    <div id="pagination-container" class="p-4 border-t border-gray-100"></div>
                </div>

            </div>
        </section>
    </main>

    <div id="modal-form" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" id="modal-backdrop"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form id="form-transaksi">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="flex justify-between items-center mb-4 border-b pb-2">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Form Balasan</h3>
                            <button type="button" id="btn-close-modal" class="text-gray-400 hover:text-gray-500">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>

                        <input type="hidden" name="mode" id="form_mode" value="insert">
                        <input type="hidden" name="old_kata_kunci" id="old_kata_kunci">

                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">Kata Kunci (Unik)</label>
                                <input type="text" name="kata_kunci" id="kata_kunci" class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none" required placeholder="Contoh: loker">
                                <p class="text-[10px] text-gray-500 mt-1">*Sistem akan merespon jika chat mengandung kata ini.</p>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">Isi Balasan</label>
                                <textarea name="isi_balasan" id="isi_balasan" rows="6" class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none" required placeholder="Halo kak..."></textarea>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">Status</label>
                                <select name="status_aktif" id="status_aktif" class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 outline-none">
                                    <option value="1">Aktif</option>
                                    <option value="0">Tidak Aktif</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-2">
                        <button type="submit" id="btn-save" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none sm:w-auto sm:text-sm">
                            <i class="fas fa-save mr-2 mt-1"></i> Simpan
                        </button>
                        <button type="button" id="btn-cancel" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:w-auto sm:text-sm">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

  <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <script src="../../js/whatsapp/kelola_balasan_otomatis_handler.js" type="module"></script>
</body>
</html>