import { api } from "../services/api.js";
import { animationHandler } from "./animationHandler.js";

export const memberDataHandler = {
  currentPage: 1,
  totalPages: 1,
  currentData: [],
  filters: {
    status: "all",
    branch: "all",
    search: "",
  },

  // Initialize member data
  async initialize() {
    try {
      await this.loadMemberData();
      this.setupEventListeners();
    } catch (error) {
      console.error("Error initializing member data:", error);
      this.showError("Gagal memuat data member");
    }
  },

  // Load member data from API
  async loadMemberData(page = 1) {
    try {
      this.showLoading();

      const response = await api.getMemberData(page, 15);

      if (response.success) {
        // Pastikan data array ada dan tidak undefined
        if (!response.data || !Array.isArray(response.data)) {
          console.error("Invalid data format:", response);
          throw new Error("Data format tidak valid");
        }

        this.currentData = response.data; // Use data directly from Redis API
        this.currentPage = response.page || page;
        this.totalPages = response.total_pages || 1;

        this.renderMemberList();
        this.updatePagination();
        this.updateMemberCount(response.total || 0);
      } else {
        throw new Error("Failed to load member data");
      }
    } catch (error) {
      console.error("Error loading member data:", error);
      this.showError("Gagal memuat data member: " + error.message);
    } finally {
      this.hideLoading();
    }
  },

  // Render member list
  renderMemberList() {
    const container = document.getElementById("memberListContainer");
    if (!container) return;

    let dataToRender = this.currentData;

    // Kalau bukan mode search, baru pakai filterData
    if (!this.filters.search || this.filters.search.length < 3) {
      dataToRender = this.filterData();
    } else {
      container.innerHTML = this.currentData
        .map(
          (m) => `
    <div class="member-item bg-white rounded-xl p-4 border cursor-pointer"
         data-member-id="${m.kd_cust}">
      <h4 class="font-semibold text-gray-800">${m.nama_cust}</h4>
      <p class="text-sm text-gray-600">${m.kd_cust}</p>
    </div>
  `
        )
        .join("");
      return;
    }

    if (!dataToRender || dataToRender.length === 0) {
      container.innerHTML = this.renderEmptyState();
      return;
    }

    container.innerHTML = dataToRender
      .map((member) => this.renderMemberCard(member))
      .join("");

    this.attachCardEvents();
  },

  // Filter data based on current filters
  filterData() {
    let filtered = [...this.currentData];

    // Filter by status
    if (this.filters.status !== "all") {
      filtered = filtered.filter(
        (member) => member.status === this.filters.status
      );
    }

    // Filter by branch
    if (this.filters.branch !== "all") {
      filtered = filtered.filter((member) =>
        member.cabang.toLowerCase().includes(this.filters.branch.toLowerCase())
      );
    }

    // Filter by search
    if (this.filters.search) {
      const searchTerm = this.filters.search.toLowerCase();
      filtered = filtered.filter(
        (member) =>
          (member.nama_lengkap || member.nama || "")
            .toLowerCase()
            .includes(searchTerm) ||
          (member.alamat_email || member.email || "")
            .toLowerCase()
            .includes(searchTerm) ||
          (member.kode_member || member.nomor_hp || "")
            .toLowerCase()
            .includes(searchTerm) ||
          (member.nama_cabang || member.cabang || "")
            .toLowerCase()
            .includes(searchTerm)
      );
    }

    return filtered;
  },

  // Render member card
  renderMemberCard(member) {
    // Use the actual status from the API response
    const status = member.status_aktif;
    const statusClass = this.getStatusClass(status);
    const statusText = this.getStatusText(status);

    // Safe access to member data
    const memberName =
      member.nama_lengkap || member.nama || "Nama tidak tersedia";
    const memberCode = member.kode_member || member.id || "";
    const memberBranch =
      member.nama_cabang || member.cabang || "Cabang tidak tersedia";
    const memberPoints = member.total_poin || 0;

    return `
            <div class="member-item bg-white rounded-xl p-4 shadow-sm border border-gray-100 hover:shadow-md transition-all duration-200 cursor-pointer"
                 data-status="${status}" 
                 data-branch="${memberBranch}" 
                 data-member-id="${memberCode}"
                 data-member-data='${JSON.stringify(member)}'>
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 bg-gradient-to-r from-blue-500 to-purple-600 rounded-full flex items-center justify-center text-white font-bold text-lg">
                            ${this.getInitials(memberName)}
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-800">${memberName}</h4>
                            <p class="text-sm text-gray-600">${memberCode}</p>
                            <p class="text-xs text-gray-500">${memberBranch}</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <span class="status-badge ${statusClass}">${statusText}</span>
                        <p class="text-sm font-semibold text-blue-600 mt-1">${memberPoints.toLocaleString()} poin</p>
                    </div>
                </div>
            </div>
        `;
  },

  // Get initials from name
  getInitials(name) {
    if (!name) return "??";
    return name
      .split(" ")
      .map((word) => word.charAt(0))
      .join("")
      .toUpperCase()
      .substring(0, 2);
  },

  // Get status CSS class
  getStatusClass(status) {
    const statusClasses = {
      Aktif: "status-active",
      "Non-Aktif": "status-inactive",
      "Member Lama Non-Aktif": "status-pending",
    };
    return statusClasses[status] || "status-inactive";
  },

  // Get status text
  getStatusText(status) {
    const statusTexts = {
      Aktif: "Aktif",
      "Non-Aktif": "Non-Aktif",
      "Member Lama Non-Aktif": "Member Lama Non-Aktif",
    };
    return statusTexts[status] || "Non-Aktif";
  },

  // Render empty state
  renderEmptyState() {
    return `
            <div class="text-center py-12">
                <i class="fas fa-users text-gray-300 text-6xl mb-4"></i>
                <h3 class="text-lg font-medium text-gray-500 mb-2">Tidak ada member ditemukan</h3>
                <p class="text-sm text-gray-400">Coba ubah filter atau kata kunci pencarian</p>
            </div>
        `;
  },

  // Attach click events to member cards
  attachCardEvents() {
    const memberCards = document.querySelectorAll(".member-item");
    memberCards.forEach((card) => {
      card.addEventListener("click", (e) => {
        // Remove previous selection
        document.querySelectorAll(".member-item.selected").forEach((item) => {
          item.classList.remove("selected");
        });

        // Add selection to clicked card
        card.classList.add("selected");
      });
    });
  },

  // Update pagination
  updatePagination() {
    const paginationContainer = document.querySelector(".pagination-container");
    if (!paginationContainer) {
      console.warn("Pagination container not found");
      return;
    }

    const paginationHTML = this.renderPagination();
    paginationContainer.innerHTML = paginationHTML;
  },

  // Render pagination
  renderPagination() {

    if (this.totalPages <= 1) {
      return '<div class="text-sm text-gray-600">Hanya 1 halaman</div>';
    }

    const maxVisiblePages = 5;
    const startPage = Math.max(
      1,
      this.currentPage - Math.floor(maxVisiblePages / 2)
    );
    const endPage = Math.min(this.totalPages, startPage + maxVisiblePages - 1);

    let paginationHTML = `
            <!-- First Page -->
            <button ${this.currentPage === 1 ? "disabled" : ""} 
                    onclick="window.memberDataHandler.goToFirstPage()"
                    class="p-2 rounded-md hover:bg-blue-50 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200 text-blue-600"
                    title="Halaman Pertama">
                <i class="fas fa-angle-double-left"></i>
            </button>
            
            <!-- Previous Page -->
            <button ${this.currentPage === 1 ? "disabled" : ""} 
                    onclick="window.memberDataHandler.loadMemberData(${
                      this.currentPage - 1
                    })"
                    class="p-2 rounded-md hover:bg-blue-50 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200 text-blue-600"
                    title="Sebelumnya">
                <i class="fas fa-chevron-left"></i>
            </button>
        `;

    // Page numbers
    for (let i = startPage; i <= endPage; i++) {
      paginationHTML += `
                <button onclick="window.memberDataHandler.loadMemberData(${i})"
                        class="px-3 py-1 ${
                          i === this.currentPage
                            ? "bg-blue-500 text-white"
                            : "hover:bg-blue-50 text-blue-600"
                        } rounded-md cursor-pointer transition-all"
                        title="Halaman ${i}">
                    ${i}
                </button>
            `;
    }

    paginationHTML += `
            <!-- Next Page -->
            <button ${this.currentPage === this.totalPages ? "disabled" : ""} 
                    onclick="window.memberDataHandler.loadMemberData(${
                      this.currentPage + 1
                    })"
                    class="p-2 rounded-md hover:bg-blue-50 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200 text-blue-600"
                    title="Selanjutnya">
                <i class="fas fa-chevron-right"></i>
            </button>
            
            <!-- Last Page -->
            <button ${this.currentPage === this.totalPages ? "disabled" : ""} 
                    onclick="window.memberDataHandler.goToLastPage()"
                    class="p-2 rounded-md hover:bg-blue-50 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200 text-blue-600"
                    title="Halaman Terakhir">
                <i class="fas fa-angle-double-right"></i>
            </button>
        `;

    // Add page info
    paginationHTML += `
            <div class="ml-4 px-3 py-1 text-sm text-gray-600 bg-gray-100 rounded-md">
                ${this.currentPage} / ${this.totalPages}
            </div>
        `;

    return paginationHTML;
  },

  // Go to first page
  async goToFirstPage() {
    if (this.currentPage !== 1) {
      await this.loadMemberData(1);
    }
  },

  // Go to last page
  async goToLastPage() {
    if (this.currentPage !== this.totalPages) {
      await this.loadMemberData(this.totalPages);
    }
  },

  // Update member count
  updateMemberCount(total) {
    const memberCountElement = document.getElementById("memberCount");
    if (memberCountElement) {
      memberCountElement.textContent = total.toLocaleString();
    }
  },

  // Setup event listeners
  setupEventListeners() {
    // Search input
    const searchInput = document.getElementById("memberSearchInput");
    if (searchInput) {
      let searchTimeout;
      searchInput.addEventListener("input", async (e) => {
        const keyword = e.target.value.trim();
        this.filters.search = keyword;

        // Clear previous timeout
        if (searchTimeout) {
          clearTimeout(searchTimeout);
        }

        // Set new timeout for search
        searchTimeout = setTimeout(async () => {
          if (keyword === "" || keyword.length < 3) {
            // Jika kosong atau kurang dari 3 huruf → tampilkan data normal
            await this.loadMemberData(1);
            return;
          }

          try {
            this.showLoading();
            const response = await api.searchMember(keyword);

            if (response.success) {
              this.currentData = response.data;
              this.currentPage = response.page || 1;
              this.totalPages = response.total_pages || 1;

              this.renderMemberList();
              this.updatePagination();
              this.updateMemberCount(response.total || 0);
            } else {
              throw new Error("Gagal mencari member");
            }
          } catch (error) {
            console.error("Error searching members:", error);
            this.showError("Gagal mencari member: " + error.message);
          } finally {
            this.hideLoading();
          }
        }, 500); // Delay 500ms
      });
    }
  },

  // Show loading state
  showLoading() {
    const container = document.getElementById("memberListContainer");
    if (container) {
      container.innerHTML = `
                <div class="text-center py-12">
                    <i class="fas fa-spinner fa-spin text-blue-500 text-4xl mb-4"></i>
                    <p class="text-gray-600">Memuat data member...</p>
                </div>
            `;
    }
  },

  // Hide loading state
  hideLoading() {
    // Loading state will be replaced by actual data
  },

  // Show error message
  showError(message) {
    const container = document.getElementById("memberListContainer");
    if (container) {
      container.innerHTML = `
                <div class="text-center py-12">
                    <i class="fas fa-exclamation-triangle text-red-500 text-4xl mb-4"></i>
                    <h3 class="text-lg font-medium text-red-600 mb-2">Error</h3>
                    <p class="text-gray-600">${message}</p>
                    <button onclick="window.memberDataHandler.loadMemberData()" 
                            class="mt-4 px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-all">
                        Coba Lagi
                    </button>
                </div>
            `;
    }
  },
};

// Export to window object for global access
window.memberDataHandler = memberDataHandler;
