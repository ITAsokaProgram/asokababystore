<?php
session_start();
include '../../../aa_kon_sett.php';
require_once __DIR__ . '/../../component/menu_handler.php';
$menuHandler = new MenuHandler('wa_balasan');

$TEMPLATE_CONTACTS = [
    'Jabodetabek' => [
        "Daan Mogot" => "6281808174105",
        "Poris" => "6281806683401",
        "Harapan Indah" => "6287889552647",
        "Bintaro" => "6287775692431",
        "Cinere" => "6287787987127",
        "Pamulang" => "6285947461478",
        "Ciledug" => "6287849816901",
        "Kartini" => "6287849816904",
        "Parung" => "6287887689802",
        "Condet" => "6287739974652",
        "Duren Sawit" => "6285951449821",
        "Rawamangun" => "6287773844521",
        "Cibubur" => "6287863814646",
        "Ceger" => "6285965847263",
        "Jatiwaringin" => "6281998482529",
        "Graha Raya" => "6287846959785",
        "Galaxy" => "6287852415221",
        "Jatiasih" => "6287856599869",
        "PIK 2" => "6287772562015",
    ],
    "Bangka & Belitung" => [
        "Pangkal Pinang" => "6287896370431",
        "Merapin" => "6287797561846",
        "Toboali" => "6281995651279",
        "Semabung" => "6281908239741",
        "Koba" => "6285933237653",
        "Sungailiat" => "6285933237651",
        "Tanjung Pandan" => "6281929765780",
        "Air Raya" => "6281929746487",
        "Manggar" => "6287866839246",
    ]
];

