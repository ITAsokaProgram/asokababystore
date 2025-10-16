import {  switchPageForgot,validateForgotEmailForm,validateForgotHpForm } from "./forgot_ui.js";
import { validationUiLogin, switchPageLogin } from "./login_ui.js";
import { validateRegistrationForm, switchPageRegister } from "./register_ui.js";
import { phoneLoginUI, switchPagePhoneLogin } from "./phone_login_ui.js";
import { pubsLogin, pubsLoginByPhone } from "../auth/auth_login.js";
import { pubsRegister } from "../auth/auth_register.js";
import { googleLogin } from "../auth/auth_google.js";
import resetPassword from "../auth/auth_reset.js";
// Function to initialize the validation and page switch functionality
const initValidationUi = () => {
    // Initialize validation for the registration form
    const registerForm = document.querySelector("#register-form");
    if (registerForm) {
        registerForm.addEventListener("submit", (event) => {
            event.preventDefault();
            if (validateRegistrationForm()) {
                // Submit the form or perform any other action
                const name = document.getElementById('registerName').value;
                const phone = document.getElementById('registerPhone').value;
                const regisEmail = document.getElementById('registerEmail').value;
                const regisPassword = document.getElementById('registerPassword').value;
                pubsRegister(regisEmail, regisPassword, name, phone);
            }
        });
        switchPageRegister();
    }

    // Initialize validation for the login form
    const loginForm = document.querySelector("#login-form");
    if (loginForm) {
        loginForm.addEventListener("submit", (event) => {
            event.preventDefault();
            if (validationUiLogin()) {
                const identifier = document.getElementById('loginIdentifier').value; 
                const password = document.getElementById('loginPassword').value;
                pubsLogin(identifier, password); 
            }
        });
        switchPageLogin();
    }


    const forgotFormEmail = document.querySelector("#forgotFormEmail");
    const forgotFormHp = document.querySelector("#forgotFormHp");

    if (forgotFormEmail && forgotFormHp) {
        forgotFormEmail.addEventListener("submit", (event) => {
            event.preventDefault();
            if (validateForgotEmailForm()) { 
                const email = document.getElementById('forgotEmail').value;
                resetPassword({ method: 'email', value: email });
            }
        });
        forgotFormHp.addEventListener("submit", (event) => {
            event.preventDefault();
            if (validateForgotHpForm()) { 
                const noHp = document.getElementById('forgotHp').value;
                resetPassword({ method: 'hp', value: noHp });
            }
        });
        
        // Panggil fungsi switchPageForgot yang sudah dimodifikasi
        switchPageForgot();
    }
    // const phoneForm = document.querySelector("#numberPhoneForm");
    // if (phoneForm) {
    //     if(phoneLoginUI()){
    //         const valPhone = document.getElementById("numberPhoneLogin");
    //         phoneForm.addEventListener("submit", (event) => {
    //             event.preventDefault();
    //             pubsLoginByPhone(valPhone.value);
    //         });
    //     }
    //     switchPagePhoneLogin();
    // }
};

// Call the function to initialize validation and page switch functionality
initValidationUi();

googleLogin();
export { initValidationUi };

