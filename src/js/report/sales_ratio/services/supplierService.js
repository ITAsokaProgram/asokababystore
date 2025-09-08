/**
 * @fileoverview Supplier Service untuk Sales Ratio Report
 * @description Mengelola operasi yang berkaitan dengan supplier data dan suggestions
 */

import { ELEMENT_IDS, CSS_CLASSES, MAX_SUPPLIERS } from '../config/constants.js';
import apiService from './api.js';
import branchService from './branchService.js';

/**
 * Class untuk mengelola supplier operations
 * @class SupplierService
 */
class SupplierService {
    constructor() {
        this.supplierCache = new Map();
        this.loadedInputs = new Set();
    }

    /**
     * Get suppliers untuk store code tertentu
     * @param {string} storeCode - Store code
     * @param {boolean} useCache - Use cached data if available
     * @returns {Promise<Array>} Array of supplier codes
     */
    async getSuppliers(storeCode, useCache = true) {
        try {
            // Check cache first if useCache is true
            if (useCache && this.supplierCache.has(storeCode)) {
                console.log('📦 Using cached suppliers for store:', storeCode);
                return this.supplierCache.get(storeCode);
            }

            // Fetch fresh data
            const suppliers = await apiService.fetchSuppliers(storeCode);
            
            // Cache the result
            this.supplierCache.set(storeCode, suppliers);
            
            return suppliers;
            
        } catch (error) {
            console.error('❌ Error getting suppliers:', error);
            return [];
        }
    }

    /**
     * Load suppliers untuk input field tertentu
     * @param {jQuery} inputField - Input field element
     * @returns {Promise<Array>} Array of suppliers
     */
    async loadSuppliersForInput(inputField) {
        try {
            const inputId = inputField.attr('id');
            
            // Check if already loaded
            if (this.loadedInputs.has(inputId)) {
                return inputField.data('suppliers') || [];
            }

            const storeCode = branchService.getCurrentStoreCode();
            if (!storeCode) {
                throw new Error('Store code tidak ditemukan');
            }

            const suppliers = await this.getSuppliers(storeCode);
            
            // Mark as loaded and store data
            this.loadedInputs.add(inputId);
            inputField.data('loaded', true);
            inputField.data('suppliers', suppliers);
            
            console.log('✅ Suppliers loaded for input:', inputId);
            return suppliers;
            
        } catch (error) {
            console.error('❌ Error loading suppliers for input:', error);
            return [];
        }
    }

    /**
     * Filter suppliers berdasarkan keyword
     * @param {Array} suppliers - Array of suppliers
     * @param {string} keyword - Search keyword
     * @returns {Array} Filtered suppliers
     */
    filterSuppliers(suppliers, keyword) {
        if (!keyword || keyword.trim() === '') {
            return suppliers;
        }
        
        const lowerKeyword = keyword.toLowerCase();
        return suppliers.filter(supplier => 
            supplier.toLowerCase().includes(lowerKeyword)
        );
    }

    /**
     * Show suggestions dropdown untuk input
     * @param {jQuery} inputField - Input field element
     * @param {Array} suppliers - Array of suppliers to show
     */
    showSuggestions(inputField, suppliers) {
        // Remove existing suggestion box
        $('.suggestion-box').remove();

        const suggestionBox = $('<div>')
            .addClass(CSS_CLASSES.SUGGESTION_BOX);

        if (!Array.isArray(suppliers) || suppliers.length === 0) {
            $('<div>')
                .addClass('px-4 py-2 text-gray-500 text-center italic')
                .text('Supplier tidak ditemukan')
                .appendTo(suggestionBox);
        } else {
            suppliers.forEach(supplier => {
                $('<div>')
                    .addClass(CSS_CLASSES.SUGGESTION_ITEM)
                    .text(supplier)
                    .on('click', () => {
                        inputField.val(supplier);
                        $('.suggestion-box').remove();
                        
                        // Trigger change event
                        inputField.trigger('change');
                    })
                    .appendTo(suggestionBox);
            });
        }

        // Position dropdown
        this._positionSuggestionBox(inputField, suggestionBox);
        
        $('body').append(suggestionBox);
    }

