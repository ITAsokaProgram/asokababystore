/**
 * @fileoverview UI Manager untuk laporan sub departemen
 * @description Mengelola semua operasi UI seperti loading, notifications, visibility, dan layout
 */

import { ELEMENT_IDS } from '../config/constants.js';

/**
 * Class untuk mengelola UI operations
 * @class UIManager
 */
class UIManager {
    constructor() {
        this.isInitialized = false;
        this.loadingState = false;
    }

    /**
     * Inisialisasi UI manager
     * @returns {boolean} Status inisialisasi
     */
    initialize() {
        try {
            

            // Setup initial UI state
            this._setupInitialState();
            
            // Setup sidebar functionality
            this._setupSidebar();

            this.isInitialized = true;
            
            return true;

        } catch (error) {
            console.error('❌ Error initializing UI Manager:', error);
            return false;
        }
    }

    /**
     * Setup initial UI state
     * @private
     */
    _setupInitialState() {
        // Hide elements yang tidak perlu tampil di awal
        this.hideElement(ELEMENT_IDS.CONTAINER_BAR);
        this.hideElement(ELEMENT_IDS.CONTAINER_PIE);
        this.hideElement(ELEMENT_IDS.BTN_BACK);
        this.hideElement(ELEMENT_IDS.CONTAINER_TABLE);

        
    }

    /**
     * Setup sidebar functionality
     * @private
     */
    _setupSidebar() {
        const sidebar = document.getElementById(ELEMENT_IDS.SIDEBAR);
        const closeBtn = document.getElementById(ELEMENT_IDS.CLOSE_SIDEBAR);

        if (sidebar && closeBtn) {
            closeBtn.addEventListener('click', () => {
                sidebar.classList.remove('open');
                
            });
        }
    }

