<?php
session_start();
include '../../../aa_kon_sett.php';

$query_store = "SELECT Kd_Store, Nm_Alias, Nm_NPWP FROM kode_store WHERE display = 'on' ORDER BY nm_alias ASC";
$result_store = $conn->query($query_store);
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
</head>

<body class="bg-gray-50">

    <?php include '../../component/navigation_report.php'; ?>
    <?php include '../../component/sidebar_report.php'; ?>

    <main id="main-content" class="flex-1 p-4 ml-64">
        <section class="min-h-screen">
            <div class="max-w-4xl mx-auto">

                <div class="header-card p-4 rounded-2xl mb-6 bg-white shadow-sm border border-gray-100">
                    <div class="flex items-center justify-between flex-wrap gap-3">
                        <div class="flex items-center gap-3">
                            <div class="bg-pink-100 p-2 rounded-lg text-pink-600">
                                <i class="fa-solid fa-file-invoice-dollar fa-lg"></i>
                            </div>
                            <div>
                                <h1 class="text-xl font-bold text-gray-800 mb-1">Import Data Coretax</h1>
                                <p class="text-xs text-gray-600">Upload file Excel Faktur Pajak.</p>
                            </div>
                        </div>
                        <a href="index.php"
                            class="btn-secondary inline-flex items-center justify-center gap-2 no-underline text-sm px-4 py-2 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 transition-colors">
                            <i class="fa-solid fa-arrow-left"></i>
                            <span>Kembali</span>
                        </a>
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-lg border border-pink-100 overflow-hidden">
                    <div class="p-6">
                        <form id="formImport" enctype="multipart/form-data">

                            <div class="mb-6">
                                <label for="kode_store" class="block text-sm font-semibold text-gray-700 mb-2">Pilih
                                    Cabang / Store</label>
                                <select name="kode_store" id="kode_store" required
                                    class="w-full px-4 py-2.5 rounded-xl border border-gray-300 focus:border-pink-500 focus:ring-2 focus:ring-pink-200 outline-none transition-all text-sm bg-gray-50">
                                    <option value="" disabled selected>-- Pilih Cabang --</option>
                                    <?php if ($result_store && $result_store->num_rows > 0): ?>
                                        <?php while ($row = $result_store->fetch_assoc()): ?>
                                            <option value="<?= htmlspecialchars($row['Kd_Store']) ?>">
                                                <?= htmlspecialchars($row['Kd_Store']) ?> -
                                                <?= htmlspecialchars($row['Nm_Alias']) ?>
                                                (<?= htmlspecialchars($row['Nm_NPWP']) ?>)
                                            </option>
                                        <?php endwhile; ?>
                                    <?php endif; ?>
                                </select>
                            </div>

                            <div class="mb-6">
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Upload File Excel (.xlsx /
                                    .csv)</label>
                                <div class="relative border-2 border-dashed border-pink-300 bg-pink-50 rounded-xl p-8 text-center hover:bg-pink-100 transition-colors cursor-pointer"
                                    id="drop-zone">
                                    <input type="file" name="file_excel" id="file_excel"
                                        class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                                        accept=".xlsx, .xls, .csv" required>

                                    <div class="space-y-3 pointer-events-none">
                                        <i class="fa-solid fa-cloud-arrow-up text-4xl text-pink-500"></i>
                                        <p class="text-gray-600 text-sm font-medium">
                                            <span class="text-pink-600 underline">Klik untuk upload</span> atau drag &
                                            drop file di sini
                                        </p>
                                        <p class="text-xs text-gray-400">Format yang didukung: XLSX, CSV (Max 5MB)</p>
                                    </div>
                                </div>
                                <div id="file-name-display"
                                    class="mt-2 text-sm text-gray-600 font-medium hidden flex items-center gap-2">
                                    <i class="fa-solid fa-file-excel text-green-600"></i>
                                    <span id="file-name-text"></span>
                                </div>
                            </div>

                            <div class="bg-blue-50 border border-blue-100 rounded-lg p-4 mb-6">
                                <h3 class="text-xs font-bold text-blue-800 mb-2"><i
                                        class="fa-solid fa-circle-info mr-1"></i> Struktur Kolom (Baris 1 Header, Data
                                    mulai Baris 2)</h3>
                                <p class="text-[10px] text-blue-600 leading-relaxed">
                                    Pastikan urutan kolom Excel dari <strong>A sampai R</strong>: <br>
                                    NPWP Penjual, Nama Penjual, No Faktur, Tgl Faktur, Masa, Tahun, Masa Kredit, Tahun
                                    Kredit, Status, Harga Jual, DPP Lain, PPN, PPnBM, Perekam, No SP2D, Valid,
                                    Dilaporkan, Dilaporkan Oleh Penjual.
                                </p>
                            </div>

                            <button type="submit" id="btn-submit"
                                class="w-full bg-pink-600 hover:bg-pink-700 text-white font-bold py-3 px-4 rounded-xl shadow-lg shadow-pink-200 transition-all transform hover:scale-[1.01] flex items-center justify-center gap-2">
                                <i class="fa-solid fa-upload"></i>
                                <span>Proses Import Data</span>
                            </button>
                        </form>
                    </div>
                </div>

                <div id="result-container"
                    class="hidden mt-6 bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-sm font-bold text-gray-800 mb-3">Hasil Import</h3>
                    <div id="result-content"
                        class="text-xs font-mono bg-gray-900 text-green-400 p-4 rounded-lg h-64 overflow-y-auto">
                    </div>
                </div>

            </div>
        </section>
    </main>

    <script src="/src/js/middleware_auth.js"></script>
    <script src="../../js/shared/internal/sidebar-profile.js" defer></script>

    <script src="../../js/coretax/import_handler.js" type="module"></script>

</body>

</html>