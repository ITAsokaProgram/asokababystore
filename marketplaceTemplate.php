<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Asoka Baby Store - Toko Perlengkapan Bayi & Anak</title>
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
  <link rel="icon" type="image/png" href="/images/logo1.png" />
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
    crossorigin="anonymous" referrerpolicy="no-referrer" />
  <link href="https://unpkg.com/splitting/dist/splitting.css" rel="stylesheet" />
  <link
    href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap"
    rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    * {
      font-family: 'Poppins', sans-serif;
      font-weight: 400;
    }

    :root {
      /* Ocean Wave Color Palette */
      --primary: #0891b2;           /* cyan-600 - ocean blue */
      --primary-dark: #0e7490;     /* cyan-700 - deep ocean */
      --primary-light: #22d3ee;    /* cyan-400 - light ocean */
      --secondary: #0d9488;        /* teal-600 - sea green */
      --secondary-dark: #0f766e;   /* teal-700 - deep sea green */
      --accent: #06b6d4;           /* cyan-500 - ocean accent */
      --gradient: linear-gradient(135deg, #0891b2 0%, #0d9488 50%, #06b6d4 100%);
      --ocean-blue: #0ea5e9;       /* sky-500 */
      --sea-foam: #67e8f9;         /* cyan-300 */
      --deep-blue: #0c4a6e;        /* sky-900 */
      --wave-gradient: linear-gradient(135deg, #0891b2 0%, #0d9488 25%, #06b6d4 50%, #22d3ee 75%, #67e8f9 100%);
    }

    .ocean-gradient {
      background: var(--wave-gradient);
    }

    .ocean-card-gradient {
      background: linear-gradient(145deg, #ffffff 0%, #f0f9ff 100%);
    }

    .glass-effect {
      background: rgba(255, 255, 255, 0.25);
      backdrop-filter: blur(10px);
      -webkit-backdrop-filter: blur(10px);
      border: 1px solid rgba(8, 145, 178, 0.18);
    }

    .nice-scrollbar::-webkit-scrollbar {
      width: 8px;
      height: 8px;
    }

    .nice-scrollbar::-webkit-scrollbar-thumb {
      background: var(--wave-gradient);
      border-radius: 9999px;
    }

    .nice-scrollbar::-webkit-scrollbar-track {
      background: #f0f9ff;
      border-radius: 9999px;
    }

    .ocean-pattern {
      background-image:
        radial-gradient(circle at 25px 25px, rgba(8, 145, 178, 0.1) 3px, transparent 3px),
        radial-gradient(circle at 75px 75px, rgba(13, 148, 136, 0.1) 3px, transparent 3px),
        radial-gradient(circle at 125px 25px, rgba(6, 182, 212, 0.1) 2px, transparent 2px);
      background-size: 150px 150px, 100px 100px, 175px 175px;
    }

    .floating-element {
      animation: float 6s ease-in-out infinite;
    }

    @keyframes float {
      0%, 100% { transform: translateY(0px); }
      50% { transform: translateY(-20px); }
    }

    .hover-lift {
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .hover-lift:hover {
      transform: translateY(-10px);
      box-shadow: 0 25px 50px -12px rgba(8, 145, 178, 0.25);
    }

    .product-card {
      background: linear-gradient(145deg, #ffffff, #f0f9ff);
      border: 1px solid rgba(8, 145, 178, 0.1);
      transition: all 0.3s ease;
    }

    .product-card:hover {
      border-color: rgba(8, 145, 178, 0.3);
      box-shadow: 0 20px 40px -12px rgba(8, 145, 178, 0.25);
    }

    .category-chip {
      background: linear-gradient(135deg, rgba(8, 145, 178, 0.1), rgba(13, 148, 136, 0.1));
      border: 1px solid rgba(8, 145, 178, 0.2);
      transition: all 0.3s ease;
    }

    .category-chip:hover {
      background: linear-gradient(135deg, rgba(8, 145, 178, 0.2), rgba(13, 148, 136, 0.2));
      transform: scale(1.05);
    }

    .search-bar {
      background: rgba(255, 255, 255, 0.9);
      backdrop-filter: blur(10px);
      border: 2px solid transparent;
      background-clip: padding-box;
    }

    .search-bar:focus {
      background: rgba(255, 255, 255, 0.95);
      border-color: var(--primary);
      box-shadow: 0 0 0 4px rgba(8, 145, 178, 0.1);
    }

    @media (max-width: 768px) {
      .mobile-optimized {
        padding: 1rem;
      }
      
      .hero-mobile {
        background-size: cover;
        min-height: 50vh;
      }
    }

    .promotion-banner {
      background: var(--wave-gradient);
      background-size: 400% 400%;
      animation: oceanWave 8s ease infinite;
    }

    @keyframes oceanWave {
      0% { background-position: 0% 50%; }
      50% { background-position: 100% 50%; }
      100% { background-position: 0% 50%; }
    }

    .cart-icon {
      position: relative;
    }

    .cart-badge {
      position: absolute;
      top: -8px;
      right: -8px;
      background: var(--primary);
      color: white;
      border-radius: 50%;
      width: 18px;
      height: 18px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 10px;
      font-weight: bold;
    }

    .mobile-bottom-nav {
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(10px);
      border-top: 1px solid rgba(8, 145, 178, 0.1);
    }

    /* Dropdown Styles */
    .dropdown {
      position: relative;
    }

    .dropdown-menu {
      position: absolute;
      top: 100%;
      right: 0;
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(8, 145, 178, 0.2);
      border-radius: 1rem;
      box-shadow: 0 20px 25px -5px rgba(8, 145, 178, 0.1);
      min-width: 240px;
      opacity: 0;
      visibility: hidden;
      transform: translateY(-10px);
      transition: all 0.3s ease;
      z-index: 100;
    }

    .dropdown:hover .dropdown-menu,
    .dropdown-menu:hover {
      opacity: 1;
      visibility: visible;
      transform: translateY(0);
    }

    .dropdown-item {
      padding: 12px 16px;
      display: flex;
      align-items: center;
      gap: 12px;
      transition: all 0.2s ease;
      border-radius: 0.75rem;
      margin: 4px 8px;
    }

    .dropdown-item:hover {
      background: linear-gradient(135deg, rgba(8, 145, 178, 0.1), rgba(13, 148, 136, 0.1));
      transform: translateX(4px);
    }

    .user-avatar {
      width: 40px;
      height: 40px;
      background: var(--wave-gradient);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
      border: 2px solid rgba(255, 255, 255, 0.2);
    }

    .user-avatar:hover {
      transform: scale(1.05);
      box-shadow: 0 8px 25px -8px rgba(8, 145, 178, 0.4);
    }

    .ocean-btn {
      background: var(--wave-gradient);
      color: white;
      border: none;
      border-radius: 0.75rem;
      padding: 0.5rem 1rem;
      font-weight: 500;
      transition: all 0.3s ease;
      cursor: pointer;
    }

    .ocean-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 25px -8px rgba(8, 145, 178, 0.4);
    }
  </style>
</head>

<body class="bg-gradient-to-br from-cyan-50 via-white to-sky-50 min-h-screen ocean-pattern">
  
  <!-- Header with Ocean Wave Theme -->
  <header class="glass-effect sticky top-0 z-50 border-b border-cyan-100">
    <div class="max-w-7xl mx-auto px-4 py-4">
      <!-- Desktop Header -->
      <div class="hidden md:flex items-center gap-8">
        <!-- Logo -->
        <div class="flex items-center gap-3 flex-shrink-0">
          <img src="public/images/logo.png" alt="Logo Asoka Baby Store" class="w-10 h-10 hover:scale-105 transition-transform duration-300" />
          <div>
            <h1 class="text-xl font-bold text-gray-800">Asoka Baby Store</h1>
            <p class="text-xs text-gray-600">Perlengkapan Bayi & Anak</p>
          </div>
        </div>

        <!-- Search Bar -->
        <div class="flex-1 max-w-2xl">
          <div class="relative">
            <input type="text" id="searchInput" placeholder="Cari produk bayi & anak..." 
                   class="search-bar w-full pl-12 pr-20 py-3 rounded-2xl text-sm focus:outline-none transition-all duration-300">
            <div class="absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400">
              <i class="fas fa-search"></i>
            </div>
            <button class="absolute right-2 top-1/2 transform -translate-y-1/2 ocean-btn text-sm">
              <i class="fas fa-search mr-1"></i>
              Cari
            </button>
          </div>
        </div>

        <!-- User Dropdown -->
        <div class="flex items-center gap-4">
          <!-- Cart Icon -->
          <div class="cart-icon cursor-pointer">
            <i class="fas fa-shopping-cart text-xl text-gray-600 hover:text-cyan-600 transition-colors"></i>
            <div class="cart-badge">3</div>
          </div>

          <!-- User Dropdown -->
          <div class="dropdown">
            <div class="user-avatar">
              <i class="fas fa-user"></i>
            </div>
            <div class="dropdown-menu">
              <div class="p-4 border-b border-gray-100">
                <div class="flex items-center gap-3">
                  <div class="w-12 h-12 bg-gradient-to-r from-cyan-500 to-teal-500 rounded-full flex items-center justify-center text-white font-semibold">
                    JD
                  </div>
                  <div>
                    <h4 class="font-semibold text-gray-800">John Doe</h4>
                    <p class="text-sm text-gray-500">Premium Member</p>
                  </div>
                </div>
              </div>
              <div class="py-2">
                <a href="#" class="dropdown-item text-gray-700 hover:text-cyan-600">
                  <i class="fas fa-user-circle text-cyan-500"></i>
                  <span>Profil Saya</span>
                </a>
                <a href="#" class="dropdown-item text-gray-700 hover:text-cyan-600">
                  <i class="fas fa-shopping-bag text-teal-500"></i>
                  <span>Pesanan Saya</span>
                </a>
                <a href="#" class="dropdown-item text-gray-700 hover:text-cyan-600">
                  <i class="fas fa-heart text-pink-500"></i>
                  <span>Wishlist</span>
                </a>
                <a href="#" class="dropdown-item text-gray-700 hover:text-cyan-600">
                  <i class="fas fa-star text-yellow-500"></i>
                  <span>Poin & Reward</span>
                </a>
                <a href="#" class="dropdown-item text-gray-700 hover:text-cyan-600">
                  <i class="fas fa-cog text-gray-500"></i>
                  <span>Pengaturan</span>
                </a>
                <div class="border-t border-gray-100 mt-2 pt-2">
                  <a href="#" class="dropdown-item text-red-600 hover:text-red-700">
                    <i class="fas fa-sign-out-alt text-red-500"></i>
                    <span>Keluar</span>
                  </a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Mobile Header -->
      <div class="md:hidden">
        <!-- Logo and User Actions -->
        <div class="flex items-center justify-between mb-3">
          <div class="flex items-center gap-3">
            <img src="public/images/logo.png" alt="Logo" class="w-8 h-8" />
            <div>
              <h1 class="text-lg font-bold text-gray-800">Asoka</h1>
              <p class="text-xs text-gray-600">Baby Store</p>
            </div>
          </div>
          
          <!-- Mobile User Actions -->
          <div class="flex items-center gap-3">
            <div class="cart-icon">
              <i class="fas fa-shopping-cart text-lg text-gray-600"></i>
              <div class="cart-badge">3</div>
            </div>
            <div class="dropdown">
              <div class="user-avatar w-8 h-8 text-xs">
                <i class="fas fa-user"></i>
              </div>
              <div class="dropdown-menu right-0 w-48">
                <div class="py-2">
                  <a href="#" class="dropdown-item text-sm">
                    <i class="fas fa-user-circle text-cyan-500"></i>
                    <span>Profil</span>
                  </a>
                  <a href="#" class="dropdown-item text-sm">
                    <i class="fas fa-shopping-bag text-teal-500"></i>
                    <span>Pesanan</span>
                  </a>
                  <a href="#" class="dropdown-item text-sm">
                    <i class="fas fa-sign-out-alt text-red-500"></i>
                    <span>Keluar</span>
                  </a>
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Mobile Search -->
        <div class="relative">
          <input type="text" placeholder="Cari produk bayi & anak..." 
                 class="search-bar w-full pl-10 pr-16 py-2.5 rounded-xl text-sm focus:outline-none">
          <div class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400">
            <i class="fas fa-search"></i>
          </div>
          <button class="absolute right-2 top-1/2 transform -translate-y-1/2 ocean-btn text-xs px-3 py-1">
            <i class="fas fa-search"></i>
          </button>
        </div>
      </div>
    </div>
  </header>

  <div class="max-w-7xl mx-auto px-4 py-6 md:py-8">
    <!-- Promotion Banner with Ocean Wave -->
    <div class="promotion-banner rounded-3xl p-6 md:p-8 mb-8 text-center text-white shadow-xl">
      <div class="floating-element">
        <h1 class="text-2xl md:text-4xl font-bold mb-4">
          <i class="fas fa-baby mr-2"></i>Koleksi Terbaru untuk Si Buah Hati<i class="fas fa-child ml-2"></i>
        </h1>
        <p class="text-base md:text-lg mb-6 opacity-90">
          Perlengkapan bayi & anak berkualitas dengan harga terjangkau
        </p>
        <div class="flex flex-wrap justify-center gap-3 md:gap-4 text-xs md:text-sm">
          <span class="glass-effect px-4 py-2 rounded-full">
            <i class="fas fa-gift mr-2"></i>Gratis Bungkus Kado
          </span>
          <span class="glass-effect px-4 py-2 rounded-full">
            <i class="fas fa-percent mr-2"></i>Diskon Member
          </span>
          <span class="glass-effect px-4 py-2 rounded-full">
            <i class="fas fa-star mr-2"></i>Tukar Poin Hadiah
          </span>
        </div>
      </div>
    </div>

    <!-- Categories with Ocean Theme -->
    <div class="mb-8">
      <h2 class="text-xl md:text-2xl font-bold text-gray-800 mb-4 text-center">
        <i class="fas fa-tags mr-2 text-cyan-500"></i>Kategori Pilihan
      </h2>
      <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-3 md:gap-4">
        <div class="category-chip rounded-2xl p-4 text-center hover-lift cursor-pointer">
          <div class="text-2xl md:text-3xl mb-2 text-cyan-500">
            <i class="fas fa-tshirt"></i>
          </div>
          <p class="text-xs md:text-sm font-medium text-gray-700">Pakaian Bayi</p>
        </div>
        <div class="category-chip rounded-2xl p-4 text-center hover-lift cursor-pointer">
          <div class="text-2xl md:text-3xl mb-2 text-teal-500">
            <i class="fas fa-dice"></i>
          </div>
          <p class="text-xs md:text-sm font-medium text-gray-700">Mainan</p>
        </div>
        <div class="category-chip rounded-2xl p-4 text-center hover-lift cursor-pointer">
          <div class="text-2xl md:text-3xl mb-2 text-sky-500">
            <i class="fas fa-baby-carriage"></i>
          </div>
          <p class="text-xs md:text-sm font-medium text-gray-700">Perlengkapan</p>
        </div>
        <div class="category-chip rounded-2xl p-4 text-center hover-lift cursor-pointer">
          <div class="text-2xl md:text-3xl mb-2 text-indigo-500">
            <i class="fas fa-child"></i>
          </div>
          <p class="text-xs md:text-sm font-medium text-gray-700">Fashion Anak</p>
        </div>
        <div class="category-chip rounded-2xl p-4 text-center hover-lift cursor-pointer">
          <div class="text-2xl md:text-3xl mb-2 text-cyan-600">
            <i class="fas fa-shopping-bag"></i>
          </div>
          <p class="text-xs md:text-sm font-medium text-gray-700">Tas & Sepatu</p>
        </div>
        <div class="category-chip rounded-2xl p-4 text-center hover-lift cursor-pointer">
          <div class="text-2xl md:text-3xl mb-2 text-teal-600">
            <i class="fas fa-book"></i>
          </div>
          <p class="text-xs md:text-sm font-medium text-gray-700">Edukasi</p>
        </div>
      </div>
    </div>

    <!-- Filter & Sort -->
    <div class="flex flex-col md:flex-row items-stretch md:items-center justify-between gap-4 mb-6">
      <div class="flex flex-col sm:flex-row gap-3 md:gap-4">
        <select id="priceFilter" class="rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:ring-2 focus:ring-cyan-300 focus:border-cyan-300">
          <option value=""><i class="fas fa-dollar-sign"></i> Urutkan Harga</option>
          <option value="termurah">Harga: Termurah</option>
          <option value="termahal">Harga: Termahal</option>
        </select>
        <select class="rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:ring-2 focus:ring-cyan-300 focus:border-cyan-300">
          <option value="">Kategori</option>
          <option value="bayi">Pakaian Bayi</option>
          <option value="mainan">Mainan</option>
          <option value="perlengkapan">Perlengkapan</option>
        </select>
      </div>
    </div>

    <!-- Products Grid -->
    <div id="productsGrid" class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4 md:gap-6">
      
      <!-- Sample Product Cards with Ocean Theme -->
      <div class="product-card rounded-2xl overflow-hidden shadow-lg hover-lift">
        <div class="relative">
          <img src="https://via.placeholder.com/300x300/0891b2/ffffff?text=Baby+Clothes" alt="Produk" class="w-full h-40 md:h-48 object-cover">
          <div class="absolute top-2 left-2 bg-red-500 text-white px-2 py-1 rounded-full text-xs font-semibold">
            -20%
          </div>
          <button class="absolute top-2 right-2 w-8 h-8 bg-white rounded-full shadow-md flex items-center justify-center text-gray-600 hover:text-red-500 transition-colors">
            <i class="fas fa-heart text-sm"></i>
          </button>
        </div>
        <div class="p-3 md:p-4">
          <h3 class="font-semibold text-sm md:text-base text-gray-800 mb-1 line-clamp-2">
            Baju Bayi Lucu Set 3pcs
          </h3>
          <div class="flex items-center gap-1 mb-2">
            <div class="flex text-yellow-400 text-xs">
              <i class="fas fa-star"></i>
              <i class="fas fa-star"></i>
              <i class="fas fa-star"></i>
              <i class="fas fa-star"></i>
              <i class="fas fa-star"></i>
            </div>
            <span class="text-xs text-gray-500">(28)</span>
          </div>
          <div class="flex items-center justify-between mb-3">
            <div>
              <span class="text-xs text-gray-400 line-through">Rp 2.500.000</span>
              <div class="text-lg font-bold text-sky-600">Rp 1.950.000</div>
            </div>
          </div>
          <div class="flex gap-2">
            <button class="flex-1 ocean-btn text-xs font-medium">
              <i class="fas fa-shopping-cart mr-1"></i>
              Beli
            </button>
            <button class="bg-gray-100 text-gray-600 py-2 px-3 rounded-xl text-xs hover:bg-gray-200 transition-colors">
              <i class="fas fa-eye"></i>
            </button>
          </div>
        </div>
      </div>

      <!-- Fourth Product Card -->
      <div class="product-card rounded-2xl overflow-hidden shadow-lg hover-lift">
        <div class="relative">
          <img src="https://via.placeholder.com/300x300/22d3ee/ffffff?text=Baby+Shoes" alt="Produk" class="w-full h-40 md:h-48 object-cover">
          <div class="absolute top-2 left-2 bg-purple-500 text-white px-2 py-1 rounded-full text-xs font-semibold">
            Sale
          </div>
          <button class="absolute top-2 right-2 w-8 h-8 bg-white rounded-full shadow-md flex items-center justify-center text-gray-600 hover:text-red-500 transition-colors">
            <i class="fas fa-heart text-sm"></i>
          </button>
        </div>
        <div class="p-3 md:p-4">
          <h3 class="font-semibold text-sm md:text-base text-gray-800 mb-1">
            Sepatu Bayi Lucu
          </h3>
          <div class="flex items-center gap-1 mb-2">
            <div class="flex text-yellow-400 text-xs">
              <i class="fas fa-star"></i>
              <i class="fas fa-star"></i>
              <i class="fas fa-star"></i>
              <i class="fas fa-star"></i>
              <i class="fas fa-star-half-alt"></i>
            </div>
            <span class="text-xs text-gray-500">(67)</span>
          </div>
          <div class="flex items-center justify-between mb-3">
            <div>
              <span class="text-xs text-gray-400 line-through">Rp 95.000</span>
              <div class="text-lg font-bold text-cyan-400">Rp 65.000</div>
            </div>
          </div>
          <div class="flex gap-2">
            <button class="flex-1 ocean-btn text-xs font-medium">
              <i class="fas fa-shopping-cart mr-1"></i>
              Beli
            </button>
            <button class="bg-gray-100 text-gray-600 py-2 px-3 rounded-xl text-xs hover:bg-gray-200 transition-colors">
              <i class="fas fa-eye"></i>
            </button>
          </div>
        </div>
      </div>

      <!-- Fifth Product Card -->
      <div class="product-card rounded-2xl overflow-hidden shadow-lg hover-lift">
        <div class="relative">
          <img src="https://via.placeholder.com/300x300/67e8f9/000000?text=Baby+Books" alt="Produk" class="w-full h-40 md:h-48 object-cover">
          <div class="absolute top-2 left-2 bg-orange-500 text-white px-2 py-1 rounded-full text-xs font-semibold">
            Hot
          </div>
          <button class="absolute top-2 right-2 w-8 h-8 bg-white rounded-full shadow-md flex items-center justify-center text-gray-600 hover:text-red-500 transition-colors">
            <i class="fas fa-heart text-sm"></i>
          </button>
        </div>
        <div class="p-3 md:p-4">
          <h3 class="font-semibold text-sm md:text-base text-gray-800 mb-1">
            Buku Edukasi Anak
          </h3>
          <div class="flex items-center gap-1 mb-2">
            <div class="flex text-yellow-400 text-xs">
              <i class="fas fa-star"></i>
              <i class="fas fa-star"></i>
              <i class="fas fa-star"></i>
              <i class="fas fa-star"></i>
              <i class="fas fa-star"></i>
            </div>
            <span class="text-xs text-gray-500">(89)</span>
          </div>
          <div class="flex items-center justify-between mb-3">
            <div>
              <div class="text-lg font-bold text-teal-600">Rp 45.000</div>
            </div>
          </div>
          <div class="flex gap-2">
            <button class="flex-1 ocean-btn text-xs font-medium">
              <i class="fas fa-shopping-cart mr-1"></i>
              Beli
            </button>
            <button class="bg-gray-100 text-gray-600 py-2 px-3 rounded-xl text-xs hover:bg-gray-200 transition-colors">
              <i class="fas fa-eye"></i>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Load More Button -->
    <div class="text-center mt-8">
      <button class="ocean-btn px-8 py-3 text-sm font-medium rounded-2xl hover:scale-105 transition-transform">
        <i class="fas fa-plus mr-2"></i>
        Muat Lebih Banyak
      </button>
    </div>
  </div>

  <!-- Footer with Ocean Theme -->
  <footer class="ocean-gradient mt-16 text-white">
    <div class="max-w-7xl mx-auto px-4 py-12">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
        <!-- Company Info -->
        <div>
          <div class="flex items-center gap-3 mb-4">
            <img src="public/images/logo.png" alt="Logo" class="w-10 h-10" />
            <div>
              <h3 class="text-lg font-bold">Asoka Baby Store</h3>
              <p class="text-sm opacity-80">Perlengkapan Bayi & Anak</p>
            </div>
          </div>
          <p class="text-sm opacity-90 mb-4">
            Toko terpercaya untuk segala kebutuhan bayi dan anak dengan kualitas terbaik dan harga terjangkau.
          </p>
          <div class="flex gap-3">
            <a href="#" class="w-8 h-8 bg-white/20 rounded-full flex items-center justify-center hover:bg-white/30 transition-colors">
              <i class="fab fa-facebook-f"></i>
            </a>
            <a href="#" class="w-8 h-8 bg-white/20 rounded-full flex items-center justify-center hover:bg-white/30 transition-colors">
              <i class="fab fa-instagram"></i>
            </a>
            <a href="#" class="w-8 h-8 bg-white/20 rounded-full flex items-center justify-center hover:bg-white/30 transition-colors">
              <i class="fab fa-whatsapp"></i>
            </a>
          </div>
        </div>

        <!-- Quick Links -->
        <div>
          <h4 class="font-semibold mb-4">Navigasi</h4>
          <ul class="space-y-2 text-sm">
            <li><a href="#" class="opacity-80 hover:opacity-100 transition-opacity">Beranda</a></li>
            <li><a href="#" class="opacity-80 hover:opacity-100 transition-opacity">Kategori</a></li>
            <li><a href="#" class="opacity-80 hover:opacity-100 transition-opacity">Promo</a></li>
            <li><a href="#" class="opacity-80 hover:opacity-100 transition-opacity">Tentang Kami</a></li>
            <li><a href="#" class="opacity-80 hover:opacity-100 transition-opacity">Kontak</a></li>
          </ul>
        </div>

        <!-- Customer Service -->
        <div>
          <h4 class="font-semibold mb-4">Layanan</h4>
          <ul class="space-y-2 text-sm">
            <li><a href="#" class="opacity-80 hover:opacity-100 transition-opacity">Pusat Bantuan</a></li>
            <li><a href="#" class="opacity-80 hover:opacity-100 transition-opacity">Cara Berbelanja</a></li>
            <li><a href="#" class="opacity-80 hover:opacity-100 transition-opacity">Kebijakan Return</a></li>
            <li><a href="#" class="opacity-80 hover:opacity-100 transition-opacity">Syarat & Ketentuan</a></li>
            <li><a href="#" class="opacity-80 hover:opacity-100 transition-opacity">Kebijakan Privasi</a></li>
          </ul>
        </div>

        <!-- Contact Info -->
        <div>
          <h4 class="font-semibold mb-4">Hubungi Kami</h4>
          <div class="space-y-3 text-sm">
            <div class="flex items-center gap-3">
              <i class="fas fa-map-marker-alt opacity-80"></i>
              <span class="opacity-90">Jakarta, Indonesia</span>
            </div>
            <div class="flex items-center gap-3">
              <i class="fas fa-phone opacity-80"></i>
              <span class="opacity-90">+62 812-3456-7890</span>
            </div>
            <div class="flex items-center gap-3">
              <i class="fas fa-envelope opacity-80"></i>
              <span class="opacity-90">info@asokababystore.com</span>
            </div>
            <div class="flex items-center gap-3">
              <i class="fas fa-clock opacity-80"></i>
              <span class="opacity-90">09:00 - 21:00 WIB</span>
            </div>
          </div>
        </div>
      </div>
      
      <div class="border-t border-white/20 mt-8 pt-6 text-center">
        <p class="text-sm opacity-80">
          © 2024 Asoka Baby Store. All rights reserved.
        </p>
      </div>
    </div>
  </footer>

  <!-- Mobile Bottom Navigation -->
  <div class="md:hidden mobile-bottom-nav fixed bottom-0 left-0 right-0 z-40">
    <div class="flex items-center justify-around py-3">
      <a href="#" class="flex flex-col items-center gap-1 text-gray-600">
        <i class="fas fa-home text-lg"></i>
        <span class="text-xs">Home</span>
      </a>
      <a href="#" class="flex flex-col items-center gap-1 text-gray-600">
        <i class="fas fa-th-large text-lg"></i>
        <span class="text-xs">Kategori</span>
      </a>
      <a href="#" class="flex flex-col items-center gap-1 text-gray-600 relative">
        <i class="fas fa-shopping-cart text-lg"></i>
        <div class="cart-badge">3</div>
        <span class="text-xs">Keranjang</span>
      </a>
      <a href="#" class="flex flex-col items-center gap-1 text-gray-600">
        <i class="fas fa-user text-lg"></i>
        <span class="text-xs">Profil</span>
      </a>
    </div>
  </div>

  <!-- JavaScript for interactions -->
  <script>
    // Simple cart functionality
    let cartCount = 3;
    
    // Update cart badge
    function updateCartBadge() {
      const badges = document.querySelectorAll('.cart-badge');
      badges.forEach(badge => {
        badge.textContent = cartCount;
      });
    }

    // Add to cart functionality
    document.addEventListener('click', function(e) {
      if (e.target.closest('.ocean-btn') && e.target.closest('.ocean-btn').textContent.includes('Beli')) {
        e.preventDefault();
        cartCount++;
        updateCartBadge();
        
        // Show temporary feedback
        const btn = e.target.closest('.ocean-btn');
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-check mr-1"></i>Ditambahkan!';
        btn.style.background = '#10b981';
        
        setTimeout(() => {
          btn.innerHTML = originalText;
          btn.style.background = '';
        }, 2000);
      }
    });

    // Search functionality
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
      searchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
          e.preventDefault();
          alert('Pencarian: ' + this.value);
        }
      });
    }

    // Price filter functionality
    const priceFilter = document.getElementById('priceFilter');
    if (priceFilter) {
      priceFilter.addEventListener('change', function() {
        const value = this.value;
        const productsGrid = document.getElementById('productsGrid');
        const products = Array.from(productsGrid.children);
        
        if (value === 'termurah') {
          products.sort((a, b) => {
            const priceA = parseInt(a.querySelector('.text-lg.font-bold').textContent.replace(/[^\d]/g, ''));
            const priceB = parseInt(b.querySelector('.text-lg.font-bold').textContent.replace(/[^\d]/g, ''));
            return priceA - priceB;
          });
        } else if (value === 'termahal') {
          products.sort((a, b) => {
            const priceA = parseInt(a.querySelector('.text-lg.font-bold').textContent.replace(/[^\d]/g, ''));
            const priceB = parseInt(b.querySelector('.text-lg.font-bold').textContent.replace(/[^\d]/g, ''));
            return priceB - priceA;
          });
        }
        
        if (value) {
          products.forEach(product => productsGrid.appendChild(product));
        }
      });
    }
  </script>
</body>
</html>