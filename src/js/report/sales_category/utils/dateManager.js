/**
 * @fileoverview Date Manager untuk laporan penjualan kategori
 * @description Mengelola date picker dan validasi tanggal
 */

import { getDefaultDateRange, formatDate, isValidDateRange } from '../utils/formatters.js';
import { ELEMENT_IDS } from '../config/constants.js';

/**
 * Class untuk mengelola date operations
 */
class DateManager {
    constructor() {
        this.flatpickrInstances = {};
        this.isInitialized = false;
    }

    /**
     * Initialize date picker components
     * @returns {boolean} Success status
     */
    initialize() {
        try {
            this._initializeDateInputs();
            this._initializeFlatpickr();
            this._setDefaultDates();
            
            this.isInitialized = true;
            return true;
        } catch (error) {
            console.error('Failed to initialize Date Manager:', error);
            return false;
        }
    }

    /**
     * Initialize basic date inputs
     * @private
     */
    _initializeDateInputs() {
        const startDateInput = document.getElementById(ELEMENT_IDS.DATE_START);
        const endDateInput = document.getElementById(ELEMENT_IDS.DATE_END);

        if (!startDateInput || !endDateInput) {
            console.error("Date input elements not found!");
            return;
        }

        // Add validation on change
        startDateInput.addEventListener('change', () => this._validateDateRange());
        endDateInput.addEventListener('change', () => this._validateDateRange());
    }

    /**
     * Initialize Flatpickr date pickers
     * @private
     */
    _initializeFlatpickr() {
        if (typeof flatpickr === 'undefined') {
            console.warn('Flatpickr library not found, using basic date inputs');
            return;
        }

        // Configuration for Flatpickr
        const config = {
            dateFormat: "d-m-Y",
            allowInput: true,
            locale: {
                weekdays: {
                    shorthand: ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'],
                    longhand: ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu']
                },
                months: {
                    shorthand: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
                    longhand: ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember']
                }
            },
            onClose: () => this._validateDateRange(),
            maxDate: new Date() // Tidak boleh lebih dari hari ini
        };

        // Initialize start date picker
        const startDateElement = document.getElementById(ELEMENT_IDS.DATE_START);
        if (startDateElement) {
            this.flatpickrInstances.startDate = flatpickr(startDateElement, {
                ...config,
                onChange: (selectedDates) => {
                    if (selectedDates.length > 0 && this.flatpickrInstances.endDate) {
                        // Set minimum date untuk end date
                        this.flatpickrInstances.endDate.set('minDate', selectedDates[0]);
                    }
                }
            });
        }

        // Initialize end date picker
        const endDateElement = document.getElementById(ELEMENT_IDS.DATE_END);
        if (endDateElement) {
            this.flatpickrInstances.endDate = flatpickr(endDateElement, {
                ...config,
                onChange: (selectedDates) => {
                    if (selectedDates.length > 0 && this.flatpickrInstances.startDate) {
                        // Set maximum date untuk start date
                        this.flatpickrInstances.startDate.set('maxDate', selectedDates[0]);
                    }
                }
            });
        }

    }

    /**
     * Set default date range (30 hari terakhir)
     * @private
     */
    _setDefaultDates() {
        const { startDate, endDate } = getDefaultDateRange();
        
        const startDateElement = document.getElementById(ELEMENT_IDS.DATE_START);
        const endDateElement = document.getElementById(ELEMENT_IDS.DATE_END);

        if (startDateElement && endDateElement) {
            startDateElement.value = startDate;
            endDateElement.value = endDate;

            // Update Flatpickr instances if available
            if (this.flatpickrInstances.startDate) {
                this.flatpickrInstances.startDate.setDate(startDate);
            }
            if (this.flatpickrInstances.endDate) {
                this.flatpickrInstances.endDate.setDate(endDate);
            }

        }
    }

    /**
     * Validate date range
     * @private
     * @returns {boolean} Validation result
     */
    _validateDateRange() {
        const startDate = this.getStartDate();
        const endDate = this.getEndDate();

        if (!startDate || !endDate) {
            this._showDateError('Silakan isi tanggal mulai dan tanggal akhir');
            return false;
        }

        if (!isValidDateRange(startDate, endDate)) {
            this._showDateError('Tanggal mulai harus lebih kecil atau sama dengan tanggal akhir');
            return false;
        }

        // Check if date range is not too far in the past (optional)
        const start = new Date(startDate.split('-').reverse().join('-'));
        const today = new Date();
        const maxDaysBack = 365; // 1 year
        const daysDiff = Math.ceil((today - start) / (1000 * 60 * 60 * 24));

        if (daysDiff > maxDaysBack) {
            this._showDateError(`Tanggal tidak boleh lebih dari ${maxDaysBack} hari yang lalu`);
            return false;
        }

        this._clearDateError();
        return true;
    }

