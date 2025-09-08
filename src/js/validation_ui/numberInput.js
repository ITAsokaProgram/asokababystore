export const validateNIK = () => {
    const kodeNik = document.getElementById("no_nik").value;
    const nikError = document.getElementById("nik-error");

    if (kodeNik.length < 16) {
        nikError.classList.remove("hidden");
        document.getElementById("send_data").disabled = true;
    } else {
        nikError.classList.add("hidden");
        document.getElementById("send_data").disabled = false;
    }
}

export const onlyNumberInput = (placeholder, maxLength) => {
    const input = document.querySelector(placeholder);
    input.addEventListener("input", () => {
        input.value = input.value.replace(/[^0-9]/g, "").slice(0, maxLength);
    });
}

export default {validateNIK, onlyNumberInput};