<?php
session_start();
include '../../../aa_kon_sett.php';
require_once __DIR__ . '/../../component/menu_handler.php';
$menuHandler = new MenuHandler('program_supplier_input');
// if (!$menuHandler->initialize()) { exit(); }
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Input Program Supplier</title>

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
        .input-row-container {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 0.75rem;
            padding: 1.25rem;
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

        @media (min-width: 1280px) {
            .form-grid {
                grid-template-columns: repeat(5, 1fr);
            }
        }

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
            transition: border-color 0.15s ease-in-out;
        }

        .input-compact:focus {
            outline: none;
            border-color: #ec4899;
            box-shadow: 0 0 0 3px rgba(236, 72, 153, 0.1);
        }

        /* Style Table Compact (Sesuai Referensi) */
        .table-compact th {
            padding: 0.75rem 1rem;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            background-color: #fdf2f8;
            /* Pink header base */
            color: #831843;
            font-weight: 700;
            white-space: nowrap;
        }

        .table-compact td {
            padding: 0.75rem 1rem;
            font-size: 0.875rem;
            border-bottom: 1px solid #f3f4f6;
            vertical-align: top;
            /* Penting untuk data stacked */
        }

        .table-compact tr:hover {
            background-color: #fdf2f8;
        }

        /* Helper khusus untuk stacked content (Vertikal List) */
        .stacked-cell div {
            margin-bottom: 2px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 180px;
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
                            <i class="fa-solid fa-handshake fa-lg"></i>
                        </div>
                        <div>
                            <h1 class="text-xl font-bold text-gray-800">Input Program Supplier</h1>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <a href="laporan_program_supplier.php"
                            class="btn-report flex items-center gap-2 px-3 py-2 shadow-sm decoration-0 rounded bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium">
                            <i class="fas fa-list"></i> <span>Lihat Laporan</span>
                        </a>
                        <button type="button" id="btn-save"
                            class="btn-primary flex items-center gap-2 px-6 py-2 shadow-lg shadow-pink-500/30 rounded text-white bg-pink-600 hover:bg-pink-700 transition-all font-medium">
                            <i class="fas fa-save"></i> <span>Simpan</span>
                        </button>
                    </div>
                </div>

                <div class="input-row-container mb-6 relative">
                    <div id="edit-mode-indicator"
                        class="hidden absolute -top-3 left-4 bg-amber-100 text-amber-700 px-3 py-1 rounded-full text-xs font-bold border border-amber-200 shadow-sm">
                        <i class="fas fa-edit mr-1"></i> Mode Edit
                    </div>

                    <form id="program-form" autocomplete="off">
                        <input type="hidden" id="inp_old_nomor_dokumen" name="old_nomor_dokumen" value="">

                        <div class="form-grid">
                            <div>
                                <label class="form-label">Cabang</label>
                                <select id="inp_kode_cabang" name="kode_cabang"
                                    class="input-compact bg-white cursor-pointer">
                                    <option value="">Pilih Cabang</option>
                                </select>
                            </div>

                            <div>
                                <label class="form-label">Nama Supplier</label>
                                <input type="text" id="inp_nama_supplier" name="nama_supplier" class="input-compact"
                                    placeholder="Cari Supplier..." list="supplier_list">
                                <datalist id="supplier_list"></datalist>
                            </div>

                            <div>
                                <label class="form-label">PIC (Pisahkan koma)</label>
                                <input type="text" id="inp_pic" name="pic" class="input-compact"
                                    placeholder="Cth: Dewi, Erna, Annisa">
                            </div>

                            <div>
                                <label class="form-label">Periode Program</label>
                                <input type="text" id="inp_periode_program" name="periode_program" class="input-compact"
                                    placeholder="Cth: July 2025">
                            </div>

                            <div>
                                <label class="form-label">Nama Program</label>
                                <input type="text" id="inp_nama_program" name="nama_program" class="input-compact"
                                    placeholder="Cth: Diskon Kemerdekaan">
                            </div>

                            <div class="relative">
                                <label class="form-label">Nomor Dokumen (Pisahkan koma)</label>
                                <input type="text" id="inp_nomor_dokumen" name="nomor_dokumen"
                                    class="input-compact font-mono text-blue-700 font-medium"
                                    placeholder="Cth: ASK-CDT.., ASK-" required>
                            </div>

                            <div>
                                <label class="form-label text-right">Nilai Program (Rp)</label>
                                <input type="text" id="inp_nilai_program" name="nilai_program"
                                    class="input-compact text-right font-mono" value="0">
                            </div>

                            <div>
                                <label class="form-label">MOP (Metode)</label>
                                <select id="inp_mop" name="mop" class="input-compact bg-white cursor-pointer">
                                    <option value="Potong Tagihan">Potong Tagihan</option>
                                    <option value="Transfer">Transfer</option>
                                </select>
                            </div>

                            <div>
                                <label class="form-label">Jatuh Tempo (TOP)</label>
                                <input type="date" id="inp_top_date" name="top_date" class="input-compact">
                            </div>

                            <div>
                                <label class="form-label text-right">Nilai Transfer (Rp)</label>
                                <input type="text" id="inp_nilai_transfer" name="nilai_transfer"
                                    class="input-compact text-right font-mono" value="0">
                            </div>

                            <div>
                                <label class="form-label">Tanggal Transfer</label>
                                <input type="date" id="inp_tanggal_transfer" name="tanggal_transfer"
                                    class="input-compact">
                            </div>

                            <div>
                                <label class="form-label">Tanggal FPK</label>
                                <input type="date" id="inp_tgl_fpk" name="tgl_fpk" class="input-compact">
                            </div>

                            <div>
                                <label class="form-label">NSFP</label>
                                <input type="text" id="inp_nsfp" name="nsfp" class="input-compact"
                                    placeholder="No Seri Faktur Pajak">
                            </div>

                            <div>
                                <label class="form-label">Nomor Bukpot</label>
                                <input type="text" id="inp_nomor_bukpot" name="nomor_bukpot" class="input-compact"
                                    placeholder="Nomor Bukti Potong">
                            </div>

                            <div>
                                <label class="form-label text-right">DPP</label>
                                <input type="text" id="inp_dpp" name="dpp" class="input-compact text-right font-mono"
                                    value="0">
                            </div>

                            <div>
                                <label class="form-label text-right">PPN</label>
                                <input type="text" id="inp_ppn" name="ppn" class="input-compact text-right font-mono"
                                    value="0">
                            </div>

                            <div>
                                <label class="form-label text-right">PPH</label>
                                <input type="text" id="inp_pph" name="pph" class="input-compact text-right font-mono"
                                    value="0">
                            </div>

                            <div class="flex items-end h-full pb-1 gap-2">
                                <button type="button" id="btn-cancel-edit"
                                    class="hidden text-gray-500 hover:text-red-500 transition-colors bg-gray-100 hover:bg-gray-200 px-3 py-2 rounded text-sm w-full"
                                    title="Batal Edit">
                                    <i class="fas fa-times mr-1"></i> Batal
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div
                        class="p-4 border-b border-gray-100 flex flex-col md:flex-row justify-between items-center gap-3">
                        <h3 class="font-bold text-gray-700 text-sm"><i class="fas fa-history mr-2"></i>Data Terakhir
                            Diinput</h3>
                        <div class="relative w-full sm:w-64">
                            <input type="text" id="inp_search_table"
                                class="w-full pl-4 py-1.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:border-pink-400 focus:ring-2 focus:ring-pink-100 transition-all"
                                placeholder="Cari Invoice, Supplier, Harga...">
                            <i class="fas fa-search absolute right-3 top-2 text-gray-400 text-xs"></i>
                        </div>
                    </div>


                    <div class="overflow-x-auto relative" style="max-height: 450px; overflow-y: auto;"
                        id="table-scroll-container">
                        <table class="w-full text-left border-collapse table-compact sticky top-0">
                            <thead class="sticky top-0 z-10 shadow-sm">
                                <tr>
                                    <th class="w-10 text-center">No</th>
                                    <th>PIC</th>
                                    <th>Supplier / Cabang</th>
                                    <th>Program / Periode</th>
                                    <th>No Dokumen</th>
                                    <th class="text-right">Nilai Prg</th>
                                    <th class="text-center">MOP / TOP</th>
                                    <th class="text-right">Transfer</th>
                                    <th class="text-center">Pajak (DPP/PPN/PPH)</th>
                                    <th class="text-center w-20">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="table-body">
                            </tbody>
                            <tbody id="loading-sentinel">
                                <tr class="hidden" id="loader-row">
                                    <td colspan="10" class="text-center p-4">
                                        <i class="fas fa-circle-notch fa-spin text-pink-500 text-lg"></i>
                                        <span class="ml-2 text-gray-500 text-xs">Memuat data lainnya...</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </section>
    </main>

    <script src="/src/js/middleware_auth.js"></script>
    <script src="../../js/program_supplier/input_program_supplier_handler.js" type="module"></script>

</body>

</html>