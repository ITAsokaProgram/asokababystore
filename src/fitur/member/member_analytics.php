<?php
// require_once __DIR__ . '/../../component/menu_handler.php';

// $menuHandler = new MenuHandler('member_analytics');
// if (!$menuHandler->initialize()) {
// exit();
// }

// $user_id = $menuHandler->getUserId();
// $logger = $menuHandler->getLogger();
// $token = $menuHandler->getToken();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics Member - Asoka Baby Store</title>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/all.min.css">

    <!-- CSS Files -->
    <link rel="stylesheet" href="../../style/header.css">
    <link rel="stylesheet" href="../../style/sidebar.css">
    <link rel="stylesheet" href="../../style/animation-fade-in.css">
    <link rel="stylesheet" href="../../../css/cabang_selective.css">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="../../../public/images/logo1.png">
    <link rel="stylesheet" href="../../style/default-font.css">
    <link rel="stylesheet" href="../../output2.css">

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- GSAP Animation -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>

    <!-- Tippy.js -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tippy.js@6/dist/tippy.css" />
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/tippy.js@6/dist/tippy-bundle.umd.min.js"></script>

    <style>
        .analytics-card {
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }

        .analytics-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 15px 35px rgba(59, 130, 246, 0.15);
        }

        .chart-container {
            position: relative;
            height: 300px;
            width: 100%;
        }

        .large-chart-container {
            position: relative;
            height: 400px;
            width: 100%;
        }

        .metric-icon {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .gradient-bg-1 { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .gradient-bg-2 { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
        .gradient-bg-3 { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
        .gradient-bg-4 { background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); }
        .gradient-bg-5 { background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); }
        .gradient-bg-6 { background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%); }

        .location-progress {
            height: 8px;
            border-radius: 10px;
            overflow: hidden;
        }

        .fade-in-up {
            opacity: 0;
            transform: translateY(30px);
            animation: fadeInUp 0.6s ease forwards;
        }

        @keyframes fadeInUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .pulse-dot {
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.1); opacity: 0.7; }
            100% { transform: scale(1); opacity: 1; }
        }
    </style>
</head>

