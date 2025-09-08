/**
 * @fileoverview Event Handlers untuk laporan sub departemen
 * @description Mengelola semua event handling dan user interactions
 */

import { ELEMENT_IDS, QUERY_TYPES, SORT_OPTIONS } from '../config/constants.js';
import apiService from '../services/api.js';
import branchService from '../services/branchService.js';
import chartManager from '../components/chartManager.js';
import tableManager from '../components/tableManager.js';
import uiManager from '../components/uiManager.js';
import stateManager from '../utils/state.js';
import { sortArrayByKey } from '../utils/formatters.js';

/**
 * Class untuk mengelola event handlers
 * @class EventHandlers
 */
class EventHandlers {
    constructor() {
        this.isInitialized = false;
        this.eventListeners = new Map();
        this.dateManager = null; // Will be set by main app
    }

    /**
     * Set reference ke dateManager
     * @param {DateManager} dateManager - Instance dateManager
     */
    setDateManager(dateManager) {
        this.dateManager = dateManager;
        console.log('📅 DateManager reference set in EventHandlers');
    }

    /**
     * Get start date value (with fallback)
     * @returns {string} Start date string
     * @private
     */
    _getStartDate() {
        if (this.dateManager) {
            return this.dateManager.getStartDate();
        }
        return uiManager.getElementValue(ELEMENT_IDS.DATE);
    }

    /**
     * Get end date value (with fallback)
     * @returns {string} End date string
     * @private
     */
    _getEndDate() {
        if (this.dateManager) {
            return this.dateManager.getEndDate();
        }
        return uiManager.getElementValue(ELEMENT_IDS.DATE1);
    }

    /**
     * Inisialisasi semua event handlers
     * @returns {boolean} Status inisialisasi
     */
    initialize() {
        try {
            console.log('🎯 Initializing Event Handlers...');

            // Setup all event handlers
            this._setupQueryTypeButtons();
            this._setupSubmitButton();
            this._setupSubButton();
            this._setupPromoButtons();
            this._setupBackButton();
            this._setupSortHandlers();
            this._setupBranchChangeHandler();
            this._setupBodyClickHandler();
            this._setupCustomEventHandlers();

            this.isInitialized = true;
            console.log('✅ Event Handlers initialized successfully');
            return true;

        } catch (error) {
            console.error('❌ Error initializing Event Handlers:', error);
            return false;
        }
    }

    /**
     * Setup query type buttons
     * @private
     */
    _setupQueryTypeButtons() {
        const buttons = document.querySelectorAll("button[name='query_type']");
        
        buttons.forEach(button => {
            const handler = () => {
                const queryType = button.value;
                stateManager.setActiveQueryType(queryType);
                console.log('🔄 Query type changed to:', queryType);
            };

            this._addEventListener(button, 'click', handler);
        });

        console.log('🔘 Query type buttons configured');
    }

    /**
     * Setup submit button (untuk subdept data)
     * @private
     */
    _setupSubmitButton() {
        const submitBtn = document.getElementById(ELEMENT_IDS.BTN_SUBMIT);
        if (!submitBtn) return;

        const handler = async (e) => {
            e.preventDefault();
            
            try {
                const filter = uiManager.getElementValue(ELEMENT_IDS.SORT_BY);
                console.log('📊 Submit button clicked with filter:', filter);

                // Reset state
                stateManager.updatePagination(1, 1);
                
                // Load page
                await this._loadPage(1, filter);

            } catch (error) {
                console.error('❌ Error in submit handler:', error);
                uiManager.showError('Error', 'Gagal memuat data');
            }
        };

        this._addEventListener(submitBtn, 'click', handler);
        console.log('✅ Submit button configured');
    }

    /**
     * Setup sub button (untuk supplier data)
     * @private
     */
    _setupSubButton() {
        const subBtn = document.getElementById(ELEMENT_IDS.BTN_SUB);
        if (!subBtn) return;

        const handler = async (e) => {
            e.preventDefault();
            
            try {
                const filter = uiManager.getElementValue(ELEMENT_IDS.SORT_BY);
                console.log('🏪 Sub button clicked with filter:', filter);

                // Reset state
                stateManager.updatePagination(1, 1);
                
                // Load supplier data
                await this._loadSupplierData(1, filter);
                
                // Show back button
                uiManager.showElement(ELEMENT_IDS.BTN_BACK);

            } catch (error) {
                console.error('❌ Error in sub handler:', error);
                uiManager.showError('Error', 'Gagal memuat data supplier');
            }
        };

        this._addEventListener(subBtn, 'click', handler);
        console.log('✅ Sub button configured');
    }

