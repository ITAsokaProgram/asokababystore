/**
 * @fileoverview State management untuk laporan penjualan kategori
 * @description Mengelola state aplikasi, cache, dan history untuk navigasi chart
 */

import { deepClone } from '../utils/formatters.js';
import branchService from '../services/branchService.js';

/**
 * Class untuk mengelola state aplikasi
 */
class SalesCategoryState {
    constructor() {
        this.storeCode = "";
        this.cachedChartData = null;
        this.cachedChartMode = null;
        this.chartHistoryStack = [];
    }

    /**
     * Set store code berdasarkan pilihan cabang
     * @param {string} selectedBranch - Cabang yang dipilih
     */
    async setStoreCode(selectedBranch) {
        if (selectedBranch === 'SEMUA CABANG') {
            const allBranchCodes = await branchService.getAllBranchCodes();
            this.storeCode = allBranchCodes.join(',');
        } else {
            const storeCode = await branchService.getStoreCodeForBranch(selectedBranch);
            this.storeCode = storeCode || '';
        }
    }

    /**
     * Get current store code
     * @returns {string} Current store code
     */
    getStoreCode() {
        return this.storeCode;
    }

    /**
     * Set cache untuk chart dan table data
     * @param {Object} cacheData - Data yang akan di-cache
     * @param {string} cacheData.chartMode - Mode chart (early, category, detail)
     * @param {Array} cacheData.labels - Labels untuk chart
     * @param {Array} cacheData.chartData - Data untuk chart
     * @param {string} cacheData.tableMode - Mode table
     * @param {Array} cacheData.tableData - Data untuk table
     */
    setFullCache({ chartMode, labels, chartData, tableMode, tableData }) {
        // Cek apakah state terakhir sama untuk menghindari duplikasi
        const last = this.chartHistoryStack[this.chartHistoryStack.length - 1];
        
        // Default ke array kosong jika undefined/null
        const safeLabels = labels || [];
        const safeChartData = chartData || [];
        const safeTableData = tableData || [];
        
        const newState = {
            type: 'full',
            chartMode,
            labels: safeLabels,
            chartData: safeChartData,
            tableMode,
            tableData: safeTableData
        };

        // Skip jika state sama dengan yang terakhir
        if (last && JSON.stringify(last) === JSON.stringify(newState)) {
            return;
        }

        this.chartHistoryStack.push(newState);
    }

    /**
     * Get cache data terakhir
     * @returns {Object|null} Cache data atau null jika tidak ada
     */
    getFullCache() {
        const last = this.chartHistoryStack[this.chartHistoryStack.length - 1];
        if (last && last.type === 'full') {
            return {
                chartMode: last.chartMode,
                labels: deepClone(last.labels),
                chartData: deepClone(last.chartData),
                tableData: deepClone(last.tableData)
            };
        }
        return null;
    }

    /**
     * Restore state sebelumnya dari history stack
     * @returns {Object|null} Previous state atau null jika tidak ada
     */
    restorePreviousState() {
        // Remove current state
        this.chartHistoryStack.pop();
        
        const previousState = this.chartHistoryStack[this.chartHistoryStack.length - 1];
        if (!previousState || previousState.type !== 'full') {
            return null;
        }

        // Update cached data
        this.cachedChartData = { 
            labels: deepClone(previousState.labels), 
            data: deepClone(previousState.chartData) 
        };
        this.cachedChartMode = previousState.chartMode;

        return deepClone(previousState);
    }

    /**
     * Clear semua cache dan history
     */
    clearCache() {
        this.cachedChartData = null;
        this.cachedChartMode = null;
        this.chartHistoryStack = [];
    }

    /**
     * Get history stack length
     * @returns {number} Panjang history stack
     */
    getHistoryLength() {
        return this.chartHistoryStack.length;
    }

    /**
     * Check apakah ada previous state
     * @returns {boolean} true jika ada previous state
     */
    hasPreviousState() {
        return this.chartHistoryStack.length > 1;
    }

    /**
     * Get current cached chart data
     * @returns {Object|null} Cached chart data
     */
    getCachedChartData() {
        return this.cachedChartData ? deepClone(this.cachedChartData) : null;
    }

    /**
     * Get current cached chart mode
     * @returns {string|null} Cached chart mode
     */
    getCachedChartMode() {
        return this.cachedChartMode;
    }

    /**
     * Set cached chart data
     * @param {Object} data - Chart data
     */
    setCachedChartData(data) {
        this.cachedChartData = deepClone(data);
    }

    /**
     * Set cached chart mode
     * @param {string} mode - Chart mode
     */
    setCachedChartMode(mode) {
        this.cachedChartMode = mode;
    }
}

// Create singleton instance
const salesCategoryState = new SalesCategoryState();

export default salesCategoryState;
