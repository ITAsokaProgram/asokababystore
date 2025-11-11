import * as api from "./member_api_service.js";
let currentFilter = "";
let currentStatus = "";
let ageChartInstance = null;
let locationChartInstance = null;
let currentLocationLevel = "city";
let selectedCity = null;
let selectedDistrict = null;
function showAgeLoading(isLoading) {
  const spinner = document.getElementById("loading-spinner");
  if (spinner) spinner.classList.toggle("hidden", !isLoading);
}
function showAgeChart(isShown) {
  const chartContainer = document.getElementById("age-chart-container");
  if (chartContainer) chartContainer.classList.toggle("hidden", !isShown);
}
function showAgeError(message) {
  const errorEl = document.getElementById("age-chart-error");
  if (errorEl) {
    errorEl.textContent = message;
    errorEl.classList.remove("hidden");
  }
}
function hideAgeError() {
  const errorEl = document.getElementById("age-chart-error");
  if (errorEl) errorEl.classList.add("hidden");
}
function showLocationLoading(isLoading) {
  const spinner = document.getElementById("location-loading-spinner");
  if (spinner) spinner.classList.toggle("hidden", !isLoading);
}
function showLocationChart(isShown) {
  const chartContainer = document.getElementById("location-chart-container");
  if (chartContainer) chartContainer.classList.toggle("hidden", !isShown);
}
function showLocationError(message) {
  const errorEl = document.getElementById("location-chart-error");
  if (errorEl) {
    errorEl.textContent = message;
    errorEl.classList.remove("hidden");
  }
}
function hideLocationError() {
  const errorEl = document.getElementById("location-chart-error");
  if (errorEl) errorEl.classList.add("hidden");
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
  const colors = [
    "rgba(59, 130, 246, 0.8)",
    "rgba(16, 185, 129, 0.8)",
    "rgba(234, 179, 8, 0.8)",
    "rgba(239, 68, 68, 0.8)",
    "rgba(139, 92, 246, 0.8)",
    "rgba(249, 115, 22, 0.8)",
    "rgba(107, 114, 128, 0.8)",
  ];
  const backgroundColors = [];
  for (let i = 0; i < labels.length; i++) {
    backgroundColors.push(colors[i % colors.length]);
  }
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
  console.log("Rendering location chart with data:", data);
  const ctx = document.getElementById("memberLocationChart").getContext("2d");
  if (locationChartInstance) {
    locationChartInstance.destroy();
  }

  // --- TAMBAHAN: Urutkan data agar legend menampilkan 5 teratas ---
  data.sort((a, b) => b.count - a.count);
  // --- AKHIR TAMBAHAN ---

  const labels = data.map((d) => d.location_name);
  const counts = data.map((d) => d.count);
  const total = counts.reduce((a, b) => a + b, 0);
  const chartType = "pie";
  let chartTitle = "Distribusi Member per ";
  if (currentLocationLevel === "city") chartTitle += "Kota";
  else if (currentLocationLevel === "district") chartTitle += "Kecamatan";
  else chartTitle += "Kelurahan";
  const colors = [
    "rgba(59, 130, 246, 0.8)",
    "rgba(16, 185, 129, 0.8)",
    "rgba(234, 179, 8, 0.8)",
    "rgba(239, 68, 68, 0.8)",
    "rgba(139, 92, 246, 0.8)",
    "rgba(249, 115, 22, 0.8)",
    "rgba(20, 184, 166, 0.8)",
    "rgba(217, 70, 239, 0.8)",
    "rgba(107, 114, 128, 0.8)",
  ];
  const backgroundColors = [];
  for (let i = 0; i < labels.length; i++) {
    backgroundColors.push(colors[i % colors.length]);
  }
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
        // --- PERUBAHAN: Tambahkan filter di legend ---
        legend: {
          display: true,
          position: "top",
          labels: {
            filter: function (legendItem, chartData) {
              // Tampilkan hanya 5 item pertama (yang sudah disortir)
              return legendItem.index < 5;
            },
          },
        },
        // --- AKHIR PERUBAHAN ---
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
async function loadAgeData() {
  showAgeLoading(true);
  showAgeChart(false);
  hideAgeError();
  try {
    const result = await api.getMemberByAge(currentFilter, currentStatus);
    if (result.success === true && result.data && result.data.length > 0) {
      renderAgeChart(result.data);
      showAgeChart(true);
    } else if (result.success === true && result.data.length === 0) {
      showAgeError("Tidak ada data member (umur) untuk filter ini.");
    } else {
      throw new Error(result.message || "Gagal memuat data umur");
    }
  } catch (error) {
    console.error("Error loading member age data:", error);
    showAgeError(`Gagal memuat chart umur: ${error.message}`);
  } finally {
    showAgeLoading(false);
  }
}
async function loadLocationData() {
  showLocationLoading(true);
  showLocationChart(false);
  hideLocationError();
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
      showLocationChart(true);
    } else if (result.success === true && result.data.length === 0) {
      let levelText = "kota";
      if (currentLocationLevel === "district") levelText = "kecamatan";
      if (currentLocationLevel === "subdistrict") levelText = "kelurahan";
      showLocationError(
        `Tidak ada data member (${levelText}) untuk filter ini.`
      );
    } else {
      throw new Error(result.message || "Gagal memuat data lokasi");
    }
  } catch (error) {
    console.error("Error loading member location data:", error);
    showLocationError(`Gagal memuat chart lokasi: ${error.message}`);
  } finally {
    showLocationLoading(false);
  }
}
document.addEventListener("DOMContentLoaded", () => {
  const params = new URLSearchParams(window.location.search);
  currentFilter = params.get("filter");
  currentStatus = params.get("status");
  if (currentFilter && currentStatus) {
    loadAgeData();
    loadLocationData();
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
    showAgeLoading(false);
    showLocationLoading(false);
    showAgeError("Parameter filter atau status tidak ditemukan.");
    showLocationError("Parameter filter atau status tidak ditemukan.");
  }
});
