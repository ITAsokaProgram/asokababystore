import { insertUser } from "./fetch.js";
const init = async () => {
    const form = document.getElementById("registrationForm");
    form.addEventListener("submit", async (e) => {
        const btn = document.getElementById('btnSubmit');
        const btnText = document.getElementById('btnSubmitText');
        const btnLoading = document.getElementById('btnSubmitLoading');
        btn.disabled = true;
        btnText.classList.add('hidden');
        btnLoading.classList.remove('hidden');
        e.preventDefault();
        const formData = new FormData(form);
        try {
            await insertUser(formData);
            btnLoading.classList.add('hidden');
            btnText.classList.remove('hidden');
            btn.disabled = false;
        } catch (error) {
            console.error("Error inserting user:", error);
            btnLoading.classList.add('hidden');
            btnText.classList.remove('hidden');
            btn.disabled = false;
        }
    });
}

init();