const chart1 = echarts.init(document.getElementById('chart1'));
const chart2 = echarts.init(document.getElementById('chart2'));
window.addEventListener("resize", () => {
    if (chart1 || chart2) {
        chart1.resize()
        chart2.resize()
    }
})

function fetchDataTotalTrans(store, startDate, endDate) {
    // Ambil nilai dari inputan user
    const dataFilter = document.getElementById('filterRange').value;
    const requestData = {
        data: dataFilter,
        start: startDate,
        end: endDate,
        cabang: store
    };
    // Siapkan URL untuk fetch berdasarkan parameter filter
    document.getElementById('progressOverlay').classList.remove('hidden');

    fetch("https://asokababystore.com/src/api/transaction/get_transaction_member", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(requestData)
    })
        .then(res => {
            if (!res.ok) {
                throw new Error(`HTTP error! status: ${res.status}`);
            }
            return res.json(); // ubah jadi text dulu biar bisa dicek manual
        })
        .then(data => {
            updateCharts(data);
            loadBarangTable(data);
        })
        .catch(error => {
            console.error('Error fetching data:', error);
        })
        .finally(() => {
            document.getElementById('progressOverlay').classList.add('hidden');
        });

}

function fetchDataTotalTransNonMember(store, startDate, endDate) {
    const dataFilter = document.getElementById("filterRange").value;
    const reqBody = {
        data: dataFilter,
        cabang: store,
        start_date: startDate,
        end_date: endDate,
    }
    document.getElementById('progressOverlay').classList.remove('hidden');
    fetch("https://asokababystore.com/src/api/transaction/get_transaction_non_member", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(reqBody)
    }).then(res => res.json())
        .then(data => {
            updateCharts(data)
            loadBarangTable(data)
        }).catch(error => {
            console.log("Fetch Data Gagal", error)
        }).finally(() => {
            document.getElementById('progressOverlay').classList.add('hidden');
        });

}

function fetchAllTransaction(store, startDate, endDate) {
    const reqBody = {
        cabang: store,
        start_date: startDate,
        end_date: endDate
    }
    document.getElementById('progressOverlay').classList.remove('hidden');
    fetch("https://asokababystore.com/src/api/transaction/get_all_transaction", {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify(reqBody)
    }).then(res => res.json())
        .then(data => {
            updateBarCharts(data)
            loadBarangTable(data)
            updateChartsPie(data)
        }).catch(error => {
            console.log("Gagal Fetch Data", error)
        }).finally(() => {
            document.getElementById('progressOverlay').classList.add('hidden');

        })
}

function resetChart(domId) {
    const el = document.getElementById(domId);
    if (echarts.getInstanceByDom(el)) {
        echarts.dispose(el);
    }
    return echarts.init(el);
}

function updateCharts(data) {
    const dates = data.data.map(item => item.periode);
    const totalTransactions = data.data.map(item => item.total_transaksi);

    const chart1 = resetChart('chart1');
    chart1.setOption({
        tooltip: { trigger: 'axis' },
        xAxis: { type: 'category', data: dates },
        yAxis: { type: 'value' },
        series: [{
            name: "Transaksi",
            data: totalTransactions,
            type: 'line',
            smooth: true,
            itemStyle: { color: '#42a5f5' }
        }]
    });
    setTimeout(() => chart1.resize(), 300);

    const totalPromo = data.data.reduce((sum, item) => sum + (parseInt(item.total_barang_terjual_promo) || 0), 0);
    const totalNonPromo = data.data.reduce((sum, item) => sum + (parseInt(item.total_barang_terjual_non_promo) || 0), 0);

    const chart2 = resetChart('chart2');
    chart2.setOption({
        tooltip: { trigger: 'item' },
        series: [{
            type: 'pie',
            radius: '55%',
            data: [
                { name: 'Promo', value: totalPromo },
                { name: 'Non-Promo', value: totalNonPromo }
            ],
            itemStyle: { borderColor: '#fff', borderWidth: 2 }
        }]
    });
    setTimeout(() => chart2.resize(), 300);
}

function updateChartsPie(data) {
    const totalMember = data.data.reduce((sum, item) => sum + (parseInt(item.transaksi_member) || 0), 0);
    const totalNonMember = data.data.reduce((sum, item) => sum + (parseInt(item.transaksi_non_member) || 0), 0);

    const chart2 = resetChart('chart2');
    chart2.setOption({
        tooltip: {
            trigger: 'item',
            formatter: '{b}: {c} ({d}%)'
        },
        series: [{
            type: 'pie',
            radius: '55%',
            data: [
                { name: 'Member', value: totalMember },
                { name: 'Non-Member', value: totalNonMember }
            ],
            label: {
                formatter: '{b}: {d}%',
                fontSize: 14
            },
            itemStyle: {
                borderColor: '#fff',
                borderWidth: 2
            }
        }]
    });
    setTimeout(() => chart2.resize(), 300);
}

