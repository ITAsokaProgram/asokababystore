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
const currencyFormatter = new Intl.NumberFormat("id-ID", {
  style: "currency",
  currency: "IDR",
  minimumFractionDigits: 0,
});
const numberFormatter = new Intl.NumberFormat("id-ID");
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
  window.addEventListener("resize", () => {
    chartInstance.resize();
  });
}
export function renderAgeChart(chartInstance, elementId, data, currentFilter) {
  const chartElement = document.getElementById(elementId);
  if (!chartElement) return null;
  if (chartInstance) {
    chartInstance.dispose();
  }
  const newChartInstance = echarts.init(chartElement);
  const filteredData = data;
  const chartData = filteredData.map((d) => ({
    value: d.count,
    name: d.age_group,
  }));
  const total = filteredData.reduce((acc, curr) => acc + curr.count, 0);
  const option = {
    animationDuration: 1000,
    animationEasing: "cubicOut",
    color: CHART_COLORS,
    tooltip: {
      trigger: "item",
      appendToBody: true,
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
        minAngle: 15,
        type: "pie",
        radius: "50%",
        data: chartData,
        fontSize: 16,
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
  newChartInstance.setOption(option);
  newChartInstance.off("click");
  newChartInstance.on("click", (params) => {
    if (params.componentType !== "series") return;
    const ageGroup = params.name;
    if (ageGroup) {
      const targetUrl = `umur.php?filter=${encodeURIComponent(
        currentFilter
      )}&age_group=${encodeURIComponent(ageGroup)}`;
      window.location.href = targetUrl;
    }
  });
  addChartHoverHandlers(newChartInstance, chartElement);
  return newChartInstance;
}
export function renderLocationChart(
  chartInstance,
  elementId,
  data,
  state,
  callbacks
) {
  const chartElement = document.getElementById(elementId);
  if (!chartElement) return null;
  if (chartInstance) {
    chartInstance.dispose();
  }
  const newChartInstance = echarts.init(chartElement);
  const filteredData = data;
  filteredData.sort((a, b) => b.count - a.count);
  const chartData = filteredData.map((d) => ({
    value: d.count,
    name: d.location_name,
  }));
  const total = filteredData.reduce((acc, curr) => acc + curr.count, 0);
  const option = {
    animationDuration: 1000,
    animationEasing: "cubicOut",
    color: CHART_COLORS,
    tooltip: {
      trigger: "item",
      appendToBody: true,
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
        minAngle: 15,
        type: "pie",
        radius: "50%",
        data: chartData,
        fontSize: 16,

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
  newChartInstance.setOption(option);
  newChartInstance.off("click");
  newChartInstance.on("click", (params) => {
    if (params.componentType !== "series") return;
    const clickedLabel = params.name;
    if (state.currentLocationLevel === "city") {
      callbacks.updateLocationState("district", clickedLabel, null);
    } else if (state.currentLocationLevel === "district") {
      callbacks.updateLocationState(
        "subdistrict",
        state.selectedCity,
        clickedLabel
      );
    } else if (state.currentLocationLevel === "subdistrict") {
      const selectedSubDistrict = clickedLabel;
      const targetUrl = `lokasi.php?filter=${encodeURIComponent(
        state.currentFilter
      )}&status=${encodeURIComponent(
        state.currentStatus
      )}&city=${encodeURIComponent(
        state.selectedCity
      )}&district=${encodeURIComponent(
        state.selectedDistrict
      )}&subdistrict=${encodeURIComponent(selectedSubDistrict)}`;
      window.location.href = targetUrl;
    }
  });
  addChartHoverHandlers(newChartInstance, chartElement);
  return newChartInstance;
}
export function renderTopMemberChart(chartInstance, elementId, data, state) {
  const chartElement = document.getElementById(elementId);
  if (!chartElement) return null;
  if (chartInstance) {
    chartInstance.dispose();
  }
  const newChartInstance = echarts.init(chartElement);
  const filteredData = data;
  const chartData = filteredData.map((d) => ({
    value: d.total_spent,
    name: `${d.nama_cust} - (${d.kd_cust})`,
    kd_cust: d.kd_cust,
    nama_cust: d.nama_cust,
  }));
  const total = filteredData.reduce((acc, curr) => acc + curr.total_spent, 0);
  const option = {
    animationDuration: 1000,
    animationEasing: "cubicOut",
    color: CHART_COLORS,
    tooltip: {
      trigger: "item",
      appendToBody: true,
      formatter: (params) => {
        const percentage =
          total > 0 ? ((params.value / total) * 100).toFixed(1) : 0;
        return `${params.name}: ${currencyFormatter.format(
          params.value
        )} (${percentage}%)`;
      },
    },
    legend: {
      show: false,
    },
    series: [
      {
        minAngle: 15,
        type: "pie",
        radius: "50%",
        data: chartData,
        fontSize: 16,

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
  newChartInstance.setOption(option);
  newChartInstance.off("click");
  newChartInstance.on("click", (params) => {
    if (params.componentType !== "series") return;
    const customerData = params.data;
    if (customerData) {
      const targetUrl = `customer.php?filter=${encodeURIComponent(
        state.currentFilter
      )}&status=${encodeURIComponent(
        state.currentStatus
      )}&kd_cust=${encodeURIComponent(
        customerData.kd_cust
      )}&nama_cust=${encodeURIComponent(customerData.nama_cust)}`;
      window.location.href = targetUrl;
    }
  });
  addChartHoverHandlers(newChartInstance, chartElement);
  return newChartInstance;
}
export function renderTopProductChart(chartInstance, elementId, data, state) {
  const chartElement = document.getElementById(elementId);
  if (!chartElement) return null;
  if (chartInstance) {
    chartInstance.dispose();
  }
  const newChartInstance = echarts.init(chartElement);
  const filteredData = data;
  const chartData = filteredData.map((d) => ({
    value: d.total_item_qty,
    name: `${d.nama_cust} (${d.kd_cust}) - ${d.descp}`,
    kd_cust: d.kd_cust,
    nama_cust: d.nama_cust,
  }));
  const total = filteredData.reduce(
    (acc, curr) => acc + curr.total_item_qty,
    0
  );
  const option = {
    animationDuration: 1000,
    animationEasing: "cubicOut",
    color: CHART_COLORS,
    tooltip: {
      trigger: "item",
      appendToBody: true,
      formatter: (params) => {
        const percentage =
          total > 0 ? ((params.value / total) * 100).toFixed(1) : 0;
        return `${params.name}: ${numberFormatter.format(
          params.value
        )} qty (${percentage}%)`;
      },
    },
    legend: {
      show: false,
    },
    series: [
      {
        minAngle: 15,
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
  newChartInstance.setOption(option);
  newChartInstance.off("click");
  newChartInstance.on("click", (params) => {
    if (params.componentType !== "series") return;
    const customerData = params.data;
    if (customerData) {
      const targetUrl = `customer.php?filter=${encodeURIComponent(
        state.currentFilter
      )}&status=${encodeURIComponent(
        state.currentStatus
      )}&kd_cust=${encodeURIComponent(
        customerData.kd_cust
      )}&nama_cust=${encodeURIComponent(customerData.nama_cust)}`;
      window.location.href = targetUrl;
    }
  });
  addChartHoverHandlers(newChartInstance, chartElement);
  return newChartInstance;
}
export function renderMemberChart(elementId, data, filter) {
  const chartElement = document.getElementById(elementId);
  if (!chartElement) {
    console.error(`Chart element '${elementId}' not found`);
    return null;
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
      appendToBody: true,
      formatter: (params) => {
        const percentage = parseFloat(params.data.percentage).toFixed(2);
        return `${params.data.name}<br/>Jumlah: ${params.value}<br/>Persentase: ${percentage}%`;
      },
    },
    series: [
      {
        minAngle: 15,
        type: "pie",
        radius: "85%",
        label: {
          fontSize: 16,
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
  addChartHoverHandlers(chartInstance, chartElement);
  return chartInstance;
}
