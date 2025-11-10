import * as api from "./member_api_service.js";

document.addEventListener("DOMContentLoaded", () => {
  // BARU: Panggil fungsi untuk memuat summary member
  loadMemberSummaryData();
  loadProdukFavoritData();
  loadProdukTerlarisData();
});

function updatePlaceholder(id, value, isError = false) {
  const el = document.getElementById(id);
  if (el) {
    el.textContent = value;
    el.classList.remove("loading-placeholder", "text-red-500");
    if (isError) {
      el.classList.add("text-red-500");
    }
    // BARU: Hapus ikon spinner setelah loading selesai
    const spinner = el.querySelector(".fa-spinner");
    if (spinner) {
      spinner.remove();
    }
  }
}

// BARU: Fungsi untuk memuat data summary member
async function loadMemberSummaryData() {
  try {
    const result = await api.getMemberSummary();
    if (result.status === true) {
      updatePlaceholder("total-member-placeholder", result.data.total_member);
      updatePlaceholder("active-member-placeholder", result.data.active_member);
      updatePlaceholder(
        "non-active-member-placeholder",
        result.data.non_active_member
      );
    } else {
      throw new Error(result.message || "Gagal memuat summary");
    }
  } catch (error) {
    console.error("Error loading member summary:", error);
    updatePlaceholder("total-member-placeholder", "Gagal", true);
    updatePlaceholder("active-member-placeholder", "Gagal", true);
    updatePlaceholder("non-active-member-placeholder", "Gagal", true);
  }
}

async function loadProdukFavoritData() {
  try {
    const tren = await api.getTrendPembelian();
    updatePlaceholder(
      "tren-penjualan-placeholder",
      `${tren.data?.length || 0} Item`
    );
  } catch (error) {
    console.error("Error loading tren penjualan:", error);
    updatePlaceholder("tren-penjualan-placeholder", "Gagal", true);
  }

  try {
    const performa = await api.getProductPerforma();
    updatePlaceholder(
      "produk-terlaris-placeholder",
      `${performa.data?.length || 0} Item`
    );
  } catch (error) {
    console.error("Error loading produk performa:", error);
    updatePlaceholder("produk-terlaris-placeholder", "Gagal", true);
  }

  try {
    const favorit = await api.getProductFav();
    updatePlaceholder(
      "daftar-favorit-placeholder",
      `${favorit.data?.length || 0} Item`
    );
  } catch (error) {
    console.error("Error loading daftar favorit:", error);
    updatePlaceholder("daftar-favorit-placeholder", "Gagal", true);
  }
}

async function loadProdukTerlarisData() {
  try {
    const result = await api.getTransactionBranchDetail("all");
    const data = result.data;

    if (data.top_10_member && data.top_10_member.length > 0) {
      updatePlaceholder(
        "top-barang-member-placeholder",
        data.top_10_member[0].barang
      );
    } else {
      updatePlaceholder("top-barang-member-placeholder", "Data kosong");
    }

    if (data.top_10_non && data.top_10_non.length > 0) {
      updatePlaceholder(
        "top-barang-non-member-placeholder",
        data.top_10_non[0].barang
      );
    } else {
      updatePlaceholder("top-barang-non-member-placeholder", "Data kosong");
    }
  } catch (error) {
    console.error("Error loading produk terlaris:", error);
    updatePlaceholder("top-barang-member-placeholder", "Gagal", true);
    updatePlaceholder("top-barang-non-member-placeholder", "Gagal", true);
  }
}
