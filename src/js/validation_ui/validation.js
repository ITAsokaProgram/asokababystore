// Form validation functions
export function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

export function validatePassword(password) {
    return password.length >= 8 && password.length <= 16 &&/[A-Z]/.test(password) && /[0-9]/.test(password);
}

export function validateName(name) {
    return name.trim().length > 0;
}

export function validatePhone(phone) {
    // Remove any non-digit characters
    const cleanPhone = phone.replace(/\D/g, '');
    // Check if phone number is at least 10 digits

    return cleanPhone.length >= 10;
}

export function onlyNumberInput(placeholder, maxLength) {
    const input = document.querySelector(placeholder);
    input.addEventListener("input", () => {
        input.value = input.value.replace(/[^0-9]/g, "").slice(0, maxLength);
    });
}

export function showError(input, errorElement) {
    input.classList.add('border-red-500', 'animate-shake');
    errorElement.classList.remove('hidden');
}

export function hideError(input, errorElement) {
    input.classList.remove('border-red-500', 'animate-shake');
    errorElement.classList.add('hidden');
}

export default {
    validateEmail,
    validatePassword,
    validateName,
    validatePhone,
    showError,
    onlyNumberInput,
    hideError
};