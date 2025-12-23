const echartDiagram = echarts.init(document.getElementById("chartDiagram"));
document.getElementById("wrapper-table").style.display = "none";
document.getElementById("btn-back").style.display = "none";
document.getElementById("sort-filter").style.display = "none";
document.getElementById("sort-filter1").style.display = "none";

window.addEventListener("resize", function () {
  if (echartDiagram) {
    echartDiagram.resize();
  }
});
const nameMapping = {
  BABY: "BABY STORE",
  DST: "DEPARTEMEN STORE",
  SPM: "SUPERMARKET",
};
let storeCode = "";
let cachedChartData = null;
let cachedChartMode = null;
let chartHistoryStack = [];
var storeCodes = {
  ABIN: "1502",
  ACE: "1505",
  ACIB: "1379",
  ACIL: "1504",
  ACIN: "1641",
  ACSA: "1902",
  ADET: "1376",
  ADMB: "3190",
  AHA: "1506",
  AHIN: "2102",
  ALANG: "1503",
  ANGIN: "2102",
  APEN: "1908",
  APIK: "3191",
  APRS: "1501",
  ARAW: "1378",
  ARUNG: "1611",
  ASIH: "2104",
  ATIN: "1642",
  AWIT: "1377",
  AXY: "2103",
};

var allCabang = Object.values(storeCodes);
// Event listener ketika cabang berubah
$("#cabang").on("change", function () {
  var selectedBranch = $(this).val(); // Ambil nilai cabang yang dipilih
  if (selectedBranch === "SEMUA CABANG") {
    storeCode = allCabang.join(",");
  } else {
    storeCode = storeCodes[selectedBranch] || ""; // Set nilai input kode_store
  }
});
// Trigger event saat halaman dimuat untuk mengisi nilai awal
$("#cabang").trigger("change");
document.getElementById("btn-back").style.display = "none";
document.getElementById("chartDiagram").style.display = "none";

const btnSend = document.getElementById("btn-send");
const btnBack = document.getElementById("btn-back");

btnSend.addEventListener("click", (e) => {
  e.preventDefault();
  console.log("test");
  var startDateInput = $("#date").val();
  var endDateInput = $("#date1").val();
  if (document.getElementById("cabang").value === "none") {
    Swal.fire({
      icon: "error",
      title: "Silahkan Pilih Cabang",
    });
  } else {
    sendDataFromBody(startDateInput, endDateInput, "allCate", storeCode);
    document.getElementById("btn-back").style.display = "none";
    document.getElementById("chartDiagram").style.display = "block";
  }
});
btnBack.addEventListener("click", (e) => {
  e.preventDefault();
  restoreChartFromCache();
});
function setFullCache({ chartMode, labels, chartData, tableMode, tableData }) {
  // Cek apakah state terakhir sama → skip
  const last = chartHistoryStack[chartHistoryStack.length - 1];
  labels = labels || []; // Default ke array kosong jika labels null/undefined
  chartData = chartData || []; // Default ke array kosong jika chartData null/undefined
  tableData = tableData || []; // Default ke array kosong jika tableData null/undefined
  if (
    last &&
    JSON.stringify(last) ===
      JSON.stringify({
        type: "full",
        chartMode,
        labels,
        chartData,
        tableMode,
        tableData,
      })
  ) {
    return;
  }

  chartHistoryStack.push({
    type: "full",
    chartMode,
    labels,
    chartData,
    tableMode,
    tableData,
  });
}
function getFullCache() {
  const last = chartHistoryStack[chartHistoryStack.length - 1];
  if (last && last.type === "full") {
    return {
      labels: last.labels,
      chartData: last.chartData,
      tableData: last.tableData,
    };
  }
  return null;
}
function restoreChartFromCache() {
  chartHistoryStack.pop(); // remove current

  const previousState = chartHistoryStack[chartHistoryStack.length - 1];
  if (!previousState || previousState.type !== "full") return;

  const { chartMode, labels, chartData, tableMode, tableData } = previousState;

  // Restore chart
  cachedChartData = { labels, data: chartData };
  cachedChartMode = chartMode;

  switch (chartMode) {
    case "early":
      updateChartEarly(labels, chartData);
      document.getElementById("sort-filter").style.display = "none";
      document.getElementById("sort-filter1").style.display = "none";
      break;
    case "category":
      updateChartCat(labels, chartData);
      document.getElementById("sort-filter").style.display = "block";
      document.getElementById("sort-filter1").style.display = "none";
      break;
    case "detail":
      updateChartDetailCatSup(labels, chartData);
      document.getElementById("sort-filter").style.display = "none";
      document.getElementById("sort-filter1").style.display = "block";
      break;
  }

  // Restore table
  renderTableWithData(tableMode, tableData);
}

