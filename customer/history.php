<?php
require_once __DIR__ . '/../aa_kon_sett.php';
require_once __DIR__ . '/../src/auth/middleware_login.php';
require_once __DIR__ . '/../src/helpers/visitor_helper.php';

$user = getAuthenticatedUser();

logVisitor($conn, $user->id, "History Transaksi");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Riwayat Transaksi - Asoka Baby Store</title>

    <!-- Tailwind CSS CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
        integrity="sha512-..." crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="icon" type="image/png" href="/images/logo1.png">
    <link rel="stylesheet" href="/src/output2.css">
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

        /* Transaction Status Colors */
        .status-success {
            background: linear-gradient(135deg, #10b981, #059669);
        }

        .status-pending {
            background: linear-gradient(135deg, #f59e0b, #d97706);
        }

        .status-failed {
            background: linear-gradient(135deg, #ef4444, #dc2626);
        }
    </style>
</head>

<body class="bg-gradient-to-br from-pink-50 via-white to-purple-50 min-h-screen text-gray-800">
    <div class="max-w-4xl mx-auto p-4 pb-32 space-y-6">

        <!-- Enhanced Back Button -->
        <div class="mb-4">
            <button onclick="history.back()" class="inline-flex items-center gap-3 px-4 py-2 bg-white/80 backdrop-blur-lg rounded-xl shadow-lg border border-pink-100 text-pink-600 hover:bg-pink-50 hover:scale-105 transition-all duration-300 group">
                <i class="fas fa-arrow-left group-hover:-translate-x-1 transition-transform duration-300"></i>
                <span class="font-medium">Kembali</span>
            </button>
        </div>

        <!-- Enhanced Header -->
        <header class="bg-gradient-to-r from-blue-500 to-indigo-600 rounded-3xl shadow-xl p-8 text-white relative overflow-hidden">
            <div class="absolute inset-0 bg-black/10"></div>
            <div class="relative z-10">
                <div class="flex items-center gap-4 mb-4">
                    <div class="w-16 h-14 p-4 bg-white/20 backdrop-blur-sm rounded-full flex items-center justify-center float-animation">
                        <i class="fas fa-receipt text-3xl"></i>
                    </div>
                    <div>
                        <h1 class="text-3xl md:text-4xl font-bold mb-2">Riwayat Transaksi</h1>
                        <p class="text-blue-100 text-lg">Lihat semua transaksi Anda di Asoka Baby Store</p>
                    </div>
                </div>
                <div class="flex items-center gap-6 text-sm">
                    <div class="flex items-center gap-2">
                        <i class="fas fa-clock text-blue-100"></i>
                        <span class="text-blue-100">Riwayat Lengkap</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <i class="fas fa-shield-alt text-blue-100"></i>
                        <span class="text-blue-100">Data Aman</span>
                    </div>
                </div>
            </div>

            <!-- Decorative Elements -->
            <div class="absolute top-6 right-6 w-20 h-20 bg-white/10 rounded-full"></div>
            <div class="absolute bottom-6 right-12 w-12 h-12 bg-white/10 rounded-full"></div>
            <div class="absolute top-1/2 right-8 w-8 h-8 bg-white/10 rounded-full"></div>
        </header>

        <!-- Enhanced Transaction Section -->
        <section class="bg-white/80 backdrop-blur-lg rounded-3xl shadow-xl border border-pink-100 overflow-hidden">
            <!-- Section Header -->
            <div class="bg-gradient-to-r from-gray-50 to-blue-50 p-6 border-b border-gray-100">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-indigo-600 rounded-full flex items-center justify-center">
                            <i class="fas fa-history text-white"></i>
                        </div>
                        <h2 class="text-xl font-bold text-gray-800">Daftar Transaksi</h2>
                    </div>
                    <div class="text-sm text-gray-500 bg-white px-3 py-1 rounded-full border">
                        <span id="transaction-count"></span> transaksi
                    </div>
                </div>
            </div>

            <!-- Transaction Container -->
            <div class="p-6">
                <div class="space-y-4" id="transaksi-container">
                    <!-- Loading State -->
                    <div id="transaksi-loader" class="space-y-4">
                        <div class="bg-gray-50 rounded-2xl p-6 shimmer">
                            <div class="flex items-center gap-4 mb-4">
                                <div class="w-12 h-12 bg-gray-200 rounded-full"></div>
                                <div class="flex-1">
                                    <div class="h-4 bg-gray-200 rounded mb-2"></div>
                                    <div class="h-3 bg-gray-200 rounded w-1/2"></div>
                                </div>
                            </div>
                            <div class="space-y-2">
                                <div class="h-3 bg-gray-200 rounded"></div>
                                <div class="h-3 bg-gray-200 rounded w-3/4"></div>
                            </div>
                        </div>
                        <div class="bg-gray-50 rounded-2xl p-6 shimmer">
                            <div class="flex items-center gap-4 mb-4">
                                <div class="w-12 h-12 bg-gray-200 rounded-full"></div>
                                <div class="flex-1">
                                    <div class="h-4 bg-gray-200 rounded mb-2"></div>
                                    <div class="h-3 bg-gray-200 rounded w-1/2"></div>
                                </div>
                            </div>
                            <div class="space-y-2">
                                <div class="h-3 bg-gray-200 rounded"></div>
                                <div class="h-3 bg-gray-200 rounded w-3/4"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- No Transactions Message -->
                <div id="no-transactions" class="hidden text-center py-12">
                    <div class="w-24 h-24 bg-gradient-to-r from-gray-100 to-blue-100 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-receipt text-4xl text-gray-400"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-700 mb-2">Belum Ada Transaksi</h3>
                    <p class="text-gray-500 mb-6">Anda belum memiliki riwayat transaksi di Asoka Baby Store</p>
                    <a href="/customer/home" class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-pink-500 to-purple-600 text-white rounded-xl font-semibold hover:from-pink-600 hover:to-purple-700 transition-all duration-300 hover:scale-105 shadow-lg">
                        <i class="fas fa-home"></i>
                        Kembali ke Beranda
                    </a>
                </div>
            </div>
        </section>

        <!-- Quick Actions -->
        <section class="bg-white/80 backdrop-blur-lg rounded-3xl shadow-xl p-6 border border-pink-100">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-10 h-10 bg-gradient-to-r from-green-400 to-emerald-500 rounded-full flex items-center justify-center">
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
                <a href="/customer/promo" class="bg-gradient-to-r from-yellow-400 to-orange-500 text-white p-4 rounded-2xl text-center card-hover-effect transition-all duration-300">
                    <i class="fas fa-tags text-2xl mb-2"></i>
                    <p class="font-semibold">Promo</p>
                    <p class="text-xs opacity-90">Lihat promo tersedia</p>
                </a>
            </div>
        </section>
    </div>

    <!-- Review Modal -->
    <?php include "../src/fitur/pubs/review/view.php" ?>
    <div id="chatModalCust" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl relative animate-fade-in-up max-h-[90vh] flex flex-col">
            <div class="bg-gradient-to-r from-pink-500 to-purple-600 rounded-t-2xl p-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center">
                            <i class="fas fa-comments text-white text-lg"></i>
                        </div>
                        <div>
                            <h2 class="text-lg font-bold text-white">Percakapan dengan Admin</h2>
                            <p class="text-white/80 text-xs" id="chatBonCust">-</p>
                        </div>
                    </div>
                    <button type="button" id="closeChatModalCust" class="text-white/80 hover:text-white">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
            </div>

            <div id="chatScrollContainerCust" class="p-4 space-y-4 overflow-y-auto flex-1">
                <div id="chatConversationMessagesCust" class="space-y-3 p-2">
                </div>
            </div>
             <div class="p-4 border-t border-gray-200 bg-gray-50 rounded-b-2xl">
                <div id="chatResolvedMessageCust" class="hidden text-center text-sm text-gray-600 bg-yellow-100 p-3 rounded-lg mb-3">
                    <i class="fas fa-check-circle text-green-500 mr-2"></i>
                    Kasus ini telah diselesaikan. Percakapan ditutup.
                </div>

                <div id="imagePreviewContainerCust" class="hidden relative w-32 mb-3">
                    <img id="imagePreviewCust" src="" alt="Preview" class="rounded-lg w-full object-cover">
                    <button id="removeImagePreviewCust" type="button" class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center shadow-md">
                        <i class="fas fa-times text-sm"></i>
                    </button>
                </div>

                <div id="chatInputContainerCust" class="flex items-start space-x-3">
                    <button type="button" id="attachFileBtnCust"
                        class="px-3 py-2 bg-gray-200 text-gray-600 rounded-lg hover:bg-gray-300 transition-all shadow-sm flex items-center h-full mt-2">
                        <i class="fas fa-paperclip"></i>
                    </button>
                    <input type="file" id="mediaInputCust" class="hidden" accept="image/*">

                    <div class="flex-1">
                        <textarea id="chatMessageInputCust" rows="2" placeholder="Ketik balasan Anda..."
                            class="block w-full rounded-lg border border-gray-300 px-4 py-3 shadow-sm text-sm resize-none"></textarea>
                    </div>
                    <button type="button" id="sendChatMessageBtnCust"
                        class="px-4 py-2 bg-gradient-to-r from-pink-500 to-purple-600 text-white rounded-lg hover:opacity-90 transition-all shadow-sm flex items-center h-full mt-2">
                        <i class="fas fa-paper-plane mr-2"></i> Kirim
                    </button>
                </div>
            </div>
        </div>
    </div>


    <script type="module">
        import {
            displayTransaksi
        } from "/src/js/index/handler/transaksiHandler.js";
        displayTransaksi()
    </script>

    <script>
        window.addEventListener("pageshow", function(event) {
            if (event.persisted || (window.performance && window.performance.navigation.type === 2)) {
                location.reload();
            }
        });

        document.documentElement.style.scrollBehavior = 'smooth';

        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(() => {
                const loadingState = document.getElementById('transaksi-loader');
                if (loadingState) {
                    loadingState.style.display = 'none';
                }
            }, 1000);
        });

        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.card-hover-effect');
            cards.forEach((card, index) => {
                card.style.animationDelay = `${index * 0.1}s`;
                card.classList.add('animate-fade-in');
            });
        });


        function toggleNoTransactions(show) {
            const noTransactions = document.getElementById('no-transactions');
            const container = document.getElementById('transaksi-container');

            if (show) {
                noTransactions.classList.remove('hidden');
                container.classList.add('hidden');
            } else {
                noTransactions.classList.add('hidden');
                container.classList.remove('hidden');
            }
        }
    </script>
</body>

</html>