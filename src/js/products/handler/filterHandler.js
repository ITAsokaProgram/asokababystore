import el from "../services/dom.js";
import { cabangHandler } from "./cabangHandler.js";
import { api } from "../services/api.js";

class FilterHandler {
  constructor() {
    this.currentFilters = {
      page: 1,
      pageSize: 10,
      cabang: "",
      keyword: "",
    };
    this.initializeFilters();
  }

  initializeFilters() {
    // Initialize search filter
    if (el.filterSearch) {
      let debounceTimer;
      el.filterSearch.addEventListener("input", (e) => {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
          this.currentFilters.keyword = e.target.value.trim();
          this.currentFilters.page = 1; // Reset ke halaman pertama saat search
          this.applyFilters();
        }, 300);
      });
    }

    // Initialize cabang filter
    if (el.filterCabang) {
      el.filterCabang.addEventListener("change", (e) => {
        this.currentFilters.cabang = e.target.value;
        this.currentFilters.page = 1; // Reset ke halaman pertama saat ganti cabang
        this.applyFilters();
      });
    }
  }

  async applyFilters() {
    try {
      // Show loading state
      if (el.tbody) {
        el.tbody.innerHTML = `
                    <tr>
                        <td colspan="8" class="px-4 py-6 text-center text-gray-500">
                            <i class="fas fa-spinner fa-spin mr-2"></i>
                            Mencari data...
                        </td>
                    </tr>
                `;
      }

      const result = await api.filterData(this.currentFilters);

      if (result.success) {
        this.updateTable(result.data);
        this.updatePagination(result);
      } else {
        throw new Error(result.message || "Failed to fetch filtered data");
      }
    } catch (error) {
      console.error("Error applying filters:", error);
      if (el.tbody) {
        el.tbody.innerHTML = `
                    <tr>
                        <td colspan="8" class="px-4 py-6 text-center text-red-500">
                            Error: ${error.message}
                        </td>
                    </tr>
                `;
      }
    }
  }

  updateTable(data) {
    if (!el.tbody) return;

    el.tbody.innerHTML = "";

    if (data.length === 0) {
      const keyword = this.currentFilters.keyword;
      const cabang = this.currentFilters.cabang;
      let message = "Tidak ada data produk yang ditemukan";

      if (keyword && cabang) {
        message = `Tidak ada produk yang cocok dengan pencarian "${keyword}" di cabang yang dipilih`;
      } else if (keyword) {
        message = `Tidak ada produk yang cocok dengan pencarian "${keyword}"`;
      } else if (cabang) {
        message = "Tidak ada produk di cabang yang dipilih";
      }

      el.tbody.innerHTML = `
                <tr>
                    <td colspan="8" class="px-4 py-6 text-center">
                        <div class="flex flex-col items-center gap-2">
                            <i class="fas fa-search text-gray-400 text-3xl mb-2"></i>
                            <p class="text-gray-500">${message}</p>
                            ${
                              keyword || cabang
                                ? `
                                <button onclick="window.location.reload()" 
                                        class="mt-2 text-blue-600 hover:text-blue-700 text-sm">
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

    data.forEach((product, index) => {
      const row = document.createElement("tr");
      row.className = "hover:bg-gray-50 transition-colors duration-200";
      const rowNumber =
        (this.currentFilters.page - 1) * this.currentFilters.pageSize +
        index +
        1;

      row.innerHTML = `
                <td class="px-4 py-3">${rowNumber}</td>
                <td class="px-4 py-3">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 h-10 w-10">
                            <img class="h-10 w-10 rounded-full" src="${
                              product.image_url || "/path/to/default/image.jpg"
                            }" alt="${product.nama_produk}">
                        </div>
                        <div class="ml-4">
                            <div class="text-sm font-medium text-gray-900">${
                              product.nama_produk
                            }</div>
                            <div class="text-sm text-gray-500">${
                              product.barcode || "-"
                            }</div>
                        </div>
                    </div>
                </td>
                <td class="px-4 py-3">${new Intl.NumberFormat("id-ID", {
                  style: "currency",
                  currency: "IDR",
                }).format(product.harga_jual || 0)}</td>
                <td class="px-4 py-3">${product.qty || 0}</td>
                <td class="px-4 py-3">${product.kategori || "-"}</td>
                <td class="px-4 py-3">${new Date(
                  product.tanggal_upload
                ).toLocaleDateString("id-ID", {
                  day: "2-digit",
                  month: "2-digit",
                  year: "numeric",
                })}</td>
                <td class="px-4 py-3">${product.cabang || "-"}</td>
                <td class="px-4 py-3">
                    <div class="flex gap-2">
                        <button  data-id="${product.id}"
                                class="p-2 text-blue-600 hover:text-blue-800">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button data-id="${product.id}"
                                class="p-2 text-red-600 hover:text-red-800">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            `;
      el.tbody.appendChild(row);
    });
  }

  updatePagination(result) {
    // Update total count
    if (el.countText) {
      el.countText.textContent = `Total: ${result.total} produk`;
    }

    // Update page info
    if (el.pageText) {
      const startItem = (result.page - 1) * result.pageSize + 1;
      const endItem = Math.min(result.page * result.pageSize, result.total);
      el.pageText.textContent = `Menampilkan ${startItem}-${endItem} dari ${result.total}`;
    }

    // Update navigation buttons
    if (el.prevBtn) {
      el.prevBtn.disabled = result.page <= 1;
    }
    if (el.nextBtn) {
      const maxPage = Math.ceil(result.total / result.pageSize);
      el.nextBtn.disabled = result.page >= maxPage;
    }
  }

  setPageSize(size) {
    this.currentFilters.pageSize = size;
    this.currentFilters.page = 1;
    this.applyFilters();
  }

  nextPage() {
    this.currentFilters.page++;
    this.applyFilters();
  }

  previousPage() {
    if (this.currentFilters.page > 1) {
      this.currentFilters.page--;
      this.applyFilters();
    }
  }

  hasActiveFilters() {
    return (
      this.currentFilters.keyword.trim() !== "" ||
      this.currentFilters.cabang !== ""
    );
  }
}

export default FilterHandler;
