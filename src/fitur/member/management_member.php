<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Member</title>

    <!-- font awesome cdn link  -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/all.min.css">

    <!-- CSS Files -->
    <link rel="stylesheet" href="../../style/header.css">
    <link rel="stylesheet" href="../../style/sidebar.css">
    <link rel="stylesheet" href="../../style/animation-fade-in.css">
    <link rel="stylesheet" href="../../../css/cabang_selective.css">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap"
        rel="stylesheet">
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap"
        rel="stylesheet">

    <!-- Setting logo pada tab di website Anda / Favicon -->
    <link rel="icon" type="image/png" href="../../../public/images/logo1.png">
    <link rel="stylesheet" href="../../style/default-font.css">
    <link rel="stylesheet" href="../../output2.css">

    <!-- GSAP Animation -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>

    <!-- Tippy.js -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tippy.js@6/dist/tippy.css" />
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/tippy.js@6/dist/tippy-bundle.umd.min.js"></script>

    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>

    <!-- Toastify -->
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>

    <style>
        .member-card {
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }

        .member-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(59, 130, 246, 0.15);
        }

        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .status-active {
            background-color: #d1fae5;
            color: #065f46;
        }

        .status-inactive {
            background-color: #fee2e2;
            color: #991b1b;
        }

        .status-pending {
            background-color: #fed7aa;
            color: #9a3412;
        }

        /* Member item hover and selection styles */
        .member-item {
            transition: all 0.2s ease-in-out;
        }

        .member-item:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }

        .member-item.selected {
            border-color: #3b82f6 !important;
            background-color: #eff6ff !important;
            box-shadow: 0 0 0 2px #3b82f6;
        }

        /* Prevent button clicks from triggering card click */
        .member-item button {
            position: relative;
            z-index: 10;
        }

        .search-box {
            position: relative;
            overflow: hidden;
        }

        .search-box::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(45deg, rgba(59, 130, 246, 0.1), rgba(147, 51, 234, 0.1));
            border-radius: inherit;
            padding: 1px;
            mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
            mask-composite: xor;
        }

        .floating-label {
            transition: all 0.3s ease;
            background: #fff;
            z-index: 10;
            padding: 0 0.25rem;
            position: absolute;
            left: 1rem;
            top: 1rem;
            pointer-events: none;
        }

        .member-input {
            transition: all 0.3s ease;
            background-color: #fff;
        }

        .member-input:focus {
            transform: scale(1.02);
        }

        .member-input:focus+.floating-label,
        .member-input:not(:placeholder-shown)+.floating-label {
            transform: translateY(-1.5rem) scale(0.85);
            color: #3b82f6;
            background: #fff;
            z-index: 10;
        }

        .gradient-border {
            background: linear-gradient(white, white) padding-box,
                linear-gradient(45deg, #3b82f6, #8b5cf6) border-box;
            border: 2px solid transparent;
        }

        @keyframes slideIn {
            from {
                transform: translateX(-100%);
                opacity: 0;
            }

            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        .slide-in {
            animation: slideIn 0.5s ease-out forwards;
        }

        .fade-in-header {
            opacity: 1;
            animation: fadeInHeader 0.6s ease-out forwards;
        }

        @keyframes fadeInHeader {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Custom scrollbar */
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 10px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        /* Status select option styling */
        #statusFilter option {
            padding: 8px 12px;
            border-radius: 6px;
            margin: 2px 0;
            font-weight: 500;
            background: white;
        }

        #statusFilter option[value="all"] {
            background: linear-gradient(45deg, rgba(59, 130, 246, 0.1), rgba(147, 51, 234, 0.1));
            color: #1e40af;
        }

        #statusFilter option[value="Aktif"] {
            background: #d1fae5;
            color: #065f46;
        }

        #statusFilter option[value="Non-Aktif"] {
            background: #fee2e2;
            color: #991b1b;
        }

        #statusFilter option[value="Member Lama Non-Aktif"] {
            background: #fed7aa;
            color: #9a3412;
        }
    </style>
</head>

