import el from "../services/dom.js";
import { eventHandler } from "./eventHandler.js";
import { api } from "../services/api.js";
const tableHandler = {
  renderTable: function (data) {
    // Clear existing table content
    if (!el.tbody) {
      console.error("Table tbody element not found");
      return;
    }

    el.tbody.innerHTML = "";

    // Check if data exists and has products
    if (!data || !data.success || !data.data || !Array.isArray(data.data)) {
      this.showEmptyState();
      return;
    }

    const products = data.data;

    // Update count text
    if (el.countText) {
      el.countText.textContent = `${products.length} produk`;
    }

    // Render each product row
    products.forEach((product, index) => {
      const row = this.createProductRow(product, index + 1);
      el.tbody.appendChild(row);
    });
  },

  createProductRow: function (product, number) {
    const row = document.createElement("tr");
    row.className = "table-row hover:bg-gray-50 transition-colors";

    // Format price (assuming it exists in future API updates)
    const formatPrice = (price) => {
      if (!price) return "Rp 0";
      return new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
        minimumFractionDigits: 0,
      }).format(price);
    };

    // Format date
    const formatDate = (dateString) => {
      if (!dateString) return "-";
      try {
        const date = new Date(dateString);
        return date.toLocaleDateString("id-ID", {
          year: "numeric",
          month: "short",
          day: "numeric",
        });
      } catch {
        return dateString;
      }
    };

    // Generate stock badge (placeholder since not in current API)
    const getStockBadge = () => {
      return `<span class="status-badge stock-medium">${
        product.qty || 0
      }</span>`;
    };

    row.innerHTML = `
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">${number}</td>
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="w-12 h-12 rounded-lg overflow-hidden bg-gray-100 flex items-center justify-center">
                    ${
                      product.image_url
                        ? `<img src="${product.image_url}" alt="${
                            product.nama_produk || "Product"
                          }" class="w-full h-full object-cover">`
                        : `<i class="fa-solid fa-image text-gray-400"></i>`
                    }
                </div>
            </td>
            <td class="px-6 py-4">
                <div class="flex flex-col">
                    <div class="text-sm font-medium text-gray-900">${
                      product.nama_produk || "Nama Tidak Tersedia"
                    }</div>
                    <div class="text-sm text-gray-500 truncate max-w-xs" title="${
                      product.deskripsi || ""
                    }">${product.deskripsi || "Tidak ada deskripsi"}</div>
                    <div class="text-xs text-gray-400 mt-1">Ditambahkan: ${formatDate(
                      product.tanggal_upload
                    )}</div>
                </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                ${formatPrice(product.harga_jual || 0)}
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                ${getStockBadge()}
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <span class="px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded-full">
                    ${product.kategori || "Umum"}
                </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                ${product.cabang ?? "-"}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                <div class="flex items-center gap-2">
                    <button class="text-blue-600 hover:text-blue-900 transition-colors" data-id="${
                      product.id
                    }"  title="Edit">
                        <i class="fa-solid fa-pen-to-square"></i>
                    </button>
                    <button class="text-red-600 hover:text-red-900 transition-colors" data-id="${
                      product.id
                    }"  title="Hapus">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                    
                </div>
            </td>
        `;

    return row;
  },

  showEmptyState: function () {
    if (!el.tbody) return;

    el.tbody.innerHTML = `
            <tr>
                <td colspan="7" class="px-6 py-12 text-center">
                    <div class="flex flex-col items-center justify-center text-gray-500">
                        <i class="fa-solid fa-box-open text-4xl mb-4 text-gray-300"></i>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Belum ada produk</h3>
                        <p class="text-sm text-gray-500 mb-4">Mulai tambahkan produk pertama Anda</p>
                        <button onclick="document.getElementById('btnAdd').click()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                            <i class="fa-solid fa-plus mr-2"></i>
                            Tambah Produk
                        </button>
                    </div>
                </td>
            </tr>
        `;

    if (el.countText) {
      el.countText.textContent = "0 produk";
    }
  },
  bindRowEvents: function () {
    if (!el.tbody) return;

    el.tbody.addEventListener("click", (e) => {
      const btn = e.target.closest("button");
      if (!btn) return;

      const id = btn.dataset.id;
      const action = btn.title; // "Edit" | "Hapus" | "Lihat Detail"

      switch (action) {
        case "Edit":
          eventHandler.editData(id);
          break;
        case "Hapus":
          eventHandler.deleteData(id);
          break;
        case "Lihat Detail":
          eventHandler.viewData(id);
          break;
      }
    });
  },
};

// Make tableHandler globally accessible for onclick handlers
if (typeof window !== "undefined") {
  window.tableHandler = tableHandler;
}

export { tableHandler };
