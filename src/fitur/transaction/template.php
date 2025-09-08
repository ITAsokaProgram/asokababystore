<!-- iNVALID TEMPLATE ENHANCEMENT -->
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VOID Transaction Report</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        
        .glass-morphism {
            backdrop-filter: blur(16px);
            background: rgba(255, 255, 255, 0.9);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .card-hover {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .card-hover:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }
        
        .scrollbar-thin::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        
        .scrollbar-thin::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 3px;
        }
        
        .scrollbar-thin::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, #e11d48, #f472b6);
            border-radius: 3px;
        }
        
        .animate-pulse-slow {
            animation: pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
        
        .floating-animation {
            animation: float 6s ease-in-out infinite;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
        
        .shine-effect {
            background: linear-gradient(45deg, transparent 30%, rgba(255, 255, 255, 0.5) 50%, transparent 70%);
            background-size: 200% 100%;
            animation: shine 3s infinite;
        }
        
        @keyframes shine {
            0% { background-position: -200% 0; }
            100% { background-position: 200% 0; }
        }
        
        .modal-backdrop {
            backdrop-filter: blur(8px);
            background: rgba(0, 0, 0, 0.4);
        }
        
        .fade-in {
            animation: fadeIn 0.3s ease-out;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .table-stripe:nth-child(even) {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        }
        
        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .ripple-effect {
            position: relative;
            overflow: hidden;
        }
        
        .ripple-effect::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.6);
            transition: width 0.6s, height 0.6s, top 0.6s, left 0.6s;
        }
        
        .ripple-effect:active::before {
            width: 300px;
            height: 300px;
            top: -150px;
            left: -150px;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50 min-h-screen">
    <!-- Navigation (placeholder) -->
    <nav class="fixed top-0 left-0 right-0 z-40 glass-morphism h-16 flex items-center px-6 shadow-lg">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-rose-500 to-pink-600 flex items-center justify-center shadow-lg">
                <i class="fas fa-chart-line text-white text-lg"></i>
            </div>
            <h1 class="text-xl font-bold bg-gradient-to-r from-gray-700 to-gray-900 bg-clip-text text-transparent">
                Transaction Reports
            </h1>
        </div>
    </nav>

    <!-- Sidebar (placeholder) -->
    <aside class="fixed left-0 top-16 w-64 h-full glass-morphism shadow-2xl z-30">
        <div class="p-6">
            <div class="space-y-4">
                <div class="flex items-center gap-3 p-3 rounded-xl bg-gradient-to-r from-rose-500 to-pink-600 text-white shadow-lg">
                    <i class="fas fa-ban text-lg"></i>
                    <span class="font-semibold">VOID Reports</span>
                </div>
            </div>
        </div>
    </aside>

    <main class="ml-64 mt-16 p-8 transition-all duration-300">
        <div class="max-w-7xl mx-auto">
            <!-- Enhanced Header -->
            <div class="mb-10 relative">
                <div class="absolute inset-0 bg-gradient-to-r from-rose-500/10 via-pink-500/10 to-red-500/10 rounded-3xl blur-xl"></div>
                <div class="relative glass-morphism rounded-3xl p-8 shadow-2xl">
                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-8">
                        <div class="flex items-center gap-6">
                            <div class="floating-animation">
                                <div class="w-20 h-20 rounded-3xl bg-gradient-to-br from-rose-500 via-pink-500 to-red-500 flex items-center justify-center shadow-2xl shine-effect">
                                    <i class="fas fa-ban text-white text-3xl"></i>
                                </div>
                            </div>
                            <div>
                                <h1 class="text-5xl font-black bg-gradient-to-r from-rose-600 via-pink-600 to-red-600 bg-clip-text text-transparent tracking-tight leading-tight mb-2">
                                    VOID Transaction Monitor
                                </h1>
                                <p class="text-slate-600 text-lg font-medium leading-relaxed">
                                    Comprehensive invalid transaction monitoring & analytics dashboard
                                </p>
                                <div class="flex items-center gap-4 mt-4">
                                    <div class="flex items-center gap-2 text-emerald-600">
                                        <i class="fas fa-circle text-xs animate-pulse"></i>
                                        <span class="text-sm font-semibold">Real-time Data</span>
                                    </div>
                                    <div class="flex items-center gap-2 text-blue-600">
                                        <i class="fas fa-shield-alt text-xs"></i>
                                        <span class="text-sm font-semibold">Secure</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="flex items-center gap-4">
                            <div class="relative">
                                <select id="cabang-select" class="appearance-none bg-white/80 border border-slate-200 rounded-2xl px-6 py-3 text-sm font-semibold focus:ring-4 focus:ring-rose-200 focus:border-rose-400 transition-all duration-300 shadow-lg min-w-[200px]">
                                    <option value="">Select Branch</option>
                                    <option value="branch1">Branch 1</option>
                                    <option value="branch2">Branch 2</option>
                                </select>
                                <i class="fas fa-chevron-down absolute right-4 top-1/2 transform -translate-y-1/2 text-slate-400 pointer-events-none"></i>
                            </div>
                            
                            <button id="btn-export-excel" class="ripple-effect bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white px-8 py-3 rounded-2xl shadow-lg font-semibold flex items-center gap-3 transition-all duration-300 card-hover">
                                <i class="fas fa-file-excel text-lg"></i>
                                <span>Export Excel</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Enhanced Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
                <div class="glass-morphism rounded-3xl p-6 shadow-xl card-hover fade-in">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-rose-500 to-pink-600 flex items-center justify-center shadow-lg">
                            <i class="fas fa-ban text-white text-2xl"></i>
                        </div>
                        <div class="text-right">
                            <div class="text-xs text-slate-500 font-bold uppercase tracking-widest mb-1">Total Invalid</div>
                            <div class="text-3xl font-black text-rose-600" id="total-void">1,247</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 text-sm">
                        <span class="status-badge bg-rose-100 text-rose-700">Critical</span>
                        <span class="text-slate-500">+12% from last week</span>
                    </div>
                </div>

                <div class="glass-morphism rounded-3xl p-6 shadow-xl card-hover fade-in">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center shadow-lg">
                            <i class="fas fa-store text-white text-2xl"></i>
                        </div>
                        <div class="text-right">
                            <div class="text-xs text-slate-500 font-bold uppercase tracking-widest mb-1">Top Branch</div>
                            <div class="text-3xl font-black text-blue-600" id="top-cabang">Branch A</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 text-sm">
                        <span class="status-badge bg-blue-100 text-blue-700">Highest</span>
                        <span class="text-slate-500">324 transactions</span>
                    </div>
                </div>

                <div class="glass-morphism rounded-3xl p-6 shadow-xl card-hover fade-in">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center shadow-lg">
                            <i class="fas fa-clock text-white text-2xl"></i>
                        </div>
                        <div class="text-right">
                            <div class="text-xs text-slate-500 font-bold uppercase tracking-widest mb-1">Peak Hour</div>
                            <div class="text-3xl font-black text-amber-600">14:00</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 text-sm">
                        <span class="status-badge bg-amber-100 text-amber-700">Peak</span>
                        <span class="text-slate-500">156 voids/hour</span>
                    </div>
                </div>

                <div class="glass-morphism rounded-3xl p-6 shadow-xl card-hover fade-in">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-purple-500 to-violet-600 flex items-center justify-center shadow-lg">
                            <i class="fas fa-percentage text-white text-2xl"></i>
                        </div>
                        <div class="text-right">
                            <div class="text-xs text-slate-500 font-bold uppercase tracking-widest mb-1">Void Rate</div>
                            <div class="text-3xl font-black text-purple-600">3.2%</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 text-sm">
                        <span class="status-badge bg-purple-100 text-purple-700">Normal</span>
                        <span class="text-slate-500">Within threshold</span>
                    </div>
                </div>
            </div>

            <!-- Enhanced Data Table -->
            <div class="glass-morphism rounded-3xl shadow-2xl overflow-hidden">
                <div class="bg-gradient-to-r from-slate-800 to-slate-900 px-8 py-6">
                    <div class="flex items-center justify-between">
                        <h2 class="text-2xl font-bold text-white flex items-center gap-3">
                            <i class="fas fa-table text-rose-400"></i>
                            Transaction Details
                        </h2>
                        <div class="flex items-center gap-4">
                            <div class="flex items-center gap-2 text-slate-300">
                                <i class="fas fa-database text-sm"></i>
                                <span class="text-sm font-medium">Live Data</span>
                            </div>
                            <div class="w-2 h-2 bg-emerald-400 rounded-full animate-pulse"></div>
                        </div>
                    </div>
                </div>
                
                <div class="overflow-x-auto scrollbar-thin" style="max-height: 600px;">
                    <table class="w-full">
                        <thead class="sticky top-0 bg-gradient-to-r from-slate-100 to-slate-200 border-b border-slate-300">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-bold text-slate-700 uppercase tracking-wider">No</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-slate-700 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-slate-700 uppercase tracking-wider">Category</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-slate-700 uppercase tracking-wider">Branch</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-slate-700 uppercase tracking-wider">Cashier</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-slate-700 uppercase tracking-wider">Void Count</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-slate-700 uppercase tracking-wider">Action</th>
                            </tr>
                        </thead>
                        <tbody id="void-table-body" class="bg-white divide-y divide-slate-200">
                            <!-- Sample Data -->
                            <tr class="table-stripe hover:bg-slate-50 transition-colors duration-200">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-900">1</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-700">2024-01-15</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="status-badge bg-rose-100 text-rose-700">Critical</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-700">Branch A</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-700">John Doe</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-2">
                                        <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-rose-500 to-pink-600 flex items-center justify-center">
                                            <span class="text-white font-bold text-sm">15</span>
                                        </div>
                                        <span class="text-sm text-slate-600">transactions</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <button onclick="openModal()" class="ripple-effect bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700 text-white px-4 py-2 rounded-xl text-sm font-semibold flex items-center gap-2 transition-all duration-300">
                                        <i class="fas fa-eye"></i>
                                        View Details
                                    </button>
                                </td>
                            </tr>
                            
                            <tr class="table-stripe hover:bg-slate-50 transition-colors duration-200">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-900">2</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-700">2024-01-14</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="status-badge bg-amber-100 text-amber-700">Warning</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-700">Branch B</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-700">Jane Smith</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-2">
                                        <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center">
                                            <span class="text-white font-bold text-sm">8</span>
                                        </div>
                                        <span class="text-sm text-slate-600">transactions</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <button onclick="openModal()" class="ripple-effect bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700 text-white px-4 py-2 rounded-xl text-sm font-semibold flex items-center gap-2 transition-all duration-300">
                                        <i class="fas fa-eye"></i>
                                        View Details
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <div class="bg-gradient-to-r from-slate-50 to-slate-100 px-8 py-4 border-t border-slate-200">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-slate-600">Showing 1-10 of 1,247 results</span>
                        <div class="flex items-center gap-2">
                            <button class="px-4 py-2 bg-white border border-slate-200 rounded-xl text-sm font-medium text-slate-700 hover:bg-slate-50 transition-colors">
                                <i class="fas fa-chevron-left mr-2"></i>Previous
                            </button>
                            <button class="px-4 py-2 bg-gradient-to-r from-blue-500 to-indigo-600 text-white rounded-xl text-sm font-medium hover:from-blue-600 hover:to-indigo-700 transition-all">
                                Next<i class="fas fa-chevron-right ml-2"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Enhanced Modal -->
    <div id="modal-detail" class="fixed inset-0 z-50 flex items-center justify-center modal-backdrop hidden">
        <div class="glass-morphism rounded-3xl shadow-2xl p-8 w-full max-w-7xl mx-4 max-h-[90vh] overflow-y-auto fade-in">
            <div class="flex items-center justify-between mb-8">
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-rose-500 to-pink-600 flex items-center justify-center shadow-lg">
                        <i class="fas fa-ban text-white text-2xl"></i>
                    </div>
                    <div>
                        <h2 class="text-3xl font-black bg-gradient-to-r from-rose-600 to-pink-600 bg-clip-text text-transparent">
                            Transaction Details
                        </h2>
                        <p class="text-slate-600 font-medium">Comprehensive void transaction analysis</p>
                    </div>
                </div>
                <button onclick="closeModal()" class="w-12 h-12 rounded-2xl bg-gradient-to-br from-slate-100 to-slate-200 hover:from-slate-200 hover:to-slate-300 flex items-center justify-center text-slate-600 hover:text-slate-800 transition-all duration-300 shadow-lg">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <div class="overflow-x-auto scrollbar-thin rounded-2xl shadow-xl">
                <table class="w-full">
                    <thead class="bg-gradient-to-r from-slate-800 to-slate-900 text-white">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider">No</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider">Product</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider">Receipt No</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider">Time</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider">Branch</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider">Cashier</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider">Code</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider">Date</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider">Notes</th>
                        </tr>
                    </thead>
                    <tbody id="modal-detail-tbody" class="bg-white divide-y divide-slate-200">
                        <!-- Sample Detail Data -->
                        <tr class="hover:bg-slate-50 transition-colors duration-200">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-900">1</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-700">Premium Coffee</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-700">#RC001234</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-700">14:30:25</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-700">Branch A</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-700">John Doe</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-700">CSH001</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-700">2024-01-15</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="status-badge bg-rose-100 text-rose-700">Customer Return</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        // Sample JavaScript functions
        function openModal() {
            document.getElementById('modal-detail').classList.remove('hidden');
        }

        function closeModal() {
            document.getElementById('modal-detail').classList.add('hidden');
        }

        // Sample data loading simulation
        function loadData() {
            // Your existing data loading logic here
        }

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            loadData();
        });
    </script>
</body>
</html>