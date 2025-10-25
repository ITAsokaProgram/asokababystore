<?php
session_start();
include '../../../aa_kon_sett.php';
require_once __DIR__ . '/lib/ShopeeApiService.php';
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
$shopeeService = new ShopeeApiService();
$redirect_uri = "https://" . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
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
if (isset($_GET['code'])) {
    $code = $_GET['code'];
    $shop_id = $_GET['shop_id'] ?? null;
    $main_account_id = $_GET['main_account_id'] ?? null;
    $is_main_account = false;
    $id_to_pass = 0;
    if ($shop_id) {
        $id_to_pass = (int)$shop_id;
        $is_main_account = false;
    } elseif ($main_account_id) {
        $id_to_pass = (int)$main_account_id;
        $is_main_account = true;
    }
    if ($id_to_pass > 0) {
        $shopeeService->handleOAuthCallback($code, $id_to_pass, $is_main_account);
        header('Location: ' . strtok($redirect_uri, '?'));
        exit();
    }
}
if (isset($_GET['action']) && $_GET['action'] == 'disconnect') {
    $shopeeService->disconnect();
    header('Location: ' . strtok($redirect_uri, '?'));
    exit();
}
require_once __DIR__ . '/../../component/menu_handler.php';
$menuHandler = new MenuHandler('shopee_dashboard');
if (!$menuHandler->initialize()) {
    exit();
}
if ($shopeeService->isConnected()) {
    $vendors = [];
    $stok_items = [];
    $filter_kd_store = '9998'; 
    $result_vendors = $conn->query("SELECT DISTINCT VENDOR FROM s_stok_ol WHERE KD_STORE = '9998' AND VENDOR IS NOT NULL AND VENDOR != '' ORDER BY VENDOR");
    if ($result_vendors) {
        while ($row = $result_vendors->fetch_assoc()) {
            $vendors[] = $row['VENDOR'];
        }
    }
    $filter_vendor = $_GET['vendor'] ?? 'ALL';
    $filter_plu = $_GET['plu'] ?? '';
    $filter_sku = $_GET['sku'] ?? '';
    $filter_descp = $_GET['descp'] ?? '';
    $allowed_sort_cols = ['plu', 'ITEM_N', 'DESCP', 'VENDOR', 'Qty', 'hrg_beli', 'price'];
    $sort_by = $_GET['sort_by'] ?? 'DESCP';
    $sort_dir = $_GET['sort_dir'] ?? 'ASC';
    if (!in_array($sort_by, $allowed_sort_cols)) {
        $sort_by = 'DESCP';
    }
    if (!in_array(strtoupper($sort_dir), ['ASC', 'DESC'])) {
        $sort_dir = 'ASC';
    }
    $nama_supplier = '';
    if (!empty($filter_vendor) && $filter_vendor !== 'ALL') {
        $stmt_supp = $conn->prepare("SELECT nama_supp FROM supplier WHERE kode_supp = ?");
        if ($stmt_supp) {
            $stmt_supp->bind_param("s", $filter_vendor);
            $stmt_supp->execute();
            $result_supp = $stmt_supp->get_result();
            if ($row_supp = $result_supp->fetch_assoc()) {
                $nama_supplier = $row_supp['nama_supp'];
            }
            $stmt_supp->close();
        }
    }
    if (!empty($filter_vendor)) {
        $params = [];
        $types = "";
        $sql_stok = "SELECT KD_STORE, plu, ITEM_N, DESCP, VENDOR, avg_cost, hrg_beli, ppn, netto, price, Qty 
                     FROM s_stok_ol 
                     WHERE KD_STORE = ?";
        $params[] = $filter_kd_store;
        $types .= "s";
        if ($filter_vendor !== 'ALL') {
            $sql_stok .= " AND VENDOR = ?";
            $params[] = $filter_vendor;
            $types .= "s";
        }
        if (!empty($filter_plu)) {
            $sql_stok .= " AND plu LIKE ?";
            $params[] = "%" . $filter_plu . "%";
            $types .= "s";
        }
        if (!empty($filter_sku)) {
            $sql_stok .= " AND ITEM_N LIKE ?";
            $params[] = "%" . $filter_sku . "%";
            $types .= "s";
        }
        if (!empty($filter_descp)) {
            $sql_stok .= " AND DESCP LIKE ?";
            $params[] = "%" . $filter_descp . "%";
            $types .= "s";
        }
        $sql_stok .= " ORDER BY $sort_by $sort_dir"; 
        $stmt = $conn->prepare($sql_stok);
        if ($stmt) {
            if (count($params) > 0) {
                $stmt->bind_param($types, ...$params);
            }
            $stmt->execute();
            $result_stok = $stmt->get_result();
            if ($result_stok) {
                while ($row = $result_stok->fetch_assoc()) {
                    $stok_items[] = $row;
                }
            }
            $stmt->close();
        }
    }
} else {
    $auth_url = $shopeeService->getAuthUrl($redirect_uri);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Terima Barang Cabang</title>
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
    /* Definisi gradient .btn-success dihapus */
    /* Definisi gradient .btn-danger dihapus */
    .btn-secondary {
        background: #f1f5f9;
        color: #475569;
        border-radius: 10px;
        padding: 10px 20px;
        font-weight: 600;
        transition: all 0.3s;
    }
    .btn-secondary:hover {
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
        transform: scale(1.01);
    }
    .table-modern tbody td {
        padding: 14px 12px;
        border-bottom: 1px solid #f1f5f9;
        font-size: 14px;
    }
    .input-qty {
        width: 90px;
        border: 2px solid #e2e8f0;
        border-radius: 8px;
        padding: 8px;
        text-align: center;
        font-weight: 600;
        transition: all 0.3s;
    }
    .input-qty:focus {
        outline: none;
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }
    .badge {
        display: inline-block;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }
    .badge-info {
        background: #dbeafe;
        color: #1e40af;
    }
    .badge-warning {
        background: #fef3c7;
        color: #92400e;
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
    .btn-primary {
      background: #ffeaf0;
      color: gray;
      border: none;
      border-radius: 10px;
      padding: 12px 24px;
      font-weight: 600;
      transition: all 0.3s;
    }
     .btn-primary:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 25px rgba(102, 126, 234, 0.4);
    }
    .btn-success {
      background: linear-gradient(135deg, #10b981 0%, #059669 100%);
      color: white;
      border-radius: 10px;
      padding: 14px 32px;
      font-weight: 600;
      box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
    }
    .btn-danger {
      background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    }
    .btn-secondary {
      background: #f1f5f9;
      color: #475569;
      border-radius: 10px;
      padding: 10px 20px;
      font-weight: 600;
      transition: all 0.3s;
    }
    .btn-secondary:hover {
      background: #e2e8f0;
    }
</style>
</head>
<body class="bg-gray-50">
  <?php include '../../component/navigation_report.php' ?>
  <?php include '../../component/sidebar_report.php' ?>
  <main id="main-content" class="flex-1 p-6 ml-64">
    <section class="min-h-screen">
      <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="header-card p-6 rounded-2xl mb-6">
          <div class="flex items-center justify-between flex-wrap gap-4">
            <div class="flex items-center gap-4">
              <div class="bg-white/20 backdrop-blur-sm p-3 rounded-xl">
                <img src="../../../public/images/logo/shopee.png" alt="Shopee" class="h-8 w-8">
              </div>
              <div>
                <h1 class="text-2xl font-bold mb-1">Terima Barang</h1>
                <p class="text-white/80 text-sm">Kelola penerimaan barang dari supplier</p>
              </div>
            </div>
            <?php if ($shopeeService->isConnected()): ?>
              <a href="?action=disconnect" class="btn-danger inline-flex items-center gap-2 py-2 px-5 rounded-lg transition shadow-lg hover:shadow-xl text-white">
                <i class="fas fa-unlink"></i>
                <span>Disconnect</span>
              </a>
            <?php endif; ?>
          </div>
        </div>
        <?php if ($shopeeService->isConnected()): ?>
          <!-- Filter Section -->
          <div class="filter-card p-6 mb-6">
            <div class="flex items-center justify-between mb-5">
              <h2 class="text-lg font-bold text-gray-800">
                <i class="fas fa-filter text-purple-600 mr-2"></i>
                Filter Data Stok
              </h2>
              <a href="history_terima_barang.php" class="btn-secondary inline-flex items-center gap-2 text-sm">
                <i class="fas fa-history"></i>
                <span>Lihat History</span>
              </a>
            </div>
            <form method="GET" action="terima_barang.php" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
              <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                  <i class="fas fa-store text-purple-600 mr-1"></i>
                  Vendor (Supplier)
                </label>
                <select name="vendor" class="input-modern w-full" required>
                  <option value="">Pilih Vendor</option>
                  <option value="ALL" <?php echo ($filter_vendor == 'ALL') ? 'selected' : ''; ?>>Semua Vendor</option>
                  <?php foreach ($vendors as $vendor): ?>
                    <option value="<?php echo htmlspecialchars($vendor); ?>" <?php echo ($filter_vendor == $vendor) ? 'selected' : ''; ?>>
                      <?php echo htmlspecialchars($vendor); ?>
                    </option>
                  <?php endforeach; ?>
                </select>
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
                  <i class="fas fa-tag text-purple-600 mr-1"></i>
                  SKU (ITEM_N)
                </label>
                <input type="text" name="sku" class="input-modern w-full" value="<?php echo htmlspecialchars($filter_sku); ?>" placeholder="Cari SKU...">
              </div>
              <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                  <i class="fas fa-align-left text-purple-600 mr-1"></i>
                  Deskripsi
                </label>
                <input type="text" name="descp" class="input-modern w-full" value="<?php echo htmlspecialchars($filter_descp); ?>" placeholder="Cari Deskripsi...">
              </div>
              <div class="lg:col-span-4">
                <button type="submit" class="btn-primary inline-flex items-center gap-2 w-full md:w-auto">
                  <i class="fas fa-search"></i>
                  <span>Muat Data Stok</span>
                </button>
              </div>
            </form>
          </div>
          <!-- Table Section -->
          <?php if (!empty($stok_items)): ?>
            <form id="bulk-receive-form" class="filter-card p-6">
              <div class="flex flex-wrap items-end gap-4 mb-6 pb-5 border-b border-gray-200">
                <div class="flex-1 min-w-[250px]">
                  <label class="block text-sm font-semibold text-gray-700 mb-2">
                    <i class="fas fa-file-invoice text-purple-600 mr-1"></i>
                    No. LPB
                  </label>
                  <input type="text" id="no_lpb" name="no_lpb" class="input-modern w-full" placeholder="Masukkan No. LPB" required>
                  <input type="hidden" id="form_kd_store" name="kd_store" value="<?php echo htmlspecialchars($filter_kd_store); ?>">
                </div>
                <button type="submit" class="btn-success inline-flex items-center gap-2 whitespace-nowrap">
                  <i class="fas fa-save"></i>
                  <span>Simpan Penerimaan</span>
                </button>
              </div>
              <div class="table-container scroll-container">
                <table class="table-modern" id="stock-table" style="min-width: 1500px;">
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
                        <a href="<?php echo build_sort_url($sort_by, $sort_dir, 'plu'); ?>">PLU<?php $sort_icon('plu'); ?></a>
                      </th>
                      <th>
                        <a href="<?php echo build_sort_url($sort_by, $sort_dir, 'ITEM_N'); ?>">SKU<?php $sort_icon('ITEM_N'); ?></a>
                      </th>
                      <th style="min-width: 250px;">
                        <a href="<?php echo build_sort_url($sort_by, $sort_dir, 'DESCP'); ?>">Deskripsi<?php $sort_icon('DESCP'); ?></a>
                      </th>
                      <?php if ($filter_vendor === 'ALL'): ?>
                        <th>
                          <a href="<?php echo build_sort_url($sort_by, $sort_dir, 'VENDOR'); ?>">Vendor<?php $sort_icon('VENDOR'); ?></a>
                        </th>
                      <?php endif; ?>
                      <th>
                        <a href="<?php echo build_sort_url($sort_by, $sort_dir, 'Qty'); ?>">Stok Saat Ini<?php $sort_icon('Qty'); ?></a>
                      </th>
                      <th>
                        <a href="<?php echo build_sort_url($sort_by, $sort_dir, 'hrg_beli'); ?>">Hrg. Beli<?php $sort_icon('hrg_beli'); ?></a>
                      </th>
                      <th>
                        <a href="<?php echo build_sort_url($sort_by, $sort_dir, 'price'); ?>">Hrg. Jual<?php $sort_icon('price'); ?></a>
                      </th>
                      <th>Qty Terima</th>
                      <th>Admin</th>
                      <th>Ongkir</th>
                      <th>Promo</th>
                      <th>Biaya Pesan</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($stok_items as $item): ?>
                      <tr data-plu="<?php echo htmlspecialchars($item['plu']); ?>"
                          data-descp="<?php echo htmlspecialchars($item['DESCP']); ?>"
                          data-avg-cost="<?php echo htmlspecialchars($item['avg_cost']); ?>"
                          data-hrg-beli="<?php echo htmlspecialchars($item['hrg_beli']); ?>"
                          data-ppn="<?php echo htmlspecialchars($item['ppn']); ?>"
                          data-netto="<?php echo htmlspecialchars($item['netto']); ?>"
                          data-price="<?php echo htmlspecialchars($item['price']); ?>"
                          data-vendor="<?php echo htmlspecialchars($item['VENDOR']); ?>">
                        <td class="font-semibold text-gray-900"><?php echo htmlspecialchars($item['plu']); ?></td>
                        <td><?php echo htmlspecialchars($item['ITEM_N']); ?></td>
                        <td>
                          <span class="link-history open-history-modal"
                                data-plu="<?php echo htmlspecialchars($item['plu']); ?>"
                                data-descp="<?php echo htmlspecialchars($item['DESCP']); ?>">
                              <?php echo htmlspecialchars($item['DESCP']); ?>
                          </span>
                        </td>
                        <?php if ($filter_vendor === 'ALL'): ?>
                          <td><span class="badge badge-info"><?php echo htmlspecialchars($item['VENDOR']); ?></span></td>
                        <?php endif; ?>
                        <td><?php echo htmlspecialchars($item['Qty']); ?></td>
                        <td>Rp <?php echo number_format($item['hrg_beli'], 0, ',', '.'); ?></td>
                        <td>Rp <?php echo number_format($item['price'], 0, ',', '.'); ?></td>
                        <td>
                          <input type="number" name="qty_rec" class="input-qty" value="0" min="0">
                        </td>
                        <td>
                          <input type="number" name="admin_s" class="input-qty" value="0" min="0" step="0.01">
                        </td>
                        <td>
                          <input type="number" name="ongkir" class="input-qty" value="0" min="0" step="0.01">
                        </td>
                        <td>
                          <input type="number" name="promo" class="input-qty" value="0" min="0" step="0.01">
                        </td>
                        <td>
                          <input type="number" name="biaya_psn" class="input-qty" value="0" min="0" step="0.01">
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            </form>
          <?php elseif (!empty($filter_vendor)): ?>
            <div class="filter-card p-8 text-center">
              <i class="fas fa-inbox text-gray-300 text-5xl mb-4"></i>
              <p class="text-gray-600">Tidak ada data stok ditemukan</p>
            </div>
          <?php endif; ?>
        <?php else: ?>
          <div class="filter-card p-16 text-center">
            <div class="max-w-md mx-auto">
              <div class="bg-gradient-to-br from-purple-100 to-blue-100 w-24 h-24 mx-auto mb-6 rounded-full flex items-center justify-center">
                <img src="../../../public/images/logo/shopee.png" alt="Shopee" class="h-12 w-12">
              </div>
              <h2 class="text-3xl font-bold text-gray-800 mb-3">Hubungkan Toko Shopee</h2>
              <p class="text-gray-600 mb-6">Sambungkan akun Shopee Anda untuk mulai mengelola penerimaan barang</p>
              <?php if(isset($auth_url)): ?>
                <a href="<?php echo htmlspecialchars($auth_url); ?>" class="btn-primary inline-flex items-center justify-center gap-3 px-8 py-4 text-lg">
                  <i class="fas fa-link"></i>
                  <span>Hubungkan Sekarang</span>
                </a>
              <?php else: ?>
                <div class="bg-red-50 border-2 border-red-200 text-red-700 px-6 py-4 rounded-xl inline-flex items-center gap-2">
                  <i class="fas fa-exclamation-triangle"></i>
                  <span class="font-semibold">Gagal membuat URL autentikasi</span>
                </div>
              <?php endif; ?>
            </div>
          </div>
        <?php endif; ?>
      </div>
    </section>
  </main>
  <!-- Modal History -->
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
  <script src="../../js/shopee/terima_barang_handler.js" type="module"></script>
  <script src="../../js/shopee/item_history_modal.js" type="module"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</body>
</html>