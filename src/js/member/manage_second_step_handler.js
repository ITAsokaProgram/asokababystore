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

// Formatters tetap sama
const currencyFormatter = new Intl.NumberFormat("id-ID", {
  style: "currency",
  currency: "IDR",
  minimumFractionDigits: 0,
});
const numberFormatter = new Intl.NumberFormat("id-ID");

// Palet warna untuk ECharts (dari Chart.js sebelumnya)
const CHART_COLORS = [
  "rgba(59, 130, 246, 0.9)",
  "rgba(16, 185, 129, 0.9)",
  "rgba(234, 179, 8, 0.9)",
  "rgba(239, 68, 68, 0.9)",
  "rgba(139, 92, 246, 0.9)",
  "rgba(249, 115, 22, 0.9)",
  "rgba(20, 184, 166, 0.9)",
  "rgba(217, 70, 239, 0.9)",
  "rgba(107, 114, 128, 0.9)",
  "rgba(22, 163, 74, 0.9)",
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

/**
 * Helper untuk event handler ECharts (Hover)
 */
function addChartHoverHandlers(chartInstance, chartElement) {
  chartInstance.off("mouseover");
  chartInstance.on("mouseover", (params) => {
    if (params.componentType === "series") {
      chartElement.style.cursor = "pointer";
    }
  });
  chartInstance.off("mouseout");
  chartInstance.on("mouseout", () => {
    chartElement.style.cursor = "default";
  });
  // Tambahkan resize handler
  window.addEventListener("resize", () => {
    chartInstance.resize();
  });
}

function renderAgeChart(data) {
  const chartElement = document.getElementById("memberAgeChart");
  if (!chartElement) return;
  if (ageChartInstance) {
    ageChartInstance.dispose();
  }
  ageChartInstance = echarts.init(chartElement);
  const chartData = data.map((d) => ({ value: d.count, name: d.age_group }));
  const total = data.reduce((acc, curr) => acc + curr.count, 0);

  const option = {
    animationDuration: 1000,
    animationEasing: "cubicOut",
    color: CHART_COLORS,

    tooltip: {
      trigger: "item",
      formatter: (params) => {
        const percentage =
          total > 0 ? ((params.value / total) * 100).toFixed(1) : 0;
        return `${params.name}: ${params.value} member (${percentage}%)`;
      },
    },
    legend: {
      show: false,
      type: "scroll",
      orient: "vertical",
      right: 10,
      top: 20,
      bottom: 20,
    },
    series: [
      {
        type: "pie",
        radius: "50%",
        data: chartData,
        label: {
          formatter: (params) => {
            const percentage =
              total > 0 ? ((params.value / total) * 100).toFixed(1) : 0;
            return `${params.name}\n(${percentage}%)`;
          },
        },
      },
    ],
  };

  ageChartInstance.setOption(option);

  // Click Handler
  ageChartInstance.off("click");
  ageChartInstance.on("click", (params) => {
    if (params.componentType !== "series") return;
    const ageGroup = params.name; // ECharts provides name
    if (ageGroup) {
      const targetUrl = `umur.php?filter=${encodeURIComponent(
        currentFilter
      )}&age_group=${encodeURIComponent(ageGroup)}`;
      window.location.href = targetUrl;
    }
  });

  addChartHoverHandlers(ageChartInstance, chartElement);
}

function renderLocationChart(data) {
  const chartElement = document.getElementById("memberLocationChart");
  if (!chartElement) return;
  if (locationChartInstance) {
    locationChartInstance.dispose();
  }
  locationChartInstance = echarts.init(chartElement);

  data.sort((a, b) => b.count - a.count);
  const chartData = data.map((d) => ({
    value: d.count,
    name: d.location_name,
  }));
  const total = data.reduce((acc, curr) => acc + curr.count, 0);

  let chartTitle = "Distribusi Member per ";
  if (currentLocationLevel === "city") chartTitle += "Kota";
  else if (currentLocationLevel === "district") chartTitle += "Kecamatan";
  else chartTitle += "Kelurahan";

  const option = {
    animationDuration: 1000,
    animationEasing: "cubicOut",
    color: CHART_COLORS,

    tooltip: {
      trigger: "item",
      formatter: (params) => {
        const percentage =
          total > 0 ? ((params.value / total) * 100).toFixed(1) : 0;
        return `${params.name}: ${params.value} member (${percentage}%)`;
      },
    },
    legend: {
      show: false,
      type: "scroll",
      orient: "vertical",
      right: 10,
      top: 20,
      bottom: 20,
    },
    series: [
      {
        type: "pie",
        radius: "50%",
        data: chartData,
        label: {
          formatter: (params) => {
            const percentage =
              total > 0 ? ((params.value / total) * 100).toFixed(1) : 0;
            return `${params.name}\n(${percentage}%)`;
          },
        },
      },
    ],
  };

  locationChartInstance.setOption(option);

  // Click Handler (Drill-down)
  locationChartInstance.off("click");
  locationChartInstance.on("click", (params) => {
    if (params.componentType !== "series") return;
    const clickedLabel = params.name;
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
      )}&status=${encodeURIComponent(currentStatus)}&city=${encodeURIComponent(
        selectedCity
      )}&district=${encodeURIComponent(
        selectedDistrict
      )}&subdistrict=${encodeURIComponent(selectedSubDistrict)}`;
      window.location.href = targetUrl;
    }
  });

  addChartHoverHandlers(locationChartInstance, chartElement);
}

function renderTopMemberChart(data) {
  const chartElement = document.getElementById("topMemberChart");
  if (!chartElement) return;
  if (topMemberChartInstance) {
    topMemberChartInstance.dispose();
  }
  topMemberChartInstance = echarts.init(chartElement);

  // Simpan data asli di ECharts data object
  const chartData = data.map((d) => ({
    value: d.total_spent,
    name: `${d.nama_cust} - (${d.kd_cust})`,
    // Simpan data ekstra untuk click handler
    kd_cust: d.kd_cust,
    nama_cust: d.nama_cust,
  }));
  const total = data.reduce((acc, curr) => acc + curr.total_spent, 0);

  const option = {
    animationDuration: 1000,
    animationEasing: "cubicOut",
    color: CHART_COLORS,

    tooltip: {
      trigger: "item",
      formatter: (params) => {
        const percentage =
          total > 0 ? ((params.value / total) * 100).toFixed(1) : 0;
        return `${params.name}: ${currencyFormatter.format(
          params.value
        )} (${percentage}%)`;
      },
    },
    legend: {
      show: false, // Sesuai config Chart.js sebelumnya
    },
    series: [
      {
        type: "pie",
        radius: "50%",
        data: chartData,
        label: {
          formatter: (params) => {
            const percentage =
              total > 0 ? ((params.value / total) * 100).toFixed(1) : 0;
            return `${params.name}\n(${percentage}%)`;
          },
        },
      },
    ],
  };

  topMemberChartInstance.setOption(option);

  // Click Handler
  topMemberChartInstance.off("click");
  topMemberChartInstance.on("click", (params) => {
    if (params.componentType !== "series") return;
    const customerData = params.data; // ECharts menyimpan seluruh objek data di sini
    if (customerData) {
      const targetUrl = `customer.php?filter=${encodeURIComponent(
        currentFilter
      )}&status=${encodeURIComponent(
        currentStatus
      )}&kd_cust=${encodeURIComponent(
        customerData.kd_cust
      )}&nama_cust=${encodeURIComponent(customerData.nama_cust)}`;
      window.location.href = targetUrl;
    }
  });

  addChartHoverHandlers(topMemberChartInstance, chartElement);
}

function renderTopProductChart(data) {
  const chartElement = document.getElementById("topMemberProductChart");
  if (!chartElement) return;
  if (topMemberProductChartInstance) {
    topMemberProductChartInstance.dispose();
  }
  topMemberProductChartInstance = echarts.init(chartElement);

  const chartData = data.map((d) => ({
    value: d.total_item_qty,
    name: `${d.nama_cust} (${d.kd_cust}) - ${d.descp}`,
    // Simpan data ekstra untuk click handler
    kd_cust: d.kd_cust,
    nama_cust: d.nama_cust,
  }));
  const total = data.reduce((acc, curr) => acc + curr.total_item_qty, 0);

  const option = {
    animationDuration: 1000,
    animationEasing: "cubicOut",
    color: CHART_COLORS,

    tooltip: {
      trigger: "item",
      formatter: (params) => {
        const percentage =
          total > 0 ? ((params.value / total) * 100).toFixed(1) : 0;
        return `${params.name}: ${numberFormatter.format(
          params.value
        )} qty (${percentage}%)`;
      },
    },
    legend: {
      show: false, // Sesuai config Chart.js sebelumnya
    },
    series: [
      {
        type: "pie",
        radius: "50%",
        data: chartData,
        label: {
          formatter: (params) => {
            const percentage =
              total > 0 ? ((params.value / total) * 100).toFixed(1) : 0;
            return `${params.name}\n(${percentage}%)`;
          },
        },
      },
    ],
  };

  topMemberProductChartInstance.setOption(option);

  // Click Handler
  topMemberProductChartInstance.off("click");
  topMemberProductChartInstance.on("click", (params) => {
    if (params.componentType !== "series") return;
    const customerData = params.data; // ECharts menyimpan seluruh objek data
    if (customerData) {
      const targetUrl = `customer.php?filter=${encodeURIComponent(
        currentFilter
      )}&status=${encodeURIComponent(
        currentStatus
      )}&kd_cust=${encodeURIComponent(
        customerData.kd_cust
      )}&nama_cust=${encodeURIComponent(customerData.nama_cust)}`;
      window.location.href = targetUrl;
    }
  });

  addChartHoverHandlers(topMemberProductChartInstance, chartElement);
}

// =================================================================
// FUNGSI LOAD DATA (DENGAN PERBAIKAN)
// =================================================================

async function loadAgeData() {
  setChartUIState(UI_ELEMENTS.age, "loading");
  try {
    const result = await api.getMemberByAge(currentFilter, currentStatus);
    if (result.success === true && result.data && result.data.length > 0) {
      renderAgeChart(result.data);
      setChartUIState(UI_ELEMENTS.age, "success");
      // FIX: Panggil resize SETELAH container terlihat
      if (ageChartInstance) ageChartInstance.resize();
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
      // FIX: Panggil resize SETELAH container terlihat
      if (locationChartInstance) locationChartInstance.resize();
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
      // FIX: Panggil resize SETELAH container terlihat
      if (topMemberChartInstance) topMemberChartInstance.resize();
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
      // FIX: Panggil resize SETELAH container terlihat
      if (topMemberProductChartInstance) topMemberProductChartInstance.resize();
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

// =================================================================
// EVENT LISTENER (TIDAK BERUBAH)
// =================================================================

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
