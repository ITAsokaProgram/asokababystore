export const renderPagination = (total, page, limit, fetchCallback) => {
    const totalPages = Math.ceil(total / limit);
    const viewData = document.getElementById('viewData');
    const paginationContainer = document.getElementById('paginationContainer');
    viewData.textContent = `Menampilkan halaman ${page} dari ${totalPages}`;
    paginationContainer.innerHTML = "";

    const createButton = (text, disabled, onClick, isActive = false) => {
        const btn = document.createElement("button");
        btn.className = `px-3 py-1 rounded-lg border 
            ${disabled ? 'text-gray-400 cursor-not-allowed' : 'text-gray-700 hover:bg-gray-100'}
            ${isActive ? 'bg-blue-600 text-white' : ''}`;
        btn.disabled = disabled;
        btn.textContent = text;
        if (onClick && !disabled) btn.onclick = onClick;
        return btn;
    };

    // Tombol sebelumnya
    paginationContainer.appendChild(createButton("‹", page === 1, () => fetchCallback(page - 1, limit)));

    // Tombol angka (maksimal 10, sliding window)
    const maxButtons = 10;
    let start = Math.max(1, page - Math.floor(maxButtons / 2));
    let end = start + maxButtons - 1;
    if (end > totalPages) {
        end = totalPages;
        start = Math.max(1, end - maxButtons + 1);
    }

    for (let i = start; i <= end; i++) {
        const isActive = i === page;
        paginationContainer.appendChild(createButton(i, false, () => fetchCallback(i, limit), isActive));
    }

    // Tombol selanjutnya
    paginationContainer.appendChild(createButton("›", page === totalPages, () => fetchCallback(page + 1 , limit)));
};



export default renderPagination;
