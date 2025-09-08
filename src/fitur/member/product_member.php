<?php
require_once __DIR__ . '/../../component/menu_handler.php';

$menuHandler = new MenuHandler('product_member');

if (!$menuHandler->initialize()) {
    exit();
}

$user_id = $menuHandler->getUserId();
$logger = $menuHandler->getLogger();
$token = $menuHandler->getToken();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analisis Pembelian Member - Asoka Baby Store</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#3B82F6',
                        secondary: '#10B981',
                        accent: '#F59E0B',
                        dark: '#1F2937'
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Header -->
    <header class="bg-white shadow-lg border-b-4 border-primary">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-6">
                <div class="flex items-center">
                    <i class="fas fa-chart-line text-3xl text-primary mr-3"></i>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Analisis Pembelian Member</h1>
                        <p class="text-gray-600">Dashboard untuk Direksi PT Asoka Baby Store</p>
                    </div>
                </div>
                <div class="text-right">
                    <p class="text-sm text-gray-500">Update Terakhir</p>
                    <p class="font-semibold text-gray-900"><?php echo date('d M Y H:i'); ?></p>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Top 3 Members Section -->
        <div class="mb-8">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-bold text-gray-900 flex items-center">
                    <i class="fas fa-trophy text-accent mr-2"></i>
                    Top 3 Member Terbaik
                </h2>
                <div class="flex space-x-2">
                    <span class="px-3 py-1 bg-green-100 text-green-800 text-sm font-medium rounded-full">
                        <i class="fas fa-check-circle mr-1"></i>
                        Real-time Data
                    </span>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Top 1 Member -->
                <div class="relative bg-gradient-to-br from-yellow-400 to-yellow-600 rounded-xl shadow-xl transform hover:scale-105 transition-all duration-300">
                    <div class="absolute -top-3 -right-3">
                        <div class="bg-yellow-500 text-white rounded-full w-8 h-8 flex items-center justify-center">
                            <i class="fas fa-crown text-sm"></i>
                        </div>
                    </div>
                    <div class="p-6 text-white">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-bold">Sarah Johnson</h3>
                            <span class="text-2xl font-bold">#1</span>
                        </div>
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="text-yellow-100">Total Pembelian:</span>
                                <span class="font-bold">Rp 2.450.000</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-yellow-100">Produk Favorit:</span>
                                <span class="font-bold">Diaper Premium</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-yellow-100">Qty Terbanyak:</span>
                                <span class="font-bold">45 pcs</span>
                            </div>
                        </div>
                        <div class="mt-4 pt-4 border-t border-yellow-300">
                            <div class="flex items-center">
                                <i class="fas fa-star text-yellow-200 mr-2"></i>
                                <span class="text-sm">Member sejak 2022</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Top 2 Member -->
                <div class="relative bg-gradient-to-br from-gray-400 to-gray-600 rounded-xl shadow-xl transform hover:scale-105 transition-all duration-300">
                    <div class="absolute -top-3 -right-3">
                        <div class="bg-gray-500 text-white rounded-full w-8 h-8 flex items-center justify-center">
                            <span class="text-sm font-bold">2</span>
                        </div>
                    </div>
                    <div class="p-6 text-white">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-bold">Ahmad Rahman</h3>
                            <span class="text-2xl font-bold">#2</span>
                        </div>
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="text-gray-100">Total Pembelian:</span>
                                <span class="font-bold">Rp 1.890.000</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-100">Produk Favorit:</span>
                                <span class="font-bold">Susu Formula</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-100">Qty Terbanyak:</span>
                                <span class="font-bold">32 pcs</span>
                            </div>
                        </div>
                        <div class="mt-4 pt-4 border-t border-gray-300">
                            <div class="flex items-center">
                                <i class="fas fa-star text-gray-200 mr-2"></i>
                                <span class="text-sm">Member sejak 2023</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Top 3 Member -->
                <div class="relative bg-gradient-to-br from-orange-400 to-orange-600 rounded-xl shadow-xl transform hover:scale-105 transition-all duration-300">
                    <div class="absolute -top-3 -right-3">
                        <div class="bg-orange-500 text-white rounded-full w-8 h-8 flex items-center justify-center">
                            <span class="text-sm font-bold">3</span>
                        </div>
                    </div>
                    <div class="p-6 text-white">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-bold">Lisa Chen</h3>
                            <span class="text-2xl font-bold">#3</span>
                        </div>
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="text-orange-100">Total Pembelian:</span>
                                <span class="font-bold">Rp 1.650.000</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-orange-100">Produk Favorit:</span>
                                <span class="font-bold">Mainan Edukatif</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-orange-100">Qty Terbanyak:</span>
                                <span class="font-bold">28 pcs</span>
                            </div>
                        </div>
                        <div class="mt-4 pt-4 border-t border-orange-300">
                            <div class="flex items-center">
                                <i class="fas fa-star text-orange-200 mr-2"></i>
                                <span class="text-sm">Member sejak 2023</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Dashboard -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <!-- Chart Section -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-bold text-gray-900 flex items-center">
                        <i class="fas fa-chart-bar text-primary mr-2"></i>
                        Tren Pembelian Bulanan
                    </h3>
                    <select class="border border-gray-300 rounded-lg px-3 py-1 text-sm">
                        <option>2024</option>
                        <option>2023</option>
                    </select>
                </div>
                <div class="h-64">
                    <canvas id="purchaseChart"></canvas>
                </div>
            </div>

            <!-- Product Performance -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h3 class="text-lg font-bold text-gray-900 flex items-center mb-6">
                    <i class="fas fa-box text-secondary mr-2"></i>
                    Performa Produk Terlaris
                </h3>
                <div class="space-y-4">
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <div class="flex items-center">
                            <div class="w-3 h-3 bg-green-500 rounded-full mr-3"></div>
                            <div>
                                <p class="font-semibold text-gray-900">Diaper Premium</p>
                                <p class="text-sm text-gray-600">Kategori: Perawatan</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="font-bold text-green-600">1,250 pcs</p>
                            <p class="text-xs text-gray-500">+15% dari bulan lalu</p>
                        </div>
                    </div>
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <div class="flex items-center">
                            <div class="w-3 h-3 bg-blue-500 rounded-full mr-3"></div>
                            <div>
                                <p class="font-semibold text-gray-900">Susu Formula</p>
                                <p class="text-sm text-gray-600">Kategori: Nutrisi</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="font-bold text-blue-600">890 pcs</p>
                            <p class="text-xs text-gray-500">+8% dari bulan lalu</p>
                        </div>
                    </div>
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <div class="flex items-center">
                            <div class="w-3 h-3 bg-purple-500 rounded-full mr-3"></div>
                            <div>
                                <p class="font-semibold text-gray-900">Mainan Edukatif</p>
                                <p class="text-sm text-gray-600">Kategori: Mainan</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="font-bold text-purple-600">650 pcs</p>
                            <p class="text-xs text-gray-500">+12% dari bulan lalu</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detailed Member List -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-bold text-gray-900 flex items-center">
                    <i class="fas fa-users text-primary mr-2"></i>
                    Daftar Lengkap Member & Pembelian
                </h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Ranking
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Nama Member
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Produk Favorit
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Qty Terbanyak
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Total Pembelian
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                    <i class="fas fa-crown mr-1"></i>1
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <div class="h-10 w-10 rounded-full bg-yellow-400 flex items-center justify-center">
                                            <span class="text-white font-bold">SJ</span>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">Sarah Johnson</div>
                                        <div class="text-sm text-gray-500">sarah.j@email.com</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">Diaper Premium</div>
                                <div class="text-sm text-gray-500">Kategori: Perawatan</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <span class="font-bold text-green-600">45 pcs</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <span class="font-bold">Rp 2.450.000</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    <i class="fas fa-check-circle mr-1"></i>Active
                                </span>
                            </td>
                        </tr>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    2
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <div class="h-10 w-10 rounded-full bg-gray-400 flex items-center justify-center">
                                            <span class="text-white font-bold">AR</span>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">Ahmad Rahman</div>
                                        <div class="text-sm text-gray-500">ahmad.r@email.com</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">Susu Formula</div>
                                <div class="text-sm text-gray-500">Kategori: Nutrisi</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <span class="font-bold text-blue-600">32 pcs</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <span class="font-bold">Rp 1.890.000</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    <i class="fas fa-check-circle mr-1"></i>Active
                                </span>
                            </td>
                        </tr>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                    3
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <div class="h-10 w-10 rounded-full bg-orange-400 flex items-center justify-center">
                                            <span class="text-white font-bold">LC</span>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">Lisa Chen</div>
                                        <div class="text-sm text-gray-500">lisa.c@email.com</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">Mainan Edukatif</div>
                                <div class="text-sm text-gray-500">Kategori: Mainan</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <span class="font-bold text-purple-600">28 pcs</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <span class="font-bold">Rp 1.650.000</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    <i class="fas fa-check-circle mr-1"></i>Active
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mt-8">
            <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl p-6 text-white">
                <div class="flex items-center">
                    <div class="p-2 bg-white bg-opacity-20 rounded-lg">
                        <i class="fas fa-users text-2xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm opacity-90">Total Member</p>
                        <p class="text-2xl font-bold">1,247</p>
                    </div>
                </div>
            </div>
            <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-xl p-6 text-white">
                <div class="flex items-center">
                    <div class="p-2 bg-white bg-opacity-20 rounded-lg">
                        <i class="fas fa-shopping-cart text-2xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm opacity-90">Total Transaksi</p>
                        <p class="text-2xl font-bold">5,890</p>
                    </div>
                </div>
            </div>
            <div class="bg-gradient-to-r from-purple-500 to-purple-600 rounded-xl p-6 text-white">
                <div class="flex items-center">
                    <div class="p-2 bg-white bg-opacity-20 rounded-lg">
                        <i class="fas fa-dollar-sign text-2xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm opacity-90">Revenue</p>
                        <p class="text-2xl font-bold">Rp 2.1M</p>
                    </div>
                </div>
            </div>
            <div class="bg-gradient-to-r from-orange-500 to-orange-600 rounded-xl p-6 text-white">
                <div class="flex items-center">
                    <div class="p-2 bg-white bg-opacity-20 rounded-lg">
                        <i class="fas fa-chart-line text-2xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm opacity-90">Growth</p>
                        <p class="text-2xl font-bold">+23%</p>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200 mt-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <i class="fas fa-baby text-primary text-xl mr-2"></i>
                    <span class="text-gray-600 font-medium">Asoka Baby Store - Member Analytics</span>
                </div>
                <div class="text-sm text-gray-500">
                    © 2024 PT Asoka Baby Store. All rights reserved.
                </div>
            </div>
        </div>
    </footer>

    <script>
        // Chart Configuration
        const ctx = document.getElementById('purchaseChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'],
                datasets: [{
                    label: 'Total Pembelian (Jutaan)',
                    data: [1.2, 1.4, 1.6, 1.8, 2.0, 2.1, 2.3, 2.5, 2.7, 2.9, 3.1, 3.3],
                    borderColor: '#3B82F6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4
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
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.1)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });

        // Add some interactive effects
        document.addEventListener('DOMContentLoaded', function() {
            // Add hover effects to cards
            const cards = document.querySelectorAll('.transform');
            cards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'scale(1.05) translateY(-5px)';
                });
                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'scale(1) translateY(0)';
                });
            });
        });
    </script>
</body>
</html>