    /**
     * Setup promo buttons
     * @private
     */
    _setupPromoButtons() {
        // Button see penjualan
        const seePenjualanBtn = document.getElementById(ELEMENT_IDS.BTN_SEE_PENJUALAN);
        if (seePenjualanBtn) {
            const handler = (e) => {
                e.preventDefault();
                const promoBtn = document.getElementById(ELEMENT_IDS.BTN_PROMO);
                if (promoBtn) {
                    promoBtn.click();
                }
            };
            this._addEventListener(seePenjualanBtn, 'click', handler);
        }

        // Button see promo
        const seePromoBtn = document.getElementById(ELEMENT_IDS.BTN_SEE_PROMO);
        if (seePromoBtn) {
            const handler = async (e) => {
                e.preventDefault();
                await this._loadPromoData();
            };
            this._addEventListener(seePromoBtn, 'click', handler);
        }

        console.log('🎁 Promo buttons configured');
    }

    /**
     * Setup back button
     * @private
     */
    _setupBackButton() {
        const backBtn = document.getElementById(ELEMENT_IDS.BTN_BACK);
        if (!backBtn) return;

        const handler = (e) => {
            e.preventDefault();
            
            try {
                console.log('⬅️ Back button clicked');
                
                // Reset UI state
                uiManager.resetToInitialState();
                
                // Reset state flags
                stateManager.setSubdeptActive(false);
                stateManager.setPromoStatus(false);
                
                // Reset charts
                chartManager.resetPieChart();
                chartManager.resetBarChart();
                
                console.log('🔄 Returned to initial state');

            } catch (error) {
                console.error('❌ Error in back handler:', error);
            }
        };

        this._addEventListener(backBtn, 'click', handler);
        console.log('⬅️ Back button configured');
    }

    /**
     * Setup sort handlers
     * @private
     */
    _setupSortHandlers() {
        // Sort handler untuk subdept
        const sortBy = document.getElementById(ELEMENT_IDS.SORT_BY);
        if (sortBy) {
            const handler = () => {
                this._handleSortChange(ELEMENT_IDS.SORT_BY, 'subdept');
            };
            this._addEventListener(sortBy, 'change', handler);
        }

        // Sort handler untuk promo
        const sortBy1 = document.getElementById(ELEMENT_IDS.SORT_BY1);
        if (sortBy1) {
            const handler = () => {
                this._handleSortChange(ELEMENT_IDS.SORT_BY1, 'promo');
            };
            this._addEventListener(sortBy1, 'change', handler);
        }

        console.log('🔢 Sort handlers configured');
    }

    /**
     * Setup branch change handler
     * @private
     */
    _setupBranchChangeHandler() {
        const cabangSelect = document.getElementById(ELEMENT_IDS.CABANG);
        if (!cabangSelect) return;

        const handler = async (e) => {
            try {
                const selectedBranch = e.target.value;
                console.log('🏢 Branch changed to:', selectedBranch);

                // Get store codes mapping
                const storeCodes = await branchService.getStoreCodes();
                
                // Update store code input
                uiManager.updateStoreCodeInput(selectedBranch, storeCodes);

            } catch (error) {
                console.error('❌ Error in branch change handler:', error);
            }
        };

        this._addEventListener(cabangSelect, 'change', handler);
        console.log('🏢 Branch change handler configured');
    }

    /**
     * Setup body click handler untuk chart resize
     * @private
     */
    _setupBodyClickHandler() {
        const handler = (e) => {
            // Trigger chart resize setelah klik
            chartManager.resizeCharts();
            
            // Handle back button click dari body
            if (e.target.id === ELEMENT_IDS.BTN_BACK) {
                // Already handled in _setupBackButton
                return;
            }
        };

        this._addEventListener(document.body, 'click', handler);
        console.log('👆 Body click handler configured');
    }

    /**
     * Setup custom event handlers
     * @private
     */
    _setupCustomEventHandlers() {
        // Handler untuk pie chart click
        const handler = (e) => {
            const { name, value, data } = e.detail;
            console.log('🥧 Pie chart clicked:', { name, value });
            
            // Bisa ditambahkan logic untuk drill-down atau detail view
        };

        this._addEventListener(document, 'pieChartClick', handler);
        console.log('🎯 Custom event handlers configured');
    }

