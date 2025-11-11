<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Member Distribution Dashboard</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="icon" type="image/png" href="../../../public/images/logo1.png">
    <link rel="stylesheet" href="../../style/default-font.css">
    <link rel="stylesheet" href="../../output2.css">
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #667eea 100%);
        }

        .glass-effect {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .chart-container {
            position: relative;
            height: 350px;
            width: 100%;
        }


        .skeleton {
            background: linear-gradient(90deg, #e5e7eb 25%, #f3f4f6 50%, #e5e7eb 75%);
            background-size: 200% 100%;
            animation: skeleton-loading 1.5s infinite;
        }


        @keyframes skeleton-loading {
            0% {
                background-position: 200% 0;
            }

            100% {
                background-position: -200% 0;
            }
        }

        .city-item {
            transition: all 0.3s ease;
            transform: translateX(0);
        }

        .city-item:hover {
            transform: translateX(8px);
            box-shadow: 0 8px 25px rgba(34, 197, 94, 0.2);
        }

        .progress-bar {
            transition: width 1s ease-in-out;
        }

        .stat-card {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.9) 0%, rgba(255, 255, 255, 0.7) 100%);
            backdrop-filter: blur(20px);
        }
    </style>
</head>

<body class="min-h-screen gradient-bg p-6">
    <div class="max-w-7xl mx-auto">

        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold text-white mb-2">
                <i class="fas fa-globe-asia mr-3"></i>
                Sebaran Member Dashboard
            </h1>
            <p class="text-blue-100 text-lg">Visualisasi distribusi member berdasarkan lokasi</p>
        </div>

        <!-- Statistics Overview -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">

            <!-- Total Members -->
            <div class="stat-card rounded-2xl p-6 text-center shadow-xl">
                <div class="bg-blue-500 w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-users text-white text-xl"></i>
                </div>
                <h3 class="text-2xl font-bold text-gray-800" id="totalMembers">
                    <span class="block h-6 w-16 mx-auto skeleton"></span>
                </h3>
                <p class="text-gray-600 text-sm">Total Member</p>
            </div>

            <!-- Total Cities -->
            <div class="stat-card rounded-2xl p-6 text-center shadow-xl">
                <div class="bg-green-500 w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-map-marker-alt text-white text-xl"></i>
                </div>
                <h3 class="text-2xl font-bold text-gray-800" id="totalCities">
                    <span class="block h-6 w-10 mx-auto skeleton"></span>
                </h3>
                <p class="text-gray-600 text-sm">Total Kota</p>
            </div>

            <!-- Top City -->
            <div class="stat-card rounded-2xl p-6 text-center shadow-xl">
                <div class="bg-purple-500 w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-crown text-white text-xl"></i>
                </div>
                <h3 class="text-2xl font-bold text-gray-800" id="topCity">
                    <span class="block h-6 w-20 mx-auto skeleton"></span>
                </h3>
                <p class="text-gray-600 text-sm">Kota Terbesar</p>
            </div>

            <!-- Top Percentage -->
            <div class="stat-card rounded-2xl p-6 text-center shadow-xl">
                <div class="bg-orange-500 w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-percentage text-white text-xl"></i>
                </div>
                <h3 class="text-2xl font-bold text-gray-800" id="topPercentage">
                    <span class="block h-6 w-12 mx-auto skeleton"></span>
                </h3>
                <p class="text-gray-600 text-sm">Konsentrasi Tertinggi</p>
            </div>
        </div>

        <!-- Main Content -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            <!-- Chart Visualization -->
            <div class="lg:col-span-2 glass-effect rounded-2xl shadow-xl p-6">
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center gap-3">
                        <div class="bg-gradient-to-r from-blue-500 to-purple-600 p-3 rounded-xl">
                            <i class="fas fa-chart-pie text-white text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-800">Visualisasi Data</h3>
                            <p class="text-gray-600 text-sm">Distribusi member dalam bentuk chart</p>
                        </div>
                    </div>
                    <div class="flex bg-gray-100 rounded-lg p-1">
                        <button onclick="showChart('pie')" id="pieBtn"
                            class="px-4 py-2 rounded-md text-sm font-medium bg-white shadow-sm text-gray-700">
                            <i class="fas fa-chart-pie mr-1"></i>Pie
                        </button>
                        <button onclick="showChart('bar')" id="barBtn"
                            class="px-4 py-2 rounded-md text-sm font-medium text-gray-500 hover:text-gray-700">
                            <i class="fas fa-chart-bar mr-1"></i>Bar
                        </button>
                        <button onclick="showChart('doughnut')" id="doughnutBtn"
                            class="px-4 py-2 rounded-md text-sm font-medium text-gray-500 hover:text-gray-700">
                            <i class="fas fa-circle-notch mr-1"></i>Ring
                        </button>
                    </div>
                </div>
                <div class="chart-container h-[350px]">
                    <canvas id="memberChart"></canvas>
                </div>
            </div>

            <!-- Member List -->
            <div class="glass-effect rounded-2xl shadow-xl p-6">
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center gap-3">
                        <div class="bg-gradient-to-r from-green-500 to-emerald-600 p-3 rounded-xl">
                            <i class="fas fa-list text-white text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-800">Detail Sebaran</h3>
                            <p class="text-gray-600 text-sm">Daftar lengkap per kota</p>
                        </div>
                    </div>
                    <button onclick="toggleSortOrder()"
                        class="text-blue-600 hover:text-blue-800 text-sm font-medium px-3 py-1 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors">
                        <i class="fas fa-sort mr-1"></i><span id="sortText">Terbesar</span>
                    </button>
                </div>
                <div id="memberDistribution" class="space-y-3 max-h-96 overflow-y-auto">
                    <!-- Skeleton list -->
                    <div class="p-4 rounded-xl border border-gray-200 bg-white">
                        <div class="h-4 w-32 mb-2 skeleton rounded"></div>
                        <div class="h-3 w-full skeleton rounded"></div>
                    </div>
                    <div class="p-4 rounded-xl border border-gray-200 bg-white">
                        <div class="h-4 w-28 mb-2 skeleton rounded"></div>
                        <div class="h-3 w-full skeleton rounded"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Additional Insights -->
        <div class="mt-8 glass-effect rounded-2xl shadow-xl p-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="bg-gradient-to-r from-indigo-500 to-blue-600 p-3 rounded-xl">
                    <i class="fas fa-lightbulb text-white text-xl"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-800">Insights & Analisis</h3>
            </div>
            <div id="insights" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <!-- Skeleton insight -->
                <div class="h-20 skeleton rounded-lg"></div>
                <div class="h-20 skeleton rounded-lg"></div>
                <div class="h-20 skeleton rounded-lg"></div>
            </div>
        </div>
    </div>

    <script>
        let memberData = [];
        let currentChart = null;
        let currentChartType = 'pie';
        let sortAscending = false;

        // Sample data
        const sampleData = [{
            kota: "Jakarta",
            total: 1250,
            persen: 35.2
        },
        {
            kota: "Surabaya",
            total: 890,
            persen: 25.1
        },
        {
            kota: "Bandung",
            total: 675,
            persen: 19.0
        },
        {
            kota: "Medan",
            total: 420,
            persen: 11.8
        },
        {
            kota: "Makassar",
            total: 215,
            persen: 6.1
        },
        {
            kota: "Yogyakarta",
            total: 98,
            persen: 2.8
        }
        ];

        // Warna chart
        const colors = [
            '#3B82F6', '#10B981', '#F59E0B', '#EF4444',
            '#8B5CF6', '#06B6D4', '#84CC16', '#F97316'
        ];

        async function loadMemberDistribution() {
            try {
                const res = await fetch("/src/api/member/management/get_city_member_all");
                const json = await res.json();
                memberData = json.success ? json.data : sampleData;
            } catch (error) {
                console.error('Error loading data:', error);
                memberData = sampleData; // fallback
            }

            updateStatistics();
            renderMemberList();
            renderChart();
            generateInsights();
        }

        function updateStatistics() {
            const totalMembers = memberData.reduce((sum, item) => sum + item.total, 0);
            const totalCities = memberData.length;
            const topCity = [...memberData].sort((a, b) => b.total - a.total)[0];

            document.getElementById('totalMembers').textContent = totalMembers.toLocaleString();
            document.getElementById('totalCities').textContent = totalCities;
            document.getElementById('topCity').textContent = topCity ? topCity.kota : '-';
            document.getElementById('topPercentage').textContent = topCity ? topCity.persen + '%' : '0%';
        }

        function renderMemberList() {
            const container = document.getElementById("memberDistribution");
            const sortedData = [...memberData].sort((a, b) =>
                sortAscending ? a.total - b.total : b.total - a.total
            );

            let html = "";
            const maxTotal = Math.max(...memberData.map(d => d.total));

            sortedData.forEach((item, idx) => {
                const widthPercentage = (item.total / maxTotal) * 100;

                html += `
        <div class="city-item p-4 bg-white rounded-xl border border-gray-200 shadow-sm hover:shadow-md">
          <div class="flex items-center justify-between mb-2">
            <div class="flex items-center gap-3">
              <div class="w-4 h-4 rounded-full" style="background-color: ${colors[idx % colors.length]}"></div>
              <span class="font-semibold text-gray-800">${item.kota}</span>
              ${idx === 0 ? '<i class="fas fa-crown text-yellow-500 ml-1"></i>' : ''}
            </div>
            <div class="text-right">
              <span class="text-xl font-bold" style="color: ${colors[idx % colors.length]}">${item.total.toLocaleString()}</span>
              <p class="text-xs text-gray-500">${item.persen}%</p>
            </div>
          </div>
          <div class="w-full bg-gray-200 rounded-full h-2">
            <div class="progress-bar h-2 rounded-full"
                 style="background-color:${colors[idx % colors.length]}; width:${widthPercentage}%"></div>
          </div>
          <div class="mt-2 flex items-center text-xs text-gray-600">
            <i class="fas fa-users mr-1"></i>
            <span>Ranking #${idx + 1}</span>
          </div>
        </div>
      `;
            });

            container.innerHTML = html;
        }

        function getTopCities(data, limit = 10) {
            const sorted = [...data].sort((a, b) => b.total - a.total);
            const top = sorted.slice(0, limit);
            const others = sorted.slice(limit);

            if (others.length > 0) {
                const otherTotal = others.reduce((sum, d) => sum + d.total, 0);
                const persen = ((otherTotal / data.reduce((s, d) => s + d.total, 0)) * 100).toFixed(2);
                top.push({
                    kota: "Lainnya",
                    total: otherTotal,
                    persen
                });
            }
            return top;
        }

        function renderChart(forceRecreate = false) {
            const ctx = document.getElementById('memberChart').getContext('2d');
            const topData = getTopCities(memberData, 10);

            const chartConfig = {
                type: currentChartType,
                data: {
                    labels: topData.map(item => item.kota),
                    datasets: [{
                        data: topData.map(item => item.total),
                        backgroundColor: colors.slice(0, topData.length),
                        borderWidth: 2,
                        borderColor: '#ffffff',
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: false,
                    plugins: {
                        legend: {
                            position: currentChartType === 'bar' ? 'top' : 'right',
                            labels: {
                                padding: 20,
                                font: {
                                    size: 12
                                }
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    const item = topData[context.dataIndex];
                                    return `${context.label}: ${context.parsed.toLocaleString()} member (${item.persen}%)`;
                                }
                            }
                        }
                    },
                    ...(currentChartType === 'bar' && {
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: (value) => value.toLocaleString()
                                }
                            }
                        }
                    })
                }
            };

            if (forceRecreate || !currentChart) {
                if (currentChart) currentChart.destroy();
                currentChart = new Chart(ctx, chartConfig);
            } else {
                currentChart.data = chartConfig.data;
                currentChart.options = chartConfig.options;
                currentChart.update();
            }
        }

        function showChart(type) {
            currentChartType = type;

            // Update tombol aktif
            document.querySelectorAll('#pieBtn, #barBtn, #doughnutBtn').forEach(btn => {
                btn.className = btn.className.replace(' bg-white shadow-sm text-gray-700', ' text-gray-500 hover:text-gray-700');
            });
            document.getElementById(type + 'Btn').className += ' bg-white shadow-sm text-gray-700';

            // Recreate chart dengan type baru
            renderChart(true);
        }

        function toggleSortOrder() {
            sortAscending = !sortAscending;
            document.getElementById('sortText').textContent = sortAscending ? 'Terkecil' : 'Terbesar';
            renderMemberList();
        }

        function generateInsights() {
            const insights = document.getElementById('insights');
            const totalMembers = memberData.reduce((sum, item) => sum + item.total, 0);
            const topCity = [...memberData].sort((a, b) => b.total - a.total)[0];
            const bottomCity = [...memberData].sort((a, b) => a.total - b.total)[0];
            const avgMembers = Math.round(totalMembers / memberData.length);

            insights.innerHTML = `
      <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
        <div class="flex items-center mb-2">
          <i class="fas fa-chart-line text-blue-600 mr-2"></i>
          <h4 class="font-semibold text-blue-800">Dominasi Pasar</h4>
        </div>
        <p class="text-sm text-blue-700">${topCity.kota} mendominasi dengan ${topCity.persen}% dari total member</p>
      </div>
      <div class="bg-green-50 p-4 rounded-lg border border-green-200">
        <div class="flex items-center mb-2">
          <i class="fas fa-balance-scale text-green-600 mr-2"></i>
          <h4 class="font-semibold text-green-800">Rata-rata</h4>
        </div>
        <p class="text-sm text-green-700">Rata-rata member per kota: ${avgMembers.toLocaleString()}</p>
      </div>
      <div class="bg-orange-50 p-4 rounded-lg border border-orange-200">
        <div class="flex items-center mb-2">
          <i class="fas fa-seedling text-orange-600 mr-2"></i>
          <h4 class="font-semibold text-orange-800">Potensi Pertumbuhan</h4>
        </div>
        <p class="text-sm text-orange-700">${bottomCity.kota} memiliki potensi besar untuk ekspansi</p>
      </div>
    `;
        }

        document.addEventListener('DOMContentLoaded', loadMemberDistribution);
    </script>

</body>

</html>