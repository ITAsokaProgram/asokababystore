/**
 * @fileoverview Branch Service untuk Sales Ratio Report
 * @description Mengelola operasi yang berkaitan dengan cabang dan store codes
 */

import { STORE_CODES } from '../config/constants.js';

/**
 * Class untuk mengelola branch operations
 * @class BranchService
 */
class BranchService {
    constructor() {
        this.storeCodes = STORE_CODES;
        this.allCabang = Object.values(STORE_CODES);
        this.currentStoreCode = '';
    }

    /**
     * Get store code berdasarkan nama cabang
     * @param {string} branchName - Nama cabang
     * @returns {string} Store code
     */
    getStoreCode(branchName) {
        if (branchName === 'SEMUA CABANG') {
            return this.allCabang.join(',');
        }
        return this.storeCodes[branchName] || '';
    }

    /**
     * Set current store code
     * @param {string} branchName - Nama cabang
     */
    setCurrentStoreCode(branchName) {
        this.currentStoreCode = this.getStoreCode(branchName);
    }

    /**
     * Get current store code
     * @returns {string} Current store code
     */
    getCurrentStoreCode() {
        return this.currentStoreCode;
    }

    /**
     * Get all branch names
     * @returns {Array<string>} Array of branch names
     */
    getAllBranches() {
        return Object.keys(this.storeCodes);
    }

    /**
     * Get all store codes
     * @returns {Array<string>} Array of store codes
     */
    getAllStoreCodes() {
        return this.allCabang;
    }

    /**
     * Check if branch name exists
     * @param {string} branchName - Nama cabang
     * @returns {boolean} Exists status
     */
    branchExists(branchName) {
        return branchName === 'SEMUA CABANG' || this.storeCodes.hasOwnProperty(branchName);
    }

    /**
     * Get branch info
     * @param {string} branchName - Nama cabang
     * @returns {Object} Branch information
     */
    getBranchInfo(branchName) {
        if (branchName === 'SEMUA CABANG') {
            return {
                name: branchName,
                storeCode: this.allCabang.join(','),
                isAll: true,
                storeCount: this.allCabang.length
            };
        }

        return {
            name: branchName,
            storeCode: this.storeCodes[branchName] || '',
            isAll: false,
            exists: this.branchExists(branchName)
        };
    }

    /**
     * Get select options for dropdown
     * @param {boolean} includeAll - Include "SEMUA CABANG" option
     * @returns {Array<Object>} Select options array
     */
    getSelectOptions(includeAll = true) {
        const options = [];

        if (includeAll) {
            options.push({
                value: 'SEMUA CABANG',
                text: 'SEMUA CABANG',
                storeCode: this.allCabang.join(','),
                isAll: true
            });
        }

        Object.keys(this.storeCodes).forEach(branchName => {
            options.push({
                value: branchName,
                text: branchName,
                storeCode: this.storeCodes[branchName],
                isAll: false
            });
        });

        return options;
    }

    /**
     * Initialize branch service dengan default branch
     * @param {string} defaultBranch - Default branch name
     */
    initialize(defaultBranch = 'SEMUA CABANG') {
        this.setCurrentStoreCode(defaultBranch);
    }

    /**
     * Update branch selection
     * @param {string} selectedBranch - Selected branch name
     * @returns {Object} Updated branch info
     */
    updateBranchSelection(selectedBranch) {
        this.setCurrentStoreCode(selectedBranch);
        
        const branchInfo = this.getBranchInfo(selectedBranch);
        
        // Trigger custom event for branch change
        const event = new CustomEvent('branchChanged', {
            detail: {
                branchName: selectedBranch,
                storeCode: this.currentStoreCode,
                branchInfo: branchInfo
            }
        });
        
        document.dispatchEvent(event);
        
        return branchInfo;
    }

    /**
     * Get summary untuk debugging
     * @returns {Object} Branch service summary
     */
    getSummary() {
        return {
            totalBranches: Object.keys(this.storeCodes).length,
            totalStoreCodes: this.allCabang.length,
            currentStoreCode: this.currentStoreCode,
            branches: this.getAllBranches(),
            storeCodes: this.storeCodes
        };
    }
}

// Create singleton instance
const branchService = new BranchService();

export default branchService;
