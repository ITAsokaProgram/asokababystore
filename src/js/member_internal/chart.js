export const renderChart = (dataTren) => {
    const chartDom = document.getElementById('activeMemberChart');
    const myChart = echarts.init(chartDom);

    const trend = dataTren.data.trend_active; // Ambil array trend

    const option = {
        tooltip: {
            trigger: 'axis'
        },
        xAxis: {
            type: 'category',
            data: trend.map(i => i.bulan) // Ambil nama bulan
        },
        yAxis: {
            type: 'value'
        },
        series: [{
            name: 'Jumlah Member',
            data: trend.map(i => i.total_member_aktif), // Ambil nilainya
            type: 'line',
            smooth: true,
            areaStyle: {
                color: 'rgba(37, 99, 235, 0.2)'
            },
            lineStyle: {
                color: '#2563EB'
            },
            itemStyle: {
                color: '#2563EB'
            }
        }]
    };

    myChart.setOption(option);
    window.addEventListener('resize', () => myChart.resize());
};
export const renderChartFilter = (dataTren) => {
    const chartDom = document.getElementById('activeMemberChart');
    const myChart = echarts.init(chartDom);

    const trend = dataTren.active_trend; // Ambil array trend

    const option = {
        tooltip: {
            trigger: 'axis'
        },
        xAxis: {
            type: 'category',
            data: trend.map(i => i.bulan) // Ambil nama bulan
        },
        yAxis: {
            type: 'value'
        },
        series: [{
            name: 'Jumlah Member',
            data: trend.map(i => i.total_member_aktif), // Ambil nilainya
            type: 'line',
            smooth: true,
            areaStyle: {
                color: 'rgba(37, 99, 235, 0.2)'
            },
            lineStyle: {
                color: '#2563EB'
            },
            itemStyle: {
                color: '#2563EB'
            }
        }]
    };

    myChart.setOption(option);
    window.addEventListener('resize', () => myChart.resize());
};
export const renderChartNon = (dataTren) => {
    const chartDom = document.getElementById('nonactiveTrendChart');
    const myChart = echarts.init(chartDom);

    const trend = dataTren.data.trend; // Ambil array trend

    const option = {
        tooltip: {
            trigger: 'axis'
        },
        xAxis: {
            type: 'category',
            data: trend.map(i => i.bulan) // Ambil nama bulan
        },
        yAxis: {
            type: 'value'
        },
        series: [{
            name: 'Jumlah Nonaktif',
            data: trend.map(i => i.jumlah_nonaktif), // Ambil nilainya
            type: 'line',
            smooth: true,
            areaStyle: {
                color: 'rgba(37, 99, 235, 0.2)'
            },
            lineStyle: {
                color: '#2563EB'
            },
            itemStyle: {
                color: '#2563EB'
            }
        }]
    };

    myChart.setOption(option);
    window.addEventListener('resize', () => myChart.resize());
}
export const renderChartFilterNon = (dataTren) => {
    const chartDom = document.getElementById('activeMemberChart');
    const myChart = echarts.init(chartDom);

    const trend = dataTren.active_trend; // Ambil array trend

    const option = {
        tooltip: {
            trigger: 'axis'
        },
        xAxis: {
            type: 'category',
            data: trend.map(i => i.bulan) // Ambil nama bulan
        },
        yAxis: {
            type: 'value'
        },
        series: [{
            name: 'Jumlah Member',
            data: trend.map(i => i.total_member_aktif), // Ambil nilainya
            type: 'line',
            smooth: true,
            areaStyle: {
                color: 'rgba(37, 99, 235, 0.2)'
            },
            lineStyle: {
                color: '#2563EB'
            },
            itemStyle: {
                color: '#2563EB'
            }
        }]
    };

    myChart.setOption(option);
    window.addEventListener('resize', () => myChart.resize());
};
export default { renderChart, renderChartNon, renderChartFilter, renderChartFilterNon };