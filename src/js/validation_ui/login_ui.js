import {
  validateEmail,
  validatePassword,
  validatePhone,
  showError,
  hideError,
} from "./validation.js";
export const validateIdentifier = () => {
  const identifier = document.querySelector("#loginIdentifier");
  const identifierError = document.querySelector("#identifierError");
  if (!validateEmail(identifier.value) && !validatePhone(identifier.value)) {
    showError(identifier, identifierError);
    return false;
  }
  hideError(identifier, identifierError);
  return true;
};
export const validateLoginPassword = () => {
  const password = document.querySelector("#loginPassword");
  const passwordError = document.querySelector("#passwordError");
  if (!validatePassword(password.value)) {
    showError(password, passwordError);
    return false;
  }
  hideError(password, passwordError);
  return true;
};
export const validationUiLogin = () => {
  let isValid = true;
  const identifier = document.querySelector("#loginIdentifier");
  const password = document.querySelector("#loginPassword");
  const identifierError = document.querySelector("#identifierError");
  if (!validateEmail(identifier.value) && !validatePhone(identifier.value)) {
    showError(identifier, identifierError);
    isValid = false;
  } else {
    hideError(identifier, identifierError);
  }
  if (!validatePassword(password.value)) {
    showError(password, document.querySelector("#passwordError"));
    isValid = false;
  } else {
    hideError(password, document.querySelector("#passwordError"));
  }
  return isValid;
};
export const switchPageLogin = () => {
  const loginPage = document.querySelector("#login-form");
  const registerPage = document.querySelector("#register-form");
  const forgotPage = document.querySelector("#forgot-form");
  document.getElementById("show-login").onclick = () => {
    loginPage.classList.remove("hidden");
    registerPage.classList.add("hidden");
    forgotPage.classList.add("hidden");
  };
  const forgotStep2Button = document.getElementById("show-forgot-step2");
  if (forgotStep2Button) {
    forgotStep2Button.onclick = () => {
      loginPage.classList.add("hidden");
      forgotPage.classList.remove("hidden");
    };
  }
  const oldForgotButton = document.getElementById("show-forgot");
  if (oldForgotButton) {
    oldForgotButton.onclick = () => {
      loginPage.classList.add("hidden");
      forgotPage.classList.remove("hidden");
    };
  }
};
export default {
  validationUiLogin,
  switchPageLogin,
  validateIdentifier,
  validateLoginPassword,
};
