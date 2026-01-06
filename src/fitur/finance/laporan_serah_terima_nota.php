<?php
session_start();
include '../../../aa_kon_sett.php';

// Default Tanggal
$tanggal_kemarin = date('Y-m-d', strtotime('-1 day'));
$default_tgl_mulai = $tanggal_kemarin;
$default_tgl_selesai = $tanggal_kemarin;

$default_filter_type = 'month';
$default_bulan = date('m');
$default_tahun = date('Y');

$tgl_mulai = $_GET['tgl_mulai'] ?? $default_tgl_mulai;
$tgl_selesai = $_GET['tgl_selesai'] ?? $default_tgl_selesai;

$filter_type = $_GET['filter_type'] ?? $default_filter_type;
$bulan = $_GET['bulan'] ?? $default_bulan;
$tahun = $_GET['tahun'] ?? $default_tahun;

$default_page = 1;
$page = (int) ($_GET['page'] ?? $default_page);
if ($page < 1) {
    $page = 1;
}

$list_bulan = [
    '01' => 'Januari',
    '02' => 'Februari',
    '03' => 'Maret',
    '04' => 'April',
    '05' => 'Mei',
    '06' => 'Juni',
    '07' => 'Juli',
    '08' => 'Agustus',
    '09' => 'September',
    '10' => 'Oktober',
    '11' => 'November',
    '12' => 'Desember'
];

require_once __DIR__ . '/../../component/menu_handler.php';
// Pastikan permission key ini sesuai dengan database Anda, atau ganti 'finance_laporan' jika perlu
// $menuHandler = new MenuHandler('finance_laporan_surat_terima');
// if (!$menuHandler->initialize()) {
//     exit();
// }
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Serah Terima Nota</title>
    <link rel="stylesheet" href="../../style/header.css">
    <link rel="stylesheet" href="../../style/sidebar.css">
    <link rel="stylesheet" href="../../style/animation-fade-in.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../style/default-font.css">
    <link rel="stylesheet" href="../../output2.css">
    <link rel="stylesheet" href="../../style/pink-theme.css">
    <link rel="icon" type="image/png" href="../../../public/images/logo1.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/all.min.css">
    <style>
        #form-otorisasi input:disabled,
        #form-otorisasi select:disabled {
            background-color: #f3f4f6 !important;
            color: #9ca3af !important;
            cursor: not-allowed !important;

        }

        #form-otorisasi #auth_status_baru:disabled {
            background-color: #f3f4f6 !important;
            color: #9ca3af !important;
            cursor: not-allowed !important;
        }



        .table-container {
            max-height: 70vh;
            overflow-y: auto;
            position: relative;
            border-radius: 0.5rem;
        }

        .table-modern thead th {
            position: sticky;
            top: 0;
            z-index: 20;
            background-color: #fdf2f8;

            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
            border-bottom: 2px solid #fbcfe8;
        }
    </style>
</head>

