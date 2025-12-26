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
    </style>
</head>

<body class="bg-gray-50">

    <?php include '../../component/navigation_report.php'; ?>
    <?php include '../../component/sidebar_report.php'; ?>

    <main id="main-content" class="flex-1 p-4 ml-64">
        <section class="min-h-screen">
            <div class="max-w-[1600px] mx-auto">

                <div class="header-card p-4 rounded-2xl mb-4 bg-white shadow-sm flex justify-between items-center">
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
                    </div>
                </div>

                <div class="input-row-container mb-6 relative">
                    <div id="edit-mode-indicator"
                        class="hidden absolute -top-3 left-4 bg-amber-100 text-amber-700 px-3 py-1 rounded-full text-xs font-bold border border-amber-200 shadow-sm">
                        <i class="fas fa-edit mr-1"></i> Mode Edit
                    </div>

                    <form id="single-form" autocomplete="off">
                        <input type="hidden" id="inp_id" name="id" value="">

                        <div class="form-grid">
                            <div>
                                <label class="form-label">Cabang (Invoice)</label>
                                <select id="inp_kode_store" name="kode_store"
                                    class="input-compact bg-white cursor-pointer">
                                    <option value="">Pilih Cabang</option>
                                </select>
                            </div>

                            <div>
                                <label class="form-label text-pink-600">Cabang Bayar</label>
                                <select id="inp_store_bayar" name="store_bayar"
                                    class="input-compact bg-white cursor-pointer border-pink-200 focus:border-pink-500">
                                    <option value="">Pilih Cabang</option>
                                </select>
                            </div>

                            <div class="relative">
                                <label class="form-label">No. Invoice</label>
                                <input type="text" id="inp_no_faktur" name="no_faktur" class="input-compact"
                                    placeholder="Cari Invoice / Input Manual...">
                                <p id="err_no_faktur"
                                    class="text-red-500 absolute -bottom-4 left-0 hidden font-semibold"
                                    style="font-size: 0.55rem"></p>
                            </div>

                            <div class="hidden">
                                <label class="form-label">Kode Supplier</label>
                                <input type="text" id="inp_kode_supplier" name="kode_supplier" class="input-compact"
                                    placeholder="Kode Supplier...">
                            </div>

                            <div>
                                <label class="form-label">Nama Supplier</label>
                                <input type="text" id="inp_nama_supplier" name="nama_supplier" class="input-compact"
                                    list="supplier_list">
                                <datalist id="supplier_list"></datalist>
                            </div>

                            <div>
                                <label class="form-label">Tgl Nota (Faktur)</label>
                                <input type="date" id="inp_tgl_nota" name="tgl_nota" class="input-compact">
                            </div>

                            <div>
                                <label class="form-label">Tanggal Bayar</label>
                                <input type="date" id="inp_tgl_bayar" name="tanggal_bayar" class="input-compact"
                                    value="">
                            </div>

                            <div>
                                <label class="form-label text-right">Potongan</label>
                                <input type="text" id="inp_potongan" name="potongan"
                                    class="input-compact text-right font-mono" value="0">
                            </div>

                            <div>
                                <label class="form-label">Ket. Potongan</label>
                                <input type="text" id="inp_ket_potongan" name="ket_potongan" class="input-compact"
                                    placeholder="(Opsional)">
                            </div>

                            <div>
                                <label class="form-label">Keterangan / Notes</label>
                                <input type="text" id="inp_ket" name="ket" class="input-compact"
                                    placeholder="Keterangan tambahan...">
                            </div>
                            <div>
                                <label class="form-label">Nilai Faktur</label>
                                <input type="text" id="inp_nilai_faktur" name="nilai_faktur"
                                    class="input-compact text-right font-mono" value="0">
                            </div>

                            <div>
                                <label class="form-label text-right text-pink-600 font-bold">Total Bayar</label>
                                <input type="text" id="inp_total_bayar" name="total_bayar"
                                    class="input-compact text-right font-bold font-mono text-pink-600" value="0">
                            </div>

                            <div class="flex items-end h-full pb-1">
                                <button type="button" id="btn-cancel-edit"
                                    class="hidden text-gray-400 hover:text-red-500 transition-colors"
                                    title="Batal Edit">
                                    <i class="fas fa-times-circle fa-lg"></i>
                                </button>
                            </div>
                        </div>
                    </form>
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
                                    <th>Cabang Inv</th>
                                    <th>Cabang Bayar</th>
                                    <th>Ket</th>
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