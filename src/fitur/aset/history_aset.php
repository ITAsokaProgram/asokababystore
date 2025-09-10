<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Master Produk</title>
    <!-- Tailwind via CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#f0f9ff',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                            900: '#1e3a8a'
                        }
                    }
                }
            }
        }
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

        .filter-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
        }

        .date-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
        }

        @media (max-width: 768px) {
            .filter-grid {
                grid-template-columns: 1fr;
            }

            .date-grid {
                grid-template-columns: 1fr;
            }
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
                                <i class="fa-solid fa-boxes-stacked text-2xl text-white"></i>
                            </div>
                            <div>
                                <h1
                                    class="text-4xl font-bold bg-gradient-to-r from-slate-800 to-slate-600 bg-clip-text text-transparent">
                                    History Aset
                                </h1>
                                <p class="text-slate-600 mt-2 text-lg">Management Inventory Asset</p>
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
                <div class="card-modern p-6 rounded-2xl">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="p-3 bg-gradient-to-br from-indigo-100 to-blue-100 rounded-xl">
                            <i class="fa-solid fa-filter text-indigo-600 text-lg"></i>
                        </div>
                        <h3 class="text-xl font-bold text-slate-800">Filter Aset</h3>
                    </div>

                    <!-- Main Filters -->
                    <div class="filter-grid mb-6">
                        <!-- Filter Cabang -->
                        <div class="space-y-3">
                            <label for="filterCabang" class="block text-sm font-semibold text-slate-700">
                                <i class="fa-solid fa-store-alt mr-2 text-blue-500"></i>
                                Cabang
                            </label>
                            <select id="filterCabang"
                                class="w-full px-4 py-4 border-2 input-modern text-slate-700 font-medium focus:ring-2 focus:ring-blue-200">
                                <option value="" disabled selected>Memuat cabang...</option>
                                <!-- opsi cabang akan diisi via JS dari API kode_store; default ke alias pertama -->
                            </select>
                        </div>

                        <!-- Filter Search -->
                        <div class="space-y-3">
                            <label for="filterSearch" class="block text-sm font-semibold text-slate-700">
                                <i class="fa-solid fa-search mr-2 text-blue-500"></i>
                                Pencarian Lanjutan
                            </label>
                            <div class="relative">
                                <input type="text" id="filterSearch" placeholder="Cari berdasarkan nama aset"
                                    class="w-full px-4 py-4 pr-12 border-2 input-modern text-slate-700 placeholder-slate-400 font-medium">
                                <div class="absolute right-4 top-1/2 transform -translate-y-1/2">
                                    <i class="fas fa-search text-slate-400 text-lg"></i>
                                </div>
                            </div>
                        </div>

                        <!-- Filter Group Aset -->
                        <div class="space-y-3">
                            <label for="filter_group_aset" class="block text-sm font-semibold text-slate-700">
                                <i class="fa-solid fa-layer-group mr-2 text-blue-500"></i>
                                Group Aset
                            </label>
                            <div class="relative">
                                <input type="text" id="filter_group_aset" placeholder="Ketik group, suggestion muncul"
                                    class="w-full px-4 py-4 border-2 input-modern text-slate-700 placeholder-slate-400 font-medium"
                                    autocomplete="off">
                                <div id="group_suggestions"
                                    class="absolute top-full left-0 right-0 z-10 bg-white border border-gray-200 mt-1 rounded-lg shadow-lg hidden max-h-40 overflow-auto">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Clear Filters Button -->
                    <div class="mb-6">
                        <button id="clearFilters" type="button"
                            class="px-6 py-3 bg-white border-2 border-gray-200 hover:border-red-300 hover:bg-red-50 rounded-xl text-sm font-medium text-gray-700 hover:text-red-600 transition-all duration-300 shadow-sm hover:shadow-md">
                            <i class="fa-solid fa-eraser mr-2"></i>
                            Clear Filters
                        </button>
                    </div>

                    <!-- Date Filters Section -->
                    <div class="space-y-4">
                        <!-- Tanggal Beli & Perbaikan -->
                        <div class="date-grid">
                            <div class="space-y-2">
                                <label for="filter_tanggal_beli_from"
                                    class="block text-sm font-semibold text-slate-700">
                                    <i class="fa-solid fa-calendar-plus mr-2 text-green-500"></i>
                                    Tanggal Beli (Dari)
                                </label>
                                <input type="date" id="filter_tanggal_beli_from"
                                    class="w-full px-4 py-3 border-2 input-modern text-slate-700 font-medium">
                            </div>
                            <div class="space-y-2">
                                <label for="filter_tanggal_beli_to" class="block text-sm font-semibold text-slate-700">
                                    <i class="fa-solid fa-calendar-check mr-2 text-green-500"></i>
                                    Tanggal Beli (Sampai)
                                </label>
                                <input type="date" id="filter_tanggal_beli_to"
                                    class="w-full px-4 py-3 border-2 input-modern text-slate-700 font-medium">
                            </div>
                            <div class="space-y-2">
                                <label for="filter_tanggal_perbaikan_from"
                                    class="block text-sm font-semibold text-slate-700">
                                    <i class="fa-solid fa-wrench mr-2 text-blue-500"></i>
                                    Tanggal Perbaikan (Dari)
                                </label>
                                <input type="date" id="filter_tanggal_perbaikan_from"
                                    class="w-full px-4 py-3 border-2 input-modern text-slate-700 font-medium">
                            </div>
                            <div class="space-y-2">
                                <label for="filter_tanggal_perbaikan_to"
                                    class="block text-sm font-semibold text-slate-700">
                                    <i class="fa-solid fa-tools mr-2 text-blue-500"></i>
                                    Tanggal Perbaikan (Sampai)
                                </label>
                                <input type="date" id="filter_tanggal_perbaikan_to"
                                    class="w-full px-4 py-3 border-2 input-modern text-slate-700 font-medium">
                            </div>
                        </div>

                        <!-- Tanggal Rusak & Mutasi -->
                        <div class="date-grid">
                            <div class="space-y-2">
                                <label for="filter_tanggal_rusak_from"
                                    class="block text-sm font-semibold text-slate-700">
                                    <i class="fa-solid fa-exclamation-triangle mr-2 text-red-500"></i>
                                    Tanggal Rusak (Dari)
                                </label>
                                <input type="date" id="filter_tanggal_rusak_from"
                                    class="w-full px-4 py-3 border-2 input-modern text-slate-700 font-medium">
                            </div>
                            <div class="space-y-2">
                                <label for="filter_tanggal_rusak_to" class="block text-sm font-semibold text-slate-700">
                                    <i class="fa-solid fa-times-circle mr-2 text-red-500"></i>
                                    Tanggal Rusak (Sampai)
                                </label>
                                <input type="date" id="filter_tanggal_rusak_to"
                                    class="w-full px-4 py-3 border-2 input-modern text-slate-700 font-medium">
                            </div>
                            <div class="space-y-2">
                                <label for="filter_tanggal_mutasi_from"
                                    class="block text-sm font-semibold text-slate-700">
                                    <i class="fa-solid fa-exchange-alt mr-2 text-purple-500"></i>
                                    Tanggal Mutasi (Dari)
                                </label>
                                <input type="date" id="filter_tanggal_mutasi_from"
                                    class="w-full px-4 py-3 border-2 input-modern text-slate-700 font-medium">
                            </div>
                            <div class="space-y-2">
                                <label for="filter_tanggal_mutasi_to"
                                    class="block text-sm font-semibold text-slate-700">
                                    <i class="fa-solid fa-arrow-right mr-2 text-purple-500"></i>
                                    Tanggal Mutasi (Sampai)
                                </label>
                                <input type="date" id="filter_tanggal_mutasi_to"
                                    class="w-full px-4 py-3 border-2 input-modern text-slate-700 font-medium">
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!--  Table -->
            <section class="card-modern table-modern">
                <div class="p-6 border-b border-slate-100">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div class="p-3 bg-gradient-to-br from-emerald-100 to-green-100 rounded-xl">
                                <i class="fa-solid fa-list text-emerald-600 text-xl"></i>
                            </div>
                            <div>
                                <h2 class="text-2xl font-bold text-slate-800">Daftar Barang</h2>
                                <p class="text-slate-600 text-sm mt-1">Kelola semua aset dalam satu tempat</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-3">
                            <div
                                class="px-4 py-2 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg border border-blue-200">
                                <span class="text-sm font-semibold text-blue-700" id="countText">0 Barang</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full table-auto" id="productTable">
                        <thead class="table-header">
                            <tr>
                                <th
                                    class="truncate px-6 py-5 text-left text-xs font-bold text-slate-600 uppercase tracking-wider">
                                    <div class="flex items-center gap-2">
                                        <i class="fa-solid fa-hashtag"></i>
                                        No
                                    </div>
                                </th>
                                <th
                                    class="truncate px-6 py-5 text-left text-xs font-bold text-slate-600 uppercase tracking-wider">
                                    <div class="flex items-center gap-2">
                                        <i class="fa-solid fa-box-open"></i>
                                        Nama Barang
                                    </div>
                                </th>

                                <th
                                    class="truncate px-6 py-5 text-left text-xs font-bold text-slate-600 uppercase tracking-wider">
                                    <div class="flex items-center gap-2">
                                        <i class="fa-solid fa-industry"></i>
                                        Merk
                                    </div>
                                </th>

                                <th
                                    class="truncate px-6 py-5 text-left text-xs font-bold text-slate-600 uppercase tracking-wider">
                                    <div class="flex items-center gap-2">
                                        <i class="fa-solid fa-calendar-xmark"></i>
                                        Tanggal Rusak
                                    </div>
                                </th>

                                <th
                                    class="truncate px-6 py-5 text-left text-xs font-bold text-slate-600 uppercase tracking-wider">
                                    <div class="flex items-center gap-2">
                                        <i class="fa-solid fa-calendar-check"></i>
                                        Tanggal Perbaikan
                                    </div>
                                </th>
                                <th
                                    class="truncate px-6 py-5 text-left text-xs font-bold text-slate-600 uppercase tracking-wider">
                                    <div class="flex items-center gap-2">
                                        <i class="fa-solid fa-calendar-check"></i>
                                        Tanggal Ganti
                                    </div>
                                </th>

                                <th
                                    class="truncate px-6 py-5 text-left text-xs font-bold text-slate-600 uppercase tracking-wider">
                                    <div class="flex items-center gap-2">
                                        <i class="fa-solid fa-money-bill-wave"></i>
                                        Harga Beli
                                    </div>
                                </th>

                                <th
                                    class="truncate px-6 py-5 text-left text-xs font-bold text-slate-600 uppercase tracking-wider">
                                    <div class="flex items-center gap-2">
                                        <i class="fa-solid fa-store"></i>
                                        Toko
                                    </div>
                                </th>

                                <th
                                    class="truncate px-6 py-5 text-left text-xs font-bold text-slate-600 uppercase tracking-wider">
                                    <div class="flex items-center gap-2">
                                        <i class="fa-solid fa-calendar-alt"></i>
                                        Tanggal Beli
                                    </div>
                                </th>

                                <th
                                    class="truncate px-6 py-5 text-left text-xs font-bold text-slate-600 uppercase tracking-wider">
                                    <div class="flex items-center gap-2">
                                        <i class="fa-solid fa-arrow-right-arrow-left"></i>
                                        Mutasi Dari
                                    </div>
                                </th>

                                <th
                                    class="truncate px-6 py-5 text-left text-xs font-bold text-slate-600 uppercase tracking-wider">
                                    <div class="flex items-center gap-2">
                                        <i class="fa-solid fa-arrow-right"></i>
                                        Mutasi Untuk
                                    </div>
                                </th>
                                <th
                                    class="truncate px-6 py-5 text-left text-xs font-bold text-slate-600 uppercase tracking-wider">
                                    <div class="flex items-center gap-2">
                                        <i class="fa-solid fa-calendar-check"></i>
                                        Tanggal Mutasi
                                    </div>
                                </th>
                                <th
                                    class="truncate px-6 py-5 text-left text-xs font-bold text-slate-600 uppercase tracking-wider">
                                    <div class="flex items-center gap-2">
                                        <i class="fa-solid fa-info-circle"></i>
                                        Status
                                    </div>
                                </th>

                                <th
                                    class="truncate px-6 py-5 text-left text-xs font-bold text-slate-600 uppercase tracking-wider w-24">
                                    <div class="flex items-center gap-2">
                                        <i class="fa-solid fa-image"></i>
                                        Foto
                                    </div>
                                </th>

                                <th
                                    class="truncate px-6 py-5 text-left text-xs font-bold text-slate-600 uppercase tracking-wider w-40">
                                    <div class="flex items-center gap-2">
                                        <i class="fa-solid fa-building"></i>
                                        Nama Cabang
                                    </div>
                                </th>
                                <th
                                    class="truncate px-6 py-5 text-left text-xs font-bold text-slate-600 uppercase tracking-wider w-40">
                                    <div class="flex items-center gap-2">
                                        <i class="fa-solid fa-building"></i>
                                        Group Aset
                                    </div>
                                </th>
                                <th
                                    class="truncate px-6 py-5 text-left text-xs font-bold text-slate-600 uppercase tracking-wider w-40">
                                    <div class="flex items-center gap-2">
                                        <i class="fa-solid fa-bolt"></i>
                                        Action
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
                            class="truncate px-4 py-2 bg-white border-2 border-slate-200 rounded-lg text-sm font-medium hover:border-blue-300 hover:bg-blue-50 transition-all flex items-center gap-2">
                            <i class="fa-solid fa-chevron-left"></i>
                            Sebelumnya
                        </button>
                        <div
                            class="truncate px-4 py-2 bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-lg text-sm font-semibold shadow-sm">
                            <span id="pageText">Hal 1 dari 10</span>
                        </div>
                        <button id="nextBtn"
                            class="truncate px-4 py-2 bg-white border-2 border-slate-200 rounded-lg text-sm font-medium hover:border-blue-300 hover:bg-blue-50 transition-all flex items-center gap-2">
                            Selanjutnya
                            <i class="fa-solid fa-chevron-right"></i>
                        </button>
                    </div>
                </div>
            </section>
        </div>
    </main>

    <!-- Scripts -->
    <script>
        document.getElementById("toggle-sidebar").addEventListener("click", function () {
            document.getElementById("sidebar").classList.toggle("open");
        });
        document.addEventListener("DOMContentLoaded", function () {
            const sidebar = document.getElementById("sidebar");
            const closeBtn = document.getElementById("closeSidebar");

            closeBtn.addEventListener("click", function () {
                sidebar.classList.remove("open");
            });
        });
        document.getElementById("toggle-hide").addEventListener("click", function () {
            var sidebarTexts = document.querySelectorAll(".sidebar-text");
            let mainContent = document.getElementById("main-content");
            let sidebar = document.getElementById("sidebar");
            var toggleButton = document.getElementById("toggle-hide");
            var icon = toggleButton.querySelector("i");

            if (sidebar.classList.contains("w-64")) {
                sidebar.classList.remove("w-64", "px-5");
                sidebar.classList.add("w-16", "px-2");
                sidebarTexts.forEach((text) => text.classList.add("hidden"));
                mainContent.classList.remove("ml-64");
                mainContent.classList.add("ml-16");
                toggleButton.classList.add("left-20");
                toggleButton.classList.remove("left-64");
                icon.classList.remove("fa-angle-left");
                icon.classList.add("fa-angle-right");
            } else {
                sidebar.classList.remove("w-16", "px-2");
                sidebar.classList.add("w-64", "px-5");
                sidebarTexts.forEach((text) => text.classList.remove("hidden"));
                mainContent.classList.remove("ml-16");
                mainContent.classList.add("ml-64");
                toggleButton.classList.add("left-64");
                toggleButton.classList.remove("left-20");
                icon.classList.remove("fa-angle-right");
                icon.classList.add("fa-angle-left");
            }
        });
        document.addEventListener("DOMContentLoaded", function () {
            const profileImg = document.getElementById("profile-img");
            const profileCard = document.getElementById("profile-card");

            profileImg.addEventListener("click", function (event) {
                event.preventDefault();
                profileCard.classList.toggle("show");
            });

            document.addEventListener("click", function (event) {
                if (!profileCard.contains(event.target) && !profileImg.contains(event.target)) {
                    profileCard.classList.remove("show");
                }
            });
        });
    </script>
    <!-- Add Asset Modal -->
    <div id="addAssetModal" class="fixed inset-0 z-50 overflow-y-auto hidden">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="fixed inset-0 bg-black opacity-50"></div>

            <div class="relative bg-white rounded-xl shadow-2xl max-w-4xl w-full p-6 overflow-hidden">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-2xl font-semibold text-gray-800">Edit Asset</h3>
                    <button type="button" class="close-modal text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-2xl"></i>
                    </button>
                </div>

                <form id="assetForm" class="space-y-6">
                    <input type="hidden" name="idhistory_aset" id="idhistory_aset" value="">
                    <!-- Basic Info Section -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Nama Barang <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="nama_barang" required
                                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Merk <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="merk" required
                                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Harga Beli <span class="text-red-500">*</span>
                            </label>
                            <input type="number" name="harga_beli" required
                                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Nama Toko <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="nama_toko" required
                                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Tanggal Beli
                            </label>
                            <input type="date" name="tanggal_beli"
                                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Kode Store <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="kd_store" required
                                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Tanggal Ganti
                            </label>
                            <input type="date" name="tanggal_ganti"
                                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Tanggal Perbaikan
                            </label>
                            <input type="date" name="tanggal_perbaikan"
                                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Tanggal Mutasi
                            </label>
                            <input type="date" name="tanggal_mutasi"
                                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Tanggal Rusak
                            </label>
                            <input type="date" name="tanggal_rusak"
                                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Mutasi Dari
                            </label>
                            <input type="text" name="mutasi_dari"
                                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Mutasi Untuk
                            </label>
                            <input type="text" name="mutasi_untuk"
                                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Group Aset</label>
                            <input type="text" name="group_aset" id="input_group_aset" placeholder="Isi group manually"
                                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Status
                            </label>
                            <select name="status"
                                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="Baru">Baru</option>
                                <option value="Services">Services</option>
                                <option value="Mutasi">Mutasi</option>
                            </select>
                        </div>
                    </div>

                    <!-- Image Upload Section -->
                    <div class="mt-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Foto Aset
                        </label>
                        <div class="mt-1 flex flex-col items-center justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg"
                            id="dropzone">
                            <div class="space-y-1 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none"
                                    viewBox="0 0 48 48" aria-hidden="true">
                                    <path
                                        d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02"
                                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                <div class="flex text-sm text-gray-600">
                                    <label for="image"
                                        class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                                        <span>Upload a file</span>
                                        <input id="image" name="image" type="file" class="sr-only" accept="image/*">
                                    </label>
                                    <p class="pl-1">or drag and drop</p>
                                </div>
                                <p class="text-xs text-gray-500">PNG, JPG, GIF up to 10MB</p>
                            </div>

                            <!-- Image Preview -->
                            <div id="imagePreview" class="mt-4 hidden">
                                <img src="" alt="Preview" class="max-h-48 rounded-lg mx-auto shadow-md">
                            </div>
                        </div>

                    </div>

                    <div class="flex justify-end gap-4 mt-6 pt-6 border-t">
                        <button type="button"
                            class="close-modal px-6 py-2 border rounded-lg text-gray-600 hover:bg-gray-50">
                            Cancel
                        </button>
                        <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                            Save Asset
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Modal handling
        document.addEventListener('DOMContentLoaded', function () {
            const modal = document.getElementById('addAssetModal');
            const openModalBtn = document.getElementById('btnAdd');
            const closeModalBtns = document.querySelectorAll('.close-modal');
            const form = document.getElementById('assetForm');
            const imageInput = document.getElementById('image');
            const imagePreview = document.getElementById('imagePreview');
            const previewImg = imagePreview.querySelector('img');

            // Open modal
            openModalBtn.addEventListener('click', () => {
                modal.classList.remove('hidden');
            });

            // Close modal
            closeModalBtns.forEach(btn => {
                btn.addEventListener('click', () => {
                    modal.classList.add('hidden');
                });
            });
        });
    </script>

    <script src="/src/js/aset/main.js" type="module">
    </script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

</body>

</html>