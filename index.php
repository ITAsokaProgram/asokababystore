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
$pageName = "Asoka Page";


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
    <meta name="google-site-verification" content="7pjjd3ZqF-3OOn0FfQstCJMjnHCUriQoJER7EMLrIl4" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <title>Asoka Baby Store - Toko Perlengkapan Bayi & Anak</title>
    <meta name="description"
        content="Temukan perlengkapan bayi dan anak berkualitas di Asoka Baby Store. Aman, nyaman, dan terpercaya untuk si kecil usia 0-8 tahun." />
    <meta name="keywords"
        content="perlengkapan bayi, toko bayi, kebutuhan anak, stroller, pakaian bayi, mainan anak, Asoka Baby Store" />
    <meta name="author" content="Asoka Baby Store" />

    <!-- Open Graph / Facebook -->
    <meta property="og:title" content="Asoka Baby Store - Toko Perlengkapan Bayi & Anak" />
    <meta property="og:description"
        content="Toko perlengkapan bayi & anak usia 0-8 tahun. Kualitas terbaik, harga bersahabat." />
    <meta property="og:image" content="https://asokababystore.com/images/logo1.png" />
    <meta property="og:url" content="https://asokababystore.com/home" />
    <meta property="og:type" content="website" />

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

    <!-- Swiper CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
    <script src="https://unpkg.com/splitting/dist/splitting.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        * {
            font-family: 'Poppins', 'sans-serif';
            font-weight: 400;
        }

        html {
            scroll-behavior: smooth;
        }

        #map {
            height: 50vh;
            flex-grow: 1;
        }

        .leaflet-popup-content {
            width: 15rem;
        }

        /* Sembunyikan secara default (mobile-first) */
        .user-name {
            display: none;
        }

        /* Tampilkan di layar lebih besar dari 640px (misalnya tablet/desktop) */
        @media screen and (min-width: 640px) {
            .user-name {
                display: inline;
            }
        }

        /* Enhanced Animations */
        @keyframes float {

            0%,
            100% {
                transform: translateY(0px);
            }

            50% {
                transform: translateY(-10px);
            }
        }

        .float-animation {
            animation: float 3s ease-in-out infinite;
        }

        /* Gradient Text Animation */
        @keyframes gradient-shift {

            0%,
            100% {
                background-position: 0% 50%;
            }

            50% {
                background-position: 100% 50%;
            }
        }

        .gradient-text {
            background: linear-gradient(-45deg, #ec4899, #8b5cf6, #3b82f6, #ec4899);
            background-size: 400% 400%;
            animation: gradient-shift 3s ease infinite;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* Card Hover Effects */
        .card-hover-effect:hover {
            transform: translateY(-5px);
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.1);
        }

        /* Stats Counter Animation */
        @keyframes countUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .count-animation {
            animation: countUp 0.8s ease-out;
        }

        /* Glass Effect */
        .glass-effect {
            background: rgba(255, 255, 255, 0.25);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.18);
        }

        /* Custom Animation Delays */
        .animation-delay-2000 {
            animation-delay: 2s;
        }

        .animation-delay-4000 {
            animation-delay: 4s;
        }

        /* Custom Scrollbar */
        .scrollbar-thin::-webkit-scrollbar {
            width: 6px;
        }

        .scrollbar-thin::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 3px;
        }

        .scrollbar-thin::-webkit-scrollbar-thumb {
            background: #f472b6;
            border-radius: 3px;
        }

        .scrollbar-thin::-webkit-scrollbar-thumb:hover {
            background: #ec4899;
        }

        /* Firefox */
        .scrollbar-thin {
            scrollbar-width: thin;
            scrollbar-color: #f472b6 #f1f5f9;
        }
    </style>
</head>

