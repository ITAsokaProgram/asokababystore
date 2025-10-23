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
      
      $sql = "SELECT item_n, qty FROM s_barang WHERE kd_store = ? AND item_n IN ($placeholders)";
      
      $stmt = $conn->prepare($sql);
      
      if ($stmt) {
          $stmt->bind_param("s" . $types, $kd_store, ...$unique_skus);
          $stmt->execute();
          $result = $stmt->get_result();
          
          while ($row = $result->fetch_assoc()) {
              $sku_stock_map[$row['item_n']] = (int)$row['qty'];
          }
          $stmt->close();
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
                <span>Sync Semua Stok ke DB</span>
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
                <?php foreach ($detailed_products as $item): ?>
                  <div class="product-card p-6">
                    <div class="flex gap-6 mb-5">
                      <div class="product-image flex-shrink-0">
                            <a href="detail_produk_shopee.php?item_id=<?php echo $item['item_id']; ?>" class="product-image flex-shrink-0 cursor-pointer hover:opacity-80 transition">
                              <img src="<?php echo htmlspecialchars($item['image']['image_url_list'][0] ?? 'https://placehold.co/100x100'); ?>" 
                                   alt="<?php echo htmlspecialchars($item['item_name'] ?? '-'); ?>" 
                                   class="w-24 h-24 object-cover rounded-xl bg-gray-100 border-2 border-gray-200">
                            </a>
                      </div>
                      
                      <div class="flex-grow min-w-0">
                        <a href="detail_produk_shopee.php?item_id=<?php echo $item['item_id']; ?>" class="hover:text-orange-600 transition">
                          <h3 class="font-bold text-gray-900 mb-3 text-lg line-clamp-2 leading-snug"><?php echo htmlspecialchars($item['item_name'] ?? '-'); ?></h3>
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
                          <?php foreach ($item['models'] as $model): ?>
                            <div class="variant-card p-4">
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
                              </div>
                            </div>
                          <?php endforeach; ?>
                        </div>
                      <?php else: ?>
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
                <div class="pagination-controls p-6 border-t border-gray-100 bg-white rounded-b-2xl">
                    <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                        <div class="text-sm text-gray-600">
                            <?php 
                            if (empty($search_keyword)): 
                                $start_item = (int)$current_offset + 1; 
                                $end_item = (int)$current_offset + count($detailed_products);
                            ?>
                                Menampilkan <span class="font-semibold text-gray-800"><?php echo $start_item; ?></span> - ...
                            <?php else: ?>
                                Menampilkan <span class="font-semibold text-gray-800"><?php echo count($detailed_products); ?></span> produk ...
                            <?php endif; ?>
                        </div>
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

  <script src="../../js/shopee/produk_handler.js" type="module"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</body>
</html>