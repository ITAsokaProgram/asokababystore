import { fetchTopMember } from "../member_internal/product/fetch_product.js";
import { getCookie } from "/src/js/index/utils/cookies.js";
import { fetchMargin } from "/src/js/margin/fetch/get_margin.js";
// Fungsi untuk memperbarui UI
function updateUI(data) {
  const trans = data.trans_tertinggi[0];
  const gabungkan = data.jumlah_member_per_cabang.find(
    (item) => item.cabang === trans.cabang
  );
  const jumlahMember = gabungkan ? gabungkan.jumlah_member : "-";
  setText("total_trans", data?.total_trans?.[0]?.total_transaksi ?? "-");
  setText("total_trans_member", data?.total_trans?.[0]?.member ?? "-");
  setText("total_trans_non", data?.total_trans?.[0]?.non_member ?? "-");
  setText(
    "cabang-t",
    (data.trans_tertinggi[0].cabang ?? "-") +
      (jumlahMember > 0 ? " (" + jumlahMember + " Member)" : "")
  );
  setText("trans_tertinggi_member", data?.trans_tertinggi?.[0]?.member ?? "-");
  setText("trans_tertinggi_non", data?.trans_tertinggi?.[0]?.non_member ?? "-");
  setText(
    "trans_tertinggi_total",
    data?.trans_tertinggi?.[0]?.total_transaksi ?? "-"
  );

  const transTerendah = data.trans_terendah[0];
  const gabungkanTerendah = data.jumlah_member_per_cabang.find(
    (item) => item.cabang === transTerendah.cabang
  );
  const jumlahMemberTerendah = gabungkanTerendah
    ? gabungkanTerendah.jumlah_member
    : "-";
  setText(
    "cabang-tr",
    (data.trans_terendah[0].cabang ?? "-") +
      (jumlahMemberTerendah > 0 ? " (" + jumlahMemberTerendah + " Member)" : "")
  );
  setText(
    "trans_terendah_total",
    data?.trans_terendah?.[0]?.total_transaksi ?? "-"
  );
  setText("trans_terendah_member", data?.trans_terendah?.[0]?.member ?? "-");
  setText("trans_terendah_non", data?.trans_terendah?.[0]?.non_member ?? "-");

  setText("top_sales_member", data?.top_sales_by_member?.[0]?.barang ?? "-");
  setText(
    "top_sales_product_member",
    data?.top_sales_by_product?.[0]?.barang ?? "-"
  );
}

// Fungsi untuk mengubah teks pada elemen HTML
function setText(id, text) {
  const el = document.getElementById(id);
  if (el) el.textContent = text;
}

// Function untuk mengambil data invalid transaksi
async function loadInvalidTransaksi() {
  try {
    const token = getCookie("token");
    const response = await fetch("/src/api/invalid/view_invalid_top", {
      method: "GET",
      headers: {
        Authorization: "Bearer " + token,
        "Content-Type": "application/json",
      },
    });
    const margin = await fetch("/src/api/dashboard/top_margin", {
      method: "GET",
      headers: {
        Authorization: "Bearer " + token,
        "Content-Type": "application/json",
      },
    });
    const activity = await fetch("/src/api/customer/get_top_5_activity_cust", {
      method: "GET",
      headers: {
        Authorization: "Bearer " + token,
        "Content-Type": "application/json",
      },
    });
    const topMember = await fetch("/src/api/member/product/get_top_member", {
      method: "GET",
      headers: {
        Authorization: "Bearer " + token,
        "Content-Type": "application/json",
      },
    });
    if (response.ok && activity.ok && margin.ok && topMember.ok ) {
      const result = await response.json();
      const marginData = await margin.json();

      const activityData = await activity.json();
      const topMemberData = await topMember.json();
      if (
        result.status === "success" &&
        result.data &&
        result.data.length > 0
      ) {
        displayInvalidTransaksi(result.data);
        displayTop5margin(marginData.data);
        displayTop5Retur(result.dataRetur);
        displayTop5Activity(activityData.data);
        setText("top_member", topMemberData.data[0].nama_cust);
        setText("top_member_nominal", "Rp. " + topMemberData.data[0].total_penjualan.toLocaleString("id-ID"));
      } else {
        displayNoData();
      }
    } else {
      displayError("Gagal mengambil data");
    }
  } catch (error) {
    console.error("Error:", error);
    displayError("Terjadi kesalahan");
  }
}

