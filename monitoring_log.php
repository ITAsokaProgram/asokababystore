<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Auto Log Monitor Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .log-entry {
            transition: all 0.3s ease;
        }
        .log-entry:hover {
            transform: translateX(4px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }
        .scrollbar-thin::-webkit-scrollbar {
            width: 8px;
        }
        .scrollbar-thin::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 4px;
        }
        .scrollbar-thin::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }
        .scrollbar-thin::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
        .fade-in {
            animation: fadeIn 0.5s ease-in;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .pulse-dot {
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .new-log {
            animation: slideInRight 0.5s ease-out;
            border-left: 4px solid #10b981 !important;
        }
        @keyframes slideInRight {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Header -->
    <header class="gradient-bg text-white shadow-2xl">
        <div class="container mx-auto px-6 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="bg-white bg-opacity-20 p-3 rounded-full">
                        <i class="fas fa-eye text-2xl"></i>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold">Auto Log Monitor</h1>
                        <p class="text-blue-100 text-sm">Monitoring otomatis file log harian</p>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <div id="autoRefreshStatus" class="flex items-center space-x-2">
                        <div class="pulse-dot w-3 h-3 bg-green-400 rounded-full"></div>
                        <span class="text-sm">Auto-refresh: ON</span>
                    </div>
                    <div class="text-right">
                        <div class="text-sm opacity-90">Terakhir update:</div>
                        <div id="lastUpdate" class="text-sm font-mono">--:--:--</div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <div class="container mx-auto px-6 py-8">
        <!-- Controls -->
        <div class="bg-white rounded-xl shadow-lg p-6 mb-8 fade-in">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-folder-open mr-2 text-blue-500"></i>Folder Path Log
                    </label>
                    <div class="flex space-x-2">
                        <input type="text" id="logFolderPath" placeholder="/path/to/logs" value="./logs"
                               class="flex-1 px-4 py-3 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                        <button id="loadFolderBtn" class="px-4 py-3 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors">
                            <i class="fas fa-folder-open"></i>
                        </button>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-calendar mr-2 text-green-500"></i>Pilih Tanggal
                    </label>
                    <input type="date" id="dateFilter" 
                           class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all">
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-filter mr-2 text-purple-500"></i>Filter Level
                    </label>
                    <select id="logLevel" class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all">
                        <option value="">Semua Level</option>
                        <option value="ERROR">Error</option>
                        <option value="WARN">Warning</option>
                        <option value="INFO">Info</option>
                        <option value="DEBUG">Debug</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-search mr-2 text-orange-500"></i>Cari dalam Log
                    </label>
                    <div class="relative">
                        <input type="text" id="searchInput" placeholder="Cari teks..."
                               class="w-full px-4 py-3 pr-10 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all">
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                            <i class="fas fa-search text-gray-400"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="mt-6 flex flex-wrap gap-3">
                <button id="toggleAutoRefresh" class="px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition-colors flex items-center space-x-2">
                    <i class="fas fa-play"></i>
                    <span>Auto Refresh: ON</span>
                </button>
                <button id="manualRefresh" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors flex items-center space-x-2">
                    <i class="fas fa-sync-alt"></i>
                    <span>Manual Refresh</span>
                </button>
                <button id="clearBtn" class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-colors flex items-center space-x-2">
                    <i class="fas fa-trash"></i>
                    <span>Clear</span>
                </button>
                <button id="exportBtn" class="px-4 py-2 bg-indigo-500 text-white rounded-lg hover:bg-indigo-600 transition-colors flex items-center space-x-2">
                    <i class="fas fa-download"></i>
                    <span>Export CSV</span>
                </button>
                <div class="flex items-center space-x-2 ml-4">
                    <label class="text-sm font-semibold text-gray-700">Interval:</label>
                    <select id="refreshInterval" class="px-3 py-2 border rounded-lg text-sm">
                        <option value="5000">5 detik</option>
                        <option value="10000" selected>10 detik</option>
                        <option value="30000">30 detik</option>
                        <option value="60000">1 menit</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- File List & Stats Row -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
            <!-- Available Files -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-files mr-3 text-blue-500"></i>
                    File Log Tersedia
                </h3>
                <div id="fileList" class="space-y-2 max-h-40 overflow-y-auto scrollbar-thin">
                    <div class="text-center py-4 text-gray-500 text-sm">
                        Masukkan path folder untuk melihat file
                    </div>
                </div>
            </div>

            <!-- Stats -->
            <div class="lg:col-span-2">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="bg-white p-4 rounded-xl shadow-lg border-l-4 border-red-500">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-red-600 text-xs font-semibold uppercase tracking-wide">Errors</p>
                                <p class="text-2xl font-bold text-gray-900" id="errorCount">0</p>
                            </div>
                            <div class="bg-red-100 p-2 rounded-full">
                                <i class="fas fa-exclamation-triangle text-red-500"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white p-4 rounded-xl shadow-lg border-l-4 border-yellow-500">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-yellow-600 text-xs font-semibold uppercase tracking-wide">Warnings</p>
                                <p class="text-2xl font-bold text-gray-900" id="warnCount">0</p>
                            </div>
                            <div class="bg-yellow-100 p-2 rounded-full">
                                <i class="fas fa-exclamation-circle text-yellow-500"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white p-4 rounded-xl shadow-lg border-l-4 border-blue-500">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-blue-600 text-xs font-semibold uppercase tracking-wide">Info</p>
                                <p class="text-2xl font-bold text-gray-900" id="infoCount">0</p>
                            </div>
                            <div class="bg-blue-100 p-2 rounded-full">
                                <i class="fas fa-info-circle text-blue-500"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white p-4 rounded-xl shadow-lg border-l-4 border-gray-500">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-600 text-xs font-semibold uppercase tracking-wide">Total</p>
                                <p class="text-2xl font-bold text-gray-900" id="totalCount">0</p>
                            </div>
                            <div class="bg-gray-100 p-2 rounded-full">
                                <i class="fas fa-list text-gray-500"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Log Display -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden fade-in">
            <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                        <i class="fas fa-file-alt mr-3 text-indigo-500"></i>
                        Log Entries
                        <span id="logCount" class="ml-3 px-3 py-1 bg-indigo-100 text-indigo-800 rounded-full text-sm font-medium">0 entries</span>
                    </h3>
                    <div class="flex items-center space-x-3">
                        <div id="currentFile" class="text-sm text-gray-600">
                            <i class="fas fa-file mr-1"></i>
                            <span>Tidak ada file aktif</span>
                        </div>
                        <button id="scrollToBottom" class="px-3 py-1 bg-gray-200 text-gray-700 rounded text-sm hover:bg-gray-300 transition-colors">
                            <i class="fas fa-arrow-down mr-1"></i>Bottom
                        </button>
                    </div>
                </div>
            </div>
            
            <div id="logContainer" class="max-h-96 overflow-y-auto scrollbar-thin p-4 space-y-2">
                <div id="emptyState" class="text-center py-12 text-gray-500">
                    <i class="fas fa-folder-open text-6xl mb-4 text-gray-300"></i>
                    <p class="text-xl mb-2">Auto Log Monitor Siap</p>
                    <p class="text-sm">Masukkan path folder logs dan tekan tombol folder untuk memulai monitoring</p>
                </div>
            </div>
        </div>
    </div>
        <script src="src/js/monitoring/index.js" type="module"></script>
</body>
</html>