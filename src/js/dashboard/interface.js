import { fetchTopMember } from "../member_internal/product/fetch_product.js";
import { getCookie } from "/src/js/index/utils/cookies.js";
import { fetchMargin } from "/src/js/margin/fetch/get_margin.js";

function updateUI(data) {
  if (!data) {
    setText("total_trans", "-");
    setText("total_trans_member", "-");
    setText("total_trans_non", "-");
    setText("cabang-t", "-");
    setText("trans_tertinggi_total", "-");
    setText("trans_tertinggi_member", "-");
    setText("trans_tertinggi_non", "-");
    setText("cabang-tr", "-");
    setText("trans_terendah_total", "-");
    setText("trans_terendah_member", "-");
    setText("trans_terendah_non", "-");
    setText("top_sales_member", "-");
    setText("top_sales_product_member", "-");
    return;
  }
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

function setText(id, text) {
  const el = document.getElementById(id);
  if (el) el.textContent = text;
}

async function loadInvalidTransaksi() {
  try {
    const token = getCookie("admin_token");
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
    if (response.ok && activity.ok && margin.ok && topMember.ok) {
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
        setText(
          "top_member_nominal",
          "Rp. " + topMemberData.data[0].total_penjualan.toLocaleString("id-ID")
        );
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

function displayInvalidTransaksi(data) {
  const container = document.getElementById("invalid-transaksi-container");
  if (!container) return;
  container.innerHTML = "";

  const topData = data.slice(0, 3);

  topData.forEach((item, idx) => {
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

  data.slice(0, 3).forEach((item, idx) => {
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

  data.slice(0, 3).forEach((item, idx) => {
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
async function loadReviewData() {
  try {
    const token = getCookie("admin_token");
    const response = await fetch("/src/api/dashboard/get_review_summary", {
      method: "GET",
      headers: {
        Authorization: "Bearer " + token,
        "Content-Type": "application/json",
      },
    });

    if (response.ok) {
      const result = await response.json();
      if (result.status === "success" && result.data) {
        displayReviewStats(result.data);
        displayFeaturedReview(result.data.featured_review);
        displayPendingReviews(result.data.pending_reviews);
      } else {
        displayNoReviews();
      }
    } else {
      displayReviewError("Gagal mengambil data review");
    }
  } catch (error) {
    console.error("Error:", error);
    displayReviewError("Terjadi kesalahan");
  }
}

function displayReviewStats(data) {
  setText("avg-rating", data.avg_rating || "0.0");
  setText("total-reviews", data.total_reviews || "0");
  setText("pending-count", data.pending_count || "0");
}
function displayPendingReviews(data) {
  const container = document.getElementById("pending-reviews-container");
  if (!container) return;
  container.innerHTML = "";

  if (!data || data.length === 0) {
    container.innerHTML = `
      <div class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-xl p-3 text-center border border-green-200">
        <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-2">
          <i class="fa-solid fa-check-circle text-green-500 text-sm"></i>
        </div>
        <div class="text-xs font-semibold text-green-800">Semua review sudah terpecahkan!</div>
      </div>
    `;
    return;
  }
}

function displayReviewError(message) {
  const container = document.getElementById("pending-reviews-container");
  if (container) {
    container.innerHTML = `
      <div class="bg-gradient-to-br from-red-50 to-pink-50 rounded-xl p-3 text-center border border-red-200">
        <i class="fa-solid fa-exclamation-triangle text-red-500 text-sm mb-1"></i>
        <div class="text-xs font-semibold text-red-800">${message}</div>
      </div>
    `;
  }
}

function displayNoReviews() {
  const container = document.getElementById("pending-reviews-container");
  if (container) {
    container.innerHTML = `
      <div class="bg-gradient-to-br from-gray-50 to-gray-100 rounded-xl p-3 text-center border border-gray-200">
        <i class="fa-solid fa-inbox text-gray-400 text-sm mb-1"></i>
        <div class="text-xs text-gray-600">Belum ada review</div>
      </div>
    `;
  }
}

function displayFeaturedReview(review) {
  const container = document.getElementById("featured-review-container");
  if (!container) return;

  if (!review) {
    container.innerHTML = `
            <div class="bg-gradient-to-br from-gray-50 to-gray-100 rounded-xl p-3 text-center border border-gray-200">
                <i class="fa-solid fa-inbox text-gray-400 text-sm mb-1"></i>
                <div class="text-xs text-gray-600">Belum ada review untuk ditampilkan</div>
            </div>`;
    return;
  }

  const statusStyles = {
    pending: {
      bgColor: "from-orange-50 to-red-50",
      borderColor: "border-orange-200",
      spanClasses: "text-orange-600 bg-orange-100/70", // Style untuk span
    },
    in_progress: {
      bgColor: "from-blue-50 to-cyan-50",
      borderColor: "border-blue-200",
      spanClasses: "text-blue-600 bg-blue-100/70", // Style untuk span
    },
    resolved: {
      bgColor: "from-green-50 to-emerald-50",
      borderColor: "border-green-200",
      spanClasses: "text-green-600 bg-green-100/70", // Style untuk span
    },
    // Style default jika status tidak dikenali
    default: {
      bgColor: "from-gray-50 to-gray-100",
      borderColor: "border-gray-200",
      spanClasses: "text-gray-600 bg-gray-100/70",
    },
  };

  const currentStatus = review.review_status || "default";
  const currentStyles = statusStyles[currentStatus] || statusStyles.default;

  // Mapping untuk teks yang akan ditampilkan
  const statusDisplayText = {
    pending: "Pending",
    in_progress: "In Progress",
    resolved: "Resolved",
  };

  // Teks untuk ditampilkan di span, fallback ke 'Baru' jika tidak ada
  const displayText = statusDisplayText[review.review_status] || "Baru";

  let stars = "";
  for (let i = 1; i <= 5; i++) {
    stars += `<i class="fa-solid fa-star ${
      i <= review.rating ? "text-yellow-400" : "text-gray-300"
    }"></i>`;
  }

  const customerName = review.nama_customer || "Customer";
  const statusReview = {
    pending: "Pending",
    in_progress: "In Progress",
    resolved: "Resolved",
  };

  container.innerHTML = `
        <div class="cursor-pointer bg-gradient-to-br ${currentStyles.bgColor} rounded-xl p-3 shadow-sm border ${currentStyles.borderColor} hover:shadow-lg transition-all duration-300 animate-fade-in-up" onclick="window.location.href='/src/fitur/laporan/in_review_cust'">
            <div class="flex items-start justify-between mb-2">
                <div class="flex flex-col">
                    <span class="font-bold text-sm text-gray-800">${customerName}</span>
                </div>
                <span class="text-xs font-semibold px-2 py-0.5 rounded-full shadow ${currentStyles.spanClasses}">
                    ${displayText}
                </span>
            </div>
            <div class="flex text-xs">${stars}</div>
        </div>
    `;
}

export {
  updateUI,
  setText,
  loadInvalidTransaksi,
  displayInvalidTransaksi,
  displayTop5margin,
  displayNoData,
  displayError,
  loadReviewData,
  displayReviewStats,
  displayPendingReviews,
  displayReviewError,
  displayNoReviews,
};
