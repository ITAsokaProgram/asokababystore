//ATUR AGAR TIDAK MUAT HALAMAN----------------------------------------------------------------START
let tableDataCache = []; // 🔹 Menyimpan hasil dari AJAX `btn-sub`
let currentPage = 1;
<<<<<<< HEAD
let rowsPerPage = 10; // 🔹 Jumlah data per halaman
let isSubdeptActive = false; // 🔹 Default: Tidak menampilkan subdept
let isPromo = false;
document.getElementById("bar").style.display = "none";
document.getElementById("btn-back").style.display = "none";
document.getElementById("btn-submit").addEventListener("click", function (e) {
  e.preventDefault();
  currentPage = 1;
  loadPage(currentPage);
=======
let isSubdeptActive = false; // 🔹 Default: Tidak menampilkan subdept
let isPromo = false;
var barChart = echarts.init(document.getElementById("barDiagram"));
var pieChart = echarts.init(document.getElementById("chartDiagram"));
window.addEventListener("resize", function () {
  pieChart.resize();
  barChart.resize();
});



let activeQueryType = sessionStorage.getItem("activeQueryType") || "query1"; // Default query type

// Tangkap tombol yang diklik dan simpan query_type
document.querySelectorAll("button[name='query_type']").forEach(button => {
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
  e.preventDefault();
  currentPage = 1;
  let limit = Number(document.querySelector("#limitData").value);
  if (limit <= 9) {
    Swal.fire({
      icon: "error",
      title: "Form Input",
      text: "Limit Minimal 10",
    })
  } else {
    const Toast = Swal.mixin({
      toast: true,
      position: "top-end",
      showConfirmButton: false,
      timer: 1500,
      timerProgressBar: true,
      didOpen: (toast) => {
        toast.onmouseenter = Swal.stopTimer;
        toast.onmouseleave = Swal.resumeTimer;
      }
    });
    Toast.fire({
      icon: "success",
      title: "Berhasil, Silahkan Tunggu"
    });
    loadPage(currentPage);
  }
>>>>>>> master
});
document.getElementById("btn-sub").addEventListener("click", function (e) {
  e.preventDefault();
  currentPage = 1;
  btnSubSend(currentPage);
  document.getElementById("btn-back").style.display = "block";
});
<<<<<<< HEAD

document.body.addEventListener("click", function (e) {
  console.log("supdet", isSubdeptActive);
  console.log("promo", isPromo);
=======
document.getElementById("btn-see-promo").addEventListener("click", function (e) {
  e.preventDefault();
  isSubdeptActive = true;
  isPromo = true;
  var cabangText =
    document.getElementById("cabang").options[
      document.getElementById("cabang").selectedIndex
    ].text;
  var startDate = $("#date").val(); // Ambil nilai langsung dari datepicker
  var endDate = $("#date1").val();
  var reportHeader = document.getElementById("reportHeaderPromo");
  reportHeader.innerHTML = `Data Promo<br><p> Cabang :  ${cabangText} (${startDate} s/d ${endDate})</p>`;
  let formData = new FormData();
  formData.append("ajax", true);
  formData.append("kd_store", document.querySelector("#kd_store")?.value);
  formData.append("start_date", document.querySelector("#date")?.value);
  formData.append("end_date", document.querySelector("#date1")?.value);
  formData.append("subdept", document.querySelector("#subdept")?.value || "");
  formData.append("limit", document.querySelector("#limitData").value);
  formData.append(
    "kode_supp",
    document.querySelector("#kode_supp")?.value || ""
  );
  formData.append("page", currentPage);
  formData.append("query_type", this.value);

  console.log("🔄 Mengirim data ke server:", Object.fromEntries(formData));

  $.ajax({
    url: "post_data_sub_dept.php?ajax=1",
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
          updateTable(tableDataCache, "salesTablePromo");
          updateThead();
          updatePagination(); // 🔄 UPDATE PAGINATION
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
})
document.body.addEventListener("click", function (e) {
  var barChart = echarts.init(document.getElementById("barDiagram"));
  var pieChart = echarts.init(document.getElementById("chartDiagram"));
  setTimeout(() => {
    pieChart.resize()
    barChart.resize()
  }, 200)
  activeQueryType = sessionStorage.getItem("activeQueryType") || "query1";
>>>>>>> master
  if (e.target.id === "btn-back") {
    e.preventDefault();
    currentPage = 1;
    if (isSubdeptActive && !isPromo) {
<<<<<<< HEAD
      loadPage(currentPage);
    } else {
      isPromo = false;
      btnSubSend(currentPage);
    }
  }
});
function loadPage(page) {
  // Mengambil nilai dari form
=======
      isPromo = false;
      loadPage(currentPage);
      if (activeQueryType === "query2") {
        activeQueryType = "query1"
      } else {
        activeQueryType = "query1"
      }
    } else {
      isPromo = false;
      document.querySelector("#kode_supp").value = '';
      btnSubSend(currentPage);
      if (activeQueryType === "query4") {
        activeQueryType = "query2"
      } else {
        activeQueryType = "query2"
      }
    }
  }
  sessionStorage.setItem("activeQueryType", activeQueryType);
  console.log("Setelah klik btn-back, activeQueryType sekarang:", sessionStorage.getItem("activeQueryType"));
});



function resetPieChart() {
  var pieChart = echarts.init(document.getElementById("chartDiagram"));
  pieChart.clear()
  pieChart.resize
}

function loadPage(page) {
  Swal.fire({
    title: "Loading...",
    html: "Tunggu Sebentar",
    allowOutsideClick: false,
    timerProgressBar: true,
    didOpen: () => {
      Swal.showLoading();
    },
  });


  document.getElementById("container-table").style.display = "block";
>>>>>>> master
  document.getElementById("pie").style.display = "block";
  document.getElementById("bar").style.display = "none";
  document.getElementById("btn-back").style.display = "none";
  document.querySelector("#btn-sub").disabled = false;
  isSubdeptActive = false; // Subdept di nonaktifkan
  isPromo = false; // Subdept di nonaktifkan
  var formData = new FormData(document.getElementById("laporanForm"));
  var startDate = $("#date").val(); // Ambil nilai langsung dari datepicker
  var endDate = $("#date1").val();
<<<<<<< HEAD
=======
  var limit = $("#limitData").val();
>>>>>>> master
  console.log("📅 Tanggal Sebelum Dikirim:", startDate, endDate); // Debugging
  var cabangText =
    document.getElementById("cabang").options[
      document.getElementById("cabang").selectedIndex
    ].text;
  var queryType = document.getElementById("btn-submit").value;
  formData.append("query_type", queryType);
  console.log(queryType);
  // Format tanggal menjadi dd-mm-yyyy
  console.log("tanggal setelah dikirim", startDate, endDate);

  // Mengatur header laporan
  var reportHeader = document.getElementById("reportHeader");
  reportHeader.innerHTML = `Data Penjualan<br><p> Cabang :  ${cabangText} (${startDate} s/d ${endDate})</p>`;
<<<<<<< HEAD
  // Mengirimkan form dengan AJAX
=======
  var labelChart = document.getElementById("label-chart");
  labelChart.innerHTML = `Penjualan Sub Departemen ${cabangText} (${startDate} s/d ${endDate})`
  // Mengirimkan form dengan AJAX
  formData.append("limit", limit);
>>>>>>> master
  formData.append("ajax", "true"); // Pastikan flag ajax ditambahkan
  formData.append("query_type", "query1");
  formData.append("page", page); // Tambahkan nomor halaman ke data form
  for (var pair of formData.entries()) {
    console.log(pair[0] + ": " + pair[1]);
  }
  var xhr = new XMLHttpRequest();
  xhr.open(
    "POST",
<<<<<<< HEAD
    "http://localhost/asoka-id/in_laporan_sub_dept.php?ajax=1",
=======
    "post_data_sub_dept.php?ajax=1",
>>>>>>> master
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
        totalPages = response.totalPages ? response.totalPages : 1;
        console.log("data pages dari db:", totalPages);
        // Update chart with new data
        updateChart(labels, chartData, tableData);

        // Update table with new data
<<<<<<< HEAD
        updateTable(tableData);

        // Update pagination info
        updatePagination();
=======
        updateTable(tableData, "salesTable");
        updateThead();
        // Update pagination info
        updatePagination();
        Swal.close();

>>>>>>> master
      } else if (response.status === "error") {
        alert(response.message);
      }
    } else {
      alert("Terjadi kesalahan saat memuat data.");
<<<<<<< HEAD
=======
      Swal.fire("Error", "Gagal mengambil data!", "error");
>>>>>>> master
    }
  };
  xhr.onerror = function () {
    alert("Terjadi kesalahan jaringan.");
<<<<<<< HEAD
=======
    Swal.fire("Error", "Terjadi kesalahan pada request!", "error");
    // Tutup SweetAlert saat terjadi error
    Swal.close();
>>>>>>> master
  };
  xhr.send(formData);
}
//ATUR AGAR TIDAK MUAT HALAMAN----------------------------------------------------------------END

// TOMBOL NEXT PAGE
document.getElementById("nextBtn").addEventListener("click", function () {
  if (currentPage < totalPages) {
    currentPage++;

    console.log("Next Clicked - Current Page:", currentPage);
    console.log(
      "Before Execution - isSubdeptActive:",
      isSubdeptActive,
      "isPromo:",
      isPromo
    );

    if (isSubdeptActive && isPromo) {
      console.log("Triggering Promo Click");
      document.querySelector("#btn-promo").click();
    } else if (isSubdeptActive) {
      isPromo = false;
      console.log("Calling btnSubSend with", currentPage);
      btnSubSend(currentPage);
    } else {
      console.log("Loading page", currentPage);
      loadPage(currentPage);
    }

    console.log(
      "After Execution - isSubdeptActive:",
      isSubdeptActive,
      "isPromo:",
      isPromo
    );
  }
});

document.getElementById("prevBtn").addEventListener("click", function () {
  if (currentPage > 1) {
    currentPage--;
    console.log("Next Clicked - Current Page:", currentPage);
    console.log(
      "Before Execution - isSubdeptActive:",
      isSubdeptActive,
      "isPromo:",
      isPromo
    );

    if (isSubdeptActive && isPromo) {
      console.log("Triggering Promo Click");
      document.querySelector("#btn-promo").click();
    } else if (isSubdeptActive) {
      isPromo = false;
      console.log("Calling btnSubSend with", currentPage);
      btnSubSend(currentPage);
    } else {
      console.log("Loading page", currentPage);
      isPromo = false;
      loadPage(currentPage);
    }

    console.log(
      "After Execution - isSubdeptActive:",
      isSubdeptActive,
      "isPromo:",
      isPromo
    );
  }
});


function updatePagination() {
  console.log("🔄 Updating Pagination...");

  document.getElementById(
    "pageInfo"
  ).textContent = `Page ${currentPage} of ${totalPages}`;

  document.getElementById("prevBtn").disabled = currentPage <= 1;
  document.getElementById("nextBtn").disabled = currentPage >= totalPages;

  console.log(`📌 Current Page: ${currentPage}, Total Pages: ${totalPages}`);
}
// Function to format the date to YYYY-MM-DD
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
<<<<<<< HEAD
function updateTable(data) {
  console.log("📋 Updating Table with Data:", data);

  var tableBody = document.querySelector("#salesTable tbody");
=======
function updateTable(data, tableId) {
  console.log("📋 Updating Table with Data:", data);

  var tableBody = document.querySelector(`#${tableId} tbody`);
>>>>>>> master
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
<<<<<<< HEAD
    newRow.className = "border border-gray-300 hover:bg-gray-100"; // Tailwind untuk tampilan lebih rapi

    let itemNumber = (currentPage - 1) * rowsPerPage + index + 1; // ✅ Menyesuaikan nomor urut berdasarkan halaman

    newRow.insertCell(0).textContent = itemNumber; // 🔹 Nomor urut berlanjut
    newRow.insertCell(1).textContent =
      row.nama_subdept || row.kode_supp || row.promo;
    newRow.insertCell(2).textContent = row.Qty;
    newRow.insertCell(3).textContent = new Intl.NumberFormat("id-ID", {
      style: "decimal",
    }).format(row.Total);
=======
    newRow.className = "hover:bg-blue-50 transition-all duration-200 shadow-sm"; // Tailwind untuk tampilan lebih rapi
    let rowsPerPage = parseInt(document.getElementById("limitData").value); // 🔹 Jumlah data per halaman
    let itemNumber = (currentPage - 1) * rowsPerPage + index + 1; // ✅ Menyesuaikan nomor urut berdasarkan halaman
    let cell0 = newRow.insertCell(0);
    let cell1 = newRow.insertCell(1);
    let cell2 = newRow.insertCell(2);
    let cell3 = newRow.insertCell(3);

    cell0.textContent = itemNumber; // 🔹 Nomor urut berlanjut
    cell1.textContent = row.nama_subdept || row.kode_supp || row.promo;
    cell2.textContent = row.Qty;
    cell3.textContent = new Intl.NumberFormat("id-ID", {
      style: "decimal",
    }).format(row.Total);

    // 🔹 Atur posisi teks di tengah untuk TOP dan QTY
    cell0.classList.add("text-center");
    cell2.classList.add("text-center");
>>>>>>> master
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
<<<<<<< HEAD
=======
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
      }
    }).then((result) => {
      /* Read more about handling dismissals below */
      if (result.dismiss === Swal.DismissReason.timer) {
        setTimeout(() => {
          echartDiagram.resize();
        }, 300)
      }
    });
