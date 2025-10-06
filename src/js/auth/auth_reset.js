export const resetPassword = async (email) => {
    const button = document.getElementById('resetButton');
    const spinner = button.querySelector('.loading-spinner');
    const buttonText = button.querySelector('.button-text');
    // Show loading state
    spinner.classList.remove('hidden');
    buttonText.textContent = 'Mengirim...';
    button.disabled = true;
    try {
        const response = await fetch('/src/api/forget_pass/send_link_reset', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ email: email }),
        });

        const data = await response.json();
        if (response.ok && data.status === 'success') {
            await Swal.fire({
                icon: 'success',
                title: 'Permintaan Reset Password Berhasil',
                text: `Silakan cek email Anda untuk instruksi lebih lanjut.`,
                timer: 1000,
                showConfirmButton: false,
                background: '#fff',
                customClass: {
                    popup: 'rounded-xl shadow-xl border-2 border-pink-100',
                    title: 'text-pink-600 font-bold',
                    content: 'text-gray-600'
                }
            });
            return data;
        } else {
            console.error('Reset password request failed:', data);
            // Tampilkan notifikasi error jika Swal tersedia
            await Swal.fire({
                icon: 'error',
                title: 'Permintaan Reset Password Gagal',
                text: data.message || 'Terjadi kesalahan saat mengirim permintaan reset password.',
                confirmButtonColor: '#f87171',
                background: '#fff',
                customClass: {
                    popup: 'rounded-xl shadow-xl border-2 border-pink-100',
                    title: 'text-pink-600 font-bold',
                    content: 'text-gray-600'
                }
            });
            throw new Error(data.message || 'Reset password request failed');
        }

    } catch (error) {
        throw error;
    } finally {
        // Reset loading state
        spinner.classList.add('hidden');
        buttonText.textContent = 'Kirim Link Reset';
        button.disabled = false;
    }
}

export default resetPassword;