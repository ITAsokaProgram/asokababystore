import {
  switchPageForgot,
  validateForgotEmailForm,
  validateForgotHpForm,
} from "./forgot_ui.js";
import {
  validateIdentifier,
  validateLoginPassword,
  switchPageLogin,
} from "./login_ui.js";
import { validateRegistrationForm, switchPageRegister } from "./register_ui.js";
import { phoneLoginUI, switchPagePhoneLogin } from "./phone_login_ui.js";
import { pubsLogin, pubsLoginByPhone } from "../auth/auth_login.js";
import { pubsRegister } from "../auth/auth_register.js";
import { googleLogin } from "../auth/auth_google.js";
import resetPassword from "../auth/auth_reset.js";
import {
  showLoadingScreen,
  closeLoadingScreen,
} from "./loading_screen_login.js";
const initValidationUi = () => {
  const forgotEmailFormHp = document.querySelector("#forgotEmailFormHp");
  if (forgotEmailFormHp) {
      forgotEmailFormHp.addEventListener("submit", async (e) => {
          e.preventDefault();
          const noHp = document.getElementById('forgotEmailHp').value;
          const newEmail = document.getElementById('newEmailReset').value;
          const button = document.getElementById('resetEmailButton');
          
          button.disabled = true;
          button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Memproses...';

          try {
              const response = await fetch('/src/api/forget_pass/request_change_email_wa.php', {
                  method: 'POST',
                  headers: { 'Content-Type': 'application/json' },
                  body: JSON.stringify({ no_hp: noHp, new_email: newEmail }),
              });
              const data = await response.json();
              if (data.success) {
                  window.location.href = data.whatsapp_url;
              } else {
                  Swal.fire('Gagal', data.message, 'error');
              }
          } catch (error) {
              Swal.fire('Error', 'Terjadi kesalahan jaringan', 'error');
          } finally {
              button.disabled = false;
              button.innerHTML = '<i class="fab fa-whatsapp mr-2"></i> Verifikasi via WhatsApp';
          }
      });
  }
  const registerForm = document.querySelector("#register-form");
  if (registerForm) {
    registerForm.addEventListener("submit", (event) => {
      event.preventDefault();
      if (validateRegistrationForm()) {
        const name = document.getElementById("registerName").value;
        const phone = document.getElementById("registerPhone").value;
        const regisEmail = document.getElementById("registerEmail").value;
        const regisPassword = document.getElementById("registerPassword").value;
        pubsRegister(regisEmail, regisPassword, name, phone);
      }
    });
    switchPageRegister();
  }
  const loginForm = document.querySelector("#loginForm");
  const loginSubmitButton = document.getElementById("loginSubmitButton");
  const identifierField =
    document.getElementById("loginIdentifier").parentElement.parentElement;
  const passwordStep = document.getElementById("password-step");
  const backButton = document.getElementById("back-to-identifier");
  let loginStep = 1;
  if (loginForm) {
    loginSubmitButton.addEventListener("click", async (event) => {
      event.preventDefault();
      console.log("TEST");
      if (loginStep === 1) {
        if (!validateIdentifier()) {
          return;
        }
        const identifier = document.getElementById("loginIdentifier").value;
        showLoadingScreen();
        try {
          const response = await fetch("/src/auth/login_step_1.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ identifier: identifier }),
          });
          const data = await response.json();
          closeLoadingScreen();
          if (data.status === "normal_login") {
            identifierField.classList.add("hidden");
            passwordStep.classList.remove("hidden");
            loginSubmitButton.innerHTML =
              '<i class="fas fa-sign-in-alt mr-2"></i>Masuk';
            loginStep = 2;
          } else if (data.status === "verify_customer") {
            await Swal.fire({
              icon: "info",
              title: "Verifikasi Nomor HP",
              text: "Nomor HP Anda terdaftar sebagai Member Asoka. Kami akan mengarahkan Anda ke WhatsApp untuk verifikasi dan melanjutkan pendaftaran akun.",
              confirmButtonText: "Lanjutkan ke WhatsApp",
              confirmButtonColor: "#ec4899",
            }).then((result) => {
              if (result.isConfirmed) {
                window.open(data.whatsapp_url, "_blank");
              }
            });
          } else {
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
        if (!validateLoginPassword()) {
          return;
        }
        const identifier = document.getElementById("loginIdentifier").value;
        const password = document.getElementById("loginPassword").value;
        pubsLogin(identifier, password);
      }
    });
    backButton.addEventListener("click", () => {
      identifierField.classList.remove("hidden");
      passwordStep.classList.add("hidden");
      loginSubmitButton.innerHTML =
        '<i class="fas fa-arrow-right mr-2"></i>Selanjutnya';
      loginStep = 1;
      document.getElementById("passwordError").classList.add("hidden");
      document
        .getElementById("loginPassword")
        .classList.remove("border-red-500");
    });
    switchPageLogin();
  }
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
    switchPageForgot();
  }
};
initValidationUi();
googleLogin();
export { initValidationUi };
