document.addEventListener("DOMContentLoaded", function () {
    resetForm(); // Panggil saat halaman dimuat
});

async function resetForm() {
    const formContainer = document.querySelector('.form-container');

    try {
        const csrf_token = await fetchCSRFToken();

        // Cek apakah form sudah ada, kalau belum baru buat
        let form = document.getElementById("memberForm");
        if (!form) {
            form = document.createElement('form');
            form.id = "memberForm";
            formContainer.appendChild(form);
        }

        // Perbarui isi form tanpa merusak elemen utama
        form.innerHTML = `
            <h3>Cek Poin Member</h3>
            <input type="number" name="memberNumber" class="box" placeholder="Masukkan kode customer Anda" required>
            <input type="hidden" id="csrf_token" value="${csrf_token}">
            <div id="errorMessage" style="color: red;"></div>
            <input type="submit" class="btn" value="Cek Member">
        `;

        form.addEventListener("submit", function (event) {
            event.preventDefault();
            submitForm();
        });
    } catch (error) {
        console.error("Gagal mengambil CSRF Token:", error);
    }
}

async function fetchCSRFToken() {
    try {
        const response = await fetch("member.php", { method: "GET", credentials: "same-origin" });
        if (!response.ok) throw new Error("Gagal mendapatkan CSRF token");

        const token = response.headers.get("X-CSRF-Token");
        if (token) {
            sessionStorage.setItem("csrf_token", token);
        } else {
            sessionStorage.removeItem("csrf_token");
        }
        return token;
    } catch (error) {
        console.error("Error mengambil CSRF Token:", error);
        return null;
    }
}

function validateMemberNumber(number) {
    return /^[0-9]{10,13}$/.test(number); // Hanya angka, panjang 10-13 digit
}


async function submitForm() {
    const form = document.getElementById("memberForm");
    const errorMessage = document.getElementById("errorMessage");
    const kd_cust = form.querySelector('input[name="memberNumber"]').value.trim();
    const csrf_token = sessionStorage.getItem("csrf_token");

    if (!csrf_token) {
        errorMessage.textContent = "Token tidak ditemukan!";
        return false;
    }

    if (!validateMemberNumber(kd_cust)) {
        errorMessage.textContent = "Kode Customer tidak valid!";
        return false;
    }

    errorMessage.textContent = '';

    try {
        const response = await fetch("member.php?ajax=1", {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded",
                "X-CSRF-Token": csrf_token
            },
            body: new URLSearchParams({
                memberNumber: kd_cust,
                csrf_token: csrf_token,
                ajax: "1"
            })
        });

        if (!response.ok) throw new Error("Gagal menghubungi server");

        const result = await response.json();
        if (result.status) {
<<<<<<< HEAD
=======
            console.log("response", result)
>>>>>>> master
            displayMemberInfo(result.data, result.updateRequired);
        } else {
            errorMessage.textContent = result.message;
        }
    } catch (error) {
        console.error("Error mengirim data:", error);
        errorMessage.textContent = "Terjadi kesalahan. Silakan coba lagi.";
    }
}

function displayMemberInfo(data, updateRequired) {
    const form = document.getElementById("memberForm");
    form.innerHTML = ''; // Kosongkan isi tanpa menghapus elemen utama

    const userInfo = document.createElement('div');
    userInfo.classList.add('member-info'); // Tambahkan class agar CSS tetap berlaku
    userInfo.innerHTML = `
        <h3>Detail Member</h3>
        <label><span>Nomor :</span> <input type="text" class="box" value="${data.kd_cust}" disabled></label>
        <label><span>Nama :</span> <input type="text" class="box" value="${data.nama_cust}" disabled></label>
        ${updateRequired ? `
            <p>Untuk melihat total poin Anda, Klik Tombol Lengkapi Data Diri</p>
            <a href="update_data.php?token=${data.token}" class="btn">Lengkapi Data Diri</a>

        ` : `
            <label><span>Poin:</span> <input type="text" class="box" value="${data.total_point}" disabled></label>
            <input type="button" class="btn" value="Cek Member Lain" onclick="resetForm();">
        `}
    `;

    form.appendChild(userInfo);

}
