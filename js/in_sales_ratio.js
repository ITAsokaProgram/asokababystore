<<<<<<< HEAD
$(".supplier-input").hide();
let totalPages = 0; // Untuk supplier 1
let totalPages2 = 0; // Untuk supplier 2
let totalPages3 = 0; // Untuk supplier 2
let totalPages4 = 0; // Untuk supplier 2
let totalPages5 = 0; // Untuk supplier 2
let currentPage = 1;
let currentPageKode2 = 1;
let currentPageKode3 = 1;
let currentPageKode4 = 1;
let currentPageKode5 = 1;
let rowsPerPage = 13;
document.getElementById("lihatTable").style.display = "none";
document.getElementById("sendTable").style.display = "none";
document.getElementById("hideTable").style.display = "none";
document.getElementById("bar").style.display = "none";

document.getElementById("toggle-sidebar").addEventListener("click", function () {
  document.getElementById("sidebar").classList.toggle("open");
});

document.getElementById("toggle-hide").addEventListener("click", function () {
  var sidebarTexts = document.querySelectorAll(".sidebar-text");
  let mainContent = document.getElementById("main-content");
  let sidebar = document.getElementById("sidebar");
  var toggleButton = document.getElementById("toggle-hide");
  var icon = toggleButton.querySelector("i");
  var barChart = echarts.init(document.getElementById("barDiagram"));

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
      barChart.resize();
    }, 250)
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
      barChart.resize();
    }, 250)
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

