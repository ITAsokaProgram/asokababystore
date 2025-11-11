import * as api from "./member_api_service.js";
function showLoading(isLoading) {
  const spinner = document.getElementById("loading-spinner");
  if (spinner) {
    spinner.classList.toggle("hidden", !isLoading);
  }
}
function showChart(isShown) {
  const chartContainer = document.getElementById("age-chart-container");
  if (chartContainer) {
    chartContainer.classList.toggle("hidden", !isShown);
  }
}
function showError(message) {
  const errorEl = document.getElementById("age-chart-error");
  if (errorEl) {
    errorEl.textContent = message;
    errorEl.classList.remove("hidden");
  }
}
function hideError() {
  const errorEl = document.getElementById("age-chart-error");
  if (errorEl) {
    errorEl.classList.add("hidden");
  }
}

function renderAgeChart(data, filter, status) {
  const ctx = document.getElementById("memberAgeChart").getContext("2d");
  let existingChart = Chart.getChart("memberAgeChart");
  if (existingChart) {
    existingChart.destroy();
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
  new Chart(ctx, {
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
        legend: {
          display: true,
          position: "top",
        },
        title: {
          display: true,
          text: "Jumlah Member per Kelompok Umur",
        },
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
            filter
          )}&age_group=${encodeURIComponent(ageGroup)}`;
          window.location.href = targetUrl;
        }
      },
      onHover: (event, chartElement) => {
        const canvas = event.native.target;
        if (chartElement.length > 0) {
          canvas.style.cursor = "pointer";
        } else {
          canvas.style.cursor = "default";
        }
      },
    },
  });
}

async function loadAgeData(filter, status) {
  showLoading(true);
  showChart(false);
  hideError();
  try {
    const result = await api.getMemberByAge(filter, status);
    if (result.success === true && result.data && result.data.length > 0) {
      renderAgeChart(result.data, filter, status);
      showChart(true);
    } else if (result.success === true && result.data.length === 0) {
      showError("Tidak ada data member untuk filter ini.");
    } else {
      throw new Error(result.message || "Gagal memuat data umur");
    }
  } catch (error) {
    console.error("Error loading member age data:", error);
    showError(`Gagal memuat chart: ${error.message}`);
  } finally {
    showLoading(false);
  }
}
document.addEventListener("DOMContentLoaded", () => {
  const params = new URLSearchParams(window.location.search);
  const filter = params.get("filter");
  const status = params.get("status");
  if (filter && status) {
    loadAgeData(filter, status);
  } else {
    console.error("Filter atau Status tidak ditemukan di URL.");
    showLoading(false);
    showError("Parameter filter atau status tidak ditemukan.");
  }
});