$TEMPLATE_LOCATIONS = [
    'Jabodetabek' => [
        'Daan Mogot' => ['latitude' => '-6.1503038', 'longitude' => '106.7107386', 'name' => 'ASOKA Baby Store Daan Mogot', 'address' => 'Perumahan Daan Mogot Baru, Jalan Gilimanuk No. 38, Kalideres, Kec. Kalideres, Kota Jakarta Barat'],
        'Poris' => ['latitude' => '-6.1745246', 'longitude' => '106.6827735', 'name' => 'ASOKA Baby Store Poris', 'address' => 'Garden, Jl. Raya Poris Indah Blok A1 No.3, Cipondoh Indah, Kec. Cipondoh, Kota Tangerang'],
        'Harapan Indah' => ['latitude' => '-6.1867495', 'longitude' => '106.9794397', 'name' => 'ASOKA Baby Store Harapan Indah', 'address' => 'Ruko Boulevard Hijau, Jl. Boulevard Hijau Raya No.38, Pejuang, Kecamatan Medan Satria, Kota Bks'],
        'Bintaro' => ['latitude' => '-6.2701572', 'longitude' => '106.7314572', 'name' => 'ASOKA Baby Store Bintaro', 'address' => 'Jl. Bintaro Utama 5 Blok EA No. 21-23, East Jurang Manggu, Pondok Aren, South Tangerang City'],
        'Cinere' => ['latitude' => '-6.3407371', 'longitude' => '106.7767895', 'name' => 'ASOKA Baby Store Cinere', 'address' => 'Jl cinere raya NC 17, Cinere, Kec. Cinere, Kota Depok'],
        'Pamulang' => ['latitude' => '-6.3433559', 'longitude' => '106.7277418', 'name' => 'ASOKA Baby Store Pamulang', 'address' => 'Jl. Siliwangi No.9 Blok E, West Pamulang, Pamulang, South Tangerang City'],
        'Ciledug' => ['latitude' => '-6.2274337', 'longitude' => '106.7152493', 'name' => 'ASOKA Baby Store Ciledug', 'address' => 'JL HOS COKROAMINOTO BLOK 0 NO. 18 SUDIMARA TIMUR CILEDUG. TANGERANG'],
        'Kartini' => ['latitude' => '-6.4024028', 'longitude' => '106.8160667', 'name' => 'ASOKA Baby Store Kartini', 'address' => 'Jl. Kartini No.43, Depok, Kec. Pancoran Mas, Kota Depok'],
        'Parung' => ['latitude' => '-6.4387641', 'longitude' => '106.6980704', 'name' => 'ASOKA Baby Store Parung', 'address' => 'Jl. H. Mawi No.1A, Bojong Sempu, Kec. Parung, Kabupaten Bogor'],
        'Condet' => ['latitude' => '-6.2707673', 'longitude' => '106.8585867', 'name' => 'ASOKA Baby Store Condet', 'address' => 'JL RAYA CONDET BLOK O NO. 39 BATU AMPAR KRAMAT JATI, JAKARTA TIMUR'],
        'Duren Sawit' => ['latitude' => '-6.2428015', 'longitude' => '106.9007402', 'name' => 'ASOKA Baby Store Duren Sawit', 'address' => 'RT.5/RW.12, Pd. Bambu, Kec. Duren Sawit, Kota Jakarta Timur'],
        'Rawamangun' => ['latitude' => '-6.2005677', 'longitude' => '106.8926293', 'name' => 'ASOKA Baby Store Rawamangun', 'address' => 'Jl. Tawes No.27 3, RT.3/RW.7, Jati, Kec. Pulo Gadung, Kota Jakarta Timur'],
        'Cibubur' => ['latitude' => '-6.3475857', 'longitude' => '106.8726729', 'name' => 'ASOKA Baby Store Cibubur', 'address' => 'Jl. Lap. Tembak Cibubur No.131, Pekayon, Kec. Ciracas, Kota Jakarta Timur'],
        'Ceger' => ['latitude' => '-6.26322', 'longitude' => '106.7237342', 'name' => 'ASOKA Baby Store Ceger', 'address' => 'Jl. Ceger Raya No.22, Jurang Manggu Tim., Kec. Pd. Aren, Kota Tangerang Selatan'],
        'Jatiwaringin' => ['latitude' => '-6.2760389', 'longitude' => '106.9101746', 'name' => 'ASOKA Baby Store Jati Waringin', 'address' => 'Jl. Raya Jatiwaringin No.56, Jatiwaringin, Kec. Pd. Gede, Kota Bks'],
        'Graha Raya' => ['latitude' => '-6.2360847', 'longitude' => '106.6756861', 'name' => 'ASOKA Baby Store Graha Raya', 'address' => 'Jl. Boulevard Graha Raya No.11a, Sudimara Pinang, Kec. Serpong Utara, Kota Tangerang Selatan'],
        'Galaxy' => ['latitude' => '-6.2594662', 'longitude' => '106.9679006', 'name' => 'ASOKA Baby Store Taman Galaxy', 'address' => 'Jl. Pulosirih Tengah 17 No.149 Blok E, Pekayon Jaya, Kec. Bekasi Sel., Kota Bks'],
        'Jatiasih' => ['latitude' => '-6.2933534', 'longitude' => '106.9588403', 'name' => 'ASOKA Baby Store Jati Asih', 'address' => 'Jl. Raya Jatiasih No.86, Jatiasih, Kec. Jatiasih, Kota Bks'],
        'PIK 2' => ['latitude' => '-6.0514482', 'longitude' => '106.6860203', 'name' => 'ASOKA Baby Store PIK 2', 'address' => 'Soho Orchard Boulevard Blok A No. 15, Salembaran, Kec. Kosambi, Kabupaten Tangerang']
    ],
    "Bangka & Belitung" => [
        'Pangkal Pinang' => ['latitude' => '-2.13295', 'longitude' => '106.11545', 'name' => 'ASOKA Supermarket & Departemen Store Pangkal Pinang', 'address' => 'Jl. Ahmad Yani No.1, Batin Tikal, Kec. Taman Sari, Kota Pangkal Pinang, Kepulauan Bangka Belitung 33684, Indonesia'],
        'Merapin' => ['latitude' => '-2.14881', 'longitude' => '106.13313', 'name' => 'ASOKA Baby Store - Merapin', 'address' => 'Ruko City Hill, Jl. Kampung Melayu No.Raya A1-A3, Bukit Merapin, Kec. Gerunggang, Kota Pangkal Pinang, Kepulauan Bangka Belitung 33123, Indonesia'],
        'Toboali' => ['latitude' => '-3.0106671', 'longitude' => '106.4563138', 'name' => 'Asoka Toboali Bangka Selatan', 'address' => 'XFQ4+MGX, Toboali, Kec. Toboali, Kabupaten Bangka Selatan, Kepulauan Bangka Belitung 33783, Indonesia'],
        'Semabung' => ['latitude' => '-2.1350629', 'longitude' => '106.1202253', 'name' => 'Asoka Baby Store Semabung', 'address' => 'Semabung Lama, Kec. Bukitintan, Kota Pangkal Pinang, Kepulauan Bangka Belitung 33684, Indonesia'],
        'Koba' => ['latitude' => '-2.50462', 'longitude' => '106.30337', 'name' => 'ASOKA Supermarket Koba', 'address' => 'FCW3+485, Simpang Perlang, Kec. Koba, Kabupaten Bangka Tengah, Kepulauan Bangka Belitung, Indonesia'],
        'Sungailiat' => ['latitude' => '-1.85404', 'longitude' => '106.12196', 'name' => 'ASOKA Supermarket & Department Store Sungailiat', 'address' => 'Jl. Jenderal Sudirman No.127, Sungailiat, Sungai Liat, Kabupaten Bangka, Kepulauan Bangka Belitung 33215, Indonesia'],
        'Tanjung Pandan' => ['latitude' => '-2.73752', 'longitude' => '107.63004', 'name' => 'ASOKA Baby Store - Tanjung Pandan', 'address' => '7J6J+X25, Parit, Kec. Tj. Pandan, Kabupaten Belitung, Kepulauan Bangka Belitung 33411, Indonesia'],
        'Air Raya' => ['latitude' => '-2.74709', 'longitude' => '107.65842', 'name' => 'Asoka Baby Store Air Raya', 'address' => '7M35+59Q, Jl. Jend. Sudirman, Lesung Batang, Kec. Tj. Pandan, Kabupaten Belitung, Kepulauan Bangka Belitung 33412, Indonesia'],
        'Manggar' => ['latitude' => '-2.8607083', 'longitude' => '108.2837832', 'name' => 'Asoka Baby Store Manggar', 'address' => '47QM+WXW, Kurnia Jaya, Kec. Manggar, Kabupaten Belitung Timur, Kepulauan Bangka Belitung 33512, Indonesia'],
    ]
];
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

    <script>
        window.TEMPLATE_DATA = {
            contacts: <?php echo json_encode($TEMPLATE_CONTACTS); ?>,
            locations: <?php echo json_encode($TEMPLATE_LOCATIONS); ?>
        };
    </script>

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

        /* Scrollbar custom untuk container pesan */
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

        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #9ca3af;
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
                                <p class="text-xs text-gray-600">Kelola keyword dan respon multi-pesan</p>
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
                            Daftar Balasan Otomatis
                        </h3>
                    </div>
                    <div class="table-container">
                        <table class="table-modern">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width: 60px;">No</th>
                                    <th style="width: 200px;">Kata Kunci</th>
                                    <th>Preview Balasan</th>
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
                                    <p class="text-xs text-green-100 mt-0.5">Konfigurasi keyword dan urutan pesan</p>
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
                        <input type="hidden" name="id" id="data_id">

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
                                Bot akan merespon jika pesan masuk mengandung kata ini.
                            </p>
                        </div>

                        <div>
                            <label class="form-label-required block text-sm font-bold text-gray-700 mb-2">
                                <i class="fas fa-comments mr-1"></i>
                                Daftar Balasan Otomatis
                            </label>
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 mb-3 text-xs text-blue-800">
                                <i class="fas fa-lightbulb mr-1"></i>
                                Pesan akan dikirim secara berurutan dari atas ke bawah.
                            </div>

                            <div id="message-container"
                                class="space-y-3 max-h-[350px] overflow-y-auto pr-2 custom-scrollbar">
                            </div>

                            <button type="button" id="btn-add-message"
                                class="mt-3 w-full py-2.5 border-2 border-dashed border-green-500 text-green-600 rounded-lg hover:bg-green-50 transition-colors text-sm font-bold flex items-center justify-center gap-2">
                                <i class="fas fa-plus-circle"></i> Tambah Pesan Lagi
                            </button>
                        </div>

                        <div class="input-group pt-2">
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