// Helper untuk menampilkan notifikasi (misalnya ToastifyJS)
const showToast = (message, isSuccess = true) => {
    Toastify({
        text: message,
        duration: 4000,
        gravity: "top",
        position: "center",
        backgroundColor: isSuccess ? "#10B981" : "#EF4444",
    }).showToast();
};

document.addEventListener('DOMContentLoaded', () => {
    const btnUbahNoHp = document.getElementById('btnUbahNoHp');
    const modalInputNoHp = document.getElementById('modalInputNoHp');
    const btnBatalNoHp = document.getElementById('btnBatalNoHp');
    const btnKirimLink = document.getElementById('btnKirimLink');
    const inputNoHp = document.getElementById('inputNoHp');
    const errorNoHp = document.getElementById('errorNoHp');

    btnUbahNoHp.addEventListener('click', () => {
        modalInputNoHp.classList.remove('hidden');
    });

    btnBatalNoHp.addEventListener('click', () => {
        modalInputNoHp.classList.add('hidden');
    });

    btnKirimLink.addEventListener('click', async () => {
        const noHp = inputNoHp.value.trim();
        errorNoHp.classList.add('hidden');

        if (!/^08\d{8,11}$/.test(noHp)) {
            errorNoHp.textContent = 'Format nomor HP tidak valid.';
            errorNoHp.classList.remove('hidden');
            return;
        }

        btnKirimLink.disabled = true;
        btnKirimLink.innerHTML = `<i class="fas fa-spinner fa-spin mr-2"></i> Memproses...`;

        try {
            const response = await fetch('/src/api/user/request_link_api.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' }, // Token otentikasi akan dihandle oleh cookie di backend
                body: JSON.stringify({ no_hp: noHp })
            });

            const result = await response.json();

            if (!response.ok) {
                throw new Error(result.message || 'Gagal membuat link verifikasi.');
            }
            
            // Sembunyikan modal dan tampilkan notifikasi
            modalInputNoHp.classList.add('hidden');
            showToast('Anda akan diarahkan ke WhatsApp. Silakan kirim pesan yang telah disiapkan.', true);

            // Redirect ke WhatsApp setelah beberapa saat
            setTimeout(() => {
                window.location.href = result.data.whatsapp_url;
            }, 2000);

        } catch (error) {
            showToast(error.message, false);
            errorNoHp.textContent = error.message;
            errorNoHp.classList.remove('hidden');
        } finally {
            btnKirimLink.disabled = false;
            btnKirimLink.innerHTML = `<i class="fab fa-whatsapp mr-2"></i> Lanjutkan ke WhatsApp`;
        }
    });
});