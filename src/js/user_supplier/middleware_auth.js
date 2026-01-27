document.addEventListener("DOMContentLoaded", async () => {
  const token = getCookie("supplier_token");
  const currentPath = window.location.pathname;
  if (!token && !currentPath.includes("supplier_login.php")) {
    window.location.href = "/supplier_login.php";
    return;
  }
  if (token) {
    try {
      const response = await fetch("/src/api/user_supplier/verify_token.php", {
        method: "GET",
        headers: {
          Authorization: `Bearer ${token}`,
        },
      });
      const result = await response.json();
      if (result.status !== "success") {
        throw new Error("Sesi berakhir");
      } else {
        if(result.data) {
            localStorage.setItem("supplier_data", JSON.stringify(result.data));
        }
      }
    } catch (error) {
      console.error("Gagal verifikasi token supplier", error);
      document.cookie = "supplier_token=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
      localStorage.removeItem("supplier_token");
      localStorage.removeItem("supplier_data");
      await Swal.fire({
        icon: "warning",
        title: "Sesi Berakhir",
        text: "Token Anda sudah habis. Silakan login kembali.",
        confirmButtonColor: "#ec4899",
      }).then(() => {
        window.location.href = "/supplier_login.php";
      });
    }
  }
});
function getCookie(name) {
  const value = `; ${document.cookie}`;
  const parts = value.split(`; ${name}=`);
  if (parts.length === 2) return parts.pop().split(";").shift();
  return null;
}