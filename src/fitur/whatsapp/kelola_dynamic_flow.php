<?php
session_start();
include '../../../aa_kon_sett.php';
require_once __DIR__ . '/../../component/menu_handler.php';
$menuHandler = new MenuHandler('wa_flow'); // Sesuaikan menu handler jika perlu
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Dynamic Flow</title>
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
        .modal-content-large {
            max-width: 900px;
            width: 95%;
        }

        .modal-header-wa {
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            /* Warna Indigo untuk Flow */
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
            margin-bottom: 1.0rem;
        }

        .input-enhanced:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }

        .modal-footer-enhanced {
            background: #f9fafb;
            padding: 1rem 1.5rem;
            border-top: 1px solid #e5e7eb;
            border-radius: 0 0 0.75rem 0.75rem;
        }

        .btn-save-enhanced {
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            transition: all 0.3s ease;
        }

        .btn-save-enhanced:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(99, 102, 241, 0.4);
        }

        .btn-save-enhanced:disabled {
            background: #9ca3af;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
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

        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #d1d5db;
            border-radius: 4px;
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
                            <div class="icon-wrapper bg-indigo-100 text-indigo-600 p-3 rounded-lg">
                                <i class="fa-solid fa-diagram-project fa-lg"></i>
                            </div>
                            <div>
                                <h1 class="text-xl font-bold text-gray-800 mb-1">Dynamic Flow Service</h1>
                                <p class="text-xs text-gray-600">Kelola alur percakapan interaktif, form input, dan
                                    logika bertingkat</p>
                            </div>
                        </div>
                        <button id="btn-add-data" class="btn-primary bg-indigo-600 hover:bg-indigo-700">
                            <i class="fas fa-plus"></i> <span>Buat Flow Baru</span>
                        </button>
                    </div>
                </div>

                <div class="filter-card-simple">
                    <form id="filter-form" class="flex flex-col md:flex-row gap-3 items-end">
                        <div class="flex-1">
                            <label class="block text-xs font-semibold text-gray-700 mb-2">
                                <i class="fas fa-search mr-1"></i> Cari Keyword / Deskripsi
                            </label>
                            <input type="text" name="search_keyword" class="input-modern w-full"
                                placeholder="Cari keyword flow...">
                        </div>
                        <button type="submit" class="btn-primary bg-gray-600 hover:bg-gray-700">
                            <i class="fas fa-filter"></i> <span>Filter</span>
                        </button>
                    </form>
                </div>

                <div class="filter-card">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-bold text-gray-800">
                            <i class="fas fa-list mr-2"></i> Daftar Flow
                        </h3>
                    </div>
                    <div class="table-container">
                        <table class="table-modern">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width: 50px;">No</th>
                                    <th style="width: 180px;">Trigger Keyword</th>
                                    <th>Deskripsi Flow</th>
                                    <th class="text-center">Total Steps</th>
                                    <th class="text-center">Limit/Exp</th>
                                    <th class="text-center" style="width: 100px;">Status</th>
                                    <th class="text-center" style="width: 120px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="table-body">
                                <tr>
                                    <td colspan="7" class="text-center p-8">
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

    <div id="modal-form" class="fixed inset-0 z-50 hidden overflow-y-auto" role="dialog" aria-modal="true">
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
                                    <i class="fa-solid fa-diagram-project fa-lg"></i>
                                </div>
                                <div>
                                    <h3 class="text-lg font-bold" id="modal-title">Form Dynamic Flow</h3>
                                    <p class="text-xs text-indigo-100 mt-0.5">Konfigurasi langkah dan logika flow</p>
                                </div>
                            </div>
                            <button type="button" id="btn-close-modal"
                                class="text-white hover:text-indigo-200 transition-colors">
                                <i class="fas fa-times fa-lg"></i>
                            </button>
                        </div>
                    </div>

                    <div class="p-6 overflow-y-auto max-h-[75vh] custom-scrollbar">
                        <input type="hidden" name="mode" id="form_mode" value="insert">
                        <input type="hidden" name="id" id="data_id">

                        <div id="template-area"
                            class="mb-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg flex flex-col sm:flex-row items-center justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <i class="fas fa-magic text-yellow-600 text-xl"></i>
                                <div>
                                    <h4 class="text-sm font-bold text-gray-800">Bingung mulai darimana?</h4>
                                    <p class="text-xs text-gray-600">Gunakan template siap pakai untuk kasus umum.</p>
                                </div>
                            </div>
                            <div class="flex gap-2">
                                <select id="template-select"
                                    class="text-sm border-gray-300 rounded-lg px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500">
                                    <option value="">-- Pilih Template --</option>
                                    <option value="promo_lokasi">🎯 Promo + Cari Cabang Terdekat</option>
                                    <option value="registrasi">📝 Registrasi Member Sederhana</option>
                                </select>
                                <button type="button" id="btn-apply-template"
                                    class="bg-yellow-500 hover:bg-yellow-600 text-white text-xs font-bold py-2 px-4 rounded-lg transition-colors">
                                    Pakai
                                </button>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6 border-b border-gray-200 pb-4">
                            <div class="input-group">
                                <label class="form-label-required block text-sm font-bold text-gray-700 mb-1">Keyword
                                    Trigger</label>
                                <input type="text" name="keyword" id="keyword"
                                    class="input-enhanced w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"
                                    placeholder="Contoh: VCR:PROMO" required>
                            </div>
                            <div class="input-group">
                                <label class="block text-sm font-bold text-gray-700 mb-1">Deskripsi Singkat</label>
                                <input type="text" name="deskripsi" id="deskripsi"
                                    class="input-enhanced w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"
                                    placeholder="Contoh: Promo SGM">
                            </div>
                            <div class="input-group">
                                <label class="block text-sm font-bold text-gray-700 mb-1">Expired At (Opsional)</label>
                                <input type="datetime-local" name="expired_at" id="expired_at"
                                    class="input-enhanced w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                            </div>
                            <div class="input-group">
                                <label class="block text-sm font-bold text-gray-700 mb-1">Max Global Usage</label>
                                <input type="number" name="max_global_usage" id="max_global_usage" value="0"
                                    class="input-enhanced w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                            </div>
                            <div class="input-group">
                                <label class="block text-sm font-bold text-gray-700 mb-1">Max User Usage</label>
                                <input type="number" name="max_user_usage" id="max_user_usage" value="0"
                                    class="input-enhanced w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                            </div>
                            <div class="input-group">
                                <label class="block text-sm font-bold text-gray-700 mb-1">Status Flow</label>
                                <select name="status_aktif" id="status_aktif"
                                    class="input-enhanced w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                                    <option value="1">✅ Aktif</option>
                                    <option value="0">⛔ Tidak Aktif</option>
                                </select>
                            </div>
                        </div>

                        <div x-data="{ showErrors: false }" class="mb-6">
                            <button type="button" @click="showErrors = !showErrors"
                                class="text-xs font-bold text-indigo-600 hover:text-indigo-800 flex items-center gap-1 mb-2">
                                <i class="fas" :class="showErrors ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                                Konfigurasi Pesan Limit (Opsional)
                            </button>
                            <div x-show="showErrors"
                                class="grid grid-cols-1 md:grid-cols-2 gap-4 bg-gray-50 p-3 rounded-lg border border-gray-200">
                                <div>
                                    <label class="block text-xs font-bold text-gray-600 mb-1">Pesan Kuota Habis</label>
                                    <textarea name="pesan_habis" id="pesan_habis" rows="2"
                                        class="w-full text-sm border-gray-300 rounded px-2 py-1"></textarea>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-600 mb-1">Pesan Sudah Klaim</label>
                                    <textarea name="pesan_sudah_klaim" id="pesan_sudah_klaim" rows="2"
                                        class="w-full text-sm border-gray-300 rounded px-2 py-1"></textarea>
                                </div>
                            </div>
                        </div>

                        <div>
                            <div class="flex justify-between items-end mb-2">
                                <label class="form-label-required block text-sm font-bold text-gray-700">
                                    <i class="fas fa-shoe-prints mr-1"></i> Langkah Flow (Steps)
                                </label>
                                <div class="text-[10px] text-gray-500">Step dijalankan urut dari 1 sampai akhir.</div>
                            </div>

                            <div id="steps-container" class="space-y-4 mb-4">
                            </div>

                            <button type="button" id="btn-add-step"
                                class="w-full py-2.5 border-2 border-dashed border-indigo-400 text-indigo-600 rounded-lg hover:bg-indigo-50 transition-colors text-sm font-bold flex items-center justify-center gap-2">
                                <i class="fas fa-plus-circle"></i> Tambah Langkah (Step)
                            </button>
                        </div>
                    </div>

                    <div class="modal-footer-enhanced">
                        <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-3">
                            <button type="button" id="btn-cancel"
                                class="w-full sm:w-auto px-6 py-2.5 border-2 border-gray-300 rounded-lg text-sm font-semibold text-gray-700 hover:bg-gray-50 transition-all">
                                <i class="fas fa-times mr-2"></i> Batal
                            </button>
                            <button type="submit" id="btn-save"
                                class="btn-save-enhanced w-full sm:w-auto px-6 py-2.5 rounded-lg text-sm font-semibold text-white shadow-lg">
                                <i class="fas fa-save mr-2"></i> Simpan Flow
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="../../js/whatsapp/kelola_dynamic_flow_handler.js" type="module"></script>
</body>

</html>