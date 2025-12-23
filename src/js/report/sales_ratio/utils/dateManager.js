/**
 * @fileoverview Date Manager utility untuk Sales Ratio Report
 * @description Mengelola operasi tanggal, date picker, dan validasi tanggal
 */

import { ELEMENT_IDS, DATE_CONFIG } from '../config/constants.js';
import { formatDate, toApiDateFormat } from './formatters.js';

/**
 * Class untuk mengelola date operations
 * @class DateManager
 */
class DateManager {
    constructor() {
        this.flatpickrInstances = new Map();
        this.isInitialized = false;
        this.dateConstraints = {
            minDate: null,
            maxDate: null
        };
    }

    /**
     * Initialize date manager
     * @returns {boolean} Success status
     */
    initialize() {
        try {
            
            
            // Check if Flatpickr is available
            if (typeof flatpickr === 'undefined') {
                throw new Error('Flatpickr library not found');
            }

            // Setup date constraints
            this._setupDateConstraints();
            
            // Initialize date pickers
            this._initializeDatePickers();
            
            // Set default dates
            this._setDefaultDates();
            
            this.isInitialized = true;
            
            return true;
            
        } catch (error) {
            console.error('❌ Error initializing Date Manager:', error);
            return false;
        }
    }

    /**
     * Setup date constraints
     * @private
     */
    _setupDateConstraints() {
        // Set max date to today
        this.dateConstraints.maxDate = new Date();
        
        // Set min date to 1 year ago (configurable)
        const minDate = new Date();
        minDate.setFullYear(minDate.getFullYear() - 1);
        this.dateConstraints.minDate = minDate;
        
        
    }

    /**
     * Initialize date pickers
     * @private
     */
    _initializeDatePickers() {
        // Initialize start date picker
        this._initializeDatePicker(ELEMENT_IDS.DATE_START, {
            ...DATE_CONFIG.FLATPICKR_OPTIONS,
            onChange: (selectedDates, dateStr, instance) => {
                this._onStartDateChange(selectedDates, dateStr, instance);
            }
        });

        // Initialize end date picker
        this._initializeDatePicker(ELEMENT_IDS.DATE_END, {
            ...DATE_CONFIG.FLATPICKR_OPTIONS,
            onChange: (selectedDates, dateStr, instance) => {
                this._onEndDateChange(selectedDates, dateStr, instance);
            }
        });
    }

    /**
     * Initialize individual date picker
     * @private
     * @param {string} elementId - Element ID
     * @param {Object} options - Flatpickr options
     */
    _initializeDatePicker(elementId, options) {
        const element = document.getElementById(elementId);
        if (!element) {
            console.warn(`⚠️ Date picker element ${elementId} not found`);
            return;
        }

        // Merge with default constraints
        const finalOptions = {
            ...options,
            minDate: this.dateConstraints.minDate,
            maxDate: this.dateConstraints.maxDate
        };

        try {
            const instance = flatpickr(element, finalOptions);
            this.flatpickrInstances.set(elementId, instance);
            
            
        } catch (error) {
            console.error(`❌ Error initializing date picker for ${elementId}:`, error);
        }
    }

    /**
     * Handle start date change
     * @private
     * @param {Array} selectedDates - Selected dates
     * @param {string} dateStr - Date string
     * @param {Object} instance - Flatpickr instance
     */
    _onStartDateChange(selectedDates, dateStr, instance) {
        if (selectedDates.length > 0) {
            const startDate = selectedDates[0];
            const endDatePicker = this.flatpickrInstances.get(ELEMENT_IDS.DATE_END);
            
            if (endDatePicker) {
                // Set minimum date for end date picker
                endDatePicker.set('minDate', startDate);
                
                // If end date is before start date, clear end date
                const currentEndDate = endDatePicker.selectedDates[0];
                if (currentEndDate && currentEndDate < startDate) {
                    endDatePicker.clear();
                }
            }
            
            
        }
    }

    /**
     * Handle end date change
     * @private
     * @param {Array} selectedDates - Selected dates
     * @param {string} dateStr - Date string
     * @param {Object} instance - Flatpickr instance
     */
    _onEndDateChange(selectedDates, dateStr, instance) {
        if (selectedDates.length > 0) {
            const endDate = selectedDates[0];
            const startDatePicker = this.flatpickrInstances.get(ELEMENT_IDS.DATE_START);
            
            if (startDatePicker) {
                // Set maximum date for start date picker
                startDatePicker.set('maxDate', endDate);
                
                // If start date is after end date, clear start date
                const currentStartDate = startDatePicker.selectedDates[0];
                if (currentStartDate && currentStartDate > endDate) {
                    startDatePicker.clear();
                }
            }
            
            
        }
    }

