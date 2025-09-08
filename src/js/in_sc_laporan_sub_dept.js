//ATUR AGAR TIDAK MUAT HALAMAN----------------------------------------------------------------START
let tableDataCache = [];
let dataCharBar = [];
let chartInstance; // Global chart instance
let chartDataGlobal = []; // ← simpan newData terbaru
let currentPage = 1;
let isSubdeptActive = false;
let isPromo = false;
let totalPages = 0;
var barChart = echarts.init(document.getElementById("barDiagram"));
var pieChart = echarts.init(document.getElementById("chartDiagram"));
window.addEventListener("resize", function () {
  pieChart.resize();
  barChart.resize();
});

let activeQueryType = sessionStorage.getItem("activeQueryType") || "query1"; // Default query type

// Tangkap tombol yang diklik dan simpan query_type
document.querySelectorAll("button[name='query_type']").forEach((button) => {
  button.addEventListener("click", function () {
    activeQueryType = this.value;
    sessionStorage.setItem("activeQueryType", activeQueryType);
    console.log("Tombol ditekan, activeQueryType sekarang:", activeQueryType);
  });
});

document.getElementById("bar").style.display = "none";
document.getElementById("pie").style.display = "none";
document.getElementById("btn-back").style.display = "none";
document.getElementById("container-table").style.display = "none";

document.addEventListener("DOMContentLoaded", function () {
  const sidebar = document.getElementById("sidebar");
  const closeBtn = document.getElementById("closeSidebar");

  closeBtn.addEventListener("click", function () {
    sidebar.classList.remove("open"); // Hilangkan class .open agar sidebar tertutup
  });
});

document.getElementById("btn-submit").addEventListener("click", function (e) {
  const filter = $("#sort-by").val();
  e.preventDefault();
  currentPage = 1;
  loadPage(currentPage, filter);
});

document.getElementById("btn-sub").addEventListener("click", function (e) {
  e.preventDefault();
  const filter = $("#sort-by").val();
  currentPage = 1;
  btnSubSend(currentPage, filter);
  document.getElementById("btn-back").style.display = "block";
});

document.getElementById("btn-see-penjualan").addEventListener("click", (e) => {
  e.preventDefault();
  document.querySelector("#btn-promo").click();
});
document
  .getElementById("btn-see-promo")
  .addEventListener("click", function (e) {
    e.preventDefault();
    var cabangText =
      document.getElementById("cabang").options[
        document.getElementById("cabang").selectedIndex
      ].text;
    var startDate = $("#date").val(); // Ambil nilai langsung dari datepicker
    var endDate = $("#date1").val();
    var reportHeader = document.getElementById("reportHeaderPromo");
    reportHeader.innerHTML = `Data Promo<br><p> Cabang :  ${cabangText} (${startDate} s/d ${endDate})</p>`;
    let formData = new FormData();
    const filter = $("#sort-by1").val();
    isSubdeptActive = true;
    isPromo = true;
    Swal.fire({
      title: "Loading...",
      html: "Tunggu Sebentar",
      allowOutsideClick: false,
      timerProgressBar: true,
      didOpen: () => {
        Swal.showLoading();
      },
    });

    formData.append("ajax", true);
    formData.append("kd_store", document.querySelector("#kd_store")?.value);
    formData.append("start_date", document.querySelector("#date")?.value);
    formData.append("end_date", document.querySelector("#date1")?.value);
    formData.append("subdept", document.querySelector("#subdept")?.value || "");
    // formData.append("limit", document.querySelector("#limitData").value);
    formData.append(
      "kode_supp",
      document.querySelector("#kode_supp")?.value || ""
    );
    formData.append("page", currentPage);
    formData.append("query_type", "query3");

    console.log("🔄 Mengirim data ke server:", Object.fromEntries(formData));

    $.ajax({
      url: `../../api/subdepartemen/post_data_sub_dept.php?filter=${filter}`,
      method: "POST",
      data: formData,
      processData: false,
      contentType: false,
      success: function (response) {
        console.log("✅ Response dari server (RAW):", response);
        let jsonResponse;
        try {
          jsonResponse =
            typeof response === "string" ? JSON.parse(response) : response;
          console.log("📋 Parsed JSON Response:", jsonResponse);
        } catch (error) {
          console.error("❌ Gagal parsing JSON:", error, response);
          return;
        }
        if (jsonResponse && jsonResponse.status === "success") {
          if (jsonResponse.tableData) {
            tableDataCache = jsonResponse.tableData;
            totalPages = jsonResponse.totalPages ? jsonResponse.totalPages : 1;
            console.log("📌 Total Pages Updated:", totalPages);
            Swal.close();
            localStorage.setItem(
              "salesTableOriginal",
              JSON.stringify(tableDataCache)
            );

            updateTable(tableDataCache, "salesTablePromo");
            updateThead("thHeadPromo");
            // updatePagination(); // 🔄 UPDATE PAGINATION
          } else {
            console.warn(
              "⚠️ Table Data Tidak Ditemukan di Response:",
              jsonResponse
            );
          }
        } else {
          console.warn("⚠️ Server mengembalikan status error:", jsonResponse);
        }
      },
    });
  });
document.body.addEventListener("click", function (e) {
  const filter = $("#sort-by").val();
  var barChart = echarts.init(document.getElementById("barDiagram"));
  var pieChart = echarts.init(document.getElementById("chartDiagram"));
  setTimeout(() => {
    pieChart.resize();
    barChart.resize();
  }, 200);
  activeQueryType = sessionStorage.getItem("activeQueryType") || "query1";
  if (e.target.id === "btn-back") {
    e.preventDefault();
    currentPage = 1;
    if (isSubdeptActive && !isPromo) {
      isPromo = false;
      loadPage(currentPage, filter);
      if (activeQueryType === "query2") {
        activeQueryType = "query1";
      } else {
        activeQueryType = "query1";
      }
    } else {
      isPromo = false;
      document.querySelector("#kode_supp").value = "";
      btnSubSend(currentPage, filter);
      if (activeQueryType === "query4") {
        activeQueryType = "query2";
      } else {
        activeQueryType = "query2";
      }
    }
  }
  sessionStorage.setItem("activeQueryType", activeQueryType);
  console.log(
    "Setelah klik btn-back, activeQueryType sekarang:",
    sessionStorage.getItem("activeQueryType")
  );
});

function resetPieChart() {
  var pieChart = echarts.init(document.getElementById("chartDiagram"));
  pieChart.clear();
  pieChart.resize;
}
document.getElementById("sort-by").addEventListener("change", function () {
  const sortBy = this.value;
  const tableData = JSON.parse(localStorage.getItem("salesTableOriginal"));
  if (!tableData) return;

  let sortedTable = [...tableData];

  if (sortBy === "Qty") {
    sortedTable.sort((a, b) => b.Qty - a.Qty);
  } else {
    sortedTable.sort((a, b) => {
      const numA = a.Total || 0;
      const numB = b.Total || 0;
      return numB - numA;
    });
  }

  const labels = sortedTable.map(
    (item) => item.nama_subdept || item.nama_barang || item.kode
  );

  const data = sortedTable.map((item) => {
    const kode = item.kode || item.kode_subdept || "";
    const nama = item.nama_subdept || item.nama_barang || "";
    const value = sortBy === "Qty" ? item.Qty : item.Total || 0;
    return `${kode},${nama},${value}`;
  });

  updateChart(labels, data, sortedTable);
  updatePieChart(labels, data, sortedTable);
  updateTable(sortedTable, "salesTable");
  updateTable(sortedTable, "salesTableSupplier");
});
document.getElementById("sort-by1").addEventListener("change", function () {
  const selected = this.value;
  const tableData = JSON.parse(localStorage.getItem("chartBart"));
  if (!tableData) return;

  const tableDataOri = JSON.parse(localStorage.getItem("salesTableOriginal"));

  let sortedTable = [...tableDataOri];

  if (selected === "Qty") {
    sortedTable.sort((a, b) => b.Qty - a.Qty);
  } else {
    sortedTable.sort((a, b) => {
      const numA = a.Total || 0;
      const numB = b.Total || 0;
      return numB - numA;
    });
  }

  // Tidak melakukan sort
  const updatedLabels = tableData.map((item) => {
    const persen =
      selected === "Total"
        ? parseFloat(item.persentase_rp)
        : parseFloat(item.Percentage);
    const persenFix = isNaN(persen) ? "0.00" : persen.toFixed(2);
    return `${item.promo} (${persenFix}%)`;
  });

  const chartData = tableData.map((item) =>
    selected === "Qty" ? parseFloat(item.Qty) : parseFloat(item.Total)
  );

  updateBarChart(updatedLabels, chartData, tableData);
  updateTable(sortedTable, "salesTablePromo");
  updateTable(sortedTable, "salesTablePenjualan");
});

function loadPage(page, filter) {
  showLoading();
  prepareUI();

  const formData = new FormData(document.getElementById("laporanForm"));
  const startDate = $("#date").val();
  const endDate = $("#date1").val();
  const cabang = getSelectedText("cabang");

  formData.append("query_type", "query1");
  formData.append("page", page);
  formData.append("ajax", "true");
  formData.append("filter", filter);
  setReportHeaders(cabang, startDate, endDate);

  fetch(`../../api/subdepartemen/post_data_sub_dept.php?filter=${filter}`, {
    method: "POST",
    headers: { "X-Requested-With": "XMLHttpRequest" },
    body: formData,
  })
    .then((res) => res.json())
    .then((response) => {
      if (response.status === "success") {
        const {
          labels,
          data: chartData,
          tableData,
          totalPages: pages = 1,
        } = response;
        totalPages = pages;
        updateChart(labels, chartData, tableData);
        localStorage.setItem("salesTableOriginal", JSON.stringify(tableData));
        updateTable(tableData, "salesTable");
        // updatePagination();
        Swal.close();
      } else {
        Swal.fire("Error", response.message, "error");
      }
    })
    .catch((error) => {
      console.error("Fetch error:", error);
      Swal.fire("Error", "Terjadi kesalahan saat mengambil data!", "error");
    });
}
function showLoading() {
  Swal.fire({
    title: "Loading...",
    html: "Tunggu Sebentar",
    allowOutsideClick: false,
    timerProgressBar: true,
    didOpen: () => Swal.showLoading(),
  });
}
function prepareUI() {
  document.getElementById("btn-see-supplier").style.display = "none";
  document.getElementById("btn-see-data").style.display = "block";
  document.getElementById("container-table").style.display = "block";
  document.getElementById("pie").style.display = "block";
  document.getElementById("bar").style.display = "none";
  document.getElementById("btn-back").style.display = "none";
  document.querySelector("#btn-sub").disabled = false;
  isSubdeptActive = false;
  isPromo = false;
}
function setReportHeaders(cabangText, startDate, endDate) {
  document.getElementById(
    "reportHeader"
  ).innerHTML = `Data Penjualan<br><p> Cabang :  ${cabangText} (${startDate} s/d ${endDate})</p>`;
  document.getElementById(
    "label-chart"
  ).innerHTML = `Penjualan Sub Departemen ${cabangText} (${startDate} s/d ${endDate})`;
}
function getSelectedText(selectId) {
  const select = document.getElementById(selectId);
  return select.options[select.selectedIndex].text;
}
//ATUR AGAR TIDAK MUAT HALAMAN----------------------------------------------------------------END

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
//ATUR TANGGAL----------------------------------------------------------------END

