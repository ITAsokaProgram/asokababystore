<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Lengkapi Pendaftaran - Asoka Baby Store</title>

    <link rel="stylesheet" href="/src/output2.css">

    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />

    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet" />

    <link rel="icon" type="image/png" href="/images/logo1.png" />

    <style>
        * {
            font-family: 'Poppins', sans-serif;
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0px);
            }

            50% {
                transform: translateY(-10px);
            }
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

        .float-animation {
            animation: float 3s ease-in-out infinite;
        }

        .gradient-text {
            background: linear-gradient(-45deg, #ec4899, #8b5cf6, #3b82f6, #ec4899);
            background-size: 400% 400%;
            animation: gradient-shift 3s ease infinite;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
    </style>
</head>

<body
    class="min-h-screen bg-gradient-to-br from-pink-50 via-purple-50 to-blue-50 flex items-center justify-center p-4 font-['Poppins']">

    <div
        class="w-full max-w-6xl bg-white/80 backdrop-blur-lg rounded-3xl shadow-2xl overflow-hidden flex flex-col lg:flex-row transition-all duration-500 border border-white/20">

        <div class="lg:w-1/2 bg-gradient-to-br from-pink-400 via-purple-500 to-blue-500 relative overflow-hidden">
            <div class="absolute inset-0 bg-black/20"></div>
            <div class="relative h-full flex flex-col justify-center items-center text-white p-8 lg:p-12">
                <div class="text-center space-y-3">
                    <div
                        class="w-20 h-20 bg-white/20 rounded-full flex items-center justify-center mx-auto float-animation">
                        <a href="/">
                            <img src="public/images/LOGO Asoka k.png" class="w-[70px]" alt="asoka">
                        </a>
                    </div>
                    <h1 class="text-xl lg:text-xl font-bold">ASOKA Baby Store</h1>
                    <p class="text-lg text-white/90">Satu langkah lagi untuk bergabung!</p>
                </div>
            </div>
        </div>

        <div class="lg:w-1/2 p-6 lg:p-12 flex flex-col justify-center">
            <div id="form-container" class="w-full transition-all duration-500">

                <div id="register-form" class="w-full space-y-6">
                    <div class="text-center mb-8">
                        <h2 class="text-3xl lg:text-4xl font-bold gradient-text mb-2">Lengkapi Pendaftaran</h2>
                        <p class="text-gray-600">Buat akun online Anda untuk terhubung dengan data customer.</p>
                    </div>

                    <form id="completeRegisterForm" method="POST" class="space-y-5">

                        <input type="hidden" id="regToken" name="token">

                        <div class="space-y-2">
                            <label for="registerName" class="block text-sm font-medium text-gray-700">Nama
                                Lengkap</label>
                            <div class="relative">
                                <i
                                    class="fas fa-user-check absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                                <input type="text" name="name" id="registerName" placeholder="Masukkan nama Anda"
                                    class="w-full border-2 border-gray-200 pl-10 pr-4 py-4 rounded-xl focus:outline-none focus:ring-2 focus:ring-pink-500 focus:border-transparent transition-all duration-300 text-lg" />
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label for="registerPhone" class="block text-sm font-medium text-gray-700">Nomor HP
                                (terverifikasi)</label>
                            <div class="relative">
                                <i
                                    class="fas fa-phone-check absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                                <input type="tel" name="phone" id="registerPhone" placeholder="Memuat nomor HP..."
                                    class="w-full border-2 border-gray-200 pl-10 pr-4 py-4 rounded-xl focus:outline-none focus:ring-2 focus:ring-pink-500 focus:border-transparent transition-all duration-300 text-lg bg-gray-100 cursor-not-allowed"
                                    readonly />
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label for="registerEmail" class="block text-sm font-medium text-gray-700"></label>
                            <div class="relative">
                                <i
                                    class="fas fa-envelope absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                                <input type="email" name="email" id="registerEmail" placeholder="Masukkan email Anda"
                                    class="w-full border-2 border-gray-200 pl-10 pr-4 py-4 rounded-xl focus:outline-none focus:ring-2 focus:ring-pink-500 focus:border-transparent transition-all duration-300 text-lg" />
                            </div>
                            <div id="registerEmailError" class="hidden text-sm text-red-500 mt-1">
                                <i class="fas fa-exclamation-circle mr-1"></i>Email tidak valid.
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label for="registerPassword" class="block text-sm font-medium text-gray-700">Buat
                                Password</label>
                            <div class="relative">
                                <i
                                    class="fas fa-lock absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                                <input type="password" name="password" id="registerPassword"
                                    placeholder="Buat password yang kuat"
                                    class="w-full border-2 border-gray-200 pl-10 pr-12 py-4 rounded-xl focus:outline-none focus:ring-2 focus:ring-pink-500 focus:border-transparent transition-all duration-300 text-lg" />
                                <button type="button"
                                    class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors"
                                    onclick="togglePassword('registerPassword', this)">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <div id="registerPasswordError" class="hidden text-sm text-red-500 mt-1">
                                <i class="fas fa-exclamation-circle mr-1"></i>Password minimal 8 karakter, 1 Huruf
                                Besar, dan 1 Angka
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label for="confirmPassword" class="block text-sm font-medium text-gray-700">Konfirmasi
                                Password</label>
                            <div class="relative">
                                <i
                                    class="fas fa-lock absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                                <input type="password" name="confirmPassword" id="confirmPassword"
                                    placeholder="Ulangi password Anda"
                                    class="w-full border-2 border-gray-200 pl-10 pr-12 py-4 rounded-xl focus:outline-none focus:ring-2 focus:ring-pink-500 focus:border-transparent transition-all duration-300 text-lg" />
                                <button type="button"
                                    class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors"
                                    onclick="togglePassword('confirmPassword', this)">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <div id="confirmPasswordError" class="hidden text-sm text-red-500 mt-1">
                                <i class="fas fa-exclamation-circle mr-1"></i>Password tidak cocok.
                            </div>
                        </div>

                        <button type="submit"
                            class="w-full bg-gradient-to-r from-pink-500 to-purple-600 hover:from-pink-600 hover:to-purple-700 transition-all duration-300 text-white py-4 rounded-xl font-semibold text-lg shadow-lg hover:shadow-xl transform hover:scale-105">
                            <i class="fas fa-user-plus mr-2"></i>Daftar dan Selesaikan
                        </button>
                    </form>

                    <div class="text-center">
                        <div class="text-sm text-gray-600">
                            Sudah punya akun?
                            <a href="/log_in.php"
                                class="text-pink-600 font-semibold hover:text-pink-700 hover:underline transition-colors">Login</a>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <div id="loading-screen"
        class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 hidden">
        <div
            class="bg-white/95 backdrop-blur-lg p-8 rounded-2xl shadow-2xl flex items-center space-x-4 border border-white/20">
            <div class="w-8 h-8 border-4 border-pink-200 border-t-pink-500 rounded-full animate-spin"></div>
            <span class="text-gray-700 text-lg font-medium">Mohon tunggu...</span>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // Fungsi toggle password (sama seperti di login)
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

    <script type="module">
        // Import helper untuk validasi dan submit
        import { registerCustomer } from '/src/js/auth/auth_register_customer.js';

        // Asumsi Anda punya file validation.js seperti di kode sebelumnya
        // Jika tidak ada, Anda bisa ganti ini dengan implementasi regex manual
        import { validateEmail, validatePassword, showError, hideError } from "/src/js/validation_ui/validation.js";

        const loadingScreen = document.getElementById('loading-screen');
        const form = document.getElementById('completeRegisterForm');
        const tokenField = document.getElementById('regToken');
        const nameField = document.getElementById('registerName');
        const phoneField = document.getElementById('registerPhone');
        const emailField = document.getElementById('registerEmail');
        const passwordField = document.getElementById('registerPassword');
        const confirmPasswordField = document.getElementById('confirmPassword');

        // Tampilkan SweetAlert
        const showSwal = (icon, title, text, redirectUrl = null) => {
            Swal.fire({
                icon: icon,
                title: title,
                text: text,
                confirmButtonColor: '#ec4899',
            }).then(() => {
                if (redirectUrl) {
                    window.location.href = redirectUrl;
                }
            });
        };

        // 1. Validasi Token saat Halaman Dimuat
        document.addEventListener('DOMContentLoaded', async () => {
            loadingScreen.classList.remove('hidden');
            const urlParams = new URLSearchParams(window.location.search);
            const token = urlParams.get('token');

            if (!token || !token.startsWith('final_reg_')) {
                showSwal('error', 'Token Tidak Valid', 'Link yang Anda gunakan tidak valid atau telah kedaluwarsa.', '/log_in.php');
                return;
            }

            try {
                const response = await fetch('/src/api/verify_reg_token.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ token: token })
                });

                const data = await response.json();

                if (data.status === 'success') {
                    // Isi data ke form
                    tokenField.value = token;
                    nameField.value = data.nama_cust;
                    phoneField.value = data.no_hp;
                    loadingScreen.classList.add('hidden');
                } else {
                    showSwal('error', 'Token Gagal Diverifikasi', data.message, '/log_in.php');
                }
            } catch (error) {
                showSwal('error', 'Koneksi Gagal', 'Gagal terhubung ke server untuk verifikasi token.', '/log_in.php');
            }
        });

        // 2. Client-side Validation
        const validateForm = () => {
            let isValid = true;
            const email = emailField.value;
            const password = passwordField.value;
            const confirmPassword = confirmPasswordField.value;

            // Validate Email
            if (!validateEmail(email)) {
                showError(emailField, document.getElementById('registerEmailError'));
                isValid = false;
            } else {
                hideError(emailField, document.getElementById('registerEmailError'));
            }

            // Validate Password
            if (!validatePassword(password)) {
                showError(passwordField, document.getElementById('registerPasswordError'));
                isValid = false;
            } else {
                hideError(passwordField, document.getElementById('registerPasswordError'));
            }

            // Validate Confirm Password
            if (password !== confirmPassword) {
                showError(confirmPasswordField, document.getElementById('confirmPasswordError'));
                isValid = false;
            } else {
                hideError(confirmPasswordField, document.getElementById('confirmPasswordError'));
            }

            return isValid;
        };

        // 3. Handle Form Submit
        form.addEventListener('submit', (event) => {
            event.preventDefault();

            if (validateForm()) {
                const formData = {
                    token: tokenField.value,
                    nama_lengkap: nameField.value,
                    no_hp: phoneField.value,
                    email: emailField.value,
                    password: passwordField.value
                };

                // Panggil fungsi register dari modul auth
                registerCustomer(formData);
            }
        });
    </script>

</body>

</html>