>>>>>>> master
    document.querySelector("#btn-sub").click();
  }
});

// Button Click Dari Pie Chart untuk mengirimkan data subdept dari ajax ke php
function btnSubSend(page) {
<<<<<<< HEAD
  document.getElementById("bar").style.display = "none";
  document.getElementById("btn-back").style.display = "block";
  document.getElementById("pie").style.display = "block";
=======
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
  }, 300)

  document.getElementById("bar").style.display = "none";
  document.getElementById("btn-back").style.display = "block";
  document.getElementById("pie").style.display = "block";
  document.getElementById("container-table").style.display = "block";

>>>>>>> master
  isSubdeptActive = true; // 🔹 Aktifkan mode subdept
  let formData = new FormData();
  var queryType = document.getElementById("btn-sub").value;
  formData.append("ajax", true);
  formData.append("query_type", queryType);
  formData.append("kd_store", document.querySelector("#kd_store")?.value);
  formData.append("start_date", document.querySelector("#date")?.value);
  formData.append("end_date", document.querySelector("#date1")?.value);
  formData.append("subdept", document.querySelector("#subdept").value);
<<<<<<< HEAD
=======
  formData.append("limit", document.querySelector("#limitData").value);
>>>>>>> master
  formData.append("page", page);

  console.log("🔄 Mengirim data ke server:", Object.fromEntries(formData));

  $.ajax({
<<<<<<< HEAD
    url: "http://localhost/asoka-id/in_laporan_sub_dept.php?ajax=1",
=======
    url: "post_data_sub_dept.php?ajax=1",
>>>>>>> master
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
          console.log("data pages dari db:", jsonResponse.totalPages);
          console.log("✅ Table Data Ditemukan:", jsonResponse.tableData);
          // 🔹 Perbaiki urutan update totalPages sebelum updatePagination()
          tableDataCache = jsonResponse.tableData;
          totalPages = jsonResponse.totalPages ? jsonResponse.totalPages : 1;
          console.log("📌 Total Pages Updated:", totalPages);
<<<<<<< HEAD
          updateTable(tableDataCache);
=======
          updateTable(tableDataCache, "salesTable");
          updateThead();
>>>>>>> master
          updatePagination(); // 🔄 UPDATE PAGINATION
          document.getElementById("btn-sub").disabled = true;
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
        updatePieChart(
          jsonResponse.labels,
          jsonResponse.data,
          jsonResponse.tableData
        );
      }
    },
