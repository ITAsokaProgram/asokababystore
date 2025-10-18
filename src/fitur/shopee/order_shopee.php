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
                <h1 class="text-2xl font-bold text-gray-800 mb-1">Order Shopee</h1>
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

      </div>
    </section>
  </main>

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