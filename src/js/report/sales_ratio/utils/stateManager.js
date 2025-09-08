/**
 * @fileoverview State Manager untuk Sales Ratio Report
 * @description Mengelola application state, caching, dan persistence
 */

import { STORAGE_KEYS, SORT_OPTIONS } from '../config/constants.js';
import { debounce } from './formatters.js';

/**
 * Class untuk mengelola application state
 * @class StateManager
 */
class StateManager {
    constructor() {
        this.state = this._initializeState();
        this.listeners = new Map();
        this.isInitialized = false;
        
        // Debounced save function
        this._debouncedSave = debounce(() => this._saveToStorage(), 500);
    }

    /**
     * Initialize default state
     * @private
     * @returns {Object} Initial state
     */
    _initializeState() {
        return {
            // Form data
            form: {
                dateStart: '',
                dateEnd: '',
                selectedBranch: '',
                selectedSuppliers: [],
                sortBy: SORT_OPTIONS.TOTAL
            },
            
            // UI state
            ui: {
                isLoading: false,
                sidebarOpen: false,
                activeView: 'form', // form, chart, table
                errors: [],
                notifications: []
            },
            
            // Data cache
            cache: {
                branches: null,
                suppliers: null,
                chartData: null,
                tableData: null,
                lastUpdated: null
            },
            
            // Application state
            app: {
                initialized: false,
                currentRequest: null,
                requestHistory: [],
                settings: {
                    autoRefresh: false,
                    exportFormat: 'xlsx',
                    theme: 'light'
                }
            }
        };
    }

    /**
     * Initialize state manager
     * @returns {boolean} Success status
     */
    initialize() {
        try {
            console.log('📦 Initializing State Manager...');
            
            // Load state from storage
            this._loadFromStorage();
            
            // Setup storage event listener
            this._setupStorageListener();
            
            this.isInitialized = true;
            console.log('✅ State Manager initialized successfully');
            return true;
            
        } catch (error) {
            console.error('❌ Error initializing State Manager:', error);
            return false;
        }
    }

    /**
     * Load state from localStorage
     * @private
     */
    _loadFromStorage() {
        try {
            const saved = localStorage.getItem(STORAGE_KEYS.APP_STATE);
            if (saved) {
                const parsedState = JSON.parse(saved);
                this.state = { ...this.state, ...parsedState };
                console.log('📦 State loaded from storage');
            }
        } catch (error) {
            console.warn('⚠️ Error loading state from storage:', error);
        }
    }

    /**
     * Save state to localStorage
     * @private
     */
    _saveToStorage() {
        try {
            // Don't save sensitive data or temporary state
            const stateToSave = {
                form: this.state.form,
                ui: {
                    sidebarOpen: this.state.ui.sidebarOpen,
                    activeView: this.state.ui.activeView
                },
                app: {
                    settings: this.state.app.settings
                }
            };
            
            localStorage.setItem(STORAGE_KEYS.APP_STATE, JSON.stringify(stateToSave));
            console.log('💾 State saved to storage');
        } catch (error) {
            console.warn('⚠️ Error saving state to storage:', error);
        }
    }

    /**
     * Setup storage event listener for cross-tab synchronization
     * @private
     */
    _setupStorageListener() {
        window.addEventListener('storage', (e) => {
            if (e.key === STORAGE_KEYS.APP_STATE && e.newValue) {
                try {
                    const newState = JSON.parse(e.newValue);
                    this.state = { ...this.state, ...newState };
                    this._notifyListeners('storage_sync', this.state);
                } catch (error) {
                    console.warn('⚠️ Error syncing state from storage:', error);
                }
            }
        });
    }

    /**
     * Get state value
     * @param {string} path - State path (e.g., 'form.dateStart', 'ui.isLoading')
     * @returns {any} State value
     */
    get(path) {
        const keys = path.split('.');
        let current = this.state;
        
        for (const key of keys) {
            if (current && typeof current === 'object' && key in current) {
                current = current[key];
            } else {
                return undefined;
            }
        }
        
        return current;
    }

