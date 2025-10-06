/**
 * @fileoverview Event Handlers untuk Sales Ratio Report
 * @description Mengelola semua event handling dan user interactions
 */

import { ELEMENT_IDS, SORT_OPTIONS } from '../config/constants.js';
import api from '../services/api.js';
import branchService from '../services/branchService.js';
import supplierService from '../services/supplierService.js';
import chartManager from '../components/chartManager.js';
import tableManager from '../components/tableManager.js';
import uiManager from '../components/uiManager.js';
import dateManager from '../utils/dateManager.js';
import stateManager from '../utils/stateManager.js';

/**
 * Class untuk mengelola event handlers
 * @class EventHandlers
 */
class EventHandlers {
    constructor() {
        this.isInitialized = false;
        this.abortController = null;
    }

    /**
     * Initialize event handlers
     * @returns {boolean} Success status
     */
    initialize() {
        try {
            
            
            // Setup all event handlers
            this._setupFormHandlers();
            this._setupUIHandlers();
            this._setupKeyboardHandlers();
            
            this.isInitialized = true;
            
            return true;
            
        } catch (error) {
            console.error('❌ Error initializing Event Handlers:', error);
            return false;
        }
    }

    /**
     * Setup form event handlers
     * @private
     */
    _setupFormHandlers() {
        // Submit button
        const submitBtn = document.getElementById(ELEMENT_IDS.SUBMIT_BUTTON);
        if (submitBtn) {
            submitBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.handleFormSubmit();
            });
        }

        // Branch select change
        const branchSelect = document.getElementById(ELEMENT_IDS.CABANG_SELECT);
        if (branchSelect) {
            branchSelect.addEventListener('change', (e) => {
                this.handleBranchChange(e.target.value);
            });
        }

        // Supplier select change
        const supplierSelect = document.getElementById(ELEMENT_IDS.SUPPLIER_SELECT);
        if (supplierSelect) {
            supplierSelect.addEventListener('change', (e) => {
                this.handleSupplierChange(e.target.value);
            });
        }

        // Sort radio buttons
        const sortRadios = document.querySelectorAll('input[name="sortBy"]');
        sortRadios.forEach(radio => {
            radio.addEventListener('change', (e) => {
                if (e.target.checked) {
                    this.handleSortChange(e.target.value);
                }
            });
        });

        // Reset button
        const resetBtn = document.getElementById(ELEMENT_IDS.RESET_BUTTON);
        if (resetBtn) {
            resetBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.handleFormReset();
            });
        }

        // Form prevent default submission
        const form = document.getElementById(ELEMENT_IDS.FORM);
        if (form) {
            form.addEventListener('submit', (e) => {
                e.preventDefault();
                this.handleFormSubmit();
            });
        }
    }

    /**
     * Setup UI event handlers
     * @private
     */
    _setupUIHandlers() {
        // Sidebar toggle
        const sidebarToggle = document.getElementById(ELEMENT_IDS.SIDEBAR_TOGGLE);
        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', () => {
                this.handleSidebarToggle();
            });
        }

        // Sidebar overlay click
        const sidebarOverlay = document.getElementById(ELEMENT_IDS.SIDEBAR_OVERLAY);
        if (sidebarOverlay) {
            sidebarOverlay.addEventListener('click', () => {
                uiManager.closeSidebar();
            });
        }

        // Window resize handler
        let resizeTimeout;
        window.addEventListener('resize', () => {
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(() => {
                this.handleWindowResize();
            }, 250);
        });

        // Before unload handler
        window.addEventListener('beforeunload', (e) => {
            this.handleBeforeUnload(e);
        });
    }

    /**
     * Setup keyboard event handlers
     * @private
     */
    _setupKeyboardHandlers() {
        document.addEventListener('keydown', (e) => {
            this.handleKeyboardShortcuts(e);
        });
    }

    /**
     * Handle form submission
     */
    async handleFormSubmit() {
        try {
            

            // Validate form
            const validation = this._validateForm();
            if (!validation.isValid) {
                uiManager.showError(validation.errors.join('\n'));
                return;
            }

            // Show loading state
            uiManager.showLoading(ELEMENT_IDS.SUBMIT_BUTTON, 'Processing...');
            stateManager.set('ui.isLoading', true);

            // Cancel previous request
            if (this.abortController) {
                this.abortController.abort();
            }
            this.abortController = new AbortController();

            // Get form data
            const formData = this._getFormData();
            
            // Save request to history
            stateManager.addToHistory({
                type: 'form_submit',
                data: formData,
                status: 'started'
            });

            // Update state
            stateManager.update({
                'form.dateStart': formData.startDate,
                'form.dateEnd': formData.endDate,
                'form.selectedBranch': formData.branch,
                'form.selectedSuppliers': formData.suppliers,
                'form.sortBy': formData.sortBy
            });

            // Send data to API
            const response = await api.sendBarChartData(formData, this.abortController.signal);

            if (response.success && response.data) {
                // Update cache
                stateManager.setCache('chartData', response.data, 5 * 60 * 1000); // 5 minutes
                stateManager.setCache('tableData', response.data, 5 * 60 * 1000);

                // Update chart
                chartManager.updateBarChart(response.data, formData.sortBy);
                
                // Update table
                tableManager.updateTable(response.data, formData.sortBy);

                // Show success
                uiManager.showSuccess('Data berhasil dimuat!');
                stateManager.set('ui.activeView', 'results');

                
            } else {
                throw new Error(response.message || 'Failed to load data');
            }

        } catch (error) {
            if (error.name === 'AbortError') {
                
                return;
            }

            console.error('❌ Form submission error:', error);
            uiManager.showError('Error: ' + error.message);
            
            // Save error to history
            stateManager.addToHistory({
                type: 'form_submit',
                status: 'error',
                error: error.message
            });

        } finally {
            // Hide loading state
            uiManager.hideLoading(ELEMENT_IDS.SUBMIT_BUTTON);
            stateManager.set('ui.isLoading', false);
            this.abortController = null;
        }
    }

    /**
     * Handle branch selection change
     * @param {string} branchValue - Selected branch value
     */
    async handleBranchChange(branchValue) {
        try {
            

            if (!branchValue) {
                // Clear suppliers when no branch selected
                supplierService.clearSuppliers();
                return;
            }

            // Show loading for supplier select
            uiManager.showLoading(ELEMENT_IDS.SUPPLIER_SELECT, 'Loading suppliers...');

            // Get store code for branch
            const storeCode = branchService.getStoreCode(branchValue);
            if (!storeCode) {
                throw new Error('Invalid branch selected');
            }

            // Load suppliers for branch
            await supplierService.loadSuppliersForBranch(storeCode);

            // Update state
            stateManager.set('form.selectedBranch', branchValue);

            

        } catch (error) {
            console.error('❌ Branch change error:', error);
            uiManager.showError('Error loading suppliers: ' + error.message);
            
        } finally {
            uiManager.hideLoading(ELEMENT_IDS.SUPPLIER_SELECT);
        }
    }

    /**
     * Handle supplier selection change
     * @param {string} supplierValue - Selected supplier value
     */
    handleSupplierChange(supplierValue) {
        

        // Update state with current supplier selection
        const currentSuppliers = stateManager.get('form.selectedSuppliers') || [];
        const supplierSelect = document.getElementById(ELEMENT_IDS.SUPPLIER_SELECT);
        
        if (supplierSelect) {
            const selectedOptions = Array.from(supplierSelect.selectedOptions);
            const selectedValues = selectedOptions.map(option => option.value);
            
            stateManager.set('form.selectedSuppliers', selectedValues);
            
            
        }
    }

    /**
     * Handle sort option change
     * @param {string} sortValue - Selected sort value
     */
    handleSortChange(sortValue) {
        

        // Update state
        stateManager.set('form.sortBy', sortValue);

        // If we have data, update displays immediately
        const cachedData = stateManager.getCache('chartData');
        if (cachedData) {
            chartManager.updateBarChart(cachedData, sortValue);
            tableManager.updateTable(cachedData, sortValue);
            
            uiManager.showInfo(`Sorted by ${sortValue === SORT_OPTIONS.QTY ? 'Quantity' : 'Total'}`);
        }
    }

    /**
     * Handle form reset
     */
    async handleFormReset() {
        try {
            const confirmed = await uiManager.showConfirmation(
                'Reset Form',
                'Are you sure you want to reset all form data?',
                'Reset'
            );

            if (!confirmed) return;

            

            // Reset date manager
            dateManager.resetToDefault();

            // Reset form elements
            uiManager.resetForm();

            // Clear displays
            chartManager.resetBarChart();
            tableManager.clear();

            // Clear cache
            stateManager.clearCache();

            // Reset state
            stateManager.reset('form');
            stateManager.set('ui.activeView', 'form');

            // Clear supplier dropdown
            supplierService.clearSuppliers();

            uiManager.showSuccess('Form has been reset');
            

        } catch (error) {
            console.error('❌ Form reset error:', error);
            uiManager.showError('Error resetting form: ' + error.message);
        }
    }

    /**
     * Handle sidebar toggle
     */
    handleSidebarToggle() {
        const isOpen = stateManager.get('ui.sidebarOpen');
        
        if (isOpen) {
            uiManager.closeSidebar();
            stateManager.set('ui.sidebarOpen', false);
        } else {
            uiManager.openSidebar();
            stateManager.set('ui.sidebarOpen', true);
        }

        
    }

    /**
     * Handle window resize
     */
    handleWindowResize() {
        // Resize chart
        if (chartManager.isReady()) {
            chartManager.resize();
        }

        // Handle responsive sidebar
        const isMobile = window.innerWidth < 768;
        const sidebarOpen = stateManager.get('ui.sidebarOpen');
        
        if (isMobile && sidebarOpen) {
            uiManager.closeSidebar(false); // Don't save state for mobile
        }

        
    }

    /**
     * Handle before unload
     * @param {Event} e - Before unload event
     */
    handleBeforeUnload(e) {
        const isLoading = stateManager.get('ui.isLoading');
        
        if (isLoading) {
            const message = 'A request is currently in progress. Are you sure you want to leave?';
            e.returnValue = message;
            return message;
        }
    }

    /**
     * Handle keyboard shortcuts
     * @param {KeyboardEvent} e - Keyboard event
     */
    handleKeyboardShortcuts(e) {
        // Ctrl/Cmd + Enter: Submit form
        if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
            e.preventDefault();
            const isLoading = stateManager.get('ui.isLoading');
            if (!isLoading) {
                this.handleFormSubmit();
            }
        }

        // Escape: Close sidebar
        if (e.key === 'Escape') {
            const sidebarOpen = stateManager.get('ui.sidebarOpen');
            if (sidebarOpen) {
                uiManager.closeSidebar();
                stateManager.set('ui.sidebarOpen', false);
            }
        }

        // Ctrl/Cmd + R: Reset form
        if ((e.ctrlKey || e.metaKey) && e.key === 'r') {
            const isLoading = stateManager.get('ui.isLoading');
            if (!isLoading) {
                e.preventDefault();
                this.handleFormReset();
            }
        }
    }

    /**
     * Validate form data
     * @private
     * @returns {Object} Validation result
     */
    _validateForm() {
        const errors = [];

        // Validate dates
        const dateValidation = dateManager.validateDateRange();
        if (!dateValidation.isValid) {
            errors.push(...dateValidation.errors);
        }

        // Validate branch selection
        const branchSelect = document.getElementById(ELEMENT_IDS.CABANG_SELECT);
        if (!branchSelect?.value) {
            errors.push('Please select a branch');
        }

        // Validate supplier selection
        const supplierSelect = document.getElementById(ELEMENT_IDS.SUPPLIER_SELECT);
        if (!supplierSelect?.selectedOptions.length) {
            errors.push('Please select at least one supplier');
        }

        return {
            isValid: errors.length === 0,
            errors
        };
    }

    /**
     * Get form data
     * @private
     * @returns {Object} Form data
     */
    _getFormData() {
        const dateRange = dateManager.getDateRange();
        const branchSelect = document.getElementById(ELEMENT_IDS.CABANG_SELECT);
        const supplierSelect = document.getElementById(ELEMENT_IDS.SUPPLIER_SELECT);
        const sortBy = document.querySelector('input[name="sortBy"]:checked')?.value || SORT_OPTIONS.TOTAL;

        // Get selected suppliers
        const selectedSuppliers = supplierSelect ? 
            Array.from(supplierSelect.selectedOptions).map(option => option.value) : [];

        return {
            startDate: dateRange.startDateStr,
            endDate: dateRange.endDateStr,
            branch: branchSelect?.value || '',
            suppliers: selectedSuppliers,
            sortBy: sortBy
        };
    }

    /**
     * Setup date preset handlers
     * @param {Array} presetButtons - Preset button elements
     */
    setupDatePresets(presetButtons) {
        presetButtons.forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                const preset = button.dataset.preset;
                if (preset) {
                    dateManager.setPresetRange(preset);
                    uiManager.showInfo(`Date range set to: ${preset}`);
                }
            });
        });
    }

    /**
     * Setup export handlers
     * @param {Object} exportButtons - Export button elements
     */
    setupExportHandlers(exportButtons) {
        if (exportButtons.excel) {
            exportButtons.excel.addEventListener('click', () => {
                this.handleExport('excel');
            });
        }

        if (exportButtons.pdf) {
            exportButtons.pdf.addEventListener('click', () => {
                this.handleExport('pdf');
            });
        }
    }

    /**
     * Handle export functionality
     * @param {string} format - Export format ('excel' or 'pdf')
     */
    async handleExport(format) {
        try {
            const hasData = tableManager.hasData();
            if (!hasData) {
                uiManager.showWarning('No data to export. Please submit the form first.');
                return;
            }

            

            if (format === 'excel') {
                await tableManager.exportToExcel();
            } else if (format === 'pdf') {
                await tableManager.exportToPDF();
            }

            uiManager.showSuccess(`Export to ${format.toUpperCase()} completed!`);
            
        } catch (error) {
            console.error(`❌ Export ${format} error:`, error);
            uiManager.showError(`Export failed: ${error.message}`);
        }
    }

    /**
     * Check if event handlers are ready
     * @returns {boolean} Ready status
     */
    isReady() {
        return this.isInitialized;
    }

    /**
     * Cleanup event handlers
     */
    cleanup() {
        // Abort any pending requests
        if (this.abortController) {
            this.abortController.abort();
            this.abortController = null;
        }

        // Remove event listeners would go here if we stored references
        // For now, since we're using addEventListener directly, 
        // they'll be cleaned up when the page unloads

        
        this.isInitialized = false;
    }
}

// Create singleton instance
const eventHandlers = new EventHandlers();

export default eventHandlers;
