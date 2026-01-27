document.addEventListener('DOMContentLoaded', () => {
    const userData = JSON.parse(localStorage.getItem('supplier_data'));
    
    if(userData) {
        const nama = userData.nama || 'Supplier';
        const wilayah = userData.wilayah || 'Area Supplier'; 

        const elNama = document.getElementById('user-nama');
        const elRole = document.getElementById('user-role');
        const elNamaDrop = document.getElementById('user-nama-dropdown');
        const elRoleDrop = document.getElementById('user-role-dropdown');

        if(elNama) elNama.textContent = nama;
        if(elRole) elRole.textContent = wilayah;
        if(elNamaDrop) elNamaDrop.textContent = nama;
        if(elRoleDrop) elRoleDrop.textContent = wilayah;

        const avatarUrl = `https://ui-avatars.com/api/?name=${encodeURIComponent(nama)}&background=random`;
        const elHeaderAvatar = document.getElementById('header-avatar');
        const elDropAvatar = document.getElementById('dropdown-avatar');

        if(elHeaderAvatar) elHeaderAvatar.src = avatarUrl;
        if(elDropAvatar) elDropAvatar.src = avatarUrl;
    }

    const profileBtn = document.getElementById('profile-img');
    const profileCard = document.getElementById('profile-card');
    const chevron = document.getElementById('chevron-icon');

    if (profileBtn && profileCard) {
        profileBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            profileCard.classList.toggle('invisible');
            profileCard.classList.toggle('opacity-0');
            profileCard.classList.toggle('scale-95');
            
            if(chevron) chevron.classList.toggle('rotate-180');
        });

        document.addEventListener('click', (e) => {
            if (!profileBtn.contains(e.target) && !profileCard.contains(e.target)) {
                profileCard.classList.add('invisible', 'opacity-0', 'scale-95');
                if(chevron) chevron.classList.remove('rotate-180');
            }
        });
    }
});