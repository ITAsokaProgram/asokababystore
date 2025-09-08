/**
 * @fileoverview Main application file untuk laporan penjualan kategori
 * @description Entry point aplikasi yang menginisialisasi semua komponen
 * @author Asoka Baby Store Development Team
 * @version 2.0.0
 */

// Import semua dependencies
import chartManager from './components/chartManager.js';
import tableManager from './components/tableManager.js';
import uiManager from './components/uiManager.js';
import eventHandlers from './handlers/eventHandlers.js';
import dateManager from './utils/dateManager.js';
import salesCategoryState from './utils/state.js';
import branchService from './services/branchService.js';

/**
 * Main Application Class untuk Sales Category Report
 */
class SalesCategoryApp {
    constructor() {
        this.isInitialized = false;
        this.components = {
            chart: chartManager,
            table: tableManager,
            ui: uiManager,
            events: eventHandlers,
            date: dateManager
        };
    }

    /**
     * Initialize aplikasi
     * @returns {Promise<boolean>} Success status
     */
    async initialize() {
        try {

            // Load branch data first (critical dependency)
            await branchService.loadStoreCodes();

            // Initialize UI Manager first
            if (!this.components.ui.initialize()) {
                throw new Error('Failed to initialize UI Manager');
            }

            // Initialize Chart Manager
            if (!this.components.chart.initialize()) {
                throw new Error('Failed to initialize Chart Manager');
            }

            // Initialize Table Manager
            if (!this.components.table.initialize()) {
                console.warn('Table Manager initialization failed, continuing...');
            }

            // Initialize Date Manager
            if (!this.components.date.initialize()) {
                console.warn('Date Manager initialization failed, continuing...');
            }

            // Initialize Event Handlers (must be last)
            if (!this.components.events.initialize()) {
                throw new Error('Failed to initialize Event Handlers');
            }

            this.isInitialized = true;
            

            return true;
        } catch (error) {
            console.error('❌ Failed to initialize Sales Category Application:', error);
            
            // Show error notification
            this.components.ui.showError(
                'Gagal Memuat Aplikasi',
                'Terjadi kesalahan saat memuat aplikasi. Silakan refresh halaman.'
            );
            
            return false;
        }
    }

    /**
     * Get application status
     * @returns {Object} Application status information
     */
    getStatus() {
        return {
            initialized: this.isInitialized,
            components: {
                chart: this.components.chart.isInitialized,
                ui: this.components.ui.isInitialized,
                date: this.components.date.isInitialized,
                events: this.components.events.isInitialized
            },
            state: {
                historyLength: salesCategoryState.getHistoryLength(),
                hasPreviousState: salesCategoryState.hasPreviousState(),
                currentStoreCode: salesCategoryState.getStoreCode()
            }
        };
    }

    /**
     * Restart aplikasi (re-initialize semua komponen)
     * @returns {Promise<boolean>} Success status
     */
    async restart() {
        
        this.dispose();
        return await this.initialize();
    }

    /**
     * Reset aplikasi ke state awal
     */
    reset() {
        
        // Clear state
        salesCategoryState.clearCache();
        
        // Reset UI
        this.components.ui.setEarlyMode();
        this.components.chart.hide();
        this.components.table.hide();
        
        // Reset date to default
        this.components.date.resetToDefault();
        
    }

    /**
     * Dispose aplikasi (cleanup)
     */
    dispose() {
        
        // Dispose all components
        Object.values(this.components).forEach(component => {
            if (component && typeof component.dispose === 'function') {
                component.dispose();
            }
        });
        
        // Clear state
        salesCategoryState.clearCache();
        
        this.isInitialized = false;
    }

    /**
     * Handle global errors
     * @param {Error} error - Error object
     * @param {string} context - Error context
     */
    handleError(error, context = 'Unknown') {
        console.error(`Application Error [${context}]:`, error);
        
        this.components.ui.showError(
            'Terjadi Kesalahan',
            `Error: ${error.message || 'Unknown error'}`
        );
        
        // Optional: Send error to logging service
        // this.logError(error, context);
    }

    /**
     * Get component instance
     * @param {string} componentName - Component name
     * @returns {Object|null} Component instance
     */
    getComponent(componentName) {
        return this.components[componentName] || null;
    }

    /**
     * Check if application is ready for use
     * @returns {boolean} Ready status
     */
    isReady() {
        return this.isInitialized && 
               this.components.ui.isInitialized && 
               this.components.chart.isInitialized;
    }
}

/**
 * Global error handler untuk uncaught errors
 */
window.addEventListener('error', (event) => {
    console.error('Global Error:', event.error);
    if (window.salesCategoryApp) {
        window.salesCategoryApp.handleError(event.error, 'Global');
    }
});

/**
 * Global promise rejection handler
 */
window.addEventListener('unhandledrejection', (event) => {
    console.error('Unhandled Promise Rejection:', event.reason);
    if (window.salesCategoryApp) {
        window.salesCategoryApp.handleError(
            new Error(event.reason), 
            'Promise Rejection'
        );
    }
});

/**
 * Initialize aplikasi ketika DOM ready
 */
document.addEventListener('DOMContentLoaded', async () => {
    
    // Create global app instance
    window.salesCategoryApp = new SalesCategoryApp();
    
    // Initialize application
    const success = await window.salesCategoryApp.initialize();
    
    if (success) {
        console.log('Sales Category Application is ready!');
    } else {
        console.error('Failed to start Sales Category Application');
    }
});

/**
 * Handle page unload (cleanup)
 */
window.addEventListener('beforeunload', () => {
    if (window.salesCategoryApp) {
        window.salesCategoryApp.dispose();
    }
});

// Export for module usage
export default SalesCategoryApp;
