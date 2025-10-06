/**
 * @fileoverview Chart Manager untuk Sales Ratio Report
 * @description Mengelola operasi chart (Bar Chart) menggunakan ECharts
 */

import { ELEMENT_IDS, CHART_COLORS, CHART_CONFIG, SORT_OPTIONS, STORAGE_KEYS } from '../config/constants.js';

/**
 * Class untuk mengelola chart operations
 * @class ChartManager
 */
class ChartManager {
    constructor() {
        this.barChart = null;
        this.isInitialized = false;
        this.currentData = null;
    }

    /**
     * Initialize chart manager
     * @returns {boolean} Success status
     */
    initialize() {
        try {
            
            // Check if echarts is available
            if (typeof echarts === 'undefined') {
                throw new Error('ECharts library not found');
            }

            // Initialize bar chart
            this._initializeBarChart();
            
            // Setup window resize listener
            this._setupResizeListener();
            
            this.isInitialized = true;
            return true;
            
        } catch (error) {
            console.error('❌ Error initializing Chart Manager:', error);
            return false;
        }
    }

    /**
     * Initialize bar chart instance
     * @private
     */
    _initializeBarChart() {
        const barElement = document.getElementById(ELEMENT_IDS.BAR_DIAGRAM);
        if (!barElement) {
            throw new Error(`Bar chart element ${ELEMENT_IDS.BAR_DIAGRAM} not found`);
        }

        this.barChart = echarts.init(barElement);
    }

    /**
     * Setup window resize listener
     * @private
     */
    _setupResizeListener() {
        window.addEventListener('resize', () => {
            if (this.barChart) {
                this.barChart.resize();
            }
        });
    }

    /**
     * Update bar chart dengan data baru
     * @param {Array} tableData - Data untuk chart
     * @param {string} sortBy - Sort option ('Qty' atau 'Total')
     */
    updateBarChart(tableData, sortBy = SORT_OPTIONS.TOTAL) {
        try {
            if (!this.barChart || !tableData) {
                console.warn('⚠️ Chart or data not available');
                return;
            }

            
            // Process and sort data
            const processedData = this._processChartData(tableData, sortBy);
            
            // Create chart options
            const options = this._createChartOptions(processedData, sortBy);
            
            // Set chart options
            this.barChart.setOption(options, true);
            
            // Store current data
            this.currentData = { tableData, sortBy };
            
            // Trigger resize to ensure proper display
            setTimeout(() => {
                this.barChart.resize();
            }, CHART_CONFIG.ANIMATION_DURATION);
            
            
        } catch (error) {
            console.error('❌ Error updating bar chart:', error);
        }
    }

    /**
     * Process data untuk chart
     * @private
     * @param {Array} tableData - Raw table data
     * @param {string} sortBy - Sort option
     * @returns {Object} Processed data
     */
    _processChartData(tableData, sortBy) {
        // Sort data berdasarkan sortBy
        let sortedTable = [...tableData];
        if (sortBy === SORT_OPTIONS.QTY) {
            sortedTable.sort((a, b) => b.Qty - a.Qty);
        } else {
            sortedTable.sort((a, b) => {
                const numA = a.Total || 0;
                const numB = b.Total || 0;
                return numB - numA;
            });
        }

        // Transform data untuk chart
        const newData = sortedTable.map(item => ({
            kode_supp: item.kode_supp,
            Qty: Number(item.Qty),
            Total: Number(item.Total || 0),
            tanggal: String(item.periode),
            percent: item.persentase_rp,
            percentage: item.Percentage
        }));

        // Sort berdasarkan tanggal
        newData.sort((a, b) => {
            const [dayA, monthA] = a.tanggal.split('-').map(Number);
            const [dayB, monthB] = b.tanggal.split('-').map(Number);
            return monthA !== monthB ? monthA - monthB : dayA - dayB;
        });

        // Get unique dates and supplier codes
        const tanggal = [...new Set(newData.map(item => item.tanggal))];
        const suppliers = [...new Set(newData.map(item => item.kode_supp))];

        return {
            newData,
            tanggal,
            suppliers,
            sortBy
        };
    }

    /**
     * Create chart options
     * @private
     * @param {Object} processedData - Processed data
     * @param {string} sortBy - Sort option
     * @returns {Object} ECharts options
     */
    _createChartOptions(processedData, sortBy) {
        const { newData, tanggal, suppliers } = processedData;

        // Create color mapping
        const colorMap = {};
        suppliers.forEach((supplier, index) => {
            colorMap[supplier] = CHART_COLORS[index % CHART_COLORS.length];
        });

        // Create series data
        const seriesData = suppliers.map((supplier, index) => ({
            name: supplier,
            type: 'bar',
            data: tanggal.map(date => {
                const found = newData.find(d => 
                    d.tanggal === date && d.kode_supp === supplier
                );
                
                return found ? {
                    value: sortBy === SORT_OPTIONS.QTY ? found.Qty : found.Total,
                    persen: sortBy === SORT_OPTIONS.QTY ? found.percentage : found.percent
                } : 0;
            }),
            itemStyle: {
                color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [
                    { 
                        offset: 0, 
                        color: this._shadeColor(CHART_COLORS[index % CHART_COLORS.length], 0.2) 
                    },
                    { 
                        offset: 1, 
                        color: CHART_COLORS[index % CHART_COLORS.length] 
                    }
                ])
            },
            label: {
                show: false,
                rotate: 74,
                align: 'left',
                verticalAlign: 'bottom',
                position: 'insideBottom',
                color: '#f2eded',
                fontSize: 14,
                formatter: function(params) {
                    const persen = params.data.persen;
                    return `${persen}%`;
                }
            }
        }));

