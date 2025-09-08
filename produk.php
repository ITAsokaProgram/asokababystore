<?php
include "aa_kon_sett.php";
header("Access-Control-Allow-Origin: *");
// Atur header keamanan
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: no-referrer');
header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');


$userId = null;
$ip = $_SERVER['REMOTE_ADDR'];
$ua = $_SERVER['HTTP_USER_AGENT'];
$page = $_SERVER['REQUEST_URI'];
$pageName = "Guest Produk";

// Cek apakah sudah ada record dalam 5 menit terakhir
$stmt = $conn->prepare("
    SELECT id FROM visitors
    WHERE COALESCE(user_id, ip) = COALESCE(?, ?)
      AND page = ?
      AND visit_time >= (NOW() - INTERVAL 5 MINUTE)
    LIMIT 1
");
$stmt->bind_param("sss", $userId, $ip, $page);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
  $stmt = $conn->prepare("
        INSERT INTO visitors (user_id, ip, user_agent, page, page_name) 
        VALUES (?, ?, ?, ?, ?)
    ");
  $stmt->bind_param("issss", $userId, $ip, $ua, $page, $pageName);
  $stmt->execute();
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Asoka Baby Store - Toko Perlengkapan Bayi & Anak</title>
  <!-- <script src="https://cdn.tailwindcss.com"></script> -->
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
  <link rel="icon" type="image/png" href="/images/logo1.png" />
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
    integrity="sha512-..." crossorigin="anonymous" referrerpolicy="no-referrer" />
  <link href="https://unpkg.com/splitting/dist/splitting.css" rel="stylesheet" />
  <link
    href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="/src/output2.css">
  <style>
    * {
      font-family: 'Poppins', 'sans-serif';
      font-weight: 400;
    }

    :root {
      --primary: #14b8a6;
      /* Ocean teal */
      --primary-dark: #0ea5a0;
      --primary-50: #f0f9f9;
      --primary-100: #e6f7f6;
      --accent: #60a5fa;
      /* soft sky accent */
      --accent-dark: #3b82f6;
      --accent-50: #eff6ff;
      --accent-100: #dbeafe;
    }

    .nice-scrollbar::-webkit-scrollbar {
      width: 8px;
      height: 8px
    }

    .nice-scrollbar::-webkit-scrollbar-thumb {
      background: #cbd5e1;
      border-radius: 9999px
    }

    .nice-scrollbar::-webkit-scrollbar-track {
      background: #f1f5f9
    }

    .baby-pattern {
      background-image:
        radial-gradient(circle at 20px 20px, rgba(20, 184, 166, 0.06) 2px, transparent 2px),
        radial-gradient(circle at 60px 60px, rgba(96, 165, 250, 0.06) 2px, transparent 2px);
      background-size: 80px 80px;
    }
  </style>
</head>

<body class="bg-gradient-to-br from-[var(--primary-50)] via-white to-[var(--accent-50)] min-h-screen font-sans baby-pattern">
  <!-- Header -->
  <header class="bg-white/95 backdrop-blur-md shadow-lg sticky top-0 z-50 border-b border-pink-100">
    <div class="max-w-6xl mx-auto px-4 py-4 flex items-center justify-between">
      <div class="flex items-center gap-2">
        <img src="public/images/logo.png" alt="Logo Asoka Baby Store" class="w-25 h-8 hover:scale-105 transition-transform duration-300" />
      </div>

      <nav id="menu-mobile" class="grid grid-cols-1 text-center gap-2 p-8
                md:flex md:flex-row md:gap-6 hidden
                transition-all duration-300 ease-in-out transform origin-top
                scale-y-0 opacity-0
                md:scale-y-100 md:opacity-100 md:transform-none
                text-sm font-semibold absolute md:static top-full left-0 w-full md:w-auto
                bg-white/95 backdrop-blur-md md:bg-transparent px-4 md:px-0 py-4 md:py-0 z-50">
        <a href="/index#home-section" class="hover:text-pink-500 transition-colors duration-300 relative group">
          Beranda
          <span class="absolute -bottom-1 left-0 w-0 h-0.5 bg-pink-500 transition-all duration-300 group-hover:w-full"></span>
        </a>
        <a href="/index#gallery-section" class="hover:text-pink-500 transition-colors duration-300 relative group">
          Galeri
          <span class="absolute -bottom-1 left-0 w-0 h-0.5 bg-pink-500 transition-all duration-300 group-hover:w-full"></span>
        </a>
        <a href="/pesan_sekarang" class="hover:text-pink-500 transition-colors duration-300 relative group">
          Kontak
          <span class="absolute -bottom-1 left-0 w-0 h-0.5 bg-pink-500 transition-all duration-300 group-hover:w-full"></span>
        </a>
        <a href="/index#lokasi" class="hover:text-pink-500 transition-colors duration-300 relative group">
          Lokasi
          <span class="absolute -bottom-1 left-0 w-0 h-0.5 bg-pink-500 transition-all duration-300 group-hover:w-full"></span>
        </a>
        <a href="/index#member-section" class="hover:text-pink-500 transition-colors duration-300 relative group" id="openModal">
          Member
          <span class="absolute -bottom-1 left-0 w-0 h-0.5 bg-pink-500 transition-all duration-300 group-hover:w-full"></span>
        </a>
        <a href="/produk" class="hover:text-pink-500 transition-colors duration-300 relative group">
          Produk
          <span class="absolute -bottom-1 left-0 w-0 h-0.5 bg-pink-500 transition-all duration-300 group-hover:w-full"></span>
        </a>
        <a href="/kontak" class="hover:text-pink-500 transition-colors duration-300 relative group">
          Lapor
          <span class="absolute -bottom-1 left-0 w-0 h-0.5 bg-pink-500 transition-all duration-300 group-hover:w-full"></span>
        </a>

      </nav>

      <div class="flex items-center gap-4">
        <!-- Mobile Menu Toggle -->
        <button id="mobileMenuToggle" class="md:hidden text-2xl text-gray-700 hover:text-pink-500 transition-colors duration-300">
          <i class="fas fa-bars"></i>
        </button>

        <div class="relative" id="userMenu">
          <!-- Icon User -->
          <!-- <button id="userButton" class="text-2xl text-pink-600 focus:outline-none md:hidden hover:scale-110 transition-transform duration-300">
                        <i class="fas fa-user-circle"></i>
                    </button> -->
          <!-- Nama user saat login -->
          <span id="userName" class="ml-2 font-medium text-sm user-name cursor-pointer hover:text-pink-500 transition-colors duration-300"></span>
          <!-- Dropdown saat belum login -->
          <!-- <div id="loginDropdown"
                        class="absolute right-0 mt-2 w-40 bg-white/95 backdrop-blur-md shadow-lg rounded-xl py-2 hidden z-50 border border-pink-100">
                        <a href="/log_in" id="loginBtn" class="block px-4 py-2 text-gray-700 hover:bg-pink-50 hover:text-pink-600 transition-colors duration-300"
                            id="link_login">Login</a>
                    </div> -->

          <!-- Dropdown saat sudah login -->
          <div id="profileDropdown"
            class="absolute right-0 mt-2 w-40 bg-white/95 backdrop-blur-md shadow-lg rounded-xl py-2 hidden z-50 border border-pink-100">
            <a href="#" class="block px-4 py-2 text-gray-700 hover:bg-pink-50 hover:text-pink-600 transition-colors duration-300" id="profileBtn">Profile</a>
            <a href="#" id="logoutBtn" class="block px-4 py-2 text-gray-700 hover:bg-pink-50 hover:text-pink-600 transition-colors duration-300">Logout</a>
          </div>
        </div>

        <a href="/log_in" class=" md:inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-pink-500 to-purple-600 text-white rounded-full text-sm font-semibold hover:from-pink-600 hover:to-purple-700 transition-all duration-300 hover:scale-105 shadow-lg" id="btn-klik-login">
          <i class="fas fa-sign-in-alt"></i>
          Login
        </a>
      </div>
    </div>
  </header>
  <div class="max-w-7xl mx-auto px-4 py-8">
    <!-- Hero Banner -->
    <div class="bg-gradient-to-r from-[var(--primary-50)] via-white to-[var(--accent-50)] rounded-3xl p-8 mb-8 text-center shadow-lg border" style="border-color:rgba(20,184,166,0.12)">
      <h1 class="text-3xl md:text-4xl font-bold bg-gradient-to-r from-[var(--primary)] to-[var(--accent)] bg-clip-text text-transparent mb-4"> Koleksi Terbaru untuk Si Buah Hati </h1>
      <p class="text-gray-600 text-lg mb-4">Perlengkapan bayi & anak berkualitas dengan harga terjangkau</p>
      <div class="flex flex-wrap justify-center gap-4 text-sm">
        <span class="bg-white px-4 py-2 rounded-full shadow border" style="border-color:rgba(20,184,166,0.12)"><i class="fas fa-check-circle text-green-500 mr-2"></i>Gratis Bungkus Kado</span>
        <span class="bg-white px-4 py-2 rounded-full shadow border" style="border-color:rgba(96,165,250,0.12)"><i class="fas fa-check-circle text-green-500 mr-2"></i>Diskon Member</span>
        <span class="bg-white px-4 py-2 rounded-full shadow border" style="border-color:rgba(20,184,166,0.12)"><i class="fas fa-check-circle text-green-500 mr-2"></i>Tukar Poin Hadiah</span>
      </div>
    </div>

    <!-- Categories -->
    <div class="flex flex-col md:flex-row items-stretch md:items-center justify-between gap-4 mb-6">
      <!-- filter harga -->
      <div class="w-full md:w-auto">
        <label for="priceFilter" class="sr-only">Urutkan Harga</label>
        <select id="priceFilter"
          class="w-full md:w-auto rounded-xl border border-gray-200 px-3 py-2 text-sm focus:ring-2 focus:ring-pink-300">
          <option value="">Urutkan Harga</option>
          <option value="termurah">Harga: Termurah</option>
          <option value="termahal">Harga: Termahal</option>
        </select>
      </div>

      <!-- tombol lihat semua -->
      <div class="w-full md:w-auto">
        <a href="/log_in"
          class="w-full md:w-auto inline-flex justify-center items-center gap-2 px-4 py-2 bg-gradient-to-r from-pink-500 to-purple-600 text-white rounded-xl text-sm font-semibold hover:from-pink-600 hover:to-purple-700 transition-all duration-200 shadow">
          <i class="fas fa-eye"></i>
          Lihat Semua Produk
        </a>
      </div>
    </div>


    <!-- Products Grid -->
    <div id="productsGrid" class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-5 xl:grid-cols-5 gap-6"></div>

    <!-- Empty State -->
    <div id="emptyState" class="hidden text-center py-20">
      <div class="mx-auto w-16 h-16 rounded-2xl bg-white shadow grid place-items-center mb-4">
        <i class="fas fa-search text-2xl text-gray-400"></i>
      </div>
      <p class="text-slate-600">Tidak ada produk yang cocok dengan pencarian.</p>
    </div>
  </div>

  <!-- Product Detail Modal -->
  <div id="productModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-4xl w-full max-h-[90vh] overflow-y-auto shadow-2xl">
      <!-- Modal Header -->
      <div class="flex justify-between items-center p-6 border-b border-gray-200">
        <h2 id="modalTitle" class="text-2xl font-bold text-gray-800">Detail Produk</h2>
        <button id="closeModal" class="text-gray-500 hover:text-gray-700 text-2xl hover:scale-110 transition-transform duration-300" aria-label="Tutup">
          <i class="fas fa-times"></i>
        </button>
      </div>
      <!-- Modal Content -->
      <div id="modalContent" class="p-6 nice-scrollbar"></div>
    </div>
  </div>

  <div class="border-t my-4 border-gray-300"></div>

  <script src="/src/js/products/products_pubs/main.js" type="module"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <!-- Floating Message Button Component -->
  <?php include 'src/component/floating_message.php'; ?>

  <!-- Footer -->
  <?php include 'src/component/footer.php'; ?>
  <script>
    // Mobile menu toggle functionality
    document.addEventListener('DOMContentLoaded', function() {
      const mobileMenuToggle = document.getElementById('mobileMenuToggle');
      const menuMobile = document.getElementById('menu-mobile');

      if (mobileMenuToggle && menuMobile) {
        mobileMenuToggle.addEventListener('click', function() {
          const isHidden = menuMobile.classList.contains('hidden');

          if (isHidden) {
            menuMobile.classList.remove('hidden', 'scale-y-0', 'opacity-0');
            menuMobile.classList.add('scale-y-100', 'opacity-100');
          } else {
            menuMobile.classList.add('scale-y-0', 'opacity-0');
            menuMobile.classList.remove('scale-y-100', 'opacity-100');

            setTimeout(() => {
              menuMobile.classList.add('hidden');
            }, 300);
          }
        });
      }

      // Close mobile menu when clicking menu items
      const menuLinks = menuMobile?.querySelectorAll('a');
      menuLinks?.forEach(link => {
        link.addEventListener('click', () => {
          menuMobile.classList.add('scale-y-0', 'opacity-0');
          menuMobile.classList.remove('scale-y-100', 'opacity-100');
          setTimeout(() => {
            menuMobile.classList.add('hidden');
          }, 300);
        });
      });
    });
  </script>
</body>

</html>