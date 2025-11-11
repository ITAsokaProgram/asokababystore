import * as api from "./member_api_service.js";
let currentPage = 1;
const LIMIT = 10;
let currentFilter = "";
let currentAgeGroup = "";
function showLoading(isLoading) {
  const spinner = document.getElementById("loading-spinner");
  if (spinner) {
    spinner.classList.toggle("hidden", !isLoading);
  }
}
function showError(message) {
  const errorEl = document.getElementById("error-message");
  if (errorEl) {
    if (message) {
      errorEl.textContent = message;
      errorEl.classList.remove("hidden");
    } else {
      errorEl.textContent = "";
      errorEl.classList.add("hidden");
    }
  }
}
function showTable(isShown) {
  const tableContainer = document.getElementById("product-table-container");
  const paginationContainer = document.getElementById("pagination-container");
  if (tableContainer) {
    tableContainer.classList.toggle("hidden", !isShown);
  }
  if (paginationContainer) {
    paginationContainer.classList.toggle("hidden", !isShown);
  }
}
function renderProductTable(products) {
  const tableBody = document.getElementById("product-table-body");
  if (!tableBody) return;
  tableBody.innerHTML = "";
  if (products.length === 0) {
    tableBody.innerHTML = `<tr><td colspan="4" class="px-6 py-4 text-center text-gray-500">Tidak ada data produk terlaris ditemukan untuk kriteria ini.</td></tr>`;
    return;
  }
  const formatter = new Intl.NumberFormat("id-ID");
  products.forEach((product, index) => {
    const rank = (currentPage - 1) * LIMIT + index + 1;
    const row = `
      <tr class="hover:bg-gray-50">
        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${rank}</td>
        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${
          product.plu
        }</td>
        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800">${
          product.descp
        }</td>
        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-bold">${formatter.format(
          product.total_qty
        )}</td>
      </tr>
    `;
    tableBody.innerHTML += row;
  });
}
function renderPagination(pagination) {
  const { total_records, current_page, limit, total_pages } = pagination;
  const infoEl = document.getElementById("pagination-info");
  const buttonsEl = document.getElementById("pagination-buttons");
  if (!infoEl || !buttonsEl) return;
  if (total_records === 0) {
    infoEl.innerHTML = "";
    buttonsEl.innerHTML = "";
    return;
  }
  const startRecord = (current_page - 1) * limit + 1;
  const endRecord = Math.min(current_page * limit, total_records);
  infoEl.innerHTML = `Menampilkan <strong>${startRecord}</strong>-<strong>${endRecord}</strong> dari <strong>${total_records}</strong> produk`;
  let buttonsHTML = "";
  buttonsHTML += `
    <button 
      class="pagination-btn ${current_page === 1 ? "" : ""}" 
      ${current_page === 1 ? "disabled" : ""}
      data-page="${current_page - 1}"
    >
      <i class="fa-solid fa-chevron-left"></i>
    </button>
  `;
  const maxPagesToShow = 5;
  let startPage = Math.max(1, current_page - Math.floor(maxPagesToShow / 2));
  let endPage = Math.min(total_pages, startPage + maxPagesToShow - 1);
  if (
    endPage - startPage + 1 < maxPagesToShow &&
    total_pages >= maxPagesToShow
  ) {
    startPage = Math.max(1, endPage - maxPagesToShow + 1);
  }
  if (startPage > 1) {
    buttonsHTML += `<button class="pagination-btn" data-page="1">1</button>`;
    if (startPage > 2) {
      buttonsHTML += `<span class="pagination-ellipsis">...</span>`;
    }
  }
  for (let i = startPage; i <= endPage; i++) {
    buttonsHTML += `
      <button 
        class="pagination-btn ${i === current_page ? "active" : ""}"
        data-page="${i}"
      >
        ${i}
      </button>
    `;
  }
  if (endPage < total_pages) {
    if (endPage < total_pages - 1) {
      buttonsHTML += `<span class="pagination-ellipsis">...</span>`;
    }
    buttonsHTML += `<button class="pagination-btn" data-page="${total_pages}">${total_pages}</button>`;
  }
  buttonsHTML += `
    <button 
      class="pagination-btn" 
      ${current_page === total_pages ? "disabled" : ""}
      data-page="${current_page + 1}"
    >
      <i class="fa-solid fa-chevron-right"></i>
    </button>
  `;
  buttonsEl.innerHTML = buttonsHTML;
  buttonsEl.querySelectorAll("button").forEach((button) => {
    button.addEventListener("click", () => {
      const page = parseInt(button.dataset.page);
      if (page !== currentPage) {
        currentPage = page;
        loadTopProducts(currentFilter, currentAgeGroup);
      }
    });
  });
}
async function loadTopProducts(filter, ageGroup) {
  showLoading(true);
  showError("");
  showTable(false);
  try {
    const result = await api.getTopProductsByAge(
      filter,
      ageGroup,
      currentPage,
      LIMIT
    );
    if (result.success === true && result.data) {
      renderProductTable(result.data);
      renderPagination(result.pagination);
      showTable(true);
    } else {
      throw new Error(result.message || "Gagal memuat data produk");
    }
  } catch (error) {
    console.error("Error loading top products:", error);
    showError(`Gagal memuat data: ${error.message}`);
  } finally {
    showLoading(false);
  }
}
document.addEventListener("DOMContentLoaded", () => {
  const params = new URLSearchParams(window.location.search);
  currentFilter = params.get("filter");
  currentAgeGroup = params.get("age_group");
  if (currentFilter && currentAgeGroup) {
    currentPage = 1;
    loadTopProducts(currentFilter, currentAgeGroup);
  } else {
    console.error("Filter atau Age Group tidak ditemukan di URL.");
    showLoading(false);
    showError("Parameter filter atau kelompok umur tidak valid.");
  }
});
