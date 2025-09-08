/**
 * @fileoverview UI Manager untuk Sales Ratio Report
 * @description Mengelola operasi UI, loading states, dan user feedback
 */

import { ELEMENT_IDS, UI_CONFIG, STORAGE_KEYS } from '../config/constants.js';

/**
 * Class untuk mengelola UI operations
 * @class UIManager
 */
class UIManager {
    constructor() {
        this.isInitialized = false;
        this.loadingStates = new Map();
        this.sidebarState = this._loadSidebarState();
    }

    /**
     * Initialize UI manager
     * @returns {boolean} Success status
     */
    initialize() {
        try {
            console.log('🎨 Initializing UI Manager...');
            
            // Setup UI event listeners
            this._setupUIEvents();
            
            // Restore sidebar state
            this._applySidebarState();
            
            // Setup responsive handlers
            this._setupResponsiveHandlers();
            
            this.isInitialized = true;
            console.log('✅ UI Manager initialized successfully');
            return true;
            
        } catch (error) {
            console.error('❌ Error initializing UI Manager:', error);
            return false;
        }
    }

    /**
     * Setup UI event listeners
     * @private
     */
    _setupUIEvents() {
        // Sidebar toggle
        const sidebarToggle = document.getElementById(ELEMENT_IDS.SIDEBAR_TOGGLE);
        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', () => this.toggleSidebar());
        }

        // Form submission prevention on Enter key
        this._setupFormEvents();
        