<body class="bg-gradient-to-br from-slate-50 to-blue-50 text-gray-900">
    <?php include '../../component/navigation_report.php'; ?>
    <?php include '../../component/sidebar_report.php'; ?>

    <main id="main-content" class="flex-1 p-4 lg:p-6 transition-all duration-300 ml-64">
        <div class="max-w-7xl mx-auto space-y-8">

            <!-- Header Section -->
            <div class="bg-white/80 backdrop-blur-md rounded-2xl shadow-xl border border-blue-100 p-6 fade-in-up">
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                    <div class="flex items-center space-x-4">
                        <div class="bg-gradient-to-r from-indigo-500 to-purple-600 p-3 rounded-xl shadow-lg">
                            <i class="fas fa-chart-line text-white text-2xl"></i>
                        </div>
                        <div>
                            <h1 class="text-3xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">
                                Analytics Member
                            </h1>
                            <p class="text-gray-600 mt-1">Dashboard analytics dan insights member terkini</p>
                        </div>
                    </div>

                    <!-- Date Range Selector -->
                    <div class="flex flex-col sm:flex-row gap-3">
                        <select class="px-4 py-2 rounded-lg border border-blue-200 text-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-300 transition-all duration-200 text-sm bg-white shadow-sm">
                            <option value="7">7 Hari Terakhir</option>
                            <option value="30" selected>30 Hari Terakhir</option>
                            <option value="90">3 Bulan Terakhir</option>
                            <option value="365">1 Tahun Terakhir</option>
                            <option value="custom">Custom Range</option>
                        </select>
                        <button class="px-6 py-2 bg-gradient-to-r from-indigo-500 to-purple-600 text-white rounded-lg hover:from-indigo-600 hover:to-purple-700 transition-all duration-200 flex items-center gap-2 shadow-lg">
                            <i class="fas fa-download text-sm"></i>
                            <span>Export Report</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Key Metrics Overview -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="bg-white/80 backdrop-blur-md rounded-2xl shadow-xl border border-emerald-100 p-6 analytics-card fade-in-up" style="animation-delay: 0.1s">
                    <div class="flex items-center justify-between mb-4">
                        <div class="gradient-bg-4 p-3 rounded-xl">
                            <i class="fas fa-users text-white text-xl"></i>
                        </div>
                        <span class="bg-emerald-100 text-emerald-700 px-2 py-1 rounded-full text-xs font-semibold">
                            +12.5%
                        </span>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-800 mb-1">1,247</h3>
                    <p class="text-sm text-gray-600">Total Member Aktif</p>
                    <div class="mt-3 flex items-center text-xs text-emerald-600">
                        <i class="fas fa-arrow-up mr-1"></i>
                        <span>156 member baru bulan ini</span>
                    </div>
                </div>

                <div class="bg-white/80 backdrop-blur-md rounded-2xl shadow-xl border border-blue-100 p-6 analytics-card fade-in-up" style="animation-delay: 0.2s">
                    <div class="flex items-center justify-between mb-4">
                        <div class="gradient-bg-3 p-3 rounded-xl">
                            <i class="fas fa-chart-line text-white text-xl"></i>
                        </div>
                        <span class="bg-blue-100 text-blue-700 px-2 py-1 rounded-full text-xs font-semibold">
                            +8.3%
                        </span>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-800 mb-1">78.4%</h3>
                    <p class="text-sm text-gray-600">Member Engagement Rate</p>
                    <div class="mt-3 flex items-center text-xs text-blue-600">
                        <i class="fas fa-arrow-up mr-1"></i>
                        <span>Naik dari bulan sebelumnya</span>
                    </div>
                </div>

                <div class="bg-white/80 backdrop-blur-md rounded-2xl shadow-xl border border-purple-100 p-6 analytics-card fade-in-up" style="animation-delay: 0.3s">
                    <div class="flex items-center justify-between mb-4">
                        <div class="gradient-bg-2 p-3 rounded-xl">
                            <i class="fas fa-shopping-cart text-white text-xl"></i>
                        </div>
                        <span class="bg-purple-100 text-purple-700 px-2 py-1 rounded-full text-xs font-semibold">
                            +15.2%
                        </span>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-800 mb-1">Rp 2.8M</h3>
                    <p class="text-sm text-gray-600">Revenue dari Member</p>
                    <div class="mt-3 flex items-center text-xs text-purple-600">
                        <i class="fas fa-arrow-up mr-1"></i>
                        <span>Rata-rata: Rp 2,246/member</span>
                    </div>
                </div>

                <div class="bg-white/80 backdrop-blur-md rounded-2xl shadow-xl border border-orange-100 p-6 analytics-card fade-in-up" style="animation-delay: 0.4s">
                    <div class="flex items-center justify-between mb-4">
                        <div class="gradient-bg-5 p-3 rounded-xl">
                            <i class="fas fa-clock text-white text-xl"></i>
                        </div>
                        <span class="bg-orange-100 text-orange-700 px-2 py-1 rounded-full text-xs font-semibold">
                            -5.1%
                        </span>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-800 mb-1">32 hari</h3>
                    <p class="text-sm text-gray-600">Avg. Member Lifetime</p>
                    <div class="mt-3 flex items-center text-xs text-orange-600">
                        <i class="fas fa-arrow-down mr-1"></i>
                        <span>Perlu improvement retention</span>
                    </div>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                
                <!-- Member Growth Trend -->
                <div class="bg-white/80 backdrop-blur-md rounded-2xl shadow-xl border border-blue-100 p-6 fade-in-up" style="animation-delay: 0.5s">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h3 class="text-xl font-bold text-gray-800">Pertumbuhan Member</h3>
                            <p class="text-sm text-gray-600 mt-1">Trend pendaftaran member per bulan</p>
                        </div>
                        <div class="flex gap-2">
                            <button class="px-3 py-1 text-xs bg-blue-100 text-blue-700 rounded-lg font-medium">Bulan</button>
                            <button class="px-3 py-1 text-xs bg-gray-100 text-gray-600 rounded-lg">Minggu</button>
                        </div>
                    </div>
                    <div class="chart-container">
                        <canvas id="memberGrowthChart"></canvas>
                    </div>
                </div>

                <!-- Member Status Distribution -->
                <div class="bg-white/80 backdrop-blur-md rounded-2xl shadow-xl border border-green-100 p-6 fade-in-up" style="animation-delay: 0.6s">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h3 class="text-xl font-bold text-gray-800">Status Member</h3>
                            <p class="text-sm text-gray-600 mt-1">Distribusi status keanggotaan</p>
                        </div>
                        <div class="pulse-dot w-3 h-3 bg-green-500 rounded-full"></div>
                    </div>
                    <div class="chart-container">
                        <canvas id="memberStatusChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Geographic Distribution -->
            <div class="bg-white/80 backdrop-blur-md rounded-2xl shadow-xl border border-blue-100 p-6 fade-in-up" style="animation-delay: 0.7s">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h3 class="text-xl font-bold text-gray-800">
                            <i class="fas fa-map-marker-alt text-blue-500 mr-2"></i>
                            Distribusi Geografis Member
                        </h3>
                        <p class="text-sm text-gray-600 mt-1">Sebaran member berdasarkan lokasi dan tingkat aktivitas</p>
                    </div>
                    <button onclick="showMapModal()" class="px-4 py-2 bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200 transition-all text-sm font-medium">
                        <i class="fas fa-map mr-2"></i>View Map
                    </button>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Location List -->
                    <div class="lg:col-span-2">
                        <div class="space-y-4">
                            <!-- Jakarta -->
                            <div class="p-4 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl border border-blue-100">
                                <div class="flex items-center justify-between mb-3">
                                    <div class="flex items-center gap-3">
                                        <div class="w-4 h-4 bg-blue-500 rounded-full pulse-dot"></div>
                                        <div>
                                            <h4 class="font-semibold text-gray-800">Jakarta Pusat</h4>
                                            <p class="text-xs text-gray-500">DKI Jakarta</p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-lg font-bold text-blue-700">342</p>
                                        <p class="text-xs text-gray-500">28.5% dari total</p>
                                    </div>
                                </div>
                                <div class="location-progress bg-blue-200">
                                    <div class="h-full bg-blue-500" style="width: 28.5%"></div>
                                </div>
                                <div class="flex justify-between mt-2 text-xs text-gray-600">
                                    <span>Aktif: 298 (87%)</span>
                                    <span>Revenue: Rp 856K</span>
                                </div>
                            </div>

                            <!-- Bekasi -->
                            <div class="p-4 bg-gradient-to-r from-green-50 to-emerald-50 rounded-xl border border-green-100">
                                <div class="flex items-center justify-between mb-3">
                                    <div class="flex items-center gap-3">
                                        <div class="w-4 h-4 bg-green-500 rounded-full"></div>
                                        <div>
                                            <h4 class="font-semibold text-gray-800">Bekasi</h4>
                                            <p class="text-xs text-gray-500">Jawa Barat</p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-lg font-bold text-green-700">278</p>
                                        <p class="text-xs text-gray-500">23.2% dari total</p>
                                    </div>
                                </div>
                                <div class="location-progress bg-green-200">
                                    <div class="h-full bg-green-500" style="width: 23.2%"></div>
                                </div>
                                <div class="flex justify-between mt-2 text-xs text-gray-600">
                                    <span>Aktif: 234 (84%)</span>
                                    <span>Revenue: Rp 623K</span>
                                </div>
                            </div>

                            <!-- Tangerang -->
                            <div class="p-4 bg-gradient-to-r from-purple-50 to-pink-50 rounded-xl border border-purple-100">
                                <div class="flex items-center justify-between mb-3">
                                    <div class="flex items-center gap-3">
                                        <div class="w-4 h-4 bg-purple-500 rounded-full"></div>
                                        <div>
                                            <h4 class="font-semibold text-gray-800">Tangerang</h4>
                                            <p class="text-xs text-gray-500">Banten</p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-lg font-bold text-purple-700">195</p>
                                        <p class="text-xs text-gray-500">16.3% dari total</p>
                                    </div>
                                </div>
                                <div class="location-progress bg-purple-200">
                                    <div class="h-full bg-purple-500" style="width: 16.3%"></div>
                                </div>
                                <div class="flex justify-between mt-2 text-xs text-gray-600">
                                    <span>Aktif: 156 (80%)</span>
                                    <span>Revenue: Rp 456K</span>
                                </div>
                            </div>

                            <!-- Other locations -->
                            <div class="p-4 bg-gradient-to-r from-orange-50 to-yellow-50 rounded-xl border border-orange-100">
                                <div class="flex items-center justify-between mb-3">
                                    <div class="flex items-center gap-3">
                                        <div class="w-4 h-4 bg-orange-500 rounded-full"></div>
                                        <div>
                                            <h4 class="font-semibold text-gray-800">Lokasi Lainnya</h4>
                                            <p class="text-xs text-gray-500">Depok, Bogor, dll</p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-lg font-bold text-orange-700">432</p>
                                        <p class="text-xs text-gray-500">32.0% dari total</p>
                                    </div>
                                </div>
                                <div class="location-progress bg-orange-200">
                                    <div class="h-full bg-orange-500" style="width: 32%"></div>
                                </div>
                                <div class="flex justify-between mt-2 text-xs text-gray-600">
                                    <span>Aktif: 321 (74%)</span>
                                    <span>Revenue: Rp 785K</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Geographic Chart -->
                    <div class="lg:col-span-1">
                        <div class="bg-gray-50 rounded-xl p-4 h-80">
                            <canvas id="geographicChart"></canvas>
                        </div>
                        <div class="mt-4 space-y-2">
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-600">Total Lokasi Aktif</span>
                                <span class="font-semibold">24 kota</span>
                            </div>
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-600">Konsentrasi Tertinggi</span>
                                <span class="font-semibold">Jakarta (28.5%)</span>
                            </div>
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-600">Area Potensi</span>
                                <span class="font-semibold text-green-600">Bogor, Cibubur</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Member Behavior Analytics -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                
                <!-- Shopping Patterns -->
                <div class="bg-white/80 backdrop-blur-md rounded-2xl shadow-xl border border-purple-100 p-6 fade-in-up" style="animation-delay: 0.8s">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h3 class="text-xl font-bold text-gray-800">
                                <i class="fas fa-shopping-bag text-purple-500 mr-2"></i>
                                Pola Belanja Member
                            </h3>
                            <p class="text-sm text-gray-600 mt-1">Analisis behavior dan preferensi member</p>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div class="flex items-center justify-between p-3 bg-purple-50 rounded-lg">
                            <div class="flex items-center gap-3">
                                <i class="fas fa-clock text-purple-600"></i>
                                <span class="text-sm font-medium text-gray-700">Waktu Belanja Favorit</span>
                            </div>
                            <span class="text-sm font-bold text-purple-700">19:00 - 21:00</span>
                        </div>
                        
                        <div class="flex items-center justify-between p-3 bg-blue-50 rounded-lg">
                            <div class="flex items-center gap-3">
                                <i class="fas fa-calendar text-blue-600"></i>
                                <span class="text-sm font-medium text-gray-700">Hari Tersibuk</span>
                            </div>
                            <span class="text-sm font-bold text-blue-700">Sabtu & Minggu</span>
                        </div>
                        
                        <div class="flex items-center justify-between p-3 bg-green-50 rounded-lg">
                            <div class="flex items-center gap-3">
                                <i class="fas fa-money-bill-wave text-green-600"></i>
                                <span class="text-sm font-medium text-gray-700">Avg. Transaksi</span>
                            </div>
                            <span class="text-sm font-bold text-green-700">Rp 245,000</span>
                        </div>
                        
                        <div class="flex items-center justify-between p-3 bg-orange-50 rounded-lg">
                            <div class="flex items-center gap-3">
                                <i class="fas fa-heart text-orange-600"></i>
                                <span class="text-sm font-medium text-gray-700">Kategori Favorit</span>
                            </div>
                            <span class="text-sm font-bold text-orange-700">Baby Gear (45%)</span>
                        </div>
                        
                        <div class="flex items-center justify-between p-3 bg-pink-50 rounded-lg">
                            <div class="flex items-center gap-3">
                                <i class="fas fa-sync-alt text-pink-600"></i>
                                <span class="text-sm font-medium text-gray-700">Frekuensi Kunjungan</span>
                            </div>
                            <span class="text-sm font-bold text-pink-700">2.3x per bulan</span>
                        </div>
                    </div>
                </div>

                <!-- Member Satisfaction -->
                <div class="bg-white/80 backdrop-blur-md rounded-2xl shadow-xl border border-green-100 p-6 fade-in-up" style="animation-delay: 0.9s">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h3 class="text-xl font-bold text-gray-800">
                                <i class="fas fa-star text-yellow-500 mr-2"></i>
                                Kepuasan Member
                            </h3>
                            <p class="text-sm text-gray-600 mt-1">Rating dan feedback dari member</p>
                        </div>
                    </div>

                    <div class="text-center mb-6">
                        <div class="text-4xl font-bold text-yellow-500 mb-2">4.7</div>
                        <div class="flex justify-center gap-1 mb-2">
                            <i class="fas fa-star text-yellow-400"></i>
                            <i class="fas fa-star text-yellow-400"></i>
                            <i class="fas fa-star text-yellow-400"></i>
                            <i class="fas fa-star text-yellow-400"></i>
                            <i class="fas fa-star text-gray-300"></i>
                        </div>
                        <p class="text-sm text-gray-600">dari 1,247 reviews</p>
                    </div>

                    <div class="space-y-3">
                        <!-- 5 Stars -->
                        <div class="flex items-center gap-3">
                            <span class="text-sm font-medium w-8">5★</span>
                            <div class="flex-1 h-2 bg-gray-200 rounded-full">
                                <div class="h-full bg-yellow-400 rounded-full" style="width: 68%"></div>
                            </div>
                            <span class="text-sm text-gray-600 w-12">68%</span>
                        </div>
                        
                        <!-- 4 Stars -->
                        <div class="flex items-center gap-3">
                            <span class="text-sm font-medium w-8">4★</span>
                            <div class="flex-1 h-2 bg-gray-200 rounded-full">
                                <div class="h-full bg-yellow-400 rounded-full" style="width: 22%"></div>
                            </div>
                            <span class="text-sm text-gray-600 w-12">22%</span>
                        </div>
                        
                        <!-- 3 Stars -->
                        <div class="flex items-center gap-3">
                            <span class="text-sm font-medium w-8">3★</span>
                            <div class="flex-1 h-2 bg-gray-200 rounded-full">
                                <div class="h-full bg-yellow-400 rounded-full" style="width: 7%"></div>
                            </div>
                            <span class="text-sm text-gray-600 w-12">7%</span>
                        </div>
                        
                        <!-- 2 Stars -->
                        <div class="flex items-center gap-3">
                            <span class="text-sm font-medium w-8">2★</span>
                            <div class="flex-1 h-2 bg-gray-200 rounded-full">
                                <div class="h-full bg-yellow-400 rounded-full" style="width: 2%"></div>
                            </div>
                            <span class="text-sm text-gray-600 w-12">2%</span>
                        </div>
                        
                        <!-- 1 Star -->
                        <div class="flex items-center gap-3">
                            <span class="text-sm font-medium w-8">1★</span>
                            <div class="flex-1 h-2 bg-gray-200 rounded-full">
                                <div class="h-full bg-yellow-400 rounded-full" style="width: 1%"></div>
                            </div>
                            <span class="text-sm text-gray-600 w-12">1%</span>
                        </div>
                    </div>

                    <div class="mt-6 p-4 bg-green-50 rounded-xl">
                        <div class="flex items-center gap-2 text-green-700 text-sm font-semibold mb-2">
                            <i class="fas fa-thumbs-up"></i>
                            Top Feedback Categories
                        </div>
                        <div class="space-y-1 text-sm text-gray-600">
                            <p>• Kualitas produk sangat baik (78%)</p>
                            <p>• Pelayanan ramah dan cepat (65%)</p>
                            <p>• Harga kompetitif (52%)</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recommendations & Actions -->
            <div class="bg-white/80 backdrop-blur-md rounded-2xl shadow-xl border border-indigo-100 p-6 fade-in-up" style="animation-delay: 1s">
                <div class="flex items-center gap-3 mb-6">
                    <div class="bg-gradient-to-r from-indigo-500 to-purple-600 p-3 rounded-xl">
                        <i class="fas fa-lightbulb text-white text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-gray-800">AI-Powered Insights & Recommendations</h3>
                        <p class="text-sm text-gray-600 mt-1">Rekomendasi berdasarkan analisis data member</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Growth Opportunities -->
                    <div class="bg-gradient-to-r from-green-50 to-emerald-50 rounded-xl p-5 border border-green-100">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="bg-green-500 p-2 rounded-lg">
                                <i class="fas fa-chart-line text-white"></i>
                            </div>
                            <h4 class="font-semibold text-green-800">Peluang Pertumbuhan</h4>
                        </div>
                        <ul class="space-y-2 text-sm text-gray-700">
                            <li class="flex items-start gap-2">
                                <i class="fas fa-arrow-right text-green-500 mt-1 text-xs"></i>
                                Target member baru di area Bogor & Cibubur
                            </li>
                            <li class="flex items-start gap-2">
                                <i class="fas fa-arrow-right text-green-500 mt-1 text-xs"></i>
                                Fokus campaign pada weekend (19-21)
                            </li>
                            <li class="flex items-start gap-2">
                                <i class="fas fa-arrow-right text-green-500 mt-1 text-xs"></i>
                                Tingkatkan stok Baby Gear kategori
                            </li>
                        </ul>
                    </div>

                    <!-- Member Retention -->
                    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl p-5 border border-blue-100">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="bg-blue-500 p-2 rounded-lg">
                                <i class="fas fa-heart text-white"></i>
                            </div>
                            <h4 class="font-semibold text-blue-800">Member Retention</h4>
                        </div>
                        <ul class="space-y-2 text-sm text-gray-700">
                            <li class="flex items-start gap-2">
                                <i class="fas fa-arrow-right text-blue-500 mt-1 text-xs"></i>
                                89 member berpotensi churn minggu ini
                            </li>
                            <li class="flex items-start gap-2">
                                <i class="fas fa-arrow-right text-blue-500 mt-1 text-xs"></i>
                                Kirim reminder aktivitas ke 156 member
                            </li>
                            <li class="flex items-start gap-2">
                                <i class="fas fa-arrow-right text-blue-500 mt-1 text-xs"></i>
                                Program loyalitas untuk member lama
                            </li>
                        </ul>
                    </div>

                    <!-- Business Insights -->
                    <div class="bg-gradient-to-r from-purple-50 to-pink-50 rounded-xl p-5 border border-purple-100">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="bg-purple-500 p-2 rounded-lg">
                                <i class="fas fa-brain text-white"></i>
                            </div>
                            <h4 class="font-semibold text-purple-800">Business Insights</h4>
                        </div>
                        <ul class="space-y-2 text-sm text-gray-700">
                            <li class="flex items-start gap-2">
                                <i class="fas fa-arrow-right text-purple-500 mt-1 text-xs"></i>
                                Revenue per member meningkat 15.2%
                            </li>
                            <li class="flex items-start gap-2">
                                <i class="fas fa-arrow-right text-purple-500 mt-1 text-xs"></i>
                                Member aktif berkontribusi 85% revenue
                            </li>
                            <li class="flex items-start gap-2">
                                <i class="fas fa-arrow-right text-purple-500 mt-1 text-xs"></i>
                                Potensi buka cabang di Bogor Tengah
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

        </div>
    </main>

    <!-- Modal View Map untuk Store Location -->
    <div id="mapModal" class="fixed inset-0 bg-black/50 flex justify-center items-center z-50 hidden backdrop-blur-sm transition-all duration-300">
        <div class="bg-white/95 backdrop-blur-md w-full max-w-7xl h-[90vh] rounded-2xl shadow-2xl border border-blue-100 relative overflow-hidden">
            <!-- Modal Header -->
            <div class="bg-gradient-to-r from-blue-500 to-indigo-600 p-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="bg-white/20 backdrop-blur-sm p-2 rounded-lg">
                            <i class="fas fa-map-marked-alt text-white text-xl"></i>
                        </div>
                        <div>
                            <h2 class="text-2xl font-bold text-white">Geographic Map & Store Location Analysis</h2>
                            <p class="text-blue-100 text-sm">Analisis sebaran member dan rekomendasi lokasi toko baru</p>
                        </div>
                    </div>
                    <button onclick="closeMapModal()" class="text-white/80 hover:text-white text-2xl bg-white/10 rounded-full p-2 hover:bg-white/20 transition-all duration-200">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>

            <!-- Modal Content -->
            <div class="flex h-full">
                <!-- Map Container -->
                <div class="flex-1 relative bg-gray-100">
                    <!-- Map Placeholder -->
                    <div id="mapContainer" class="w-full h-full bg-gradient-to-br from-blue-50 to-indigo-100 relative overflow-hidden">
                        <!-- Simulasi Jakarta Map -->
                        <div class="absolute inset-0 flex items-center justify-center">
                            <div class="relative w-full h-full max-w-4xl max-h-4xl">
                                <!-- Background Map Style -->
                                <svg viewBox="0 0 800 600" class="w-full h-full opacity-20">
                                    <defs>
                                        <pattern id="grid" width="40" height="40" patternUnits="userSpaceOnUse">
                                            <path d="M 40 0 L 0 0 0 40" fill="none" stroke="#cbd5e1" stroke-width="1"/>
                                        </pattern>
                                    </defs>
                                    <rect width="100%" height="100%" fill="url(#grid)" />
                                </svg>

                                <!-- Jakarta Central Area -->
                                <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2">
                                    <!-- Jakarta Pusat - Highest concentration -->
                                    <div class="absolute -top-8 -left-8 group cursor-pointer" onclick="selectLocation('jakarta-pusat')">
                                        <div class="relative">
                                            <div class="w-16 h-16 bg-blue-500 rounded-full animate-pulse opacity-30"></div>
                                            <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center shadow-lg">
                                                <i class="fas fa-store text-white text-xs"></i>
                                            </div>
                                            <div class="absolute -bottom-8 left-1/2 transform -translate-x-1/2 bg-white px-2 py-1 rounded shadow-lg text-xs font-semibold text-blue-600 opacity-0 group-hover:opacity-100 transition-opacity">
                                                Jakarta Pusat<br><span class="text-gray-500">342 member</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Bekasi -->
                                    <div class="absolute -top-4 left-12 group cursor-pointer" onclick="selectLocation('bekasi')">
                                        <div class="relative">
                                            <div class="w-12 h-12 bg-green-500 rounded-full animate-pulse opacity-30"></div>
                                            <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-6 h-6 bg-green-600 rounded-full flex items-center justify-center shadow-lg">
                                                <i class="fas fa-store text-white text-xs"></i>
                                            </div>
                                            <div class="absolute -bottom-8 left-1/2 transform -translate-x-1/2 bg-white px-2 py-1 rounded shadow-lg text-xs font-semibold text-green-600 opacity-0 group-hover:opacity-100 transition-opacity">
                                                Bekasi<br><span class="text-gray-500">278 member</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Tangerang -->
                                    <div class="absolute top-2 -left-16 group cursor-pointer" onclick="selectLocation('tangerang')">
                                        <div class="relative">
                                            <div class="w-10 h-10 bg-purple-500 rounded-full animate-pulse opacity-30"></div>
                                            <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-5 h-5 bg-purple-600 rounded-full flex items-center justify-center shadow-lg">
                                                <i class="fas fa-store text-white text-xs"></i>
                                            </div>
                                            <div class="absolute -bottom-8 left-1/2 transform -translate-x-1/2 bg-white px-2 py-1 rounded shadow-lg text-xs font-semibold text-purple-600 opacity-0 group-hover:opacity-100 transition-opacity">
                                                Tangerang<br><span class="text-gray-500">195 member</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Depok -->
                                    <div class="absolute top-8 left-4 group cursor-pointer" onclick="selectLocation('depok')">
                                        <div class="relative">
                                            <div class="w-8 h-8 bg-orange-500 rounded-full animate-pulse opacity-30"></div>
                                            <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-4 h-4 bg-orange-600 rounded-full flex items-center justify-center shadow-lg">
                                                <i class="fas fa-store text-white text-xs"></i>
                                            </div>
                                            <div class="absolute -bottom-8 left-1/2 transform -translate-x-1/2 bg-white px-2 py-1 rounded shadow-lg text-xs font-semibold text-orange-600 opacity-0 group-hover:opacity-100 transition-opacity">
                                                Depok<br><span class="text-gray-500">156 member</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Bogor - Recommendation Area -->
                                    <div class="absolute top-12 left-8 group cursor-pointer" onclick="selectLocation('bogor')">
                                        <div class="relative">
                                            <div class="w-12 h-12 border-4 border-dashed border-yellow-500 rounded-full animate-bounce opacity-60"></div>
                                            <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-6 h-6 bg-yellow-500 rounded-full flex items-center justify-center shadow-lg">
                                                <i class="fas fa-plus text-white text-xs"></i>
                                            </div>
                                            <div class="absolute -bottom-8 left-1/2 transform -translate-x-1/2 bg-yellow-50 border border-yellow-200 px-2 py-1 rounded shadow-lg text-xs font-semibold text-yellow-600 opacity-0 group-hover:opacity-100 transition-opacity">
                                                Bogor<br><span class="text-gray-500">Rekomendasi</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Cibubur - Recommendation Area -->
                                    <div class="absolute top-4 left-16 group cursor-pointer" onclick="selectLocation('cibubur')">
                                        <div class="relative">
                                            <div class="w-10 h-10 border-4 border-dashed border-yellow-500 rounded-full animate-bounce opacity-60"></div>
                                            <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-5 h-5 bg-yellow-500 rounded-full flex items-center justify-center shadow-lg">
                                                <i class="fas fa-plus text-white text-xs"></i>
                                            </div>
                                            <div class="absolute -bottom-8 left-1/2 transform -translate-x-1/2 bg-yellow-50 border border-yellow-200 px-2 py-1 rounded shadow-lg text-xs font-semibold text-yellow-600 opacity-0 group-hover:opacity-100 transition-opacity">
                                                Cibubur<br><span class="text-gray-500">Rekomendasi</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Map Legend -->
                                <div class="absolute bottom-4 left-4 bg-white/90 backdrop-blur-sm rounded-xl p-4 shadow-lg">
                                    <h4 class="font-semibold text-gray-800 mb-3 text-sm">Legend</h4>
                                    <div class="space-y-2 text-xs">
                                        <div class="flex items-center gap-2">
                                            <div class="w-4 h-4 bg-blue-600 rounded-full"></div>
                                            <span>Toko Existing</span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <div class="w-4 h-4 border-2 border-dashed border-yellow-500 rounded-full"></div>
                                            <span>Lokasi Rekomendasi</span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <div class="w-4 h-4 bg-red-500 rounded-full opacity-50"></div>
                                            <span>Area Kompetitor</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Zoom Controls -->
                                <div class="absolute top-4 right-4 bg-white/90 backdrop-blur-sm rounded-lg shadow-lg">
                                    <button class="p-2 hover:bg-gray-100 transition-colors" onclick="zoomIn()">
                                        <i class="fas fa-plus text-gray-600"></i>
                                    </button>
                                    <div class="border-t border-gray-200"></div>
                                    <button class="p-2 hover:bg-gray-100 transition-colors" onclick="zoomOut()">
                                        <i class="fas fa-minus text-gray-600"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar Info Panel -->
                <div class="w-96 bg-white border-l border-gray-200 overflow-y-auto">
                    <!-- Location Info Header -->
                    <div class="p-6 border-b border-gray-200">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-3 h-3 bg-blue-500 rounded-full" id="selectedLocationDot"></div>
                            <h3 class="text-lg font-bold text-gray-800" id="selectedLocationName">Jakarta Pusat</h3>
                        </div>
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <p class="text-gray-500">Total Member</p>
                                <p class="font-bold text-gray-800" id="selectedLocationMembers">342</p>
                            </div>
                            <div>
                                <p class="text-gray-500">Market Share</p>
                                <p class="font-bold text-gray-800" id="selectedLocationShare">28.5%</p>
                            </div>
                        </div>
                    </div>

                    <!-- Detailed Analytics -->
                    <div class="p-6 space-y-6">
                        <!-- Member Demographics -->
                        <div>
                            <h4 class="font-semibold text-gray-800 mb-3">Member Demographics</h4>
                            <div class="space-y-3">
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">Member Aktif</span>
                                    <span class="font-semibold" id="activeMemberCount">298 (87%)</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">Avg. Transaksi</span>
                                    <span class="font-semibold" id="avgTransaction">Rp 245K</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">Revenue Kontribusi</span>
                                    <span class="font-semibold" id="revenueContribution">Rp 856K</span>
                                </div>
                            </div>
                        </div>

                        <!-- Market Analysis -->
                        <div>
                            <h4 class="font-semibold text-gray-800 mb-3">Market Analysis</h4>
                            <div class="space-y-3">
                                <div class="bg-blue-50 p-3 rounded-lg">
                                    <div class="flex items-center gap-2 mb-2">
                                        <i class="fas fa-chart-bar text-blue-600"></i>
                                        <span class="text-sm font-medium text-blue-800">Potensi Pasar</span>
                                    </div>
                                    <p class="text-xs text-blue-700" id="marketPotential">Area dengan densitas tinggi dan growth rate positif</p>
                                </div>
                                <div class="bg-green-50 p-3 rounded-lg">
                                    <div class="flex items-center gap-2 mb-2">
                                        <i class="fas fa-users text-green-600"></i>
                                        <span class="text-sm font-medium text-green-800">Target Audience</span>
                                    </div>
                                    <p class="text-xs text-green-700" id="targetAudience">Keluarga muda dengan anak 0-5 tahun, income menengah ke atas</p>
                                </div>
                                <div class="bg-orange-50 p-3 rounded-lg">
                                    <div class="flex items-center gap-2 mb-2">
                                        <i class="fas fa-exclamation-triangle text-orange-600"></i>
                                        <span class="text-sm font-medium text-orange-800">Kompetitor</span>
                                    </div>
                                    <p class="text-xs text-orange-700" id="competitors">2 kompetitor dalam radius 3km</p>
                                </div>
                            </div>
                        </div>

                        <!-- Store Recommendation (untuk area rekomendasi) -->
                        <div id="storeRecommendation" class="hidden">
                            <h4 class="font-semibold text-gray-800 mb-3">
                                <i class="fas fa-lightbulb text-yellow-500 mr-2"></i>
                                Store Recommendation
                            </h4>
                            <div class="bg-gradient-to-r from-yellow-50 to-orange-50 p-4 rounded-xl border border-yellow-200">
                                <div class="space-y-3">
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm font-medium text-gray-700">Prioritas</span>
                                        <span class="bg-green-100 text-green-800 px-2 py-1 rounded-full text-xs font-bold" id="recommendationPriority">Tinggi</span>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm font-medium text-gray-700">Estimasi Member</span>
                                        <span class="font-bold text-gray-800" id="estimatedMembers">150-200</span>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm font-medium text-gray-700">ROI Prediction</span>
                                        <span class="font-bold text-green-600" id="roiPrediction">18 bulan</span>
                                    </div>
                                    <div class="pt-3 border-t border-yellow-200">
                                        <p class="text-xs text-gray-600 mb-3" id="recommendationReason">
                                            Area dengan density member tinggi namun belum ada outlet. 
                                            Potensi revenue Rp 45M/tahun.
                                        </p>
                                        <button class="w-full px-4 py-2 bg-gradient-to-r from-yellow-500 to-orange-600 text-white rounded-lg hover:from-yellow-600 hover:to-orange-700 transition-all duration-200 text-sm font-medium">
                                            <i class="fas fa-plus mr-2"></i>
                                            Plan New Store
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Quick Actions -->
                        <div class="space-y-3">
                            <button class="w-full px-4 py-2 bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200 transition-all text-sm font-medium" onclick="exportLocationData()">
                                <i class="fas fa-download mr-2"></i>
                                Export Location Data
                            </button>
                            <button class="w-full px-4 py-2 bg-green-100 text-green-700 rounded-lg hover:bg-green-200 transition-all text-sm font-medium" onclick="analyzeTraffic()">
                                <i class="fas fa-route mr-2"></i>
                                Analyze Traffic Pattern
                            </button>
                            <button class="w-full px-4 py-2 bg-purple-100 text-purple-700 rounded-lg hover:bg-purple-200 transition-all text-sm font-medium" onclick="generateReport()">
                                <i class="fas fa-file-alt mr-2"></i>
                                Generate Full Report
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="../../js/ui/navbar_toogle.js" type="module"></script>

    <script>
        // Initialize animations
        gsap.from(".fade-in-up", {
            duration: 0.8,
            y: 50,
            opacity: 0,
            stagger: 0.1,
            ease: "power2.out"
        });

        // Map Modal Functions
        function showMapModal() {
            document.getElementById('mapModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            
            // Initialize with Jakarta Pusat selected
            selectLocation('jakarta-pusat');
        }

        function closeMapModal() {
            document.getElementById('mapModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        function selectLocation(locationId) {
            const locations = {
                'jakarta-pusat': {
                    name: 'Jakarta Pusat',
                    members: '342',
                    share: '28.5%',
                    active: '298 (87%)',
                    avgTransaction: 'Rp 245K',
                    revenue: 'Rp 856K',
                    color: '#3b82f6',
                    marketPotential: 'Area dengan densitas tinggi dan growth rate positif (+15% YoY)',
                    targetAudience: 'Keluarga muda dengan anak 0-5 tahun, income menengah ke atas',
                    competitors: '2 kompetitor dalam radius 3km',
                    isRecommendation: false
                },
                'bekasi': {
                    name: 'Bekasi',
                    members: '278',
                    share: '23.2%',
                    active: '234 (84%)',
                    avgTransaction: 'Rp 238K',
                    revenue: 'Rp 623K',
                    color: '#22c55e',
                    marketPotential: 'Market saturasi sedang dengan potensi expansion',
                    targetAudience: 'Keluarga pekerja komuter, budget conscious',
                    competitors: '1 kompetitor utama dalam radius 5km',
                    isRecommendation: false
                },
                'tangerang': {
                    name: 'Tangerang',
                    members: '195',
                    share: '16.3%',
                    active: '156 (80%)',
                    avgTransaction: 'Rp 228K',
                    revenue: 'Rp 456K',
                    color: '#a855f7',
                    marketPotential: 'Area berkembang dengan growth potential tinggi',
                    targetAudience: 'Young professionals, early adopters',
                    competitors: '3 kompetitor tersebar di area luas',
                    isRecommendation: false
                },
                'depok': {
                    name: 'Depok',
                    members: '156',
                    share: '13.0%',
                    active: '124 (79%)',
                    avgTransaction: 'Rp 215K',
                    revenue: 'Rp 335K',
                    color: '#f97316',
                    marketPotential: 'Stable market dengan loyalitas tinggi',
                    targetAudience: 'Keluarga dengan background pendidikan tinggi',
                    competitors: '1 kompetitor kecil dalam radius 4km',
                    isRecommendation: false
                },
                'bogor': {
                    name: 'Bogor',
                    members: '98',
                    share: '8.2%',
                    active: '89 (91%)',
                    avgTransaction: 'Rp 267K',
                    revenue: 'Rp 276K',
                    color: '#eab308',
                    marketPotential: 'Untapped market dengan demand tinggi',
                    targetAudience: 'Keluarga suburban, nature lovers',
                    competitors: 'Belum ada kompetitor langsung dalam radius 10km',
                    isRecommendation: true,
                    priority: 'Tinggi',
                    estimatedMembers: '200-300',
                    roiPrediction: '12-15 bulan',
                    reason: 'Area dengan member density tinggi namun belum ada outlet. Potensi revenue Rp 65M/tahun dengan ROI tercepat.'
                },
                'cibubur': {
                    name: 'Cibubur',
                    members: '76',
                    share: '6.3%',
                    active: '68 (89%)',
                    avgTransaction: 'Rp 289K',
                    revenue: 'Rp 198K',
                    color: '#eab308',
                    marketPotential: 'Emerging market dengan purchasing power tinggi',
                    targetAudience: 'High-income families, premium segment',
                    competitors: 'Belum ada kompetitor baby store dalam radius 8km',
                    isRecommendation: true,
                    priority: 'Sedang',
                    estimatedMembers: '120-180',
                    roiPrediction: '18-24 bulan',
                    reason: 'Market premium dengan daya beli tinggi. Cocok untuk flagship store dengan estimasi revenue Rp 35M/tahun.'
                }
            };

            const location = locations[locationId];
            if (!location) return;

            // Update location info
            document.getElementById('selectedLocationName').textContent = location.name;
            document.getElementById('selectedLocationMembers').textContent = location.members;
            document.getElementById('selectedLocationShare').textContent = location.share;
            document.getElementById('selectedLocationDot').style.backgroundColor = location.color;
            
            // Update analytics
            document.getElementById('activeMemberCount').textContent = location.active;
            document.getElementById('avgTransaction').textContent = location.avgTransaction;
            document.getElementById('revenueContribution').textContent = location.revenue;
            document.getElementById('marketPotential').textContent = location.marketPotential;
            document.getElementById('targetAudience').textContent = location.targetAudience;
            document.getElementById('competitors').textContent = location.competitors;

            // Show/hide recommendation panel
            const recommendationPanel = document.getElementById('storeRecommendation');
            if (location.isRecommendation) {
                recommendationPanel.classList.remove('hidden');
                document.getElementById('recommendationPriority').textContent = location.priority;
                document.getElementById('estimatedMembers').textContent = location.estimatedMembers;
                document.getElementById('roiPrediction').textContent = location.roiPrediction;
                document.getElementById('recommendationReason').textContent = location.reason;
            } else {
                recommendationPanel.classList.add('hidden');
            }
        }

        function zoomIn() {
            console.log('Zoom in functionality');
            // Implement zoom in logic
        }

        function zoomOut() {
            console.log('Zoom out functionality');
            // Implement zoom out logic
        }

        function exportLocationData() {
            console.log('Export location data');
            // Implement export functionality
        }

        function analyzeTraffic() {
            console.log('Analyze traffic pattern');
            // Implement traffic analysis
        }

        function generateReport() {
            console.log('Generate full report');
            // Implement report generation
        }

        // Close modal when clicking outside
        document.getElementById('mapModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeMapModal();
            }
        });

        // Member Growth Chart
        const memberGrowthCtx = document.getElementById('memberGrowthChart').getContext('2d');
        new Chart(memberGrowthCtx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Ags'],
                datasets: [{
                    label: 'Member Baru',
                    data: [45, 67, 89, 123, 156, 178, 134, 156],
                    borderColor: 'rgb(59, 130, 246)',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    tension: 0.4,
                    fill: true
                }, {
                    label: 'Member Aktif',
                    data: [856, 923, 1012, 1135, 1291, 1469, 1603, 1247],
                    borderColor: 'rgb(34, 197, 94)',
                    backgroundColor: 'rgba(34, 197, 94, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Member Status Chart
        const memberStatusCtx = document.getElementById('memberStatusChart').getContext('2d');
        new Chart(memberStatusCtx, {
            type: 'doughnut',
            data: {
                labels: ['Aktif', 'Tidak Aktif', 'Pending', 'Suspended'],
                datasets: [{
                    data: [1247, 189, 45, 12],
                    backgroundColor: [
                        'rgb(34, 197, 94)',
                        'rgb(239, 68, 68)',
                        'rgb(251, 191, 36)',
                        'rgb(156, 163, 175)'
                    ],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // Geographic Chart
        const geographicCtx = document.getElementById('geographicChart').getContext('2d');
        new Chart(geographicCtx, {
            type: 'bar',
            data: {
                labels: ['Jakarta', 'Bekasi', 'Tangerang', 'Depok', 'Bogor', 'Lainnya'],
                datasets: [{
                    label: 'Jumlah Member',
                    data: [342, 278, 195, 156, 98, 178],
                    backgroundColor: [
                        'rgba(59, 130, 246, 0.8)',
                        'rgba(34, 197, 94, 0.8)',
                        'rgba(147, 51, 234, 0.8)',
                        'rgba(251, 146, 60, 0.8)',
                        'rgba(236, 72, 153, 0.8)',
                        'rgba(156, 163, 175, 0.8)'
                    ],
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
</body>

</html>
