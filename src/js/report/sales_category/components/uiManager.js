
import { ELEMENT_IDS } from '../config/constants.js';


class UIManager {
    constructor() {
        this.elements = {};
        this.isInitialized = false;
    }


    initialize() {
        try {
            this.elements = {
                chartDiagram: document.getElementById(ELEMENT_IDS.CHART_DIAGRAM),
                wrapperTable: document.getElementById(ELEMENT_IDS.WRAPPER_TABLE),
                btnBack: document.getElementById(ELEMENT_IDS.BTN_BACK),
                btnSend: document.getElementById(ELEMENT_IDS.BTN_SEND),
                sortFilter: document.getElementById(ELEMENT_IDS.SORT_FILTER),
                sortFilter1: document.getElementById(ELEMENT_IDS.SORT_FILTER1),
                dateStart: document.getElementById(ELEMENT_IDS.DATE_START),
                dateEnd: document.getElementById(ELEMENT_IDS.DATE_END),
                branchSelect: document.getElementById(ELEMENT_IDS.BRANCH_SELECT)
            };

            // Check missing elements
            const missingElements = Object.entries(this.elements)
                .filter(([key, element]) => !element)
                .map(([key]) => key);

            if (missingElements.length > 0) {
                console.warn('Missing UI elements:', missingElements);
            }

            this.isInitialized = true;
            this._initializeDefaultStates();

            return true;
        } catch (error) {
            console.error('Failed to initialize UI Manager:', error);
            return false;
        }
    }


    _initializeDefaultStates() {
        this.hideElement(ELEMENT_IDS.WRAPPER_TABLE);
        this.hideElement(ELEMENT_IDS.BTN_BACK);
        this.hideElement(ELEMENT_IDS.SORT_FILTER);
        this.hideElement(ELEMENT_IDS.SORT_FILTER1);
        this.hideElement(ELEMENT_IDS.CHART_DIAGRAM);
    }


    showElement(elementId, displayType = 'block') {
        const element = document.getElementById(elementId);
        if (element) {
            element.style.display = displayType;
        }
    }


    hideElement(elementId) {
        const element = document.getElementById(elementId);
        if (element) {
            element.style.display = 'none';
        }
    }


    toggleElement(elementId, displayType = 'block') {
        const element = document.getElementById(elementId);
        if (element) {
            if (element.style.display === 'none' || !element.style.display) {
                element.style.display = displayType;
            } else {
                element.style.display = 'none';
            }
        }
    }


    setEarlyMode() {
        this.hideElement(ELEMENT_IDS.BTN_BACK);
        this.hideElement(ELEMENT_IDS.WRAPPER_TABLE);
        this.hideElement(ELEMENT_IDS.SORT_FILTER);
        this.hideElement(ELEMENT_IDS.SORT_FILTER1);
        this.showElement(ELEMENT_IDS.CHART_DIAGRAM);

    }


    setCategoryMode() {
        this.showElement(ELEMENT_IDS.BTN_BACK);
        this.showElement(ELEMENT_IDS.WRAPPER_TABLE);
        this.showElement(ELEMENT_IDS.SORT_FILTER);
        this.hideElement(ELEMENT_IDS.SORT_FILTER1);
        this.showElement(ELEMENT_IDS.CHART_DIAGRAM);

    }

    setDetailMode() {
        this.showElement(ELEMENT_IDS.BTN_BACK);
        this.showElement(ELEMENT_IDS.WRAPPER_TABLE);
        this.hideElement(ELEMENT_IDS.SORT_FILTER);
        this.showElement(ELEMENT_IDS.SORT_FILTER1);
        this.showElement(ELEMENT_IDS.CHART_DIAGRAM);

    }


    showLoading(message = 'Memuat data...') {
        // Implementation depends on your loading component
        if (typeof showProgressBar === 'function') {
            showProgressBar();
        }

    }


    hideLoading() {
        if (typeof completeProgressBar === 'function') {
            completeProgressBar();
        }

    }


    showSuccess(title = 'Berhasil', message = '') {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'success',
                title: title,
                text: message,
                timer: 3000,
                showConfirmButton: false
            });
        } else {
        }
    }


    showError(title = 'Terjadi Kesalahan', message = '') {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'error',
                title: title,
                text: message,
                confirmButtonColor: '#ec4899'
            });
        } else {
            console.error('❌ Error:', title, message);
        }
    }


    showWarning(title = 'Peringatan', message = '') {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'warning',
                title: title,
                text: message,
                confirmButtonColor: '#ec4899'
            });
        } else {
            console.warn('⚠️ Warning:', title, message);
        }
    }


    async showConfirmation(title = 'Konfirmasi', message = 'Apakah Anda yakin?') {
        if (typeof Swal !== 'undefined') {
            const result = await Swal.fire({
                icon: 'question',
                title: title,
                text: message,
                showCancelButton: true,
                confirmButtonColor: '#ec4899',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Ya',
                cancelButtonText: 'Batal'
            });

            return result.isConfirmed;
        } else {
            return confirm(`${title}\n${message}`);
        }
    }

    updateText(elementId, text) {
        const element = document.getElementById(elementId);
        if (element) {
            element.textContent = text;
        }
    }


    updateHTML(elementId, html) {
        const element = document.getElementById(elementId);
        if (element) {
            element.innerHTML = html;
        }
    }


    addClass(elementId, className) {
        const element = document.getElementById(elementId);
        if (element) {
            element.classList.add(className);
        }
    }


    removeClass(elementId, className) {
        const element = document.getElementById(elementId);
        if (element) {
            element.classList.remove(className);
        }
    }


    toggleClass(elementId, className) {
        const element = document.getElementById(elementId);
        if (element) {
            element.classList.toggle(className);
        }
    }


    disableElement(elementId) {
        const element = document.getElementById(elementId);
        if (element) {
            element.disabled = true;
            element.classList.add('opacity-50', 'cursor-not-allowed');
        }
    }


    enableElement(elementId) {
        const element = document.getElementById(elementId);
        if (element) {
            element.disabled = false;
            element.classList.remove('opacity-50', 'cursor-not-allowed');
        }
    }


    getValue(elementId) {
        const element = document.getElementById(elementId);
        return element ? element.value : null;
    }


    setValue(elementId, value) {
        const element = document.getElementById(elementId);
        if (element) {
            element.value = value;
        }
    }


    elementExists(elementId) {
        return !!document.getElementById(elementId);
    }
}
const uiManager = new UIManager();

export default uiManager;
