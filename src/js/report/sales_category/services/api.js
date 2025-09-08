/**
 * @fileoverview API Service untuk laporan penjualan kategori
 * @description Mengelola semua request ke backend API
 */

import { API_ENDPOINTS } from '../config/constants.js';

/**
 * Class untuk mengelola API calls
 */
class SalesCategoryAPI {
    
    /**
     * Base method untuk melakukan fetch request
     * @private
     * @param {string} url - URL endpoint
     * @param {Object} options - Options untuk fetch
     * @returns {Promise<Object>} Response data
     */
    async _fetchData(url, options = {}) {
        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    ...options.headers
                },
                ...options
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();
            
            if (data.status !== 'success') {
                throw new Error(data.message || 'Request failed');
            }

            return data;
        } catch (error) {
            console.error('API request failed:', error);
            throw error;
        }
    }

    /**
     * Fetch data untuk chart awal (semua kategori)
     * @param {Object} params - Parameter request
     * @param {string} params.storeCode - Kode store/cabang
     * @param {string} params.startDate - Tanggal mulai
     * @param {string} params.endDate - Tanggal akhir
     * @param {string} params.query - Query type
     * @returns {Promise<Object>} Response data
     */
    async fetchInitialData({ storeCode, startDate, endDate, query = 'allCate' }) {
        const payload = {
            kd_store: storeCode,
            start_date: startDate,
            end_date: endDate,
            query: query
        };

        
        const data = await this._fetchData(API_ENDPOINTS.SALES_CATEGORY, {
            body: JSON.stringify(payload)
        });
        return data;
    }

    /**
     * Fetch data kategori berdasarkan filter
     * @param {Object} params - Parameter request
     * @param {string} params.storeCode - Kode store/cabang
     * @param {string} params.startDate - Tanggal mulai
     * @param {string} params.endDate - Tanggal akhir
     * @param {string} params.query - Query/kategori yang dipilih
     * @param {string} params.filter - Filter sorting
     * @returns {Promise<Object>} Response data
     */
    async fetchCategoryData({ storeCode, startDate, endDate, query, filter }) {
        const payload = {
            kd_store: storeCode,
            start_date: startDate,
            end_date: endDate,
            query: query,
            filter: filter
        };

        
        const url = `${API_ENDPOINTS.SALES_CATEGORY}?filter=${encodeURIComponent(filter)}`;
        const data = await this._fetchData(url, {
            body: JSON.stringify(payload)
        });

        return data;
    }

    /**
     * Fetch data detail supplier berdasarkan kode supplier
     * @param {Object} params - Parameter request
     * @param {string} params.storeCode - Kode store/cabang
     * @param {string} params.startDate - Tanggal mulai
     * @param {string} params.endDate - Tanggal akhir
     * @param {string} params.supplierCode - Kode supplier
     * @param {string} params.category - Kategori produk
     * @param {string} params.filter - Filter sorting
     * @returns {Promise<Object>} Response data
     */
    async fetchSupplierDetailData({ storeCode, startDate, endDate, supplierCode, category, filter }) {
        const payload = {
            kategori: category,
            kode_supp: supplierCode,
            kd_store: storeCode,
            start_date: startDate,
            end_date: endDate,
            filter: filter
        };

        
        const url = `${API_ENDPOINTS.SALES_CATEGORY}?filter=${encodeURIComponent(filter)}`;
        const data = await this._fetchData(url, {
            body: JSON.stringify(payload)
        });

        return data;
    }

    /**
     * Handle API error dengan user-friendly message
     * @param {Error} error - Error object
     * @returns {string} User-friendly error message
     */
    handleError(error) {
        console.error('API Error:', error);
        
        if (error.name === 'NetworkError' || error.message.includes('fetch')) {
            return 'Koneksi bermasalah. Silakan periksa internet Anda.';
        }
        
        if (error.message.includes('404')) {
            return 'Endpoint tidak ditemukan. Hubungi administrator.';
        }
        
        if (error.message.includes('500')) {
            return 'Terjadi kesalahan server. Coba lagi nanti.';
        }
        
        return error.message || 'Terjadi kesalahan yang tidak diketahui.';
    }
}

// Create singleton instance
const salesCategoryAPI = new SalesCategoryAPI();

export default salesCategoryAPI;
