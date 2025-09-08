<?php
require_once __DIR__ . '/../aa_kon_sett.php';
require_once __DIR__ . '/../src/auth/middleware_login.php';

$token = $_COOKIE['token'];
$userId = null;
$ip = $_SERVER['REMOTE_ADDR'];
$ua = $_SERVER['HTTP_USER_AGENT'];
$page = $_SERVER['REQUEST_URI'];
$verify = verify_token($token);
$pageName = "Customer Produk";
if ($token) {
    $userId = $verify->id;
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
} else {
    header("Location:/log_in");
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

        /* Header Search Styles */
        .search-container {
            position: relative;
        }

        .search-input {
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
        }

        .search-input:focus {
            background: rgba(255, 255, 255, 1);
            box-shadow: 0 10px 25px rgba(20, 184, 166, 0.15);
        }

        /* Dropdown Animation */
        .dropdown-menu {
            transform: translateY(-10px) scale(0.95);
            opacity: 0;
            visibility: hidden;
            transition: all 0.2s ease;
        }

        .dropdown-menu.show {
            transform: translateY(0) scale(1);
            opacity: 1;
            visibility: visible;
        }

        /* Mobile header adjustments */
        @media (max-width: 768px) {
            .mobile-search {
                position: absolute;
                top: 100%;
                left: 0;
                right: 0;
                background: white;
                border-radius: 0 0 20px 20px;
                box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
                transform: translateY(-10px);
                opacity: 0;
                visibility: hidden;
                transition: all 0.3s ease;
                z-index: 50;
            }

            .mobile-search.show {
                transform: translateY(0);
                opacity: 1;
                visibility: visible;
            }
        }
    </style>
</head>

<body class="bg-gradient-to-br from-[var(--primary-50)] via-white to-[var(--accent-50)] min-h-screen font-sans baby-pattern">
    <!-- Header -->
    <header class="sticky top-0 z-40 bg-white/90 backdrop-blur-xl border-b border-gray-200/50 shadow-sm">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex items-center justify-between h-16 md:h-20">
                <a href="/customer/home"
                    class="flex items-center space-x-2 group p-2 rounded-xl hover:bg-gray-100 transition-colors">
                    <i class="fas fa-arrow-left text-gray-700 group-hover:text-[var(--primary)] text-lg"></i>
                    <span class="hidden sm:inline text-sm font-medium text-gray-700 group-hover:text-[var(--primary)]">
                        Kembali
                    </span>
                </a>

                <!-- Desktop Search Bar -->
                <div class="hidden md:flex flex-1 max-w-2xl mx-8">
                    <div class="search-container w-full relative">
                        <div class="relative">
                            <input
                                type="text"
                                id="searchInput"
                                placeholder="Cari produk bayi & anak..."
                                class="search-input w-full pl-12 pr-4 py-3 rounded-2xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-[var(--primary)]/30 focus:border-[var(--primary)] text-sm">
                            <i class="fas fa-search absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        </div>
                    </div>
                </div>

                <!-- Right Side Actions -->
                <div class="flex items-center space-x-2 md:space-x-4">
                    <!-- Mobile Search Toggle -->
                    <button id="mobileSearchToggle" class="md:hidden p-2 rounded-xl hover:bg-gray-100 transition-colors">
                        <i class="fas fa-search text-gray-600"></i>
                    </button>

                    <!-- Cart -->
                    <button class="relative p-2 rounded-xl hover:bg-gray-100 transition-colors" id="cartBtn">
                        <i class="fas fa-shopping-cart text-gray-600"></i>
                        <!-- <span class="absolute -top-1 -right-1 bg-[var(--accent)] text-white text-xs rounded-full w-5 h-5 flex items-center justify-center"></span> -->
                    </button>

                    <!-- User Dropdown -->
                    <div class="relative">
                        <button id="userDropdownToggle" class="flex items-center space-x-2 p-2 rounded-xl hover:bg-gray-100 transition-colors">
                            <div class="w-8 h-8 bg-gradient-to-br from-[var(--primary)] to-[var(--accent)] rounded-full flex items-center justify-center">
                                <i class="fas fa-user text-white text-sm"></i>
                            </div>
                            <span class="hidden md:inline text-sm text-gray-700"><?php echo $verify->nama; ?></span>
                            <i class="fas fa-chevron-down text-xs text-gray-500"></i>
                        </button>

                        <!-- Dropdown Menu -->
                        <div id="userDropdownMenu" class="dropdown-menu absolute right-0 mt-2 w-56 bg-white rounded-2xl shadow-xl border border-gray-200 py-2">
                            <div class="px-4 py-3 border-b border-gray-100">
                                <p class="text-sm font-semibold text-gray-800"><?php echo $verify->nama; ?></p>
                                <p class="text-xs text-gray-500"><?php echo $verify->email; ?></p>
                            </div>
                            <div class="py-2">
                                <a href="/customer/home" class="flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                                    <i class="fas fa-user-circle mr-3 text-gray-400"></i>
                                    Dashboard
                                </a>
                                <a href="/src/fitur/pubs/user/profile/view" class="flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                                    <i class="fas fa-shopping-bag mr-3 text-gray-400"></i>
                                    Profile Saya
                                </a>
                                <hr class="my-2 border-gray-100">
                                <a href="#" id="logoutButton" class="flex items-center px-4 py-3 text-sm text-red-600 hover:bg-red-50 transition-colors">
                                    <i class="fas fa-sign-out-alt mr-3"></i>
                                    Keluar
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Mobile Search Bar -->
            <div id="mobileSearchBar" class="mobile-search md:hidden">
                <div class="p-4">
                    <div class="relative">
                        <input
                            type="text"
                            id="mobileSearchInput"
                            placeholder="Cari produk bayi & anak..."
                            class="w-full pl-12 pr-4 py-3 rounded-2xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-[var(--primary)]/30 focus:border-[var(--primary)] text-sm bg-gray-50">
                        <i class="fas fa-search absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <div class="max-w-7xl mx-auto px-4 py-8">
        <!-- Hero Banner -->
        <div class="bg-gradient-to-r from-[var(--primary-50)] via-white to-[var(--accent-50)] rounded-3xl p-8 mb-8 text-center shadow-lg border" style="border-color:rgba(20,184,166,0.12)">
            <h1 class="text-3xl md:text-4xl font-bold bg-gradient-to-r from-[var(--primary)] to-[var(--accent)] bg-clip-text text-transparent mb-4"> Koleksi Terbaru untuk Si Buah Hati </h1>
            <p class="text-gray-600 text-lg mb-4">
                Nikmati promo spesial & reward eksklusif hanya untuk kamu
            </p>
        </div>

        <!-- Categories -->
        <div class="flex flex-col gap-4 mb-6">
            <!-- filter harga -->
            <div class="w-full md:w-auto">
                <label for="priceFilter" class="text-sm">Urutkan Harga</label>
                <select id="priceFilter"
                    class="w-full md:w-auto rounded-xl border border-gray-200 px-3 py-2 text-sm focus:ring-2 focus:ring-pink-300">
                    <option value="">Semua Harga</option>
                    <option value="termurah">Harga: Termurah</option>
                    <option value="termahal">Harga: Termahal</option>
                </select>
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

    <script src="/src/js/products/products_pubs/main.js" type="module"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.getElementById('logoutButton').addEventListener('click', function(e) {
            e.preventDefault();
            Swal.fire({
                title: 'Yakin ingin keluar?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Ya, keluar',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Hapus cookie token dengan mengatur tanggal kedaluwarsa di masa lalu
                    document.cookie = "token=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
                    // Redirect ke halaman login
                    window.location.href = "/log_in";
                }
            });
        });
        
        document.getElementById("cartBtn").addEventListener("click", function(){
            Swal.fire({
                icon: 'info',
                title: 'Keranjang Belanja',
                text: 'Fitur ini akan segera hadir!',
            })
        })

        document.addEventListener('DOMContentLoaded', function() {
            // User Dropdown Toggle
            const userDropdownToggle = document.getElementById('userDropdownToggle');
            const userDropdownMenu = document.getElementById('userDropdownMenu');

            userDropdownToggle.addEventListener('click', function(e) {
                e.stopPropagation();
                userDropdownMenu.classList.toggle('show');
            });

            // Mobile Search Toggle
            const mobileSearchToggle = document.getElementById('mobileSearchToggle');
            const mobileSearchBar = document.getElementById('mobileSearchBar');

            mobileSearchToggle.addEventListener('click', function() {
                mobileSearchBar.classList.toggle('show');
            });

            // Close dropdowns when clicking outside
            document.addEventListener('click', function(e) {
                if (!userDropdownToggle.contains(e.target) && !userDropdownMenu.contains(e.target)) {
                    userDropdownMenu.classList.remove('show');
                }

                if (!mobileSearchToggle.contains(e.target) && !mobileSearchBar.contains(e.target)) {
                    mobileSearchBar.classList.remove('show');
                }
            });



            // Mobile menu toggle functionality (existing code)
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