    /**
     * Set state value
     * @param {string} path - State path
     * @param {any} value - New value
     * @param {boolean} persist - Save to storage
     */
    set(path, value, persist = true) {
        const keys = path.split('.');
        let current = this.state;
        
        // Navigate to the parent of the target property
        for (let i = 0; i < keys.length - 1; i++) {
            const key = keys[i];
            if (!(key in current) || typeof current[key] !== 'object') {
                current[key] = {};
            }
            current = current[key];
        }
        
        // Set the value
        const lastKey = keys[keys.length - 1];
        const oldValue = current[lastKey];
        current[lastKey] = value;
        
        // Notify listeners
        this._notifyListeners(path, value, oldValue);
        
        // Auto-save if enabled
        if (persist) {
            this._debouncedSave();
        }
        
        console.log(`📦 State updated: ${path} =`, value);
    }

    /**
     * Update multiple state values
     * @param {Object} updates - Object with path-value pairs
     * @param {boolean} persist - Save to storage
     */
    update(updates, persist = true) {
        for (const [path, value] of Object.entries(updates)) {
            this.set(path, value, false); // Don't persist individually
        }
        
        if (persist) {
            this._debouncedSave();
        }
    }

    /**
     * Reset state to default
     * @param {string} path - Optional path to reset (resets all if not provided)
     */
    reset(path = null) {
        if (path) {
            const defaultState = this._initializeState();
            const defaultValue = this.get.call({ state: defaultState }, path);
            this.set(path, defaultValue);
        } else {
            this.state = this._initializeState();
            this._saveToStorage();
            this._notifyListeners('reset', this.state);
        }
        
        console.log(`🔄 State reset${path ? ` for ${path}` : ''}`);
    }

    /**
     * Subscribe to state changes
     * @param {string} path - State path to watch
     * @param {Function} callback - Callback function
     * @returns {Function} Unsubscribe function
     */
    subscribe(path, callback) {
        if (!this.listeners.has(path)) {
            this.listeners.set(path, new Set());
        }
        
        this.listeners.get(path).add(callback);
        
        // Return unsubscribe function
        return () => {
            const pathListeners = this.listeners.get(path);
            if (pathListeners) {
                pathListeners.delete(callback);
                if (pathListeners.size === 0) {
                    this.listeners.delete(path);
                }
            }
        };
    }

    /**
     * Notify listeners of state changes
     * @private
     * @param {string} path - Changed path
     * @param {any} newValue - New value
     * @param {any} oldValue - Old value
     */
    _notifyListeners(path, newValue, oldValue) {
        // Notify exact path listeners
        const exactListeners = this.listeners.get(path);
        if (exactListeners) {
            exactListeners.forEach(callback => {
                try {
                    callback(newValue, oldValue, path);
                } catch (error) {
                    console.error('❌ Error in state listener:', error);
                }
            });
        }
        
        // Notify wildcard listeners (path starts with listener path)
        for (const [listenerPath, callbacks] of this.listeners) {
            if (listenerPath !== path && path.startsWith(listenerPath + '.')) {
                callbacks.forEach(callback => {
                    try {
                        callback(newValue, oldValue, path);
                    } catch (error) {
                        console.error('❌ Error in wildcard state listener:', error);
                    }
                });
            }
        }
    }

    /**
     * Cache data dengan expiry
     * @param {string} key - Cache key
     * @param {any} data - Data to cache
     * @param {number} ttl - Time to live in ms (optional)
     */
    setCache(key, data, ttl = null) {
        const cacheItem = {
            data,
            timestamp: Date.now(),
            ttl
        };
        
        this.set(`cache.${key}`, cacheItem, false);
        
        // Also store in sessionStorage for faster access
        try {
            const storageKey = `${STORAGE_KEYS.CACHE_PREFIX}${key}`;
            sessionStorage.setItem(storageKey, JSON.stringify(cacheItem));
        } catch (error) {
            console.warn('⚠️ Error storing cache in sessionStorage:', error);
        }
        
        console.log(`💾 Data cached: ${key}`);
    }

    /**
     * Get cached data
     * @param {string} key - Cache key
     * @returns {any} Cached data or null if expired/not found
     */
    getCache(key) {
        // Try sessionStorage first
        try {
            const storageKey = `${STORAGE_KEYS.CACHE_PREFIX}${key}`;
            const cached = sessionStorage.getItem(storageKey);
            if (cached) {
                const cacheItem = JSON.parse(cached);
                if (this._isCacheValid(cacheItem)) {
                    return cacheItem.data;
                } else {
                    sessionStorage.removeItem(storageKey);
                }
            }
        } catch (error) {
            console.warn('⚠️ Error reading cache from sessionStorage:', error);
        }
        
        // Fallback to state
        const cacheItem = this.get(`cache.${key}`);
        if (cacheItem && this._isCacheValid(cacheItem)) {
            return cacheItem.data;
        }
        
        return null;
    }

