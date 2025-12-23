/**
 * @fileoverview API Service untuk Sales Ratio Report
 * @description Mengelola semua komunikasi dengan API endpoints
 */

import { API_ENDPOINTS } from '../config/constants.js';

/**
 * Class untuk mengelola API calls
 * @class ApiService
 */
class ApiService {
    constructor() {
        this.defaultHeaders = {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        };
    }

    /**
     * Get CSRF token dari form
     * @private
     * @returns {string|null} CSRF token
     */
    _getCSRFToken() {
        const tokenElement = document.querySelector("[name='csrf_token']");
        return tokenElement ? tokenElement.value : null;
    }

    /**
     * Create FormData dengan CSRF token
     * @private
     * @param {Object} data - Data object untuk form
     * @returns {FormData} FormData dengan CSRF token
     */
    _createFormData(data = {}) {
        const formData = new FormData();
        
        // Add CSRF token
        const csrfToken = this._getCSRFToken();
        if (csrfToken) {
            formData.append('csrf_token', csrfToken);
        }
        
        // Add ajax flag
        formData.append('ajax', true);
        
        // Add other data
        Object.entries(data).forEach(([key, value]) => {
            if (value !== null && value !== undefined) {
                formData.append(key, value);
            }
        });
        
        return formData;
    }

    /**
     * Fetch suppliers dari API
     * @param {string} storeCode - Kode store
     * @returns {Promise<Array>} Array of suppliers
     */
    async fetchSuppliers(storeCode) {
        try {
            
            
            const response = await fetch(`${API_ENDPOINTS.GET_SUPPLIERS}?kode=${storeCode}`, {
                method: 'GET',
                headers: this.defaultHeaders
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();
            
            // Transform data to only include kode_supp
            const suppliers = data.map(item => item.kode_supp.trim());
            
            
            return suppliers;
            
        } catch (error) {
            console.error('❌ Error fetching suppliers:', error);
            throw new Error(`Gagal mengambil data supplier: ${error.message}`);
        }
    }

    /**
     * Send data untuk bar chart
     * @param {Object} chartData - Data untuk chart
     * @param {string} filter - Filter sorting
     * @returns {Promise<Object>} Response data
     */
    async sendBarChartData(chartData, filter) {
        try {
            
            
            const formData = this._createFormData({
                kode_supp1: chartData.kode_supp1 || '',
                kode_supp2: chartData.kode_supp2 || '',
                kode_supp3: chartData.kode_supp3 || '',
                kode_supp4: chartData.kode_supp4 || '',
                kode_supp5: chartData.kode_supp5 || '',
                kd_store: chartData.kd_store,
                start_date: chartData.start_date,
                end_date: chartData.end_date,
                filter: filter
            });

            const response = await fetch(`${API_ENDPOINTS.RATIO_BAR_PROCESS}?filter=${filter}`, {
                method: 'POST',
                body: formData
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();
            
            
            return data;
            
        } catch (error) {
            console.error('❌ Error sending bar chart data:', error);
            throw new Error(`Gagal mengirim data chart: ${error.message}`);
        }
    }

    /**
     * Send data untuk table
     * @param {Object} tableData - Data untuk table
     * @param {number} page - Page number
     * @param {string} filter - Filter sorting
     * @returns {Promise<Object>} Response data
     */
    async sendTableData(tableData, page = 1, filter = 'Total') {
        try {
            
            
            const formData = this._createFormData({
                selectKode: tableData.selectKode,
                kd_store: tableData.kd_store,
                start_date: tableData.start_date,
                end_date: tableData.end_date,
                page: page,
                filter: filter
            });

            const response = await fetch(`${API_ENDPOINTS.RATIO_TABLE_PROCESS}?filter=${filter}`, {
                method: 'POST',
                body: formData
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();
            
            
            return data;
            
        } catch (error) {
            console.error('❌ Error sending table data:', error);
            throw new Error(`Gagal mengirim data table: ${error.message}`);
        }
    }

    /**
     * Generic AJAX request using jQuery (for compatibility)
     * @param {Object} options - jQuery AJAX options
     * @returns {Promise} jQuery promise
     */
    ajaxRequest(options) {
        const defaultOptions = {
            dataType: 'json',
            processData: false,
            contentType: false,
            timeout: 30000
        };

        return $.ajax({
            ...defaultOptions,
            ...options
        });
    }

    /**
     * Check if API endpoint is reachable
     * @param {string} endpoint - API endpoint to test
     * @returns {Promise<boolean>} Reachability status
     */
    async checkEndpoint(endpoint) {
        try {
            const response = await fetch(endpoint, {
                method: 'HEAD',
                headers: this.defaultHeaders
            });
            return response.ok;
        } catch (error) {
            console.warn(`⚠️ Endpoint ${endpoint} not reachable:`, error.message);
            return false;
        }
    }

    /**
     * Get network status
     * @returns {Object} Network status info
     */
    getNetworkStatus() {
        return {
            online: navigator.onLine,
            connection: navigator.connection ? {
                effectiveType: navigator.connection.effectiveType,
                downlink: navigator.connection.downlink,
                rtt: navigator.connection.rtt
            } : null
        };
    }
}

// Create singleton instance
const apiService = new ApiService();

export default apiService;
