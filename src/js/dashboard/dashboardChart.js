const API_URL_TRANS = '/src/api/dashboard/get_data_dashboard'; // URL API PHP yang dihasilkan
const API_URL_OMSET = '/src/api/dashboard/get_pendapatan'; // URL API untuk pendapatan
// Ambil parameter periode dari select dropdown
const periodSelect = document.getElementById('period1');
const periodSelect2 = document.getElementById('period2');
const chart1Skeleton = document.getElementById('chart1-spinner');
periodSelect.addEventListener('change', fetchData); // Event untuk period1
periodSelect2.addEventListener('change', fetchDataForChart2); // Event untuk period2

// Fungsi untuk mengambil data dan memperbarui grafik (Chart 1)
function fetchData() {
    const period = periodSelect.value;
    const chart1Skeleton = document.getElementById('chart1-skeleton');

    // Tampilkan skeleton loader
    chart1Skeleton.classList.remove('hidden');
    // Fetch data dari server untuk chart1
    fetch(`${API_URL_TRANS}?period=${period}`)
        .then(res => {
            if (!res.ok) throw new Error('Network response was not ok');
            return res.json();
        })
        .then(data => {
            const responseData = data.data;

            // Siapkan data untuk grafik
            const xAxisData = responseData.map(item => {
                if (period === 'day') return item.tanggal;
                if (period === 'month') return `${item.tahun}-${item.bulan < 10 ? '0' + item.bulan : item.bulan}`;
                return item.tahun; // For 'year'
            });

            const seriesData = responseData.map(item => item.total_transaksi);

            // Update Grafik 1
            updateChart('chart1', 'Total Transaksi', xAxisData, seriesData);
            chart1Skeleton.classList.add('hidden');
        })
        .catch(err => {
            console.error('Gagal fetch data:', err);
        });
}
// Fungsi untuk mengambil data dan memperbarui grafik (Chart 2)
function fetchDataForChart2() {
    const period2 = periodSelect2.value;
    const chart2Skeleton = document.getElementById('chart2-skeleton');

    // Tampilkan skeleton loader
    chart2Skeleton.classList.remove('hidden');
    // Fetch data dari server untuk chart2
    fetch(`${API_URL_OMSET}?filter=${period2}`)
        .then(res => {
            if (!res.ok) throw new Error('Network response was not ok');
            return res.json();
        })
        .then(data => {
            // Cek jika data.data ada dan merupakan array
            if (!data.data || !Array.isArray(data.data)) {
                console.error('Data tidak ditemukan atau tidak dalam format array:', data);
                return;
            }

            const responseData = data.data;


            // Siapkan data untuk grafik
            const xAxisData = responseData.map(item => {
                if (period2 === 'per_jam') return item.label;  // jam untuk per_jam
                if (period2 === '7_hari' || period2 === '30_hari') return item.label;
                if (period2 === '12_bulan') return item.label;
            });

            // Debug: Pastikan item.total_pendapatan sesuai dengan yang diharapkan
            const seriesData = responseData.map(item => {
                const totalPendapatan = item.total_pendapatan.replace(/[^\d]/g, ''); // Menghapus "Rp" dan karakter non-digit
                return parseInt(totalPendapatan, 10);  // Mengonversi ke angka
            });


            // Update Grafik 2
            updateChart('chart2', 'Total Pendapatan', xAxisData, seriesData);
            chart2Skeleton.classList.add('hidden');

        })
        .catch(err => {
            console.error('Gagal fetch data:', err);
        });
}
// Fungsi untuk mengupdate chart (Chart.js atau ECharts)
function updateChart(chartId, title, xAxisData, seriesData) {
    const myChart = echarts.getInstanceByDom(document.getElementById(chartId)) || echarts.init(document.getElementById(chartId));
    window.addEventListener('resize', function () {
        if (myChart) {
            myChart.resize();
        }
    });
    const option = {
        grid: {
            left: '2%',
            right: '3%',
            bottom: '5%',
            containLabel: true
          },
        title: {
            text: title,
            left: 'center',
            top: '10px',
            textStyle: { fontWeight: 'bold', fontSize: 18, color: '#333' }
        },
        tooltip: { trigger: 'axis' },
        xAxis: {
            type: 'category',
            data: xAxisData,
            axisLine: { lineStyle: { color: '#ccc' } },
            axisLabel: { fontSize: 12, color: '#666' }
        },
        yAxis: {
            type: 'value',
            axisLine: { lineStyle: { color: '#ccc' } },
            axisLabel: { formatter: '{value}', fontSize: 12, color: '#666',padding: [0, 1, 0, 20] },
            nameGap : 30
            
        },
        series: [{
            name: title,
            type: 'line',
            data: seriesData,
            smooth: false,
            lineStyle: {
                width: 3,
                color: '#4CAF50'
            },
            symbol: 'circle',
            symbolSize: 8,
            itemStyle: {
                color: '#4CAF50'
            },
            emphasis: {
                itemStyle: {
                    color: '#ff5733'
                }
            }
        }]
    };

    myChart.setOption(option);
    // Membuat chart responsif
    window.addEventListener('resize', () => {
        myChart.resize();  // Menyesuaikan ukuran chart saat jendela berubah
    });
}
// Initial chart load
fetchData();  // Initial load for chart1
fetchDataForChart2();  // Initial load for chart2

