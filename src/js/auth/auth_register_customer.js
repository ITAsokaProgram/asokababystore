// Asumsi Anda punya file loading_screen_login.js
import {
  showLoadingScreen,
  closeLoadingScreen,
} from "../validation_ui/loading_screen_login.js";

// Tampilkan SweetAlert
const showSwal = (icon, title, text, redirectUrl = null) => {
  Swal.fire({
    icon: icon,
    title: title,
    text: text,
    confirmButtonColor: "#ec4899",
  }).then(() => {
    if (redirectUrl) {
      window.location.href = redirectUrl;
    }
  });
};

export const registerCustomer = async (formData) => {
  showLoadingScreen();

  try {
    const response = await fetch("/src/auth/register_customer.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(formData),
    });

    const data = await response.json();
    closeLoadingScreen();

    if (response.ok && data.status === "success") {
      // Pendaftaran berhasil
      showSwal("success", "Pendaftaran Berhasil", data.message, "/log_in.php");
    } else {
      // Error dari server (misal: email duplicate, token expired, dll)
      showSwal("error", "Pendaftaran Gagal", data.message);
    }
  } catch (error) {
    closeLoadingScreen();
    showSwal(
      "error",
      "Koneksi Gagal",
      "Tidak dapat terhubung ke server. Silakan coba lagi."
    );
  }
};

export default { registerCustomer };
