import * as api from "./member_api_service.js";

document.addEventListener("DOMContentLoaded", () => {
  loadMemberSummaryData();
  loadProdukFavoritData();
  loadProdukTerlarisData();
  loadMemberPoinData();
  // --- PERUBAHAN DI SINI ---
  // Memanggil satu fungsi gabungan, bukan dua fungsi terpisah
  loadCombinedDashboardData();
  // -------------------------
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
    const result = await api.getMemberPoinList(5, 1); // Ambil 5 data

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
      const formatter = new Intl.NumberFormat("id-ID");
      updatePlaceholder(
        "total-member-placeholder",
        formatter.format(result.data.total_member)
      );
      updatePlaceholder(
        "active-member-placeholder",
        formatter.format(result.data.active_member)
      );
      updatePlaceholder(
        "non-active-member-placeholder",
        formatter.format(result.data.non_active_member)
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
      const barang = data.top_10_member[0].barang || "Data kosong";
      updatePlaceholder("top-barang-member-placeholder", barang);
      // Atur title untuk truncate
      document
        .getElementById("top-barang-member-placeholder")
        ?.setAttribute("title", barang);
    } else {
      updatePlaceholder("top-barang-member-placeholder", "Data kosong");
    }

    if (data.top_10_non && data.top_10_non.length > 0) {
      const barang = data.top_10_non[0].barang || "Data kosong";
      updatePlaceholder("top-barang-non-member-placeholder", barang);
      // Atur title untuk truncate
      document
        .getElementById("top-barang-non-member-placeholder")
        ?.setAttribute("title", barang);
    } else {
      updatePlaceholder("top-barang-non-member-placeholder", "Data kosong");
    }
  } catch (error) {
    console.error("Error loading produk terlaris:", error);
    updatePlaceholder("top-barang-member-placeholder", "Gagal", true);
    updatePlaceholder("top-barang-non-member-placeholder", "Gagal", true);
  }
}

