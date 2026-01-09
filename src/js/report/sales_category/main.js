import chartManager from './components/chartManager.js';
import tableManager from './components/tableManager.js';
import uiManager from './components/uiManager.js';
import eventHandlers from './handlers/eventHandlers.js';
import dateManager from './utils/dateManager.js';
import salesCategoryState from './utils/state.js';
import branchService from './services/branchService.js';

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

    async initialize() {
        try {
            await branchService.loadStoreCodes();
            if (!this.components.ui.initialize()) {
                throw new Error('Failed to initialize UI Manager');
            }
            if (!this.components.chart.initialize()) {
                throw new Error('Failed to initialize Chart Manager');
            }
            if (!this.components.table.initialize()) {
                console.warn('Table Manager initialization failed, continuing...');
            }
            if (!this.components.date.initialize()) {
                console.warn('Date Manager initialization failed, continuing...');
            }
            if (!this.components.events.initialize()) {
                throw new Error('Failed to initialize Event Handlers');
            }
            this.isInitialized = true;
            return true;
        } catch (error) {
            console.error('❌ Failed to initialize Sales Category Application:', error);
            this.components.ui.showError(
                'Gagal Memuat Aplikasi',
                'Terjadi kesalahan saat memuat aplikasi. Silakan refresh halaman.'
            );
            return false;
        }
    }

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

    async restart() {
        this.dispose();
        return await this.initialize();
    }

    reset() {
        salesCategoryState.clearCache();
        this.components.ui.setEarlyMode();
        this.components.chart.hide();
        this.components.table.hide();
        this.components.date.resetToDefault();
    }

    dispose() {
        Object.values(this.components).forEach(component => {
            if (component && typeof component.dispose === 'function') {
                component.dispose();
            }
        });
        salesCategoryState.clearCache();
        this.isInitialized = false;
    }

    handleError(error, context = 'Unknown') {
        console.error(`Application Error [${context}]:`, error);
        this.components.ui.showError(
            'Terjadi Kesalahan',
            `Error: ${error.message || 'Unknown error'}`
        );
    }

    getComponent(componentName) {
        return this.components[componentName] || null;
    }
    isReady() {
        return this.isInitialized &&
            this.components.ui.isInitialized &&
            this.components.chart.isInitialized;
    }
}
window.addEventListener('error', (event) => {
    console.error('Global Error:', event.error);
    if (window.salesCategoryApp) {
        window.salesCategoryApp.handleError(event.error, 'Global');
    }
});
window.addEventListener('unhandledrejection', (event) => {
    console.error('Unhandled Promise Rejection:', event.reason);
    if (window.salesCategoryApp) {
        window.salesCategoryApp.handleError(
            new Error(event.reason),
            'Promise Rejection'
        );
    }
});
document.addEventListener('DOMContentLoaded', async () => {
    window.salesCategoryApp = new SalesCategoryApp();
    const success = await window.salesCategoryApp.initialize();
    if (success) {
    } else {
        console.error('Failed to start Sales Category Application');
    }
});
window.addEventListener('beforeunload', () => {
    if (window.salesCategoryApp) {
        window.salesCategoryApp.dispose();
    }
});
export default SalesCategoryApp;
