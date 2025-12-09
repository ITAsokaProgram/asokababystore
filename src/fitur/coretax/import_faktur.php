<?php
session_start();
include '../../../aa_kon_sett.php';

$query_store = "SELECT Kd_Store, Nm_Alias, Nm_NPWP FROM kode_store WHERE display = 'on' ORDER BY nm_alias ASC";
$result_store = $conn->query($query_store);

require_once __DIR__ . '/../../component/menu_handler.php';

$menuHandler = new MenuHandler('pajak_import');
if (!$menuHandler->initialize()) {
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Import Faktur Pajak (Coretax)</title>

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
            padding: 1.5rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
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
            padding: 0.6rem;
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            transition: all 0.15s ease-in-out;
            background-color: #f9fafb;
        }

        .input-compact:focus {
            outline: none;
            border-color: #ec4899;
            background-color: #fff;
            box-shadow: 0 0 0 3px rgba(236, 72, 153, 0.1);
        }
    </style>
</head>

<body class="bg-gray-50">

    <?php include '../../component/navigation_report.php'; ?>
    <?php include '../../component/sidebar_report.php'; ?>

    <main id="main-content" class="flex-1 p-4 ml-64">
        <section class="min-h-screen">
            <div class="max-w-6xl mx-auto">

                <div class="header-card p-4 rounded-2xl mb-6 bg-white shadow-sm border border-gray-100">
                    <div class="flex items-center justify-between flex-wrap gap-3">
                        <div class="flex items-center gap-3">
                            <div class="bg-pink-100 p-2 rounded-lg text-pink-600">
                                <i class="fa-solid fa-cloud-arrow-up fa-lg"></i>
                            </div>
                            <div>
                                <h1 class="text-xl font-bold text-gray-800 mb-1">Import Data Coretax</h1>
                                <p class="text-xs text-gray-600">Upload file Excel Faktur Pajak dari DJP.</p>
                            </div>
                        </div>

                        <a href="data_coretax.php"
                            class="btn-secondary inline-flex items-center justify-center gap-2 no-underline text-sm px-4 py-2 rounded-lg bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 transition-colors shadow-sm">
                            <i class="fa-solid fa-list text-pink-500"></i>
                            <span>Lihat Laporan</span>
                        </a>
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-lg border border-pink-100 overflow-hidden">
                    <div class="p-6">
                        <form id="formImport" enctype="multipart/form-data">

                            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

                                <div class="lg:col-span-1 flex flex-col gap-6">

                                    <div>
                                        <label for="kode_store" class="form-label">Pilih Cabang / Toko</label>
                                        <select name="kode_store" id="kode_store" required
                                            class="input-compact cursor-pointer">
                                            <option value="" disabled selected>-- Pilih Cabang --</option>
                                            <?php if ($result_store && $result_store->num_rows > 0): ?>
                                                <?php while ($row = $result_store->fetch_assoc()): ?>
                                                    <option value="<?= htmlspecialchars($row['Kd_Store']) ?>">
                                                        <?= htmlspecialchars($row['Kd_Store']) ?> -
                                                        <?= htmlspecialchars($row['Nm_Alias']) ?>
                                                    </option>
                                                <?php endwhile; ?>
                                            <?php endif; ?>
                                        </select>
                                    </div>

                                    <div class="bg-blue-50 border border-blue-100 rounded-xl p-4">
                                        <h3 class="text-xs font-bold text-blue-800 mb-2 flex items-center gap-2">
                                            <i class="fa-solid fa-circle-info"></i> Struktur Kolom
                                        </h3>
                                        <p class="text-[11px] text-blue-700 leading-relaxed mb-2">
                                            Header di Baris 1. Data mulai Baris 2. Pastikan urutan kolom <strong>A s/d
                                                R</strong> sesuai template DJP.
                                        </p>
                                        <div
                                            class="text-[10px] text-blue-500 bg-white p-2 rounded border border-blue-100 font-mono">
                                            A: NPWP Penjual<br>
                                            C: No Faktur<br>
                                            D: Tgl Faktur<br>
                                            J: Harga Jual<br>
                                            L: PPN
                                        </div>
                                    </div>

                                    <div class="mt-auto">
                                        <button type="submit" id="btn-submit"
                                            class="w-full bg-pink-600 hover:bg-pink-700 text-white font-bold py-3 px-4 rounded-xl shadow-lg shadow-pink-200 transition-all transform hover:scale-[1.02] active:scale-95 flex items-center justify-center gap-2">
                                            <i class="fa-solid fa-upload"></i>
                                            <span>Proses Import</span>
                                        </button>
                                    </div>
                                </div>

                                <div class="lg:col-span-2 h-full">
                                    <label class="form-label mb-2">Area Upload File</label>
                                    <div class="relative h-full min-h-[300px] border-2 border-dashed border-pink-300 bg-pink-50/50 rounded-2xl text-center hover:bg-pink-50 transition-colors cursor-pointer group flex flex-col items-center justify-center"
                                        id="drop-zone">

                                        <input type="file" name="file_excel" id="file_excel"
                                            class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10"
                                            accept=".xlsx, .xls, .csv" required>

                                        <div
                                            class="space-y-4 pointer-events-none transition-transform group-hover:-translate-y-1 duration-300">
                                            <div
                                                class="w-20 h-20 bg-white rounded-full flex items-center justify-center mx-auto shadow-sm border border-pink-100">
                                                <i class="fa-solid fa-file-excel text-4xl text-green-500"></i>
                                            </div>

                                            <div class="px-4">
                                                <p class="text-gray-700 font-bold text-lg mb-1">
                                                    Drag & Drop file di sini
                                                </p>
                                                <p class="text-gray-500 text-sm">
                                                    atau <span class="text-pink-600 font-semibold underline">klik untuk
                                                        mencari</span>
                                                </p>
                                            </div>

                                            <p
                                                class="text-xs text-gray-400 bg-white/80 inline-block px-3 py-1 rounded-full border border-gray-100">
                                                Format: .xlsx, .csv (Max 5MB)
                                            </p>
                                        </div>

                                        <div id="file-name-display"
                                            class="hidden absolute bottom-4 left-0 right-0 mx-auto w-max max-w-[90%] bg-white shadow-md border border-green-100 px-4 py-2 rounded-lg flex items-center gap-3 animate-fade-in-up z-20">
                                            <i class="fa-solid fa-check-circle text-green-500"></i>
                                            <span id="file-name-text"
                                                class="text-sm font-semibold text-gray-700 truncate"></span>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </form>
                    </div>
                </div>

                <div id="result-container"
                    class="hidden mt-6 bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-sm font-bold text-gray-800 mb-3 border-b pb-2">Log Hasil Import</h3>
                    <div id="result-content"
                        class="text-xs font-mono bg-gray-900 text-green-400 p-4 rounded-lg h-64 overflow-y-auto custom-scrollbar">
                    </div>
                </div>

            </div>
        </section>
    </main>

    <script src="/src/js/middleware_auth.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="../../js/coretax/import_handler.js" type="module"></script>

</body>

</html>