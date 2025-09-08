/**
 * @fileoverview Chart Manager untuk laporan sub departemen
 * @description Mengelola semua operasi chart (Pie Chart dan Bar Chart) menggunakan ECharts
 */

import { ELEMENT_IDS, CHART_COLORS, CHART_ANIMATION } from '../config/constants.js';
import { formatCurrency, formatNumber, formatPercentage } from '../utils/formatters.js';
import stateManager from '../utils/state.js';

/**
 * Class untuk mengelola chart operations
 * @class ChartManager
 */
class ChartManager {
    constructor() {
        this.barChart = null;
        this.pieChart = null;
        this.isInitialized = false;
    }

    /**
     * Inisialisasi chart instances
     * @returns {boolean} Status inisialisasi
     */
    initialize() {
        try {
            console.log('🎨 Initializing Chart Manager...');

            // Initialize bar chart
            const barElement = document.getElementById(ELEMENT_IDS.BAR_CHART);
            if (barElement) {
                this.barChart = echarts.init(barElement);
                console.log('📊 Bar chart initialized');
            } else {
                console.warn('⚠️ Bar chart element tidak ditemukan');
            }

            // Initialize pie chart  
            const pieElement = document.getElementById(ELEMENT_IDS.PIE_CHART);
            if (pieElement) {
                this.pieChart = echarts.init(pieElement);
                console.log('🥧 Pie chart initialized');
            } else {
                console.warn('⚠️ Pie chart element tidak ditemukan');
            }

            // Setup resize handler
            this._setupResizeHandler();

            // Update state dengan chart instances
            stateManager.setChartInstances({
                barChart: this.barChart,
                pieChart: this.pieChart
            });

            this.isInitialized = true;
            return true;

        } catch (error) {
            console.error('❌ Error initializing Chart Manager:', error);
            return false;
        }
    }

    /**
     * Setup resize handler untuk responsive charts
     * @private
     */
    _setupResizeHandler() {
        window.addEventListener("resize", () => {
            if (this.pieChart) {
                this.pieChart.resize();
            }
            if (this.barChart) {
                this.barChart.resize();
            }
        });
    }

    /**
     * Update pie chart dengan data baru
     * @param {Array} labels - Label untuk chart
     * @param {Array} data - Data untuk chart
     * @param {Array} tableData - Data tabel untuk referensi
     */
    updatePieChart(labels, data, tableData) {
        if (!this.pieChart) {
            console.warn('⚠️ Pie chart belum diinisialisasi');
            return;
        }

        try {
            console.log('🥧 Updating pie chart...');

            // Prepare data untuk pie chart
            const chartData = this._preparePieChartData(labels, data, tableData);
            
            const option = {
                tooltip: {
                    trigger: 'item',
                    formatter: (params) => {
                        const data = params.data;
                        return `
                            <div style="padding: 8px;">
                                <strong>${data.name}</strong><br/>
                                <span style="color: ${params.color};">●</span> 
                                Quantity: ${formatNumber(data.qty)}<br/>
                                <span style="color: ${params.color};">●</span> 
                                Total: ${formatCurrency(data.value)}<br/>
                                <span style="color: ${params.color};">●</span> 
                                Persentase: ${data.percentage}%
                            </div>
                        `;
                    }
                },
                legend: {
                    type: 'scroll',
                    orient: 'vertical',
                    right: 10,
                    top: 20,
                    bottom: 20,
                    data: labels,
                    textStyle: {
                        fontSize: 12
                    }
                },
                series: [
                    {
                        name: 'Data',
                        type: 'pie',
                        radius: ['40%', '70%'],
                        center: ['40%', '50%'],
                        avoidLabelOverlap: false,
                        label: {
                            show: false,
                            position: 'center'
                        },
                        emphasis: {
                            label: {
                                show: true,
                                fontSize: '16',
                                fontWeight: 'bold'
                            }
                        },
                        labelLine: {
                            show: false
                        },
                        data: chartData,
                        ...CHART_ANIMATION
                    }
                ]
            };

            this.pieChart.setOption(option);
            
            // Setup click handler
            this._setupPieChartClickHandler();

            console.log('✅ Pie chart updated successfully');

        } catch (error) {
            console.error('❌ Error updating pie chart:', error);
        }
    }

