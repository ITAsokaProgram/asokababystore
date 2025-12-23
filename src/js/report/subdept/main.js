/**
 * @fileoverview Main Application untuk laporan sub departemen
 * @description Entry point dan orchestrator untuk seluruh aplikasi sub departemen
 * @version 1.0.0
 * @author Development Team
 */

// Import semua dependencies
import { ELEMENT_IDS } from './config/constants.js';
import chartManager from './components/chartManager.js';
import tableManager from './components/tableManager.js';
import uiManager from './components/uiManager.js';
import dateManager from './utils/dateManager.js';
import eventHandlers from './handlers/eventHandlers.js';
import stateManager from './utils/state.js';
import branchService from './services/branchService.js';

/**
 * Main Application Class untuk Sub Department Report
 * @class SubDeptApp
 */
class SubDeptApp {
    constructor() {
        this.isInitialized = false;
        this.components = {
            chart: chartManager,
            table: tableManager,
            ui: uiManager,
            datePicker: dateManager,
            events: eventHandlers,
            state: stateManager,
            branch: branchService
        };
    }

    /**
     * Initialize aplikasi
     * @async
     * @returns {Promise<boolean>} Success status
     */
    async initialize() {
        try {
            
            

            // Load branch data untuk store codes dinamis
            
            await this.components.branch.initialize();
            

            // Populate branch options to select dropdown
            await this._populateBranchOptions();

            // Initialize State Manager first
            
            // State manager tidak perlu explicit initialization, sudah auto-init

            // Initialize UI Manager 
            
            if (!this.components.ui.initialize()) {
                throw new Error('Failed to initialize UI Manager');
            }

            // Initialize Date Picker Manager
            
            if (!this.components.datePicker.initialize()) {
                console.warn('⚠️ Date Picker Manager initialization failed, continuing...');
            }

            // Initialize Chart Manager
            
            if (!this.components.chart.initialize()) {
                throw new Error('Failed to initialize Chart Manager');
            }

            // Initialize Table Manager
            
            if (!this.components.table.initialize()) {
                console.warn('⚠️ Table Manager initialization failed, continuing...');
            }

            // Initialize Event Handlers (must be last)
            
            this.components.events.setDateManager(this.components.datePicker);
            if (!this.components.events.initialize()) {
                throw new Error('Failed to initialize Event Handlers');
            }

            // Final setup
            this._finalSetup();
            this.isInitialized = true;

            
            
            
            return true;

        } catch (error) {
            console.error('❌ Failed to initialize Sub Department Application:', error);
            
            // Show error notification
            this.components.ui.showError(
                'Gagal Memuat Aplikasi',
                'Terjadi kesalahan saat memuat aplikasi Sub Departemen. Silakan refresh halaman.'
            );
            
            return false;
        }
    }

    /**
     * Populate branch options ke select dropdown
     * @private
     * @async
     */
    async _populateBranchOptions() {
        try {
            
            
            // Get branch options dari branchService
            const branchOptions = await this.components.branch.getSelectOptions(true);
            
            // Populate ke cabang select menggunakan ELEMENT_IDS
            this.components.ui.populateSelectOptions(ELEMENT_IDS.CABANG, branchOptions, true);
            
            

        } catch (error) {
            console.warn('⚠️ Failed to populate branch options:', error.message);
        }
    }

    /**
     * Final setup setelah semua komponen terinisialisasi
     * @private
     */
    _finalSetup() {
        // Setup global error handler
        window.addEventListener('error', (event) => {
            console.error('🚨 Global error caught:', event.error);
        });

        // Setup unhandled promise rejection handler
        window.addEventListener('unhandledrejection', (event) => {
            console.error('🚨 Unhandled promise rejection:', event.reason);
        });

        // Expose app instance untuk debugging
        if (typeof window !== 'undefined') {
            window.subDeptApp = this;
            window.appStatus = () => this.getStatus();
        }

        
    }

    /**
     * Get status aplikasi untuk debugging
     * @returns {Object} Status object
     */
    getStatus() {
        const status = {
            initialized: this.isInitialized,
            timestamp: new Date().toISOString(),
            components: {
                chart: this.components.chart.isReady(),
                table: this.components.table.isReady(),
                ui: this.components.ui.isReady(),
                events: this.components.events.isReady(),
                branch: this.components.branch.isDataLoaded()
            },
            state: this.components.state.getStateSummary(),
            chartInstances: this.components.state.getChartInstances(),
            memory: {
                jsHeapSizeLimit: performance.memory?.jsHeapSizeLimit,
                totalJSHeapSize: performance.memory?.totalJSHeapSize,
                usedJSHeapSize: performance.memory?.usedJSHeapSize
            }
        };

        console.table(status.components);
        return status;
    }

    /**
     * Restart aplikasi (untuk development/debugging)
     * @async
     */
    async restart() {
        try {
            
            
            // Cleanup existing instance
            this.cleanup();
            
            // Re-initialize
            await this.initialize();
            
            
            
        } catch (error) {
            console.error('❌ Failed to restart application:', error);
        }
    }

    /**
     * Cleanup aplikasi
     */
    cleanup() {
        try {
            

            // Cleanup event handlers
            if (this.components.events.isReady()) {
                this.components.events.cleanup();
            }

            // Dispose chart instances
            if (this.components.chart.isReady()) {
                this.components.chart.dispose();
            }

            // Reset state
            this.components.state.reset();

            // Clear branch cache
            this.components.branch.clearCache();

            this.isInitialized = false;
            

        } catch (error) {
            console.error('❌ Error during cleanup:', error);
        }
    }

    /**
     * Get component instance
     * @param {string} componentName - Nama komponen
     * @returns {Object|null} Component instance
     */
    getComponent(componentName) {
        return this.components[componentName] || null;
    }

    /**
     * Check if aplikasi ready untuk digunakan
     * @returns {boolean} Ready status
     */
    isReady() {
        return this.isInitialized && 
               this.components.ui.isReady() && 
               this.components.events.isReady();
    }
}

/**
 * Inisialisasi aplikasi ketika DOM ready
 * @async
 */
async function initializeApp() {
    try {
        
        
        const app = new SubDeptApp();
        const success = await app.initialize();
        
        if (success) {
            
        } else {
            console.error('💥 Sub Department Application failed to start');
        }
        
    } catch (error) {
        console.error('💥 Critical error during app initialization:', error);
    }
}

/**
 * Auto-initialize ketika DOM ready
 */
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeApp);
} else {
    // DOM already loaded
    initializeApp();
}

/**
 * Export untuk testing atau manual initialization
 */
export { SubDeptApp, initializeApp };
export default SubDeptApp;