function renderTableWithData(mode, data) {
  renderTableFromData(data);
}
function sendDataFromBody(tanggalAwal, tanggalAkhir, query, kodeStore) {
  // document.getElementById("sort-filter").style.display = "block";
  showProgressBar();
  const jsonData = {
    kd_store: kodeStore,
    start_date: tanggalAwal,
    end_date: tanggalAkhir,
    query: query,
  };

  fetch("/src/api/category/post_data_sales_category", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify(jsonData),
  })
    .then((response) => {
      if (!response.ok) {
        throw new Error("Network response error");
      }
      return response.json();
    })
    .then((data) => {
      // Mengecek apakah status "success" dan data ada
      if (data.status === "success" && data.data.length >= 0) {
        // Ambil label dan nilai (value, persentase) dari data
        let labels = data.data.map((item) => item.type_kategori);
        let values = data.data.map((item) => ({
          value: item.total_qty,
          persentase: item.persentase,
          uang: formatCurrency(item.total),
        }));
        completeProgressBar();
        // Panggil fungsi updateChart untuk memperbarui grafik
        updateChartEarly(labels, values);
        // Setelah fetch dan update chart/table selesai
        setFullCache({
          chartMode: "early",
          labels: labels,
          chartData: values,
          tableMode: "early",
          tableData: ["input"],
        });
      } else {
        console.error("Data tidak ditemukan atau status tidak 'success'");
      }
    })
    .catch((error) => {
      console.error("Gagal mengirim data:", error);
      completeProgressBar();
    });
}

function sendCategory(tanggalAwal, tanggalAkhir, query, kodeStore, filter) {
  showProgressBar();
  document.getElementById("sort-filter").style.display = "block";
  document.getElementById("sort-filter1").style.display = "none";
  const jsonData = {
    kd_store: kodeStore,
    start_date: tanggalAwal,
    end_date: tanggalAkhir,
    query: query,
    filter: filter,
  };

  fetch(`/src/api/category/post_data_sales_category?filter=${filter}`, {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify(jsonData),
  })
    .then((response) => {
      if (!response.ok) {
        throw new Error("Network response error");
      }
      return response.json();
    })
    .then((data) => {
      // Mengecek apakah status "success" dan data ada
      if (data.status === "success" && data.data.length > 0) {
        // Ambil label dan nilai (value, persentase) dari data
        let formatted = data.data.map((item) => ({
          nama_supplier: item.nama_supp,
          kode_supplier: item.kode_supp,
          qty: item.total_qty,
          kategori: item.type_kategori,
          total: formatCurrency(item.total),
        }));
        let labels = data.data.map((item) => item.nama_supp);
        let values = data.data.map((item) => ({
          kategori: item.type_kategori,
          kode: item.kode_supp,
          qty: item.total_qty,
          total: formatCurrency(item.total),
          persen_qty: item.persentase,
          persen_rp: item.persentase_rp,
        }));
        completeProgressBar();
        renderTableFromData(formatted);
        updateChartCat(labels, values);
        setFullCache({
          chartMode: "category",
          labels: labels,
          chartData: values,
          tableMode: "category",
          tableData: formatted,
        });
      } else {
        completeProgressBar();
        console.error("Data tidak ditemukan atau status tidak 'success'");
      }
    })
    .catch((error) => {
      completeProgressBar();
      console.error("Gagal mengirim data:", error);
    });
}
function formatCurrency(amount) {
  return `Rp ${amount.toLocaleString("id-ID")}`; // Format dengan pemisah ribuan
}

