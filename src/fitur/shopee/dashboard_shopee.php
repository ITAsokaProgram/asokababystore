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
  <link rel="icon" type="image/png" href="../../../public/images/logo1.png">
  <style>
    * {
      box-sizing: border-box;
    }

    body {
      background: linear-gradient(135deg, #f5f7fa 0%, #e8ecf1 100%);
      min-height: 100vh;
    }

    .stats-badge { 
      display: inline-flex; 
      align-items: center;
      gap: 6px;
      padding: 6px 12px;
      border-radius: 20px;
      font-size: 13px;
      font-weight: 600;
      transition: all 0.2s ease;
    }
    
    .stats-badge:hover {
      transform: translateY(-1px);
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    
    .badge-stock { 
      background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%); 
      color: #1e40af; 
      border: 1px solid #93c5fd;
    }
    
    .badge-price { 
      background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%); 
      color: #15803d; 
      border: 1px solid #86efac;
    }
    
    .product-card {
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      border: 1px solid #e5e7eb;
      background: white;
      position: relative;
      overflow: hidden;
    }
    
    .product-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
      transition: left 0.5s;
    }
    
    .product-card:hover::before {
      left: 100%;
    }
    
    .product-card:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
      border-color: #3b82f6;
    }
    
    .product-image {
      position: relative;
      overflow: hidden;
      border-radius: 12px;
      background: #f3f4f6;
    }
    
    .product-image img {
      transition: transform 0.3s ease;
    }
    
    .product-card:hover .product-image img {
      transform: scale(1.05);
    }
    
    .btn-action {
      font-size: 13px;
      padding: 8px 16px;
      font-weight: 600;
      transition: all 0.2s ease;
      border: none;
      cursor: pointer;
      position: relative;
      overflow: hidden;
    }
    
    .btn-action::before {
      content: '';
      position: absolute;
      top: 50%;
      left: 50%;
      width: 0;
      height: 0;
      border-radius: 50%;
      background: rgba(255,255,255,0.3);
      transform: translate(-50%, -50%);
      transition: width 0.4s, height 0.4s;
    }
    
    .btn-action:hover::before {
      width: 300px;
      height: 300px;
    }
    
    .btn-action:active {
      transform: scale(0.97);
    }
    
    .btn-stock {
      background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
      color: white;
      box-shadow: 0 2px 8px rgba(59, 130, 246, 0.3);
    }
    
    .btn-stock:hover {
      box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
    }
    
    .btn-price {
      background: linear-gradient(135deg, #10b981 0%, #059669 100%);
      color: white;
      box-shadow: 0 2px 8px rgba(16, 185, 129, 0.3);
    }
    
    .btn-price:hover {
      box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4);
    }
    
    .input-field {
      font-size: 14px;
      padding: 10px 14px;
      border: 2px solid #e5e7eb;
      border-radius: 10px;
      transition: all 0.2s ease;
      background: white;
      max-width: 100%;
      min-width: 0;
    }
    
    .input-field:focus {
      outline: none;
      border-color: #3b82f6;
      box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
      transform: translateY(-1px);
    }
    
    .input-field:hover {
      border-color: #9ca3af;
    }
    
    .variant-card {
      background: white;
      border: 2px solid #e5e7eb;
      border-radius: 12px;
      transition: all 0.2s ease;
    }
    
    .variant-card:hover {
      border-color: #3b82f6;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    }
    
    .header-card {
      background: linear-gradient(135deg, #ffffff 0%, #f9fafb 100%);
      border: 1px solid #e5e7eb;
      box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    }
    
    .section-card {
      background: white;
      border: 1px solid #e5e7eb;
      box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    }
    
    .update-form-wrapper {
      background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%);
      border-radius: 12px;
      padding: 20px;
      border: 1px solid #e5e7eb;
    }
    
    .form-row {
      display: flex;
      gap: 12px;
      align-items: center;
      flex-wrap: wrap;
      width: 100%;
    }
    
    .form-group {
      display: flex;
      align-items: center;
      gap: 10px;
      flex: 1;
      max-width: 100%;
      flex-wrap: wrap;
    }
    
    .divider {
      width: 100%;
      height: 1px;
      background: linear-gradient(90deg, transparent, #e5e7eb, transparent);
      margin: 16px 0;
    }
    
    .loading-spinner {
      display: inline-block;
      width: 14px;
      height: 14px;
      border: 2px solid rgba(255,255,255,0.3);
      border-top-color: white;
      border-radius: 50%;
      animation: spin 0.6s linear infinite;
    }
    
    @keyframes spin {
      to { transform: rotate(360deg); }
    }
    
    .badge-id {
      background: #f3f4f6;
      color: #6b7280;
      padding: 4px 10px;
      border-radius: 6px;
      font-size: 11px;
      font-weight: 600;
      font-family: 'Courier New', monospace;
    }
    
    .connect-card {
      background: linear-gradient(135deg, #ffffff 0%, #fef3c7 100%);
    }
    
    .icon-wrapper {
      background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
      padding: 16px;
      border-radius: 20px;
      display: inline-flex;
      box-shadow: 0 4px 12px rgba(251, 191, 36, 0.2);
    }
    
    @media (max-width: 768px) {
      .form-row {
        flex-direction: column;
      }
      
      .form-group {
        width: 100%;
        min-width: auto;
      }
      
      .stats-badge {
        font-size: 12px;
        padding: 5px 10px;
      }
    }
    
    .section-header {
      background: linear-gradient(135deg, #f9fafb 0%, #ffffff 100%);
      border-bottom: 2px solid #e5e7eb;
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
                <h1 class="text-2xl font-bold text-gray-800 mb-1">Shopee Dashboard</h1>
                <p class="text-sm text-gray-600">Kelola produk dan stok toko Anda dengan mudah</p>
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
                          <span class="badge-id">SKU: <?php echo htmlspecialchars($item['item_sku'] ?? 'N/A'); ?></span>
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
                              </div>
                            </div>
                          <?php endforeach; ?>
                        </div>
                      <?php else: ?>
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

  <script src="../../js/shopee/dashboard_handler.js" type="module"></script>
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