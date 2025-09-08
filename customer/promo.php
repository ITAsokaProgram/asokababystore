<?php
require_once __DIR__ . '/../aa_kon_sett.php';
require_once __DIR__ . '/../src/auth/middleware_login.php';

$token = $_COOKIE['token'];
$userId = null;
$ip = $_SERVER['REMOTE_ADDR'];
$ua = $_SERVER['HTTP_USER_AGENT'];
$page = $_SERVER['REQUEST_URI'];
$pageName = "Customer Promo";
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
        $stmt->bind_param("issss", $userId, $ip, $ua, $page , $pageName);
        $stmt->execute();
    }
} else {
    header("Location:/log_in");
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <meta name="color-scheme" content="light dark">
    <title>Promo Spesial - Asoka Baby Store</title>

    <!-- Tailwind CSS CDN -->
    <!-- <script src="https://cdn.tailwindcss.com"></script> -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
        integrity="sha512-..." crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="icon" type="image/png" href="/images/logo1.png">
    <link rel="stylesheet" href="/src/output2.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">

    <style>
        * {
            font-family: 'Poppins', sans-serif;
        }

        /* Custom Animations */
        @keyframes float {

            0%,
            100% {
                transform: translateY(0px);
            }

            50% {
                transform: translateY(-5px);
            }
        }

        .float-animation {
            animation: float 3s ease-in-out infinite;
        }

        /* Gradient Text */
        .gradient-text {
            background: linear-gradient(-45deg, #ec4899, #8b5cf6, #3b82f6, #ec4899);
            background-size: 400% 400%;
            animation: gradient-shift 3s ease infinite;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        @keyframes gradient-shift {

            0%,
            100% {
                background-position: 0% 50%;
            }

            50% {
                background-position: 100% 50%;
            }
        }

        /* Card Hover Effects */
        .card-hover-effect:hover {
            transform: translateY(-5px);
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
        }

        /* Glass Effect */
        .glass-effect {
            background: rgba(255, 255, 255, 0.25);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.18);
        }

        /* Custom Scrollbar */
        .scrollbar-thin::-webkit-scrollbar {
            width: 4px;
        }

        .scrollbar-thin::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 2px;
        }

        .scrollbar-thin::-webkit-scrollbar-thumb {
            background: #f472b6;
            border-radius: 2px;
        }

        .scrollbar-thin::-webkit-scrollbar-thumb:hover {
            background: #ec4899;
        }

        /* Loading Animation */
        @keyframes shimmer {
            0% {
                background-position: -200px 0;
            }

            100% {
                background-position: calc(200px + 100%) 0;
            }
        }

        .shimmer {
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200px 100%;
            animation: shimmer 1.5s infinite;
        }
    </style>
</head>

<body class="bg-gradient-to-br from-pink-50 via-white to-purple-50 min-h-screen text-gray-800">
    <div class="max-w-6xl mx-auto p-4 pb-32 space-y-6">

        <!-- Enhanced Back Button -->
        <div class="mb-4">
            <button onclick="history.back()" class="inline-flex items-center gap-3 px-4 py-2 bg-white/80 backdrop-blur-lg rounded-xl shadow-lg border border-pink-100 text-pink-600 hover:bg-pink-50 hover:scale-105 transition-all duration-300 group">
                <i class="fas fa-arrow-left group-hover:-translate-x-1 transition-transform duration-300"></i>
                <span class="font-medium">Kembali</span>
            </button>
        </div>

        <!-- Enhanced Header -->
        <header class="bg-gradient-to-r from-pink-500 to-purple-600 rounded-3xl shadow-xl p-8 text-white relative overflow-hidden">
            <div class="absolute inset-0 bg-black/10"></div>
            <div class="relative z-10">
                <div class="flex items-center gap-4 mb-4">
                    <div class="w-16 h-16 bg-white/20 backdrop-blur-sm rounded-full flex items-center justify-center float-animation">
                        <i class="fas fa-gift text-3xl"></i>
                    </div>
                    <div>
                        <h1 class="text-3xl md:text-4xl font-bold mb-2">Promo Spesial untuk Bunda</h1>
                        <p class="text-pink-100 text-lg">Dapatkan penawaran terbaik khusus untuk Anda</p>
                    </div>
                </div>
                <div class="flex items-center gap-6 text-sm">
                    <div class="flex items-center gap-2">
                        <i class="fas fa-clock text-pink-100"></i>
                        <span class="text-pink-100">Terbatas Waktu</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <i class="fas fa-star text-pink-100"></i>
                        <span class="text-pink-100">Eksklusif Member</span>
                    </div>
                </div>
            </div>

            <!-- Decorative Elements -->
            <div class="absolute top-6 right-6 w-20 h-20 bg-white/10 rounded-full"></div>
            <div class="absolute bottom-6 right-12 w-12 h-12 bg-white/10 rounded-full"></div>
            <div class="absolute top-1/2 right-8 w-8 h-8 bg-white/10 rounded-full"></div>
        </header>

        <!-- Enhanced Promo Grid -->
        <section class="space-y-6">
            <!-- Loading State -->
            <div id="loading-state" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <div class="bg-white/80 backdrop-blur-lg rounded-2xl shadow-lg p-6 shimmer">
                    <div class="w-full h-48 bg-gray-200 rounded-xl mb-4"></div>
                    <div class="h-4 bg-gray-200 rounded mb-2"></div>
                    <div class="h-3 bg-gray-200 rounded w-3/4"></div>
                </div>
                <div class="bg-white/80 backdrop-blur-lg rounded-2xl shadow-lg p-6 shimmer">
                    <div class="w-full h-48 bg-gray-200 rounded-xl mb-4"></div>
                    <div class="h-4 bg-gray-200 rounded mb-2"></div>
                    <div class="h-3 bg-gray-200 rounded w-3/4"></div>
                </div>
                <div class="bg-white/80 backdrop-blur-lg rounded-2xl shadow-lg p-6 shimmer">
                    <div class="w-full h-48 bg-gray-200 rounded-xl mb-4"></div>
                    <div class="h-4 bg-gray-200 rounded mb-2"></div>
                    <div class="h-3 bg-gray-200 rounded w-3/4"></div>
                </div>
            </div>

            <!-- Promo Container -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6" id="promo-container">
                <!-- Promo items will be populated here -->
            </div>

            <!-- No Promo Message -->
            <div id="no-promo" class="hidden text-center py-12">
                <div class="w-24 h-24 bg-gradient-to-r from-pink-100 to-purple-100 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i class="fas fa-gift text-4xl text-pink-400"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-700 mb-2">Belum Ada Promo</h3>
                <p class="text-gray-500">Saat ini belum ada promo yang tersedia. Silakan cek kembali nanti!</p>
            </div>
        </section>

        <!-- Quick Actions -->
        <section class="bg-white/80 backdrop-blur-lg rounded-3xl shadow-xl p-6 border border-pink-100">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-10 h-10 bg-gradient-to-r from-yellow-400 to-orange-500 rounded-full flex items-center justify-center">
                    <i class="fas fa-bolt text-white"></i>
                </div>
                <h2 class="text-xl font-bold text-gray-800">Aksi Cepat</h2>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <a href="/customer/home" class="bg-gradient-to-r from-pink-400 to-purple-500 text-white p-4 rounded-2xl text-center card-hover-effect transition-all duration-300">
                    <i class="fas fa-home text-2xl mb-2"></i>
                    <p class="font-semibold">Beranda</p>
                    <p class="text-xs opacity-90">Kembali ke beranda</p>
                </a>
                <a href="/customer/history" class="bg-gradient-to-r from-blue-400 to-indigo-500 text-white p-4 rounded-2xl text-center card-hover-effect transition-all duration-300">
                    <i class="fas fa-history text-2xl mb-2"></i>
                    <p class="font-semibold">Riwayat</p>
                    <p class="text-xs opacity-90">Lihat transaksi</p>
                </a>
            </div>
        </section>
    </div>

    <!-- Enhanced Modal untuk preview -->
    <div id="imageModal" class="fixed inset-0 bg-black/80 backdrop-blur-sm flex items-center justify-center z-50 hidden">
        <div class="relative w-full h-full flex items-center justify-center p-4">
            <img id="modalImage" class="max-w-full max-h-full rounded-2xl shadow-2xl object-contain" />
            <button onclick="closeModal()" class="absolute top-4 right-4 w-12 h-12 bg-white/20 backdrop-blur-sm rounded-full flex items-center justify-center text-white hover:bg-white/30 transition-all duration-300 hover:scale-110">
                <i class="fas fa-times text-lg"></i>
            </button>
            <div class="absolute bottom-4 left-1/2 transform -translate-x-1/2 bg-white/20 backdrop-blur-sm rounded-full px-4 py-2 text-white text-sm">
                <i class="fas fa-expand-arrows-alt mr-2"></i>
                Tap untuk memperbesar
            </div>
        </div>
    </div>
    <script type="module">
        import {
            imagePromoHandler
        } from "/src/js/index/handler/promoHandler.js"
        imagePromoHandler();
    </script>

    <script>
        // Close modal function
        function closeModal() {
            document.getElementById('imageModal').classList.add('hidden');
        }

        // Handle page refresh on back navigation
        window.addEventListener("pageshow", function(event) {
            if (event.persisted || (window.performance && window.performance.navigation.type === 2)) {
                location.reload();
            }
        });

        // Add smooth scrolling
        document.documentElement.style.scrollBehavior = 'smooth';

        // Hide loading state when content loads
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(() => {
                const loadingState = document.getElementById('loading-state');
                if (loadingState) {
                    loadingState.style.display = 'none';
                }
            }, 1000);
        });

        // Add card animations
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.card-hover-effect');
            cards.forEach((card, index) => {
                card.style.animationDelay = `${index * 0.1}s`;
                card.classList.add('animate-fade-in');
            });
        });
    </script>
</body>

</html>