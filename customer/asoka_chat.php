<?php
require_once __DIR__ . '/../aa_kon_sett.php';
require_once __DIR__ . '/../src/auth/middleware_login.php';

$user = getAuthenticatedUser();

$userId = $user->id; 
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <title>Hubungi Kami - Asoka Baby Store</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link rel="icon" type="image/png" href="/images/logo1.png">
    <link rel="stylesheet" href="/src/output2.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">

    <style>
        * {
            font-family: 'Poppins', sans-serif;
        }
        /* Custom animation for fade-in effect */
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-in-up {
            animation: fadeInUp 0.5s ease-out forwards;
        }
        /* Custom scrollbar */
        #chatScrollContainer::-webkit-scrollbar { width: 6px; }
        #chatScrollContainer::-webkit-scrollbar-track { background: #f1f5f9; }
        #chatScrollContainer::-webkit-scrollbar-thumb { background: #f472b6; border-radius: 3px; }
        #chatScrollContainer::-webkit-scrollbar-thumb:hover { background: #ec4899; }
    </style>
</head>

<body class="bg-gradient-to-br from-pink-50 via-white to-purple-50 min-h-screen text-gray-800">
    <div class="max-w-4xl mx-auto p-4 pb-32 space-y-6">

        <header class="bg-gradient-to-r from-orange-400 to-pink-500 rounded-3xl shadow-xl p-6 text-white relative overflow-hidden">
             <div class="absolute inset-0 bg-black/10"></div>
             <div class="relative z-10">
                 <div class="flex items-center gap-4">
                     <div class="w-14 h-14 p-4 bg-white/20 backdrop-blur-sm rounded-full flex items-center justify-center">
                         <i class="fas fa-headset text-3xl"></i>
                     </div>
                     <div>
                         <h1 class="text-2xl md:text-3xl font-bold">Hubungi Kami</h1>
                         <p class="text-orange-100">Punya pertanyaan atau masukan? Kami siap membantu!</p>
                     </div>
                 </div>
            </div>
        </header>

        <section class="bg-white/80 backdrop-blur-lg rounded-3xl shadow-xl p-6 border border-pink-100">
            <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-3">
                <i class="fas fa-paper-plane text-pink-500"></i>
                Kirim Pesan Baru
            </h2>
            <form id="contactUsForm" class="space-y-4">
                <div>
                    <label for="subject" class="block text-sm font-medium text-gray-700 mb-1">Subjek</label>
                    <input type="text" id="subject" name="subject" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-pink-500 focus:border-pink-500 transition" placeholder="Contoh: Pertanyaan tentang produk">
                </div>
                <div>
                    <label for="message" class="block text-sm font-medium text-gray-700 mb-1">Pesan</label>
                    <textarea id="message" name="message" rows="4" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-pink-500 focus:border-pink-500 transition" placeholder="Tuliskan pesan Anda di sini..."></textarea>
                </div>
                <button type="submit" id="submitBtn" class="w-full flex items-center justify-center gap-2 px-6 py-3 bg-gradient-to-r from-pink-500 to-purple-600 text-white rounded-xl font-semibold hover:opacity-90 transition-all duration-300 shadow-lg">
                    <i class="fas fa-paper-plane"></i>
                    <span>Kirim Pesan</span>
                </button>
            </form>
        </section>

        <section class="bg-white/80 backdrop-blur-lg rounded-3xl shadow-xl p-6 border border-pink-100">
            <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-3">
                <i class="fas fa-history text-purple-500"></i>
                Riwayat Pesan Anda
            </h2>
            <div id="contact-history-container" class="space-y-3">
                <div id="history-loader" class="text-center py-8">
                    <div class="w-10 h-10 border-4 border-pink-200 border-t-pink-500 rounded-full animate-spin mx-auto mb-3"></div>
                    <p class="text-gray-500">Memuat riwayat...</p>
                </div>
            </div>
        </section>
    </div>

    <div id="chatModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm hidden flex items-center justify-center p-4" style="z-index: 60;">
        <div class="bg-white/95 backdrop-blur-md rounded-2xl shadow-2xl w-full max-w-2xl relative animate-fade-in-up transition-all duration-300 border border-white/20 max-h-[90vh] flex flex-col">
            
            <div class="bg-gradient-to-r from-pink-500 to-purple-600 rounded-t-2xl p-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-white/20 rounded-lg sm:flex items-center justify-center hidden">
                            <i class="fas fa-comments text-white text-lg"></i>
                        </div>
                        <div class="break-words"
                            style="max-width: 260px;"
                        >
                            <h2 class="text-lg font-bold text-white">Percakapan dengan Admin</h2>
                            <p class="text-white/80 text-xs" id="chatSubject">-</p>
                            <p class="text-white/80 text-xs" id="chatMessage">-</p>
                        </div>
                    </div>
                    <button type="button" id="closeChatModal" class="text-white/80 hover:text-white">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
            </div>

            <div id="chatScrollContainer" class="p-4 space-y-4 overflow-y-auto flex-1">
                <div id="chatConversationMessages" class="space-y-3 p-2">
                    </div>
            </div>

            <div class="p-4 border-t border-gray-200 bg-gray-50 rounded-b-2xl">
                <div id="chatInputContainer" class="flex items-start space-x-3">
                    <textarea id="chatMessageInput" rows="2" placeholder="Ketik balasan Anda..." class="block w-full rounded-lg border border-gray-300 px-4 py-3 shadow-sm text-sm resize-none focus:ring-pink-500 focus:border-pink-500"></textarea>
                    <button type="button" id="sendChatMessageBtn" class="px-4 py-2 bg-gradient-to-r from-pink-500 to-purple-600 text-white rounded-lg hover:opacity-90 transition-all shadow-sm flex items-center h-full">
                        <i class="fas fa-paper-plane mr-2"></i> Kirim
                    </button>
                </div>
            </div>

        </div>
    </div>

    <?php include __DIR__ . "/../src/component/bottom_navigation_user.php" ?>

    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <script src="/src/js/contact_us/main.js" type="module"></script>
</body>
</html>