import SidebarToggle from './sidebar-toggle.js';
import SidebarCollapse from './sidebar-collapse.js';
import ProfileDropdown from './profile-dropdown.js';

const NavbarMain = (() => {
    const init = () => {
        // Wait for DOM to be ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initializeModules);
        } else {
            initializeModules();
        }
    };

    const initializeModules = () => {
        // Initialize all navbar modules
        SidebarToggle.init();
        SidebarCollapse.init();
        ProfileDropdown.init();
    };

    return {
        init
    };
})();

// Auto-initialize when module is loaded
NavbarMain.init();

export default NavbarMain; 