    /**
     * Update bar chart dengan data baru
     * @param {Array} labels - Label untuk chart
     * @param {Array} chartData - Data untuk chart
     * @param {Array} tableData - Data tabel untuk referensi
     */
    updateBarChart(labels, chartData, tableData) {
        if (!this.barChart) {
            console.warn('⚠️ Bar chart belum diinisialisasi');
            return;
        }

        try {
            console.log('📊 Updating bar chart...');

            const option = {
                title: {
                    text: 'Grafik Batang Data',
                    left: 'center',
                    textStyle: {
                        fontSize: 16,
                        fontWeight: 'bold'
                    }
                },
                tooltip: {
                    trigger: 'axis',
                    axisPointer: {
                        type: 'shadow'
                    },
                    formatter: (params) => {
                        const data = params[0];
                        const originalData = tableData[data.dataIndex];
                        return `
                            <div style="padding: 8px;">
                                <strong>${data.name}</strong><br/>
                                Value: ${formatCurrency(data.value)}<br/>
                                ${originalData ? `Qty: ${formatNumber(originalData.Qty || 0)}` : ''}
                            </div>
                        `;
                    }
                },
                xAxis: {
                    type: 'category',
                    data: labels,
                    axisLabel: {
                        interval: 0,
                        rotate: 45,
                        fontSize: 10,
                        formatter: (value) => {
                            // Truncate long labels
                            return value.length > 15 ? value.substring(0, 15) + '...' : value;
                        }
                    }
                },
                yAxis: {
                    type: 'value',
                    axisLabel: {
                        formatter: (value) => {
                            if (value >= 1000000000) {
                                return (value / 1000000000).toFixed(1) + 'B';
                            } else if (value >= 1000000) {
                                return (value / 1000000).toFixed(1) + 'M';
                            } else if (value >= 1000) {
                                return (value / 1000).toFixed(1) + 'K';
                            }
                            return value;
                        }
                    }
                },
                series: [
                    {
                        name: 'Value',
                        type: 'bar',
                        data: chartData.map((value, index) => ({
                            value: value,
                            itemStyle: {
                                color: CHART_COLORS[index % CHART_COLORS.length]
                            }
                        })),
                        emphasis: {
                            itemStyle: {
                                shadowBlur: 10,
                                shadowOffsetX: 0,
                                shadowColor: 'rgba(0, 0, 0, 0.5)'
                            }
                        },
                        ...CHART_ANIMATION
                    }
                ]
            };

            this.barChart.setOption(option);
            console.log('✅ Bar chart updated successfully');

        } catch (error) {
            console.error('❌ Error updating bar chart:', error);
        }
    }

    /**
     * Prepare data untuk pie chart
     * @private
     * @param {Array} labels - Labels untuk chart
     * @param {Array} data - Raw data
     * @param {Array} tableData - Table data untuk referensi
     * @returns {Array} Formatted data untuk pie chart
     */
    _preparePieChartData(labels, data, tableData) {
        return labels.map((label, index) => {
            const rawData = data[index];
            const tableItem = tableData[index];
            
            // Parse data yang mungkin dalam format "code,name,value"
            let value, qty, percentage;
            if (typeof rawData === 'string') {
                const parts = rawData.split(',');
                value = parseFloat(parts[2]) || 0;
            } else {
                value = parseFloat(rawData) || 0;
            }

            // Get additional info dari tableData
            if (tableItem) {
                qty = tableItem.Qty || 0;
                percentage = tableItem.persentase || tableItem.Percentage || 0;
            }

            return {
                name: label,
                value: value,
                qty: qty,
                percentage: percentage,
                itemStyle: {
                    color: CHART_COLORS[index % CHART_COLORS.length]
                }
            };
        });
    }

    /**
     * Setup click handler untuk pie chart
     * @private
     */
    _setupPieChartClickHandler() {
        if (!this.pieChart) return;

        this.pieChart.off('click'); // Remove existing handlers
        
        this.pieChart.on('click', (params) => {
            console.log('🖱️ Pie chart clicked:', params.name);
            
            // Trigger custom event untuk komunikasi dengan komponen lain
            const event = new CustomEvent('pieChartClick', {
                detail: {
                    name: params.name,
                    value: params.value,
                    data: params.data
                }
            });
            document.dispatchEvent(event);
        });
    }

    /**
     * Reset pie chart ke state kosong
     */
    resetPieChart() {
        if (!this.pieChart) return;

        try {
            this.pieChart.clear();
            console.log('🔄 Pie chart reset');
        } catch (error) {
            console.error('❌ Error resetting pie chart:', error);
        }
    }

    /**
     * Reset bar chart ke state kosong  
     */
    resetBarChart() {
        if (!this.barChart) return;

        try {
            this.barChart.clear();
            console.log('🔄 Bar chart reset');
        } catch (error) {
            console.error('❌ Error resetting bar chart:', error);
        }
    }

    /**
     * Resize charts (manual trigger)
     */
    resizeCharts() {
        setTimeout(() => {
            if (this.pieChart) {
                this.pieChart.resize();
            }
            if (this.barChart) {
                this.barChart.resize();
            }
            console.log('📏 Charts resized');
        }, 200);
    }

    /**
     * Dispose chart instances
     */
    dispose() {
        try {
            if (this.barChart) {
                this.barChart.dispose();
                this.barChart = null;
            }
            if (this.pieChart) {
                this.pieChart.dispose();
                this.pieChart = null;
            }
            this.isInitialized = false;
            console.log('🗑️ Chart Manager disposed');
        } catch (error) {
            console.error('❌ Error disposing Chart Manager:', error);
        }
    }

    /**
     * Get initialization status
     * @returns {boolean} Status inisialisasi
     */
    isReady() {
        return this.isInitialized && (this.barChart || this.pieChart);
    }

    /**
     * Get chart instances
     * @returns {Object} Chart instances
     */
    getChartInstances() {
        return {
            barChart: this.barChart,
            pieChart: this.pieChart
        };
    }
}

// Export singleton instance
const chartManager = new ChartManager();
export default chartManager;
