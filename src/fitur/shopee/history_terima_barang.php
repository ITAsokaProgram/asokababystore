<?php
session_start();
include '../../../aa_kon_sett.php';
require_once __DIR__ . '/../../component/menu_handler.php';

$menuHandler = new MenuHandler('shopee_dashboard');
if (!$menuHandler->initialize()) {
    exit();
}

function build_sort_url($current_sort_by, $current_sort_dir, $new_sort_by) {
    $params = $_GET;
    $new_sort_dir = 'ASC';
    if ($current_sort_by == $new_sort_by && $current_sort_dir == 'ASC') {
        $new_sort_dir = 'DESC';
    }
    $params['sort_by'] = $new_sort_by;
    $params['sort_dir'] = $new_sort_dir;
    return '?' . http_build_query($params);
}

$filter_no_faktur = $_GET['no_faktur'] ?? '';
$filter_plu = $_GET['plu'] ?? '';
$filter_descp = $_GET['descp'] ?? '';
$filter_no_lpb = $_GET['no_lpb'] ?? '';
$filter_kode_supp = $_GET['kode_supp'] ?? '';
$filter_tgl_dari = $_GET['tgl_dari'] ?? '';
$filter_tgl_sampai = $_GET['tgl_sampai'] ?? '';

// 1. Array allowed_sort_cols diperbarui
$allowed_sort_cols = [
    'tgl_pesan', 'jam', 'no_faktur', 'no_lpb', 'plu', 'barcode', 'descp',
    'kode_supp', 'nama_supp', 'QTY_REC', 'avg_cost', 'hrg_beli', 'ppn', 'netto',
    'admin_s', 'ongkir', 'promo', 'biaya_psn', 'price', 'net_price'
];
$sort_by = $_GET['sort_by'] ?? 'tgl_pesan';
$sort_dir = $_GET['sort_dir'] ?? 'DESC';

if (!in_array($sort_by, $allowed_sort_cols)) {
    $sort_by = 'tgl_pesan';
}
if (!in_array(strtoupper($sort_dir), ['ASC', 'DESC'])) {
    $sort_dir = 'DESC';
}

$suppliers = [];
$sql_supp = "SELECT DISTINCT s.kode_supp, s.nama_supp
              FROM supplier s
              JOIN s_receipt r ON s.kode_supp = r.kode_supp
              ORDER BY s.nama_supp ASC";
$result_supp = $conn->query($sql_supp);
if ($result_supp) {
    while ($row = $result_supp->fetch_assoc()) {
        $suppliers[] = $row;
    }
}

$history_items = [];
$params = [];
$types = "";

// 2. SQL Query diperbarui (menambahkan r.avg_cost, r.net_price)
$sql = "SELECT r.kd_store, r.no_faktur, r.plu, r.barcode, r.descp,
               r.avg_cost, r.hrg_beli, r.ppn, r.netto, r.admin_s, r.ongkir, r.promo, r.biaya_psn,
               r.price, r.net_price, r.QTY_REC, r.tgl_pesan, r.no_lpb, r.kode_kasir, r.kode_supp, r.jam,
               s.nama_supp
        FROM s_receipt r
        LEFT JOIN (
            SELECT kode_supp, MAX(nama_supp) as nama_supp
            FROM supplier
            GROUP BY kode_supp
        ) s ON r.kode_supp = s.kode_supp
        WHERE 1=1";

if (!empty($filter_no_faktur)) {
    $sql .= " AND r.no_faktur LIKE ?";
    $params[] = "%" . $filter_no_faktur . "%";
    $types .= "s";
}
if (!empty($filter_plu)) {
    $sql .= " AND r.plu LIKE ?";
    $params[] = "%" . $filter_plu . "%";
    $types .= "s";
}
if (!empty($filter_descp)) {
    $sql .= " AND r.descp LIKE ?";
    $params[] = "%" . $filter_descp . "%";
    $types .= "s";
}
if (!empty($filter_no_lpb)) {
    $sql .= " AND r.no_lpb LIKE ?";
    $params[] = "%" . $filter_no_lpb . "%";
    $types .= "s";
}
if (!empty($filter_kode_supp)) {
    $sql .= " AND r.kode_supp = ?";
    $params[] = $filter_kode_supp;
    $types .= "s";
}
if (!empty($filter_tgl_dari)) {
    $sql .= " AND DATE(r.tgl_pesan) >= ?";
    $params[] = $filter_tgl_dari;
    $types .= "s";
}
if (!empty($filter_tgl_sampai)) {
    $sql .= " AND DATE(r.tgl_pesan) <= ?";
    $params[] = $filter_tgl_sampai;
    $types .= "s";
}

