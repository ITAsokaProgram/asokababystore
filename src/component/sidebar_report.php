<div id="sidebar"
    class="bg-white text-gray-700 w-64 min-h-screen p-6 fixed left-0 top-0 transition-all duration-300 shadow-2xl border-r border-blue-200 z-40">
    <button id="closeSidebar" class="absolute top-2 right-4 text-gray-600 hover:text-gray-800 text-2xl font-bold transition-colors duration-200 hover:scale-110">&times;</button>
    <!-- Navigation -->
    <nav class="text-sm mt-20 space-y-2">
        
        <!-- Beranda Link -->
        <a href="/in_beranda" id="berandaLink" data-menu="dashboard"
            class="group flex items-center py-3 px-4 rounded-xl hover:bg-gradient-to-r hover:from-blue-100 hover:to-blue-200 hover:text-blue-700 hover:shadow-lg transition-all duration-300 mb-4 border border-transparent hover:border-blue-300">
            <div class="w-8 flex justify-center">
                <i class="fas fa-home text-xl text-blue-600 group-hover:text-blue-700 transition-all duration-300 group-hover:scale-125 group-hover:-rotate-12 group-hover:drop-shadow-lg"></i>
            </div>
            <span class="sidebar-text ml-3 font-medium transition-all duration-300 group-hover:translate-x-1">Beranda</span>
        </a>
        <a href="/src/fitur/shopee/dashboard_shopee" id="shopeeLink" data-menu="shopee_dashboard"
            class="group flex items-center py-3 px-4 rounded-xl hover:bg-gradient-to-r hover:from-orange-100 hover:to-red-200 hover:text-orange-700 hover:shadow-lg transition-all duration-300 mb-4 border border-transparent hover:border-orange-300">
            <div class="w-8 flex justify-center">
                <i class="fas fa-shopping-bag text-xl text-orange-600 group-hover:text-orange-700 transition-all duration-300 group-hover:scale-125 group-hover:-rotate-12 group-hover:drop-shadow-lg"></i>
            </div>
            <span class="sidebar-text ml-3 font-medium transition-all duration-300 group-hover:translate-x-1">Shopee</span>
        </a>
        <!-- <a href="/src/fitur/whatsapp_cs/dashboard_whatsapp" id="whatsappLink" data-menu="whatsapp_dashboard"
            class="group flex items-center py-3 px-4 rounded-xl hover:bg-gradient-to-r hover:from-orange-100 hover:to-red-200 hover:text-orange-700 hover:shadow-lg transition-all duration-300 mb-4 border border-transparent hover:border-orange-300">
            <div class="w-8 flex justify-center">
                <i class="fa-brands fa-whatsapp  text-xl text-orange-600 group-hover:text-orange-700 transition-all duration-300 group-hover:scale-125 group-hover:-rotate-12 group-hover:drop-shadow-lg"></i>
            </div>
            <span class="sidebar-text ml-3 font-medium transition-all duration-300 group-hover:translate-x-1">WhatsApp CS</span>
        </a> -->

        <!-- Laporan Section -->
        <div x-data="{ open: false, nestedOpenPenjualan: false, nestedOpenPelanggan: false }" class="relative mb-4">
            <button @click="open = !open" id="laporan"
                class="group flex items-center w-full py-3 px-4 rounded-xl hover:bg-gradient-to-r hover:from-purple-100 hover:to-purple-200 hover:text-purple-700 hover:shadow-lg transition-all duration-300 cursor-pointer focus:outline-none border border-transparent hover:border-purple-300">
                <div class="w-8 flex justify-center">
                    <i class="fa fa-book text-xl text-purple-600 group-hover:text-purple-700 transition-all duration-300 group-hover:scale-125 group-hover:-rotate-12 group-hover:drop-shadow-lg"></i>
                </div>
                <span class="sidebar-text ml-3 font-medium transition-all duration-300 group-hover:translate-x-1">Laporan</span>
                <svg class="ml-auto w-4 h-4 transform transition-transform duration-200 group-hover:translate-x-1"
                    :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>

            <div x-show="open" @click.away="open = false" class="mt-3 ml-4 bg-gradient-to-br from-white to-purple-50 rounded-xl shadow-xl border border-purple-200 z-10 backdrop-blur-sm"
                style="display: none;">
                <ul class="py-2">
                    <li>
                        <button @click="nestedOpenPenjualan = !nestedOpenPenjualan"
                            class="w-full text-left px-4 py-2.5 text-gray-700 hover:bg-green-100 hover:text-green-700 transition-all duration-200 flex items-center group cursor-pointer rounded-lg">
                            <span class="transition-all duration-300 group-hover:translate-x-1 font-medium flex items-center">
                                <i class="fa-solid fa-chart-line mr-2 text-lg text-green-500 group-hover:text-green-600 transition-all duration-200 group-hover:scale-110"></i>
                                Penjualan
                            </span>
                            <svg class="ml-auto w-4 h-4 transform transition-transform duration-200 group-hover:translate-x-1"
                                :class="{ 'rotate-180': nestedOpenPenjualan }" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div x-show="nestedOpenPenjualan" @click.away="nestedOpenPenjualan = false" class="ml-4 mt-1"
                            style="display: none;">
                            <ul class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-lg p-2 space-y-1 border border-green-200">
                                <li>
                                    <a href="/src/fitur/laporan/in_laporan_sub_dept" data-menu="laporan_penjualan_subdept"
                                        class="flex items-center px-3 py-2 text-gray-700 hover:bg-pink-100 hover:text-pink-600 transition-all duration-200 group rounded-md">
                                        <span class="transition-all duration-300 group-hover:translate-x-1 text-sm flex items-center">
                                            <i class="fa-regular fa-building mr-2 text-base text-green-400 group-hover:text-green-600 group-hover:scale-110 transition-all duration-200"></i>
                                            Sub Dept
                                        </span>
                                    </a>
                                </li>
                                <li>
                                    <a href="/src/fitur/laporan/in_sales_ratio" data-menu="laporan_penjualan_salesratio"
                                        class="flex items-center px-3 py-2 text-gray-700 hover:bg-pink-100 hover:text-pink-600 transition-all duration-200 group rounded-md">
                                        <span class="transition-all duration-300 group-hover:translate-x-1 text-sm flex items-center">
                                            <i class="fa-solid fa-percent mr-2 text-base text-green-400 group-hover:text-green-600 group-hover:scale-110 transition-all duration-200"></i>
                                            Sales Ratio
                                        </span>
                                    </a>
                                </li>
                                <li>
                                    <a href="/src/fitur/laporan/in_sales_category" data-menu="laporan_penjualan_kategori"
                                        class="flex items-center px-3 py-2 text-gray-700 hover:bg-pink-100 hover:text-pink-600 transition-all duration-200 group rounded-md">
                                        <span class="transition-all duration-300 group-hover:translate-x-1 text-sm flex items-center">
                                            <i class="fa-solid fa-layer-group mr-2 text-base text-green-400 group-hover:text-green-600 group-hover:scale-110 transition-all duration-200"></i>
                                            Kategori
                                        </span>
                                    </a>
                                </li>
                                <li>
                                    <a href="/src/fitur/laporan/in_transaction" data-menu="laporan_penjualan_mnonm"
                                        class="flex items-center px-3 py-2 text-gray-700 hover:bg-pink-100 hover:text-pink-600 transition-all duration-200 group rounded-md">
                                        <span class="transition-all duration-300 group-hover:translate-x-1 text-sm flex items-center">
                                            <i class="fa-solid fa-barcode mr-2 text-base text-green-400 group-hover:text-green-600 group-hover:scale-110 transition-all duration-200"></i>
                                            M / Non M
                                        </span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    <li>
                        <button @click="nestedOpenPelanggan = !nestedOpenPelanggan"
                            class="w-full text-left px-4 py-2.5 text-gray-700 hover:bg-yellow-100 hover:text-yellow-700 transition-all duration-200 flex items-center group cursor-pointer rounded-lg">
                            <span class="transition-all duration-300 group-hover:translate-x-1 font-medium flex items-center">
                                <i class="fa-solid fa-users mr-2 text-lg text-yellow-500 group-hover:text-yellow-600 transition-all duration-200 group-hover:scale-110"></i>
                                Pelanggan
                            </span>
                            <svg class="ml-auto w-4 h-4 transform transition-transform duration-200 group-hover:translate-x-1"
                                :class="{ 'rotate-180': nestedOpenPelanggan }" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div x-show="nestedOpenPelanggan" @click.away="nestedOpenPelanggan = false" class="ml-4 mt-1"
                            style="display: none;">
                            <ul class="bg-gradient-to-br from-yellow-50 to-amber-50 rounded-lg p-2 space-y-1 border border-yellow-200">
                                <li>
                                    <a href="/src/fitur/laporan/in_customer" data-menu="laporan_pelanggan_aktifitas"
                                        class="flex items-center px-3 py-2 text-gray-700 hover:bg-pink-100 hover:text-pink-600 transition-all duration-200 group rounded-md">
                                        <span class="transition-all duration-300 group-hover:translate-x-1 text-sm flex items-center">
                                            <i class="fa-solid fa-shopping-bag mr-2 text-base text-yellow-400 group-hover:text-yellow-600 group-hover:scale-110 transition-all duration-200"></i>
                                            Aktivitas Belanja
                                        </span>
                                    </a>
                                </li>
                                <li>
                                    <a href="/src/fitur/laporan/layanan" data-menu="laporan_pelanggan_layanan"
                                        class="flex items-center px-3 py-2 text-gray-700 hover:bg-pink-100 hover:text-pink-600 transition-all duration-200 group rounded-md">
                                        <span class="transition-all duration-300 group-hover:translate-x-1 text-sm flex items-center">
                                            <i class="fa-solid fa-headset mr-2 text-base text-yellow-400 group-hover:text-yellow-600 group-hover:scale-110 transition-all duration-200"></i>
                                            Layanan
                                        </span>
                                    </a>
                                </li>
                                <li>
                                    <a href="/src/fitur/laporan/in_review_cust" data-menu="laporan_pelanggan_review"
                                        class="flex items-center px-3 py-2 text-gray-700 hover:bg-pink-100 hover:text-pink-600 transition-all duration-200 group rounded-md">
                                        <span class="transition-all duration-300 group-hover:translate-x-1 text-sm flex items-center">
                                            <i class="fa-solid fa-star mr-2 text-base text-yellow-400 group-hover:text-yellow-600 group-hover:scale-110 transition-all duration-200"></i>
                                            Review
                                        </span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Transaksi Section -->
        <div x-data="{ open: false, nestedOpenTrans: false, nestedOpenTrans: false }" class="relative mb-4">
            <button @click="open = !open" id="transaction" 
                class="group flex items-center w-full py-3 px-4 rounded-xl hover:bg-gradient-to-r hover:from-orange-100 hover:to-orange-200 hover:text-orange-700 hover:shadow-lg transition-all duration-300 cursor-pointer focus:outline-none border border-transparent hover:border-orange-300">
                <div class="w-8 flex justify-center">
                    <i class="fa-solid fa-money-bill-transfer text-xl text-orange-600 group-hover:text-orange-700 transition-all duration-300 group-hover:scale-125 group-hover:-rotate-12 group-hover:drop-shadow-lg"></i>
                </div>
                <span class="sidebar-text ml-3 font-medium transition-all duration-300 group-hover:translate-x-1">Transaksi</span>
                <svg class="ml-auto w-4 h-4 transform transition-transform duration-200 group-hover:translate-x-1"
                    :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>

            <div x-show="open" @click.away="open = false" class="mt-3 ml-4 bg-gradient-to-br from-white to-orange-50 rounded-xl shadow-xl border border-orange-200 z-10 backdrop-blur-sm"
                style="display: none;">
                <ul class="py-2 space-y-1">
                    <li>
                        <a href="/src/fitur/transaction/view_promo" data-menu="transaksi_promo"
                            class="flex items-center px-4 py-2.5 text-gray-700 hover:bg-orange-100 hover:text-orange-700 transition-all duration-200 group rounded-lg">
                            <span class="transition-all duration-300 group-hover:translate-x-1 text-sm font-medium flex items-center">
                                <i class="fa-solid fa-percent mr-2 text-base text-orange-400 group-hover:text-orange-600 group-hover:scale-110 transition-all duration-200"></i>
                                Promo
                            </span>
                        </a>
                    </li>
                    <li>
                        <a href="/src/fitur/transaction/invalid_trans" data-menu="transaksi_invalid"
                            class="flex items-center px-4 py-2.5 text-gray-700 hover:bg-pink-50 hover:text-pink-600 transition-all duration-200 group rounded-lg">
                            <span class="transition-all duration-300 group-hover:translate-x-1 text-sm font-medium flex items-center">
                                <i class="fa-solid fa-ban mr-2 text-base text-orange-400 group-hover:text-orange-600 group-hover:scale-110 transition-all duration-200"></i>
                                Invalid
                            </span>
                        </a>
                    </li>
                    <li>
                        <a href="/src/fitur/transaction/margin" data-menu="transaksi_margin"
                            class="flex items-center px-4 py-2.5 text-gray-700 hover:bg-pink-50 hover:text-pink-600 transition-all duration-200 group rounded-lg">
                            <span class="transition-all duration-300 group-hover:translate-x-1 text-sm font-medium flex items-center">
                                <i class="fa-solid fa-coins mr-2 text-base text-orange-400 group-hover:text-orange-600 group-hover:scale-110 transition-all duration-200"></i>
                                Margin
                            </span>
                        </a>
                    </li>
                    <li>
                        <a href="/src/fitur/transaction/reward_give" data-menu="reward_give"
                            class="flex items-center px-4 py-2.5 text-gray-700 hover:bg-pink-50 hover:text-pink-600 transition-all duration-200 group rounded-lg">
                            <span class="transition-all duration-300 group-hover:translate-x-1 text-sm font-medium flex items-center">
                                <i class="fa-solid fa-gift mr-2 text-base text-orange-400 group-hover:text-orange-600 group-hover:scale-110 transition-all duration-200"></i>
                                Hadiah
                            </span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Member Section -->
        <div x-data="{ open: false, nestedOpenMember: false, nestedOpenMember: false }" class="relative mb-4">
            <button @click="open = !open" id="member"
                class="group flex items-center w-full py-3 px-4 rounded-xl hover:bg-gradient-to-r hover:from-teal-100 hover:to-teal-200 hover:text-teal-700 hover:shadow-lg transition-all duration-300 cursor-pointer focus:outline-none border border-transparent hover:border-teal-300">
                <div class="w-8 flex justify-center">
                    <i class="fa-solid fa-id-card text-xl text-teal-600 group-hover:text-teal-700 transition-all duration-300 group-hover:scale-125 group-hover:-rotate-12 group-hover:drop-shadow-lg"></i>
                </div>
                <span class="sidebar-text ml-3 font-medium transition-all duration-300 group-hover:translate-x-1">Member</span>
                <svg class="ml-auto w-4 h-4 transform transition-transform duration-200 group-hover:translate-x-1"
                    :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>

            <div x-show="open" @click.away="open = false" class="mt-3 ml-4 bg-gradient-to-br from-white to-teal-50 rounded-xl shadow-xl border border-teal-200 z-10 backdrop-blur-sm"
                style="display: none;">
                <ul class="py-2">
                    <li>
                        <a href="/src/fitur/member/poin_member"  data-menu="member_poin"
                            class="flex items-center px-4 py-2.5 text-gray-700 hover:bg-teal-100 hover:text-teal-700 transition-all duration-200 group rounded-lg">
                            <span class="transition-all duration-300 group-hover:translate-x-1 text-sm font-medium flex items-center">
                                <i class="fa-solid fa-coins mr-2 text-base text-teal-400 group-hover:text-teal-600 group-hover:scale-110 transition-all duration-200"></i>
                                Poin
                            </span>
                        </a>
                    </li>
                </ul>
                <ul class="py-2">
                    <li>
                        <a href="/src/fitur/member/management_member"  data-menu="member_poin"
                            class="flex items-center px-4 py-2.5 text-gray-700 hover:bg-teal-100 hover:text-teal-700 transition-all duration-200 group rounded-lg">
                            <span class="transition-all duration-300 group-hover:translate-x-1 text-sm font-medium flex items-center">
                                <i class="fa-solid fa-coins mr-2 text-base text-teal-400 group-hover:text-teal-600 group-hover:scale-110 transition-all duration-200"></i>
                                Kelola
                            </span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Account Section -->
        <div x-data="{ open: false, nestedOpenAccount: false, nestedOpenAccount: false }" class="relative mb-4">
            <button @click="open = !open" id="account" 
                class="group flex items-center w-full py-3 px-4 rounded-xl hover:bg-gradient-to-r hover:from-indigo-100 hover:to-indigo-200 hover:text-indigo-700 hover:shadow-lg transition-all duration-300 cursor-pointer focus:outline-none border border-transparent hover:border-indigo-300">
                <div class="w-8 flex justify-center">
                    <i class="fa-solid fa-user text-xl text-indigo-600 group-hover:text-indigo-700 transition-all duration-300 group-hover:scale-125 group-hover:-rotate-12 group-hover:drop-shadow-lg"></i>
                </div>
                <span class="sidebar-text ml-3 font-medium transition-all duration-300 group-hover:translate-x-1">Account</span>
                <svg class="ml-auto w-4 h-4 transform transition-transform duration-200 group-hover:translate-x-1"
                    :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>

            <div x-show="open" @click.away="open = false" class="mt-3 ml-4 bg-gradient-to-br from-white to-indigo-50 rounded-xl shadow-xl border border-indigo-200 z-10 backdrop-blur-sm"
                style="display: none;">
                <ul class="py-2 space-y-1">
                    <li>
                        <a href="/src/fitur/account/in_new_user" data-menu="insert_new_user"
                            class="flex items-center px-4 py-2.5 text-gray-700 hover:bg-indigo-100 hover:text-indigo-700 transition-all duration-200 group rounded-lg">
                            <span class="transition-all duration-300 group-hover:translate-x-1 text-sm font-medium flex items-center">
                                <i class="fa-solid fa-user-plus mr-2 text-base text-indigo-400 group-hover:text-indigo-600 group-hover:scale-110 transition-all duration-200"></i>
                                Tambah Anggota
                            </span>
                        </a>
                    </li>
                    <li>
                        <a href="/src/fitur/account/manajemen_user" data-menu="user_management"
                            class="flex items-center px-4 py-2.5 text-gray-700 hover:bg-pink-50 hover:text-pink-600 transition-all duration-200 group rounded-lg">
                            <span class="transition-all duration-300 group-hover:translate-x-1 text-sm font-medium flex items-center">
                                <i class="fa-solid fa-users-cog mr-2 text-base text-indigo-400 group-hover:text-indigo-600 group-hover:scale-110 transition-all duration-200"></i>
                                Kelola Anggota
                            </span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>

         <!-- Product -->
        <div class="relative mb-4">
            <a href="/src/fitur/products/product" id="productLink" data-menu="products"
                class="group flex items-center w-full py-3 px-4 rounded-xl hover:bg-gradient-to-r hover:from-fuchsia-100 hover:to-fuchsia-200 hover:text-fuchsia-700 hover:shadow-lg transition-all duration-300 cursor-pointer focus:outline-none border border-transparent hover:border-fuchsia-300">
                <div class="w-8 flex justify-center">
                    <i class="fa-solid fa-box text-xl text-fuchsia-600 group-hover:text-fuchsia-700 transition-all duration-300 group-hover:scale-125 group-hover:-rotate-12 group-hover:drop-shadow-lg"></i>
                </div>
                <span class="sidebar-text ml-3 font-medium transition-all duration-300 group-hover:translate-x-1">Produk</span>
            </a>
        </div>

        <!-- Asset -->
        <div class="relative mb-4">
            <a href="/src/fitur/aset/history_aset" id="productLink" data-menu="history_aset"
                class="group flex items-center w-full py-3 px-4 rounded-xl hover:bg-gradient-to-r hover:from-fuchsia-100 hover:to-fuchsia-200 hover:text-fuchsia-700 hover:shadow-lg transition-all duration-300 cursor-pointer focus:outline-none border border-transparent hover:border-fuchsia-300">
                <div class="w-8 flex justify-center">
                    <i class="fa-solid fa-boxes-packing text-xl text-fuchsia-600 group-hover:text-fuchsia-700 transition-all duration-300 group-hover:scale-125 group-hover:-rotate-12 group-hover:drop-shadow-lg"></i>
                </div>
                <span class="sidebar-text ml-3 font-medium transition-all duration-300 group-hover:translate-x-1">Management Aset</span>
            </a>
        </div>
       <!-- Upload -->
        <div x-data="{ open: false }" class="relative mb-4">
            <button @click="open = !open" id="upload-menu" data-menu="upload_banner"
                class="group flex items-center w-full py-3 px-4 rounded-xl hover:bg-gradient-to-r hover:from-fuchsia-100 hover:to-fuchsia-200 hover:text-fuchsia-700 hover:shadow-lg transition-all duration-300 cursor-pointer focus:outline-none border border-transparent hover:border-fuchsia-300">
                <div class="w-8 flex justify-center">
                    <i class="fa-solid fa-upload text-xl text-fuchsia-600 group-hover:text-fuchsia-700 transition-all duration-300 group-hover:scale-125 group-hover:-rotate-12 group-hover:drop-shadow-lg"></i>
                </div>
                <span class="sidebar-text ml-3 font-medium transition-all duration-300 group-hover:translate-x-1">Upload</span>
                <svg class="ml-auto w-4 h-4 transform transition-transform duration-200 group-hover:translate-x-1"
                    :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>
            <div x-show="open" @click.away="open = false" class="mt-3 ml-4 bg-gradient-to-br from-white to-fuchsia-50 rounded-xl shadow-xl border border-fuchsia-200 z-10 backdrop-blur-sm"
                style="display: none;">
                <ul class="py-2">
                    <li>
                        <a href="/src/fitur/banner/view_banner" data-menu="upload_banner"
                            class="flex items-center px-4 py-2.5 text-gray-700 hover:bg-fuchsia-100 hover:text-fuchsia-700 transition-all duration-200 group rounded-lg">
                            <span class="transition-all duration-300 group-hover:translate-x-1 text-sm font-medium flex items-center">
                                <i class="fa-regular fa-image mr-2 text-base text-fuchsia-400 group-hover:text-fuchsia-600 group-hover:scale-110 transition-all duration-200"></i>
                                Banner
                            </span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