    /**
     * Check if cache item is valid
     * @private
     * @param {Object} cacheItem - Cache item
     * @returns {boolean} Is valid
     */
    _isCacheValid(cacheItem) {
        if (!cacheItem || !cacheItem.timestamp) return false;
        if (!cacheItem.ttl) return true; // No expiry
        
        return (Date.now() - cacheItem.timestamp) < cacheItem.ttl;
    }

    /**
     * Clear cache
     * @param {string} key - Specific key to clear (clears all if not provided)
     */
    clearCache(key = null) {
        if (key) {
            this.set(`cache.${key}`, null, false);
            
            try {
                const storageKey = `${STORAGE_KEYS.CACHE_PREFIX}${key}`;
                sessionStorage.removeItem(storageKey);
            } catch (error) {
                console.warn('⚠️ Error clearing cache from sessionStorage:', error);
            }
            
            console.log(`🗑️ Cache cleared: ${key}`);
        } else {
            this.set('cache', {}, false);
            
            // Clear all cache from sessionStorage
            try {
                const keysToRemove = [];
                for (let i = 0; i < sessionStorage.length; i++) {
                    const key = sessionStorage.key(i);
                    if (key && key.startsWith(STORAGE_KEYS.CACHE_PREFIX)) {
                        keysToRemove.push(key);
                    }
                }
                keysToRemove.forEach(key => sessionStorage.removeItem(key));
            } catch (error) {
                console.warn('⚠️ Error clearing cache from sessionStorage:', error);
            }
            
            console.log('🗑️ All cache cleared');
        }
    }

    /**
     * Get application state snapshot
     * @returns {Object} Complete state
     */
    getSnapshot() {
        return JSON.parse(JSON.stringify(this.state));
    }

    /**
     * Import state from external source
     * @param {Object} importedState - State to import
     * @param {boolean} merge - Merge with current state or replace
     */
    importState(importedState, merge = true) {
        if (merge) {
            this.state = { ...this.state, ...importedState };
        } else {
            this.state = { ...this._initializeState(), ...importedState };
        }
        
        this._saveToStorage();
        this._notifyListeners('import', this.state);
        
        console.log('📥 State imported');
    }

    /**
     * Export state untuk backup/debugging
     * @returns {string} Serialized state
     */
    exportState() {
        return JSON.stringify(this.state, null, 2);
    }

    /**
     * Add request to history
     * @param {Object} request - Request details
     */
    addToHistory(request) {
        const history = this.get('app.requestHistory') || [];
        const requestWithTimestamp = {
            ...request,
            timestamp: Date.now(),
            id: `req_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`
        };
        
        // Keep only last 50 requests
        const updatedHistory = [requestWithTimestamp, ...history].slice(0, 50);
        this.set('app.requestHistory', updatedHistory);
        
        console.log('📋 Request added to history');
    }

    /**
     * Get request history
     * @param {number} limit - Number of recent requests
     * @returns {Array} Request history
     */
    getHistory(limit = 10) {
        const history = this.get('app.requestHistory') || [];
        return history.slice(0, limit);
    }

    /**
     * Clear request history
     */
    clearHistory() {
        this.set('app.requestHistory', []);
        console.log('🗑️ Request history cleared');
    }

    /**
     * Check if state manager is ready
     * @returns {boolean} Ready status
     */
    isReady() {
        return this.isInitialized;
    }

    /**
     * Cleanup state manager
     */
    cleanup() {
        // Clear all listeners
        this.listeners.clear();
        
        // Save final state
        this._saveToStorage();
        
        console.log('🧹 State Manager cleaned up');
    }

    /**
     * Debug helper - log current state
     */
    debug() {
        console.log('🔍 Current State:', this.state);
        console.log('🔍 Listeners:', this.listeners);
        console.log('🔍 Cache Status:', Object.keys(this.state.cache || {}));
    }
}

// Create singleton instance
const stateManager = new StateManager();

export default stateManager;
