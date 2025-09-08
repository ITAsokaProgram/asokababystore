import { renderAllKategori, renderDetailAllKategori, renderFilterByTanggal } from "./all_kategori.js";

export const paginationKat = (page = 1, limit = 10, sessionKey = "kategori_invalid") => {
    const session = sessionStorage.getItem(sessionKey);
    const paginationContainer = document.getElementById("paginationContainer");
    const viewData = document.getElementById("viewData");

    if (!session) return;

    try {
        const stored = JSON.parse(session);
        const data = stored.data;

        if (!Array.isArray(data)) return;

        const total = data.length;
        const totalPages = Math.ceil(total / limit);
        const offset = (page - 1) * limit;
        const paginated = data.slice(offset, offset + limit);

        // render tabel
        switch (sessionKey) {
            case "kategori_invalid":
            case "kategori_search":
                renderAllKategori(paginated, offset);
                break;

            case "kategori_by_tanggal":
            case "kategori_search_tanggal":
                renderFilterByTanggal(paginated, offset);
                break;
            default:
                renderAllKategori(paginated, offset);
        }
        // Simpan sumber aktif (penting untuk tombol pagination)
        sessionStorage.setItem("kategori_source", sessionKey);
        // update info halaman
        if (viewData) {
            viewData.textContent = `Menampilkan halaman ${page} dari ${totalPages}`;
        }

        // render pagination
        if (paginationContainer) {
            paginationContainer.innerHTML = "";

            const createBtn = (label, onClick, disabled = false, active = false) => {
                const btn = document.createElement("button");
                btn.textContent = label;
                btn.disabled = disabled;
                btn.className = `
                    px-3 py-1 mx-1 rounded-md border text-sm
                    ${active ? 'bg-blue-500 text-white' : 'bg-white text-gray-700'}
                    ${disabled ? 'opacity-50 cursor-not-allowed' : 'hover:bg-blue-100'}
                `;
                if (onClick && !disabled) btn.addEventListener("click", onClick);
                return btn;
            };

            // Prev
            paginationContainer.appendChild(
                createBtn("‹", () => paginationKat(page - 1, limit, sessionKey), page === 1)
            );

            // Halaman numerik (max 5)
            const maxButtons = 10;
            let startPage = Math.max(1, page - Math.floor(maxButtons / 2));
            let endPage = startPage + maxButtons - 1;
            if (endPage > totalPages) {
                endPage = totalPages;
                startPage = Math.max(1, endPage - maxButtons + 1);
            }
            for (let i = startPage; i <= endPage; i++) {
                paginationContainer.appendChild(
                    createBtn(i, () => paginationKat(i, limit, sessionKey), false, i === page)
                );
            }

            // Next
            paginationContainer.appendChild(
                createBtn("›", () => paginationKat(page + 1, limit, sessionKey), page === totalPages)
            );
        }
    } catch (err) {
        console.error("Gagal parsing sessionStorage:", err);
    }
}
export const paginationDetail = (page = 1, limit = 10, sessionKey = "detail_kategori") => {
    const session = sessionStorage.getItem(sessionKey);
    const paginationContainer = document.getElementById("paginationContainerDetail");
    const viewData = document.getElementById("viewDataDetail");

    if (!session) return;

    try {
        const stored = JSON.parse(session);
        const data = stored.data;

        if (!Array.isArray(data)) return;

        const total = data.length;
        const totalPages = Math.ceil(total / limit);
        const offset = (page - 1) * limit;
        const paginated = data.slice(offset, offset + limit);
        renderDetailAllKategori(paginated,offset)

        // Simpan sumber aktif (penting untuk tombol pagination)
        sessionStorage.setItem("kategori_source", sessionKey);
        // update info halaman
        if (viewData) {
            viewData.textContent = `Menampilkan halaman ${page} dari ${totalPages}`;
        }

        // render pagination
        if (paginationContainer) {
            paginationContainer.innerHTML = "";

            const createBtn = (label, onClick, disabled = false, active = false) => {
                const btn = document.createElement("button");
                btn.textContent = label;
                btn.disabled = disabled;
                btn.className = `
                    px-3 py-1 mx-1 rounded-md border text-sm
                    ${active ? 'bg-blue-500 text-white' : 'bg-white text-gray-700'}
                    ${disabled ? 'opacity-50 cursor-not-allowed' : 'hover:bg-blue-100'}
                `;
                if (onClick && !disabled) btn.addEventListener("click", onClick);
                return btn;
            };

            // Prev
            paginationContainer.appendChild(
                createBtn("‹", () => paginationDetail(page - 1, limit, sessionKey), page === 1)
            );

            // Halaman numerik (max 5)
            const maxButtons = 10;
            let startPage = Math.max(1, page - Math.floor(maxButtons / 2));
            let endPage = startPage + maxButtons - 1;
            if (endPage > totalPages) {
                endPage = totalPages;
                startPage = Math.max(1, endPage - maxButtons + 1);
            }
            for (let i = startPage; i <= endPage; i++) {
                paginationContainer.appendChild(
                    createBtn(i, () => paginationDetail(i, limit, sessionKey), false, i === page)
                );
            }

            // Next
            paginationContainer.appendChild(
                createBtn("›", () => paginationDetail(page + 1, limit, sessionKey), page === totalPages)
            );
        }
    } catch (err) {
        console.error("Gagal parsing sessionStorage:", err);
    }
}
export default {paginationKat, paginationDetail}