<?php
require_once __DIR__ . '/../aa_kon_sett.php';
require_once __DIR__ . '/../src/auth/middleware_login.php';

$token = $_COOKIE['token'];
$userId = null;
$ip = $_SERVER['REMOTE_ADDR'];
$ua = $_SERVER['HTTP_USER_AGENT'];
$page = $_SERVER['REQUEST_URI'];
$pageName = "Dashboard Customer";
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
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <meta name="color-scheme" content="light dark">
    <title>Ringkasan Poin dan Transaksi - Asoka Baby Store</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
        integrity="sha512-..." crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="icon" type="image/png" href="/images/logo1.png">
    <!-- <link rel="stylesheet" href="/src/output2.css"> -->
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">

    <style>
        * {
            font-family: 'Poppins', sans-serif;
        }


        /* Responsive adjustments */
        @media (max-width: 640px) {
            .card-hover-effect {
                padding: 1.25rem;
            }

            .card-hover-effect .grid-cols-2 {
                grid-template-columns: 1fr;
                gap: 1rem;
            }

            .text-4xl {
                font-size: 2.5rem;
            }

            .space-x-4>*+* {
                margin-left: 0.75rem;
            }
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .card-hover-effect {
                padding: 1.25rem;
            }

            .grid-cols-3 {
                grid-template-columns: 1fr;
                gap: 1rem;
            }

            .md\\:col-span-2 {
                grid-column: span 1;
            }

            .flex-items-start {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }
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
            transform: translateY(-2px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
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
    </style>
</head>

<body class="bg-gradient-to-br from-pink-50 via-white to-purple-50 min-h-screen text-gray-800">
    <div class="max-w-4xl mx-auto p-4 pb-20 space-y-6">

        <!-- Unified Header & Member Info Card -->
        <section class="mb-6">
            <div class="relative bg-gradient-to-br from-slate-900 via-purple-900 to-slate-900 rounded-3xl shadow-2xl p-6 text-white overflow-hidden card-hover-effect transition-all duration-500 hover:shadow-purple-500/25 hover:scale-[1.01]">

                <!-- Background Pattern -->
                <div class="absolute inset-0 opacity-10">
                    <div class="absolute top-0 right-0 w-40 h-40 bg-gradient-to-br from-purple-400 to-pink-400 rounded-full -translate-y-20 translate-x-20"></div>
                    <div class="absolute bottom-0 left-0 w-32 h-32 bg-gradient-to-tr from-blue-400 to-purple-400 rounded-full translate-y-16 -translate-x-16"></div>
                    <div class="absolute top-1/2 left-1/2 w-24 h-24 bg-gradient-to-bl from-pink-400 to-purple-400 rounded-full opacity-50 -translate-x-12 -translate-y-12"></div>
                </div>

                <!-- Top Section - Greeting & Status -->
                <div class="relative z-10 flex items-start justify-between mb-8">
                    <div class="flex items-center space-x-4">
                        <!-- Profile Avatar -->
                        <div class="relative">
                            <div class="w-16 h-16 bg-gradient-to-br from-purple-400 to-pink-400 rounded-2xl flex items-center justify-center shadow-xl float-animation">
                                <i class="fas fa-user-circle text-3xl text-white"></i>
                            </div>
                            <!-- Online Indicator -->
                            <div class="absolute -bottom-1 -right-1 w-5 h-5 bg-green-400 rounded-full flex items-center justify-center border-2 border-slate-900">
                                <div class="w-2 h-2 bg-white rounded-full animate-pulse"></div>
                            </div>
                        </div>

                        <!-- Greeting Info -->
                        <div class="space-y-1">
                            <h1 class="text-2xl font-bold bg-gradient-to-r from-white to-purple-200 bg-clip-text text-transparent" id="nameUser">
                                Selamat Datang!
                            </h1>
                            <div class="flex items-center space-x-3">
                                <div class="flex items-center space-x-2">
                                    <div class="w-6 h-6 bg-gradient-to-br from-yellow-400 to-orange-400 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-crown text-white text-xs"></i>
                                    </div>
                                    <span class="text-yellow-200 text-sm font-medium" id="status"></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Member Badge -->
                    <div class="text-right">
                        <div class="inline-flex items-center space-x-2 backdrop-blur-sm rounded-full border border-white/20">
                            <div class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></div>
                        </div>
                    </div>
                </div>

                <!-- Middle Section - Description -->
                <div class="relative z-10 mb-8">
                    <p class="text-purple-200 text-sm leading-relaxed">
                        Terima kasih telah menjadi member setia kami. Nikmati berbagai keuntungan eksklusif dan kumpulkan poin rewards setiap pembelian di
                        <span class="font-semibold text-white">Asoka Baby Store</span>.
                    </p>
                </div>

                <!-- Bottom Section - Points & Actions -->
                <div class="relative z-10 grid grid-cols-1 md:grid-cols-3 gap-6">

                    <!-- Points Display -->
                    <div class="md:col-span-2 space-y-4">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-gradient-to-br from-yellow-400 to-orange-400 rounded-xl flex items-center justify-center shadow-lg">
                                <i class="fas fa-coins text-white text-lg"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-white">Poin Rewards Anda</h3>
                                <p class="text-purple-300 text-sm">Tukarkan dengan berbagai hadiah menarik</p>
                            </div>
                        </div>

                        <div class="bg-white/5 backdrop-blur-sm rounded-2xl p-4 border border-white/10">
                            <div class="flex items-end space-x-4">
                                <div>
                                    <p class="text-2xl font-bold text-white mb-1" id="poinCust"></p>
                                </div>
                                <div class="flex-1">
                                    <div class="text-right text-xs text-purple-300 mb-1">
                                        <span id="periodeBerlaku" class="font-medium text-purple-200"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="space-y-3">
                        <button onclick="window.location='/customer/poin'"
                            class="w-full flex items-center justify-center space-x-2 bg-gradient-to-r from-purple-500 to-pink-500 hover:from-purple-600 hover:to-pink-600 rounded-xl py-3 px-4 transition-all duration-300 hover:scale-105 shadow-lg hover:shadow-xl font-medium">
                            <i class="fas fa-exchange-alt text-white text-sm"></i>
                            <span class="text-white">Tukar Poin</span>
                        </button>

                        <button onclick="window.location='/customer/history_poin'" class="w-full flex items-center justify-center space-x-2 bg-white/10 hover:bg-white/20 backdrop-blur-sm rounded-xl py-3 px-4 transition-all duration-300 hover:scale-105 border border-white/20">
                            <i class="fas fa-history text-purple-200 text-sm"></i>
                            <span class="text-white">Riwayat Poin</span>
                        </button>


                    </div>
                </div>

                <!-- Decorative Bottom Accent -->
                <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-purple-500 via-pink-500 to-orange-500"></div>

                <!-- Shine Effect -->
                <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/3 to-transparent -skew-x-12 translate-x-[-200%] hover:translate-x-[200%] transition-transform duration-1500"></div>
            </div>
        </section>

        <!-- Enhanced Riwayat Transaksi -->
        <section class="bg-white/80 backdrop-blur-lg rounded-3xl shadow-xl p-6 border border-pink-100">
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 p-4 bg-gradient-to-r from-pink-500 to-purple-600 rounded-full flex items-center justify-center">
                        <i class="fas fa-history text-white"></i>
                    </div>
                    <h2 class="text-xl font-bold text-gray-800">Riwayat Transaksi</h2>
                </div>
                <a href="history" class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-pink-500 to-purple-600 text-white rounded-full text-sm font-semibold hover:from-pink-600 hover:to-purple-700 transition-all duration-300 hover:scale-105 shadow-lg">
                    <i class="fas fa-external-link-alt text-xs"></i>
                    Lihat Semua
                </a>
            </div>
            <div class="space-y-3" id="transaksi-container">
                <div id="transaksi-loader" class="text-center py-8">
                    <div class="w-12 h-12 border-4 border-pink-200 border-t-pink-500 rounded-full animate-spin mx-auto mb-4"></div>
                    <p class="text-gray-500 text-sm">Memuat data transaksi...</p>
                </div>
            </div>
        </section>

        <!-- Enhanced Promo Section -->
        <section class="bg-white/80 backdrop-blur-lg rounded-3xl shadow-xl p-6 border border-pink-100">
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 p-4 bg-gradient-to-r from-yellow-400 to-orange-500 rounded-full flex items-center justify-center">
                        <i class="fas fa-gift text-white"></i>
                    </div>
                    <h2 class="text-xl font-bold text-gray-800">
                        Promo Tersedia
                    </h2>
                </div>
                <a href="promo" class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-yellow-400 to-orange-500 text-white rounded-full text-sm font-semibold hover:from-yellow-500 hover:to-orange-600 transition-all duration-300 hover:scale-105 shadow-lg">
                    <i class="fas fa-external-link-alt text-xs"></i>
                    Lihat Semua
                </a>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4" id="promo-container">
                <!-- Promo items will be populated here -->
            </div>
        </section>
    </div>

    <!-- Enhanced Modal untuk preview -->
    <div id="imageModal" class="fixed inset-0 bg-black/80 backdrop-blur-sm flex items-center justify-center z-50 hidden pb-20">
        <div class="relative w-full h-full flex items-center justify-center p-4">
            <img id="modalImage" class="max-w-full max-h-full rounded-2xl shadow-2xl object-contain" />
            <button onclick="closeModal()" class="absolute top-4 right-4 w-10 h-10 bg-white/20 backdrop-blur-sm rounded-full flex items-center justify-center text-white hover:bg-white/30 transition-all duration-300">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>
    <!-- Floating Button Produk + Tooltip -->
    <div id="fab-container" class="fixed bottom-20 right-5 z-50 flex flex-col-reverse items-center gap-4">
    
        <div id="fab-menu" class="hidden flex flex-col-reverse items-center gap-4 transition-all duration-300 ease-in-out">
            
            <div>
                <a href="/customer/asoka_chat"
                class="bg-orange-500 hover:bg-orange-600 text-white rounded-full w-12 h-12 flex items-center justify-center shadow-lg transform transition-transform duration-300 hover:scale-110">
                    <i class="fas fa-headset text-2xl"></i>
                </a>
            </div>

            <div>
                <a href="/customer/produk"
                class="bg-blue-500 hover:bg-blue-600 text-white rounded-full w-12 h-12 flex items-center justify-center shadow-lg transform transition-transform duration-300 hover:scale-110">
                    <i class="fas fa-box-open text-xl"></i>
                </a>
            </div>

        </div>

        <button id="options-fab" 
                class="bg-gradient-to-r from-pink-500 to-purple-600 hover:from-pink-600 hover:to-purple-700 text-white rounded-full w-14 h-14 flex items-center justify-center shadow-xl transform transition-transform duration-300 hover:scale-110 focus:outline-none">
            <i id="fab-icon" class="fas fa-ellipsis-h text-2xl transition-transform duration-300"></i>
        </button>
    </div>
    <!-- Bottom Navigation (reusable) -->
    <?php include "../src/component/bottom_navigation_user.php" ?>
    <?php include "../src/fitur/pubs/review/view.php" ?>

    <script type="module">
        import {
            imagePromoHandlerHome
        } from "/src/js/index/handler/promoHandler.js"
        import {
            statusCustomerHandler
        } from "/src/js/index/handler/statusCustomerHandler.js"
        imagePromoHandlerHome();
        statusCustomerHandler();
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

        // Add loading animation for cards
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.card-hover-effect');
            cards.forEach((card, index) => {
                card.style.animationDelay = `${index * 0.1}s`;
                card.classList.add('animate-fade-in');
            });
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const optionsFab = document.getElementById('options-fab');
            const fabMenu = document.getElementById('fab-menu');
            const fabIcon = document.getElementById('fab-icon');
            const fabContainer = document.getElementById('fab-container');

            // Fungsi untuk membuka/menutup menu
            const toggleMenu = () => {
                fabMenu.classList.toggle('hidden');
                fabMenu.classList.toggle('flex');
                
                // Animasi & perubahan ikon
                fabIcon.classList.toggle('rotate-90');
                if (fabIcon.classList.contains('fa-ellipsis-h')) {
                    fabIcon.classList.remove('fa-ellipsis-h');
                    fabIcon.classList.add('fa-times');
                } else {
                    fabIcon.classList.remove('fa-times');
                    fabIcon.classList.add('fa-ellipsis-h');
                }
            };

            // Event listener untuk tombol utama
            optionsFab.addEventListener('click', (event) => {
                event.stopPropagation(); // Mencegah event 'click' menyebar ke window
                toggleMenu();
            });

            // Event listener untuk menutup menu saat klik di luar area FAB
            window.addEventListener('click', function(e) {
                if (!fabContainer.contains(e.target) && !fabMenu.classList.contains('hidden')) {
                    toggleMenu();
                }
            });
        });
    </script>
</body>

</html>