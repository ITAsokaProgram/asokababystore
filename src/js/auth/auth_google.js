import { closeLoadingScreen, showLoadingScreen } from "../validation_ui/loading_screen_login.js";

export const googleLogin = () => {
  // Tambahkan di awal fungsi googleLogin
  window.addEventListener('storage', function (e) {
    if (e.key === 'googleLoginResponse') {
      const data = JSON.parse(e.newValue);
      if (data && data.status === 'success') {
        closeLoadingScreen();
        if (window.Swal) {
          Swal.fire({
            icon: 'success',
            title: 'Login Google Berhasil',
            text: 'Selamat datang kembali!',
            timer: 1500,
            showConfirmButton: false
          }).then(() => {
            window.location.href = '/customer/home';
            sessionStorage.removeItem("googleLoginProsess")
          });
        }
      }
    } else if (data && data.status === 'error') {
      if (window.Swal) {
        Swal.fire({
          icon: 'error',
          title: 'Login Google Gagal',
          text: data.message
        });
      }
      sessionStorage.removeItem("googleLoginProsess")
    } else {
      if (window.Swal) {
        Swal.fire({
          icon: 'error',
          title: 'Login Google Gagal',
          text: 'Terjadi kesalahan saat login Google.'
        });
      }
      sessionStorage.removeItem("googleLoginProsess")
    }
  });

  // Handler pesan dari popup
  function messageHandler(event) {
    // Untuk sementara disable origin check saat testing
    // if (event.origin !== window.location.origin) return;

    const data = event.data;

    if (data && data.status === 'success') {
      if (window.Swal) {
        Swal.fire({
          icon: 'success',
          title: 'Login Google Berhasil',
          text: 'Selamat datang kembali!',
          timer: 1500,
          showConfirmButton: false
        }).then(() => {
          window.location.href = '/index';
          sessionStorage.removeItem("googleLoginProsess")
        });
      } else {
        window.location.href = '/index';
      }
      window.removeEventListener('message', messageHandler);
    } else if (data && data.status === 'error') {
      if (window.Swal) {
        Swal.fire({
          icon: 'error',
          title: 'Login Google Gagal',
          text: data.message || 'Terjadi kesalahan saat login Google.'
        });
      }
      window.removeEventListener('message', messageHandler);
      sessionStorage.removeItem("googleLoginProsess")
    }
  }

  // Pasang event listener message sekali saja
  window.addEventListener('message', messageHandler);
  let lastClickTime = 0;
  // Tombol login Google buka popup
  document.getElementById('google-login').onclick = () => {
      showLoadingScreen();

    if (sessionStorage.getItem("googleLoginProsess") === 'true') {
      Swal.fire({
        icon: 'warning',
        title: "Google Login",
        text: "Harap pindah ke tab google login untuk melanjutkan prosess loginnya",
        showConfirmButton: false
      })
      return;
    }
    const now = Date.now();

    if (now - lastClickTime < 3000) {  // batasi klik hanya 1x setiap 3 detik
      Swal.fire({
        icon: 'warning',
        title: 'Terlalu Banyak Klik',
        text: 'Anda terlalu banyak klik google login tunggu sebentar ...',
        showConfirmButton: false
      })
      return;
    }

    lastClickTime = now;
    const popup = window.open('/src/auth/google_login_pubs.php', 'googleLogin', 'width=500,height=600');
    const btn = document.getElementById("google-login");

    sessionStorage.setItem("googleLoginProsess", "true");


    const popupChecker = setInterval(() => {
      if (!popup || popup.closed) {
        clearInterval(popupChecker);
        sessionStorage.removeItem("googleLoginProsess")
        location.reload();
      }
    }, 500);
  };
};



window.addEventListener("DOMContentLoaded", () => {
  const nav = performance.getEntriesByType("navigation")[0];
  if (nav && nav.type === "reload") sessionStorage.removeItem("googleLoginProsess");
})

export default {googleLogin};