<body class="bg-gradient-to-br from-slate-50 to-blue-50 text-gray-900">
    <?php include '../../component/navigation_report.php'; ?>
    <?php include '../../component/sidebar_report.php'; ?>

    <main id="main-content" class="flex-1 p-4 lg:p-6 transition-all duration-300 ml-64">
        <div class="max-w-7xl mx-auto space-y-6">

            <!-- Header Section -->
            <div class="bg-white/80 backdrop-blur-md rounded-2xl shadow-xl border border-blue-100 p-6 fade-in-header">
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                    <!-- Title & Description -->
                    <div class="flex items-center space-x-4">
                        <div class="bg-gradient-to-r from-blue-500 to-purple-600 p-3 rounded-xl shadow-lg">
                            <i class="fas fa-users text-white text-2xl"></i>
                        </div>
                        <div>
                            <h1
                                class="text-3xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">
                                Kelola Member
                            </h1>
                            <p class="text-gray-600 mt-1">Manajemen data member dan informasi lengkap</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div onclick="showMemberManagement('all')"
                    class="bg-gradient-to-r from-emerald-50 to-teal-50 p-6 rounded-xl border border-emerald-100 shadow-sm hover:shadow-md transition-all duration-200 member-card cursor-pointer hover:scale-105">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <p class="text-sm font-medium text-emerald-600 mb-1">Total Member</p>
                            <p class="text-2xl font-bold text-emerald-700" id="totalMember"></p>
                        </div>
                        <div class="bg-emerald-100 p-3 rounded-lg flex-shrink-0">
                            <i class="fas fa-users text-emerald-600 text-xl"></i>
                        </div>
                    </div>
                    <div class="mt-2 text-xs text-emerald-600 font-medium">
                        <i class="fas fa-mouse-pointer mr-1"></i>Klik untuk detail
                    </div>
                </div>

                <div
                    class="bg-gradient-to-r from-blue-50 to-indigo-50 p-6 rounded-xl border border-blue-100 shadow-sm hover:shadow-md transition-all duration-200 member-card cursor-pointer hover:scale-105">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <p class="text-sm font-medium text-blue-600 mb-1">Member Aktif</p>
                            <p class="text-2xl font-bold text-blue-700" id="memberAktif"></p>
                        </div>
                        <div class="bg-blue-100 p-3 rounded-lg flex-shrink-0">
                            <i class="fas fa-user-check text-blue-600 text-xl"></i>
                        </div>
                    </div>
                </div>

                <div
                    class="bg-gradient-to-r from-amber-50 to-orange-50 p-6 rounded-xl border border-amber-100 shadow-sm hover:shadow-md transition-all duration-200 member-card cursor-pointer hover:scale-105">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <p class="text-sm font-medium text-amber-600 mb-1">Member Baru</p>
                            <p class="text-2xl font-bold text-amber-700" id="memberBaru"></p>
                        </div>
                        <div class="bg-amber-100 p-3 rounded-lg flex-shrink-0">
                            <i class="fas fa-user-plus text-amber-600 text-xl"></i>
                        </div>
                    </div>
                </div>

                <div
                    class="bg-gradient-to-r from-purple-50 to-pink-50 p-6 rounded-xl border border-purple-100 shadow-sm hover:shadow-md transition-all duration-200 member-card cursor-pointer hover:scale-105">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <p class="text-sm font-medium text-purple-600 mb-1">Member Non Aktif</p>
                            <p class="text-2xl font-bold text-purple-700" id="memberNonAktif"></p>
                        </div>
                        <div class="bg-purple-100 p-3 rounded-lg flex-shrink-0">
                            <i class="fas fa-coins text-purple-600 text-xl"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Analytics & Advanced Features -->
            <div>

                <!-- Member Location Analytics -->
                <div class="bg-white/80 backdrop-blur-md rounded-2xl shadow-xl border border-blue-100 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <div class="bg-gradient-to-r from-green-500 to-emerald-600 p-2 rounded-lg">
                                <i class="fas fa-map-marker-alt text-white text-lg"></i>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-800">Sebaran Member</h3>
                        </div>
                        <button class="text-blue-600 hover:text-blue-800 text-sm font-medium"
                            onclick="window.location.href='sebaran_member'">
                            <i class="fas fa-external-link-alt mr-1"></i>Detail
                        </button>
                    </div>

                    <!-- kasih id di sini -->
                    <div id="memberDistribution" class="space-y-3">
                        <!-- isi akan diganti via JS -->
                    </div>
                </div>
            </div>

            <!-- Member Actions -->
            <!-- <div class="bg-white/80 backdrop-blur-md rounded-2xl shadow-xl border border-purple-100 p-6">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="bg-gradient-to-r from-purple-500 to-pink-600 p-2 rounded-lg">
                            <i class="fas fa-bolt text-white text-lg"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-800">Quick Actions</h3>
                    </div>

                    <div class="space-y-3">
                        <button onclick="showModal('modalSuspendMember')" class="w-full p-3 bg-gradient-to-r from-red-50 to-pink-50 hover:from-red-100 hover:to-pink-100 rounded-lg border border-red-100 transition-all duration-200 group">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="bg-red-100 p-2 rounded-lg group-hover:bg-red-200 transition-colors">
                                        <i class="fas fa-user-slash text-red-600 text-sm"></i>
                                    </div>
                                    <div class="text-left">
                                        <p class="font-semibold text-gray-800 text-sm">Suspend Member</p>
                                        <p class="text-xs text-gray-500">Nonaktifkan member bermasalah</p>
                                    </div>
                                </div>
                                <i class="fas fa-chevron-right text-gray-400 group-hover:text-gray-600 transition-colors"></i>
                            </div>
                        </button>

                        <button onclick="showModal('modalEmailBlast')" class="w-full p-3 bg-gradient-to-r from-blue-50 to-indigo-50 hover:from-blue-100 hover:to-indigo-100 rounded-lg border border-blue-100 transition-all duration-200 group">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="bg-blue-100 p-2 rounded-lg group-hover:bg-blue-200 transition-colors">
                                        <i class="fas fa-envelope text-blue-600 text-sm"></i>
                                    </div>
                                    <div class="text-left">
                                        <p class="font-semibold text-gray-800 text-sm">Email Blast</p>
                                        <p class="text-xs text-gray-500">Kirim promo ke semua member</p>
                                    </div>
                                </div>
                                <i class="fas fa-chevron-right text-gray-400 group-hover:text-gray-600 transition-colors"></i>
                            </div>
                        </button>

                        <button onclick="showModal('modalWhatsAppBlast')" class="w-full p-3 bg-gradient-to-r from-green-50 to-emerald-50 hover:from-green-100 hover:to-emerald-100 rounded-lg border border-green-100 transition-all duration-200 group">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="bg-green-100 p-2 rounded-lg group-hover:bg-green-200 transition-colors">
                                        <i class="fab fa-whatsapp text-green-600 text-sm"></i>
                                    </div>
                                    <div class="text-left">
                                        <p class="font-semibold text-gray-800 text-sm">WhatsApp Blast</p>
                                        <p class="text-xs text-gray-500">Reminder aktivasi member</p>
                                    </div>
                                </div>
                                <i class="fas fa-chevron-right text-gray-400 group-hover:text-gray-600 transition-colors"></i>
                            </div>
                        </button>

                        <button onclick="showModal('modalBulkActivation')" class="w-full p-3 bg-gradient-to-r from-purple-50 to-pink-50 hover:from-purple-100 hover:to-pink-100 rounded-lg border border-purple-100 transition-all duration-200 group">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="bg-purple-100 p-2 rounded-lg group-hover:bg-purple-200 transition-colors">
                                        <i class="fas fa-user-check text-purple-600 text-sm"></i>
                                    </div>
                                    <div class="text-left">
                                        <p class="font-semibold text-gray-800 text-sm">Bulk Activation</p>
                                        <p class="text-xs text-gray-500">Aktifkan member pending</p>
                                    </div>
                                </div>
                                <i class="fas fa-chevron-right text-gray-400 group-hover:text-gray-600 transition-colors"></i>
                            </div>
                        </button>

                        <button class="w-full p-3 bg-gradient-to-r from-orange-50 to-yellow-50 hover:from-orange-100 hover:to-yellow-100 rounded-lg border border-orange-100 transition-all duration-200 group">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="bg-orange-100 p-2 rounded-lg group-hover:bg-orange-200 transition-colors">
                                        <i class="fas fa-file-export text-orange-600 text-sm"></i>
                                    </div>
                                    <div class="text-left">
                                        <p class="font-semibold text-gray-800 text-sm">Export Data</p>
                                        <p class="text-xs text-gray-500">Download laporan member</p>
                                    </div>
                                </div>
                                <i class="fas fa-chevron-right text-gray-400 group-hover:text-gray-600 transition-colors"></i>
                            </div>
                        </button>
                    </div>
                </div> -->



            <!-- Member Monitoring -->
            <?php require_once __DIR__ . "/monitoring_member.php" ?>
        </div>
    </main>

    <!-- Modal Suspend Member -->
    <div id="modalSuspendMember"
        class="fixed inset-0 bg-black/50 flex justify-center items-center z-50 hidden backdrop-blur-sm transition-all duration-300">
        <div
            class="bg-white/95 backdrop-blur-md w-full max-w-2xl rounded-2xl shadow-2xl border border-red-100 relative animate-fade-in-up">
            <button onclick="closeModal('modalSuspendMember')"
                class="absolute top-4 right-4 text-gray-500 hover:text-red-500 text-2xl bg-white/80 rounded-full p-2 shadow-md transition-all duration-200 z-10">
                <i class="fas fa-times"></i>
            </button>

            <div class="p-6">
                <div class="flex items-center gap-3 mb-6">
                    <div class="bg-gradient-to-r from-red-500 to-pink-600 p-3 rounded-xl">
                        <i class="fas fa-user-slash text-white text-xl"></i>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold text-red-600">Suspend Member</h2>
                        <p class="text-gray-600">Nonaktifkan sementara akun member bermasalah</p>
                    </div>
                </div>

                <form class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Pilih Member</label>
                        <select
                            class="w-full px-4 py-3 border border-red-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-red-300">
                            <option value="">Pilih member yang akan disuspend...</option>
                            <option value="1">Ahmad Wijaya - 081234567890</option>
                            <option value="2">Budi Santoso - 081987654321</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Alasan Suspend</label>
                        <select
                            class="w-full px-4 py-3 border border-red-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-red-300">
                            <option value="">Pilih alasan...</option>
                            <option value="fraud">Aktivitas Mencurigakan/Fraud</option>
                            <option value="violation">Pelanggaran Aturan</option>
                            <option value="spam">Spam/Abuse</option>
                            <option value="request">Permintaan Member</option>
                            <option value="other">Lainnya</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Keterangan Tambahan</label>
                        <textarea rows="3"
                            class="w-full px-4 py-3 border border-red-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-red-300"
                            placeholder="Jelaskan detail alasan suspend..."></textarea>
                    </div>
                    <div class="flex gap-4 pt-4">
                        <button type="button" onclick="closeModal('modalSuspendMember')"
                            class="flex-1 px-6 py-3 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 transition-all duration-200">
                            Batal
                        </button>
                        <button type="submit"
                            class="flex-1 px-6 py-3 bg-gradient-to-r from-red-500 to-pink-600 text-white rounded-xl hover:from-red-600 hover:to-pink-700 transition-all duration-200">
                            <i class="fas fa-ban mr-2"></i>Suspend Member
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Email Blast -->
    <div id="modalEmailBlast"
        class="fixed inset-0 bg-black/50 flex justify-center items-center z-50 hidden backdrop-blur-sm transition-all duration-300">
        <div
            class="bg-white/95 backdrop-blur-md w-full max-w-4xl rounded-2xl shadow-2xl border border-blue-100 relative animate-fade-in-up max-h-[90vh] overflow-y-auto">
            <button onclick="closeModal('modalEmailBlast')"
                class="absolute top-4 right-4 text-gray-500 hover:text-red-500 text-2xl bg-white/80 rounded-full p-2 shadow-md transition-all duration-200 z-10">
                <i class="fas fa-times"></i>
            </button>

            <div class="p-6">
                <div class="flex items-center gap-3 mb-6">
                    <div class="bg-gradient-to-r from-blue-500 to-indigo-600 p-3 rounded-xl">
                        <i class="fas fa-envelope text-white text-xl"></i>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold text-blue-600">Email Blast Campaign</h2>
                        <p class="text-gray-600">Kirim email promo ke member terpilih</p>
                    </div>
                </div>

                <form class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Target Member</label>
                            <select
                                class="w-full px-4 py-3 border border-blue-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-300">
                                <option value="all">Semua Member Aktif (1,250 member)</option>
                                <option value="location">Berdasarkan Lokasi</option>
                                <option value="status">Berdasarkan Status</option>
                                <option value="custom">Custom Selection</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Template Email</label>
                            <select
                                class="w-full px-4 py-3 border border-blue-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-300">
                                <option value="">Pilih template...</option>
                                <option value="promo">Flash Sale - Baby Equipment</option>
                                <option value="welcome">Welcome New Member</option>
                                <option value="birthday">Happy Birthday</option>
                                <option value="reminder">Reminder Aktivasi</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Subject Email</label>
                        <input type="text"
                            class="w-full px-4 py-3 border border-blue-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-300"
                            placeholder="Flash Sale Baby Equipment - Diskon hingga 70%!" />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Konten Email</label>
                        <textarea rows="6"
                            class="w-full px-4 py-3 border border-blue-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-300"
                            placeholder="Tulis konten email di sini..."></textarea>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Jadwal Kirim</label>
                            <select
                                class="w-full px-4 py-3 border border-blue-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-300">
                                <option value="now">Kirim Sekarang</option>
                                <option value="schedule">Jadwalkan</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Estimasi Penerima</label>
                            <input type="text" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl"
                                value="1,250 member" readonly />
                        </div>
                    </div>

                    <div class="bg-blue-50 p-4 rounded-xl border border-blue-200">
                        <h3 class="font-semibold text-blue-800 mb-2">Preview Campaign</h3>
                        <div class="text-sm text-blue-700">
                            <p><strong>Target:</strong> Semua Member Aktif</p>
                            <p><strong>Estimasi Penerima:</strong> 1,250 email</p>
                            <p><strong>Estimasi Biaya:</strong> Rp 125.000 (Rp 100/email)</p>
                        </div>
                    </div>

                    <div class="flex gap-4 pt-4">
                        <button type="button" onclick="closeModal('modalEmailBlast')"
                            class="flex-1 px-6 py-3 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 transition-all duration-200">
                            Batal
                        </button>
                        <button type="button"
                            class="px-6 py-3 bg-orange-100 text-orange-700 rounded-xl hover:bg-orange-200 transition-all duration-200">
                            <i class="fas fa-eye mr-2"></i>Preview
                        </button>
                        <button type="submit"
                            class="flex-1 px-6 py-3 bg-gradient-to-r from-blue-500 to-indigo-600 text-white rounded-xl hover:from-blue-600 hover:to-indigo-700 transition-all duration-200">
                            <i class="fas fa-paper-plane mr-2"></i>Kirim Email Blast
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal WhatsApp Blast -->
    <div id="modalWhatsAppBlast"
        class="fixed inset-0 bg-black/50 flex justify-center items-center z-50 hidden backdrop-blur-sm transition-all duration-300">
        <div
            class="bg-white/95 backdrop-blur-md w-full max-w-3xl rounded-2xl shadow-2xl border border-green-100 relative animate-fade-in-up max-h-[90vh] overflow-y-auto">
            <button onclick="closeModal('modalWhatsAppBlast')"
                class="absolute top-4 right-4 text-gray-500 hover:text-red-500 text-2xl bg-white/80 rounded-full p-2 shadow-md transition-all duration-200 z-10">
                <i class="fas fa-times"></i>
            </button>

            <div class="p-6">
                <div class="flex items-center gap-3 mb-6">
                    <div class="bg-gradient-to-r from-green-500 to-emerald-600 p-3 rounded-xl">
                        <i class="fab fa-whatsapp text-white text-xl"></i>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold text-green-600">WhatsApp Blast Campaign</h2>
                        <p class="text-gray-600">Kirim reminder aktivasi via WhatsApp</p>
                    </div>
                </div>

                <form class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Target Member</label>
                            <select
                                class="w-full px-4 py-3 border border-green-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-300">
                                <option value="inactive">Member Tidak Aktif (189 member)</option>
                                <option value="pending">Member Pending (45 member)</option>
                                <option value="birthday">Ulang Tahun Bulan Ini (23 member)</option>
                                <option value="custom">Custom Selection</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Jenis Pesan</label>
                            <select
                                class="w-full px-4 py-3 border border-green-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-300">
                                <option value="activation">Reminder Aktivasi</option>
                                <option value="promo">Promo Spesial</option>
                                <option value="birthday">Ucapan Ulang Tahun</option>
                                <option value="thank">Terima Kasih</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Template Pesan</label>
                        <textarea rows="5"
                            class="w-full px-4 py-3 border border-green-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-300"
                            placeholder="Halo {{nama}}, 

