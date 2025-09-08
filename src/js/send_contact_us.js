document.getElementById("send").addEventListener("click", (e) => {
    e.preventDefault();

    const hp = document.getElementById('phone').value.trim();
    const name = document.getElementById('name').value.trim();
    const email = document.getElementById('email').value.trim();
    const subject = document.getElementById('subject').value.trim();
    const message = document.getElementById('message').value.trim();
    
    // Validasi semua input wajib diisi
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
                title: 'Pesan Berhasil Dikirim'
            });
            setTimeout(() => {
                window.location.href = "/";
            }, 1000);
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Pesan Gagal Dikirim'
            });
        }
    })
    .catch(error => {
        console.error(error);
        Swal.fire({
            icon: 'error',
            title: 'Pesan Gagal Dikirim'
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
        input.value = input.value.replace(/[^a-zA-Z0-9 ]/g, "").slice(0, maxLength); // Tambahkan spasi di regex
    });
}
function validateEmailInput(placeholder) {
    const input = document.querySelector(placeholder);

    // Buat elemen error message
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
onlyAlphabetInput("#subject", 40);
onlyAlphaAndNumberInput("#message", 1400)
validateEmailInput("#email");