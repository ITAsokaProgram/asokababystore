import * as api from "./member_api_service.js";
let currentPage = 1;
const LIMIT = 10;
let currentFilter = "";
let currentStatus = "";

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
  const tableContainer = document.getElementById("member-table-container");
  const paginationContainer = document.getElementById("pagination-container");
  if (tableContainer) {
    tableContainer.classList.toggle("hidden", !isShown);
  }
  if (paginationContainer) {
    paginationContainer.classList.toggle("hidden", !isShown);
  }
}
function renderMemberTable(members) {
  const tableBody = document.getElementById("member-table-body");
  if (!tableBody) return;
  tableBody.innerHTML = "";
  if (members.length === 0) {
    // --- Perbarui colspan dari 5 menjadi 6 ---
    tableBody.innerHTML = `<tr><td colspan="6" class="px-6 py-4 text-center text-gray-500">Tidak ada data member ditemukan untuk kriteria ini.</td></tr>`;
    return;
  }
  const formatter = new Intl.NumberFormat("id-ID");
  members.forEach((member, index) => {
    const rank = (currentPage - 1) * LIMIT + index + 1;
    // --- Perbarui 'row' untuk menambahkan sel poin ---
    const row = `
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${rank}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${
                  member.kd_cust
                }</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800">${
                  member.nama_cust
                }</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-bold">${formatter.format(
                  member.total_transactions
                )}</td>
                
                <td class="px-6 py-4 whitespace-nowrap text-sm text-blue-600 font-bold">${formatter.format(
                  member.total_poin_customer || 0
                )}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                    <a href="customer.php?filter=${encodeURIComponent(
                      currentFilter
                    )}&status=${encodeURIComponent(
      currentStatus
    )}&kd_cust=${encodeURIComponent(
      member.kd_cust
    )}&nama_cust=${encodeURIComponent(member.nama_cust)}"
                       class="btn-send-voucher bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded-md text-xs shadow-sm"
                       title="Lihat Detail Produk Customer">
                        <i class="fa-solid fa-eye"></i>
                    </a>
                </td>
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
  infoEl.innerHTML = `Menampilkan <strong>${startRecord}</strong>-<strong>${endRecord}</strong> dari <strong>${total_records}</strong> member`;
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
        loadTopMembers(currentFilter, currentStatus);
      }
    });
  });
}
async function loadTopMembers(filter, status) {
  showLoading(true);
  showError("");
  showTable(false);
  try {
    const result = await api.getTopMembersByFrequency(
      filter,
      status,
      LIMIT,
      currentPage
    );
    if (result.success === true && result.data) {
      renderMemberTable(result.data);
      renderPagination(result.pagination);
      showTable(true);
    } else {
      throw new Error(result.message || "Gagal memuat data member");
    }
  } catch (error) {
    console.error("Error loading top members:", error);
    showError(`Gagal memuat data: ${error.message}`);
  } finally {
    showLoading(false);
  }
}
document.addEventListener("DOMContentLoaded", () => {
  const params = new URLSearchParams(window.location.search);
  currentFilter = params.get("filter");
  currentStatus = params.get("status");
  if (currentFilter && currentStatus) {
    currentPage = 1;
    loadTopMembers(currentFilter, currentStatus);
  } else {
    console.error("Filter atau Status tidak ditemukan di URL.");
    showLoading(false);
    showError("Parameter filter atau status tidak valid.");
  }
});
