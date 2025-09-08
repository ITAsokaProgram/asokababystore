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
$pageName = "Lapor Keluhan";

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
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <title>Hubungi Kami - Asoka Baby Store</title>
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
    <!-- Swiper CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <!-- <script src="https://cdn.tailwindcss.com"></script> -->
    <link rel="stylesheet" href="/src/output2.css">

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

        /* Custom Animation Delays */
        .animation-delay-2000 {
            animation-delay: 2s;
        }

        .animation-delay-4000 {
            animation-delay: 4s;
        }

        /* Glass Effect */
        .glass-effect {
            background: rgba(255, 255, 255, 0.25);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.18);
        }

        /* Floating Animation */
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

        /* Input Focus Effects */
        .input-focus-effect:focus {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(236, 72, 153, 0.2);
        }

        /* Button Hover Effects */
        .btn-hover-effect:hover {
            transform: translateY(-3px);
            box-shadow: 0 20px 40px rgba(236, 72, 153, 0.3);
        }

        /* Card Hover Effects */
        .card-hover-effect:hover {
            transform: translateY(-5px);
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.1);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .mobile-padding {
                padding: 1rem;
            }
        }
    </style>
</head>

<body class="bg-white text-gray-800 overflow-x-hidden w-full">
    <!-- Header -->
    <header class="bg-white shadow-md sticky top-0 z-50">
        <div class="max-w-6xl mx-auto px-4 py-4 flex items-center justify-between">
            <img src="public/images/logo.png" alt="Logo Asoka Baby Store" class="w-25 h-8" />

            <button id="menu-toogle" class="md:hidden text-2xl text-pink-600 focus-outline-none">
                <i class="fas fa-bars"></i>
            </button>

            <nav id="menu-mobile" class="grid grid-cols-1 text-center gap-2 p-8
                md:flex md:flex-row md:gap-6 hidden
                transition-all duration-300 ease-in-out transform origin-top
                scale-y-0 opacity-0
                md:scale-y-100 md:opacity-100 md:transform-none
                text-sm font-semibold absolute md:static top-full left-0 w-full md:w-auto
                bg-white md:bg-transparent px-4 md:px-0 py-4 md:py-0 z-50">
                <a href="/index#home-section" class="hover:text-pink-500">Beranda</a>
                <a href="/index#gallery-section" class="hover:text-pink-500">Galeri</a>
                <a href="/pesan_sekarang" class="hover:text-pink-500">Kontak</a>
                <a href="/index#lokasi" class="hover:text-pink-500">Lokasi</a>
                <a href="/index#member-section" class="hover:text-pink-500" id="openModal">Member</a>
            </nav>
        </div>
    </header>

    <main>
        <!-- Hero Section -->
        <section class="min-h-screen bg-gradient-to-br from-pink-50 via-white to-blue-50 flex items-center justify-center p-6 relative overflow-hidden">
            <!-- Background Pattern -->
            <div class="absolute inset-0 opacity-5">
                <div class="absolute top-20 left-20 w-72 h-72 bg-pink-400 rounded-full mix-blend-multiply filter blur-xl animate-pulse"></div>
                <div class="absolute top-40 right-20 w-72 h-72 bg-purple-400 rounded-full mix-blend-multiply filter blur-xl animate-pulse animation-delay-2000"></div>
                <div class="absolute -bottom-8 left-40 w-72 h-72 bg-blue-400 rounded-full mix-blend-multiply filter blur-xl animate-pulse animation-delay-4000"></div>
            </div>

            <div class="relative z-10 max-w-6xl w-full">
                <!-- Header Section -->
                <div class="text-center mb-12" data-aos="fade-down" data-aos-duration="800">
                    <h1 class="text-4xl md:text-6xl font-bold text-gray-800 mb-4">
                        <span class="bg-gradient-to-r from-pink-500 to-purple-600 bg-clip-text text-transparent">
                            Hubungi Kami
                        </span>
                    </h1>
                    <p class="text-lg md:text-xl text-gray-600 max-w-2xl mx-auto leading-relaxed">
                        Ada pertanyaan atau saran? Kami siap membantu Anda dengan pelayanan terbaik
                    </p>
                </div>

                <!-- Contact Cards Grid -->
                <div class="grid md:grid-cols-2 gap-8 mb-12">
                    <!-- Contact Info Card -->
                    <div class="bg-white/80 backdrop-blur-lg rounded-3xl shadow-2xl p-8 border border-white/20 card-hover-effect transition-all duration-300"
                        data-aos="fade-right" data-aos-duration="800" data-aos-delay="200">
                        <h3 class="text-2xl font-bold text-gray-800 mb-6 flex items-center gap-3">
                            <i class="fas fa-info-circle text-pink-500"></i>
                            Informasi Kontak
                        </h3>

                        <div class="space-y-6">
                            <div class="flex items-center gap-4 p-4 bg-gradient-to-r from-pink-50 to-purple-50 rounded-2xl hover:shadow-lg transition-all duration-300">
                                <div class="w-12 h-12 bg-pink-500 rounded-full flex items-center justify-center text-white float-animation">
                                    <i class="fas fa-phone"></i>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">Telepon</p>
                                    <p class="font-semibold text-gray-800">0817-1712-1250</p>
                                </div>
                            </div>

                            <div class="flex items-center gap-4 p-4 bg-gradient-to-r from-blue-50 to-cyan-50 rounded-2xl hover:shadow-lg transition-all duration-300">
                                <div class="w-12 h-12 bg-blue-500 rounded-full flex items-center justify-center text-white float-animation" style="animation-delay: 0.5s;">
                                    <i class="fab fa-instagram"></i>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">Instagram</p>
                                    <p class="font-semibold text-gray-800">@asokababyofficial</p>
                                </div>
                            </div>

                            <div class="flex items-center gap-4 p-4 bg-gradient-to-r from-green-50 to-emerald-50 rounded-2xl hover:shadow-lg transition-all duration-300">
                                <div class="w-12 h-12 bg-green-500 rounded-full flex items-center justify-center text-white float-animation" style="animation-delay: 1s;">
                                    <i class="fas fa-map-marker-alt"></i>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">Lokasi</p>
                                    <p class="font-semibold text-gray-800">Asoka Baby Store</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Contact Form Card -->
                    <div class="bg-white/80 backdrop-blur-lg rounded-3xl shadow-2xl p-8 border border-white/20 card-hover-effect transition-all duration-300"
                        data-aos="fade-left" data-aos-duration="800" data-aos-delay="400">
                        <h3 class="text-2xl font-bold text-gray-800 mb-6 flex items-center gap-3">
                            <i class="fas fa-envelope text-pink-500"></i>
                            Kirim Pesan
                        </h3>

                        <form class="space-y-6">
                            <div class="grid md:grid-cols-2 gap-4">
                                <div data-aos="fade-up" data-aos-duration="600" data-aos-delay="100">
                                    <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">
                                        <i class="fas fa-user text-pink-500 mr-2"></i>Nama Lengkap
                                    </label>
                                    <input type="text" id="name" name="name" placeholder="Masukkan nama lengkap"
                                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-pink-500 focus:border-transparent transition-all duration-300 bg-white/50 backdrop-blur-sm input-focus-effect">
                                </div>

                                <div data-aos="fade-up" data-aos-duration="600" data-aos-delay="200">
                                    <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">
                                        <i class="fas fa-envelope text-pink-500 mr-2"></i>Email
                                    </label>
                                    <input type="email" id="email" name="email" placeholder="john@gmail.com"
                                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-pink-500 focus:border-transparent transition-all duration-300 bg-white/50 backdrop-blur-sm input-focus-effect">
                                </div>
                            </div>

                            <div data-aos="fade-up" data-aos-duration="600" data-aos-delay="300">
                                <label for="phone" class="block text-sm font-semibold text-gray-700 mb-2">
                                    <i class="fas fa-phone text-pink-500 mr-2"></i>No Handphone
                                </label>
                                <input type="text" id="phone" name="hp" placeholder="08555111245" autocomplete="off"
                                    class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-pink-500 focus:border-transparent transition-all duration-300 bg-white/50 backdrop-blur-sm input-focus-effect">
                            </div>

                            <div data-aos="fade-up" data-aos-duration="600" data-aos-delay="400">
                                <label for="subject" class="block text-sm font-semibold text-gray-700 mb-2">
                                    <i class="fas fa-tag text-pink-500 mr-2"></i>Subject
                                </label>
                                <select id="subject" name="subject"
                                    class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-pink-500 focus:border-transparent transition-all duration-300 bg-white/50 backdrop-blur-sm input-focus-effect">
                                    <option value="">Pilih Subject</option>
                                    <option value="Pelayanan">Pelayanan</option>
                                    <option value="Product">Product</option>
                                    <option value="Promo">Promo</option>
                                    <option value="Transaksi">Transaksi</option>
                                    <option value="Review">Review</option>
                                </select>
                            </div>

                            <div data-aos="fade-up" data-aos-duration="600" data-aos-delay="500">
                                <label for="message" class="block text-sm font-semibold text-gray-700 mb-2">
                                    <i class="fas fa-comment text-pink-500 mr-2"></i>Pesan
                                </label>
                                <textarea id="message" name="message" rows="5" placeholder="Ketik pesan Anda di sini..."
                                    class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-pink-500 focus:border-transparent transition-all duration-300 bg-white/50 backdrop-blur-sm resize-none input-focus-effect"></textarea>
                            </div>

                            <div class="text-center" data-aos="fade-up" data-aos-duration="600" data-aos-delay="600">
                                <button type="button" id="send"
                                    class="group relative inline-flex items-center justify-center px-8 py-4 text-lg font-semibold text-white bg-gradient-to-r from-pink-500 to-purple-600 rounded-2xl shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-300 overflow-hidden btn-hover-effect">
                                    <span class="relative z-10 flex items-center gap-2">
                                        <i class="fas fa-paper-plane"></i>
                                        Kirim Pesan
                                    </span>
                                    <div class="absolute inset-0 bg-gradient-to-r from-purple-600 to-pink-500 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Additional Info Section -->
                <div class="text-center" data-aos="fade-up" data-aos-duration="800" data-aos-delay="600">
                    <div class="bg-white/60 backdrop-blur-lg rounded-2xl p-6 border border-white/20">
                        <h4 class="text-xl font-semibold text-gray-800 mb-3">Mengapa Memilih Kami?</h4>
                        <div class="grid md:grid-cols-3 gap-6 mt-6">
                            <div class="text-center">
                                <div class="w-16 h-16 bg-pink-100 rounded-full flex items-center justify-center mx-auto mb-3">
                                    <i class="fas fa-clock text-pink-500 text-xl"></i>
                                </div>
                                <h5 class="font-semibold text-gray-800 mb-2">Respon Cepat</h5>
                                <p class="text-sm text-gray-600">Kami akan merespon dalam waktu 24 jam</p>
                            </div>
                            <div class="text-center">
                                <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-3">
                                    <i class="fas fa-shield-alt text-blue-500 text-xl"></i>
                                </div>
                                <h5 class="font-semibold text-gray-800 mb-2">Terpercaya</h5>
                                <p class="text-sm text-gray-600">Pelayanan yang aman dan terpercaya</p>
                            </div>
                            <div class="text-center">
                                <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-3">
                                    <i class="fas fa-heart text-green-500 text-xl"></i>
                                </div>
                                <h5 class="font-semibold text-gray-800 mb-2">Ramah</h5>
                                <p class="text-sm text-gray-600">Tim kami siap membantu dengan ramah</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Bottom Navigation Component -->
    <?php include 'src/component/bottom_navigation_other.php'; ?>

    <!-- Footer -->
    <?php include 'src/component/footer.php'; ?>
    <script src="src/js/send_contact_us.js"></script>
    <script>
        AOS.init();

        // Menu Toggle Functionality
        const menuToogle = document.getElementById("menu-toogle");
        const menuMobile = document.getElementById("menu-mobile");

        menuToogle.addEventListener('click', () => {
            menuToogle.querySelector('i').classList.toggle('fa-bars');
            menuToogle.querySelector('i').classList.toggle('fa-xmark');
            if (menuMobile.classList.contains("scale-y-0")) {
                menuMobile.classList.remove("hidden");
                menuMobile.offsetWidth;
                menuMobile.classList.remove("scale-y-0", "opacity-0")
                menuMobile.classList.add("scale-y-100", "opacity-100")
            } else {
                menuMobile.classList.remove("scale-y-100", "opacity-100");
                menuMobile.classList.add("scale-y-0", "opacity-0");
                setTimeout(() => {
                    menuMobile.classList.add("hidden");
                }, 300)
            }
        });
    </script>
</body>

</html>