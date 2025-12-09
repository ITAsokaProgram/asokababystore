<?php
include 'aa_kon_sett.php';
include 'src/auth/middleware_login.php';
include 'src/api/middleware/permission_access.php';
require_once 'src/component/menu_handler.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");


$menuHandler = new MenuHandler('dashboard');

if (!$menuHandler->initialize()) {
  exit();
}

$user_id = $menuHandler->getUserId();
$logger = $menuHandler->getLogger();
$token = $menuHandler->getToken();

$permissionChecker = new PermissionAccess($conn);
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Beranda</title>

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/all.min.css">

  <link rel="stylesheet" href="css/header.css">
  <link rel="stylesheet" href="css/sidebar.css">
  <link rel="stylesheet" href="css/animation-fade-in.css">
  <link rel="icon" type="image/png" href="images/logo1.png">
  <link
    href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="src/style/default-font.css">
  <link rel="stylesheet" href="src/output2.css">
  <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">

  <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
  <style>
    /* --- Enhanced Card & Glass Styles --- */
    .glass-container {
      background: rgba(255, 255, 255, 0.80);
      backdrop-filter: blur(8px);
      border-radius: 1.25rem;
      box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.18);
      border: 1.5px solid #bae6fd;
      padding: 2rem;
    }

    .card-glass {
      background: rgba(255, 255, 255, 0.90);
      backdrop-filter: blur(6px);
      border-radius: 1.25rem;
      box-shadow: 0 4px 24px 0 rgba(31, 38, 135, 0.10);
      border: 1.5px solid #ec4899;
      transition: box-shadow 0.2s, transform 0.2s;
    }

    .card-glass:hover {
      box-shadow: 0 8px 32px 0 rgba(236, 72, 153, 0.18);
      transform: scale(1.02);
    }

    .skeleton-loader {
      background: linear-gradient(90deg, #f3f4f6 25%, #e5e7eb 50%, #f3f4f6 75%);
      background-size: 200% 100%;
      animation: skeleton 1.2s ease-in-out infinite;
    }

    @keyframes skeleton {
      0% {
        background-position: 200% 0;
      }

      100% {
        background-position: -200% 0;
      }
    }
  </style>
</head>

<body class="bg-white flex">

  <?php include 'src/component/navigation_report.php'; ?>
  <?php include 'src/component/sidebar_report.php'; ?>


  <main id="main-content" class="flex-1 p-8 transition-all duration-300 ml-64 mt-16 bg-gray-100">

    <section class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-4 mb-8">
      <div
        class="dashboard-card bg-gradient-to-br from-blue-50 via-white to-indigo-100 rounded-2xl p-2 shadow border border-blue-200/70 backdrop-blur-sm animate-fade-in-up">
        <div class="flex items-center justify-between mb-3">
          <h2 class="text-sm font-bold flex items-center gap-2 text-blue-800">
            <span class="w-6 h-6 flex items-center justify-center rounded-full bg-blue-100 shadow">
              <i class="fa-solid fa-users text-blue-600 text-base"></i>
            </span>
            Member
          </h2>
          <div class="w-2 h-2 bg-blue-400 rounded-full animate-pulse"></div>
        </div>

        <div class="space-y-4">
          <div
            class="cursor-pointer bg-gradient-to-br from-white/90 to-blue-50/80 rounded-xl p-1.5 shadow border border-blue-100/60 hover:scale-105 hover:shadow-md transition-all duration-300 flex flex-col animate-fade-in-up"
            onclick="window.location.href='/src/fitur/member/top_products'">
            <div class="flex items-center justify-between mb-0">
              <div class="flex items-center gap-2">
                <div class="w-2 h-2 bg-blue-500 rounded-full animate-pulse"></div>
                <span class="font-semibold text-xs text-gray-800 whitespace-nowrap" title="Top Sales">Top Sales</span>
              </div>
            </div>
            <p class="text-[10px] text-gray-500 italic px-1 mb-1 ml-4">Produk terlaris Member (Kemarin)</p>
            <div class="flex items-center justify-between text-xs text-gray-600">
              <div class="flex items-center gap-1 max-w-[100px]" title="Product Terlaris">
                <i class="fa-solid fa-trophy text-blue-600 text-xs"></i>
                <span id="top_sales_product_member" class="whitespace-nowrap"></span>
              </div>
            </div>
          </div>

          <div
            class="cursor-pointer bg-gradient-to-br from-white/90 to-amber-50/80 rounded-xl p-1.5 shadow border border-amber-100/60 hover:scale-105 hover:shadow-md transition-all duration-300 flex flex-col animate-fade-in-up"
            onclick="window.location.href='/src/fitur/member/product_favorite'">
            <div class="flex items-center justify-between mb-0">
              <div class="flex items-center gap-2">
                <div class="w-2 h-2 bg-blue-500 rounded-full animate-pulse"></div>
                <span class="font-semibold text-xs text-gray-800 whitespace-nowrap" title="Produk Favorit">Produk
                  Favorit</span>
              </div>
            </div>
            <p class="text-[10px] text-gray-500 italic px-1 mb-1 ml-4">Produk favorit by Member (Kemarin)</p>
            <div class="flex items-center justify-between text-xs text-gray-600">
              <div class="flex items-center gap-1 max-w-[100px]" title="Favorit Member">
                <i class="fa-solid fa-heart text-amber-500 text-xs"></i>
                <span id="top_sales_member" class="whitespace-nowrap"></span>
              </div>
            </div>
          </div>

          <div
            class="cursor-pointer bg-gradient-to-br from-white/90 to-green-50/80 rounded-xl p-1.5 shadow border border-green-100/60 hover:scale-105 hover:shadow-md transition-all duration-300 flex flex-col animate-fade-in-up"
            onclick="window.location.href='/src/fitur/member/top_sales'">
            <div class="flex items-center justify-between mb-0">
              <div class="flex items-center gap-2">
                <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                <span class="font-semibold text-xs text-gray-800 whitespace-nowrap" title="Top Member">Top Member</span>
              </div>
            </div>
            <p class="text-[10px] text-gray-500 italic px-1 mb-1 ml-4">Penjualan Member tertinggi (Kemarin)</p>
            <div class="flex items-center justify-between text-xs text-gray-600">
              <div class="flex items-center gap-1 max-w-[100px]" title="Top Member">
                <i class="fa-solid fa-user text-green-500 text-xs"></i>
                <span id="top_member" class="whitespace-nowrap"></span>
              </div>
              <div class="flex items-center gap-1 max-w-[200px]" title="Top Member">
                <i class="fa-solid fa-money-bill text-green-500 text-xs"></i>
                <span id="top_member_nominal" class="whitespace-nowrap font-mono text-xs italic"></span>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div
        class="dashboard-card bg-gradient-to-br from-emerald-50 to-teal-100 rounded-xl p-3 shadow-lg border border-emerald-200/50 backdrop-blur-sm">
        <div class="flex items-center justify-between mb-2">
          <h2 class="text-base font-bold flex items-center gap-2 text-emerald-800">
            <i class="fa-solid fa-credit-card text-emerald-600"></i> Transaksi
          </h2>
          <div class="flex items-center gap-2">
            <div class="w-1.5 h-1.5 bg-emerald-400 rounded-full animate-pulse"></div>
            <button id="view-all-transaksi" class="text-emerald-600 hover:text-emerald-800 transition hover:scale-110"
              title="Lihat Semua Transaksi">
              <i class="fa-solid fa-external-link-alt text-xs"></i>
            </button>
          </div>
        </div>

        <p class="text-xs text-gray-600 italic -mt-2 mb-2">Data transaksi (Member & Non-Member) kemarin.</p>

        <div
          class="cursor-pointer bg-gradient-to-br from-emerald-50 via-white to-emerald-100 rounded-xl p-1.5 shadow border border-emerald-200/80 text-center hover:shadow-lg hover:scale-[1.01] transition-all duration-200 mb-2 backdrop-blur-sm animate-fade-in-up"
          onclick="window.location.href='/src/fitur/transaction/detail_transaksi_cabang?cabang=all'">
          <div class="flex flex-col items-center mb-1">
            <span class="font-semibold text-emerald-700 text-xs tracking-wide">Total Transaksi Kemarin</span>
          </div>
          <div class="flex flex-col sm:flex-row items-center justify-center gap-1">
            <div class="flex flex-col items-center px-1">
              <span class="text-[10px] text-gray-500 font-medium">Total</span>
              <span class="text-xs font-bold text-emerald-700" id="total_trans"></span>
            </div>
            <div class="flex flex-col items-center px-1">
              <span class="text-[10px] text-gray-500 font-medium">Member</span>
              <span class="text-xs font-bold text-blue-700" id="total_trans_member"></span>
            </div>
            <div class="flex flex-col items-center px-1">
              <span class="text-[10px] text-gray-500 font-medium">Non</span>
              <span class="text-xs font-bold text-red-700" id="total_trans_non"></span>
            </div>
          </div>
        </div>

        <div class="space-y-1.5 mb-2 text-xs">
          <div
            class="bg-gradient-to-br from-emerald-50 via-white to-emerald-100 rounded-xl p-1.5 shadow border border-emerald-200/80 transition-all duration-200 hover:shadow-lg hover:scale-[1.01] cursor-pointer backdrop-blur-sm animate-fade-in-up"
            onclick="window.location.href='/src/fitur/transaction/transaksi_cabang'">
            <div class="flex items-center justify-between mb-1">
              <span id="cabang-t" class="font-medium flex items-center gap-1">
                <i class="fa-solid fa-store text-emerald-600"></i>
                <span class="truncate max-w-[80px]"></span>
              </span>
              <span
                class="text-emerald-600 text-[11px] font-semibold bg-emerald-100 px-2 py-0.5 rounded-full">Tertinggi</span>
            </div>
            <div class="flex justify-between font-bold text-[11px]">
              <span>Total: <span class="text-emerald-700" id="trans_tertinggi_total"></span></span>
              <span>Member: <span class="text-blue-600" id="trans_tertinggi_member"></span></span>
              <span>Non: <span class="text-red-500" id="trans_tertinggi_non"></span></span>
            </div>
          </div>

          <div
            class="bg-gradient-to-br from-teal-50 via-white to-teal-100 rounded-xl p-1.5 shadow border border-teal-200/80 transition-all duration-200 hover:shadow-lg hover:scale-[1.01] cursor-pointer backdrop-blur-sm animate-fade-in-up"
            onclick="window.location.href='/src/fitur/transaction/transaksi_cabang'">
            <div class="flex items-center justify-between mb-1">
              <span id="cabang-tr" class="font-medium flex items-center gap-1">
                <i class="fa-solid fa-store text-teal-600"></i>
                <span class="truncate max-w-[80px]"></span>
              </span>
              <span class="text-teal-600 text-[11px] font-semibold bg-teal-100 px-2 py-0.5 rounded-full">Terendah</span>
            </div>
            <div class="flex justify-between font-bold text-[11px]">
              <span>Total: <span class="text-teal-700" id="trans_terendah_total"></span></span>
              <span>Member: <span class="text-blue-600" id="trans_terendah_member"></span></span>
              <span>Non: <span class="text-red-500" id="trans_terendah_non"></span></span>
            </div>
          </div>
        </div>
      </div>

      <div
        class="dashboard-card bg-gradient-to-br from-red-50 to-rose-100 rounded-xl p-3 shadow-lg border border-red-200/50 backdrop-blur-sm">
        <div class="flex items-center justify-between mb-2">
          <h2 class="text-base font-bold flex items-center gap-2 text-red-800">
            <i class="fa-solid fa-exclamation-triangle text-red-600"></i> Invalid Transaksi
          </h2>
          <div class="flex items-center gap-2">
            <div class="w-1.5 h-1.5 bg-red-400 rounded-full animate-pulse"></div>
            <button id="view-all-invalid" class="text-red-600 hover:text-red-800 transition hover:scale-110"
              title="Lihat Semua Invalid Transaksi">
              <i class="fa-solid fa-external-link-alt text-xs"></i>
            </button>
          </div>
        </div>

        <p class="text-xs text-gray-600 italic -mt-2 mb-2">Top 3 kasir (berdasarkan void) kemarin.</p>

        <div class="grid grid-cols-1 gap-2 mb-2 text-xs" id="invalid-transaksi-container">
          <div class="flex items-center justify-center text-gray-500 bg-white/60 rounded-xl p-2">
            <i class="fa-solid fa-spinner fa-spin mr-1"></i> Loading...
          </div>
        </div>
      </div>

      <div
        class="dashboard-card bg-gradient-to-br from-orange-50 to-amber-100 rounded-xl p-4 shadow-lg border border-orange-200/50 backdrop-blur-sm">
        <div class="flex items-center justify-between mb-2">
          <h2 class="text-lg font-bold flex items-center gap-2 text-orange-800">
            <i class="fa-solid fa-chart-line text-orange-600"></i> Margin Minus
          </h2>
          <div class="flex items-center gap-2">
            <div class="w-1.5 h-1.5 bg-orange-400 rounded-full animate-pulse"></div>
            <button id="view-all-margin-minus" class="text-orange-600 hover:text-orange-800 transition hover:scale-110"
              title="Lihat Semua Margin Minus">
              <i class="fa-solid fa-external-link-alt text-xs"></i>
            </button>
          </div>
        </div>

        <p class="text-xs text-gray-600 italic -mt-2 mb-2">Top 3 cabang (margin minus) kemarin.</p>

        <div id="top-margin-minus-container" class="grid grid-cols-1 gap-2 text-xs">
          <div class="flex items-center justify-center text-gray-500 bg-white/60 rounded-xl p-2">
            <i class="fa-solid fa-spinner fa-spin mr-1"></i> Loading...
          </div>
        </div>
      </div>

      <div
        class="dashboard-card bg-gradient-to-br from-purple-50 to-violet-100 rounded-xl p-4 shadow-lg border border-purple-200/50 backdrop-blur-sm">
        <div class="flex items-center justify-between mb-3">
          <h2 class="text-lg font-bold flex items-center gap-2 text-purple-800">
            <i class="fa-solid fa-undo text-purple-600"></i> Retur Barang
          </h2>
          <div class="flex items-center gap-2">
            <div class="w-1.5 h-1.5 bg-purple-400 rounded-full animate-pulse"></div>
            <button id="view-all-retur" class="text-purple-600 hover:text-purple-800 transition hover:scale-110"
              title="Lihat Semua Retur Barang">
              <i class="fa-solid fa-external-link-alt text-xs"></i>
            </button>
          </div>
        </div>

        <p class="text-xs text-gray-600 italic -mt-2 mb-2">Top 3 kasir (berdasarkan retur) kemarin.</p>

        <div class="grid grid-cols-1 gap-2 mb-3 text-xs" id="top-retur-container">
          <div class="flex items-center justify-center text-gray-500 bg-white/60 rounded-xl p-2">
            <i class="fa-solid fa-spinner fa-spin mr-1"></i> Loading...
          </div>
        </div>
      </div>

      <div
        class="dashboard-card bg-gradient-to-br from-indigo-50 to-blue-100 rounded-xl p-4 shadow-lg border border-indigo-200/50 backdrop-blur-sm">
        <div class="flex items-center justify-between mb-3">
          <h2 class="text-lg font-bold flex items-center gap-2 text-indigo-800">
            <i class="fa-solid fa-bag-shopping text-indigo-600"></i> Multi - Transaksi
          </h2>
          <div class="flex items-center gap-2">
            <div class="w-1.5 h-1.5 bg-indigo-400 rounded-full animate-pulse"></div>
            <button id="view-all-activity" class="text-indigo-600 hover:text-indigo-800 transition hover:scale-110"
              title="Lihat Semua Aktifitas Transaksi">
              <i class="fa-solid fa-external-link-alt text-xs"></i>
            </button>
          </div>
        </div>

        <p class="text-xs text-gray-600 italic -mt-2 mb-2">Top 3 customer (frekuensi terbanyak) kemarin.</p>

        <div class="grid grid-cols-1 gap-2 mb-3 text-xs" id="top-activity-container">
          <div class="flex items-center justify-center text-gray-500 bg-white/60 rounded-xl p-2">
            <i class="fa-solid fa-spinner fa-spin mr-1"></i> Loading...
          </div>
        </div>
      </div>
    </section>

    <section class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-4 mb-8">
      <div
        class="dashboard-card bg-gradient-to-br from-yellow-50 via-white to-amber-100 rounded-2xl p-2 shadow border border-yellow-200/70 backdrop-blur-sm animate-fade-in-up">
        <div class="flex items-center justify-between mb-3">
          <h2 class="text-sm font-bold flex items-center gap-2 text-yellow-800">
            <span class="w-6 h-6 flex items-center justify-center rounded-full bg-yellow-100 shadow">
              <i class="fa-solid fa-star text-yellow-600 text-base"></i>
            </span>
            Review Customer
          </h2>
          <div class="w-2 h-2 bg-yellow-400 rounded-full animate-pulse"></div>
        </div>

        <p class="text-xs text-gray-600 italic -mt-2 mb-2 px-1">Data review (rating & pending) keseluruhan.</p>

        <div class="space-y-4">
          <div id="featured-review-container">
          </div>

          <div
            class="cursor-pointer bg-gradient-to-br from-white/90 to-yellow-50/80 rounded-xl p-1.5 shadow border border-yellow-100/60 hover:scale-105 hover:shadow-md transition-all duration-300 flex flex-col animate-fade-in-up"
            onclick="window.location.href='/src/fitur/laporan/in_review_cust'">
            <div class="flex items-center justify-between mb-1">
              <div class="flex items-center gap-1" title="Rata-rata Rating">
                <i class="fa-solid fa-star text-yellow-500 text-xs"></i>
                <span class="font-semibold text-xs text-gray-800 whitespace-nowrap">Rating</span>
              </div>
              <div class="flex items-center gap-1" title="Total Reviews">
                <i class="fa-solid fa-comments text-amber-600 text-xs"></i>
                <span class="font-semibold text-xs text-gray-800 whitespace-nowrap">Total Review</span>
              </div>
            </div>
            <div class="flex items-center justify-between text-xs text-gray-600">
              <span id="avg-rating" class="whitespace-nowrap font-bold text-yellow-700">-</span>
              <span id="total-reviews" class="whitespace-nowrap font-bold text-amber-700">-</span>
            </div>
          </div>
          <div
            class="cursor-pointer bg-gradient-to-br from-white/90 to-orange-50/80 rounded-xl p-1.5 shadow border border-orange-100/60 hover:scale-105 hover:shadow-md transition-all duration-300 flex flex-col animate-fade-in-up"
            onclick="window.location.href='/src/fitur/laporan/in_review_cust'">
            <div class="flex items-center justify-between mb-1">
              <div class="flex items-center gap-2">
                <div class="w-2 h-2 bg-orange-500 rounded-full animate-pulse"></div>
                <span class="font-semibold text-xs text-gray-800 whitespace-nowrap" title="Review Pending">Review
                  Pending</span>
              </div>
            </div>
            <div class="flex items-center justify-between text-xs text-gray-600">
              <div class="flex items-center gap-1 max-w-[100px]" title="Pending">
                <i class="fa-solid fa-clock text-orange-600 text-xs"></i>
                <span id="pending-count" class="whitespace-nowrap font-bold text-orange-700">-</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <?php
    $canViewSalesGraph = $permissionChecker->hasPermission($user_id, 'dashboard_sales_graph', 'can_view');

    if ($canViewSalesGraph):
      ?>

      <section      
        class="glass-container animate-fade-in-up mt-8 backdrop-blur-sm bg-white/90 rounded-2xl shadow-xl border border-white/20 p-8">
        <div        
          class="flex flex-col lg:flex-row items-center justify-between mb-8 p-6 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl border border-blue-200/50">
          <div class="flex items-center gap-3 mb-4 lg:mb-0">
            <div            
              class="w-12 h-12 bg-gradient-to-r from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center shadow-lg">
              <i class="fa fa-chart-line text-white text-xl"></i>
            </div>
            <div>
              <h2 class="text-2xl font-bold bg-gradient-to-r from-blue-600 to-indigo-700 bg-clip-text text-transparent">
                Grafik Penjualan
              </h2>
              <p class="text-blue-600 text-sm font-medium">Analisis Performa Penjualan</p>
            </div>
          </div>
          <div class="flex items-center gap-2 text-sm text-blue-600">
            <div class="w-2 h-2 bg-blue-500 rounded-full animate-pulse"></div>
            <span class="font-medium">Real-time Analytics</span>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
          <div class="bg-white/80 rounded-xl p-4 border border-blue-100/50 shadow-sm">
            <label for="period1" class="block text-blue-700 font-semibold mb-2 flex items-center gap-2">
              <i class="fa-solid fa-calendar-alt text-blue-600"></i>
              Periode Grafik 1
            </label>
            <select id="period1"            
              class="w-full px-4 py-3 border border-blue-200 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-300 focus:border-blue-400 transition-all duration-200 bg-white/90 font-medium text-blue-700">
              <option value="day">📅 Per Hari</option>
              <option value="month">📊 Per Bulan</option>
              <option value="year">📈 Per Tahun</option>
            </select>
          </div>

          <div class="bg-white/80 rounded-xl p-4 border border-blue-100/50 shadow-sm">
            <label for="period2" class="block text-blue-700 font-semibold mb-2 flex items-center gap-2">
              <i class="fa-solid fa-chart-bar text-blue-600"></i>
              Periode Grafik 2
            </label>
            <select id="period2"            
              class="w-full px-4 py-3 border border-blue-200 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-300 focus:border-blue-400 transition-all duration-200 bg-white/90 font-medium text-blue-700">
              <option value="per_jam">⏰ Per Jam (Kemarin)</option>
              <option value="7_hari">📅 7 Hari Terakhir</option>
              <option value="30_hari">📊 30 Hari Terakhir</option>
              <option value="12_bulan">📈 1 Tahun Terakhir</option>
            </select>
          </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
          <div          
            class="bg-gradient-to-br from-white/95 to-blue-50/50 rounded-2xl shadow-lg p-6 h-[420px] relative border border-blue-200/50 hover:shadow-xl transition-all duration-300">
            <div class="flex items-center justify-between mb-4">
              <h3 class="text-lg font-semibold text-blue-800 flex items-center gap-2">
                <i class="fa-solid fa-chart-line text-blue-600"></i>
                Tren Penjualan
              </h3>
              <div class="w-3 h-3 bg-blue-500 rounded-full animate-pulse"></div>
            </div>
            <div id="chart1-skeleton"            
              class="absolute inset-0 flex justify-center items-center skeleton-loader bg-white/80 rounded-xl">
              <div              
                class="w-16 h-16 border-4 border-t-transparent border-blue-500 border-solid rounded-full animate-spin shadow-lg">
              </div>
            </div>
            <div id="chart1" class="w-full h-[350px]"></div>
          </div>

          <div          
            class="bg-gradient-to-br from-white/95 to-indigo-50/50 rounded-2xl shadow-lg p-6 h-[420px] relative border border-indigo-200/50 hover:shadow-xl transition-all duration-300">
            <div class="flex items-center justify-between mb-4">
              <h3 class="text-lg font-semibold text-indigo-800 flex items-center gap-2">
                <i class="fa-solid fa-chart-area text-indigo-600"></i>
                Tren Omzet
              </h3>
              <div class="w-3 h-3 bg-indigo-500 rounded-full animate-pulse"></div>
            </div>
            <div id="chart2-skeleton"            
              class="absolute inset-0 flex justify-center items-center skeleton-loader bg-white/80 rounded-xl">
              <div            
                class="w-16 h-16 border-4 border-t-transparent border-indigo-500 border-solid rounded-full animate-spin shadow-lg">
              </div>
            </div>
            <div id="chart2" class="w-full h-[350px]"></div>
          </div>
        </div>
      </section>

      <?php
      // --- TAMBAHKAN KODE INI ---
    endif; // Akhir dari cek permission $canViewSalesGraph
// -------------------------
    ?>




    <!-- <section
      class="glass-container animate-fade-in-up mt-8 backdrop-blur-sm bg-white/90 rounded-2xl shadow-xl border border-white/20 p-8">
      <div
        class="flex flex-col lg:flex-row items-center justify-between mb-8 p-6 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl border border-blue-200/50">
        <div class="flex items-center gap-3 mb-4 lg:mb-0">
          <div
            class="w-12 h-12 bg-gradient-to-r from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center shadow-lg">
            <i class="fa fa-chart-line text-white text-xl"></i>
          </div>
          <div>
            <h2 class="text-2xl font-bold bg-gradient-to-r from-blue-600 to-indigo-700 bg-clip-text text-transparent">
              Grafik Penjualan
            </h2>
            <p class="text-blue-600 text-sm font-medium">Analisis Performa Penjualan</p>
          </div>
        </div>
        <div class="flex items-center gap-2 text-sm text-blue-600">
          <div class="w-2 h-2 bg-blue-500 rounded-full animate-pulse"></div>
          <span class="font-medium">Real-time Analytics</span>
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <div class="bg-white/80 rounded-xl p-4 border border-blue-100/50 shadow-sm">
          <label for="period1" class="block text-blue-700 font-semibold mb-2 flex items-center gap-2">
            <i class="fa-solid fa-calendar-alt text-blue-600"></i>
            Periode Grafik 1
          </label>
          <select id="period1"
            class="w-full px-4 py-3 border border-blue-200 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-300 focus:border-blue-400 transition-all duration-200 bg-white/90 font-medium text-blue-700">
            <option value="day">📅 Per Hari</option>
            <option value="month">📊 Per Bulan</option>
            <option value="year">📈 Per Tahun</option>
          </select>
        </div>

        <div class="bg-white/80 rounded-xl p-4 border border-blue-100/50 shadow-sm">
          <label for="period2" class="block text-blue-700 font-semibold mb-2 flex items-center gap-2">
            <i class="fa-solid fa-chart-bar text-blue-600"></i>
            Periode Grafik 2
          </label>
          <select id="period2"
            class="w-full px-4 py-3 border border-blue-200 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-300 focus:border-blue-400 transition-all duration-200 bg-white/90 font-medium text-blue-700">
            <option value="per_jam">⏰ Per Jam (Kemarin)</option>
            <option value="7_hari">📅 7 Hari Terakhir</option>
            <option value="30_hari">📊 30 Hari Terakhir</option>
            <option value="12_bulan">📈 1 Tahun Terakhir</option>
          </select>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <div
          class="bg-gradient-to-br from-white/95 to-blue-50/50 rounded-2xl shadow-lg p-6 h-[420px] relative border border-blue-200/50 hover:shadow-xl transition-all duration-300">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-blue-800 flex items-center gap-2">
              <i class="fa-solid fa-chart-line text-blue-600"></i>
              Tren Penjualan
            </h3>
            <div class="w-3 h-3 bg-blue-500 rounded-full animate-pulse"></div>
          </div>
          <div id="chart1-skeleton"
            class="absolute inset-0 flex justify-center items-center skeleton-loader bg-white/80 rounded-xl">
            <div
              class="w-16 h-16 border-4 border-t-transparent border-blue-500 border-solid rounded-full animate-spin shadow-lg">
            </div>
          </div>
          <div id="chart1" class="w-full h-[350px]"></div>
        </div>

        <div
          class="bg-gradient-to-br from-white/95 to-indigo-50/50 rounded-2xl shadow-lg p-6 h-[420px] relative border border-indigo-200/50 hover:shadow-xl transition-all duration-300">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-indigo-800 flex items-center gap-2">
              <i class="fa-solid fa-chart-area text-indigo-600"></i>
              Tren Omzet
            </h3>
            <div class="w-3 h-3 bg-indigo-500 rounded-full animate-pulse"></div>
          </div>
          <div id="chart2-skeleton"
            class="absolute inset-0 flex justify-center items-center skeleton-loader bg-white/80 rounded-xl">
            <div
              class="w-16 h-16 border-4 border-t-transparent border-indigo-500 border-solid rounded-full animate-spin shadow-lg">
            </div>
          </div>
          <div id="chart2" class="w-full h-[350px]"></div>
        </div>
      </div>
    </section> -->
  </main>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/echarts@5.3.0/dist/echarts.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
  <script src="src/js/dashboard/display.js" type="module"></script>
  <script src="src/js/dashboard/dashboardChart.js"></script>
  <script src="src/js/middleware_auth.js"></script>
  <script src="src/js/logout.js"></script>

</body>

</html>