<<<<<<< HEAD
=======
    complete: () => {
      Swal.close();
    }
>>>>>>> master
  });
}

// Update Chart Diagram
function updateChart(labels, data, table) {
  console.log("📊 Updating Chart with Data:", labels, data, table);
  var newData = labels.map((label, index) => ({
    name: label,
    value: String(data[index]).slice(5),
    subdept: String(data[index]).slice(0, 4),
<<<<<<< HEAD
  }));
  console.log("Data Tabel: ", newData);
  echartDiagram.setOption({
    series: [
      {
=======
    precentage: table[index]?.Percentage
  }));
  console.log("Data Tabel: ", newData);
  echartDiagram.setOption({
    tooltip: {
      trigger: 'item',
      formatter: function (params) {
        return `Subdept <br/> ${params.name} : ${params.value} (${params.data.precentage}) `
      }
    },
    series: [
      {
        label: {
          fontSize: function () {
            let chartWidth = echartDiagram.getWidth(); // Ambil lebar chart
            return chartWidth < 400 ? 10 : chartWidth < 800 ? 12 : 14; // Ukuran font responsif
          },
          formatter: function (params) {
            return `${params.name} (${params.data.precentage})`
          }
        },
>>>>>>> master
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
}

function updatePieChart(labels, data, table) {
  console.log("📊 Updating Chart with Data:", labels, data, table);
  var newData = labels.map((label, index) => ({
    name: label,
    value: String(data[index]).slice(5),
    subdept: String(data[index]).slice(0, 4),
    kode: String(table[index]?.kode_supp),
<<<<<<< HEAD
=======
    precentage: table[index]?.Percentage
>>>>>>> master
  }));
  console.log("Data Tabel: ", newData);
  echartDiagram.setOption({
    series: [
      {
<<<<<<< HEAD
=======
        tooltip: {
          trigger: 'item',
          formatter: function (params) {
            return `Supplier <br/> ${params.name} : ${params.value} (${params.data.precentage}) `
          }
        },
        label: {
          fontSize: function () {
            let chartWidth = echartDiagram.getWidth(); // Ambil lebar chart
            return chartWidth < 400 ? 10 : chartWidth < 800 ? 12 : 14; // Ukuran font responsif
          },
          formatter: (params) => {
            return `${params.name} (${params.data.precentage})`
          }
        },
>>>>>>> master
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
<<<<<<< HEAD
=======

>>>>>>> master
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
<<<<<<< HEAD
=======
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
  labelChart.innerHTML = `Penjualan Sub Departemen ${cabangText} (${document.querySelector("#date")?.value} s/d ${document.querySelector("#date1")?.value})`
  var barChart = echarts.init(document.getElementById("barDiagram"));
  setTimeout(() => {
    barChart.resize();
  }, 300)
>>>>>>> master
  isSubdeptActive = true;
  isPromo = true;
  currentPage = 1;
  document.getElementById("bar").style.display = "block";
  document.getElementById("pie").style.display = "none";
  let formData = new FormData();
  formData.append("ajax", true);
  formData.append("kd_store", document.querySelector("#kd_store")?.value);
  formData.append("start_date", document.querySelector("#date")?.value);
  formData.append("end_date", document.querySelector("#date1")?.value);
  formData.append("subdept", document.querySelector("#subdept").value);
  formData.append("kode_supp", document.querySelector("#kode_supp").value);
<<<<<<< HEAD
=======
  formData.append("limit", document.querySelector("#limitData").value);
>>>>>>> master
  formData.append("page", currentPage);
  formData.append("query_type", this.value);

  console.log("🔄 Mengirim data ke server:", Object.fromEntries(formData));

  $.ajax({
<<<<<<< HEAD
    url: "http://localhost/asoka-id/in_laporan_sub_dept.php?ajax=1",
=======
    url: "post_data_sub_dept.php?ajax=1",
>>>>>>> master
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
<<<<<<< HEAD
=======
    complete: () => {
      Swal.close();
    }
>>>>>>> master
  });
});

document.getElementById("btn-promo").addEventListener("click", function (e) {
  e.preventDefault();
<<<<<<< HEAD
=======
  document.getElementById("container-table").style.display = "show";
>>>>>>> master
  isSubdeptActive = true;
  isPromo = true;
  let formData = new FormData();
  formData.append("ajax", true);
  formData.append("kd_store", document.querySelector("#kd_store")?.value);
  formData.append("start_date", document.querySelector("#date")?.value);
  formData.append("end_date", document.querySelector("#date1")?.value);
  formData.append("subdept", document.querySelector("#subdept")?.value || "");
<<<<<<< HEAD
=======
  formData.append("limit", document.querySelector("#limitData").value);

>>>>>>> master
  formData.append(
    "kode_supp",
    document.querySelector("#kode_supp")?.value || ""
  );
  formData.append("page", currentPage);
  formData.append("query_type", this.value);

  console.log("🔄 Mengirim data ke server:", Object.fromEntries(formData));

  $.ajax({
<<<<<<< HEAD
    url: "http://localhost/asoka-id/in_laporan_sub_dept.php?ajax=1",
=======
    url: "post_data_sub_dept.php?ajax=1",
>>>>>>> master
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
<<<<<<< HEAD
          updateTable(tableDataCache);
=======
          updateTable(tableDataCache, "salesTable");
          updateThead();
>>>>>>> master
          updatePagination(); // 🔄 UPDATE PAGINATION
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

  var newData = labels.map((label, index) => ({
    promo: label,
    Qty: Number(data[index]),
    tanggal: String(table[index]?.periode),
<<<<<<< HEAD
=======
    precentage: table[index]?.Percentage,
>>>>>>> master
  }));
  console.log("Data Tabel: ", newData);
  // Ambil daftar unik tanggal dan promo
  var tanggal = newData.map((item) => item.tanggal);
  var promos = [...new Set(newData.map((item) => item.promo))];

  console.log("tanggal sebelum kirim:", tanggal)

  // Buat struktur data Qty berdasarkan promo
  var promoQtyMap = {};
  promos.forEach((promo) => {
    promoQtyMap[promo] = tanggal.map((tgl) => {
      let item = newData.find((d) => d.promo === promo && d.tanggal === tgl);
      return item ? item.Qty : 0;
    });
  });
  console.log(promoQtyMap);
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
<<<<<<< HEAD
    rotate: 90,
    align: "left",
    verticalAlign: "middle",
    position: "insideBottom",
    distance: 15,
=======
    rotate: 0,
    align: "left",
    verticalAlign: "bottom",
    position: "top",
    distance: 0,
>>>>>>> master
    onChange: function () {
      const labelOption = {
        rotate: app.config.rotate,
        align: app.config.align,
        verticalAlign: app.config.verticalAlign,
        position: app.config.position,
        distance: app.config.distance,
      };
      barChart.setOption({
        series: [
          {
<<<<<<< HEAD
            label: labelOption,
          },
          {
            label: labelOption,
          },
          {
            label: labelOption,
          },
          {
            label: labelOption,
=======
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
>>>>>>> master
          },
        ],
      });
    },
  };
  const labelOption = {
    show: true,
    position: app.config.position,
    distance: app.config.distance,
    align: app.config.align,
    verticalAlign: app.config.verticalAlign,
    rotate: app.config.rotate,
    formatter: function (params) {
      return params.seriesName; // Hanya menampilkan nama promo
    },
    fontSize: 14,
    rich: {
      name: {},
    },
  };
  optionBarCharts = {
<<<<<<< HEAD
=======
    grid: {
      left: '4%',
      right: '4%',
      bottom: '5%',
      containLabel: true
    },
>>>>>>> master
    tooltip: {
      trigger: "axis",
      axisPointer: {
        type: "shadow",
      },
      formatter: function (params) {
        let dateLabel = params[0].axisValue; // Ambil label tanggal
        let promoDetails = promos
          .map((promo, index) => {
            let qty = promoQtyMap[promo][tanggal.indexOf(dateLabel)] || 0;
<<<<<<< HEAD
            return qty > 0 ? `<br>● ${promo}: <b>${qty}</b>` : "";
          })
          .join(""); // Gabungkan semua promo yang ada di tanggal ini
        return `<b>${dateLabel}</b><br>Promo: <b>${promoDetails}</b>`;
=======
            return qty > 0 ? `<br>● ${promo}: <b>${qty} Qty</b>` : "";
          })
          .join(""); // Gabungkan semua promo yang ada di tanggal ini
        return `Tanggal: <b>${dateLabel}</b><br>Promo: <b>${promoDetails}</b>`;
>>>>>>> master
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
        restore: { show: true },
        saveAsImage: { show: true },
      },
      connectNulls: true,
    },
    xAxis: [
      {
        type: "category",
<<<<<<< HEAD
=======
        name: "Hari/Bulan/Tahun",
        nameLocation: 'center',
        nameGap: 35,
        nameTextStyle: {
          fontSize: 14,
          fontWeight: 'bold',
          padding: [10, 0, 0, 0],
        },
>>>>>>> master
        boundaryGap: true,
        axisTick: { show: false },
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
<<<<<<< HEAD
=======
          rotate: 45
>>>>>>> master
        },
      },
    ],
    yAxis: [
      {
        type: "value",
<<<<<<< HEAD
=======
        name: "Kuantiti",
        nameLocation: "center",
        nameRotate: "90",
        nameTextStyle: {
          fontSize: 14,
          fontWeight: 'bold',
          padding: [0, 0, 40, 0]
        }
>>>>>>> master
      },
    ],
    series: [
      {
        connectNulls: true,
        name: "Promo", // Tetap satu kategori
        type: "bar",
        stack: "total",
        data: tanggal.map((tgl) => {
<<<<<<< HEAD
          return newData
            .filter((item) => item.tanggal === tgl)
            .reduce((sum, item) => sum + item.Qty, 0); // Total qty per tanggal
=======
          let items = newData.filter((item) => item.tanggal === tgl);
          let totalQty = items.reduce((sum, item) => sum + item.Qty, 0);
          let percentage = items.length > 0 ? items[0].precentage : ""; // Ambil percentage dari salah satu item

          return { value: totalQty, percentage: percentage };
>>>>>>> master
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
<<<<<<< HEAD
          rotate: 90,
          align: "left",
          verticalAlign: "middle",
          position: "insideBottom",
          formatter: function (params) {
            let tanggalData = newData.find((item) => item.tanggal === params.name);
            return tanggalData ? tanggalData.promo : ""; // Gunakan promo sebagai label
=======
          rotate: 74,
          align: "left",
          verticalAlign: "bottom",
          position: "insideBottom",
          distance: 0,
          formatter: function (params) {
            return params.data.percentage ? params.data.percentage : "";
>>>>>>> master
          },
          fontSize: 12,
          color: "#000000", // Warna hitam
        },
      },
    ],
<<<<<<< HEAD

    // series: promos.map((promo) => ({
    //   connectNulls: true,
    //   name: promo, // Setiap promo sebagai satu series
    //   type: "bar",
    //   stack: "total", // Supaya semua promo ditumpuk jadi satu bar per tanggal
    //   data: promoQtyMap[promo].map((qty) => (qty > 0 ? qty : null)),
    //   itemStyle: {
    //     color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [
    //       { offset: 0, color: '#83bff6' },
    //       { offset: 0.5, color: '#188df0' },
    //       { offset: 1, color: '#188df0' }
    //     ])
    //   },
    //   label: labelOption,
    //   emphasis: { focus: "series" },
    // })),
  };

=======
  };
  // **Tambahkan event listener untuk menangani perubahan magicType**
  barChart.on("magictypechanged", function (event) {
    let newType = event.currentType; // Dapatkan jenis chart yang dipilih
    barChart.setOption({
      series: [
        {
          label: { show: newType === "line" ? false : true }
        },
      ],
    });
  });
>>>>>>> master
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

document.getElementById('toggle-sidebar').addEventListener('click', function () {
<<<<<<< HEAD
  document.getElementById('sidebar').classList.toggle('open');
=======
  var pieChart = echarts.init(document.getElementById("chartDiagram"));
  var barChart = echarts.init(document.getElementById("barDiagram"));
  document.getElementById('sidebar').classList.toggle('open');
  setTimeout(() => {
    pieChart.resize()
    barChart.resize()
  })
>>>>>>> master
});

document.getElementById("toggle-hide").addEventListener("click", function () {
  var sidebarTexts = document.querySelectorAll(".sidebar-text");
  let mainContent = document.getElementById("main-content");
  let sidebar = document.getElementById("sidebar");
  var toggleButton = document.getElementById("toggle-hide");
  var icon = toggleButton.querySelector("i");
<<<<<<< HEAD
=======
  var barChart = echarts.init(document.getElementById("barDiagram"));
  var pieChart = echarts.init(document.getElementById("chartDiagram"));
>>>>>>> master

  if (sidebar.classList.contains("w-64")) {
    // Sidebar mengecil
    sidebar.classList.remove("w-64", "px-5");
    sidebar.classList.add("w-16", "px-2");
    sidebarTexts.forEach(text => text.classList.add("hidden")); // Sembunyikan teks
    mainContent.classList.remove("ml-64");
    mainContent.classList.add("ml-16"); // Main ikut mundur
    toggleButton.classList.add("left-20"); // Geser tombol lebih dekat
    toggleButton.classList.remove("left-64");
    icon.classList.remove("fa-angle-left"); // Ubah ikon
    icon.classList.add("fa-angle-right");
<<<<<<< HEAD
=======
    setTimeout(() => {
      pieChart.resize();
      barChart.resize();
    }, 300)
>>>>>>> master
  } else {
    // Sidebar membesar
    sidebar.classList.remove("w-16", "px-2");
    sidebar.classList.add("w-64", "px-5");
    sidebarTexts.forEach(text => text.classList.remove("hidden")); // Tampilkan teks kembali
    mainContent.classList.remove("ml-16");
    mainContent.classList.add("ml-64");
    toggleButton.classList.add("left-64"); // Geser tombol ke posisi awal
    toggleButton.classList.remove("left-20");
    icon.classList.remove("fa-angle-right"); // Ubah ikon
    icon.classList.add("fa-angle-left");
<<<<<<< HEAD
=======
    setTimeout(() => {
      pieChart.resize();
      barChart.resize();
    }, 300)
>>>>>>> master
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
    if (!profileCard.contains(event.target) && !profileImg.contains(event.target)) {
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
<<<<<<< HEAD
    'AXY': '2103'
  };

=======
    'AXY': '2103',
    'SEMUA CABANG': allCabang
  };

  var allCabang = Object.values(storeCodes);

>>>>>>> master
  // Event listener ketika cabang berubah
  $('#cabang').on('change', function () {
    var selectedBranch = $(this).val(); // Ambil nilai cabang yang dipilih
    var storeCode = storeCodes[selectedBranch] || '1501'; // Ambil kode store atau default '1501'
<<<<<<< HEAD
    $('#kd_store').val(storeCode); // Set nilai input kode_store
=======

    if (selectedBranch === 'SEMUA CABANG') {
      $('#kd_store').val(allCabang.join(','))
    } else {
      $('#kd_store').val(storeCode); // Set nilai input kode_store
    }
>>>>>>> master
  });

  // Trigger event saat halaman dimuat untuk mengisi nilai awal
  $('#cabang').trigger('change');
});

document.addEventListener("DOMContentLoaded", function () {
  flatpickr("#date", {
    dateFormat: "d-m-Y",
    allowInput: true
  });

  flatpickr("#date1", {
    dateFormat: "d-m-Y",
    allowInput: true
  });
});
<<<<<<< HEAD
=======

function updateThead() {
  const tableHead = document.getElementById('thHead');
  const subdeptElement = document.querySelector('#subdept');
  const kodeSupElement = document.querySelector('#kode_supp');

  if (!subdeptElement || !kodeSupElement || !tableHead) {
    console.error("Elemen tidak ditemukan!");
    return;
  }

  const subdeptHead = subdeptElement.value.trim();
  const kodeSupHead = kodeSupElement.value.trim();

  console.log("subdeptHead:", subdeptHead);
  console.log("kodeSupHead:", kodeSupHead);

  if (isSubdeptActive && isPromo) {
    tableHead.textContent = 'DATA BARANG';
  } else if (isSubdeptActive) {
    tableHead.textContent = 'SUPPLIER';
  } else {
    tableHead.textContent = 'SUBDEPT';
  }

}


// Input Data Limit
document.getElementById('limitData').addEventListener('click', function (event) {
  event.stopPropagation(); // Mencegah event bubbling
  document.getElementById('dropdown').classList.toggle('hidden');
});

document.querySelectorAll('#dropdown div').forEach(item => {
  item.addEventListener('click', function () {
    document.getElementById('limitData').value = this.getAttribute('data-value');
    document.getElementById('dropdown').classList.add('hidden');
  });
});

document.body.addEventListener('click', function () {
  document.getElementById('dropdown').classList.add('hidden');
});


console.log("Nilai query parameter: ", activeQueryType)


function exportExcel1(query) {
  const limit = 1000;
  let formDataExport = new FormData();
  formDataExport.append("ajax", true);
  formDataExport.append("csrf_token", document.querySelector("[name='csrf_token']").value);
  formDataExport.append("kd_store", document.querySelector("#kd_store")?.value);
  formDataExport.append("start_date", document.querySelector("#date")?.value);
  formDataExport.append("end_date", document.querySelector("#date1")?.value);
  formDataExport.append("query_type", query);
  formDataExport.append("limit", limit);
  formDataExport.append('export_all', true);
  const cabangText =
    document.getElementById("cabang").options[
      document.getElementById("cabang").selectedIndex
    ].text;
  const kodeSupDept = document.querySelector("#subdept").value;
  fetch("post_data_sub_dept.php?ajax=1", {
    method: "POST",
    body: formDataExport
  })
    .then(response => response.json())
    .then(data => {
      let workbook = new ExcelJS.Workbook();
      let worksheet = workbook.addWorksheet("Data Penjualan");

      // **Buat Header**
      let headers = ["No", "SUB DEPT", "QTY", "TOTAL"];
      // Cek apakah data memiliki nama_subdept atau kode_supp
      if (data.tableData.length > 0) {
        if (data.tableData.some(item => item.nama_subdept)) {
          headers[1] = "SUBDEPT";
        } else if (data.tableData.some(item => item.kode_supp)) {
          headers[1] = "SUPPLIER";
        } else if (data.tableData.some(item => item.promo)) {
          headers[1] = "DATA BARANG";
        }
      }
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
        let row = worksheet.addRow([index + 1, item.nama_subdept || item.kode_supp || item.promo, item.Qty, item.Total]);
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
        link.download = `Data_Penjualan_Cabang_${cabangText}_Subdept_${kodeSupDept}.xlsx`;
        link.click();
      });
    })
    .catch(error => console.error("Error fetching data:", error));
}

function exportExcel2(query) {
  const limit = 1000;
  let formDataExport = new FormData();
  formDataExport.append("ajax", true);
  formDataExport.append("csrf_token", document.querySelector("[name='csrf_token']").value);
  formDataExport.append("kd_store", document.querySelector("#kd_store")?.value);
  formDataExport.append("start_date", document.querySelector("#date")?.value);
  formDataExport.append("end_date", document.querySelector("#date1")?.value);
  formDataExport.append("subdept", document.querySelector("#subdept").value);
  formDataExport.append("query_type", query);
  formDataExport.append("limit", limit);
  formDataExport.append('export_all', true);
  const cabangText =
    document.getElementById("cabang").options[
      document.getElementById("cabang").selectedIndex
    ].text;
  const kodeSupDept = document.querySelector("#subdept").value;
  fetch("post_data_sub_dept.php?ajax=1", {
    method: "POST",
    body: formDataExport
  })
    .then(response => response.json())
    .then(data => {
      let workbook = new ExcelJS.Workbook();
      let worksheet = workbook.addWorksheet("Data Penjualan");

      // **Buat Header**
      let headers = ["No", "SUB DEPT", "QTY", "TOTAL"];
      // Cek apakah data memiliki nama_subdept atau kode_supp
      if (data.tableData.length > 0) {
        if (data.tableData.some(item => item.nama_subdept)) {
          headers[1] = "SUBDEPT";
        } else if (data.tableData.some(item => item.kode_supp)) {
          headers[1] = "SUPPLIER";
        } else if (data.tableData.some(item => item.promo)) {
          headers[1] = "DATA BARANG";
        }
      }
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
        let row = worksheet.addRow([index + 1, item.nama_subdept || item.kode_supp || item.promo, item.Qty, item.Total]);
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
        link.download = `Data_Penjualan_Cabang_${cabangText}_Subdept_${kodeSupDept}.xlsx`;
        link.click();
      });
    })
    .catch(error => console.error("Error fetching data:", error));
}

function exportExcel3(query) {
  const limit = 1000;
  let formDataExport = new FormData();
  formDataExport.append("ajax", true);
  formDataExport.append("csrf_token", document.querySelector("[name='csrf_token']").value);
  formDataExport.append("kd_store", document.querySelector("#kd_store")?.value);
  formDataExport.append("start_date", document.querySelector("#date")?.value);
  formDataExport.append("end_date", document.querySelector("#date1")?.value);
  formDataExport.append("subdept", document.querySelector("#subdept").value);
  formDataExport.append("limit", limit);
  formDataExport.append(
    "kode_supp",
    document.querySelector("#kode_supp")?.value || ""
  );
  formDataExport.append("query_type", query);
  formDataExport.append('export_all', true);
  const cabangText =
    document.getElementById("cabang").options[
      document.getElementById("cabang").selectedIndex
    ].text;
  const kodeSupDept = document.querySelector("#subdept").value;
  fetch("post_data_sub_dept.php?ajax=1", {
    method: "POST",
    body: formDataExport
  })
    .then(response => response.json())
    .then(data => {
      let workbook = new ExcelJS.Workbook();
      let worksheet = workbook.addWorksheet("Data Penjualan");

      // **Buat Header**
      let headers = ["No", "SUB DEPT", "QTY", "TOTAL"];
      // Cek apakah data memiliki nama_subdept atau kode_supp
      if (data.tableData.length > 0) {
        if (data.tableData.some(item => item.nama_subdept)) {
          headers[1] = "SUBDEPT";
        } else if (data.tableData.some(item => item.kode_supp)) {
          headers[1] = "SUPPLIER";
        } else if (data.tableData.some(item => item.promo)) {
          headers[1] = "DATA BARANG";
        }
      }
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
        let row = worksheet.addRow([index + 1, item.nama_subdept || item.kode_supp || item.promo, item.Qty, item.Total]);
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
        link.download = `Data_Penjualan_Cabang_${cabangText}_Subdept_${kodeSupDept}.xlsx`;
        link.click();
      });
    })
    .catch(error => console.error("Error fetching data:", error));
}

function exportQuery1(query) {
  const limit = 1000;
  let formDataExport = new FormData();
  formDataExport.append("ajax", true);
  formDataExport.append("csrf_token", document.querySelector("[name='csrf_token']").value);
  formDataExport.append("kd_store", document.querySelector("#kd_store")?.value);
  formDataExport.append("start_date", document.querySelector("#date")?.value);
  formDataExport.append("end_date", document.querySelector("#date1")?.value);
  formDataExport.append("query_type", query);
  formDataExport.append("limit", limit);
  formDataExport.append("export_all", true);

  const { jsPDF } = window.jspdf;
  let doc = new jsPDF();
  const cabangText =
    document.getElementById("cabang").options[
      document.getElementById("cabang").selectedIndex
    ].text;
  const kodeSupDept = document.querySelector("#subdept").value;
  fetch("post_data_sub_dept.php?ajax=1", {
    method: "POST",
    body: formDataExport
  })
    .then(response => response.json())
    .then(data => {
      if (!data.tableData || data.tableData.length === 0) {
        console.error("Tidak ada data untuk diekspor.");
        return;
      }

      let headers = ["No", "SUB DEPT", "QTY", "TOTAL"];
      // Cek apakah data memiliki nama_subdept atau kode_supp
      if (data.tableData.length > 0) {
        if (data.tableData.some(item => item.nama_subdept)) {
          headers[1] = "SUBDEPT";
        } else if (data.tableData.some(item => item.kode_supp || item.promo)) {
          headers[1] = "SUPPLIER";
        } else if (data.tableData.some(item => item.promo)) {
          headers[1] = "DATA BARANG";
        }
      }
      let rows = data.tableData.map((item, index) => [
        index + 1, item.nama_subdept || item.kode_supp || item.promo, item.Qty, `Rp. ${new Intl.NumberFormat('id-ID').format(item.Total)}`
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
          fontSize: 10,
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

          doc.setFont("helvetica", "bold");
          doc.setFontSize(14);
          doc.text("PT. Asoka Indonesia", 45, 15);

          doc.setFontSize(10);
          doc.setFont("helvetica", "normal");
          doc.text("Lb 5, Jl. Utan Jati Blok Lb 5 No.9, RT.10/RW.12, Kalideres, West Jakarta City, Jakarta 11840", 45, 20);
          doc.text("Telp: 0819-4943-1969 | Email: info@contoh.com", 45, 25);

          // **Buat Garis Pemisah**
          doc.setLineWidth(0.5);
          doc.line(10, 30, 200, 30);
          // **Tambahkan Judul**
          doc.setFont("helvetica", "bold");
          doc.setFontSize(14);
          doc.text("Laporan Data Penjualan", 15, 45);
        }
      });


      doc.save(`Data_Penjualan_Cabang_${cabangText}_Subdept_${kodeSupDept}.pdf`);
    })
    .catch(error => console.error("Error fetching data:", error));
}

function exportQuery2(query) {
  const limit = 1000;
  let formDataExport = new FormData();
  formDataExport.append("ajax", true);
  formDataExport.append("csrf_token", document.querySelector("[name='csrf_token']").value);
  formDataExport.append("kd_store", document.querySelector("#kd_store")?.value);
  formDataExport.append("start_date", document.querySelector("#date")?.value);
  formDataExport.append("end_date", document.querySelector("#date1")?.value);
  formDataExport.append("subdept", document.querySelector("#subdept").value);
  formDataExport.append("query_type", query);
  formDataExport.append("limit", limit);
  formDataExport.append("export_all", true);

  const { jsPDF } = window.jspdf;
  let doc = new jsPDF();
  const cabangText =
    document.getElementById("cabang").options[
      document.getElementById("cabang").selectedIndex
    ].text;
  const kodeSupDept = document.querySelector("#subdept").value;
  fetch("post_data_sub_dept.php?ajax=1", {
    method: "POST",
    body: formDataExport
  })
    .then(response => response.json())
    .then(data => {
      if (!data.tableData || data.tableData.length === 0) {
        console.error("Tidak ada data untuk diekspor.");
        return;
      }

      let headers = ["No", "SUB DEPT", "QTY", "TOTAL"];
      // Cek apakah data memiliki nama_subdept atau kode_supp
      if (data.tableData.length > 0) {
        if (data.tableData.some(item => item.nama_subdept)) {
          headers[1] = "SUBDEPT";
        } else if (data.tableData.some(item => item.kode_supp || item.promo)) {
          headers[1] = "SUPPLIER";
        } else if (data.tableData.some(item => item.promo)) {
          headers[1] = "DATA BARANG";
        }
      }
      let rows = data.tableData.map((item, index) => [
        index + 1, item.nama_subdept || item.kode_supp || item.promo, item.Qty, `Rp. ${new Intl.NumberFormat('id-ID').format(item.Total)}`
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
          fontSize: 10,
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

          doc.setFont("helvetica", "bold");
          doc.setFontSize(14);
          doc.text("PT. Asoka Indonesia", 45, 15);

          doc.setFontSize(10);
          doc.setFont("helvetica", "normal");
          doc.text("Lb 5, Jl. Utan Jati Blok Lb 5 No.9, RT.10/RW.12, Kalideres, West Jakarta City, Jakarta 11840", 45, 20);
          doc.text("Telp: 0819-4943-1969 | Email: info@contoh.com", 45, 25);

          // **Buat Garis Pemisah**
          doc.setLineWidth(0.5);
          doc.line(10, 30, 200, 30);
          // **Tambahkan Judul**
          doc.setFont("helvetica", "bold");
          doc.setFontSize(14);
          doc.text("Laporan Data Penjualan", 15, 45);
        }
      });


      doc.save(`Data_Penjualan_Cabang_${cabangText}_Subdept_${kodeSupDept}.pdf`);
    })
    .catch(error => console.error("Error fetching data:", error));
}

function exportQuery3(query) {
  const limit = 1000;
  let formDataExport = new FormData();
  formDataExport.append("ajax", true);
  formDataExport.append("csrf_token", document.querySelector("[name='csrf_token']").value);
  formDataExport.append("kd_store", document.querySelector("#kd_store")?.value);
  formDataExport.append("start_date", document.querySelector("#date")?.value);
  formDataExport.append("subdept", document.querySelector("#subdept").value);
  formDataExport.append("query_type", query);
  formDataExport.append(
    "kode_supp",
    document.querySelector("#kode_supp")?.value || ""
  );
  formDataExport.append("end_date", document.querySelector("#date1")?.value);
  formDataExport.append("limit", limit);
  formDataExport.append("export_all", true);

  const { jsPDF } = window.jspdf;
  let doc = new jsPDF();
  const cabangText =
    document.getElementById("cabang").options[
      document.getElementById("cabang").selectedIndex
    ].text;
  const kodeSupDept = document.querySelector("#subdept").value;
  fetch("post_data_sub_dept.php?ajax=1", {
    method: "POST",
    body: formDataExport
  })
    .then(response => response.json())
    .then(data => {
      if (!data.tableData || data.tableData.length === 0) {
        console.error("Tidak ada data untuk diekspor.");
        return;
      }

      let headers = ["No", "SUB DEPT", "QTY", "TOTAL"];
      // Cek apakah data memiliki nama_subdept atau kode_supp
      if (data.tableData.length > 0) {
        if (data.tableData.some(item => item.nama_subdept)) {
          headers[1] = "SUBDEPT";
        } else if (data.tableData.some(item => item.kode_supp || item.promo)) {
          headers[1] = "SUPPLIER";
        } else if (data.tableData.some(item => item.promo)) {
          headers[1] = "DATA BARANG";
        }
      }
      let rows = data.tableData.map((item, index) => [
        index + 1, item.nama_subdept || item.kode_supp || item.promo, item.Qty, `Rp. ${new Intl.NumberFormat('id-ID').format(item.Total)}`
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
          fontSize: 10,
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

          doc.setFont("helvetica", "bold");
          doc.setFontSize(14);
          doc.text("PT. Asoka Indonesia", 45, 15);

          doc.setFontSize(10);
          doc.setFont("helvetica", "normal");
          doc.text("Lb 5, Jl. Utan Jati Blok Lb 5 No.9, RT.10/RW.12, Kalideres, West Jakarta City, Jakarta 11840", 45, 20);
          doc.text("Telp: 0819-4943-1969 | Email: info@contoh.com", 45, 25);

          // **Buat Garis Pemisah**
          doc.setLineWidth(0.5);
          doc.line(10, 30, 200, 30);
          // **Tambahkan Judul**
          doc.setFont("helvetica", "bold");
          doc.setFontSize(14);
          doc.text("Laporan Data Penjualan", 15, 45);
        }
      });


      doc.save(`Data_Penjualan_Cabang_${cabangText}_Subdept_${kodeSupDept}.pdf`);
    })
    .catch(error => console.error("Error fetching data:", error));
}

