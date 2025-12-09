<?php
require_once __DIR__ . '/../../component/menu_handler.php';

$menuHandler = new MenuHandler('transaksi_cabang');

if (!$menuHandler->initialize()) {
    exit();
}

$user_id = $menuHandler->getUserId();
$logger = $menuHandler->getLogger();
$token = $menuHandler->getToken();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaksi Cabang</title>

    <!-- font awesome cdn link  -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/all.min.css">

    <!-- Penjelasan: Link ke CSS eksternal dengan cache busting query string -->
    <link rel="stylesheet" href="../../style/header.css">
    <link rel="stylesheet" href="../../style/sidebar.css">
    <!-- Setting logo pada tab di website Anda / Favicon -->
    <link rel="icon" type="image/png" href="../../../public/images/logo1.png">
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="../../style/default-font.css">
    <!-- <link rel="stylesheet" href="../../style/output.css"> -->
    <link rel="stylesheet" href="../../output2.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/2.2.2/css/dataTables.dataTables.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css">
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


    <style>
        /* --- Enhanced Glass & Card Styles --- */
        .glass-container {
            background: rgba(255, 255, 255, 0.80);
            backdrop-filter: blur(8px);
            border-radius: 1.25rem;
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.18);
            border: 1.5px solid #bae6fd;
            padding: 2rem;
        }

        .card-glass {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(6px);
            border-radius: 1.25rem;
            box-shadow: 0 4px 24px 0 rgba(31, 38, 135, 0.10);
            border: 1.5px solid #22c55e;
            transition: box-shadow 0.2s, transform 0.2s;
        }

        .card-glass:hover {
            box-shadow: 0 8px 32px 0 rgba(34, 197, 94, 0.18);
            transform: scale(1.02);
        }
    </style>
</head>

<body class="bg-white">
    <?php include '../../component/navigation_report.php'; ?>
    <?php include '../../component/sidebar_report.php'; ?>


    <main id="main-content"
        class="flex-1 p-6 transition-all duration-300 ml-64 bg-gradient-to-br from-gray-50 via-blue-50 to-indigo-50 min-h-screen">
        <div
            class="glass-container animate-fade-in-up backdrop-blur-sm bg-white/80 rounded-2xl shadow-xl border border-white/20 p-6">
            <div
                class="flex flex-col md:flex-row items-center justify-between mb-8 gap-6 p-4 bg-gradient-to-r from-green-50 to-emerald-50 rounded-xl border border-green-200/50">
                <div class="flex flex-col md:flex-row items-center gap-4">
                    <button onclick="window.history.back()"
                        class="bg-gradient-to-r from-green-500 to-emerald-600 hover:from-blue-600 hover:to-emerald-700 text-white px-6 py-3 rounded-xl shadow-lg border-0 text-lg flex items-center gap-3 font-semibold hover:scale-105 hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
                        <i class="fa-solid fa-arrow-left text-white animate-pulse"></i>
                    </button>
                    <h1
                        class="text-3xl font-bold bg-gradient-to-r from-green-600 to-emerald-700 bg-clip-text text-transparent flex items-center gap-3">
                        <i class="fa-solid fa-store text-green-600"></i> Transaksi Cabang
                    </h1>
                </div>
                <div class="flex items-center gap-2 text-sm text-green-600">
                    <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                    <span class="font-medium">View Data</span>
                </div>
            </div>

            <div class="space-y-8">
                <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl p-4 border border-blue-200/50">
                    <h2 class="text-xl font-semibold text-blue-800 mb-4 flex items-center gap-2">
                        <i class="fa-solid fa-chart-line text-blue-600"></i>
                        Semua Cabang
                    </h2>
                    <div id="cabang-container-all"
                        class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-6">
                        <!-- Card cabang akan diisi JS -->
                    </div>
                </div>

                <div class="bg-gradient-to-r from-emerald-50 to-green-50 rounded-xl p-4 border border-emerald-200/50">
                    <h2 class="text-xl font-semibold text-emerald-800 mb-4 flex items-center gap-2">
                        <i class="fa-solid fa-filter text-emerald-600"></i>
                        Cabang
                    </h2>
                    <div id="cabang-container"
                        class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-6">
                        <!-- Card cabang akan diisi JS -->
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- custom js file link -->
    <!-- AlpineJS -->
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <!-- Custom JS -->
    <script src="../../js/middleware_auth.js"></script>
    <script src="../../js/transaction_branch/main.js" type="module"></script>




</body>

</html>