// Function untuk menampilkan data invalid transaksi (compact design)
function displayInvalidTransaksi(data) {
  const container = document.getElementById("invalid-transaksi-container");
  if (!container) return;
  container.innerHTML = "";

  // Top 3 data
  const topData = data.slice(0, 3);

  topData.forEach((item, idx) => {
    // Soft color per urutan
    const bg =
      [
        "from-red-100 to-red-50",
        "from-orange-100 to-orange-50",
        "from-yellow-100 to-yellow-50",
      ][idx] || "from-white to-gray-50";
    const border =
      ["border-red-300", "border-orange-300", "border-yellow-300"][idx] ||
      "border-gray-200";

    const card = document.createElement("div");
    card.onclick = () => {
      window.location.href = "/src/fitur/transaction/top_invalid";
    };
    card.className = `cursor-pointer bg-gradient-to-br ${bg} rounded-xl p-1.5 shadow border ${border} hover:scale-105 hover:shadow-md transition-all duration-300 flex flex-col animate-fade-in-up mb-1`;

    card.innerHTML = `
        <div class="flex items-center justify-between mb-1" >
          <div class="flex items-center gap-2">
            <div class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></div>
            <span class="font-semibold text-xs text-gray-800 truncate max-w-[80px]" title="Kasir: ${item.kasir}">${item.kasir}</span>
          </div>
          <span class="text-xs text-gray-500">${item.cabang}</span>
        </div>
        <div class="flex items-center justify-between text-xs text-gray-600">
          <div class="flex items-center gap-1 truncate max-w-[100px]" title="${item.kategori}">
            <i class="fa-solid fa-ban text-red-600 text-xs"></i>
            ${item.kategori}
          </div>
          <span class="font-bold text-xs bg-red-100 text-red-700 px-2 py-0.5 rounded-full shadow">${item.jml_gagal}</span>
        </div>
      `;

    container.appendChild(card);
  });
}

function displayTop5margin(data) {
  const container = document.getElementById("top-margin-minus-container");
  if (!container) return;
  container.innerHTML = "";
  data.slice(0, 3).forEach((item, idx) => {
    const bg =
      [
        "from-yellow-100 to-yellow-50",
        "from-gray-100 to-gray-50",
        "from-orange-100 to-orange-50",
      ][idx] || "from-white to-gray-50";
    const border =
      ["border-yellow-300", "border-gray-300", "border-orange-300"][idx] ||
      "border-gray-200";
    const card = document.createElement("div");
    card.className = `cursor-pointer bg-gradient-to-br ${bg} rounded-xl p-1.5 shadow border ${border} hover:scale-105 hover:shadow-md transition-all duration-300 flex flex-col items-center animate-fade-in-up mb-1`;
    card.onclick = () => {
      window.location.href = "/src/fitur/transaction/top_margin";
    };
    card.innerHTML = `
      <div class="flex items-center gap-1 mb-1">
        <span class="font-semibold text-xs text-gray-800">${item.cabang}</span>
      </div>
      <span class="font-semibold text-orange-600 text-xs bg-orange-100 px-1.5 py-0.5 rounded-full">Rp.${item.Margin.toLocaleString(
        "id-ID"
      )}</span>
    `;
    container.appendChild(card);
  });
}
// Function untuk menampilkan pesan tidak ada data
function displayNoData() {
  const container = document.getElementById("invalid-transaksi-container");
  if (container) {
    container.innerHTML = `
        <div class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-xl p-6 text-center border border-green-200 shadow-sm">
          <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-3">
            <i class="fa-solid fa-check-circle text-green-500 text-lg animate-pulse"></i>
          </div>
          <div class="text-sm font-semibold text-green-800 mb-1">Semua Transaksi Valid!</div>
          <div class="text-xs text-green-600">Tidak ada invalid transaksi hari ini</div>
        </div>
      `;
  }
}

