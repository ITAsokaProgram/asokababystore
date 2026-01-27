<header class="bg-white border-b border-gray-200 shadow-sm fixed top-0 w-full z-50 text-gray-800">
    <div class="flex items-center justify-between h-16 px-4">
        
        <div class="flex items-center space-x-4">
            <a href="/supplier_dashboard.php" class="flex items-center space-x-3 group">
                <div class="relative">
                    <img src="/images/logo.png" alt="Asoka Baby Store" 
                            class="h-8 w-auto transition-transform duration-300 group-hover:scale-105">
                </div>
                <div class="hidden sm:block">
                    <h1 class="text-lg font-bold text-gray-900">Asoka Baby Store</h1>
                    <p class="text-xs text-gray-500">Supplier Portal</p>
                </div>
            </a>
        </div>

        <div class="flex items-center space-x-4">
            
            <div class="relative">
                <button id="profile-img" class="flex items-center space-x-3 p-2 rounded-lg hover:bg-gray-100 transition-colors duration-200 group focus:outline-none">
                    <div class="relative">
                        <img src="/public/images/pic-1.jpg" onerror="this.src='https://ui-avatars.com/api/?name=User'"
                                class="w-8 h-8 rounded-full object-cover border-2 border-white shadow-sm group-hover:shadow-md transition-shadow duration-200" id="header-avatar">
                        <div class="absolute bottom-0 right-0 block h-2.5 w-2.5 bg-green-400 rounded-full border-2 border-white"></div>
                    </div>
                    <div class="hidden sm:block text-left">
                        <p class="text-sm font-medium text-gray-900" id="user-nama">Memuat...</p>
                        <p class="text-xs text-gray-500" id="user-role">...</p>
                    </div>
                    <i class="fas fa-chevron-down text-xs text-gray-400 group-hover:text-gray-600 transition-colors duration-200" id="chevron-icon"></i>
                </button>

                <div class="profile-card absolute right-0 top-12 bg-white rounded-xl shadow-lg border border-gray-200 w-56 transition-all duration-300 z-50 opacity-0 invisible transform origin-top-right scale-95" 
                        id="profile-card">
                    
                    <div class="p-4 border-b border-gray-100">
                        <div class="flex items-center space-x-3">
                            <img src="/public/images/pic-1.jpg" onerror="this.src='https://ui-avatars.com/api/?name=User'"
                                    class="w-12 h-12 rounded-full object-cover border-2 border-gray-200" id="dropdown-avatar">
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-gray-900 truncate" id="user-nama-dropdown">User Name</p>
                                <p class="text-xs text-gray-500 truncate" id="user-role-dropdown">Role</p>
                            </div>
                        </div>
                    </div>

                    <div class="p-2 space-y-1">

                        <button type="button" id="logout-btn" 
                                class="flex items-center space-x-3 px-3 py-2 text-sm text-red-600 hover:bg-red-50 rounded-lg transition-colors duration-200 w-full text-left">
                            <i class="fas fa-sign-out-alt text-red-400 w-4"></i>
                            <span>Keluar</span>
                        </button>
                    </div>
                </div>
            </div>

            <button id="toggle-sidebar" 
                    class="md:hidden flex items-center justify-center w-10 h-10 bg-gray-100 hover:bg-gray-200 rounded-lg text-gray-600 hover:text-gray-800 transition-all duration-200">
                <i class="fas fa-bars text-lg"></i>
            </button>
        </div>
    </div>
</header>
<div class="h-8"></div> 