// echart js
function updateTable(data, tableId) {
  console.log("📋 Updating Table with Data:", data);
  var tableBody = document.querySelector(`#${tableId} tbody`);
  if (!tableBody) {
    console.error("❌ Table body tidak ditemukan!");
    return;
  }

  tableBody.innerHTML = "";

  if (!Array.isArray(data) || data.length === 0) {
    console.warn("⚠️ Data tabel kosong atau bukan array!", data);
    var newRow = tableBody.insertRow();
    var cell = newRow.insertCell(0);
    cell.colSpan = 4;
    cell.textContent = "Tidak ada data tersedia";
    cell.style.textAlign = "center";
    return;
  }

  data.forEach((row, index) => {
    var newRow = tableBody.insertRow();
    newRow.className = "hover:bg-blue-50 transition-all duration-200 shadow-sm"; // Tailwind untuk tampilan lebih rapi
    let cell0 = newRow.insertCell(0);
    let cell1 = newRow.insertCell(1);
    let cell2 = newRow.insertCell(2);
    let cell3 = newRow.insertCell(3);
    if (row.barcode) {
      let cell4 = newRow.insertCell(4);
      cell0.textContent = index + 1; // 🔹 Nomor urut berlanjut
      cell1.textContent = row.barcode;
      cell2.textContent = row.nama_subdept || row.kode_supp || row.promo;
      cell3.textContent = row.Qty;
      cell4.textContent = new Intl.NumberFormat("id-ID", {
        style: "decimal",
      }).format(row.Total);
      // 🔹 Atur posisi teks di tengah untuk TOP dan QTY
      cell0.classList.add("text-center");
      cell1.classList.add("text-center");
      cell2.classList.add("text-left");
      cell3.classList.add("text-center");
      cell4.classList.add("text-center");
    } else {
      cell0.textContent = index + 1; // 🔹 Nomor urut berlanjut
      cell1.textContent = row.nama_subdept || row.kode_supp || row.promo;
      cell2.textContent = row.Qty;
      cell3.textContent = row.Total.toLocaleString();

      // 🔹 Atur posisi teks di tengah untuk TOP dan QTY
      cell0.classList.add("text-center");
      cell1.classList.add("text-left");
      cell2.classList.add("text-center");
      cell3.classList.add("text-center");
    }
  });

  console.log("✅ Table updated successfully!");
}

// Fungsi untuk memperbarui chart
// Inisialisasi ECharts
var echartDiagram = echarts.init(document.getElementById("chartDiagram")); // Pie Chart
// Opsi default
var optionsChart = {
  series: [
    {
      name: "Total Penjualan",
      type: "pie",
      radius: "70%",
      data: [{ name: "No Data" }],
      emphasis: {
        itemStyle: {
          shadowBlur: 10,
          shadowOffsetX: 0,
          shadowColor: "rgba(0, 0, 0, 0.5)",
        },
      },
    },
  ],
  formatter: function (params) {
    return params.name; // ❌ Hanya tampilkan label, tanpa value
  },
};
// Set opsi awal ke dalam chart
echartDiagram.setOption(optionsChart);

// Event handler saat pie chart diklik
echartDiagram.on("click", function (params) {
  console.log(params); // Debugging
  let subdept = params.data.subdept || "";
  let kodeSupp = params.data.kode || "";

  document.querySelector("#subdept").value = subdept;
  document.querySelector("#kode_supp").value = kodeSupp;

  if (subdept.trim() !== "" && kodeSupp.trim() !== "") {
    document.querySelector("#btn-bar").click();
  } else if (subdept.trim() !== "") {
    let timerInterval;
    Swal.fire({
      title: "Loading...",
      html: "Tunggu Sebentar...",
      timer: 3500,
      timerProgressBar: true,
      didOpen: () => {
        Swal.showLoading();
      },
      willClose: () => {
        clearInterval(timerInterval);
      },
    }).then((result) => {
      /* Read more about handling dismissals below */
      if (result.dismiss === Swal.DismissReason.timer) {
        setTimeout(() => {
          echartDiagram.resize();
        }, 300);
      }
    });
    document.querySelector("#btn-sub").click();
  }
});

// Button Click Dari Pie Chart untuk mengirimkan data subdept dari ajax ke php
function btnSubSend(page, filter) {
  document.getElementById("btn-see-supplier").style.display = "block";
  document.getElementById("btn-see-data").style.display = "none";
  var cabangText =
    document.getElementById("cabang").options[
      document.getElementById("cabang").selectedIndex
    ].text;
  var startDate = $("#date").val(); // Ambil nilai langsung dari datepicker
  var endDate = $("#date1").val();
  var reportHeader = document.getElementById("reportHeaderSupplier");
  reportHeader.innerHTML = `Data Supplier<br><p> Cabang :  ${cabangText} (${startDate} s/d ${endDate})</p>`;
  Swal.fire({
    title: "Loading...",
    html: "Tunggu Sebentar",
    allowOutsideClick: false,
    timerProgressBar: true,
    didOpen: () => {
      Swal.showLoading();
    },
  });
  var pieChart = echarts.init(document.getElementById("chartDiagram"));

  setTimeout(() => {
    pieChart.resize();
  }, 300);

  document.getElementById("bar").style.display = "none";
  document.getElementById("btn-back").style.display = "block";
  document.getElementById("pie").style.display = "block";
  document.getElementById("container-table").style.display = "block";

  isSubdeptActive = true; // 🔹 Aktifkan mode subdept
  let formData = new FormData();
  var queryType = document.getElementById("btn-sub").value;
  formData.append("ajax", true);
  formData.append("query_type", queryType);
  formData.append("kd_store", document.querySelector("#kd_store")?.value);
  formData.append("start_date", document.querySelector("#date")?.value);
  formData.append("end_date", document.querySelector("#date1")?.value);
  formData.append("subdept", document.querySelector("#subdept").value);
  formData.append("filter", filter);
  // formData.append("limit", document.querySelector("#limitData").value);
  formData.append("page", page);

  console.log("🔄 Mengirim data ke server:", Object.fromEntries(formData));

  fetch(`../../api/subdepartemen/post_data_sub_dept.php?filter=${filter}`, {
    method: "POST",
    body: formData,
  })
    .then((res) => res.json())
    .then((jsonResponse) => {
      console.log("📋 Parsed JSON Response:", jsonResponse);

      if (jsonResponse.status === "success") {
        if (jsonResponse.tableData) {
          console.log("data pages dari db:", jsonResponse.totalPages);
          console.log("✅ Table Data Ditemukan:", jsonResponse.tableData);

          tableDataCache = jsonResponse.tableData;
          totalPages = jsonResponse.totalPages || 1;
          localStorage.setItem(
            "salesTableOriginal",
            JSON.stringify(tableDataCache)
          );

          updateTable(tableDataCache, "salesTableSupplier");
          document.getElementById("btn-sub").disabled = true;
        } else {
          console.warn(
            "⚠️ Table Data Tidak Ditemukan di Response:",
            jsonResponse
          );
        }

        if (jsonResponse.data && jsonResponse.labels) {
          updatePieChart(
            jsonResponse.labels,
            jsonResponse.data,
            jsonResponse.tableData
          );
        }
      } else {
        console.warn("⚠️ Server mengembalikan status error:", jsonResponse);
      }
    })
    .catch((error) => {
      console.error("❌ Fetch error:", error);
    })
    .finally(() => {
      Swal.close();
    });
}

