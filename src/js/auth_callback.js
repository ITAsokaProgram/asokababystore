import { createClient } from "https://cdn.jsdelivr.net/npm/@supabase/supabase-js/+esm";
import { supabaseUrl, supabaseKey } from "/src/js/config.js";
const supabase = createClient(supabaseUrl, supabaseKey);
document.addEventListener("DOMContentLoaded", async () => {
    const { data: sessionData, error } = await supabase.auth.getSession();
    if (sessionData.session) {
        Swal.fire({
            title: "Berhasil Login Loading...",
            text: "Menyimpan data ke server...",
            icon: "success",
            showConfirmButton: false,
          });
        await fetch("https://asokababystore.com/src/api/google_login.php", {
            method: "POST",
            headers:
            {
                "Content-Type": "application/json",
            },
            body: JSON.stringify({
                access_token: sessionData.session.access_token,
                email: sessionData.session.user.email,
                name: sessionData.session.user.user_metadata.name,
                gprovider: sessionData.session.provider_token
            }),
        });
        // Setelah session berhasil didapat, barulah kita hapus query params
        setTimeout(() => {
            window.location.href = "/in_beranda";
          }, 1000);
    } else {
        Swal.fire({
            title: "Gagal login!",
            text: "Tidak bisa mendapatkan session. Silakan coba lagi.",
            icon: "error",
            confirmButtonText: "OK",
          }).then(() => {
            window.location.href = "/in_login";
          });
    }
});