function displayTop5Retur(data) {
  const container = document.getElementById("top-retur-container");
  if (!container) return;
  container.innerHTML = "";

  // Ambil hanya 3 data teratas
  data.slice(0, 3).forEach((item, idx) => {
    // Warna background dan border berbeda tiap urutan
    const bg =
      [
        "from-purple-100 to-violet-50",
        "from-indigo-100 to-blue-50",
        "from-pink-100 to-pink-50",
      ][idx] || "from-white to-gray-50";
    const border =
      ["border-purple-300", "border-indigo-300", "border-pink-300"][idx] ||
      "border-gray-200";

    const card = document.createElement("div");
    card.className = `cursor-pointer bg-gradient-to-br ${bg} rounded-xl p-1.5 shadow border ${border} hover:scale-105 hover:shadow-md transition-all duration-300 flex flex-col animate-fade-in-up mb-1`;
    card.onclick = () => {
      window.location.href = "/src/fitur/transaction/top_retur";
    };
    card.innerHTML = `
        <div class="flex items-center justify-between mb-1">
          <div class="flex items-center gap-2">
            <div class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></div>
            <span class="font-semibold text-xs text-gray-800 truncate max-w-[80px]" title="Kasir: ${item.kasir}">${item.kasir}</span>
          </div>
          <span class="text-xs text-gray-500">${item.cabang}</span>
        </div>
        <div class="flex items-center justify-between text-xs text-gray-600">
          <div class="flex items-center gap-1 truncate max-w-[100px]" title="${item.kategori}">
            <i class="fa-solid fa-rotate-left text-violet-600"></i>
            ${item.kategori}
          </div>
          <span class="font-bold text-xs bg-violet-100 text-violet-700 px-2 py-0.5 rounded-full shadow">${item.jml_gagal}</span>
        </div>
      `;

    container.appendChild(card);
  });
}

function displayTop5Activity(data) {
  const container = document.getElementById("top-activity-container");
  if (!container) return;
  container.innerHTML = "";

  // Ambil hanya 3 data teratas
  data.slice(0, 3).forEach((item, idx) => {
    // Warna background dan border berbeda tiap urutan
    const bg =
      [
        "from-blue-100 to-blue-50",
        "from-cyan-100 to-cyan-50",
        "from-teal-100 to-teal-50",
      ][idx] || "from-white to-gray-50";
    const border =
      ["border-blue-300", "border-cyan-300", "border-teal-300"][idx] ||
      "border-gray-200";

    const card = document.createElement("div");
    card.className = `cursor-pointer bg-gradient-to-br ${bg} rounded-xl p-1.5 shadow border ${border} hover:scale-105 hover:shadow-md transition-all duration-300 flex flex-col animate-fade-in-up mb-1`;
    card.onclick = () => {
      window.location.href = "/src/fitur/laporan/in_customer";
    };
    card.innerHTML = `
        <div class="flex items-center justify-between mb-1">
          <div class="flex items-center gap-2">
            <div class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></div>
            <span class="font-semibold text-xs text-gray-800 truncate max-w-[80px]" title="Kasir: ${item.kasir}">${item.kasir}</span>
          </div>
          <span class="text-xs text-gray-500">${item.cabang}</span>
        </div>
        <div class="flex items-center justify-between text-xs text-gray-600">
          <div class="flex items-center gap-1 truncate" title="${item.nama_cust}">
            <i class="fa-solid fa-user text-blue-600"></i>
            ${item.nama_cust}
          </div>
          <span class="font-bold text-xs bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full shadow">${item.T_Trans}</span>
        </div>
      `;

    container.appendChild(card);
  });
}
// Function untuk menampilkan error
function displayError(message) {
  const container = document.getElementById("invalid-transaksi-container");
  if (container) {
    container.innerHTML = `
        <div class="bg-gradient-to-br from-red-50 to-pink-50 rounded-xl p-6 text-center border border-red-200 shadow-sm">
          <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-3">
            <i class="fa-solid fa-exclamation-triangle text-red-500 text-lg animate-bounce"></i>
          </div>
          <div class="text-sm font-semibold text-red-800 mb-1">Terjadi Kesalahan</div>
          <div class="text-xs text-red-600">${message}</div>
        </div>
      `;
  }
}

export {
  updateUI,
  setText,
  loadInvalidTransaksi,
  displayInvalidTransaksi,
  displayTop5margin,
  displayNoData,
  displayError,
};
