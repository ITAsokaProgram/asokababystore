import * as api from "./member_api_service.js";

document.addEventListener("DOMContentLoaded", () => {
  loadMemberSummaryData();
  loadProdukFavoritData();
  loadProdukTerlarisData();
  loadMemberPoinData();
  loadCombinedDashboardData(); // Tetap panggil ini untuk Ringkasan Transaksi
  loadSebaranMemberData(); // <-- MEMANGGIL FUNGSI BARU
  loadTopMemberSalesData();
  loadSebaranMember3BulanData();
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
        const kd_cust = member.kd_cust || member.no_hp;

        const itemDiv = document.createElement("div");
        itemDiv.className = "flex justify-between items-center";

        const nameLink = document.createElement("a");

        // --- MODIFIKASI DI SINI: Tambahkan hover:underline ---
        nameLink.className =
          "text-gray-500 truncate hover:text-blue-600 hover:font-semibold hover:underline transition-all"; // --- SELESAI MODIFIKASI ---
        nameLink.textContent = nama;
        nameLink.title = `Lihat detail ${nama}`;

        if (kd_cust) {
          nameLink.href = `management_member?kd_cust=${encodeURIComponent(
            kd_cust
          )}`;
        } else {
          nameLink.href = "javascript:void(0)";
          nameLink.style.cursor = "default";
          nameLink.title = `${nama} (ID tidak ditemukan)`;
        }

        const poinSpan = document.createElement("span");
        poinSpan.className = "font-semibold text-gray-700";

        poinSpan.textContent = `${new Intl.NumberFormat("id-ID").format(
          poin
        )} Poin`;

        itemDiv.appendChild(nameLink); // 4. Tambahkan link (bukan span lagi)
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
async function loadCombinedDashboardData() {
  // --- AMBIL ID PLACEHOLDER YANG BARU ---
  const cabangPlaceholder = document.getElementById(
    "top-member-cabang-placeholder"
  );

  try {
    // Panggil API SATU KALI SAJA (untuk 2 kartu)
    const result = await api.getTransactionDashboardData();

    if (result.status === true && result.data) {
      const data = result.data;
      const formatter = new Intl.NumberFormat("id-ID");

      // --- Bagian 1: Logika untuk "Top Member (Cabang)" (KARTU BARU) ---
      if (
        cabangPlaceholder &&
        data.jumlah_member_per_cabang &&
        data.jumlah_member_per_cabang.length > 0
      ) {
        cabangPlaceholder.innerHTML = "";

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
          cabangPlaceholder.appendChild(itemDiv);
        });
      } else if (cabangPlaceholder) {
        cabangPlaceholder.innerHTML = "";
        const noData = document.createElement("span");
        noData.className = "text-gray-500";
        noData.textContent = "Belum ada data cabang.";
        cabangPlaceholder.appendChild(noData);
      }

      // --- Bagian 2: Logika untuk "Ringkasan Transaksi" (KARTU LAMA) ---
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
    } else {
      throw new Error(result.message || "Gagal memuat data dashboard");
    }
  } catch (error) {
    console.error("Error loading combined dashboard data:", error);

    // Tampilkan error untuk KEDUA kartu yang dikelola fungsi ini
    if (cabangPlaceholder) {
      cabangPlaceholder.innerHTML = "";
      const errorSpan = document.createElement("span");
      errorSpan.className = "text-red-500";
      errorSpan.textContent = "Gagal memuat data.";
      cabangPlaceholder.appendChild(errorSpan);
    }
    updatePlaceholder("total-transaksi-placeholder", "Gagal", true);
    updatePlaceholder("transaksi-member-placeholder", "Gagal", true);
    updatePlaceholder("transaksi-non-member-placeholder", "Gagal", true);
  }
}
// --- FUNGSI BARU UNTUK SEBARAN MEMBER (TOP 5 KOTA) ---
async function loadSebaranMemberData() {
  const sebaranPlaceholder = document.getElementById(
    "sebaran-member-placeholder"
  );
  if (!sebaranPlaceholder) return; // Guard clause

  const formatter = new Intl.NumberFormat("id-ID");

  try {
    // Panggil API yang benar untuk sebaran member
    const result = await api.getCityMember();

    // Sesuaikan dengan struktur JSON yang Anda berikan (success: true, data: [...])
    if (result.success === true && result.data && result.data.length > 0) {
      sebaranPlaceholder.innerHTML = "";

      // Ambil 5 data teratas (data dari API sepertinya sudah diurutkan)
      const top5Data = result.data.slice(0, 5);

      top5Data.forEach((item) => {
        // Gunakan key 'kota' dan 'total' dari JSON
        const kota = item.kota || "Tidak Diketahui";
        const jumlah = item.total || 0;

        const itemDiv = document.createElement("div");
        itemDiv.className = "flex justify-between items-center";

        const nameSpan = document.createElement("span");
        nameSpan.className = "text-gray-500 truncate";
        nameSpan.textContent = kota;
        nameSpan.title = kota;

        const valueSpan = document.createElement("span");
        valueSpan.className = "font-semibold text-gray-700";
        valueSpan.textContent = `${formatter.format(jumlah)} Member`;

        itemDiv.appendChild(nameSpan);
        itemDiv.appendChild(valueSpan);
        sebaranPlaceholder.appendChild(itemDiv);
      });
    } else {
      sebaranPlaceholder.innerHTML = "";
      const noData = document.createElement("span");
      noData.className = "text-gray-500";
      noData.textContent = "Belum ada data sebaran.";
      sebaranPlaceholder.appendChild(noData);
    }
  } catch (error) {
    console.error("Error loading sebaran member:", error);
    sebaranPlaceholder.innerHTML = "";
    const errorSpan = document.createElement("span");
    errorSpan.className = "text-red-500";
    errorSpan.textContent = "Gagal memuat data.";
    sebaranPlaceholder.appendChild(errorSpan);
  }
}

