document.getElementById("send").addEventListener("click", (e) => {
    e.preventDefault();
    const hp = document.getElementById('phone').value.trim();
    const name = document.getElementById('name').value.trim();
    const email = document.getElementById('email').value.trim();
    const subject = document.getElementById('subject').value.trim();
    const message = document.getElementById('message').value.trim();
    if (!hp || !name || !email || !subject || !message) {
        Swal.fire({
            icon: 'warning',
            title: 'Semua kolom wajib diisi!'
        });
        return;
    }
    const data = { hp, name, email, subject, message };
    fetch("/src/api/post_from_message", {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data),
    })
    .then(res => res.json())
    .then(response => {
        if (response.status.toLowerCase() === 'berhasil') {
            Swal.fire({
                icon: 'success',
                title: 'Pesan Berhasil Dikirim!',
                html: 'Ingin mendapatkan respon lebih cepat? <br> Silakan login untuk prioritas balasan.',
                showCancelButton: true,
                reverseButtons: true,
                confirmButtonText: `
                    <span class="flex items-center gap-2">
                        <i class="fas fa-sign-in-alt"></i>
                        Login Sekarang
                    </span>
                `,
                cancelButtonText: `
                    <span class="flex items-center gap-2">
                        <i class="fas fa-times"></i>
                        Tidak, Terima Kasih
                    </span>
                `,
                customClass: {
                    confirmButton: 'group relative inline-flex items-center justify-center px-6 py-3 text-base font-semibold text-white bg-gradient-to-r from-pink-500 to-purple-600 rounded-xl shadow-lg transform hover:scale-105 transition-all duration-300 btn-hover-effect',
                    cancelButton: 'group relative inline-flex items-center justify-center px-6 py-3 text-base font-semibold text-white bg-gray-500 hover:bg-gray-600 rounded-xl shadow-lg transform hover:scale-105 transition-all duration-300 btn-hover-effect'
                },
                confirmButtonColor: 'transparent',
                cancelButtonColor: 'transparent'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = "/log_in";
                } else {
                    window.location.href = "/";
                }
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Pesan Gagal Dikirim',
                text: response.message || 'Terjadi kesalahan, silakan coba lagi.'
            });
        }
    })
    .catch(error => {
        console.error(error);
        Swal.fire({
            icon: 'error',
            title: 'Pesan Gagal Dikirim',
            text: 'Tidak dapat terhubung ke server.'
        });
    });
});
function onlyNumberInput(placeholder, maxLength) {
    const input = document.querySelector(placeholder);
    input.addEventListener("input", () => {
        input.value = input.value.replace(/[^0-9]/g, "").slice(0, maxLength);
    });
}
function onlyAlphabetInput(placeholder, maxLength) {
    const input = document.querySelector(placeholder);
    input.addEventListener("input", () => {
        input.value = input.value.replace(/[^a-zA-Z\s]/g, "").slice(0, maxLength);
    });
}
function onlyAlphaAndNumberInput(placeholder, maxLength) {
    const input = document.querySelector(placeholder);
    input.addEventListener("input", () => {
        input.value = input.value.replace(/[^a-zA-Z0-9 ]/g, "").slice(0, maxLength);
    });
}
function validateEmailInput(placeholder) {
    const input = document.querySelector(placeholder);
    const errorMessage = document.createElement("small");
    errorMessage.textContent = "Format email belum benar";
    errorMessage.style.color = "red";
    errorMessage.style.display = "none";
    errorMessage.style.fontSize = "0.8rem";
    input.parentNode.insertBefore(errorMessage, input.nextSibling);
    input.addEventListener("input", () => {
        const email = input.value;
        const isValid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
        if (!isValid && email !== "") {
            input.style.borderColor = "red";
            errorMessage.style.display = "block"
        } else {
            input.style.borderColor = "";
            errorMessage.style.display = "none"
        }
    });
}
onlyNumberInput("#phone", 13);
onlyAlphabetInput("#name", 40);
onlyAlphaAndNumberInput("#subject", 40);
onlyAlphaAndNumberInput("#message", 1400)
validateEmailInput("#email");