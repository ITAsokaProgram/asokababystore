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
$pageName = "Nomor Cabang";

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
    </style>
</head>

<body class="bg-white text-gray-800 overflow-x-hidden w-full">
    <!-- Header -->
    <header class="bg-white shadow-md sticky top-0 z-50">
        <div class="max-w-6xl mx-auto px-4 py-4 flex items-center justify-between">
            <a href="/">
                <img src="public/images/logo.png" alt="Logo Asoka Baby Store" class="w-25 h-8" />
            </a>
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
        <section class="min-h-screen bg-gradient-to-br from-green-50 via-white to-pink-50 flex items-center justify-center p-6 relative overflow-hidden">
            <!-- Background Pattern -->
            <div class="absolute inset-0 opacity-10 pointer-events-none select-none">
                <div class="absolute top-24 left-24 w-72 h-72 bg-green-300 rounded-full mix-blend-multiply filter blur-xl animate-pulse"></div>
                <div class="absolute top-40 right-24 w-72 h-72 bg-pink-300 rounded-full mix-blend-multiply filter blur-xl animate-pulse animation-delay-2000"></div>
                <div class="absolute -bottom-8 left-40 w-72 h-72 bg-blue-300 rounded-full mix-blend-multiply filter blur-xl animate-pulse animation-delay-4000"></div>
            </div>
            <div class="relative z-20 w-full max-w-4xl mx-auto">
                <div class="text-center mb-10" data-aos="fade-down" data-aos-duration="800">
                    <h2 class="text-4xl md:text-5xl font-bold mb-2 bg-gradient-to-r from-green-500 via-pink-500 to-blue-500 bg-clip-text text-transparent">
                        Pesan Sekarang
                    </h2>
                    <p class="text-lg text-gray-600 max-w-2xl mx-auto leading-relaxed">
                        Cek barang dan harga lewat WhatsApp kami. Pilih cabang terdekat dari daftar berikut:
                    </p>
                </div>
                <div id="container-wa" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-8">
                    <!-- Card WhatsApp akan diisi oleh JS -->
                </div>
            </div>
        </section>
    </main>

    <!-- Bottom Navigation Component -->
    <?php include 'src/component/bottom_navigation_other.php'; ?>

    <!-- Footer -->
    <?php include 'src/component/footer.php'; ?>
    <script>
        AOS.init();
        const dataCabang = [
            { number: '081808174105', label: 'Daan Mogot Baru', kota: 'Jakarta Barat' },
            { number: '087739974652', label: 'Condet', kota: 'Jakarta Timur' },
            { number: '085951449821', label: 'Duren Sawit', kota: 'Jakarta Timur' },
            { number: '087773844521', label: 'Rawamangun', kota: 'Jakarta Timur' },
            { number: '087863814646', label: 'Cibubur', kota: 'Jakarta Timur' },
            { number: '081806683401', label: 'Poris', kota: 'Tangerang' },
            { number: '087849816901', label: 'Ciledug', kota: 'Tangerang' },
            { number: '087775692431', label: 'Bintaro', kota: ' Tangerang Selatan' },
            { number: '085947461478', label: 'Pamulang', kota: 'Tangerang Selatan' },
            { number: '087846959785', label: 'Graha Raya', kota: 'Tangerang Selatan' },
            { number: '085965847263', label: 'Ceger', kota: 'Tangerang Selatan' },
            { number: '087889552647', label: 'Harapan Indah', kota: 'Bekasi' },
            { number: '081998482529', label: 'Jatiwaringin', kota: 'Bekasi' },
            { number: '085952415221', label: 'Galaxy', kota: 'Bekasi' },
            { number: '087856599869', label: 'Jatiasih', kota: 'Bekasi' },
            { number: '087787987127', label: 'Cinere', kota: 'Depok' },
            { number: '087849816904', label: 'Kartini', kota: 'Depok' },
            { number: '087887689802', label: 'Parung', kota: 'Bogor' },
        ];

        const containerWA = document.getElementById("container-wa");
        dataCabang.forEach((contact, idx) => {
            const html = `
                <div class="flex flex-col items-center justify-center bg-white/80 rounded-2xl shadow-xl border border-green-100 p-6 transition-all duration-300 hover:scale-105 hover:shadow-2xl" data-aos="fade-up" data-aos-delay="${100 + idx * 50}" data-aos-duration="700">
                    <div class="w-16 h-16 flex items-center justify-center rounded-full bg-green-500 mb-3 shadow-lg animate-pulse">
                        <i class="fa-brands fa-whatsapp text-white text-3xl"></i>
                    </div>
                    <div class="text-center mb-2">
                        <div class="font-bold text-lg text-gray-800">${contact.label}</div>
                        <div class="text-sm text-gray-500">${contact.kota}</div>
                    </div>
                    <a href="https://wa.me/+62${contact.number}" target="_blank"
                        class="inline-flex justify-center items-center gap-2 px-4 py-2 bg-green-500 hover:bg-green-600 text-white rounded-lg text-sm font-semibold transition">
                        <i class="fa-brands fa-whatsapp"></i>
                        ${contact.number}
                    </a>
                </div>`
            containerWA.insertAdjacentHTML("beforeend", html);
        });

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