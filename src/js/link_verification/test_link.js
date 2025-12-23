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

// Fungsi untuk mengecek nomor HP
const checkPhoneNumberExists = async (noHp) => {
    try {
        const response = await fetch('/src/api/user/check_phone_api.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ no_hp: noHp })
        });
        if (!response.ok) {
            const errorData = await response.json();
            throw new Error(errorData.message || 'Gagal memeriksa nomor HP.');
        }
        const result = await response.json();
        return result.exists;
    } catch (error) {
        // Jika terjadi error, kita anggap pengecekan gagal dan lempar error
        throw error;
    }
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

        // 1. Validasi Format
        if (!/^08\d{8,11}$/.test(noHp)) {
            errorNoHp.textContent = 'Format nomor HP tidak valid (Contoh: 081234567890).';
            errorNoHp.classList.remove('hidden');
            return;
        }

        btnKirimLink.disabled = true;
        btnKirimLink.innerHTML = `<i class="fas fa-spinner fa-spin mr-2"></i> Memproses...`;

        try {
            // 2. Cek apakah nomor sudah terdaftar
            const isExists = await checkPhoneNumberExists(noHp);
            if (isExists) {
                throw new Error('Nomor HP ini sudah digunakan oleh akun lain.');
            }

            // 3. Jika aman, lanjutkan request link WA
            const response = await fetch('/src/api/user/request_link_api.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ no_hp: noHp })
            });

            const result = await response.json();

            if (!response.ok) {
                throw new Error(result.message || 'Gagal membuat link verifikasi.');
            }
            
            modalInputNoHp.classList.add('hidden');
            showToast('Anda akan diarahkan ke WhatsApp. Silakan kirim pesan yang telah disiapkan.', true);

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