import { openModal,closeModalTerms } from "./modal_interaction.js";

export const checkTerms = () => {
    const checkSyarat = document.getElementById("syarat");
    const btnSetuju = document.getElementById("setuju");
    const btnTidakSetuju = document.getElementById("tidak-setuju");
    btnSetuju.addEventListener("click", function () {
        checkSyarat.checked = true;
        closeModalTerms("modalTerms", "modalContentTerms")
    })
    btnTidakSetuju.addEventListener("click", function () {
        checkSyarat.checked = false;
        closeModalTerms("modalTerms", "modalContentTerms")
    })
}
openModal("modalTerms", "modalContentTerms", "syarat")


export default checkTerms;