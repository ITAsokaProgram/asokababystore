import { fetchUser } from './fetch/fetch_user.js';
import { getCookie } from './utils/cookies.js';
import { toogleLoginDropdown } from './mobile_ui/dropdown.js';
import { logoutUser } from './fetch/logout_user.js';
import { getDataToMember, openModal } from '../cek_member.js';

// Wait for DOM to be ready
document.addEventListener('DOMContentLoaded', () => {
const cookies = getCookie('token');
const menu = document.getElementById('openModalBottom');
const menuWeb = document.getElementById('openModal');
const label = document.getElementById('memberLabelMobile');
const icon = document.getElementById('memberIconMobile');
const btnKlikLogin = document.getElementById('btn-klik-login');
    
const init_fetch = async () => {
    // Jika token tidak ada, set menu untuk login
    openModal("modalMember1", "modalContent1", "openModal1");
        // Enable the modal button
        const openModal1Btn = document.getElementById("openModal1");
        if (openModal1Btn) {
            openModal1Btn.disabled = false;
        } else {
            console.warn("Modal button not found");
        }
        
        if (menu) {
    menu.href = '#member-section';
    menu.setAttribute('data-section', 'member-section');
        }
        
        if (label) {
    label.textContent = 'Member';
        }
        
        if (icon) {
    icon.classList.add('fa-user');
        }
        
        if (btnKlikLogin) {
    btnKlikLogin.classList.add('md:inline');
        }
        
    // Set active state for member section
        if (menu && window.location.hash === '#member-section') {
        menu.classList.add('text-pink-500');
        menu.classList.remove('text-gray-600');
        } else if (menu) {
        menu.classList.remove('text-pink-500');
        menu.classList.add('text-gray-600');
    }
        
    toogleLoginDropdown(false);
    };
    
init_fetch();
logoutUser("logoutBtn");
});
