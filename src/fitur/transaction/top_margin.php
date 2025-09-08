<?php
require_once __DIR__ . '/../../component/menu_handler.php';

$menuHandler = new MenuHandler('top_margin');

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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Top Margin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/all.min.css">
    <link rel="stylesheet" href="/css/header.css">
    <link rel="stylesheet" href="/css/sidebar.css">
    <link rel="stylesheet" href="/css/animation-fade-in.css">
    <link rel="icon" type="image/png" href="/images/logo1.png">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/src/style/default-font.css">
    <link rel="stylesheet" href="/src/output2.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <script src="https://cdn.jsdelivr.net/npm/toastify-js" defer></script>

    <style>
        body,
        .font-poppins {
            font-family: 'Poppins', sans-serif;
        }

        .scrollbar-thin::-webkit-scrollbar {
            height: 8px;
            width: 8px;
        }

        .scrollbar-thin::-webkit-scrollbar-thumb {
            background: #e5e7eb;
            border-radius: 8px;
        }

        .scrollbar-thin::-webkit-scrollbar-track {
            background: transparent;
        }
    </style>
</head>

<body class="bg-gradient-to-br from-white to-emerald-50 min-h-screen flex">
    <?php include '../../component/navigation_report.php'; ?>
    <?php include '../../component/sidebar_report.php'; ?>

    <main id="main-content"
        class="flex-1 p-8 transition-all duration-300 ml-64 mt-16 font-sans antialiased text-gray-800">
        <div class="max-w-8xl mx-auto">
            <!-- HEADER & FILTER -->
            <div class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between gap-6">
                <div>
                    <div class="flex items-center gap-4 mb-2">
                        <span
                            class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-br from-green-500 to-emerald-400 shadow-xl">
                            <i class="fas fa-coins text-white text-3xl"></i>
                        </span>
                        <div>
                            <h1
                                class="text-4xl font-extrabold bg-gradient-to-r from-green-600 to-emerald-500 bg-clip-text text-transparent tracking-tight leading-tight font-poppins drop-shadow">
                                TOP Margin Cabang</h1>
                            <p class="text-gray-500 text-lg font-medium font-poppins tracking-tight mt-1">Laporan margin
                                per cabang, prioritas audit & monitoring</p>
                        </div>
                    </div>
                </div>

            </div>

            <!-- RINGKASAN STATISTIK -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8" id="top3-minus-summary">
                <!-- Card diisi via JS -->
            </div>

            <!-- MARGIN MINUS PER CABANG SECTION -->
            <div>
                <h2 class="text-xl font-bold text-emerald-400 mb-4 flex items-center gap-2"><i
                        class="fas fa-arrow-trend-down"></i> Margin Minus per Cabang</h2>
                <div id="minus-margin-cards" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
                    <!-- Card diisi via JS -->
                </div>
            </div>



            <!-- MODAL DETAIL -->
            <div id="modal-detail"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40 hidden font-poppins">
                <div
                    class="bg-white rounded-2xl border-4 border-emerald-200 shadow-2xl p-6 w-full max-w-8xl relative overflow-y-auto max-h-[100vh]">
                    <button id="close-modal"
                        class="absolute top-3 right-3 text-gray-400 hover:text-red-500 text-3xl font-bold">&times;</button>
                    <div class="flex items-center gap-3 mb-4">
                        <span
                            class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-gradient-to-br from-green-500 to-emerald-400 shadow-lg">
                            <i class="fas fa-ban text-white text-2xl"></i>
                        </span>
                        <h2
                            class="text-xl font-extrabold bg-gradient-to-r from-green-600 to-emerald-500 bg-clip-text text-transparent font-poppins" id="modal-detail-title"></h2>
                    </div>
                    <div class="overflow-x-auto overflow-y-auto max-h-[500px] bg-white rounded-2xl scrollbar-thin">
                        <table class="min-w-full table-auto text-sm font-poppins border border-emerald-200">
                            <thead
                                class="bg-gradient-to-r from-green-600 to-emerald-500 text-white text-xs uppercase tracking-wide">
                                <tr class="border-b border-emerald-200">
                                    <th class="px-4 py-2">No</th>
                                    <th class="px-4 py-2">PLU</th>
                                    <th class="px-4 py-2 text-left">No Bon</th>
                                    <th class="px-4 py-2 text-left">Nama Produk</th>
                                    <th class="px-4 py-2">Qty</th>
                                    <th class="px-4 py-2">Gross</th>
                                    <th class="px-4 py-2">Net</th>
                                    <th class="px-4 py-2">Avg Cost</th>
                                    <th class="px-4 py-2">PPN</th>
                                    <th class="px-4 py-2">Margin</th>
                                    <th class="px-4 py-2">Tanggal</th>
                                    <th class="px-4 py-2">Cabang</th>
                                </tr>
                            </thead>
                            <tbody id="modal-detail-tbody" class="bg-white divide-y divide-gray-200">
                                <!-- Data diisi via JS -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="../../js/ui/navbar_toogle.js" type="module"></script>
    <script type="module">
        import { getTopMargin, getDetailMargin } from '/src/js/margin/fetch/get_top_margin.js';
        import { renderTop3Minus, renderMinusMarginCards, renderDetailMargin } from '/src/js/margin/table/render.js';
        import { getCookie } from '/src/js/index/utils/cookies.js';

        const token = getCookie('token');
        const data = await getTopMargin(token);
        renderTop3Minus(data.data);
        renderMinusMarginCards(data.data);
        window.onclick = (e) => {
            if (e.target.id === "modal-detail") {
                closeModal();
            }
        }
        document.querySelectorAll(".item-detail").forEach(item => {
            item.addEventListener("click", async () => {
                const store = item.dataset.store;
                const data = await getDetailMargin(store, token);
                const modal = document.getElementById("modal-detail");
                modal.classList.remove("hidden");
                document.getElementById("modal-detail-title").textContent = `Detail Transaksi Margin ${item.dataset.storeName}`;
                renderDetailMargin(data.data);
            })
        })
        document.getElementById("close-modal").addEventListener("click", closeModal);
        function closeModal() {
            const modal = document.getElementById("modal-detail");
            modal.classList.add("hidden");
        }
    </script>
</body>

</html>