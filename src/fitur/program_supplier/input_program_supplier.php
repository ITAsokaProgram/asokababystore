<?php
session_start();
include '../../../aa_kon_sett.php';
require_once __DIR__ . '/../../component/menu_handler.php';
$menuHandler = new MenuHandler('program_supplier_input');
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

        .table-compact th {
            padding: 0.75rem 1rem;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            background-color: #fdf2f8;
            color: #831843;
            font-weight: 700;
            white-space: nowrap;
        }

        .table-compact td {
            padding: 0.75rem 1rem;
            font-size: 0.875rem;
            border-bottom: 1px solid #f3f4f6;
            vertical-align: top;
        }

        .table-compact tr:hover {
            background-color: #fdf2f8;
        }

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
                                <label class="form-label">PIC (Pisahkan koma)</label>
                                <input type="text" id="inp_pic" name="pic" class="input-compact"
                                    placeholder="Cth: Dewi, Erna, Annisa">
                            </div>

                            <div>
                                <label class="form-label">Nama Supplier</label>
                                <input type="text" id="inp_nama_supplier" name="nama_supplier" class="input-compact"
                                    placeholder="Nama Badan" list="supplier_list">
                                <datalist id="supplier_list"></datalist>

                            </div>
                            <div>
                                <label class="form-label">NPWP (16 Digit)</label>
                                <input type="text" id="inp_npwp" name="npwp" class="input-compact" maxlength="16"
                                    oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                            </div>
                            <div>
                                <label class="form-label">Status PPN</label>
                                <select id="inp_status_ppn" name="status_ppn"
                                    class="input-compact bg-white cursor-pointer">
                                    <option value="Non PPN">Non PPN</option>
                                    <option value="PPN">PPN</option>
                                </select>
                            </div>

                            <div>
                                <label class="form-label">Cabang</label>
                                <select id="inp_kode_cabang" name="kode_cabang"
                                    class="input-compact bg-white cursor-pointer">
                                    <option value="">Pilih Cabang</option>
                                </select>
                            </div>
                            <div>
                                <label class="form-label">Nomor Program (Auto)</label>
                                <input type="text" id="inp_nomor_program" name="nomor_program"
                                    class="input-compact bg-gray-100 text-gray-500 font-mono text-xs"
                                    placeholder="(Auto Generated)" readonly>
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
                                    <option value="Cash">Cash</option>
                                </select>
                            </div>

                            <div>
                                <label class="form-label">Jatuh Tempo (TOP)</label>
                                <input type="date" id="inp_top_date" name="top_date" class="input-compact">
                            </div>
                            <div class="md:col-span-2 lg:col-span-4 xl:col-span-4"> <label
                                    class="form-label">Keterangan</label>
                                <textarea id="inp_keterangan" name="keterangan" class="input-compact" rows="1"
                                    placeholder="Tambahkan catatan jika ada..."></textarea>
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
                                placeholder="Cari Data...">
                            <i class="fas fa-search absolute right-3 top-2 text-gray-400 text-xs"></i>
                        </div>
                    </div>


                    <div class="overflow-x-auto relative" style="max-height: 450px; overflow-y: auto;"
                        id="table-scroll-container">
                        <table class="w-full text-left border-collapse table-compact sticky top-0">
                            <thead class="sticky top-0 z-10 shadow-sm">
                                <tr>
                                    <th class="text-center w-20">Aksi</th>
                                    <th>No Program</th>
                                    <th>PIC</th>
                                    <th>Supplier</th>
                                    <th>Cabang</th>
                                    <th>Periode</th>
                                    <th>Nama Program</th>
                                    <th>No Dokumen</th>
                                    <th class="text-center">Sts PPN</th>
                                    <th class="text-right">Nilai Prg</th>
                                    <th class="text-center">MOP</th>
                                    <th class="text-center">TOP</th>
                                    <th>Keterangan</th>
                                </tr>
                            </thead>
                            <tbody id="table-body">
                            </tbody>
                            <tbody id="loading-sentinel">
                                <tr class="hidden" id="loader-row">
                                    <td colspan="12" class="text-center p-4">
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

    <div x-data="detailModal()" x-show="show" x-cloak @show-detail-modal.window="openModal($event.detail)"
        style="display: none; z-index: 10000;" class="fixed inset-0 z-[9999] overflow-y-auto">

        <div x-show="show" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" @click="closeModal()"
            class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm">
        </div>

        <div class="flex min-h-screen items-center justify-center p-4">
            <div x-show="show" x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 transform scale-95"
                x-transition:enter-end="opacity-100 transform scale-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 transform scale-100"
                x-transition:leave-end="opacity-0 transform scale-95" @click.stop
                class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md transform">

                <div
                    class="flex items-center justify-between p-5 border-b border-gray-100 bg-gradient-to-r from-pink-50 to-white rounded-t-2xl">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-pink-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-info-circle text-pink-600 text-lg"></i>
                        </div>
                        <h3 class="text-lg font-bold text-gray-800" x-text="title"></h3>
                    </div>
                    <button @click="closeModal()"
                        class="text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg p-2 transition-all">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <div class="p-6">
                    <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                        <p class="text-gray-700 text-sm leading-relaxed whitespace-pre-wrap break-words"
                            x-text="content"></p>
                    </div>
                </div>

                <div class="flex justify-end gap-2 p-5 border-t border-gray-100 bg-gray-50 rounded-b-2xl">
                    <button @click="closeModal()"
                        class="px-4 py-2 text-sm bg-gray-200 hover:bg-gray-300 rounded text-gray-700 font-medium transition-colors">
                        <i class="fas fa-times mr-2"></i>
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function detailModal() {
            return {
                show: false,
                title: '',
                content: '',

                openModal(data) {
                    this.title = data.title;
                    this.content = data.content;
                    this.show = true;
                    document.body.style.overflow = 'hidden';
                },

                closeModal() {
                    this.show = false;
                    document.body.style.overflow = 'auto';
                }
            }
        }
    </script>

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>

    <script src="/src/js/middleware_auth.js"></script>
    <script src="../../js/program_supplier/input_program_supplier_handler.js" type="module"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

</body>

</html>