        return {
            grid: CHART_CONFIG.GRID,
            tooltip: {
                trigger: CHART_CONFIG.TOOLTIP_TRIGGER,
                axisPointer: { type: 'shadow' },
                formatter: function(params) {
                    const dateLabel = params[0].axisValue;
                    const details = params
                        .filter(p => p.value > 0)
                        .map(p => {
                            const value = Number(p.value).toLocaleString('id-ID');
                            const persen = p.data.persen;
                            return `● ${p.seriesName}: <b>${value}</b> (${persen})`;
                        })
                        .join('<br>');
                    return `<b>${dateLabel}</b><br>${details}`;
                }
            },
            legend: { 
                data: suppliers,
                top: 10
            },
            toolbox: {
                show: true,
                feature: {
                    dataView: { show: true, readOnly: false },
                    magicType: { show: true, type: ['line', 'bar', 'stack'] },
                    restore: { show: false },
                    saveAsImage: { show: true }
                }
            },
            xAxis: {
                type: 'category',
                data: tanggal,
                name: 'Periode',
                axisLabel: {
                    rotate: 45,
                    interval: 0
                }
            },
            yAxis: {
                type: 'value',
                name: sortBy === SORT_OPTIONS.QTY ? 'Qty' : 'Rp',
                axisLabel: {
                    formatter: sortBy === SORT_OPTIONS.QTY 
                        ? '{value}'
                        : function(value) {
                            return value.toLocaleString();
                        }
                }
            },
            series: seriesData,
            barCategoryGap: CHART_CONFIG.BAR_CATEGORY_GAP
        };
    }

    /**
     * Shade color untuk gradients
     * @private
     * @param {string} color - Hex color
     * @param {number} percent - Shade percentage
     * @returns {string} Shaded color
     */
    _shadeColor(color, percent) {
        const f = parseInt(color.slice(1), 16);
        const t = percent < 0 ? 0 : 255;
        const p = percent < 0 ? percent * -1 : percent;
        const R = f >> 16;
        const G = (f >> 8) & 0x00FF;
        const B = f & 0x0000FF;
        
        return '#' + (
            0x1000000 +
            (Math.round((t - R) * p) + R) * 0x10000 +
            (Math.round((t - G) * p) + G) * 0x100 +
            (Math.round((t - B) * p) + B)
        ).toString(16).slice(1);
    }

    /**
     * Reset/clear bar chart
     */
    resetBarChart() {
        if (this.barChart) {
            this.barChart.clear();
            this.barChart.resize();
        }
    }

    /**
     * Show chart container
     */
    show() {
        const container = document.getElementById(ELEMENT_IDS.BAR_CONTAINER);
        if (container) {
            container.style.display = 'block';
            
            // Trigger resize after showing
            setTimeout(() => {
                if (this.barChart) {
                    this.barChart.resize();
                }
            }, CHART_CONFIG.ANIMATION_DURATION);
        }
    }

    /**
     * Hide chart container
     */
    hide() {
        const container = document.getElementById(ELEMENT_IDS.BAR_CONTAINER);
        if (container) {
            container.style.display = 'none';
        }
    }

    /**
     * Resize chart
     */
    resize() {
        if (this.barChart) {
            this.barChart.resize();
        }
    }

    /**
     * Update chart berdasarkan sort option saja (tanpa reload data)
     * @param {string} sortBy - Sort option
     */
    updateSort(sortBy) {
        const cachedData = localStorage.getItem(STORAGE_KEYS.RATIO_CHART);
        if (cachedData) {
            try {
                const tableData = JSON.parse(cachedData);
                this.updateBarChart(tableData, sortBy);
            } catch (error) {
                console.error('❌ Error parsing cached chart data:', error);
            }
        }
    }

    /**
     * Dispose chart instance
     */
    dispose() {
        if (this.barChart) {
            this.barChart.dispose();
            this.barChart = null;
        }
        this.isInitialized = false;
        this.currentData = null;
    }

    /**
     * Check if chart is ready
     * @returns {boolean} Ready status
     */
    isReady() {
        return this.isInitialized && this.barChart !== null;
    }

    /**
     * Get chart instance untuk debugging
     * @returns {Object|null} ECharts instance
     */
    getChartInstance() {
        return this.barChart;
    }

    /**
     * Get current chart data
     * @returns {Object|null} Current data
     */
    getCurrentData() {
        return this.currentData;
    }
}

// Create singleton instance
const chartManager = new ChartManager();

export default chartManager;