async function loadTopMemberSalesData() {
  const placeholder = document.getElementById("top-member-sales-placeholder");
  if (!placeholder) return; // Guard clause

  try {
    const result = await api.getTopMemberBySales();

    // ... penanganan "Data belum tersedia" unchanged ...
    if (result.success === false && result.message === "Data belum tersedia") {
      placeholder.innerHTML = "";
      const noData = document.createElement("span");
      noData.className = "text-gray-500";
      noData.textContent = "Data sedang diproses...";
      placeholder.appendChild(noData);
      return;
    }

    // ... penanganan error lain unchanged ...
    if (result.success === false) {
      throw new Error(result.message || "Format data tidak valid");
    }

    // Menangani data sukses (data dari cache Redis)
    if (result.data && result.data.length > 0) {
      placeholder.innerHTML = "";
      const salesFormatter = new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
      });

      // Mengambil 5 data teratas saja
      const top5Data = result.data.slice(0, 5);

      top5Data.forEach((member) => {
        const nama = member.nama_cust || "Nama tidak diketahui";
        const sales = parseFloat(member.total_penjualan) || 0;

        const itemDiv = document.createElement("div");
        itemDiv.className = "flex justify-between items-center";

        const nameSpan = document.createElement("span");
        nameSpan.className = "text-gray-500 truncate";
        nameSpan.textContent = nama;
        nameSpan.title = nama;

        const salesSpan = document.createElement("span");
        salesSpan.className = "font-semibold text-gray-700";
        salesSpan.textContent = salesFormatter.format(sales);

        itemDiv.appendChild(nameSpan);
        itemDiv.appendChild(salesSpan);
        placeholder.appendChild(itemDiv);
      });
    } else {
      placeholder.innerHTML = "";
      const noData = document.createElement("span");
      noData.className = "text-gray-500";
      noData.textContent = "Belum ada data sales.";
      placeholder.appendChild(noData);
    }
  } catch (error) {
    // ... error handling unchanged ...
    console.error("Error loading top member sales:", error);

    placeholder.innerHTML = "";
    const errorSpan = document.createElement("span");
    errorSpan.className = "text-red-500";
    errorSpan.textContent = "Gagal memuat data.";
    placeholder.appendChild(errorSpan);
  }
}

async function loadSebaranMember3BulanData() {
  const totalMemberEl = document.getElementById(
    "sebaran-total-member-placeholder"
  );
  const totalKotaEl = document.getElementById("sebaran-total-kota-placeholder");
  const kotaTerbesarEl = document.getElementById(
    "sebaran-kota-terbesar-placeholder"
  ); // Guard clause jika elemen tidak ditemukan

  if (!totalMemberEl || !totalKotaEl || !kotaTerbesarEl) {
    console.error("Elemen placeholder sebaran 3 bulan tidak ditemukan.");
    return;
  }

  const formatter = new Intl.NumberFormat("id-ID");

  try {
    // Panggil API BARU (getCityMemberAll)
    const result = await api.getCityMemberAll();

    if (result.success === true && result.data && result.data.length > 0) {
      // 1. Total Member: Sum dari 'total' semua kota
      const totalMember = result.data.reduce(
        (acc, city) => acc + (city.total || 0),
        0
      ); // 2. Total Kota: Jumlah item dalam array data

      const totalKota = result.data.length; // 3. Kota Terbesar: Item pertama (karena API sudah ORDER BY DESC)

      const kotaTerbesar = result.data[0].kota || "Tidak Diketahui";

      updatePlaceholder(
        "sebaran-total-member-placeholder",
        formatter.format(totalMember)
      );
      updatePlaceholder(
        "sebaran-total-kota-placeholder",
        formatter.format(totalKota)
      );
      updatePlaceholder("sebaran-kota-terbesar-placeholder", kotaTerbesar); // Atur title untuk truncate

      kotaTerbesarEl.setAttribute("title", kotaTerbesar);
    } else {
      // Tidak ada data
      updatePlaceholder("sebaran-total-member-placeholder", "Data kosong");
      updatePlaceholder("sebaran-total-kota-placeholder", "Data kosong");
      updatePlaceholder("sebaran-kota-terbesar-placeholder", "Data kosong");
    }
  } catch (error) {
    console.error("Error loading sebaran member 3 bulan:", error);
    updatePlaceholder("sebaran-total-member-placeholder", "Gagal", true);
    updatePlaceholder("sebaran-total-kota-placeholder", "Gagal", true);
    updatePlaceholder("sebaran-kota-terbesar-placeholder", "Gagal", true);
  }
}
