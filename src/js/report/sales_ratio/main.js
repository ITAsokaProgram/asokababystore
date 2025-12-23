/**
 * @fileoverview Main Application Entry Point untuk Sales Ratio Report
 * @description Orchestrator utama yang mengoordinasikan semua komponen
 */

// Import all dependencies
import { ELEMENT_IDS, APP_CONFIG } from './config/constants.js';

// Services
import api from './services/api.js';
import branchService from './services/branchService.js';
import supplierService from './services/supplierService.js';

// Components
import chartManager from './components/chartManager.js';
import tableManager from './components/tableManager.js';
import uiManager from './components/uiManager.js';

// Utils
import dateManager from './utils/dateManager.js';
import stateManager from './utils/stateManager.js';
import formatters from './utils/formatters.js';

// Handlers
import eventHandlers from './handlers/eventHandlers.js';

/**
 * Main Application Class
 * @class SalesRatioApp
 */
class SalesRatioApp {
    constructor() {
        this.isInitialized = false;
        this.components = new Map();
        this.initializationOrder = [
            'stateManager',
            'uiManager', 
            'dateManager',
            'branchService',
            'supplierService',
            'chartManager',
            'tableManager',
            'eventHandlers'
        ];
    }

    /**
     * Initialize the application
     * @returns {Promise<boolean>} Success status
     */
    async initialize() {
        try {
            
            // Check browser compatibility
            if (!this._checkBrowserCompatibility()) {
                throw new Error('Browser not compatible with this application');
            }

            // Initialize components in order
            await this._initializeComponents();
            
            // Load initial data
            await this._loadInitialData();
            
            // Setup application state
            this._setupApplicationState();
            
            // Mark as initialized
            this.isInitialized = true;
            stateManager.set('app.initialized', true);
            
            
            // Show welcome message
            this._showWelcomeMessage();
            
            return true;
            
        } catch (error) {
            console.error('❌ Application initialization failed:', error);
            this._handleInitializationError(error);
            return false;
        }
    }

    /**
     * Check browser compatibility
     * @private
     * @returns {boolean} Is compatible
     */
    _checkBrowserCompatibility() {
        const requiredFeatures = [
            'fetch',
            'Promise',
            'Map',
            'Set',
            'localStorage',
            'sessionStorage'
        ];

        const missingFeatures = requiredFeatures.filter(feature => {
            return !(feature in window) && !(feature in window.constructor.prototype);
        });

        if (missingFeatures.length > 0) {
            console.error('❌ Missing browser features:', missingFeatures);
            return false;
        }

        // Check for ES6+ support
        try {
            eval('const test = () => {}; class Test {}');
        } catch (e) {
            console.error('❌ ES6+ not supported');
            return false;
        }

        return true;
    }

    /**
     * Initialize all components in order
     * @private
     */
    async _initializeComponents() {
        const componentMap = {
            stateManager,
            uiManager,
            dateManager,
            branchService,
            supplierService,
            chartManager,
            tableManager,
            eventHandlers
        };

        for (const componentName of this.initializationOrder) {
            const component = componentMap[componentName];
            
            if (!component) {
                console.warn(`⚠️ Component ${componentName} not found`);
                continue;
            }

            try {
                
                // Initialize component
                const success = await component.initialize();
                
                if (!success) {
                    throw new Error(`Failed to initialize ${componentName}`);
                }
                
                // Store component reference
                this.components.set(componentName, component);
                
                
                // Add small delay to prevent blocking
                await this._sleep(10);
                
            } catch (error) {
                console.error(`❌ Error initializing ${componentName}:`, error);
                throw new Error(`Component initialization failed: ${componentName}`);
            }
        }
    }

    /**
     * Load initial application data
     * @private
     */
    async _loadInitialData() {
        try {
            
            // Show loading indicator
            uiManager.showLoading(ELEMENT_IDS.FORM, 'Loading application data...');
            
            // Load branches
            await branchService.populateBranches();
            
            // Check if we have saved state to restore
            const savedBranch = stateManager.get('form.selectedBranch');
            if (savedBranch) {
                await this._restoreSavedState();
            }
            
            
        } catch (error) {
            console.error('❌ Error loading initial data:', error);
            // Don't throw here - app can still work without saved state
            uiManager.showWarning('Some initial data could not be loaded, but the application is still functional.');
            
        } finally {
            uiManager.hideLoading(ELEMENT_IDS.FORM);
        }
    }

