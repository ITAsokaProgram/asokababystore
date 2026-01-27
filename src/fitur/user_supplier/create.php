<?php
session_start();
require_once __DIR__ . '/../../component/menu_handler.php';
// $menuHandler = new MenuHandler('user_supplier_create'); 
// if (!$menuHandler->initialize()) { exit(); }
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah User Supplier</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/all.min.css">
    <link rel="stylesheet" href="../../style/header.css">
    <link rel="stylesheet" href="../../style/sidebar.css">
    <link rel="stylesheet" href="../../style/animation-fade-in.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../style/default-font.css">
    <link rel="stylesheet" href="../../output2.css">
    <link rel="stylesheet" href="../../style/pink-theme.css">
    <link rel="icon" type="image/png" href="../../../public/images/logo1.png">
    
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="bg-gray-50">

    <?php include '../../component/navigation_report.php'; ?>
    <?php include '../../component/sidebar_report.php'; ?>

    <main id="main-content" class="flex-1 p-4 ml-64">
        <section class="min-h-screen">
            <div class="max-w-4xl mx-auto">

                <div class="header-card p-4 rounded-2xl mb-6">
                    <div class="flex items-center justify-between flex-wrap gap-3">
                        <div class="flex items-center gap-3">
                            <div class="icon-wrapper">
                                <i class="fa-solid fa-user-plus fa-lg"></i>
                            </div>
                            <div>
                                <h1 class="text-xl font-bold text-gray-800 mb-1">Tambah Supplier</h1>
                                <p class="text-xs text-gray-600">Buat akun akses untuk supplier baru.</p>
                            </div>
                        </div>
                        <a href="index.php" class="btn-secondary inline-flex items-center justify-center gap-2 no-underline">
                            <i class="fa-solid fa-arrow-left"></i>
                            <span>Kembali</span>
                        </a>
                    </div>
                </div>

                <form id="formCreate" class="filter-card">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Nama Supplier <span class="text-red-500">*</span></label>
                            <input type="text" name="nama" class="input-modern w-full" placeholder="PT. Sumber Rejeki" required>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Email</label>
                            <input type="email" name="email" class="input-modern w-full" placeholder="email@domain.com">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">No. Telepon</label>
                            <input type="text" name="no_telpon" class="input-modern w-full" placeholder="0812...">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Password <span class="text-red-500">*</span></label>
                            <input type="password" name="password" class="input-modern w-full" placeholder="******" required>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Wilayah Cakupan</label>
                            <textarea name="wilayah" class="input-modern w-full h-24" placeholder="Pisahkan dengan koma. Contoh: Jakarta Barat, Tangerang, Bekasi"></textarea>
                        </div>

                    </div>

                    <div class="mt-6 border-t border-gray-100 pt-4">
                        <button type="submit" id="btn-submit" class="btn-primary w-full py-3 flex items-center justify-center gap-2 text-base shadow-lg shadow-pink-200">
                            <i class="fa-solid fa-save"></i>
                            <span>Simpan Data</span>
                        </button>
                    </div>
                </form>

            </div>
        </section>
    </main>

    <script src="/src/js/middleware_auth.js"></script>
    <script src="../../js/user_supplier/create_handler.js" type="module"></script>
</body>
</html>