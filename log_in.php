<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login Asoka Baby Store</title>
  
  <!-- Tailwind CSS CDN -->
  <link rel="stylesheet" href="/src/output2.css">
  
  <!-- Font Awesome -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />
  
  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
  
  <!-- Favicon -->
  <link rel="icon" type="image/png" href="/images/logo1.png" />

  <style>
    * {
      font-family: 'Poppins', sans-serif;
    }

    @keyframes float {
      0%, 100% { transform: translateY(0px); }
      50% { transform: translateY(-10px); }
    }

    @keyframes gradient-shift {
      0%, 100% { background-position: 0% 50%; }
      50% { background-position: 100% 50%; }
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

    .glass-effect {
      background: rgba(255, 255, 255, 0.25);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(255, 255, 255, 0.18);
    }

    .card-hover-effect:hover {
      transform: translateY(-5px);
      box-shadow: 0 25px 50px rgba(0, 0, 0, 0.1);
    }
  </style>
</head>

<body class="min-h-screen bg-gradient-to-br from-pink-50 via-purple-50 to-blue-50 flex items-center justify-center p-4 font-['Poppins']">
  
  <!-- Animated Background Elements -->
  <div class="fixed inset-0 overflow-hidden pointer-events-none">
    <div class="absolute top-20 left-20 w-32 h-32 bg-pink-300 rounded-full opacity-20 float-animation"></div>
    <div class="absolute top-40 right-20 w-24 h-24 bg-purple-300 rounded-full opacity-20 float-animation" style="animation-delay: 1s;"></div>
    <div class="absolute bottom-20 left-1/4 w-20 h-20 bg-blue-300 rounded-full opacity-20 float-animation" style="animation-delay: 2s;"></div>
    <div class="absolute bottom-40 right-1/3 w-28 h-28 bg-pink-200 rounded-full opacity-20 float-animation" style="animation-delay: 0.5s;"></div>
  </div>

  <div class="w-full max-w-6xl bg-white/80 backdrop-blur-lg rounded-3xl shadow-2xl overflow-hidden flex flex-col lg:flex-row card-hover-effect transition-all duration-500 border border-white/20">
    
    <!-- Left Section - Image & Branding -->
    <div class="lg:w-1/2 bg-gradient-to-br from-pink-400 via-purple-500 to-blue-500 relative overflow-hidden">
      <div class="absolute inset-0 bg-black/20"></div>
      <div class="relative h-full flex flex-col justify-center items-center text-white p-8 lg:p-12">
        <div class="text-center space-y-3">
          <div class="w-20 h-20 bg-white/20 rounded-full flex items-center justify-center mx-auto float-animation">
            <!-- <i class="fas fa-baby text-white text-4xl"></i> -->
             <img src="public/images/LOGO Asoka k.png" class="w-[70px]" alt="asoka">
          </div>
          <h1 class="text-xl lg:text-xl font-bold">ASOKA Baby Store</h1>
          <div class="flex items-center justify-center space-x-4 mt-8">
            <div class="flex items-center space-x-2">
              <i class="fas fa-check-circle text-green-300"></i>
              <span class="text-sm">Aman & Terpercaya</span>
            </div>
            <div class="flex items-center space-x-2">
              <i class="fas fa-check-circle text-green-300"></i>
              <span class="text-sm">Kualitas Terbaik</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Right Section - Login Forms -->
    <div class="lg:w-1/2 p-6 lg:p-12 flex flex-col justify-center">
      <div id="form-container" class="w-full transition-all duration-500">

        <!-- Login Form -->
        <div id="login-form" class="w-full space-y-6">
          <div class="text-center mb-8">
            <h2 class="text-xl lg:text-xl font-bold gradient-text mb-2">Selamat Datang Kembali</h2>
            <p class="text-gray-600">Masuk ke akun Anda untuk melanjutkan</p>
          </div>

          <!-- Social Login Buttons -->
          <div class="space-y-3">
            <button type="button" id="google-login" class="w-full flex items-center justify-center gap-3 bg-white border-2 border-gray-200 p-4 rounded-xl transition-all duration-300 hover:bg-gray-50 hover:border-pink-300 hover:shadow-lg hover:-translate-y-1">
              <img src="https://www.google.com/favicon.ico" alt="Google" class="w-5 h-5" />
              <span class="font-medium">Masuk dengan Google</span>
            </button>
          </div>

          <!-- Divider -->
          <div class="flex items-center my-6">
            <div class="flex-1 border-t border-gray-200"></div>
            <span class="px-4 text-sm text-gray-500 font-medium">atau</span>
            <div class="flex-1 border-t border-gray-200"></div>
          </div>

          <!-- Login Form -->
          <form id="loginForm" method="POST" class="space-y-5">
            <div class="space-y-2">
              <label for="loginEmail" class="block text-sm font-medium text-gray-700">Email</label>
              <div class="relative">
                <i class="fas fa-envelope absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                <input type="email" name="email" id="loginEmail" placeholder="Masukkan email Anda"
                  class="w-full border-2 border-gray-200 pl-10 pr-4 py-4 rounded-xl focus:outline-none focus:ring-2 focus:ring-pink-500 focus:border-transparent transition-all duration-300 text-lg" />
              </div>
              <div id="emailError" class="hidden text-sm text-red-500 mt-1">
                <i class="fas fa-exclamation-circle mr-1"></i>Email tidak valid
              </div>
            </div>

            <div class="space-y-2">
              <label for="loginPassword" class="block text-sm font-medium text-gray-700">Password</label>
              <div class="relative">
                <i class="fas fa-lock absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                <input type="password" name="password" id="loginPassword" placeholder="Masukkan password Anda"
                  class="w-full border-2 border-gray-200 pl-10 pr-12 py-4 rounded-xl focus:outline-none focus:ring-2 focus:ring-pink-500 focus:border-transparent transition-all duration-300 text-lg" />
                <button type="button" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors"
                  onclick="togglePassword('loginPassword', this)">
                  <i class="fas fa-eye"></i>
                </button>
              </div>
              <div id="passwordError" class="hidden text-sm text-red-500 mt-1">
                <i class="fas fa-exclamation-circle mr-1"></i>Password minimal 8 karakter, 1 Huruf Besar Dan 1 Angka
              </div>
            </div>

            <button type="submit" class="w-full bg-gradient-to-r from-pink-500 to-purple-600 hover:from-pink-600 hover:to-purple-700 transition-all duration-300 text-white py-4 rounded-xl font-semibold text-lg shadow-lg hover:shadow-xl transform hover:scale-105">
              <i class="fas fa-sign-in-alt mr-2"></i>Masuk
            </button>
          </form>

          <div id="loginSuccess" class="hidden text-center p-4 bg-green-100 text-green-700 rounded-xl border border-green-200">
            <i class="fas fa-check-circle mr-2"></i>Login berhasil! Mengalihkan...
          </div>

          <!-- Action Links -->
          <div class="text-center space-y-3">
            <div class="text-sm text-gray-600">
              Belum punya akun?
              <button id="show-register" class="text-pink-600 font-semibold hover:text-pink-700 hover:underline transition-colors">Daftar Sekarang</button>
            </div>
            <div class="text-sm">
              <button id="show-forgot" class="text-gray-500 hover:text-red-500 hover:underline transition-colors">
                <i class="fas fa-key mr-1"></i>Lupa Password?
              </button>
            </div>
          </div>
        </div>

        <!-- Register Form -->
        <div id="register-form" class="w-full hidden space-y-6">
          <div class="text-center mb-8">
            <h2 class="text-3xl lg:text-4xl font-bold gradient-text mb-2">Daftar Akun Baru</h2>
            <p class="text-gray-600">Buat akun untuk mulai berbelanja</p>
          </div>

          <form id="registerForm" method="POST" class="space-y-5">
            <div class="space-y-2">
              <label for="registerName" class="block text-sm font-medium text-gray-700">Nama Lengkap</label>
              <div class="relative">
                <i class="fas fa-user absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                <input type="text" name="name" id="registerName" placeholder="Masukkan nama lengkap"
                  class="w-full border-2 border-gray-200 pl-10 pr-4 py-4 rounded-xl focus:outline-none focus:ring-2 focus:ring-pink-500 focus:border-transparent transition-all duration-300 text-lg" />
              </div>
              <div id="nameError" class="hidden text-sm text-red-500 mt-1">
                <i class="fas fa-exclamation-circle mr-1"></i>Nama harus diisi
              </div>
            </div>

            <div class="space-y-2">
              <label for="registerPhone" class="block text-sm font-medium text-gray-700">Nomor HP</label>
              <div class="relative">
                <i class="fas fa-phone absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                <input type="tel" name="phone" id="registerPhone" placeholder="Contoh: 08123456789"
                  class="w-full border-2 border-gray-200 pl-10 pr-4 py-4 rounded-xl focus:outline-none focus:ring-2 focus:ring-pink-500 focus:border-transparent transition-all duration-300 text-lg" />
              </div>
              <div id="phoneError" class="hidden text-sm text-red-500 mt-1">
                  <i class="fas fa-exclamation-circle mr-1"></i>Format No. HP harus diawali '08' dan total 10-13 digit.
              </div>
            </div>

            <div class="space-y-2">
              <label for="registerEmail" class="block text-sm font-medium text-gray-700">Email</label>
              <div class="relative">
                <i class="fas fa-envelope absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                <input type="email" name="email" id="registerEmail" placeholder="Masukkan email Anda"
                  class="w-full border-2 border-gray-200 pl-10 pr-4 py-4 rounded-xl focus:outline-none focus:ring-2 focus:ring-pink-500 focus:border-transparent transition-all duration-300 text-lg" />
              </div>
              <div id="registerEmailError" class="hidden text-sm text-red-500 mt-1">
                <i class="fas fa-exclamation-circle mr-1"></i>Email tidak valid, harus @gmail.com
              </div>
            </div>

            <div class="space-y-2">
              <label for="registerPassword" class="block text-sm font-medium text-gray-700">Password</label>
              <div class="relative">
                <i class="fas fa-lock absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                <input type="password" name="password" id="registerPassword" placeholder="Buat password yang kuat"
                  class="w-full border-2 border-gray-200 pl-10 pr-12 py-4 rounded-xl focus:outline-none focus:ring-2 focus:ring-pink-500 focus:border-transparent transition-all duration-300 text-lg" />
                <button type="button" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors"
                  onclick="togglePassword('registerPassword', this)">
                  <i class="fas fa-eye"></i>
                </button>
              </div>
              <div id="registerPasswordError" class="hidden text-sm text-red-500 mt-1">
                <i class="fas fa-exclamation-circle mr-1"></i>Password minimal 8 karakter, 1 Huruf Besar, dan 1 Angka
              </div>
              <div id="passwordStrength" class="hidden flex gap-1 mt-2">
                <div class="h-2 flex-1 rounded bg-gray-300"></div>
                <div class="h-2 flex-1 rounded bg-gray-300"></div>
                <div class="h-2 flex-1 rounded bg-gray-300"></div>
              </div>
            </div>

            <button type="submit" class="w-full bg-gradient-to-r from-pink-500 to-purple-600 hover:from-pink-600 hover:to-purple-700 transition-all duration-300 text-white py-4 rounded-xl font-semibold text-lg shadow-lg hover:shadow-xl transform hover:scale-105">
              <i class="fas fa-user-plus mr-2"></i>Daftar
            </button>
          </form>

          <div id="registerSuccess" class="hidden text-center p-4 bg-green-100 text-green-700 rounded-xl border border-green-200">
            <i class="fas fa-check-circle mr-2"></i>Pendaftaran berhasil! Silakan login.
          </div>

          <div class="text-center">
            <div class="text-sm text-gray-600">
              Sudah punya akun?
              <button id="show-login" class="text-pink-600 font-semibold hover:text-pink-700 hover:underline transition-colors">Login</button>
            </div>
          </div>
        </div>

        <!-- Forgot Password Form -->
        <div id="forgot-form" class="w-full hidden space-y-6">
          <div class="text-center mb-8">
            <h2 class="text-3xl lg:text-4xl font-bold gradient-text mb-2">Reset Password</h2>
            <p class="text-gray-600">Masukkan email untuk reset password</p>
          </div>

          <form id="forgotForm" method="POST" class="space-y-5">
            <div class="space-y-2">
              <label for="forgotEmail" class="block text-sm font-medium text-gray-700">Email</label>
              <div class="relative">
                <i class="fas fa-envelope absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                <input type="email" name="email" id="forgotEmail" placeholder="Masukkan email Anda"
                  class="w-full border-2 border-gray-200 pl-10 pr-4 py-4 rounded-xl focus:outline-none focus:ring-2 focus:ring-pink-500 focus:border-transparent transition-all duration-300 text-lg" />
              </div>
              <div id="forgotEmailError" class="hidden text-sm text-red-500 mt-1">
                <i class="fas fa-exclamation-circle mr-1"></i>Email tidak valid
              </div>
            </div>

            <button type="submit" class="w-full bg-gradient-to-r from-red-500 to-pink-600 hover:from-red-600 hover:to-pink-700 transition-all duration-300 text-white py-4 rounded-xl font-semibold text-lg shadow-lg hover:shadow-xl transform hover:scale-105 relative" id="resetButton">
              <span class="inline-flex items-center">
                <span class="loading-spinner hidden mr-2">
                  <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                </span>
                <span class="button-text">
                  <i class="fas fa-paper-plane mr-2"></i>Kirim Link Reset
                </span>
              </span>
            </button>
          </form>

          <div id="forgotSuccess" class="hidden text-center p-4 bg-green-100 text-green-700 rounded-xl border border-green-200">
            <i class="fas fa-check-circle mr-2"></i>Link reset password telah dikirim ke email Anda.
          </div>

          <div class="text-center">
            <button id="show-login2" class="text-pink-600 font-semibold hover:text-pink-700 hover:underline transition-colors">
              <i class="fas fa-arrow-left mr-1"></i>Kembali ke Login
            </button>
          </div>
        </div>

        <!-- Phone Login Form -->
        <div id="number-phone" class="w-full hidden space-y-6">
          <div class="text-center mb-8">
            <h2 class="text-3xl lg:text-4xl font-bold gradient-text mb-2">Masuk dengan No Handphone</h2>
            <p class="text-gray-600">Gunakan nomor HP untuk login</p>
          </div>

          <form id="numberPhoneForm" method="POST" class="space-y-5">
            <div class="space-y-2">
              <label for="numberPhoneLogin" class="block text-sm font-medium text-gray-700">Nomor HP</label>
              <div class="relative">
                <i class="fas fa-phone absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                <input type="tel" name="phone" id="numberPhoneLogin" placeholder="Contoh: 08123456789"
                  class="w-full border-2 border-gray-200 pl-10 pr-4 py-4 rounded-xl focus:outline-none focus:ring-2 focus:ring-pink-500 focus:border-transparent transition-all duration-300 text-lg" />
              </div>
              <div id="numberPhoneError" class="hidden text-sm text-red-500 mt-1">
                <i class="fas fa-exclamation-circle mr-1"></i>Nomor HP tidak valid (minimal 10 digit)
              </div>
            </div>

            <button type="submit" id="loginWithPhone" class="w-full bg-gradient-to-r from-green-500 to-blue-600 hover:from-green-600 hover:to-blue-700 transition-all duration-300 text-white py-4 rounded-xl font-semibold text-lg shadow-lg hover:shadow-xl transform hover:scale-105">
              <i class="fas fa-mobile-alt mr-2"></i>Masuk
            </button>
          </form>

          <div class="text-center">
            <button id="show-login3" class="text-pink-600 font-semibold hover:text-pink-700 hover:underline transition-colors">
              <i class="fas fa-arrow-left mr-1"></i>Kembali ke Login Email
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Loading Screen -->
  <div id="loading-screen" class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 hidden">
    <div class="bg-white/95 backdrop-blur-lg p-8 rounded-2xl shadow-2xl flex items-center space-x-4 border border-white/20">
      <div class="w-8 h-8 border-4 border-pink-200 border-t-pink-500 rounded-full animate-spin"></div>
      <span class="text-gray-700 text-lg font-medium">Mohon tunggu, sedang proses login...</span>
    </div>
  </div>

  <!-- Scripts -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script type="module" src="src/js/validation_ui/display.js"></script>
  
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

  <script type="module">
    import { getCookie } from "/src/js/index/utils/cookies.js"
    window.addEventListener("pageshow", function(event) {
      const token = getCookie("token");
      if (token) {
        Swal.fire({
          icon: "success",
          title: "Sudah Login",
          text: "Anda sudah login, silahkan klik ok untuk ke beranda",
          showConfirmButton: true,
          confirmButtonColor: '#ec4899',
          background: '#fff',
          customClass: {
            popup: 'rounded-xl shadow-xl border-2 border-pink-100',
            title: 'text-pink-600 font-bold',
            content: 'text-gray-600'
          }
        }).then(() => {
          window.location.href = "/customer/home";
        });
      }
    });
  </script>
</body>
</html>
</html>