    /**
     * Set default dates (last 30 days)
     * @private
     */
    _setDefaultDates() {
        const defaultRange = this._getDefaultDateRange();
        
        this.setDateRange(defaultRange.startDate, defaultRange.endDate);
        
        
    }

    /**
     * Get default date range
     * @private
     * @returns {Object} Default date range
     */
    _getDefaultDateRange() {
        const endDate = new Date();
        const startDate = new Date();
        startDate.setDate(startDate.getDate() - DATE_CONFIG.DEFAULT_RANGE_DAYS);
        
        return { startDate, endDate };
    }

    /**
     * Set date range programmatically
     * @param {Date|string} startDate - Start date
     * @param {Date|string} endDate - End date
     */
    setDateRange(startDate, endDate) {
        const startPicker = this.flatpickrInstances.get(ELEMENT_IDS.DATE_START);
        const endPicker = this.flatpickrInstances.get(ELEMENT_IDS.DATE_END);

        if (startPicker && startDate) {
            const startDateObj = typeof startDate === 'string' ? new Date(startDate) : startDate;
            if (!isNaN(startDateObj.getTime())) {
                startPicker.setDate(startDateObj, true);
            }
        }

        if (endPicker && endDate) {
            const endDateObj = typeof endDate === 'string' ? new Date(endDate) : endDate;
            if (!isNaN(endDateObj.getTime())) {
                endPicker.setDate(endDateObj, true);
            }
        }
    }

    /**
     * Get current date range
     * @returns {Object} Current date range
     */
    getDateRange() {
        const startPicker = this.flatpickrInstances.get(ELEMENT_IDS.DATE_START);
        const endPicker = this.flatpickrInstances.get(ELEMENT_IDS.DATE_END);

        const startDate = startPicker?.selectedDates[0] || null;
        const endDate = endPicker?.selectedDates[0] || null;

        return {
            startDate,
            endDate,
            startDateStr: startDate ? toApiDateFormat(startDate) : '',
            endDateStr: endDate ? toApiDateFormat(endDate) : '',
            isValid: startDate && endDate && startDate <= endDate
        };
    }

    /**
     * Validate date range
     * @returns {Object} Validation result
     */
    validateDateRange() {
        const range = this.getDateRange();
        const errors = [];

        if (!range.startDate) {
            errors.push('Tanggal mulai harus diisi');
        }

        if (!range.endDate) {
            errors.push('Tanggal akhir harus diisi');
        }

        if (range.startDate && range.endDate) {
            if (range.startDate > range.endDate) {
                errors.push('Tanggal mulai tidak boleh lebih besar dari tanggal akhir');
            }

            // Check if range is too long
            const diffDays = this._calculateDaysDifference(range.startDate, range.endDate);
            if (diffDays > DATE_CONFIG.MAX_RANGE_DAYS) {
                errors.push(`Rentang tanggal maksimal ${DATE_CONFIG.MAX_RANGE_DAYS} hari`);
            }

            // Check if dates are in the future
            const today = new Date();
            today.setHours(23, 59, 59, 999);
            
            if (range.startDate > today) {
                errors.push('Tanggal mulai tidak boleh di masa depan');
            }
            
            if (range.endDate > today) {
                errors.push('Tanggal akhir tidak boleh di masa depan');
            }
        }

        return {
            isValid: errors.length === 0,
            errors,
            range
        };
    }

    /**
     * Calculate days difference between two dates
     * @private
     * @param {Date} startDate - Start date
     * @param {Date} endDate - End date
     * @returns {number} Days difference
     */
    _calculateDaysDifference(startDate, endDate) {
        const timeDiff = endDate.getTime() - startDate.getTime();
        return Math.ceil(timeDiff / (1000 * 3600 * 24));
    }

