<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buat Jadwal SO</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/all.min.css">
    <link rel="stylesheet" href="../../../style/header.css">
    <link rel="stylesheet" href="../../../style/sidebar.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../../style/default-font.css">
    <link rel="stylesheet" href="../../../output2.css">
    
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
        /* Custom select style simple */
        .multi-select-container {
            height: 200px;
            overflow-y: auto;
            border: 1px solid #e2e8f0;
            border-radius: 0.5rem;
        }
    </style>
</head>

<body class="bg-gradient-to-br from-slate-50 to-blue-50 text-gray-900">

    <?php include '../../../component/navigation_report.php'; ?>
    <?php include '../../../component/sidebar_report.php'; ?>

    <main id="main-content" class="flex-1 p-4 lg:p-6 transition-all duration-300 ml-64 mt-16">
        <div class="max-w-4xl mx-auto space-y-6">
            
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-800">
                            <i class="fa-solid fa-calendar-plus mr-2 text-blue-600"></i>
                            Buat Jadwal SO
                        </h1>
                        <p class="text-gray-500 mt-1">Buat jadwal stock opname untuk multi-cabang & supplier.</p>
                    </div>
                    <a href="javascript:history.back()" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition-colors">
                        <i class="fa-solid fa-arrow-left mr-2"></i> Kembali
                    </a>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden">
                <form id="formJadwalSO" class="p-6 space-y-6">
                    
                    <div class="space-y-3">
                        <label class="block text-sm font-semibold text-gray-700">
                            1. Pilih Cabang (Store)
                            <span class="text-red-500">*</span>
                        </label>
                        <div id="loading-cabang" class="hidden text-sm text-blue-500"><i class="fas fa-spinner fa-spin"></i> Memuat cabang...</div>
                        
                        <div class="multi-select-container p-2 bg-gray-50 space-y-1" id="container-cabang">
                            </div>
                        <div class="flex justify-end text-xs text-gray-500">
                            <button type="button" id="btn-select-all-cabang" class="text-blue-600 hover:underline mr-3">Pilih Semua</button>
                            <button type="button" id="btn-deselect-all-cabang" class="text-red-600 hover:underline">Hapus Semua</button>
                        </div>
                    </div>

                    <hr class="border-gray-100">

                    <div class="space-y-3 opacity-50 pointer-events-none transition-all duration-300" id="step-supplier">
                        <label class="block text-sm font-semibold text-gray-700">
                            2. Pilih Supplier
                            <span class="text-red-500">*</span>
                            <span class="text-xs font-normal text-gray-500 ml-2">(Muncul berdasarkan cabang yang dipilih)</span>
                        </label>
                        <div id="loading-supplier" class="hidden text-sm text-blue-500"><i class="fas fa-spinner fa-spin"></i> Memuat supplier...</div>

                        <div class="multi-select-container p-2 bg-gray-50 space-y-1" id="container-supplier">
                            <div class="text-center text-gray-400 py-10 text-sm">Silahkan pilih cabang terlebih dahulu</div>
                        </div>
                        <div class="flex justify-between items-center text-xs text-gray-500">
                            <span id="supplier-counter">0 supplier dipilih</span>
                            <div>
                                <button type="button" id="btn-select-all-supp" class="text-blue-600 hover:underline mr-3">Pilih Semua</button>
                                <button type="button" id="btn-deselect-all-supp" class="text-red-600 hover:underline">Hapus Semua</button>
                            </div>
                        </div>
                    </div>

                    <hr class="border-gray-100">

                    <div class="space-y-3">
                        <label for="tgl_schedule" class="block text-sm font-semibold text-gray-700">
                            3. Tanggal Jadwal SO
                            <span class="text-red-500">*</span>
                        </label>
                        <input type="date" id="tgl_schedule" name="tgl_schedule" 
                            class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                        <p class="text-xs text-gray-500">
                            <i class="fas fa-info-circle mr-1"></i> Minimal 3 hari dari sekarang (Default: <span id="default-date-display"></span>)
                        </p>
                    </div>

                    <div class="pt-4">
                        <button type="submit" id="btn-submit" class="w-full bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-bold py-3 px-4 rounded-xl shadow-lg transform transition hover:-translate-y-0.5 disabled:opacity-50 disabled:cursor-not-allowed">
                            <i class="fa-solid fa-save mr-2"></i> Simpan Jadwal
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </main>

    <script src="../../../js/ui/navbar_toogle.js" type="module"></script>
    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <script src="../../../js/laporan/jadwal_so/create_handler_jadwal_so.js" type="module"></script>

</body>
</html>