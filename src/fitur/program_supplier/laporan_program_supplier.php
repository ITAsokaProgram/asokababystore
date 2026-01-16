<?php
session_start();
include '../../../aa_kon_sett.php';


$kd_store = $_GET['kd_store'] ?? 'all';

$default_page = 1;
$page = (int) ($_GET['page'] ?? $default_page);
if ($page < 1) {
    $page = 1;
}


require_once __DIR__ . '/../../component/menu_handler.php';
$menuHandler = new MenuHandler('laporan_program_supplier');
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Program Supplier</title>
    <link rel="stylesheet" href="../../style/header.css">
    <link rel="stylesheet" href="../../style/sidebar.css">
    <link rel="stylesheet" href="../../style/animation-fade-in.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../style/default-font.css">
    <link rel="stylesheet" href="../../output2.css">
    <link rel="stylesheet" href="../../style/pink-theme.css">
    <link rel="icon" type="image/png" href="../../../public/images/logo1.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/exceljs/4.3.0/exceljs.min.js"></script>
</head>

<body class="bg-gray-50 theme-pembelian">
    <?php include '../../component/navigation_report.php' ?>
    <?php include '../../component/sidebar_report.php' ?>

    <main id="main-content" class="flex-1 p-4 ml-64">
        <section class="min-h-screen">
            <div class="max-w-full mx-auto">
                <div class="header-card p-4 rounded-2xl mb-4">
                    <div class="flex items-center justify-between flex-wrap gap-3">
                        <div class="flex items-center gap-3">
                            <div class="icon-wrapper">
                                <i class="fa-solid fa-handshake fa-lg"></i>
                            </div>
                            <div>
                                <h1 id="page-title" class="text-xl font-bold text-gray-800 mb-1">Laporan Program
                                    Supplier</h1>
                                <p id="page-subtitle" class="text-xs text-gray-600">Memuat data...</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <a href="input_program_supplier.php"
                                class="btn-primary flex items-center gap-2 px-4 py-2 shadow-lg shadow-pink-500/30 rounded-lg text-white transition-transform hover:scale-105 text-sm decoration-0">
                                <i class="fas fa-plus"></i> <span>Input Program Supplier</span>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="filter-card-simple">
                    <form id="filter-form" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3 items-end"
                        method="GET">

                        <div class="lg:col-span-1">
                            <label for="kd_store" class="block text-xs font-semibold text-gray-700 mb-2">
                                <i class="fas fa-store text-pink-600 mr-1"></i> Cabang
                            </label>
                            <select name="kd_store" id="kd_store" class="input-modern w-full">
                                <option value="all">Seluruh Store</option>
                            </select>
                        </div>
                        <div class="lg:col-span-1">
                            <label for="status_bukpot" class="block text-xs font-semibold text-gray-700 mb-2">
                                <i class="fas fa-file-invoice text-pink-600 mr-1"></i> Status Bukpot
                            </label>
                            <select name="status_bukpot" id="status_bukpot" class="input-modern w-full">
                                <option value="all" <?php echo (isset($_GET['status_bukpot']) && $_GET['status_bukpot'] == 'all') ? 'selected' : ''; ?>>Semua</option>
                                <option value="sudah" <?php echo (isset($_GET['status_bukpot']) && $_GET['status_bukpot'] == 'sudah') ? 'selected' : ''; ?>>Sudah Ada Bukpot</option>
                                <option value="belum" <?php echo (isset($_GET['status_bukpot']) && $_GET['status_bukpot'] == 'belum') ? 'selected' : ''; ?>>Belum Ada Bukpot</option>
                            </select>
                        </div>

                        <div class="lg:col-span-1">
                            <label for="search_query" class="block text-xs font-semibold text-gray-700 mb-2">
                                <i class="fas fa-search text-pink-600 mr-1"></i> Cari Data
                            </label>
                            <div class="flex gap-2">
                                <input type="text" name="search_query" id="search_query" class="input-modern w-full"
                                    style="min-width: 120px;"
                                    value="<?php echo htmlspecialchars($_GET['search_query'] ?? ''); ?>"
                                    placeholder="Cari Dok / Supplier / PIC...">

                                <button type="submit" id="filter-submit-button"
                                    class="btn-primary flex justify-center items-center rounded-lg"
                                    title="Terapkan Filter">
                                    <i class="fas fa-filter"></i>
                                </button>

                                <button type="button" id="export-excel-button"
                                    class="w-10 bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-2 rounded-lg transition-colors shadow-sm inline-flex items-center justify-center"
                                    title="Export Excel">
                                    <i class="fas fa-file-excel"></i>
                                </button>
                            </div>
                        </div>

                        <input type="hidden" name="page" value="1">
                    </form>
                </div>


                <div class="filter-card">
                    <div class="flex flex-wrap justify-between items-center mb-3 gap-3">
                        <h3 class="text-lg font-bold text-gray-800">
                            <i class="fas fa-list text-pink-600 mr-2"></i>
                            Hasil Laporan
                        </h3>
                    </div>
                    <div class="table-container overflow-x-auto">
                        <table class="table-modern w-full text-xs" id="program-table">
                            <thead>
                                <tr>
                                    <th class="whitespace-nowrap text-center w-20">Aksi</th>
                                    <th class="whitespace-nowrap">No Program</th>
                                    <th class="whitespace-nowrap">PIC</th>
                                    <th class="whitespace-nowrap">Supplier & NPWP</th>
                                    <th class="whitespace-nowrap">Cabang</th>
                                    <th class="whitespace-nowrap text-center">Sts PPN</th>
                                    <th class="whitespace-nowrap">Periode Prg</th>
                                    <th class="whitespace-nowrap">Nama Program</th>
                                    <th class="whitespace-nowrap">No Dokumen</th>
                                    <th class="whitespace-nowrap text-right">Nilai Program</th>
                                    <th class="whitespace-nowrap text-center">MOP</th>
                                    <th class="whitespace-nowrap text-center">TOP</th>
                                    <th class="whitespace-nowrap text-right">Nilai Transfer</th>
                                    <th class="whitespace-nowrap text-center">Tgl Transfer</th>
                                    <th class="whitespace-nowrap text-center">Tgl FPK</th>
                                    <th class="whitespace-nowrap">NSFP</th>
                                    <th class="whitespace-nowrap text-right">DPP</th>
                                    <th class="whitespace-nowrap text-right">PPN</th>
                                    <th class="whitespace-nowrap text-right">PPH</th>
                                    <th class="whitespace-nowrap">Bukpot</th>
                                    <th class="whitespace-nowrap">Keterangan</th>
                                </tr>
                            </thead>
                            <tbody id="program-table-body">
                                <tr>
                                    <td colspan="20" class="text-center p-8">
                                        <div class="spinner-simple"></div>
                                        <p class="mt-3 text-gray-500 font-medium">Memuat data...</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div id="pagination-container" class="flex justify-between items-center mt-4">
                        <span id="pagination-info" class="text-sm text-gray-600"></span>
                        <div id="pagination-links" class="flex items-center gap-2">
                        </div>
                    </div>
                </div>

            </div>
        </section>
    </main>
    <div id="modal-finance" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title"
        role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:p-0">

            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>

            <div
                class="relative bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:max-w-md sm:w-full border border-pink-100">
                <form id="form-finance">
                    <input type="hidden" name="mode" value="finance">
                    <input type="hidden" name="nomor_dokumen" id="fin_nomor_dokumen">

                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="flex justify-between items-center mb-4 border-b pb-2">
                            <div class="flex items-center gap-2">
                                <div
                                    class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-600">
                                    <i class="fas fa-wallet"></i>
                                </div>
                                <h3 class="text-lg leading-6 font-medium text-gray-900">Update Finance</h3>
                            </div>
                            <button type="button" class="btn-close-finance text-gray-400 hover:text-gray-500">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>

                        <div class="mb-4 p-3 bg-blue-50 text-blue-800 rounded text-xs border border-blue-100">
                            Dokumen: <span id="fin_display_doc" class="font-mono font-bold"></span>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Nilai Transfer
                                    (Rp)</label>
                                <input type="text" name="nilai_transfer" id="fin_nilai_transfer"
                                    class="input-modern w-full font-mono text-right" placeholder="0">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Tanggal Transfer</label>
                                <input type="date" name="tanggal_transfer" id="fin_tanggal_transfer"
                                    class="input-modern w-full">
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-2">
                        <button type="submit"
                            class="btn-primary w-full sm:w-auto px-4 py-2 text-white rounded bg-blue-600 hover:bg-blue-700">Simpan
                        </button>
                        <button type="button"
                            class="btn-close-finance mt-3 w-full sm:w-auto px-4 py-2 border rounded bg-white text-gray-700 hover:bg-gray-50">Batal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <div id="modal-tax" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog"
        aria-modal="true">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:p-0">

            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>

            <div
                class="relative bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:max-w-lg sm:w-full border border-pink-100">
                <form id="form-tax">
                    <input type="hidden" name="mode" value="tax">
                    <input type="hidden" name="nomor_dokumen" id="tax_nomor_dokumen">

                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="flex justify-between items-center mb-4 border-b pb-2">
                            <div class="flex items-center gap-2">
                                <div
                                    class="w-8 h-8 rounded-full bg-purple-100 flex items-center justify-center text-purple-600">
                                    <i class="fas fa-file-invoice-dollar"></i>
                                </div>
                                <h3 class="text-lg leading-6 font-medium text-gray-900">Update Tax</h3>
                            </div>
                            <button type="button" class="btn-close-tax text-gray-400 hover:text-gray-500">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>

                        <div class="mb-4 p-3 bg-purple-50 text-purple-800 rounded text-xs border border-purple-100">
                            Dokumen: <span id="tax_display_doc" class="font-mono font-bold"></span>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div class="col-span-2">
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Tanggal FPK</label>
                                <input type="date" name="tgl_fpk" id="tax_tgl_fpk" class="input-modern w-full">
                            </div>
                            <div class="col-span-2">
                                <label class="block text-xs font-semibold text-gray-700 mb-1">NSFP (Faktur
                                    Pajak)</label>
                                <input type="text" name="nsfp" id="tax_nsfp" class="input-modern w-full"
                                    placeholder="No Seri Faktur Pajak" maxlength="17">
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">DPP</label>
                                <input type="text" name="dpp" id="tax_dpp"
                                    class="input-modern w-full font-mono text-right" placeholder="0">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">PPN</label>
                                <input type="text" name="ppn" id="tax_ppn"
                                    class="input-modern w-full font-mono text-right" placeholder="0">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">PPH</label>
                                <input type="text" name="pph" id="tax_pph"
                                    class="input-modern w-full font-mono text-right" placeholder="0">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Nomor Bukpot</label>
                                <input type="text" name="nomor_bukpot" id="tax_nomor_bukpot" class="input-modern w-full"
                                    placeholder="Nomor Bukti Potong" maxlength="9">
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-2">
                        <button type="submit"
                            class="btn-primary w-full sm:w-auto px-4 py-2 text-white rounded bg-purple-600 hover:bg-purple-700">Simpan
                        </button>
                        <button type="button"
                            class="btn-close-tax mt-3 w-full sm:w-auto px-4 py-2 border rounded bg-white text-gray-700 hover:bg-gray-50">Batal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
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
                        class="btn-secondary px-4 py-2 text-sm bg-gray-200 hover:bg-gray-300 rounded text-gray-700 font-medium transition-colors">
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
    <script src="../../js/program_supplier/laporan_program_supplier_handler.js" type="module"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>

</html>