/**
 * @fileoverview API Service untuk laporan sub departemen
 * @description Mengelola semua komunikasi dengan backend API
 */

import { API_ENDPOINTS } from '../config/constants.js';

/**
 * Service class untuk mengelola API calls
 * @class ApiService
 */
class ApiService {
    /**
     * Fetch data dari subdepartemen endpoint
     * @param {FormData} formData - Form data yang akan dikirim
     * @param {string} filter - Filter untuk sorting data
     * @returns {Promise<Object>} Response dari server
     */
    async fetchSubdeptData(formData, filter = '') {
        try {
            
            

            const url = filter 
                ? `${API_ENDPOINTS.SUBDEPT_DATA}?filter=${encodeURIComponent(filter)}`
                : API_ENDPOINTS.SUBDEPT_DATA;

            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            });

            if (!response.ok) {
                throw new Error(`HTTP Error: ${response.status} ${response.statusText}`);
            }

            const data = await response.json();
            

            return this._validateResponse(data);

        } catch (error) {
            console.error('❌ Error saat fetch data:', error);
            throw new Error(`Gagal mengambil data: ${error.message}`);
        }
    }

    /**
     * Fetch data menggunakan jQuery AJAX (untuk kompatibilitas dengan existing code)
     * @param {FormData} formData - Form data yang akan dikirim  
     * @param {string} filter - Filter untuk sorting data
     * @param {string} queryType - Jenis query (query1, query2, query3)
     * @returns {Promise<Object>} Response dari server
     */
    async fetchDataWithAjax(formData, filter = '', queryType = 'query1') {
        return new Promise((resolve, reject) => {
            try {
                
                

                // Tambahkan query_type ke formData
                formData.append('query_type', queryType);

                const url = filter 
                    ? `${API_ENDPOINTS.SUBDEPT_DATA}?filter=${encodeURIComponent(filter)}`
                    : API_ENDPOINTS.SUBDEPT_DATA;

                $.ajax({
                    url: url,
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: (response) => {
                        
                        
                        let jsonResponse;
                        try {
                            jsonResponse = typeof response === 'string' 
                                ? JSON.parse(response) 
                                : response;
                            
                        } catch (error) {
                            console.error('❌ Gagal parsing JSON:', error, response);
                            reject(new Error('Invalid JSON response'));
                            return;
                        }

                        try {
                            const validatedResponse = this._validateResponse(jsonResponse);
                            resolve(validatedResponse);
                        } catch (validationError) {
                            reject(validationError);
                        }
                    },
                    error: (xhr, status, error) => {
                        console.error('❌ AJAX Error:', { xhr, status, error });
                        reject(new Error(`AJAX Error: ${error}`));
                    }
                });

            } catch (error) {
                console.error('❌ Error dalam AJAX setup:', error);
                reject(error);
            }
        });
    }

    /**
     * Validasi response dari server
     * @private
     * @param {Object} response - Response yang akan divalidasi
     * @returns {Object} Response yang sudah divalidasi
     */
    _validateResponse(response) {
        if (!response) {
            throw new Error('Response kosong dari server');
        }

        if (response.status !== 'success') {
            const message = response.message || 'Unknown error dari server';
            throw new Error(`Server error: ${message}`);
        }

        // Validasi struktur data yang diperlukan
        const requiredFields = ['status'];
        const missingFields = requiredFields.filter(field => !(field in response));
        
        if (missingFields.length > 0) {
            console.warn('⚠️ Missing fields dalam response:', missingFields);
        }

        return response;
    }

    /**
     * Create FormData dari form element
     * @param {string} formId - ID dari form element
     * @param {Object} additionalData - Data tambahan yang akan ditambahkan
     * @returns {FormData} FormData object
     */
    createFormData(formId, additionalData = {}) {
        const form = document.getElementById(formId);
        if (!form) {
            throw new Error(`Form dengan ID '${formId}' tidak ditemukan`);
        }

        const formData = new FormData(form);
        
        // Tambahkan data tambahan
        Object.entries(additionalData).forEach(([key, value]) => {
            if (value !== null && value !== undefined) {
                formData.append(key, value);
            }
        });

        // Selalu tambahkan ajax flag
        formData.append('ajax', 'true');

        return formData;
    }

    /**
     * Get value dari element dengan error handling
     * @param {string} elementId - ID dari element
     * @returns {string} Value dari element atau empty string
     */
    getElementValue(elementId) {
        const element = document.getElementById(elementId);
        if (!element) {
            console.warn(`⚠️ Element dengan ID '${elementId}' tidak ditemukan`);
            return '';
        }
        return element.value || '';
    }

    /**
     * Get selected text dari select element
     * @param {string} selectId - ID dari select element
     * @returns {string} Selected text atau empty string
     */
    getSelectedText(selectId) {
        const select = document.getElementById(selectId);
        if (!select || select.selectedIndex === -1) {
            console.warn(`⚠️ Select dengan ID '${selectId}' tidak ditemukan atau tidak ada pilihan`);
            return '';
        }
        
        return select.options[select.selectedIndex].text || '';
    }

    /**
     * Build query parameters untuk URL
     * @param {Object} params - Object berisi parameter
     * @returns {string} Query string
     */
    buildQueryString(params) {
        const filteredParams = Object.entries(params)
            .filter(([key, value]) => value !== null && value !== undefined && value !== '')
            .map(([key, value]) => `${encodeURIComponent(key)}=${encodeURIComponent(value)}`)
            .join('&');
        
        return filteredParams ? `?${filteredParams}` : '';
    }
}

// Export singleton instance
const apiService = new ApiService();
export default apiService;
