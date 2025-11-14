const API_URL_TRANS = "/src/api/dashboard/get_data_dashboard";
const API_URL_OMSET = "/src/api/dashboard/get_pendapatan";
/**
 * Fungsi untuk mengambil data dan memperbarui grafik (Chart 1)
 */
function fetchData() {
  const periodSelect = document.getElementById("period1");
  if (!periodSelect) return;
  const period = periodSelect.value;
  const chart1Skeleton = document.getElementById("chart1-skeleton");
  if (chart1Skeleton) {
    chart1Skeleton.classList.remove("hidden");
  }
  fetch(`${API_URL_TRANS}?period=${period}`)
    .then((res) => {
      if (!res.ok) throw new Error("Network response was not ok");
      return res.json();
    })
    .then((data) => {
      const responseData = data.data;
      const xAxisData = responseData.map((item) => {
        if (period === "day") return item.tanggal;
        if (period === "month")
          return `${item.tahun}-${
            item.bulan < 10 ? "0" + item.bulan : item.bulan
          }`;
        return item.tahun;
      });
      const seriesData = responseData.map((item) => item.total_transaksi);
      updateChart("chart1", "Total Transaksi", xAxisData, seriesData);
      if (chart1Skeleton) {
        chart1Skeleton.classList.add("hidden");
      }
    })
    .catch((err) => {
      console.error("Gagal fetch data untuk Chart 1:", err);
      if (chart1Skeleton) {
        chart1Skeleton.classList.add("hidden");
      }
    });
}
/**
 * Fungsi untuk mengambil data dan memperbarui grafik (Chart 2)
 */
function fetchDataForChart2() {
  const periodSelect2 = document.getElementById("period2");
  if (!periodSelect2) return;
  const period2 = periodSelect2.value;
  const chart2Skeleton = document.getElementById("chart2-skeleton");
  if (chart2Skeleton) {
    chart2Skeleton.classList.remove("hidden");
  }
  fetch(`${API_URL_OMSET}?filter=${period2}`)
    .then((res) => {
      if (!res.ok) throw new Error("Network response was not ok");
      return res.json();
    })
    .then((data) => {
      if (!data.data || !Array.isArray(data.data)) {
        console.error(
          "Data tidak ditemukan atau tidak dalam format array:",
          data
        );
        if (chart2Skeleton) chart2Skeleton.classList.add("hidden");
        return;
      }
      const responseData = data.data;
      const xAxisData = responseData.map((item) => {
        if (period2 === "per_jam") return item.label;
        if (period2 === "7_hari" || period2 === "30_hari") return item.label;
        if (period2 === "12_bulan") return item.label;
      });
      const seriesData = responseData.map((item) => {
        const totalPendapatan = item.total_pendapatan.replace(/[^\d]/g, "");
        return parseInt(totalPendapatan, 10);
      });
      updateChart("chart2", "Total Pendapatan", xAxisData, seriesData);
      if (chart2Skeleton) {
        chart2Skeleton.classList.add("hidden");
      }
    })
    .catch((err) => {
      console.error("Gagal fetch data untuk Chart 2:", err);
      if (chart2Skeleton) {
        chart2Skeleton.classList.add("hidden");
      }
    });
}
/**
 * Fungsi untuk mengupdate chart (ECharts)
 */
function updateChart(chartId, title, xAxisData, seriesData) {
  const chartDom = document.getElementById(chartId);
  if (!chartDom) {
    console.error(`Elemen chart dengan ID '${chartId}' tidak ditemukan.`);
    return;
  }
  const myChart = echarts.getInstanceByDom(chartDom) || echarts.init(chartDom);
  window.addEventListener("resize", function () {
    if (myChart) {
      myChart.resize();
    }
  });
  const option = {
    grid: {
      left: "2%",
      right: "3%",
      bottom: "5%",
      containLabel: true,
    },
    title: {
      text: title,
      left: "center",
      top: "10px",
      textStyle: { fontWeight: "bold", fontSize: 18, color: "#333" },
    },
    tooltip: { trigger: "axis" },
    xAxis: {
      type: "category",
      data: xAxisData,
      axisLine: { lineStyle: { color: "#ccc" } },
      axisLabel: { fontSize: 12, color: "#666" },
    },
    yAxis: {
      type: "value",
      axisLine: { lineStyle: { color: "#ccc" } },
      axisLabel: {
        formatter: "{value}",
        fontSize: 12,
        color: "#666",
        padding: [0, 1, 0, 20],
      },
      nameGap: 30,
    },
    series: [
      {
        name: title,
        type: "line",
        data: seriesData,
        smooth: false,
        lineStyle: {
          width: 3,
          color: "#4CAF50",
        },
        symbol: "circle",
        symbolSize: 8,
        itemStyle: {
          color: "#4CAF50",
        },
        emphasis: {
          itemStyle: {
            color: "#ff5733",
          },
        },
      },
    ],
  };
  myChart.setOption(option);
}
const periodSelect = document.getElementById("period1");
const periodSelect2 = document.getElementById("period2");
if (periodSelect) {
  periodSelect.addEventListener("change", fetchData);
  fetchData();
}
if (periodSelect2) {
  periodSelect2.addEventListener("change", fetchDataForChart2);
  fetchDataForChart2();
}
