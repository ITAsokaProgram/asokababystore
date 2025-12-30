<?php
session_start();
include '../../../aa_kon_sett.php';
require_once __DIR__ . '/../../component/menu_handler.php';
// Sesuaikan permission menu Anda, misal: 'buku_besar_input'
// $menuHandler = new MenuHandler('buku_besar_input');
// if (!$menuHandler->initialize()) {
//     exit();
// }
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Input Buku Besar</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/all.min.css">
    <link rel="stylesheet" href="../../style/header.css">
    <link rel="stylesheet" href="../../style/sidebar.css">
    <link rel="stylesheet" href="../../output2.css">
    <link rel="stylesheet" href="../../style/pink-theme.css">
    <link rel="icon" type="image/png" href="../../../public/images/logo1.png">

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>

    <style>
        .input-row-container {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 0.75rem;
            padding: 1rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }

        .form-grid {
            display: grid;
            gap: 15px;
            align-items: end;
            grid-template-columns: 1fr;
        }

        @media (min-width: 640px) {
            .form-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (min-width: 1024px) {
            .form-grid {
                grid-template-columns: repeat(4, 1fr);
            }
        }

        /* Adjusted cols */

        .form-label {
            font-size: 0.75rem;
            font-weight: 600;
            color: #374151;
            margin-bottom: 0.25rem;
            display: block;
        }

        .input-compact {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            font-size: 0.875rem;
        }

        .input-compact:focus {
            outline: none;
            border-color: #ec4899;
            box-shadow: 0 0 0 3px rgba(236, 72, 153, 0.1);
        }

        .input-readonly {
            background-color: #f9fafb;
            color: #6b7280;
            cursor: default;
        }

        .table-compact th {
            padding: 0.75rem 1rem;
            font-size: 0.75rem;
            text-transform: uppercase;
            background-color: #fdf2f8;
            color: #831843;
            font-weight: 700;
        }

        .table-compact td {
            padding: 0.75rem 1rem;
            font-size: 0.875rem;
            border-bottom: 1px solid #f3f4f6;
        }

        .cell-merged {
            vertical-align: top;
            background-color: #fff;
            border-right: 5px solid #f3f4f6;
        }

        .row-group-start td {
            border-top: 5px solid #e5e7eb;
        }
    </style>
</head>

<body class="bg-gray-50">

    <?php include '../../component/navigation_report.php'; ?>
    <?php include '../../component/sidebar_report.php'; ?>

    <main id="main-content" class="flex-1 p-4 ml-64">
        <section class="min-h-screen">
            <div class="max-w-[1600px] mx-auto">

                <div class="header-card p-4 rounded-2xl bg-white shadow-sm flex gap-4 items-center">
                    <div class="flex items-center gap-3">
                        <div class="icon-wrapper bg-pink-100 text-pink-600 p-2 rounded-lg">
                            <i class="fa-solid fa-book-open fa-lg"></i>
                        </div>
                        <div>
                            <h1 class="text-xl font-bold text-gray-800">Buku Besar</h1>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <a href="laporan_buku_besar.php"
                            class="btn-report flex items-center gap-2 px-3 py-2 shadow-sm decoration-0 rounded">
                            <i class="fas fa-file-invoice"></i> <span>Lihat Laporan</span>
                        </a>
                        <button type="button" id="btn-export"
                            class="btn-export flex items-center gap-2 px-3 py-2 shadow-sm decoration-0 text-green-600 bg-green-50 border border-green-200 hover:bg-green-100 rounded text-sm font-medium">
                            <i class="fas fa-file-excel"></i> <span>Export</span>
                        </button>

                        <button type="button" id="btn-import"
                            class="btn-import flex items-center gap-2 px-3 py-2 shadow-sm decoration-0 text-blue-600 bg-blue-50 border border-blue-200 hover:bg-blue-100 rounded text-sm font-medium">
                            <i class="fas fa-cloud-upload-alt"></i> <span>Import</span>
                        </button>

                        <input type="file" id="file_import" accept=".xlsx, .xls" class="hidden">

                        <button type="button" id="btn-save"
                            class="btn-primary flex items-center gap-2 px-6 py-2 shadow-lg shadow-pink-500/30">
                            <i class="fas fa-save"></i> <span>Simpan</span>
                        </button>

                        <button type="button" id="btn-cancel-edit"
                            class="hidden bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded shadow-lg flex items-center gap-2 transition-all">
                            <i class="fas fa-times"></i> <span>Batal</span>
                        </button>

                        <div id="edit-mode-indicator"
                            class="hidden bg-amber-100 text-amber-800 px-4 py-2 rounded border border-amber-300 font-bold text-center">
                            <i class="fas fa-pencil-alt mr-2"></i> MODE EDIT DATA
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">

                    <div class="lg:col-span-1 space-y-4 input-row-container">

                        <div class="bg-white p-4 rounded-xl shadow-sm border border-pink-100">
                            <h3 class="text-pink-600 font-bold text-sm mb-3 border-b pb-2"><i
                                    class="fas fa-user-tie mr-1"></i> Data Pembayaran </h3>
                            <div class="space-y-3">
                                <div>
                                    <label class="form-label">Nama Supplier <span class="text-red-500">*</span></label>

                                    <input type="hidden" id="inp_id">

                                    <input type="hidden" id="inp_total_bayar">

                                    <input type="hidden" id="inp_ket">

                                    <input type="hidden" id="inp_kode_supplier">
                                    <input type="text" id="inp_nama_supplier" class="input-compact" list="supplier_list"
                                        placeholder="Ketik nama supplier...">
                                    <datalist id="supplier_list"></datalist>
                                </div>
                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <label class="form-label">Tanggal Bayar</label>
                                        <input type="date" id="inp_tgl_bayar" class="input-compact"
                                            value="<?php echo date('Y-m-d'); ?>">
                                    </div>
                                    <div>
                                        <label class="form-label">Jatuh Tempo (TOP)</label>
                                        <input type="date" id="inp_top" class="input-compact" title="Opsional">
                                    </div>

                                </div>
                                <div class="grid grid-cols-2 gap-2">

                                    <div>
                                        <label class="form-label">Status <span class="text-red-500">*</span></label>
                                        <select id="inp_status" class="input-compact bg-white">
                                            <option value="">Pilih Status...</option>
                                            <option value="PKP">PKP</option>
                                            <option value="NON PKP">NON PKP</option>
                                            <option value="BTKP">BTKP</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="form-label">Cabang Bayar <span
                                                class="text-red-500">*</span></label>
                                        <input type="text" id="inp_store_bayar" class="input-compact border-pink-200"
                                            placeholder="Ketik cabang bayar...">
                                    </div>
                                </div>
                                <div>
                                    <label class="form-label">MOP <span class="text-red-500">*</span></label>
                                    <select id="inp_ket_global"
                                        class="input-compact bg-white font-semibold text-blue-800">
                                        <option value="" selected disabled>Pilih...</option>
                                        <option value="TRANSFER">TRANSFER</option>
                                        <option value="CASH">CASH</option>
                                    </select>
                                </div>
                                <div class="mt-3 pt-3 border-t border-dashed border-gray-200">
                                    <label class="form-label text-blue-600">Total Bayar <span
                                            class="text-red-500">*</span></label>
                                    <div class="flex flex-col">
                                        <input type="text" id="inp_global_total"
                                            class="input-compact text-right font-bold font-mono text-blue-700"
                                            placeholder="0">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white p-4 rounded-xl shadow-sm border border-blue-100 relative">
                            <div class="absolute top-0 left-0 w-1 h-full bg-blue-500 rounded-l-xl"></div>
                            <h3 class="text-blue-600 font-bold text-sm mb-3 border-b pb-2 pl-2"><i
                                    class="fas fa-plus-circle mr-1"></i> Input Faktur</h3>

                            <div class="space-y-3">
                                <div>
                                    <label class="form-label">No. Invoice / Faktur <span
                                            class="text-red-500">*</span></label>
                                    <div class="relative">
                                        <input type="text" id="inp_no_faktur" class="input-compact pr-8"
                                            placeholder="Scan / Cari Invoice...">
                                    </div>
                                </div>
                                <div id="installment-info-box"
                                    class="hidden mt-3 bg-blue-50 border border-blue-100 rounded-lg p-3">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <p class="text-xs text-blue-600 font-bold mb-1"><i
                                                    class="fas fa-info-circle mr-1"></i> Status Angsuran</p>
                                            <div class="text-xs text-gray-600">Sudah Bayar: <span id="info-sudah-bayar"
                                                    class="font-mono font-bold text-green-600">0</span></div>
                                            <div class="text-xs text-gray-600 mt-1">Sisa Hutang: <span
                                                    id="info-sisa-hutang"
                                                    class="font-mono font-bold text-red-600 text-sm">0</span></div>
                                        </div>
                                        <button type="button" id="btn-view-history-detail"
                                            class="bg-white hover:bg-blue-100 text-blue-600 border border-blue-200 rounded px-2 py-1 shadow-sm transition-all"
                                            title="Lihat Rincian">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <label class="form-label">Cabang (Inv) <span
                                                class="text-red-500">*</span></label>
                                        <select id="inp_kode_store" class="input-compact bg-white">
                                            <option value="">Pilih...</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="form-label">Tgl Nota <span class="text-red-500">*</span></label>
                                        <input type="date" id="inp_tgl_nota" class="input-compact">
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <label class="form-label">Nilai Faktur</label>
                                        <input type="text" id="inp_nilai_faktur"
                                            class="input-compact text-right font-mono" value="0">
                                    </div>
                                    <div>
                                        <label class="form-label">Potongan</label>
                                        <input type="text" id="inp_potongan"
                                            class="input-compact text-right font-mono text-red-500" value="0">
                                    </div>
                                </div>

                                <div>
                                    <label class="form-label">Ket. Potongan</label>
                                    <input type="text" id="inp_ket_potongan" class="input-compact"
                                        placeholder="(Opsional)">
                                </div>

                                <div class="pt-2 border-t border-dashed">

                                    <button type="button" id="btn-add-item"
                                        class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 rounded shadow-md text-sm font-semibold transition-all">
                                        <i class="fas fa-arrow-down mr-1"></i> Tambah ke Daftar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="lg:col-span-1 flex flex-col h-full">
                        <div
                            class="bg-white rounded-xl shadow-sm border border-gray-200 flex-1 flex flex-col overflow-hidden">
                            <div class="p-4 bg-gray-50 border-b border-gray-200 flex justify-between items-center">
                                <h3 class="font-bold text-gray-700"><i class="fas fa-list-ol mr-2"></i> Daftar Faktur
                                    yang akan dibayar</h3>
                                <button type="button" id="btn-clear-list"
                                    class="text-xs text-red-500 hover:text-red-700 underline hidden">Hapus
                                    Semua</button>
                            </div>

                            <div class="flex-1 overflow-y-auto bg-white p-0 relative" style="min-height: 300px;">
                                <table class="w-full text-left border-collapse">
                                    <thead class="bg-gray-100 sticky top-0 z-10 text-xs text-gray-600 uppercase">
                                        <tr>
                                            <th class="p-3 border-b">No. Inv</th>
                                            <th class="p-3 border-b">Cabang</th>
                                            <th class="p-3 border-b text-right">Nilai</th>
                                            <th class="p-3 border-b text-right">Pot</th>
                                            <th class="p-3 border-b text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody id="temp-list-body" class="text-sm">
                                        <tr>
                                            <td colspan="6" class="text-center p-10 text-gray-400">
                                                <i class="fas fa-basket-shopping text-4xl mb-2 opacity-30"></i><br>
                                                Belum ada faktur ditambahkan
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="p-4 bg-pink-50 border-t border-pink-100">
                                <div
                                    class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                                    <div class="text-sm text-pink-800">
                                        Total Faktur: <span id="lbl_count_item" class="font-bold">0</span> Item
                                    </div>

                                    <div class="text-right space-y-1">
                                        <div class="text-sm text-gray-600">
                                            Total Tagihan:
                                            <span class="font-mono font-bold text-lg text-pink-600 ml-1">
                                                Rp <span id="lbl_total_tagihan">0</span>
                                            </span>
                                        </div>

                                        <div id="summary-payment-details" class="hidden">
                                            <div class="text-xs text-gray-500">
                                                Total Bayar:
                                                <span class="font-mono font-bold text-blue-600 ml-1">
                                                    Rp <span id="lbl_summary_bayar">0</span>
                                                </span>
                                            </div>
                                            <div class="text-sm font-bold border-t border-pink-200 mt-1 pt-1">
                                                Selisih:
                                                <span class="font-mono ml-1" id="lbl_summary_selisih_container">
                                                    Rp <span id="lbl_summary_selisih">0</span>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-4 flex justify-end gap-3">
                                    <button type="button" id="btn-save-batch" disabled
                                        class="btn-primary flex items-center gap-2 px-8 py-3 shadow-lg shadow-pink-500/30 opacity-50 cursor-not-allowed">
                                        <i class="fas fa-save fa-lg"></i> <span>Simpan</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div
                        class="p-4 border-b border-gray-100 flex flex-col md:flex-row justify-between items-center gap-3">
                        <div class="relative w-full sm:w-64">
                            <input type="text" id="inp_search_table"
                                class="w-full pl-4 py-1.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:border-pink-400"
                                placeholder="Cari Faktur, Supplier...">
                            <i class="fas fa-search absolute right-3 top-2 text-gray-400 text-xs"></i>
                        </div>
                    </div>

                    <div class="overflow-x-auto relative" style="max-height: 450px; overflow-y: auto;"
                        id="table-scroll-container">
                        <table class="w-full text-left border-collapse table-compact sticky top-0">
                            <thead class="sticky top-0 z-10 shadow-sm">
                                <tr>
                                    <th class="w-10 text-center">No</th>
                                    <th>Tgl Bayar</th>
                                    <th>Tgl Nota</th>
                                    <th>No Invoice</th>
                                    <th>Supplier</th>
                                    <th>Status Pajak</th>
                                    <th>TOP</th>
                                    <th>Cabang Inv</th>
                                    <th>Cabang Bayar</th>
                                    <th>MOP</th>
                                    <th class="text-right">Nilai Faktur</th>
                                    <th class="text-right">Potongan</th>
                                    <th class="text-right">Total Bayar</th>
                                    <th class="w-20 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="table-body"></tbody>
                            <tbody id="loading-sentinel">
                                <tr class="hidden" id="loader-row">
                                    <td colspan="9" class="text-center p-4"><i
                                            class="fas fa-circle-notch fa-spin text-pink-500"></i></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </section>
    </main>

    <script src="/src/js/middleware_auth.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/exceljs/4.3.0/exceljs.min.js"></script>
    <script src="../../js/buku_besar/input_buku_besar_handler.js" type="module"></script>
</body>

</html>