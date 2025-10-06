/**
 * @fileoverview State Management untuk laporan sub departemen
 * @description Mengelola state aplikasi seperti data cache, pagination, dan status
 */

import { STORAGE_KEYS, DEFAULTS, QUERY_TYPES } from '../config/constants.js';
import { deepClone } from './formatters.js';

/**
 * Class untuk mengelola state aplikasi
 * @class StateManager
 */
class StateManager {
    constructor() {
        this.init();
    }

    /**
     * Inisialisasi state
     * @private
     */
    init() {
        // Data cache
        this.tableDataCache = [];
        this.chartDataGlobal = [];
        this.dataCharBar = [];
        
        // Pagination
        this.currentPage = DEFAULTS.CURRENT_PAGE;
        this.totalPages = DEFAULTS.TOTAL_PAGES;
        
        // Status flags
        this.isSubdeptActive = false;
        this.isPromo = false;
        
        // Chart instances
        this.chartInstance = null;
        this.barChart = null;
        this.pieChart = null;
        
        // Active query type
        this.activeQueryType = this.getStoredQueryType();
        
        
    }

    /**
     * Get active query type dari session storage
     * @returns {string} Active query type
     */
    getStoredQueryType() {
        return sessionStorage.getItem(STORAGE_KEYS.ACTIVE_QUERY_TYPE) || DEFAULTS.ACTIVE_QUERY_TYPE;
    }

    /**
     * Set active query type
     * @param {string} queryType - Query type yang akan diset
     */
    setActiveQueryType(queryType) {
        this.activeQueryType = queryType;
        sessionStorage.setItem(STORAGE_KEYS.ACTIVE_QUERY_TYPE, queryType);
        
    }

    /**
     * Get current active query type
     * @returns {string} Current active query type
     */
    getActiveQueryType() {
        return this.activeQueryType;
    }

    /**
     * Update table data cache
     * @param {Array} data - Data tabel yang akan di-cache
     */
    updateTableCache(data) {
        this.tableDataCache = Array.isArray(data) ? deepClone(data) : [];
        
        // Simpan ke localStorage untuk persistence
        localStorage.setItem(STORAGE_KEYS.SALES_TABLE_ORIGINAL, JSON.stringify(this.tableDataCache));
        
        
    }

    /**
     * Get table data dari cache
     * @param {boolean} fromStorage - Ambil dari localStorage jika true
     * @returns {Array} Data tabel
     */
    getTableCache(fromStorage = false) {
        if (fromStorage) {
            try {
                const stored = localStorage.getItem(STORAGE_KEYS.SALES_TABLE_ORIGINAL);
                return stored ? JSON.parse(stored) : [];
            } catch (error) {
                console.warn('⚠️ Error loading dari localStorage:', error);
                return [];
            }
        }
        return deepClone(this.tableDataCache);
    }

    /**
     * Update chart data cache
     * @param {Array} data - Data chart yang akan di-cache
     */
    updateChartCache(data) {
        this.chartDataGlobal = Array.isArray(data) ? deepClone(data) : [];
        
    }

    /**
     * Get chart data dari cache
     * @returns {Array} Data chart
     */
    getChartCache() {
        return deepClone(this.chartDataGlobal);
    }

    /**
     * Update bar chart data cache
     * @param {Array} data - Data bar chart yang akan di-cache
     */
    updateBarChartCache(data) {
        this.dataCharBar = Array.isArray(data) ? deepClone(data) : [];
        
        // Simpan ke localStorage untuk bar chart
        localStorage.setItem(STORAGE_KEYS.CHART_BART, JSON.stringify(this.dataCharBar));
        
        
    }

    /**
     * Get bar chart data dari cache
     * @param {boolean} fromStorage - Ambil dari localStorage jika true
     * @returns {Array} Data bar chart
     */
    getBarChartCache(fromStorage = false) {
        if (fromStorage) {
            try {
                const stored = localStorage.getItem(STORAGE_KEYS.CHART_BART);
                return stored ? JSON.parse(stored) : [];
            } catch (error) {
                console.warn('⚠️ Error loading bar chart dari localStorage:', error);
                return [];
            }
        }
        return deepClone(this.dataCharBar);
    }

    /**
     * Update pagination info
     * @param {number} current - Current page
     * @param {number} total - Total pages
     */
    updatePagination(current, total) {
        this.currentPage = current || DEFAULTS.CURRENT_PAGE;
        this.totalPages = total || DEFAULTS.TOTAL_PAGES;
        
     
    }

    /**
     * Get pagination info
     * @returns {Object} Pagination info
     */
    getPagination() {
        return {
            currentPage: this.currentPage,
            totalPages: this.totalPages
        };
    }

    /**
     * Set subdept active status
     * @param {boolean} active - Status aktif
     */
    setSubdeptActive(active) {
        this.isSubdeptActive = !!active;
        
    }

    /**
     * Get subdept active status
     * @returns {boolean} Status aktif
     */
    isSubdeptActiveStatus() {
        return this.isSubdeptActive;
    }

    /**
     * Set promo status
     * @param {boolean} active - Status promo
     */
    setPromoStatus(active) {
        this.isPromo = !!active;
        
    }

    /**
     * Get promo status
     * @returns {boolean} Status promo
     */
    getPromoStatus() {
        return this.isPromo;
    }

    /**
     * Set chart instances
     * @param {Object} instances - Chart instances
     */
    setChartInstances(instances) {
        if (instances.barChart) {
            this.barChart = instances.barChart;
        }
        if (instances.pieChart) {
            this.pieChart = instances.pieChart;
        }
        if (instances.chartInstance) {
            this.chartInstance = instances.chartInstance;
        }
        
        
    }

    /**
     * Get chart instances
     * @returns {Object} Chart instances
     */
    getChartInstances() {
        return {
            barChart: this.barChart,
            pieChart: this.pieChart,
            chartInstance: this.chartInstance
        };
    }

    /**
     * Reset semua state ke default
     */
    reset() {
        this.tableDataCache = [];
        this.chartDataGlobal = [];
        this.dataCharBar = [];
        this.currentPage = DEFAULTS.CURRENT_PAGE;
        this.totalPages = DEFAULTS.TOTAL_PAGES;
        this.isSubdeptActive = false;
        this.isPromo = false;
        this.activeQueryType = DEFAULTS.ACTIVE_QUERY_TYPE;
        
        // Clear localStorage
        localStorage.removeItem(STORAGE_KEYS.SALES_TABLE_ORIGINAL);
        localStorage.removeItem(STORAGE_KEYS.CHART_BART);
        sessionStorage.removeItem(STORAGE_KEYS.ACTIVE_QUERY_TYPE);
        
        
    }

    /**
     * Get current state summary
     * @returns {Object} State summary
     */
    getStateSummary() {
        return {
            tableDataCount: this.tableDataCache.length,
            chartDataCount: this.chartDataGlobal.length,
            barChartDataCount: this.dataCharBar.length,
            pagination: this.getPagination(),
            isSubdeptActive: this.isSubdeptActive,
            isPromo: this.isPromo,
            activeQueryType: this.activeQueryType,
            hasChartInstances: {
                barChart: !!this.barChart,
                pieChart: !!this.pieChart,
                chartInstance: !!this.chartInstance
            }
        };
    }
}

// Export singleton instance
const stateManager = new StateManager();
export default stateManager;