    /**
     * Restore saved application state
     * @private
     */
    async _restoreSavedState() {
        const savedBranch = stateManager.get('form.selectedBranch');
        const savedSuppliers = stateManager.get('form.selectedSuppliers');
        const savedSort = stateManager.get('form.sortBy');
        
        // Restore branch selection
        if (savedBranch) {
            const branchSelect = document.getElementById(ELEMENT_IDS.CABANG_SELECT);
            if (branchSelect) {
                branchSelect.value = savedBranch;
                
                // Load suppliers for saved branch
                const storeCode = branchService.getStoreCode(savedBranch);
                if (storeCode) {
                    await supplierService.loadSuppliersForBranch(storeCode);
                    
                    // Restore supplier selection
                    if (savedSuppliers && savedSuppliers.length > 0) {
                        const supplierSelect = document.getElementById(ELEMENT_IDS.SUPPLIER_SELECT);
                        if (supplierSelect) {
                            savedSuppliers.forEach(supplierId => {
                                const option = supplierSelect.querySelector(`option[value="${supplierId}"]`);
                                if (option) {
                                    option.selected = true;
                                }
                            });
                        }
                    }
                }
            }
        }
        
        // Restore sort selection
        if (savedSort) {
            const sortRadio = document.querySelector(`input[name="sortBy"][value="${savedSort}"]`);
            if (sortRadio) {
                sortRadio.checked = true;
            }
        }
        
    }

    /**
     * Setup application state and listeners
     * @private
     */
    _setupApplicationState() {
        // Subscribe to important state changes
        stateManager.subscribe('ui.isLoading', (isLoading) => {
            this._handleLoadingStateChange(isLoading);
        });
        
        stateManager.subscribe('form', (formData, oldData, path) => {
        });
        
        // Setup periodic cache cleanup
        setInterval(() => {
            this._cleanupExpiredCache();
        }, APP_CONFIG.CACHE_CLEANUP_INTERVAL);
        
        // Setup auto-save for form data
        this._setupAutoSave();
        
    }

    /**
     * Handle loading state changes
     * @private
     * @param {boolean} isLoading - Loading status
     */
    _handleLoadingStateChange(isLoading) {
        const submitBtn = document.getElementById(ELEMENT_IDS.SUBMIT_BUTTON);
        const form = document.getElementById(ELEMENT_IDS.FORM);
        
        if (isLoading) {
            // Disable form elements during loading
            dateManager.disableAll();
            uiManager.disable(ELEMENT_IDS.CABANG_SELECT);
            uiManager.disable(ELEMENT_IDS.SUPPLIER_SELECT);
            
            // Add loading class to form
            if (form) {
                form.classList.add('loading');
            }
            
        } else {
            // Re-enable form elements
            dateManager.enableAll();
            uiManager.enable(ELEMENT_IDS.CABANG_SELECT);
            uiManager.enable(ELEMENT_IDS.SUPPLIER_SELECT);
            
            // Remove loading class
            if (form) {
                form.classList.remove('loading');
            }
        }
    }

    /**
     * Setup auto-save for form data
     * @private
     */
    _setupAutoSave() {
        const autoSaveFields = [
            ELEMENT_IDS.CABANG_SELECT,
            ELEMENT_IDS.SUPPLIER_SELECT,
            ELEMENT_IDS.DATE_START,
            ELEMENT_IDS.DATE_END
        ];
        
        autoSaveFields.forEach(fieldId => {
            const field = document.getElementById(fieldId);
            if (field) {
                const saveData = formatters.debounce(() => {
                    this._saveFormData();
                }, 1000);
                
                field.addEventListener('change', saveData);
                if (field.tagName === 'INPUT') {
                    field.addEventListener('input', saveData);
                }
            }
        });
        
        // Save sort radio changes
        const sortRadios = document.querySelectorAll('input[name="sortBy"]');
        sortRadios.forEach(radio => {
            radio.addEventListener('change', () => {
                this._saveFormData();
            });
        });
    }

