document.addEventListener("DOMContentLoaded", () => {
    const btn = document.getElementById('logout-btn');
    if (btn) {
        btn.addEventListener('click', async (e) => {
            e.preventDefault();
            
            Swal.fire({
                title: 'Keluar?',
                text: "Anda akan mengakhiri sesi ini.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#ec4899',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Ya, Keluar',
                cancelButtonText: 'Batal'
            }).then(async (result) => {
                if (result.isConfirmed) {
                    try {
                        const response = await fetch("/supplier_logout.php", {
                            method: "POST",
                        });

                        const data = await response.json();

                        if (data.status === 'success') {
                            // Hapus client side storage juga
                            localStorage.removeItem("supplier_token");
                            localStorage.removeItem("supplier_data");
                            
                            // Redirect
                            window.location.href = "/supplier_login.php";
                        }
                    } catch (error) {
                        console.error("Error saat logout:", error);
                    }
                }
            });
        });
    }
});