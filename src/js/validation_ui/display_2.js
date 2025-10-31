import {
  switchPageForgot,
  validateForgotEmailForm,
  validateForgotHpForm,
} from "./forgot_ui.js";
// MODIFIKASI: Import fungsi validasi baru
import {
  validateIdentifier,
  validateLoginPassword,
  switchPageLogin,
} from "./login_ui_2.js";
import { validateRegistrationForm, switchPageRegister } from "./register_ui.js";
import { phoneLoginUI, switchPagePhoneLogin } from "./phone_login_ui.js";
import { pubsLogin, pubsLoginByPhone } from "../auth/auth_login.js";
import { pubsRegister } from "../auth/auth_register.js";
import { googleLogin } from "../auth/auth_google.js";
import resetPassword from "../auth/auth_reset.js";
// TAMBAHAN: Import loading screen
import {
  showLoadingScreen,
  closeLoadingScreen,
} from "../validation_ui/loading_screen_login.js";

// Function to initialize the validation and page switch functionality
const initValidationUi = () => {
  // Initialize validation for the registration form
  const registerForm = document.querySelector("#register-form");
  if (registerForm) {
    registerForm.addEventListener("submit", (event) => {
      event.preventDefault();
      if (validateRegistrationForm()) {
        // Submit the form or perform any other action
        const name = document.getElementById("registerName").value;
        const phone = document.getElementById("registerPhone").value;
        const regisEmail = document.getElementById("registerEmail").value;
        const regisPassword = document.getElementById("registerPassword").value;
        pubsRegister(regisEmail, regisPassword, name, phone);
      }
    });
    switchPageRegister();
  }

  // ===== MODIFIKASI LOGIC LOGIN 2 STEP =====
  const loginForm = document.querySelector("#loginForm");
  const loginSubmitButton = document.getElementById("loginSubmitButton");
  const identifierField =
    document.getElementById("loginIdentifier").parentElement.parentElement; // div 'space-y-2'
  const passwordStep = document.getElementById("password-step");
  const backButton = document.getElementById("back-to-identifier");
  let loginStep = 1;

  if (loginForm) {
    loginSubmitButton.addEventListener("click", async (event) => {
      event.preventDefault();
      console.log("TEST");

      if (loginStep === 1) {
        // STEP 1: Validasi identifier dan cek ke backend
        if (!validateIdentifier()) {
          return;
        }

        const identifier = document.getElementById("loginIdentifier").value;
        showLoadingScreen();

        try {
          // Kita panggil endpoint backend baru untuk cek
          const response = await fetch("/src/auth/login_step_1.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ identifier: identifier }),
          });

          const data = await response.json();
          closeLoadingScreen();

          if (data.status === "normal_login") {
            // Lanjut ke step 2 (password)
            identifierField.classList.add("hidden");
            passwordStep.classList.remove("hidden");
            loginSubmitButton.innerHTML =
              '<i class="fas fa-sign-in-alt mr-2"></i>Masuk';
            loginStep = 2;
          } else if (data.status === "verify_customer") {
            // Alur verifikasi customer baru via WA
            await Swal.fire({
              icon: "info",
              title: "Verifikasi Nomor HP",
              text: "Nomor HP Anda terdaftar sebagai customer. Kami akan mengarahkan Anda ke WhatsApp untuk verifikasi dan melanjutkan pendaftaran akun.",
              confirmButtonText: "Lanjutkan ke WhatsApp",
              confirmButtonColor: "#ec4899",
            }).then((result) => {
              if (result.isConfirmed) {
                window.open(data.whatsapp_url, "_blank");
              }
            });
          } else {
            // Error dari backend
            Swal.fire({
              icon: "error",
              title: "Oops...",
              text: data.message || "Terjadi kesalahan.",
              confirmButtonColor: "#f87171",
            });
          }
        } catch (error) {
          closeLoadingScreen();
          Swal.fire({
            icon: "error",
            title: "Koneksi Gagal",
            text: "Tidak dapat terhubung ke server.",
            confirmButtonColor: "#f87171",
          });
        }
      } else if (loginStep === 2) {
        // STEP 2: Validasi password dan login
        if (!validateLoginPassword()) {
          return;
        }

        const identifier = document.getElementById("loginIdentifier").value;
        const password = document.getElementById("loginPassword").value;
        // Panggil fungsi login yang sudah ada
        pubsLogin(identifier, password);
      }
    });

    // Listener untuk tombol "Kembali"
    backButton.addEventListener("click", () => {
      identifierField.classList.remove("hidden");
      passwordStep.classList.add("hidden");
      loginSubmitButton.innerHTML =
        '<i class="fas fa-arrow-right mr-2"></i>Selanjutnya';
      loginStep = 1;
      // Sembunyikan error password jika ada
      document.getElementById("passwordError").classList.add("hidden");
      document
        .getElementById("loginPassword")
        .classList.remove("border-red-500");
    });

    // Panggil switchPageLogin untuk fungsionalitas tombol "Daftar" dan "Lupa Password"
    switchPageLogin();
  }
  // ===== AKHIR MODIFIKASI =====

  const forgotFormEmail = document.querySelector("#forgotFormEmail");
  const forgotFormHp = document.querySelector("#forgotFormHp");

  if (forgotFormEmail && forgotFormHp) {
    forgotFormEmail.addEventListener("submit", (event) => {
      event.preventDefault();
      if (validateForgotEmailForm()) {
        const email = document.getElementById("forgotEmail").value;
        resetPassword({ method: "email", value: email });
      }
    });
    forgotFormHp.addEventListener("submit", (event) => {
      event.preventDefault();
      if (validateForgotHpForm()) {
        const noHp = document.getElementById("forgotHp").value;
        resetPassword({ method: "hp", value: noHp });
      }
    });

    // Panggil fungsi switchPageForgot yang sudah dimodifikasi
    switchPageForgot();
  }
};

// Call the function to initialize validation and page switch functionality
initValidationUi();

googleLogin();
export { initValidationUi };
