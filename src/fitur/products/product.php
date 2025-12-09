<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Master Produk</title>
    <!-- Tailwind via CDN -->
    <link rel="stylesheet" href="../../output2.css">
    <script>

    </script>
    <link rel="stylesheet" href="../../style/header.css">
    <link rel="stylesheet" href="../../style/sidebar.css">
    <link rel="icon" type="image/png" href="../../../public/images/logo1.png">

    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
        integrity="sha512-Avb2QiuDEEvB4bZJYdft2mNjVShBftLdPG8FJ0V7irTLQ8Uo0qcPxh4Plq7G5tGm0rU+1SPhVotteLpBERwTkw=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.js"></script>

    <style>
        /* Enhanced color palette and modern styling */
        :root {
            --primary-50: #f0f9ff;
            --primary-100: #e0f2fe;
            --primary-500: #3b82f6;
            --primary-600: #2563eb;
            --primary-700: #1d4ed8;
            --primary-900: #1e3a8a;
            --accent: #3b82f6;
            --accent-hover: #2563eb;
            --success: #10b981;
            --success-light: #d1fae5;
            --warning: #f59e0b;
            --warning-light: #fef3c7;
            --danger: #ef4444;
            --danger-light: #fee2e2;
            --surface: #ffffff;
            --surface-2: #f8fafc;
            --surface-3: #f1f5f9;
            --border: #e2e8f0;
            --border-light: #f1f5f9;
            --text: #1e293b;
            --text-secondary: #475569;
            --text-light: #64748b;
            --text-lighter: #94a3b8;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            min-height: 100vh;
        }

        /* Enhanced card styling */
        .card-modern {
            background: var(--surface);
            border-radius: 16px;
            border: 1px solid var(--border-light);
            box-shadow: var(--shadow);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .card-modern:hover {
            box-shadow: var(--shadow-lg);
            transform: translateY(-2px);
            border-color: var(--border);
        }

        .card-elevated {
            background: var(--surface);
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.8);
            box-shadow: var(--shadow-lg);
            backdrop-filter: blur(10px);
        }

        /* Modern button styles */
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-500) 0%, var(--primary-600) 100%);
            color: white;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: none;
            position: relative;
            overflow: hidden;
        }

        .btn-primary::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }

        .btn-primary:hover::before {
            left: 100%;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, var(--primary-600) 0%, var(--primary-700) 100%);
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(59, 130, 246, 0.3);
        }

        .btn-accent {
            background: linear-gradient(135deg, var(--accent) 0%, var(--accent-hover) 100%);
            color: white;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .btn-accent::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }

        .btn-accent:hover::before {
            left: 100%;
        }

        .btn-accent:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(59, 130, 246, 0.3);
        }

        .btn-ghost {
            background: var(--surface);
            border: 2px solid var(--border);
            color: var(--text-secondary);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .btn-ghost:hover {
            background: var(--surface-2);
            border-color: var(--primary-500);
            color: var(--primary-600);
            transform: translateY(-1px);
        }

        .btn-danger {
            background: linear-gradient(135deg, var(--danger) 0%, #dc2626 100%);
            color: white;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .btn-danger:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(239, 68, 68, 0.3);
        }

        /* Enhanced input styling */
        .input-modern {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: 2px solid var(--border);
            background: var(--surface);
            border-radius: 12px;
        }

        .input-modern:focus {
            outline: none;
            border-color: var(--primary-500);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            background: var(--surface);
        }

        .input-modern:hover {
            border-color: var(--text-lighter);
        }

        /* Table enhancements */
        .table-modern {
            border-radius: 16px;
            overflow: hidden;
            border: 1px solid var(--border-light);
        }

        .table-header {
            background: linear-gradient(135deg, var(--surface-2) 0%, var(--surface-3) 100%);
            border-bottom: 2px solid var(--border);
        }

        .table-row {
            transition: all 0.2s ease;
            border-bottom: 1px solid var(--border-light);
        }

        .table-row:hover {
            background: linear-gradient(135deg, var(--primary-50) 0%, rgba(59, 130, 246, 0.05) 100%);
            transform: scale(1.005);
        }

        .table-row:last-child {
            border-bottom: none;
        }

        /* Status badges with gradients */
        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            position: relative;
            overflow: hidden;
        }

        .stock-high {
            background: linear-gradient(135deg, var(--success-light) 0%, #a7f3d0 100%);
            color: #065f46;
            border: 1px solid #34d399;
        }

        .stock-medium {
            background: linear-gradient(135deg, var(--warning-light) 0%, #fde68a 100%);
            color: #92400e;
            border: 1px solid #fbbf24;
        }

        .stock-low {
            background: linear-gradient(135deg, var(--danger-light) 0%, #fecaca 100%);
            color: #991b1b;
            border: 1px solid #f87171;
        }

        /* Modal enhancements */
        .modal-backdrop {
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(8px);
        }

        .modal-content {
            border-radius: 24px;
            box-shadow: var(--shadow-xl);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        /* Enhanced animations */
        .fade-in {
            animation: fadeInScale 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        @keyframes fadeInScale {
            from {
                opacity: 0;
                transform: translateY(30px) scale(0.95);
            }

            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .slide-in {
            animation: slideIn 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }

            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        /* Image upload area enhancement */
        .upload-area {
            border: 3px dashed var(--border);
            border-radius: 16px;
            background: linear-gradient(135deg, var(--surface) 0%, var(--surface-2) 100%);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .upload-area::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(59, 130, 246, 0.1), transparent);
            transition: left 0.5s;
        }

        .upload-area:hover {
            border-color: var(--primary-500);
            background: linear-gradient(135deg, var(--primary-50) 0%, rgba(59, 130, 246, 0.05) 100%);
            transform: translateY(-2px);
        }

        .upload-area:hover::before {
            left: 100%;
        }

        /* Cropper specific styles */
        .cropper-container {
            font-size: 0;
            line-height: 0;
            position: relative;
            user-select: none;
            direction: ltr;
            touch-action: none;
            -webkit-tap-highlight-color: transparent;
            -webkit-touch-callout: none;
            border-radius: 12px;
            overflow: hidden;
        }

        .cropper-container img {
            display: block;
            min-width: 0 !important;
            max-width: none !important;
            min-height: 0 !important;
            max-height: none !important;
            width: 100%;
            height: 100%;
            image-orientation: 0deg;
        }

        .cropper-canvas,
        .cropper-crop-box,
        .cropper-drag-box,
        .cropper-modal,
        .cropper-wrap-box {
            position: absolute;
            top: 0;
            right: 0;
            bottom: 0;
            left: 0;
        }

        .cropper-wrap-box {
            overflow: hidden;
        }

        .cropper-drag-box {
            opacity: 0;
            background-color: #fff;
        }

        .cropper-modal {
            opacity: 0.5;
            background-color: #000;
        }

        .cropper-view-box {
            display: block;
            overflow: hidden;
            width: 100%;
            height: 100%;
            outline: 2px solid var(--primary-500);
            outline-color: var(--primary-500);
            border-radius: 8px;
        }

        .cropper-dashed {
            position: absolute;
            display: block;
            opacity: 0.5;
            border: 0 dashed #eee;
        }

        .cropper-dashed.dashed-h {
            top: 33.33333%;
            left: 0;
            width: 100%;
            height: 33.33333%;
            border-top-width: 1px;
            border-bottom-width: 1px;
        }

        .cropper-dashed.dashed-v {
            top: 0;
            left: 33.33333%;
            width: 33.33333%;
            height: 100%;
            border-right-width: 1px;
            border-left-width: 1px;
        }

        .cropper-center {
            position: absolute;
            top: 50%;
            left: 50%;
            display: block;
            width: 0;
            height: 0;
            opacity: 0.75;
        }

        .cropper-center:before,
        .cropper-center:after {
            position: absolute;
            display: block;
            content: ' ';
            background-color: var(--primary-500);
        }

        .cropper-center:before {
            top: 0;
            left: -3px;
            width: 7px;
            height: 1px;
        }

        .cropper-center:after {
            top: -3px;
            left: 0;
            width: 1px;
            height: 7px;
        }

        .cropper-face,
        .cropper-line,
        .cropper-point {
            position: absolute;
            display: block;
            width: 100%;
            height: 100%;
            opacity: 0.1;
        }

        .cropper-face {
            top: 0;
            left: 0;
            background-color: #fff;
        }

        .cropper-line {
            background-color: var(--primary-500);
        }

        .cropper-line.line-e {
            top: 0;
            right: -3px;
            width: 5px;
            cursor: e-resize;
        }

        .cropper-line.line-n {
            top: -3px;
            left: 0;
            height: 5px;
            cursor: n-resize;
        }

        .cropper-line.line-w {
            top: 0;
            left: -3px;
            width: 5px;
            cursor: w-resize;
        }

        .cropper-line.line-s {
            bottom: -3px;
            left: 0;
            height: 5px;
            cursor: s-resize;
        }

        .cropper-point {
            width: 5px;
            height: 5px;
            opacity: 0.75;
            background-color: var(--primary-500);
            border-radius: 50%;
        }

        .cropper-point.point-e {
            top: 50%;
            right: -3px;
            margin-top: -3px;
            cursor: e-resize;
        }

        .cropper-point.point-n {
            top: -3px;
            left: 50%;
            margin-left: -3px;
            cursor: n-resize;
        }

        .cropper-point.point-w {
            top: 50%;
            left: -3px;
            margin-top: -3px;
            cursor: w-resize;
        }

        .cropper-point.point-s {
            bottom: -3px;
            left: 50%;
            margin-left: -3px;
            cursor: s-resize;
        }

        .cropper-point.point-ne {
            top: -3px;
            right: -3px;
            cursor: ne-resize;
        }

        .cropper-point.point-nw {
            top: -3px;
            left: -3px;
            cursor: nw-resize;
        }

        .cropper-point.point-sw {
            bottom: -3px;
            left: -3px;
            cursor: sw-resize;
        }

        .cropper-point.point-se {
            right: -3px;
            bottom: -3px;
            cursor: se-resize;
            width: 8px;
            height: 8px;
            opacity: 1;
            background: linear-gradient(135deg, var(--primary-500), var(--primary-600));
        }

        .cropper-point.point-se:before {
            position: absolute;
            right: -50%;
            bottom: -50%;
            display: block;
            width: 200%;
            height: 200%;
            content: ' ';
            opacity: 0;
            background-color: var(--primary-500);
        }

        /* Glassmorphism effect */
        .glass {
            background: rgba(255, 255, 255, 0.25);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: var(--surface-2);
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, var(--primary-500), var(--primary-600));
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(135deg, var(--primary-600), var(--primary-700));
        }

        /* Responsive enhancements */
        @media (max-width: 768px) {
            .card-modern {
                border-radius: 12px;
            }

            .modal-content {
                border-radius: 16px;
                margin: 1rem;
            }
        }
    </style>
</head>

<body class="bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50 font-sans min-h-screen">
    <?php include '../../component/navigation_report.php'; ?>
    <?php include '../../component/sidebar_report.php'; ?>

    <main id="main-content" class="flex-1 p-4 lg:p-8 transition-all duration-300 ml-64">
        <div class="max-w-7xl mx-auto">
            <!-- Enhanced Header -->
            <header class="mb-8">
                <div class="card-elevated p-8 mb-6">
                    <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-6">
                        <div class="flex items-center gap-4">
                            <div class="p-4 bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl shadow-lg">
                                <i class="fa-solid fa-box-open text-2xl text-white"></i>
                            </div>
                            <div>
                                <h1
                                    class="text-4xl font-bold bg-gradient-to-r from-slate-800 to-slate-600 bg-clip-text text-transparent">
                                    Produk Online
                                </h1>
                                <p class="text-slate-600 mt-2 text-lg">Kelola katalog produk Anda dengan mudah dan
                                    efisien</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-4 w-full lg:w-auto">
                            <button id="btnAdd"
                                class="px-8 py-4 rounded-xl btn-primary shadow-lg flex items-center gap-3 text-sm font-semibold whitespace-nowrap">
                                <i class="fa-solid fa-plus text-lg"></i>
                                Tambah Produk
                            </button>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Enhanced Filters Section -->
            <section class="mb-8">
                <div class="card-modern p-6">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="p-2 bg-gradient-to-br from-indigo-100 to-blue-100 rounded-lg">
                            <i class="fa-solid fa-filter text-indigo-600"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-slate-800">Filter Produk</h3>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <!-- Filter Cabang -->
                        <div class="space-y-3">
                            <label for="filterCabang" class="block text-sm font-semibold text-slate-700">
                                <i class="fa-solid fa-store-alt mr-2 text-blue-500"></i>
                                Cabang
                            </label>
                            <select id="filterCabang"
                                class="w-full px-4 py-4 border-2 input-modern text-slate-700 font-medium">
                                <option value="">Semua Cabang</option>
                                <!-- opsi cabang bisa diisi via JS dari API kode_store -->
                            </select>
                        </div>

                        <!-- Filter Search -->
                        <div class="space-y-3 lg:col-span-2">
                            <label for="filterSearch" class="block text-sm font-semibold text-slate-700">
                                <i class="fa-solid fa-search mr-2 text-blue-500"></i>
                                Pencarian Lanjutan
                            </label>
                            <div class="relative">
                                <input type="text" id="filterSearch"
                                    placeholder="Cari berdasarkan nama produk, atau barcode..."
                                    class="w-full px-4 py-4 pr-12 border-2 input-modern text-slate-700 placeholder-slate-400 font-medium">
                                <div class="absolute right-4 top-1/2 transform -translate-y-1/2">
                                    <i class="fas fa-search text-slate-400 text-lg"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Enhanced Product Table -->
            <section class="card-modern table-modern">
                <div class="p-6 border-b border-slate-100">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div class="p-3 bg-gradient-to-br from-emerald-100 to-green-100 rounded-xl">
                                <i class="fa-solid fa-list text-emerald-600 text-xl"></i>
                            </div>
                            <div>
                                <h2 class="text-2xl font-bold text-slate-800">Daftar Produk</h2>
                                <p class="text-slate-600 text-sm mt-1">Kelola semua produk dalam satu tempat</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-3">
                            <div
                                class="px-4 py-2 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg border border-blue-200">
                                <span class="text-sm font-semibold text-blue-700" id="countText">0 produk</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full table-auto" id="productTable">
                        <thead class="table-header">
                            <tr>
                                <th
                                    class="px-6 py-5 text-left text-xs font-bold text-slate-600 uppercase tracking-wider w-16">
                                    <div class="flex items-center gap-2">
                                        <i class="fa-solid fa-hashtag"></i>
                                        No
                                    </div>
                                </th>
                                <th
                                    class="px-6 py-5 text-left text-xs font-bold text-slate-600 uppercase tracking-wider">
                                    <div class="flex items-center gap-2">
                                        <i class="fa-solid fa-box"></i>
                                        Produk
                                    </div>
                                </th>
                                <th
                                    class="px-6 py-5 text-left text-xs font-bold text-slate-600 uppercase tracking-wider">
                                    <div class="flex items-center gap-2">
                                        <i class="fa-solid fa-tag"></i>
                                        Harga
                                    </div>
                                </th>
                                <th
                                    class="px-6 py-5 text-left text-xs font-bold text-slate-600 uppercase tracking-wider">
                                    <div class="flex items-center gap-2">
                                        <i class="fa-solid fa-warehouse"></i>
                                        Stok
                                    </div>
                                </th>
                                <th
                                    class="px-6 py-5 text-left text-xs font-bold text-slate-600 uppercase tracking-wider">
                                    <div class="flex items-center gap-2">
                                        <i class="fa-solid fa-layer-group"></i>
                                        Kategori
                                    </div>
                                </th>
                                <th
                                    class="px-6 py-5 text-left text-xs font-bold text-slate-600 uppercase tracking-wider">
                                    <div class="flex items-center gap-2">
                                        <i class="fa-solid fa-layer-group"></i>
                                        Tanggal Upload
                                    </div>
                                </th>
                                <th
                                    class="px-6 py-5 text-left text-xs font-bold text-slate-600 uppercase tracking-wider">
                                    <div class="flex items-center gap-2">
                                        <i class="fa-solid fa-store"></i>
                                        Cabang
                                    </div>
                                </th>
                                <th
                                    class="px-6 py-5 text-center text-xs font-bold text-slate-600 uppercase tracking-wider w-40">
                                    <div class="flex items-center justify-center gap-2">
                                        <i class="fa-solid fa-cog"></i>
                                        Aksi
                                    </div>
                                </th>
                            </tr>
                        </thead>
                        <tbody id="tbody" class="bg-white divide-y divide-slate-100">
                        </tbody>
                    </table>
                </div>

                <!-- Enhanced Pagination -->
                <div
                    class="px-6 py-5 bg-gradient-to-r from-slate-50 to-blue-50 border-t border-slate-100 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="p-2 bg-white rounded-lg border border-slate-200 shadow-sm">
                            <i class="fa-solid fa-info-circle text-blue-500"></i>
                        </div>
                        <span class="text-sm font-medium text-slate-600" id="countText2">Menampilkan 1-10 dari 100
                            produk</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <button id="prevBtn"
                            class="px-4 py-2 bg-white border-2 border-slate-200 rounded-lg text-sm font-medium hover:border-blue-300 hover:bg-blue-50 transition-all flex items-center gap-2">
                            <i class="fa-solid fa-chevron-left"></i>
                            Sebelumnya
                        </button>
                        <div
                            class="px-4 py-2 bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-lg text-sm font-semibold shadow-sm">
                            <span id="pageText">Hal 1 dari 10</span>
                        </div>
                        <button id="nextBtn"
                            class="px-4 py-2 bg-white border-2 border-slate-200 rounded-lg text-sm font-medium hover:border-blue-300 hover:bg-blue-50 transition-all flex items-center gap-2">
                            Selanjutnya
                            <i class="fa-solid fa-chevron-right"></i>
                        </button>
                    </div>
                </div>
            </section>
        </div>

        <!-- Enhanced Modal Form -->
        <div id="modal" class="fixed inset-0 modal-backdrop hidden flex items-center justify-center z-50 p-4">
            <div class="modal-content bg-white w-full max-w-5xl shadow-2xl fade-in max-h-[95vh] overflow-y-auto">
                <!-- Modal Header -->
                <div class="p-8 border-b border-slate-100 sticky top-0 bg-white z-10">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div class="p-3 bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl shadow-lg">
                                <i class="fa-solid fa-box text-white text-xl"></i>
                            </div>
                            <div>
                                <h2 id="modalTitle" class="text-2xl font-bold text-slate-800">Tambah Produk Baru</h2>
                                <p class="text-slate-600 mt-1">Lengkapi informasi produk dengan detail</p>
                            </div>
                        </div>
                        <button id="closeModal"
                            class="text-slate-400 hover:text-slate-600 transition-colors p-2 hover:bg-slate-100 rounded-lg">
                            <i class="fa-solid fa-xmark text-2xl"></i>
                        </button>
                    </div>
                </div>

                <form id="productForm" class="p-8">
                    <input type="hidden" id="productId" />

                    <!-- Basic Information Section -->
                    <div class="mb-8">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="p-2 bg-gradient-to-br from-emerald-100 to-green-100 rounded-lg">
                                <i class="fa-solid fa-info-circle text-emerald-600"></i>
                            </div>
                            <h3 class="text-lg font-semibold text-slate-800">Informasi Dasar</h3>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label class="block text-sm font-semibold text-slate-700">
                                    <i class="fa-solid fa-barcode mr-2 text-blue-500"></i>
                                    Barcode
                                </label>
                                <input id="barcode" name="barcode"
                                    class="w-full px-4 py-4 border-2 rounded-xl input-modern bg-white font-medium"
                                    placeholder="Masukkan barcode produk" required type="number" />
                            </div>

                            <div class="space-y-2">
                                <label class="block text-sm font-semibold text-slate-700">
                                    <i class="fa-solid fa-hashtag mr-2 text-blue-500"></i>
                                    PLU
                                </label>
                                <input id="plu" name="plu"
                                    class="w-full px-4 py-4 border-2 rounded-xl input-modern bg-white font-medium"
                                    placeholder="Masukkan kode PLU" required type="number" />
                            </div>

                            <div class="space-y-2">
                                <label class="block text-sm font-semibold text-slate-700">
                                    <i class="fa-solid fa-store mr-2 text-blue-500"></i>
                                    Cabang
                                </label>
                                <select name="branch" id="cabang"
                                    class="w-full px-4 py-4 border-2 rounded-xl input-modern bg-white font-medium">
                                    <option value="">Pilih Cabang</option>
                                </select>
                            </div>

                            <div class="space-y-2">
                                <label class="block text-sm font-semibold text-slate-700">
                                    <i class="fa-solid fa-box mr-2 text-blue-500"></i>
                                    Nama Produk
                                </label>
                                <input id="name" name="nama_produk"
                                    class="w-full px-4 py-4 border-2 rounded-xl input-modern bg-white font-medium"
                                    placeholder="Masukkan nama produk" required />
                            </div>

                            <div class="space-y-2 md:col-span-2">
                                <label class="block text-sm font-semibold text-slate-700">
                                    <i class="fa-solid fa-layer-group mr-2 text-blue-500"></i>
                                    Kategori
                                </label>
                                <input id="category" name="kategori"
                                    class="w-full px-4 py-4 border-2 rounded-xl input-modern bg-white font-medium"
                                    placeholder="Masukkan kategori produk" />
                            </div>
                        </div>
                    </div>

                    <!-- Description Section -->
                    <div class="mb-8">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="p-2 bg-gradient-to-br from-purple-100 to-indigo-100 rounded-lg">
                                <i class="fa-solid fa-align-left text-purple-600"></i>
                            </div>
                            <h3 class="text-lg font-semibold text-slate-800">Deskripsi Produk</h3>
                        </div>

                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-slate-700">
                                <i class="fa-solid fa-file-text mr-2 text-blue-500"></i>
                                Detail Deskripsi
                            </label>
                            <textarea id="description" name="deskripsi" rows="5"
                                class="w-full px-4 py-4 border-2 rounded-xl input-modern bg-white resize-none font-medium"
                                placeholder="Tuliskan deskripsi produk secara detail..."></textarea>
                        </div>
                    </div>

                    <!-- Image Upload Section -->
                    <div class="mb-8">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="p-2 bg-gradient-to-br from-pink-100 to-rose-100 rounded-lg">
                                <i class="fa-solid fa-image text-pink-600"></i>
                            </div>
                            <h3 class="text-lg font-semibold text-slate-800">Gambar Produk</h3>
                        </div>

                        <div class="upload-area p-8 text-center">
                            <input id="imageInput" name="gambar-produk" type="file" accept="image/*" class="hidden" />
                            <button type="button" id="uploadBtn"
                                class="text-slate-500 hover:text-slate-700 transition-colors flex flex-col items-center justify-center w-full">
                                <div class="p-4 bg-gradient-to-br from-blue-100 to-indigo-100 rounded-2xl mb-4">
                                    <i class="fa-solid fa-cloud-upload-alt text-4xl text-blue-600"></i>
                                </div>
                                <div class="text-lg font-semibold text-slate-700 mb-2">Upload Gambar Produk</div>
                                <div class="text-sm text-slate-500">Klik untuk memilih gambar atau drag & drop di sini
                                </div>
                                <div class="text-xs text-slate-400 mt-2">Format: JPG, PNG, WEBP (Max: 5MB)</div>
                            </button>

                            <!-- Preview dan Cropper Container -->
                            <div id="cropperContainer" class="mt-6 hidden">
                                <div class="relative bg-gradient-to-br from-slate-100 to-slate-200 rounded-2xl overflow-hidden shadow-inner"
                                    style="max-height: 400px;">
                                    <img id="preview" src="" alt="" class="max-w-full max-h-96 mx-auto block" />
                                </div>

                                <!-- Crop Controls -->
                                <div id="cropControls" class="mt-6 flex items-center justify-center gap-3 flex-wrap">
                                    <button type="button" id="cropBtn"
                                        class="px-4 py-3 bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-xl hover:from-blue-600 hover:to-blue-700 transition-all flex items-center gap-2 font-semibold shadow-md">
                                        <i class="fa-solid fa-crop"></i>
                                        Crop Gambar
                                    </button>
                                    <button type="button" id="resetCropBtn"
                                        class="px-4 py-3 bg-gradient-to-r from-slate-500 to-slate-600 text-white rounded-xl hover:from-slate-600 hover:to-slate-700 transition-all flex items-center gap-2 font-semibold shadow-md">
                                        <i class="fa-solid fa-undo"></i>
                                        Reset
                                    </button>
                                    <button type="button" id="cancelCropBtn"
                                        class="px-4 py-3 bg-gradient-to-r from-red-500 to-red-600 text-white rounded-xl hover:from-red-600 hover:to-red-700 transition-all flex items-center gap-2 font-semibold shadow-md">
                                        <i class="fa-solid fa-times"></i>
                                        Batal
                                    </button>
                                </div>
                            </div>

                            <!-- Final Cropped Result -->
                            <div id="croppedResult" class="mt-6 hidden">
                                <div
                                    class="p-6 bg-gradient-to-br from-green-50 to-emerald-50 rounded-2xl border-2 border-green-200">
                                    <div class="text-sm font-semibold text-emerald-700 mb-4 flex items-center gap-2">
                                        <i class="fa-solid fa-check-circle"></i>
                                        Gambar Siap Digunakan
                                    </div>
                                    <img id="croppedPreview" src="" alt=""
                                        class="mx-auto rounded-xl border-2 border-emerald-200 shadow-md"
                                        style="max-width: 200px; max-height: 200px;" />
                                    <div class="mt-4 flex justify-center gap-3">
                                        <button type="button" id="editCropBtn"
                                            class="px-4 py-2 text-sm bg-blue-100 text-blue-600 rounded-xl hover:bg-blue-200 transition-colors font-medium border border-blue-200">
                                            <i class="fa-solid fa-edit mr-1"></i>
                                            Edit Ulang
                                        </button>
                                        <button type="button" id="removeCropBtn"
                                            class="px-4 py-2 text-sm bg-red-100 text-red-600 rounded-xl hover:bg-red-200 transition-colors font-medium border border-red-200">
                                            <i class="fa-solid fa-trash mr-1"></i>
                                            Hapus
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="flex justify-end gap-4 pt-6 border-t border-slate-200">
                        <button type="button" id="cancelBtn"
                            class="px-8 py-4 btn-ghost rounded-xl text-sm font-semibold flex items-center gap-2">
                            <i class="fa-solid fa-times"></i>
                            Batal
                        </button>
                        <button type="submit"
                            class="px-8 py-4 btn-accent rounded-xl text-sm font-semibold flex items-center gap-2 shadow-lg">
                            <i class="fa-solid fa-save"></i>
                            Simpan Produk
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <script type="module" src="/src/js/products/main.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

</body>

</html>