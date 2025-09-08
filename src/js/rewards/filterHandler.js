class FilterHandler {
  constructor() {
    this.currentFilters = {
      branch: "",
      search: "",
      page: 1,
      pageSize: 10,
    };
    this.initializeFilters();
  }

  initializeFilters() {
    // Initialize search filter
    const searchInput = document.getElementById("filterSearch");
    if (searchInput) {
      let debounceTimer;
      searchInput.addEventListener("input", (e) => {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
          this.currentFilters.search = e.target.value.trim();
          this.currentFilters.page = 1; // Reset to first page on new search
          this.applyFilters();
        }, 300);
      });
    }

    // Initialize branch filter
    const branchSelect = document.getElementById("filterCabang");
    if (branchSelect) {
      branchSelect.addEventListener("change", (e) => {
        this.currentFilters.branch = e.target.value;
        this.currentFilters.page = 1; // Reset to first page on branch change
        this.applyFilters();
      });
    }
  }

  async applyFilters() {
    try {
      // Show loading state
      const tableBody = document.getElementById("tableBody");
      if (tableBody) {
        tableBody.innerHTML = `
                    <tr>
                        <td colspan="11" class="px-4 py-6 text-center text-gray-500">
                            <i class="fas fa-spinner fa-spin mr-2"></i>
                            Mencari data...
                        </td>
                    </tr>
                `;
      }

      const url = new URL(
        "/src/api/rewards/filter_reward.php",
        window.location.origin
      );
      url.searchParams.append("page", this.currentFilters.page);
      url.searchParams.append("pageSize", this.currentFilters.pageSize);

      if (this.currentFilters.branch) {
        url.searchParams.append("branch", this.currentFilters.branch);
      }

      if (this.currentFilters.search) {
        url.searchParams.append("search", this.currentFilters.search);
      }

      const response = await fetch(url, {
        method: "GET",
        headers: {
          Authorization: "Bearer " + localStorage.getItem("token"),
          "Content-Type": "application/json",
        },
      });

      if (!response.ok) {
        throw new Error("Network response was not ok");
      }

      const result = await response.json();

      if (result.success) {
        // Update global pagination variables
        window.currentPage = result.page;
        window.totalPages = result.totalPages;
        window.totalRecords = result.total;
        window.currentPageSize = result.pageSize;

        // Update table and pagination
        this.updateTable(result.data);
        this.updatePagination(result);
      } else {
        throw new Error(result.message || "Failed to fetch filtered data");
      }
    } catch (error) {
      console.error("Error applying filters:", error);
      // Show error message to user
      const tableBody = document.getElementById("tableBody");
      if (tableBody) {
        tableBody.innerHTML = `
                    <tr>
                        <td colspan="10" class="px-4 py-6 text-center text-red-500">
                            Error: ${error.message}
                        </td>
                    </tr>
                `;
      }
    }
  }

  updateTable(data) {
    const tableBody = document.getElementById("tableBody");
    if (!tableBody) return;

    tableBody.innerHTML = "";

    if (data.length === 0) {
      const searchTerm = this.currentFilters.search;
      const branchFilter = this.currentFilters.branch;
      let message = "Tidak ada data hadiah yang ditemukan";

      if (searchTerm && branchFilter) {
        message = `Tidak ada hadiah yang cocok dengan pencarian "${searchTerm}" di cabang yang dipilih`;
      } else if (searchTerm) {
        message = `Tidak ada hadiah yang cocok dengan pencarian "${searchTerm}"`;
      } else if (branchFilter) {
        message = "Tidak ada hadiah di cabang yang dipilih";
      }

      tableBody.innerHTML = `
                <tr>
                    <td colspan="11" class="px-4 py-6 text-center">
                        <div class="flex flex-col items-center gap-2">
                            <i class="fas fa-search text-gray-400 text-3xl mb-2"></i>
                            <p class="text-gray-500">${message}</p>
                            ${
                              searchTerm || branchFilter
                                ? `
                                <button onclick="window.location.reload()" 
                                        class="mt-2 text-pink-600 hover:text-pink-700 text-sm">
                                    <i class="fas fa-redo mr-1"></i> Reset Filter
                                </button>
                            `
                                : ""
                            }
                        </div>
                    </td>
                </tr>
            `;
      return;
    }

    data.forEach((reward, index) => {
      const row = document.createElement("tr");
      row.className = "hover:bg-amber-50 transition-colors duration-200";
      // Menghitung nomor urut berdasarkan halaman dan ukuran halaman
      const rowNumber =
        (this.currentFilters.page - 1) * this.currentFilters.pageSize +
        index +
        1;
      row.innerHTML = `
                    <td class="px-4 py-3 text-sm text-gray-700">${
                      rowNumber
                    }</td>
                    <td class="px-4 py-3 text-sm font-medium text-gray-900">${
                      reward.plu || "-"
                    }</td>
                    <td class="px-4 py-3 text-sm text-gray-900">${
                      reward.nama_hadiah || "-"
                    }</td>
                    <td class="px-4 py-3 text-sm text-center font-medium">
                        <span class="px-2 py-1 bg-amber-100 text-amber-800 rounded-full text-xs">
                            ${reward.poin || "0"}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-900">${
                      reward.nama_karyawan || "-"
                    }</td>
                    <td class="px-4 py-3 text-sm text-gray-700">${
                      reward.kode_karyawan || "-"
                    }</td>
                    <td class="px-4 py-3 text-sm text-gray-700 whitespace-nowrap">
                        ${this.formatDate(reward.tanggal_dibuat) || "-"}
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-700 whitespace-nowrap">
                        ${this.formatDate(reward.tanggal_diubah) || "-"}
                    </td>
                    <td class="px-4 py-3 text-sm text-center font-medium">
                        <span class="px-2 py-1 ${
                          reward.qty > 0
                            ? "bg-green-100 text-green-800"
                            : "bg-red-100 text-red-800"
                        } rounded-full text-xs">
                            ${reward.qty || "0"}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-700 truncate" title="${
                      reward.nm_alias || "-"
                    }">${this.formatAlias(reward.nm_alias) || "-"}</td>
                    <td class="px-4 py-3 text-sm text-center">
                        <div class="flex items-center justify-center space-x-2">
                            <button class="edit-btn text-blue-600 hover:text-blue-800 transition-colors p-1 rounded-full hover:bg-blue-50" 
                                    data-id="${reward.id_hadiah}" title="Edit">
                                <i class="fas fa-edit w-4 h-4"></i>
                            </button>
                            <button class="delete-btn text-red-600 hover:text-red-800 transition-colors p-1 rounded-full hover:bg-red-50" 
                                    data-id="${reward.id_hadiah}" title="Hapus">
                                <i class="fas fa-trash w-4 h-4"></i>
                            </button>
                        </div>
                    </td>
                `;
      tableBody.appendChild(row);
    });
  }

  formatAlias(alias) {
    if (!alias) return "-";
    const aliasArr = alias.split(",").map((item) => item.trim());
    if (aliasArr.length <= 3) return aliasArr.join(", ");
    return aliasArr.slice(0, 3).join(", ") + "...";
  }

  formatDate(dateString) {
    if (!dateString) return "";
    const options = {
      day: "2-digit",
      month: "2-digit",
      year: "numeric",
      hour: "2-digit",
      minute: "2-digit",
    };
    return new Date(dateString).toLocaleString("id-ID", options);
  }

  updatePagination(result) {
    const paginationContainer = document.getElementById("paginationContainer");
    if (!paginationContainer) return;

    // Update data info
    const dataInfo = document.getElementById("dataInfo");
    if (dataInfo) {
      const startItem = (result.page - 1) * result.pageSize + 1;
      const endItem = Math.min(result.page * result.pageSize, result.total);
      dataInfo.textContent = `Menampilkan ${startItem}-${endItem} dari ${result.total} hadiah`;
    }

    // Update page numbers
    const pageNumbers = document.getElementById("pageNumbers");
    if (pageNumbers) {
      pageNumbers.innerHTML = "";

      const maxPagesToShow = 5;
      let startPage = Math.max(1, result.page - Math.floor(maxPagesToShow / 2));
      let endPage = Math.min(result.totalPages, startPage + maxPagesToShow - 1);

      if (endPage - startPage + 1 < maxPagesToShow) {
        startPage = Math.max(1, endPage - maxPagesToShow + 1);
      }

      for (let i = startPage; i <= endPage; i++) {
        const pageBtn = document.createElement("button");
        pageBtn.textContent = i;
        pageBtn.className = `w-10 h-10 rounded-lg flex items-center justify-center ${
          i === result.page
            ? "bg-pink-600 text-white"
            : "text-pink-600 hover:bg-pink-50"
        }`;
        pageBtn.addEventListener("click", () => {
          this.currentFilters.page = i;
          this.applyFilters();
        });
        pageNumbers.appendChild(pageBtn);
      }
    }

    // Update navigation buttons
    const firstPageBtn = document.getElementById("firstPage");
    const prevPageBtn = document.getElementById("prevPage");
    const nextPageBtn = document.getElementById("nextPage");
    const lastPageBtn = document.getElementById("lastPage");

    if (firstPageBtn) firstPageBtn.disabled = result.page === 1;
    if (prevPageBtn) prevPageBtn.disabled = result.page === 1;
    if (nextPageBtn) nextPageBtn.disabled = result.page >= result.totalPages;
    if (lastPageBtn) lastPageBtn.disabled = result.page >= result.totalPages;
  }

  setPageSize(size) {
    this.currentFilters.pageSize = size;
    this.currentFilters.page = 1;
    this.applyFilters();
  }

    goToPage(page) {
        this.currentFilters.page = page;
        this.applyFilters();
    }

    // Cek apakah ada filter yang aktif
    hasActiveFilters() {
        return (
            this.currentFilters.search.trim() !== '' || 
            this.currentFilters.branch !== ''
        );
    }
}

export default FilterHandler;