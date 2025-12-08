<?php
require_once __DIR__ . '/../aa_kon_sett.php';
require_once __DIR__ . '/../src/auth/middleware_login.php';
require_once __DIR__ . '/../src/helpers/visitor_helper.php';

$user = getAuthenticatedUser();

logVisitor($conn, $user->id, "BARCODE MEMBER");
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barcode Member</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
        integrity="sha512-..." crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="icon" type="image/png" href="/images/logo1.png">
    <link rel="stylesheet" href="/src/output2.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap"
        rel="stylesheet">
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
                transform: scale(1.05);
                /* Scale lebih kecil untuk barcode */
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
    <header class="bg-white shadow-sm sticky top-0 z-40">
        <div class="px-4 py-3 flex items-center justify-between">
            <button onclick="history.back()" class="p-2 rounded-full hover:bg-gray-100">
                <i class="fas fa-arrow-left text-gray-600 text-xl"></i>
            </button>
            <h1 class="text-lg font-semibold text-gray-800">Kode Member</h1>
            <div class="w-10"></div>
        </div>
    </header>

    <main class="px-4 py-6 pb-24">
        <div class="bg-white rounded-2xl shadow-lg p-6 mb-6 fade-in">
            <div class="text-center">
                <h2 class="text-xl font-bold text-gray-800 mb-2">Scan Barcode</h2>
                <p class="text-gray-600 text-sm mb-6">Tunjukkan barcode ini kepada kasir</p>

                <div class="relative mx-auto w-full max-w-sm h-40 mb-6">
                    <div
                        class="absolute inset-0 bg-gradient-to-r from-pink-400 to-purple-400 rounded-2xl pulse-ring opacity-20">
                    </div>
                    <div class="relative bg-white p-4 rounded-2xl shadow-inner h-full flex items-center justify-center">

                        <div id="qr-loading" class="w-full h-24 qr-shimmer rounded-xl"></div>

                        <div id="qr-code" class="hidden w-full flex justify-center">
                            <svg id="barcode"></svg>
                        </div>
                    </div>
                </div>
                <div class="font-mono text-lg font-bold text-gray-700 mt-2 tracking-wider" id="text-display-number">
                </div>
            </div>
        </div>

        <div class="mt-6 space-y-3">
            <button onclick="shareQR()"
                class="w-full bg-white border-2 border-pink-500 text-pink-500 py-3 rounded-xl font-semibold hover:bg-pink-50 transition-all duration-200">
                <i class="fas fa-share-alt mr-2"></i>
                Bagikan Barcode
            </button>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            generateBarcodeCode();
        });

        function generateBarcodeCode() {
            // Show loading state
            document.getElementById('qr-loading').classList.remove('hidden');
            document.getElementById('qr-code').classList.add('hidden');

            // Ambil nomor HP dari URL parameter
            const hp = new URLSearchParams(window.location.search).get('number');

            // Tampilkan nomor HP di bawah container sebagai teks cadangan
            if (hp) {
                document.getElementById('text-display-number').textContent = hp;
            }

            setTimeout(() => {
                if (hp) {
                    // Generate Barcode menggunakan JsBarcode
                    JsBarcode("#barcode", hp, {
                        format: "CODE128", // Format umum barcode
                        lineColor: "#000",
                        width: 2,
                        height: 80,
                        displayValue: false, // Value kita taruh manual di div bawah agar lebih rapi css nya
                        margin: 0
                    });

                    // Hide loading, show Barcode
                    document.getElementById('qr-loading').classList.add('hidden');
                    document.getElementById('qr-code').classList.remove('hidden');
                } else {
                    document.getElementById('text-display-number').textContent = "Nomor tidak ditemukan";
                    document.getElementById('qr-loading').classList.add('hidden');
                }
            }, 800); // Sedikit delay untuk efek loading
        }

        function shareQR() {
            if (navigator.share) {
                navigator.share({
                    title: 'Barcode Member',
                    text: 'Scan Barcode member ini',
                    url: window.location.href
                });
            } else {
                navigator.clipboard.writeText(window.location.href).then(() => {
                    alert('Link Barcode telah disalin ke clipboard!');
                });
            }
        }
    </script>
</body>

</html>