    /**
     * Set predefined date range
     * @param {string} preset - Preset type ('today', 'yesterday', 'thisWeek', 'lastWeek', 'thisMonth', 'lastMonth')
     */
    setPresetRange(preset) {
        const today = new Date();
        let startDate, endDate;

        switch (preset) {
            case 'today':
                startDate = new Date(today);
                endDate = new Date(today);
                break;
                
            case 'yesterday':
                startDate = new Date(today);
                startDate.setDate(today.getDate() - 1);
                endDate = new Date(startDate);
                break;
                
            case 'thisWeek':
                startDate = new Date(today);
                startDate.setDate(today.getDate() - today.getDay());
                endDate = new Date(today);
                break;
                
            case 'lastWeek':
                startDate = new Date(today);
                startDate.setDate(today.getDate() - today.getDay() - 7);
                endDate = new Date(startDate);
                endDate.setDate(startDate.getDate() + 6);
                break;
                
            case 'thisMonth':
                startDate = new Date(today.getFullYear(), today.getMonth(), 1);
                endDate = new Date(today);
                break;
                
            case 'lastMonth':
                startDate = new Date(today.getFullYear(), today.getMonth() - 1, 1);
                endDate = new Date(today.getFullYear(), today.getMonth(), 0);
                break;
                
            case 'last7Days':
                startDate = new Date(today);
                startDate.setDate(today.getDate() - 6);
                endDate = new Date(today);
                break;
                
            case 'last30Days':
                startDate = new Date(today);
                startDate.setDate(today.getDate() - 29);
                endDate = new Date(today);
                break;
                
            default:
                console.warn(`⚠️ Unknown preset: ${preset}`);
                return;
        }

        this.setDateRange(startDate, endDate);
        
    }

    /**
     * Clear date range
     */
    clearDateRange() {
        const startPicker = this.flatpickrInstances.get(ELEMENT_IDS.DATE_START);
        const endPicker = this.flatpickrInstances.get(ELEMENT_IDS.DATE_END);

        if (startPicker) {
            startPicker.clear();
        }

        if (endPicker) {
            endPicker.clear();
        }

        
    }

    /**
     * Reset to default date range
     */
    resetToDefault() {
        this._setDefaultDates();
        
    }

    /**
     * Disable date picker
     * @param {string} elementId - Element ID to disable
     */
    disable(elementId) {
        const picker = this.flatpickrInstances.get(elementId);
        const element = document.getElementById(elementId);

        if (picker) {
            // Flatpickr doesn't have direct disable, so we disable the input
            if (element) {
                element.disabled = true;
            }
        }
    }

    /**
     * Enable date picker
     * @param {string} elementId - Element ID to enable
     */
    enable(elementId) {
        const picker = this.flatpickrInstances.get(elementId);
        const element = document.getElementById(elementId);

        if (picker) {
            if (element) {
                element.disabled = false;
            }
        }
    }

    /**
     * Disable all date pickers
     */
    disableAll() {
        this.disable(ELEMENT_IDS.DATE_START);
        this.disable(ELEMENT_IDS.DATE_END);
    }

    /**
     * Enable all date pickers
     */
    enableAll() {
        this.enable(ELEMENT_IDS.DATE_START);
        this.enable(ELEMENT_IDS.DATE_END);
    }

    /**
     * Get formatted date range string for display
     * @param {string} format - Date format
     * @param {string} separator - Separator between dates
     * @returns {string} Formatted date range
     */
    getFormattedDateRange(format = 'medium', separator = ' - ') {
        const range = this.getDateRange();
        
        if (!range.startDate || !range.endDate) {
            return '';
        }

        const startStr = formatDate(range.startDate, format);
        const endStr = formatDate(range.endDate, format);

        return `${startStr}${separator}${endStr}`;
    }

    /**
     * Check if date manager is ready
     * @returns {boolean} Ready status
     */
    isReady() {
        return this.isInitialized && this.flatpickrInstances.size > 0;
    }

    /**
     * Destroy all date picker instances
     */
    destroy() {
        for (const [elementId, instance] of this.flatpickrInstances) {
            try {
                instance.destroy();
                
            } catch (error) {
                console.error(`❌ Error destroying date picker for ${elementId}:`, error);
            }
        }
        
        this.flatpickrInstances.clear();
        this.isInitialized = false;
        
        
    }

    /**
     * Get date picker instance for debugging
     * @param {string} elementId - Element ID
     * @returns {Object|null} Flatpickr instance
     */
    getInstance(elementId) {
        return this.flatpickrInstances.get(elementId) || null;
    }

    /**
     * Update date constraints
     * @param {Object} constraints - New constraints
     */
    updateConstraints(constraints = {}) {
        if (constraints.minDate) {
            this.dateConstraints.minDate = new Date(constraints.minDate);
        }
        
        if (constraints.maxDate) {
            this.dateConstraints.maxDate = new Date(constraints.maxDate);
        }

        // Update all picker instances with new constraints
        for (const [elementId, instance] of this.flatpickrInstances) {
            instance.set('minDate', this.dateConstraints.minDate);
            instance.set('maxDate', this.dateConstraints.maxDate);
        }

        
    }
}

// Create singleton instance
const dateManager = new DateManager();

export default dateManager;