function sendKodeSupp(
  tanggalAwal,
  tanggalAkhir,
  kodeSupp,
  kodeStore,
  kategori,
  filter
) {
  document.getElementById("sort-filter").style.display = "none";
  document.getElementById("sort-filter1").style.display = "block";
  showProgressBar();
  const jsonData = {
    kategori: kategori,
    kode_supp: kodeSupp,
    kd_store: kodeStore,
    start_date: tanggalAwal,
    end_date: tanggalAkhir,
    filter: filter,
  };

  fetch(`/src/api/category/post_data_sales_category?filter=${filter}`, {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify(jsonData),
  })
    .then((response) => {
      if (!response.ok) {
        throw new Error("Network response error");
      }
      return response.json();
    })
    .then((data) => {
      const sortBy = $("#sort-by1").val();
      // Mengecek apakah status "success" dan data ada
      if (data.status === "success" && data.data.length > 0) {
        let labels = data.data.map((item) => item.descp);
        let values = data.data.map((item) => ({
          periode: item.periode,
          kategori: item.kategori,
          value: item.total_qty,
          persen_qty: item.persentase,
          persen_rp: item.persentase_rp,
          total: formatCurrency(item.Total),
        }));
        let supplier = data.supplierTable.map((item) => ({
          Barcode: item.barcode,
          Product: item.nama_barang,
          Qty: item.total_qty,
          Total: formatCurrency(item.total),
        }));
        completeProgressBar();
        updateChartDetailCatSup(labels, values, sortBy);
        renderTableFromData(supplier);
        setFullCache({
          chartMode: "detail",
          labels: labels,
          chartData: values,
          tableMode: "detail",
          tableData: supplier,
        });
        // Panggil fungsi updateChart untuk memperbarui grafik
        // updateChartEarly(labels, values);
      } else {
        completeProgressBar();
        console.error("Data tidak ditemukan atau status tidak 'success'");
      }
    })
    .catch((error) => {
      completeProgressBar();
      console.error("Gagal mengirim data:", error);
    });
}

function updateChartEarly(labels, data) {
  document.getElementById("btn-back").style.display = "none";
  document.getElementById("wrapper-table").style.display = "none";
  const valueFilter = $("#sort-by").val();
  let newData = labels.map((label, index) => ({
    name: label,
    value: data[index].value,
    uang: data[index].uang,
    percentage: data[index].persentase,
  }));
  var startDateInput = $("#date").val();
  var endDateInput = $("#date1").val();
  // Pastikan echartDiagram tidak undefined
  if (!echartDiagram) {
    echartDiagram = echarts.init(document.getElementById("chartDiagram"));
  }

  // Set opsi baru
  echartDiagram.setOption(
    {
      animationDurationUpdate: 1500,
      animationEasingUpdate: "quinticInOut",
      xAxis: undefined, // Hapus sumbu X
      yAxis: undefined, // Hapus sumbu Y
      tooltip: {
        trigger: "item",
        formatter: (params) => {
          const mappedName = nameMapping[params.data.name] || params.data.name;
          const uang = params.data.uang;
          const percentage = parseFloat(params.data.percentage).toFixed(2);
          return `${mappedName} <br> Terjual : ${params.value} <br> Persentase: ${percentage}% <br/> Total: ${uang}`;
        },
      },
      series: [
        {
          type: "pie",
          label: {
            fontSize: 12,
            formatter: (params) => {
              const mappedName =
                nameMapping[params.data.name] || params.data.name;
              const percentage = parseFloat(params.data.percentage).toFixed(2);
              return `${mappedName} (${percentage}%)`;
            },
          },
          data: newData,
          itemStyle: {
            color: (params) => {
              let colors = [
                "rgba(255, 99, 132, 1)",
                "rgba(54, 162, 235, 1)",
                "rgba(255, 206, 86, 1)",
                "rgba(75, 192, 192, 1)",
                "rgba(153, 102, 255, 1)",
                "rgba(255, 159, 64, 1)",
              ];
              return colors[params.dataIndex % colors.length];
            },
          },
        },
      ],
    },
    { notMerge: true }
  );
  bindChartClick((params) => {
    let kategori = params.name;
    sendCategory(
      startDateInput,
      endDateInput,
      kategori,
      storeCode,
      valueFilter
    );
  });
  // Pastikan resize setelah update
  setTimeout(() => {
    echartDiagram.resize();
  }, 300);
}

