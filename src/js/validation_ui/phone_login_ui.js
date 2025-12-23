import { validatePhone, showError, hideError, onlyNumberInput } from "./validation.js";

export const phoneLoginUI = () => {

    const phoneInput = document.querySelector("#numberPhoneLogin");
    const errorElement = document.querySelector("#numberPhoneError");

    // Only allow numbers in the phone input
    onlyNumberInput("#numberPhoneLogin", 13);

    // Validate phone number on input
    phoneInput.addEventListener("input", () => {
        if (validatePhone(phoneInput.value)) {
            hideError(phoneInput, errorElement);
        } else {
            showError(phoneInput, errorElement);
        }
    });
    return true;
}

export const switchPagePhoneLogin = () => {
    // const phoneLoginPage = document.querySelector("#number-phone");
    // const btnPhoneLogin = document.getElementById("hanphone-login");
    // const btnBackToEmailLogin = document.getElementById("show-login3");
    // const loginPage = document.querySelector("#login-form");
    // const registerPage = document.querySelector("#register-form");
    // const forgotPage = document.querySelector("#forgot-form");
    
    // btnPhoneLogin.onclick = () => {
    //     phoneLoginPage.classList.remove('hidden');
    //     loginPage.classList.add('hidden');
    //     registerPage.classList.add('hidden');
    //     forgotPage.classList.add('hidden');
    // }
    // btnBackToEmailLogin.onclick = () => {
    //     phoneLoginPage.classList.add('hidden');
    //     loginPage.classList.remove('hidden');
    //     registerPage.classList.add('hidden');
    //     forgotPage.classList.add('hidden');
    // }
}



export default { phoneLoginUI, switchPagePhoneLogin };