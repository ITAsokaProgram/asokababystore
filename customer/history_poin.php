<?php
require_once __DIR__ . '/../aa_kon_sett.php';
require_once __DIR__ . '/../src/auth/middleware_login.php';

$token = $_COOKIE['token'];
$userId = null;
$ip = $_SERVER['REMOTE_ADDR'];
$ua = $_SERVER['HTTP_USER_AGENT'];
$page = $_SERVER['REQUEST_URI'];
$pageName = "History Poin";
if ($token) {
    $verify = verify_token($token);
    $userId = $verify->id;
    // Cek apakah sudah ada record dalam 5 menit terakhir
    $stmt = $conn->prepare("
    SELECT id FROM visitors
    WHERE COALESCE(user_id, ip) = COALESCE(?, ?)
      AND page = ?
      AND visit_time >= (NOW() - INTERVAL 5 MINUTE)
    LIMIT 1
");
    $stmt->bind_param("sss", $userId, $ip, $page);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 0) {
        $stmt = $conn->prepare("
        INSERT INTO visitors (user_id, ip, user_agent, page, page_name) 
        VALUES (?, ?, ?, ?, ?)
    ");
        $stmt->bind_param("issss", $userId, $ip, $ua, $page, $pageName);
        $stmt->execute();
    }
} else {
    header("Location:/log_in");
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Poin - Asoka Baby Store</title>
     <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
        integrity="sha512-..." crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="icon" type="image/png" href="/images/logo1.png">
    <link rel="stylesheet" href="/src/output2.css">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
</head>

<body class="bg-slate-50 min-h-screen">
    <div class="max-w-4xl mx-auto p-4 pb-20 space-y-6">
        <button onclick="history.back()" class="inline-flex items-center gap-3 px-4 py-2 bg-white/80 backdrop-blur-lg rounded-xl shadow-lg border border-pink-100 text-pink-600 hover:bg-pink-50 hover:scale-105 transition-all duration-300 group">
            <i class="fas fa-arrow-left group-hover:-translate-x-1 transition-transform duration-300"></i>
            <span class="font-medium">Kembali</span>
        </button>
        <!-- Header Card -->
        <section class="mb-6">
            <div class="relative bg-gradient-to-br from-slate-900 via-purple-900 to-slate-900 rounded-3xl shadow-2xl p-6 text-white overflow-hidden transition-all duration-500 hover:shadow-purple-500/25 hover:scale-[1.01]">

                <!-- Background Pattern -->
                <div class="absolute inset-0 opacity-10">
                    <div class="absolute top-0 right-0 w-40 h-40 bg-gradient-to-br from-purple-400 to-pink-400 rounded-full -translate-y-20 translate-x-20"></div>
                    <div class="absolute bottom-0 left-0 w-32 h-32 bg-gradient-to-tr from-blue-400 to-purple-400 rounded-full translate-y-16 -translate-x-16"></div>
                </div>

                <!-- Header Content -->
                <div class="relative z-10">
                    <div class="flex items-center justify-between mb-6">
                        <div class="flex items-center space-x-4">
                            <div class="w-12 h-12 bg-gradient-to-br from-purple-400 to-pink-400 rounded-2xl flex items-center justify-center shadow-xl">
                                <i class="fas fa-history text-2xl text-white"></i>
                            </div>
                            <div>
                                <h1 class="text-2xl font-bold bg-gradient-to-r from-white to-purple-200 bg-clip-text text-transparent">
                                    Riwayat Poin
                                </h1>
                                <p class="text-purple-200 text-sm">Lacak pergerakan poin rewards Anda</p>
                            </div>
                        </div>

                        <!-- Total Points Display -->
                        <div class="text-right">
                            <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-4 border border-white/20">
                                <div class="flex items-center space-x-2 mb-1">
                                    <i class="fas fa-coins text-yellow-400"></i>
                                    <span class="text-xs text-purple-200">Total Poin</span>
                                </div>
                                <div class="text-xl font-bold text-white" id="totalPoints"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Filter Buttons -->
                    <div class="flex flex-wrap items-center gap-3 mb-4">
                        <div class="w-10 h-10 bg-white/10 backdrop-blur-sm rounded-2xl p-2 border border-white/20 flex items-center justify-center">
                            <button id="filter-all" data-filter="all" class="filter-btn active flex items-center justify-center px-0 py-0 w-8 h-8 rounded-xl text-sm font-medium transition-all duration-300">
                                <i class="fas fa-list text-lg mx-auto"></i>
                            </button>
                        </div>
                        <div class="w-10 h-10 bg-white/10 backdrop-blur-sm rounded-2xl p-2 border border-white/20 flex items-center justify-center">
                            <button id="filter-plus" data-filter="plus" class="filter-btn flex items-center justify-center px-0 py-0 w-8 h-8 rounded-xl text-sm font-medium transition-all duration-300">
                                <i class="fas fa-plus text-lg mx-auto"></i>
                            </button>
                        </div>
                        <div class="w-10 h-10 bg-white/10 backdrop-blur-sm rounded-2xl p-2 border border-white/20 flex items-center justify-center">
                            <button id="filter-minus" data-filter="minus" class="filter-btn flex items-center justify-center px-0 py-0 w-8 h-8 rounded-xl text-sm font-medium transition-all duration-300">
                                <i class="fas fa-minus text-lg mx-auto"></i>
                            </button>
                        </div>
                    </div>


                    <!-- Shine Effect -->
                    <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/3 to-transparent -skew-x-12 translate-x-[-200%] hover:translate-x-[200%] transition-transform duration-1500"></div>
                </div>
        </section>

        <!-- Points History List -->
        <section>
            <div class="divide-y divide-slate-100" id="pointsList">
                <!-- Items will be populated by JavaScript -->
            </div>
        </section>

        <!-- Empty State -->
        <div id="emptyState" class="hidden">
            <div class="bg-white rounded-3xl shadow-xl p-12 text-center border border-slate-200/60">
                <div class="w-20 h-20 bg-gradient-to-br from-slate-100 to-slate-200 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-inbox text-slate-400 text-2xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-slate-700 mb-2">Tidak ada riwayat</h3>
                <p class="text-slate-500">Belum ada transaksi poin yang sesuai dengan filter yang dipilih.</p>
            </div>
        </div>
    </div>
    <script src="/src/js/index/poin/history_poin/index.js" type="module"></script>
    <style>
        .point-item {
            animation: fadeInUp 0.4s ease-out;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Hover effects */
        .group:hover .fas {
            animation: pulse 1s infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.05);
            }
        }

        /* Scrollbar styling */
        ::-webkit-scrollbar {
            width: 6px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb {
            background: linear-gradient(to bottom, #8b5cf6, #ec4899);
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(to bottom, #7c3aed, #db2777);
        }
    </style>
</body>

</html>