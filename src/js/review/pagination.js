export const renderPagination = (paginationData, callbackFetch) => {
    const paginationContainer = document.getElementById("paginationContainer");
    const viewData = document.getElementById("viewData");
    const { total_pages, current_page, total_records } = paginationData;
    const limit = 10; 
    if (viewData) {
        const startRecord = (current_page - 1) * limit + 1;
        const endRecord = Math.min(current_page * limit, total_records);
        viewData.innerHTML = `Menampilkan ${startRecord} - ${endRecord} dari ${total_records} data`;
    }
    if (paginationContainer) {
        paginationContainer.innerHTML = "";
        if (total_pages <= 1) return; 
        const createBtn = (label, targetPage, disabled = false, active = false) => {
            const btn = document.createElement("button");
            btn.innerHTML = label;
            btn.disabled = disabled;
        btn.className = `
            px-3 py-1 mx-1 rounded-md border text-sm
            ${active ? "bg-blue-500 text-white" : "bg-white text-gray-700"}
            ${disabled ? "opacity-50 cursor-not-allowed" : "hover:bg-blue-100"}
        `;            
        if (!disabled) {
                btn.addEventListener("click", () => callbackFetch(targetPage));
            }
            return btn;
        };
        paginationContainer.appendChild(createBtn("‹", current_page - 1, current_page === 1));
        for (let i = 1; i <= total_pages; i++) {
            paginationContainer.appendChild(createBtn(i, i, false, i === current_page));
        }
        paginationContainer.appendChild(createBtn("›", current_page + 1, current_page === total_pages));
    }
};