function exportToExcel() {
  console.log("Nilai query parameter: ", activeQueryType)
  if (activeQueryType === "query1") {
    console.log("query benarr 1")
    exportExcel1(activeQueryType);
  } else if (activeQueryType === "query2") {
    console.log("query benarr 2")
    exportExcel2(activeQueryType);

  } else if (activeQueryType === "query4") {
    console.log("query benar 3")
    exportExcel3(activeQueryType);
  }

}

function exportToPDF() {
  console.log("Nilai query parameter: ", activeQueryType)

  if (activeQueryType === "query1") {
    console.log("query benarr 1")
    exportQuery1(activeQueryType);
  } else if (activeQueryType === "query2") {
    console.log("query benarr 2")
    exportQuery2(activeQueryType)
  } else if (activeQueryType === "query4") {
    console.log("query benarr 3")
    exportQuery3(activeQueryType);
  }


}

function exportPDFModal() {
  const { jsPDF } = window.jspdf;
  const doc = new jsPDF();
  const cabangText =
  document.getElementById("cabang").options[
    document.getElementById("cabang").selectedIndex
  ].text;
const kodeSupDept = document.querySelector("#subdept").value;
  const table = document.getElementById("salesTablePromo");
  const rows = [];

  // Mengambil data dari tabel
  table.querySelectorAll("tbody tr").forEach((row, index) => {
      const rowData = [
          index + 1, // Nomor urut
          row.cells[1].innerText, // PROMO
          row.cells[2].innerText, // QTY
          row.cells[3].innerText  // TOTAL
      ];
      rows.push(rowData);
  });

  let headers = [["No", "PROMO", "QTY", "TOTAL"]];

  doc.autoTable({
      head: headers,
      body: rows,
      startY: 50,
      theme: 'grid',
      styles: { fontSize: 8, cellPadding: 3 },
      headStyles: {
          fillColor: [0, 112, 192],
          textColor: [255, 255, 255],
          fontSize: 10,
          fontStyle: "bold",
          halign: "center"
      },
      bodyStyles: {
          textColor: [10, 10, 10],
      },
      columnStyles: {
          0: { halign: "center" },
          1: { halign: "left" },
          2: { halign: "center" },
          3: { halign: "left" }
      },
      margin: { top: 50 },
      didDrawPage: function (data) {
          const logo = "/images/logo.png"; // Ganti dengan base64 jika perlu
          doc.addImage(logo, "PNG", 15, 10, 25, 10);

          doc.setFont("helvetica", "bold");
          doc.setFontSize(14);
          doc.text("PT. Asoka Indonesia", 45, 15);

          doc.setFontSize(10);
          doc.setFont("helvetica", "normal");
          doc.text("Lb 5, Jl. Utan Jati Blok Lb 5 No.9, RT.10/RW.12, Kalideres, West Jakarta City, Jakarta 11840", 45, 20);
          doc.text("Telp: 0819-4943-1969 | Email: info@contoh.com", 45, 25);

          doc.setLineWidth(0.5);
          doc.line(10, 30, 200, 30);

          doc.setFont("helvetica", "bold");
          doc.setFontSize(14);
          doc.text("Laporan Data Penjualan", 15, 45);
      }
  });

  doc.save("Laporan_Penjualan.pdf");
}