    /**
     * Show date validation error
     * @private
     * @param {string} message - Error message
     */
    _showDateError(message) {
        // Remove existing error
        this._clearDateError();

        // Add error styling to date inputs
        const startDateElement = document.getElementById(ELEMENT_IDS.DATE_START);
        const endDateElement = document.getElementById(ELEMENT_IDS.DATE_END);

        if (startDateElement) startDateElement.classList.add('border-red-500', 'ring-red-500');
        if (endDateElement) endDateElement.classList.add('border-red-500', 'ring-red-500');

        // Show error message
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'warning',
                title: 'Tanggal Tidak Valid',
                text: message,
                confirmButtonColor: '#ec4899',
                timer: 4000
            });
        } else {
            console.warn('Date validation error:', message);
        }
    }

    /**
     * Clear date validation error
     * @private
     */
    _clearDateError() {
        const startDateElement = document.getElementById(ELEMENT_IDS.DATE_START);
        const endDateElement = document.getElementById(ELEMENT_IDS.DATE_END);

        if (startDateElement) {
            startDateElement.classList.remove('border-red-500', 'ring-red-500');
        }
        if (endDateElement) {
            endDateElement.classList.remove('border-red-500', 'ring-red-500');
        }
    }

    /**
     * Get start date value
     * @returns {string|null} Start date in dd-mm-yyyy format
     */
    getStartDate() {
        const element = document.getElementById(ELEMENT_IDS.DATE_START);
        return element ? element.value : null;
    }

    /**
     * Get end date value
     * @returns {string|null} End date in dd-mm-yyyy format
     */
    getEndDate() {
        const element = document.getElementById(ELEMENT_IDS.DATE_END);
        return element ? element.value : null;
    }

    /**
     * Set start date value
     * @param {string} date - Date in dd-mm-yyyy format
     */
    setStartDate(date) {
        const element = document.getElementById(ELEMENT_IDS.DATE_START);
        if (element) {
            element.value = date;
            if (this.flatpickrInstances.startDate) {
                this.flatpickrInstances.startDate.setDate(date);
            }
        }
    }

    /**
     * Set end date value
     * @param {string} date - Date in dd-mm-yyyy format
     */
    setEndDate(date) {
        const element = document.getElementById(ELEMENT_IDS.DATE_END);
        if (element) {
            element.value = date;
            if (this.flatpickrInstances.endDate) {
                this.flatpickrInstances.endDate.setDate(date);
            }
        }
    }

    /**
     * Get date range object
     * @returns {Object|null} Date range object or null if invalid
     */
    getDateRange() {
        const startDate = this.getStartDate();
        const endDate = this.getEndDate();

        if (this._validateDateRange()) {
            return { startDate, endDate };
        }
        return null;
    }

    /**
     * Set date range
     * @param {Object} dateRange - Date range object
     * @param {string} dateRange.startDate - Start date
     * @param {string} dateRange.endDate - End date
     */
    setDateRange({ startDate, endDate }) {
        this.setStartDate(startDate);
        this.setEndDate(endDate);
    }

    /**
     * Reset to default date range
     */
    resetToDefault() {
        this._setDefaultDates();
    }

    /**
     * Get formatted date range for display
     * @returns {string} Formatted date range string
     */
    getFormattedDateRange() {
        const startDate = this.getStartDate();
        const endDate = this.getEndDate();

        if (startDate && endDate) {
            return `${startDate} s/d ${endDate}`;
        }
        return '';
    }

    /**
     * Check if current date range is valid
     * @returns {boolean} Validation result
     */
    isValidRange() {
        return this._validateDateRange();
    }

    /**
     * Dispose date manager (cleanup)
     */
    dispose() {
        // Destroy Flatpickr instances
        Object.values(this.flatpickrInstances).forEach(instance => {
            if (instance && typeof instance.destroy === 'function') {
                instance.destroy();
            }
        });

        this.flatpickrInstances = {};
        this.isInitialized = false;
    }
}

// Create singleton instance
const dateManager = new DateManager();

export default dateManager;
