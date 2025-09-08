/**
 * @fileoverview UI Manager untuk laporan penjualan kategori
 * @description Mengelola tampilan UI, loading states, dan notifikasi
 */

import { ELEMENT_IDS } from '../config/constants.js';

/**
 * Class untuk mengelola UI operations
 */
class UIManager {
    constructor() {
        this.elements = {};
        this.isInitialized = false;
    }

    /**
     * Initialize UI elements
     * @returns {boolean} Success status
     */
    initialize() {
        try {
            this.elements = {
                chartDiagram: document.getElementById(ELEMENT_IDS.CHART_DIAGRAM),
                wrapperTable: document.getElementById(ELEMENT_IDS.WRAPPER_TABLE),
                btnBack: document.getElementById(ELEMENT_IDS.BTN_BACK),
                btnSend: document.getElementById(ELEMENT_IDS.BTN_SEND),
                sortFilter: document.getElementById(ELEMENT_IDS.SORT_FILTER),
                sortFilter1: document.getElementById(ELEMENT_IDS.SORT_FILTER1),
                dateStart: document.getElementById(ELEMENT_IDS.DATE_START),
                dateEnd: document.getElementById(ELEMENT_IDS.DATE_END),
                branchSelect: document.getElementById(ELEMENT_IDS.BRANCH_SELECT)
            };

            // Check missing elements
            const missingElements = Object.entries(this.elements)
                .filter(([key, element]) => !element)
                .map(([key]) => key);

            if (missingElements.length > 0) {
                console.warn('Missing UI elements:', missingElements);
            }

            this.isInitialized = true;
            this._initializeDefaultStates();
            
            return true;
        } catch (error) {
            console.error('Failed to initialize UI Manager:', error);
            return false;
        }
    }

    /**
     * Set initial UI states
     * @private
     */
    _initializeDefaultStates() {
        this.hideElement(ELEMENT_IDS.WRAPPER_TABLE);
        this.hideElement(ELEMENT_IDS.BTN_BACK);
        this.hideElement(ELEMENT_IDS.SORT_FILTER);
        this.hideElement(ELEMENT_IDS.SORT_FILTER1);
        this.hideElement(ELEMENT_IDS.CHART_DIAGRAM);
    }

    /**
     * Show element
     * @param {string} elementId - Element ID
     * @param {string} displayType - CSS display type (default: 'block')
     */
    showElement(elementId, displayType = 'block') {
        const element = document.getElementById(elementId);
        if (element) {
            element.style.display = displayType;
        }
    }

    /**
     * Hide element
     * @param {string} elementId - Element ID
     */
    hideElement(elementId) {
        const element = document.getElementById(elementId);
        if (element) {
            element.style.display = 'none';
        }
    }

    /**
     * Toggle element visibility
     * @param {string} elementId - Element ID
     * @param {string} displayType - CSS display type when shown
     */
    toggleElement(elementId, displayType = 'block') {
        const element = document.getElementById(elementId);
        if (element) {
            if (element.style.display === 'none' || !element.style.display) {
                element.style.display = displayType;
            } else {
                element.style.display = 'none';
            }
        }
    }

    /**
     * Set UI state untuk mode early (initial chart)
     */
    setEarlyMode() {
        this.hideElement(ELEMENT_IDS.BTN_BACK);
        this.hideElement(ELEMENT_IDS.WRAPPER_TABLE);
        this.hideElement(ELEMENT_IDS.SORT_FILTER);
        this.hideElement(ELEMENT_IDS.SORT_FILTER1);
        this.showElement(ELEMENT_IDS.CHART_DIAGRAM);
        
    }

    /**
     * Set UI state untuk mode category
     */
    setCategoryMode() {
        this.showElement(ELEMENT_IDS.BTN_BACK);
        this.showElement(ELEMENT_IDS.WRAPPER_TABLE);
        this.showElement(ELEMENT_IDS.SORT_FILTER);
        this.hideElement(ELEMENT_IDS.SORT_FILTER1);
        this.showElement(ELEMENT_IDS.CHART_DIAGRAM);
        
    }

    /**
     * Set UI state untuk mode detail
     */
    setDetailMode() {
        this.showElement(ELEMENT_IDS.BTN_BACK);
        this.showElement(ELEMENT_IDS.WRAPPER_TABLE);
        this.hideElement(ELEMENT_IDS.SORT_FILTER);
        this.showElement(ELEMENT_IDS.SORT_FILTER1);
        this.showElement(ELEMENT_IDS.CHART_DIAGRAM);
        
    }

