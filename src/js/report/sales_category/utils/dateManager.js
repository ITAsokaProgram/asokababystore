import { getDefaultDateRange, formatDate, isValidDateRange } from '../utils/formatters.js';
import { ELEMENT_IDS } from '../config/constants.js';
class DateManager {
    constructor() {
        this.flatpickrInstances = {};
        this.isInitialized = false;
    }
    initialize() {
        try {
            this._initializeDateInputs();
            this._initializeFlatpickr();
            this._setDefaultDates();
            this.isInitialized = true;
            return true;
        } catch (error) {
            console.error('Failed to initialize Date Manager:', error);
            return false;
        }
    }
    _initializeDateInputs() {
        const startDateInput = document.getElementById(ELEMENT_IDS.DATE_START);
        const endDateInput = document.getElementById(ELEMENT_IDS.DATE_END);
        if (!startDateInput || !endDateInput) {
            console.error("Date input elements not found!");
            return;
        }
        startDateInput.addEventListener('change', () => this._validateDateRange());
        endDateInput.addEventListener('change', () => this._validateDateRange());
    }

    _initializeFlatpickr() {
        if (typeof flatpickr === 'undefined') {
            console.warn('Flatpickr library not found, using basic date inputs');
            return;
        }
        const config = {
            dateFormat: "d-m-Y",
            allowInput: true,
            locale: {
                weekdays: {
                    shorthand: ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'],
                    longhand: ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu']
                },
                months: {
                    shorthand: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
                    longhand: ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember']
                }
            },
            onClose: () => this._validateDateRange(),
            maxDate: new Date()
        };
        const startDateElement = document.getElementById(ELEMENT_IDS.DATE_START);
        if (startDateElement) {
            this.flatpickrInstances.startDate = flatpickr(startDateElement, {
                ...config,
                onChange: (selectedDates) => {
                    if (selectedDates.length > 0 && this.flatpickrInstances.endDate) {
                        this.flatpickrInstances.endDate.set('minDate', selectedDates[0]);
                    }
                }
            });
        }
        const endDateElement = document.getElementById(ELEMENT_IDS.DATE_END);
        if (endDateElement) {
            this.flatpickrInstances.endDate = flatpickr(endDateElement, {
                ...config,
                onChange: (selectedDates) => {
                    if (selectedDates.length > 0 && this.flatpickrInstances.startDate) {
                        this.flatpickrInstances.startDate.set('maxDate', selectedDates[0]);
                    }
                }
            });
        }
    }

    _setDefaultDates() {
        const { startDate, endDate } = getDefaultDateRange();
        const startDateElement = document.getElementById(ELEMENT_IDS.DATE_START);
        const endDateElement = document.getElementById(ELEMENT_IDS.DATE_END);
        if (startDateElement && endDateElement) {
            startDateElement.value = startDate;
            endDateElement.value = endDate;
            if (this.flatpickrInstances.startDate) {
                this.flatpickrInstances.startDate.setDate(startDate);
            }
            if (this.flatpickrInstances.endDate) {
                this.flatpickrInstances.endDate.setDate(endDate);
            }
        }
    }

    _validateDateRange() {
        const startDate = this.getStartDate();
        const endDate = this.getEndDate();
        if (!startDate || !endDate) {
            this._showDateError('Silakan isi tanggal mulai dan tanggal akhir');
            return false;
        }
        if (!isValidDateRange(startDate, endDate)) {
            this._showDateError('Tanggal mulai harus lebih kecil atau sama dengan tanggal akhir');
            return false;
        }
        this._clearDateError();
        return true;
    }

    _showDateError(message) {
        this._clearDateError();
        const startDateElement = document.getElementById(ELEMENT_IDS.DATE_START);
        const endDateElement = document.getElementById(ELEMENT_IDS.DATE_END);
        if (startDateElement) startDateElement.classList.add('border-red-500', 'ring-red-500');
        if (endDateElement) endDateElement.classList.add('border-red-500', 'ring-red-500');
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'warning',
                title: 'Tanggal Tidak Valid',
                text: message,
                confirmButtonColor: '#ec4899',
                timer: 4000
            });
        } else {
            console.warn('Date validation error:', message);
        }
    }

    _clearDateError() {
        const startDateElement = document.getElementById(ELEMENT_IDS.DATE_START);
        const endDateElement = document.getElementById(ELEMENT_IDS.DATE_END);
        if (startDateElement) {
            startDateElement.classList.remove('border-red-500', 'ring-red-500');
        }
        if (endDateElement) {
            endDateElement.classList.remove('border-red-500', 'ring-red-500');
        }
    }

    getStartDate() {
        const element = document.getElementById(ELEMENT_IDS.DATE_START);
        return element ? element.value : null;
    }

    getEndDate() {
        const element = document.getElementById(ELEMENT_IDS.DATE_END);
        return element ? element.value : null;
    }

    setStartDate(date) {
        const element = document.getElementById(ELEMENT_IDS.DATE_START);
        if (element) {
            element.value = date;
            if (this.flatpickrInstances.startDate) {
                this.flatpickrInstances.startDate.setDate(date);
            }
        }
    }

    setEndDate(date) {
        const element = document.getElementById(ELEMENT_IDS.DATE_END);
        if (element) {
            element.value = date;
            if (this.flatpickrInstances.endDate) {
                this.flatpickrInstances.endDate.setDate(date);
            }
        }
    }

    getDateRange() {
        const startDate = this.getStartDate();
        const endDate = this.getEndDate();
        if (this._validateDateRange()) {
            return { startDate, endDate };
        }
        return null;
    }

    setDateRange({ startDate, endDate }) {
        this.setStartDate(startDate);
        this.setEndDate(endDate);
    }

    resetToDefault() {
        this._setDefaultDates();
    }

    getFormattedDateRange() {
        const startDate = this.getStartDate();
        const endDate = this.getEndDate();
        if (startDate && endDate) {
            return `${startDate} s/d ${endDate}`;
        }
        return '';
    }

    isValidRange() {
        return this._validateDateRange();
    }

    dispose() {
        Object.values(this.flatpickrInstances).forEach(instance => {
            if (instance && typeof instance.destroy === 'function') {
                instance.destroy();
            }
        });
        this.flatpickrInstances = {};
        this.isInitialized = false;
    }
}
const dateManager = new DateManager();
export default dateManager;
