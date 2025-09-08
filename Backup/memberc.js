function resetForm() {
    var formContainer = document.querySelector('.form-container');
    formContainer.innerHTML = `
        <form>
            <h3>Cek Poin Member</h3>
            <input type="text" name="memberNumber" class="box" placeholder="Masukkan kode customer Anda" required>
            <div id="errorMessage" style="color: red;"></div>
            <input type="button" class="btn" value="Cek Member" onclick="submitForm();">
            <p>Belum memiliki member? <a href="register.php">Daftar</a></p>
        </form>`;

    var input = document.querySelector('input[name="memberNumber"]');
    input.addEventListener('keypress', function(event) {
        if (event.keyCode === 13) {
            event.preventDefault(); // Hentikan submit form
            submitForm();
        }
    });
}

function submitForm() {
    var form = document.querySelector('.form-container form');
    var kd_cust = form.querySelector('input[name="memberNumber"]').value;
    var errorMessage = document.getElementById('errorMessage');
    errorMessage.textContent = ''; // Bersihkan pesan error sebelumnya

    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            var response = JSON.parse(this.responseText);
            if (response.status) {
                var userInfo = `
                    <h3>Detail Member</h3>
                    <label><span>Nomor :</span> <input type="text" class="box" value="${response.data.kd_cust}" disabled></label>
                    <label><span>Nama :</span> <input type="text" class="box" value="${response.data.nama_cust}" disabled></label>
                    <label><span>Poin:</span> <input type="text" class="box" value="${response.data.total_point}" disabled></label>
                    <input type="button" class="btn" value="Cek Member Lain" onclick="resetForm();">`;
                form.innerHTML = userInfo;
            } else {
                errorMessage.textContent = response.message;
            }
        }
    };

    xhttp.open("POST", "memberc?ajax=1", true);
    xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xhttp.send("memberNumber=" + kd_cust + "&ajax=1");
    return false;
}

// Panggil resetForm saat load halaman
resetForm()
