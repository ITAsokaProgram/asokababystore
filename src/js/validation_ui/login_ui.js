import { validateEmail, validatePassword, validatePhone, showError, hideError } from "./validation.js";

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