    /**
     * Position suggestion box relative to input
     * @private
     * @param {jQuery} inputField - Input field element
     * @param {jQuery} suggestionBox - Suggestion box element
     */
    _positionSuggestionBox(inputField, suggestionBox) {
        const inputOffset = inputField.offset();
        
        suggestionBox.css({
            position: 'absolute',
            top: inputOffset.top + inputField.outerHeight(),
            left: inputOffset.left,
            width: inputField.outerWidth(),
            background: '#fff',
            borderRadius: '5px',
            border: '1px solid #ddd',
            boxShadow: '0px 4px 6px rgba(0, 0, 0, 0.1)',
            zIndex: 1000
        });
    }

    /**
     * Hide all suggestion boxes
     */
    hideSuggestions() {
        $('.suggestion-box').remove();
    }

    /**
     * Get supplier inputs yang visible
     * @returns {Array<jQuery>} Array of visible supplier inputs
     */
    getVisibleSupplierInputs() {
        const inputs = [];
        
        for (let i = 1; i <= MAX_SUPPLIERS; i++) {
            const input = $(`#${ELEMENT_IDS.KODE_SUPP_PREFIX}${i}`);
            if (input.length && input.is(':visible')) {
                inputs.push(input);
            }
        }
        
        return inputs;
    }

    /**
     * Get supplier values dari visible inputs
     * @returns {Array<string>} Array of supplier values
     */
    getSupplierValues() {
        const values = [];
        const inputs = this.getVisibleSupplierInputs();
        
        inputs.forEach(input => {
            const value = input.val().trim();
            if (value) {
                values.push(value);
            }
        });
        
        return values;
    }

    /**
     * Update supplier dropdown
     * @param {string} dropdownSelector - Dropdown selector
     */
    updateSupplierDropdown(dropdownSelector = `#${ELEMENT_IDS.SUPPLIER_DROPDOWN}`) {
        const dropdown = $(dropdownSelector);
        const values = this.getSupplierValues();
        
        // Clear existing options
        dropdown.empty();
        
        // Add new options
        values.forEach(value => {
            if (!this._isOptionExists(dropdown, value)) {
                dropdown.append(`<option value="${value}">${value}</option>`);
            }
        });
        
        console.log('📋 Supplier dropdown updated with', values.length, 'options');
    }

    /**
     * Check if option exists in dropdown
     * @private
     * @param {jQuery} dropdown - Dropdown element
     * @param {string} value - Option value to check
     * @returns {boolean} Exists status
     */
    _isOptionExists(dropdown, value) {
        return dropdown.find('option').filter(function() {
            return $(this).val() === value;
        }).length > 0;
    }

    /**
     * Show/hide supplier inputs berdasarkan ratio number
     * @param {number} ratioNumber - Number of inputs to show
     */
    showSupplierInputs(ratioNumber) {
        // Hide all inputs first
        $(`.${CSS_CLASSES.SUPPLIER_INPUT}`).hide();
        $('[id^="kode_supp"]').hide();
        
        if (isNaN(ratioNumber) || ratioNumber < 1) {
            console.log('🔒 All supplier inputs hidden');
            return;
        }
        
        // Show inputs up to ratioNumber
        for (let i = 1; i <= Math.min(ratioNumber, MAX_SUPPLIERS); i++) {
            const input = $(`#${ELEMENT_IDS.KODE_SUPP_PREFIX}${i}`);
            if (input.length) {
                input.show();
            }
        }
        
        console.log('👁️ Showing', ratioNumber, 'supplier inputs');
    }

    /**
     * Clear supplier cache
     * @param {string} storeCode - Specific store code to clear, or all if not provided
     */
    clearCache(storeCode = null) {
        if (storeCode) {
            this.supplierCache.delete(storeCode);
            console.log('🗑️ Cleared cache for store:', storeCode);
        } else {
            this.supplierCache.clear();
            this.loadedInputs.clear();
            console.log('🗑️ Cleared all supplier cache');
        }
    }

    /**
     * Reset loaded inputs status
     */
    resetLoadedInputs() {
        this.loadedInputs.clear();
        
        // Remove loaded data from inputs
        $(`.${CSS_CLASSES.SUPPLIER_INPUT}`).each(function() {
            const $input = $(this);
            $input.removeData('loaded');
            $input.removeData('suppliers');
        });
        
        console.log('🔄 Reset loaded inputs status');
    }

    /**
     * Get cache statistics
     * @returns {Object} Cache statistics
     */
    getCacheStats() {
        return {
            cachedStoreCodes: Array.from(this.supplierCache.keys()),
            totalCachedItems: this.supplierCache.size,
            loadedInputs: Array.from(this.loadedInputs)
        };
    }
}

// Create singleton instance
const supplierService = new SupplierService();

export default supplierService;
