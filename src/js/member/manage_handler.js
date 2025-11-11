import * as api from "./member_api_service.js";
function updatePlaceholder(id, value, isError = false) {
  const el = document.getElementById(id);
  if (el) {
    el.textContent = value;
    el.classList.remove("text-red-500");
    if (isError) {
      el.classList.add("text-red-500");
    }
    const spinner = el.querySelector(".fa-spinner");
    if (spinner) {
      spinner.remove();
    }
  }
}

function renderMemberChart(data, filter) {
  const ctx = document.getElementById("memberActivityChart").getContext("2d");
  let existingChart = Chart.getChart("memberActivityChart");
  if (existingChart) {
    existingChart.destroy();
  }
  new Chart(ctx, {
    type: "pie",
    data: {
      labels: ["Active Members", "Inactive Members"],
      datasets: [
        {
          label: "Aktivitas Member",
          data: [data.active, data.inactive],
          backgroundColor: ["rgba(22, 163, 74, 0.7)", "rgba(220, 38, 38, 0.7)"],
          borderColor: ["rgba(22, 163, 74, 1)", "rgba(220, 38, 38, 1)"],
          borderWidth: 1,
        },
      ],
    },
    options: {
      responsive: true,
      plugins: {
        legend: {
          display: false,
        },
        title: {
          display: true,
          text: "Proporsi Member Active vs Inactive",
        },
        tooltip: {
          callbacks: {
            label: function (context) {
              let label = context.label || "";
              if (label) {
                label += ": ";
              }
              if (context.parsed !== null) {
                const total = data.active + data.inactive;
                const percentage =
                  total > 0 ? ((context.parsed / total) * 100).toFixed(2) : 0;
                label += `${context.formattedValue} (${percentage}%)`;
              }
              return label;
            },
          },
        },
      },
      onClick: (e, elements) => {
        if (elements.length === 0) return;
        const clickedElementIndex = elements[0].index;
        let status = "";
        if (clickedElementIndex === 0) {
          status = "active";
        } else if (clickedElementIndex === 1) {
          status = "inactive";
        }
        if (status && filter) {
          const targetUrl = `manage_second_step.php?filter=${encodeURIComponent(
            filter
          )}&status=${encodeURIComponent(status)}`;
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

async function loadActivityData() {
  const formatter = new Intl.NumberFormat("id-ID");
  try {
    const params = new URLSearchParams(window.location.search);
    const filter = params.get("filter");
    if (!filter) {
      console.log("Tidak ada filter, chart tidak dimuat.");
      return;
    }
    const result = await api.getMemberActivity(filter);
    if (result.success === true && result.data) {
      const data = result.data;
      updatePlaceholder(
        "total-member-placeholder",
        formatter.format(data.total)
      );
      updatePlaceholder(
        "active-member-placeholder",
        formatter.format(data.active)
      );
      updatePlaceholder(
        "inactive-member-placeholder",
        formatter.format(data.inactive)
      );
      renderMemberChart(data, filter);
    } else {
      throw new Error(result.message || "Gagal memuat data aktivitas");
    }
  } catch (error) {
    console.error("Error loading member activity:", error);
    updatePlaceholder("total-member-placeholder", "Gagal", true);
    updatePlaceholder("active-member-placeholder", "Gagal", true);
    updatePlaceholder("inactive-member-placeholder", "Gagal", true);
    const ctx = document.getElementById("memberActivityChart").getContext("2d");
    ctx.font = "16px Arial";
    ctx.fillStyle = "red";
    ctx.textAlign = "center";
    ctx.fillText(
      "Gagal memuat chart. Cek console.",
      ctx.canvas.width / 2,
      ctx.canvas.height / 2
    );
  }
}
document.addEventListener("DOMContentLoaded", () => {
  const chartSection = document.getElementById("chart-section");
  if (chartSection) {
    loadActivityData();
  }
});
