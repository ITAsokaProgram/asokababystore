const ProfileDropdown = (() => {
    const init = () => {
        const profileImg = document.getElementById('profile-img');
        const profileCard = document.getElementById('profile-card');

        if (!profileImg || !profileCard) return;

        // Toggle profile dropdown on image click
        profileImg.addEventListener('click', (event) => {
            event.preventDefault();
            profileCard.classList.toggle('show');
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', (event) => {
            if (!profileCard.contains(event.target) && !profileImg.contains(event.target)) {
                profileCard.classList.remove('show');
            }
        });
    };

    return {
        init
    };
})();

export default ProfileDropdown; 