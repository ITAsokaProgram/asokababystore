import { validateEmail, validatePassword, showError, hideError } from "./validation.js";

export const validationUiLogin = () => {
    let isValid = true;
    const email = document.querySelector("#loginEmail");
    const password = document.querySelector("#loginPassword");

    if (!validateEmail(email.value)) {
        showError(email, document.querySelector("#emailError"));
        isValid = false;
    } else {
        hideError(email, document.querySelector("#emailError"));
    }
    if (!validatePassword(password.value)) {
        showError(password, document.querySelector("#passwordError"));
        isValid = false;
    } else {
        hideError(password, document.querySelector("#passwordError"));
    }
    return isValid;
}

export const switchPageLogin = () => {
    const loginPage = document.querySelector("#login-form");
    const registerPage = document.querySelector("#register-form");
    const forgotPage = document.querySelector("#forgot-form");
    document.getElementById('show-login').onclick = () => {
        loginPage.classList.remove('hidden');
        registerPage.classList.add('hidden');
        forgotPage.classList.add('hidden');
    };
}

export default { validationUiLogin, switchPageLogin };