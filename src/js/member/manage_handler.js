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
  const chartElement = document.getElementById("memberActivityChart");
  if (!chartElement) {
    console.error("Chart element 'memberActivityChart' not found");
    return;
  }
  const chartInstance = echarts.init(chartElement);
  const total = data.active + data.inactive;
  const chartData = [
    {
      name: "Active Members",
      value: data.active,
      percentage: total > 0 ? ((data.active / total) * 100).toFixed(2) : 0,
    },
    {
      name: "Inactive Members",
      value: data.inactive,
      percentage: total > 0 ? ((data.inactive / total) * 100).toFixed(2) : 0,
    },
  ];
  const option = {
    animationDuration: 1000,
    animationEasing: "cubicOut",
    tooltip: {
      trigger: "item",
      formatter: (params) => {
        const percentage = parseFloat(params.data.percentage).toFixed(2);
        return `${params.data.name}<br/>Jumlah: ${params.value}<br/>Persentase: ${percentage}%`;
      },
    },
    series: [
      {
        type: "pie",
        label: {
          fontSize: 12,
          formatter: (params) => {
            const percentage = parseFloat(params.data.percentage).toFixed(2);
            return `${params.data.name}\n(${percentage}%)`;
          },
        },
        data: chartData,
        itemStyle: {
          color: (params) => {
            const colors = ["rgba(22, 163, 74, 0.9)", "rgba(220, 38, 38, 0.8)"];
            return colors[params.dataIndex % colors.length];
          },
        },
      },
    ],
  };
  chartInstance.setOption(option);
  chartInstance.off("click");
  chartInstance.on("click", (params) => {
    if (params.componentType !== "series") return;
    const clickedElementIndex = params.dataIndex;
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
  });
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
  window.addEventListener("resize", () => {
    chartInstance.resize();
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
    const chartElement = document.getElementById("memberActivityChart");
    if (chartElement) {
      chartElement.innerHTML = `
        <div style="text-align: center; padding-top: 50px; color: red; font-family: Arial, sans-serif;">
          <strong>Gagal memuat chart.</strong><br>
          <span>${error.message || "Cek console untuk detail."}</span>
        </div>
      `;
    }
  }
}
document.addEventListener("DOMContentLoaded", () => {
  const chartSection = document.getElementById("chart-section");
  if (chartSection) {
    loadActivityData();
  }
});
