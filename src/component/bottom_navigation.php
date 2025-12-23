<?php

// Get current page/route
$current_page = $_SERVER['REQUEST_URI'];

$is_active = function ($link) use ($current_page) {
    if ($link === '/kontak' && $current_page === '/kontak') {
        return true;
    }
    if ($link === '/pesan_sekarang' && $current_page === '/pesan_sekarang') {
        return true;
    }
    // Handle index page sections
    if (strpos($link, '#') !== false && $current_page === '/index') {
        return true;
    }
    return false;
};
?>
<nav class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 md:hidden z-50">
    <div class="flex justify-around items-center h-16 relative px-2">
        <a href="#home-section"
            class="nav-link flex flex-col items-center justify-center w-full h-full <?php echo $is_active('#home-section') ? 'text-pink-500' : 'text-gray-600 hover:text-pink-500 hover:bg-pink-50'; ?>"
            data-section="home-section">
            <i class="fas fa-home text-lg"></i>
            <span class="text-[10px] mt-0.5">Beranda</span>
        </a>
        <a href="#gallery-section"
            class="nav-link flex flex-col items-center justify-center w-full h-full <?php echo $is_active('#gallery-section') ? 'text-pink-500' : 'text-gray-600 hover:text-pink-500 hover:bg-pink-50'; ?>"
            data-section="gallery-section">
            <i class="fas fa-images text-lg"></i>
            <span class="text-[10px] mt-0.5">Galeri</span>
        </a>
        <a href="#lokasi"
            class="nav-link flex flex-col items-center justify-center w-full h-full <?php echo $is_active('#lokasi') ? 'text-pink-500' : 'text-gray-600 hover:text-pink-500 hover:bg-pink-50'; ?>"
            data-section="lokasi">
            <i class="fas fa-map-marker-alt text-lg"></i>
            <span class="text-[10px] mt-0.5">Lokasi</span>
        </a>
        <a href="#member-section" id="openModalBottom"
            class="nav-link flex flex-col items-center justify-center w-full h-full <?php echo $is_active('#member-section') ? 'text-pink-500' : 'text-gray-600 hover:text-pink-500 hover:bg-pink-50'; ?>"
            data-section="member-section">
            <i class="fas fa-user text-lg" id="memberIconMobile"></i>
            <span class="text-[10px] mt-0.5" id="memberLabelMobile">Member</span>
        </a>
        <!-- <a href="/produk"
            class="nav-link flex flex-col items-center justify-center w-full h-full <?php echo $is_active('/produk') ? 'text-pink-500' : 'text-gray-600 hover:text-pink-500 hover:bg-pink-50'; ?>"
            data-section="produk">
            <i class="fas fa-box text-lg"></i>
            <span class="text-[10px] mt-0.5">Produk</span>
        </a> -->
        
        <a href="/kontak"
            class="nav-link flex flex-col items-center justify-center w-full h-full <?php echo $is_active('/kontak') ? 'text-pink-500' : 'text-gray-600 hover:text-pink-500 hover:bg-pink-50'; ?>"
            data-section="kontak">
            <i class="fas fa-question-circle text-lg"></i>
            <span class="text-[10px] mt-0.5">Bantuan</span>
        </a>
    </div>
</nav>

<script type="module">
    document.addEventListener('DOMContentLoaded', function () {
        const navLinks = document.querySelectorAll('.nav-link');
        const sections = document.querySelectorAll('section[id]');

        // Function to set active state
        function setActiveLink() {
            const scrollPosition = window.scrollY;
            let activeSection = null;

            sections.forEach(section => {
                const sectionTop = section.offsetTop - 100;
                const sectionBottom = sectionTop + section.offsetHeight;
                const sectionId = section.getAttribute('id');

                if (scrollPosition >= sectionTop && scrollPosition < sectionBottom) {
                    activeSection = sectionId;
                }
            });

            // Update active state
            navLinks.forEach(link => {
                const linkSection = link.getAttribute('data-section');
                if (linkSection === activeSection) {
                    link.classList.remove('text-gray-600');
                    link.classList.add('text-pink-500');
                } else {
                    link.classList.remove('text-pink-500');
                    link.classList.add('text-gray-600');
                }
            });
        }

        // Set initial active state
        const hash = window.location.hash.replace('#', '');
        if (hash) {
            navLinks.forEach(link => {
                if (link.getAttribute('data-section') === hash) {
                    link.classList.remove('text-gray-600');
                    link.classList.add('text-pink-500');
                } else {
                    link.classList.remove('text-pink-500');
                    link.classList.add('text-gray-600');
                }
            });
        } else {
            // If no hash, set home as active
            navLinks.forEach(link => {
                if (link.getAttribute('data-section') === 'home-section') {
                    link.classList.remove('text-gray-600');
                    link.classList.add('text-pink-500');
                } else {
                    link.classList.remove('text-pink-500');
                    link.classList.add('text-gray-600');
                }
            });
        }

        // Add click event listeners
        navLinks.forEach(link => {
            link.addEventListener('click', function (e) {
                const section = this.getAttribute('data-section');

                // Update active state
                navLinks.forEach(l => {
                    l.classList.remove('text-pink-500');
                    l.classList.add('text-gray-600');
                });
                this.classList.remove('text-gray-600');
                this.classList.add('text-pink-500');

                // Handle navigation
                if (section === 'member-section') {
                    e.preventDefault();
                    document.getElementById('openModal').click();
                } else if (section === 'kontak') {
                    // Let it navigate to /kontak
                } else if(section === 'produk'){
                    
                }
                else {
                    e.preventDefault();
                    const targetSection = document.getElementById(section);
                    if (targetSection) {
                        targetSection.scrollIntoView({ behavior: 'smooth' });
                    }
                }
            });
        });

        // Add scroll event listener with debounce
        let scrollTimeout;
        window.addEventListener('scroll', function () {
            clearTimeout(scrollTimeout);
            scrollTimeout = setTimeout(setActiveLink, 100);
        });

        // Initial check
        setActiveLink();
        // fetchUserData();
    });
</script>