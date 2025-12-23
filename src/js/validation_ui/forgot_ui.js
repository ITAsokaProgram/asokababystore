import { validateEmail, validatePhone } from "./validation.js";

export const validateForgotEmailForm = () => {
    const emailInput = document.getElementById('forgotEmail');
    const emailError = document.getElementById('forgotEmailError');
    if (!validateEmail(emailInput.value)) {
        emailInput.classList.add('border-red-500');
        emailError.classList.remove('hidden');
        return false;
    }
    emailInput.classList.remove('border-red-500');
    emailError.classList.add('hidden');
    return true;
};

export const validateForgotHpForm = () => {
    const hpInput = document.getElementById('forgotHp');
    const hpError = document.getElementById('forgotHpError');
    if (!validatePhone(hpInput.value)) {
        hpInput.classList.add('border-red-500');
        hpError.classList.remove('hidden');
        return false;
    }
    hpInput.classList.remove('border-red-500');
    hpError.classList.add('hidden');
    return true;
};


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
    };

    const tabEmail = document.getElementById('tab-email');
    const tabHp = document.getElementById('tab-hp');
    const formEmail = document.getElementById('forgotFormEmail');
    const formHp = document.getElementById('forgotFormHp');

    tabEmail.addEventListener('click', () => {
        formEmail.classList.remove('hidden');
        formHp.classList.add('hidden');
        tabEmail.classList.add('text-pink-600', 'border-pink-600');
        tabEmail.classList.remove('text-gray-500');
        tabHp.classList.add('text-gray-500');
        tabHp.classList.remove('text-pink-600', 'border-pink-600');
    });

    tabHp.addEventListener('click', () => {
        formHp.classList.remove('hidden');
        formEmail.classList.add('hidden');
        tabHp.classList.add('text-pink-600', 'border-pink-600');
        tabHp.classList.remove('text-gray-500');
        tabEmail.classList.add('text-gray-500');
        tabEmail.classList.remove('text-pink-600', 'border-pink-600');
    });
};