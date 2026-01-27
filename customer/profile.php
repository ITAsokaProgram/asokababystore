<?php
require_once '../aa_kon_sett.php';
require_once '../src/auth/middleware_login.php';
require_once '../src/helpers/visitor_helper.php';

$user = getAuthenticatedUser();

logVisitor($conn, $user->id, "Customer Profile");

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Profile - Asoka Baby Store</title>

    <link rel="stylesheet" href="/src/output2.css">
    <link rel="icon" type="image/png" href="/images/logo1.png">
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        * {
            font-family: 'Poppins', sans-serif;
        }

        /* Custom Animations */
        @keyframes float {

            0%,
            100% {
                transform: translateY(0px);
            }

            50% {
                transform: translateY(-5px);
            }
        }

        .float-animation {
            animation: float 3s ease-in-out infinite;
        }

        /* Gradient Text */
        .gradient-text {
            background: linear-gradient(-45deg, #ec4899, #8b5cf6, #3b82f6, #ec4899);
            background-size: 400% 400%;
            animation: gradient-shift 3s ease infinite;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        @keyframes gradient-shift {

            0%,
            100% {
                background-position: 0% 50%;
            }

            50% {
                background-position: 100% 50%;
            }
        }

        /* Card Hover Effects */
        .card-hover-effect:hover {
            transform: translateY(-2px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }

        /* Glass Effect */
        .glass-effect {
            background: rgba(255, 255, 255, 0.25);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.18);
        }

        /* Custom Scrollbar */
        .scrollbar-thin::-webkit-scrollbar {
            width: 4px;
        }

        .scrollbar-thin::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 2px;
        }

        .scrollbar-thin::-webkit-scrollbar-thumb {
            background: #f472b6;
            border-radius: 2px;
        }

        .scrollbar-thin::-webkit-scrollbar-thumb:hover {
            background: #ec4899;
        }
    </style>
</head>

<body class="bg-gradient-to-br from-pink-50 via-white to-purple-50 min-h-screen text-gray-800">
    <div class="max-w-4xl mx-auto p-4 pb-32 space-y-6">

        <!-- Enhanced Back Button -->
        <div class="mb-4">
            <button onclick="history.back()"
                class="inline-flex items-center gap-3 px-4 py-2 bg-white/80 backdrop-blur-lg rounded-xl shadow-lg border border-pink-100 text-pink-600 hover:bg-pink-50 hover:scale-105 transition-all duration-300 group">
                <i class="fas fa-arrow-left group-hover:-translate-x-1 transition-transform duration-300"></i>
                <span class="font-medium">Kembali</span>
            </button>
        </div>

        <!-- Enhanced Profile Card -->
        <div class="bg-white/80 backdrop-blur-lg rounded-3xl shadow-xl border border-pink-100 overflow-hidden">
            <!-- Profile Header -->
            <div class="bg-gradient-to-r from-pink-500 to-purple-600 p-8 text-white relative overflow-hidden">
                <div class="absolute inset-0 bg-black/10"></div>
                <div class="relative z-10">
                    <div class="flex items-center gap-6">
                        <div
                            class="w-20 h-20 bg-white/20 backdrop-blur-sm rounded-full flex items-center justify-center float-animation">
                            <i class="fas fa-user-circle text-3xl"></i>
                        </div>
                        <div>
                            <h2 class="text-2xl font-bold mb-1">
                                <?= isset($user->nama) ? htmlspecialchars($user->nama) : "User" ?>
                            </h2>
                            <p id="status" class="text-pink-100 text-sm">Member Asoka Baby Store</p>
                            <div class="flex items-center gap-2 mt-2">
                                <div class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></div>
                                <span class="text-xs text-pink-100">Status Aktif</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Decorative Elements -->
                <div class="absolute top-4 right-4 w-16 h-16 bg-white/10 rounded-full"></div>
                <div class="absolute bottom-4 right-8 w-8 h-8 bg-white/10 rounded-full"></div>
            </div>

            <!-- Profile Information -->
            <div class="p-6 space-y-4">
                <!-- Email -->
                <div
                    class="flex items-center gap-4 p-4 bg-gray-50 rounded-2xl hover:bg-gray-100 transition-colors duration-300">
                    <div
                        class="w-10 h-10 bg-gradient-to-r from-blue-400 to-indigo-500 rounded-full flex items-center justify-center">
                        <i class="fas fa-envelope text-white text-sm"></i>
                    </div>
                    <div class="flex-1">
                        <p class="text-xs text-gray-500 font-medium">Email</p>
                        <p class="text-gray-800 font-semibold break-words" id="email">
                            <?= isset($user->email) ? htmlspecialchars($user->email) : "-" ?>
                        </p>
                    </div>
                </div>

                <!-- Phone Number -->
                <div class="flex items-center gap-4 p-4 bg-gray-50 rounded-2xl hover:bg-gray-100 transition-colors duration-300 cursor-pointer"
                    id="numberPhoneContainer">
                    <div class="w-10 h-10 bg-gradient-to-r from-green-400 to-emerald-500 rounded-full flex items-center justify-center">
                        <i class="fas fa-phone text-white text-sm"></i>
                    </div>
                    
                    <div class="flex-1">
                        <p class="text-xs text-gray-500 font-medium">No. HP</p>
                        <p class="text-gray-800 font-semibold" id="phoneValueText">-</p>
                    </div>

                    <i class="fas fa-chevron-right text-gray-400 text-sm" id="phoneActionIcon"></i>
                </div>

                <!-- Address -->
                <div
                    class="flex items-start gap-4 p-4 bg-gray-50 rounded-2xl hover:bg-gray-100 transition-colors duration-300">
                    <div
                        class="w-10 h-10 bg-gradient-to-r from-purple-400 to-pink-500 rounded-full flex items-center justify-center">
                        <i class="fas fa-home text-white text-sm"></i>
                    </div>
                    <div class="flex-1">
                        <p class="text-xs text-gray-500 font-medium">Alamat</p>
                        <p id="addres" class="text-gray-800 font-semibold whitespace-pre-line break-words">-</p>
                    </div>
                </div>

                <!-- Domisili -->
                <div
                    class="flex items-center gap-4 p-4 bg-gray-50 rounded-2xl hover:bg-gray-100 transition-colors duration-300">
                    <div
                        class="w-10 h-10 bg-gradient-to-r from-yellow-400 to-orange-500 rounded-full flex items-center justify-center">
                        <i class="fas fa-map-marker-alt text-white text-sm"></i>
                    </div>
                    <div class="flex-1">
                        <p class="text-xs text-gray-500 font-medium">Domisili</p>
                        <p id="alamatDomisili" class="text-gray-800 font-semibold">-</p>
                    </div>
                </div>

                <!-- Gender -->
                <div
                    class="flex items-center gap-4 p-4 bg-gray-50 rounded-2xl hover:bg-gray-100 transition-colors duration-300">
                    <div
                        class="w-10 h-10 bg-gradient-to-r from-pink-400 to-rose-500 rounded-full flex items-center justify-center">
                        <i class="fas fa-venus-mars text-white text-sm"></i>
                    </div>
                    <div class="flex-1">
                        <p class="text-xs text-gray-500 font-medium">Jenis Kelamin</p>
                        <p id="gender" class="text-gray-800 font-semibold">-</p>
                    </div>
                </div>

                <!-- Children Count -->
                <div
                    class="flex items-center gap-4 p-4 bg-gray-50 rounded-2xl hover:bg-gray-100 transition-colors duration-300">
                    <div
                        class="w-10 h-10 bg-gradient-to-r from-cyan-400 to-blue-500 rounded-full flex items-center justify-center">
                        <i class="fas fa-baby text-white text-sm"></i>
                    </div>
                    <div class="flex-1">
                        <p class="text-xs text-gray-500 font-medium">Jumlah Anak</p>
                        <p id="jumlahAnak" class="text-gray-800 font-semibold">-</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="space-y-4">
            <!-- Edit Profile Button -->
            <button
                class="w-full bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700 text-white font-semibold py-4 px-6 rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 hover:scale-105 flex items-center justify-center gap-3"
                id="sendEdit">
                <i class="fas fa-edit text-lg"></i>
                <span>Isi Data Diri</span>
            </button>
            <button
                class="w-full bg-gradient-to-r from-purple-500 to-blue-600 hover:from-blue-600 hover:to-indigo-700 text-white font-semibold py-4 px-6 rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 hover:scale-105 flex items-center justify-center gap-3"
                onclick="window.location.href='./change_email.php'">
                <i class="fas fa-envelope text-white text-lg"></i>
                <span>Ganti Email</span>
            </button>

            <!-- <button class="w-full bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700 text-white font-semibold py-4 px-6 rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 hover:scale-105 flex items-center justify-center gap-3" id="sendEdit">
                <i class="fas fa-edit text-lg"></i>
                <span>Tambah Password</span>
            </button> -->

            <!-- Logout Button -->
            <button
                class="w-full bg-gradient-to-r from-pink-500 to-rose-600 hover:from-pink-600 hover:to-rose-700 text-white font-semibold py-4 px-6 rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 hover:scale-105 flex items-center justify-center gap-3"
                id="logout">
                <i class="fas fa-sign-out-alt text-lg"></i>
                <span>Logout</span>
            </button>
        </div>

        <!-- Edit Note -->
        <div id="editNote" class="hidden bg-yellow-50 border border-yellow-200 rounded-2xl p-4">
            <div class="flex items-start gap-3">
                <i class="fas fa-exclamation-triangle text-yellow-500 text-lg mt-0.5"></i>
                <div>
                    <p class="text-yellow-800 font-medium text-sm">Profil hanya dapat diedit oleh member terdaftar di
                        Asoka Baby Store.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Enhanced Modal Input No. HP -->
    <div id="modalInputNoHp"
        class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center hidden">
        <div
            class="bg-white/95 backdrop-blur-lg rounded-3xl shadow-2xl max-w-md w-full mx-4 p-6 space-y-4 border border-pink-100">
            <div class="text-center mb-4">
                <div
                    class="w-16 h-16 bg-gradient-to-r from-green-400 to-emerald-500 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-phone text-white text-2xl"></i>
                </div>
                <h2 class="text-xl font-bold text-gray-800 mb-2">Masukkan Nomor HP</h2>
                <p class="text-sm text-gray-500">Pastikan nomor HP Anda sesuai yang didaftarkan sebagai member</p>
            </div>

            <div class="relative">
                <i class="fas fa-phone absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                <input type="tel" id="inputNoHp" placeholder="08xxx"
                    class="w-full border-2 border-gray-200 rounded-xl pl-10 pr-4 py-3 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all duration-300" />
            </div>

            <p id="errorNoHp" class="text-sm text-red-500 hidden"></p>

            <div class="flex gap-3 pt-4">
                <button id="btnBatalNoHp"
                    class="flex-1 px-4 py-3 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold transition-all duration-300 hover:scale-105">Batal</button>
                <button id="btnSimpanNoHp"
                    class="flex-1 px-4 py-3 rounded-xl bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white font-semibold transition-all duration-300 hover:scale-105 shadow-lg">Simpan</button>
            </div>
        </div>
    </div>

    <!-- Enhanced Profile Update Modal -->
    <form method="POST" id="data_cust">
        <div id="modalMemberProfile"
            class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 hidden">
            <div id="modalContentProfile"
                class="transition duration-300 ease-out bg-white/95 backdrop-blur-lg w-11/12 max-w-4xl p-6 rounded-3xl shadow-2xl opacity-0 scale-90 overflow-y-auto max-h-[90vh] border border-pink-100">

                <!-- Modal Header -->
                <div class="text-center mb-6">
                    <div
                        class="w-16 h-16 bg-gradient-to-r from-pink-500 to-purple-600 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-user-edit text-white text-2xl"></i>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-800 mb-2">Update Profile Member</h2>
                    <p class="text-gray-600">Silakan isi data diri Anda dengan lengkap</p>
                </div>

                <!-- Warning Note -->
                <div class="bg-gradient-to-r from-yellow-50 to-orange-50 border border-yellow-200 rounded-2xl p-4 mb-6">
                    <div class="flex items-start gap-3">
                        <i class="fas fa-info-circle text-yellow-500 text-lg mt-0.5"></i>
                        <div>
                            <p class="font-semibold text-yellow-800 mb-1">📝 Catatan Penting:</p>
                            <p class="text-yellow-700 text-sm">Pilih provinsi terlebih dahulu agar data wilayah lain
                                tampil. Pilih secara berurutan ya, terima kasih 🙏</p>
                        </div>
                    </div>
                </div>

                <!-- Form Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Member Code -->
                    <div class="col-span-2">
                        <label for="memberKode" class="block text-sm font-semibold mb-2 text-gray-700">Kode Member / No
                            HP</label>
                        <div class="relative">
                            <i class="fas fa-user absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                            <input type="text" required id="memberKode" name="kode_member" pattern="[0-9]"
                                maxlength="13" placeholder="Contoh: 08123456789" readonly
                                class="w-full border-2 border-gray-200 rounded-xl pl-10 pr-4 py-3 bg-gray-50 focus:outline-none transition-all duration-300" />
                        </div>
                    </div>

                    <!-- Full Name -->
                    <div class="col-span-2">
                        <label for="nama_lengkap" class="block text-sm font-semibold mb-2 text-gray-700">Nama
                            Lengkap</label>
                        <div class="relative">
                            <i
                                class="fas fa-user-circle absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                            <input type="text" required id="nama_lengkap" name="nama_lengkap" pattern="[a-zA-Z\s]+"
                                maxlength="50" placeholder="Nama sesuai KTP"
                                class="w-full border-2 border-gray-200 rounded-xl pl-10 pr-4 py-3 focus:outline-none focus:ring-2 focus:ring-pink-500 focus:border-transparent transition-all duration-300" />
                        </div>
                    </div>

                    <!-- KTP Address -->
                    <div class="col-span-2">
                        <label for="alamat_ktp" class="block text-sm font-semibold mb-2 text-gray-700">Alamat
                            KTP</label>
                        <div class="relative">
                            <i class="fas fa-home absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                            <input type="text" required id="alamat_ktp" name="alamat_ktp"
                                placeholder="Alamat sesuai KTP"
                                class="w-full border-2 border-gray-200 rounded-xl pl-10 pr-4 py-3 focus:outline-none focus:ring-2 focus:ring-pink-500 focus:border-transparent transition-all duration-300" />
                        </div>
                    </div>

                    <!-- Province -->
                    <div class="col-span-2">
                        <label for="provinsi" class="block text-sm font-semibold mb-2 text-gray-700">Provinsi</label>
                        <div class="relative">
                            <i class="fas fa-map absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                            <select required id="provinsi" name="provinsi"
                                class="w-full border-2 border-gray-200 rounded-xl pl-10 pr-4 py-3 focus:outline-none focus:ring-2 focus:ring-pink-500 focus:border-transparent transition-all duration-300 appearance-none bg-white">
                                <option value="">Pilih Provinsi</option>
                            </select>
                            <i
                                class="fas fa-chevron-down absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 pointer-events-none"></i>
                        </div>
                    </div>

                    <!-- City -->
                    <div class="col-span-2">
                        <label for="kota" class="block text-sm font-semibold mb-2 text-gray-700">Kota /
                            Kabupaten</label>
                        <div class="relative">
                            <i class="fas fa-city absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                            <select required id="kota" name="kota"
                                class="w-full border-2 border-gray-200 rounded-xl pl-10 pr-4 py-3 focus:outline-none focus:ring-2 focus:ring-pink-500 focus:border-transparent transition-all duration-300 appearance-none bg-white">
                                <option value="">Pilih Kota/Kabupaten</option>
                            </select>
                            <i
                                class="fas fa-chevron-down absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 pointer-events-none"></i>
                        </div>
                    </div>

                    <!-- District -->
                    <div class="col-span-2">
                        <label for="kec" class="block text-sm font-semibold mb-2 text-gray-700">Kecamatan</label>
                        <div class="relative">
                            <i
                                class="fas fa-map-marker-alt absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                            <select required id="kec" name="kec"
                                class="w-full border-2 border-gray-200 rounded-xl pl-10 pr-4 py-3 focus:outline-none focus:ring-2 focus:ring-pink-500 focus:border-transparent transition-all duration-300 appearance-none bg-white">
                                <option value="">Pilih Kecamatan</option>
                            </select>
                            <i
                                class="fas fa-chevron-down absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 pointer-events-none"></i>
                        </div>
                    </div>

                    <!-- Village -->
                    <div class="col-span-2">
                        <label for="kel" class="block text-sm font-semibold mb-2 text-gray-700">Kelurahan</label>
                        <div class="relative">
                            <i
                                class="fas fa-map-pin absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                            <select required id="kel" name="kel"
                                class="w-full border-2 border-gray-200 rounded-xl pl-10 pr-4 py-3 focus:outline-none focus:ring-2 focus:ring-pink-500 focus:border-transparent transition-all duration-300 appearance-none bg-white">
                                <option value="">Pilih Kelurahan</option>
                            </select>
                            <i
                                class="fas fa-chevron-down absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 pointer-events-none"></i>
                        </div>
                    </div>

                    <!-- Same Address Checkbox -->
                    <div id="checkbox-container"
                        class="flex items-center gap-3 col-span-2 p-4 bg-blue-50 rounded-2xl border border-blue-200">
                        <input type="checkbox" id="sesuai" class="w-5 h-5 text-blue-600 rounded focus:ring-blue-500" />
                        <label for="sesuai" class="text-sm text-blue-800 font-medium">Alamat Domisili sama dengan
                            KTP</label>
                    </div>

                    <!-- Domisili Address -->
                    <div class="col-span-2">
                        <label for="alamat_domisili" class="block text-sm font-semibold mb-2 text-gray-700">Alamat
                            Domisili</label>
                        <div class="relative">
                            <i class="fas fa-home absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                            <input type="text" name="alamat_domisili" required placeholder="Alamat Domisili"
                                id="alamat_domisili"
                                class="w-full border-2 border-gray-200 rounded-xl pl-10 pr-4 py-3 focus:outline-none focus:ring-2 focus:ring-pink-500 focus:border-transparent transition-all duration-300" />
                        </div>
                    </div>

                    <!-- Domisili Province -->
                    <div class="col-span-2">
                        <label for="provinsi_domisili" class="block text-sm font-semibold mb-2 text-gray-700">Provinsi
                            Domisili</label>
                        <div class="relative">
                            <i class="fas fa-map absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                            <select type="text" name="provinsi_domisili" required id="provinsi_domisili"
                                class="w-full border-2 border-gray-200 rounded-xl pl-10 pr-4 py-3 focus:outline-none focus:ring-2 focus:ring-pink-500 focus:border-transparent transition-all duration-300 appearance-none bg-white">
                                <option value="">Provinsi Domisili</option>
                            </select>
                            <i
                                class="fas fa-chevron-down absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 pointer-events-none"></i>
                        </div>
                    </div>

                    <!-- Domisili City -->
                    <div class="col-span-2">
                        <label for="kota_domisili" class="block text-sm font-semibold mb-2 text-gray-700">Kab/Kota
                            Domisili</label>
                        <div class="relative">
                            <i class="fas fa-city absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                            <select type="text" required id="kota_domisili" name="kota_domisili"
                                class="w-full border-2 border-gray-200 rounded-xl pl-10 pr-4 py-3 focus:outline-none focus:ring-2 focus:ring-pink-500 focus:border-transparent transition-all duration-300 appearance-none bg-white">
                                <option value="">Kota Domisili</option>
                            </select>
                            <i
                                class="fas fa-chevron-down absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 pointer-events-none"></i>
                        </div>
                    </div>

                    <!-- Domisili District -->
                    <div class="col-span-2">
                        <label for="kecamatan_domisili" class="block text-sm font-semibold mb-2 text-gray-700">Kecamatan
                            Domisili</label>
                        <div class="relative">
                            <i
                                class="fas fa-map-marker-alt absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                            <select type="text" required id="kecamatan_domisili" name="kec_domisili"
                                class="w-full border-2 border-gray-200 rounded-xl pl-10 pr-4 py-3 focus:outline-none focus:ring-2 focus:ring-pink-500 focus:border-transparent transition-all duration-300 appearance-none bg-white">
                                <option value="">Kecamatan Domisili</option>
                            </select>
                            <i
                                class="fas fa-chevron-down absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 pointer-events-none"></i>
                        </div>
                    </div>

                    <!-- Domisili Village -->
                    <div class="col-span-2">
                        <label for="kelurahan_domisili" class="block text-sm font-semibold mb-2 text-gray-700">Kelurahan
                            Domisili</label>
                        <div class="relative">
                            <i
                                class="fas fa-map-pin absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                            <select type="text" required id="kelurahan_domisili" name="kel_domisili"
                                class="w-full border-2 border-gray-200 rounded-xl pl-10 pr-4 py-3 focus:outline-none focus:ring-2 focus:ring-pink-500 focus:border-transparent transition-all duration-300 appearance-none bg-white">
                                <option value="">Kelurahan Domisili</option>
                            </select>
                            <i
                                class="fas fa-chevron-down absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 pointer-events-none"></i>
                        </div>
                    </div>

                    <!-- NIK -->
                    <div class="col-span-2">
                        <label for="no_nik" class="block text-sm font-semibold mb-2 text-gray-700">Nomor NIK</label>
                        <div class="relative">
                            <i
                                class="fas fa-id-card absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                            <input type="number" id="no_nik" name="nik" pattern="[0-9]+"
                                placeholder="NIK KTP (Opsional)" onchange="validateNIK()" maxlength="16"
                                class="w-full border-2 border-gray-200 rounded-xl pl-10 pr-4 py-3 focus:outline-none focus:ring-2 focus:ring-pink-500 focus:border-transparent transition-all duration-300" />
                        </div>
                        <p id="nik-error" class="mt-2 text-red-600 text-sm hidden flex items-center gap-2">
                            <i class="fas fa-exclamation-circle"></i>
                            NIK Tidak Valid (Harus 16 digit)
                        </p>
                    </div>

                    <div class="col-span-2">
                        <label for="member-email" class="block text-sm font-semibold mb-2 text-gray-700">Email</label>
                        <div class="relative">
                            <i
                                class="fas fa-envelope absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                            <input type="email" readonly required id="member-email" name="email"
                                placeholder="Email Aktif" autocomplete="on"
                                class="w-full border-2 border-gray-200 rounded-xl pl-10 pr-4 py-3 focus:outline-none focus:ring-2 focus:ring-pink-500 focus:border-transparent transition-all duration-300"
                                value="<?= isset($user->email) ? htmlspecialchars($user->email) : "-" ?>" />
                        </div>
                    </div>

                    <div class="col-span-2">
                        <label for="tanggal_lahir" class="block text-sm font-semibold mb-2 text-gray-700">Tanggal
                            Lahir</label>
                        <div class="relative">
                            <i
                                class="fas fa-calendar absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                            <input type="date" required id="tanggal_lahir" name="tanggal_lahir"
                                class="w-full border-2 border-gray-200 rounded-xl pl-10 pr-4 py-3 focus:outline-none focus:ring-2 focus:ring-pink-500 focus:border-transparent transition-all duration-300" />
                        </div>
                    </div>

                    <div class="col-span-2">
                        <label for="jenis_kelamin" class="block text-sm font-semibold mb-2 text-gray-700">Jenis
                            Kelamin</label>
                        <div class="relative">
                            <i
                                class="fas fa-venus-mars absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                            <select id="jenis_kelamin" name="jenis_kelamin" required
                                class="w-full border-2 border-gray-200 rounded-xl pl-10 pr-4 py-3 focus:outline-none focus:ring-2 focus:ring-pink-500 focus:border-transparent transition-all duration-300 appearance-none bg-white">
                                <option value="">Pilih Jenis Kelamin</option>
                                <option value="Laki-Laki">Laki-Laki</option>
                                <option value="Perempuan">Perempuan</option>
                            </select>
                            <i
                                class="fas fa-chevron-down absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 pointer-events-none"></i>
                        </div>
                    </div>

                    <div class="col-span-2">
                        <label for="jumlah_anak" class="block text-sm font-semibold mb-2 text-gray-700">Jumlah
                            Anak</label>
                        <div class="relative">
                            <i class="fas fa-baby absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                            <select id="jumlah_anak" name="jumlah_anak" required
                                class="w-full border-2 border-gray-200 rounded-xl pl-10 pr-4 py-3 focus:outline-none focus:ring-2 focus:ring-pink-500 focus:border-transparent transition-all duration-300 appearance-none bg-white">
                                <option value="0">0</option>
                                <option value="1">1</option>
                                <option value="2">2</option>
                                <option value="3">3</option>
                                <option value="4">4</option>
                                <option value="5">5</option>
                                <option value="6">6</option>
                                <option value="7">7</option>
                            </select>
                            <i
                                class="fas fa-chevron-down absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 pointer-events-none"></i>
                        </div>
                    </div>

                    <!-- Terms Checkbox -->
                    <div class="flex items-center gap-3 col-span-2 p-4 bg-pink-50 rounded-2xl border border-pink-200">
                        <input type="checkbox" required id="syarat"
                            class="w-5 h-5 text-pink-600 rounded focus:ring-pink-500" />
                        <label for="syarat" class="text-sm text-pink-800 font-medium">Saya setuju dengan Syarat dan
                            Ketentuan</label>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex gap-3 pt-6">
                    <button id="closeModalProfile"
                        class="flex-1 px-6 py-3 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold transition-all duration-300 hover:scale-105">Tutup</button>
                    <button type="submit" id="send_data"
                        class="flex-1 px-6 py-3 rounded-xl bg-gradient-to-r from-pink-500 to-purple-600 hover:from-pink-600 hover:to-purple-700 text-white font-semibold transition-all duration-300 hover:scale-105 shadow-lg">
                        <i class="fas fa-save mr-2"></i>Submit
                    </button>
                </div>
            </div>
        </div>
    </form>

    <!-- Enhanced Terms Modal -->
    <div id="modalTerms"
        class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 hidden px-2">
        <div id="modalContentTerms"
            class="bg-white/95 backdrop-blur-lg w-full max-w-md max-h-[90vh] p-6 rounded-3xl shadow-2xl overflow-y-auto transition duration-300 ease-out opacity-0 scale-90 border border-pink-100">
            <div class="text-center mb-6">
                <div
                    class="w-16 h-16 bg-gradient-to-r from-pink-500 to-purple-600 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-file-contract text-white text-2xl"></i>
                </div>
                <h2 class="text-xl font-bold text-gray-800 mb-2">Syarat dan Ketentuan Member Asoka</h2>
                <p class="text-gray-600 text-sm">Dengan mendaftar sebagai member ASOKA, Anda dianggap telah membaca,
                    memahami, dan menyetujui syarat dan ketentuan berikut:</p>
            </div>

            <div class="space-y-4 text-sm text-gray-700">
                <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
                    <h3 class="font-semibold text-blue-800 mb-2">💰 Biaya Pendaftaran</h3>
                    <ul class="list-disc ml-5 text-blue-700 space-y-1">
                        <li>Biaya pendaftaran sebesar Rp 10.000,-</li>
                        <li>Gratis jika belanja minimum Rp 300.000,-</li>
                    </ul>
                </div>

                <div class="bg-green-50 border border-green-200 rounded-xl p-4">
                    <h3 class="font-semibold text-green-800 mb-2">🎁 Hak dan Keuntungan Member</h3>
                    <ul class="list-disc ml-5 text-green-700 space-y-1">
                        <li>Diskon produk tertentu</li>
                        <li>1 poin untuk setiap Rp 100.000,- (produk tertentu)</li>
                        <li>Poin bisa ditukar hadiah menarik</li>
                        <li>Dapat ikut program eksklusif</li>
                    </ul>
                </div>

                <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4">
                    <h3 class="font-semibold text-yellow-800 mb-2">📱 Nomor Telepon Terdaftar</h3>
                    <ul class="list-disc ml-5 text-yellow-700 space-y-1">
                        <li>Menjadi Nomor Member dan tidak bisa diubah</li>
                        <li>Berlaku hanya untuk transaksi di toko ASOKA</li>
                        <li>Nomor harus aktif untuk menjaga status member</li>
                    </ul>
                </div>

                <div class="bg-purple-50 border border-purple-200 rounded-xl p-4">
                    <h3 class="font-semibold text-purple-800 mb-2">⏰ Kebijakan Poin Member</h3>
                    <ul class="list-disc ml-5 text-purple-700 space-y-1">
                        <li>Poin harus ditukar sebelum akhir Juni (Periode 1) atau akhir Desember (Periode 2)</li>
                        <li>Poin yang lewat batas hangus</li>
                    </ul>
                </div>

                <div class="bg-red-50 border border-red-200 rounded-xl p-4">
                    <h3 class="font-semibold text-red-800 mb-2">🔒 Tanggung Jawab Member</h3>
                    <ul class="list-disc ml-5 text-red-700 space-y-1">
                        <li>Menjaga kerahasiaan nomor dan data keanggotaan</li>
                        <li>ASOKA tidak bertanggung jawab atas kelalaian member</li>
                    </ul>
                </div>

                <div class="bg-indigo-50 border border-indigo-200 rounded-xl p-4">
                    <h3 class="font-semibold text-indigo-800 mb-2">🛡️ Privasi dan Data</h3>
                    <ul class="list-disc ml-5 text-indigo-700 space-y-1">
                        <li>ASOKA menjaga data member</li>
                        <li>Data tidak dibagikan tanpa izin, kecuali oleh hukum</li>
                    </ul>
                </div>

                <div class="bg-gray-50 border border-gray-200 rounded-xl p-4">
                    <h3 class="font-semibold text-gray-800 mb-2">📝 Perubahan Syarat dan Ketentuan</h3>
                    <ul class="list-disc ml-5 text-gray-700 space-y-1">
                        <li>ASOKA berhak mengubah ketentuan tanpa pemberitahuan</li>
                    </ul>
                </div>
            </div>

            <p class="text-sm font-medium text-gray-700 mt-6 p-4 bg-pink-50 rounded-xl border border-pink-200">
                Dengan ini, saya bermaksud untuk mendaftarkan diri sebagai member ASOKA dan menyatakan telah membaca
                serta memahami syarat dan ketentuan yang berlaku.
            </p>

            <div class="flex gap-3 pt-6">
                <button id="tidak-setuju"
                    class="flex-1 px-4 py-3 rounded-xl bg-red-500 hover:bg-red-600 text-white font-semibold transition-all duration-300 hover:scale-105">Tidak
                    Setuju</button>
                <button id="setuju"
                    class="flex-1 px-4 py-3 rounded-xl bg-green-500 hover:bg-green-600 text-white font-semibold transition-all duration-300 hover:scale-105">Setuju</button>
            </div>
        </div>
    </div>


    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
    <script src="/../src/js/send_info_cust.js"></script>
    <script src="/../src/js/customer_pubs/profile_pubs.js" type="module"></script>
</body>

</html>