import * as api from "./member_api_service.js";
let currentFilter = "";
let currentStatus = "";
let ageChartInstance = null;
let locationChartInstance = null;
let topMemberChartInstance = null;
let topMemberProductChartInstance = null;
let currentLocationLevel = "city";
let selectedCity = null;
let selectedDistrict = null;
const currencyFormatter = new Intl.NumberFormat("id-ID", {
  style: "currency",
  currency: "IDR",
  minimumFractionDigits: 0,
});
const numberFormatter = new Intl.NumberFormat("id-ID");
const CHART_COLORS = [
  "rgba(59, 130, 246, 0.8)",
  "rgba(16, 185, 129, 0.8)",
  "rgba(234, 179, 8, 0.8)",
  "rgba(239, 68, 68, 0.8)",
  "rgba(139, 92, 246, 0.8)",
  "rgba(249, 115, 22, 0.8)",
  "rgba(20, 184, 166, 0.8)",
  "rgba(217, 70, 239, 0.8)",
  "rgba(107, 114, 128, 0.8)",
  "rgba(22, 163, 74, 0.8)",
];

const UI_ELEMENTS = {
  age: {
    loadingId: "loading-spinner",
    containerId: "age-chart-container",
    errorId: "age-chart-error",
  },
  location: {
    loadingId: "location-loading-spinner",
    containerId: "location-chart-container",
    errorId: "location-chart-error",
  },
  topMember: {
    loadingId: "top-member-chart-loading-spinner",
    containerId: "top-member-chart-container",
    errorId: "top-member-chart-error",
  },
  topProduct: {
    loadingId: "top-product-chart-loading-spinner",
    containerId: "top-product-chart-container",
    errorId: "top-product-chart-error",
  },
};
function getBackgroundColors(count) {
  const colors = [];
  for (let i = 0; i < count; i++) {
    colors.push(CHART_COLORS[i % CHART_COLORS.length]);
  }
  return colors;
}
function setChartUIState(
  { loadingId, containerId, errorId },
  state,
  message = ""
) {
  const loadingEl = document.getElementById(loadingId);
  const containerEl = document.getElementById(containerId);
  const errorEl = document.getElementById(errorId);
  if (loadingEl) loadingEl.classList.toggle("hidden", state !== "loading");
  if (containerEl) containerEl.classList.toggle("hidden", state !== "success");
  if (errorEl) {
    errorEl.classList.toggle("hidden", state !== "error" && state !== "empty");
    if (state === "error" || state === "empty") {
      errorEl.textContent = message;
    }
  }
}
function updateLocationHeader() {
  const header = document.getElementById("location-chart-header");
  const backBtn = document.getElementById("location-back-btn");
  const breadcrumb = document.getElementById("location-breadcrumb");
  if (currentLocationLevel === "city") {
    header.classList.add("hidden");
  } else {
    header.classList.remove("hidden");
    if (currentLocationLevel === "district") {
      breadcrumb.textContent = `Kota: ${selectedCity}`;
    } else if (currentLocationLevel === "subdistrict") {
      breadcrumb.textContent = `Kota: ${selectedCity} > Kec: ${selectedDistrict}`;
    }
  }
}
function renderAgeChart(data) {
  const ctx = document.getElementById("memberAgeChart").getContext("2d");
  if (ageChartInstance) {
    ageChartInstance.destroy();
  }
  const labels = data.map((d) => d.age_group);
  const counts = data.map((d) => d.count);
  const backgroundColors = getBackgroundColors(labels.length);
  ageChartInstance = new Chart(ctx, {
    type: "pie",
    data: {
      labels: labels,
      datasets: [
        {
          label: "Jumlah Member",
          data: counts,
          backgroundColor: backgroundColors,
          borderColor: "#FFFFFF",
          borderWidth: 2,
        },
      ],
    },
    options: {
      responsive: true,
      plugins: {
        legend: { display: true, position: "top" },
        title: { display: true, text: "Jumlah Member per Kelompok Umur" },
        tooltip: {
          callbacks: {
            label: function (context) {
              const label = context.label || "";
              const value = context.raw;
              const total = context.chart.getDatasetMeta(0).total;
              const percentage = ((value / total) * 100).toFixed(1) + "%";
              return `${label}: ${value} member (${percentage})`;
            },
          },
        },
      },
      onClick: (e, elements) => {
        if (elements.length === 0) return;
        const clickedElementIndex = elements[0].index;
        const ageGroup = labels[clickedElementIndex];
        if (ageGroup) {
          const targetUrl = `umur.php?filter=${encodeURIComponent(
            currentFilter
          )}&age_group=${encodeURIComponent(ageGroup)}`;
          window.location.href = targetUrl;
        }
      },
      onHover: (event, chartElement) => {
        const canvas = event.native.target;
        canvas.style.cursor = chartElement.length > 0 ? "pointer" : "default";
      },
    },
  });
}
function renderLocationChart(data) {
  const ctx = document.getElementById("memberLocationChart").getContext("2d");
  if (locationChartInstance) {
    locationChartInstance.destroy();
  }
  data.sort((a, b) => b.count - a.count);
  const labels = data.map((d) => d.location_name);
  const counts = data.map((d) => d.count);
  const total = counts.reduce((a, b) => a + b, 0);
  const chartType = "pie";
  let chartTitle = "Distribusi Member per ";
  if (currentLocationLevel === "city") chartTitle += "Kota";
  else if (currentLocationLevel === "district") chartTitle += "Kecamatan";
  else chartTitle += "Kelurahan";
  const backgroundColors = getBackgroundColors(labels.length);
  locationChartInstance = new Chart(ctx, {
    type: chartType,
    data: {
      labels: labels,
      datasets: [
        {
          label: "Jumlah Member",
          data: counts,
          backgroundColor: backgroundColors,
          borderColor: "#FFFFFF",
          borderWidth: 2,
        },
      ],
    },
    options: {
      responsive: true,
      plugins: {
        legend: {
          display: true,
          position: "top",
          labels: {
            filter: function (legendItem, chartData) {
              return legendItem.index < 10;
            },
          },
        },
        title: { display: true, text: chartTitle },
        tooltip: {
          callbacks: {
            label: function (context) {
              const label = context.label || "";
              const value = context.raw;
              const percentage =
                total > 0 ? ((value / total) * 100).toFixed(1) + "%" : "0%";
              return `${label}: ${value} member (${percentage})`;
            },
          },
        },
      },
      onClick: (e, elements) => {
        if (elements.length === 0) return;
        const clickedElementIndex = elements[0].index;
        const clickedLabel = labels[clickedElementIndex];
        if (currentLocationLevel === "city") {
          currentLocationLevel = "district";
          selectedCity = clickedLabel;
          loadLocationData();
        } else if (currentLocationLevel === "district") {
          currentLocationLevel = "subdistrict";
          selectedDistrict = clickedLabel;
          loadLocationData();
        } else if (currentLocationLevel === "subdistrict") {
          const selectedSubDistrict = clickedLabel;
          const targetUrl = `lokasi.php?filter=${encodeURIComponent(
            currentFilter
          )}&status=${encodeURIComponent(
            currentStatus
          )}&city=${encodeURIComponent(
            selectedCity
          )}&district=${encodeURIComponent(
            selectedDistrict
          )}&subdistrict=${encodeURIComponent(selectedSubDistrict)}`;
          window.location.href = targetUrl;
        }
      },
      onHover: (event, chartElement) => {
        const canvas = event.native.target;
        canvas.style.cursor = chartElement.length > 0 ? "pointer" : "default";
      },
    },
  });
}
function renderTopMemberChart(data) {
  const ctx = document.getElementById("topMemberChart").getContext("2d");
  if (topMemberChartInstance) {
    topMemberChartInstance.destroy();
  }
  const labels = data.map((d) => `${d.nama_cust} - (${d.kd_cust})`);
  const counts = data.map((d) => d.total_spent);
  const total = counts.reduce((a, b) => a + b, 0);
  const backgroundColors = getBackgroundColors(labels.length);
  topMemberChartInstance = new Chart(ctx, {
    type: "pie",
    data: {
      labels: labels,
      datasets: [
        {
          label: "Total Belanja",
          data: counts,
          backgroundColor: backgroundColors,
          borderColor: "#FFFFFF",
          borderWidth: 2,
        },
      ],
    },
    options: {
      responsive: true,
      plugins: {
        legend: { display: false, position: "top" },
        title: { display: true, text: "Top 10 Member (Total Belanja)" },
        tooltip: {
          callbacks: {
            label: function (context) {
              const label = context.label || "";
              const value = context.raw;
              const percentage =
                total > 0 ? ((value / total) * 100).toFixed(1) + "%" : "0%";
              return `${label}: ${currencyFormatter.format(
                value
              )} (${percentage})`;
            },
          },
        },
      },
      onHover: (event, chartElement) => {
        const canvas = event.native.target;
        canvas.style.cursor = chartElement.length > 0 ? "default" : "default";
      },
    },
  });
}
function renderTopProductChart(data) {
  const ctx = document.getElementById("topMemberProductChart").getContext("2d");
  if (topMemberProductChartInstance) {
    topMemberProductChartInstance.destroy();
  }
  const labels = data.map((d) => `${d.nama_cust} (${d.kd_cust}) - ${d.descp}`);
  const counts = data.map((d) => d.total_item_qty);
  const total = counts.reduce((a, b) => a + b, 0);
  const backgroundColors = getBackgroundColors(labels.length);
  topMemberProductChartInstance = new Chart(ctx, {
    type: "pie",
    data: {
      labels: labels,
      datasets: [
        {
          label: "Jumlah Qty",
          data: counts,
          backgroundColor: backgroundColors,
          borderColor: "#FFFFFF",
          borderWidth: 2,
        },
      ],
    },
    options: {
      responsive: true,
      plugins: {
        legend: { display: false, position: "top" },
        title: { display: true, text: "Top 10 Pembelian Produk (Qty)" },
        tooltip: {
          callbacks: {
            label: function (context) {
              const label = context.label || "";
              const value = context.raw;
              const percentage =
                total > 0 ? ((value / total) * 100).toFixed(1) + "%" : "0%";
              return `${label}: ${numberFormatter.format(
                value
              )} qty (${percentage})`;
            },
          },
        },
      },
      onHover: (event, chartElement) => {
        const canvas = event.native.target;
        canvas.style.cursor = chartElement.length > 0 ? "default" : "default";
      },
    },
  });
}
async function loadAgeData() {
  setChartUIState(UI_ELEMENTS.age, "loading");
  try {
    const result = await api.getMemberByAge(currentFilter, currentStatus);
    if (result.success === true && result.data && result.data.length > 0) {
      renderAgeChart(result.data);
      setChartUIState(UI_ELEMENTS.age, "success");
    } else if (result.success === true && result.data.length === 0) {
      setChartUIState(
        UI_ELEMENTS.age,
        "empty",
        "Tidak ada data member (umur) untuk filter ini."
      );
    } else {
      throw new Error(result.message || "Gagal memuat data umur");
    }
  } catch (error) {
    console.error("Error loading member age data:", error);
    setChartUIState(
      UI_ELEMENTS.age,
      "error",
      `Gagal memuat chart umur: ${error.message}`
    );
  }
}
async function loadLocationData() {
  setChartUIState(UI_ELEMENTS.location, "loading");
  updateLocationHeader();
  try {
    const result = await api.getMemberByLocation(
      currentFilter,
      currentStatus,
      currentLocationLevel,
      selectedCity,
      selectedDistrict
    );
    if (result.success === true && result.data && result.data.length > 0) {
      renderLocationChart(result.data);
      setChartUIState(UI_ELEMENTS.location, "success");
    } else if (result.success === true && result.data.length === 0) {
      let levelText = "kota";
      if (currentLocationLevel === "district") levelText = "kecamatan";
      if (currentLocationLevel === "subdistrict") levelText = "kelurahan";
      setChartUIState(
        UI_ELEMENTS.location,
        "empty",
        `Tidak ada data member (${levelText}) untuk filter ini.`
      );
    } else {
      throw new Error(result.message || "Gagal memuat data lokasi");
    }
  } catch (error) {
    console.error("Error loading member location data:", error);
    setChartUIState(
      UI_ELEMENTS.location,
      "error",
      `Gagal memuat chart lokasi: ${error.message}`
    );
  }
}
async function loadTopMemberData() {
  setChartUIState(UI_ELEMENTS.topMember, "loading");
  try {
    const result = await api.getTopMembersByFilter(
      currentFilter,
      currentStatus
    );
    if (result.success === true && result.data && result.data.length > 0) {
      renderTopMemberChart(result.data);
      setChartUIState(UI_ELEMENTS.topMember, "success");
    } else if (result.success === true && result.data.length === 0) {
      setChartUIState(
        UI_ELEMENTS.topMember,
        "empty",
        "Tidak ada data top member untuk filter ini."
      );
    } else {
      throw new Error(result.message || "Gagal memuat data top member");
    }
  } catch (error) {
    console.error("Error loading top member data:", error);
    setChartUIState(
      UI_ELEMENTS.topMember,
      "error",
      `Gagal memuat data: ${error.message}`
    );
  }
}
async function loadTopProductData() {
  setChartUIState(UI_ELEMENTS.topProduct, "loading");
  try {
    const result = await api.getTopMemberProductPairs(
      currentFilter,
      currentStatus
    );
    if (result.success === true && result.data && result.data.length > 0) {
      renderTopProductChart(result.data);
      setChartUIState(UI_ELEMENTS.topProduct, "success");
    } else if (result.success === true && result.data.length === 0) {
      setChartUIState(
        UI_ELEMENTS.topProduct,
        "empty",
        "Tidak ada data pembelian produk untuk filter ini."
      );
    } else {
      throw new Error(result.message || "Gagal memuat data produk");
    }
  } catch (error) {
    console.error("Error loading top product data:", error);
    setChartUIState(
      UI_ELEMENTS.topProduct,
      "error",
      `Gagal memuat data: ${error.message}`
    );
  }
}
document.addEventListener("DOMContentLoaded", () => {
  const params = new URLSearchParams(window.location.search);
  currentFilter = params.get("filter");
  currentStatus = params.get("status");
  if (currentFilter && currentStatus) {
    loadAgeData();
    loadLocationData();
    loadTopMemberData();
    loadTopProductData();
    const backBtn = document.getElementById("location-back-btn");
    backBtn.addEventListener("click", () => {
      if (currentLocationLevel === "district") {
        currentLocationLevel = "city";
        selectedCity = null;
        loadLocationData();
      } else if (currentLocationLevel === "subdistrict") {
        currentLocationLevel = "district";
        selectedDistrict = null;
        loadLocationData();
      }
    });
  } else {
    console.error("Filter atau Status tidak ditemukan di URL.");
    const errorMsg = "Parameter filter atau status tidak ditemukan.";
    setChartUIState(UI_ELEMENTS.age, "error", errorMsg);
    setChartUIState(UI_ELEMENTS.location, "error", errorMsg);
    setChartUIState(UI_ELEMENTS.topMember, "error", errorMsg);
    setChartUIState(UI_ELEMENTS.topProduct, "error", errorMsg);
  }
});
