import * as api from "./member_api_service.js";

document.addEventListener("DOMContentLoaded", () => {
  loadMemberSummaryData();
  loadProdukFavoritData();
  loadProdukTerlarisData();

  loadMemberPoinData();
  loadSebaranMemberData();
});

function updatePlaceholder(id, value, isError = false) {
  const el = document.getElementById(id);
  if (el) {
    el.textContent = value;
    el.classList.remove("loading-placeholder", "text-red-500");
    if (isError) {
      el.classList.add("text-red-500");
    }
    const spinner = el.querySelector(".fa-spinner");
    if (spinner) {
      spinner.remove();
    }
  }
}

async function loadMemberPoinData() {
  const placeholder = document.getElementById("poin-tertinggi-placeholder");

  try {
    const result = await api.getMemberPoinList(5, 1);

    if (result.success === true && result.data && result.data.length > 0) {
      placeholder.innerHTML = "";

      result.data.forEach((member) => {
        const nama = member.nama_cust || "Nama tidak diketahui";
        const poin = member.total_poin || 0;

        const itemDiv = document.createElement("div");
        itemDiv.className = "flex justify-between items-center";

        const nameSpan = document.createElement("span");
        nameSpan.className = "text-gray-500 truncate";
        nameSpan.textContent = nama;
        nameSpan.title = nama;

        const poinSpan = document.createElement("span");
        poinSpan.className = "font-semibold text-gray-700";

        poinSpan.textContent = `${new Intl.NumberFormat("id-ID").format(
          poin
        )} Poin`;

        itemDiv.appendChild(nameSpan);
        itemDiv.appendChild(poinSpan);
        placeholder.appendChild(itemDiv);
      });
    } else {
      placeholder.innerHTML = "";
      const noData = document.createElement("span");
      noData.className = "text-gray-500";
      noData.textContent = "Belum ada data poin.";
      placeholder.appendChild(noData);
    }
  } catch (error) {
    console.error("Error loading member poin:", error);

    placeholder.innerHTML = "";
    const errorSpan = document.createElement("span");
    errorSpan.className = "text-red-500";
    errorSpan.textContent = "Gagal memuat data.";
    placeholder.appendChild(errorSpan);
  }
}

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

async function loadSebaranMemberData() {
  const placeholder = document.getElementById("sebaran-member-placeholder");

  try {
    const result = await api.getCityMember(); // Panggil API baru

    if (result.success === true && result.data && result.data.length > 0) {
      placeholder.innerHTML = ""; // Kosongkan loading spinner

      // Loop data dari API (kota, total, persen)
      result.data.forEach((item) => {
        const kota = item.kota || "Tidak Diketahui";
        const total = item.total || 0;
        const persen = item.persen || 0;

        const itemDiv = document.createElement("div");
        itemDiv.className = "flex justify-between items-center";

        const nameSpan = document.createElement("span");
        nameSpan.className = "text-gray-500 truncate";
        nameSpan.textContent = kota;
        nameSpan.title = kota; // Tooltip jika nama kota panjang

        const valueSpan = document.createElement("span");
        valueSpan.className = "font-semibold text-gray-700";
        // Tampilkan total dan persentase
        valueSpan.textContent = `${new Intl.NumberFormat("id-ID").format(
          total
        )} (${persen}%)`;

        itemDiv.appendChild(nameSpan);
        itemDiv.appendChild(valueSpan);
        placeholder.appendChild(itemDiv);
      });
    } else {
      // Jika tidak ada data
      placeholder.innerHTML = "";
      const noData = document.createElement("span");
      noData.className = "text-gray-500";
      noData.textContent = "Belum ada data sebaran.";
      placeholder.appendChild(noData);
    }
  } catch (error) {
    // Jika terjadi error
    console.error("Error loading sebaran member:", error);
    // Tampilkan pesan error di placeholder
    placeholder.innerHTML = "";
    const errorSpan = document.createElement("span");
    errorSpan.className = "text-red-500";
    errorSpan.textContent = "Gagal memuat data.";
    placeholder.appendChild(errorSpan);
  }
}
