// Import all handlers
import { tableHandler } from "./handlers/tableHandler.js";
import { summaryHandler } from "./handlers/summaryHandler.js";
import { modalHandler } from "./handlers/modalHandler.js";
import { formHandler } from "./handlers/formHandler.js";
import { animationHandler } from "./handlers/animationHandler.js";
import { utilityHandler } from "./handlers/utilityHandler.js";
import { memberDetailHandler } from "./handlers/memberDetailHandler.js";
import { memberDataHandler } from "./handlers/memberDataHandler.js";

let currentPage = 1;
let totalPages = 1;
let pageSize = 10;

const init = async () => {
  try {
    // Initialize all handlers
    await summaryHandler();
    setupPageSizeSelector();
    await loadAndRender(currentPage, pageSize);
    setupSearch();

    // Initialize animations
    animationHandler.addHoverAnimations();

  } catch (error) {
    console.error("Error initializing member management module:", error);
    animationHandler.showNotification("Gagal menginisialisasi modul", "error");
  }
};

async function loadAndRender(page, limit) {
  try {
    const { pagination } = await tableHandler(limit, page);
    if (pagination) {
      currentPage = pagination.page;
      totalPages = pagination.total_pages;
      pageSize = pagination.limit;
      renderPagination(pagination);
      updateDataInfo(pagination);

      // Animate table refresh
      animationHandler.refreshTableAnimation();
    }
  } catch (error) {
    console.error("Error loading and rendering data:", error);
    animationHandler.showNotification("Gagal memuat data", "error");
  }
}

function setupPageSizeSelector() {
  const pageSizeSelect = document.getElementById("pageSize");
  if (pageSizeSelect) {
    pageSizeSelect.innerHTML = `
            <option value="10">10 per halaman</option>
            <option value="25">25 per halaman</option>
            <option value="50">50 per halaman</option>
        `;
    pageSizeSelect.value = pageSize;
    pageSizeSelect.addEventListener("change", async (e) => {
      pageSize = parseInt(e.target.value);
      currentPage = 1;
      await loadAndRender(currentPage, pageSize);
    });
  }
}

function renderPagination(pagination) {
  const container = document.getElementById("paginationContainer");
  if (!container) return;
  container.innerHTML = "";

  // First & Prev
  container.appendChild(createPageBtn("firstPage", "<<", currentPage === 1));
  container.appendChild(createPageBtn("prevPage", "<", currentPage === 1));

  // Page numbers (show max 5)
  const pageNumbers = document.createElement("div");
  pageNumbers.className = "flex items-center gap-1 mx-2";
  let start = Math.max(1, currentPage - 2);
  let end = Math.min(totalPages, start + 4);
  if (end - start < 4) start = Math.max(1, end - 4);
  for (let i = start; i <= end; i++) {
    const btn = document.createElement("button");
    btn.className = `px-3 py-1 rounded-md ${
      i === currentPage
        ? "bg-blue-500 text-white font-bold"
        : "bg-white text-blue-600 hover:bg-blue-50"
    } transition-all`;
    btn.textContent = i;
    btn.disabled = i === currentPage;
    btn.addEventListener("click", async () => {
      currentPage = i;
      await loadAndRender(currentPage, pageSize);
    });
    pageNumbers.appendChild(btn);
  }
  container.appendChild(pageNumbers);

  // Next & Last
  container.appendChild(
    createPageBtn("nextPage", ">", currentPage === totalPages)
  );
  container.appendChild(
    createPageBtn("lastPage", ">>", currentPage === totalPages)
  );
}

function createPageBtn(id, label, disabled) {
  const btn = document.createElement("button");
  btn.id = id;
  btn.className =
    "p-2 rounded-md hover:bg-blue-50 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200";
  btn.textContent = label;
  btn.disabled = disabled;
  btn.addEventListener("click", async () => {
    if (id === "firstPage") currentPage = 1;
    else if (id === "prevPage") currentPage = Math.max(1, currentPage - 1);
    else if (id === "nextPage")
      currentPage = Math.min(totalPages, currentPage + 1);
    else if (id === "lastPage") currentPage = totalPages;
    await loadAndRender(currentPage, pageSize);
  });
  return btn;
}

function updateDataInfo(pagination) {
  const dataInfo = document.getElementById("dataInfo");
  if (dataInfo) {
    const showing = pagination.current_count || 0;
    dataInfo.textContent = `Menampilkan ${showing} dari ${pagination.total} data`;
  }
}

function setupSearch() {
  const searchInput = document.getElementById("searchInput");
  const clearSearch = document.getElementById("clearSearch");
  if (searchInput) {
    let searchTimeout;
    searchInput.addEventListener("input", (e) => {
      clearTimeout(searchTimeout);
      const searchTerm = e.target.value.toLowerCase();
      if (clearSearch) {
        if (searchTerm.length > 0) {
          clearSearch.classList.remove("hidden");
          clearSearch.innerHTML = '<i class="fas fa-times"></i>';
        } else {
          clearSearch.classList.add("hidden");
        }
      }
      searchTimeout = setTimeout(() => {
        filterTable(searchTerm);
      }, 300);
    });
  }
  if (clearSearch) {
    clearSearch.addEventListener("click", () => {
      searchInput.value = "";
      clearSearch.classList.add("hidden");
      filterTable("");
    });
  }
}

function filterTable(searchTerm) {
  const tableRows = document.querySelectorAll("#tableBody tr");
  tableRows.forEach((row) => {
    const text = row.textContent.toLowerCase();
    if (text.includes(searchTerm)) {
      row.style.display = "";
      row.classList.add("slide-in");
    } else {
      row.style.display = "none";
      row.classList.remove("slide-in");
    }
  });
}

// Global functions for external access
window.refreshMemberList = async () => {
  await loadAndRender(currentPage, pageSize);
};

window.goToPage = async (page) => {
  currentPage = page;
  await loadAndRender(currentPage, pageSize);
};

// Modal member management functions
window.showMemberManagement = async (filter = "all") => {
  // Show modal
  modalHandler.showModal("modalMemberManagement");

  // Initialize member data handler
  await memberDataHandler.initialize();

  // Apply filter if specified
  if (filter !== "all") {
    memberDataHandler.filters.status = filter;
    memberDataHandler.renderMemberList();
  }
};

window.closeMemberManagement = () => {
  modalHandler.closeModal("modalMemberManagement");
};

// Member action functions - delegate to appropriate handlers
window.editMember = (memberId) => {
  utilityHandler.editMember(memberId);
};

window.contactMember = (phoneNumber) => {
  const whatsappUrl = `https://wa.me/${phoneNumber.replace(/[^0-9]/g, "")}`;
  window.open(whatsappUrl, "_blank");
};

window.activateMember = async (memberId, memberName) => {
  // This should be handled by memberDetailHandler
  memberDetailHandler.activateMember(memberId);
};

window.deactivateMember = async (memberId, memberName) => {
  // This should be handled by memberDetailHandler
  memberDetailHandler.deactivateMember(memberId, memberName);
};

window.deleteMember = async (memberId, memberName) => {
  // Delegate to utilityHandler - avoid duplication
  utilityHandler.deleteMember(memberId, memberName);
};

// Initialize when DOM is ready
document.addEventListener("DOMContentLoaded", () => {
  init();
});