Kami merindukanmu! 😊
Aktifkan kembali membership kamu di Asoka Baby Store dan dapatkan:
🎁 Poin bonus 100 poin
💝 Diskon 20% untuk pembelian pertama
⭐ Akses eksklusif ke promo member

Klik link ini untuk aktivasi: {{link_aktivasi}}

Salam,
Tim Asoka Baby Store"></textarea>
                    </div>

                    <div class="bg-green-50 p-4 rounded-xl border border-green-200">
                        <h3 class="font-semibold text-green-800 mb-2">WhatsApp Blast Info</h3>
                        <div class="text-sm text-green-700 space-y-1">
                            <p><strong>Target:</strong> Member Tidak Aktif</p>
                            <p><strong>Estimasi Penerima:</strong> 189 nomor WhatsApp</p>
                            <p><strong>Variabel Tersedia:</strong> {{nama}}, {{nomor_hp}}, {{cabang}}, {{link_aktivasi}}
                            </p>
                            <p><strong>Status API:</strong> <span class="text-green-600 font-semibold">Terhubung</span>
                            </p>
                        </div>
                    </div>

                    <div class="flex gap-4 pt-4">
                        <button type="button" onclick="closeModal('modalWhatsAppBlast')"
                            class="flex-1 px-6 py-3 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 transition-all duration-200">
                            Batal
                        </button>
                        <button type="button"
                            class="px-6 py-3 bg-yellow-100 text-yellow-700 rounded-xl hover:bg-yellow-200 transition-all duration-200">
                            <i class="fas fa-mobile-alt mr-2"></i>Test Kirim
                        </button>
                        <button type="submit"
                            class="flex-1 px-6 py-3 bg-gradient-to-r from-green-500 to-emerald-600 text-white rounded-xl hover:from-green-600 hover:to-emerald-700 transition-all duration-200">
                            <i class="fab fa-whatsapp mr-2"></i>Kirim WhatsApp Blast
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Bulk Activation -->
    <div id="modalBulkActivation"
        class="fixed inset-0 bg-black/50 flex justify-center items-center z-50 hidden backdrop-blur-sm transition-all duration-300">
        <div
            class="bg-white/95 backdrop-blur-md w-full max-w-3xl rounded-2xl shadow-2xl border border-purple-100 relative animate-fade-in-up">
            <button onclick="closeModal('modalBulkActivation')"
                class="absolute top-4 right-4 text-gray-500 hover:text-red-500 text-2xl bg-white/80 rounded-full p-2 shadow-md transition-all duration-200 z-10">
                <i class="fas fa-times"></i>
            </button>

            <div class="p-6">
                <div class="flex items-center gap-3 mb-6">
                    <div class="bg-gradient-to-r from-purple-500 to-pink-600 p-3 rounded-xl">
                        <i class="fas fa-user-check text-white text-xl"></i>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold text-purple-600">Bulk Member Activation</h2>
                        <p class="text-gray-600">Aktifkan beberapa member sekaligus</p>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="bg-purple-50 p-4 rounded-xl border border-purple-200">
                        <h3 class="font-semibold text-purple-800 mb-3">Member Pending (45 member)</h3>
                        <div class="max-h-64 overflow-y-auto space-y-2">
                            <label
                                class="flex items-center gap-3 p-3 bg-white rounded-lg border border-purple-100 hover:bg-purple-50 cursor-pointer">
                                <input type="checkbox" class="w-4 h-4 text-purple-600 rounded" />
                                <div class="flex-1">
                                    <p class="font-medium text-gray-800">Siti Nurhaliza</p>
                                    <p class="text-sm text-gray-500">081234567890 • Terdaftar: 2024-08-10</p>
                                </div>
                                <span
                                    class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded-full text-xs font-medium">Pending</span>
                            </label>
                            <label
                                class="flex items-center gap-3 p-3 bg-white rounded-lg border border-purple-100 hover:bg-purple-50 cursor-pointer">
                                <input type="checkbox" class="w-4 h-4 text-purple-600 rounded" />
                                <div class="flex-1">
                                    <p class="font-medium text-gray-800">Budi Santoso</p>
                                    <p class="text-sm text-gray-500">081987654321 • Terdaftar: 2024-08-12</p>
                                </div>
                                <span
                                    class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded-full text-xs font-medium">Pending</span>
                            </label>
                            <label
                                class="flex items-center gap-3 p-3 bg-white rounded-lg border border-purple-100 hover:bg-purple-50 cursor-pointer">
                                <input type="checkbox" class="w-4 h-4 text-purple-600 rounded" />
                                <div class="flex-1">
                                    <p class="font-medium text-gray-800">Dewi Lestari</p>
                                    <p class="text-sm text-gray-500">081555444333 • Terdaftar: 2024-08-13</p>
                                </div>
                                <span
                                    class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded-full text-xs font-medium">Pending</span>
                            </label>
                        </div>

                        <div class="mt-4 flex justify-between items-center">
                            <label class="flex items-center gap-2">
                                <input type="checkbox" class="w-4 h-4 text-purple-600 rounded" id="selectAll" />
                                <span class="text-sm font-medium text-gray-700">Pilih Semua (45 member)</span>
                            </label>
                            <span class="text-sm text-gray-500">0 member dipilih</span>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Poin Bonus Aktivasi</label>
                        <input type="number"
                            class="w-full px-4 py-3 border border-purple-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-300"
                            value="50" min="0" />
                        <p class="text-xs text-gray-500 mt-1">Poin yang akan diberikan saat aktivasi</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Kirim Notifikasi</label>
                        <div class="space-y-2">
                            <label class="flex items-center gap-2">
                                <input type="checkbox" class="w-4 h-4 text-purple-600 rounded" checked />
                                <span class="text-sm">Email welcome untuk member yang diaktifkan</span>
                            </label>
                            <label class="flex items-center gap-2">
                                <input type="checkbox" class="w-4 h-4 text-purple-600 rounded" checked />
                                <span class="text-sm">WhatsApp konfirmasi aktivasi</span>
                            </label>
                        </div>
                    </div>

                    <div class="flex gap-4 pt-4">
                        <button type="button" onclick="closeModal('modalBulkActivation')"
                            class="flex-1 px-6 py-3 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 transition-all duration-200">
                            Batal
                        </button>
                        <button type="submit"
                            class="flex-1 px-6 py-3 bg-gradient-to-r from-purple-500 to-pink-600 text-white rounded-xl hover:from-purple-600 hover:to-pink-700 transition-all duration-200">
                            <i class="fas fa-check-double mr-2"></i>Aktifkan Member Terpilih
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal Member Management -->
    <div id="modalMemberManagement"
        class="fixed inset-0 bg-black/50 z-50 hidden backdrop-blur-sm transition-all duration-300">
        <div class="bg-white/95 backdrop-blur-md w-full h-full relative flex flex-col">
            <!-- Modal Header -->
            <div class="bg-gradient-to-r from-blue-500 to-purple-600 p-6 sticky top-0 z-10 shadow-lg">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="bg-white/20 backdrop-blur-sm p-3 rounded-xl">
                            <i class="fas fa-users text-white text-xl"></i>
                        </div>
                        <div>
                            <h2 class="text-2xl font-bold text-white" id="memberModalTitle">Data Member</h2>
                            <p class="text-blue-100 text-sm" id="memberModalSubtitle"></p>
                        </div>
                    </div>
                    <button onclick="closeModal('modalMemberManagement')"
                        class="text-white/80 hover:text-white text-2xl bg-white/10 rounded-full p-3 hover:bg-white/20 transition-all duration-200">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <!-- Filters dan Actions -->
                <div class="flex flex-col lg:flex-row gap-4 mt-6">
                    <!-- Search Box -->
                    <div class="relative flex-1">
                        <input type="text" id="memberSearchInput" placeholder="Cari nama, email, atau nomor HP..."
                            class="w-full pl-12 pr-4 py-3 rounded-xl border border-white/20 focus:outline-none focus:ring-2 focus:ring-white/30 focus:border-white/30 transition-all duration-200 bg-white/10 backdrop-blur-sm text-white placeholder-blue-100" />
                        <i class="fas fa-search absolute left-4 top-1/2 transform -translate-y-1/2 text-blue-100"></i>
                    </div>

                    <!-- Filters -->
                    <div class="flex gap-2">
                        <select id="statusFilter"
                            class="px-4 py-3 rounded-xl border border-white/20 bg-white/10 backdrop-blur-sm text-white focus:outline-none focus:ring-2 focus:ring-white/30">
                            <option value="all" class="text-white bg-white/10">Semua Status</option>
                            <option value="Aktif" class="text-green-800 bg-green-100">Aktif</option>
                            <option value="Non-Aktif" class="text-red-800 bg-red-100">Non-Aktif</option>
                            <option value="Member Lama Non-Aktif" class="text-orange-800 bg-orange-100">Member Lama
                                Non-Aktif</option>
                        </select>

                        <!-- <select id="branchFilter" class="px-4 py-3 rounded-xl border border-white/20 bg-white/10 backdrop-blur-sm text-white focus:outline-none focus:ring-2 focus:ring-white/30">
                            <option value="all">Semua Cabang</option>
                            <option value="jakarta-pusat">Jakarta Pusat</option>
                            <option value="jakarta-selatan">Jakarta Selatan</option>
                            <option value="bandung">Bandung</option>
                            <option value="surabaya">Surabaya</option>
                        </select> -->

                        <!-- Date Range Filter -->
                        <!-- <div class="relative">
                            <button id="dateRangeButton" onclick="toggleDateRangeFilter()" class="px-4 py-3 bg-white/20 backdrop-blur-sm text-white rounded-xl hover:bg-white/30 transition-all duration-200 flex items-center gap-2">
                                <i class="fas fa-calendar-alt"></i>
                                <span class="hidden sm:inline">Range Waktu</span>
                            </button> -->

                        <!-- Date Range Dropdown -->
                        <!-- <div id="dateRangeDropdown" class="absolute top-full mt-2 right-0 bg-white rounded-xl shadow-2xl border border-gray-200 p-4 min-w-80 z-50 hidden">
                                <div class="mb-3">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Filter Berdasarkan:</label>
                                    <select id="dateFilterType" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-gray-700">
                                        <option value="join">Tanggal Bergabung</option>
                                        <option value="activity">Aktivitas Terakhir</option>
                                    </select>
                                </div>

                                <div class="grid grid-cols-2 gap-3 mb-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Dari:</label>
                                        <input type="date" id="dateFrom" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-gray-700">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Sampai:</label>
                                        <input type="date" id="dateTo" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-gray-700">
                                    </div>
                                </div> -->

                        <!-- Quick Date Presets -->
                        <!-- <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Preset Cepat:</label>
                                    <div class="grid grid-cols-2 gap-2">
                                        <button onclick="setDatePreset('today')" class="px-3 py-2 text-xs bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-all duration-200">Hari Ini</button>
                                        <button onclick="setDatePreset('week')" class="px-3 py-2 text-xs bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-all duration-200">7 Hari</button>
                                        <button onclick="setDatePreset('month')" class="px-3 py-2 text-xs bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-all duration-200">30 Hari</button>
                                        <button onclick="setDatePreset('year')" class="px-3 py-2 text-xs bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-all duration-200">1 Tahun</button>
                                    </div>
                                </div>

                                <div class="flex gap-2 justify-end">
                                    <button onclick="clearDateRange()" class="px-4 py-2 text-sm bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-all duration-200">Reset</button>
                                    <button onclick="applyDateRange()" class="px-4 py-2 text-sm bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-all duration-200">Terapkan</button>
                                </div>
                            </div>
                        </div> -->

                        <!-- Export Button -->
                        <button onclick="exportMemberData()"
                            class="px-4 py-3 bg-white/20 backdrop-blur-sm text-white rounded-xl hover:bg-white/30 transition-all duration-200 flex items-center gap-2">
                            <i class="fas fa-download"></i>
                            <span class="hidden sm:inline">Export</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Modal Content -->
            <div class="flex flex-1 min-h-0 overflow-hidden">
                <!-- Member List - Now narrower to give more space to detail panel -->
                <div class="flex-1 w-1/2 overflow-y-auto p-6 bg-white/50 custom-scrollbar">
                    <div id="memberListContainer" class="space-y-4">
                        <!-- Member items will be loaded dynamically from API -->
                    </div>

                    <!-- Pagination -->
                    <div class="mt-8 flex justify-center">
                        <div class="flex items-center gap-2 pagination-container">
                            <!-- Pagination will be dynamically generated -->
                        </div>
                    </div>
                </div>

                <!-- Member Detail Panel - Now wider and more prominent -->
                <div class="w-1/2 min-w-96 bg-gradient-to-br from-gray-50 to-white border-l border-gray-200 overflow-y-auto custom-scrollbar shadow-lg"
                    id="memberDetailPanel">

                </div>
            </div>
        </div>
    </div>


    <!-- Modal Activity Log -->
    <div id="modalActivityLog"
        class="fixed inset-0 bg-black/50 z-50 hidden backdrop-blur-sm transition-all duration-300">
        <div class="bg-white/95 backdrop-blur-md w-full h-full relative overflow-hidden">
            <!-- Modal Header -->
            <div class="bg-gradient-to-r from-indigo-500 to-purple-600 p-6 sticky top-0 z-10">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="bg-white/20 backdrop-blur-sm p-2 rounded-lg">
                            <i class="fas fa-history text-white text-xl"></i>
                        </div>
                        <div>
                            <h2 class="text-2xl font-bold text-white">Activity Log</h2>
                            <p class="text-indigo-100 text-sm">Riwayat aktivitas lengkap sistem member</p>
                        </div>
                    </div>
                    <button onclick="closeModal('modalActivityLog')"
                        class="text-white/80 hover:text-white text-2xl bg-white/10 rounded-full p-2 hover:bg-white/20 transition-all duration-200">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <!-- Filters dan Search -->
                <div class="flex flex-col lg:flex-row gap-4 mt-6">
                    <!-- Search Box -->
                    <div class="relative flex-1">
                        <input type="text" id="activitySearch" placeholder="Cari aktivitas, user, atau deskripsi..."
                            class="w-full pl-12 pr-4 py-3 rounded-xl border border-white/20 focus:outline-none focus:ring-2 focus:ring-white/30 focus:border-white/30 transition-all duration-200 bg-white/10 backdrop-blur-sm text-white placeholder-indigo-100" />
                        <i class="fas fa-search absolute left-4 top-1/2 transform -translate-y-1/2 text-indigo-100"></i>
                    </div>

                    <!-- Date Range Filter -->
                    <div class="flex gap-2">
                        <select id="dateRangeFilter"
                            class="px-4 py-3 rounded-xl border border-white/20 bg-white/10 backdrop-blur-sm text-white focus:outline-none focus:ring-2 focus:ring-white/30">
                            <option value="today">Hari Ini</option>
                            <option value="week" selected>7 Hari Terakhir</option>
                            <option value="month">30 Hari Terakhir</option>
                            <option value="all">Semua Waktu</option>
                        </select>

                        <!-- Activity Type Filter -->
                        <select id="activityTypeFilter"
                            class="px-4 py-3 rounded-xl border border-white/20 bg-white/10 backdrop-blur-sm text-white focus:outline-none focus:ring-2 focus:ring-white/30">
                            <option value="all">Semua Aktivitas</option>
                            <option value="member">Member Actions</option>
                            <option value="system">System Events</option>
                            <option value="email">Email Campaigns</option>
                            <option value="whatsapp">WhatsApp</option>
                            <option value="admin">Admin Actions</option>
                        </select>

                        <!-- Export Button -->
                        <button onclick="exportActivityLog()"
                            class="px-4 py-3 bg-white/20 backdrop-blur-sm text-white rounded-xl hover:bg-white/30 transition-all duration-200 flex items-center gap-2">
                            <i class="fas fa-download"></i>
                            <span class="hidden sm:inline">Export</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Modal Content -->
            <div class="flex h-full">
                <!-- Activity List -->
                <div class="flex-1 overflow-y-auto p-6 bg-white/50">
                    <div id="activityList" class="space-y-4">
                        <!-- Activity Items will be loaded here -->
                        <!-- Sample Activities -->
                        <div class="activity-item bg-white rounded-xl p-4 shadow-sm border border-gray-100 hover:shadow-md transition-all duration-200 cursor-pointer"
                            data-type="member" data-date="2025-08-16" onclick="showActivityDetail('act_001')">
                            <div class="flex items-start gap-4">
                                <div class="bg-green-100 p-2 rounded-full flex-shrink-0">
                                    <i class="fas fa-user-plus text-green-600 text-sm"></i>
                                </div>
                                <div class="flex-1">
                                    <div class="flex items-center justify-between mb-2">
                                        <h4 class="font-semibold text-gray-800">Member Baru Terdaftar</h4>
                                        <span class="text-xs text-gray-500">2 menit lalu</span>
                                    </div>
                                    <p class="text-sm text-gray-600 mb-2">
                                        <strong>Siti Nurhaliza</strong> (081234567890) bergabung melalui cabang Jakarta
                                        Pusat
                                    </p>
                                    <div class="flex gap-2">
                                        <span
                                            class="bg-green-100 text-green-700 px-2 py-1 rounded-full text-xs font-medium">
                                            New Registration
                                        </span>
                                        <span
                                            class="bg-blue-100 text-blue-700 px-2 py-1 rounded-full text-xs font-medium">
                                            Jakarta Pusat
                                        </span>
                                    </div>
                                </div>
                                <div class="text-gray-400">
                                    <i class="fas fa-chevron-right text-xs"></i>
                                </div>
                            </div>
                        </div>

                        <div class="activity-item bg-white rounded-xl p-4 shadow-sm border border-gray-100 hover:shadow-md transition-all duration-200 cursor-pointer"
                            data-type="email" data-date="2025-08-16" onclick="showActivityDetail('act_002')">
                            <div class="flex items-start gap-4">
                                <div class="bg-blue-100 p-2 rounded-full flex-shrink-0">
                                    <i class="fas fa-envelope text-blue-600 text-sm"></i>
                                </div>
                                <div class="flex-1">
                                    <div class="flex items-center justify-between mb-2">
                                        <h4 class="font-semibold text-gray-800">Email Blast Dikirim</h4>
                                        <span class="text-xs text-gray-500">15 menit lalu</span>
                                    </div>
                                    <p class="text-sm text-gray-600 mb-2">
                                        Promo "Flash Sale Baby Equipment" dikirim ke 1,250 member aktif
                                    </p>
                                    <div class="flex gap-2">
                                        <span
                                            class="bg-blue-100 text-blue-700 px-2 py-1 rounded-full text-xs font-medium">
                                            Email Campaign
                                        </span>
                                        <span
                                            class="bg-purple-100 text-purple-700 px-2 py-1 rounded-full text-xs font-medium">
                                            1,250 Recipients
                                        </span>
                                    </div>
                                </div>
                                <div class="text-gray-400">
                                    <i class="fas fa-chevron-right text-xs"></i>
                                </div>
                            </div>
                        </div>

                        <div class="activity-item bg-white rounded-xl p-4 shadow-sm border border-gray-100 hover:shadow-md transition-all duration-200 cursor-pointer"
                            data-type="admin" data-date="2025-08-16" onclick="showActivityDetail('act_003')">
                            <div class="flex items-start gap-4">
                                <div class="bg-orange-100 p-2 rounded-full flex-shrink-0">
                                    <i class="fas fa-user-slash text-orange-600 text-sm"></i>
                                </div>
                                <div class="flex-1">
                                    <div class="flex items-center justify-between mb-2">
                                        <h4 class="font-semibold text-gray-800">Member Disuspend</h4>
                                        <span class="text-xs text-gray-500">1 jam lalu</span>
                                    </div>
                                    <p class="text-sm text-gray-600 mb-2">
                                        <strong>Ahmad Wijaya</strong> (081987654321) disuspend karena aktivitas
                                        mencurigakan
                                    </p>
                                    <div class="flex gap-2">
                                        <span
                                            class="bg-red-100 text-red-700 px-2 py-1 rounded-full text-xs font-medium">
                                            Account Suspended
                                        </span>
                                        <span
                                            class="bg-orange-100 text-orange-700 px-2 py-1 rounded-full text-xs font-medium">
                                            Security Issue
                                        </span>
                                    </div>
                                </div>
                                <div class="text-gray-400">
                                    <i class="fas fa-chevron-right text-xs"></i>
                                </div>
                            </div>
                        </div>

                        <div class="activity-item bg-white rounded-xl p-4 shadow-sm border border-gray-100 hover:shadow-md transition-all duration-200 cursor-pointer"
                            data-type="whatsapp" data-date="2025-08-16" onclick="showActivityDetail('act_004')">
                            <div class="flex items-start gap-4">
                                <div class="bg-green-100 p-2 rounded-full flex-shrink-0">
                                    <i class="fab fa-whatsapp text-green-600 text-sm"></i>
                                </div>
                                <div class="flex-1">
                                    <div class="flex items-center justify-between mb-2">
                                        <h4 class="font-semibold text-gray-800">WhatsApp Reminder Berhasil</h4>
                                        <span class="text-xs text-gray-500">2 jam lalu</span>
                                    </div>
                                    <p class="text-sm text-gray-600 mb-2">
                                        Reminder aktivasi dikirim ke 89 member inactive, 23 member merespon positif
                                    </p>
                                    <div class="flex gap-2">
                                        <span
                                            class="bg-green-100 text-green-700 px-2 py-1 rounded-full text-xs font-medium">
                                            WhatsApp Blast
                                        </span>
                                        <span
                                            class="bg-teal-100 text-teal-700 px-2 py-1 rounded-full text-xs font-medium">
                                            25.8% Response Rate
                                        </span>
                                    </div>
                                </div>
                                <div class="text-gray-400">
                                    <i class="fas fa-chevron-right text-xs"></i>
                                </div>
                            </div>
                        </div>

                        <div class="activity-item bg-white rounded-xl p-4 shadow-sm border border-gray-100 hover:shadow-md transition-all duration-200 cursor-pointer"
                            data-type="system" data-date="2025-08-15" onclick="showActivityDetail('act_005')">
                            <div class="flex items-start gap-4">
                                <div class="bg-purple-100 p-2 rounded-full flex-shrink-0">
                                    <i class="fas fa-cog text-purple-600 text-sm"></i>
                                </div>
                                <div class="flex-1">
                                    <div class="flex items-center justify-between mb-2">
                                        <h4 class="font-semibold text-gray-800">Sistem Backup Otomatis</h4>
                                        <span class="text-xs text-gray-500">Kemarin 23:00</span>
                                    </div>
                                    <p class="text-sm text-gray-600 mb-2">
                                        Backup database member berhasil dilakukan secara otomatis (1.2GB)
                                    </p>
                                    <div class="flex gap-2">
                                        <span
                                            class="bg-purple-100 text-purple-700 px-2 py-1 rounded-full text-xs font-medium">
                                            System Event
                                        </span>
                                        <span
                                            class="bg-green-100 text-green-700 px-2 py-1 rounded-full text-xs font-medium">
                                            Success
                                        </span>
                                    </div>
                                </div>
                                <div class="text-gray-400">
                                    <i class="fas fa-chevron-right text-xs"></i>
                                </div>
                            </div>
                        </div>

                        <div class="activity-item bg-white rounded-xl p-4 shadow-sm border border-gray-100 hover:shadow-md transition-all duration-200 cursor-pointer"
                            data-type="member" data-date="2025-08-15" onclick="showActivityDetail('act_006')">
                            <div class="flex items-start gap-4">
                                <div class="bg-yellow-100 p-2 rounded-full flex-shrink-0">
                                    <i class="fas fa-user-edit text-yellow-600 text-sm"></i>
                                </div>
                                <div class="flex-1">
                                    <div class="flex items-center justify-between mb-2">
                                        <h4 class="font-semibold text-gray-800">Update Profile Member</h4>
                                        <span class="text-xs text-gray-500">Kemarin 16:30</span>
                                    </div>
                                    <p class="text-sm text-gray-600 mb-2">
                                        <strong>Rina Sari</strong> mengubah alamat dan nomor telepon di profil
                                    </p>
                                    <div class="flex gap-2">
                                        <span
                                            class="bg-yellow-100 text-yellow-700 px-2 py-1 rounded-full text-xs font-medium">
                                            Profile Update
                                        </span>
                                        <span
                                            class="bg-blue-100 text-blue-700 px-2 py-1 rounded-full text-xs font-medium">
                                            Self Service
                                        </span>
                                    </div>
                                </div>
                                <div class="text-gray-400">
                                    <i class="fas fa-chevron-right text-xs"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div class="mt-6 flex justify-center">
                        <div class="flex items-center gap-2">
                            <button
                                class="px-3 py-2 rounded-lg bg-white border border-gray-200 text-gray-600 hover:bg-gray-50 transition-all">
                                <i class="fas fa-chevron-left"></i>
                            </button>
                            <button class="px-3 py-2 rounded-lg bg-indigo-500 text-white">1</button>
                            <button
                                class="px-3 py-2 rounded-lg bg-white border border-gray-200 text-gray-600 hover:bg-gray-50 transition-all">2</button>
                            <button
                                class="px-3 py-2 rounded-lg bg-white border border-gray-200 text-gray-600 hover:bg-gray-50 transition-all">3</button>
                            <span class="px-3 py-2 text-gray-500">...</span>
                            <button
                                class="px-3 py-2 rounded-lg bg-white border border-gray-200 text-gray-600 hover:bg-gray-50 transition-all">10</button>
                            <button
                                class="px-3 py-2 rounded-lg bg-white border border-gray-200 text-gray-600 hover:bg-gray-50 transition-all">
                                <i class="fas fa-chevron-right"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Activity Detail Panel -->
                <div class="w-96 bg-gray-50 border-l border-gray-200 overflow-y-auto" id="activityDetailPanel">
                    <div class="p-6">
                        <div class="text-center text-gray-500 mt-20">
                            <i class="fas fa-mouse-pointer text-4xl mb-4 opacity-50"></i>
                            <p class="text-sm">Klik aktivitas untuk melihat detail</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="/src/js/member_internal/management/main.js" type="module"></script>
    <script>
        async function loadMemberDistribution() {
            const res = await fetch("/src/api/member/management/get_city_member");
            const json = await res.json();

            if (json.success) {
                const container = document.getElementById("memberDistribution");
                container.innerHTML = "";

                json.data.forEach((item, idx) => {
                    container.innerHTML += `
              <div class="flex items-center justify-between p-3 bg-gradient-to-r from-green-50 to-emerald-50 rounded-lg border border-green-100">
                  <div class="flex items-center gap-2">
                      <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                      <span class="text-sm font-medium text-gray-700">${item.kota}</span>
                  </div>
                  <div class="text-right">
                      <span class="text-lg font-bold text-green-700">${item.total}</span>
                      <p class="text-xs text-gray-500">${item.persen}%</p>
                  </div>
              </div>
            `;
                });
            }
        }

        loadMemberDistribution();
    </script>
</body>

</html>