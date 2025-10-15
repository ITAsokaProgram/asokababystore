import { validateEmail } from "./validation.js";

// Function to validate the forgot password form
export const validateForgotPasswordForm = () => {
    const emailInput = document.getElementById('forgotEmail');
    const emailError = document.getElementById('forgotEmailError');
    let isValid = true;

    // Validate email
    if (!validateEmail(emailInput.value)) {
        emailInput.classList.add('border-red-500', 'animate-shake');
        emailError.classList.remove('hidden');
        isValid = false;
    } else {
        emailInput.classList.remove('border-red-500', 'animate-shake');
        emailError.classList.add('hidden');
    }

    return isValid;
}

export const switchPageForgot = () => {
    const forgotPage = document.querySelector("#forgot-form");
    const loginPage = document.querySelector("#login-form");
    const registerPage = document.querySelector("#register-form");
    document.getElementById('show-forgot').onclick = () => {
      forgotPage.classList.remove('hidden');
      loginPage.classList.add('hidden');
      registerPage.classList.add('hidden');
    };
    document.getElementById('show-login2').onclick = () => {
        forgotPage.classList.add('hidden');
        loginPage.classList.remove('hidden');
        registerPage.classList.add('hidden');
    }
}

export default { validateForgotPasswordForm, switchPageForgot };