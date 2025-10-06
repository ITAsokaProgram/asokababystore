//ATUR AGAR TIDAK MUAT HALAMAN----------------------------------------------------------------START
let currentPage = 1;
let totalPages = 1;

document
  .getElementById("laporanForm")
  .addEventListener("submit", function (event) {
    const categorySelect = document.getElementById("category");
    const thSupplier = document.getElementById("thName");

    function updateSupplierHeader() {
      const selectedCategory = categorySelect.value;

      if (selectedCategory === "top_sales_sub_dept") {
        thSupplier.textContent = "SUB DEPT";
      } else {
        thSupplier.textContent = "SUPPLIER";
      }
    }

    // Panggil fungsi pertama kali untuk menyesuaikan label saat halaman dimuat
    updateSupplierHeader();
    event.preventDefault(); // Mencegah pengiriman form biasa
    currentPage = 1; // Reset ke halaman pertama setiap kali form disubmit
    loadPage(currentPage);
  });

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
  }
  var xhr = new XMLHttpRequest();
  xhr.open("POST", "http://localhost/asoka-id/in_laporan.php?ajax=1", true); // Gunakan URL absolut dan query string
  xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest"); // Menambahkan header AJAX
  xhr.onload = function () {
    if (xhr.status === 200) {
      var response = JSON.parse(xhr.responseText);
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