    /**
     * Handle sort change
     * @private
     * @param {string} sortElementId - ID dari sort element
     * @param {string} dataType - Tipe data (subdept/promo)
     */
    _handleSortChange(sortElementId, dataType) {
        try {
            const selected = uiManager.getElementValue(sortElementId);
            console.log(`🔢 Sort changed for ${dataType}:`, selected);

            if (dataType === 'subdept') {
                this._handleSubdeptSort(selected);
            } else if (dataType === 'promo') {
                this._handlePromoSort(selected);
            }

        } catch (error) {
            console.error('❌ Error in sort change:', error);
        }
    }

    /**
     * Handle subdept sort
     * @private
     * @param {string} selected - Selected sort option
     */
    _handleSubdeptSort(selected) {
        const tableDataOri = stateManager.getTableCache(true);
        if (!tableDataOri.length) return;

        // Sort data
        const sortedTable = sortArrayByKey(tableDataOri, selected, 'desc');

        // Prepare chart data
        const labels = sortedTable.map(item => item.nama_subdept || item.nama_barang || item.kode);
        const data = sortedTable.map(item => {
            const kode = item.kode || item.kode_subdept || "";
            const nama = item.nama_subdept || item.nama_barang || "";
            const value = selected === SORT_OPTIONS.QTY ? item.Qty : item.Total || 0;
            return `${kode},${nama},${value}`;
        });

        // Update UI
        chartManager.updatePieChart(labels, data, sortedTable);
        chartManager.updateBarChart(labels, data.map(d => parseFloat(d.split(',')[2])), sortedTable);
        tableManager.updateTable(sortedTable, ELEMENT_IDS.SALES_TABLE, 'subdept');
        tableManager.updateTable(sortedTable, ELEMENT_IDS.SALES_TABLE_SUPPLIER, 'supplier');
    }

    /**
     * Handle promo sort
     * @private
     * @param {string} selected - Selected sort option
     */
    _handlePromoSort(selected) {
        const chartBartData = stateManager.getBarChartCache(true);
        if (!chartBartData.length) return;

        const tableDataOri = stateManager.getTableCache(true);
        let sortedTable = [...tableDataOri];

        // Sort table data
        if (selected === SORT_OPTIONS.QTY) {
            sortedTable.sort((a, b) => (b.Qty || 0) - (a.Qty || 0));
        } else {
            sortedTable.sort((a, b) => (b.Total || 0) - (a.Total || 0));
        }

        // Update chart labels
        const updatedLabels = chartBartData.map(item => {
            const persen = selected === 'Total'
                ? parseFloat(item.persentase_rp || 0)
                : parseFloat(item.Percentage || 0);
            const persenFix = isNaN(persen) ? '0.00' : persen.toFixed(2);
            return `${item.promo} (${persenFix}%)`;
        });

        const chartData = chartBartData.map(item =>
            selected === SORT_OPTIONS.QTY
                ? parseFloat(item.Qty || 0)
                : parseFloat(item.Total || 0)
        );

        // Update UI
        chartManager.updateBarChart(updatedLabels, chartData, chartBartData);
        tableManager.updateTable(sortedTable, ELEMENT_IDS.SALES_TABLE_PROMO, 'promo');
        tableManager.updateTable(sortedTable, ELEMENT_IDS.SALES_TABLE_PENJUALAN, 'promo');
    }

    /**
     * Load page data
     * @private
     * @param {number} page - Page number
     * @param {string} filter - Filter option
     */
    async _loadPage(page, filter) {
        try {
            uiManager.showLoading();
            uiManager.prepareSubdeptMode();

            // Get form data
            const additionalData = {
                query_type: QUERY_TYPES.SUBDEPT,
                page: page,
                filter: filter
            };

            const formData = apiService.createFormData(ELEMENT_IDS.FORM, additionalData);
            
            // Set report headers
            const cabang = apiService.getSelectedText(ELEMENT_IDS.CABANG);
            const startDate = this._getStartDate();
            const endDate = this._getEndDate();
            
            console.log('📅 Using dates:', { startDate, endDate });
            uiManager.updateReportHeader(cabang, startDate, endDate);

            // Fetch data
            const response = await apiService.fetchSubdeptData(formData, filter);

            if (response.status === 'success') {
                const { labels, data: chartData, tableData, totalPages = 1 } = response;
                
                stateManager.updatePagination(page, totalPages);
                chartManager.updatePieChart(labels, chartData, tableData);
                stateManager.updateTableCache(tableData);
                tableManager.updateTable(tableData, ELEMENT_IDS.SALES_TABLE, 'subdept');
                
                console.log('✅ Page data loaded successfully');
            }

        } catch (error) {
            console.error('❌ Error loading page:', error);
            uiManager.showError('Error', error.message);
        } finally {
            uiManager.hideLoading();
        }
    }

