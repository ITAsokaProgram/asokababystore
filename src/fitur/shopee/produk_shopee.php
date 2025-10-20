<?php
session_start();
include '../../../aa_kon_sett.php';
require_once __DIR__ . '/lib/ShopeeApiService.php'; 

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

$shopeeService = new ShopeeApiService();
$redirect_uri = "https://" . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];

if (isset($_GET['code']) && isset($_GET['shop_id'])) {
    $shopeeService->handleOAuthCallback($_GET['code'], (int)$_GET['shop_id']);
    header('Location: ' . strtok($redirect_uri, '?'));
    exit();
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

if ($shopeeService->isConnected()) {
  $product_list_response = $shopeeService->getProductList(['offset' => 0, 'page_size' => 20, 'item_status' => 'NORMAL']);
    
    if (isset($product_list_response['error']) && 
        ($product_list_response['error'] === 'invalid_acceess_token' || $product_list_response['error'] === 'invalid_access_token')) {
        
        $shopeeService->disconnect();
        
        $_SESSION['shopee_flash_message'] = 'Sesi Shopee Anda telah habis (expired). Silakan hubungkan kembali.';
        
        header('Location: ' . strtok($redirect_uri, '?'));
        exit();
    }

  $detailed_products = $shopeeService->getDetailedProductInfo($product_list_response);
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
  <style>
    
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
          <div class="section-card rounded-2xl overflow-hidden">
            <div class="section-header p-6">
              <div class="flex items-center justify-between">
                <div>
                  <h2 class="text-xl font-bold text-gray-800 mb-1">Daftar Produk</h2>
                  <p class="text-sm text-gray-600">Update harga dan stok produk Anda dengan cepat</p>
                </div>
                <div class="stats-badge" style="background: linear-gradient(135deg, #ede9fe 0%, #ddd6fe 100%); color: #6b21a8; border: 1px solid #c4b5fd;">
                  <i class="fas fa-boxes"></i>
                  <span><?php echo count($detailed_products); ?> Produk</span>
                </div>
              </div>
            </div>

            <?php if (!empty($detailed_products)): ?>
              <div class="divide-y divide-gray-100">
                <?php foreach ($detailed_products as $item): ?>
                  <div class="product-card p-6">
                    <div class="flex gap-6 mb-5">
                      <div class="product-image flex-shrink-0">
                        <img src="<?php echo htmlspecialchars($item['image']['image_url_list'][0] ?? 'https://placehold.co/100x100'); ?>" 
                             alt="<?php echo htmlspecialchars($item['item_name']); ?>" 
                             class="w-24 h-24 object-cover rounded-xl bg-gray-100 border-2 border-gray-200">
                      </div>
                      
                      <div class="flex-grow min-w-0">
                        <h3 class="font-bold text-gray-900 mb-3 text-lg line-clamp-2 leading-snug"><?php echo htmlspecialchars($item['item_name']); ?></h3>
                        
                        <div class="flex flex-wrap gap-2 mb-3">
                          <span class="stats-badge badge-price">
                            <i class="fas fa-tag"></i>
                            <span>Rp <span id="price-display-<?php echo $item['item_id']; ?>">
                              <?php echo (isset($item['has_model']) && $item['has_model'] === true && !empty($item['models'])) ? getPriceRange($item['models']) : number_format($item['price_info'][0]['original_price'] ?? 0, 0, ',', '.'); ?>
                            </span></span>
                          </span>
                          <span class="stats-badge badge-stock">
                            <i class="fas fa-boxes"></i>
                            <span>Stok: <strong id="stock-display-<?php echo $item['item_id']; ?>"><?php echo htmlspecialchars($item['calculated_total_stock'] ?? $item['stock_info_v2']['summary_info']['total_available_stock'] ?? $item['stock_info'][0]['seller_stock'] ?? 'N/A'); ?></strong></span>
                          </span>
                        </div>
                        
                        <div class="flex gap-2 flex-wrap">
                          <span class="badge-id">ID: <?php echo htmlspecialchars($item['item_id']); ?></span>
                          
                          <?php if (!(isset($item['has_model']) && $item['has_model'] === true && !empty($item['models']))): ?>
                            <span class="badge-id">SKU: <?php echo htmlspecialchars($item['item_sku'] ?? 'N/A'); ?></span>
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
                                    <span class="text-xs" style="background: #dbeafe; color: #1e40af; padding: 4px 8px; border-radius: 6px; font-weight: 600;">
                                      📦 Stok: <strong id="stock-display-<?php echo $model['model_id']; ?>" class="variant-stock"><?php echo htmlspecialchars($model['stock_info_v2']['summary_info']['total_available_stock'] ?? $model['stock_info'][0]['seller_stock'] ?? 'N/A'); ?></strong>
                                    </span>

                                    <span class="text-xs" style="background: #e0e7ff; color: #3730a3; padding: 4px 8px; border-radius: 6px; font-weight: 600;">
                                      SKU: <strong><?php echo htmlspecialchars($model['model_sku'] ?? 'N/A'); ?></strong>
                                    </span>
                                    </div>
                                </div>
                              </div>

                              <div class="form-row">
                                <form class="update-stock-form form-group" data-model-id="<?php echo $model['model_id']; ?>">
                                  <input type="hidden" name="item_id" value="<?php echo htmlspecialchars($item['item_id']); ?>">
                                  <input type="hidden" name="model_id" value="<?php echo htmlspecialchars($model['model_id']); ?>">
                                  <label class="text-sm font-semibold text-gray-700 whitespace-nowrap">
                                      Stok Baru:
                                  </label>
                                  <input type="number" name="new_stock" placeholder="0" class="input-field flex-1" required>
                                  <button type="submit" class="btn-action btn-stock rounded-xl whitespace-nowrap">
                                    <i class="fas fa-check mr-1"></i> Update Stok
                                  </button>
                                </form>
                                
                                <div class="divider"></div>
                                
                                <form class="update-price-form form-group" data-model-id="<?php echo $model['model_id']; ?>">
                                  <input type="hidden" name="item_id" value="<?php echo htmlspecialchars($item['item_id']); ?>">
                                  <input type="hidden" name="model_id" value="<?php echo htmlspecialchars($model['model_id']); ?>">
                                  <label class="text-sm font-semibold text-gray-700 whitespace-nowrap">
                                      Harga Baru:
                                  </label>
                                  <input type="number" name="new_price" placeholder="0" class="input-field flex-1" required>
                                  <button type="submit" class="btn-action btn-price rounded-xl whitespace-nowrap">
                                    <i class="fas fa-check mr-1"></i> Update Harga
                                  </button>
                                </form>
                                <div class="divider"></div>
                                <form class="sync-stock-form" data-item-id="<?php echo htmlspecialchars($item['item_id']); ?>" data-model-id="<?php echo htmlspecialchars($model['model_id']); ?>">
                                <input type="hidden" name="item_id" value="<?php echo htmlspecialchars($item['item_id']); ?>">
                                <input type="hidden" name="model_id" value="<?php echo htmlspecialchars($model['model_id']); ?>">
                                <input type="hidden" name="sku" value="<?php echo htmlspecialchars($model['model_sku']); ?>">
                                <button type="submit" class="btn-action btn-sync rounded-xl whitespace-nowrap" 
                                    title="Samakan stok Shopee dengan stok database (SKU: <?php echo htmlspecialchars($model['model_sku']); ?>)">
                                    <i class="fas fa-sync-alt mr-1"></i> Sync Stok
                                </button>
                            </form>
                              </div>
                            </div>
                          <?php endforeach; ?>
                        </div>
                      <?php else: ?>
                        <?php 
                          if (isset($_SESSION['shopee_flash_message'])): 
                          ?>
                              <div class="mb-6 p-4 rounded-xl bg-yellow-50 border-2 border-yellow-200 text-yellow-800" role="alert">
                                  <div class="flex items-center gap-3">
                                      <i class="fas fa-exclamation-triangle text-xl"></i>
                                      <span class="font-medium"><?php echo $_SESSION['shopee_flash_message']; ?></span>
                                  </div>
                              </div>
                          <?php 
                              unset($_SESSION['shopee_flash_message']); // Hapus pesan setelah ditampilkan
                          endif; 
                          ?>
                        <div class="space-y-4">
                          <form class="update-stock-form form-group" data-item-id="<?php echo $item['item_id']; ?>">
                            <input type="hidden" name="item_id" value="<?php echo htmlspecialchars($item['item_id']); ?>">
                            <label class="text-sm font-semibold text-gray-700 whitespace-nowrap w-28">
                              <i class="fas fa-box mr-1"></i> Stok Baru:
                            </label>
                            <input type="number" name="new_stock" placeholder="0" class="input-field flex-1" required>
                            <button type="submit" class="btn-action btn-stock rounded-xl whitespace-nowrap">
                              <i class="fas fa-check mr-1"></i> Update
                            </button>
                          </form>
                          
                          <div class="divider"></div>
                          
                          <form class="update-price-form form-group" data-item-id="<?php echo $item['item_id']; ?>">
                            <input type="hidden" name="item_id" value="<?php echo htmlspecialchars($item['item_id']); ?>">
                            <label class="text-sm font-semibold text-gray-700 whitespace-nowrap w-28">
                              <i class="fas fa-tag mr-1"></i> Harga Baru:
                            </label>
                            <input type="number" name="new_price" placeholder="0" class="input-field flex-1" required>
                            <button type="submit" class="btn-action btn-price rounded-xl whitespace-nowrap">
                              <i class="fas fa-check mr-1"></i> Update
                            </button>
                          </form>
                          <div class="divider"></div>
                          <form class="sync-stock-form" data-item-id="<?php echo htmlspecialchars($item['item_id']); ?>">
                            <input type="hidden" name="item_id" value="<?php echo htmlspecialchars($item['item_id']); ?>">
                            <input type="hidden" name="model_id" value="0"> <input type="hidden" name="sku" value="<?php echo htmlspecialchars($item['item_sku']); ?>">
                            <button type="submit" class="btn-action btn-sync rounded-xl whitespace-nowrap" 
                            title="Samakan stok Shopee dengan stok database (SKU: <?php echo htmlspecialchars($item['item_sku']); ?>)">
                              <i class="fas fa-sync-alt mr-1"></i> Sync Stok
                            </button>
                          </form>
                        </div>
                      <?php endif; ?>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php elseif (isset($product_list_response['error'])): ?>
              <div class="p-6">
                <div class="bg-red-50 border-2 border-red-200 text-red-700 px-6 py-4 rounded-xl" role="alert">
                  <div class="flex items-center gap-3">
                    <i class="fas fa-exclamation-circle text-2xl"></i>
                    <div>
                      <strong class="font-bold text-lg">Error API!</strong>
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
  <script>
      document.addEventListener("DOMContentLoaded", function () {
        const profileImg = document.getElementById("profile-img");
        const profileCard = document.getElementById("profile-card");

        if (profileImg && profileCard) {
          profileImg.addEventListener("click", function (event) {
            event.preventDefault();
            profileCard.classList.toggle("show");
          });

          document.addEventListener("click", function (event) {
            if (!profileCard.contains(event.target) && !profileImg.contains(event.target)) {
              profileCard.classList.remove("show");
            }
          });
        }
    });
  </script>
</body>
</html>