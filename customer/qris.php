<?php
require_once __DIR__ . '/../aa_kon_sett.php';
require_once __DIR__ . '/../src/auth/middleware_login.php';
require_once __DIR__ . '/../src/helpers/visitor_helper.php';

$user = getAuthenticatedUser();

logVisitor($conn, $user->id, "QR CODE");
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Member</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
        integrity="sha512-..." crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="icon" type="image/png" href="/images/logo1.png">
    <link rel="stylesheet" href="/src/output2.css">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
    <style>
        .qr-shimmer {
            background: linear-gradient(90deg, #f0f0f0 0%, #e0e0e0 50%, #f0f0f0 100%);
            background-size: 200% 100%;
            animation: shimmer 1.5s infinite;
        }

        @keyframes shimmer {
            0% {
                background-position: -200% 0;
            }

            100% {
                background-position: 200% 0;
            }
        }

        .fade-in {
            animation: fadeIn 0.5s ease-in;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .pulse-ring {
            animation: pulse-ring 2s infinite;
        }

        @keyframes pulse-ring {
            0% {
                transform: scale(1);
                opacity: 1;
            }

            50% {
                transform: scale(1.1);
                opacity: 0.7;
            }

            100% {
                transform: scale(1);
                opacity: 1;
            }
        }
    </style>
</head>

<body class="bg-gradient-to-br from-pink-50 to-purple-50 min-h-screen">
    <!-- Header -->
    <header class="bg-white shadow-sm sticky top-0 z-40">
        <div class="px-4 py-3 flex items-center justify-between">
            <button onclick="history.back()" class="p-2 rounded-full hover:bg-gray-100">
                <i class="fas fa-arrow-left text-gray-600 text-xl"></i>
            </button>
            <h1 class="text-lg font-semibold text-gray-800">Kode Member</h1>
            <div class="w-10"></div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="px-4 py-6 pb-24">
        <!-- QR Code Section -->
        <div class="bg-white rounded-2xl shadow-lg p-6 mb-6 fade-in">
            <div class="text-center">
                <h2 class="text-xl font-bold text-gray-800 mb-2">Scan QR Code</h2>
                <p class="text-gray-600 text-sm mb-6">Gunakan QR Code untuk melakukan pembayaran member</p>

                <!-- QR Code Container -->
                <div class="relative mx-auto w-64 h-64 mb-6">
                    <div class="absolute inset-0 bg-gradient-to-r from-pink-400 to-purple-400 rounded-2xl pulse-ring opacity-20"></div>
                    <div class="relative bg-white p-4 rounded-2xl shadow-inner">
                        <!-- Loading State -->
                        <div id="qr-loading" class="w-56 h-56 qr-shimmer rounded-xl"></div>

                        <!-- QR Code (Hidden initially) -->
                        <div id="qr-code" class="hidden">
                            <img id="qrRewardCode" src="" alt="QR Code" class="mx-auto w-56 h-56" />
                        </div>
                    </div>
                    <div class="font-mono text-md mt-3"><span id="qr-phone-number"></span></div>
                </div>
            </div>
        </div>

        <!-- Payment Methods -->
        <!-- <div class="bg-white rounded-2xl shadow-lg p-6 mb-6 fade-in">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">
                <i class="fas fa-mobile-alt text-pink-500 mr-2"></i>
                Aplikasi yang Didukung
            </h3>
            <div class="grid grid-cols-3 gap-4">
                <div class="text-center p-3 bg-gray-50 rounded-xl hover:bg-green-50 transition-colors duration-200">
                    <div class="w-12 h-12 bg-white rounded-full flex items-center justify-center mx-auto mb-2 shadow-sm border border-gray-100">
                        <img src="https://upload.wikimedia.org/wikipedia/commons/8/86/Gopay_logo.svg" alt="GoPay" class="w-8 h-8 object-contain">
                    </div>
                    <span class="text-xs text-gray-600 font-medium">GoPay</span>
                </div>
                <div class="text-center p-3 bg-gray-50 rounded-xl hover:bg-purple-50 transition-colors duration-200">
                    <div class="w-12 h-12 bg-white rounded-full flex items-center justify-center mx-auto mb-2 shadow-sm border border-gray-100">
                        <img src="https://upload.wikimedia.org/wikipedia/commons/e/eb/Logo_ovo_purple.svg" alt="OVO" class="w-8 h-8 object-contain">
                    </div>
                    <span class="text-xs text-gray-600 font-medium">OVO</span>
                </div>
                <div class="text-center p-3 bg-gray-50 rounded-xl hover:bg-blue-50 transition-colors duration-200">
                    <div class="w-12 h-12 bg-white rounded-full flex items-center justify-center mx-auto mb-2 shadow-sm border border-gray-100">
                        <img src="https://upload.wikimedia.org/wikipedia/commons/7/72/Logo_dana_blue.svg" alt="DANA" class="w-8 h-8 object-contain">
                    </div>
                    <span class="text-xs text-gray-600 font-medium">DANA</span>
                </div>
                <div class="text-center p-3 bg-gray-50 rounded-xl hover:bg-red-50 transition-colors duration-200">
                    <div class="w-12 h-12 bg-white rounded-full flex items-center justify-center mx-auto mb-2 shadow-sm border border-gray-100">
                        <img src="https://upload.wikimedia.org/wikipedia/commons/8/85/LinkAja.svg" alt="LinkAja" class="w-8 h-8 object-contain">
                    </div>
                    <span class="text-xs text-gray-600 font-medium">LinkAja</span>
                </div>
                <div class="text-center p-3 bg-gray-50 rounded-xl hover:bg-orange-50 transition-colors duration-200">
                    <div class="w-12 h-12 bg-white rounded-full flex items-center justify-center mx-auto mb-2 shadow-sm border border-gray-100">
                        <img src="https://upload.wikimedia.org/wikipedia/commons/f/fe/Shopee.svg" alt="ShopeePay" class="w-7 h-7 object-contain">
                    </div>
                    <span class="text-xs text-gray-600 font-medium">ShopeePay</span>
                </div>
                <div class="text-center p-3 bg-gray-50 rounded-xl hover:bg-gray-100 transition-colors duration-200">
                    <div class="w-12 h-12 bg-white rounded-full flex items-center justify-center mx-auto mb-2 shadow-sm border border-gray-100">
                        <div class="flex space-x-0.5">
                            <div class="w-1.5 h-1.5 bg-gray-400 rounded-full"></div>
                            <div class="w-1.5 h-1.5 bg-gray-400 rounded-full"></div>
                            <div class="w-1.5 h-1.5 bg-gray-400 rounded-full"></div>
                        </div>
                    </div>
                    <span class="text-xs text-gray-600 font-medium">Lainnya</span>
                </div>
            </div>
        </div> -->

        <!-- Instructions -->
        <!-- <div class="bg-white rounded-2xl shadow-lg p-6 fade-in">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">
                <i class="fas fa-info-circle text-blue-500 mr-2"></i>
                Cara Pembayaran
            </h3>
            <div class="space-y-3">
                <div class="flex items-start">
                    <div class="w-6 h-6 bg-pink-500 text-white rounded-full flex items-center justify-center text-xs font-bold mr-3 mt-0.5 flex-shrink-0">1</div>
                    <p class="text-gray-600 text-sm">Buka aplikasi e-wallet atau mobile banking favorit Anda</p>
                </div>
                <div class="flex items-start">
                    <div class="w-6 h-6 bg-pink-500 text-white rounded-full flex items-center justify-center text-xs font-bold mr-3 mt-0.5 flex-shrink-0">2</div>
                    <p class="text-gray-600 text-sm">Pilih menu "Scan QR" atau "Bayar dengan QR"</p>
                </div>
                <div class="flex items-start">
                    <div class="w-6 h-6 bg-pink-500 text-white rounded-full flex items-center justify-center text-xs font-bold mr-3 mt-0.5 flex-shrink-0">3</div>
                    <p class="text-gray-600 text-sm">Arahkan kamera ke QR code di atas</p>
                </div>
                <div class="flex items-start">
                    <div class="w-6 h-6 bg-pink-500 text-white rounded-full flex items-center justify-center text-xs font-bold mr-3 mt-0.5 flex-shrink-0">4</div>
                    <p class="text-gray-600 text-sm">Konfirmasi pembayaran sesuai nominal yang tertera</p>
                </div>
            </div>
        </div> -->

        <!-- Action Buttons -->
        <div class="mt-6 space-y-3">
            <!-- <button onclick="generateNewQR()" class="w-full bg-gradient-to-r from-pink-500 to-purple-500 text-white py-3 rounded-xl font-semibold hover:from-pink-600 hover:to-purple-600 transition-all duration-200 transform hover:scale-105">
                <i class="fas fa-sync-alt mr-2"></i>
                Generate QR Baru
            </button> -->

            <button onclick="shareQR()" class="w-full bg-white border-2 border-pink-500 text-pink-500 py-3 rounded-xl font-semibold hover:bg-pink-50 transition-all duration-200">
                <i class="fas fa-share-alt mr-2"></i>
                Bagikan QR Code
            </button>
        </div>
    </main>

    <!-- QR Code Generator Script -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrious/4.0.2/qrious.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            generateQRCode();
        });

        function generateQRCode() {
            // Show loading state
            document.getElementById('qr-loading').classList.remove('hidden');
            document.getElementById('qr-code').classList.add('hidden');

            // Simulate loading delay
            setTimeout(async () => {
                const canvas = document.getElementById('qr-canvas');
                const hp = new URLSearchParams(window.location.search).get('number');
                const phoneNumberElement = document.getElementById('qr-phone-number');

                const img = document.getElementById('qrRewardCode');
                img.src = `/src/api/qr/code.php?number=${hp}`;
                fetch(`/src/api/qr/code.php?info=1&number=${hp}`)
                    .then(res => res.json())
                    .then(data => {
                        // Misal tampilkan ke elemen HTML
                        phoneNumberElement.textContent = data.encrypted || 'Tidak tersedia';
                    });
                // Hide loading, show QR code
                document.getElementById('qr-loading').classList.add('hidden');
                document.getElementById('qr-code').classList.remove('hidden');
            }, 1500);
        }

        function generateNewQR() {
            generateQRCode();

            // Show success message
            const button = event.target;
            const originalText = button.innerHTML;
            button.innerHTML = '<i class="fas fa-check mr-2"></i>QR Code Diperbarui!';
            button.classList.add('bg-green-500');

            setTimeout(() => {
                button.innerHTML = originalText;
                button.classList.remove('bg-green-500');
            }, 2000);
        }

        function shareQR() {
            if (navigator.share) {
                navigator.share({
                    title: 'QR Code Member',
                    text: 'Scan QR code ini menggunakan member',
                    url: window.location.href
                });
            } else {
                // Fallback for browsers that don't support Web Share API
                navigator.clipboard.writeText(window.location.href).then(() => {
                    alert('Link QR code telah disalin ke clipboard!');
                });
            }
        }

        // Auto refresh QR code every 5 minutes for security
        setInterval(generateQRCode(), 300000);
    </script>
</body>

</html>