function exportToExcelModal() {
  const cabangText =
  document.getElementById("cabang").options[
    document.getElementById("cabang").selectedIndex
  ].text;
const kodeSupDept = document.querySelector("#subdept").value;
const tableRows = document.querySelectorAll("#salesTablePromo tbody tr");
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
      tableRows.forEach((row, index) => {
        let rowData = [
          index + 1,                // No
          row.cells[1].innerText,   // PROMO / SUB DEPT
          row.cells[2].innerText,   // QTY
          row.cells[3].innerText    // TOTAL
        ];
        let excelRow = worksheet.addRow(rowData);
      
        excelRow.getCell(1).alignment = { horizontal: "center" }; // No → Tengah
        excelRow.getCell(2).alignment = { horizontal: "left" };   // PROMO / SUB DEPT → Kiri
        excelRow.getCell(3).alignment = { horizontal: "center" }; // QTY → Tengah
        excelRow.getCell(4).alignment = { horizontal: "right" };  // TOTAL → Kanan
      
        excelRow.getCell(4).numFmt = '"Rp" #,##0'; // Format Total ke Rupiah
      
        excelRow.eachCell((cell) => {
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
        link.download = `Data_Penjualan_Cabang_${cabangText}_Subdept_${kodeSupDept}.xlsx`;
        link.click();
      });
}
>>>>>>> master
