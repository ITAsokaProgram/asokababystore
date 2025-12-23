import { renderTableDefault } from "./render.js";

export const paginationMargin = (
  page = 1,
  limit = 10,
  sessionKey = "default_table"
) => {
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
      case "default_table":
        renderTableDefault(paginated, offset);
        break;

      case "detail_table":
        // renderFilterByTanggal(paginated, offset);
        break;
      default:
        renderTableDefault(paginated, offset);
    }
    // Simpan sumber aktif (penting untuk tombol pagination)
    sessionStorage.setItem("MarginPagination", sessionKey);
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
                    ${
                      active
                        ? "bg-blue-500 text-white"
                        : "bg-white text-gray-700"
                    }
                    ${
                      disabled
                        ? "opacity-50 cursor-not-allowed"
                        : "hover:bg-blue-100"
                    }
                `;
        if (onClick && !disabled) btn.addEventListener("click", onClick);
        return btn;
      };

      // Prev
      paginationContainer.appendChild(
        createBtn(
          "‹",
          () => paginationMargin(page - 1, limit, sessionKey),
          page === 1
        )
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
          createBtn(
            i,
            () => paginationMargin(i, limit, sessionKey),
            false,
            i === page
          )
        );
      }

      // Next
      paginationContainer.appendChild(
        createBtn(
          "›",
          () => paginationMargin(page + 1, limit, sessionKey),
          page === totalPages
        )
      );
    }
  } catch (err) {
    console.error("Gagal parsing sessionStorage:", err);
  }
};

export default { paginationMargin };
