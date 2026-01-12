<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Ganti Email</title>
    <link rel="stylesheet" href="/src/output2.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet" />
    <link rel="icon" type="image/png" href="../images/logo1.png">

</head>

<body
    class="min-h-screen bg-gradient-to-br from-pink-50 via-purple-50 to-blue-50 flex items-center justify-center p-4 font-['Poppins']">

    <div
        class="w-full max-w-md bg-white/80 backdrop-blur-lg rounded-3xl shadow-2xl overflow-hidden p-8 border border-white/20">

        <div class="text-center mb-8">
            <h2 class="text-2xl font-bold mb-2">
                Ganti Email</h2>
            <p class="text-gray-600 text-sm">Amankan akun Anda dengan memperbarui email</p>
        </div>

        <form id="changeEmailForm" class="space-y-5">

            <div class="space-y-2">
                <label for="newEmail" class="block text-sm font-medium text-gray-700">Email Baru</label>
                <div class="relative">
                    <i class="fas fa-envelope absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    <input type="email" id="newEmail" required placeholder="Masukkan email baru"
                        class="w-full border-2 border-gray-200 pl-10 pr-4 py-3 rounded-xl focus:outline-none focus:ring-2 focus:ring-pink-500 transition-all" />
                </div>
            </div>

            <div class="space-y-2">
                <label for="currentPassword" class="block text-sm font-medium text-gray-700">Password Saat Ini</label>
                <div class="relative">
                    <i class="fas fa-lock absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    <input type="password" id="currentPassword" required placeholder="Konfirmasi password Anda"
                        class="w-full border-2 border-gray-200 pl-10 pr-12 py-3 rounded-xl focus:outline-none focus:ring-2 focus:ring-pink-500 transition-all" />
                    <button type="button"
                        class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600"
                        onclick="togglePassword('currentPassword', this)">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
                <p class="text-xs text-gray-500 mt-1"><i class="fas fa-info-circle mr-1"></i>Kami butuh password Anda
                    untuk verifikasi.</p>
            </div>

            <button type="submit" id="submitBtn"
                class="w-full bg-gradient-to-r from-pink-500 to-purple-600 hover:from-pink-600 hover:to-purple-700 text-white py-3 rounded-xl font-semibold shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-300">
                <span id="btnText"><i class="fas fa-save mr-2"></i>Simpan Perubahan</span>
                <span id="btnLoading" class="hidden"><i class="fas fa-spinner fa-spin mr-2"></i>Memproses...</span>
            </button>

            <div class="text-center mt-4">
                <a href="/customer/home" class="text-sm text-gray-500 hover:text-pink-600 transition-colors">
                    <i class="fas fa-arrow-left mr-1"></i>Kembali ke Beranda
                </a>
            </div>

        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script type="module" src="/../src/js/customer/account/change_email.js"></script>

    <script>
        function togglePassword(inputId, button) {
            const input = document.getElementById(inputId);
            const icon = button.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    </script>
</body>

</html>