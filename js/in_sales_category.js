const echartDiagram = echarts.init(document.getElementById("chartDiagram"));

let storeCode = "";
let currentPage = 1;
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
});
document.getElementById("bar").style.display = "none";
document.getElementById("btn-back").style.display = "none";
document.getElementById("chartDiagram").style.display = "none";

// document.getElementById("chartDiagram").style.display = "none"

const btnSend = document.getElementById("btn-send");

btnSend.addEventListener("click", (e) => {
  e.preventDefault();
  console.log("TEST");
  if (storeCode === "") {
    Swal.fire({
      icon: "error",
      title: "Pilih Cabang Dulu",
      text: "Silahkan Pilih Cabang!..",
    });
  } else {
    let startDate = $("#date").val();
    let endDate = $("#date1").val();
    document.getElementById("chartDiagram").style.display = "block";
    awalPemanggilan();
  }
});

function sendDataFromBody(code, tanggalAwal, tanggalAkhir, query, page) {
  let formData = new FormData(document.getElementById("laporanForm"));
  formData.append("kd_store", code);
  formData.append("start_date", tanggalAwal);
  formData.append("end_date", tanggalAkhir);
  formData.append("query", query);
  formData.append("page", page);
  for (var pair of formData.entries()) {
  }
  $.ajax({
    type: "POST",
    url: "post_data_sales_category.php?ajax=1",
    data: formData,
    processData: false, // Tambahkan ini
    contentType: false, // Tambahkan ini
    success: function (response) {
      updateChart(response.labels, response.data, response.tableData);
    },
  });
}

function sendDataSupermarket(code, tanggalAwal, tanggalAkhir, query, page) {
  let formData = new FormData(document.getElementById("laporanForm"));
  formData.append("kd_store", code);
  formData.append("start_date", tanggalAwal);
  formData.append("end_date", tanggalAkhir);
  formData.append("query", query);
  formData.append("page", page);
  for (var pair of formData.entries()) {
  }
  $.ajax({
    type: "POST",
    url: "post_data_sales_category.php?ajax=1",
    data: formData,
    processData: false, // Tambahkan ini
    contentType: false, // Tambahkan ini
    success: function (response) {},
  });
}
function sendDataBabyStore(code, tanggalAwal, tanggalAkhir, query, page) {
  let formData = new FormData(document.getElementById("laporanForm"));
  formData.append("kd_store", code);
  formData.append("start_date", tanggalAwal);
  formData.append("end_date", tanggalAkhir);
  formData.append("query", query);
  formData.append("page", page);
  for (var pair of formData.entries()) {
  }
  $.ajax({
    type: "POST",
    url: "post_data_sales_category.php?ajax=1",
    data: formData,
    processData: false, // Tambahkan ini
    contentType: false, // Tambahkan ini
    success: function (response) {},
  });
}

function sendDataDept(cabang, tanggalAwal, tanggalAkhir, kodeSup, query) {}

function updateChart(labels, data, table) {
  let newData = labels.map((label, index) => ({
    name: label,
    value: data[index],
  }));

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
        formatter: (params) =>
          `${params.name}: ${params.value} (${params.percent}%)`,
      },
      series: [
        {
          type: "pie",
          label: {
            fontSize: 12,
            formatter: "{b}",
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
    { notMerge: false }
  );

  // Pastikan resize setelah update
  setTimeout(() => {
    echartDiagram.resize();
  }, 300);
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

function awalPemanggilan() {
  var echartDiagram = echarts.init(document.getElementById("chartDiagram"));

  // Opsi default
  var optionsChart = {
    tooltip: {
      trigger: "item",
      formatter: (params) =>
        `${params.name}: ${params.value} (${params.percent}%)`,
    },
    series: [
      {
        name: "Total Penjualan",
        type: "pie",
        radius: "70%",
        label: {
          formatter: "{b}", // Hanya tampilkan nama kategori
        },
        data: [
          { value: 30, name: "Departemen Store" },
          { value: 40, name: "Supermarket" },
          { value: 35, name: "Baby Store" },
        ],
        emphasis: {
          itemStyle: {
            shadowBlur: 10,
            shadowOffsetX: 0,
            shadowColor: "rgba(0, 0, 0, 0.5)",
          },
        },
      },
    ],
  };

  echartDiagram.setOption(optionsChart);

  // Event click yang lebih dinamis
  echartDiagram.on("click", (params) => {
    let startDate = $("#date").val();
    let endDate = $("#date1").val();

    if (storeCode === "") {
      Swal.fire({
        icon: "error",
        title: "Oops...",
        text: "Silahkan Pilih Cabang!..",
      });
    } else {
      sendDataFromBody(storeCode, startDate, endDate, "1", currentPage);
    }

    // Tampilkan chart baru atau tombol kembali jika perlu
    document.getElementById("chartDiagram").style.display = "block";
    document.getElementById("btn-back").style.display = "block";
  });

  // Resize chart saat window berubah ukuran
  window.addEventListener("resize", () => {
    echartDiagram.resize();
  });
}

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