$sql .= " ORDER BY $sort_by $sort_dir";
$sql .= " LIMIT 1000";

$stmt = $conn->prepare($sql);
if ($stmt) {
    if (count($params) > 0) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $history_items[] = $row;
        }
    }
    $stmt->close();
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>History Terima Barang</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/all.min.css">
    <link rel="stylesheet" href="../../style/header.css">
    <link rel="stylesheet" href="../../style/sidebar.css">
    <link rel="stylesheet" href="../../style/animation-fade-in.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../style/default-font.css">
    <link rel="stylesheet" href="../../output2.css">
    <link rel="stylesheet" href="../../style/shopee/shopee.css">
    <link rel="icon" type="image/png" href="../../../public/images/logo1.png">
    <style>
    * {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
    }
    
    /* Definisi gradient body dihapus */

    /* Definisi gradient .header-card dihapus */

    .filter-card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        transition: transform 0.2s;
    }

    .filter-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
    }

    .input-modern {
        border: 2px solid #e2e8f0;
        border-radius: 10px;
        padding: 10px 14px;
        transition: all 0.3s;
        font-size: 14px;
    }

    .input-modern:focus {
        outline: none;
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    /* Definisi gradient .btn-primary dihapus */

    /* Definisi gradient .btn-primary:hover dihapus */

    .btn-secondary {
        background: white;
        color: #667eea;
        border: 2px solid #667eea;
        border-radius: 10px;
        padding: 10px 20px;
        font-weight: 600;
        transition: all 0.3s;
    }

    .btn-secondary:hover {
        background: #667eea;
        color: white;
    }

    .btn-reset {
        background: #f1f5f9;
        color: #475569;
        border-radius: 10px;
        padding: 12px 24px;
        font-weight: 600;
        transition: all 0.3s;
    }

    .btn-reset:hover {
        background: #e2e8f0;
    }

    .table-container {
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    }

    .table-modern {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }

    /* Definisi gradient .table-modern thead dihapus */

    .table-modern thead th {
        padding: 16px 12px;
        font-weight: 600;
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        position: sticky;
        top: 0;
        z-index: 10;
    }

    .table-modern thead th a {
        /* Asumsi .shopee.css akan memberi warna text */
        /* color: white; */
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .table-modern tbody tr {
        background: white;
        transition: all 0.2s;
    }

    .table-modern tbody tr:hover {
        background: #f8fafc;
        transform: scale(1.005);
    }

    .table-modern tbody td {
        padding: 14px 12px;
        border-bottom: 1px solid #f1f5f9;
        font-size: 14px;
    }

    .badge {
        display: inline-block;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }

    .badge-success {
        background: #d1fae5;
        color: #065f46;
    }

    .badge-info {
        background: #dbeafe;
        color: #1e40af;
    }

    .badge-purple {
        background: #ede9fe;
        color: #5b21b6;
    }

    .link-history {
        color: #667eea;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
    }

    .link-history:hover {
        color: #764ba2;
        text-decoration: underline;
    }

    .modal-overlay {
        background: rgba(0, 0, 0, 0.6);
        backdrop-filter: blur(4px);
    }

    .modal-content {
        background: white;
        border-radius: 20px;
        box-shadow: 0 25px 50px rgba(0, 0, 0, 0.25);
        animation: slideUp 0.3s ease-out;
    }

    @keyframes slideUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .scroll-container {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    .scroll-container::-webkit-scrollbar {
        height: 8px;
    }

    .scroll-container::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 10px;
    }

    .scroll-container::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 10px;
    }

    .scroll-container::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }

    .stats-card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        border-left: 4px solid #667eea;
    }

    .empty-state {
        text-align: center;
        padding: 60px 20px;
    }

    .empty-state i {
        font-size: 64px;
        color: #cbd5e1;
        margin-bottom: 20px;
    }
