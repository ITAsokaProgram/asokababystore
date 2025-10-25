<?php
session_start();
include '../../../aa_kon_sett.php';
require_once __DIR__ . '/lib/ShopeeApiService.php'; 
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

$shopeeService = new ShopeeApiService();
$redirect_uri = "https://" . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];

function build_pagination_url($new_offset) {
    $params = $_GET;
    $params['offset'] = $new_offset;
    if ($new_offset == 0) {
        unset($params['offset']);
    }
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

$detailed_products = [];
$product_list_response = null;
$auth_url = null;
$page_size = 20; 
$search_keyword = isset($_GET['search']) ? trim($_GET['search']) : ''; 
$current_offset_raw = $_GET['offset'] ?? 0; 

$pagination_info = null;
$total_count = 0;
$has_next_page = false;
$next_offset = 0;

if (!empty($search_keyword)) {
    $current_offset = $current_offset_raw;
    $has_prev_page = ($current_offset_raw != 0); 
    $prev_offset = 0; 
} else {
    $current_offset = (int)$current_offset_raw;
    $has_prev_page = $current_offset > 0;
    $prev_offset = max(0, $current_offset - $page_size);
}
if ($shopeeService->isConnected()) {

    if (!empty($search_keyword)) {
        $search_params = [
            'offset'    => $current_offset,
            'page_size' => $page_size,
            'item_name'   => $search_keyword
        ];
        
        $product_list_response = $shopeeService->searchProductList($search_params);
        
        if (isset($product_list_response['response']['item_id_list'])) {
            
            $items_from_search = [];
            foreach ($product_list_response['response']['item_id_list'] as $item_id) {
                $items_from_search[] = ['item_id' => $item_id];
            }

            $total_from_api = $product_list_response['response']['total_count'] ?? 0;
            $next_offset_from_api = $product_list_response['response']['next_offset'] ?? 0;

            $calculated_has_next = $next_offset_from_api > 0;
            
            $normalized_response = [
                'response' => [
                    'item'          => $items_from_search,
                    'total_count'   => $total_from_api,
                    'has_next_page' => $calculated_has_next, 
                    'next_offset'   => $next_offset_from_api 
                ],
                'error'      => $product_list_response['error'] ?? '',
                'request_id' => $product_list_response['request_id'] ?? ''
            ];
            
            $product_list_response = $normalized_response;

        }
    } else {
        $list_params = [
            'offset'      => $current_offset,
            'page_size'   => $page_size,
            'item_status' => 'NORMAL'
        ];
        $product_list_response = $shopeeService->getProductList($list_params);
    }
    if (isset($product_list_response['error']) && 
        ($product_list_response['error'] === 'invalid_acceess_token' || $product_list_response['error'] === 'invalid_access_token')) {
        
        $shopeeService->disconnect();
        
        $_SESSION['shopee_flash_message'] = 'Sesi Shopee Anda telah habis (expired). Silakan hubungkan kembali.';
        
        header('Location: ' . strtok($redirect_uri, '?'));
        exit();
    }
    if (isset($product_list_response['response'])) {
        $pagination_info = $product_list_response['response'];
        $total_count = $pagination_info['total_count'] ?? 0;
        $has_next_page = $pagination_info['has_next_page'] ?? false;
        $next_offset = $pagination_info['next_offset'] ?? 0;
    }

  $detailed_products = $shopeeService->getDetailedProductInfo($product_list_response);

  $all_skus = [];
  $sku_stock_map = [];
  $kd_store = '3190';

  foreach ($detailed_products as $product) {
      if (isset($product['has_model']) && $product['has_model'] === true && !empty($product['models'])) {
          foreach ($product['models'] as $model) {
              if (!empty($model['model_sku'])) {
                  $all_skus[] = $model['model_sku'];
              }
          }
      } else {
          if (!empty($product['item_sku'])) {
              $all_skus[] = $product['item_sku'];
          }
      }
  }

  if (!empty($all_skus) && isset($conn) && $conn instanceof mysqli) {
      $unique_skus = array_unique($all_skus);
      $placeholders = implode(',', array_fill(0, count($unique_skus), '?'));
      $types = str_repeat('s', count($unique_skus));
      
      $sku_barang_data_map = [];
      $sku_stock_map = []; 

      $sql_barang = "SELECT item_n, plu, DESCP, VENDOR, Harga_Beli, Harga_Jual, qty 
                      FROM s_barang 
                      WHERE kd_store = ? AND item_n IN ($placeholders)";

      $stmt_barang = $conn->prepare($sql_barang);

      if ($stmt_barang) {
          $stmt_barang->bind_param("s" . $types, $kd_store, ...$unique_skus);
          $stmt_barang->execute();
          $result_barang = $stmt_barang->get_result();
          while ($row = $result_barang->fetch_assoc()) {
              $trimmed_sku = trim($row['item_n']);
              $sku_barang_data_map[$trimmed_sku] = [
                  'plu' => $row['plu'],
                  'descp' => $row['DESCP'],
                  'vendor' => $row['VENDOR'],
                  'harga_beli' => $row['Harga_Beli'],
                  'harga_jual' => $row['Harga_Jual']
              ];
              $sku_stock_map[$trimmed_sku] = (int)$row['qty'];
          }
          $stmt_barang->close();
      }

      $sku_stok_ol_data_map = [];
      $kd_store_ol = '9998'; 
      $sql_stok_ol = "SELECT item_n, plu, DESCP, VENDOR, hrg_beli, price 
                      FROM s_stok_ol 
                      WHERE kd_store = ? AND item_n IN ($placeholders)";

      $stmt_stok_ol = $conn->prepare($sql_stok_ol);

      if ($stmt_stok_ol) {
          $stmt_stok_ol->bind_param("s" . $types, $kd_store_ol, ...$unique_skus);
          $stmt_stok_ol->execute();
          $result_stok_ol = $stmt_stok_ol->get_result();
          while ($row = $result_stok_ol->fetch_assoc()) {
              $sku_stok_ol_data_map[trim($row['item_n'])] = [
                  'plu' => $row['plu'],
                  'descp' => $row['DESCP'],
                  'vendor' => $row['VENDOR'],
                  'hrg_beli' => $row['hrg_beli'],
                  'price' => $row['price']
              ];
          }
          $stmt_stok_ol->close();
      }
  }
} else {
  $auth_url = $shopeeService->getAuthUrl($redirect_uri);
}

function getPriceRange($models) {
    if (empty($models)) return null;
    $prices = array_column(array_column($models, 'price_info'), 0);
    $original_prices = array_column($prices, 'original_price');
    if (empty($original_prices)) return null;
    
    $minPrice = min($original_prices);
    $maxPrice = max($original_prices);
    
    return ($minPrice == $maxPrice)
        ? number_format($minPrice, 0, ',', '.')
        : number_format($minPrice, 0, ',', '.') . ' - ' . number_format($maxPrice, 0, ',', '.');
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard Shopee</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/all.min.css">
  <link rel="stylesheet" href="../../style/header.css">
  <link rel="stylesheet" href="../../style/sidebar.css">
  <link rel="stylesheet" href="../../style/animation-fade-in.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../../style/default-font.css">
  <link rel="stylesheet" href="../../output2.css">
  <link rel="stylesheet" href="../../style/shopee/shopee.css">
  <link rel="icon" type="image/png" href="../../../public/images/logo1.png">
</head>
<style>
    
    .btn-manage-stok-ol {
        padding: 0.5rem 1rem;
        font-size: 0.875rem;
        font-weight: 600;
        border: none;
        cursor: pointer;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }
    .btn-manage-ol-add {
        background-color: #dcfce7; 
        color: #166534; 
    }
    .btn-manage-ol-add:hover {
        background-color: #bbf7d0; 
    }
    .btn-manage-ol-edit {
        background-color: #e0e7ff; 
        color: #3730a3; 
    }
    .btn-manage-ol-edit:hover {
        background-color: #c7d2fe; 
    }
    .btn-manage-ol-disabled {
        background-color: #f3f4f6; 
        color: #9ca3af; 
        cursor: not-allowed;
    }
    
.swal2-modal {
    width: 100%;
    max-width: 600px;
}

.swal2-popup {
    border-radius: 1rem;
    padding: 2rem;
}

.btn-manage-stok-ol {
    padding: 0.5rem 1rem;
    font-size: 0.875rem;
    font-weight: 600;
    border: none;
    cursor: pointer;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    border-radius: 0.75rem;
}

.btn-manage-ol-add {
    background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%);
    color: #166534;
    border: 1px solid #86efac;
}

.btn-manage-ol-add:hover {
    background: linear-gradient(135deg, #bbf7d0 0%, #86efac 100%);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(34, 197, 94, 0.2);
}

.btn-manage-ol-edit {
    background: linear-gradient(135deg, #e0e7ff 0%, #c7d2fe 100%);
    color: #3730a3;
    border: 1px solid #a5b4fc;
}

.btn-manage-ol-edit:hover {
    background: linear-gradient(135deg, #c7d2fe 0%, #a5b4fc 100%);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(99, 102, 241, 0.2);
}

.btn-manage-ol-disabled {
    background-color: #f3f4f6;
    color: #9ca3af;
    cursor: not-allowed;
    opacity: 0.6;
}

.swal2-html-container {
    text-align: left !important;
    overflow: visible !important;
    max-height: none !important;
    padding: 0 !important;
    margin: 1.5rem 0 !important;
}

.swal-form-grid {
    display: grid;
    grid-template-columns: 140px 1fr;
    gap: 1.25rem;
    align-items: center;
    margin-top: 1.5rem;
    padding: 0 0.5rem;
}

.swal-form-grid label {
    font-weight: 600;
    text-align: right;
    font-size: 0.875rem;
    color: #374151;
    padding-right: 0.75rem;
    display: flex;
    align-items: center;
    justify-content: flex-end;
    min-height: 42px;
}

.swal-form-grid input {
    width: 100% !important;
    padding: 0.625rem 0.875rem !important;
    border: 2px solid #e5e7eb !important;
    border-radius: 0.5rem !important;
    font-size: 0.875rem !important;
    box-sizing: border-box !important;
    transition: all 0.2s ease !important;
    margin: 0 !important;
    background-color: white !important;
    color: #1f2937 !important;
    font-family: inherit !important;
}

.swal-form-grid input:focus {
    outline: none !important;
    border-color: #6366f1 !important;
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1) !important;
    background-color: white !important;
}

.swal-form-grid input:not(:read-only):hover {
    border-color: #d1d5db !important;
}

.swal-form-grid input:read-only {
    background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%) !important;
    color: #6b7280 !important;
    cursor: not-allowed !important;
    border-color: #e5e7eb !important;
    font-weight: 500 !important;
}

.swal2-html-container > p {
    font-size: 0.875rem;
    color: #6b7280;
    margin-bottom: 1rem;
    padding: 0.75rem 1rem;
    background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
    border-radius: 0.5rem;
    border-left: 3px solid #6366f1;
}

.swal2-html-container > p strong {
    color: #1f2937;
    font-weight: 600;
}

.swal2-title {
    font-size: 1.5rem !important;
    font-weight: 700 !important;
    color: #1f2937 !important;
    padding: 0 0 1rem 0 !important;
}

.swal2-confirm {
    background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%) !important;
    border: none !important;
    border-radius: 0.5rem !important;
    padding: 0.625rem 1.5rem !important;
    font-weight: 600 !important;
    transition: all 0.2s ease !important;
}

.swal2-confirm:hover {
    background: linear-gradient(135deg, #4f46e5 0%, #4338ca 100%) !important;
    transform: translateY(-1px) !important;
    box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3) !important;
}

.swal2-cancel {
    background-color: #e5e7eb !important;
    color: #4b5563 !important;
    border: none !important;
    border-radius: 0.5rem !important;
    padding: 0.625rem 1.5rem !important;
    font-weight: 600 !important;
    transition: all 0.2s ease !important;
}

.swal2-cancel:hover {
    background-color: #d1d5db !important;
    transform: translateY(-1px) !important;
}

@media (max-width: 640px) {
    .swal-form-grid {
        grid-template-columns: 1fr;
        gap: 0.75rem;
    }
    
    .swal-form-grid label {
        text-align: left;
        justify-content: flex-start;
        padding-right: 0;
        margin-bottom: 0.25rem;
        min-height: auto;
    }
    
    .swal2-popup {
        padding: 1.5rem;
    }
}

@keyframes shake-input {
    0%, 100% { transform: translateX(0); }
    25% { transform: translateX(-8px); }
    75% { transform: translateX(8px); }
}

.swal-form-grid input.invalid {
    animation: shake-input 0.3s ease;
    border-color: #ef4444 !important;
}

.swal2-loading .swal2-confirm {
    opacity: 0.7;
}

</style>

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
                <h1 class="text-2xl font-bold text-gray-800 mb-1">Produk</h1>
                <p class="text-sm text-gray-600">Kelola produk</p>
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

           <div class="search-filter-section">
            <div class="flex flex-col md:flex-row gap-4 items-center justify-between">
              <div class="search-box w-full md:w-auto">
                <i class="fas fa-search"></i>
                <input 
                  type="text" 
                  id="product-search" 
                  placeholder="Cari produk berdasarkan nama atau ID..." 
                  autocomplete="off"
                  aria-label="Cari produk"
                  value="<?php echo htmlspecialchars($search_keyword); ?>"
                >
                <button id="clear-search" class="hidden absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                  <i class="fas fa-times hidden"></i>
                </button>
              </div>
              <button id="sync-all-stock-btn" class="" data-total-count="<?php echo $total_count; ?>">
                <i class="fas fa-sync-alt"></i>
                <span>Sync Semua Stok DB ke Shopee</span>
             </button>
            </div>
          </div>

          <div class="section-card rounded-2xl overflow-hidden">
            <div class="section-header p-6">
              <div class="flex items-center justify-between">
                <div>
                  <h2 class="text-xl font-bold text-gray-800 mb-1">Daftar Produk</h2>
                  <p class="text-sm text-gray-600">Update harga dan stok produk Anda dengan cepat</p>
                </div>
                <div class="stats-badge" style="background: linear-gradient(135deg, #ede9fe 0%, #ddd6fe 100%); color: #6b21a8; border: 1px solid #c4b5fd;">
                  <i class="fas fa-boxes"></i>
                  <span><?php echo $total_count; ?> Produk</span>
                </div>
              </div>
            </div>

            <?php if (!empty($detailed_products)): ?>
              <div class="divide-y divide-gray-100">
              <?php 
              foreach ($detailed_products as $item): 
                  $product_card_style = '';
                  if (!(isset($item['has_model']) && $item['has_model'] === true && !empty($item['models']))) {
                      $item_sku_trimmed = trim($item['item_sku'] ?? '');
                      $stok_ol_data = $sku_stok_ol_data_map[$item_sku_trimmed] ?? null;
                      if ($stok_ol_data) {
                          $product_card_style = 'style="background-color: #ffeaf0;"';
                      }
                  }
              ?>
                  <div class="product-card p-6" <?php echo $product_card_style; ?>>
                    <div class="flex gap-6 mb-5">
                      <div class="product-image flex-shrink-0">
                            <a href="detail_produk_shopee.php?item_id=<?php echo $item['item_id']; ?>" class="product-image flex-shrink-0 cursor-pointer hover:opacity-80 transition">
                              <img src="<?php echo htmlspecialchars($item['image']['image_url_list'][0] ?? 'https://placehold.co/100x100'); ?>" 
                                   alt="<?php echo htmlspecialchars($item['item_name']); ?>" 
                                   class="w-24 h-24 object-cover rounded-xl bg-gray-100 border-2 border-gray-200">
                            </a>
                      </div>
                      
                      <div class="flex-grow min-w-0">
                        <a href="detail_produk_shopee.php?item_id=<?php echo $item['item_id']; ?>" class="hover:text-orange-600 transition">
                          <h3 class="font-bold text-gray-900 mb-3 text-lg line-clamp-2 leading-snug"><?php echo htmlspecialchars($item['item_name']); ?></h3>
                        </a>
                        <div class="flex flex-wrap gap-2 mb-3">
                          <span class="stats-badge badge-price">
                            <i class="fas fa-tag"></i>
                            <span>Rp <span id="price-display-<?php echo $item['item_id']; ?>">
                              <?php echo (isset($item['has_model']) && $item['has_model'] === true && !empty($item['models'])) ? getPriceRange($item['models']) : number_format($item['price_info'][0]['original_price'] ?? 0, 0, ',', '.'); ?>
                            </span></span>
                          </span>
                          <span class="stats-badge badge-stock">
                            <i class="fas fa-boxes"></i>
                            <span>Stok Shopee: <strong id="stock-display-<?php echo $item['item_id']; ?>"><?php echo htmlspecialchars($item['calculated_total_stock'] ?? $item['stock_info_v2']['summary_info']['total_available_stock'] ?? $item['stock_info'][0]['seller_stock'] ?? ''); ?></strong></span>
                          </span>
                          <?php if (!(isset($item['has_model']) && $item['has_model'] === true && !empty($item['models']))): ?>
                              <?php
                                $item_sku = $item['item_sku'] ?? null;
                                $db_stock = $sku_stock_map[$item_sku] ?? '';
                              ?>
                              <span class="stats-badge" style="background-color: #f3e8ff; color: #581c87; border-color: #e9d5ff;">
                                <i class="fas fa-database fa-fw"></i>
                                <span>Stok Sistem: <strong><?php echo $db_stock; ?></strong></span>
                              </span>
                          <?php endif; ?>
                        </div>
                        
                        <div class="flex gap-2 flex-wrap">
                          <span class="badge-id">ID: <?php echo htmlspecialchars($item['item_id']); ?></span>
                          
                          <?php if (!(isset($item['has_model']) && $item['has_model'] === true && !empty($item['models']))): ?>
                            <span class="badge-id">SKU: <?php echo htmlspecialchars($item['item_sku'] ?? ''); ?></span>
                          <?php endif; ?>
                          </div>
                      </div>
                    </div>

                    <div class="update-form-wrapper">
                      <?php if (isset($item['has_model']) && $item['has_model'] === true && !empty($item['models'])): ?>
                        <div class="flex items-center gap-2 mb-4">
                          <i class="fas fa-layer-group text-indigo-600"></i>
                          <p class="text-sm font-bold text-gray-700 uppercase tracking-wide">Variasi Produk</p>
                        </div>
                       <div class="space-y-3">
                        <?php 
                        foreach ($item['models'] as $model): 
                            $model_sku_trimmed = trim($model['model_sku'] ?? '');
                            $barang_data = $sku_barang_data_map[$model_sku_trimmed] ?? null;
                            $stok_ol_data = $sku_stok_ol_data_map[$model_sku_trimmed] ?? null;
                            $variant_card_style = '';
                            if ($stok_ol_data) {
                                $variant_card_style = 'style="background-color: #ffeaf0;"';
                            }
                        ?>
                            <div class="variant-card p-4" <?php echo $variant_card_style; ?>>
                              <div class="flex justify-between items-start mb-4">
                                <div>
                                  <p class="font-bold text-gray-900 mb-2"><?php echo htmlspecialchars($model['model_name'] ?? 'Variation'); ?></p>
                                  <div class="flex gap-3 flex-wrap">
                                    <span class="text-xs" style="background: #dcfce7; color: #15803d; padding: 4px 8px; border-radius: 6px; font-weight: 600;">
                                      💰 Rp <span id="price-display-<?php echo $model['model_id']; ?>" class="variant-price"><?php echo number_format($model['price_info'][0]['original_price'] ?? 0, 0, ',', '.'); ?></span>
                                    </span>
                                    <span class="text-xs" style="background: #e0e7ff; color: #3730a3; padding: 4px 8px; border-radius: 6px; font-weight: 600;">
                                      SKU: <strong><?php echo htmlspecialchars($model['model_sku'] ?? ''); ?></strong>
                                    </span>
                                    <span class="text-xs" style="background: #dbeafe; color: #1e40af; padding: 4px 8px; border-radius: 6px; font-weight: 600;">
                                      📦 Stok Shopee: <strong id="stock-display-<?php echo $model['model_id']; ?>" class="variant-stock"><?php echo htmlspecialchars($model['stock_info_v2']['summary_info']['total_available_stock'] ?? $model['stock_info'][0]['seller_stock'] ?? ''); ?></strong>
                                    </span>
                                    <?php
                                      $model_sku = $model['model_sku'] ?? null;
                                      $db_stock = $sku_stock_map[trim($model_sku)] ?? '';             
                                    ?>
                                    <span class="text-xs" style="background: #f3e8ff; color: #581c87; padding: 4px 8px; border-radius: 6px; font-weight: 600;">
                                      <i class="fas fa-database fa-fw"></i> Stok Sistem: <strong><?php echo $db_stock; ?></strong>
                                    </span>
                                  </div>
                                </div>
                              </div>

                              <div class="flex md:items-center justify-between md:flex-row flex-col flex-wrap gap-4">
                                <form class="update-stock-form form-group" data-model-id="<?php echo $model['model_id']; ?>">
                                  <input type="hidden" name="item_id" value="<?php echo htmlspecialchars($item['item_id']); ?>">
                                  <input type="hidden" name="model_id" value="<?php echo htmlspecialchars($model['model_id']); ?>">
                                  <label class="text-sm font-semibold text-gray-700 whitespace-nowrap w-28">
                                    <i class="fas fa-box mr-1"></i> Stok Baru:
                                  </label>
                                  <input type="number" name="new_stock" placeholder="0" class="input-field flex-1" required>
                                  <button type="submit" class="btn-action btn-stock rounded-xl whitespace-nowrap">
                                    Update
                                  </button>
                                </form>
                                
                                <form class="update-price-form form-group" data-model-id="<?php echo $model['model_id']; ?>">
                                  <input type="hidden" name="item_id" value="<?php echo htmlspecialchars($item['item_id']); ?>">
                                  <input type="hidden" name="model_id" value="<?php echo htmlspecialchars($model['model_id']); ?>">
                                  <label class="text-sm font-semibold text-gray-700 whitespace-nowrap w-28">
                                    <i class="fas fa-tag mr-1"></i> Harga Baru:
                                  </label>
                                  <input type="number" name="new_price" placeholder="0" class="input-field flex-1" required>
                                  <button type="submit" class="btn-action btn-price rounded-xl whitespace-nowrap">
                                    Update
                                  </button>
                                </form>
                                
                                <form class="sync-stock-form" data-item-id="<?php echo htmlspecialchars($item['item_id']); ?>" data-model-id="<?php echo htmlspecialchars($model['model_id']); ?>">
                                  <input type="hidden" name="item_id" value="<?php echo htmlspecialchars($item['item_id']); ?>">
                                  <input type="hidden" name="model_id" value="<?php echo htmlspecialchars($model['model_id']); ?>">
                                  <input type="hidden" name="sku" value="<?php echo htmlspecialchars($model['model_sku']); ?>">
                                  <button type="submit" class="btn-action btn-sync rounded-xl whitespace-nowrap" 
                                    title="Samakan stok Shopee dengan stok database (SKU: <?php echo htmlspecialchars($model['model_sku']); ?>)">
                                    Sinkronisasi Stok
                                  </button>
                                </form>
                              <?php

                                $btn_text = '';
                                $btn_class = '';
                                $btn_icon = 'fa-plus';
                                $btn_disabled = false;
                                $data_attrs = '';

                                $show_button = false; 

                                if ($barang_data) {
                                    if ($stok_ol_data) {
                                        $show_button = false; 
                                    } else {
                                        $show_button = true;
                                        $btn_text = 'Masukkan ke Stok Online';
                                        $btn_class = 'btn-manage-ol-add';
                                        $btn_icon = 'fa-plus';
                                        $data_attrs = 'data-mode="add" ' .
                                                      'data-sku="' . htmlspecialchars($model_sku_trimmed) . '" ' .
                                                      'data-plu="' . htmlspecialchars($barang_data['plu']) . '" ' .
                                                      'data-descp="' . htmlspecialchars($barang_data['descp']) . '" ' .
                                                      'data-vendor="' . htmlspecialchars($barang_data['vendor']) . '" ' .
                                                      'data-hrg_beli="' . htmlspecialchars($barang_data['harga_beli']) . '" ' .
                                                      'data-price="' . htmlspecialchars($barang_data['harga_jual']) . '"';
                                    }
                                } else {
                                    $show_button = true;
                                    $btn_text = 'SKU tdk ada di s_barang';
                                    $btn_class = 'btn-manage-ol-disabled';
                                    $btn_icon = 'fa-times';
                                    $btn_disabled = true;
                                }
                                ?>
                                <?php if ($show_button):  ?>
                                <button 
                                    type="button" 
                                    class="btn-action btn-manage-stok-ol <?php echo $btn_class; ?> rounded-xl whitespace-nowrap"
                                    <?php echo $data_attrs; ?>
                                    <?php if ($btn_disabled) echo 'disabled'; ?>>
                                    <i class="fas <?php echo $btn_icon; ?> fa-fw"></i>
                                    <span><?php echo $btn_text; ?></span>
                                </button>
                                <?php endif; ?>
                              </div>
                            </div>
                          <?php endforeach; ?>
                        </div>
                      <?php else: ?>
                      <?php
                        $item_sku_trimmed = trim($item['item_sku'] ?? '');
                        $barang_data = $sku_barang_data_map[$item_sku_trimmed] ?? null;
                        $stok_ol_data = $sku_stok_ol_data_map[$item_sku_trimmed] ?? null;
                        
                        ?>
                        <div class="flex md:items-center justify-between md:flex-row flex-col flex-wrap gap-4">
                          <form class="update-stock-form form-group" data-item-id="<?php echo $item['item_id']; ?>">
                              <input type="hidden" name="item_id" value="<?php echo htmlspecialchars($item['item_id']); ?>">
                              <label class="text-sm font-semibold text-gray-700 whitespace-nowrap w-28">
                                  <i class="fas fa-box mr-1"></i> Stok Baru:
                              </label>
                              <input type="number" name="new_stock" placeholder="0" class="input-field flex-1" required>
                              <button type="submit" class="btn-action btn-stock rounded-xl whitespace-nowrap">
                                  Update
                              </button>
                          </form>
                          
                          <form class="update-price-form form-group" data-item-id="<?php echo $item['item_id']; ?>">
                              <input type="hidden" name="item_id" value="<?php echo htmlspecialchars($item['item_id']); ?>">
                              <label class="text-sm font-semibold text-gray-700 whitespace-nowrap w-28">
                                  <i class="fas fa-tag mr-1"></i> Harga Baru:
                              </label>
                              <input type="number" name="new_price" placeholder="0" class="input-field flex-1" required>
                              <button type="submit" class="btn-action btn-price rounded-xl whitespace-nowrap">
                                  Update
                              </button>
                          </form>
                          <form class="sync-stock-form" data-item-id="<?php echo htmlspecialchars($item['item_id']); ?>">
                              <input type="hidden" name="item_id" value="<?php echo htmlspecialchars($item['item_id']); ?>">
                              <input type="hidden" name="model_id" value="0"> <input type="hidden" name="sku" value="<?php echo htmlspecialchars($item['item_sku']); ?>">
                              <button type="submit" class="btn-action btn-sync rounded-xl whitespace-nowrap" 
                              title="Samakan stok Shopee dengan stok database (SKU: <?php echo htmlspecialchars($item['item_sku']); ?>)">
                                  Sinkronisasi Stok
                              </button>
                          </form>
                          <?php
                          $btn_text = '';
                          $btn_class = '';
                          $btn_icon = 'fa-plus';
                          $btn_disabled = false;
                          $data_attrs = '';
                          
                          $show_button = false; 

                          if ($barang_data) {
                              if ($stok_ol_data) {
                                  $show_button = false;
                              } else {
                                  $show_button = true;
                                  $btn_text = 'Masukkan ke Stok Online';
                                  $btn_class = 'btn-manage-ol-add';
                                  $btn_icon = 'fa-plus';
                                  $data_attrs = 'data-mode="add" ' .
                                                'data-sku="' . htmlspecialchars($item_sku_trimmed) . '" ' .
                                                'data-plu="' . htmlspecialchars($barang_data['plu']) . '" ' .
                                                'data-descp="' . htmlspecialchars($barang_data['descp']) . '" ' .
                                                'data-vendor="' . htmlspecialchars($barang_data['vendor']) . '" ' .
                                                'data-hrg_beli="' . htmlspecialchars($barang_data['harga_beli']) . '" ' .
                                                'data-price="' . htmlspecialchars($barang_data['harga_jual']) . '"';
                              }
                          } else {
                              $show_button = true;
                              $btn_text = 'SKU tdk ada di s_barang';
                              $btn_class = 'btn-manage-ol-disabled';
                              $btn_icon = 'fa-times';
                              $btn_disabled = true;
                          }
                          ?>
                          <?php if ($show_button):  ?>
                          <button 
                              type="button" 
                              class="btn-action btn-manage-stok-ol <?php echo $btn_class; ?> rounded-xl whitespace-nowrap"
                              <?php echo $data_attrs; ?>
                              <?php if ($btn_disabled) echo 'disabled'; ?>>
                              <i class="fas <?php echo $btn_icon; ?> fa-fw"></i>
                              <span><?php echo $btn_text; ?></span>
                          </button>
                          <?php endif; ?>
                      </div>
                  <?php endif; ?>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
               <?php if (isset($_SESSION['shopee_flash_message'])): ?>
                <script>
                  document.addEventListener('DOMContentLoaded', () => {
                    Swal.fire({
                      icon: 'warning',
                      title: 'Perhatian',
                      text: '<?php echo addslashes($_SESSION['shopee_flash_message']); ?>',
                      toast: true,
                      position: 'top-end',
                      showConfirmButton: false,
                      timer: 4000,
                      timerProgressBar: true,
                      didOpen: (toast) => {
                        toast.addEventListener('mouseenter', Swal.stopTimer);
                        toast.addEventListener('mouseleave', Swal.resumeTimer);
                      }
                    });
                  });
                </script>
                <?php 
                  unset($_SESSION['shopee_flash_message']);
                endif; 
                ?>
            <?php elseif (!empty($product_list_response['error'])): ?>
              <div class="p-6">
                <div class="bg-red-50 border-2 border-red-200 text-red-700 px-6 py-4 rounded-xl" role="alert">
                  <div class="flex items-center gap-3">
                    <i class="fas fa-exclamation-circle text-2xl"></i>
                    <div>
                      <strong class="font-bold text-lg">Error API!</strong>
                      <?php ddd($product_list_response) ?>
                      <p class="text-sm mt-1"><?php echo htmlspecialchars($product_list_response['message']); ?></p>
                    </div>
                  </div>
                </div>
              </div>
            <?php else: ?>
              <div class="p-16 text-center">
                <i class="fas fa-box-open text-6xl text-gray-300 mb-4"></i>
                <p class="text-gray-500 text-lg font-medium">Tidak ada produk ditemukan</p>
                <p class="text-gray-400 text-sm mt-2">Produk Anda akan muncul di sini</p>
              </div>
            <?php endif; ?>
          </div>
          <?php if (!empty($detailed_products) && $pagination_info): ?>
                <div class="pagination-controls p-6 border-t border-gray-100 bg-white rounded-b-2xl mt-6">
                    <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                        <div class="inline-flex items-center gap-2">
                            <a href="<?php echo build_pagination_url($prev_offset); ?>"
                               class="pagination-btn <?php echo !$has_prev_page ? 'disabled' : ''; ?>"
                               <?php echo !$has_prev_page ? 'aria-disabled="true" tabindex="-1"' : ''; ?>>
                                <i class="fas fa-arrow-left"></i>
                                <span>Sebelumnya</span>
                            </a>
                            <a href="<?php echo build_pagination_url($next_offset); ?>"
                               class="pagination-btn <?php echo !$has_next_page ? 'disabled' : ''; ?>"
                               <?php echo !$has_next_page ? 'aria-disabled="true" tabindex="-1"' : ''; ?>>
                                <span>Berikutnya</span>
                                <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
                    
        <?php else: ?>
          <div class="connect-card p-16 rounded-2xl">
            <div class="max-w-md mx-auto text-center py-4">
              <div class="icon-wrapper w-24 h-24 mx-auto mb-8 flex items-center justify-center">
                <img src="../../../public/images/logo/shopee.png" alt="Shopee" class="h-12 w-12">
              </div>
              <h2 class="text-3xl font-bold text-gray-800 mb-4">Hubungkan Toko Shopee</h2>
              <p class="text-gray-600 mb-8 text-lg leading-relaxed">Kelola produk dan stok toko Shopee Anda dengan mudah dari satu dashboard yang terintegrasi</p>
              
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
  <script src="/src/js/middleware_auth.js"></script>
  <script src="../../js/shopee/produk_handler.js" type="module"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</body>
</html>