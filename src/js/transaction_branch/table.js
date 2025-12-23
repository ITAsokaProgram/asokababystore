export const paginationCard = (
  page = 1,
  limit = 10,
  data,
  callbackRender,
  view,
  container
) => {
  const paginationContainer = document.getElementById(container);
  const viewData = document.getElementById(view);
  const totalPages = Math.ceil(data.length / limit);
  const offset = (page - 1) * limit;
  const paginated = data.slice(offset, offset + limit);
  callbackRender(paginated, offset);
  if (viewData) {
    viewData.textContent = `Menampilkan halaman ${page} dari ${totalPages}`;
  }
  if (paginationContainer) {
    paginationContainer.innerHTML = "";
    const createBtn = (label, targetPage, disabled = false, active = false) => {
      const btn = document.createElement("button");
      btn.textContent = label;
      btn.disabled = disabled;
      btn.className = `
        px-3 py-1 mx-1 rounded-md border text-sm
        ${active ? "bg-blue-500 text-white" : "bg-white text-gray-700"}
        ${disabled ? "opacity-50 cursor-not-allowed" : "hover:bg-blue-100"}
      `;
      if (!disabled) {
        btn.addEventListener("click", () =>
          paginationCard(
            targetPage,
            limit,
            data,
            callbackRender,
            view,
            container
          )
        );
      }
      return btn;
    };
    paginationContainer.appendChild(createBtn("‹", page - 1, page === 1));
    const maxButtons = 5;
    let startPage = Math.max(1, page - Math.floor(maxButtons / 2));
    let endPage = startPage + maxButtons - 1;
    if (endPage > totalPages) {
      endPage = totalPages;
      startPage = Math.max(1, endPage - maxButtons + 1);
    }
    for (let i = startPage; i <= endPage; i++) {
      paginationContainer.appendChild(createBtn(i, i, false, i === page));
    }
    paginationContainer.appendChild(
      createBtn("›", page + 1, page === totalPages)
    );
  }
};
export default { paginationCard };
