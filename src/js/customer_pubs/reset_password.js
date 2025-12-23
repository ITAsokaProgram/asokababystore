const resetForm = document.getElementById('resetPasswordForm');
const token = new URLSearchParams(window.location.search).get('token');

if (!token) {
    Swal.fire({
        icon: 'error',
        title: 'Error',
        text: 'Sesi reset password sudah berakhir atau tidak valid.',
        showCancelButton: false,
        showConfirmButton: true,
        allowOutsideClick: false,
        allowEscapeKey: false,
        allowEnterKey: false,
        confirmButtonColor: '#3085d6',
        confirmButtonText: 'OK'
    }).then(() => {
        window.location.href = '/log_in';
    }
    );
    throw new Error('Token tidak ditemukan.');
}

fetch(`/src/api/forget_pass/check_token.php?token=${token}`)
    .then(async response => {
        const data = await response.json();

        // Tangani manual jika status 400 atau lainnya
        if (!response.ok || data.status !== 'success') {
            throw new Error(data.message);
        }

        // Token valid
        console.log('Token valid:', data);
        // lanjutkan proses

    })
    .catch(error => {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: error.message,
            confirmButtonColor: '#3085d6',
            confirmButtonText: 'OK'
        }).then(() => {
            window.location.href = '/log_in';
        });
    });

resetForm.addEventListener('submit', async (event) => {
    event.preventDefault();

    const newPassword = document.getElementById('newPassword').value;
    const confirmPassword = document.getElementById('confirmPassword').value;

    if (!token) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Tidak ada sesi reset password yang ditemukan.',
            confirmButtonText: 'OK'
        });
        return;
    }

    if (newPassword !== confirmPassword) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Konfirmasi password tidak cocok.',
            confirmButtonText: 'OK'
        });
        return;
    }
    const passwordRegex = /^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$/;

    if (!passwordRegex.test(newPassword)) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Password baru harus minimal 8 karakter dan mengandung huruf serta angka.',
            confirmButtonText: 'OK'
        });
        return;
    }

    const req = {
        token: token,
        newPassword: confirmPassword
    };

    try {
        const response = await fetch('/src/api/forget_pass/update_password', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(req)
        });

        const data = await response.json();

        if (response.ok && data.status === 'success') {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: 'Password berhasil diubah.',
                confirmButtonText: 'OK'
            }).then(() => {
                window.location.href = '/log_in';
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: data.message || 'Gagal mengubah password.',
                confirmButtonText: 'OK'
            });
        }
    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Terjadi kesalahan saat mengubah password atau tidak dapat menghubungi server.',
            confirmButtonText: 'OK'
        });
    }
});
