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

$allowed_sort_cols = [
    'tgl_pesan', 'no_faktur', 'no_lpb', 'plu', 'descp',
    'kode_supp', 'nama_supp', 'QTY_REC', 'hrg_beli', 'admin_s', 'ongkir'
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

$sql = "SELECT r.kd_store, r.no_faktur, r.plu, r.barcode, r.descp,
               r.hrg_beli, r.ppn, r.netto, r.admin_s, r.ongkir, r.promo, r.biaya_psn,
               r.price, r.QTY_REC, r.tgl_pesan, r.no_lpb, r.kode_kasir, r.kode_supp, r.jam,
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
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../style/default-font.css">
    <link rel="stylesheet" href="../../output2.css">
    <link rel="stylesheet" href="../../style/shopee/shopee.css">
    <link rel="icon" type="image/png" href="../../../public/images/logo1.png">
    <style>
        .table-container {
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            border: 1px solid #e2e8f0;
            border-radius: 0.75rem;
            background-color: white;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        .min-w-table {
            min-width: 2000px; 
        }
        .sticky-header th {
            position: sticky;
            top: 0;
            background-color: #f8fafc;
            z-index: 10;
        }
        .sticky-header th a {
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
        .sticky-header th a:hover {
            color: #2563eb;
        }
    </style>
</head>
<body class="bg-gray-50 overflow-auto">

    <?php include '../../component/navigation_report.php' ?>
    <?php include '../../component/sidebar_report.php' ?>
    
    <main id="main-content" class="flex-1 p-6 ml-64">
        <section class="min-h-[85vh] px-2 md:px-6">
            <div class="w-full max-w-none mx-auto">
                
                <div class="header-card p-6 rounded-2xl mb-6">
                    <div class="flex items-center justify-between flex-wrap gap-4">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-800 mb-1">History Terima Barang Cabang</h1>
                            <p class="text-gray-600">Menampilkan data penerimaan barang</p>
                        </div>
                        <a href="terima_barang.php" class="inline-flex items-center gap-2 bg-white hover:bg-gray-50 text-blue-600 font-semibold py-2 px-4 rounded-lg transition shadow-sm border border-gray-200">
                            <i class="fas fa-arrow-left"></i>
                            <span>Kembali ke Terima Barang</span>
                        </a>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-2xl shadow-lg mb-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">Filter History</h2>
                    <form method="GET" action="history_terima_barang.php" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        
                        <div>
                            <label for="tgl_dari" class="block text-sm font-medium text-gray-700 mb-1">Tgl. Dari</label>
                            <input type="date" id="tgl_dari" name="tgl_dari" class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500" value="<?php echo htmlspecialchars($filter_tgl_dari); ?>">
                        </div>
                        <div>
                            <label for="tgl_sampai" class="block text-sm font-medium text-gray-700 mb-1">Tgl. Sampai</label>
                            <input type="date" id="tgl_sampai" name="tgl_sampai" class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500" value="<?php echo htmlspecialchars($filter_tgl_sampai); ?>">
                        </div>
                        <div>
                            <label for="kode_supp" class="block text-sm font-medium text-gray-700 mb-1">Vendor (Supplier)</label>
                            <select id="kode_supp" name="kode_supp" class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Semua Vendor</option>
                                <?php foreach ($suppliers as $supplier): ?>
                                    <option value="<?php echo htmlspecialchars($supplier['kode_supp']); ?>" <?php echo ($filter_kode_supp == $supplier['kode_supp']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($supplier['nama_supp']) . ' (' . htmlspecialchars($supplier['kode_supp']) . ')'; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label for="filter_no_lpb" class="block text-sm font-medium text-gray-700 mb-1">No. LPB</label>
                            <input type="text" id="filter_no_lpb" name="no_lpb" class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500" value="<?php echo htmlspecialchars($filter_no_lpb); ?>" placeholder="Cari No. LPB...">
                        </div>
                        <div>
                            <label for="filter_no_faktur" class="block text-sm font-medium text-gray-700 mb-1">No. Faktur</label>
                            <input type="text" id="filter_no_faktur" name="no_faktur" class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500" value="<?php echo htmlspecialchars($filter_no_faktur); ?>" placeholder="Cari No. Faktur...">
                        </div>
                        <div>
                            <label for="filter_plu" class="block text-sm font-medium text-gray-700 mb-1">PLU</label>
                            <input type="text" id="filter_plu" name="plu" class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500" value="<?php echo htmlspecialchars($filter_plu); ?>" placeholder="Cari PLU...">
                        </div>
                        <div>
                            <label for="filter_descp" class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                            <input type="text" id="filter_descp" name="descp" class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500" value="<?php echo htmlspecialchars($filter_descp); ?>" placeholder="Cari Deskripsi...">
                        </div>

                        <div class="md:col-span-4 flex gap-4">
                            <button type="submit" class="inline-flex items-center gap-2 w-full md:w-auto bg-blue-500 hover:bg-blue-600 text-white font-semibold py-2 px-6 rounded-lg shadow-md transition transform hover:-translate-y-0.5">
                                <i class="fas fa-search"></i>
                                <span>Filter Data</span>
                            </button>
                            <a href="history_terima_barang.php" class="inline-flex items-center gap-2 w-full md:w-auto bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-2 px-6 rounded-lg shadow-md transition">
                                <i class="fas fa-undo"></i>
                                <span>Reset</span>
                            </a>
                        </div>
                    </form>
                </div>

                <div class="bg-white p-0 md:p-6 rounded-2xl shadow-lg">
                    <div class="table-container">
                        <table class="min-w-table w-full text-sm text-left text-gray-600">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 sticky-header">
                                <tr>
                                    <?php
                                    $sort_icon = function($col_name) use ($sort_by, $sort_dir) {
                                        if ($sort_by == $col_name) {
                                            echo ($sort_dir == 'ASC') ? ' <i class="fas fa-sort-up"></i>' : ' <i class="fas fa-sort-down"></i>';
                                        } else {
                                            echo ' <i class="fas fa-sort text-gray-300"></i>';
                                        }
                                    };
                                    ?>
                                    <th scope="col" class="py-3 px-4">
                                        <a href="<?php echo build_sort_url($sort_by, $sort_dir, 'tgl_pesan'); ?>">Tanggal<?php $sort_icon('tgl_pesan'); ?></a>
                                    </th>
                                    <th scope="col" class="py-3 px-4">
                                        <a href="<?php echo build_sort_url($sort_by, $sort_dir, 'no_faktur'); ?>">No. Faktur<?php $sort_icon('no_faktur'); ?></a>
                                    </th>
                                    <th scope="col" class="py-3 px-4">
                                        <a href="<?php echo build_sort_url($sort_by, $sort_dir, 'no_lpb'); ?>">No. LPB<?php $sort_icon('no_lpb'); ?></a>
                                    </th>
                                    <th scope="col" class="py-3 px-4">
                                        <a href="<?php echo build_sort_url($sort_by, $sort_dir, 'plu'); ?>">PLU<?php $sort_icon('plu'); ?></a>
                                    </th>
                                    <th scope="col" class="py-3 px-4" style="min-width: 250px;">
                                        <a href="<?php echo build_sort_url($sort_by, $sort_dir, 'descp'); ?>">Deskripsi<?php $sort_icon('descp'); ?></a>
                                    </th>
                                    <th scope="col" class="py-3 px-4">
                                        <a href="<?php echo build_sort_url($sort_by, $sort_dir, 'kode_supp'); ?>">Kd. Supp<?php $sort_icon('kode_supp'); ?></a>
                                    </th>
                                    <th scope="col" class="py-3 px-4" style="min-width: 200px;">
                                        <a href="<?php echo build_sort_url($sort_by, $sort_dir, 'nama_supp'); ?>">Nama Supplier<?php $sort_icon('nama_supp'); ?></a>
                                    </th>
                                    <th scope="col" class="py-3 px-4">
                                        <a href="<?php echo build_sort_url($sort_by, $sort_dir, 'QTY_REC'); ?>">Qty Terima<?php $sort_icon('QTY_REC'); ?></a>
                                    </th>
                                    <th scope="col" class="py-3 px-4">
                                        <a href="<?php echo build_sort_url($sort_by, $sort_dir, 'hrg_beli'); ?>">Hrg. Beli<?php $sort_icon('hrg_beli'); ?></a>
                                    </th>
                                    <th scope="col" class="py-3 px-4">
                                        <a href="<?php echo build_sort_url($sort_by, $sort_dir, 'admin_s'); ?>">Admin<?php $sort_icon('admin_s'); ?></a>
                                    </th>
                                    <th scope="col" class="py-3 px-4">
                                        <a href="<?php echo build_sort_url($sort_by, $sort_dir, 'ongkir'); ?>">Ongkir<?php $sort_icon('ongkir'); ?></a>
                                    </th>
                                    <th scope="col" class="py-3 px-4">Promo</th>
                                    <th scope="col" class="py-3 px-4">Biaya Pesan</th>
                                    <th scope="col" class="py-3 px-4">Store</th>
                                    <th scope="col" class="py-3 px-4">Kasir</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white">
                                <?php if (empty($history_items)): ?>
                                    <tr class="border-b">
                                        <td colspan="15" class="py-4 px-4 text-center text-gray-500">
                                            Tidak ada data history ditemukan dengan filter yang diterapkan.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($history_items as $item): ?>
                                        <tr class="border-b hover:bg-gray-50">
                                            <td class="py-3 px-4 whitespace-nowrap"><?php echo htmlspecialchars(date('d-m-Y H:i', strtotime($item['tgl_pesan']))); ?></td>
                                            <td class="py-3 px-4 font-medium text-gray-900 whitespace-nowrap"><?php echo htmlspecialchars($item['no_faktur']); ?></td>
                                            <td class="py-3 px-4"><?php echo htmlspecialchars($item['no_lpb']); ?></td>
                                            <td class="py-3 px-4"><?php echo htmlspecialchars($item['plu']); ?></td>
                                            <td class="py-3 px-4"><?php echo htmlspecialchars($item['descp']); ?></td>
                                            <td class="py-3 px-4"><?php echo htmlspecialchars($item['kode_supp']); ?></td>
                                            <td class="py-3 px-4"><?php echo htmlspecialchars($item['nama_supp']); ?></td>
                                            <td class="py-3 px-4 font-semibold text-blue-600"><?php echo number_format($item['QTY_REC'], 0); ?></td>
                                            <td class="py-3 px-4 text-right"><?php echo number_format($item['hrg_beli'], 0); ?></td>
                                            <td class="py-3 px-4 text-right"><?php echo number_format($item['admin_s'], 0); ?></td>
                                            <td class="py-3 px-4 text-right"><?php echo number_format($item['ongkir'], 0); ?></td>
                                            <td class="py-3 px-4 text-right"><?php echo number_format($item['promo'], 0); ?></td>
                                            <td class="py-3 px-4 text-right"><?php echo number_format($item['biaya_psn'], 0); ?></td>
                                            <td class="py-3 px-4"><?php echo htmlspecialchars($item['kd_store']); ?></td>
                                            <td class="py-3 px-4"><?php echo htmlspecialchars($item['kode_kasir']); ?></td>
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
    
    <script src="/src/js/middleware_auth.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</body>
</html>