export const renderPagination = (paginationData, callbackFetch) => {
  const paginationContainer = document.getElementById("paginationContainer");
  const viewData = document.getElementById("viewData");
  if (!paginationData) return;
  const { total_pages, current_page, total_records } = paginationData;
  const limit = 10;
  if (viewData) {
    if (total_records === 0) {
      viewData.innerHTML = "Menampilkan 0 dari 0 data";
    } else {
      const startRecord = (current_page - 1) * limit + 1;
      const endRecord = Math.min(current_page * limit, total_records);
      viewData.innerHTML = `Menampilkan ${startRecord} - ${endRecord} dari ${total_records} data`;
    }
  }
  if (paginationContainer) {
    paginationContainer.innerHTML = "";
    if (total_pages <= 1) return;
    const createBtn = (label, targetPage, disabled = false, active = false) => {
      const btn = document.createElement("button");
      btn.innerHTML = label;
      btn.disabled = disabled;
      btn.className = `
                px-3 py-1 mx-1 rounded-md border text-sm transition-colors duration-200
                ${
                  active
                    ? "bg-orange-500 text-white border-orange-500 font-bold"
                    : "bg-white text-gray-700 hover:bg-orange-50"
                }
                ${
                  disabled
                    ? "opacity-50 cursor-not-allowed bg-gray-100"
                    : "cursor-pointer"
                }
            `;
      if (!disabled && !active) {
        btn.addEventListener("click", () => callbackFetch(targetPage));
      }
      return btn;
    };
    paginationContainer.appendChild(
      createBtn(
        '<i class="fas fa-chevron-left"></i>',
        current_page - 1,
        current_page === 1
      )
    );
    const pagesToShow = [];
    const maxAround = 2;
    for (let i = 1; i <= total_pages; i++) {
      if (
        i === 1 ||
        i === total_pages ||
        (i >= current_page - maxAround && i <= current_page + maxAround)
      ) {
        pagesToShow.push(i);
      }
    }
    let lastPage = 0;
    for (const pageNum of pagesToShow) {
      if (lastPage !== 0 && pageNum > lastPage + 1) {
        const ellipsis = document.createElement("span");
        ellipsis.className = "px-2 py-1 text-gray-400";
        ellipsis.textContent = "...";
        paginationContainer.appendChild(ellipsis);
      }
      paginationContainer.appendChild(
        createBtn(pageNum, pageNum, false, pageNum === current_page)
      );
      lastPage = pageNum;
    }
    paginationContainer.appendChild(
      createBtn(
        '<i class="fas fa-chevron-right"></i>',
        current_page + 1,
        current_page === total_pages
      )
    );
  }
};
