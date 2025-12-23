<!-- Fixed Bottom Navigation -->
<nav class="fixed bottom-0 left-0 right-0 bg-white/95 backdrop-blur-lg border-t border-pink-100 shadow-lg z-50">
    <div class="max-w-4xl mx-auto px-2 sm:px-4">
        <div class="flex items-center justify-between relative">
            <!-- Beranda -->
            <a href="/customer/home" class="flex flex-col items-center py-2 px-1 sm:py-3 sm:px-2 relative group transition-all duration-300 hover:bg-pink-50 active:bg-pink-100 flex-1 min-w-0">
                <div class="relative">
                    <i class="fas fa-house text-lg sm:text-xl mb-1 text-gray-600 group-hover:text-pink-500 transition-colors duration-300"></i>
                    <div class="absolute -top-1 -right-1 w-2 h-2 bg-pink-500 rounded-full opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                </div>
                <span class="text-[10px] sm:text-xs font-medium text-gray-600 group-hover:text-pink-500 transition-colors duration-300 text-center">Beranda</span>
                <div class="absolute bottom-0 left-1/2 transform -translate-x-1/2 w-0 h-0.5 bg-gradient-to-r from-pink-500 to-purple-600 group-hover:w-6 sm:group-hover:w-8 transition-all duration-300"></div>
            </a>

            <!-- Riwayat -->
            <a href="/customer/history" class="flex flex-col items-center py-2 px-1 sm:py-3 sm:px-2 relative group transition-all duration-300 hover:bg-blue-50 active:bg-blue-100 flex-1 min-w-0">
                <div class="relative">
                    <i class="fas fa-clock-rotate-left text-lg sm:text-xl mb-1 text-gray-600 group-hover:text-blue-500 transition-colors duration-300"></i>
                    <div class="absolute -top-1 -right-1 w-2 h-2 bg-blue-500 rounded-full opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                </div>
                <span class="text-[10px] sm:text-xs font-medium text-gray-600 group-hover:text-blue-500 transition-colors duration-300 text-center">Riwayat</span>
                <div class="absolute bottom-0 left-1/2 transform -translate-x-1/2 w-0 h-0.5 bg-gradient-to-r from-blue-500 to-indigo-600 group-hover:w-6 sm:group-hover:w-8 transition-all duration-300"></div>
            </a>

            <!-- QRIS Button (Center, Floating) -->
            <div class="flex flex-col items-center justify-center px-2 sm:px-4 relative">
                <div class="relative -top-4 sm:-top-6">
                    <div class="absolute left-1/2 transform -translate-x-1/2 top-6 sm:top-8 w-16 sm:w-20 h-16 sm:h-20 bg-white rounded-full shadow-inner border-2 sm:border-4 border-gray-50"></div>
                    <a href="/customer/qris" id="qris" class="qris-btn relative z-10 flex flex-col items-center justify-center w-14 h-14 sm:w-16 sm:h-16 bg-gradient-to-br from-purple-500 via-pink-500 to-red-500 rounded-full shadow-2xl transform transition-all duration-300 hover:scale-110 hover:shadow-purple-500/50 active:scale-95 group">
                        <div class="absolute inset-0 bg-gradient-to-br from-purple-400 via-pink-400 to-red-400 rounded-full animate-ping opacity-20"></div>
                        <div class="relative flex flex-col items-center">
                            <i class="fas fa-qrcode text-white text-lg sm:text-xl mb-0.5 drop-shadow-sm"></i>
                            <span class="text-white text-[7px] sm:text-[8px] font-bold tracking-wide drop-shadow-sm">QR</span>
                        </div>
                        <!-- Glow effect -->
                        <div class="absolute inset-0 rounded-full bg-gradient-to-br from-purple-500 via-pink-500 to-red-500 opacity-0 group-hover:opacity-30 transition-opacity duration-300 blur-md"></div>
                    </a>
                </div>
            </div>

            <!-- Promo -->
            <a href="/customer/promo" class="flex flex-col items-center py-2 px-1 sm:py-3 sm:px-2 relative group transition-all duration-300 hover:bg-yellow-50 active:bg-yellow-100 flex-1 min-w-0">
                <div class="relative">
                    <i class="fas fa-tags text-lg sm:text-xl mb-1 text-gray-600 group-hover:text-yellow-500 transition-colors duration-300"></i>
                    <div class="absolute -top-1 -right-1 w-2 h-2 bg-yellow-500 rounded-full opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                </div>
                <span class="text-[10px] sm:text-xs font-medium text-gray-600 group-hover:text-yellow-500 transition-colors duration-300 text-center">Promo</span>
                <div class="absolute bottom-0 left-1/2 transform -translate-x-1/2 w-0 h-0.5 bg-gradient-to-r from-yellow-500 to-orange-600 group-hover:w-6 sm:group-hover:w-8 transition-all duration-300"></div>
            </a>

            
            <!-- Akun -->
            <a href="/customer/profile" class="flex flex-col items-center py-2 px-1 sm:py-3 sm:px-2 relative group transition-all duration-300 hover:bg-green-50 active:bg-green-100 flex-1 min-w-0">
                <div class="relative">
                    <i class="fas fa-user text-lg sm:text-xl mb-1 text-gray-600 group-hover:text-green-500 transition-colors duration-300"></i>
                    <div class="absolute -top-1 -right-1 w-2 h-2 bg-green-500 rounded-full opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                </div>
                <span class="text-[10px] sm:text-xs font-medium text-gray-600 group-hover:text-green-500 transition-colors duration-300 text-center">Akun</span>
                <div class="absolute bottom-0 left-1/2 transform -translate-x-1/2 w-0 h-0.5 bg-gradient-to-r from-green-500 to-emerald-600 group-hover:w-6 sm:group-hover:w-8 transition-all duration-300"></div>
            </a>
        </div>
    </div>

    <!-- Active Page Indicator -->
    <script>
        // Add active state based on current page
        document.addEventListener('DOMContentLoaded', function() {
            const currentPath = window.location.pathname;
            const navLinks = document.querySelectorAll('nav a:not(.qris-btn)');
            const qrisBtn = document.querySelector('.qris-btn');

            navLinks.forEach(link => {
                if (link.getAttribute('href') === currentPath) {
                    link.classList.add('active');
                    const icon = link.querySelector('i');
                    const text = link.querySelector('span');
                    const indicator = link.querySelector('.absolute.-top-1');
                    const underline = link.querySelector('.absolute.bottom-0');

                    // Set active colors based on page
                    if (currentPath.includes('home')) {
                        icon.classList.remove('text-gray-600', 'group-hover:text-pink-500');
                        icon.classList.add('text-pink-500');
                        text.classList.remove('text-gray-600', 'group-hover:text-pink-500');
                        text.classList.add('text-pink-500');
                        indicator.classList.remove('opacity-0');
                        indicator.classList.add('opacity-100');
                        underline.classList.remove('w-0');
                        underline.classList.add('w-6', 'sm:w-8');
                        link.classList.add('bg-pink-50');
                    } else if (currentPath.includes('history')) {
                        icon.classList.remove('text-gray-600', 'group-hover:text-blue-500');
                        icon.classList.add('text-blue-500');
                        text.classList.remove('text-gray-600', 'group-hover:text-blue-500');
                        text.classList.add('text-blue-500');
                        indicator.classList.remove('opacity-0');
                        indicator.classList.add('opacity-100');
                        underline.classList.remove('w-0');
                        underline.classList.add('w-6', 'sm:w-8');
                        link.classList.add('bg-blue-50');
                    } else if (currentPath.includes('promo')) {
                        icon.classList.remove('text-gray-600', 'group-hover:text-yellow-500');
                        icon.classList.add('text-yellow-500');
                        text.classList.remove('text-gray-600', 'group-hover:text-yellow-500');
                        text.classList.add('text-yellow-500');
                        indicator.classList.remove('opacity-0');
                        indicator.classList.add('opacity-100');
                        underline.classList.remove('w-0');
                        underline.classList.add('w-6', 'sm:w-8');
                        link.classList.add('bg-yellow-50');
                    } else if (currentPath.includes('profile')) {
                        icon.classList.remove('text-gray-600', 'group-hover:text-green-500');
                        icon.classList.add('text-green-500');
                        text.classList.remove('text-gray-600', 'group-hover:text-green-500');
                        text.classList.add('text-green-500');
                        indicator.classList.remove('opacity-0');
                        indicator.classList.add('opacity-100');
                        underline.classList.remove('w-0');
                        underline.classList.add('w-6', 'sm:w-8');
                        link.classList.add('bg-green-50');
                    }
                }
            });

            // Handle QRIS button active state
            if (currentPath.includes('qris')) {
                qrisBtn.classList.add('scale-110', 'shadow-purple-500/70');
                const glowEffect = qrisBtn.querySelector('.absolute.inset-0.rounded-full.bg-gradient-to-br');
                if (glowEffect) {
                    glowEffect.classList.remove('opacity-0');
                    glowEffect.classList.add('opacity-30');
                }
            }

            // Add ripple effect to QRIS button
            if (qrisBtn) {
                qrisBtn.addEventListener('click', function(e) {
                    // Create ripple effect
                    const ripple = document.createElement('div');
                    ripple.classList.add('absolute', 'inset-0', 'rounded-full', 'bg-white', 'opacity-30', 'animate-ping');
                    this.appendChild(ripple);

                    setTimeout(() => {
                        ripple.remove();
                    }, 600);
                });
            }
        });
    </script>

    <style>
        .qris-btn {
            box-shadow: 0 8px 20px -4px rgba(168, 85, 247, 0.4), 0 6px 8px -4px rgba(168, 85, 247, 0.4);
        }

        .qris-btn:hover {
            box-shadow: 0 16px 32px -8px rgba(168, 85, 247, 0.6), 0 12px 16px -8px rgba(168, 85, 247, 0.4);
        }

        /* Pulse animation for QRIS */
        @keyframes pulse-qris {

            0%,
            100% {
                transform: scale(1);
                opacity: 0.2;
            }

            50% {
                transform: scale(1.05);
                opacity: 0.1;
            }
        }

        .qris-btn .animate-ping {
            animation: pulse-qris 2s cubic-bezier(0, 0, 0.2, 1) infinite;
        }

        /* Responsive adjustments */
        @media (max-width: 640px) {
            .qris-btn {
                box-shadow: 0 6px 16px -3px rgba(168, 85, 247, 0.4), 0 4px 6px -3px rgba(168, 85, 247, 0.4);
            }
        }
    </style>
</nav>