// --- FUNGSI GABUNGAN BARU (MENGGANTIKAN 2 FUNGSI LAMA) ---
async function loadCombinedDashboardData() {
  const sebaranPlaceholder = document.getElementById(
    "sebaran-member-placeholder"
  );

  try {
    // Panggil API SATU KALI SAJA
    const result = await api.getTransactionDashboardData();

    if (result.status === true && result.data) {
      const data = result.data;
      const formatter = new Intl.NumberFormat("id-ID");

      // --- Bagian 1: Logika untuk "Sebaran Member (Top 5)" ---
      if (
        sebaranPlaceholder &&
        data.jumlah_member_per_cabang &&
        data.jumlah_member_per_cabang.length > 0
      ) {
        sebaranPlaceholder.innerHTML = "";

        const sortedData = data.jumlah_member_per_cabang
          .sort((a, b) => parseInt(b.jumlah_member) - parseInt(a.jumlah_member))
          .slice(0, 5); // Mengambil 5 teratas

        sortedData.forEach((item) => {
          const cabang = item.cabang || "Tidak Diketahui";
          const jumlah = item.jumlah_member || 0;

          const itemDiv = document.createElement("div");
          itemDiv.className = "flex justify-between items-center";

          const nameSpan = document.createElement("span");
          nameSpan.className = "text-gray-500 truncate";
          nameSpan.textContent = cabang;
          nameSpan.title = cabang;

          const valueSpan = document.createElement("span");
          valueSpan.className = "font-semibold text-gray-700";
          valueSpan.textContent = `${formatter.format(jumlah)} Member`;

          itemDiv.appendChild(nameSpan);
          itemDiv.appendChild(valueSpan);
          sebaranPlaceholder.appendChild(itemDiv);
        });
      } else if (sebaranPlaceholder) {
        sebaranPlaceholder.innerHTML = "";
        const noData = document.createElement("span");
        noData.className = "text-gray-500";
        noData.textContent = "Belum ada data sebaran.";
        sebaranPlaceholder.appendChild(noData);
      }

      // --- Bagian 2: Logika untuk "Transaction Dashboard Cards" ---

      // 1. Ringkasan Transaksi (total_trans)
      if (data.total_trans && data.total_trans.length > 0) {
        const trans = data.total_trans[0];
        updatePlaceholder(
          "total-transaksi-placeholder",
          formatter.format(trans.total_transaksi || 0)
        );
        updatePlaceholder(
          "transaksi-member-placeholder",
          formatter.format(trans.member || 0)
        );
        updatePlaceholder(
          "transaksi-non-member-placeholder",
          formatter.format(trans.non_member || 0)
        );
      } else {
        updatePlaceholder("total-transaksi-placeholder", "Data kosong");
        updatePlaceholder("transaksi-member-placeholder", "Data kosong");
        updatePlaceholder("transaksi-non-member-placeholder", "Data kosong");
      }

      // 2. Performa Cabang (trans_tertinggi)
      if (data.trans_tertinggi && data.trans_tertinggi.length > 0) {
        const tertinggi = data.trans_tertinggi[0];
        updatePlaceholder(
          "trans-tertinggi-placeholder",
          `${tertinggi.cabang} (${formatter.format(
            tertinggi.total_transaksi || 0
          )})`
        );
      } else {
        updatePlaceholder("trans-tertinggi-placeholder", "Data kosong");
      }

      // 3. Performa Cabang (trans_terendah)
      if (data.trans_terendah && data.trans_terendah.length > 0) {
        const terendah = data.trans_terendah[0];
        updatePlaceholder(
          "trans-terendah-placeholder",
          `${terendah.cabang} (${formatter.format(
            terendah.total_transaksi || 0
          )})`
        );
      } else {
        updatePlaceholder("trans-terendah-placeholder", "Data kosong");
      }

      // 4. Top Sales (top_sales_by_product)
      if (data.top_sales_by_product && data.top_sales_by_product.length > 0) {
        const topProduk = data.top_sales_by_product[0];
        const barang = topProduk.barang || "Data kosong";
        updatePlaceholder("top-produk-placeholder", barang);
        document
          .getElementById("top-produk-placeholder")
          ?.setAttribute("title", barang);
      } else {
        updatePlaceholder("top-produk-placeholder", "Data kosong");
      }

      console.log("data", data.top_sales_by_member);
      // 5. Top Sales (top_sales_by_member)
      if (data.top_sales_by_member && data.top_sales_by_member.length > 0) {
        const topMember = data.top_sales_by_member[0];
        const nama = topMember.nama_customer || "Data kosong";
        updatePlaceholder("top-member-placeholder", nama);
        document
          .getElementById("top-member-placeholder")
          ?.setAttribute("title", nama);
      } else {
        updatePlaceholder("top-member-placeholder", "Data kosong");
      }
    } else {
      throw new Error(result.message || "Gagal memuat data dashboard");
    }
  } catch (error) {
    console.error("Error loading combined dashboard data:", error);
    // Set semua placeholder yang relevan ke "Gagal"
    if (sebaranPlaceholder) {
      sebaranPlaceholder.innerHTML = "";
      const errorSpan = document.createElement("span");
      errorSpan.className = "text-red-500";
      errorSpan.textContent = "Gagal memuat data.";
      sebaranPlaceholder.appendChild(errorSpan);
    }
    updatePlaceholder("total-transaksi-placeholder", "Gagal", true);
    updatePlaceholder("transaksi-member-placeholder", "Gagal", true);
    updatePlaceholder("transaksi-non-member-placeholder", "Gagal", true);
    updatePlaceholder("trans-tertinggi-placeholder", "Gagal", true);
    updatePlaceholder("trans-terendah-placeholder", "Gagal", true);
    updatePlaceholder("top-produk-placeholder", "Gagal", true);
    updatePlaceholder("top-member-placeholder", "Gagal", true);
  }
}
