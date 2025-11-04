<?php
session_start();
include '../../../aa_kon_sett.php';

$selected_date_get = $_GET['tanggal'] ?? date('Y-m-d');
$date_options = [];
for ($i = 0; $i < 7; $i++) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $date_options[] = $date;
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log Sinkronisasi Cabang</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/all.min.css">
    <link rel="stylesheet" href="../../style/header.css">
    <link rel="stylesheet" href="../../style/sidebar.css">
    <link rel="stylesheet" href="../../style/animation-fade-in.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../style/default-font.css">
    <link rel="stylesheet" href="../../output2.css">
    <link rel="icon" type="image/png" href="../../../public/images/logo1.png">

    <style>
        :root {
            --primary-color: #ec4899;
            --primary-dark: #db2777;
            --primary-light: #f472b6;
            --secondary-color: #f43f5e;
            --accent-color: #fb7185;
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --danger-color: #ef4444;
            --gray-50: #f9fafb;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-300: #d1d5db;
            --gray-600: #4b5563;
            --gray-700: #374151;
            --gray-800: #1f2937;
            --gray-900: #111827;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
        }

        .badge {
            padding: 0.375rem 0.875rem;
            font-size: 0.8125rem;
            font-weight: 600;
            border-radius: 50px;
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            transition: all 0.2s ease;
        }

        .badge i {
            font-size: 0.75rem;
        }

        .badge-success {
            color: #065f46;
            background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
            box-shadow: 0 2px 8px rgba(16, 185, 129, 0.2);
        }

        .badge-warning {
            color: #92400e;
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            box-shadow: 0 2px 8px rgba(245, 158, 11, 0.2);
        }

        .badge-danger {
            color: #991b1b;
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
            box-shadow: 0 2px 8px rgba(239, 68, 68, 0.2);
        }

        .log-container {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            color: #e2e8f0;
            padding: 1.5rem;
            border-radius: 1rem;
            max-height: 65vh;
            overflow-y: auto;
            font-family: 'Courier New', Courier, monospace;
            font-size: 0.875rem;
            box-shadow: inset 0 2px 10px rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(148, 163, 184, 0.1);
        }

        .log-container::-webkit-scrollbar {
            width: 8px;
        }

        .log-container::-webkit-scrollbar-track {
            background: rgba(15, 23, 42, 0.5);
            border-radius: 10px;
        }

        .log-container::-webkit-scrollbar-thumb {
            background: rgba(100, 116, 139, 0.5);
            border-radius: 10px;
        }

        .log-container::-webkit-scrollbar-thumb:hover {
            background: rgba(148, 163, 184, 0.7);
        }

        .log-container pre {
            white-space: pre-wrap;
            word-wrap: break-word;
            line-height: 1.6;
        }

        .log-success {
            color: #34d399;
            font-weight: 500;
        }

        .log-error {
            color: #f87171;
            font-weight: 700;
        }

        .header-card {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.95) 0%, rgba(255, 255, 255, 0.9) 100%);
            backdrop-filter: blur(10px);
            border-radius: 1.5rem;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.5);
        }

        .icon-wrapper {
            width: 64px;
            height: 64px;
            border-radius: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #ec4899 0%, #f43f5e 100%);
            box-shadow: 0 8px 24px rgba(236, 72, 153, 0.35);
            color: white;
        }

        .summary-card {
            background: white;
            padding: 1.75rem;
            border-radius: 1.25rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            border: 1px solid var(--gray-200);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .summary-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--card-color) 0%, var(--card-color-light) 100%);
        }

        .summary-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 32px rgba(0, 0, 0, 0.12);
        }

        .summary-card.total {
            --card-color: #ec4899;
            --card-color-light: #f472b6;
        }

        .summary-card.success {
            --card-color: #10b981;
            --card-color-light: #34d399;
        }

        .summary-card.danger {
            --card-color: #ef4444;
            --card-color-light: #f87171;
        }

        .summary-icon {
            width: 48px;
            height: 48px;
            border-radius: 0.875rem;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
        }

        .summary-card.total .summary-icon {
            background: linear-gradient(135deg, rgba(236, 72, 153, 0.1) 0%, rgba(244, 114, 182, 0.1) 100%);
            color: var(--primary-color);
        }

        .summary-card.success .summary-icon {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.1) 0%, rgba(52, 211, 153, 0.1) 100%);
            color: var(--success-color);
        }

        .summary-card.danger .summary-icon {
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.1) 0%, rgba(248, 113, 113, 0.1) 100%);
            color: var(--danger-color);
        }

        .filter-card-simple {
            background: white;
            border-radius: 1.25rem;
            padding: 1.75rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            border: 1px solid var(--gray-200);
        }

        .input-modern {
            padding: 0.75rem 1rem;
            border: 2px solid var(--gray-300);
            border-radius: 0.75rem;
            font-size: 0.9375rem;
            transition: all 0.2s ease;
            background: white;
        }

        .input-modern:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px rgba(236, 72, 153, 0.1);
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 0.75rem;
            font-weight: 600;
            font-size: 0.9375rem;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 16px rgba(236, 72, 153, 0.35);
        }

        .btn-primary:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(236, 72, 153, 0.45);
        }

        .btn-primary:active:not(:disabled) {
            transform: translateY(0);
        }

        .btn-primary:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .btn-secondary {
            background: white;
            color: var(--gray-700);
            padding: 0.75rem 1.5rem;
            border-radius: 0.75rem;
            font-weight: 600;
            font-size: 0.9375rem;
            border: 2px solid var(--gray-300);
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-secondary:hover:not(:disabled) {
            background: var(--gray-50);
            border-color: var(--primary-color);
            color: var(--primary-color);
            transform: translateY(-2px);
        }

        .filter-card {
            background: white;
            border-radius: 1.25rem;
            padding: 1.75rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            border: 1px solid var(--gray-200);
        }

        .table-modern {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }

        .table-modern thead {
            background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%);
        }

        .table-modern thead th {
            padding: 1rem 1.5rem;
            text-align: left;
            font-weight: 700;
            font-size: 0.875rem;
            color: var(--gray-700);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border-bottom: 2px solid var(--gray-200);
        }

        .table-modern thead th:first-child {
            border-top-left-radius: 0.75rem;
        }

        .table-modern thead th:last-child {
            border-top-right-radius: 0.75rem;
        }

        .table-modern tbody tr {
            transition: all 0.2s ease;
            border-bottom: 1px solid var(--gray-100);
        }

        .table-modern tbody tr.clickable-row {
            cursor: pointer;
        }

        .table-modern tbody tr.clickable-row:hover {
            background: linear-gradient(90deg, rgba(236, 72, 153, 0.03) 0%, rgba(244, 63, 94, 0.03) 100%);
            transform: translateX(4px);
        }

        .table-modern tbody td {
            padding: 1.25rem 1.5rem;
            font-size: 0.9375rem;
        }

        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(4px);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            animation: fadeIn 0.2s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        .modal-content {
            background: white;
            border-radius: 1.5rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            animation: slideUp 0.3s ease;
            max-height: 90vh;
            overflow-y: auto;
        }

        @keyframes slideUp {
            from {
                transform: translateY(20px);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .spinner-simple {
            border: 4px solid var(--gray-200);
            border-top: 4px solid var(--primary-color);
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 0.8s linear infinite;
            margin: 0 auto;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        @media (max-width: 768px) {
            .icon-wrapper {
                width: 52px;
                height: 52px;
            }

            .summary-card {
                padding: 1.25rem;
            }

            .filter-card-simple form {
                flex-direction: column;
                align-items: stretch !important;
            }

            .filter-card-simple .input-modern {
                width: 100% !important;
            }

            .filter-card-simple button {
                width: 100%;
                justify-content: center;
            }
        }

        .table-container {
            overflow-x: auto;
            border-radius: 0.75rem;
        }

        .table-container::-webkit-scrollbar {
            height: 8px;
        }

        .table-container::-webkit-scrollbar-track {
            background: var(--gray-100);
            border-radius: 10px;
        }

        .table-container::-webkit-scrollbar-thumb {
            background: var(--gray-300);
            border-radius: 10px;
        }

        .table-container::-webkit-scrollbar-thumb:hover {
            background: var(--gray-400);
        }
    </style>

</head>

<body class="bg-gray-50">
    <?php include '../../component/navigation_report.php' ?>
    <?php include '../../component/sidebar_report.php' ?>

    <main id="main-content" class="flex-1 p-6 ml-64">
        <section class="min-h-screen">
            <div class="max-w-7xl mx-auto">

                <div class="header-card p-6 rounded-2xl mb-6">
                    <div class="flex items-center justify-between flex-wrap gap-4">
                        <div class="flex items-center gap-4">
                            <div class="icon-wrapper">
                                <i class="fas fa-sync-alt fa-lg"></i>
                            </div>
                            <div>
                                <h1 class="text-2xl font-bold text-gray-800 mb-1">Log Sinkronisasi Cabang</h1>
                                <p class="text-sm text-gray-600">Monitor status sinkronisasi harian dari semua cabang
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="summary-card total">
                            <div class="summary-icon">
                                <i class="fas fa-store fa-lg"></i>
                            </div>
                            <h3 class="text-sm font-semibold text-gray-600 mb-1">Total Cabang</h3>
                            <p id="summary-total-cabang" class="text-3xl font-bold text-gray-900">-</p>
                        </div>
                        <div class="summary-card success">
                            <div class="summary-icon">
                                <i class="fas fa-check-circle fa-lg"></i>
                            </div>
                            <h3 class="text-sm font-semibold text-gray-600 mb-1">Sudah Sinkron</h3>
                            <p id="summary-sudah-sinkron" class="text-3xl font-bold text-green-600">-</p>
                        </div>
                        <div class="summary-card danger">
                            <div class="summary-icon">
                                <i class="fas fa-exclamation-circle fa-lg"></i>
                            </div>
                            <h3 class="text-sm font-semibold text-gray-600 mb-1">Belum Sinkron</h3>
                            <p id="summary-belum-sinkron" class="text-3xl font-bold text-red-600">-</p>
                        </div>
                    </div>
                </div>

                <div class="filter-card-simple">
                    <form id="filter-form" class="flex items-end gap-4">
                        <div class="flex-1">
                            <label for="tanggal" class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-calendar-alt text-pink-600 mr-1"></i>
                                Pilih Tanggal
                            </label>
                            <select name="tanggal" id="tanggal" class="input-modern w-full md:w-64">
                                <?php foreach ($date_options as $date_opt): ?>
                                    <option value="<?php echo $date_opt; ?>" <?php echo ($selected_date_get == $date_opt) ? 'selected' : ''; ?>>
                                        <?php echo date('d F Y', strtotime($date_opt)) . ($date_opt == date('Y-m-d') ? ' (Hari Ini)' : ''); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="submit" id="filter-submit-button"
                            class="btn-primary inline-flex items-center gap-2">
                            <i class="fas fa-filter"></i>
                            <span>Tampilkan</span>
                        </button>
                        <button type="button" id="show-all-logs-button"
                            class="btn-secondary inline-flex items-center gap-2">
                            <i class="fas fa-file-alt"></i>
                            <span>Lihat Semua Log</span>
                        </button>
                    </form>
                </div>

                <div class="filter-card">
                    <h3 class="text-lg font-bold text-gray-800 mb-4">
                        <i class="fas fa-list text-pink-600 mr-2"></i>
                        Status Sinkronisasi (<span
                            id="tanggal-dipilih-teks"><?php echo htmlspecialchars($selected_date_get); ?></span>)
                    </h3>
                    <div class="table-container">
                        <table class="table-modern" id="log-backup-table">
                            <thead>
                                <tr>
                                    <th><i class="fas fa-building mr-2"></i>Nama Cabang</th>
                                    <th><i class="fas fa-signal mr-2"></i>Status</th>
                                    <th><i class="fas fa-check mr-2"></i>Total Sinkron</th>
                                    <th><i class="fas fa-exclamation-triangle mr-2"></i>Total Error</th>
                                </tr>
                            </thead>
                            <tbody id="log-table-body">
                                <tr>
                                    <td colspan="4" class="text-center p-8">
                                        <div class="spinner-simple"></div>
                                        <p class="mt-3 text-gray-500 font-medium">Memuat data...</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </section>
    </main>

    <div id="logDetailModal" class="modal-overlay" style="display: none;">
        <div class="modal-content relative mx-4 p-6 w-full max-w-4xl">
            <div class="flex justify-between items-center border-b border-gray-200 pb-4 mb-4">
                <h3 class="text-xl font-bold text-gray-900">
                    <i class="fas fa-file-code text-pink-600 mr-2"></i>
                    Detail Log: <span id="modalCabangName" class="text-pink-600"></span>
                </h3>
                <button id="closeLogModal"
                    class="text-gray-400 hover:text-gray-600 text-3xl leading-none transition-colors">&times;</button>
            </div>
            <div id="modalBodyContent" class="log-container">
                <p class="text-center text-gray-500">Memuat data...</p>
            </div>
        </div>
    </div>

    <script src="/src/js/middleware_auth.js"></script>
    <script src="../../js/log_backup/log_handler.js" type="module"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</body>

</html>