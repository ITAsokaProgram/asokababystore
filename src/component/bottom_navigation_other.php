<?php
/**
 * Bottom Navigation Component for non-index pages
 * A reusable bottom navigation bar for mobile view
 */

// Get current page/route
$current_page = $_SERVER['REQUEST_URI'];

$is_active = function($link) use ($current_page) {
    if ($link === '/kontak' && $current_page === '/kontak') {
        return true;
    }
    if ($link === '/pesan_sekarang' && $current_page === '/pesan_sekarang') {
        return true;
    }
    return false;
};
?>
<nav class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 md:hidden z-50">
    <div class="flex justify-around items-center h-16 relative px-2">
        <a href="/index#home-section" class="nav-link flex flex-col items-center justify-center w-full h-full text-gray-600 hover:text-pink-500 hover:bg-pink-50" data-section="home-section">
            <i class="fas fa-home text-lg"></i>
            <span class="text-[10px] mt-0.5">Beranda</span>
        </a>
        <a href="/index#gallery-section" class="nav-link flex flex-col items-center justify-center w-full h-full text-gray-600 hover:text-pink-500 hover:bg-pink-50" data-section="gallery-section">
            <i class="fas fa-images text-lg"></i>
            <span class="text-[10px] mt-0.5">Galeri</span>
        </a>
        <a href="/index#lokasi" class="nav-link flex flex-col items-center justify-center w-full h-full text-gray-600 hover:text-pink-500 hover:bg-pink-50" data-section="lokasi">
            <i class="fas fa-map-marker-alt text-lg"></i>
            <span class="text-[10px] mt-0.5">Lokasi</span>
        </a>
        <a href="/index#member-section" class="nav-link flex flex-col items-center justify-center w-full h-full text-gray-600 hover:text-pink-500 hover:bg-pink-50" data-section="member-section">
            <i class="fas fa-user text-lg"></i>
            <span class="text-[10px] mt-0.5">Member</span>
        </a>
        <a href="/kontak" class="nav-link flex flex-col items-center justify-center w-full h-full <?php echo $is_active('/kontak') ? 'text-pink-500' : 'text-gray-600 hover:text-pink-500 hover:bg-pink-50'; ?>" data-section="kontak">
            <i class="fas fa-question-circle text-lg"></i>
            <span class="text-[10px] mt-0.5">Bantuan</span>
        </a>
    </div>
</nav>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const navLinks = document.querySelectorAll('.nav-link');
    const currentPath = window.location.pathname;

    // Set initial active state
    navLinks.forEach(link => {
        const section = link.getAttribute('data-section');
        if (section === 'kontak' && currentPath === '/kontak') {
            link.classList.remove('text-gray-600');
            link.classList.add('text-pink-500');
        } else {
            link.classList.remove('text-pink-500');
            link.classList.add('text-gray-600');
        }
    });

    // Add click event listeners
    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            const section = this.getAttribute('data-section');

            // Update active state
            navLinks.forEach(l => {
                l.classList.remove('text-pink-500');
                l.classList.add('text-gray-600');
            });
            this.classList.remove('text-gray-600');
            this.classList.add('text-pink-500');

            // Handle navigation
            if (section === 'kontak' && currentPath === '/kontak') {
                e.preventDefault();
                return;
            }
            // Let it navigate to the target page
        });
    });
});
</script> 