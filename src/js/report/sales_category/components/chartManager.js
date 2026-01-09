
import {
  CATEGORY_NAME_MAPPING,
  CHART_COLORS,
  CHART_ANIMATION_CONFIG,
  ELEMENT_IDS,
  CHART_MODES,
} from "../config/constants.js";
import { parseCurrencyToNumber } from "../utils/formatters.js";
class ChartManager {
  constructor() {
    this.chartInstance = null;
    this.isInitialized = false;
    this.clickHandler = null;
  }

  initialize() {
    try {
      const chartElement = document.getElementById(ELEMENT_IDS.CHART_DIAGRAM);
      if (!chartElement) {
        console.error("Chart element not found");
        return false;
      }
      this.chartInstance = echarts.init(chartElement);
      this.isInitialized = true;
      window.addEventListener("resize", this.handleResize.bind(this));
      return true;
    } catch (error) {
      console.error("Failed to initialize chart:", error);
      return false;
    }
  }

  handleResize() {
    if (this.chartInstance && this.isInitialized) {
      this.chartInstance.resize();
    }
  }

  updateEarlyChart(labels, data, onClickHandler) {
    if (!this.ensureInitialized()) return;
    const chartData = labels.map((label, index) => ({
      name: label,
      value: data[index]?.value || 0,
      uang: data[index]?.uang || "Rp 0",
      percentage: data[index]?.persentase || 0,
    }));
    const option = {
      animationDuration: CHART_ANIMATION_CONFIG.duration,
      animationEasing: CHART_ANIMATION_CONFIG.easing,
      tooltip: {
        trigger: "item",
        formatter: (params) => {
          const mappedName =
            CATEGORY_NAME_MAPPING[params.data.name] || params.data.name;
          const percentage = parseFloat(params.data.percentage).toFixed(2);
          return `${mappedName}<br/>Terjual: ${params.value}<br/>Persentase: ${percentage}%<br/>Total: ${params.data.uang}`;
        },
      },
      series: [
        {
          type: "pie",
          label: {
            fontSize: 12,
            formatter: (params) => {
              const mappedName =
                CATEGORY_NAME_MAPPING[params.data.name] || params.data.name;
              const percentage = parseFloat(params.data.percentage).toFixed(2);
              return `${mappedName}\n(${percentage}%)`;
            },
          },
          data: chartData,
          itemStyle: {
            color: (params) =>
              CHART_COLORS[params.dataIndex % CHART_COLORS.length],
          },
        },
      ],
    };
    this.chartInstance.setOption(option, { notMerge: true });
    this.bindClickHandler(onClickHandler);
    setTimeout(() => this.chartInstance.resize(), 300);
  }
  updateCategoryChart(labels, data, sortBy, onClickHandler) {
    if (!this.ensureInitialized()) return;
    const chartData = labels.map((label, index) => {
      const item = data[index];
      const value =
        sortBy === "total" ? parseCurrencyToNumber(item.total) : item.qty;
      return {
        name: label,
        value: isNaN(value) ? 0 : value,
        kode: item.kode,
        kategori: item.kategori,
        total: item.total,
        qty: item.qty,
        persen_qty: item.persen_qty,
        persen_rp: item.persen_rp,
      };
    });
    const option = {
      animationDuration: CHART_ANIMATION_CONFIG.duration,
      animationEasing: CHART_ANIMATION_CONFIG.easing,
      tooltip: {
        trigger: "item",
        showDelay: 0,
        formatter: (params) => {
          if (sortBy === "total") {
            return `${params.name}<br/>Qty: ${params.data.qty
              }<br/>Total: Rp ${params.value.toLocaleString()}`;
          } else {
            return `${params.name}<br/>Qty: ${params.value}<br/>Total: ${params.data.total}`;
          }
        },
      },
      toolbox: {
        show: true,
        feature: {
          saveAsImage: {
            show: true,
            title: "Simpan Gambar",
          },
        },
      },
      series: [
        {
          type: "pie",
          roam: true,
          scaleLimit: {
            min: 0.5,
            max: 4,
          },
          selectedMode: "single",
          radius: ["30%", "80%"],
          center: ["50%", "50%"],
          minAngle: 2,
          avoidLabelOverlap: true,
          itemStyle: {
            borderRadius: 6,
            borderColor: "#fff",
            borderWidth: 2,
          },
          emphasis: {
            focus: "self",
            scale: true,
            scaleSize: 20,
            label: {
              show: true,
              fontSize: 15,
              fontWeight: "bold",
            },
            itemStyle: {
              shadowBlur: 15,
              shadowOffsetX: 0,
              shadowColor: "rgba(0, 0, 0, 0.6)",
              borderWidth: 4,
              borderColor: "#fff",
            },
          },
          label: {
            show: true,
            fontSize: 11,
            position: "outer",
            alignTo: "edge",
            margin: 20,
            edgeDistance: 10,
            lineHeight: 15,
            formatter: (params) => {
              const percentage =
                sortBy === "total"
                  ? params.data.persen_rp
                  : params.data.persen_qty;
              const persenFix = !isNaN(Number(percentage))
                ? Number(percentage).toFixed(2)
                : "0.00";
              if (persenFix < 1) {
                return `${params.name.substring(0, 15)}...\n${persenFix}%`;
              }
              return `${params.name}\n(${persenFix}%)`;
            },
            overflow: "break",
            distanceToLabelLine: 5,
          },
          labelLine: {
            show: true,
            length: 15,
            length2: 30,
            smooth: 0.2,
            lineStyle: {
              width: 1.5,
            },
          },
          data: chartData,
          itemStyle: {
            color: (params) =>
              CHART_COLORS[params.dataIndex % CHART_COLORS.length],
          },
        },
      ],
    };
    this.chartInstance.setOption(option, { notMerge: true });
    this.bindClickHandler(onClickHandler);
    this.chartInstance.off("mousemove");
    this.chartInstance.on("mousemove", (params) => {
      if (params.componentType === "series" && params.dataIndex !== undefined) {
        this.chartInstance.dispatchAction({
          type: "highlight",
          seriesIndex: 0,
          dataIndex: params.dataIndex,
        });
      }
    });
    this.chartInstance.off("mouseout");
    this.chartInstance.on("mouseout", (params) => {
      if (params.componentType === "series") {
        this.chartInstance.dispatchAction({
          type: "downplay",
          seriesIndex: 0,
        });
      }
    });
    setTimeout(() => this.chartInstance.resize(), 300);
  }