</div>
<!-- <script src="/src/js/account/internal/menu.js" type="module"></script> -->
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const currentPath = window.location.pathname; // Mengambil URL path saat ini

        // Menambahkan kelas "active" pada link yang sesuai
        if (currentPath.includes('/in_beranda')) {
            document.getElementById('berandaLink').classList.add('btn', 'active');
        } else if (currentPath.includes('/src/fitur/laporan/in_laporan_sub_dept')) {
            document.getElementById('laporan').classList.add('btn', 'active');
        } else if (currentPath.includes('/src/fitur/laporan/in_sales_ratio')) {
            document.getElementById('laporan').classList.add('btn', 'active');
        } else if (currentPath.includes('/src/fitur/laporan/in_sales_category')) {
            document.getElementById('laporan').classList.add('btn', 'active');
        } else if (currentPath.includes('/src/fitur/laporan/in_transaction')) {
            document.getElementById('laporan').classList.add('btn', 'active');
        } 
        else if (currentPath.includes('/src/fitur/account/in_new_user')) {
            document.getElementById('account').classList.add('btn', 'active');
        } else if (currentPath.includes('/src/fitur/account/manajemen_user')) {
            document.getElementById('account').classList.add('btn', 'active');
        }
        else if (currentPath.includes('/src/fitur/transaction/view_promo')) {
            document.getElementById('transaction').classList.add('btn', 'active');
        } else if (currentPath.includes('/src/fitur/transaction/view_promo')) {
            document.getElementById('transaction').classList.add('btn', 'active');
        } else if (currentPath.includes('/src/fitur/transaction/invalid_trans')) {
            document.getElementById('transaction').classList.add('btn', 'active');
        } else if (currentPath.includes('/src/fitur/transaction/margin')) {
            document.getElementById('transaction').classList.add('btn', 'active');
        } 
        else if (currentPath.includes('/src/fitur/member/member_poin')) {
            document.getElementById('member').classList.add('btn', 'active');
        }
        else if (currentPath.includes('/src/fitur/banner/view_banner.php')) {
            document.getElementById('upload-menu').classList.add('btn', 'active');
        }else if (currentPath.includes('/src/fitur/shopee/dashboard_shopee')) {
            document.getElementById('shopeeLink').classList.add('btn', 'active');
        }
        // Tambahkan kondisi untuk menu lainnya sesuai kebutuhan
    });
</script>
<style>
    /* Professional icon animations */
    .group:hover i {
        animation: iconBounce 0.6s ease-in-out;
    }
    
    @keyframes iconBounce {
        0%, 20%, 50%, 80%, 100% {
            transform: translateY(0) scale(1);
        }
        40% {
            transform: translateY(-3px) scale(1.1);
        }
        60% {
            transform: translateY(-1px) scale(1.05);
        }
    }
    
    /* Active state for icons */
    .group.active i {
        color: #ec4899 !important;
        text-shadow: 0 0 8px rgba(236, 72, 153, 0.3);
        animation: iconGlow 2s ease-in-out infinite alternate;
    }
    
    @keyframes iconGlow {
        from {
            text-shadow: 0 0 8px rgba(236, 72, 153, 0.3);
        }
        to {
            text-shadow: 0 0 12px rgba(236, 72, 153, 0.6);
        }
    }
    
    /* Hover effects for submenu icons */
    .group:hover i[class*="fa-"] {
        filter: drop-shadow(0 2px 4px rgba(236, 72, 153, 0.2));
    }
</style>