<body class="bg-white text-gray-800 overflow-x-hidden w-full">

    <!-- Header -->
    <header class="bg-white/95 backdrop-blur-md shadow-lg sticky top-0 z-50 border-b border-pink-100">
        <div class="max-w-6xl mx-auto px-4 py-4 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <a href="/">
                    <img src="public/images/logo.png" alt="Logo Asoka Baby Store"
                        class="w-25 h-8 hover:scale-105 transition-transform duration-300" />
                </a>
            </div>

            <nav id="menu-mobile" class="grid grid-cols-1 text-center gap-2 p-8
                md:flex md:flex-row md:gap-6 hidden
                transition-all duration-300 ease-in-out transform origin-top
                scale-y-0 opacity-0
                md:scale-y-100 md:opacity-100 md:transform-none
                text-sm font-semibold absolute md:static top-full left-0 w-full md:w-auto
                bg-white/95 backdrop-blur-md md:bg-transparent px-4 md:px-0 py-4 md:py-0 z-50">
                <a href="#home-section" class="hover:text-pink-500 transition-colors duration-300 relative group">
                    Beranda
                    <span
                        class="absolute -bottom-1 left-0 w-0 h-0.5 bg-pink-500 transition-all duration-300 group-hover:w-full"></span>
                </a>
                <a href="#gallery-section" class="hover:text-pink-500 transition-colors duration-300 relative group">
                    Galeri
                    <span
                        class="absolute -bottom-1 left-0 w-0 h-0.5 bg-pink-500 transition-all duration-300 group-hover:w-full"></span>
                </a>
                <a href="/pesan_sekarang#kontak"
                    class="hover:text-pink-500 transition-colors duration-300 relative group">
                    Kontak
                    <span
                        class="absolute -bottom-1 left-0 w-0 h-0.5 bg-pink-500 transition-all duration-300 group-hover:w-full"></span>
                </a>
                <a href="#lokasi" class="hover:text-pink-500 transition-colors duration-300 relative group">
                    Lokasi
                    <span
                        class="absolute -bottom-1 left-0 w-0 h-0.5 bg-pink-500 transition-all duration-300 group-hover:w-full"></span>
                </a>
                <a href="#member-section" class="hover:text-pink-500 transition-colors duration-300 relative group"
                    id="openModal">
                    Member
                    <span
                        class="absolute -bottom-1 left-0 w-0 h-0.5 bg-pink-500 transition-all duration-300 group-hover:w-full"></span>
                </a>

                <a href="/kontak" class="hover:text-pink-500 transition-colors duration-300 relative group">
                    Lapor
                    <span
                        class="absolute -bottom-1 left-0 w-0 h-0.5 bg-pink-500 transition-all duration-300 group-hover:w-full"></span>
                </a>
            </nav>

            <div class="flex items-center gap-4">
                <div class="relative" id="userMenu">

                    <span id="userName"
                        class="ml-2 font-medium text-sm user-name cursor-pointer hover:text-pink-500 transition-colors duration-300"></span>

                    <div id="profileDropdown"
                        class="absolute right-0 mt-2 w-40 bg-white/95 backdrop-blur-md shadow-lg rounded-xl py-2 hidden z-50 border border-pink-100">
                        <a href="#"
                            class="block px-4 py-2 text-gray-700 hover:bg-pink-50 hover:text-pink-600 transition-colors duration-300"
                            id="profileBtn">Profile</a>
                        <a href="#" id="logoutBtn"
                            class="block px-4 py-2 text-gray-700 hover:bg-pink-50 hover:text-pink-600 transition-colors duration-300">Logout</a>
                    </div>
                </div>

                <a href="/log_in"
                    class=" md:inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-pink-500 to-purple-600 text-white rounded-full text-sm font-semibold hover:from-pink-600 hover:to-purple-700 transition-all duration-300 hover:scale-105 shadow-lg"
                    id="btn-klik-login">
                    <i class="fas fa-sign-in-alt"></i>
                    Login
                </a>
            </div>
        </div>
    </header>

    <main>

        <!-- Hero Section -->
        <section id="home-section" class="relative w-full text-center md:h-screen h-auto overflow-hidden"
            data-aos="fade-up" data-aos-delay="200" data-aos-duration="1000">
            <!-- Pengumuman Perbaikan -->
            <!-- <div id="announcement"
                class="fixed top-0 w-full bg-yellow-50 text-yellow-800 py-2 px-4 text-center z-50 overflow-hidden border-b border-yellow-200">
                <div class="inline-block animate-marquee whitespace-nowrap">
                    Kami mohon maaf kepada seluruh pelanggan yang mengalami kendala saat login setelah melakukan
                    pendaftaran. Saat ini masalah tersebut telah diperbaiki, dan Anda sudah dapat login serta melihat
                    riwayat transaksi Anda. Jika ada masukan dan saran untuk pengembangan website ini bisa masuk ke menu bantuan. Terima kasih atas pengertiannya.
                </div>
            </div> -->
            <div class="swiper mySwiper h-auto w-full ">
                <div class="swiper-wrapper" id="carousel-wrapper">
                    <!-- Slide konten akan diisi lewat JS -->
                </div>
            </div>
        </section>

        <!-- Stats Section -->
        <div class="relative z-10 mt-12 max-w-4xl mx-auto bg-white/90 backdrop-blur-lg shadow-2xl rounded-2xl py-8 px-8 grid grid-cols-1 sm:grid-cols-3 gap-8 text-center border border-pink-100"
            data-aos="fade-up" data-aos-delay="200" data-aos-duration="1000">
            <div class="group">
                <div class="text-4xl font-bold gradient-text mb-2 count-animation" id="total_pelanggan"></div>
                <p class="text-sm text-gray-600 font-medium">Total Pelanggan</p>
                <div
                    class="w-16 h-1 bg-gradient-to-r from-pink-500 to-purple-600 mx-auto mt-3 group-hover:w-20 transition-all duration-300">
                </div>
            </div>
            <div class="group">
                <div class="text-4xl font-bold gradient-text mb-2 count-animation" id="total_cabang"></div>
                <p class="text-sm text-gray-600 font-medium">Total Cabang</p>
                <div
                    class="w-16 h-1 bg-gradient-to-r from-pink-500 to-purple-600 mx-auto mt-3 group-hover:w-20 transition-all duration-300">
                </div>
            </div>
            <div class="group">
                <div class="text-4xl font-bold gradient-text mb-2 count-animation" id="total_product"></div>
                <p class="text-sm text-gray-600 font-medium">Total Produk</p>
                <div
                    class="w-16 h-1 bg-gradient-to-r from-pink-500 to-purple-600 mx-auto mt-3 group-hover:w-20 transition-all duration-300">
                </div>
            </div>
        </div>

        <!-- Member Section -->
        <section id="member-section" class="py-20 bg-gradient-to-br from-pink-50 via-white to-purple-50">
            <div class="max-w-6xl mx-auto py-10 px-5">
                <div class="text-center mb-12" data-aos="fade-down" data-aos-duration="800">
                    <h2 class="text-3xl md:text-4xl font-bold mb-4 gradient-text">Keuntungan Member</h2>
                    <p class="text-lg text-gray-600 max-w-2xl mx-auto">Bergabunglah dengan member kami dan nikmati
                        berbagai keuntungan eksklusif</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-12">
                    <div class="bg-white/80 backdrop-blur-lg rounded-2xl shadow-xl p-8 border border-pink-100 card-hover-effect transition-all duration-300"
                        data-aos="fade-up" data-aos-duration="600" data-aos-delay="100">
                        <div
                            class="w-16 h-16 bg-gradient-to-r from-pink-500 to-purple-600 rounded-full flex items-center justify-center mx-auto mb-6 float-animation">
                            <i class="fa-solid fa-tags text-white text-2xl"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-800 mb-4 text-center">Diskon Khusus Member</h3>
                        <p class="text-gray-600 text-center">Dapatkan diskon eksklusif untuk member setia kami</p>
                    </div>

                    <div class="bg-white/80 backdrop-blur-lg rounded-2xl shadow-xl p-8 border border-pink-100 card-hover-effect transition-all duration-300"
                        data-aos="fade-up" data-aos-duration="600" data-aos-delay="200">
                        <div class="w-16 h-16 bg-gradient-to-r from-pink-500 to-purple-600 rounded-full flex items-center justify-center mx-auto mb-6 float-animation"
                            style="animation-delay: 0.5s;">
                            <i class="fas fa-star text-white text-2xl"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-800 mb-4 text-center">Tukar Poin Member</h3>
                        <p class="text-gray-600 text-center">Kumpulkan poin dan tukar dengan hadiah menarik</p>
                    </div>

                    <div class="bg-white/80 backdrop-blur-lg rounded-2xl shadow-xl p-8 border border-pink-100 card-hover-effect transition-all duration-300"
                        data-aos="fade-up" data-aos-duration="600" data-aos-delay="300">
                        <div class="w-16 h-16 bg-gradient-to-r from-pink-500 to-purple-600 rounded-full flex items-center justify-center mx-auto mb-6 float-animation"
                            style="animation-delay: 1s;">
                            <i class="fas fa-gift text-white text-2xl"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-800 mb-4 text-center">Gratis Bungkus Kado</h3>
                        <p class="text-gray-600 text-center">Bungkus kado sepuasnya untuk member kami</p>
                    </div>
                </div>

                <div class="text-center" data-aos="fade-up" data-aos-delay="400" data-aos-duration="600">
                    <a href="#member-section" id="openModal1"
                        class="inline-flex items-center gap-2 bg-gradient-to-r from-pink-500 to-purple-600 hover:from-pink-600 hover:to-purple-700 text-white font-semibold py-3 px-8 rounded-full shadow-lg hover:shadow-xl transition-all duration-300 hover:scale-105">
                        <i class="fas fa-user-plus"></i>
                        Check Member
                    </a>
                </div>
            </div>
        </section>

        <!-- Gallery Section -->
        <section id="gallery-section" class="py-20 bg-gradient-to-br from-gray-50 to-white">
            <div class="max-w-6xl mx-auto px-4">
                <div class="text-center mb-12" data-aos="fade-down" data-aos-duration="800">
                    <h2 class="text-3xl md:text-4xl font-bold mb-4 gradient-text">Galeri Produk</h2>
                    <p class="text-lg text-gray-600 max-w-2xl mx-auto">Lihat koleksi produk terbaik kami untuk si kecil
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 sm:grid-cols-2 gap-8">
                    <?php
                    include "aa_kon_sett.php";
                    $sql = "SELECT * FROM products LIMIT 6";
                    $stmt = $conn->query($sql);
                    $delay = 100;
                    while ($row = $stmt->fetch_assoc()) {
                        $image = $row['image'];
                        $name = "Products";
                        echo '
                    <div class="group overflow-hidden rounded-2xl shadow-lg card-hover-effect transition-all duration-300" data-aos="fade-up" data-aos-duration="600" data-aos-delay="' . $delay . '">
                        <img class="w-full h-64 object-cover group-hover:scale-110 transition-transform duration-500" src="uploaded_img/' . $image . '" alt="' . htmlspecialchars($name) . '" />
                        <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                    </div>
                ';
                        $delay += 100;
                    }
                    $stmt->close();
                    $conn->close();
                    ?>
                </div>
            </div>
        </section>

        <section id="lokasi" class="py-20 bg-gradient-to-br from-blue-50 via-white to-pink-50">
            <div class="max-w-6xl mx-auto px-4">
                <div class="text-center mb-12" data-aos="fade-down" data-aos-duration="800">
                    <h2 class="text-3xl md:text-4xl font-bold mb-4 gradient-text">Lokasi Toko Kami</h2>
                    <p class="text-lg text-gray-600 max-w-2xl mx-auto">Temukan cabang terdekat dari lokasi Anda</p>
                </div>

                <!-- Location Stats -->
                <div class="max-w-4xl mx-auto mb-8" data-aos="fade-up" data-aos-duration="600">
                    <div
                        class="bg-gradient-to-r from-pink-50 to-purple-50 border border-pink-100 shadow-xl rounded-2xl p-6">
                        <div class="text-center">
                            <h3 class="text-2xl font-bold text-gray-800 mb-2">Temukan Cabang Terdekat</h3>
                            <p class="text-gray-600">Gunakan peta di bawah untuk menemukan cabang ASOKA Baby Store
                                terdekat dari lokasi Anda</p>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 h-auto lg:h-[70vh]">
                    <!-- Sidebar scrollable -->
                    <div id="sidebar"
                        class="bg-white/90 backdrop-blur-lg shadow-xl rounded-2xl p-6 overflow-hidden lg:col-span-1 h-full min-h-[50vh] border border-blue-100"
                        data-aos="fade-right" data-aos-duration="600">
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-xl font-bold text-gray-800 flex items-center gap-3">
                                <div
                                    class="w-10 h-10 bg-gradient-to-r from-pink-500 to-purple-600 rounded-full flex items-center justify-center">
                                    <i class="fas fa-store text-white text-lg"></i>
                                </div>
                                Daftar Cabang
                            </h3>
                        </div>

                        <!-- Search and Filter Controls -->
                        <div class="mb-4 space-y-3">
                            <div class="relative">
                                <i
                                    class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                                <input type="text" id="search-toko" placeholder="Cari nama toko..."
                                    class="w-full border-2 border-gray-200 rounded-xl pl-10 pr-4 py-3 focus:outline-none focus:ring-2 focus:ring-pink-500 focus:border-transparent transition-all duration-300 text-sm">
                            </div>

                            <div class="relative">
                                <i
                                    class="fas fa-map-marker-alt absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                                <select name="city" id="search-city"
                                    class="w-full border-2 border-gray-200 rounded-xl pl-10 pr-4 py-3 focus:outline-none focus:ring-2 focus:ring-pink-500 focus:border-transparent transition-all duration-300 text-sm appearance-none bg-white">
                                    <option value="all">Semua Kota</option>
                                    <option value="TANGERANG">TANGERANG</option>
                                    <option value="JAKARTA TIMUR">JAKARTA TIMUR</option>
                                    <option value="TANGERANG SELATAN">TANGERANG SELATAN</option>
                                    <option value="DEPOK">DEPOK</option>
                                    <option value="BOGOR">BOGOR</option>
                                    <option value="JAKARTA BARAT">JAKARTA BARAT</option>
                                    <option value="BEKASI">BEKASI</option>
                                    <option value="BELITUNG">BELITUNG</option>
                                    <option value="BANGKA">BANGKA</option>
                                    <option value="JAKARTA UTARA">JAKARTA UTARA</option>
                                </select>
                                <i
                                    class="fas fa-chevron-down absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 pointer-events-none"></i>
                            </div>
                        </div>

                        <!-- Location List Container -->
                        <div id="location-list"
                            class="space-y-3 overflow-y-auto max-h-[calc(70vh-200px)] pr-2 scrollbar-thin scrollbar-thumb-pink-300 scrollbar-track-gray-100">
                            <!-- Location items will be populated here -->
                        </div>

                        <!-- No Results Message -->
                        <div id="no-results" class="hidden text-center py-8">
                            <div
                                class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                <i class="fas fa-search text-gray-400 text-xl"></i>
                            </div>
                            <p class="text-gray-500 text-sm">Tidak ada cabang yang ditemukan</p>
                        </div>
                    </div>

                    <!-- Map Container -->
                    <div class="lg:col-span-2 h-full relative" data-aos="fade-left" data-aos-duration="600">
                        <div id="map-tip"
                            class="absolute top-4 left-1/2 -translate-x-1/2 bg-white/95 backdrop-blur-md text-gray-800 text-sm px-4 py-2 rounded-xl shadow-lg z-[1000] hidden lg:hidden border border-pink-100">
                            <i class="fas fa-info-circle text-pink-500 mr-2"></i>
                            Gunakan dua jari untuk menggeser peta
                        </div>
                        <div id="map" class="w-full h-full rounded-2xl shadow-xl min-h-[50vh] border border-blue-100">
                        </div>

                        <!-- Map Controls Info -->
                        <div
                            class="absolute bottom-4 right-4 bg-white/95 backdrop-blur-md rounded-xl p-3 shadow-lg border border-gray-200 hidden lg:block">
                            <div class="text-xs text-gray-600 space-y-1">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-crosshairs text-pink-500"></i>
                                    <span>Lokasi Saya</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-list text-blue-500"></i>
                                    <span>Toko Terdekat</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="faq-section" class="py-20 bg-gradient-to-br from-purple-50 via-white to-pink-50">
            <div class="max-w-6xl mx-auto px-4">
                <div class="text-center mb-12" data-aos="fade-down" data-aos-duration="800">
                    <h2 class="text-3xl md:text-4xl font-bold mb-4 gradient-text">Pertanyaan yang Sering Diajukan</h2>
                    <p class="text-lg text-gray-600 max-w-2xl mx-auto">Temukan jawaban untuk pertanyaan Anda seputar
                        Asoka Baby Store</p>
                </div>

                <div class="space-y-4 max-w-4xl mx-auto">
                    <!-- FAQ 1 -->
                    <div class="bg-white/80 backdrop-blur-lg rounded-2xl shadow-lg border border-pink-100 overflow-hidden card-hover-effect transition-all duration-300"
                        data-aos="fade-up" data-aos-duration="600" data-aos-delay="50">
                        <button
                            class="faq-button w-full text-left px-6 py-5 flex items-center justify-between hover:bg-pink-50 transition-colors duration-300">
                            <span class="font-semibold text-gray-800 pr-4">Apa saja produk yang tersedia di Asoka Baby
                                Store?</span>
                            <i class="fas fa-chevron-down text-pink-500 transition-transform duration-300 faq-icon"></i>
                        </button>
                        <div class="faq-content max-h-0 overflow-hidden transition-all duration-300 ease-in-out">
                            <div class="px-6 pb-6 pt-2 text-gray-600">
                                Asoka Baby Store menyediakan berbagai kebutuhan bayi dan anak, mulai dari pakaian,
                                popok, perlengkapan mandi, botol susu, perlengkapan makan, mainan edukatif, stroller,
                                hingga perlengkapan ibu menyusui. Kami berkomitmen untuk menyediakan produk berkualitas
                                dengan harga terbaik.
                            </div>
                        </div>
                    </div>

                    <!-- FAQ 2 -->
                    <div class="bg-white/80 backdrop-blur-lg rounded-2xl shadow-lg border border-pink-100 overflow-hidden card-hover-effect transition-all duration-300"
                        data-aos="fade-up" data-aos-duration="600" data-aos-delay="100">
                        <button
                            class="faq-button w-full text-left px-6 py-5 flex items-center justify-between hover:bg-pink-50 transition-colors duration-300">
                            <span class="font-semibold text-gray-800 pr-4">Apakah produk yang dijual di Asoka Baby Store
                                original?</span>
                            <i class="fas fa-chevron-down text-pink-500 transition-transform duration-300 faq-icon"></i>
                        </button>
                        <div class="faq-content max-h-0 overflow-hidden transition-all duration-300 ease-in-out">
                            <div class="px-6 pb-6 pt-2 text-gray-600">
                                Ya, seluruh produk yang kami jual adalah 100% original dan bergaransi resmi dari brand
                                terpercaya seperti Cussons, Sweety, MamyPoko, Pigeon, Dodo, BabyHappy, dan merek ternama
                                lainnya.
                            </div>
                        </div>
                    </div>

                    <!-- FAQ 3 -->
                    <div class="bg-white/80 backdrop-blur-lg rounded-2xl shadow-lg border border-pink-100 overflow-hidden card-hover-effect transition-all duration-300"
                        data-aos="fade-up" data-aos-duration="600" data-aos-delay="150">
                        <button
                            class="faq-button w-full text-left px-6 py-5 flex items-center justify-between hover:bg-pink-50 transition-colors duration-300">
                            <span class="font-semibold text-gray-800 pr-4">Apakah Asoka Baby Store melayani pembelian
                                online?</span>
                            <i class="fas fa-chevron-down text-pink-500 transition-transform duration-300 faq-icon"></i>
                        </button>
                        <div class="faq-content max-h-0 overflow-hidden transition-all duration-300 ease-in-out">
                            <div class="px-6 pb-6 pt-2 text-gray-600">
                                Ya. Ayah dan Bunda bisa berbelanja secara online melalui:
                                <ul class="ml-6 mt-2 space-y-1 faq-list">
                                    <li>WhatsApp Official Store</li>
                                    <li>Shopee & Tiktok</li>
                                    <li>Instagram @asokababyofficial</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- FAQ 4 -->
                    <div class="bg-white/80 backdrop-blur-lg rounded-2xl shadow-lg border border-pink-100 overflow-hidden card-hover-effect transition-all duration-300"
                        data-aos="fade-up" data-aos-duration="600" data-aos-delay="200">
                        <button
                            class="faq-button w-full text-left px-6 py-5 flex items-center justify-between hover:bg-pink-50 transition-colors duration-300">
                            <span class="font-semibold text-gray-800 pr-4">Apakah Asoka Baby Store memiliki promo atau
                                diskon?</span>
                            <i class="fas fa-chevron-down text-pink-500 transition-transform duration-300 faq-icon"></i>
                        </button>
                        <div class="faq-content max-h-0 overflow-hidden transition-all duration-300 ease-in-out">
                            <div class="px-6 pb-6 pt-2 text-gray-600">
                                Kami rutin menghadirkan promo bulanan, promo mingguan dan penawaran eksklusif bagi
                                member setia ASOKA. Informasi promo terbaru dapat dilihat di Media Sosial, Website atau
                                bisa langsung di toko langsung.
                            </div>
                        </div>
                    </div>

                    <!-- FAQ 5 -->
                    <div class="bg-white/80 backdrop-blur-lg rounded-2xl shadow-lg border border-pink-100 overflow-hidden card-hover-effect transition-all duration-300"
                        data-aos="fade-up" data-aos-duration="600" data-aos-delay="250">
                        <button
                            class="faq-button w-full text-left px-6 py-5 flex items-center justify-between hover:bg-pink-50 transition-colors duration-300">
                            <span class="font-semibold text-gray-800 pr-4">Bagaimana kebijakan penukaran barang di Asoka
                                Baby Store?</span>
                            <i class="fas fa-chevron-down text-pink-500 transition-transform duration-300 faq-icon"></i>
                        </button>
                        <div class="faq-content max-h-0 overflow-hidden transition-all duration-300 ease-in-out">
                            <div class="px-6 pb-6 pt-2 text-gray-600">
                                Penukaran dapat dilakukan dengan ketentuan berikut:
                                <ul class="ml-6 mt-2 space-y-1 faq-list">
                                    <li>Barang belum digunakan dan dalam kondisi baik</li>
                                    <li>Label Barcode dan kemasan masih lengkap</li>
                                    <li>Penukaran dilakukan maksimal 1x24 jam setelah pembelian</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- FAQ 6 -->
                    <div class="bg-white/80 backdrop-blur-lg rounded-2xl shadow-lg border border-pink-100 overflow-hidden card-hover-effect transition-all duration-300"
                        data-aos="fade-up" data-aos-duration="600" data-aos-delay="300">
                        <button
                            class="faq-button w-full text-left px-6 py-5 flex items-center justify-between hover:bg-pink-50 transition-colors duration-300">
                            <span class="font-semibold text-gray-800 pr-4">Apakah Asoka Baby Store menerima pembelian
                                grosir atau reseller?</span>
                            <i class="fas fa-chevron-down text-pink-500 transition-transform duration-300 faq-icon"></i>
                        </button>
                        <div class="faq-content max-h-0 overflow-hidden transition-all duration-300 ease-in-out">
                            <div class="px-6 pb-6 pt-2 text-gray-600">
                                Tidak, kami tidak membuka kerja sama untuk reseller dan pembelian secara grosir.
                            </div>
                        </div>
                    </div>

                    <!-- FAQ 7 -->
                    <div class="bg-white/80 backdrop-blur-lg rounded-2xl shadow-lg border border-pink-100 overflow-hidden card-hover-effect transition-all duration-300"
                        data-aos="fade-up" data-aos-duration="600" data-aos-delay="350">
                        <button
                            class="faq-button w-full text-left px-6 py-5 flex items-center justify-between hover:bg-pink-50 transition-colors duration-300">
                            <span class="font-semibold text-gray-800 pr-4">Bagaimana cara memastikan ketersediaan stok
                                produk?</span>
                            <i class="fas fa-chevron-down text-pink-500 transition-transform duration-300 faq-icon"></i>
                        </button>
                        <div class="faq-content max-h-0 overflow-hidden transition-all duration-300 ease-in-out">
                            <div class="px-6 pb-6 pt-2 text-gray-600">
                                Ayah/Bunda bisa menghubungi admin kami melalui WhatsApp atau Website untuk mengecek
                                ketersediaan stok sebelum melakukan pembelian.
                            </div>
                        </div>
                    </div>

                    <!-- FAQ 8 -->
                    <div class="bg-white/80 backdrop-blur-lg rounded-2xl shadow-lg border border-pink-100 overflow-hidden card-hover-effect transition-all duration-300"
                        data-aos="fade-up" data-aos-duration="600" data-aos-delay="400">
                        <button
                            class="faq-button w-full text-left px-6 py-5 flex items-center justify-between hover:bg-pink-50 transition-colors duration-300">
                            <span class="font-semibold text-gray-800 pr-4">Bagaimana sistem pembayaran di Asoka Baby
                                Store?</span>
                            <i class="fas fa-chevron-down text-pink-500 transition-transform duration-300 faq-icon"></i>
                        </button>
                        <div class="faq-content max-h-0 overflow-hidden transition-all duration-300 ease-in-out">
                            <div class="px-6 pb-6 pt-2 text-gray-600">
                                Kami menerima berbagai metode pembayaran di toko:
                                <ul class="ml-6 mt-2 space-y-1 faq-list">
                                    <li>Tunai (Cash)</li>
                                    <li>Debit (Minimal Pembelian Rp50.000)</li>
                                    <li>QRIS (Minimal Pembelian Rp50.000)</li>
                                    <li>Kartu Kredit (Minimal Pembelian Rp100.000)</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- FAQ 9 -->
                    <div class="bg-white/80 backdrop-blur-lg rounded-2xl shadow-lg border border-pink-100 overflow-hidden card-hover-effect transition-all duration-300"
                        data-aos="fade-up" data-aos-duration="600" data-aos-delay="450">
                        <button
                            class="faq-button w-full text-left px-6 py-5 flex items-center justify-between hover:bg-pink-50 transition-colors duration-300">
                            <span class="font-semibold text-gray-800 pr-4">Apakah Asoka Baby Store memiliki program
                                member atau poin loyalitas?</span>
                            <i class="fas fa-chevron-down text-pink-500 transition-transform duration-300 faq-icon"></i>
                        </button>
                        <div class="faq-content max-h-0 overflow-hidden transition-all duration-300 ease-in-out">
                            <div class="px-6 pb-6 pt-2 text-gray-600">
                                Ya, pelanggan dapat mendaftar menjadi member Asoka Baby Store untuk mendapatkan poin
                                dari setiap transaksi yang berbelanja minimal Rp100.000 dihitung 1 Poin, Potongan Harga
                                Khusus member potongan Rp500 s/d Rp1.000 dan Gratis kertas kado Minimal pembelanjaan
                                Rp50.000.
                            </div>
                        </div>
                    </div>

                    <!-- FAQ 10 -->
                    <div class="bg-white/80 backdrop-blur-lg rounded-2xl shadow-lg border border-pink-100 overflow-hidden card-hover-effect transition-all duration-300"
                        data-aos="fade-up" data-aos-duration="600" data-aos-delay="500">
                        <button
                            class="faq-button w-full text-left px-6 py-5 flex items-center justify-between hover:bg-pink-50 transition-colors duration-300">
                            <span class="font-semibold text-gray-800 pr-4">Apakah Asoka Baby Store Memiliki Cabang
                                lain?</span>
                            <i class="fas fa-chevron-down text-pink-500 transition-transform duration-300 faq-icon"></i>
                        </button>
                        <div class="faq-content max-h-0 overflow-hidden transition-all duration-300 ease-in-out">
                            <div class="px-6 pb-6 pt-2 text-gray-600">
                                Ya, kami mempunyai cabang yang berdomisili di daerah Jabodetabek, Bangka & Belitung.
                            </div>
                        </div>
                    </div>

                    <!-- FAQ 11 -->
                    <div class="bg-white/80 backdrop-blur-lg rounded-2xl shadow-lg border border-pink-100 overflow-hidden card-hover-effect transition-all duration-300"
                        data-aos="fade-up" data-aos-duration="600" data-aos-delay="550">
                        <button
                            class="faq-button w-full text-left px-6 py-5 flex items-center justify-between hover:bg-pink-50 transition-colors duration-300">
                            <span class="font-semibold text-gray-800 pr-4">Bagaimana cara menghubungi layanan pelanggan
                                Asoka Baby Store?</span>
                            <i class="fas fa-chevron-down text-pink-500 transition-transform duration-300 faq-icon"></i>
                        </button>
                        <div class="faq-content max-h-0 overflow-hidden transition-all duration-300 ease-in-out">
                            <div class="px-6 pb-6 pt-2 text-gray-600">
                                Untuk pertanyaan, bantuan, atau kerja sama, silakan hubungi:
                                <ul class="ml-6 mt-2 space-y-1 faq-list">
                                    <li>WhatsApp: 0817-399-588</li>
                                    <li>Instagram: @asokababyofficial</li>
                                    <li>Website: www.asokababystore.com</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <script>
            // FAQ Accordion Functionality
            document.addEventListener('DOMContentLoaded', function () {
                const faqButtons = document.querySelectorAll('.faq-button');

                faqButtons.forEach(button => {
                    button.addEventListener('click', function () {
                        const content = this.nextElementSibling;
                        const icon = this.querySelector('.faq-icon');
                        const isOpen = content.style.maxHeight && content.style.maxHeight !== '0px';

                        // Close all other FAQs
                        document.querySelectorAll('.faq-content').forEach(item => {
                            if (item !== content) {
                                item.style.maxHeight = '0';
                                item.previousElementSibling.querySelector('.faq-icon').style.transform = 'rotate(0deg)';
                            }
                        });

                        // Toggle current FAQ
                        if (isOpen) {
                            content.style.maxHeight = '0';
                            icon.style.transform = 'rotate(0deg)';
                        } else {
                            content.style.maxHeight = content.scrollHeight + 'px';
                            icon.style.transform = 'rotate(180deg)';
                        }
                    });
                });
            });
        </script>

        <style>
            /* Custom Gradient Bullet Points untuk FAQ List */
            .faq-list {
                list-style: none;
                padding-left: 0;
            }

            .faq-list li {
                position: relative;
                padding-left: 1.5rem;
            }

            .faq-list li::before {
                content: '';
                position: absolute;
                left: 0;
                top: 0.6rem;
                width: 8px;
                height: 8px;
                border-radius: 50%;
                background: linear-gradient(135deg, #ec4899, #8b5cf6);
                animation: pulse-dot 2s infinite;
            }

            /* Variasi warna untuk setiap bullet */
            .faq-list li:nth-child(1)::before {
                background: linear-gradient(135deg, #ec4899, #f472b6);
            }

            .faq-list li:nth-child(2)::before {
                background: linear-gradient(135deg, #8b5cf6, #a78bfa);
            }

            .faq-list li:nth-child(3)::before {
                background: linear-gradient(135deg, #3b82f6, #60a5fa);
            }

            .faq-list li:nth-child(4)::before {
                background: linear-gradient(135deg, #ec4899, #8b5cf6);
            }

            @keyframes pulse-dot {

                0%,
                100% {
                    opacity: 1;
                    transform: scale(1);
                }

                50% {
                    opacity: 0.8;
                    transform: scale(1.1);
                }
            }
        </style>

        <!-- Overlay + Modal -->
        <div id="modalMember"
            class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 hidden">
            <div id="modalContent"
                class="bg-white/95 backdrop-blur-lg w-11/12 max-w-md p-8 rounded-2xl shadow-2xl opacity-0 scale-90 border border-pink-100">
                <div class="text-center mb-6">
                    <div
                        class="w-16 h-16 bg-gradient-to-r from-pink-500 to-purple-600 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-user-circle text-white text-2xl"></i>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-800 mb-2">Member</h2>
                    <p id="petunjuk-member" class="text-gray-600">Silahkan masukan kode atau no hp yang didaftarkan</p>
                </div>

                <div class="space-y-4">
                    <div class="relative">
                        <i class="fas fa-user absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        <input type="text" name="kode-member" id="member" inputmode="numeric" pattern="[0-9]"
                            maxlength="13" placeholder="Kode atau no hp"
                            class="w-full border-2 border-gray-200 rounded-xl pl-10 pr-4 py-3 focus:ring-2 focus:ring-pink-500 focus:border-transparent transition-all duration-300" />
                    </div>

                    <div class="flex gap-3 pt-4">
                        <button id="closeModal"
                            class="flex-1 px-4 py-3 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold transition-all duration-300 hover:scale-105">Tutup</button>
                        <button id="cek-member"
                            class="flex-1 px-4 py-3 rounded-xl bg-gradient-to-r from-pink-500 to-purple-600 hover:from-pink-600 hover:to-purple-700 text-white font-semibold transition-all duration-300 hover:scale-105 shadow-lg">
                            <i class="fas fa-search mr-2"></i>Cek Member
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Overlay + Modal -->
        <div id="modalMember1"
            class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 hidden">
            <div id="modalContent1"
                class="bg-white/95 backdrop-blur-lg w-11/12 max-w-md p-8 rounded-2xl shadow-2xl opacity-0 scale-90 border border-pink-100">
                <div class="text-center mb-6">
                    <div
                        class="w-16 h-16 bg-gradient-to-r from-pink-500 to-purple-600 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-user-circle text-white text-2xl"></i>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-800 mb-2">Member</h2>
                    <p id="petunjuk-member1" class="text-gray-600">Silahkan masukan kode atau no hp yang didaftarkan</p>
                </div>

                <div class="space-y-4">
                    <div class="relative">
                        <i class="fas fa-user absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        <input id="member1" type="text" inputmode="numeric" pattern="[0-9]" maxlength="13"
                            placeholder="Kode atau no hp"
                            class="w-full border-2 border-gray-200 rounded-xl pl-10 pr-4 py-3 focus:ring-2 focus:ring-pink-500 focus:border-transparent transition-all duration-300" />
                    </div>

                    <div class="flex gap-3 pt-4">
                        <button id="closeModal1"
                            class="flex-1 px-4 py-3 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold transition-all duration-300 hover:scale-105">Tutup</button>
                        <button id="cek-member1"
                            class="flex-1 px-4 py-3 rounded-xl bg-gradient-to-r from-pink-500 to-purple-600 hover:from-pink-600 hover:to-purple-700 text-white font-semibold transition-all duration-300 hover:scale-105 shadow-lg">
                            <i class="fas fa-search mr-2"></i>Cek Member
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Data Customer -->
        <?php include 'src/fitur/personal/data_cust.php'; ?>

        <!-- Modal Terms And Condition -->
        <div id="modalTerms"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden px-2">
            <div id="modalContentTerms"
                class="bg-white w-full max-w-md max-h-[90vh] p-4 md:p-6 rounded-xl shadow-xl overflow-y-auto transition duration-300 ease-out opacity-0 scale-90">
                <h2 class="text-xl font-bold mb-4 text-pink-600 text-center">Syarat Dan Ketentuan Member Asoka</h2>
                <p id="petunjuk-syarat" class="text-gray-600 mb-4">
                    Dengan mendaftar sebagai member ASOKA, Anda dianggap telah membaca, memahami, dan menyetujui
                    syarat dan ketentuan berikut:
                </p>

                <ol class="list-decimal text-sm text-gray-700 space-y-2 ml-5 mb-4">
                    <li>
                        <strong>Biaya Pendaftaran</strong>
                        <ul class="list-disc ml-5">
                            <li>Biaya pendaftaran sebesar Rp 10.000,-.</li>
                            <li>Gratis jika belanja minimum Rp 300.000,-.</li>
                        </ul>
                    </li>
                    <li>
                        <strong>Hak dan Keuntungan Member</strong>
                        <ul class="list-disc ml-5">
                            <li>Diskon produk tertentu.</li>
                            <li>1 poin untuk setiap Rp 100.000,- (produk tertentu).</li>
                            <li>Poin bisa ditukar hadiah menarik.</li>
                            <li>Dapat ikut program eksklusif.</li>
                        </ul>
                    </li>
                    <li>
                        <strong>Nomor Telepon Terdaftar</strong>
                        <ul class="list-disc ml-5">
                            <li>Menjadi Nomor Member dan tidak bisa diubah.</li>
                            <li>Berlaku hanya untuk transaksi di toko ASOKA.</li>
                            <li>Nomor harus aktif untuk menjaga status member.</li>
                        </ul>
                    </li>
                    <li>
                        <strong>Kebijakan Poin Member</strong>
                        <ul class="list-disc ml-5">
                            <li>Poin harus ditukar sebelum akhir Juni (Periode 1) atau akhir Desember (Periode 2).</li>
                            <li>Poin yang lewat batas hangus.</li>
                        </ul>
                    </li>
                    <li>
                        <strong>Tanggung Jawab Member</strong>
                        <ul class="list-disc ml-5">
                            <li>Menjaga kerahasiaan nomor dan data keanggotaan.</li>
                            <li>ASOKA tidak bertanggung jawab atas kelalaian member.</li>
                        </ul>
                    </li>
                    <li>
                        <strong>Privasi dan Data</strong>
                        <ul class="list-disc ml-5">
                            <li>ASOKA menjaga data member.</li>
                            <li>Data tidak dibagikan tanpa izin, kecuali oleh hukum.</li>
                        </ul>
                    </li>
                    <li>
                        <strong>Perubahan Syarat dan Ketentuan</strong>
                        <ul class="list-disc ml-5">
                            <li>ASOKA berhak mengubah ketentuan tanpa pemberitahuan.</li>
                        </ul>
                    </li>
                </ol>

                <p class="text-sm font-medium text-gray-700 mb-4">
                    Dengan ini, saya bermaksud untuk mendaftarkan diri sebagai member ASOKA dan menyatakan telah
                    membaca serta memahami syarat dan ketentuan yang berlaku.
                </p>

                <div class="flex justify-end space-x-2 sticky bg-white pt-3">
                    <button id="setuju"
                        class="px-4 py-2 rounded text-white bg-green-500 hover:bg-green-600 text-sm">Setuju</button>
                    <button id="tidak-setuju"
                        class="px-4 py-2 rounded bg-red-500 text-white hover:bg-red-600 text-sm">Tidak Setuju</button>
                </div>
            </div>
        </div>
        <div id="progressOverlay"
            class="fixed inset-0 bg-white/90 backdrop-blur-md z-50 hidden flex flex-col items-center justify-center space-y-6">
            <!-- Loading Icon -->
            <div class="w-20 h-20 border-4 border-pink-200 border-t-pink-500 rounded-full animate-spin"></div>

            <!-- Teks Loading -->
            <div class="text-xl font-semibold text-gray-700 animate-pulse">
                Loading data...
            </div>

            <!-- Progress Bar -->
            <div class="w-80 h-3 bg-gray-200 rounded-full overflow-hidden shadow-inner">
                <div id="progressBar"
                    class="h-full bg-gradient-to-r from-pink-500 to-purple-600 w-0 rounded-full transition-all duration-300 shadow-lg">
                </div>
            </div>

            <!-- Loading Text -->
            <p class="text-sm text-gray-500 text-center max-w-md">
                Mohon tunggu sebentar, kami sedang memuat data untuk Anda
            </p>
        </div>
    </main>

    <!-- Bottom Navigation Component -->
    <?php include 'src/component/bottom_navigation.php'; ?>

    <!-- Floating Message Button Component -->
    <?php include 'src/component/floating_message.php'; ?>

    <!-- Footer -->
    <?php include 'src/component/footer.php'; ?>

    <script src="src/js/location/data_location.js" type="module"></script>
    <script src="src/js/cek_member.js" type="module"></script>
    <script src="src/js/slider_hero.js"></script>
    <script src="src/js/loadingbar.js"></script>
    <script src="src/js/index/main.js" type="module"></script>
    <!-- <script src="src/js/index/mobile_ui/notif_maintance.js" type="module"></script> -->

    <script>
        // Initialize AOS and Splitting
        AOS.init();
        Splitting();
        const observer = new IntersectionObserver(entries => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('show');
                }
            });
        }, {
            threshold: 0.1
        });

        document.querySelectorAll('.scroll-reveal').forEach(img => {
            observer.observe(img);
        });

        // Statis counter
        const counts = {
            total_pelanggan: 15,
            total_cabang: 40,
            total_product: 10000
        };

        function spinMachine(element, target) {
            let current = 0;
            let speed = 30; // Kecepatan putaran
            let interval;

            // Fungsi untuk memutarkan angka
            function spin() {
                current = Math.floor(Math.random() * 10000); // Angka acak untuk spin
                element.innerText = current;
            }

            // Mulai spin
            interval = setInterval(spin, speed);

            // Berhenti setelah beberapa detik dan tampilkan angka yang sebenarnya
            setTimeout(() => {
                clearInterval(interval);
                element.innerText = target + (target === counts.total_pelanggan ? " M" : "+");
            }, 2000); // Spin selama 3 detik sebelum berhenti
        }
        Object.entries(counts).forEach(([id, target]) => {
            const el = document.querySelector(`#${id}`);
            spinMachine(el, target);
        });


        const mapContainer = document.getElementById('map');
        const mapTip = document.getElementById('map-tip');

        mapContainer.addEventListener('touchstart', (e) => {
            if (e.touches.length === 1) {
                mapTip.classList.remove('hidden');
            } else if (e.touches.length >= 2) {
                mapTip.classList.add('hidden');
            }
        });

        mapContainer.addEventListener('touchend', () => {
            mapTip.classList.add('hidden');
        });


        // Add active state to bottom navigation
        const bottomNavLinks = document.querySelectorAll('.fixed.bottom-0 a');
        const sections = document.querySelectorAll('section[id]');

        function setActiveBottomNav() {
            const scrollPosition = window.scrollY;

            sections.forEach(section => {
                const sectionTop = section.offsetTop - 100;
                const sectionBottom = sectionTop + section.offsetHeight;
                const sectionId = section.getAttribute('id');

                if (scrollPosition >= sectionTop && scrollPosition < sectionBottom) {
                    bottomNavLinks.forEach(link => {
                        link.classList.remove('text-pink-500');
                        link.classList.add('text-gray-600');
                        if (link.getAttribute('href') === `#${sectionId}`) {
                            link.classList.remove('text-gray-600');
                            link.classList.add('text-pink-500');
                        }
                    });
                }
            });
        }

        window.addEventListener('scroll', setActiveBottomNav);
        window.addEventListener('load', setActiveBottomNav);

        // Handle member modal from bottom nav
        document.getElementById('openModalBottom').addEventListener('click', function (e) {
            e.preventDefault();
            document.getElementById('openModal').click();
        });
    </script>

    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-6R7F1JPJE7"></script>
    <script>
        window.dataLayer = window.dataLayer || [];

        function gtag() {
            dataLayer.push(arguments);
        }
        gtag('js', new Date());

        gtag('config', 'G-6R7F1JPJE7');
    </script>
</body>

</html>