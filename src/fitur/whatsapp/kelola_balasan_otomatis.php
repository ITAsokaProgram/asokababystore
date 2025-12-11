<?php
session_start();
include '../../../aa_kon_sett.php';
require_once __DIR__ . '/../../component/menu_handler.php';
$menuHandler = new MenuHandler('wa_balasan');
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
    <link rel="stylesheet" href="../../style/pink-theme.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        /* Modal Enhancements */
        .modal-content-large {
            max-width: 800px;
            width: 95%;
        }

        .modal-header-wa {
            background: linear-gradient(135deg, #059669 0%, #10b981 100%);
            color: white;
            padding: 1.25rem 1.5rem;
            border-radius: 0.75rem 0.75rem 0 0;
        }

        .form-label-required::after {
            content: '*';
            color: #ef4444;
            margin-left: 0.25rem;
        }

        .input-group {
            margin-bottom: 1.5rem;
        }

        .input-enhanced {
            transition: all 0.3s ease;
        }

        .input-enhanced:focus {
            border-color: #10b981;
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
            transform: translateY(-1px);
        }

        .textarea-counter {
            text-align: right;
            font-size: 0.75rem;
            color: #6b7280;
            margin-top: 0.25rem;
        }

        .modal-footer-enhanced {
            background: #f9fafb;
            padding: 1rem 1.5rem;
            border-top: 1px solid #e5e7eb;
            border-radius: 0 0 0.75rem 0.75rem;
        }

        .btn-save-enhanced {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            transition: all 0.3s ease;
        }

        .btn-save-enhanced:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(16, 185, 129, 0.4);
        }

        .badge-status {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            padding: 0.120rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .badge-aktif {
            background: #d1fae5;
            color: #065f46;
        }

        .badge-nonaktif {
            background: #fee2e2;
            color: #991b1b;
        }

        @media (max-width: 640px) {
            .modal-content-large {
                width: 100%;
                max-width: 100%;
                margin: 0;
                border-radius: 0;
            }

            .modal-header-wa {
                border-radius: 0;
            }
        }
    </style>
</head>

<body class="bg-gray-50">
    <?php include '../../component/navigation_report.php' ?>
    <?php include '../../component/sidebar_report.php' ?>
    <main id="main-content" class="flex-1 p-4 ml-64">
        <section class="min-h-screen">
            <div class="max-w-7xl mx-auto">
                <div class="header-card p-4 rounded-2xl mb-4">
                    <div class="flex items-center justify-between flex-wrap gap-3">
                        <div class="flex items-center gap-3">
                            <div class="icon-wrapper">
                                <i class="fa-brands fa-whatsapp fa-lg"></i>
                            </div>
                            <div>
                                <h1 class="text-xl font-bold text-gray-800 mb-1">Balasan Otomatis WhatsApp</h1>
                                <p class="text-xs text-gray-600">Kelola keyword dan respon otomatis bot WhatsApp</p>
                            </div>
                        </div>
                        <button id="btn-add-data" class="btn-primary">
                            <i class="fas fa-plus"></i>
                            <span>Tambah Keyword</span>
                        </button>
                    </div>
                </div>
                <div class="filter-card-simple">
                    <form id="filter-form" class="flex flex-col md:flex-row gap-3 items-end">
                        <div class="flex-1">
                            <label class="block text-xs font-semibold text-gray-700 mb-2">
                                <i class="fas fa-search mr-1"></i>
                                Cari Keyword
                            </label>
                            <input type="text" name="search_keyword" class="input-modern w-full"
                                placeholder="Ketik kata kunci untuk mencari...">
                        </div>
                        <button type="submit" class="btn-primary"
                            style="background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%);">
                            <i class="fas fa-filter"></i>
                            <span>Terapkan Filter</span>
                        </button>
                    </form>
                </div>
                <div class="filter-card">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-bold text-gray-800">
                            <i class="fas fa-list mr-2"></i>
                            Daftar Balasan
                            Otomatis
                        </h3>
                    </div>
                    <div class="table-container">
                        <table class="table-modern">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width: 60px;">No</th>
                                    <th style="width: 200px;">Kata Kunci</th>
                                    <th>Isi Balasan</th>
                                    <th class="text-center" style="width: 120px;">Status</th>
                                    <th class="text-center" style="width: 140px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="table-body">
                                <tr>
                                    <td colspan="5" class="text-center p-8">
                                        <div class="spinner-simple"></div>
                                        <p class="mt-3 text-gray-500">Memuat data...</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div id="pagination-container" class="mt-4"></div>
                </div>
            </div>
        </section>
    </main>
    <div id="modal-form" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog"
        aria-modal="true">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:p-0">
            <div class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity backdrop-blur-sm"
                id="modal-backdrop"></div>
            <div
                class="modal-content-large inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle">
                <form id="form-transaksi">
                    <div class="modal-header-wa">
                        <div class="flex justify-between items-center">
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-10 h-10 bg-white bg-opacity-20 rounded-lg flex items-center justify-center">
                                    <i class="fa-brands fa-whatsapp fa-lg"></i>
                                </div>
                                <div>
                                    <h3 class="text-lg font-bold" id="modal-title">Form Balasan Otomatis</h3>
                                    <p class="text-xs text-green-100 mt-0.5">Isi informasi keyword dan balasan</p>
                                </div>
                            </div>
                            <button type="button" id="btn-close-modal"
                                class="text-white hover:text-green-100 transition-colors">
                                <i class="fas fa-times fa-lg"></i>
                            </button>
                        </div>
                    </div>
                    <div class="p-6 space-y-5">
                        <input type="hidden" name="mode" id="form_mode" value="insert">
                        <input type="hidden" name="old_kata_kunci" id="old_kata_kunci">
                        <div class="input-group">
                            <label class="form-label-required block text-sm font-bold text-gray-700 mb-2">
                                <i class="fas fa-key mr-1"></i>
                                Kata Kunci (Unique)
                            </label>
                            <input type="text" name="kata_kunci" id="kata_kunci"
                                class="input-enhanced w-full px-4 py-2.5 border-2 border-gray-300 rounded-lg focus:outline-none text-sm"
                                required placeholder="Contoh: info, harga, lokasi, jam_buka">
                            <p class="text-xs text-gray-500 mt-2">
                                <i class="fas fa-info-circle mr-1"></i>
                                Bot akan merespon otomatis jika pesan mengandung kata kunci ini
                            </p>
                        </div>
                        <div class="input-group">
                            <label class="form-label-required block text-sm font-bold text-gray-700 mb-2">
                                <i class="fas fa-comment-dots mr-1"></i>
                                Isi Balasan
                            </label>
                            <textarea name="isi_balasan" id="isi_balasan" rows="8"
                                class="input-enhanced w-full px-4 py-2.5 border-2 border-gray-300 rounded-lg focus:outline-none text-sm resize-none"
                                required
                                placeholder="Tulis balasan otomatis yang akan dikirim...&#10;&#10;Tips: Gunakan emoji untuk membuat pesan lebih menarik! 😊"
                                maxlength="1000"></textarea>
                            <div class="textarea-counter">
                                <span id="char-count">0</span> / 1000 karakter
                            </div>
                        </div>
                        <div class="input-group">
                            <label class="block text-sm font-bold text-gray-700 mb-2">
                                <i class="fas fa-toggle-on mr-1"></i>
                                Status Aktif
                            </label>
                            <select name="status_aktif" id="status_aktif"
                                class="input-enhanced w-full px-4 py-2.5 border-2 border-gray-300 rounded-lg focus:outline-none text-sm">
                                <option value="1">✅ Aktif </option>
                                <option value="0">⛔ Tidak Aktif </option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer-enhanced">
                        <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-3">
                            <button type="button" id="btn-cancel"
                                class="w-full sm:w-auto px-6 py-2.5 border-2 border-gray-300 rounded-lg text-sm font-semibold text-gray-700 hover:bg-gray-50 transition-all">
                                <i class="fas fa-times mr-2"></i>
                                Batal
                            </button>
                            <button type="submit" id="btn-save"
                                class="btn-save-enhanced w-full sm:w-auto px-6 py-2.5 rounded-lg text-sm font-semibold text-white shadow-lg">
                                <i class="fas fa-save mr-2"></i>
                                Simpan Data
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="../../js/whatsapp/kelola_balasan_otomatis_handler.js" type="module"></script>
</body>

</html>