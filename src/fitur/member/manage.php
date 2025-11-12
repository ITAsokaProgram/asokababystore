<?php
$is_filter_applied = isset($_GET['filter']);
$current_filter = $_GET['filter'] ?? '3bulan';
$valid_filters = ['3bulan', '6bulan', '9bulan', '12bulan', 'semua'];
if (!in_array($current_filter, $valid_filters)) {
    $current_filter = '3bulan';
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Member</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/all.min.css">
    <link rel="stylesheet" href="../../style/header.css">
    <link rel="stylesheet" href="../../style/sidebar.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap"
        rel="stylesheet">
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap"
        rel="stylesheet">
    <link rel="icon" type="image/png" href="../../../public/images/logo1.png">
    <link rel="stylesheet" href="../../style/default-font.css">
    <link rel="stylesheet" href="../../output2.css">
    <link rel="stylesheet" href="../../style/member/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/echarts/dist/echarts.min.js"></script>

</head>

<body class="bg-gradient-to-br from-slate-50 to-blue-50 text-gray-900">
    <?php include '../../component/navigation_report.php'; ?>
    <?php include '../../component/sidebar_report.php'; ?>
    <main id="main-content" class="flex-1 p-2 lg:p-4 transition-all duration-300 ml-64">
        <div class="max-w-7xl mx-auto space-y-6">
            <<div class="member-card fade-in p-4">
                <div class="page-header">
                    <h2 class="page-title">Aktivitas Member</h2>
                </div>
                <form action="manage.php" method="GET" class="filter-form">
                    <div class="filter-group">
                        <label for="filter" class="member-label">
                            <i class="fa-solid fa-calendar-days mr-1"></i>
                            Rentang Waktu Transaksi Terakhir
                        </label>
                        <select id="filter" name="filter" class="member-select w-full">
                            <option value="3bulan" <?php echo ($current_filter == '3bulan') ? 'selected' : ''; ?>>3 Bulan
                                Terakhir</option>
                            <option value="6bulan" <?php echo ($current_filter == '6bulan') ? 'selected' : ''; ?>>6 Bulan
                                Terakhir</option>
                            <option value="9bulan" <?php echo ($current_filter == '9bulan') ? 'selected' : ''; ?>>9 Bulan
                                Terakhir</option>
                            <option value="12bulan" <?php echo ($current_filter == '12bulan') ? 'selected' : ''; ?>>12
                                Bulan Terakhir</option>
                            <option value="semua" <?php echo ($current_filter == 'semua') ? 'selected' : ''; ?>>Semua
                                Waktu</option>
                        </select>
                    </div>
                    <button type="submit" class="btn-primary">
                        <i class="fa-solid fa-filter"></i>
                        Terapkan Filter
                    </button>
                </form>
        </div>
        <?php if ($is_filter_applied): ?>
            <div id="chart-section" class="member-card slide-up p-4">
                <div class="page-header">
                    <h2 class="page-title">Ringkasan Aktivitas Member</h2>
                    <p class="page-subtitle">Filter: <strong><?php echo htmlspecialchars($current_filter); ?></strong></p>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
                    <div class="stat-card stat-card-total">
                        <div class="stat-icon stat-icon-total">
                            <i class="fa-solid fa-users"></i>
                        </div>
                        <h3 class="text-sm font-medium uppercase tracking-wide mb-1" style="color: #831843;">Total Member
                        </h3>
                        <p id="total-member-placeholder" class="text-3xl font-bold" style="color: #9f1239;">
                            <i class="fa-solid fa-spinner fa-spin"></i>
                        </p>
                    </div>
                    <div class="stat-card stat-card-active">
                        <div class="stat-icon stat-icon-active">
                            <i class="fa-solid fa-user-check"></i>
                        </div>
                        <h3 class="text-sm font-medium uppercase tracking-wide mb-1" style="color: #065f46;">Active Member
                        </h3>
                        <p id="active-member-placeholder" class="text-3xl font-bold" style="color: #047857;">
                            <i class="fa-solid fa-spinner fa-spin"></i>
                        </p>
                    </div>
                    <div class="stat-card stat-card-inactive">
                        <div class="stat-icon stat-icon-inactive">
                            <i class="fa-solid fa-user-xmark"></i>
                        </div>
                        <h3 class="text-sm font-medium uppercase tracking-wide mb-1" style="color: #991b1b;">Inactive Member
                        </h3>
                        <p id="inactive-member-placeholder" class="text-3xl font-bold" style="color: #b91c1c;">
                            <i class="fa-solid fa-spinner fa-spin"></i>
                        </p>
                    </div>
                </div>
                <div class="chart-wrapper">
                    <div class="chart-container">
                        <div id="memberActivityChart" style="width: 100%; height: 450px;"></div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        </div>
    </main>
    <script src="../../js/ui/navbar_toogle.js" type="module"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="../../js/member/manage_handler.js" type="module"></script>
</body>

</html>