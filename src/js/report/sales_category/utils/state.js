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
        this.storeCode = []; 
        this.cachedChartData = null;
        this.cachedChartMode = null;
        this.chartHistoryStack = [];
    }
    /**
     * Set store code berdasarkan pilihan cabang
     * @param {string|Array} selectedBranch - Cabang yang dipilih (Bisa string nama, string kode, atau array)
     */
    async setStoreCode(selectedBranch) {
        try {
            const allStoreMap = await branchService.getStoreCodes();
            let branchesInput = Array.isArray(selectedBranch) ? selectedBranch : [selectedBranch];
            if (branchesInput.includes('SEMUA CABANG')) {
                this.storeCode = Object.values(allStoreMap);
                return;
            }
            const resolvedCodes = branchesInput.map(item => {
                if (allStoreMap[item]) {
                    return allStoreMap[item];
                }
                if (Object.values(allStoreMap).includes(item)) {
                    return item;
                }
                return null;
            }).filter(code => code !== null); 
            this.storeCode = [...new Set(resolvedCodes)];
        } catch (error) {
            console.error("Failed to set store code in state:", error);
            this.storeCode = [];
        }
    }
    /**
     * Get current store code
     * @returns {Array|string} Current store code
     */
    getStoreCode() {
        return this.storeCode;
    }
    setFullCache({ chartMode, labels, chartData, tableMode, tableData }) {
        const last = this.chartHistoryStack[this.chartHistoryStack.length - 1];
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
        if (last && JSON.stringify(last) === JSON.stringify(newState)) {
            return;
        }
        this.chartHistoryStack.push(newState);
    }
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
    restorePreviousState() {
        this.chartHistoryStack.pop();
        const previousState = this.chartHistoryStack[this.chartHistoryStack.length - 1];
        if (!previousState || previousState.type !== 'full') {
            return null;
        }
        this.cachedChartData = { 
            labels: deepClone(previousState.labels), 
            data: deepClone(previousState.chartData) 
        };
        this.cachedChartMode = previousState.chartMode;
        return deepClone(previousState);
    }
    clearCache() {
        this.cachedChartData = null;
        this.cachedChartMode = null;
        this.chartHistoryStack = [];
    }
    getHistoryLength() {
        return this.chartHistoryStack.length;
    }
    hasPreviousState() {
        return this.chartHistoryStack.length > 1;
    }
    getCachedChartData() {
        return this.cachedChartData ? deepClone(this.cachedChartData) : null;
    }
    getCachedChartMode() {
        return this.cachedChartMode;
    }
    setCachedChartData(data) {
        this.cachedChartData = deepClone(data);
    }
    setCachedChartMode(mode) {
        this.cachedChartMode = mode;
    }
}
const salesCategoryState = new SalesCategoryState();
export default salesCategoryState;