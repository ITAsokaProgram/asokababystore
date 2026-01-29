<?php
session_start();
if (isset($_COOKIE['supplier_token'])) {
    header("Location: /supplier_dashboard");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Supplier</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/all.min.css">

    <link rel="stylesheet" href="css/header.css">
    <link rel="stylesheet" href="src/output2.css">
    <link rel="stylesheet" href="css/animation-fade-in.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert2@11.0.19/sweetalert2.min.css">

    <link rel="icon" type="image/png" href="images/logo1.png">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">

    <style>
        * {
            font-family: 'Poppins', sans-serif;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-15px); }
        }

        @keyframes pulse-glow {
            0%, 100% { box-shadow: 0 0 30px rgba(236, 72, 153, 0.3); }
            50% { box-shadow: 0 0 50px rgba(236, 72, 153, 0.6); }
        }

        @keyframes gradient-shift {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }

        @keyframes particle-float {
            0%, 100% { transform: translateY(0px) rotate(0deg); opacity: 0.6; }
            33% { transform: translateY(-20px) rotate(120deg); opacity: 1; }
            66% { transform: translateY(-10px) rotate(240deg); opacity: 0.8; }
        }

        .float-animation { animation: float 4s ease-in-out infinite; }
        .pulse-glow { animation: pulse-glow 3s ease-in-out infinite; }

        .gradient-text {
            background: linear-gradient(-45deg, #ec4899, #8b5cf6, #3b82f6, #10b981);
            background-size: 400% 400%;
            animation: gradient-shift 4s ease infinite;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .glass-effect {
            background: rgba(255, 255, 255, 0.25);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .card-hover-effect {
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        .card-hover-effect:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: 0 40px 80px rgba(0, 0, 0, 0.15);
        }

        .input-focus {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
        }

        .input-focus:focus {
            transform: translateY(-2px);
            box-shadow: 0 15px 35px rgba(236, 72, 153, 0.2);
        }

        .button-hover {
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .button-hover::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: left 0.6s;
        }

        .button-hover:hover::before { left: 100%; }

        .particle {
            position: absolute;
            width: 8px;
            height: 8px;
            background: linear-gradient(45deg, #ec4899, #8b5cf6);
            border-radius: 50%;
            animation: particle-float 8s ease-in-out infinite;
        }

        .logo-glow {
            filter: drop-shadow(0 0 20px rgba(255, 255, 255, 0.4));
            transition: filter 0.3s ease;
        }

        .logo-glow:hover { filter: drop-shadow(0 0 30px rgba(255, 255, 255, 0.6)); }

        .gradient-border {
            position: relative;
            background: white;
            border-radius: 1rem;
        }

        .gradient-border::before {
            content: '';
            position: absolute;
            inset: 0;
            padding: 2px;
            background: linear-gradient(45deg, #ec4899, #8b5cf6, #3b82f6);
            border-radius: inherit;
            mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
            mask-composite: xor;
            -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
            -webkit-mask-composite: xor;
        }

        .enhanced-header {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.95), rgba(251, 207, 232, 0.95));
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(236, 72, 153, 0.1);
        }

        .enhanced-section {
            position: relative;
            overflow: hidden;
        }

        .enhanced-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>

<body class="dark active">
    <header class="enhanced-header text-gray-800 py-4 px-6 flex items-center shadow-xl fixed top-0 w-full z-10 justify-between">
        <a href="/">
            <img src="images/logo.png" alt="Logo" class="h-12 logo-glow transition-all duration-300 hover:scale-110">
        </a>
        <div class="hidden md:flex items-center space-x-2 text-sm font-semibold text-gray-600">
            <i class="fas fa-truck-fast text-pink-500"></i>
            <span>Supplier Portal</span>
        </div>
    </header>

    <div class="fixed inset-0 overflow-hidden pointer-events-none">
        <div class="absolute top-20 left-20 w-32 h-32 bg-gradient-to-r from-pink-300/30 to-purple-300/30 rounded-full float-animation blur-xl"></div>
        <div class="absolute top-40 right-20 w-24 h-24 bg-gradient-to-r from-purple-300/30 to-blue-300/30 rounded-full float-animation blur-xl" style="animation-delay: 1s;"></div>
        <div class="absolute bottom-20 left-1/4 w-28 h-28 bg-gradient-to-r from-blue-300/30 to-green-300/30 rounded-full float-animation blur-xl" style="animation-delay: 2s;"></div>
        <div class="absolute bottom-40 right-1/3 w-36 h-36 bg-gradient-to-r from-pink-200/30 to-purple-200/30 rounded-full float-animation blur-xl" style="animation-delay: 0.5s;"></div>

        <div class="particle" style="top: 15%; left: 10%; animation-delay: 0s;"></div>
        <div class="particle" style="top: 25%; right: 15%; animation-delay: 1s;"></div>
        <div class="particle" style="bottom: 35%; left: 15%; animation-delay: 2s;"></div>
        <div class="particle" style="bottom: 20%; right: 20%; animation-delay: 3s;"></div>
        <div class="particle" style="top: 60%; left: 8%; animation-delay: 4s;"></div>
        <div class="particle" style="top: 80%; right: 12%; animation-delay: 5s;"></div>
    </div>

    <section class="fade-in enhanced-section flex items-center justify-center min-h-screen p-6 pt-20">
        <div class="enhanced-card shadow-2xl rounded-3xl p-10 w-full max-w-md md:max-w-lg card-hover-effect relative">
            <div class="absolute -top-6 -left-6 w-12 h-12 bg-gradient-to-br from-pink-400 to-purple-500 rounded-2xl rotate-12 opacity-20"></div>
            <div class="absolute -bottom-4 -right-4 w-8 h-8 bg-gradient-to-br from-blue-400 to-green-400 rounded-xl rotate-45 opacity-30"></div>

            <div class="text-center mb-10">
                <div class="w-20 h-20 bg-gradient-to-br from-pink-500 to-purple-600 rounded-2xl mx-auto mb-6 flex items-center justify-center pulse-glow shadow-2xl">
                    <i class="fas fa-truck-loading text-white text-2xl"></i>
                </div>
                <h3 class="text-4xl font-extrabold gradient-text mb-3">Login Supplier</h3>
                <p class="text-gray-600 text-lg">Akses Portal Supplier</p>
                <div class="w-16 h-1 bg-gradient-to-r from-pink-500 to-purple-500 rounded-full mx-auto mt-3"></div>
            </div>

            <form id="loginSupplierForm" class="space-y-6">
                
                <div class="space-y-3">
                    <label for="email" class="block text-gray-800 font-bold text-lg flex items-center">
                        <i class="fas fa-envelope text-pink-500 mr-2"></i>
                        Email
                        <span class="text-red-500 ml-1">*</span>
                    </label>
                    <div class="relative group">
                        <div class="absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400 group-hover:text-pink-500 transition-colors z-10">
                            <i class="fas fa-at text-lg"></i>
                        </div>
                        <input type="email" name="email" id="email" placeholder="Masukan Email Terdaftar" required
                            class="w-full border-2 border-gray-200 rounded-2xl px-14 py-5 text-gray-700 text-lg focus:outline-none focus:ring-4 focus:ring-pink-500/20 focus:border-pink-500 transition-all duration-300 input-focus gradient-border bg-white hover:border-pink-300 shadow-lg" />
                    </div>
                </div>

                <div class="space-y-3">
                    <label for="password" class="block text-gray-800 font-bold text-lg flex items-center">
                        <i class="fas fa-lock text-pink-500 mr-2"></i>
                        Kata Sandi
                        <span class="text-red-500 ml-1">*</span>
                    </label>
                    <div class="relative group">
                        <div class="absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400 group-hover:text-pink-500 transition-colors z-10">
                            <i class="fas fa-key text-lg"></i>
                        </div>

                        <input type="password" name="password" id="password" placeholder="Masukan Kata Sandi" required
                            class="w-full border-2 border-gray-200 rounded-2xl px-14 py-5 text-gray-700 text-lg focus:outline-none focus:ring-4 focus:ring-pink-500/20 focus:border-pink-500 transition-all duration-300 input-focus gradient-border bg-white hover:border-pink-300 shadow-lg" />

                        <button type="button" onclick="togglePass()"
                            class="absolute right-4 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-pink-500 transition-colors z-10 focus:outline-none cursor-pointer">
                            <i class="fas fa-eye text-lg" id="iconPass"></i>
                        </button>
                    </div>
                </div>

                <button type="submit"
                    class="w-full bg-gradient-to-r from-pink-500 via-purple-500 to-blue-500 hover:from-pink-600 hover:via-purple-600 hover:to-blue-600 text-white font-bold text-xl py-6 rounded-2xl transition-all duration-500 cursor-pointer button-hover shadow-2xl hover:shadow-3xl transform hover:scale-105 relative overflow-hidden group mt-8">
                    <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/20 to-transparent transform -skew-x-12 translate-x-full group-hover:translate-x-0 transition-transform duration-700">
                    </div>
                    <span class="relative flex items-center justify-center">
                        <i class="fas fa-sign-in-alt mr-3 text-xl"></i>
                        Masuk
                    </span>
                </button>
            </form>
        </div>
    </section>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert2@11.0.19/sweetalert2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script src="src/js/user_supplier/login_handler.js"></script>

    <script>
        function togglePass() {
            const passInput = document.getElementById('password');
            const icon = document.getElementById('iconPass');

            if (passInput.type === 'password') {
                passInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    </script>

</body>
</html>