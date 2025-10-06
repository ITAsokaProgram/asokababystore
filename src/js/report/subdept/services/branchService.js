/**
 * @fileoverview Branch Service untuk laporan sub departemen
 * @description Service untuk mengambil dan mengelola data kode cabang secara dinamis dari API
 */

import { API_ENDPOINTS } from '../config/constants.js';

/**
 * Class untuk mengelola branch/cabang operations
 * @class BranchService
 */
class BranchService {
    constructor() {
        this.storeCodes = null;
        this.allBranchCodes = null;
        this.isLoaded = false;
        this.loadPromise = null;
    }

    /**
     * Get authorization token dari cookie
     * @private
     * @returns {string|null} Token atau null jika tidak ada
     */
    _getAuthToken() {
        try {
            // Dynamic access ke document.cookie untuk menghindari DOM ready issue
            const value = document.cookie.match('(^|;)\\s*token\\s*=\\s*([^;]+)');
            return value ? value[2] : null;
        } catch (error) {
            console.warn("⚠️ Cannot access cookies:", error.message);
            return null;
        }
    }

    /**
     * Fetch store codes dari API
     * @private
     * @returns {Promise<Object>} Store codes mapping
     */
    async _fetchStoreCodes() {
        try {
            

            // Get token saat method dipanggil, bukan saat import
            const token = this._getAuthToken();
            const headers = {
                'Content-Type': 'application/json',
            };

            // Add authorization header jika token tersedia
            if (token) {
                headers.Authorization = `Bearer ${token}`;
                
            } else {
                console.warn('⚠️ No authorization token found, proceeding without auth');
            }

            const response = await fetch(API_ENDPOINTS.BRANCH_CODES, {
                method: 'GET',
                headers: headers,
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();

            // Validasi response structure
            if (!data || !data.data || !Array.isArray(data.data)) {
                throw new Error('Invalid API response structure');
            }

            // Convert array response ke object mapping
            const storeCodesMapping = {};
            data.data.forEach((item) => {
                // Adjust field names berdasarkan API response structure
                // Misal: { nama_cabang: "ABIN", store: "1502" }
                if (item.nama_cabang && item.store) {
                    storeCodesMapping[item.nama_cabang] = item.store;
                }
                // Atau struktur lain sesuai API response
                else if (item.nama_cabang && item.store) {
                    storeCodesMapping[item.nama_cabang] = item.store;
                }
                // Fallback untuk struktur yang berbeda
                else if (item.name && item.code) {
                    storeCodesMapping[item.name] = item.code;
                }
            });

            if (Object.keys(storeCodesMapping).length === 0) {
                throw new Error('No valid store codes found in API response');
            }

            
            return storeCodesMapping;

        } catch (error) {
            console.error('❌ Failed to fetch store codes from API:', error);
            throw error;
        }
    }

    /**
     * Load store codes (from API)
     * @param {boolean} forceRefresh - Force refresh dari API
     * @returns {Promise<Object>} Store codes mapping
     */
    async loadStoreCodes(forceRefresh = false) {
        // Jika sudah loaded dan tidak force refresh, return cached data
        if (this.isLoaded && !forceRefresh && this.storeCodes) {
            return this.storeCodes;
        }

        // Jika sedang loading, tunggu promise yang ada
        if (this.loadPromise && !forceRefresh) {
            return await this.loadPromise;
        }

        // Create new load promise
        this.loadPromise = this._performLoad();
        return await this.loadPromise;
    }

    /**
     * Perform the actual loading
     * @private
     * @returns {Promise<Object>} Store codes mapping
     */
    async _performLoad() {
        try {
            // Fetch from API
            this.storeCodes = await this._fetchStoreCodes();
            this.allBranchCodes = Object.values(this.storeCodes);
            this.isLoaded = true;

            
            return this.storeCodes;

        } catch (error) {
            console.error('❌ Failed to load store codes from API:', error.message);
            
            // Reset state on failure
            this.storeCodes = null;
            this.allBranchCodes = null;
            this.isLoaded = false;
            
            // Re-throw error instead of using fallback
            throw new Error(`Cannot load branch data: ${error.message}`);

        } finally {
            this.loadPromise = null;
        }
    }

    /**
     * Get store codes mapping
     * @param {boolean} forceRefresh - Force refresh dari API
     * @returns {Promise<Object>} Store codes mapping
     */
    async getStoreCodes(forceRefresh = false) {
        return await this.loadStoreCodes(forceRefresh);
    }

    /**
     * Get all branch codes as array
     * @param {boolean} forceRefresh - Force refresh dari API
     * @returns {Promise<Array>} Array of all store codes
     */
    async getAllBranchCodes(forceRefresh = false) {
        await this.loadStoreCodes(forceRefresh);
        return this.allBranchCodes || [];
    }

    /**
     * Get store code untuk branch tertentu
     * @param {string} branchName - Nama cabang
     * @param {boolean} forceRefresh - Force refresh dari API
     * @returns {Promise<string|null>} Store code atau null jika tidak ditemukan
     */
    async getStoreCodeForBranch(branchName, forceRefresh = false) {
        const storeCodes = await this.loadStoreCodes(forceRefresh);
        return storeCodes[branchName] || null;
    }

    /**
     * Get branch names as array
     * @param {boolean} forceRefresh - Force refresh dari API
     * @returns {Promise<Array>} Array of branch names
     */
    async getBranchNames(forceRefresh = false) {
        const storeCodes = await this.loadStoreCodes(forceRefresh);
        return Object.keys(storeCodes);
    }

    /**
     * Check if branch exists
     * @param {string} branchName - Nama cabang
     * @param {boolean} forceRefresh - Force refresh dari API
     * @returns {Promise<boolean>} True jika branch exists
     */
    async branchExists(branchName, forceRefresh = false) {
        const storeCodes = await this.loadStoreCodes(forceRefresh);
        return storeCodes.hasOwnProperty(branchName);
    }

    /**
     * Refresh store codes dari API
     * @returns {Promise<Object>} Updated store codes
     */
    async refreshFromAPI() {
        
        return await this.loadStoreCodes(true);
    }

    /**
     * Get loading status
     * @returns {boolean} Loading status
     */
    isLoading() {
        return this.loadPromise !== null;
    }

    /**
     * Get loaded status
     * @returns {boolean} Loaded status
     */
    isDataLoaded() {
        return this.isLoaded;
    }

    /**
     * Get current data source info
     * @returns {Object} Data source information
     */
    getDataSourceInfo() {
        return {
            isLoaded: this.isLoaded,
            isLoading: this.isLoading(),
            totalBranches: this.storeCodes ? Object.keys(this.storeCodes).length : 0,
            dataSource: this.isLoaded ? "api" : "none",
        };
    }

    /**
     * Clear cached data
     */
    clearCache() {
        this.storeCodes = null;
        this.allBranchCodes = null;
        this.isLoaded = false;
        this.loadPromise = null;
        
    }

    /**
     * Get formatted options untuk select dropdown
     * @param {boolean} includeAll - Include "SEMUA CABANG" option
     * @param {boolean} forceRefresh - Force refresh dari API
     * @returns {Promise<Array>} Array of select options
     */
    async getSelectOptions(includeAll = true, forceRefresh = false) {
        const storeCodes = await this.loadStoreCodes(forceRefresh);
        const options = [];

        if (includeAll) {
            options.push({
                value: "SEMUA CABANG",
                text: "SEMUA CABANG",
                isAll: true,
            });
        }

        Object.keys(storeCodes).forEach((branchName) => {
            options.push({
                value: branchName,
                text: branchName,
                storeCode: storeCodes[branchName],
                isAll: false,
            });
        });

        return options;
    }

    /**
     * Initialize branch service (untuk load initial data)
     * @returns {Promise<boolean>} Success status
     */
    async initialize() {
        try {
            
            await this.loadStoreCodes();
            
            return true;
        } catch (error) {
            console.warn('⚠️ Branch Service initialization failed:', error.message);
            return false;
        }
    }
}

// Create singleton instance
const branchService = new BranchService();

export default branchService;
