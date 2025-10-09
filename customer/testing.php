<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Testing Verifikasi Link WA</title>
    <link rel="stylesheet" href="/src/output2.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <style>
        .hidden { display: none; }
    </style>
</head>
<body class="bg-gray-100 p-8">

    <h1 class="text-2xl font-bold mb-4">Halaman Testing Ganti Nomor HP</h1>
    <p class="mb-6">Klik tombol di bawah untuk memulai alur verifikasi menggunakan link WhatsApp.</p>

    <button id="btnUbahNoHp" class="px-6 py-3 bg-pink-500 text-white font-semibold rounded-lg shadow-md hover:bg-pink-600 transition-all">
        <i class="fas fa-mobile-alt mr-2"></i>
        Ubah Nomor HP
    </button>

    <div id="modalInputNoHp" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden">
        <div class="bg-white rounded-3xl shadow-2xl max-w-md w-full mx-4 p-6 space-y-4 animate-fade-in-up">
            <div class="text-center">
                <div class="w-16 h-16 bg-gradient-to-r from-pink-400 to-rose-500 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-mobile-alt text-white text-2xl"></i>
                </div>
                <h2 class="text-xl font-bold text-gray-800 mb-2">Masukkan Nomor HP Baru</h2>
                <p class="text-sm text-gray-500">Pastikan nomor ini aktif di WhatsApp untuk menerima link verifikasi.</p>
            </div>
            
            <div class="relative">
                <i class="fas fa-phone absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                <input type="tel" id="inputNoHp" placeholder="08..." class="w-full border-2 border-gray-200 rounded-xl pl-12 pr-4 py-3 focus:outline-none focus:ring-2 focus:ring-pink-500 transition" />
            </div>
            
            <p id="errorNoHp" class="text-sm text-red-500 text-center hidden"></p>
            
            <div class="flex gap-3 pt-4">
                <button id="btnBatalNoHp" class="flex-1 px-4 py-3 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold transition">Batal</button>
                <button id="btnKirimLink" class="flex-1 px-4 py-3 rounded-xl bg-gradient-to-r from-pink-500 to-rose-600 text-white font-semibold shadow-lg hover:opacity-90 transition flex items-center justify-center">
                    <i class="fab fa-whatsapp mr-2"></i>
                    <span>Lanjutkan ke WhatsApp</span>
                </button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <script src="/src/js/link_verification/test_link.js" type="module"></script>
</body>
</html>