<body class="bg-gray-50 theme-pembelian">
    <?php include '../../component/navigation_report.php' ?>
    <?php include '../../component/sidebar_report.php' ?>

    <main id="main-content" class="flex-1 p-4 ml-64">
        <section class="min-h-screen">
            <div class="max-w-7xl mx-auto">

                <div class="header-card p-4 rounded-2xl mb-4">
                    <div class="flex items-center justify-between flex-wrap gap-3">
                        <div class="flex items-center gap-3">
                            <div class="icon-wrapper">
                                <i class="fa-solid fa-file-invoice fa-lg"></i>
                            </div>
                            <div>
                                <h1 id="page-title" class="text-xl font-bold text-gray-800 mb-1">Laporan Serah Terima
                                    Nota
                                </h1>
                                <p id="page-subtitle" class="text-xs text-gray-600">Memuat data...</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <a href="input_serah_terima_nota.php"
                                class="btn-primary flex items-center gap-2 px-4 py-2 shadow-lg shadow-pink-500/30 rounded-lg text-white transition-transform hover:scale-105 text-sm decoration-0">
                                <i class="fas fa-plus"></i> <span>Input Baru</span>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="filter-card-simple">
                    <form id="filter-form" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-3 items-end"
                        method="GET">

                        <div class="lg:col-span-1">
                            <label for="filter_type" class="block text-xs font-semibold text-gray-700 mb-2">
                                <i class="fas fa-filter text-pink-600 mr-1"></i> Mode Periode
                            </label>
                            <select name="filter_type" id="filter_type"
                                class="input-modern w-full font-semibold text-pink-700 bg-pink-50 border-pink-200">
                                <option value="month" <?php echo ($filter_type == 'month') ? 'selected' : ''; ?>>Per Bulan
                                </option>
                                <option value="date_range" <?php echo ($filter_type == 'date_range') ? 'selected' : ''; ?>>Rentang Tanggal</option>
                            </select>
                        </div>

                        <div id="container-month" class="contents">
                            <div class="lg:col-span-1">
                                <label for="bulan" class="block text-xs font-semibold text-gray-700 mb-2">
                                    <i class="fas fa-calendar-check text-pink-600 mr-1"></i> Bulan
                                </label>
                                <select name="bulan" id="bulan" class="input-modern w-full">
                                    <?php foreach ($list_bulan as $key => $val): ?>
                                        <option value="<?= $key ?>" <?= ($bulan == $key) ? 'selected' : '' ?>><?= $val ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="lg:col-span-1">
                                <label for="tahun" class="block text-xs font-semibold text-gray-700 mb-2">
                                    <i class="fas fa-calendar text-pink-600 mr-1"></i> Tahun
                                </label>
                                <input type="number" name="tahun" id="tahun" class="input-modern w-full"
                                    value="<?= $tahun ?>" min="2000" max="2100">
                            </div>
                        </div>

                        <div id="container-date-range" class="contents" style="display: none;">
                            <div class="lg:col-span-1">
                                <label for="tgl_mulai" class="block text-xs font-semibold text-gray-700 mb-2">
                                    <i class="fas fa-calendar-alt text-pink-600 mr-1"></i> Dari
                                </label>
                                <input type="date" name="tgl_mulai" id="tgl_mulai" class="input-modern w-full"
                                    value="<?php echo htmlspecialchars($tgl_mulai); ?>">
                            </div>
                            <div class="lg:col-span-1">
                                <label for="tgl_selesai" class="block text-xs font-semibold text-gray-700 mb-2">
                                    <i class="fas fa-calendar-alt text-pink-600 mr-1"></i> Sampai
                                </label>
                                <input type="date" name="tgl_selesai" id="tgl_selesai" class="input-modern w-full"
                                    value="<?php echo htmlspecialchars($tgl_selesai); ?>">
                            </div>
                        </div>


                        <div class="lg:col-span-1">
                            <label class="block text-xs font-semibold text-gray-700 mb-2">Kontra?</label>
                            <select name="status_kontra" id="filter_status_kontra" class="input-modern w-full">
                                <option value="">Semua</option>
                                <option value="Sudah" <?= ($_GET['status_kontra'] ?? '') == 'Sudah' ? 'selected' : '' ?>>
                                    Sudah</option>
                                <option value="Belum" <?= ($_GET['status_kontra'] ?? '') == 'Belum' ? 'selected' : '' ?>>
                                    Belum</option>
                            </select>
                        </div>

                        <div class="lg:col-span-1">
                            <label class="block text-xs font-semibold text-gray-700 mb-2">Bayar?</label>
                            <select name="status_bayar" id="filter_status_bayar" class="input-modern w-full">
                                <option value="">Semua</option>
                                <option value="Sudah" <?= ($_GET['status_bayar'] ?? '') == 'Sudah' ? 'selected' : '' ?>>
                                    Sudah</option>
                                <option value="Belum" <?= ($_GET['status_bayar'] ?? '') == 'Belum' ? 'selected' : '' ?>>
                                    Belum</option>
                            </select>
                        </div>

                        <div class="lg:col-span-1">
                            <label class="block text-xs font-semibold text-gray-700 mb-2">Pinjam?</label>
                            <select name="status_pinjam" id="filter_status_pinjam" class="input-modern w-full">
                                <option value="">Semua</option>
                                <option value="Pinjam" <?= ($_GET['status_pinjam'] ?? '') == 'Pinjam' ? 'selected' : '' ?>>
                                    Pinjam</option>
                                <option value="Tidak" <?= ($_GET['status_pinjam'] ?? '') == 'Tidak' ? 'selected' : '' ?>>
                                    Tidak</option>
                            </select>
                        </div>
                        <div class="lg:col-span-2 lg:col-start-4 lg:row-start-1">
                            <label for="search_supplier" class="block text-xs font-semibold text-gray-700 mb-2">
                                <i class="fas fa-search text-pink-600 mr-1"></i> Cari Data
                            </label>
                            <div class="flex gap-2">
                                <input type="text" name="search_supplier" id="search_supplier"
                                    class="input-modern w-full"
                                    value="<?php echo htmlspecialchars($_GET['search_supplier'] ?? ''); ?>"
                                    placeholder="Supplier / No Nota / Faktur...">

                                <button type="submit" id="filter-submit-button"
                                    class="btn-primary inline-flex items-center justify-center gap-2 px-6">
                                    <i class="fas fa-filter"></i>
                                    <span>Tampilkan</span>
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
                    <div class="table-container">
                        <table class="table-modern" id="receipt-table">
                            <thead>
                                <tr>
                                    <th class="text-center whitespace-nowrap">Aksi</th>
                                    <th class="whitespace-nowrap">Tgl Invoice</th>
                                    <th class="whitespace-nowrap">Nama Supplier</th>
                                    <th class="whitespace-nowrap">No Faktur</th>
                                    <th class="text-right whitespace-nowrap">Nominal</th>
                                    <th class="whitespace-nowrap">Tgl Diserahkan</th>
                                    <th class="whitespace-nowrap">Tgl Diterima</th>
                                    <th class="text-center whitespace-nowrap">Status Terima</th>
                                    <th class="text-center whitespace-nowrap">Kontra</th>
                                    <th class="text-center whitespace-nowrap">Bayar</th>
                                    <th class="text-center whitespace-nowrap">Pinjam</th>
                                    <th class="whitespace-nowrap">Diberikan</th>
                                    <th class="whitespace-nowrap">Penerima</th>

                                </tr>
                            </thead>
                            <tbody id="receipt-table-body">
                                <tr>
                                    <td colspan="14" class="text-center p-8">
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
    <div id="modal-otorisasi" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title"
        role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div
                class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full">
                <form id="form-otorisasi">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="flex justify-between items-center mb-4 border-b pb-2">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">Update Status Nota</h3>
                            <button type="button" class="btn-close-auth text-gray-400 hover:text-gray-500">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>

                        <input type="hidden" id="auth_nota_id" name="no_faktur_lama">
                        <input type="hidden" id="auth_nominal_awal">

                        <div id="alert-dependency"
                            class="hidden mb-4 p-3 bg-yellow-50 border border-yellow-200 text-yellow-700 rounded-lg text-xs flex items-start gap-2"
                            style="max-width: 412px; width: 100%">
                            <i class="fas fa-info-circle mt-0.5"></i>
                            <span>Harap isi <b>Status Terima (Sudah)</b>, <b>Tanggal</b>, dan <b>Penerima</b> terlebih
                                dahulu untuk mengubah status lainnya.</span>
                        </div>

                        <div id="alert-locked-paid" style="max-width: 412px; width: 100%"
                            class="hidden mb-4 p-3 bg-red-50 border border-red-200 text-red-700 rounded-lg text-xs flex items-start gap-2">
                            <i class="fas fa-lock mt-0.5"></i>
                            <span> Tidak bisa edit Nomor Faktur & Nominal karena Status sudah dibayar</span>
                        </div>

                        <div id="alert-locked-kontra" style="max-width: 412px; width: 100%"
                            class="hidden mb-4 p-3 bg-blue-50 border border-blue-200 text-blue-700 rounded-lg text-xs flex items-start gap-2">
                            <i class="fas fa-info-circle mt-0.5"></i>
                            <span><b>Info:</b> Status Kontra <b>"Sudah"</b>, tidak dapat diubah kembali.</span>
                        </div>

                        <div id="alert-locked-bayar-status" style="max-width: 412px; width: 100%"
                            class="hidden mb-4 p-3 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-lg text-xs flex items-start gap-2">
                            <i class="fas fa-check-circle mt-0.5"></i>
                            <span><b>Info:</b> Status Bayar <b>"Sudah"</b>, tidak dapat diubah kembali.</span>
                        </div>

                        <div class="p-3 bg-pink-50 rounded-lg border border-pink-100 mb-4">
                            <h4 class="text-xs font-bold text-pink-700 mb-2 uppercase">Edit Data Nota</h4>
                            <div class="flex flex-wrap gap-4 mb-2">
                                <div class="col-span-2">
                                    <label class="block text-xs font-semibold text-gray-700 mb-1">No Faktur</label>
                                    <input type="text" name="no_faktur_baru" id="auth_no_faktur_baru"
                                        class="input-modern w-full font-mono text-sm" placeholder="No Faktur">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1">Nominal</label>
                                    <input type="number" step="0.01" name="nominal" id="auth_nominal"
                                        class="input-modern w-full font-mono text-sm text-right" placeholder="0">
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div class="md:col-span-2">
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Status Terima <span
                                        class="text-red-500">*</span></label>
                                <select name="status" id="auth_status_baru" class="input-modern w-full font-bold">
                                    <option value="Belum Terima">Belum Terima</option>
                                    <option value="Sudah Terima">Sudah Terima</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Tanggal Terima <span
                                        class="text-red-500">*</span></label>
                                <input type="date" name="tgl_diterima" id="auth_tgl_diterima"
                                    class="input-modern w-full">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Penerima <span
                                        class="text-red-500">*</span></label>
                                <input type="text" name="penerima" id="auth_penerima" class="input-modern w-full"
                                    placeholder="Nama Penerima" autocomplete="off">
                            </div>
                        </div>

                        <hr class="border-gray-100 mb-4">

                        <div class="grid grid-cols-3 gap-3 mb-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Status Kontra</label>
                                <select name="status_kontra" id="auth_status_kontra" class="input-modern w-full">
                                    <option value="Belum">Belum</option>
                                    <option value="Sudah">Sudah</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Status Bayar</label>
                                <select name="status_bayar" id="auth_status_bayar" class="input-modern w-full">
                                    <option value="Belum">Belum</option>
                                    <option value="Sudah">Sudah</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Status Pinjam</label>
                                <select name="status_pinjam" id="auth_status_pinjam" class="input-modern w-full">
                                    <option value="Tidak">Tidak</option>
                                    <option value="Pinjam">Pinjam</option>
                                </select>
                            </div>
                        </div>

                        <div class="p-4 border border-red-200 rounded-lg bg-red-50">
                            <div class="mb-3">
                                <label class="block text-xs font-semibold text-gray-700 mb-1">User (Inisial)</label>
                                <input type="text" name="nama_user_cek" class="input-modern w-full"
                                    placeholder="Contoh: ADM" required autocomplete="off">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Kode Otorisasi</label>
                                <input type="password" name="kode_otorisasi" class="input-modern w-full"
                                    placeholder="Password Otorisasi" required>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-2">
                        <button type="submit" class="btn-primary w-full sm:w-auto px-4 py-2 text-white rounded">Simpan
                            Perubahan</button>
                        <button type="button"
                            class="btn-close-auth mt-3 w-full sm:w-auto px-4 py-2 border rounded">Batal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div id="modal-detail-cod" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title"
        role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" aria-hidden="true"
                onclick="document.getElementById('modal-detail-cod').classList.add('hidden')"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div
                class="inline-block w-full overflow-hidden text-left align-bottom transition-all transform bg-white rounded-lg shadow-xl sm:my-8 sm:align-middle max-w-lg">
                <div class="px-4 pt-5 pb-4 bg-white sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div
                            class="flex items-center justify-center flex-shrink-0 w-12 h-12 mx-auto bg-blue-100 rounded-full sm:mx-0 sm:h-10 sm:w-10">
                            <i class="text-blue-600 fa-solid fa-truck-fast"></i>
                        </div>
                        <div class="w-full mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 class="text-lg font-medium leading-6 text-gray-900" id="modal-title">Detail COD</h3>
                            <div class="mt-4 space-y-3">
                                <div class="grid grid-cols-2 gap-2 pb-2 border-b border-gray-100">
                                    <div>
                                        <p class="text-xs text-gray-500">Tanggal Masuk Nota</p>
                                        <p class="font-semibold text-gray-800" id="cod_tgl_masuk">-</p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500">Cabang Penerima</p>
                                        <p class="font-semibold text-gray-800" id="cod_cabang">-</p>
                                    </div>
                                    <div class="col-span-2 mt-1">
                                        <p class="text-xs text-gray-500">Keterangan Dokumen</p>
                                        <span id="cod_lengkap_badge"
                                            class="px-2 py-1 text-xs font-bold rounded-full"></span>
                                    </div>
                                </div>

                                <div id="cod_bank_section" class="hidden pt-2">
                                    <h4 class="mb-2 text-xs font-bold tracking-wide text-gray-400 uppercase">Informasi
                                        Transfer</h4>
                                    <div class="p-3 rounded-lg bg-gray-50">
                                        <div class="grid grid-cols-1 gap-2">
                                            <div>
                                                <p class="text-xs text-gray-500">Bank</p>
                                                <p class="font-bold text-gray-800" id="cod_bank_name">-</p>
                                            </div>
                                            <div>
                                                <p class="text-xs text-gray-500">No. Rekening</p>
                                                <p class="font-mono text-sm font-semibold text-gray-800"
                                                    id="cod_no_rek">-</p>
                                            </div>
                                            <div>
                                                <p class="text-xs text-gray-500">Atas Nama</p>
                                                <p class="font-semibold text-gray-800" id="cod_an_rek">-</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div id="cod_no_bank_msg" class="hidden pt-2 text-center">
                                    <p class="text-xs italic text-gray-400">Tidak ada informasi rekening.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="px-4 py-3 bg-gray-50 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button" onclick="document.getElementById('modal-detail-cod').classList.add('hidden')"
                        class="inline-flex justify-center w-full px-4 py-2 text-base font-medium text-white bg-blue-600 border border-transparent rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="/src/js/middleware_auth.js"></script>
    <script src="../../js/finance/laporan_serah_terima_nota_handler.js" type="module"></script>

    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>

</html>