    /**
     * Load supplier data
     * @private
     * @param {number} page - Page number
     * @param {string} filter - Filter option
     */
    async _loadSupplierData(page, filter) {
        try {
            uiManager.showLoading();
            stateManager.setSubdeptActive(true);

            const additionalData = {
                query_type: QUERY_TYPES.SUPPLIER,
                page: page,
                filter: filter
            };

            const formData = apiService.createFormData(ELEMENT_IDS.FORM, additionalData);
            const response = await apiService.fetchSubdeptData(formData, filter);

            if (response.status === 'success') {
                const { tableData } = response;
                stateManager.updateTableCache(tableData);
                tableManager.updateTable(tableData, ELEMENT_IDS.SALES_TABLE_SUPPLIER, 'supplier');
                
                console.log('✅ Supplier data loaded successfully');
            }

        } catch (error) {
            console.error('❌ Error loading supplier data:', error);
            uiManager.showError('Error', error.message);
        } finally {
            uiManager.hideLoading();
        }
    }

    /**
     * Load promo data
     * @private
     */
    async _loadPromoData() {
        try {
            const cabangText = apiService.getSelectedText(ELEMENT_IDS.CABANG);
            const startDate = this._getStartDate();
            const endDate = this._getEndDate();
            
            uiManager.updateReportHeader(cabangText, startDate, endDate, 'promo');
            
            const filter = uiManager.getElementValue(ELEMENT_IDS.SORT_BY1);
            stateManager.setSubdeptActive(true);
            stateManager.setPromoStatus(true);
            
            uiManager.showLoading();

            const additionalData = {
                kd_store: uiManager.getElementValue(ELEMENT_IDS.KD_STORE),
                start_date: this._getStartDate(),
                end_date: this._getEndDate(),
                subdept: uiManager.getElementValue(ELEMENT_IDS.SUBDEPT) || "",
                kode_supp: uiManager.getElementValue(ELEMENT_IDS.KODE_SUPP) || "",
                page: stateManager.getPagination().currentPage,
                query_type: QUERY_TYPES.PROMO
            };

            const formData = new FormData();
            Object.entries(additionalData).forEach(([key, value]) => {
                formData.append(key, value);
            });
            formData.append('ajax', true);

            const response = await apiService.fetchDataWithAjax(formData, filter, QUERY_TYPES.PROMO);

            if (response.status === 'success' && response.tableData) {
                stateManager.updateTableCache(response.tableData);
                stateManager.updatePagination(1, response.totalPages || 1);
                
                tableManager.updateTable(response.tableData, ELEMENT_IDS.SALES_TABLE_PROMO, 'promo');
                tableManager.updatePromoTableHeader(ELEMENT_IDS.TH_HEAD_PROMO);
                
                console.log('✅ Promo data loaded successfully');
            }

        } catch (error) {
            console.error('❌ Error loading promo data:', error);
            uiManager.showError('Error', error.message);
        } finally {
            uiManager.hideLoading();
        }
    }

    /**
     * Add event listener dengan tracking
     * @private
     * @param {Element} element - Element target
     * @param {string} event - Event type
     * @param {Function} handler - Event handler
     */
    _addEventListener(element, event, handler) {
        if (!element) return;

        element.addEventListener(event, handler);
        
        // Track untuk cleanup
        const key = `${element.id || 'anonymous'}_${event}`;
        this.eventListeners.set(key, { element, event, handler });
    }

    /**
     * Remove semua event listeners
     */
    cleanup() {
        this.eventListeners.forEach(({ element, event, handler }) => {
            element.removeEventListener(event, handler);
        });
        
        this.eventListeners.clear();
        this.isInitialized = false;
        console.log('🧹 Event handlers cleaned up');
    }

    /**
     * Get initialization status
     * @returns {boolean} Status inisialisasi
     */
    isReady() {
        return this.isInitialized;
    }
}

// Export singleton instance
const eventHandlers = new EventHandlers();
export default eventHandlers;