function updateChartCat(labels, data) {
  document.getElementById("btn-back").style.display = "block";
  document.getElementById("wrapper-table").style.display = "block";

  const sortBy = document.getElementById("sort-by").value;
  const valueFilter = $("#sort-by").val();
  let newData = labels.map((label, index) => {
    const item = data[index];
    const value = sortBy === "total" ? toAngka(item.total) : item.qty;

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

  var startDateInput = $("#date").val();
  var endDateInput = $("#date1").val();

  if (!echartDiagram) {
    echartDiagram = echarts.init(document.getElementById("chartDiagram"));
  }

  echartDiagram.setOption(
    {
      animationDurationUpdate: 1500,
      animationEasingUpdate: "quinticInOut",
      xAxis: undefined,
      yAxis: undefined,
      tooltip: {
        trigger: "item",
        formatter: (params) => {
          if (sortBy === "total") {
            return `${params.name}: ${
              params.data.qty
            } <br> Rp ${params.value.toLocaleString()}`;
          } else {
            return `${params.name}: ${params.value}<br> Total Rp: ${params.data.total}`;
          }
        },
      },
      series: [
        {
          type: "pie",
          label: {
            fontSize: 12,
            formatter: (params) => {
              let p =
                sortBy === "total"
                  ? params.data.persen_rp
                  : params.data.persen_qty;
              const persenFix = !isNaN(Number(p))
                ? Number(p).toFixed(2)
                : "0.00";
              return `${params.name} (${persenFix}%)`;
            },
          },
          data: newData,
          itemStyle: {
            color: (params) => {
              let colors = [
                "rgba(255, 99, 132, 1)",
                "rgba(54, 162, 235, 1)",
                "rgba(255, 206, 86, 1)",
                "rgba(75, 192, 192, 1)",
                "rgba(153, 102, 255, 1)",
                "rgba(255, 159, 64, 1)",
              ];
              return colors[params.dataIndex % colors.length];
            },
          },
        },
      ],
    },
    { notMerge: true }
  );

  bindChartClick((params) => {
    let supplier = params.data.kode;
    let kategori = params.data.kategori;

    sendKodeSupp(
      startDateInput,
      endDateInput,
      supplier,
      storeCode,
      kategori,
      valueFilter
    );
  });

  setTimeout(() => {
    echartDiagram.resize();
  }, 300);
}

function updateChartDetailCatSup(labels, data, sortBy) {
  document.getElementById("wrapper-table").style.display = "block";

  let newData = labels.map((label, index) => {
    const isRp = sortBy === "total";

    return {
      name: label,
      value: isRp ? toAngka(data[index].total) : data[index].value, // ganti berdasarkan sort
      kode: data[index].kode,
      kategori: data[index].kategori,
      tanggal: data[index].periode,
      persen_qty: Number(data[index].persen_qty).toFixed(2),
      persen_rp: data[index].persen_rp.toFixed(2),
      total: data[index].total,
    };
  });
  // Pastikan echartDiagram tidak undefined
  if (!echartDiagram) {
    echartDiagram = echarts.init(document.getElementById("chartDiagram"));
  }

  // Set opsi baru
  echartDiagram.setOption(
    {
      animationDurationUpdate: 1500,
      animationEasingUpdate: "quinticInOut",
      tooltip: {
        trigger: "item",
        formatter: (params) => {
          const date = params.data.tanggal || "Tanggal tidak tersedia";
          if (sortBy === "total") {
            return `Tanggal: ${date} <br> Total: ${params.data.total} (${params.data.persen_rp}%)`;
          } else {
            return `Tanggal: ${date} <br> Terjual: ${params.value} (${params.data.persen_qty}%)`;
          }
        },
      },
      toolbox: {
        show: true,
        feature: {
          magicType: {
            type: ["line", "bar"], // 👈 Ganti antara line dan bar
            title: {
              line: "Tampilan Garis",
              bar: "Tampilan Batang",
            },
          },
          saveAsImage: {
            show: true,
            title: "Simpan",
          },
        },
      },
      xAxis: {
        type: "category",
        name: "Periode",
        data: newData.map((item) => item.tanggal),
        axisLabel: {
          interval: 0,
          rotate: 30, // biar nggak numpuk kalau label panjang
        },
      },
      yAxis: {
        type: "value",
        name: sortBy === "total" ? "Rp" : "Qty", // Misalnya untuk menampilkan "Rp"
        axisLabel: {
          formatter: function (value) {
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
            formatter: function (item) {
              const percentage =
                sortBy === "total" ? item.data.persen_rp : item.data.persen_qty;
              return percentage !== undefined ? `${percentage}%` : "N/A";
            },
          },
          data: newData,
          itemStyle: {
            color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [
              { offset: 0, color: "#83bff6" },
              { offset: 0.5, color: "#188df0" },
              { offset: 1, color: "#188df0" },
            ]),
          },
        },
      ],
    },
    { notMerge: false }
  );
  echartDiagram.on("magictypechanged", function (event) {
    let newType = event.currentType; // Dapatkan jenis chart yang dipilih
    echartDiagram.setOption({
      series: [
        {
          label: { show: newType === "line" ? false : true },
        },
      ],
    });
  });
  // Pastikan resize setelah update
  setTimeout(() => {
    echartDiagram.resize();
  }, 300);
  echartDiagram.off("click");
}
function customizeDataTableLayout(tableId) {
  const $wrapper = $(`#${tableId}`).closest(".dataTables_wrapper");

  $wrapper
    .find(".dataTables_length label")
    .addClass("text-sm text-gray-600 flex items-center gap-2");
  $wrapper
    .find(".dataTables_length select")
    .addClass("px-2 py-1 border rounded-lg");

  $wrapper
    .find(".dataTables_filter label")
    .addClass("text-sm text-gray-600 flex items-center gap-2");
  $wrapper
    .find(".dataTables_filter input")
    .addClass("px-2 py-1 border rounded-lg");
}
function stylePaginationButtons() {
  $(".dataTables_paginate a").addClass(
    "px-3 py-1 border rounded-lg text-sm text-gray-700 hover:bg-pink-100 cursor-pointer"
  );
  $(".dataTables_paginate .current")
    .removeClass("text-gray-700 hover:bg-pink-100")
    .addClass("bg-pink-500 text-white font-semibold border-pink-500");

  $("#dataCategoryTable tbody tr").addClass("text-sm md:text-base");
  $("#dataCategoryTable tbody td").addClass("px-2 py-2");
  $("#dataCategoryTable thead th").addClass("text-center");
}

// Render Tabel
function renderTableFromData(dataArray) {
  const tableId = "#dataCategoryTable";

  if ($.fn.DataTable.isDataTable(tableId)) {
    $(tableId).DataTable().destroy();
    $(tableId).empty();
  }

  const columns = [
    {
      title: "No",
      data: null,
      render: function (data, type, row, meta) {
        return meta.row !== undefined ? meta.row + 1 : "-";
      },
      className: "text-center",
      width: "30px",
    },
    ...Object.keys(dataArray[0]).map((key) => ({
      title:
        key.toLowerCase() === "barcode"
          ? "BARCODE"
          : key.charAt(0).toUpperCase() + key.slice(1).replace(/_/g, " "),
      data: key,
    })),
  ];

  const dataTable = $(tableId).DataTable({
    data: dataArray,
    columns: columns,
    dom: '<"topbar flex flex-wrap md:flex-nowrap justify-between items-center gap-4 mb-4"lf<"#custom-filters">>t<"bottombar flex justify-between items-center mt-4"ip>',
    responsive: true,
    autoWidth: false,
    scrollX: false,
    language: {
      search: "Cari:",
      searchPlaceholder: "Ketik untuk mencari...",
      lengthMenu: "Tampilkan _MENU_ data",
      info: "Menampilkan _START_ - _END_ dari _TOTAL_ data",
      paginate: {
        first: "Awal",
        last: "Akhir",
        next: "›",
        previous: "‹",
      },
      emptyTable: "Data belum tersedia",
    },
    columnDefs: [
      {
        targets: "_all",
        createdCell: function (td) {
          td.style.whiteSpace = "normal"; // Agar teks wrap
          td.style.wordWrap = "break-word"; // Memastikan kata panjang bisa terputus dan tetap dalam batas kolom
          td.style.width = "auto"; // Menyesuaikan lebar kolom dengan isi teks
          td.style.minWidth = "100px"; // Tentukan lebar minimum kolom jika diperlukan
        },
      },
    ],
    drawCallback: function () {
      // Memastikan styling tetap konsisten setiap kali rendering ulang
      $(`${tableId} thead th`).addClass(
        "bg-pink-500 text-white text-sm font-semibold"
      );
      $(`${tableId} tbody td`).addClass("text-sm text-gray-700 border-b");
      $(`${tableId} tbody tr`).addClass(
        "hover:bg-pink-50 hover:scale-[101%] transition-all duration-150 ease-in-out"
      );

      stylePaginationButtons();
    },
  });

  customizeDataTableLayout(tableId.replace("#", ""));
  bindExportButtons(dataTable);
}

// Export PDF OR EXCEL
function bindExportButtons(dataTableInstance) {
  // Inject tombol export sebagai hidden
  const store = document.getElementById("cabang").value;
  var startDateInput = $("#date").val();
  var endDateInput = $("#date1").val();
  new $.fn.dataTable.Buttons(dataTableInstance, {
    buttons: [
      {
        extend: "excelHtml5",
        className: "buttons-excel d-none",
        exportOptions: {
          columns: ":visible",
        },
        filename: `Laporan_Penjualan_${store}_${startDateInput}_${endDateInput}`,
      },
      {
        extend: "pdfHtml5",
        className: "buttons-pdf d-none",
        exportOptions: {
          columns: ":visible",
        },
        filename: `Laporan_Penjualan_${store}_${startDateInput}_${endDateInput}`,
        orientation: "potrait",
        pageSize: "A4",
      },
    ],
  });
  dataTableInstance.buttons(0, null).container().appendTo(document.body);

  // Trigger tombol via icon button
  $("#exportExcel")
    .off("click")
    .on("click", function (e) {
      e.preventDefault();
      dataTableInstance.button(".buttons-excel").trigger();
    });
  $("#exportPDF")
    .off("click")
    .on("click", function (e) {
      e.preventDefault();
      dataTableInstance.button(".buttons-pdf").trigger();
    });
}

// Binding CLICK BTN
function bindChartClick(handlerFn) {
  if (!echartDiagram) return;
  echartDiagram.off("click"); // Pastikan bersih
  echartDiagram.on("click", handlerFn); // Bind baru
}
// Format Tanggal Rentang 1 Bulan
function formatDate(date) {
  if (!date) return ""; // Jika tanggal kosong, kembalikan string kosong

  var d = new Date(date);
  if (isNaN(d.getTime())) {
    console.error("❌ Format tanggal tidak valid:", date);
    return date; // Jika tidak valid, kembalikan nilai asli untuk debugging
  }

  var day = d.getDate().toString().padStart(2, "0");
  var month = (d.getMonth() + 1).toString().padStart(2, "0");
  var year = d.getFullYear();

  return `${day}-${month}-${year}`; // Format yang benar untuk laporan
}

$("#sort-by").on("change", function () {
  const selected = $(this).val();

  const cached = getFullCache();
  if (!cached) return;

  let sortedTable = [...cached.tableData];
  let sortedChart = [...cached.chartData];
  let sortedLabels = [...cached.labels];

  if (selected === "total_qty") {
    // Sort berdasarkan qty (jumlah), descending
    sortedTable.sort((a, b) => parseFloat(b.qty) - parseFloat(a.qty));
    sortedChart.sort(
      (a, b) => parseFloat(b.persen_qty) - parseFloat(a.persen_qty)
    );
  } else if (selected === "total") {
    // Sort berdasarkan total (dalam format rupiah), descending
    sortedTable.sort((a, b) => toAngka(b.total) - toAngka(a.total));
    sortedChart.sort(
      (a, b) => parseFloat(b.persen_rp) - parseFloat(a.persen_rp)
    );
  }

  sortedLabels = sortedChart.map((item) => item.nama_supplier || item.kode);

  renderTableFromData(sortedTable);
  updateChartCat(sortedLabels, sortedChart);
});
$("#sort-by1").on("change", function () {
  const selected = $(this).val();

  const cached = getFullCache();
  if (!cached) return;

  let sortedTable = [...cached.tableData];
  let sortedChart = [...cached.chartData]; // Jangan disort
  let sortedLabels = [];

  // Mengurutkan data tabel berdasarkan pilihan filter
  if (selected === "total_qty") {
    sortedTable.sort((a, b) => parseFloat(b.Qty) - parseFloat(a.Qty));
  } else if (selected === "total") {
    sortedTable.sort((a, b) => toAngka(b.Total) - toAngka(a.Total));
  }

  // Memetakan sortedLabels dan chart data sesuai dengan pilihan (persen_rp atau persen_qty)
  sortedLabels = sortedChart.map((item) => {
    const persen =
      selected === "total"
        ? parseFloat(item.persen_rp)
        : parseFloat(item.persen_qty);
    const persenFix = isNaN(persen) ? "0.00" : persen.toFixed(2);
    return `${item.nama_supplier || item.kode} (${persenFix}%)`;
  });

  // Render tabel berdasarkan sortedTable
  renderTableFromData(sortedTable);

  // Update chart dengan labels yang sudah disesuaikan dan data chart yang baru
  updateChartDetailCatSup(sortedLabels, sortedChart, selected);
});

function toAngka(val) {
  return parseFloat(val.replace(/[^0-9,-]+/g, "").replace(",", "."));
}

// Mengatur Kondisi Input Dalam Kondisi Rentang 1 Bulan
var startDateInput = document.getElementById("date");
var endDateInput = document.getElementById("date1");
// Get today's date
if (startDateInput && endDateInput) {
  var today = new Date();
  // Set tanggal awal ke 30 hari sebelumnya
  var startDate = new Date();
  startDate.setDate(today.getDate() - 30);

  // Set tanggal akhir ke 1 hari sebelumnya
  var endDate = new Date();
  endDate.setDate(today.getDate() - 1);

  // Atur nilai input
  startDateInput.value = formatDate(startDate);
  endDateInput.value = formatDate(endDate);
} else {
  console.error("Elemen input tanggal tidak ditemukan di DOM!");
}

document.addEventListener("DOMContentLoaded", function () {
  flatpickr("#date", {
    dateFormat: "d-m-Y",
    allowInput: true,
  });

  flatpickr("#date1", {
    dateFormat: "d-m-Y",
    allowInput: true,
  });
});
