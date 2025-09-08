import { showLoginFormAfterRegister } from "../validation_ui/register_ui.js";

export const pubsRegister = async (email, password, name, phone) => {
    try {
        const response = await fetch("/src/auth/register_pubs", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
            },
            body: JSON.stringify({
                email: email,
                pass: password,
                name: name,
                phone: phone,
            }),
        });

        const data = await response.json();

        if (response.ok) {
            const notification = await Swal.fire({
                title: 'Pendaftaran Berhasil',
                text: 'Akun berhasil dibuat! Silakan login.',
                icon: 'success',
                confirmButtonColor: '#f87171'
            });
            if (notification.isConfirmed) {
                showLoginFormAfterRegister();
            }
            return { success: true, data };
        } else {
            console.error("Registration failed:", data);
            // Tampilkan notifikasi error jika Swal tersedia
            if (window.Swal) {
                Swal.fire({
                    icon: 'error',
                    title: 'Pendaftaran Gagal',
                    text: data.message,
                    confirmButtonColor: '#f87171'
                });
            }

            return { success: false, error: data };
        }

    } catch (error) {
        console.error("Network error:", error);

        if (window.Swal) {
            Swal.fire({
                icon: 'error',
                title: 'Kesalahan Jaringan',
                text: 'Tidak dapat terhubung ke server. Coba lagi nanti.',
                confirmButtonColor: '#f87171'
            });
        }

        return { success: false, error };
    }
};

export default { pubsRegister };
