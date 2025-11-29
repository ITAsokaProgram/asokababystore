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
    <title>Form Faktur Pajak</title>

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
                grid-template-columns: repeat(5, 1fr);
            }
        }

        @media (min-width: 1536px) {
            .form-grid {
                grid-template-columns: 1.2fr 1.2fr 1.5fr 1.5fr 0.8fr 1fr 1fr 1fr 1.2fr auto;
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

        .input-readonly {
            background-color: #f9fafb;
            color: #6b7280;
            cursor: default;
        }

        .table-compact th {
            padding: 0.75rem 1rem;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
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
                            <i class="fa-solid fa-file-invoice-dollar fa-lg"></i>
                        </div>
                        <div>
                            <h1 class="text-xl font-bold text-gray-800">Form Faktur Pajak</h1>
                        </div>
                    </div>
                    <button type="button" id="btn-save"
                        class="btn-primary flex items-center gap-2 px-6 py-2 shadow-lg shadow-pink-500/30">
                        <i class="fas fa-save"></i> <span>Simpan</span>
                    </button>
                </div>

                <div class="input-row-container mb-6 relative">
                    <div id="edit-mode-indicator"
                        class="hidden absolute -top-3 left-4 bg-amber-100 text-amber-700 px-3 py-1 rounded-full text-xs font-bold border border-amber-200 shadow-sm">
                        <i class="fas fa-edit mr-1"></i> Mode Edit
                    </div>

                    <form id="fp-form" autocomplete="off">
                        <input type="hidden" id="inp_id" name="id" value="">

                        <div class="form-grid">
                            <div class="relative"> <label class="form-label">NSFP</label>
                                <input type="text" id="inp_no_seri" name="nsfp" class="input-compact" autofocus>
                                <p id="err_no_seri"
                                    class="text-xs text-red-500 absolute -bottom-4 left-0 hidden font-semibold"></p>
                            </div>
                            <div class="relative"> <label class="form-label">No. Invoice</label>
                                <input type="text" id="inp_no_invoice" name="no_invoice" class="input-compact"
                                    placeholder="Cari Invoice...">
                                <p id="err_no_invoice"
                                    class="text-xs text-red-500 absolute -bottom-4 left-0 hidden font-semibold"></p>
                            </div>
                            <div>
                                <label class="form-label">Toko / Cabang</label>
                                <select id="inp_kode_store" name="kode_store"
                                    class="input-compact bg-white cursor-pointer">
                                    <option value="">Pilih Toko...</option>
                                </select>
                            </div>



                            <div>
                                <label class="form-label">Nama Supplier</label>
                                <input type="text" id="inp_nama_supplier" name="nama_supplier" class="input-compact"
                                    list="supplier_list">
                                <datalist id="supplier_list"></datalist>
                            </div>

                            <div>
                                <label class="form-label">Tgl Faktur</label>
                                <input type="date" id="inp_tgl_faktur" name="tgl_faktur" class="input-compact">
                            </div>

                            <div>
                                <label class="form-label text-right">DPP</label>
                                <input type="text" id="inp_dpp" name="dpp" class="input-compact text-right font-mono"
                                    value="0">
                            </div>

                            <div>
                                <label class="form-label text-right">DPP Nilai Lain</label>
                                <input type="text" id="inp_dpp_lain" name="dpp_nilai_lain"
                                    class="input-compact text-right font-mono input-readonly" value="0" disabled>
                            </div>

                            <div>
                                <label class="form-label text-right">PPN</label>
                                <input type="text" id="inp_ppn" name="ppn" class="input-compact text-right font-mono"
                                    value="0">
                            </div>

                            <div>
                                <label class="form-label text-right">Total</label>
                                <input type="text" id="inp_total" name="total"
                                    class="input-compact text-right font-bold text-pink-600 input-readonly font-mono"
                                    readonly value="0">
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
                    <div class="p-4 border-b border-gray-100 flex justify-between items-center">
                        <h3 class="font-bold text-gray-700"><i class="fas fa-list mr-2 text-pink-500"></i>Data Faktur
                            Pajak
                            Hari Ini</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse table-compact">
                            <thead>
                                <tr>
                                    <th class="w-10 text-center">No</th>
                                    <th>Tgl Faktur</th>
                                    <th>No Invoice</th>
                                    <th>NSFP</th>
                                    <th>Cabang</th>
                                    <th>Supplier</th>
                                    <th class="text-right">DPP</th>
                                    <th class="text-right">DPP Lain</th>
                                    <th class="text-right">PPN</th>
                                    <th class="text-right">Total</th>
                                    <th class="w-20 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="table-body">
                                <tr>
                                    <td colspan="9" class="text-center p-6 text-gray-500">Memuat data...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </section>
    </main>

    <script src="/src/js/middleware_auth.js"></script>
    <script src="../../js/shared/internal/sidebar-profile.js" defer></script>
    <script src="../../js/coretax/input_faktur_pajak_handler.js" type="module"></script>
    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</body>

</html>