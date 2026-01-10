document.addEventListener("DOMContentLoaded", async () => {
  const token = getCookie("admin_token");
  const currentPath = window.location.pathname;
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
        document.cookie = "token=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
      }
    }
    return;
  }
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

    if (result.data) {
      sessionStorage.setItem("userName", result.data.nama);
      sessionStorage.setItem("userKode", result.data.kode);
    }
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
function getCookie(name) {
  const value = `; ${document.cookie}`;
  const parts = value.split(`; ${name}=`);
  if (parts.length === 2) return parts.pop().split(";").shift();
  return null;
}