    /**
     * Show loading state
     * @param {string} message - Loading message (optional)
     */
    showLoading(message = 'Memuat data...') {
        // Implementation depends on your loading component
        if (typeof showProgressBar === 'function') {
            showProgressBar();
        }
        
    }

    /**
     * Hide loading state
     */
    hideLoading() {
        // Implementation depends on your loading component
        if (typeof completeProgressBar === 'function') {
            completeProgressBar();
        }
        
    }

    /**
     * Show success notification
     * @param {string} title - Notification title
     * @param {string} message - Notification message
     */
    showSuccess(title = 'Berhasil', message = '') {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'success',
                title: title,
                text: message,
                timer: 3000,
                showConfirmButton: false
            });
        } else {
        }
    }

    /**
     * Show error notification
     * @param {string} title - Error title
     * @param {string} message - Error message
     */
    showError(title = 'Terjadi Kesalahan', message = '') {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'error',
                title: title,
                text: message,
                confirmButtonColor: '#ec4899'
            });
        } else {
            console.error('❌ Error:', title, message);
        }
    }

    /**
     * Show warning notification
     * @param {string} title - Warning title
     * @param {string} message - Warning message
     */
    showWarning(title = 'Peringatan', message = '') {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'warning',
                title: title,
                text: message,
                confirmButtonColor: '#ec4899'
            });
        } else {
            console.warn('⚠️ Warning:', title, message);
        }
    }

    /**
     * Show confirmation dialog
     * @param {string} title - Dialog title
     * @param {string} message - Dialog message
     * @returns {Promise<boolean>} User confirmation result
     */
    async showConfirmation(title = 'Konfirmasi', message = 'Apakah Anda yakin?') {
        if (typeof Swal !== 'undefined') {
            const result = await Swal.fire({
                icon: 'question',
                title: title,
                text: message,
                showCancelButton: true,
                confirmButtonColor: '#ec4899',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Ya',
                cancelButtonText: 'Batal'
            });
            
            return result.isConfirmed;
        } else {
            return confirm(`${title}\n${message}`);
        }
    }

    /**
     * Update element text content
     * @param {string} elementId - Element ID
     * @param {string} text - New text content
     */
    updateText(elementId, text) {
        const element = document.getElementById(elementId);
        if (element) {
            element.textContent = text;
        }
    }

    /**
     * Update element HTML content
     * @param {string} elementId - Element ID
     * @param {string} html - New HTML content
     */
    updateHTML(elementId, html) {
        const element = document.getElementById(elementId);
        if (element) {
            element.innerHTML = html;
        }
    }

    /**
     * Add CSS class to element
     * @param {string} elementId - Element ID
     * @param {string} className - CSS class name
     */
    addClass(elementId, className) {
        const element = document.getElementById(elementId);
        if (element) {
            element.classList.add(className);
        }
    }

    /**
     * Remove CSS class from element
     * @param {string} elementId - Element ID
     * @param {string} className - CSS class name
     */
    removeClass(elementId, className) {
        const element = document.getElementById(elementId);
        if (element) {
            element.classList.remove(className);
        }
    }

    /**
     * Toggle CSS class on element
     * @param {string} elementId - Element ID
     * @param {string} className - CSS class name
     */
    toggleClass(elementId, className) {
        const element = document.getElementById(elementId);
        if (element) {
            element.classList.toggle(className);
        }
    }

    /**
     * Disable element
     * @param {string} elementId - Element ID
     */
    disableElement(elementId) {
        const element = document.getElementById(elementId);
        if (element) {
            element.disabled = true;
            element.classList.add('opacity-50', 'cursor-not-allowed');
        }
    }

    /**
     * Enable element
     * @param {string} elementId - Element ID
     */
    enableElement(elementId) {
        const element = document.getElementById(elementId);
        if (element) {
            element.disabled = false;
            element.classList.remove('opacity-50', 'cursor-not-allowed');
        }
    }

    /**
     * Get element value
     * @param {string} elementId - Element ID
     * @returns {*} Element value
     */
    getValue(elementId) {
        const element = document.getElementById(elementId);
        return element ? element.value : null;
    }

    /**
     * Set element value
     * @param {string} elementId - Element ID
     * @param {*} value - New value
     */
    setValue(elementId, value) {
        const element = document.getElementById(elementId);
        if (element) {
            element.value = value;
        }
    }

    /**
     * Check if element exists
     * @param {string} elementId - Element ID
     * @returns {boolean} Element existence status
     */
    elementExists(elementId) {
        return !!document.getElementById(elementId);
    }
}

// Create singleton instance
const uiManager = new UIManager();

export default uiManager;
