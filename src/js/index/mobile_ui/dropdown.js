export const toogleLoginDropdown = (login, name = '') => {
    const isLoggedIn = login;
    const logginDropdown = document.getElementById('loginDropdown');
    const profileDropdown = document.getElementById('profileDropdown');
    const userBtn = document.getElementById('userButton');
    const userNameSpan = document.getElementById('userName');

    // Check if elements exist
    if (!logginDropdown || !profileDropdown || !userBtn) {
        return;
    }

    if (isLoggedIn) {
        logginDropdown.classList.add('hidden');
        if (userNameSpan) {
        userNameSpan.classList.remove('hidden');
        userNameSpan.textContent = name;
        }
    } else {
        // Ensure login dropdown is visible when not logged in
        logginDropdown.classList.add('hidden'); // Start hidden
        if (userNameSpan) {
            userNameSpan.classList.add('hidden');
        }
    }

    // Add click event for user name (when logged in)
    if (userNameSpan) {
    userNameSpan.addEventListener('click', (e) => {
        e.preventDefault();
            profileDropdown.classList.toggle('hidden');
            logginDropdown.classList.add('hidden');
        });
    }
    
    // Add click event for user button
    userBtn.addEventListener('click', (e) => {
        e.preventDefault();
        if (isLoggedIn) {
            profileDropdown.classList.toggle('hidden');
            logginDropdown.classList.add('hidden');
        } else {
            logginDropdown.classList.toggle('hidden');
            profileDropdown.classList.add('hidden');
        }
    });

    // Close dropdowns when clicking outside
    document.addEventListener('click', (e) => {
        const target = e.target;
        if (!userBtn.contains(target) && 
            !profileDropdown.contains(target) && 
            !(userNameSpan && userNameSpan.contains(target)) && 
            !logginDropdown.contains(target)) {
            profileDropdown.classList.add('hidden');
            logginDropdown.classList.add('hidden');
        }
    });
}
export default toogleLoginDropdown;