function formatCurrency(value) {
  return new Intl.NumberFormat("id-ID", {
    style: "currency",
    currency: "IDR",
    minimumFractionDigits: 0,
  }).format(value);
}
// Update Chart Diagram
function updateChart(labels, data, table) {
  console.log("📊 Updating Chart with Data:", labels, data, table);

  const newData = labels.map((label, index) => {
    const row = table[index] || {};
    const qtyValue = row.Qty || 0;
    const totalValue = row.Total || "Rp 0";
    const persentaseQty = row.Percentage || "0%";
    const persentaseRp = row.persentase_rp ? `${row.persentase_rp}%` : "0%";

    return {
      name: row.nama_subdept || row.nama_supp || `Item ${index + 1}`,
      precentage: persentaseQty, // persen dari Qty
      subdept: row.subdept?.toString() || "0",
      kode: row.kode_supp,
      value: qtyValue,
      uang: formatCurrency(totalValue),
      rp_percent: persentaseRp, // tambahan
    };
  });
  console.log("📊 Data Chart Final:", newData);

  echartDiagram.setOption({
    tooltip: {
      trigger: "item",
      formatter: function (params) {
        const sortBy = document.getElementById("sort-by").value;
        const percent =
          sortBy === "Qty"
            ? params.data.precentage
            : parseFloat(params.data.rp_percent).toFixed(2) + "%"; // Membatasi 2 angka di belakang koma
        return `Sub Departemen<br/>
          ${params.name} : ${params.value.toLocaleString()} (${percent})<br/>
          Total : ${params.data.uang}`;
      },
    },
    series: [
      {
        label: {
          fontSize: () => {
            const width = echartDiagram.getWidth();
            return width < 400 ? 10 : width < 800 ? 12 : 14;
          },
          formatter: (params) => {
            const sortBy = document.getElementById("sort-by").value;
            const percent =
              sortBy === "Qty"
                ? params.data.precentage
                : parseFloat(params.data.rp_percent).toFixed(2) + "%";
            return `${params.name} (${percent})`;
          },
        },
        data: newData,
        itemStyle: {
          color: function (params) {
            const colors = [
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
  });

  window.addEventListener("resize", () => {
    echartDiagram.resize();
  });
}

function updatePieChart(labels, data, table) {
  console.log("📊 Updating Chart with Data:", labels, data, table);
  var newData = labels.map((label, index) => {
    const parts = table[index] || {};

    return {
      name: parts.nama_subdept || parts.nama_supp || `Item ${index + 1}`,
      subdept: parts.subdept.toString() || "",
      value: parts.Qty || "",
      uang: formatCurrency(parts.Total) || "", // Gabung sisa jadi string uang
      kode: parts.kode_supp || "",
      precentage: parts.Percentage || "0%",
      rp_percent: parts.persentase_rp || "0%",
    };
  });
  console.log("Data Tabel: ", newData);
  echartDiagram.setOption({
    series: [
      {
        tooltip: {
          trigger: "item",
          formatter: function (params) {
            const sortBy = document.getElementById("sort-by").value;
            const percent =
              sortBy === "Qty"
                ? params.data.precentage
                : parseFloat(params.data.rp_percent).toFixed(2) + "%"; // Membatasi 2 angka di belakang koma
            return ` ${
              params.name
            } : ${params.value.toLocaleString()} (${percent})<br/> Total : ${
              params.data.uang
            }`;
          },
        },
        label: {
          fontSize: function () {
            let chartWidth = echartDiagram.getWidth(); // Ambil lebar chart
            return chartWidth < 400 ? 10 : chartWidth < 800 ? 12 : 14; // Ukuran font responsif
          },
          formatter: (params) => {
            const sortBy = document.getElementById("sort-by").value;
            const percent =
              sortBy === "Qty"
                ? params.data.precentage
                : parseFloat(params.data.rp_percent).toFixed(2) + "%";
            return `${params.name} (${percent})`;
          },
        },
        data: newData,
        itemStyle: {
          color: function (params) {
            var colors = [
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
  });
  window.addEventListener("resize", function () {
    echartDiagram.resize();
  });
  document.getElementById("chartDiagram").style.display = "block";
}
// END CODE PIE Echart JS

document.getElementById("btn-bar").addEventListener("click", function (e) {
  e.preventDefault();
  Swal.fire({
    title: "Loading...",
    html: "Tunggu Sebentar",
    allowOutsideClick: false,
    timerProgressBar: true,
    didOpen: () => {
      Swal.showLoading();
    },
  });
  var cabangText =
    document.getElementById("cabang").options[
      document.getElementById("cabang").selectedIndex
    ].text;
  var labelChart = document.getElementById("label-chart1");
  labelChart.innerHTML = `Penjualan Sub Departemen ${cabangText} (${
    document.querySelector("#date")?.value
  } s/d ${document.querySelector("#date1")?.value})`;
  var barChart = echarts.init(document.getElementById("barDiagram"));
  setTimeout(() => {
    barChart.resize();
  }, 300);
  isSubdeptActive = true;
  isPromo = true;
  currentPage = 1;
  document.getElementById("bar").style.display = "block";
  document.getElementById("pie").style.display = "none";
  const filter = document.getElementById("sort-by1").value;
  let formData = new FormData();
  formData.append("ajax", true);
  formData.append("kd_store", document.querySelector("#kd_store")?.value);
  formData.append("start_date", document.querySelector("#date")?.value);
  formData.append("end_date", document.querySelector("#date1")?.value);
  formData.append("subdept", document.querySelector("#subdept").value);
  formData.append("kode_supp", document.querySelector("#kode_supp").value);
  // formData.append("limit", document.querySelector("#limitData").value);
  formData.append("page", currentPage);
  formData.append("query_type", this.value);
  formData.append("filter", filter);
  console.log("🔄 Mengirim data ke server:", Object.fromEntries(formData));

  $.ajax({
    url: `../../api/subdepartemen/post_data_sub_dept?filter=${filter}`,
    method: "POST",
    data: formData,
    processData: false,
    contentType: false,
    success: function (response) {
      console.log("✅ Response dari server (RAW):", response);
      resetBarChart();
      let jsonResponse;
      try {
        jsonResponse =
          typeof response === "string" ? JSON.parse(response) : response;
        console.log("📋 Parsed JSON Response:", jsonResponse);
      } catch (error) {
        console.error("❌ Gagal parsing JSON:", error, response);
        return;
      }

      if (jsonResponse && jsonResponse.status === "success") {
        if (jsonResponse.tableData) {
          console.log("✅ Table Data Ditemukan:", jsonResponse.tableData);
          dataCharBar = localStorage.setItem(
            "chartBart",
            JSON.stringify(jsonResponse.tableData)
          );
        } else {
          console.warn(
            "⚠️ Table Data Tidak Ditemukan di Response:",
            jsonResponse
          );
        }
      } else {
        console.warn("⚠️ Server mengembalikan status error:", jsonResponse);
      }
      if (jsonResponse.data && jsonResponse.labels) {
        updateBarChart(
          jsonResponse.labels,
          jsonResponse.data,
          jsonResponse.tableData
        );
      }
      document.querySelector("#btn-promo").click();
    },
    complete: () => {
      Swal.close();
    },
  });
});

document.getElementById("btn-promo").addEventListener("click", function (e) {
  e.preventDefault();
  document.getElementById("container-table").style.display = "show";
  isSubdeptActive = true;
  isPromo = true;
  var cabangText =
    document.getElementById("cabang").options[
      document.getElementById("cabang").selectedIndex
    ].text;
  var startDate = $("#date").val(); // Ambil nilai langsung dari datepicker
  var endDate = $("#date1").val();
  var reportHeader = document.getElementById("reportHeaderPenjualan");
  reportHeader.innerHTML = `Data Barang<br><p> Cabang :  ${cabangText} (${startDate} s/d ${endDate})</p>`;
  let formData = new FormData();
  const filter = document.getElementById("sort-by1").value;
  formData.append("ajax", true);
  formData.append("kd_store", document.querySelector("#kd_store")?.value);
  formData.append("start_date", document.querySelector("#date")?.value);
  formData.append("end_date", document.querySelector("#date1")?.value);
  formData.append("subdept", document.querySelector("#subdept")?.value || "");
  formData.append(
    "kode_supp",
    document.querySelector("#kode_supp")?.value || ""
  );
  formData.append("page", currentPage);
  formData.append("query_type", this.value);
  formData.append("filter", filter);
  console.log("🔄 Mengirim data ke server:", Object.fromEntries(formData));

  $.ajax({
    url: `../../api/subdepartemen/post_data_sub_dept?filter=${filter}`,
    method: "POST",
    data: formData,
    processData: false,
    contentType: false,
    success: function (response) {
      console.log("✅ Response dari server (RAW):", response);
      let jsonResponse;
      try {
        jsonResponse =
          typeof response === "string" ? JSON.parse(response) : response;
        console.log("📋 Parsed JSON Response:", jsonResponse);
      } catch (error) {
        console.error("❌ Gagal parsing JSON:", error, response);
        return;
      }
      if (jsonResponse && jsonResponse.status === "success") {
        if (jsonResponse.tableData) {
          tableDataCache = jsonResponse.tableData;
          totalPages = jsonResponse.totalPages ? jsonResponse.totalPages : 1;
          console.log("📌 Total Pages Updated:", totalPages);
          localStorage.setItem(
            "salesTableOriginal",
            JSON.stringify(tableDataCache)
          );

          updateTable(tableDataCache, "salesTablePenjualan");
          updateThead("thHeadPenjualan");
          // updatePagination(); // 🔄 UPDATE PAGINATION
        } else {
          console.warn(
            "⚠️ Table Data Tidak Ditemukan di Response:",
            jsonResponse
          );
        }
      } else {
        console.warn("⚠️ Server mengembalikan status error:", jsonResponse);
      }
    },
  });
});

function updateBarChart(labels, data, table) {
  console.log("📊 Updating Bar Chart with Data:", labels, data, table);
  const currentSortBy = document.getElementById("sort-by1")?.value;

  var newData = labels.map((label, index) => ({
    promo: label,
    Qty: Number(data[index]),
    tanggal: String(table[index]?.periode),
    Total: Number(table[index]?.Total),
    precentage: table[index]?.Percentage,
    rp_percent: table[index]?.persentase_rp,
  }));
  console.log("Data Tabel: ", newData);
  // Ambil daftar unik tanggal dan promo
  var tanggal = newData.map((item) => item.tanggal);
  var promos = [...new Set(newData.map((item) => item.promo))];
  // Buat struktur data Qty berdasarkan promo
  var promoQtyMap = {};
  promos.forEach((promo) => {
    promoQtyMap[promo] = tanggal.map((tgl) => {
      let item = newData.find((d) => d.promo === promo && d.tanggal === tgl);
      return item ? item.Qty : 0;
    });
  });
  var app = {};
  var barChart = echarts.init(document.getElementById("barDiagram"));
  var optionBarCharts;
  const posList = [
    "left",
    "right",
    "top",
    "bottom",
    "inside",
    "insideTop",
    "insideLeft",
    "insideRight",
    "insideBottom",
    "insideTopLeft",
    "insideTopRight",
    "insideBottomLeft",
    "insideBottomRight",
  ];
  app.configParameters = {
    rotate: {
      min: -90,
      max: 90,
    },
    align: {
      options: {
        left: "left",
        center: "center",
        right: "right",
      },
    },
    verticalAlign: {
      options: {
        top: "top",
        middle: "middle",
        bottom: "bottom",
      },
    },
    position: {
      options: posList.reduce(function (map, pos) {
        map[pos] = pos;
        return map;
      }, {}),
    },
    distance: {
      min: 0,
      max: 100,
    },
  };
  app.config = {
    rotate: 0,
    align: "left",
    verticalAlign: "bottom",
    position: "top",
    distance: 0,
    onChange: function () {
      barChart.setOption({
        series: [
          {
            label: false,
          },
          {
            label: false,
          },
          {
            label: false,
          },
          {
            label: false,
          },
        ],
      });
    },
  };
  optionBarCharts = {
    grid: {
      left: "5%",
      right: "7%",
      bottom: "3%",
      containLabel: true,
    },
    tooltip: {
      trigger: "axis",
      axisPointer: {
        type: "shadow",
      },
      formatter: function (params) {
        let dateLabel = params[0].axisValue;
        const currentSortBy = document.getElementById("sort-by1").value;

        let promoDetails = promos
          .map((promo) => {
            let index = tanggal.indexOf(dateLabel);
            if (index === -1) return "";

            let qty = promoQtyMap[promo][index] || 0;

            let dataItem = newData.find(
              (item) =>
                item.promo === promo &&
                String(item.tanggal) === String(dateLabel)
            );

            // Ambil nilai sesuai filter
            let totalPenjualan = dataItem ? dataItem.Total || 0 : "N/A"; // Mengambil total penjualan
            let percentQty = dataItem ? dataItem.precentage || "0%" : "0%";
            let percentRp = dataItem
              ? dataItem.rp_percent?.toFixed(2) + "%"
              : "0%";
            // Ubah label sesuai mode tampilan berdasarkan sort-by1
            let labelInfo =
              currentSortBy === "Qty"
                ? `Terjual: <b>${qty} Qty</b> (${percentQty})`
                : `Penjualan: <b>Rp ${totalPenjualan}</b> (${percentRp})`;

            return qty > 0 ? `<br>Promo: <b>${promo}</b><br>${labelInfo}` : "";
          })
          .join("");

        return `Tanggal: <b>${dateLabel}</b>${promoDetails}`;
      },
    },
    toolbox: {
      show: true,
      orient: "vertical",
      left: "right",
      top: "center",
      feature: {
        mark: { show: true },
        dataView: { show: true, readOnly: false },
        magicType: { show: true, type: ["line", "bar", "stack"] },
        restore: { show: false },
        saveAsImage: { show: true },
      },
      connectNulls: true,
    },
    xAxis: [
      {
        type: "category",
        name: "Periode",
        data: tanggal, // Data tanggal
        axisLabel: {
          formatter: function (value) {
            return "{custom|" + value.split("sd").join("sd\n") + "}"; // Format label
          },
          rich: {
            custom: {
              fontSize: 12,
              lineHeight: 14,
              width: 100,
            },
          },
          interval: 0,
          rotate: 45,
        },
      },
    ],
    yAxis: [
      {
        type: "value",
        name: currentSortBy === "Qty" ? "Qty" : "Rp",
        axisLabel: {
          formatter:
            currentSortBy === "Qty"
              ? "{value}"
              : function (value) {
                  return value.toLocaleString();
                },
        },
      },
    ],
    series: [
      {
        connectNulls: true,
        name: "Promo", // Tetap satu kategori
        type: "bar",
        stack: "total",
        data: tanggal.map((tgl) => {
          let items = newData.filter((item) => item.tanggal === tgl);
          let totalQty = items.reduce((sum, item) => sum + item.Qty, 0);
          let percentage = items.length > 0 ? items[0].precentage : "";
          let percentRp = items.length > 0 ? items[0].rp_percent : "";
          return {
            value: totalQty,
            percentage: percentage,
            percentRp: percentRp,
          };
        }),
        itemStyle: {
          color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [
            { offset: 0, color: "#83bff6" },
            { offset: 0.5, color: "#188df0" },
            { offset: 1, color: "#188df0" },
          ]),
        },
        label: {
          show: true,
          rotate: 74,
          align: "left",
          verticalAlign: "bottom",
          position: "insideBottom",
          distance: 0,
          formatter: function (params) {
            const data = params?.data || {};

            if (currentSortBy === "Qty") {
              return data.percentage ? data.percentage : "";
            } else if (currentSortBy === "Total") {
              return (
                (!isNaN(parseFloat(data.percentRp))
                  ? parseFloat(data.percentRp).toFixed(2)
                  : "0.00") + "%"
              );
            }
            return "";
          },
          fontSize: 12,
          color: "#000000", // Warna hitam
        },
      },
    ],
  };
  // **Tambahkan event listener untuk menangani perubahan magicType**
  barChart.on("magictypechanged", function (event) {
    let newType = event.currentType; // Dapatkan jenis chart yang dipilih
    barChart.setOption({
      series: [
        {
          label: { show: newType === "line" ? false : true },
        },
      ],
    });
  });
  barChart.setOption(optionBarCharts);
  window.addEventListener("resize", () => {
    barChart.resize();
  });
}

// END CODE Bar Echart JS

function resetBarChart() {
  var barChart = echarts.init(document.getElementById("barDiagram"));
  barChart.clear();
  barChart.resize();
}

document
  .getElementById("toggle-sidebar")
  .addEventListener("click", function () {
    var pieChart = echarts.init(document.getElementById("chartDiagram"));
    var barChart = echarts.init(document.getElementById("barDiagram"));
    document.getElementById("sidebar").classList.toggle("open");
    setTimeout(() => {
      pieChart.resize();
      barChart.resize();
    });
  });

document.getElementById("toggle-hide").addEventListener("click", function () {
  var sidebarTexts = document.querySelectorAll(".sidebar-text");
  let mainContent = document.getElementById("main-content");
  let sidebar = document.getElementById("sidebar");
  var toggleButton = document.getElementById("toggle-hide");
  var icon = toggleButton.querySelector("i");
  var barChart = echarts.init(document.getElementById("barDiagram"));
  var pieChart = echarts.init(document.getElementById("chartDiagram"));

  if (sidebar.classList.contains("w-64")) {
    // Sidebar mengecil
    sidebar.classList.remove("w-64", "px-5");
    sidebar.classList.add("w-16", "px-2");
    sidebarTexts.forEach((text) => text.classList.add("hidden")); // Sembunyikan teks
    mainContent.classList.remove("ml-64");
    mainContent.classList.add("ml-16"); // Main ikut mundur
    toggleButton.classList.add("left-20"); // Geser tombol lebih dekat
    toggleButton.classList.remove("left-64");
    icon.classList.remove("fa-angle-left"); // Ubah ikon
    icon.classList.add("fa-angle-right");
    setTimeout(() => {
      pieChart.resize();
      barChart.resize();
    }, 300);
  } else {
    // Sidebar membesar
    sidebar.classList.remove("w-16", "px-2");
    sidebar.classList.add("w-64", "px-5");
    sidebarTexts.forEach((text) => text.classList.remove("hidden")); // Tampilkan teks kembali
    mainContent.classList.remove("ml-16");
    mainContent.classList.add("ml-64");
    toggleButton.classList.add("left-64"); // Geser tombol ke posisi awal
    toggleButton.classList.remove("left-20");
    icon.classList.remove("fa-angle-right"); // Ubah ikon
    icon.classList.add("fa-angle-left");
    setTimeout(() => {
      pieChart.resize();
      barChart.resize();
    }, 300);
  }
});

document.addEventListener("DOMContentLoaded", function () {
  const profileImg = document.getElementById("profile-img");
  const profileCard = document.getElementById("profile-card");

  profileImg.addEventListener("click", function (event) {
    event.preventDefault();
    profileCard.classList.toggle("show");
  });

  // Tutup profile-card jika klik di luar
  document.addEventListener("click", function (event) {
    if (
      !profileCard.contains(event.target) &&
      !profileImg.contains(event.target)
    ) {
      profileCard.classList.remove("show");
    }
  });
});

$(document).ready(async function () {
  const $select = $("#cabang");
  const $kdStore = $("#kd_store");

  // Map nama_cabang -> kode store
  let nameToCode = {};
  let allCodes = [];

  async function loadCabang() {
    try {
      const token = localStorage.getItem("token");
      const res = await fetch("/src/api/cabang/get_kode", {
        headers: {
          Accept: "application/json",
          Authorization: "Bearer " + token,
        },
      });
      if (!res.ok) throw new Error("HTTP " + res.status);

      const json = await res.json();
      const rows = (json?.data || []).filter(
        (r) => r && r.nama_cabang && r.store
      );

      // reset
      nameToCode = {};
      allCodes = [];
      $select.empty();

      // opsi default + semua cabang
      $select.append('<option value="ALL">Semua Cabang</option>');

      // isi select: value = nama_cabang, text = nama_cabang
      rows.forEach(({ nama_cabang, store }) => {
        const code = String(store).trim();
        const name = String(nama_cabang).trim();
        nameToCode[name] = code;
        allCodes.push(code);
        $select.append(`<option value="${name}">${name}</option>`);
      });
    } catch (err) {
      console.error("Gagal memuat cabang:", err);
      // Optional: tampilkan toast/alert di UI-mu
    }
  }

  // ketika cabang berubah -> isi kd_store
  $select.on("change", function () {
    const val = this.value; // nama_cabang atau 'ALL' atau ''
    if (val === "ALL") {
      $kdStore.val(allCodes.join(","));
    } else if (val) {
      $kdStore.val(nameToCode[val] || "");
    } else {
      $kdStore.val("");
    }
  });

  // muat data + set nilai awal
  await loadCabang();
  $select.trigger("change");
});

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

function updateThead(trId) {
  const tableHead = document.querySelector(`#${trId}`);
  const subdeptElement = document.querySelector("#subdept");
  const kodeSupElement = document.querySelector("#kode_supp");

  if (!subdeptElement || !kodeSupElement || !tableHead) {
    console.error("Elemen tidak ditemukan!");
    return;
  }

  const subdeptHead = subdeptElement.value.trim();
  const kodeSupHead = kodeSupElement.value.trim();

  console.log("subdeptHead:", subdeptHead);
  console.log("kodeSupHead:", kodeSupHead);

  if (isSubdeptActive && isPromo) {
    tableHead.textContent = "NAMA BARANG";
  } else if (isSubdeptActive) {
    tableHead.textContent = "SUPPLIER";
  } else {
    tableHead.textContent = "SUBDEPT";
  }
}

async function exportExcel(query) {
  const table = document.getElementById("salesTable");
  const cabSel = document.getElementById("cabang");
  const cabangText = cabSel?.options[cabSel.selectedIndex]?.text || "-";
  const kodeSupDept = document.querySelector("#subdept")?.value || "-";
  const toNumber = (v) => {
    if (v == null) return 0;
    let s = String(v)
      .trim()
      .replace(/[^\d.,-]/g, "");
    if (s.includes(",") && s.includes(".")) {
      // format Eropa: 1.234,56
      s = s.replace(/\./g, "").replace(/,/g, ".");
    } else {
      // hapus pemisah ribuan 1,234 / 1.234
      s = s.replace(/[.,](?=\d{3}\b)/g, "");
    }
    const n = parseFloat(s);
    return Number.isFinite(n) ? n : 0;
  };
  // ==== Sumber data ====
  let labelHeader = "SUB DEPT";
  let rowsData = []; // {no, label, qty, total}

  if (query === "query2") {
    // ---- Ambil dari localStorage khusus SUPPLIER ----
    labelHeader = "SUPPLIER";
    const raw = localStorage.getItem("salesTableOriginal");
    if (!raw) {
      alert(
        "Data supplier tidak ditemukan di localStorage (salesTableOriginal)."
      );
      return;
    }
    let arr;
    try {
      arr = JSON.parse(raw);
      if (!Array.isArray(arr))
        throw new Error("salesTableOriginal harus array JSON");
    } catch (e) {
      console.error(e);
      alert("Format salesTableOriginal tidak valid.");
      return;
    }

    rowsData = arr
      .map((it, i) => ({
        no: i + 1,
        label: (it.nama_supp ?? it.supplier ?? it.nama ?? "").toString().trim(),
        qty: toNumber(it.Qty ?? it.qty),
        total: toNumber(it.Total ?? it.total ?? it.amount),
      }))
      .filter((r) => r.label || r.qty || r.total);
  } else {
    // ---- Default: ambil dari DOM tabel aktif ----
    const tbody = table?.querySelector("tbody");
    if (!tbody) {
      alert("Tidak ada data untuk diekspor!");
      return;
    }

    // header dinamis
    if (query === "query1") labelHeader = "SUBDEPT";
    else if (query === "query4") labelHeader = "NAMA BARANG";

    const trs = Array.from(tbody.querySelectorAll("tr"));
    rowsData = trs
      .map((tr, i) => {
        const tds = tr.querySelectorAll("td");
        // Asumsi layout DOM: [No, Label, Qty, Total]
        return {
          no: i + 1,
          label: tds[1]?.textContent?.trim() ?? "",
          qty: toNumber(tds[2]?.textContent),
          total: toNumber(tds[3]?.textContent),
        };
      })
      .filter((r) => r.label || r.qty || r.total);
  }

  // ==== Build Excel (A..D) ====
  const wb = new ExcelJS.Workbook();
  const ws = wb.addWorksheet("Data Penjualan", {
    views: [{ state: "frozen", ySplit: 4 }],
    pageSetup: {
      orientation: "landscape",
      fitToPage: true,
      fitToWidth: 1,
      fitToHeight: 0,
    },
  });

  // Kolom
  ws.getColumn(1).width = 6; // No
  ws.getColumn(2).width = 42; // Label
  ws.getColumn(3).width = 10; // Qty
  ws.getColumn(4).width = 18; // Total

  // Judul & subjudul
  ws.mergeCells("A1:D1");
  ws.getCell("A1").value = "REKAP PENJUALAN";
  ws.getCell("A1").font = { bold: true, size: 16 };
  ws.getCell("A1").alignment = { horizontal: "center", vertical: "middle" };

  ws.mergeCells("A2:D2");
  ws.getCell(
    "A2"
  ).value = `Cabang: ${cabangText} • Subdept: ${kodeSupDept} • Dibuat: ${new Date().toLocaleString()}`;
  ws.getCell("A2").alignment = { horizontal: "center", vertical: "middle" };

  ws.addRow([]); // baris 3 kosong

  // Header (A4:D4)
  const headerRowIdx = 4;
  const hdr = ws.getRow(headerRowIdx);
  ["No", labelHeader, "QTY", "TOTAL"].forEach((text, i) => {
    const cell = hdr.getCell(1 + i);
    cell.value = text;
    cell.font = { bold: true, color: { argb: "FFFFFFFF" } };
    cell.alignment = { horizontal: "center", vertical: "middle" };
    cell.fill = {
      type: "pattern",
      pattern: "solid",
      fgColor: { argb: "FF1D4ED8" },
    };
    cell.border = {
      top: { style: "thin" },
      bottom: { style: "medium" },
      left: { style: "thin" },
      right: { style: "thin" },
    };
  });
  hdr.height = 22;

  ws.autoFilter = {
    from: { row: headerRowIdx, column: 1 },
    to: { row: headerRowIdx, column: 4 },
  };

  // Body
  let r = headerRowIdx + 1;
  rowsData.forEach((it, idx) => {
    ws.getCell(r, 1).value = it.no;
    ws.getCell(r, 2).value = it.label;
    ws.getCell(r, 3).value = it.qty;
    ws.getCell(r, 4).value = it.total;

    ws.getCell(r, 1).alignment = { horizontal: "center", vertical: "middle" };
    ws.getCell(r, 2).alignment = {
      horizontal: "left",
      vertical: "middle",
      shrinkToFit: true,
    };
    ws.getCell(r, 3).alignment = { horizontal: "center", vertical: "middle" };
    ws.getCell(r, 4).alignment = { horizontal: "right", vertical: "middle" };
    ws.getCell(r, 4).numFmt = '"Rp" #,##0;-"Rp" #,##0;""';

    for (let c = 1; c <= 4; c++) {
      ws.getCell(r, c).border = {
        top: { style: "hair" },
        left: { style: "hair" },
        bottom: { style: "hair" },
        right: { style: "hair" },
      };
      if (idx % 2 === 1)
        ws.getCell(r, c).fill = {
          type: "pattern",
          pattern: "solid",
          fgColor: { argb: "FFF3F4F6" },
        };
    }
    ws.getRow(r).height = 20;
    r++;
  });

  // Total
  const hasData = rowsData.length > 0;
  const firstDataRow = headerRowIdx + 1; // 5
  const lastDataRow = hasData ? r - 1 : firstDataRow;
  const totalRowIdx = lastDataRow + 1;

  ws.getCell(totalRowIdx, 2).value = "TOTAL";
  ws.getCell(totalRowIdx, 2).font = { bold: true };
  ws.getCell(totalRowIdx, 2).alignment = {
    horizontal: "right",
    vertical: "middle",
  };

  ws.getCell(totalRowIdx, 3).value = hasData
    ? { formula: `SUBTOTAL(9,C${firstDataRow}:C${lastDataRow})` }
    : 0;
  ws.getCell(totalRowIdx, 4).value = hasData
    ? { formula: `SUBTOTAL(9,D${firstDataRow}:D${lastDataRow})` }
    : 0;
  ws.getCell(totalRowIdx, 4).numFmt = '"Rp" #,##0;-"Rp" #,##0;""';

  for (let c = 2; c <= 4; c++) {
    const cell = ws.getCell(totalRowIdx, c);
    cell.font = { ...(cell.font || {}), bold: true };
    cell.fill = {
      type: "pattern",
      pattern: "solid",
      fgColor: { argb: "FFE5E7EB" },
    };
    cell.border = { top: "thin", left: "thin", bottom: "thin", right: "thin" };
    cell.alignment = { horizontal: "right", vertical: "middle" };
  }

  // Simpan
  const buf = await wb.xlsx.writeBuffer();
  const blob = new Blob([buf], {
    type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
  });
  const a = document.createElement("a");
  a.href = URL.createObjectURL(blob);
  const tag = new Date().toISOString().slice(0, 19).replace(/[:T]/g, "-");
  a.download = `Rekap_${labelHeader.replace(
    /\s+/g,
    "_"
  )}_${cabangText}_${tag}.xlsx`;
  a.click();
}

async function exportQuery(query) {
  const { jsPDF } = window.jspdf;
  const doc = new jsPDF("p", "mm", "a4");

  const cabSel = document.getElementById("cabang");
  const cabangText = cabSel?.options[cabSel.selectedIndex]?.text || "-";
  const kodeSupDept = document.querySelector("#subdept")?.value || "-";
  const table = document.getElementById("salesTable");

  // ---------- helpers ----------
  const toNumber = (v) => {
    if (v == null) return 0;
    let s = String(v)
      .trim()
      .replace(/[^\d.,-]/g, "");
    if (s.includes(",") && s.includes(".")) {
      s = s.replace(/\./g, "").replace(/,/g, ".");
    } else {
      s = s.replace(/[.,](?=\d{3}\b)/g, "");
    }
    const n = parseFloat(s);
    return Number.isFinite(n) ? n : 0;
  };
  const fmtInt = (n) => new Intl.NumberFormat("id-ID").format(+n || 0);
  const fmtRp = (n) => "Rp " + new Intl.NumberFormat("id-ID").format(+n || 0);

  async function urlToDataURL(url) {
    try {
      const res = await fetch(url, { mode: "cors" });
      const blob = await res.blob();
      return await new Promise((resolve) => {
        const reader = new FileReader();
        reader.onload = () => resolve(reader.result);
        reader.readAsDataURL(blob);
      });
    } catch {
      return null;
    }
  }

  // ---------- siapkan data ----------
  let labelHeader = "SUBDEPT";
  let rows = []; // array of [No, Label, QTY, TOTAL] (formatted for PDF)
  let sumQty = 0,
    sumTotal = 0;

  if (query === "query2") {
    // Supplier dari localStorage
    labelHeader = "SUPPLIER";
    const raw = localStorage.getItem("salesTableOriginal");
    if (!raw) {
      alert("Data supplier tidak ada di localStorage (salesTableOriginal).");
      return;
    }
    let arr;
    try {
      arr = JSON.parse(raw);
      if (!Array.isArray(arr)) throw new Error();
    } catch {
      alert("Format salesTableOriginal tidak valid.");
      return;
    }
    arr.forEach((it, idx) => {
      const label = (it.nama_supp ?? it.supplier ?? it.nama ?? "")
        .toString()
        .trim();
      const qty = toNumber(it.Qty ?? it.qty);
      const total = toNumber(it.Total ?? it.total ?? it.amount);
      sumQty += qty;
      sumTotal += total;
      rows.push([idx + 1, label, fmtInt(qty), fmtRp(total)]);
    });
  } else {
    // Ambil dari DOM tabel aktif
    if (!table) {
      alert("Tabel tidak ditemukan.");
      return;
    }
    if (query === "query1") labelHeader = "SUBDEPT";
    else if (query === "query4") labelHeader = "NAMA BARANG";

    const trs = table.querySelectorAll("tbody tr");
    trs.forEach((tr, i) => {
      const tds = tr.querySelectorAll("td");
      const label = tds[1]?.textContent?.trim() ?? "";
      const qty = toNumber(tds[2]?.textContent);
      const total = toNumber(tds[3]?.textContent);
      sumQty += qty;
      sumTotal += total;
      rows.push([i + 1, label, fmtInt(qty), fmtRp(total)]);
    });
  }

  // ---------- header info ----------
  const headers = ["No", labelHeader, "QTY", "TOTAL"];

 
  const logoDataUrl = await urlToDataURL("/images/logo.png");
  const nowStr = new Date().toLocaleString("id-ID");

  // ---------- render table ----------
  doc.autoTable({
    head: [headers],
    body: rows,
    startY: 50,
    theme: "grid",
    styles: {
      fontSize: 8,
      cellPadding: 2,
      overflow: "linebreak",
      minCellHeight: 6,
      lineColor: [220, 223, 230],
      lineWidth: 0.1,
    },
    headStyles: {
      fillColor: [29, 78, 216], // #1D4ED8
      textColor: [255, 255, 255],
      fontSize: 9,
      fontStyle: "bold",
      halign: "center",
      valign: "middle",
    },
    bodyStyles: { textColor: [10, 10, 10] },
    alternateRowStyles: { fillColor: [245, 247, 250] },
    columnStyles: {
      0: { halign: "center", cellWidth: 12 }, // No
      1: { halign: "left", cellWidth: 98 }, // Label
      2: { halign: "center", cellWidth: 25 }, // Qty
      3: { halign: "right", cellWidth: 35 }, // Total (kanan)
    },
    margin: { top: 50, left: 10, right: 10 },
    showFoot: 'lastPage',
    foot: rows.length
      ? [["", "TOTAL", fmtInt(sumQty), fmtRp(sumTotal)]]
      : undefined,
    footStyles: {
      fillColor: [229, 231, 235],
      textColor: [17, 24, 39],
      halign: "right",
      fontStyle: "bold",
    },
    didDrawPage: function (data) {
      // Header per page
      if (logoDataUrl) {
        doc.addImage(logoDataUrl, "PNG", 13, 10, 25, 10);
      }
      doc.setFont("helvetica", "bold");
      doc.setFontSize(14);
      doc.text("Asoka Baby Store", 45, 15);
      doc.setFont("helvetica", "normal");
      doc.setFontSize(9);
      doc.text(
        "Lb 5, Jl. Utan Jati Blok Lb 5 No.9, RT.10/RW.12, Kalideres, Jakarta 11840",
        45,
        20
      );
      doc.text("Telp: 0817-1712-1250", 45, 25);
      doc.setLineWidth(0.5);
      doc.line(10, 30, 200, 30); // garis

      // Judul laporan + info
      doc.setFont("helvetica", "bold");
      doc.setFontSize(12);
      doc.text("Laporan Data Penjualan", 15, 42);
      doc.setFont("helvetica", "normal");
      doc.setFontSize(9);
      doc.text(`Cabang: ${cabangText}`, 150, 38);
      doc.text(`Subdept: ${kodeSupDept}`, 150, 42);
      doc.text(`Dibuat: ${nowStr}`, 150, 46);

      // Footer nomor halaman
      const pageCount = doc.internal.getNumberOfPages();
      const str = `Halaman ${data.pageNumber} dari ${pageCount}`;
      doc.setFontSize(8);
      doc.text(str, 200 - doc.getTextWidth(str), 290); // kanan bawah
    },
  });

  // ---------- simpan ----------
  const labelSafe = labelHeader.replace(/\s+/g, "_");
  doc.save(
    `Laporan_${labelSafe}_${cabangText}_${new Date()
      .toISOString()
      .slice(0, 19)
      .replace(/[:T]/g, "-")}.pdf`
  );
}

function exportToExcel() {
  console.log("Nilai query parameter: ", activeQueryType);
  if (activeQueryType === "query1") {
    console.log("query benarr 1");
    exportExcel(activeQueryType);
  } else if (activeQueryType === "query2") {
    console.log("query benarr 2");
    exportExcel(activeQueryType);
  } else if (activeQueryType === "query4") {
    console.log("query benar 3");
    exportExcel(activeQueryType);
  }
}

function exportToPDF() {
  console.log("Nilai query parameter: ", activeQueryType);

  if (activeQueryType === "query1") {
    console.log("query benarr 1");
    exportQuery(activeQueryType);
  } else if (activeQueryType === "query2") {
    console.log("query benarr 2");
    exportQuery(activeQueryType);
  } else if (activeQueryType === "query4") {
    console.log("query benarr 3");
    exportQuery(activeQueryType);
  }
}

async function exportToPDFModal() {
  const { jsPDF } = window.jspdf;
  const doc = new jsPDF("p", "mm", "a4");

  const cabSel = document.getElementById("cabang");
  const cabangText = cabSel?.options[cabSel.selectedIndex]?.text || "-";
  const kodeSupDept = document.querySelector("#subdept")?.value || "-";
  const table = document.getElementById("salesTablePromo");
  if (!table) {
    alert("Tabel promo tidak ditemukan.");
    return;
  }

  // --- helper angka ---
  const toNumber = (v) => {
    if (v == null) return 0;
    let s = String(v)
      .trim()
      .replace(/[^\d.,-]/g, "");
    if (s.includes(",") && s.includes("."))
      s = s.replace(/\./g, "").replace(/,/g, ".");
    else s = s.replace(/[.,](?=\d{3}\b)/g, "");
    const n = parseFloat(s);
    return Number.isFinite(n) ? n : 0;
  };
  const fmtInt = (n) => new Intl.NumberFormat("id-ID").format(+n || 0);
  const fmtRp = (n) => "Rp " + new Intl.NumberFormat("id-ID").format(+n || 0);

  // (opsional) load logo sebagai dataURL; kalau gagal biarkan null
  async function urlToDataURL(url) {
    try {
      const res = await fetch(url, { mode: "cors" });
      const blob = await res.blob();
      return await new Promise((resolve) => {
        const fr = new FileReader();
        fr.onload = () => resolve(fr.result);
        fr.readAsDataURL(blob);
      });
    } catch {
      return null;
    }
  }
  const logoDataUrl = await urlToDataURL("/images/logo.png");

  // --- kumpulkan data dari DOM ---
  const rows = [];
  let sumQty = 0,
    sumTotal = 0;
  table.querySelectorAll("tbody tr").forEach((tr, i) => {
    const promo = tr.cells[1]?.innerText?.trim() ?? "";
    const qty = toNumber(tr.cells[2]?.innerText);
    const total = toNumber(tr.cells[3]?.innerText);
    sumQty += qty;
    sumTotal += total;
    rows.push([i + 1, promo, fmtInt(qty), fmtRp(total)]);
  });

  // --- konstanta header/layout ---
  const TITLE_Y = 42; // posisi judul
  const START_Y = 50; // awal tabel (juga margin top halaman)
  const nowStr = new Date().toLocaleString("id-ID");

  doc.autoTable({
    head: [["No", "PROMO", "QTY", "TOTAL"]],
    body: rows,
    startY: START_Y,
    margin: { top: START_Y, left: 10, right: 10 }, // konsisten di semua halaman
    theme: "grid",
    styles: {
      fontSize: 8,
      cellPadding: 2,
      lineColor: [220, 223, 230],
      lineWidth: 0.1,
    },
    headStyles: {
      fillColor: [29, 78, 216],
      textColor: [255, 255, 255],
      fontSize: 9,
      fontStyle: "bold",
      halign: "center",
    },
    bodyStyles: { textColor: [10, 10, 10] },
    alternateRowStyles: { fillColor: [245, 247, 250] },
    columnStyles: {
      0: { halign: "center", cellWidth: 12 }, // No
      1: { halign: "left", cellWidth: 98 }, // PROMO
      2: { halign: "center", cellWidth: 25 }, // QTY
      3: { halign: "right", cellWidth: 35 }, // TOTAL
    },
    showFoot: 'lastPage',
    foot: rows.length
      ? [["", "TOTAL", fmtInt(sumQty), fmtRp(sumTotal)]]
      : undefined,
    footStyles: {
      fillColor: [229, 231, 235],
      textColor: [17, 24, 39],
      fontStyle: "bold",
      halign: "right",
    },

    didDrawPage(data) {
      // Logo (ukuran FIX sesuai permintaan)
      if (logoDataUrl) doc.addImage(logoDataUrl, "PNG", 13, 10, 25, 10);

      // Identitas perusahaan
      doc.setFont("helvetica", "bold");
      doc.setFontSize(14);
      doc.text("Asoka Baby Store", 45, 15);
      doc.setFont("helvetica", "normal");
      doc.setFontSize(9);
      doc.text(
        "Lb 5, Jl. Utan Jati Blok Lb 5 No.9, RT.10/RW.12, Kalideres, Jakarta 11840",
        45,
        20
      );
      doc.text("Telp: 0817-1712-1250", 45, 25);

      // Garis pemisah
      doc.setLineWidth(0.5);
      doc.line(10, 30, 200, 30);

      // Judul & info kanan
      doc.setFont("helvetica", "bold");
      doc.setFontSize(12);
      doc.text("Laporan Data Penjualan", 15, TITLE_Y);
      doc.setFont("helvetica", "normal");
      doc.setFontSize(9);
      doc.text(`Cabang: ${cabangText}`, 150, 38);
      doc.text(`Subdept: ${kodeSupDept}`, 150, 42);
      doc.text(`Dibuat: ${nowStr}`, 150, 46);

      // Footer nomor halaman
      const pageCount = doc.internal.getNumberOfPages();
      const str = `Halaman ${data.pageNumber} dari ${pageCount}`;
      doc.setFontSize(8);
      doc.text(str, 200 - doc.getTextWidth(str), 290);
    },
  });

  const tag = new Date().toISOString().slice(0, 19).replace(/[:T]/g, "-");
  doc.save(`Laporan_Promo_${cabangText}_${tag}.pdf`);
}

async function exportToExcelModal() {
  const cabSel = document.getElementById("cabang");
  const cabangText = cabSel?.options[cabSel.selectedIndex]?.text || "-";
  const kodeSupDept = document.querySelector("#subdept")?.value || "-";
  const domRows = Array.from(
    document.querySelectorAll("#salesTablePromo tbody tr")
  );

  const wb = new ExcelJS.Workbook();
  const ws = wb.addWorksheet("Data Penjualan", {
    views: [{ state: "frozen", ySplit: 4 }],
    pageSetup: {
      orientation: "landscape",
      fitToPage: true,
      fitToWidth: 1,
      fitToHeight: 0,
    },
  });

  // Lebar kolom A..D
  ws.getColumn(1).width = 6; // A No
  ws.getColumn(2).width = 42; // B PROMO
  ws.getColumn(3).width = 10; // C QTY
  ws.getColumn(4).width = 18; // D TOTAL

  // Judul & subjudul (merge dulu, isi via getCell)
  ws.mergeCells("A1:D1");
  ws.getCell("A1").value = "REKAP PENJUALAN PROMO";
  ws.getCell("A1").font = { bold: true, size: 16 };
  ws.getCell("A1").alignment = { horizontal: "center", vertical: "middle" };

  ws.mergeCells("A2:D2");
  ws.getCell(
    "A2"
  ).value = `Cabang: ${cabangText} • Subdept: ${kodeSupDept} • Dibuat: ${new Date().toLocaleString()}`;
  ws.getCell("A2").alignment = { horizontal: "center", vertical: "middle" };

  ws.addRow([]); // baris 3 kosong

  // Header tabel di baris 4 (A4:D4)
  const headerRowIdx = 4;
  const hdr = ws.getRow(headerRowIdx);
  ["No", "PROMO", "QTY", "TOTAL"].forEach((text, i) => {
    const cell = hdr.getCell(1 + i);
    cell.value = text;
    cell.font = { bold: true, color: { argb: "FFFFFFFF" } };
    cell.alignment = { horizontal: "center", vertical: "middle" };
    cell.fill = {
      type: "pattern",
      pattern: "solid",
      fgColor: { argb: "FF1D4ED8" },
    };
    cell.border = {
      top: { style: "thin" },
      bottom: { style: "medium" },
      left: { style: "thin" },
      right: { style: "thin" },
    };
  });
  hdr.height = 22;

  // Autofilter tepat pada header
  ws.autoFilter = {
    from: { row: headerRowIdx, column: 1 },
    to: { row: headerRowIdx, column: 4 },
  };

  // Data mulai baris 5
  let r = headerRowIdx + 1;
  domRows.forEach((tr, idx) => {
    const promo = tr.cells[1]?.innerText?.trim() ?? "";
    const qty = toNumber(tr.cells[2]?.innerText);
    const total = toNumber(tr.cells[3]?.innerText);

    ws.getCell(r, 1).value = idx + 1; // No
    ws.getCell(r, 2).value = promo; // PROMO
    ws.getCell(r, 3).value = qty; // QTY
    ws.getCell(r, 4).value = total; // TOTAL

    // Alignment & format
    ws.getCell(r, 1).alignment = { horizontal: "center", vertical: "middle" };
    ws.getCell(r, 2).alignment = {
      horizontal: "left",
      vertical: "middle",
      shrinkToFit: true,
    };
    ws.getCell(r, 3).alignment = { horizontal: "center", vertical: "middle" };
    ws.getCell(r, 4).numFmt = '"Rp" #,##0;-"Rp" #,##0;""';
    ws.getCell(r, 4).alignment = { horizontal: "right", vertical: "middle" };

    // Border + zebra
    for (let c = 1; c <= 4; c++) {
      ws.getCell(r, c).border = {
        top: { style: "hair" },
        left: { style: "hair" },
        bottom: { style: "hair" },
        right: { style: "hair" },
      };
      if (idx % 2 === 1)
        ws.getCell(r, c).fill = {
          type: "pattern",
          pattern: "solid",
          fgColor: { argb: "FFF3F4F6" },
        };
    }
    ws.getRow(r).height = 20;
    r++;
  });

  const hasData = domRows.length > 0;
  const firstDataRow = headerRowIdx + 1; // 5
  const lastDataRow = hasData ? r - 1 : firstDataRow;

  // Total (SUBTOTAL agar tetap benar saat difilter)
  const totalRowIdx = lastDataRow + 1;
  ws.getCell(totalRowIdx, 2).value = "TOTAL";
  ws.getCell(totalRowIdx, 2).font = { bold: true };
  ws.getCell(totalRowIdx, 2).alignment = {
    horizontal: "right",
    vertical: "middle",
  };

  ws.getCell(totalRowIdx, 3).value = hasData
    ? { formula: `SUBTOTAL(9,C${firstDataRow}:C${lastDataRow})` }
    : 0; // Qty
  ws.getCell(totalRowIdx, 4).value = hasData
    ? { formula: `SUBTOTAL(9,D${firstDataRow}:D${lastDataRow})` }
    : 0; // Total
  ws.getCell(totalRowIdx, 4).numFmt = '"Rp" #,##0;-"Rp" #,##0;""';

  for (let c = 2; c <= 4; c++) {
    const cell = ws.getCell(totalRowIdx, c);
    cell.font = { ...(cell.font || {}), bold: true };
    cell.fill = {
      type: "pattern",
      pattern: "solid",
      fgColor: { argb: "FFE5E7EB" },
    };
    cell.border = { top: "thin", left: "thin", bottom: "thin", right: "thin" };
    cell.alignment = {
      horizontal: c === 2 ? "right" : "right",
      vertical: "middle",
    };
  }

  // Simpan
  const buf = await wb.xlsx.writeBuffer();
  const blob = new Blob([buf], {
    type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
  });
  const a = document.createElement("a");
  a.href = URL.createObjectURL(blob);
  a.download = `Rekap_Promo_${cabangText}_${new Date()
    .toISOString()
    .slice(0, 19)
    .replace(/[:T]/g, "-")}.xlsx`;
  a.click();
}

async function exportToExcelModalPenjualan() {
  const cabangEl = document.getElementById("cabang");
  const cabangText = cabangEl?.options[cabangEl.selectedIndex]?.text || "-";
  const kodeSupDept = document.querySelector("#subdept")?.value || "-";
  const rows = Array.from(
    document.querySelectorAll("#salesTablePenjualan tbody tr")
  );

  const toNumber = (v) => {
    if (v == null) return 0;
    const s = String(v)
      .replace(/[^\d,-]/g, "")
      .replace(/\./g, "")
      .replace(",", ".");
    const n = parseFloat(s);
    return Number.isFinite(n) ? n : 0;
  };

  const wb = new ExcelJS.Workbook();
  const ws = wb.addWorksheet("Data Penjualan", {
    views: [{ state: "frozen", ySplit: 4 }],
    pageSetup: {
      orientation: "landscape",
      fitToPage: true,
      fitToWidth: 1,
      fitToHeight: 0,
    },
  });

  // Lebar kolom (A untuk margin)
  ws.getColumn(1).width = 2; // A (margin)
  ws.getColumn(2).width = 6; // B No
  ws.getColumn(3).width = 20; // C Barcode
  ws.getColumn(4).width = 45; // D Nama
  ws.getColumn(5).width = 10; // E Qty
  ws.getColumn(6).width = 18; // F Total

  // Judul & subjudul (merge aman, isi via getCell)
  ws.mergeCells("A1:F1");
  ws.getCell("A1").value = "REKAP PENJUALAN";
  ws.getCell("A1").font = { bold: true, size: 16 };
  ws.getCell("A1").alignment = { horizontal: "center", vertical: "middle" };

  ws.mergeCells("A2:F2");
  ws.getCell(
    "A2"
  ).value = `Cabang: ${cabangText} • Subdept: ${kodeSupDept} • Dibuat: ${new Date().toLocaleString()}`;
  ws.getCell("A2").alignment = { horizontal: "center", vertical: "middle" };
  ws.addRow([]); // row 3 kosong

  // Header di baris 4, mulai kolom B
  const headerRowIdx = 4;
  const hdr = ws.getRow(headerRowIdx);
  ["No", "BARCODE", "NAMA BARANG", "QTY", "TOTAL"].forEach((v, i) => {
    const cell = hdr.getCell(2 + i); // 2 = kolom B
    cell.value = v;
    cell.font = { bold: true, color: { argb: "FFFFFFFF" } };
    cell.alignment = { horizontal: "center", vertical: "middle" };
    cell.fill = {
      type: "pattern",
      pattern: "solid",
      fgColor: { argb: "FF1D4ED8" },
    };
    cell.border = {
      top: { style: "thin" },
      bottom: { style: "medium" },
      left: { style: "thin" },
      right: { style: "thin" },
    };
  });
  hdr.height = 22;

  // Autofilter tepat di header range
  ws.autoFilter = {
    from: { row: headerRowIdx, column: 2 },
    to: { row: headerRowIdx, column: 6 },
  }; // B4:F4

  // Data mulai baris 5
  let r = headerRowIdx + 1;
  rows.forEach((tr, idx) => {
    const barcode = tr.cells[1]?.innerText?.trim() ?? "";
    const nama = tr.cells[2]?.innerText?.trim() ?? "";
    const qty = toNumber(tr.cells[3]?.innerText);
    const total = toNumber(tr.cells[4]?.innerText);

    ws.getCell(r, 2).value = idx + 1; // B
    ws.getCell(r, 3).value = barcode; // C
    ws.getCell(r, 4).value = nama; // D
    ws.getCell(r, 5).value = qty; // E
    ws.getCell(r, 6).value = total; // F

    // Alignment & format
    ws.getCell(r, 2).alignment = { horizontal: "center", vertical: "middle" };
    ws.getCell(r, 3).alignment = { horizontal: "center", vertical: "middle" };
    ws.getCell(r, 4).alignment = {
      horizontal: "left",
      vertical: "middle",
      shrinkToFit: true,
    };
    ws.getCell(r, 5).alignment = { horizontal: "center", vertical: "middle" };
    ws.getCell(r, 6).numFmt = '"Rp" #,##0;-"Rp" #,##0;""';
    ws.getCell(r, 6).alignment = { horizontal: "right", vertical: "middle" };

    // Border & zebra
    for (let c = 2; c <= 6; c++) {
      ws.getCell(r, c).border = {
        top: { style: "hair" },
        left: { style: "hair" },
        bottom: { style: "hair" },
        right: { style: "hair" },
      };
      if (idx % 2 === 1) {
        ws.getCell(r, c).fill = {
          type: "pattern",
          pattern: "solid",
          fgColor: { argb: "FFF3F4F6" },
        };
      }
    }
    ws.getRow(r).height = 20;
    r++;
  });

  const hasData = rows.length > 0;
  const firstDataRow = headerRowIdx + 1;
  const lastDataRow = hasData ? r - 1 : firstDataRow;

  // Row total (aman jika data kosong)
  const totalRow = ws.getRow(lastDataRow + 1);
  ws.getCell(totalRow.number, 4).value = "TOTAL"; // D
  ws.getCell(totalRow.number, 4).font = { bold: true };
  ws.getCell(totalRow.number, 4).alignment = {
    horizontal: "right",
    vertical: "middle",
  };

  ws.getCell(totalRow.number, 5).value = hasData
    ? { formula: `SUBTOTAL(9,E${firstDataRow}:E${lastDataRow})` }
    : 0;
  ws.getCell(totalRow.number, 6).value = hasData
    ? { formula: `SUBTOTAL(9,F${firstDataRow}:F${lastDataRow})` }
    : 0;
  ws.getCell(totalRow.number, 6).numFmt = '"Rp" #,##0;-"Rp" #,##0;""';

  for (let c = 4; c <= 6; c++) {
    const cell = ws.getCell(totalRow.number, c);
    cell.font = { ...(cell.font || {}), bold: true };
    cell.fill = {
      type: "pattern",
      pattern: "solid",
      fgColor: { argb: "FFE5E7EB" },
    };
    cell.border = {
      top: { style: "thin" },
      left: { style: "thin" },
      bottom: { style: "thin" },
      right: { style: "thin" },
    };
    cell.alignment = {
      horizontal: c === 4 ? "right" : "right",
      vertical: "middle",
    };
  }

  // Simpan
  const buf = await wb.xlsx.writeBuffer();
  const blob = new Blob([buf], {
    type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
  });
  const a = document.createElement("a");
  a.href = URL.createObjectURL(blob);
  a.download = `Rekap_Penjualan_${cabangText}_${new Date()
    .toISOString()
    .slice(0, 19)
    .replace(/[:T]/g, "-")}.xlsx`;
  a.click();
}

async function exportPDFModalPenjualan() {
  const { jsPDF } = window.jspdf;
  const doc = new jsPDF("p", "mm", "a4");

  const cabSel = document.getElementById("cabang");
  const cabangText = cabSel?.options[cabSel.selectedIndex]?.text || "-";
  const kodeSupDept = document.querySelector("#subdept")?.value || "-";
  const table = document.getElementById("salesTablePenjualan");
  if (!table) {
    alert("Tabel penjualan tidak ditemukan.");
    return;
  }

  // --- helpers angka & format ---
  const toNumber = (v) => {
    if (v == null) return 0;
    let s = String(v)
      .trim()
      .replace(/[^\d.,-]/g, "");
    if (s.includes(",") && s.includes(".")) {
      s = s.replace(/\./g, "").replace(/,/g, ".");
    } else {
      s = s.replace(/[.,](?=\d{3}\b)/g, "");
    }
    const n = parseFloat(s);
    return Number.isFinite(n) ? n : 0;
  };
  const fmtInt = (n) => new Intl.NumberFormat("id-ID").format(+n || 0);
  const fmtRp = (n) => "Rp " + new Intl.NumberFormat("id-ID").format(+n || 0);

  // --- kumpulkan data DOM ---
  const rows = [];
  let sumQty = 0,
    sumTotal = 0;
  table.querySelectorAll("tbody tr").forEach((tr, i) => {
    const barcode = tr.cells[1]?.innerText?.trim() ?? "";
    const nama = tr.cells[2]?.innerText?.trim() ?? "";
    const qtyN = toNumber(tr.cells[3]?.innerText);
    const totN = toNumber(tr.cells[4]?.innerText);
    sumQty += qtyN;
    sumTotal += totN;
    rows.push([i + 1, barcode, nama, fmtInt(qtyN), fmtRp(totN)]);
  });

  // --- layout konstanta ---
  const TITLE_Y = 42; // posisi judul
  const START_Y = 50; // awal tabel (dan margin top tiap halaman)
  const nowStr = new Date().toLocaleString("id-ID");

  doc.autoTable({
    head: [["No", "BARCODE", "NAMA BARANG", "QTY", "TOTAL"]],
    body: rows,
    startY: START_Y,
    margin: { top: START_Y, left: 10, right: 10, bottom: 18 }, // + bottom margin
    theme: "grid",
    styles: {
      fontSize: 8,
      cellPadding: 2,
      lineColor: [220, 223, 230],
      lineWidth: 0.1,
    },
    headStyles: {
      fillColor: [29, 78, 216],
      textColor: [255, 255, 255],
      fontSize: 9,
      fontStyle: "bold",
      halign: "center",
    },
    bodyStyles: { textColor: [10, 10, 10] },
    alternateRowStyles: { fillColor: [245, 247, 250] },
    columnStyles: {
      0: { halign: "center", cellWidth: 12 },
      1: { halign: "center", cellWidth: 28 },
      2: { halign: "left", cellWidth: 85 },
      3: { halign: "center", cellWidth: 25 },
      4: { halign: "right", cellWidth: 35 },
    },

    // ⬇️ FOOT hanya di halaman terakhir
    showFoot: "lastPage",
    foot: rows.length
      ? [["", "", "TOTAL", fmtInt(sumQty), fmtRp(sumTotal)]]
      : undefined,
    footStyles: {
      fillColor: [229, 231, 235],
      textColor: [17, 24, 39],
      fontStyle: "bold",
      halign: "right",
    },

    didDrawPage(data) {
      // logo fix
      const logoSrc = "/images/logo.png"; // base64 lebih cepat jika tersedia
      doc.addImage(logoSrc, "PNG", 13, 10, 25, 10);

      // header kiri
      doc.setFont("helvetica", "bold");
      doc.setFontSize(14);
      doc.text("Asoka Baby Store", 45, 15);
      doc.setFont("helvetica", "normal");
      doc.setFontSize(9);
      doc.text(
        "Lb 5, Jl. Utan Jati Blok Lb 5 No.9, RT.10/RW.12, Kalideres, Jakarta 11840",
        45,
        20
      );
      doc.text("Telp: 0817-1712-1250", 45, 25);

      // garis separator
      doc.setLineWidth(0.5);
      doc.line(10, 30, 200, 30);

      // judul
      doc.setFont("helvetica", "bold");
      doc.setFontSize(12);
      doc.text("Laporan Data Penjualan", 15, TITLE_Y);

      // info kanan (rapi, tidak tabrakan garis)
      doc.setFont("helvetica", "normal");
      doc.setFontSize(9);
      const pageW = doc.internal.pageSize.getWidth();
      const rightEdge = pageW - 10;
      const valueW = 40,
        boxW = 62,
        lineH = 4.6;
      const infoLeftX = rightEdge - boxW;
      let infoY = 36; // di atas judul, di bawah garis

      function putInfoRow(label, value) {
        doc.setFont("helvetica", "bold");
        doc.text(label, rightEdge - valueW - 2, infoY, { align: "right" });
        doc.setFont("helvetica", "normal");
        const wrapped = doc.splitTextToSize(String(value ?? ""), valueW);
        wrapped.forEach((line, i) => {
          doc.text(line, rightEdge, infoY + i * lineH, { align: "right" });
        });
        infoY += Math.max(lineH, wrapped.length * lineH);
      }
      putInfoRow("Cabang", cabangText);
      putInfoRow("Subdept", kodeSupDept);
      putInfoRow("Dibuat", nowStr);

      // footer page number
      const pageCount = doc.internal.getNumberOfPages();
      const str = `Halaman ${data.pageNumber} dari ${pageCount}`;
      doc.setFontSize(8);
      doc.text(str, 200 - doc.getTextWidth(str), 290);
    },
  });

  const tag = new Date().toISOString().slice(0, 19).replace(/[:T]/g, "-");
  doc.save(`Laporan_Penjualan_${cabangText}_${tag}.pdf`);
}

function searchTable(tableId) {
  let input = document.getElementById("searchInput").value.toLowerCase();
  let originalData =
    JSON.parse(localStorage.getItem("salesTableOriginal")) || [];
  // Jika input kosong, reset ke data awal
  if (input === "") {
    console.log(`🔄 Reset tabel ${tableId} ke data awal...`);
    updateTable(originalData, tableId);
    return;
  }

  let filteredData = originalData.filter(
    (row) =>
      (row.nama_subdept && row.nama_subdept.toLowerCase().includes(input)) ||
      (row.kode_supp && row.kode_supp.toLowerCase().includes(input)) ||
      (row.promo && row.promo.toLowerCase().includes(input)) ||
      (row.Qty && row.Qty.toString().includes(input)) ||
      (row.Total && row.Total.toString().includes(input))
  );

  if (filteredData.length === 0) {
    document.querySelector(`#salesTable tbody`).innerHTML = `
      <tr>
        <td colspan="4" class="py-3 px-6 text-center italic text-gray-500">Data tidak ditemukan</td>
      </tr>
    `;
    return;
  }
  currentPage = 1;
  updateTable(filteredData, tableId); // Update tabel dengan hasil pencarian
}
function searchTablePromo(tableId) {
  let input = document.getElementById("searchInputPromo").value.toLowerCase();

  let originalData =
    JSON.parse(localStorage.getItem("salesTableOriginal")) || [];
  // Jika input kosong, reset ke data awal
  if (input === "") {
    console.log(`🔄 Reset tabel ${tableId} ke data awal...`);
    updateTable(originalData, tableId);
    return;
  }

  // Jika input kosong, reset ke data awal
  if (input === "") {
    console.log(`🔄 Reset tabel ${tableId} ke data awal...`);
    updateTable(originalData, tableId);
    return;
  }

  let filteredData = originalData.filter(
    (row) =>
      (row.nama_subdept && row.nama_subdept.toLowerCase().includes(input)) ||
      (row.kode_supp && row.kode_supp.toLowerCase().includes(input)) ||
      (row.promo && row.promo.toLowerCase().includes(input)) ||
      (row.Qty && row.Qty.toString().includes(input)) ||
      (row.Total && row.Total.toString().includes(input))
  );

  if (filteredData.length === 0) {
    document.querySelector(`#salesTablePromo tbody`).innerHTML = `
      <tr>
        <td colspan="4" class="py-3 px-6 text-center italic text-gray-500">Data tidak ditemukan</td>
      </tr>
    `;
    return;
  }
  currentPage = 1;
  updateTable(filteredData, tableId); // Update tabel dengan hasil pencarian
}
function searchTablePenjualan(tableId) {
  let input = document
    .getElementById("searchInputPenjualan")
    .value.toLowerCase();

  let originalData =
    JSON.parse(localStorage.getItem("salesTableOriginal")) || [];
  // Jika input kosong, reset ke data awal
  if (input === "") {
    console.log(`🔄 Reset tabel ${tableId} ke data awal...`);
    updateTable(originalData, tableId);
    return;
  }

  // Jika input kosong, reset ke data awal
  if (input === "") {
    console.log(`🔄 Reset tabel ${tableId} ke data awal...`);
    updateTable(originalData, tableId);
    return;
  }

  let filteredData = originalData.filter(
    (row) =>
      (row.nama_subdept && row.nama_subdept.toLowerCase().includes(input)) ||
      (row.kode_supp && row.kode_supp.toLowerCase().includes(input)) ||
      (row.promo && row.promo.toLowerCase().includes(input)) ||
      (row.Qty && row.Qty.toString().includes(input)) ||
      (row.Total && row.Total.toString().includes(input))
  );

  if (filteredData.length === 0) {
    document.querySelector(`#salesTablePenjualan tbody`).innerHTML = `
      <tr>
        <td colspan="4" class="py-3 px-6 text-center italic text-gray-500">Data tidak ditemukan</td>
      </tr>
    `;
    return;
  }
  currentPage = 1;
  updateTable(filteredData, tableId); // Update tabel dengan hasil pencarian
}
function searchTableSupplier(tableId) {
  let input = document
    .getElementById("searchInputSupplier")
    .value.toLowerCase();

  let originalData =
    JSON.parse(localStorage.getItem("salesTableOriginal")) || [];
  // Jika input kosong, reset ke data awal
  if (input === "") {
    console.log(`🔄 Reset tabel ${tableId} ke data awal...`);
    updateTable(originalData, tableId);
    return;
  }

  // Jika input kosong, reset ke data awal
  if (input === "") {
    console.log(`🔄 Reset tabel ${tableId} ke data awal...`);
    updateTable(originalData, tableId);
    return;
  }

  let filteredData = originalData.filter(
    (row) =>
      (row.nama_subdept && row.nama_subdept.toLowerCase().includes(input)) ||
      (row.kode_supp && row.kode_supp.toLowerCase().includes(input)) ||
      (row.promo && row.promo.toLowerCase().includes(input)) ||
      (row.Qty && row.Qty.toString().includes(input)) ||
      (row.Total && row.Total.toString().includes(input))
  );

  if (filteredData.length === 0) {
    document.querySelector(`#salesTableSupplier tbody`).innerHTML = `
      <tr>
        <td colspan="4" class="py-3 px-6 text-center italic text-gray-500">Data tidak ditemukan</td>
      </tr>
    `;
    return;
  }
  currentPage = 1;
  updateTable(filteredData, tableId); // Update tabel dengan hasil pencarian
}
