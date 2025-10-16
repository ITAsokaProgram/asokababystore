// Ganti isi file auth_reset.js dengan kode ini
const showToast = (message, isSuccess = true) => {
    Swal.fire({
        icon: isSuccess ? 'success' : 'error',
        title: isSuccess ? 'Berhasil' : 'Gagal',
        text: message,
        timer: 3000,
        showConfirmButton: false,
        background: '#fff',
        customClass: {
            popup: 'rounded-xl shadow-xl border-2 border-pink-100',
            title: 'text-pink-600 font-bold',
            content: 'text-gray-600'
        }
    });
};

export const resetPassword = async (payload) => {
    let button, endpoint, body;

    if (payload.method === 'email') {
        button = document.getElementById('resetButtonEmail');
        endpoint = '/src/api/forget_pass/send_link_reset.php';
        body = JSON.stringify({ email: payload.value });
    } else if (payload.method === 'hp') {
        button = document.getElementById('resetButtonHp');
        endpoint = '/src/api/forget_pass/request_reset_link_wa.php'; 
        body = JSON.stringify({ no_hp: payload.value });
    } else {
        return;
    }
    
    const originalText = button.querySelector('.button-text').innerHTML;
    button.disabled = true;
    button.querySelector('.button-text').innerHTML = `<i class="fas fa-spinner fa-spin mr-2"></i> Memproses...`;

    try {
        const response = await fetch(endpoint, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: body,
        });

        const data = await response.json();

        if (!response.ok) {
            throw new Error(data.message || 'Terjadi kesalahan pada server.');
        }

        if (payload.method === 'email') {
            showToast('Link reset password telah dikirim ke email Anda.');
        } else if (payload.method === 'hp') {
            showToast('Anda akan diarahkan ke WhatsApp. Silakan kirim pesan yang telah disiapkan.');
            setTimeout(() => {
                window.location.href = data.data.whatsapp_url;
            }, 2000);
        }

    } catch (error) {
        showToast(error.message, false);
    } finally {
        button.disabled = false;
        button.querySelector('.button-text').innerHTML = originalText;
    }
}

export default resetPassword;