import { getCookie } from "/src/js/index/utils/cookies.js";
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('changeEmailForm');
    const submitBtn = document.getElementById('submitBtn');
    const btnText = document.getElementById('btnText');
    const btnLoading = document.getElementById('btnLoading');
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const newEmail = document.getElementById('newEmail').value;
        const currentPassword = document.getElementById('currentPassword').value;
        const token = getCookie('customer_token');
        if (!token) {
            Swal.fire({
                icon: 'error',
                title: 'Sesi Habis',
                text: 'Silakan login kembali.',
                confirmButtonColor: '#ec4899'
            }).then(() => {
                window.location.href = '/log_in.php';
            });
            return;
        }
        submitBtn.disabled = true;
        btnText.classList.add('hidden');
        btnLoading.classList.remove('hidden');
        try {
            const response = await fetch('/src/api/customer/account/update_email.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${token}`
                },
                body: JSON.stringify({
                    new_email: newEmail,
                    current_password: currentPassword
                })
            });
            const data = await response.json();
            if (!response.ok) {
                throw new Error(data.message || `HTTP error! status: ${response.status}`);
            }
            if (data.success) {
                await Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: data.message,
                    confirmButtonColor: '#ec4899',
                    allowOutsideClick: false,
                    customClass: {
                        popup: 'rounded-xl shadow-xl border-2 border-pink-100'
                    }
                });
                try {
                    await fetch('/src/api/user/logout_user_pubs.php');
                    window.location.href = '/log_in.php';
                } catch (logoutError) {
                    console.error("Gagal logout otomatis:", logoutError);
                    window.location.href = '/log_in.php';
                }
            } else {
                throw new Error(data.message || 'Gagal mengupdate email');
            }
        } catch (error) {
            console.error(error);
            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: error.message,
                confirmButtonColor: '#f87171',
                customClass: {
                    popup: 'rounded-xl shadow-xl border-2 border-pink-100'
                }
            });
        } finally {
            submitBtn.disabled = false;
            btnText.classList.remove('hidden');
            btnLoading.classList.add('hidden');
        }
    });
});