export const logoutUser = (idBtn) => {
    const btnLogout = document.getElementById(idBtn);
    btnLogout.addEventListener("click", (e) => {
        e.preventDefault();
        Swal.fire({
            title: 'Konfirmasi',
            text: "Apakah Anda yakin ingin keluar?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, keluar',
            cancelButtonText: 'Tidak'
        }).then((result) => {
            if (result.isConfirmed) {
                // Jika pengguna mengonfirmasi, panggil fungsi logout
                fetch('/src/api/user/logout_user_pubs')
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        // Hapus token dari cookie
                        document.cookie = 'token=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;';
                        Swal.fire({
                            title: 'Berhasil',
                            text: data.message,
                            icon: 'success',
                            confirmButtonText: 'OK'
                        }).then(() => {
                            window.location.href = '/';
                        });
                    } else {
                        Swal.fire({
                            title: 'Error',
                            text: data.message,
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    }
                });
            }
        });
    }
    );
}

export default logoutUser;