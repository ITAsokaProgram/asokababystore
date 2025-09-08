const SidebarCollapse = (() => {
    const init = () => {
        const toggleBtn = document.getElementById('toggle-hide');
        
        if (!toggleBtn) return;

        toggleBtn.addEventListener('click', () => {
            const sidebarTexts = document.querySelectorAll('.sidebar-text');
            const mainContent = document.getElementById('main-content');
            const sidebar = document.getElementById('sidebar');
            const icon = toggleBtn.querySelector('i');

            if (sidebar.classList.contains('w-64')) {
                // Collapse sidebar
                collapseSidebar(sidebar, sidebarTexts, mainContent, toggleBtn, icon);
            } else {
                // Expand sidebar
                expandSidebar(sidebar, sidebarTexts, mainContent, toggleBtn, icon);
            }
        });
    };

    const collapseSidebar = (sidebar, sidebarTexts, mainContent, toggleBtn, icon) => {
        sidebar.classList.remove('w-64', 'px-5');
        sidebar.classList.add('w-16', 'px-2');
        
        sidebarTexts.forEach(text => text.classList.add('hidden'));
        
        mainContent.classList.remove('ml-64');
        mainContent.classList.add('ml-16');
        
        toggleBtn.classList.add('left-20');
        toggleBtn.classList.remove('left-64');
        
        icon.classList.remove('fa-angle-left');
        icon.classList.add('fa-angle-right');
    };

    const expandSidebar = (sidebar, sidebarTexts, mainContent, toggleBtn, icon) => {
        sidebar.classList.remove('w-16', 'px-2');
        sidebar.classList.add('w-64', 'px-5');
        
        sidebarTexts.forEach(text => text.classList.remove('hidden'));
        
        mainContent.classList.remove('ml-16');
        mainContent.classList.add('ml-64');
        
        toggleBtn.classList.add('left-64');
        toggleBtn.classList.remove('left-20');
        
        icon.classList.remove('fa-angle-right');
        icon.classList.add('fa-angle-left');
    };

    return {
        init
    };
})();

export default SidebarCollapse; 