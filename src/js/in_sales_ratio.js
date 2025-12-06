$(".supplier-input").hide();
let totalPages = 0;
let currentPage = 1;
let tableDataOriginal = [];
document.getElementById("lihatTable").style.display = "none";
document.getElementById("sendTable").style.display = "none";
// document.getElementById("hideTable").style.display = "none";
document.getElementById("bar").style.display = "none";

document
  .getElementById("toggle-sidebar")
  .addEventListener("click", function () {
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
    }, 250);
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
    }, 250);
  }
});

document.addEventListener("DOMContentLoaded", function () {
  console.log("TEST");
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

let storeCode = "";

$(document).ready(async function () {
  const $select = $("#cabang");
  const $kdStore = $("#kd_store");

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

      nameToCode = {};
      allCodes = [];
      $select.empty();

      // Opsi default
      $select.append('<option value="ALL">Semua Cabang</option>');

      rows.forEach(({ nama_cabang, store }) => {
        const code = String(store).trim();
        const name = String(nama_cabang).trim();
        nameToCode[name] = code;
        allCodes.push(code);
        $select.append(`<option value="${name}">${name}</option>`);
      });
    } catch (err) {
      console.error("Gagal memuat cabang:", err);
    }
  }

  // hitung kode dari pilihan lalu set ke hidden + variabel
  function applyStoreCodeFromSelection(val) {
    const code =
      val === "ALL"
        ? allCodes.join(",") // semua kode cabang
        : val
        ? nameToCode[val] || ""
        : "";

    $kdStore.val(code); // setter ke input hidden
    storeCode = code; // simpan ke variabel string
  }

  $select.on("change", function () {
    applyStoreCodeFromSelection(this.value);
  });

  await loadCabang();

  // Set default ke ALL (optional) lalu trigger
  if ($select.find('option[value="ALL"]').length) {
    $select.val("ALL");
  }
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
// POST Data Dari Form
document.getElementById("laporanForm").addEventListener("submit", function (e) {
  e.preventDefault();
  const filter = $("#sort-by").val();
  sendDataToBar(filter);
  var barChart = echarts.init(document.getElementById("barDiagram"));
  setTimeout(() => {
    barChart.resize();
  }, 300);
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
$(document).on("click", ".supplier-input", function () {
  let inputField = $(this);
  if (!inputField.data("loaded")) {
    $.ajax({
      url: `/src/api/get_kode_supp?kode=${storeCode}`,
      type: "GET",
      success: function (response) {
        // Ubah format jadi array hanya berisi kode_supp
        let dataSuplier = response.map((item) => item.kode_supp.trim());

        inputField.data("loaded", true);
        inputField.data("suppliers", dataSuplier);

        showSuggestions(inputField, dataSuplier);
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

  let filteredSuppliers = allSuppliers.filter((supplier) =>
    supplier.toLowerCase().includes(keyword)
  );

  showSuggestions(inputField, filteredSuppliers);
});

// Memunculkan Data Pada Input Untuk Di Pilih
function showSuggestions(inputField, suppliers) {
  $(".suggestion-box").remove(); // Hapus dropdown lama

  let suggestionBox = $("<div>").addClass(
    "absolute bg-white border border-gray-300 w-full max-h-40 overflow-y-auto z-50 shadow-md rounded-md suggestion-box"
  );

  if (!Array.isArray(suppliers) || suppliers.length === 0) {
    $("<div>")
      .addClass("px-4 py-2 text-gray-500 text-center italic")
      .text("Supplier tidak ditemukan")
      .appendTo(suggestionBox);
  } else {
    suppliers.forEach((kode_supp) => {
      $("<div>")
        .addClass(
          "px-4 py-2 cursor-pointer border-b border-gray-200 text-gray-700 hover:bg-gray-100"
        )
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
    boxShadow: "0px 4px 6px rgba(0, 0, 0, 0.1)",
  });

  $("body").append(suggestionBox);
}

$(document).on("click", function (e) {
  if (!$(e.target).closest(".supplier-input, .suggestion-box").length) {
    $(".suggestion-box").remove(); // Tutup dropdown jika klik di luar
  }
});

//  Saat klik tombol "Cek Data", simpan input ke dalam select
$("#btn-submit").on("click", function () {
  $("#supplierDropdown").empty();
  updateSupplierDropdown();
});

//  Fungsi untuk update dropdown supplier
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
  return (
    $("#supplierDropdown option").filter(function () {
      return $(this).val() === value;
    }).length > 0
  );
}

document.getElementById("sendTable").addEventListener("click", function (e) {
  e.preventDefault();
  const filter = $("#sort-by").val();
  currentPage = 1;
  sendKodeSupp1(currentPage, filter);
});

function sendKodeSupp1(page, filter) {
  const Toast = Swal.mixin({
    toast: true,
    position: "top-end",
    showConfirmButton: false,
    timerProgressBar: true,
    didOpen: (toast) => {
      toast.onmouseenter = Swal.stopTimer;
      toast.onmouseleave = Swal.resumeTimer;
    },
  });
  // document.getElementById("hideTable").style.display = "block";
  var barChart = echarts.init(document.getElementById("barDiagram"));
  setTimeout(() => {
    barChart.resize();
  }, 300);
  var cabangText =
    document.getElementById("cabang").options[
      document.getElementById("cabang").selectedIndex
    ].text;
  var startDate = $("#date").val(); // Ambil nilai langsung dari datepicker
  var endDate = $("#date1").val();
  var reportHeader = document.getElementById("reportHeader1");
  var kodeSupp = $("#supplierDropdown").val();
  reportHeader.innerHTML = `Data Penjualan<br><p> Cabang :  ${cabangText}, Supplier: ${kodeSupp} (${startDate} s/d ${endDate})</p>`;
  let formDataToTable = new FormData();
  formDataToTable.append("ajax", true);
  formDataToTable.append(
    "csrf_token",
    document.querySelector("[name='csrf_token']").value
  );
  formDataToTable.append(
    "selectKode",
    document.querySelector("#supplierDropdown")?.value
  );
  formDataToTable.append("kd_store", storeCode);
  formDataToTable.append("start_date", document.querySelector("#date")?.value);
  formDataToTable.append("end_date", document.querySelector("#date1")?.value);
  formDataToTable.append("page", page);
  formDataToTable.append("filter", filter);
  // for (var pair of formDataToTable.entries()) {
  // }
  $.ajax({
    url: `/src/api/ratio/in_sales_ratio_proses_table?filter=${filter}`,
    method: "POST",
    dataType: "json",
    processData: false,
    contentType: false,
    data: formDataToTable,
    success: (response) => {
      if (response.tableData && response.tableData.length > 0) {
        totalPages = response.totalPages ? response.totalPages : 1;
        localStorage.setItem(
          "dataTemporary",
          JSON.stringify(response.tableData)
        );
        updateTable1(response.tableData, "tableKode1");
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
    },
  });
}
function sendDataToBar(filter) {
  var selectRatio = document.querySelector("#ratio_number").value;

  // Validasi client-side sudah benar
  if (selectRatio === "none") {
    Swal.fire({
      icon: "warning",
      title: "Error",
      text: "Ratio Harus Di Pilih",
    });
    document.getElementById("barDiagram").style.display = "none";
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
    document.getElementById("barDiagram").style.display = "block";
  }

  let formData = new FormData();
  var barChart = echarts.init(document.getElementById("barDiagram"));
  setTimeout(() => {
    barChart.resize();
  }, 300);
  resetBarChart();

  formData.append("ajax", true);
  formData.append(
    "csrf_token",
    document.querySelector("[name='csrf_token']").value
  );

  // --- TAMBAHKAN BARIS INI ---
  formData.append("ratio", selectRatio);
  // ---------------------------

  formData.append("kode_supp1", document.querySelector("#kode_supp1")?.value);
  formData.append("kode_supp2", document.querySelector("#kode_supp2")?.value);
  formData.append("kode_supp3", document.querySelector("#kode_supp3")?.value);
  formData.append("kode_supp4", document.querySelector("#kode_supp4")?.value);
  formData.append("kode_supp5", document.querySelector("#kode_supp5")?.value);
  formData.append("kd_store", storeCode);
  formData.append("start_date", document.querySelector("#date")?.value);
  formData.append("end_date", document.querySelector("#date1")?.value);
  formData.append("filter", filter);

  $.ajax({
    url: `/src/api/ratio/in_sales_ratio_proses_bar?filter=${filter}`,
    method: "POST",
    dataType: "json",
    processData: false,
    contentType: false,
    data: formData,
    success: (response) => {
      localStorage.setItem("ratioChart", JSON.stringify(response.tableData));
      const sortBy = $("#sort-by").val();
      updateBarChart(response.tableData, sortBy);
      Swal.close();
    },
    error: (xhr, status, error) => {
      Swal.fire({
        icon: "error",
        title: "Error",
        text: "Gagal Menampilkan Data!",
      });
      Swal.close();
    },
  });
}
function shadeColor(color, percent) {
  let f = parseInt(color.slice(1), 16),
    t = percent < 0 ? 0 : 255,
    p = percent < 0 ? percent * -1 : percent,
    R = f >> 16,
    G = (f >> 8) & 0x00ff,
    B = f & 0x0000ff;
  return (
    "#" +
    (
      0x1000000 +
      (Math.round((t - R) * p) + R) * 0x10000 +
      (Math.round((t - G) * p) + G) * 0x100 +
      (Math.round((t - B) * p) + B)
    )
      .toString(16)
      .slice(1)
  );
}
// Update Tampilan Bar Chart Sesuai Data
function updateBarChart(table, sortBy) {
  if (!table) return;

  // Sort berdasarkan Qty atau Total
  let sortedTable = [...table];
  if (sortBy === "Qty") {
    sortedTable.sort((a, b) => b.Qty - a.Qty);
  } else {
    sortedTable.sort((a, b) => {
      const numA = a.Total || 0;
      const numB = b.Total || 0;
      return numB - numA;
    });
  }

  // Ambil label dan data hasil sort
  const labels = sortedTable.map((item) => item.kode_supp);
  const data = sortedTable.map((item) =>
    sortBy === "Qty" ? item.Qty : item.Total || 0
  );

  // Persiapkan data untuk chart
  const newData = sortedTable.map((item, index) => ({
    kode_supp: labels[index],
    Qty: Number(data[index]),
    Total: Number(item.Total || 0),
    tanggal: String(item.periode),
    percent: item.persentase_rp,
    precentage: item.Percentage,
  }));

  // Sort berdasarkan tanggal
  newData.sort((a, b) => {
    let [dayA, monthA] = a.tanggal.split("-").map(Number);
    let [dayB, monthB] = b.tanggal.split("-").map(Number);
    return monthA !== monthB ? monthA - monthB : dayA - dayB;
  });

  const tanggal = [...new Set(newData.map((item) => item.tanggal))];
  const kods = [...new Set(newData.map((item) => item.kode_supp))];

  const colorPalette = [
    "#ff5733",
    "#33ff57",
    "#3357ff",
    "#f4a261",
    "#e63946",
    "#6a0572",
    "#0e9594",
    "#f77f00",
    "#2a9d8f",
    "#6c757d",
  ];
  const colorMap = {};
  kods.forEach(
    (kd, i) => (colorMap[kd] = colorPalette[i % colorPalette.length])
  );

  const seriesData = kods.map((kd, index) => ({
    name: kd,
    type: "bar",
    data: tanggal.map((tgl) => {
      const found = newData.find(
        (d) => d.tanggal === tgl && d.kode_supp === kd
      );
      return found
        ? {
            value: sortBy === "Qty" ? found.Qty : found.Total,
            persen: sortBy === "Qty" ? found.precentage : found.percent,
          }
        : 0;
    }),
    itemStyle: {
      color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [
        {
          offset: 0,
          color: shadeColor(colorPalette[index % colorPalette.length], 0.2),
        }, // Top (lighter)
        { offset: 1, color: colorPalette[index % colorPalette.length] }, // Bottom (base color)
      ]),
    },
    label: {
      show: false,
      rotate: 74,
      align: "left",
      verticalAlign: "bottom",
      position: "insideBottom",
      color: "#f2eded",
      fontSize: 14,
      formatter: function (params) {
        const persen = params.data.persen; // Access persen value
        return `${persen}%`; // Display persen as a percentage
      },
    },
  }));

  const barChart = echarts.init(document.getElementById("barDiagram"));
  const optionBarCharts = {
    grid: {
      left: "3%",
      right: "9%",
      bottom: "5%",
      containLabel: true,
    },
    tooltip: {
      trigger: "axis",
      axisPointer: { type: "shadow" },
      formatter: function (params) {
        const dateLabel = params[0].axisValue;
        const details = params
          .filter((p) => p.value > 0)
          .map((p) => {
            const value = Number(p.value).toLocaleString("id-ID");
            const persen = p.data.persen;
            return `● ${p.seriesName}: <b>${value}</b> (${persen})`;
          })
          .join("<br>");
        return `<b>${dateLabel}</b><br>${details}`;
      },
    },
    legend: { data: kods },
    toolbox: {
      show: true,
      feature: {
        dataView: { show: true, readOnly: false },
        magicType: { show: true, type: ["line", "bar", "stack"] },
        restore: { show: false },
        saveAsImage: { show: true },
      },
    },
    xAxis: {
      type: "category",
      data: tanggal,
      name: "Periode",
      axisLabel: {
        rotate: 45,
        interval: 0,
      },
    },
    yAxis: {
      type: "value",
      name: sortBy === "Qty" ? "Qty" : "Rp",
      axisLabel: {
        formatter:
          sortBy === "Qty"
            ? "{value}"
            : function (value) {
                return value.toLocaleString();
              },
      },
    },
    series: seriesData,
    barCategoryGap: "10%",
  };

  barChart.setOption(optionBarCharts, true);
  window.addEventListener("resize", () => barChart.resize());
}

document.getElementById("sort-by").addEventListener("change", function () {
  const sortBy = this.value;
  const tableData = JSON.parse(localStorage.getItem("ratioChart"));
  if (!tableData) return;
  updateBarChart(tableData, sortBy);
});
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

function updateTable1(data, tableID) {
  if (tableDataOriginal.length === 0) {
    tableDataOriginal = [...data]; // Simpan salinan data asli
  }
  var tableBody = document.querySelector(`#${tableID} tbody`);

  tableBody.innerHTML = ""; // 🔄 Reset tabel sebelum update

  if (!Array.isArray(data) || data.length === 0) {
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

    cell0.textContent = index + 1; // 🔹 Nomor urut berlanjut
    cell1.textContent = row.promo;
    cell2.textContent = parseInt(row.Qty) || 0;
    cell3.textContent = new Intl.NumberFormat("id-ID", {
      style: "decimal",
    }).format(row.Total);
    cell0.classList.add("text-center");
    cell2.classList.add("text-center");
  });
}

async function exportToExcel() {
  const table = document.getElementById("tableKode1");
  const cabangEl = document.getElementById("cabang");
  const cabangText = cabangEl
    ? cabangEl.options[cabangEl.selectedIndex]?.text || "-"
    : "-";

  // --- Supplier: ambil TEXT dari option yang terseleksi ---
  const supplierEl = document.getElementById("supplierDropdown");
  const supplierText = supplierEl
    ? supplierEl.options?.[supplierEl.selectedIndex]?.text ??
      String(supplierEl.value ?? "-")
    : "-";
  const supplierValue = supplierEl ? supplierEl.value ?? "-" : "-"; // kalau butuh juga valuenya

  if (!table) {
    alert("Tabel tidak ditemukan.");
    return;
  }
  const tbody = table.querySelector("tbody");
  if (!tbody) {
    alert("Tidak ada data dalam tabel untuk diekspor!");
    return;
  }

  const toNumber = (val) => {
    if (val == null) return 0;
    const raw = String(val)
      .replace(/[^\d,.-]/g, "")
      .replace(/\./g, "")
      .replace(",", ".");
    const n = Number(raw);
    return Number.isFinite(n) ? n : 0;
  };

  // Ambil data tabel
  const rows = [];
  let no = 1;
  tbody.querySelectorAll("tr").forEach((tr) => {
    const tds = tr.querySelectorAll("td");
    if (tds.length < 4) return;
    const nama = tds[1].textContent.trim();
    const qty = toNumber(tds[2].textContent);
    const total = toNumber(tds[3].textContent);
    if (!nama && qty === 0 && total === 0) return;
    rows.push([no++, nama, qty, total]);
  });
  if (rows.length === 0) {
    alert("Tidak ada baris data valid untuk diekspor.");
    return;
  }

  // Workbook
  const wb = new ExcelJS.Workbook();
  wb.creator = "Asoka Baby Store";
  wb.lastModifiedBy = "Asoka Baby Store";
  wb.created = new Date();
  wb.modified = new Date();

  const ws = wb.addWorksheet("Data Penjualan", {
    properties: { tabColor: { argb: "FF1D4ED8" } },
    pageSetup: {
      paperSize: 9,
      orientation: "landscape",
      fitToPage: true,
      fitToWidth: 1,
      fitToHeight: 0,
      margins: {
        left: 0.5,
        right: 0.5,
        top: 0.5,
        bottom: 0.5,
        header: 0.3,
        footer: 0.3,
      },
    },
    headerFooter: {
      oddHeader: `&LAsoka Baby Store&CRekap Penjualan&R&F`,
      // --- ganti Subdept -> Supplier ---
      oddFooter: `&L${new Date().toLocaleString()}&C&P / &N&R${cabangText} | Supplier ${supplierText}`,
    },
  });

  // Title & meta
  ws.mergeCells("A1:D1");
  ws.mergeCells("A2:D2");
  ws.getCell("A1").value = "ASOKA BABY STORE";
  ws.getCell("A1").font = { bold: true, size: 16 };
  ws.getCell("A1").alignment = { horizontal: "center" };
  ws.getCell("A2").value = "REKAP PENJUALAN";
  ws.getCell("A2").font = { bold: true, size: 13 };
  ws.getCell("A2").alignment = { horizontal: "center" };
  ws.getCell("A3").value = `Cabang: ${cabangText}`;
  // --- ganti baris informasi Supplier ---
  ws.getCell("A4").value = `Supplier: ${supplierText}${
    supplierValue && supplierValue !== supplierText
      ? " (" + supplierValue + ")"
      : ""
  }`;
  ws.getCell("C3").value = `Tanggal Export: ${new Date().toLocaleString()}`;

  const tableStartRow = 6;
  ws.addTable({
    name: "TabelPenjualan",
    ref: `A${tableStartRow}`,
    headerRow: true,
    totalsRow: true,
    style: { theme: "TableStyleMedium9", showRowStripes: true },
    columns: [
      { name: "No", filterButton: true },
      { name: "NAMA BARANG", totalsRowLabel: "TOTAL" },
      { name: "QTY", totalsRowFunction: "sum", filterButton: true },
      { name: "TOTAL", totalsRowFunction: "sum", filterButton: true },
    ],
    rows,
  });

  ws.columns = [
    { key: "no", width: 6 },
    { key: "nama", width: 44 },
    { key: "qty", width: 12 },
    { key: "total", width: 18 },
  ];

  ["A3", "A4", "C3"].forEach((addr) => {
    const cell = ws.getCell(addr);
    cell.font = { size: 10, color: { argb: "FF111827" } };
  });

  ws.views = [{ state: "frozen", xSplit: 0, ySplit: tableStartRow }];

  const lastDataRow = tableStartRow + rows.length;
  const lastRowWithTotals = lastDataRow + 1;

  for (let r = tableStartRow; r <= lastRowWithTotals; r++) {
    for (let c = 1; c <= 4; c++) {
      const cell = ws.getCell(r, c);
      if (c === 1)
        cell.alignment = { horizontal: "center", vertical: "middle" };
      if (c === 2) cell.alignment = { horizontal: "left", vertical: "middle" };
      if (c === 3)
        cell.alignment = { horizontal: "center", vertical: "middle" };
      if (c === 4) cell.alignment = { horizontal: "right", vertical: "middle" };
      cell.border = {
        top: { style: "thin", color: { argb: "FFCBD5E1" } },
        bottom: { style: "thin", color: { argb: "FFCBD5E1" } },
        left: { style: "thin", color: { argb: "FFCBD5E1" } },
        right: { style: "thin", color: { argb: "FFCBD5E1" } },
      };
    }
  }

  for (let r = tableStartRow + 1; r <= lastDataRow; r++) {
    ws.getCell(r, 3).numFmt = "#,##0";
    ws.getCell(r, 4).numFmt = '"Rp" #,##0';
  }
  ws.getCell(lastRowWithTotals, 3).numFmt = "#,##0";
  ws.getCell(lastRowWithTotals, 4).numFmt = '"Rp" #,##0';

  for (let c = 1; c <= 4; c++) {
    const headerCell = ws.getCell(tableStartRow, c);
    headerCell.font = { bold: true, color: { argb: "FFFFFFFF" } };
    headerCell.fill = {
      type: "pattern",
      pattern: "solid",
      fgColor: { argb: "FF1D4ED8" },
    };
    headerCell.alignment = { horizontal: "center", vertical: "middle" };
  }
  ws.getRow(lastRowWithTotals).font = { bold: true };

  const buffer = await wb.xlsx.writeBuffer();
  const blob = new Blob([buffer], {
    type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
  });

  // --- nama file pakai Supplier (text) ---
  const fileName = `Rekap_Penjualan_${cabangText}_Supplier_${supplierText}_${new Date()
    .toISOString()
    .slice(0, 10)}.xlsx`;

  const link = document.createElement("a");
  link.href = URL.createObjectURL(blob);
  link.download = fileName;
  link.click();
  URL.revokeObjectURL(link.href);
}

async function exportToPDF() {
  const { jsPDF } = window.jspdf;
  const doc = new jsPDF("p", "mm", "a4");

  // --- Ambil cabang & supplier (pakai TEXT yang terlihat user) ---
  const cabangEl = document.getElementById("cabang");
  const cabangText = cabangEl
    ? cabangEl.options[cabangEl.selectedIndex]?.text || "-"
    : "-";

  const supplierEl = document.getElementById("supplierDropdown");
  const supplierText = supplierEl
    ? supplierEl.options?.[supplierEl.selectedIndex]?.text ??
      String(supplierEl.value ?? "-")
    : "-";

  // --- Ambil data tabel ---
  const table = document.getElementById("tableKode1");
  if (!table) {
    alert("Tabel tidak ditemukan.");
    return;
  }

  const toNumber = (s) => {
    if (!s) return 0;
    // buang semua selain digit, tanda minus, titik/koma
    const raw = String(s)
      .replace(/[^\d,.-]/g, "")
      .replace(/\./g, "")
      .replace(",", ".");
    const n = Number(raw);
    return Number.isFinite(n) ? n : 0;
  };
  const rp = (n) => "Rp " + Math.round(n).toLocaleString("id-ID");

  const body = [];
  let grandTotal = 0;
  const rows = table.querySelectorAll("tbody tr");
  if (!rows.length) {
    alert("Tidak ada data dalam tabel untuk diekspor!");
    return;
  }

  rows.forEach((tr, i) => {
    const tds = tr.querySelectorAll("td");
    if (tds.length < 4) return;
    const nama = (tds[1].textContent || "").trim();
    const qty = (tds[2].textContent || "").trim();
    const totalStr = (tds[3].textContent || "").trim();
    const totalNum = toNumber(totalStr);
    grandTotal += totalNum;

    body.push([
      i + 1,
      nama,
      qty,
      rp(totalNum), // pastikan format konsisten
    ]);
  });
  if (!body.length) {
    alert("Tidak ada baris data valid untuk diekspor.");
    return;
  }

  // --- Header/Footer per halaman ---
  const totalPagesExp = "{total_pages_count_string}";
  const marginLeft = 15,
    marginRight = 12,
    startY = 62;
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
  const logoDataUrl = await urlToDataURL("/images/logo.png");

  doc.autoTable({
    head: [["No", "NAMA BARANG", "QTY", "TOTAL"]],
    body,
    startY,
    theme: "grid",
    styles: {
      font: "helvetica",
      fontSize: 9,
      cellPadding: { top: 2, right: 3, bottom: 2, left: 3 },
      lineColor: [220, 223, 230],
      lineWidth: 0.1,
      overflow: "linebreak",
      minCellHeight: 6,
      valign: "middle",
    },
    headStyles: {
      fillColor: [29, 78, 216], // #1D4ED8
      textColor: [255, 255, 255],
      fontStyle: "bold",
      halign: "center",
    },
    alternateRowStyles: { fillColor: [245, 247, 250] },
    bodyStyles: { textColor: [10, 10, 10] },
    columnStyles: {
      0: { cellWidth: 10, halign: "center" }, // No
      1: { cellWidth: 110, halign: "left" }, // Nama
      2: { cellWidth: 20, halign: "center" }, // QTY
      3: { cellWidth: 46, halign: "right" }, // TOTAL
    },
    margin: { top: 55, left: marginLeft, right: marginRight, bottom: 18 },
    // Foot (grand total)
    foot: [
      [
        {
          content: "TOTAL",
          colSpan: 3,
          styles: { halign: "right", fontStyle: "bold" },
        },
        {
          content: rp(grandTotal),
          styles: { halign: "right", fontStyle: "bold" },
        },
      ],
    ],
    footStyles: {
      fillColor: [229, 231, 235], // abu-abu muda
      textColor: [0, 0, 0],
      lineColor: [220, 223, 230],
      lineWidth: 0.1,
    },
    didDrawPage: (data) => {
      // Header
      doc.addImage(logoDataUrl, "PNG", 13, 10, 25, 10);
      doc.setFont("helvetica", "bold");
      doc.setFontSize(12);
      doc.text("Asoka Baby Store", marginLeft + 26, 15);

      doc.setFont("helvetica", "normal");
      doc.setFontSize(9);
      doc.text(
        "Lb 5, Jl. Utan Jati Blok Lb 5 No.9, Kalideres, Jakarta Barat 11840",
        marginLeft + 26,
        20
      );
      doc.text("Telp: 0819-4943-1969", marginLeft + 26, 25);

      // Garis pemisah header
      doc.setLineWidth(0.5);
      doc.setDrawColor(180, 188, 196);
      doc.line(marginLeft, 30, 210 - marginRight, 30);

      // Judul + meta
      doc.setFont("helvetica", "bold");
      doc.setFontSize(13);
      doc.text("LAPORAN DATA PENJUALAN", marginLeft, 40);

      doc.setFont("helvetica", "normal");
      doc.setFontSize(9);
      doc.text(`Cabang : ${cabangText}`, marginLeft, 48);
      doc.text(`Supplier : ${supplierText}`, marginLeft + 70, 48);
      doc.text(
        `Tanggal : ${new Date().toLocaleString("id-ID")}`,
        210 - marginRight,
        48,
        { align: "right" }
      );

      // Footer (page X / Y)
      const pageStr = `Halaman ${data.pageNumber} / ${totalPagesExp}`;
      doc.setFontSize(8);
      doc.setTextColor(100);
      doc.text(pageStr, 210 - marginRight, 297 - 8, { align: "right" });
    },
  });

  // replace token total pages
  if (typeof doc.putTotalPages === "function") {
    doc.putTotalPages(totalPagesExp);
  }

  // Nama file rapi
  const ymd = new Date().toISOString().slice(0, 10);
  const filename = `Rekap_Penjualan_${cabangText}_Supplier_${supplierText}_${ymd}.pdf`;
  doc.save(filename);
}

function searchTable() {
  let input = document.getElementById("searchInput").value.toLowerCase();
  let dataTemp = JSON.parse(localStorage.getItem("dataTemporary"));
  // Jika input kosong, tampilkan kembali data awal
  if (input.trim() === "") {
    currentPage = 1;
    updateTable1(dataTemp, "tableKode1"); // Kembali ke data awal
    return;
  }
  let filteredData = dataTemp.filter(
    (row) =>
      (row.nama_subdept && row.nama_subdept.toLowerCase().includes(input)) ||
      (row.kode_supp && row.kode_supp.toLowerCase().includes(input)) ||
      (row.promo && row.promo.toLowerCase().includes(input)) ||
      (row.Qty && row.Qty.toString().includes(input)) ||
      (row.Total && row.Total.toString().includes(input))
  );

  if (filteredData.length === 0) {
    document.querySelector(`#tableKode1 tbody`).innerHTML = `
      <tr>
        <td colspan="4" class="py-3 px-6 text-center italic text-gray-500">Data tidak ditemukan</td>
      </tr>
    `;
    return;
  }
  currentPage = 1;
  updateTable1(filteredData, "tableKode1"); // Update tabel dengan hasil pencarian
}
