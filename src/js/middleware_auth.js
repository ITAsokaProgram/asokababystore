document.addEventListener("DOMContentLoaded", async () => {
  const token = getCookie("token");
  const currentPath = window.location.pathname;

  // Kalau halaman login
  if (currentPath.includes("in_login")) {
    if (token) {
      try {
        const response = await fetch("/src/auth/verify_token.php", {
          method: "GET",
          headers: {
            Authorization: `Bearer ${token}`,
          },
        });

        const result = await response.json();
        if (result.status === "success") {
          window.location.href = "/in_beranda";
        }
      } catch (error) {
        console.error("Gagal verifikasi token", error);
        // Hapus token, biarkan user tetap di login
        document.cookie = "token=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
      }
    }
    return;
  }

  // Kalau di halaman selain login
  if (!token) {
    window.location.href = "/in_login";
    return;
  }

  try {
    const response = await fetch("/src/auth/verify_token.php", {
      method: "GET",
      headers: {
        Authorization: `Bearer ${token}`,
      },
    });

    const result = await response.json();
    sessionStorage.setItem("userName", result.data.nama);
    if (result.status !== "success") {
      document.cookie = "token=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
      await Swal.fire({
        icon: "warning",
        title: "Sesi Berakhir",
        text: "Token kamu sudah habis atau tidak valid. Silakan login kembali.",
        confirmButtonColor: "#ec4899",
      }).then(() => {
        window.location.href = "/in_login";
      });
    }
  } catch (error) {
    console.error("Gagal verifikasi token", error);
    document.cookie = "token=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
    await Swal.fire({
      icon: "error",
      title: "Kesalahan Jaringan",
      text: "Tidak bisa memverifikasi token. Silakan login ulang.",
      confirmButtonColor: "#ec4899",
    }).then(() => {
      window.location.href = "/in_login";
    });
  }
});

// Fungsi untuk ambil cookie
function getCookie(name) {
  const value = `; ${document.cookie}`;
  const parts = value.split(`; ${name}=`);
  if (parts.length === 2) return parts.pop().split(";").shift();
  return null;
}