function updateBarCharts(data) {
    const dates = data.data.map(item => item.periode);
    const memberTransactions = data.data.map(item => item.transaksi_member);
    const nonMemberTransactions = data.data.map(item => item.transaksi_non_member);

    const chart1 = resetChart('chart1');
    chart1.setOption({
        tooltip: {
            trigger: 'axis',
            axisPointer: { type: 'shadow' },
            formatter: function (params) {
                let result = params[0].axisValue + '<br/>';
                params.forEach(param => {
                    const total = params[0].value + params[1].value;
                    const percentage = (param.value / total) * 100;
                    result += `${param.marker} ${param.seriesName}: <strong>${param.value}</strong> (${percentage.toFixed(1)}%)<br/>`;
                });
                result += `Total: <strong>${params[0].value + params[1].value}</strong>`;
                return result;
            }
        },
        legend: {
            data: ['Member', 'Non-Member'],
            top: 0
        },
        xAxis: {
            type: 'category',
            name: "Periode",
            data: dates,
            axisLabel: { rotate: 45 }
        },
        yAxis: {
            type: 'value',
            name: 'Transaksi'
        },
        series: [
            {
                name: 'Member',
                type: 'bar',
                data: memberTransactions,
                itemStyle: {
                    color: {
                        type: 'linear',
                        x: 0, y: 0, x2: 0, y2: 1,
                        colorStops: [
                            { offset: 0, color: '#4CAF50' },
                            { offset: 1, color: '#C8E6C9' }
                        ]
                    }
                },
                label: { show: false }
            },
            {
                name: 'Non-Member',
                type: 'bar',
                data: nonMemberTransactions,
                itemStyle: {
                    color: {
                        type: 'linear',
                        x: 0, y: 0, x2: 0, y2: 1,
                        colorStops: [
                            { offset: 0, color: '#F44336' },
                            { offset: 1, color: '#FFCDD2' }
                        ]
                    }
                },
                label: { show: false }
            }
        ],
        dataZoom: [{
            type: 'slider',
            show: false,
            xAxisIndex: [0],
            start: 0,
            end: 100
        }]
    });

    setTimeout(() => chart1.resize(), 300);
}

function loadBarangTable(data) {
    if ($.fn.DataTable.isDataTable('#barangTable')) {
        $('#barangTable').DataTable().destroy();
    }

    $('#barangTable').DataTable({
        data: data.barang,
        columns: [
            {
                data: null,
                title: "No",
                render: (data, type, row, meta) => meta.row + 1,
                className: "text-center"
            },
            { data: 'barcode' },
            { data: 'descp' },
            { data: 'total_terjual', className: "text-center" },
            {
                data: 'harga',
                render: data => `Rp ${parseInt(data).toLocaleString()}`,
                className: "text-center"
            },
            {
                data: 'hrg_promo',
                render: data => `Rp ${parseInt(data).toLocaleString()}`,
                className: "text-center"
            }
        ],
        language: {
            search: "Cari:",
            lengthMenu: "Tampilkan _MENU_ data",
            info: "Menampilkan _START_ - _END_ dari _TOTAL_ data",
            paginate: {
                first: "Awal",
                last: "Akhir",
                next: "›",
                previous: "‹"
            }
        },
        responsive: true,
        autoWidth: false,
        scrollX: false,
        initComplete: function () {
            const $length = $('.dataTables_length');
            const $search = $('.dataTables_filter');
            const $info = $('.dataTables_info');
            const $paginate = $('.dataTables_paginate');

            // Buat wrapper atas
            const $top = $('<div class="dt-detail-top grid grid-cols-1 md:grid-cols-2 gap-4 items-center mb-4"></div>');
            $length.addClass('order-1');
            $search.addClass('order-3 justify-self-end');
            $top.append($length).append($search);
            $top.insertBefore('#barangTable');

            // Buat wrapper bawah
            const $bottom = $('<div class="dt-detail-bottom grid grid-cols-1 md:grid-cols-2 items-center mt-4"></div>');
            $info.addClass('text-sm text-gray-600');
            $paginate.addClass('flex justify-end gap-2');
            $bottom.append($info).append($paginate);
            $bottom.insertAfter('#barangTable');

            // Style elemen-elemen input/select
            $length.find('select').addClass('ml-2 px-2 py-1 border rounded-lg');
            $search.find('input[type="search"]').addClass('ml-2 px-2 py-1 border rounded-lg');
        },
        drawCallback: function () {
            // Inject Tailwind classes ke pagination
            $('.dataTables_paginate a').addClass('px-3 py-1 border rounded-lg text-sm text-gray-700 hover:bg-pink-100 cursor-pointer');
            $('.dataTables_paginate .current')
                .removeClass('text-gray-700 hover:bg-pink-100')
                .addClass('bg-pink-500 text-white font-semibold border-pink-500');

            $('#barangTable tbody tr').addClass('hover:bg-pink-50 transition-all duration-200');
            $('#barangTable tbody td').addClass('px-2 py-2');
            $('#barangTable thead th').addClass('text-center');
        }
    });
}
function resetChart(domId) {
    const el = document.getElementById(domId);
    if (echarts.getInstanceByDom(el)) {
        echarts.dispose(el);
    }
    return echarts.init(el);
}
document.getElementById("cek").addEventListener("click", (e) => {
    e.preventDefault()
    const startDate = $("#filterRangeDateStart").val();
    const endDate = $("#filterRangeDateEnd").val();
    if ($("#filterRange").val() === 'Member') {
        fetchDataTotalTrans(storeCode, startDate, endDate);
    } else if ($("#filterRange").val() === 'NonMember') {
        fetchDataTotalTransNonMember(storeCode, startDate, endDate);
    } else {
        fetchAllTransaction(storeCode, startDate, endDate);
    }
})
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

    return `${day}/${month}/${year}`; // Format yang benar untuk laporan
}
// Mengatur Kondisi Input Dalam Kondisi Rentang 1 Bulan
var startDateInput = document.getElementById("filterRangeDateStart");
var endDateInput = document.getElementById("filterRangeDateEnd");
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
    flatpickr("#filterRangeDateStart", {
        dateFormat: "d-m-Y",
        allowInput: true,
    });

    flatpickr("#filterRangeDateEnd", {
        dateFormat: "d-m-Y",
        allowInput: true,
    });
});

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