    /**
     * Show loading indicator dengan SweetAlert
     * @param {string} title - Judul loading
     * @param {string} text - Text loading
     */
    showLoading(title = 'Loading...', text = 'Tunggu Sebentar') {
        if (typeof Swal !== 'undefined') {
            this.loadingState = true;
            Swal.fire({
                title: title,
                html: text,
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                timerProgressBar: true,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
        } else {
            console.warn('⚠️ SweetAlert2 tidak tersedia');
        }
    }

    /**
     * Hide loading indicator
     */
    hideLoading() {
        if (typeof Swal !== 'undefined' && this.loadingState) {
            Swal.close();
            this.loadingState = false;
            
        }
    }

    /**
     * Show success notification
     * @param {string} title - Judul success
     * @param {string} text - Text success
     */
    showSuccess(title = 'Success!', text = 'Operasi berhasil dilakukan') {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'success',
                title: title,
                text: text,
                timer: 3000,
                timerProgressBar: true
            });
            
        }
    }

    /**
     * Show error notification
     * @param {string} title - Judul error
     * @param {string} text - Text error
     */
    showError(title = 'Error!', text = 'Terjadi kesalahan saat memproses data') {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'error',
                title: title,
                text: text,
                confirmButtonText: 'OK'
            });
            
        }
    }

    /**
     * Show warning notification
     * @param {string} title - Judul warning
     * @param {string} text - Text warning
     */
    showWarning(title = 'Warning!', text = 'Peringatan') {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'warning',
                title: title,
                text: text,
                confirmButtonText: 'OK'
            });
            
        }
    }

    /**
     * Show confirmation dialog
     * @param {string} title - Judul konfirmasi
     * @param {string} text - Text konfirmasi
     * @returns {Promise<boolean>} Result dari konfirmasi
     */
    async showConfirmation(title = 'Konfirmasi', text = 'Apakah Anda yakin?') {
        if (typeof Swal !== 'undefined') {
            const result = await Swal.fire({
                icon: 'question',
                title: title,
                text: text,
                showCancelButton: true,
                confirmButtonText: 'Ya',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33'
            });

            
            return result.isConfirmed;
        }
        return false;
    }

    /**
     * Show element by ID
     * @param {string} elementId - ID dari element
     */
    showElement(elementId) {
        const element = document.getElementById(elementId);
        if (element) {
            element.style.display = 'block';
            
        } else {
            console.warn(`⚠️ Element dengan ID '${elementId}' tidak ditemukan`);
        }
    }

    /**
     * Hide element by ID
     * @param {string} elementId - ID dari element
     */
    hideElement(elementId) {
        const element = document.getElementById(elementId);
        if (element) {
            element.style.display = 'none';
            
        } else {
            console.warn(`⚠️ Element dengan ID '${elementId}' tidak ditemukan`);
        }
    }

    /**
     * Toggle element visibility
     * @param {string} elementId - ID dari element
     */
    toggleElement(elementId) {
        const element = document.getElementById(elementId);
        if (element) {
            const isHidden = element.style.display === 'none';
            element.style.display = isHidden ? 'block' : 'none';
            
        } else {
            console.warn(`⚠️ Element dengan ID '${elementId}' tidak ditemukan`);
        }
    }

    /**
     * Update report header untuk subdept
     * @param {string} cabangText - Text cabang
     * @param {string} startDate - Tanggal mulai
     * @param {string} endDate - Tanggal akhir
     * @param {string} headerType - Jenis header (default/promo)
     */
    updateReportHeader(cabangText, startDate, endDate, headerType = 'default') {
        let headerId, headerText;

        if (headerType === 'promo') {
            headerId = ELEMENT_IDS.REPORT_HEADER_PROMO;
            headerText = `Data Promo<br><p>Cabang: ${cabangText} (${startDate} s/d ${endDate})</p>`;
        } else {
            // Default header (bisa ditambah jika ada element ID nya)
            headerText = `Data Sub Departemen<br><p>Cabang: ${cabangText} (${startDate} s/d ${endDate})</p>`;
        }

        const headerElement = document.getElementById(headerId);
        if (headerElement) {
            headerElement.innerHTML = headerText;
            
        } else if (headerId) {
            console.warn(`⚠️ Header element dengan ID '${headerId}' tidak ditemukan`);
        }
    }

    /**
     * Setup UI untuk mode subdept
     */
    prepareSubdeptMode() {
        this.showElement(ELEMENT_IDS.CONTAINER_BAR);
        this.showElement(ELEMENT_IDS.CONTAINER_PIE);
        this.showElement(ELEMENT_IDS.CONTAINER_TABLE);
        
    }

    /**
     * Setup UI untuk mode promo
     */
    preparePromoMode() {
        this.showElement(ELEMENT_IDS.BTN_BACK);
        this.showElement(ELEMENT_IDS.CONTAINER_TABLE);
        
    }

    /**
     * Reset UI ke state awal
     */
    resetToInitialState() {
        this.hideElement(ELEMENT_IDS.CONTAINER_BAR);
        this.hideElement(ELEMENT_IDS.CONTAINER_PIE);
        this.hideElement(ELEMENT_IDS.BTN_BACK);
        this.hideElement(ELEMENT_IDS.CONTAINER_TABLE);
        
    }

    /**
     * Disable/Enable button
     * @param {string} buttonId - ID dari button
     * @param {boolean} disabled - Status disabled
     */
    setButtonState(buttonId, disabled) {
        const button = document.getElementById(buttonId);
        if (button) {
            button.disabled = disabled;
            if (disabled) {
                button.classList.add('disabled');
            } else {
                button.classList.remove('disabled');
            }
            
        }
    }

    /**
     * Update button text
     * @param {string} buttonId - ID dari button
     * @param {string} text - Text baru
     */
    updateButtonText(buttonId, text) {
        const button = document.getElementById(buttonId);
        if (button) {
            button.textContent = text;
            
        }
    }

    /**
     * Add loading spinner ke button
     * @param {string} buttonId - ID dari button
     */
    addButtonSpinner(buttonId) {
        const button = document.getElementById(buttonId);
        if (button) {
            const originalText = button.textContent;
            button.innerHTML = `<span class="spinner-border spinner-border-sm" role="status"></span> ${originalText}`;
            button.disabled = true;
            
        }
    }

    /**
     * Remove loading spinner dari button
     * @param {string} buttonId - ID dari button
     * @param {string} originalText - Text original
     */
    removeButtonSpinner(buttonId, originalText) {
        const button = document.getElementById(buttonId);
        if (button) {
            button.textContent = originalText;
            button.disabled = false;
            
        }
    }

    /**
     * Populate select options dengan data branch
     * @param {string} selectId - ID dari select element
     * @param {Array} options - Array of option objects
     * @param {boolean} clearFirst - Clear existing options first
     */
    populateSelectOptions(selectId, options, clearFirst = false) {
        try {
            const selectElement = document.getElementById(selectId);
            if (!selectElement) {
                console.warn(`⚠️ Select element dengan ID '${selectId}' tidak ditemukan`);
                return;
            }

            // Clear existing options if needed
            if (clearFirst) {
                selectElement.innerHTML = '';
            }

            // Add new options
            options.forEach(option => {
                const optionElement = document.createElement('option');
                optionElement.value = option.value;
                optionElement.textContent = option.text;
                
                // Add data attributes if available
                if (option.storeCode) {
                    optionElement.setAttribute('data-store-code', option.storeCode);
                }
                if (option.isAll) {
                    optionElement.setAttribute('data-is-all', 'true');
                }
                
                selectElement.appendChild(optionElement);
            });

            

        } catch (error) {
            console.error(`❌ Error populating select ${selectId}:`, error);
        }
    }

    /**
     * Update store code input berdasarkan selected branch
     * @param {string} branchName - Nama cabang yang dipilih
     * @param {Object} storeCodes - Mapping store codes
     */
    updateStoreCodeInput(branchName, storeCodes) {
        try {
            const storeCodeElement = document.getElementById(ELEMENT_IDS.KD_STORE);
            if (!storeCodeElement) {
                console.warn('⚠️ Store code input element tidak ditemukan');
                return;
            }

            if (branchName === 'SEMUA CABANG' || !branchName) {
                storeCodeElement.value = '';
                
            } else {
                const storeCode = storeCodes[branchName];
                if (storeCode) {
                    storeCodeElement.value = storeCode;
                    
                } else {
                    console.warn(`⚠️ Store code tidak ditemukan untuk cabang: ${branchName}`);
                }
            }

        } catch (error) {
            console.error('❌ Error updating store code input:', error);
        }
    }

    /**
     * Get element value dengan error handling
     * @param {string} elementId - ID dari element
     * @returns {string} Value dari element
     */
    getElementValue(elementId) {
        const element = document.getElementById(elementId);
        if (!element) {
            console.warn(`⚠️ Element dengan ID '${elementId}' tidak ditemukan`);
            return '';
        }
        return element.value || '';
    }

    /**
     * Set element value
     * @param {string} elementId - ID dari element
     * @param {string} value - Value yang akan diset
     */
    setElementValue(elementId, value) {
        const element = document.getElementById(elementId);
        if (element) {
            element.value = value;
            
        } else {
            console.warn(`⚠️ Element dengan ID '${elementId}' tidak ditemukan`);
        }
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
const uiManager = new UIManager();
export default uiManager;
