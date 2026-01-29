<?php
session_start();
require_once __DIR__ . '/../../component/menu_handler.php';
// $menuHandler = new MenuHandler('user_supplier_update'); 
// if (!$menuHandler->initialize()) { exit(); }

$id = $_GET['id'] ?? null;
if(!$id) {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User Supplier</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/all.min.css">
    <link rel="stylesheet" href="../../style/header.css">
    <link rel="stylesheet" href="../../style/sidebar.css">
    <link rel="stylesheet" href="../../output2.css">
    <link rel="stylesheet" href="../../style/pink-theme.css">
    <link rel="icon" type="image/png" href="../../../public/images/logo1.png">
    
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">
    <style>
        .ts-control {
            border-radius: 0.5rem;
            padding: 0.75rem 1rem;
            border-color: #e5e7eb;
            font-size: 0.875rem;
            background-color: #f9fafb;
        }
        .ts-control.focus {
            box-shadow: none;
            border-color: #db2777;
            background-color: #fff;
        }
        .ts-wrapper.multi .ts-control > div {
            background: #fdf2f8; 
            color: #be185d;       
            border: 1px solid #fbcfe8;
            border-radius: 4px;
        }
        .ts-dropdown {
            border-radius: 0.5rem;
            border-color: #db2777;
        }
        .ts-dropdown .active {
            background-color: #fdf2f8;
            color: #be185d;
        }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
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
                            <div class="icon-wrapper bg-orange-100 text-orange-600">
                                <i class="fa-solid fa-pen-to-square fa-lg"></i>
                            </div>
                            <div>
                                <h1 class="text-xl font-bold text-gray-800 mb-1">Edit Supplier</h1>
                                <p class="text-xs text-gray-600">Perbarui informasi akun supplier.</p>
                            </div>
                        </div>
                        <a href="index.php" class="btn-secondary inline-flex items-center justify-center gap-2 no-underline">
                            <i class="fa-solid fa-arrow-left"></i>
                            <span>Kembali</span>
                        </a>
                    </div>
                </div>

                <form id="formUpdate" class="filter-card relative">
                    <input type="hidden" name="kode" id="kode" value="<?= htmlspecialchars($id) ?>">
                    
                    <div id="form-loading" class="absolute inset-0 bg-white/80 z-10 flex items-center justify-center">
                        <div class="spinner-simple border-pink-500"></div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Nama Supplier <span class="text-red-500">*</span></label>
                            <input type="text" name="nama" id="nama" class="input-modern w-full" required>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Email</label>
                            <input type="email" name="email" id="email" class="input-modern w-full">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">No. Telepon</label>
                            <input type="text" name="no_telpon" id="no_telpon" class="input-modern w-full">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Password Baru</label>
                            <input type="password" name="password" class="input-modern w-full" placeholder="Kosongkan jika tidak ingin mengubah password">
                            <p class="text-[10px] text-gray-400 mt-1">*Hanya isi jika ingin mengganti password.</p>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Wilayah Cakupan</label>
                            <select id="select-wilayah" multiple placeholder="Memuat data..." autocomplete="off">
                            </select>
                            <p class="text-[10px] text-gray-400 mt-1">*Bisa pilih lebih dari satu.</p>
                        </div>
                    </div>

                    <div class="mt-6 border-t border-gray-100 pt-4">
                        <button type="submit" id="btn-submit" class="btn-primary w-full py-3 flex items-center justify-center gap-2 text-base shadow-lg shadow-pink-200">
                            <i class="fa-solid fa-save"></i>
                            <span>Perbarui Data</span>
                        </button>
                    </div>
                </form>
            </div>
        </section>
    </main>

    <script src="/src/js/middleware_auth.js"></script>
    <script src="../../js/user_supplier/update_handler.js" type="module"></script>
</body>
</html>