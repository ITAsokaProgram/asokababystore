import { validateEmail, validatePassword, validateName, validatePhone, onlyNumberInput, showError, hideError } from "./validation.js";
import { passwordInspect } from "./password_inspect.js";

// Function to validate the registration form
export const validateRegistrationForm = () => {
    const emailInput = document.getElementById('registerEmail');
    const passwordInput = document.getElementById('registerPassword');
    const nameInput = document.getElementById('registerName');
    const phoneInput = document.getElementById('registerPhone');

    const emailError = document.getElementById('registerEmailError');
    const passwordError = document.getElementById('registerPasswordError');
    const nameError = document.getElementById('nameError');
    const phoneError = document.getElementById('phoneError');
    let isValid = true;

    // Validate email
    if (!emailOnlyGmail(emailInput.value) ) {
        showError(emailInput, emailError);
        isValid = false;
    } else {
        hideError(emailInput, emailError);
    }

    // Validate password
    if (!validatePassword(passwordInput.value)) {
        showError(passwordInput, passwordError);
        isValid = false;
    } else {
        hideError(passwordInput, passwordError);
    }

    // Validate name
    if (!validateName(nameInput.value)) {
        showError(nameInput, nameError);
        isValid = false;
    } else {
        hideError(nameInput, nameError);
    }

    // Validate phone
    if (!validatePhone(phoneInput.value)) {
        showError(phoneInput, phoneError);
        isValid = false;
    } else {
        hideError(phoneInput, phoneError);
    }

    return isValid;
}
function emailOnlyGmail(email) {
    // Cek format email secara umum dan pastikan berakhiran @gmail.com
    const gmailRegex = /^[a-zA-Z0-9._%+-]+@gmail\.com$/;
    return gmailRegex.test(email);
}
export const switchPageRegister = () => {
    const registerPage = document.querySelector("#register-form");
    const registerForm = document.querySelector("#registerForm");
    const loginPage = document.querySelector("#login-form");
    const forgotPage = document.querySelector("#forgot-form");
    document.getElementById('show-register').onclick = () => {
        registerForm.reset();
        registerPage.classList.remove('hidden');
        loginPage.classList.add('hidden');
        forgotPage.classList.add('hidden');
    };
}

export const showLoginFormAfterRegister = () => {
    document.querySelector("#register-form").classList.add('hidden');
    document.querySelector("#login-form").classList.remove('hidden');
}

onlyNumberInput("#registerPhone", 13);

passwordInspect();
export default { validateRegistrationForm, switchPageRegister, showLoginFormAfterRegister };