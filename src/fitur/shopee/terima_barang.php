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
      min-width: 1500px;
    }
    .sticky-header th {
      position: sticky;
      top: 0;
      background-color: #f8fafc;
      z-index: 10;
    }
    .data-input {
      width: 90px;
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
      <div class="w-full max-w-7xl mx-auto">
        
        <div class="header-card p-6 rounded-2xl mb-6">
          <div class="flex items-center justify-between flex-wrap gap-4">
            <div class="flex items-center gap-4">
              <div class="icon-wrapper">
                <img src="../../../public/images/logo/shopee.png" alt="Shopee Logo" class="h-10 w-10">
              </div>
              <div>
                <h1 class="text-2xl font-bold text-gray-800 mb-1">Terima Barang</h1>
              </div>
            </div>
            
            <?php if ($shopeeService->isConnected()): ?>
              <a href="?action=disconnect" class="inline-flex items-center gap-2 bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white font-semibold py-3 px-6 rounded-xl transition shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                <i class="fas fa-unlink"></i>
                <span>Disconnect</span>
              </a>
            <?php endif; ?>
          </div>
        </div>

        <?php if ($shopeeService->isConnected()): ?>
          
          <div class="bg-white p-6 rounded-2xl shadow-lg mb-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Filter Data Stok</h2>
            <a href="history_terima_barang.php" class="inline-flex items-center gap-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold py-2 px-4 rounded-lg transition shadow-sm">
                <i class="fas fa-history"></i>
                <span>Lihat History Penerimaan</span>
            </a>
            <form method="GET" action="terima_barang.php" class="grid grid-cols-1 md:grid-cols-4 gap-4">
              
              <div>
                <label for="vendor" class="block text-sm font-medium text-gray-700 mb-1">Vendor (Supplier)</label>
                <select id="vendor" name="vendor" class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500" required>
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
                <label for="filter_plu" class="block text-sm font-medium text-gray-700 mb-1">PLU</label>
                <input type="text" id="filter_plu" name="plu" class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500" value="<?php echo htmlspecialchars($filter_plu); ?>" placeholder="Cari PLU...">
              </div>

              <div>
                <label for="filter_sku" class="block text-sm font-medium text-gray-700 mb-1">SKU (ITEM_N)</label>
                <input type="text" id="filter_sku" name="sku" class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500" value="<?php echo htmlspecialchars($filter_sku); ?>" placeholder="Cari SKU...">
              </div>

              <div>
                <label for="filter_descp" class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                <input type="text" id="filter_descp" name="descp" class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500" value="<?php echo htmlspecialchars($filter_descp); ?>" placeholder="Cari Deskripsi...">
              </div>

              <div class="md:col-span-4 flex">
                <button type="submit" class="inline-flex items-center gap-2 w-full md:w-auto bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white font-semibold py-2 px-6 rounded-lg transition shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                  <i class="fas fa-search"></i>
                  <span>Muat Data Stok</span>
                </button>
              </div>
            </form>
          </div>


          <?php if (!empty($stok_items)): ?>
            <form id="bulk-receive-form" class="bg-white p-6 rounded-2xl shadow-lg">
              
              <div class="flex flex-wrap justify-between items-center gap-4 mb-6">
                <div>
                  <label for="no_lpb" class="block text-sm font-medium text-gray-700 mb-1">No. LPB</label>
                  <input type="text" id="no_lpb" name="no_lpb" class="px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Masukkan No. LPB" required>
                  
                  <input type="hidden" id="form_kd_store" name="kd_store" value="<?php echo htmlspecialchars($filter_kd_store); ?>">
                </div>
                <button type="submit" class="inline-flex items-center gap-2 bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white font-semibold py-3 px-8 rounded-lg transition shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                  <i class="fas fa-save"></i>
                  <span>Simpan Penerimaan</span>
                </button>
              </div>

              <div class="table-container">
                <table id="stock-table" class="min-w-table w-full text-sm text-left text-gray-600">
                  
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
                        <a href="<?php echo build_sort_url($sort_by, $sort_dir, 'plu'); ?>">PLU<?php $sort_icon('plu'); ?></a>
                      </th>
                      <th scope="col" class="py-3 px-4">
                        <a href="<?php echo build_sort_url($sort_by, $sort_dir, 'ITEM_N'); ?>">SKU (ITEM_N)<?php $sort_icon('ITEM_N'); ?></a>
                      </th>
                      <th scope="col" class="py-3 px-4" style="min-width: 250px;">
                        <a href="<?php echo build_sort_url($sort_by, $sort_dir, 'DESCP'); ?>">Deskripsi<?php $sort_icon('DESCP'); ?></a>
                      </th>
                      
                      <?php if ($filter_vendor === 'ALL'): ?>
                        <th scope="col" class="py-3 px-4 bg-gray-100">
                          <a href="<?php echo build_sort_url($sort_by, $sort_dir, 'VENDOR'); ?>">Vendor<?php $sort_icon('VENDOR'); ?></a>
                        </th>
                      <?php endif; ?>
                      
                      <th scope="col" class="py-3 px-4">
                        <a href="<?php echo build_sort_url($sort_by, $sort_dir, 'Qty'); ?>">Stok Saat Ini<?php $sort_icon('Qty'); ?></a>
                      </th>
                      <th scope="col" class="py-3 px-4">
                        <a href="<?php echo build_sort_url($sort_by, $sort_dir, 'hrg_beli'); ?>">Hrg. Beli<?php $sort_icon('hrg_beli'); ?></a>
                      </th>
                      <th scope="col" class="py-3 px-4">
                        <a href="<?php echo build_sort_url($sort_by, $sort_dir, 'price'); ?>">Hrg. Jual<?php $sort_icon('price'); ?></a>
                      </th>
                      <th scope="col" class="py-3 px-4 bg-blue-50">Qty Terima</th>
                      <th scope="col" class="py-3 px-4 bg-yellow-50">Admin</th>
                      <th scope="col" class="py-3 px-4 bg-yellow-50">Ongkir</th>
                      <th scope="col" class="py-3 px-4 bg-yellow-50">Promo</th>
                      <th scope="col" class="py-3 px-4 bg-yellow-50">Biaya Pesan</th>
                    </tr>
                  </thead>

                  <tbody class="bg-white">
                    <?php foreach ($stok_items as $item): ?>
                      <tr class="border-b hover:bg-gray-50"
                          data-plu="<?php echo htmlspecialchars($item['plu']); ?>"
                          data-descp="<?php echo htmlspecialchars($item['DESCP']); ?>"
                          data-avg-cost="<?php echo htmlspecialchars($item['avg_cost']); ?>"
                          data-hrg-beli="<?php echo htmlspecialchars($item['hrg_beli']); ?>"
                          data-ppn="<?php echo htmlspecialchars($item['ppn']); ?>"
                          data-netto="<?php echo htmlspecialchars($item['netto']); ?>"
                          data-price="<?php echo htmlspecialchars($item['price']); ?>"
                          data-vendor="<?php echo htmlspecialchars($item['VENDOR']); ?>"
                      >
                        <td class="py-3 px-4 font-medium text-gray-900"><?php echo htmlspecialchars($item['plu']); ?></td>
                        <td class="py-3 px-4"><?php echo htmlspecialchars($item['ITEM_N']); ?></td>
                        <td class="py-3 px-4">
                            <span class="text-blue-600 hover:text-blue-800 cursor-pointer font-medium open-history-modal"
                                  data-plu="<?php echo htmlspecialchars($item['plu']); ?>"
                                  data-descp="<?php echo htmlspecialchars($item['DESCP']); ?>">
                                <?php echo htmlspecialchars($item['DESCP']); ?>
                            </span>
                        </td>
                        <?php if ($filter_vendor === 'ALL'): ?>
                          <td class="py-3 px-4 bg-gray-50"><?php echo htmlspecialchars($item['VENDOR']); ?></td>
                        <?php endif; ?>
                        <td class="py-3 px-4"><?php echo htmlspecialchars($item['Qty']); ?></td>
                        <td class="py-3 px-4"><?php echo number_format($item['hrg_beli']); ?></td>
                        <td class="py-3 px-4"><?php echo number_format($item['price']); ?></td>
                        <td class="py-3 px-4 bg-blue-50">
                          <input type="number" name="qty_rec" class="data-input w-full px-2 py-1 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500" value="0" min="0">
                        </td>
                        <td class="py-3 px-4 bg-yellow-50">
                          <input type="number" name="admin_s" class="data-input w-full px-2 py-1 border border-gray-300 rounded-md focus:ring-yellow-500 focus:border-yellow-500" value="0" min="0" step="0.01">
                        </td>
                        <td class="py-3 px-4 bg-yellow-50">
                          <input type="number" name="ongkir" class="data-input w-full px-2 py-1 border border-gray-300 rounded-md focus:ring-yellow-500 focus:border-yellow-500" value="0" min="0" step="0.01">
                        </td>
                        <td class="py-3 px-4 bg-yellow-50">
                          <input type="number" name="promo" class="data-input w-full px-2 py-1 border border-gray-300 rounded-md focus:ring-yellow-500 focus:border-yellow-500" value="0" min="0" step="0.01">
                        </td>
                        <td class="py-3 px-4 bg-yellow-50">
                          <input type="number" name="biaya_psn" class="data-input w-full px-2 py-1 border border-gray-300 rounded-md focus:ring-yellow-500 focus:border-yellow-500" value="0" min="0" step="0.01">
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>

            </form>
          
          <?php elseif (!empty($filter_vendor)): ?>
            <div class="bg-white p-6 rounded-2xl shadow-lg text-center">
              <p class="text-gray-600">Tidak ada data stok ditemukan untuk Store <strong><?php echo htmlspecialchars($filter_kd_store); ?></strong> dan Vendor <strong><?php echo htmlspecialchars($filter_vendor); ?></strong>.</p>
            </div>
          <?php endif; ?>
          
        <?php else: ?>
          <div class="connect-card p-16 rounded-2xl">
            <div class="max-w-md mx-auto text-center py-4">
              <div class="icon-wrapper w-24 h-24 mx-auto mb-8 flex items-center justify-center">
                <img src="../../../public/images/logo/shopee.png" alt="Shopee" class="h-12 w-12">
              </div>
              <h2 class="text-3xl font-bold text-gray-800 mb-4">Hubungkan Toko Shopee</h2>
              <?php if(isset($auth_url)): ?>
                <a href="<?php echo htmlspecialchars($auth_url); ?>"
                   class="inline-flex items-center justify-center gap-3 w-full bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white font-bold py-4 px-8 rounded-xl text-lg transition shadow-xl hover:shadow-2xl transform hover:-translate-y-1">
                  <i class="fas fa-link text-xl"></i>
                  <span>Hubungkan Sekarang</span>
                </a>
              <?php else: ?>
                <div class="bg-red-50 border-2 border-red-200 text-red-700 px-6 py-4 rounded-xl">
                  <i class="fas fa-exclamation-triangle mr-2"></i>
                  <span class="font-semibold">Gagal membuat URL autentikasi</span>
                </div>
              <?php endif; ?>
            </div>
          </div>
        <?php endif; ?>
        
      </div>
    </section>
  </main>

  <div id="itemHistoryModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full flex items-center justify-center z-50" style="display: none;">
    <div class="relative mx-auto p-5 border w-full max-w-3xl shadow-lg rounded-xl bg-white">
        <div class="flex justify-between items-center border-b pb-3 mb-4">
            <h3 class="text-xl font-semibold text-gray-900">History Penerimaan: <span id="modalItemName" class="text-blue-600"></span></h3>
            <button id="closeHistoryModal" class="text-gray-400 hover:text-gray-600 text-2xl">&times;</button>
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