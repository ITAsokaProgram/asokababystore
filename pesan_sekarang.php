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
$pageName = "Cara Pesan & Kontak WA"; // Nama halaman diupdate

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
    <title>Cara Pesan & Kontak WhatsApp - Asoka Baby Store</title>
    <meta name="description" content="Ikuti cara pemesanan mudah via WhatsApp dan hubungi cabang Asoka Baby Store terdekat untuk memulai belanja perlengkapan bayi Anda." />
    <meta name="keywords" content="cara pesan, kontak whatsapp, toko bayi, asoka baby store" />
    <meta name="author" content="Asoka Baby Store" />

    <meta property="og:title" content="Cara Pesan & Kontak WhatsApp - Asoka Baby Store" />
    <meta property="og:description" content="Langkah mudah memesan via WhatsApp dan daftar lengkap kontak cabang kami." />
    <meta property="og:image" content="https://asokababystore.com/images/logo1.png" />
    <meta property="og:url" content="https://asokababystore.com/pesan_sekarang" />
    <meta property="og:type" content="website" />

    <link rel="icon" type="image/png" href="/images/logo1.png" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" integrity="sha512-..." crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/src/output2.css">
    
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        * {
            font-family: 'Poppins', 'sans-serif';
            font-weight: 400;
        }

        html {
            scroll-behavior: smooth;
        }
        
        @keyframes gradient-shift {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
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

<body class="bg-white text-gray-800 overflow-x-hidden w-full">
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
        <section id="cara-pesan" class="py-20 bg-gradient-to-br from-pink-50 via-white to-purple-50">
            <div class="max-w-6xl mx-auto px-5">
                
                <div class="text-center mb-12" data-aos="fade-down">
                    <h2 class="text-3xl md:text-4xl font-bold mb-4 gradient-text">Cara Pemesanan via WhatsApp</h2>
                    <p class="text-lg text-gray-600 max-w-3xl mx-auto">Ikuti langkah mudah ini untuk memesan produk favorit Anda.</p>
                </div>

                <div class="max-w-3xl mx-auto bg-white/80 backdrop-blur-lg rounded-2xl shadow-xl p-6 md:p-8 border border-pink-100" data-aos="fade-up" data-aos-delay="100">
                    <div class="space-y-6">
                        <div class="flex items-start gap-4">
                            <div class="flex-shrink-0 w-10 h-10 bg-gradient-to-r from-pink-500 to-purple-600 text-white text-lg font-bold rounded-full flex items-center justify-center shadow-md">1</div>
                            <div>
                                <h3 class="font-bold text-gray-800">Hubungi & Pesan Barang</h3>
                                <p class="text-gray-600 text-sm">Pilih cabang terdekat dari daftar di bawah, lalu kirimkan daftar pesanan Anda via chat.</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-4">
                            <div class="flex-shrink-0 w-10 h-10 bg-gradient-to-r from-pink-500 to-purple-600 text-white text-lg font-bold rounded-full flex items-center justify-center shadow-md">2</div>
                            <div>
                                <h3 class="font-bold text-gray-800">Terima Struk & Lakukan Pembayaran</h3>
                                <p class="text-gray-600 text-sm">Anda akan menerima foto struk. Lakukan pembayaran sesuai nominal ke rekening kami.</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-4">
                            <div class="flex-shrink-0 w-10 h-10 bg-gradient-to-r from-pink-500 to-purple-600 text-white text-lg font-bold rounded-full flex items-center justify-center shadow-md">3</div>
                            <div>
                                <h3 class="font-bold text-gray-800">Kirim Bukti & Barang Disiapkan</h3>
                                <p class="text-gray-600 text-sm">Kirim bukti transfer. Setelah konfirmasi, kami akan langsung menyiapkan pesanan Anda.</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-4">
                           <div class="flex-shrink-0 w-10 h-10 bg-gradient-to-r from-pink-500 to-purple-600 text-white text-lg font-bold rounded-full flex items-center justify-center shadow-md">4</div>
                            <div>
                                <h3 class="font-bold text-gray-800">Pesan Ojek Online</h3>
                                <p class="text-gray-600 text-sm">Setelah kami konfirmasi barang siap, Anda bisa memesan GoSend / Grab Express untuk pengambilan.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-center mt-12" data-aos="fade-up" data-aos-delay="200">
                    <a href="#kontak" aria-label="Scroll ke daftar kontak" class="inline-block text-pink-500 text-4xl animate-bounce">
                        <i class="fas fa-chevron-down"></i>
                    </a>
                </div>
                </div>
        </section>

        <section id="kontak" class="py-20 bg-gradient-to-br from-green-50 via-white to-blue-50 relative overflow-hidden">
            <div class="absolute inset-0 opacity-10 pointer-events-none select-none">
                <div class="absolute top-24 left-24 w-72 h-72 bg-green-300 rounded-full mix-blend-multiply filter blur-xl animate-pulse"></div>
                <div class="absolute top-40 right-24 w-72 h-72 bg-pink-300 rounded-full mix-blend-multiply filter blur-xl animate-pulse animation-delay-2000"></div>
            </div>
            <div class="relative z-10 w-full max-w-5xl mx-auto px-5">
                <div class="text-center mb-12" data-aos="fade-down">
                    <h2 class="text-3xl md:text-4xl font-bold mb-4 bg-gradient-to-r from-green-500 via-pink-500 to-blue-500 bg-clip-text text-transparent">
                        Pilih Cabang Terdekat Anda
                    </h2>
                    <p class="text-lg text-gray-600 max-w-2xl mx-auto">
                        Klik pada nomor untuk langsung memulai chat WhatsApp dengan cabang pilihan Anda.
                    </p>
                </div>
                <div id="container-wa" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                </div>
            </div>
        </section>
    </main>

    <?php include 'src/component/bottom_navigation_other.php'; ?>

    <?php include 'src/component/footer.php'; ?>
    
    <script>
        AOS.init({
            once: true,
            duration: 800,
        });

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
                <div class="flex flex-col items-center justify-center bg-white/80 rounded-2xl shadow-xl border border-green-100 p-5 transition-all duration-300 hover:scale-105 hover:shadow-2xl" data-aos="fade-up" data-aos-delay="${100 + idx * 50}">
                    <div class="w-16 h-16 flex items-center justify-center rounded-full bg-green-500 mb-3 shadow-lg">
                        <i class="fa-brands fa-whatsapp text-white text-3xl"></i>
                    </div>
                    <div class="text-center mb-3">
                        <div class="font-bold text-lg text-gray-800">${contact.label}</div>
                        <div class="text-sm text-gray-500">${contact.kota}</div>
                    </div>
                    <a href="https://wa.me/62${contact.number.substring(1)}" target="_blank"
                        class="inline-flex justify-center items-center gap-2 w-full px-4 py-2 bg-green-500 hover:bg-green-600 text-white rounded-lg text-sm font-semibold transition">
                        <i class="fa-brands fa-whatsapp"></i>
                        <span>Chat Sekarang</span>
                    </a>
                </div>`
            containerWA.insertAdjacentHTML("beforeend", html);
        });
    </script>
</body>

</html>