        // Tooltip initialization
        this._initializeTooltips();
    }

    /**
     * Setup form events
     * @private
     */
    _setupFormEvents() {
        const form = document.getElementById(ELEMENT_IDS.FORM);
        if (form) {
            form.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    // Trigger form submission through submit button
                    const submitBtn = document.getElementById(ELEMENT_IDS.SUBMIT_BUTTON);
                    if (submitBtn && !submitBtn.disabled) {
                        submitBtn.click();
                    }
                }
            });
        }
    }

    /**
     * Initialize tooltips
     * @private
     */
    _initializeTooltips() {
        // Initialize Bootstrap tooltips if available
        if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        }
    }

    /**
     * Setup responsive handlers
     * @private
     */
    _setupResponsiveHandlers() {
        let resizeTimeout;
        window.addEventListener('resize', () => {
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(() => {
                this._handleResize();
            }, UI_CONFIG.RESIZE_DEBOUNCE);
        });
    }

    /**
     * Handle window resize
     * @private
     */
    _handleResize() {
        // Auto-hide sidebar on mobile
        if (window.innerWidth < UI_CONFIG.MOBILE_BREAKPOINT) {
            if (this.sidebarState.isOpen) {
                this.closeSidebar(false); // Don't save state for mobile
            }
        }
    }

    /**
     * Show loading state untuk specific element
     * @param {string} elementId - Element ID
     * @param {string} message - Loading message
     */
    showLoading(elementId, message = 'Loading...') {
        const element = document.getElementById(elementId);
        if (!element) {
            console.warn(`⚠️ Element ${elementId} not found for loading state`);
            return;
        }

        // Store original state
        const originalState = {
            innerHTML: element.innerHTML,
            disabled: element.disabled,
            className: element.className
        };
        this.loadingStates.set(elementId, originalState);

        // Apply loading state
        if (element.tagName === 'BUTTON') {
            element.disabled = true;
            element.innerHTML = `<i class="fas fa-spinner fa-spin me-1"></i>${message}`;
        } else {
            element.innerHTML = `
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">${message}</span>
                    </div>
                    <div class="mt-2 text-muted">${message}</div>
                </div>
            `;
        }

        console.log(`🔄 Loading state applied to ${elementId}`);
    }

    /**
     * Hide loading state untuk specific element
     * @param {string} elementId - Element ID
     */
    hideLoading(elementId) {
        const element = document.getElementById(elementId);
        const originalState = this.loadingStates.get(elementId);

        if (!element || !originalState) {
            console.warn(`⚠️ Cannot restore loading state for ${elementId}`);
            return;
        }

        // Restore original state
        element.innerHTML = originalState.innerHTML;
        element.disabled = originalState.disabled;
        element.className = originalState.className;

        // Clean up stored state
        this.loadingStates.delete(elementId);

        console.log(`✅ Loading state removed from ${elementId}`);
    }

    /**
     * Show success notification
     * @param {string} message - Success message
     * @param {number} duration - Duration in ms
     */
    showSuccess(message, duration = UI_CONFIG.NOTIFICATION_DURATION) {
        this._showNotification(message, 'success', duration);
    }

    /**
     * Show error notification
     * @param {string} message - Error message
     * @param {number} duration - Duration in ms
     */
    showError(message, duration = UI_CONFIG.NOTIFICATION_DURATION) {
        this._showNotification(message, 'error', duration);
    }

    /**
     * Show warning notification
     * @param {string} message - Warning message
     * @param {number} duration - Duration in ms
     */
    showWarning(message, duration = UI_CONFIG.NOTIFICATION_DURATION) {
        this._showNotification(message, 'warning', duration);
    }

    /**
     * Show info notification
     * @param {string} message - Info message
     * @param {number} duration - Duration in ms
     */
    showInfo(message, duration = UI_CONFIG.NOTIFICATION_DURATION) {
        this._showNotification(message, 'info', duration);
    }

    /**
     * Show notification
     * @private
     * @param {string} message - Notification message
     * @param {string} type - Notification type (success, error, warning, info)
     * @param {number} duration - Duration in ms
     */
    _showNotification(message, type, duration) {
        // Try SweetAlert2 first
        if (typeof Swal !== 'undefined') {
            const config = {
                text: message,
                timer: duration,
                timerProgressBar: true,
                showConfirmButton: false,
                position: 'top-end',
                toast: true
            };

            switch (type) {
                case 'success':
                    config.icon = 'success';
                    break;
                case 'error':
                    config.icon = 'error';
                    break;
                case 'warning':
                    config.icon = 'warning';
                    break;
                case 'info':
                    config.icon = 'info';
                    break;
            }

            Swal.fire(config);
        } else {
            // Fallback to browser alert
            alert(`${type.toUpperCase()}: ${message}`);
        }

        console.log(`📢 ${type.toUpperCase()}: ${message}`);
    }

    /**
     * Show confirmation dialog
     * @param {string} title - Dialog title
     * @param {string} text - Dialog text
     * @param {string} confirmButtonText - Confirm button text
     * @returns {Promise<boolean>} User confirmed
     */
    async showConfirmation(title, text, confirmButtonText = 'Ya') {
        if (typeof Swal !== 'undefined') {
            const result = await Swal.fire({
                title: title,
                text: text,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: confirmButtonText,
                cancelButtonText: 'Batal',
                reverseButtons: true
            });
            return result.isConfirmed;
        } else {
            // Fallback to browser confirm
            return confirm(`${title}\n\n${text}`);
        }
    }

    /**
     * Toggle sidebar
     */
    toggleSidebar() {
        if (this.sidebarState.isOpen) {
            this.closeSidebar();
        } else {
            this.openSidebar();
        }
    }

    /**
     * Open sidebar
     * @param {boolean} saveState - Save state to localStorage
     */
    openSidebar(saveState = true) {
        const sidebar = document.getElementById(ELEMENT_IDS.SIDEBAR);
        const sidebarOverlay = document.getElementById(ELEMENT_IDS.SIDEBAR_OVERLAY);
        
        if (sidebar) {
            sidebar.classList.add('show');
            
            if (sidebarOverlay) {
                sidebarOverlay.classList.add('show');
            }
            
            this.sidebarState.isOpen = true;
            
            if (saveState) {
                this._saveSidebarState();
            }
            
            console.log('📂 Sidebar opened');
        }
    }

    /**
     * Close sidebar
     * @param {boolean} saveState - Save state to localStorage
     */
    closeSidebar(saveState = true) {
        const sidebar = document.getElementById(ELEMENT_IDS.SIDEBAR);
        const sidebarOverlay = document.getElementById(ELEMENT_IDS.SIDEBAR_OVERLAY);
        
        if (sidebar) {
            sidebar.classList.remove('show');
            
            if (sidebarOverlay) {
                sidebarOverlay.classList.remove('show');
            }
            
            this.sidebarState.isOpen = false;
            
            if (saveState) {
                this._saveSidebarState();
            }
            
            console.log('📁 Sidebar closed');
        }
    }

    /**
     * Load sidebar state from localStorage
     * @private
     * @returns {Object} Sidebar state
     */
    _loadSidebarState() {
        try {
            const saved = localStorage.getItem(STORAGE_KEYS.SIDEBAR_STATE);
            if (saved) {
                return JSON.parse(saved);
            }
        } catch (error) {
            console.warn('⚠️ Error loading sidebar state:', error);
        }
        
        return { isOpen: window.innerWidth >= UI_CONFIG.DESKTOP_BREAKPOINT };
    }

    /**
     * Save sidebar state to localStorage
     * @private
     */
    _saveSidebarState() {
        try {
            localStorage.setItem(STORAGE_KEYS.SIDEBAR_STATE, JSON.stringify(this.sidebarState));
        } catch (error) {
            console.warn('⚠️ Error saving sidebar state:', error);
        }
    }

    /**
     * Apply saved sidebar state
     * @private
     */
    _applySidebarState() {
        if (this.sidebarState.isOpen && window.innerWidth >= UI_CONFIG.DESKTOP_BREAKPOINT) {
            this.openSidebar(false);
        } else {
            this.closeSidebar(false);
        }
    }

    /**
     * Set element value
     * @param {string} elementId - Element ID
     * @param {any} value - Value to set
     */
    setValue(elementId, value) {
        const element = document.getElementById(elementId);
        if (!element) {
            console.warn(`⚠️ Element ${elementId} not found`);
            return;
        }

        if (element.tagName === 'SELECT') {
            element.value = value;
            // Trigger change event
            element.dispatchEvent(new Event('change'));
        } else if (element.tagName === 'INPUT') {
            element.value = value;
            // Trigger input event
            element.dispatchEvent(new Event('input'));
        } else {
            element.textContent = value;
        }
    }

    /**
     * Get element value
     * @param {string} elementId - Element ID
     * @returns {any} Element value
     */
    getValue(elementId) {
        const element = document.getElementById(elementId);
        if (!element) {
            console.warn(`⚠️ Element ${elementId} not found`);
            return null;
        }

        if (element.tagName === 'SELECT' || element.tagName === 'INPUT') {
            return element.value;
        } else {
            return element.textContent;
        }
    }

    /**
     * Enable element
     * @param {string} elementId - Element ID
     */
    enable(elementId) {
        const element = document.getElementById(elementId);
        if (element) {
            element.disabled = false;
            element.classList.remove('disabled');
        }
    }

    /**
     * Disable element
     * @param {string} elementId - Element ID
     */
    disable(elementId) {
        const element = document.getElementById(elementId);
        if (element) {
            element.disabled = true;
            element.classList.add('disabled');
        }
    }

    /**
     * Show element
     * @param {string} elementId - Element ID
     */
    show(elementId) {
        const element = document.getElementById(elementId);
        if (element) {
            element.style.display = '';
            element.classList.remove('d-none');
        }
    }

    /**
     * Hide element
     * @param {string} elementId - Element ID
     */
    hide(elementId) {
        const element = document.getElementById(elementId);
        if (element) {
            element.style.display = 'none';
            element.classList.add('d-none');
        }
    }

    /**
     * Add CSS class to element
     * @param {string} elementId - Element ID
     * @param {string} className - CSS class
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
     * @param {string} className - CSS class
     */
    removeClass(elementId, className) {
        const element = document.getElementById(elementId);
        if (element) {
            element.classList.remove(className);
        }
    }

    /**
     * Focus on element
     * @param {string} elementId - Element ID
     */
    focus(elementId) {
        const element = document.getElementById(elementId);
        if (element) {
            element.focus();
        }
    }

    /**
     * Scroll to element
     * @param {string} elementId - Element ID
     * @param {Object} options - Scroll options
     */
    scrollTo(elementId, options = { behavior: 'smooth' }) {
        const element = document.getElementById(elementId);
        if (element) {
            element.scrollIntoView(options);
        }
    }

    /**
     * Clear all loading states
     */
    clearAllLoadingStates() {
        for (const [elementId] of this.loadingStates) {
            this.hideLoading(elementId);
        }
        console.log('🧹 All loading states cleared');
    }

    /**
     * Reset form
     * @param {string} formId - Form ID
     */
    resetForm(formId = ELEMENT_IDS.FORM) {
        const form = document.getElementById(formId);
        if (form) {
            form.reset();
            
            // Trigger change events for select elements
            const selects = form.querySelectorAll('select');
            selects.forEach(select => {
                select.dispatchEvent(new Event('change'));
            });
            
            console.log('📝 Form reset');
        }
    }

    /**
     * Get form data
     * @param {string} formId - Form ID
     * @returns {Object} Form data
     */
    getFormData(formId = ELEMENT_IDS.FORM) {
        const form = document.getElementById(formId);
        if (!form) {
            console.warn(`⚠️ Form ${formId} not found`);
            return {};
        }

        const formData = new FormData(form);
        const data = {};
        
        for (let [key, value] of formData.entries()) {
            data[key] = value;
        }
        
        return data;
    }

    /**
     * Validate form
     * @param {string} formId - Form ID
     * @returns {boolean} Is valid
     */
    validateForm(formId = ELEMENT_IDS.FORM) {
        const form = document.getElementById(formId);
        if (!form) {
            console.warn(`⚠️ Form ${formId} not found`);
            return false;
        }

        return form.checkValidity();
    }

    /**
     * Check if UI is ready
     * @returns {boolean} Ready status
     */
    isReady() {
        return this.isInitialized;
    }

    /**
     * Get sidebar state
     * @returns {Object} Sidebar state
     */
    getSidebarState() {
        return { ...this.sidebarState };
    }

    /**
     * Cleanup resources
     */
    cleanup() {
        this.clearAllLoadingStates();
        
        // Remove event listeners
        window.removeEventListener('resize', this._handleResize);
        
        console.log('🧹 UI Manager cleaned up');
    }
}

// Create singleton instance
const uiManager = new UIManager();

export default uiManager;