    /**
     * Save current form data to state
     * @private
     */
    _saveFormData() {
        try {
            const dateRange = dateManager.getDateRange();
            const branchSelect = document.getElementById(ELEMENT_IDS.CABANG_SELECT);
            const supplierSelect = document.getElementById(ELEMENT_IDS.SUPPLIER_SELECT);
            const sortBy = document.querySelector('input[name="sortBy"]:checked')?.value;
            
            const selectedSuppliers = supplierSelect ? 
                Array.from(supplierSelect.selectedOptions).map(option => option.value) : [];
            
            stateManager.update({
                'form.dateStart': dateRange.startDateStr,
                'form.dateEnd': dateRange.endDateStr,
                'form.selectedBranch': branchSelect?.value || '',
                'form.selectedSuppliers': selectedSuppliers,
                'form.sortBy': sortBy || 'Total'
            }, true);
            
        } catch (error) {
            console.warn('⚠️ Error saving form data:', error);
        }
    }

    /**
     * Cleanup expired cache entries
     * @private
     */
    _cleanupExpiredCache() {
        // This is handled by stateManager's cache mechanism
        // Just log the cleanup
    }

    /**
     * Show welcome message
     * @private
     */
    _showWelcomeMessage() {
        if (!stateManager.get('app.welcomeShown')) {
            uiManager.showInfo('Welcome to Sales Ratio Report! Select branch and suppliers to get started.', 3000);
            stateManager.set('app.welcomeShown', true);
        }
    }

    /**
     * Handle initialization errors
     * @private
     * @param {Error} error - Initialization error
     */
    _handleInitializationError(error) {
        const errorMessage = `
            <div class="alert alert-danger" role="alert">
                <h4 class="alert-heading">Application Initialization Failed</h4>
                <p>The Sales Ratio Report application could not be initialized properly.</p>
                <hr>
                <p class="mb-0">
                    <strong>Error:</strong> ${error.message}<br>
                    <small class="text-muted">Please refresh the page or contact support if the problem persists.</small>
                </p>
            </div>
        `;
        
        const container = document.getElementById(ELEMENT_IDS.FORM) || document.body;
        container.innerHTML = errorMessage;
        
        // Also try to show browser alert as fallback
        alert('Application failed to initialize: ' + error.message);
    }

    /**
     * Utility function to add delays
     * @private
     * @param {number} ms - Milliseconds to sleep
     * @returns {Promise}
     */
    _sleep(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }

    /**
     * Get application status
     * @returns {Object} Application status
     */
    getStatus() {
        return {
            initialized: this.isInitialized,
            components: Array.from(this.components.keys()),
            version: APP_CONFIG.VERSION,
            state: stateManager.getSnapshot()
        };
    }

    /**
     * Restart application
     */
    async restart() {
        
        // Cleanup existing components
        await this.cleanup();
        
        // Reinitialize
        await this.initialize();
        
        uiManager.showSuccess('Application restarted successfully');
    }

    /**
     * Cleanup application resources
     */
    async cleanup() {
        try {
            
            // Cleanup components in reverse order
            const reverseOrder = [...this.initializationOrder].reverse();
            
            for (const componentName of reverseOrder) {
                const component = this.components.get(componentName);
                if (component && typeof component.cleanup === 'function') {
                    try {
                        await component.cleanup();
                    } catch (error) {
                        console.error(`❌ Error cleaning up ${componentName}:`, error);
                    }
                }
            }
            
            // Clear component references
            this.components.clear();
            
            // Reset state
            this.isInitialized = false;
            
            
        } catch (error) {
            console.error('❌ Error during cleanup:', error);
        }
    }

    /**
     * Check if application is ready
     * @returns {boolean} Ready status
     */
    isReady() {
        return this.isInitialized && 
               this.components.size === this.initializationOrder.length;
    }

    /**
     * Get component instance
     * @param {string} componentName - Component name
     * @returns {Object|null} Component instance
     */
    getComponent(componentName) {
        return this.components.get(componentName) || null;
    }

    /**
     * Debug method to inspect application state
     */
    debug() {
        for (const [name, component] of this.components) {
            const isReady = typeof component.isReady === 'function' ? component.isReady() : 'unknown';
        }
    }
}

/**
 * Application instance
 */
const app = new SalesRatioApp();

/**
 * Auto-initialize when DOM is ready
 */
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        app.initialize().catch(error => {
            console.error('❌ Failed to auto-initialize application:', error);
        });
    });
} else {
    // DOM already loaded
    app.initialize().catch(error => {
        console.error('❌ Failed to auto-initialize application:', error);
    });
}

// Make app available globally for debugging
window.SalesRatioApp = app;

// Export for module usage
export default app;
