<?php
session_start();
include '../../../aa_kon_sett.php';
require_once __DIR__ . '/../../component/menu_handler.php';
$menuHandler = new MenuHandler('wa_flow');
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

        /* UBAH: Modal header dari ungu ke pink */
        .modal-header-wa {
            background: linear-gradient(135deg, #ec4899 0%, #db2777 100%);
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

        /* UBAH: Focus color ke pink */
        .input-enhanced:focus {
            border-color: #ec4899;
            box-shadow: 0 0 0 3px rgba(236, 72, 153, 0.1);
        }

        .modal-footer-enhanced {
            background: #f9fafb;
            padding: 1rem 1.5rem;
            border-top: 1px solid #e5e7eb;
            border-radius: 0 0 0.75rem 0.75rem;
        }

        /* UBAH: Button save ke pink */
        .btn-save-enhanced {
            background: linear-gradient(135deg, #ec4899 0%, #db2777 100%);
            transition: all 0.3s ease;
        }

        .btn-save-enhanced:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(236, 72, 153, 0.4);
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

        /* UBAH: Template area dari kuning ke pink soft */
        #template-area {
            background: linear-gradient(135deg, #fdf2f8 0%, #fce7f3 100%);
            border: 2px solid #f9a8d4;
            border-radius: 0.75rem;
            padding: 1rem;
            margin-bottom: 1.5rem;
        }

        #template-area .fa-magic {
            color: #ec4899;
            font-size: 1.5rem;
        }

        #template-area h4 {
            color: #be185d;
            font-weight: 700;
        }

        #template-area p {
            color: #9f1239;
        }

        /* UBAH: Button apply template ke pink */
        #btn-apply-template {
            background: linear-gradient(135deg, #ec4899 0%, #db2777 100%);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(236, 72, 153, 0.3);
        }

        #btn-apply-template:hover {
            background: linear-gradient(135deg, #db2777 0%, #be185d 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(236, 72, 153, 0.4);
        }

        #template-select {
            border: 2px solid #f9a8d4;
            border-radius: 0.5rem;
            padding: 0.5rem 1rem;
            background: white;
            font-weight: 500;
            color: #831843;
        }

        #template-select:focus {
            outline: none;
            border-color: #ec4899;
            box-shadow: 0 0 0 3px rgba(236, 72, 153, 0.1);
        }

        /* UBAH: Step container styling dengan pink theme */
        .step-item {
            position: relative;
            animation: fadeIn 0.3s ease;
        }

        .step-item>div {
            background: linear-gradient(135deg, #fef3f8 0%, #fce7f3 100%);
            border: 2px solid #fbcfe8;
            border-radius: 0.75rem;
            padding: 1rem;
            transition: all 0.3s ease;
        }

        .step-item:hover>div {
            border-color: #f9a8d4;
            box-shadow: 0 4px 16px rgba(236, 72, 153, 0.15);
        }

        .step-number {
            background: linear-gradient(135deg, #ec4899 0%, #db2777 100%);
            color: white;
            width: 28px;
            height: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            font-weight: 700;
            font-size: 0.875rem;
            box-shadow: 0 2px 8px rgba(236, 72, 153, 0.3);
        }

        /* UBAH: Type selector styling */
        .type-selector {
            border: 2px solid #f9a8d4;
            border-radius: 0.5rem;
            padding: 0.375rem 0.75rem;
            background: white;
            font-weight: 600;
            color: #831843;
            transition: all 0.2s ease;
        }

        .type-selector:focus {
            outline: none;
            border-color: #ec4899;
            box-shadow: 0 0 0 3px rgba(236, 72, 153, 0.1);
        }

        /* Step action buttons dengan pink theme */
        .btn-move-up,
        .btn-move-down {
            color: #9ca3af;
            transition: all 0.2s ease;
        }

        .btn-move-up:hover,
        .btn-move-down:hover {
            background: #fce7f3;
            color: #ec4899;
        }

        .btn-remove-step {
            color: #9ca3af;
            transition: all 0.2s ease;
        }

        .btn-remove-step:hover {
            background: #fee2e2;
            color: #ef4444;
        }

        /* Storage key area dengan pink accent */
        .storage-key-area {
            background: linear-gradient(135deg, #fdf2f8 0%, white 100%);
            border: 1px solid #fbcfe8;
            border-radius: 0.5rem;
            padding: 0.75rem;
            margin-bottom: 0.75rem;
        }

        .storage-key-area label {
            color: #be185d;
            font-weight: 600;
        }

        .input-storage-key {
            border: 2px solid #fbcfe8;
            transition: all 0.2s ease;
        }

        .input-storage-key:focus {
            border-color: #ec4899;
            box-shadow: 0 0 0 3px rgba(236, 72, 153, 0.1);
        }

        /* Add step button dengan pink theme */
        #btn-add-step {
            border: 2px dashed #f9a8d4;
            color: #ec4899;
            background: linear-gradient(135deg, #fef3f8 0%, white 100%);
            font-weight: 600;
            transition: all 0.3s ease;
        }

        #btn-add-step:hover {
            background: linear-gradient(135deg, #fce7f3 0%, #fdf2f8 100%);
            border-color: #ec4899;
            box-shadow: 0 4px 12px rgba(236, 72, 153, 0.15);
        }

        /* Info boxes dengan pink theme */
        .bg-blue-50 {
            background: linear-gradient(135deg, #fef3f8 0%, #fce7f3 100%) !important;
        }

        .border-blue-200 {
            border-color: #fbcfe8 !important;
        }

        .text-blue-900 {
            color: #831843 !important;
        }

        /* Yellow-ish info boxes jadi pink */
        .bg-yellow-50 {
            background: linear-gradient(135deg, #fef3f8 0%, #fce7f3 100%) !important;
        }

        .border-yellow-200 {
            border-color: #fbcfe8 !important;
        }

        .text-yellow-600,
        .text-yellow-700 {
            color: #be185d !important;
        }

        .text-yellow-800 {
            color: #831843 !important;
        }

        /* Icon wrapper pink theme */
        .icon-wrapper.bg-indigo-100 {
            background: linear-gradient(135deg, rgba(236, 72, 153, 0.15) 0%, rgba(244, 114, 182, 0.15) 100%) !important;
        }

        .icon-wrapper.text-indigo-600 {
            color: #ec4899 !important;
        }

        /* Button primary pink */
        .btn-primary.bg-indigo-600 {
            background: linear-gradient(135deg, #ec4899 0%, #db2777 100%) !important;
        }

        .btn-primary.bg-indigo-600:hover {
            background: linear-gradient(135deg, #db2777 0%, #be185d 100%) !important;
        }

        /* Input modern focus pink */
        .input-modern:focus {
            border-color: #ec4899;
            box-shadow: 0 0 0 4px rgba(236, 72, 153, 0.1);
        }

        /* Responsive improvements */
        @media (max-width: 768px) {
            .modal-content-large {
                width: 100%;
                margin: 0.5rem;
            }

            #template-area {
                flex-direction: column;
                text-align: center;
            }

            #template-area .flex.gap-2 {
                flex-direction: column;
                width: 100%;
            }

            #template-select,
            #btn-apply-template {
                width: 100%;
            }

            .step-item>div {
                padding: 0.75rem;
            }
        }

        /* Section divider */
        .section-divider {
            height: 2px;
            background: linear-gradient(90deg, transparent, #f9a8d4, transparent);
            margin: 1.5rem 0;
        }

        /* Helper text styling */
        .helper-text {
            font-size: 0.75rem;
            color: #9f1239;
            display: flex;
            align-items: center;
            gap: 0.25rem;
            margin-top: 0.25rem;
        }

        .helper-text i {
            color: #ec4899;
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
                                    <p class="text-xs text-pink-100 mt-0.5">Konfigurasi langkah dan logika flow</p>
                                </div>
                            </div>
                            <button type="button" id="btn-close-modal"
                                class="text-white hover:text-pink-200 transition-colors">
                                <i class="fas fa-times fa-lg"></i>
                            </button>
                        </div>
                    </div>

                    <div class="p-6 overflow-y-auto max-h-[75vh] custom-scrollbar">
                        <input type="hidden" name="mode" id="form_mode" value="insert">
                        <input type="hidden" name="id" id="data_id">

                        <div id="template-area">
                            <div class="flex items-center gap-3 mb-3">
                                <i class="fas fa-magic"></i>
                                <div class="flex-1">
                                    <h4 class="text-sm font-bold">Mulai dengan Template</h4>
                                    <p class="text-xs">Gunakan template siap pakai untuk kasus umum.</p>
                                </div>
                            </div>
                            <div class="flex gap-2">
                                <select id="template-select" class="flex-1">
                                    <option value="">-- Pilih Template --</option>
                                    <option value="promo_lokasi">🎯 Promo + Cari Cabang Terdekat</option>
                                    <!-- <option value="registrasi">📝 Registrasi Member Sederhana</option> -->
                                </select>
                                <button type="button" id="btn-apply-template">
                                    <i class="fas fa-check-circle mr-1"></i> Pakai
                                </button>
                            </div>
                        </div>

                        <div class="section-divider"></div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6 pb-4 border-b border-gray-200">
                            <div class="input-group">
                                <label class="form-label-required block text-sm font-bold text-gray-700 mb-1">
                                    <i class="fas fa-tag text-pink-500 mr-1"></i> Keyword Trigger
                                </label>
                                <input type="text" name="keyword" id="keyword"
                                    class="input-enhanced w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"
                                    placeholder="Contoh: VCR:PROMO" required>
                                <p class="helper-text"><i class="fas fa-info-circle"></i> Kata kunci yang akan memicu
                                    flow ini</p>
                            </div>
                            <div class="input-group">
                                <label class="block text-sm font-bold text-gray-700 mb-1">
                                    <i class="fas fa-align-left text-pink-500 mr-1"></i> Deskripsi Singkat
                                </label>
                                <input type="text" name="deskripsi" id="deskripsi"
                                    class="input-enhanced w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"
                                    placeholder="Contoh: Promo SGM">
                            </div>
                            <div class="input-group">
                                <label class="block text-sm font-bold text-gray-700 mb-1">
                                    <i class="fas fa-clock text-pink-500 mr-1"></i> Expired At (Opsional)
                                </label>
                                <input type="datetime-local" name="expired_at" id="expired_at"
                                    class="input-enhanced w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                            </div>
                            <div class="input-group">
                                <label class="block text-sm font-bold text-gray-700 mb-1">
                                    <i class="fas fa-globe text-pink-500 mr-1"></i> Max Global Usage
                                </label>
                                <input type="number" name="max_global_usage" id="max_global_usage" value="0"
                                    class="input-enhanced w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                            </div>
                            <div class="input-group">
                                <label class="block text-sm font-bold text-gray-700 mb-1">
                                    <i class="fas fa-user text-pink-500 mr-1"></i> Max User Usage
                                </label>
                                <input type="number" name="max_user_usage" id="max_user_usage" value="0"
                                    class="input-enhanced w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                            </div>
                            <div class="input-group">
                                <label class="block text-sm font-bold text-gray-700 mb-1">
                                    <i class="fas fa-toggle-on text-pink-500 mr-1"></i> Status Flow
                                </label>
                                <select name="status_aktif" id="status_aktif"
                                    class="input-enhanced w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                                    <option value="1">✅ Aktif</option>
                                    <option value="0">⛔ Tidak Aktif</option>
                                </select>
                            </div>
                        </div>

                        <div x-data="{ showErrors: false }" class="mb-6">
                            <button type="button" @click="showErrors = !showErrors"
                                class="text-xs font-bold text-pink-600 hover:text-pink-800 flex items-center gap-1 mb-2">
                                <i class="fas" :class="showErrors ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                                Konfigurasi Pesan Limit (Opsional)
                            </button>
                            <div x-show="showErrors"
                                class="grid grid-cols-1 md:grid-cols-2 gap-4 bg-gradient-to-br from-pink-50 to-white p-3 rounded-lg border border-pink-200">
                                <div>
                                    <label class="block text-xs font-bold text-pink-700 mb-1">Pesan Kuota Habis</label>
                                    <textarea name="pesan_habis" id="pesan_habis" rows="2"
                                        class="w-full text-sm border-pink-200 rounded px-2 py-1 focus:border-pink-400 focus:ring focus:ring-pink-200"></textarea>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-pink-700 mb-1">Pesan Sudah Klaim</label>
                                    <textarea name="pesan_sudah_klaim" id="pesan_sudah_klaim" rows="2"
                                        class="w-full text-sm border-pink-200 rounded px-2 py-1 focus:border-pink-400 focus:ring focus:ring-pink-200"></textarea>
                                </div>
                            </div>
                        </div>

                        <div>
                            <div class="flex justify-between items-end mb-3">
                                <label class="form-label-required block text-sm font-bold text-gray-700">
                                    <i class="fas fa-shoe-prints text-pink-500 mr-1"></i> Langkah Flow (Steps)
                                </label>
                                <div class="text-[10px] text-gray-500 bg-pink-50 px-2 py-1 rounded">
                                    <i class="fas fa-info-circle text-pink-500"></i> Dijalankan urut dari 1 ke akhir
                                </div>
                            </div>

                            <div id="steps-container" class="space-y-4 mb-4">
                            </div>

                            <button type="button" id="btn-add-step"
                                class="w-full py-2.5 text-sm font-bold flex items-center justify-center gap-2">
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