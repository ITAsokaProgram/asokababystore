<?php
require_once __DIR__ . '/../aa_kon_sett.php';
require_once __DIR__ . '/../src/auth/middleware_login.php';
require_once __DIR__ . '/../src/helpers/visitor_helper.php';

$user = getAuthenticatedUser();

logVisitor($conn, $user->id, "Tukar Poin");
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tukar Poin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
        integrity="sha512-..." crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="icon" type="image/png" href="/images/logo1.png">
    <link rel="stylesheet" href="/src/output2.css">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

        body {
            font-family: 'Inter', sans-serif;
            -webkit-tap-highlight-color: transparent;
        }

        .reward-card {
            transition: all 0.2s ease;
            -webkit-tap-highlight-color: transparent;
        }

        .reward-card:active {
            transform: scale(0.98);
        }

        .insufficient-points {
            opacity: 0.6;
            filter: grayscale(30%);
        }

        .pulse-animation {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }

        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .success-animation {
            animation: bounceIn 0.4s ease-out;
        }

        @keyframes bounceIn {
            0% {
                transform: scale(0.8);
                opacity: 0;
            }

            100% {
                transform: scale(1);
                opacity: 1;
            }
        }

        /* Mobile optimizations */
        .mobile-scroll {
            scroll-behavior: smooth;
            -webkit-overflow-scrolling: touch;
        }

        .filter-btn {
            touch-action: manipulation;
            -webkit-tap-highlight-color: transparent;
        }

        .exchange-btn {
            touch-action: manipulation;
            -webkit-tap-highlight-color: transparent;
            min-height: 48px;
        }

        /* Safe area for notched phones */
        .safe-area-top {
            padding-top: env(safe-area-inset-top);
        }

        .safe-area-bottom {
            padding-bottom: env(safe-area-inset-bottom);
        }

        /* Floating Action Button */
        .floating-btn {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 40;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .floating-btn:active {
            transform: scale(0.95);
        }

        .floating-btn.hidden-btn {
            opacity: 0;
            transform: translateY(100px);
            pointer-events: none;
        }

        /* History Modal Animations */
        .slide-up {
            animation: slideUp 0.3s ease-out;
        }

        @keyframes slideUp {
            from {
                transform: translateY(100%);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
    </style>
</head>

<body class="bg-gray-50 min-h-screen">
    <!-- Header - Mobile Optimized -->
    <header class="gradient-bg text-white safe-area-top">
        <div class="p-4 pb-6">
            <!-- Title and Points -->
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-3">
                    <button id="goBackBtn" class="w-8 h-8 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center hover:bg-white/30 transition-colors">
                        <span class="text-lg font-bold">←</span>
                    </button>
                    <div class="flex items-center gap-2">
                        <span class="text-2xl"><i class="fa-solid fa-gift text-amber-500"></i></span>
                        <h1 class="text-xl font-bold">Tukar Poin</h1>
                    </div>
                </div>
                <div class="text-right">
                    <p class="text-blue-100 text-xs">Poin Anda</p>
                    <p class="text-xl font-bold" id="userPoints"></p>
                </div>
            </div>

            <!-- Points Balance Card - Mobile -->
            <div class="bg-white/20 backdrop-blur-sm rounded-xl p-4 border border-white/30">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-blue-100 text-xs mb-1">Total Poin Anda</p>
                        <p class="text-2xl font-bold" id="pointsDisplay"></p>
                        <p class="text-blue-100 text-xs mt-1">Tukar dengan hadiah menarik!</p>
                    </div>
                    <div class="text-4xl pulse-animation"><i class="fas fa-coins text-yellow-500 mr-1"></i></div>
                </div>
            </div>
        </div>
    </header>

    <!-- Filter Location - Improved UI -->
    <div class="px-4 py-4 bg-white border-b shadow-sm rounded-xl">
        <div class="mb-2">
            <h3 class="text-base font-semibold text-gray-800 mb-3 flex items-center gap-1">
                 <span><i class="fa-solid fa-filter text-blue-400 mr-2"></i>Filter Hadiah</span>
            </h3>

            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:gap-4">
                <!-- Button: Daerah Saya -->
                <button
                    class="filter-btn bg-blue-600 text-white px-5 py-2.5 rounded-full font-medium text-sm shadow hover:bg-blue-700 transition w-full sm:w-auto"
                    data-location="member-area">
                    Semua Hadiah
                </button>

                <!-- Select: Cabang Lain -->
                <div class="relative w-full sm:w-auto">
                    <select
                        id="branch-selector"
                        class="appearance-none bg-gray-100 text-gray-800 px-5 py-2.5 rounded-full font-medium text-xs w-full border border-gray-300 shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-blue-500 transition">
                        <option disabled selected value="">📌 Pilih Cabang Lain</option>
                    </select>
                    <!-- Dropdown Icon -->
                    <div class="absolute inset-y-0 right-4 flex items-center pointer-events-none text-gray-500">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- Rewards Grid - Mobile Layout -->
    <div class="p-4">
        <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-6 gap-4" id="rewardsGrid">
            <!-- Reward cards will be generated here -->
        </div>
    </div>

    <!-- Exchange History - Mobile (Now Hidden by Default) -->
    <div class="px-4 pb-20 safe-area-bottom hidden" id="originalHistory">
        <div class="flex items-center gap-2 mb-4">
            <span class="text-xl">📋</span>
            <h2 class="text-lg font-bold text-gray-800">Riwayat Penukaran</h2>
        </div>
        <div class="bg-white rounded-xl shadow-sm border" id="exchangeHistory">
            <div class="p-6 text-center text-gray-500">
                <div class="text-4xl mb-2">📋</div>
                <p class="text-sm">Belum ada riwayat penukaran</p>
            </div>
        </div>
    </div>

    <!-- Floating History Button -->
    <button id="historyFloatingBtn" class="floating-btn bg-blue-600 text-white w-14 h-14 rounded-full flex items-center justify-center font-bold text-sm shadow-lg hover:bg-blue-700 transition-colors">
        <div class="text-center">
            <div class="text-lg">📋</div>
        </div>
    </button>

    <!-- History Modal - Full Screen -->
    <div id="historyModal" class="fixed inset-0 bg-white z-50 hidden flex flex-col">
        <!-- Header -->
        <div class="gradient-bg text-white safe-area-top flex-shrink-0">
            <div class="p-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <button id="closeHistoryModalBtn" class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center">
                        ←
                    </button>
                    <h2 class="text-lg font-bold">Riwayat Penukaran</h2>
                </div>
                <div class="text-right">
                    <p class="text-blue-100 text-xs">Total Transaksi</p>
                    <p class="text-lg font-bold" id="totalTransactions">0</p>
                </div>
            </div>
        </div>

        <!-- History Content -->
        <div class="flex-1 min-h-0 safe-area-bottom" id="historyModalContent">
            <!-- Content will be populated here -->
        </div>
    </div>

    <!-- Reward Preview Modal -->
    <div id="rewardPreviewModal" class="fixed inset-0 bg-black/70 backdrop-blur-sm z-50 hidden items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-sm w-full text-center">
            <div class="relative">
                <!-- Close button -->
                <button id="closeRewardPreviewBtn" class="absolute top-3 right-3 w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center text-gray-600 hover:bg-gray-200">
                    ✕
                </button>

                <!-- Reward Image -->
                <div class="p-6 ml-5 pb-7">
                    <img id="previewImage" src="" alt="Reward Preview" class="w-72 h-72 mt-5 object-cover rounded-xl mb-4">
                    <h3 class="text-lg font-bold text-gray-800 mb-2" id="previewTitle">Nama Hadiah</h3>

                    <!-- Price and Stock -->
                    <div class="bg-gray-50 rounded-xl p-3 mb-4">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm text-gray-600">Poin:</span>
                            <span class="font-bold text-blue-600 flex items-center gap-1">
                                <span class="text-yellow-500"><i class="fas fa-coins text-yellow-500 mr-1"></i></span>
                                <span id="previewPoints">0</span>
                            </span>
                        </div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm text-gray-600">Stok:</span>
                            <span class="text-sm font-medium" id="previewStock">0 tersisa</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Cabang:</span>
                            <span class="text-sm font-medium" id="previewStock">Asoka Baby Store Condet</span>
                        </div>
                    </div>

                    <!-- Action Button -->
                    <button id="exchangeFromPreviewBtn" class="w-full py-3 rounded-xl font-medium text-sm transition-colors">
                        🎁 Tukar Sekarang
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Confirmation Modal - Mobile -->
    <div id="confirmModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden items-center justify-center p-4">
        <div class="bg-white rounded-2xl p-6 max-w-sm w-full text-center">
            <div class="text-5xl mb-3">🤔</div>
            <h3 class="text-xl font-bold text-gray-800 mb-2">Konfirmasi Penukaran</h3>
            <div class="bg-gray-50 rounded-xl p-4 mb-4">
                <div class="flex items-center gap-3 mb-2">
                    <span class="text-2xl" id="confirmEmoji">🎁</span>
                    <div class="text-left">
                        <p class="font-medium text-gray-800 text-sm" id="confirmRewardName">Nama Hadiah</p>
                    </div>
                </div>
                <div class="flex items-center justify-between pt-2 border-t border-gray-200">
                    <span class="text-xs text-gray-500">Poin:</span>
                    <span class="font-bold text-red-500 flex items-center gap-1 text-sm">
                        <span class="text-yellow-500"><i class="fas fa-coins text-yellow-500 mr-1"></i></span>
                        <span id="confirmPoints">0</span>
                    </span>
                </div>
            </div>
            <p class="text-gray-600 mb-6 text-sm">Apakah Anda yakin ingin menukar poin dengan hadiah ini?</p>
            <div class="flex gap-3">
                <button id="closeConfirmModalBtn" class="flex-1 bg-gray-200 text-gray-700 py-3 rounded-xl font-medium transition-colors">
                    Batal
                </button>
                <button id="confirmExchangeBtn" class="flex-1 bg-blue-600 text-white py-3 rounded-xl font-medium hover:bg-blue-700 transition-colors exchange-btn">
                    Ya, Tukar
                </button>
            </div>
        </div>
    </div>

    <!-- Success Modal with Code - Mobile -->
    <div id="successModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden items-center justify-center p-4">
        <div class="bg-white rounded-2xl p-6 max-w-sm w-full text-center success-animation">
            <div class="text-5xl mb-3">🎉</div>
            <h3 class="text-xl font-bold text-gray-800 mb-2">Penukaran Berhasil!</h3>
            <p class="text-gray-600 mb-4 text-sm" id="successMessage">Selamat! Penukaran kode hadiah berhasil dilakukan.</p>

            <!-- Reward Code Section -->
            <div class="bg-blue-50 border-2 border-dashed border-blue-300 rounded-xl p-4 mb-4">
                <p class="text-xs text-blue-600 mb-2 font-medium">📍 KODE PENGAMBILAN HADIAH</p>
                <div class="bg-white rounded-lg p-3 mb-3">
                    <p class="text-md font-mono text-blue-600 tracking-widest" id="rewardCode"></p>
                    <img id="qrRewardCode" src="" alt="QR Code" class="mx-auto w-32 h-32" />
                </div>
                <p class="text-xs text-blue-700 leading-relaxed">
                    Silakan tunjukkan kode ini ke cabang yang menyediakan hadiah untuk mengambil reward Anda dalam batas waktu 6 jam dari sekarang.
                </p>
            </div>

            <div class="flex gap-2">
                <button id="copyCodeBtn" class="flex-1 bg-blue-100 text-blue-600 py-2.5 rounded-lg font-medium text-sm transition-colors exchange-btn">
                    📋 Salin Kode
                </button>
                <button id="closeModalBtn" class="flex-1 bg-green-500 text-white py-2.5 rounded-lg font-medium text-sm hover:bg-green-600 transition-colors exchange-btn">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    <!-- Insufficient Points Modal - Mobile -->
    <div id="errorModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden items-center justify-center p-4">
        <div class="bg-white rounded-2xl p-6 max-w-sm w-full text-center">
            <div class="text-5xl mb-3">😔</div>
            <h3 class="text-xl font-bold text-gray-800 mb-2">Poin Tidak Cukup</h3>
            <p class="text-gray-600 mb-4 text-sm" id="errorMessage">Maaf, poin Anda tidak mencukupi untuk hadiah ini.</p>
            <button id="closeErrorModalBtn" class="w-full bg-red-500 text-white py-3 rounded-xl font-medium hover:bg-red-600 transition-colors exchange-btn">
                Tutup
            </button>
        </div>
    </div>

    <script type="module" src="/src/js/index/poin/index.js"></script>
    <script type="module">
        import {
            cabangName
        } from "../src/js/kode_cabang/cabang_name.js";
        await cabangName('branch-selector');
    </script>
</body>

</html>