  updateDetailChart(labels, data, sortBy) {
    if (!this.ensureInitialized()) return;
    const chartData = labels.map((label, index) => {
      const isRp = sortBy === "total";
      return {
        name: label,
        value: isRp
          ? parseCurrencyToNumber(data[index].total)
          : data[index].value,
        tanggal: data[index].periode,
        persen_qty: Number(data[index].persen_qty).toFixed(2),
        persen_rp: Number(data[index].persen_rp).toFixed(2),
        total: data[index].total,
      };
    });
    const option = {
      animationDuration: CHART_ANIMATION_CONFIG.duration,
      animationEasing: CHART_ANIMATION_CONFIG.easing,
      tooltip: {
        trigger: "item",
        formatter: (params) => {
          const date = params.data.tanggal || "Tanggal tidak tersedia";
          if (sortBy === "total") {
            return `Tanggal: ${date}<br/>Total: ${params.data.total} (${params.data.persen_rp}%)`;
          } else {
            return `Tanggal: ${date}<br/>Terjual: ${params.value} (${params.data.persen_qty}%)`;
          }
        },
      },
      toolbox: {
        show: true,
        feature: {
          magicType: {
            type: ["line", "bar"],
            title: {
              line: "Tampilan Garis",
              bar: "Tampilan Batang",
            },
          },
          saveAsImage: {
            show: true,
            title: "Simpan Gambar",
          },
        },
      },
      xAxis: {
        type: "category",
        name: "Periode",
        data: chartData.map((item) => item.tanggal),
        axisLabel: {
          interval: 0,
          rotate: 30,
        },
      },
      yAxis: {
        type: "value",
        name: sortBy === "total" ? "Rupiah" : "Quantity",
        axisLabel: {
          formatter: (value) => {
            return typeof value === "number" ? value.toLocaleString() : value;
          },
        },
      },
      series: [
        {
          type: "bar",
          label: {
            show: true,
            rotate: 74,
            align: "left",
            verticalAlign: "bottom",
            position: "insideBottom",
            color: "#f2eded",
            fontSize: 14,
            formatter: (item) => {
              const percentage =
                sortBy === "total" ? item.data.persen_rp : item.data.persen_qty;
              return percentage !== undefined ? `${percentage}%` : "N/A";
            },
          },
          data: chartData,
          itemStyle: {
            color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [
              { offset: 0, color: "#83bff6" },
              { offset: 0.5, color: "#188df0" },
              { offset: 1, color: "#188df0" },
            ]),
          },
        },
      ],
    };
    this.chartInstance.setOption(option, { notMerge: false });
    this.chartInstance.off("magictypechanged");
    this.chartInstance.on("magictypechanged", (event) => {
      const newType = event.currentType;
      this.chartInstance.setOption({
        series: [
          {
            label: { show: newType === "line" ? false : true },
          },
        ],
      });
    });
    this.chartInstance.off("click");
    setTimeout(() => this.chartInstance.resize(), 300);
  }

  bindClickHandler(handler) {
    if (!this.chartInstance || typeof handler !== "function") return;
    this.chartInstance.off("click");
    this.chartInstance.on("click", handler);
    this.clickHandler = handler;
  }

  removeClickHandler() {
    if (this.chartInstance) {
      this.chartInstance.off("click");
      this.clickHandler = null;
    }
  }

  ensureInitialized() {
    if (!this.isInitialized || !this.chartInstance) {
      console.warn("Chart not initialized");
      return this.initialize();
    }
    return true;
  }

  show() {
    const chartElement = document.getElementById(ELEMENT_IDS.CHART_DIAGRAM);
    if (chartElement) {
      chartElement.style.display = "block";
    }
  }

  hide() {
    const chartElement = document.getElementById(ELEMENT_IDS.CHART_DIAGRAM);
    if (chartElement) {
      chartElement.style.display = "none";
    }
  }

  resize() {
    if (this.chartInstance && this.isInitialized) {
      this.chartInstance.resize();
    }
  }

  dispose() {
    if (this.chartInstance) {
      this.chartInstance.dispose();
      this.chartInstance = null;
      this.isInitialized = false;
      this.clickHandler = null;
    }
    window.removeEventListener("resize", this.handleResize.bind(this));
  }
}
const chartManager = new ChartManager();
export default chartManager;