</style>
</head>
<body class="bg-gray-50">
    <?php include '../../component/navigation_report.php' ?>
    <?php include '../../component/sidebar_report.php' ?>
    
    <main id="main-content" class="flex-1 p-6 ml-64">
        <section class="min-h-screen">
            <div class="max-w-[1600px] mx-auto">
                
                <div class="header-card p-6 rounded-2xl mb-6">
                    <div class="flex items-center justify-between flex-wrap gap-4">
                        <div>
                            <h1 class="text-2xl font-bold mb-1">History Terima Barang</h1>
                            <p class="text-white/80 text-sm">Riwayat penerimaan barang dari supplier</p>
                        </div>
                        <a href="terima_barang.php" class="btn-secondary inline-flex items-center gap-2">
                            <i class="fas fa-arrow-left"></i>
                            <span>Kembali</span>
                        </a>
                    </div>
                </div>

                <div class="filter-card p-6 mb-6">
                    <h2 class="text-lg font-bold text-gray-800 mb-5">
                        <i class="fas fa-filter text-purple-600 mr-2"></i>
                        Filter History
                    </h2>
                    
                    <form method="GET" action="history_terima_barang.php" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-calendar text-purple-600 mr-1"></i>
                                Tanggal Dari
                            </label>
                            <input type="date" name="tgl_dari" class="input-modern w-full" value="<?php echo htmlspecialchars($filter_tgl_dari); ?>">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-calendar text-purple-600 mr-1"></i>
                                Tanggal Sampai
                            </label>
                            <input type="date" name="tgl_sampai" class="input-modern w-full" value="<?php echo htmlspecialchars($filter_tgl_sampai); ?>">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-store text-purple-600 mr-1"></i>
                                Vendor (Supplier)
                            </label>
                            <select name="kode_supp" class="input-modern w-full">
                                <option value="">Semua Vendor</option>
                                <?php foreach ($suppliers as $supplier): ?>
                                    <option value="<?php echo htmlspecialchars($supplier['kode_supp']); ?>" <?php echo ($filter_kode_supp == $supplier['kode_supp']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($supplier['nama_supp']) . ' (' . htmlspecialchars($supplier['kode_supp']) . ')'; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-file-invoice text-purple-600 mr-1"></i>
                                No. LPB
                            </label>
                            <input type="text" name="no_lpb" class="input-modern w-full" value="<?php echo htmlspecialchars($filter_no_lpb); ?>" placeholder="Cari No. LPB...">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-receipt text-purple-600 mr-1"></i>
                                No. Faktur
                            </label>
                            <input type="text" name="no_faktur" class="input-modern w-full" value="<?php echo htmlspecialchars($filter_no_faktur); ?>" placeholder="Cari No. Faktur...">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-barcode text-purple-600 mr-1"></i>
                                PLU
                            </label>
                            <input type="text" name="plu" class="input-modern w-full" value="<?php echo htmlspecialchars($filter_plu); ?>" placeholder="Cari PLU...">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-align-left text-purple-600 mr-1"></i>
                                Deskripsi
                            </label>
                            <input type="text" name="descp" class="input-modern w-full" value="<?php echo htmlspecialchars($filter_descp); ?>" placeholder="Cari Deskripsi...">
                        </div>

                        <div class="lg:col-span-4 flex gap-3">
                            <button type="submit" class="btn-primary inline-flex items-center gap-2">
                                <i class="fas fa-search"></i>
                                <span>Filter Data</span>
                            </button>
                            <a href="history_terima_barang.php" class="btn-reset inline-flex items-center gap-2">
                                <i class="fas fa-undo"></i>
                                <span>Reset</span>
                            </a>
                        </div>
                    </form>
                </div>

                <?php if (!empty($history_items)): ?>
                    <div class="stats-card mb-6">
                        <div class="flex items-center gap-3">
                            <div class="bg-purple-100 p-3 rounded-lg">
                                <i class="fas fa-chart-bar text-purple-600 text-xl"></i>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Total Data Ditemukan</p>
                                <p class="text-2xl font-bold text-gray-800"><?php echo count($history_items); ?> <span class="text-sm font-normal text-gray-500">transaksi</span></p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="filter-card p-0 md:p-6">
                    <div class="table-container scroll-container">
                        <table class="table-modern" style="min-width: 2800px;">
                            <thead>
                                <tr>
                                    <?php
                                    $sort_icon = function($col_name) use ($sort_by, $sort_dir) {
                                        if ($sort_by == $col_name) {
                                            echo ($sort_dir == 'ASC') ? ' <i class="fas fa-sort-up"></i>' : ' <i class="fas fa-sort-down"></i>';
                                        } else {
                                            echo ' <i class="fas fa-sort" style="opacity: 0.3"></i>';
                                        }
                                    };
                                    ?>
                                    <th>
                                        <a href="<?php echo build_sort_url($sort_by, $sort_dir, 'tgl_pesan'); ?>">Tanggal<?php $sort_icon('tgl_pesan'); ?></a>
                                    </th>
                                    <th>
                                        <a href="<?php echo build_sort_url($sort_by, $sort_dir, 'jam'); ?>">Jam<?php $sort_icon('jam'); ?></a>
                                    </th>
                                    <th>
                                        <a href="<?php echo build_sort_url($sort_by, $sort_dir, 'no_faktur'); ?>">No. Faktur<?php $sort_icon('no_faktur'); ?></a>
                                    </th>
                                    <th>
                                        <a href="<?php echo build_sort_url($sort_by, $sort_dir, 'no_lpb'); ?>">No. LPB<?php $sort_icon('no_lpb'); ?></a>
                                    </th>
                                    <th>
                                        <a href="<?php echo build_sort_url($sort_by, $sort_dir, 'plu'); ?>">PLU<?php $sort_icon('plu'); ?></a>
                                    </th>
                                    <th>
                                        <a href="<?php echo build_sort_url($sort_by, $sort_dir, 'barcode'); ?>">Barcode<?php $sort_icon('barcode'); ?></a>
                                    </th>
                                    <th style="min-width: 250px;">
                                        <a href="<?php echo build_sort_url($sort_by, $sort_dir, 'descp'); ?>">Deskripsi<?php $sort_icon('descp'); ?></a>
                                    </th>
                                    <th>
                                        <a href="<?php echo build_sort_url($sort_by, $sort_dir, 'kode_supp'); ?>">Kd. Supp<?php $sort_icon('kode_supp'); ?></a>
                                    </th>
                                    <th style="min-width: 200px;">
                                        <a href="<?php echo build_sort_url($sort_by, $sort_dir, 'nama_supp'); ?>">Nama Supplier<?php $sort_icon('nama_supp'); ?></a>
                                    </th>
                                    <th>
                                        <a href="<?php echo build_sort_url($sort_by, $sort_dir, 'QTY_REC'); ?>">Qty Terima<?php $sort_icon('QTY_REC'); ?></a>
                                    </th>
                                    <th>
                                        <a href="<?php echo build_sort_url($sort_by, $sort_dir, 'avg_cost'); ?>">Avg Cost<?php $sort_icon('avg_cost'); ?></a>
                                    </th>
                                    <th>
                                        <a href="<?php echo build_sort_url($sort_by, $sort_dir, 'hrg_beli'); ?>">Hrg. Beli<?php $sort_icon('hrg_beli'); ?></a>
                                    </th>
                                    <th>
                                        <a href="<?php echo build_sort_url($sort_by, $sort_dir, 'ppn'); ?>">PPN<?php $sort_icon('ppn'); ?></a>
                                    </th>
                                    <th>
                                        <a href="<?php echo build_sort_url($sort_by, $sort_dir, 'netto'); ?>">Netto<?php $sort_icon('netto'); ?></a>
                                    </th>
                                    <th>
                                        <a href="<?php echo build_sort_url($sort_by, $sort_dir, 'admin_s'); ?>">Admin<?php $sort_icon('admin_s'); ?></a>
                                    </th>
                                    <th>
                                        <a href="<?php echo build_sort_url($sort_by, $sort_dir, 'ongkir'); ?>">Ongkir<?php $sort_icon('ongkir'); ?></a>
                                    </th>
                                    <th>
                                        <a href="<?php echo build_sort_url($sort_by, $sort_dir, 'promo'); ?>">Promo<?php $sort_icon('promo'); ?></a>
                                    </th>
                                    <th>
                                        <a href="<?php echo build_sort_url($sort_by, $sort_dir, 'biaya_psn'); ?>">Biaya Pesan<?php $sort_icon('biaya_psn'); ?></a>
                                    </th>
                                    <th>
                                        <a href="<?php echo build_sort_url($sort_by, $sort_dir, 'price'); ?>">Price<?php $sort_icon('price'); ?></a>
                                    </th>
                                    <th>
                                        <a href="<?php echo build_sort_url($sort_by, $sort_dir, 'net_price'); ?>">Net Price<?php $sort_icon('net_price'); ?></a>
                                    </th>
                                    <th>Store</th>
                                    <th>Kasir</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($history_items)): ?>
                                    <tr>
                                        <td colspan="22">
                                            <div class="empty-state">
                                                <i class="fas fa-inbox"></i>
                                                <p class="text-gray-600 text-lg font-semibold mb-2">Tidak Ada Data</p>
                                                <p class="text-gray-500 text-sm">Tidak ada history penerimaan ditemukan dengan filter yang diterapkan</p>
                                            </div>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($history_items as $item): ?>
                                        <tr>
                                            <td class="whitespace-nowrap">
                                                <div class="flex items-center gap-2">
                                                    <i class="fas fa-calendar-alt text-gray-400 text-xs"></i>
                                                    <?php echo htmlspecialchars(date('d/m/Y', strtotime($item['tgl_pesan']))); ?>
                                                </div>
                                            </td>
                                            <td class="whitespace-nowrap">
                                                <div class="flex items-center gap-2">
                                                    <i class="fas fa-clock text-gray-400 text-xs"></i>
                                                    <?php echo htmlspecialchars($item['jam']); ?>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge badge-info"><?php echo htmlspecialchars($item['no_faktur']); ?></span>
                                            </td>
                                            <td>
                                                <span class="badge badge-purple"><?php echo htmlspecialchars($item['no_lpb']); ?></span>
                                            </td>
                                            <td class="font-semibold text-gray-900"><?php echo htmlspecialchars($item['plu']); ?></td>
                                            <td class="font-semibold text-gray-700"><?php echo htmlspecialchars($item['barcode']); ?></td>
                                            <td>
                                                <span class="link-history open-history-modal"
                                                    data-plu="<?php echo htmlspecialchars($item['plu']); ?>"
                                                    data-descp="<?php echo htmlspecialchars($item['descp']); ?>">
                                                    <?php echo htmlspecialchars($item['descp']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo htmlspecialchars($item['kode_supp']); ?></td>
                                            <td><?php echo htmlspecialchars($item['nama_supp']); ?></td>
                                            <td>
                                                <span class="badge badge-success">
                                                    <i class="fas fa-box mr-1"></i>
                                                    <?php echo number_format($item['QTY_REC'], 0, ',', '.'); ?>
                                                </span>
                                            </td>
                                            <td class="text-right">Rp <?php echo number_format($item['avg_cost'], 0, ',', '.'); ?></td>
                                            <td class="text-right">Rp <?php echo number_format($item['hrg_beli'], 0, ',', '.'); ?></td>
                                            <td class="text-right">Rp <?php echo number_format($item['ppn'], 0, ',', '.'); ?></td>
                                            <td class="text-right">Rp <?php echo number_format($item['netto'], 0, ',', '.'); ?></td>
                                            <td class="text-right">Rp <?php echo number_format($item['admin_s'], 0, ',', '.'); ?></td>
                                            <td class="text-right">Rp <?php echo number_format($item['ongkir'], 0, ',', '.'); ?></td>
                                            <td class="text-right">Rp <?php echo number_format($item['promo'], 0, ',', '.'); ?></td>
                                            <td class="text-right">Rp <?php echo number_format($item['biaya_psn'], 0, ',', '.'); ?></td>
                                            <td class="text-right">Rp <?php echo number_format($item['price'], 0, ',', '.'); ?></td>
                                            <td class="text-right">Rp <?php echo number_format($item['net_price'], 0, ',', '.'); ?></td>
                                            <td><?php echo htmlspecialchars($item['kd_store']); ?></td>
                                            <td><?php echo htmlspecialchars($item['kode_kasir']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
            </div>
        </section>
    </main>

    <div id="itemHistoryModal" class="modal-overlay fixed inset-0 overflow-y-auto h-full w-full flex items-center justify-center z-50" style="display: none;">
        <div class="modal-content relative mx-4 p-6 w-full max-w-3xl">
            <div class="flex justify-between items-center border-b pb-4 mb-4">
                <h3 class="text-xl font-bold text-gray-900">
                    History Penerimaan: <span id="modalItemName" class="text-purple-600"></span>
                </h3>
                <button id="closeHistoryModal" class="text-gray-400 hover:text-gray-600 text-3xl leading-none">&times;</button>
            </div>
            <div id="modalBodyContent" class="max-h-[60vh] overflow-y-auto">
                <p class="text-center text-gray-500">Memuat data...</p>
            </div>
        </div>
    </div>
    
    <script src="/src/js/middleware_auth.js"></script>
    <script src="../../js/shopee/item_history_modal.js" type="module"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</body>
</html>