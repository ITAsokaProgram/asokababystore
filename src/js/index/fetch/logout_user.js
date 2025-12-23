export const logoutUser = (idBtn) => {
    const btnLogout = document.getElementById(idBtn);
    if (!btnLogout) return;
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
                fetch('/src/api/user/logout_user_pubs')
                    .then(res => res.json())
                    .then(data => {
                        if (data.status === 'success') {
                            Swal.fire({
                                title: 'Berhasil',
                                text: "Anda telah berhasil logout.",
                                icon: 'success',
                                confirmButtonText: 'OK'
                            }).then(() => {
                                window.location.href = '/log_in';
                            });
                        } else {
                            Swal.fire({
                                title: 'Error',
                                text: data.message || 'Terjadi kesalahan saat logout.',
                                icon: 'error',
                                confirmButtonText: 'OK'
                            });
                        }
                    }).catch(err => {
                        console.error("Logout fetch error:", err);
                        Swal.fire('Error', 'Gagal menghubungi server. Silakan coba lagi.', 'error');
                    });
            }
        });
    });
}
export default logoutUser;