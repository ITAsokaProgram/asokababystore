<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/all.min.css">
    <style>
        header {
            position: relative;
            z-index: 10;
        }

        #user-btn {
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            z-index: 20;
        }

        .sidebar a {
            transition: all 0.3s ease-in-out;
        }

        .sidebar a:hover {
            background-color: rgba(255, 255, 255, 0.1);
            padding-left: 1rem;
        }

        .sidebar {
            transition: transform 0.3s ease-in-out;
            width: 12.5rem;
            position: fixed;
            height: 100vh;
            left: 0;
            top: 0;
            padding: 0.8rem;
        }

        .hidden-sidebar {
            transform: translateX(-100%);
        }

        @media (max-width: 768px) {
            #sidebar {
                transform: translateX(-100%);
            }

            #sidebar.open {
                transform: translateX(0);
            }
        }


        .profile-card {
            position: absolute;
            right: 10px;
            top: 50px;
            background: white;
            padding: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 5px;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all 0.3s ease-in-out;
            z-index: 50;
            /* Supaya tampil di atas elemen lain */
        }

        .profile-card.show {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .hidden {
            display: none;
        }
    </style>
</head>

<body class="bg-gray-100">
    <!-- Header Navigation -->
    <header
        class="bg-gradient-to-r from-white to-pink-50 text-gray-800 py-3 px-5 flex justify-between items-center shadow-md">
        <a href="in_beranda.php" class="flex items-center">
            <img src="../images/logo.png" alt="Logo" class="h-10">
        </a>
        <div class="flex items-center gap-4 relative">
            <a href="#" id="profile-img">
                <img src="../images/pic-6.jpg" class="w-10 h-10 rounded-full object-cover border-2 border-white">
            </a>

            <div class="profile-card" id="profile-card">
                <div class="text-center p-3">
                    <img src="images/pic-1.jpg" class="rounded-full w-16 mx-auto" alt="User">
                    <h6 class="mt-2"> <?php echo htmlspecialchars($nama); ?> </h6>
                    <p class="text-gray-600"> <?php echo htmlspecialchars($hak); ?> </p>
                </div>
                <hr class="dropdown-divider">
                <a class="block p-2 hover:bg-gray-100 rounded" href="in_beranda.php">Lihat Profil</a>
                <a class="block p-2 text-red-600 hover:bg-gray-100 rounded" href="in_logout.php">Keluar</a>
            </div>
            <button id="toggle-sidebar" class="text-gray-800 text-2xl md:hidden cursor-pointer">
                <i class="fas fa-bars"></i>
            </button>
        </div>
    </header>

    <!-- Sidebar -->
    <div class="sidebar bg-white text-gray-200" id="sidebar">
        <nav class="ms-5 mt-20 text-gray-600">
            <a href="in_beranda.php"
                class="block py-2 pl-1 -ml-5 mt-5 mb-1 rounded hover:bg-black hover:text-pink-500 transition">
                <i class="fas fa-home mr-2"></i> Beranda
            </a>
            <a href="in_laporan.php"
                class="block py-2 pl-1 -ml-5 mb-1 rounded hover:bg-pink-300 hover:text-pink-500 transition">
                <i class="fa fa-book mr-2"></i> Laporan
            </a>
            <a href="in_new_user.php"
                class="block py-2 pl-1 -ml-5 mb-1 rounded hover:bg-pink-300 hover:text-pink-500 transition">
                <i class="fa fa-users mr-2"></i> Anggota
            </a>
            <a href="#" class="block py-2 pl-1 -ml-5 mb-1 rounded hover:bg-pink-300 hover:text-pink-500 transition">
                <i class="fas fa-headset mr-2"></i> Kontak
            </a>
            <a href="in_about.php"
                class="block py-2 pl-1 -ml-5 mb-1 rounded hover:bg-pink-300 hover:text-pink-500 transition">
                <i class="fas fa-question-circle mr-2"></i> Tentang
            </a>
        </nav>
    </div>

    <!-- Content -->
    <main class="md:ml-64 p-6">
        <h2 class="text-xl font-bold mb-5 text-base text-gray-700">Laporan Penjualan</h2>
        <form method="POST" class="grid gap-6 p-4 bg-white shadow-md rounded-lg">
            <!-- Grid untuk setiap input -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Cabang -->
                <div class="flex flex-col">
                    <label for="cabang" class="text-gray-700 font-medium">Cabang:</label>
                    <select id="cabang" name="cabang" class="w-auto p-3 border rounded transition">
                        <option value="ABIN">ABIN</option>
                        <option value="ACE">ACE</option>
                        <option value="ACIB">ACIB</option>
                        <option value="ACIL">ACIL</option>
                        <option value="ACIN">ACIN</option>
                        <option value="ACSA">ACSA</option>
                        <option value="ADET">ADET</option>
                        <option value="ADMB">ADMB</option>
                        <option value="AHA">AHA</option>
                        <option value="AHIN">AHIN</option>
                        <option value="ALANG">ALANG</option>
                        <option value="ANGIN">ANGIN</option>
                        <option value="APEN">APEN</option>
                        <option value="APIK">APIK</option>
                        <option value="APRS">APRS</option>
                        <option value="ARAW">ARAW</option>
                        <option value="ARUNG">ARUNG</option>
                        <option value="ASIH">ASIH</option>
                        <option value="ATIN">ATIN</option>
                        <option value="AWIT">AWIT</option>
                        <option value="AXY">AXY</option>
                    </select>
                </div>

                <!-- Tanggal Awal -->
                <div class="flex flex-col">
                    <label for="tanggal_awal" class="text-gray-700 font-medium">Tanggal Awal:</label>
                    <input type="date" id="tanggal_awal" name="tanggal_awal" class="w-auto p-3 border rounded">
                </div>

                <!-- Tanggal Hari -->
                <div class="flex flex-col">
                    <label for="tanggal_hari" class="text-gray-700 font-medium">Tanggal Hari:</label>
                    <input type="date" id="tanggal_hari" name="tanggal_hari" class="p-3 border rounded">
                </div>
            </div>

            <!-- Tombol Submit -->
            <div class="flex justify-end">
                <button type="submit"
                    class="bg-blue-500 hover:bg-rose-200 text-white font-bold py-2 px-3 rounded transition cursor-pointer">
                    Cek Data
                </button>
            </div>
        </form>
        <section class="container-fluid my-4 rounded-lg">
            <div class="row g-4">
                <!-- Diagram Pie -->
                <div class="col-12 col-md-6">
                    <div class="card shadow-sm">
                        <div class="card-header bg-primary text-black">
                            <h4 class="mb-0 text-center">Diagram Pie</h4>
                        </div>
                        <div class="card-body d-flex justify-content-center align-items-center">
                            <div id="chartDiagram" class="diagram"></div>
                        </div>
                    </div>
                </div>

                <!-- Diagram Bar -->
                <div class="col-12 col-md-6">
                    <div class="card shadow-sm">
                        <div class="card-header bg-primary text-black">
                            <h4 class="mb-0 text-center">Diagram Bar</h4>
                        </div>
                        <div class="card-body d-flex justify-content-center align-items-center">
                            <div id="barDiagram" class="diagram"></div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- Tabel Penjualan -->
        <div class="mt-6 bg-white shadow-md rounded-lg overflow-hidden">
            <div class="bg-gradient-to-r from-blue-500 to-blue-700 text-white p-4">
                <h3 class="text-lg font-semibold">Data Penjualan</h3>
            </div>

            <div class="overflow-auto p-4">
                <table class="min-w-full border border-gray-200 rounded-lg text-sm">
                    <thead class="bg-gray-100 text-gray-700 uppercase text-sm w-auto">
                        <tr>
                            <th class="py-3 px-4 border border-gray-300 text-left">TOP</th>
                            <th class="py-3 px-4 border border-gray-300 text-left">SUB DEPT</th>
                            <th class="py-3 px-4 border border-gray-300 text-left">QTY</th>
                            <th class="py-3 px-4 border border-gray-300 text-left">TOTAL</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-800">
                        <tr class="border border-gray-300 hover:bg-gray-100"></tr>
                        <tr class="border border-gray-300 hover:bg-gray-100"></tr>
                        <tr class="border border-gray-300 hover:bg-gray-100"></tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="flex justify-between items-center p-4 bg-gray-100">
                <button id="prevBtn"
                    class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 disabled:opacity-50">Prev</button>
                <span id="pageInfo" class="text-gray-600">Page 1</span>
                <button id="nextBtn" class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">Next</button>
            </div>
        </div>
    </main>
    <script src="https://unpkg.com/@tailwindcss/browser@4"></script>
    <script>
        document.getElementById('toggle-sidebar').addEventListener('click', function () {
            document.getElementById('sidebar').classList.toggle('open');
        });

        document.addEventListener("DOMContentLoaded", function () {
            const profileImg = document.getElementById("profile-img");
            const profileCard = document.getElementById("profile-card");

            profileImg.addEventListener("click", function (event) {
                event.preventDefault();
                profileCard.classList.toggle("show");
            });

            // Tutup profile-card jika klik di luar
            document.addEventListener("click", function (event) {
                if (!profileCard.contains(event.target) && !profileImg.contains(event.target)) {
                    profileCard.classList.remove("show");
                }
            });
        });

    </script>
</body>

</html>