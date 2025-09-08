//ATUR DIAGRAM UNTUK TAMPILAN AWAL----------------------------------------------------------------START
var ctx = document.getElementById('myChart').getContext('2d');
var myChart = new Chart(ctx, {
  type: 'pie',
  data: {
    datasets: [{
      label: 'Kosong',
      data: [1],
      backgroundColor: ['rgba(255, 99, 132, 1)'],
      borderColor: ['rgba(255, 255, 255, 1)'],
      borderWidth: 1
    }]
  },
  options: {
    responsive: true,
    maintainAspectRatio: false
  }
});
//ATUR DIAGRAM UNTUK TAMPILAN AWAL----------------------------------------------------------------END

//ATUR AGAR TIDAK MUAT HALAMAN----------------------------------------------------------------START
document.getElementById('laporanForm').addEventListener('submit', function (event) {
  event.preventDefault(); // Mencegah pengiriman form biasa

  // Mengirimkan form dengan AJAX
  var formData = new FormData(document.getElementById('laporanForm'));
  formData.append('ajax', 'true'); // Pastikan flag ajax ditambahkan

  var xhr = new XMLHttpRequest();
  xhr.open('POST', 'https://asokababystore.com/in_laporan_hanya_tampilkan_diagram.php?ajax=1', true); // Gunakan URL absolut dan query string
  xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest'); // Menambahkan header AJAX
  xhr.onload = function () {
    if (xhr.status === 200) {
      var response = JSON.parse(xhr.responseText);
      console.log(response); // Tambahkan logging di sini untuk memeriksa respons
      if (response.status === 'success') {
        var labels = response.labels;
        var chartData = response.data;
        var category = formData.get('category');

        // Update chart with new data
        updateChart(labels, chartData, category);
      } else if (response.status === 'error') {
        alert(response.message);
      }
    } else {
      alert('Terjadi kesalahan saat memuat data.');
    }
  };
  xhr.onerror = function () {
    alert('Terjadi kesalahan jaringan.');
  };
  xhr.send(formData);
});
//ATUR AGAR TIDAK MUAT HALAMAN----------------------------------------------------------------END

//ATUR DIAGRAM----------------------------------------------------------------START
function updateChart(labels, data, category) {
  var ctx = document.getElementById('myChart').getContext('2d');
  // Destroy previous chart instance if exists
  if (window.myChart instanceof Chart) {
    window.myChart.destroy();
  }

  window.myChart = new Chart(ctx, {
    type: 'pie',
    data: {
      labels: labels,
      datasets: [{
        label: 'Total Penjualan',
        data: data,
        backgroundColor: [
          'rgba(255, 99, 132, 1)',
          'rgba(54, 162, 235, 1)',
          'rgba(255, 206, 86, 1)',
          'rgba(75, 192, 192, 1)',
          'rgba(153, 102, 255, 1)',
          'rgba(255, 159, 64, 1)'
        ],
        borderColor: [
          'rgba(255, 255, 255, 5)'
        ],
        borderWidth: 1
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        datalabels: {
          color: '#333333',
          font: {
            family: 'Arial',
            size: 10,
            weight: 'bold'
          },
          formatter: (value, ctx) => {
            let label = ctx.chart.data.labels[ctx.dataIndex];
            return label;
          }
        },
        legend: {
          display: false
        },
        tooltip: {
          enabled: true
        }
      }
    },
    plugins: [ChartDataLabels]
  });
}
//ATUR DIAGRAM----------------------------------------------------------------END


//ATUR TANGGAL----------------------------------------------------------------START
// Function to format the date to YYYY-MM-DD
function formatDate(date) {
  var d = new Date(date),
    day = '' + d.getDate(),
    month = '' + (d.getMonth() + 1),
    year = d.getFullYear();

  if (month.length < 2) month = '0' + month;
  if (day.length < 2) day = '0' + day;

  return [day, month, year].join('-');
}

// Get today's date
var today = new Date();

// Set the start date to 30 days from today
var startDate = new Date();
startDate.setDate(today.getDate() - 30);
document.getElementById('start_date').valueAsDate = startDate;

// Function to update display date
function updateDisplaysDate() {
  var startDateInput = document.getElementById('start_date').value;
  var formattedDate = formatDate(startDateInput);
  document.getElementById('display_start_date').value = formattedDate;
}

// Set the end date to 1 day from today
var endDate = new Date();
endDate.setDate(today.getDate() - 1);
document.getElementById('end_date').valueAsDate = endDate;

// Function to update display date
function updateDisplayDate() {
  var endDateInput = document.getElementById('end_date').value;
  var formattedDate = formatDate(endDateInput);
  document.getElementById('display_end_date').value = formattedDate;
}

// Initialize display date
updateDisplaysDate();
updateDisplayDate();

//ATUR TANGGAL----------------------------------------------------------------END