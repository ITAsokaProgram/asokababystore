// src/js/account/internal/pagination.js

export const paginationUserInternal = (paginationMeta, onPageChange) => {
  const paginationContainer = document.getElementById("paginationContainer");
  const viewData = document.getElementById("viewData");

  const { current_page, total_pages, total_records, limit } = paginationMeta;

  // Update info teks
  if (viewData) {
    const start = (current_page - 1) * limit + 1;
    const end = Math.min(start + limit - 1, total_records);

    if (total_records === 0) {
      viewData.textContent = "Tidak ada data";
    } else {
      viewData.textContent = `Menampilkan ${start} - ${end} dari ${total_records} pengguna`;
    }
  }

  // Render Tombol Pagination
  if (paginationContainer) {
    paginationContainer.innerHTML = "";

    // Fungsi helper buat tombol
    const createBtn = (label, targetPage, disabled = false, active = false) => {
      const btn = document.createElement("button");
      btn.textContent = label;
      btn.disabled = disabled;
      btn.className = `
        px-3 py-1 mx-1 rounded-md border text-sm
        ${
          active
            ? "bg-blue-500 text-white border-blue-500"
            : "bg-white text-gray-700 hover:bg-gray-100"
        }
        ${disabled ? "opacity-50 cursor-not-allowed" : "cursor-pointer"}
      `;
      if (!disabled) {
        btn.addEventListener("click", () => onPageChange(targetPage));
      }
      return btn;
    };

    // Tombol Previous
    paginationContainer.appendChild(
      createBtn("‹", current_page - 1, current_page <= 1)
    );

    // Logika menampilkan nomor halaman (agar tidak terlalu panjang jika halaman ribuan)
    const maxButtons = 5;
    let startPage = Math.max(1, current_page - Math.floor(maxButtons / 2));
    let endPage = startPage + maxButtons - 1;

    if (endPage > total_pages) {
      endPage = total_pages;
      startPage = Math.max(1, endPage - maxButtons + 1);
    }

    for (let i = startPage; i <= endPage; i++) {
      paginationContainer.appendChild(
        createBtn(i, i, false, i === current_page)
      );
    }

    // Tombol Next
    paginationContainer.appendChild(
      createBtn("›", current_page + 1, current_page >= total_pages)
    );
  }
};
