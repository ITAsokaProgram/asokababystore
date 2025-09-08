export const passwordInspect = () => {
    const passwordInput = document.getElementById('registerPassword');
    const passwordError = document.getElementById('passwordError');
    const passwordStrengthWrapper = document.getElementById('passwordStrength');


    passwordInput.addEventListener('input', () => {
        const password = passwordInput.value;
        let strength = 0;

        if (password.length >= 8) strength++;
        if (/[A-Z]/.test(password)) strength++;
        if (/[a-z]/.test(password)) strength++;
        if (/\d/.test(password)) strength++;
        if (/[^A-Za-z0-9]/.test(password)) strength++;

        const bars = passwordStrengthWrapper.querySelectorAll('div');

        // Reset semua bar ke abu-abu
        bars.forEach(bar => {
            bar.className = 'h-2 flex-1 rounded bg-gray-300';
        });

        let activeClass = "";
        let activeCount = 0;

        if (strength <= 1) {
            activeClass = "bg-red-500";
            activeCount = 1;
        } else if (strength === 2 || strength === 3) {
            activeClass = "bg-yellow-400";
            activeCount = 2;
        } else if (strength >= 4) {
            activeClass = "bg-green-500";
            activeCount = 3;
        }

        for (let i = 0; i < activeCount; i++) {
            bars[i].classList.remove("bg-gray-300");
            bars[i].classList.add(activeClass);
        }


        // Error jika password masih lemah
        if (strength < 3 || password.length < 8) {
            passwordError.classList.remove('hidden');
            passwordStrengthWrapper.classList.remove('hidden');
        } else {
            passwordError.classList.add('hidden');
        }
    });
};


export default passwordInspect;