//ATUR AGAR TIDAK MUAT HALAMAN----------------------------------------------------------------START
let currentPage = 1;
let totalPages = 1;
const form = document.getElementById("laporanForm");
document.getElementById("btn-submit").addEventListener('click', ((e)=>{
    e.preventDefault();
    console.log("test");
    currentPage = 1;
    loadPage(currentPage);
}));


function loadPage(page) {
  // Mengambil nilai dari form
  var formData = new FormData(document.getElementById("laporanForm"));
  var startDate = document.getElementById("date").value;
  var endDate = document.getElementById("date1").value;
  var cabangText =
    document.getElementById("cabang").options[
      document.getElementById("cabang").selectedIndex
    ].text;

  // Format tanggal menjadi dd-mm-yyyy
  startDate = formatDate(startDate);
  endDate = formatDate(endDate);

  // Mengatur header laporan
  var reportHeader = document.getElementById("reportHeader");
  reportHeader.innerHTML = `Data Penjualan<br><p> Cabang :  ${cabangText} (${startDate} s/d ${endDate})</p>`;
  // Mengirimkan form dengan AJAX
  formData.append("ajax", "true"); // Pastikan flag ajax ditambahkan
  formData.append("page", page); // Tambahkan nomor halaman ke data form
  for (var pair of formData.entries()) {
    console.log(pair[0] + ": " + pair[1]);
  }
  var xhr = new XMLHttpRequest();
  xhr.open(
    "POST",
    "http://localhost/asoka-id/in_laporan_sub_dept.php?ajax=1",
    true
  ); // Gunakan URL absolut dan query string
  xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest"); // Menambahkan header AJAX
  xhr.onload = function () {
    if (xhr.status === 200) {
      var response = JSON.parse(xhr.responseText);
      console.log(response); // Tambahkan logging di sini untuk memeriksa respons
      if (response.status === "success") {
        var labels = response.labels;
        var chartData = response.data;
        var category = formData.get("category");
        var tableData = response.tableData;
        totalPages = response.totalPages; // Dapatkan total halaman dari respons
        // Update chart with new data
        updateChart(labels, chartData, category);

        // Update table with new data
        updateTable(tableData);

        // Update pagination info
        updatePagination();
      } else if (response.status === "error") {
        alert(response.message);
      }
    } else {
      alert("Terjadi kesalahan saat memuat data.");
    }
  };
  xhr.onerror = function () {
    alert("Terjadi kesalahan jaringan.");
  };
  xhr.send(formData);
}
//ATUR AGAR TIDAK MUAT HALAMAN----------------------------------------------------------------END

// TOMBOL NEXT PAGE
document.getElementById("nextBtn").addEventListener("click", function () {
  if (currentPage < totalPages) {
    currentPage++;
    loadPage(currentPage);
  }
});

// TOMBOL PREV PAGE
document.getElementById("prevBtn").addEventListener("click", function () {
  if (currentPage > 1) {
    currentPage--;
    loadPage(currentPage);
  }
});

// ATUR PAGE NEXT PREV DAN INFO TOTAL PAGE------------------------------------------------S
function updatePagination() {
  document.getElementById(
    "pageInfo"
  ).textContent = `Page ${currentPage} of ${totalPages}`;
  document.getElementById("prevBtn").disabled = currentPage === 1;
  document.getElementById("nextBtn").disabled = currentPage === totalPages;
}
// ATUR PAGE NEXT PREV DAN INFO TOTAL PAGE------------------------------------------------E

//ATUR TANGGAL----------------------------------------------------------------START
// Function to format the date to YYYY-MM-DD
function formatDate(date) {
  var d = new Date(date),
    day = "" + d.getDate(),
    month = "" + (d.getMonth() + 1),
    year = d.getFullYear();

  if (month.length < 2) month = "0" + month;
  if (day.length < 2) day = "0" + day;

  return [day, month, year].join("-");
}

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
function updateTable(data) {
  console.log("📋 Updating Table with Data:", data);
  var tableBody = document.querySelector("#salesTable tbody");
  tableBody.innerHTML = "";

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
    console.log("📋 Updating Table with Data:", row);
    newRow.insertCell(0).textContent = index + 1;
    newRow.insertCell(1).textContent = row.kode_supp ||  row.nama_subdept;
    newRow.insertCell(2).textContent = row.Qty;
    newRow.insertCell(3).textContent = new Intl.NumberFormat("id-ID", {
      style: "decimal",
    }).format(row.Total);
  });
}

// Fungsi untuk memperbarui chart
// Inisialisasi ECharts
var echartDiagram = echarts.init(document.getElementById("chartDiagram"));

// Opsi default
var optionsChart = {
  tooltip: {
    trigger: "item",
  },
  series: [
    {
      name: "Total Penjualan",
      type: "pie",
      radius: "70%",
      data: [{ value: 100, name: "No Data" }],
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

// Set opsi awal ke dalam chart
echartDiagram.setOption(optionsChart);

// Event handler saat pie chart diklik
echartDiagram.on("click", function (params) {
  let subdept = params.value;
  let kd_store = $("#cabang").val();
  let start_date = document.querySelector("#date")?.value;
  let end_date = document.querySelector("#date1")?.value;
  document.querySelector("#subdept").value = subdept;
  if (!start_date || !end_date) {
    console.warn("⚠️ Harap pilih rentang tanggal terlebih dahulu.");
    return;
  }
  $.ajax({
    url: "http://localhost/asoka-id/in_laporan_sub_dept.php?ajax=1",
    method: "POST",
    data: {
      ajax: true,
      kd_store: kd_store,
      start_date: start_date,
      end_date: end_date,
      subdept: subdept,
    },
    success: function (response) {
        console.log("data sub: ",subdept);
        console.log("✅ Response dari server:", response);
    },
    error: function (xhr, status, error) {
      console.error("❌ AJAX Error:", error);
      console.error("❌ Response:", xhr.responseText); // Cek isi response
    },
  });
});

function updateChart(labels, data) {
  console.log("📊 Updating Chart with Data:", labels, data);
  var newData = labels.map((label, index) => ({
    name: label,
    value: String(data[index]),
  }));
  console.log("Data Tabel: ", newData);
  echartDiagram.setOption({
    series: [
      {
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
}

function toggleSidebar() {
  document.getElementById("sidebar").classList.toggle("show");
}

$(document).ready(function () {
  $("#date, #date1").datepicker({
    format: "dd-mm-yyyy", // Format tanggal
    autoclose: true, // Menutup otomatis setelah memilih tanggal
    todayHighlight: true, // Highlight tanggal hari ini
    templates: {
      leftArrow: "«",
      rightArrow: "»",
    },
  });
});

if (document.body.classList.contains("dark-mode")) {
  $(".datepicker-dropdown").css("background-color", "#333");
  $(".datepicker table tr td, .datepicker table tr th").css("color", "white");
}

document.querySelector('.container.my-5').style.display = 'none';