$(document).ready(function () {
  // Daftar kode store sesuai dengan cabang
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
    AHIN: "2101",
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

  // Event listener ketika cabang berubah
  $("#cabang").on("change", function () {
    var selectedBranch = $(this).val(); // Ambil nilai cabang yang dipilih
    var storeCode = storeCodes[selectedBranch] || "1501"; // Ambil kode store atau default '1501'
    $("#kd_store").val(storeCode); // Set nilai input kode_store
  });

  // Trigger event saat halaman dimuat untuk mengisi nilai awal
  $("#cabang").trigger("change");
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
// POST Data Dari Form
document.getElementById("laporanForm").addEventListener("submit", function (e) {
  e.preventDefault(); // Mencegah form dikirim secara default
  sendDataToBar();
  var barChart = echarts.init(document.getElementById("barDiagram"));
  setTimeout(()=>{
    barChart.resize()
  },300)
  document.getElementById("bar").style.display = "block";
  document.getElementById("lihatTable").style.display = "block";
  document.getElementById("sendTable").style.display = "block";
});
// Select Ratio Tampilkan Input
$(document).ready(function () {
  $("#ratio_number").on("change", function () {
    let selectedValue = parseInt($(this).val());

    $("[id^='kode_supp']").hide();
    // Sembunyikan semua input dulu
    if (isNaN(selectedValue)) {
      $(".supplier-input").hide();
    }

    // Tampilkan input sesuai jumlah yang dipilih
    for (let i = 1; i <= selectedValue; i++) {
      $("#kode_supp" + i).show();
    }
  });
});

// Menampilkan Data Pada Klik Input
$(document).on("focus click", ".supplier-input", function () {
  let inputField = $(this);

  if (!inputField.data("loaded")) { // Cegah duplikasi load
    $.ajax({
      url: "https://asokababystore.com/get_kode_supp.php",
      type: "GET",
      dataType: "json",
      success: function (response) {
        console.log("Data diterima:", response);

        // Ubah format jadi array hanya berisi kode_supp
        let dataSuplier = response.map(item => item.kode_supp.trim());

        inputField.data("loaded", true);
        inputField.data("suppliers", dataSuplier);

        showSuggestions(inputField, dataSuplier); // 🔹 Panggil langsung
      },
      error: function () {
        console.error("Gagal mengambil data supplier");
      },
    });
  } else {
    showSuggestions(inputField, inputField.data("suppliers"));
  }
});

// Pencarian Data Lewat Input Ketik
$(document).on("input", ".supplier-input", function () {
  let inputField = $(this);
  let keyword = inputField.val().toLowerCase();
  let allSuppliers = inputField.data("suppliers") || [];

  let filteredSuppliers = allSuppliers.filter(supplier =>
    supplier.toLowerCase().includes(keyword)
  );

  showSuggestions(inputField, filteredSuppliers);
});

// Memunculkan Data Pada Input Untuk Di Pilih
function showSuggestions(inputField, suppliers) {
  $(".suggestion-box").remove(); // Hapus dropdown lama

  let suggestionBox = $("<div>")
    .addClass("absolute bg-white border border-gray-300 w-full max-h-40 overflow-y-auto z-50 shadow-md rounded-md suggestion-box");

  if (!Array.isArray(suppliers) || suppliers.length === 0) {
    $("<div>")
      .addClass("px-4 py-2 text-gray-500 text-center italic")
      .text("Supplier tidak ditemukan")
      .appendTo(suggestionBox);
  } else {
    suppliers.forEach(kode_supp => {
      $("<div>")
        .addClass("px-4 py-2 cursor-pointer border-b border-gray-200 text-gray-700 hover:bg-gray-100")
        .text(kode_supp)
        .on("click", function () {
          inputField.val($(this).text());
          $(".suggestion-box").remove();
        })
        .appendTo(suggestionBox);
    });
  }

  // 🔥 Posisikan dropdown di bawah input tanpa mengubah layout asli
  let inputOffset = inputField.offset();
  suggestionBox.css({
    position: "absolute",
    top: inputOffset.top + inputField.outerHeight(),
    left: inputOffset.left,
    width: inputField.outerWidth(),
    background: "#fff",
    borderRadius: "5px",
    border: "1px solid #ddd",
    boxShadow: "0px 4px 6px rgba(0, 0, 0, 0.1)"
  });

  $("body").append(suggestionBox);
}

$(document).on("click", function (e) {
  if (!$(e.target).closest(".supplier-input, .suggestion-box").length) {
    $(".suggestion-box").remove(); // Tutup dropdown jika klik di luar
  }
});


// 🔥 Saat klik tombol "Cek Data", simpan input ke dalam select
$("#btn-submit").on("click", function () {
  updateSupplierDropdown();
});

// 🔥 Fungsi untuk update dropdown supplier
function updateSupplierDropdown() {
  let supplierDropdown = $("#supplierDropdown");

  $(".supplier-input").each(function () {
    let value = $(this).val().trim();
    if (value !== "" && !isOptionExists(value)) {
      supplierDropdown.append(`<option value="${value}">${value}</option>`);
    }
  });
}

// Cek apakah opsi sudah ada di dropdown
function isOptionExists(value) {
  return $("#supplierDropdown option").filter(function () {
    return $(this).val() === value;
  }).length > 0;
}

document.getElementById("sendTable").addEventListener("click", function (e){
  e.preventDefault();
  currentPage = 1;
  sendKodeSupp1(currentPage);
})

function sendKodeSupp1(page) {
document.getElementById("hideTable").style.display = "block";
var barChart = echarts.init(document.getElementById("barDiagram"));
setTimeout(()=>{
  barChart.resize()
},300)
  var cabangText =
    document.getElementById("cabang").options[
      document.getElementById("cabang").selectedIndex
    ].text;
  var startDate = $("#date").val(); // Ambil nilai langsung dari datepicker
  var endDate = $("#date1").val();
  var reportHeader = document.getElementById("reportHeader1");
  var kodeSupp = $("#supplierDropdown").val();
  reportHeader.innerHTML = `Data Penjualan<br><p> Cabang :  ${cabangText}, Supplier: ${kodeSupp} (${startDate} s/d ${endDate})</p>`;
  let formDataToTable = new FormData;
  formDataToTable.append("ajax", true);
  formDataToTable.append("csrf_token", document.querySelector("[name='csrf_token']").value);
  formDataToTable.append("selectKode", document.querySelector("#supplierDropdown")?.value);
  formDataToTable.append("kd_store", document.querySelector("#kd_store")?.value);
  formDataToTable.append("start_date", document.querySelector("#date")?.value);
  formDataToTable.append("end_date", document.querySelector("#date1")?.value);
  formDataToTable.append('page', page);
  for (var pair of formDataToTable.entries()) {
    console.log(pair[0] + ": " + pair[1]);
  }
  $.ajax({
    url: "in_sales_ratio_proses_table?ajax=1",
    method: "POST",
    dataType: "json",
    processData: false,
    contentType: false,
    data: formDataToTable,
    success: (response) => {
      console.log("Response data ke table 1:", response);
      if (response.tableData) {
        totalPages = response.totalPages ? response.totalPages : 1;
        updateTable1(response.tableData);
        updatePagination();
      } else {
        console.log("Data Tabel Tidak Ada", response);
      }
    },
    error: (xhr, status, error) => {
      console.log("Error : ", status, error);
    }
  });

}

// function sendKodeSupp2(page) {
//   var cabangText =
//     document.getElementById("cabang").options[
//       document.getElementById("cabang").selectedIndex
//     ].text;
//   var startDate = $("#date").val(); // Ambil nilai langsung dari datepicker
//   var endDate = $("#date1").val();
//   var reportHeader = document.getElementById("reportHeader2");
//   var kodeSupp = $("#kode_supp2").val();
//   reportHeader.innerHTML = `Data Penjualan<br><p> Cabang :  ${cabangText}, Supplier: ${kodeSupp} (${startDate} s/d ${endDate})</p>`;
//   let formDataToTable = new FormData;
//   formDataToTable.append("ajax", true);
//   formDataToTable.append("csrf_token", document.querySelector("[name='csrf_token']").value);
//   formDataToTable.append("kode_supp2", document.querySelector("#kode_supp2")?.value);
//   formDataToTable.append("kd_store", document.querySelector("#kd_store")?.value);
//   formDataToTable.append("start_date", document.querySelector("#date")?.value);
//   formDataToTable.append("end_date", document.querySelector("#date1")?.value);
//   formDataToTable.append('page', page);

//   $.ajax({
//     url: "in_sales_ratio_proses_table?ajax=1",
//     method: "POST",
//     dataType: "json",
//     processData: false,
//     contentType: false,
//     data: formDataToTable,
//     success: (response) => {
//       console.log("Response data ke table 2:", response);
//       if (response.tableData) {
//         totalPages2 = response.totalPages;
//         updateTable2(response.tableData);
//         updatePagination2();
//       } else {
//         console.log("Data Tabel Tidak Ada", response);
//       }
//     },
//     error: (xhr, status, error) => {
//       console.log("Error : ", status, error);
//     }
//   });

// }

// function sendKodeSupp3(page) {
//   var cabangText =
//     document.getElementById("cabang").options[
//       document.getElementById("cabang").selectedIndex
//     ].text;
//   var startDate = $("#date").val(); // Ambil nilai langsung dari datepicker
//   var endDate = $("#date1").val();
//   var reportHeader = document.getElementById("reportHeader3");
//   var kodeSupp = $("#kode_supp3").val();
//   reportHeader.innerHTML = `Data Penjualan<br><p> Cabang :  ${cabangText}, Supplier: ${kodeSupp} (${startDate} s/d ${endDate})</p>`;
//   let formDataToTable = new FormData;
//   formDataToTable.append("ajax", true);
//   formDataToTable.append("csrf_token", document.querySelector("[name='csrf_token']").value);
//   formDataToTable.append("kode_supp3", document.querySelector("#kode_supp3")?.value);
//   formDataToTable.append("kd_store", document.querySelector("#kd_store")?.value);
//   formDataToTable.append("start_date", document.querySelector("#date")?.value);
//   formDataToTable.append("end_date", document.querySelector("#date1")?.value);
//   formDataToTable.append('page', page);
//   $.ajax({
//     url: "in_sales_ratio_proses_table?ajax=1",
//     method: "POST",
//     dataType: "json",
//     processData: false,
//     contentType: false,
//     data: formDataToTable,
//     success: (response) => {
//       console.log("Response data ke table 3:", response);
//       if (response.tableData) {
//         totalPages3 = response.totalPages ? response.totalPages : 1;
//         updateTable3(response.tableData);
//         updatePagination3();
//       } else {
//         console.log("Data Tabel Tidak Ada", response);
//       }
//     },
//     error: (xhr, status, error) => {
//       console.log("Error : ", status, error);
//     }
//   });

// }

// function sendKodeSupp4(page) {
//   var cabangText =
//     document.getElementById("cabang").options[
//       document.getElementById("cabang").selectedIndex
//     ].text;
//   var startDate = $("#date").val(); // Ambil nilai langsung dari datepicker
//   var endDate = $("#date1").val();
//   var reportHeader = document.getElementById("reportHeader4");
//   var kodeSupp = $("#kode_supp1").val();
//   reportHeader.innerHTML = `Data Penjualan<br><p> Cabang :  ${cabangText}, Supplier: ${kodeSupp} (${startDate} s/d ${endDate})</p>`;
//   let formDataToTable = new FormData;
//   formDataToTable.append("ajax", true);
//   formDataToTable.append("csrf_token", document.querySelector("[name='csrf_token']").value);
//   formDataToTable.append("kode_supp4", document.querySelector("#kode_supp4")?.value);
//   formDataToTable.append("kd_store", document.querySelector("#kd_store")?.value);
//   formDataToTable.append("start_date", document.querySelector("#date")?.value);
//   formDataToTable.append("end_date", document.querySelector("#date1")?.value);
//   formDataToTable.append('page', page);
//   $.ajax({
//     url: "in_sales_ratio_proses_table?ajax=1",
//     method: "POST",
//     dataType: "json",
//     processData: false,
//     contentType: false,
//     data: formDataToTable,
//     success: (response) => {
//       console.log("Response data ke table 4:", response);
//       if (response.tableData) {
//         totalPages4 = response.totalPages ? response.totalPages : 1;
//         updateTable4(response.tableData);
//         updatePagination4();
//       } else {
//         console.log("Data Tabel Tidak Ada", response);
//       }
//     },
//     error: (xhr, status, error) => {
//       console.log("Error : ", status, error);
//     }
//   });

// }
// function sendKodeSupp5(page) {
//   var cabangText =
//     document.getElementById("cabang").options[
//       document.getElementById("cabang").selectedIndex
//     ].text;
//   var startDate = $("#date").val(); // Ambil nilai langsung dari datepicker
//   var endDate = $("#date1").val();
//   var reportHeader = document.getElementById("reportHeader5");
//   var kodeSupp = $("#kode_supp5").val();
//   reportHeader.innerHTML = `Data Penjualan<br><p> Cabang :  ${cabangText}, Supplier: ${kodeSupp} (${startDate} s/d ${endDate})</p>`;
//   let formDataToTable = new FormData;
//   formDataToTable.append("ajax", true);
//   formDataToTable.append("csrf_token", document.querySelector("[name='csrf_token']").value);
//   formDataToTable.append("kode_supp5", document.querySelector("#kode_supp5")?.value);
//   formDataToTable.append("kd_store", document.querySelector("#kd_store")?.value);
//   formDataToTable.append("start_date", document.querySelector("#date")?.value);
//   formDataToTable.append("end_date", document.querySelector("#date1")?.value);
//   formDataToTable.append('page', page);
//   $.ajax({
//     url: "in_sales_ratio_proses_table?ajax=1",
//     method: "POST",
//     dataType: "json",
//     processData: false,
//     contentType: false,
//     data: formDataToTable,
//     success: (response) => {
//       console.log("Response data ke table 5:", response);
//       if (response.tableData) {
//         totalPages5 = response.totalPages ? response.totalPages : 1;
//         updateTable5(response.tableData);
//         updatePagination5();
//       } else {
//         console.log("Data Tabel Tidak Ada", response);
//       }
//     },
//     error: (xhr, status, error) => {
//       console.log("Error : ", status, error);
//     }
//   });

// }
function sendDataToBar() {
  let formData = new FormData();
  var barChart = echarts.init(document.getElementById("barDiagram"));
  setTimeout(()=>{
    barChart.resize()
  },300)
  resetBarChart();
  formData.append("ajax", true);
  formData.append("csrf_token", document.querySelector("[name='csrf_token']").value);
  formData.append("kode_supp1", document.querySelector("#kode_supp1")?.value);
  formData.append("kode_supp2", document.querySelector("#kode_supp2")?.value);
  formData.append("kode_supp3", document.querySelector("#kode_supp3")?.value);
  formData.append("kode_supp4", document.querySelector("#kode_supp4")?.value);
  formData.append("kode_supp5", document.querySelector("#kode_supp5")?.value);
  formData.append("kd_store", document.querySelector("#kd_store")?.value);
  formData.append("start_date", document.querySelector("#date")?.value);
  formData.append("end_date", document.querySelector("#date1")?.value);

  $.ajax({
    url: "in_sales_ratio_proses_bar.php?ajax=1",
    method: "POST",
    dataType: "json",
    processData: false,
    contentType: false,
    data: formData,
    success: (response) => {
      console.log("Response data: ", response);
      updateBarChart(response.labels, response.data, response.tableData);
    },
    error: (xhr, status, error) => {
      console.log("Error : ", status, error);
    }
  });
}



// Update Tampilan Bar Chart Sesuai Data
function updateBarChart(labels, data, table) {
  console.log("📊 Updating Bar Chart with Data:", labels, data, table);

  // Persiapkan data baru dengan sorting berdasarkan tanggal
  var newData = labels.map((label, index) => ({
    kode_supp: label,
    Qty: Number(data[index]),
    tanggal: String(table[index]?.periode),
  }));
  console.log("Data Tabel: ", newData);

  newData.sort((a, b) => {
    let [dayA, monthA] = a.tanggal.split("-").map(Number);
    let [dayB, monthB] = b.tanggal.split("-").map(Number);
    return monthA !== monthB ? monthA - monthB : dayA - dayB;
  });
  // Ambil daftar unik tanggal dan kode supplier (kods)
  var tanggal = [...new Set(newData.map((item) => item.tanggal))];
  var kods = [...new Set(newData.map((item) => item.kode_supp))];

  // Generate warna unik untuk setiap supplier
  var colorPalette = [
    "#ff5733", "#33ff57", "#3357ff", "#f4a261", "#e63946",
    "#6a0572", "#0e9594", "#f77f00", "#2a9d8f", "#6c757d"
  ];
  var colorMap = {};
  kods.forEach((kd, index) => {
    colorMap[kd] = colorPalette[index % colorPalette.length];
  });

  var barChart = echarts.init(document.getElementById("barDiagram"));

  var seriesData = kods.map((kd) => ({
    name: kd,
    type: "bar",
    data: tanggal.map((tgl) => {
      let foundItem = newData.find((item) => item.tanggal === tgl && item.kode_supp === kd);
      return foundItem ? foundItem.Qty : 0;
    }),
    itemStyle: {
      color: colorMap[kd],
    },
    label: {
      show: false,
      position: "insideTop",
      formatter: (params) => (params.value > 0 ? params.value : ""),
      fontSize: 12,
      color: "#0a0a0a",
    },
  }));

  var optionBarCharts = {
    grid: {
      left: '6%',
      right: '1%',
      bottom: '3%',
      containLabel: true
    },
    tooltip: {
      trigger: "axis",
      axisPointer: {
        type: "shadow",
      },
      formatter: function (params) {
        let dateLabel = params[0].axisValue;
        let details = params
          .filter((p) => p.value > 0)
          .map((p) => `● ${p.seriesName}: <b>${p.value}</b>`)
          .join("<br>");
        return `<b>${dateLabel}</b><br>${details}`;
      },
    },
    legend: {
      data: kods,
    },
    toolbox: {
      show: true,
      feature: {
        dataView: { show: true, readOnly: false },
        magicType: { show: true, type: ["line", "bar", "stack"] },
        restore: { show: true },
        saveAsImage: { show: true },
      },
    },
    xAxis: {
      type: "category",
      data: tanggal,
      name: "Tahun/Bulan/Hari",
      nameLocation: "center",
      nameGap: 35,
      nameTextStyle: {
        fontSize: 14,
        fontWeight: "bold",
        padding : [10,0,0,0]
      },
      axisLabel: {
        rotate: 45,
        interval: 1,
      
      },
    },
    yAxis: {
      type: "value",
      name: "Kuantiti",
      nameLocation: "center",
      nameRotate: "90",
      nameTextStyle: {
        fontSize: 14,
        fontWeight: "bold",
        padding : [0,0,40,0]
      },
    },
    series: seriesData,
    barCategoryGap: "80%",
    barCategoryGap: "10%"
  };

  barChart.setOption(optionBarCharts);
  window.addEventListener("resize", () => barChart.resize());
}

// Clear Tampilan Bar Chart 
function resetBarChart() {
  var barChart = echarts.init(document.getElementById("barDiagram"));
  barChart.clear();
  barChart.resize();
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

function updateTable1(data) {
  console.log("📋 Updating Table with Data:", data);

  var tableBody = document.querySelector("#tableKode1 tbody");
  if (!tableBody) {
    console.error("❌ Table body tidak ditemukan!");
    return;
  }

  tableBody.innerHTML = ""; // 🔄 Reset tabel sebelum update

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
    var cell0 = newRow.insertCell(0);
    var cell1 = newRow.insertCell(1);
    var cell2 = newRow.insertCell(2);
    var cell3 = newRow.insertCell(3);
    newRow.className = "hover:bg-blue-50 transition-all duration-200 shadow-sm"; // Tailwind untuk tampilan lebih rapi

    let itemNumber = (currentPage - 1) * rowsPerPage + index + 1; // ✅ Menyesuaikan nomor urut berdasarkan halaman

    cell0.textContent = itemNumber; // 🔹 Nomor urut berlanjut
    cell1.textContent = row.promo;
    cell2.textContent = parseInt(row.Qty) || 0;
    cell3.textContent = new Intl.NumberFormat("id-ID", {
      style: "decimal",
    }).format(row.Total);
    cell0.classList.add("text-center");
    cell2.classList.add("text-center");
  });

  console.log("✅ Table updated successfully!");
}

// function updateTable2(data) {
//   console.log("📋 Updating Table with Data:", data);

//   var tableBody = document.querySelector("#tableKode2 tbody");
//   if (!tableBody) {
//     console.error("❌ Table body tidak ditemukan!");
//     return;
//   }

//   tableBody.innerHTML = ""; // 🔄 Reset tabel sebelum update

//   if (!Array.isArray(data) || data.length === 0) {
//     console.warn("⚠️ Data tabel kosong atau bukan array!", data);
//     var newRow = tableBody.insertRow();
//     var cell = newRow.insertCell(0);
//     cell.colSpan = 4;
//     cell.textContent = "Tidak ada data tersedia";
//     cell.style.textAlign = "center";
//     return;
//   }

//   data.forEach((row, index) => {
//     var newRow = tableBody.insertRow();
//     newRow.className = "border border-gray-300 hover:bg-gray-100"; // Tailwind untuk tampilan lebih rapi

//     let itemNumber = (currentPageKode2 - 1) * rowsPerPage + index + 1; // ✅ Menyesuaikan nomor urut berdasarkan halaman

//     newRow.insertCell(0).textContent = itemNumber; // 🔹 Nomor urut berlanjut
//     newRow.insertCell(1).textContent = row.promo;
//     newRow.insertCell(2).textContent = parseInt(row.Qty) || 0;
//     newRow.insertCell(3).textContent = new Intl.NumberFormat("id-ID", {
//       style: "decimal",
//     }).format(row.Total);
//   });

//   console.log("✅ Table updated successfully!");
// }
// function updateTable3(data) {
//   console.log("📋 Updating Table with Data:", data);

//   var tableBody = document.querySelector("#tableKode3 tbody");
//   if (!tableBody) {
//     console.error("❌ Table body tidak ditemukan!");
//     return;
//   }

//   tableBody.innerHTML = ""; // 🔄 Reset tabel sebelum update

//   if (!Array.isArray(data) || data.length === 0) {
//     console.warn("⚠️ Data tabel kosong atau bukan array!", data);
//     var newRow = tableBody.insertRow();
//     var cell = newRow.insertCell(0);
//     cell.colSpan = 4;
//     cell.textContent = "Tidak ada data tersedia";
//     cell.style.textAlign = "center";
//     return;
//   }

//   data.forEach((row, index) => {
//     var newRow = tableBody.insertRow();
//     newRow.className = "border border-gray-300 hover:bg-gray-100"; // Tailwind untuk tampilan lebih rapi

//     let itemNumber = (currentPageKode3 - 1) * rowsPerPage + index + 1; // ✅ Menyesuaikan nomor urut berdasarkan halaman

//     newRow.insertCell(0).textContent = itemNumber; // 🔹 Nomor urut berlanjut
//     newRow.insertCell(1).textContent = row.promo;
//     newRow.insertCell(2).textContent = parseInt(row.Qty) || 0;
//     newRow.insertCell(3).textContent = new Intl.NumberFormat("id-ID", {
//       style: "decimal",
//     }).format(row.Total);
//   });

//   console.log("✅ Table updated successfully!");
// }
// function updateTable4(data) {
//   console.log("📋 Updating Table with Data:", data);

//   var tableBody = document.querySelector("#tableKode4 tbody");
//   if (!tableBody) {
//     console.error("❌ Table body tidak ditemukan!");
//     return;
//   }

//   tableBody.innerHTML = ""; // 🔄 Reset tabel sebelum update

//   if (!Array.isArray(data) || data.length === 0) {
//     console.warn("⚠️ Data tabel kosong atau bukan array!", data);
//     var newRow = tableBody.insertRow();
//     var cell = newRow.insertCell(0);
//     cell.colSpan = 4;
//     cell.textContent = "Tidak ada data tersedia";
//     cell.style.textAlign = "center";
//     return;
//   }

//   data.forEach((row, index) => {
//     var newRow = tableBody.insertRow();
//     newRow.className = "border border-gray-300 hover:bg-gray-100"; // Tailwind untuk tampilan lebih rapi

//     let itemNumber = (currentPageKode4 - 1) * rowsPerPage + index + 1; // ✅ Menyesuaikan nomor urut berdasarkan halaman

//     newRow.insertCell(0).textContent = itemNumber; // 🔹 Nomor urut berlanjut
//     newRow.insertCell(1).textContent = row.promo;
//     newRow.insertCell(2).textContent = parseInt(row.Qty) || 0;
//     newRow.insertCell(3).textContent = new Intl.NumberFormat("id-ID", {
//       style: "decimal",
//     }).format(row.Total);
//   });

//   console.log("✅ Table updated successfully!");
// }
// function updateTable5(data) {
//   console.log("📋 Updating Table with Data:", data);

//   var tableBody = document.querySelector("#tableKode5 tbody");
//   if (!tableBody) {
//     console.error("❌ Table body tidak ditemukan!");
//     return;
//   }

//   tableBody.innerHTML = ""; // 🔄 Reset tabel sebelum update

//   if (!Array.isArray(data) || data.length === 0) {
//     console.warn("⚠️ Data tabel kosong atau bukan array!", data);
//     var newRow = tableBody.insertRow();
//     var cell = newRow.insertCell(0);
//     cell.colSpan = 4;
//     cell.textContent = "Tidak ada data tersedia";
//     cell.style.textAlign = "center";
//     return;
//   }

//   data.forEach((row, index) => {
//     var newRow = tableBody.insertRow();
//     newRow.className = "border border-gray-300 hover:bg-gray-100"; // Tailwind untuk tampilan lebih rapi

//     let itemNumber = (currentPageKode5 - 1) * rowsPerPage + index + 1; // ✅ Menyesuaikan nomor urut berdasarkan halaman

//     newRow.insertCell(0).textContent = itemNumber; // 🔹 Nomor urut berlanjut
//     newRow.insertCell(1).textContent = row.promo;
//     newRow.insertCell(2).textContent = parseInt(row.Qty) || 0;
//     newRow.insertCell(3).textContent = new Intl.NumberFormat("id-ID", {
//       style: "decimal",
//     }).format(row.Total);
//   });

//   console.log("✅ Table updated successfully!");
// }
function updatePagination() {
  console.log("🔄 Updating Pagination...");

  document.getElementById(
    "pageInfo"
  ).textContent = `Page ${currentPage} of ${totalPages}`;

  document.getElementById("prevBtn").disabled = currentPage <= 1;
  document.getElementById("nextBtn").disabled = currentPage >= totalPages;

  console.log(`📌 Current Page: ${currentPage}, Total Pages: ${totalPages}`);
}
// function updatePagination2() {
//   console.log("🔄 Updating Pagination 2...");

//   document.getElementById(
//     "pageInfo2"
//   ).textContent = `Page ${currentPageKode2} of ${totalPages2}`;

//   document.getElementById("prevBtn2").disabled = currentPageKode2 <= 1;
//   document.getElementById("nextBtn2").disabled = currentPageKode2 >= totalPages2;

//   console.log(`📌 Current Page: ${currentPageKode2}, Total Pages: ${totalPages2}`);
// }
// function updatePagination3() {
//   console.log("🔄 Updating Pagination 3...");

//   document.getElementById(
//     "pageInfo3"
//   ).textContent = `Page ${currentPageKode3} of ${totalPages3}`;

//   document.getElementById("prevBtn3").disabled = currentPageKode3 <= 1;
//   document.getElementById("nextBtn3").disabled = currentPageKode3 >= totalPages3;

//   console.log(`📌 Current Page: ${currentPageKode3}, Total Pages: ${totalPages3}`);
// }
// function updatePagination4() {
//   console.log("🔄 Updating Pagination 4...");

//   document.getElementById(
//     "pageInfo4"
//   ).textContent = `Page ${currentPageKode4} of ${totalPages4}`;

//   document.getElementById("prevBtn4").disabled = currentPageKode4 <= 1;
//   document.getElementById("nextBtn4").disabled = currentPageKode4 >= totalPages4;

//   console.log(`📌 Current Page: ${currentPageKode4}, Total Pages: ${totalPages4}`);
// }
// function updatePagination5() {
//   console.log("🔄 Updating Pagination 5...");

//   document.getElementById(
//     "pageInfo5"
//   ).textContent = `Page ${currentPageKode5} of ${totalPages5}`;

//   document.getElementById("prevBtn5").disabled = currentPageKode5 <= 1;
//   document.getElementById("nextBtn5").disabled = currentPageKode5 >= totalPages5;

//   console.log(`📌 Current Page: ${currentPageKode5}, Total Pages: ${totalPages5}`);
// }

// Prev Dan Next Tabel 1
document.getElementById("nextBtn").addEventListener("click", function (e) {
  e.preventDefault()
  if (currentPage < totalPages) {
    currentPage++;
    sendKodeSupp1(currentPage);
  }
});

document.getElementById("prevBtn").addEventListener("click", function (e) {
  e.preventDefault()
  if (currentPage > 1) {
    currentPage--;
    sendKodeSupp1(currentPage);
  }
});

function exportToExcel() {
  const limit = 1000;
  let formDataExport = new FormData();
  formDataExport.append("ajax", true);
  formDataExport.append("csrf_token", document.querySelector("[name='csrf_token']").value);
  formDataExport.append("kd_store", document.querySelector("#kd_store")?.value);
  formDataExport.append("start_date", document.querySelector("#date")?.value);
  formDataExport.append("selectKode", document.querySelector("#supplierDropdown")?.value);
  formDataExport.append("end_date", document.querySelector("#date1")?.value);
  formDataExport.append("limit", limit);
  formDataExport.append('export_all', true);

  const cabangText =
  document.getElementById("cabang").options[
    document.getElementById("cabang").selectedIndex
  ].text;
  const selectedKode = document.querySelector("#supplierDropdown")?.value;

  fetch("in_sales_ratio_proses_table.php?ajax=1", {
    method: "POST",
    body: formDataExport
  })
    .then(response => response.json())
    .then(data => {
      console.log("response data: ", data)
      let workbook = new ExcelJS.Workbook();
      let worksheet = workbook.addWorksheet("Data Penjualan");

      // **Buat Header**
      let headers = ["No", "SUB DEPT", "QTY", "TOTAL"];
      let headerRow = worksheet.addRow(headers);

      // **Styling Header**
      headerRow.eachCell((cell, colNumber) => {
        cell.font = { bold: true, color: { argb: "FFFFFFFF" }, size: 12 };
        cell.alignment = { horizontal: "center", vertical: "middle" };
        cell.fill = { type: "pattern", pattern: "solid", fgColor: { argb: "0070C0" } };
        cell.border = {
          top: { style: "thin" },
          bottom: { style: "thin" },
          left: { style: "thin" },
          right: { style: "thin" }
        };
      });

      // **Tambahkan Data**
      data.tableData.forEach((item, index) => {
        let row = worksheet.addRow([index + 1, item.promo, item.Qty, item.Total]);
        row.getCell(1).alignment = { horizontal: "center" }; // No → Tengah
        row.getCell(2).alignment = { horizontal: "left" };   // SUB DEPT → Kiri
        row.getCell(3).alignment = { horizontal: "center" }; // QTY → Tengah
        row.getCell(4).alignment = { horizontal: "left" };   // TOTAL → Kiri

        row.getCell(4).numFmt = '"Rp" #,##0'
        row.eachCell((cell) => {
          cell.border = {
            top: { style: "thin" },
            bottom: { style: "thin" },
            left: { style: "thin" },
            right: { style: "thin" }
          };
        });
      });

      // **Atur Lebar Kolom**
      worksheet.columns = [
        { width: 5 },   // No
        { width: 40 },  // SUB DEPT
        { width: 10 },  // QTY
        { width: 15 }   // TOTAL
      ];

      // **Simpan File**
      workbook.xlsx.writeBuffer().then(buffer => {
        let blob = new Blob([buffer], { type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" });
        let link = document.createElement("a");
        link.href = URL.createObjectURL(blob);
        link.download = `Data_Penjualan_Cabang_${cabangText}_Supplier${selectedKode}.xlsx`;
        link.click();
      });
    })
    .catch(error => console.error("Error fetching data:", error));
}

function exportToPDF() {
  const limit = 1000;
  let formDataExport = new FormData();
  formDataExport.append("ajax", true);
  formDataExport.append("csrf_token", document.querySelector("[name='csrf_token']").value);
  formDataExport.append("kd_store", document.querySelector("#kd_store")?.value);
  formDataExport.append("start_date", document.querySelector("#date")?.value);
  formDataExport.append("selectKode", document.querySelector("#supplierDropdown")?.value);
  formDataExport.append("end_date", document.querySelector("#date1")?.value);
  formDataExport.append("limit", limit);
  formDataExport.append("export_all", true);


  const { jsPDF } = window.jspdf;
  let doc = new jsPDF();
  const cabangText =
    document.getElementById("cabang").options[
      document.getElementById("cabang").selectedIndex
    ].text;
  const selectedKode = document.querySelector("#supplierDropdown")?.value;
  fetch("in_sales_ratio_proses_table.php?ajax=1", {
    method: "POST",
    body: formDataExport
  })
    .then(response => response.json())
    .then(data => {
      console.log("data export pdf: ", data)
      if (!data.tableData || data.tableData.length === 0) {
        console.error("Tidak ada data untuk diekspor.");
        return;
      }

      let headers = ["No", "SUB DEPT", "QTY", "TOTAL"];
      let rows = data.tableData.map((item, index) => [
        index + 1, item.promo, item.Qty, `Rp. ${new Intl.NumberFormat('id-ID').format(item.Total)}`
      ]);

      doc.autoTable({
        head: [headers],
        body: rows,
        startY: 50,
        theme: 'grid',
        styles: { fontSize: 8, cellPadding: 3 }, // **Default Style**
        headStyles: {
          fillColor: [0, 112, 192],
          textColor: [255, 255, 255],
          fontSize: 12,
          fontStyle: "bold",
          halign: "center" // Header selalu di tengah
        },
        bodyStyles: {
          textColor: [10,10,10],
        },
        columnStyles: {
          0: { halign: "center" }, // "No" rata tengah
          1: { halign: "left" },   // "SUB DEPT" rata kiri
          2: { halign: "center" }, // "QTY" rata tengah
          3: { halign: "left" }    // "TOTAL" rata kiri
        },
        margin: { top: 50 },
        didDrawPage: function (data) {
          const logo = "/images/logo.png"; // Ganti dengan base64 logo
          doc.addImage(logo, "PNG", 15, 10, 25, 10); // (x, y, width, height)

          doc.setFont("Times New Roman", "bold");
          doc.setFontSize(14);
          doc.text("PT. Asoka Indonesia", 45, 15);

          doc.setFontSize(12);
          doc.setFont("Times New Roman", "normal");
          doc.text("Lb 5, Jl. Utan Jati Blok Lb 5 No.9, RT.10/RW.12, Kalideres, West Jakarta City, Jakarta 11840", 45, 20);
          doc.text("Telp: 0819-4943-1969 | Email: info@contoh.com", 45, 25);

          // **Buat Garis Pemisah**
          doc.setLineWidth(0.5);
          doc.line(10, 30, 200, 30);
          // **Tambahkan Judul**
          doc.setFont("Arial Black", "bold");
          doc.setFontSize(12);
          doc.text(`Laporan Data Penjualan Sales Cabang ${cabangText} Supplier ${selectedKode}`, 15, 45);
        }
      });
      doc.save(`Data_Penjualan_Cabang_${cabangText}_Supplier_${selectedKode}.pdf`);
    })
    .catch(error => console.error("Error fetching data:", error));
}

// // Prev Dan Next Tabel 2
// document.getElementById("nextBtn2").addEventListener("click", function (e) {
//   e.preventDefault()
//   if (currentPageKode2 < totalPages2) {
//     currentPageKode2++;
//     sendKodeSupp2(currentPageKode2);
//   }
// });
// document.getElementById("prevBtn2").addEventListener("click", function (e) {
//   e.preventDefault()
//   if (currentPageKode2 > 1) {
//     currentPageKode2--;
//     sendKodeSupp2(currentPageKode2);
//   }
// });
// // Prev Dan Next Tabel 3
// document.getElementById("nextBtn3").addEventListener("click", function (e) {
//   e.preventDefault()
//   if (currentPageKode3 < totalPages3) {
//     currentPageKode3++;
//     sendKodeSupp3(currentPageKode3);

//   }
// });
// document.getElementById("prevBtn3").addEventListener("click", function (e) {
//   e.preventDefault()
//   if (currentPageKode3 > 1) {
//     currentPageKode3--;
//     sendKodeSupp3(currentPageKode3);
//   }
// });

// // Prev Dan Next Tabel 4
// document.getElementById("nextBtn4").addEventListener("click", function (e) {
//   e.preventDefault()
//   if (currentPageKode4 < totalPages4) {
//     currentPageKode4++;
//     sendKodeSupp4(currentPageKode4);

//   }
// });
// document.getElementById("prevBtn4").addEventListener("click", function (e) {
//   e.preventDefault()
//   if (currentPageKode4 > 1) {
//     currentPageKode4--;
//     sendKodeSupp4(currentPageKode4);
//   }
// });

// // Prev Dan Next Tabel 5
// document.getElementById("nextBtn5").addEventListener("click", function (e) {
//   e.preventDefault()
//   if (currentPageKode5 < totalPages5) {
//     currentPageKode5++;
//     sendKodeSupp5(currentPageKode5);

//   }
// });
// document.getElementById("prevBtn5").addEventListener("click", function (e) {
//   e.preventDefault()
//   if (currentPageKode5 > 1) {
//     currentPageKode5--;
//     sendKodeSupp5(currentPageKode5);
//   }
// });





=======
$(".supplier-input").hide();
let totalPages = 0; // Untuk supplier 1
let totalPages2 = 0; // Untuk supplier 2
let totalPages3 = 0; // Untuk supplier 2
let totalPages4 = 0; // Untuk supplier 2
let totalPages5 = 0; // Untuk supplier 2
let currentPage = 1;
let currentPageKode2 = 1;
let currentPageKode3 = 1;
let currentPageKode4 = 1;
let currentPageKode5 = 1;
let rowsPerPage = 13;
document.getElementById("lihatTable").style.display = "none";
document.getElementById("sendTable").style.display = "none";
document.getElementById("hideTable").style.display = "none";
document.getElementById("bar").style.display = "none";

document.getElementById("toggle-sidebar").addEventListener("click", function () {
  document.getElementById("sidebar").classList.toggle("open");
});
document.addEventListener("DOMContentLoaded", function () {
  const sidebar = document.getElementById("sidebar");
  const closeBtn = document.getElementById("closeSidebar");

  closeBtn.addEventListener("click", function () {
    sidebar.classList.remove("open"); // Hilangkan class .open agar sidebar tertutup
  });
});
document.getElementById("toggle-hide").addEventListener("click", function () {
  var sidebarTexts = document.querySelectorAll(".sidebar-text");
  let mainContent = document.getElementById("main-content");
  let sidebar = document.getElementById("sidebar");
  var toggleButton = document.getElementById("toggle-hide");
  var icon = toggleButton.querySelector("i");
  var barChart = echarts.init(document.getElementById("barDiagram"));

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
      barChart.resize();
    }, 250)
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
      barChart.resize();
    }, 250)
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

$(document).ready(function () {
  // Daftar kode store sesuai dengan cabang
  var storeCodes = {
    'ABIN': '1502',
    'ACE': '1505',
    'ACIB': '1379',
    'ACIL': '1504',
    'ACIN': '1641',
    'ACSA': '1902',
    'ADET': '1376',
    'ADMB': '3190',
    'AHA': '1506',
    'AHIN': '2102',
    'ALANG': '1503',
    'ANGIN': '2102',
    'APEN': '1908',
    'APIK': '3191',
    'APRS': '1501',
    'ARAW': '1378',
    'ARUNG': '1611',
    'ASIH': '2104',
    'ATIN': '1642',
    'AWIT': '1377',
    'AXY': '2103',
    'SEMUA CABANG': allCabang
  };

  var allCabang = Object.values(storeCodes);
  // Event listener ketika cabang berubah
  $('#cabang').on('change', function () {
    var selectedBranch = $(this).val(); // Ambil nilai cabang yang dipilih
    var storeCode = storeCodes[selectedBranch] || '1501'; // Ambil kode store atau default '1501'

    if (selectedBranch === 'SEMUA CABANG') {
      $('#kd_store').val(allCabang.join(','))
    } else {
      $('#kd_store').val(storeCode); // Set nilai input kode_store
    }
  });

  // Trigger event saat halaman dimuat untuk mengisi nilai awal
  $("#cabang").trigger("change");
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
// POST Data Dari Form
document.getElementById("laporanForm").addEventListener("submit", function (e) {
  e.preventDefault(); // Mencegah form dikirim secara default
  sendDataToBar();
  var barChart = echarts.init(document.getElementById("barDiagram"));
  setTimeout(() => {
    barChart.resize()
  }, 300)
  document.getElementById("bar").style.display = "block";
  document.getElementById("lihatTable").style.display = "block";
  document.getElementById("sendTable").style.display = "block";
});
// Select Ratio Tampilkan Input
$(document).ready(function () {
  $("#ratio_number").on("change", function () {
    let selectedValue = parseInt($(this).val());

    $("[id^='kode_supp']").hide();
    // Sembunyikan semua input dulu
    if (isNaN(selectedValue)) {
      $(".supplier-input").hide();
    }

    // Tampilkan input sesuai jumlah yang dipilih
    for (let i = 1; i <= selectedValue; i++) {
      $("#kode_supp" + i).show();
    }
  });
});

// Menampilkan Data Pada Klik Input
$(document).on("focus click", ".supplier-input", function () {
  let inputField = $(this);

  if (!inputField.data("loaded")) { // Cegah duplikasi load
    $.ajax({
      url: "https://asokababystore.com/get_kode_supp.php",
      type: "GET",
      dataType: "json",
      success: function (response) {
        console.log("Data diterima:", response);

        // Ubah format jadi array hanya berisi kode_supp
        let dataSuplier = response.map(item => item.kode_supp.trim());

        inputField.data("loaded", true);
        inputField.data("suppliers", dataSuplier);

        showSuggestions(inputField, dataSuplier); // 🔹 Panggil langsung
      },
      error: function () {
        console.error("Gagal mengambil data supplier");
      },
    });
  } else {
    showSuggestions(inputField, inputField.data("suppliers"));
  }
});

// Pencarian Data Lewat Input Ketik
$(document).on("input", ".supplier-input", function () {
  let inputField = $(this);
  let keyword = inputField.val().toLowerCase();
  let allSuppliers = inputField.data("suppliers") || [];

  let filteredSuppliers = allSuppliers.filter(supplier =>
    supplier.toLowerCase().includes(keyword)
  );

  showSuggestions(inputField, filteredSuppliers);
});

// Memunculkan Data Pada Input Untuk Di Pilih
function showSuggestions(inputField, suppliers) {
  $(".suggestion-box").remove(); // Hapus dropdown lama

  let suggestionBox = $("<div>")
    .addClass("absolute bg-white border border-gray-300 w-full max-h-40 overflow-y-auto z-50 shadow-md rounded-md suggestion-box");

  if (!Array.isArray(suppliers) || suppliers.length === 0) {
    $("<div>")
      .addClass("px-4 py-2 text-gray-500 text-center italic")
      .text("Supplier tidak ditemukan")
      .appendTo(suggestionBox);
  } else {
    suppliers.forEach(kode_supp => {
      $("<div>")
        .addClass("px-4 py-2 cursor-pointer border-b border-gray-200 text-gray-700 hover:bg-gray-100")
        .text(kode_supp)
        .on("click", function () {
          inputField.val($(this).text());
          $(".suggestion-box").remove();
        })
        .appendTo(suggestionBox);
    });
  }

  // 🔥 Posisikan dropdown di bawah input tanpa mengubah layout asli
  let inputOffset = inputField.offset();
  suggestionBox.css({
    position: "absolute",
    top: inputOffset.top + inputField.outerHeight(),
    left: inputOffset.left,
    width: inputField.outerWidth(),
    background: "#fff",
    borderRadius: "5px",
    border: "1px solid #ddd",
    boxShadow: "0px 4px 6px rgba(0, 0, 0, 0.1)"
  });

  $("body").append(suggestionBox);
}

$(document).on("click", function (e) {
  if (!$(e.target).closest(".supplier-input, .suggestion-box").length) {
    $(".suggestion-box").remove(); // Tutup dropdown jika klik di luar
  }
});


// 🔥 Saat klik tombol "Cek Data", simpan input ke dalam select
$("#btn-submit").on("click", function () {
  updateSupplierDropdown();
});

// 🔥 Fungsi untuk update dropdown supplier
function updateSupplierDropdown() {
  let supplierDropdown = $("#supplierDropdown");

  $(".supplier-input").each(function () {
    let value = $(this).val().trim();
    if (value !== "" && !isOptionExists(value)) {
      supplierDropdown.append(`<option value="${value}">${value}</option>`);
    }
  });
}

// Cek apakah opsi sudah ada di dropdown
function isOptionExists(value) {
  return $("#supplierDropdown option").filter(function () {
    return $(this).val() === value;
  }).length > 0;
}

document.getElementById("sendTable").addEventListener("click", function (e) {
  e.preventDefault();
  currentPage = 1;
  sendKodeSupp1(currentPage);
})

function sendKodeSupp1(page) {
  const Toast = Swal.mixin({
    toast: true,
    position: "top-end",
    showConfirmButton: false,
    timerProgressBar: true,
    didOpen: (toast) => {
      toast.onmouseenter = Swal.stopTimer;
      toast.onmouseleave = Swal.resumeTimer;
    }
  });
  document.getElementById("hideTable").style.display = "block";
  var barChart = echarts.init(document.getElementById("barDiagram"));
  setTimeout(() => {
    barChart.resize()
  }, 300)
  var cabangText =
    document.getElementById("cabang").options[
      document.getElementById("cabang").selectedIndex
    ].text;
  var startDate = $("#date").val(); // Ambil nilai langsung dari datepicker
  var endDate = $("#date1").val();
  var reportHeader = document.getElementById("reportHeader1");
  var kodeSupp = $("#supplierDropdown").val();
  reportHeader.innerHTML = `Data Penjualan<br><p> Cabang :  ${cabangText}, Supplier: ${kodeSupp} (${startDate} s/d ${endDate})</p>`;
  let formDataToTable = new FormData;
  formDataToTable.append("ajax", true);
  formDataToTable.append("csrf_token", document.querySelector("[name='csrf_token']").value);
  formDataToTable.append("selectKode", document.querySelector("#supplierDropdown")?.value);
  formDataToTable.append("kd_store", document.querySelector("#kd_store")?.value);
  formDataToTable.append("start_date", document.querySelector("#date")?.value);
  formDataToTable.append("end_date", document.querySelector("#date1")?.value);
  formDataToTable.append('page', page);
  for (var pair of formDataToTable.entries()) {
    console.log(pair[0] + ": " + pair[1]);
  }
  $.ajax({
    url: "in_sales_ratio_proses_table?ajax=1",
    method: "POST",
    dataType: "json",
    processData: false,
    contentType: false,
    data: formDataToTable,
    success: (response) => {
      console.log("Response data ke table 1:", response);
      if (response.tableData && response.tableData.length > 0) {
        totalPages = response.totalPages ? response.totalPages : 1;
        updateTable1(response.tableData);
        Toast.fire({
          icon: "success",
          title: "Berhasil, Tabel Sudah Muncul",
        });
        setTimeout(() => {
          if (Swal.getPopup()) {
            Swal.getPopup().remove();
          }
        }, 1300);
      } else {
        console.log("Data Tabel Tidak Ada", response);
        Toast.fire({
          icon: "error",
          title: "Data Tidak Ditemukan!",
        });
        setTimeout(() => {
          if (Swal.getPopup()) {
            Swal.getPopup().remove();
          }
        }, 1300);
      }
    },
    error: (xhr, status, error) => {
      console.log("Error : ", status, error);
    }
  });

}

// function sendKodeSupp2(page) {
//   var cabangText =
//     document.getElementById("cabang").options[
//       document.getElementById("cabang").selectedIndex
//     ].text;
//   var startDate = $("#date").val(); // Ambil nilai langsung dari datepicker
//   var endDate = $("#date1").val();
//   var reportHeader = document.getElementById("reportHeader2");
//   var kodeSupp = $("#kode_supp2").val();
//   reportHeader.innerHTML = `Data Penjualan<br><p> Cabang :  ${cabangText}, Supplier: ${kodeSupp} (${startDate} s/d ${endDate})</p>`;
//   let formDataToTable = new FormData;
//   formDataToTable.append("ajax", true);
//   formDataToTable.append("csrf_token", document.querySelector("[name='csrf_token']").value);
//   formDataToTable.append("kode_supp2", document.querySelector("#kode_supp2")?.value);
//   formDataToTable.append("kd_store", document.querySelector("#kd_store")?.value);
//   formDataToTable.append("start_date", document.querySelector("#date")?.value);
//   formDataToTable.append("end_date", document.querySelector("#date1")?.value);
//   formDataToTable.append('page', page);

//   $.ajax({
//     url: "in_sales_ratio_proses_table?ajax=1",
//     method: "POST",
//     dataType: "json",
//     processData: false,
//     contentType: false,
//     data: formDataToTable,
//     success: (response) => {
//       console.log("Response data ke table 2:", response);
//       if (response.tableData) {
//         totalPages2 = response.totalPages;
//         updateTable2(response.tableData);
//         updatePagination2();
//       } else {
//         console.log("Data Tabel Tidak Ada", response);
//       }
//     },
//     error: (xhr, status, error) => {
//       console.log("Error : ", status, error);
//     }
//   });

// }

// function sendKodeSupp3(page) {
//   var cabangText =
//     document.getElementById("cabang").options[
//       document.getElementById("cabang").selectedIndex
//     ].text;
//   var startDate = $("#date").val(); // Ambil nilai langsung dari datepicker
//   var endDate = $("#date1").val();
//   var reportHeader = document.getElementById("reportHeader3");
//   var kodeSupp = $("#kode_supp3").val();
//   reportHeader.innerHTML = `Data Penjualan<br><p> Cabang :  ${cabangText}, Supplier: ${kodeSupp} (${startDate} s/d ${endDate})</p>`;
//   let formDataToTable = new FormData;
//   formDataToTable.append("ajax", true);
//   formDataToTable.append("csrf_token", document.querySelector("[name='csrf_token']").value);
//   formDataToTable.append("kode_supp3", document.querySelector("#kode_supp3")?.value);
//   formDataToTable.append("kd_store", document.querySelector("#kd_store")?.value);
//   formDataToTable.append("start_date", document.querySelector("#date")?.value);
//   formDataToTable.append("end_date", document.querySelector("#date1")?.value);
//   formDataToTable.append('page', page);
//   $.ajax({
//     url: "in_sales_ratio_proses_table?ajax=1",
//     method: "POST",
//     dataType: "json",
//     processData: false,
//     contentType: false,
//     data: formDataToTable,
//     success: (response) => {
//       console.log("Response data ke table 3:", response);
//       if (response.tableData) {
//         totalPages3 = response.totalPages ? response.totalPages : 1;
//         updateTable3(response.tableData);
//         updatePagination3();
//       } else {
//         console.log("Data Tabel Tidak Ada", response);
//       }
//     },
//     error: (xhr, status, error) => {
//       console.log("Error : ", status, error);
//     }
//   });

// }

// function sendKodeSupp4(page) {
//   var cabangText =
//     document.getElementById("cabang").options[
//       document.getElementById("cabang").selectedIndex
//     ].text;
//   var startDate = $("#date").val(); // Ambil nilai langsung dari datepicker
//   var endDate = $("#date1").val();
//   var reportHeader = document.getElementById("reportHeader4");
//   var kodeSupp = $("#kode_supp1").val();
//   reportHeader.innerHTML = `Data Penjualan<br><p> Cabang :  ${cabangText}, Supplier: ${kodeSupp} (${startDate} s/d ${endDate})</p>`;
//   let formDataToTable = new FormData;
//   formDataToTable.append("ajax", true);
//   formDataToTable.append("csrf_token", document.querySelector("[name='csrf_token']").value);
//   formDataToTable.append("kode_supp4", document.querySelector("#kode_supp4")?.value);
//   formDataToTable.append("kd_store", document.querySelector("#kd_store")?.value);
//   formDataToTable.append("start_date", document.querySelector("#date")?.value);
//   formDataToTable.append("end_date", document.querySelector("#date1")?.value);
//   formDataToTable.append('page', page);
//   $.ajax({
//     url: "in_sales_ratio_proses_table?ajax=1",
//     method: "POST",
//     dataType: "json",
//     processData: false,
//     contentType: false,
//     data: formDataToTable,
//     success: (response) => {
//       console.log("Response data ke table 4:", response);
//       if (response.tableData) {
//         totalPages4 = response.totalPages ? response.totalPages : 1;
//         updateTable4(response.tableData);
//         updatePagination4();
//       } else {
//         console.log("Data Tabel Tidak Ada", response);
//       }
//     },
//     error: (xhr, status, error) => {
//       console.log("Error : ", status, error);
//     }
//   });

// }
// function sendKodeSupp5(page) {
//   var cabangText =
//     document.getElementById("cabang").options[
//       document.getElementById("cabang").selectedIndex
//     ].text;
//   var startDate = $("#date").val(); // Ambil nilai langsung dari datepicker
//   var endDate = $("#date1").val();
//   var reportHeader = document.getElementById("reportHeader5");
//   var kodeSupp = $("#kode_supp5").val();
//   reportHeader.innerHTML = `Data Penjualan<br><p> Cabang :  ${cabangText}, Supplier: ${kodeSupp} (${startDate} s/d ${endDate})</p>`;
//   let formDataToTable = new FormData;
//   formDataToTable.append("ajax", true);
//   formDataToTable.append("csrf_token", document.querySelector("[name='csrf_token']").value);
//   formDataToTable.append("kode_supp5", document.querySelector("#kode_supp5")?.value);
//   formDataToTable.append("kd_store", document.querySelector("#kd_store")?.value);
//   formDataToTable.append("start_date", document.querySelector("#date")?.value);
//   formDataToTable.append("end_date", document.querySelector("#date1")?.value);
//   formDataToTable.append('page', page);
//   $.ajax({
//     url: "in_sales_ratio_proses_table?ajax=1",
//     method: "POST",
//     dataType: "json",
//     processData: false,
//     contentType: false,
//     data: formDataToTable,
//     success: (response) => {
//       console.log("Response data ke table 5:", response);
//       if (response.tableData) {
//         totalPages5 = response.totalPages ? response.totalPages : 1;
//         updateTable5(response.tableData);
//         updatePagination5();
//       } else {
//         console.log("Data Tabel Tidak Ada", response);
//       }
//     },
//     error: (xhr, status, error) => {
//       console.log("Error : ", status, error);
//     }
//   });

// }
function sendDataToBar() {
  var selectRatio = document.querySelector("#ratio_number").value;
  console.log("ratio number value", selectRatio)
  if (selectRatio === "none") {
    Swal.fire({
      icon: "warning",
      title: "Error",
      text: "Ratio Harus Di Pilih",
    });
    document.getElementById("barChart").style.display = 'none';
    return;
  } else {
    Swal.fire({
      title: "Loading...",
      html: "Tunggu Sebentar",
      allowOutsideClick: false,
      timerProgressBar: true,
      didOpen: () => {
        Swal.showLoading();
      },
    });
  }

  let formData = new FormData();
  var barChart = echarts.init(document.getElementById("barDiagram"));
  setTimeout(() => {
    barChart.resize()
  }, 300)
  resetBarChart();
  formData.append("ajax", true);
  formData.append("csrf_token", document.querySelector("[name='csrf_token']").value);
  formData.append("kode_supp1", document.querySelector("#kode_supp1")?.value);
  formData.append("kode_supp2", document.querySelector("#kode_supp2")?.value);
  formData.append("kode_supp3", document.querySelector("#kode_supp3")?.value);
  formData.append("kode_supp4", document.querySelector("#kode_supp4")?.value);
  formData.append("kode_supp5", document.querySelector("#kode_supp5")?.value);
  formData.append("kd_store", document.querySelector("#kd_store")?.value);
  formData.append("start_date", document.querySelector("#date")?.value);
  formData.append("end_date", document.querySelector("#date1")?.value);



  $.ajax({
    url: "in_sales_ratio_proses_bar.php?ajax=1",
    method: "POST",
    dataType: "json",
    processData: false,
    contentType: false,
    data: formData,
    success: (response) => {
      console.log("Response data: ", response);
      updateBarChart(response.labels, response.data, response.tableData);
      Swal.close();
    },
    error: (xhr, status, error) => {
      console.log("Error : ", status, error);
      Swal.fire({
        icon: "error",
        title: "Error",
        text: "Gagal Menampilkan Data!",
      });
      Swal.close();
    }
  });
}



// Update Tampilan Bar Chart Sesuai Data
function updateBarChart(labels, data, table) {
  console.log("📊 Updating Bar Chart with Data:", labels, data, table);

  // Persiapkan data baru dengan sorting berdasarkan tanggal
  var newData = labels.map((label, index) => ({
    kode_supp: label,
    Qty: Number(data[index]),
    tanggal: String(table[index]?.periode),
    precentage: table[index]?.Percentage
  }));
  console.log("Data Tabel: ", newData);
  newData.sort((a, b) => {
    let [dayA, monthA] = a.tanggal.split("-").map(Number);
    let [dayB, monthB] = b.tanggal.split("-").map(Number);
    return monthA !== monthB ? monthA - monthB : dayA - dayB;
  });

  // Ambil daftar unik tanggal dan kode supplier (kods)
  var tanggal = [...new Set(newData.map((item) => item.tanggal))];
  var kods = [...new Set(newData.map((item) => item.kode_supp))];

  console.log("tanggal", tanggal)
  // Generate warna unik untuk setiap supplier
  var colorPalette = [
    "#ff5733", "#33ff57", "#3357ff", "#f4a261", "#e63946",
    "#6a0572", "#0e9594", "#f77f00", "#2a9d8f", "#6c757d"
  ];
  var colorMap = {};
  kods.forEach((kd, index) => {
    colorMap[kd] = colorPalette[index % colorPalette.length];
  });

  var barChart = echarts.init(document.getElementById("barDiagram"));
  console.log("kode kods", kods)
  var seriesData = kods.map((kd) => ({
    name: kd,
    type: "bar",
    data: tanggal.map((tgl) => {
      let foundItem = newData.find((item) => item.tanggal === tgl && item.kode_supp === kd);
      console.log("found", foundItem)
      return foundItem ? { value: foundItem.Qty, precentage: foundItem.precentage } : 0;
    }),
    itemStyle: {
      color: colorMap[kd],
    },
    label: {
      show: false,
      position: "insideTop",
      fontSize: 14,
      color: "#0a0a0a",
    },
  }));

  var optionBarCharts = {
    grid: {
      left: '6%',
      right: '1%',
      bottom: '3%',
      containLabel: true
    },
    tooltip: {
      trigger: "axis",
      axisPointer: {
        type: "shadow",
      },
      formatter: function (params) {
        let dateLabel = params[0].axisValue;
        let details = params
          .filter((p) => p.value > 0)
          .map((p) => `● ${p.seriesName}: <b>${p.value}</b> (${p.data.precentage})`)
          .join("<br>");
        return `<b>${dateLabel}</b><br>${details}`;
      },
    },
    legend: {
      data: kods,
    },
    toolbox: {
      show: true,
      feature: {
        dataView: { show: true, readOnly: false },
        magicType: { show: true, type: ["line", "bar", "stack"] },
        restore: { show: true },
        saveAsImage: { show: true },
      },
    },
    xAxis: {
      type: "category",
      data: tanggal,
      name: "Tahun/Bulan/Hari",
      nameLocation: "center",
      nameGap: 35,
      nameTextStyle: {
        fontSize: 14,
        fontWeight: "bold",
        padding: [10, 0, 0, 0]
      },
      axisLabel: {
        rotate: 45,
        interval: 0,

      },
    },
    yAxis: {
      type: "value",
      name: "Kuantiti",
      nameLocation: "center",
      nameRotate: "90",
      nameTextStyle: {
        fontSize: 14,
        fontWeight: "bold",
        padding: [0, 0, 40, 0]
      },
    },
    series: seriesData,
    barCategoryGap: "80%",
    barCategoryGap: "10%"
  };

  barChart.setOption(optionBarCharts);
  window.addEventListener("resize", () => barChart.resize());
}

// Clear Tampilan Bar Chart 
function resetBarChart() {
  var barChart = echarts.init(document.getElementById("barDiagram"));
  barChart.clear();
  barChart.resize();
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

function updateTable1(data) {
  console.log("📋 Updating Table with Data:", data);

  var tableBody = document.querySelector("#tableKode1 tbody");
  if (!tableBody) {
    console.error("❌ Table body tidak ditemukan!");
    return;
  }

  tableBody.innerHTML = ""; // 🔄 Reset tabel sebelum update

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
    var cell0 = newRow.insertCell(0);
    var cell1 = newRow.insertCell(1);
    var cell2 = newRow.insertCell(2);
    var cell3 = newRow.insertCell(3);
    newRow.className = "hover:bg-blue-50 transition-all duration-200 shadow-sm"; // Tailwind untuk tampilan lebih rapi

    let itemNumber = (currentPage - 1) * rowsPerPage + index + 1; // ✅ Menyesuaikan nomor urut berdasarkan halaman

    cell0.textContent = itemNumber; // 🔹 Nomor urut berlanjut
    cell1.textContent = row.promo;
    cell2.textContent = parseInt(row.Qty) || 0;
    cell3.textContent = new Intl.NumberFormat("id-ID", {
      style: "decimal",
    }).format(row.Total);
    cell0.classList.add("text-center");
    cell2.classList.add("text-center");
  });

  console.log("✅ Table updated successfully!");
}

// function updateTable2(data) {
//   console.log("📋 Updating Table with Data:", data);

//   var tableBody = document.querySelector("#tableKode2 tbody");
//   if (!tableBody) {
//     console.error("❌ Table body tidak ditemukan!");
//     return;
//   }

//   tableBody.innerHTML = ""; // 🔄 Reset tabel sebelum update

//   if (!Array.isArray(data) || data.length === 0) {
//     console.warn("⚠️ Data tabel kosong atau bukan array!", data);
//     var newRow = tableBody.insertRow();
//     var cell = newRow.insertCell(0);
//     cell.colSpan = 4;
//     cell.textContent = "Tidak ada data tersedia";
//     cell.style.textAlign = "center";
//     return;
//   }

//   data.forEach((row, index) => {
//     var newRow = tableBody.insertRow();
//     newRow.className = "border border-gray-300 hover:bg-gray-100"; // Tailwind untuk tampilan lebih rapi

//     let itemNumber = (currentPageKode2 - 1) * rowsPerPage + index + 1; // ✅ Menyesuaikan nomor urut berdasarkan halaman

//     newRow.insertCell(0).textContent = itemNumber; // 🔹 Nomor urut berlanjut
//     newRow.insertCell(1).textContent = row.promo;
//     newRow.insertCell(2).textContent = parseInt(row.Qty) || 0;
//     newRow.insertCell(3).textContent = new Intl.NumberFormat("id-ID", {
//       style: "decimal",
//     }).format(row.Total);
//   });

//   console.log("✅ Table updated successfully!");
// }
// function updateTable3(data) {
//   console.log("📋 Updating Table with Data:", data);

//   var tableBody = document.querySelector("#tableKode3 tbody");
//   if (!tableBody) {
//     console.error("❌ Table body tidak ditemukan!");
//     return;
//   }

//   tableBody.innerHTML = ""; // 🔄 Reset tabel sebelum update

//   if (!Array.isArray(data) || data.length === 0) {
//     console.warn("⚠️ Data tabel kosong atau bukan array!", data);
//     var newRow = tableBody.insertRow();
//     var cell = newRow.insertCell(0);
//     cell.colSpan = 4;
//     cell.textContent = "Tidak ada data tersedia";
//     cell.style.textAlign = "center";
//     return;
//   }

//   data.forEach((row, index) => {
//     var newRow = tableBody.insertRow();
//     newRow.className = "border border-gray-300 hover:bg-gray-100"; // Tailwind untuk tampilan lebih rapi

//     let itemNumber = (currentPageKode3 - 1) * rowsPerPage + index + 1; // ✅ Menyesuaikan nomor urut berdasarkan halaman

//     newRow.insertCell(0).textContent = itemNumber; // 🔹 Nomor urut berlanjut
//     newRow.insertCell(1).textContent = row.promo;
//     newRow.insertCell(2).textContent = parseInt(row.Qty) || 0;
//     newRow.insertCell(3).textContent = new Intl.NumberFormat("id-ID", {
//       style: "decimal",
//     }).format(row.Total);
//   });

//   console.log("✅ Table updated successfully!");
// }
// function updateTable4(data) {
//   console.log("📋 Updating Table with Data:", data);

//   var tableBody = document.querySelector("#tableKode4 tbody");
//   if (!tableBody) {
//     console.error("❌ Table body tidak ditemukan!");
//     return;
//   }

//   tableBody.innerHTML = ""; // 🔄 Reset tabel sebelum update

//   if (!Array.isArray(data) || data.length === 0) {
//     console.warn("⚠️ Data tabel kosong atau bukan array!", data);
//     var newRow = tableBody.insertRow();
//     var cell = newRow.insertCell(0);
//     cell.colSpan = 4;
//     cell.textContent = "Tidak ada data tersedia";
//     cell.style.textAlign = "center";
//     return;
//   }

//   data.forEach((row, index) => {
//     var newRow = tableBody.insertRow();
//     newRow.className = "border border-gray-300 hover:bg-gray-100"; // Tailwind untuk tampilan lebih rapi

//     let itemNumber = (currentPageKode4 - 1) * rowsPerPage + index + 1; // ✅ Menyesuaikan nomor urut berdasarkan halaman

//     newRow.insertCell(0).textContent = itemNumber; // 🔹 Nomor urut berlanjut
//     newRow.insertCell(1).textContent = row.promo;
//     newRow.insertCell(2).textContent = parseInt(row.Qty) || 0;
//     newRow.insertCell(3).textContent = new Intl.NumberFormat("id-ID", {
//       style: "decimal",
//     }).format(row.Total);
//   });

//   console.log("✅ Table updated successfully!");
// }
// function updateTable5(data) {
//   console.log("📋 Updating Table with Data:", data);

//   var tableBody = document.querySelector("#tableKode5 tbody");
//   if (!tableBody) {
//     console.error("❌ Table body tidak ditemukan!");
//     return;
//   }

//   tableBody.innerHTML = ""; // 🔄 Reset tabel sebelum update

//   if (!Array.isArray(data) || data.length === 0) {
//     console.warn("⚠️ Data tabel kosong atau bukan array!", data);
//     var newRow = tableBody.insertRow();
//     var cell = newRow.insertCell(0);
//     cell.colSpan = 4;
//     cell.textContent = "Tidak ada data tersedia";
//     cell.style.textAlign = "center";
//     return;
//   }

//   data.forEach((row, index) => {
//     var newRow = tableBody.insertRow();
//     newRow.className = "border border-gray-300 hover:bg-gray-100"; // Tailwind untuk tampilan lebih rapi

//     let itemNumber = (currentPageKode5 - 1) * rowsPerPage + index + 1; // ✅ Menyesuaikan nomor urut berdasarkan halaman

//     newRow.insertCell(0).textContent = itemNumber; // 🔹 Nomor urut berlanjut
//     newRow.insertCell(1).textContent = row.promo;
//     newRow.insertCell(2).textContent = parseInt(row.Qty) || 0;
//     newRow.insertCell(3).textContent = new Intl.NumberFormat("id-ID", {
//       style: "decimal",
//     }).format(row.Total);
//   });

//   console.log("✅ Table updated successfully!");
// }
function updatePagination() {
  console.log("🔄 Updating Pagination...");

  document.getElementById(
    "pageInfo"
  ).textContent = `Page ${currentPage} of ${totalPages}`;

  document.getElementById("prevBtn").disabled = currentPage <= 1;
  document.getElementById("nextBtn").disabled = currentPage >= totalPages;

  console.log(`📌 Current Page: ${currentPage}, Total Pages: ${totalPages}`);
}
// function updatePagination2() {
//   console.log("🔄 Updating Pagination 2...");

//   document.getElementById(
//     "pageInfo2"
//   ).textContent = `Page ${currentPageKode2} of ${totalPages2}`;

//   document.getElementById("prevBtn2").disabled = currentPageKode2 <= 1;
//   document.getElementById("nextBtn2").disabled = currentPageKode2 >= totalPages2;

//   console.log(`📌 Current Page: ${currentPageKode2}, Total Pages: ${totalPages2}`);
// }
// function updatePagination3() {
//   console.log("🔄 Updating Pagination 3...");

//   document.getElementById(
//     "pageInfo3"
//   ).textContent = `Page ${currentPageKode3} of ${totalPages3}`;

//   document.getElementById("prevBtn3").disabled = currentPageKode3 <= 1;
//   document.getElementById("nextBtn3").disabled = currentPageKode3 >= totalPages3;

//   console.log(`📌 Current Page: ${currentPageKode3}, Total Pages: ${totalPages3}`);
// }
// function updatePagination4() {
//   console.log("🔄 Updating Pagination 4...");

//   document.getElementById(
//     "pageInfo4"
//   ).textContent = `Page ${currentPageKode4} of ${totalPages4}`;

//   document.getElementById("prevBtn4").disabled = currentPageKode4 <= 1;
//   document.getElementById("nextBtn4").disabled = currentPageKode4 >= totalPages4;

//   console.log(`📌 Current Page: ${currentPageKode4}, Total Pages: ${totalPages4}`);
// }
// function updatePagination5() {
//   console.log("🔄 Updating Pagination 5...");

//   document.getElementById(
//     "pageInfo5"
//   ).textContent = `Page ${currentPageKode5} of ${totalPages5}`;

//   document.getElementById("prevBtn5").disabled = currentPageKode5 <= 1;
//   document.getElementById("nextBtn5").disabled = currentPageKode5 >= totalPages5;

//   console.log(`📌 Current Page: ${currentPageKode5}, Total Pages: ${totalPages5}`);
// }

// Prev Dan Next Tabel 1
document.getElementById("nextBtn").addEventListener("click", function (e) {
  e.preventDefault()
  if (currentPage < totalPages) {
    currentPage++;
    sendKodeSupp1(currentPage);
  }
});

document.getElementById("prevBtn").addEventListener("click", function (e) {
  e.preventDefault()
  if (currentPage > 1) {
    currentPage--;
    sendKodeSupp1(currentPage);
  }
});

function exportToExcel() {
  const limit = 1000;
  let formDataExport = new FormData();
  formDataExport.append("ajax", true);
  formDataExport.append("csrf_token", document.querySelector("[name='csrf_token']").value);
  formDataExport.append("kd_store", document.querySelector("#kd_store")?.value);
  formDataExport.append("start_date", document.querySelector("#date")?.value);
  formDataExport.append("selectKode", document.querySelector("#supplierDropdown")?.value);
  formDataExport.append("end_date", document.querySelector("#date1")?.value);
  formDataExport.append("limit", limit);
  formDataExport.append('export_all', true);

  const cabangText =
    document.getElementById("cabang").options[
      document.getElementById("cabang").selectedIndex
    ].text;
  const selectedKode = document.querySelector("#supplierDropdown")?.value;

  fetch("in_sales_ratio_proses_table.php?ajax=1", {
    method: "POST",
    body: formDataExport
  })
    .then(response => response.json())
    .then(data => {
      console.log("response data: ", data)
      let workbook = new ExcelJS.Workbook();
      let worksheet = workbook.addWorksheet("Data Penjualan");

      // **Buat Header**
      let headers = ["No", "SUB DEPT", "QTY", "TOTAL"];
      let headerRow = worksheet.addRow(headers);

      // **Styling Header**
      headerRow.eachCell((cell, colNumber) => {
        cell.font = { bold: true, color: { argb: "FFFFFFFF" }, size: 12 };
        cell.alignment = { horizontal: "center", vertical: "middle" };
        cell.fill = { type: "pattern", pattern: "solid", fgColor: { argb: "0070C0" } };
        cell.border = {
          top: { style: "thin" },
          bottom: { style: "thin" },
          left: { style: "thin" },
          right: { style: "thin" }
        };
      });

      // **Tambahkan Data**
      data.tableData.forEach((item, index) => {
        let row = worksheet.addRow([index + 1, item.promo, item.Qty, item.Total]);
        row.getCell(1).alignment = { horizontal: "center" }; // No → Tengah
        row.getCell(2).alignment = { horizontal: "left" };   // SUB DEPT → Kiri
        row.getCell(3).alignment = { horizontal: "center" }; // QTY → Tengah
        row.getCell(4).alignment = { horizontal: "left" };   // TOTAL → Kiri

        row.getCell(4).numFmt = '"Rp" #,##0'
        row.eachCell((cell) => {
          cell.border = {
            top: { style: "thin" },
            bottom: { style: "thin" },
            left: { style: "thin" },
            right: { style: "thin" }
          };
        });
      });

      // **Atur Lebar Kolom**
      worksheet.columns = [
        { width: 5 },   // No
        { width: 40 },  // SUB DEPT
        { width: 10 },  // QTY
        { width: 15 }   // TOTAL
      ];

      // **Simpan File**
      workbook.xlsx.writeBuffer().then(buffer => {
        let blob = new Blob([buffer], { type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" });
        let link = document.createElement("a");
        link.href = URL.createObjectURL(blob);
        link.download = `Data_Penjualan_Cabang_${cabangText}_Supplier${selectedKode}.xlsx`;
        link.click();
      });
    })
    .catch(error => console.error("Error fetching data:", error));
}

function exportToPDF() {
  const limit = 1000;
  let formDataExport = new FormData();
  formDataExport.append("ajax", true);
  formDataExport.append("csrf_token", document.querySelector("[name='csrf_token']").value);
  formDataExport.append("kd_store", document.querySelector("#kd_store")?.value);
  formDataExport.append("start_date", document.querySelector("#date")?.value);
  formDataExport.append("selectKode", document.querySelector("#supplierDropdown")?.value);
  formDataExport.append("end_date", document.querySelector("#date1")?.value);
  formDataExport.append("limit", limit);
  formDataExport.append("export_all", true);


  const { jsPDF } = window.jspdf;
  let doc = new jsPDF();
  const cabangText =
    document.getElementById("cabang").options[
      document.getElementById("cabang").selectedIndex
    ].text;
  const selectedKode = document.querySelector("#supplierDropdown")?.value;
  fetch("in_sales_ratio_proses_table.php?ajax=1", {
    method: "POST",
    body: formDataExport
  })
    .then(response => response.json())
    .then(data => {
      console.log("data export pdf: ", data)
      if (!data.tableData || data.tableData.length === 0) {
        console.error("Tidak ada data untuk diekspor.");
        return;
      }

      let headers = ["No", "SUB DEPT", "QTY", "TOTAL"];
      let rows = data.tableData.map((item, index) => [
        index + 1, item.promo, item.Qty, `Rp. ${new Intl.NumberFormat('id-ID').format(item.Total)}`
      ]);

      doc.autoTable({
        head: [headers],
        body: rows,
        startY: 50,
        theme: 'grid',
        styles: { fontSize: 8, cellPadding: 3 }, // **Default Style**
        headStyles: {
          fillColor: [0, 112, 192],
          textColor: [255, 255, 255],
          fontSize: 12,
          fontStyle: "bold",
          halign: "center" // Header selalu di tengah
        },
        bodyStyles: {
          textColor: [10, 10, 10],
        },
        columnStyles: {
          0: { halign: "center" }, // "No" rata tengah
          1: { halign: "left" },   // "SUB DEPT" rata kiri
          2: { halign: "center" }, // "QTY" rata tengah
          3: { halign: "left" }    // "TOTAL" rata kiri
        },
        margin: { top: 50 },
        didDrawPage: function (data) {
          const logo = "/images/logo.png"; // Ganti dengan base64 logo
          doc.addImage(logo, "PNG", 15, 10, 25, 10); // (x, y, width, height)

          doc.setFont("Times New Roman", "bold");
          doc.setFontSize(14);
          doc.text("PT. Asoka Indonesia", 45, 15);

          doc.setFontSize(12);
          doc.setFont("Times New Roman", "normal");
          doc.text("Lb 5, Jl. Utan Jati Blok Lb 5 No.9, RT.10/RW.12, Kalideres, West Jakarta City, Jakarta 11840", 45, 20);
          doc.text("Telp: 0819-4943-1969 | Email: info@contoh.com", 45, 25);

          // **Buat Garis Pemisah**
          doc.setLineWidth(0.5);
          doc.line(10, 30, 200, 30);
          // **Tambahkan Judul**
          doc.setFont("Arial Black", "bold");
          doc.setFontSize(12);
          doc.text(`Laporan Data Penjualan Sales Cabang ${cabangText} Supplier ${selectedKode}`, 15, 45);
        }
      });
      doc.save(`Data_Penjualan_Cabang_${cabangText}_Supplier_${selectedKode}.pdf`);
    })
    .catch(error => console.error("Error fetching data:", error));
}

// // Prev Dan Next Tabel 2
// document.getElementById("nextBtn2").addEventListener("click", function (e) {
//   e.preventDefault()
//   if (currentPageKode2 < totalPages2) {
//     currentPageKode2++;
//     sendKodeSupp2(currentPageKode2);
//   }
// });
// document.getElementById("prevBtn2").addEventListener("click", function (e) {
//   e.preventDefault()
//   if (currentPageKode2 > 1) {
//     currentPageKode2--;
//     sendKodeSupp2(currentPageKode2);
//   }
// });
// // Prev Dan Next Tabel 3
// document.getElementById("nextBtn3").addEventListener("click", function (e) {
//   e.preventDefault()
//   if (currentPageKode3 < totalPages3) {
//     currentPageKode3++;
//     sendKodeSupp3(currentPageKode3);

//   }
// });
// document.getElementById("prevBtn3").addEventListener("click", function (e) {
//   e.preventDefault()
//   if (currentPageKode3 > 1) {
//     currentPageKode3--;
//     sendKodeSupp3(currentPageKode3);
//   }
// });

// // Prev Dan Next Tabel 4
// document.getElementById("nextBtn4").addEventListener("click", function (e) {
//   e.preventDefault()
//   if (currentPageKode4 < totalPages4) {
//     currentPageKode4++;
//     sendKodeSupp4(currentPageKode4);

//   }
// });
// document.getElementById("prevBtn4").addEventListener("click", function (e) {
//   e.preventDefault()
//   if (currentPageKode4 > 1) {
//     currentPageKode4--;
//     sendKodeSupp4(currentPageKode4);
//   }
// });

// // Prev Dan Next Tabel 5
// document.getElementById("nextBtn5").addEventListener("click", function (e) {
//   e.preventDefault()
//   if (currentPageKode5 < totalPages5) {
//     currentPageKode5++;
//     sendKodeSupp5(currentPageKode5);

//   }
// });
// document.getElementById("prevBtn5").addEventListener("click", function (e) {
//   e.preventDefault()
//   if (currentPageKode5 > 1) {
//     currentPageKode5--;
//     sendKodeSupp5(currentPageKode5);
//   }
// });





>>>>>>> master
