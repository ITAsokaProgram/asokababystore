import el from "./dom.js";
import { getCookie } from "./../../index/utils/cookies.js";
import FilterHandler from "../handler/filterHandler.js";

export async function sendData() {
  const form = el.productForm;
  if (!form) {
    console.error("Form element not found");
    return;
  }

  const formData = new FormData(form);
  // Find submit button to disable during request
  const submitBtn = form.querySelector(
    'button[type="submit"], input[type="submit"]'
  );

  try {
    // Disable submit and show loading modal
    if (submitBtn) submitBtn.disabled = true;
    const loadingModal = Swal.fire({
      title: "Mengirim...",
      html: "Mohon tunggu, data sedang dikirim ke server.",
      allowOutsideClick: false,
      didOpen: () => {
        Swal.showLoading();
      },
    });

    const response = await fetch("/src/api/products/insert_products.php", {
      method: "POST",
      body: formData,
    });

    const data = await response.json();
    Swal.close();
    if (data.status === true) {
      // reset UI: tutup modal, reset form, reset preview
      if (el.modal) el.modal.classList.add("hidden");
      form.reset();
      if (el.preview) {
        el.preview.src = "";
        el.preview.classList.add("hidden");
      }
      if (el.uploadBtn) {
        el.uploadBtn.innerHTML = `
                    <i class="fa-solid fa-cloud-upload-alt text-3xl mb-2"></i>
                    <div class="text-sm">Klik untuk upload gambar</div>
                `;
      }

      // Refresh table after successful insert
      if (window.filterHandler) {
        try {
          await window.filterHandler.applyFilters();
        } catch (refreshError) {
          console.error("Failed to refresh table:", refreshError);
        }
      }
      Swal.fire({
        icon: "success",
        title: "Berhasil",
        text: "Produk berhasil disimpan!",
      });
    } else {
      console.error("Failed to insert product:", data.message);
      Swal.fire({
        icon: "error",
        title: "Gagal",
        text: data.message || "Produk gagal disimpan",
      });
    }
  } catch (error) {
    console.error("Error inserting product:", error);
    Swal.fire({
      icon: "error",
      title: "Error",
      text: "Terjadi kesalahan saat mengirim data",
    });
  } finally {
    // Re-enable submit button and close loading modal
    try {
      if (submitBtn) submitBtn.disabled = false;
    } catch (e) {
      // ignore
    }
  }
}

export const cabang = {
  getCabangData: async () => {
    try {
      const token = getCookie("admin_token");
      const response = await fetch("/src/api/cabang/get_kode", {
        method: "GET",
        headers: {
          Authorization: `Bearer ${token}`,
        },
      });

      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }

      const data = await response.json();
      return data;
    } catch (error) {
      console.error("Error in getCabangData:", error);
      throw error;
    }
  },
};

export const api = {
  getData: async (pageSize, offset) => {
    try {
      const response = await fetch(`/src/api/products/get_products?pageSize=${pageSize}&offset=${offset}`, {
        method: "GET",
        headers: {
          "Content-Type": "application/json",
        },
      });
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }
      const data = await response.json();
      return data;
    } catch (error) {
      console.error("Error fetching product data:", error);
      throw error;
    }
  },

  getProductDetail: async (id) => {
    try {
      const response = await fetch(
        `/src/api/products/get_product_detail?id=${id}`,
        {
          method: "GET",
          headers: {
            "Content-Type": "application/json",
          },
        }
      );
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }
      const data = await response.json();
      return data;
    } catch (error) {
      console.error("Error fetching product detail:", error);
      throw error;
    }
  },

  deleteProduct: async (id) => {
    try {
      const response = await fetch(
        `/src/api/products/delete_product?id=${id}`,
        {
          method: "DELETE",
          headers: {
            "Content-Type": "application/x-www-form-urlencoded",
          },
        }
      );
      const data = await response.json();
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }
      Swal.fire({
        icon: "success",
        title: "Berhasil",
        text: data.message || "Produk berhasil dihapus!",
      }).then(async () => {
        // Refresh table
        if (window.filterHandler) {
          try {
            await window.filterHandler.applyFilters();
          } catch (refreshError) {
            console.error(
              "Failed to refresh table after delete:",
              refreshError
            );
          }
        }
      });
      return data;
    } catch (error) {
      console.error("Error deleting product:", error);
      Swal.fire({
        icon: "error",
        title: "Error",
        text: "Terjadi kesalahan saat menghapus produk",
      });
      // swallow error after showing message to avoid unhandled rejections
      return;
    }
  },
  updateData: async () => {
    const form = el.productForm;
    if (!form) {
      console.error("Form element not found");
      return;
    }

    const formData = new FormData(form);

    // include product id if present
    if (el.productId && el.productId.value) {
      formData.append("id", el.productId.value);
    }

    try {
      const response = await fetch("/src/api/products/put_product.php", {
        method: "POST",
        body: formData,
      });

      const data = await response.json();

      if (data.success) {
        if (el.modal) el.modal.classList.add("hidden");
        form.reset();
        if (el.preview) {
          el.preview.src = "";
          el.preview.classList.add("hidden");
        }
        if (el.uploadBtn) {
          el.uploadBtn.innerHTML = `
          <i class="fa-solid fa-cloud-upload-alt text-3xl mb-2"></i>
          <div class="text-sm">Klik untuk upload gambar</div>
        `;
        }
        // Refresh table
        if (window.filterHandler) {
          try {
            await window.filterHandler.applyFilters();
          } catch (refreshError) {
            console.error(
              "Failed to refresh table after update:",
              refreshError
            );
          }
        }
        Swal.fire({
          icon: "success",
          title: "Berhasil",
          text: data.message || "Produk diperbarui",
        });
      } else {
        console.error("Failed to update product:", data.error || data.message);
        Swal.fire({
          icon: "error",
          title: "Gagal",
          text: data.error || data.message || "Gagal memperbarui produk",
        });
      }
    } catch (error) {
      console.error("Error updating product:", error);
      Swal.fire({
        icon: "error",
        title: "Error",
        text: "Terjadi kesalahan saat mengirim data",
      });
    }
  },

  filterData: async (filters = {}) => {
    try {
      const { page = 1, pageSize = 10, cabang = '', keyword = '' } = filters;
      
      const url = new URL('/src/api/products/filter_products.php', window.location.origin);
      url.searchParams.append('page', page);
      url.searchParams.append('pageSize', pageSize);
      
      if (cabang) {
        url.searchParams.append('cabang', cabang);
      }
      
      if (keyword) {
        url.searchParams.append('keyword', keyword);
      }

      const response = await fetch(url, {
        method: 'GET',
        headers: {
          'Content-Type': 'application/json'
        }
      });

      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }

      const data = await response.json();
      return data;
    } catch (error) {
      console.error('Error filtering products:', error);
      throw error;
    }
  }
};
