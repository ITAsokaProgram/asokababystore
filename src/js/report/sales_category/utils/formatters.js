/**
 * @fileoverview Utility functions untuk laporan penjualan kategori
 * @description Berisi fungsi-fungsi helper untuk formatting, validasi, dan operasi umum
 */

/**
 * Format angka menjadi format currency Rupiah
 * @param {number} amount - Jumlah yang akan diformat
 * @returns {string} Format currency Rupiah
 * @example
 * formatCurrency(1000000) // "Rp 1.000.000"
 */
export const formatCurrency = (amount) => {
    if (typeof amount !== 'number') return 'Rp 0';
    return `Rp ${amount.toLocaleString('id-ID')}`;
};

/**
 * Konversi string currency ke angka
 * @param {string} currencyString - String currency yang akan dikonversi
 * @returns {number} Angka hasil konversi
 * @example
 * parseCurrencyToNumber("Rp 1.000.000") // 1000000
 */
export const parseCurrencyToNumber = (currencyString) => {
    if (typeof currencyString !== 'string') return 0;
    return parseFloat(currencyString.replace(/[^0-9,-]+/g, '').replace(',', '.')) || 0;
};

/**
 * Format tanggal ke format Indonesia (dd-mm-yyyy)
 * @param {Date|string} date - Tanggal yang akan diformat
 * @returns {string} Tanggal dalam format dd-mm-yyyy
 * @example
 * formatDate(new Date()) // "08-08-2025"
 */
export const formatDate = (date) => {
    if (!date) return "";
    
    const d = new Date(date);
    if (isNaN(d.getTime())) {
        console.error("❌ Format tanggal tidak valid:", date);
        return date.toString();
    }

    const day = d.getDate().toString().padStart(2, "0");
    const month = (d.getMonth() + 1).toString().padStart(2, "0");
    const year = d.getFullYear();

    return `${day}-${month}-${year}`;
};

/**
 * Generate default date range (30 hari sebelumnya sampai kemarin)
 * @returns {Object} Object dengan startDate dan endDate
 */
export const getDefaultDateRange = () => {
    const today = new Date();
    
    const startDate = new Date();
    startDate.setDate(today.getDate() - 30);
    
    const endDate = new Date();
    endDate.setDate(today.getDate() - 1);
    
    return {
        startDate: formatDate(startDate),
        endDate: formatDate(endDate)
    };
};

/**
 * Parse date string dalam format dd-mm-yyyy ke Date object
 * @param {string} dateString - String tanggal dalam format dd-mm-yyyy
 * @returns {Date|null} Date object atau null jika invalid
 * @example
 * parseDateString("01-07-2025") // Date object untuk 1 Juli 2025
 */
export const parseDateString = (dateString) => {
    if (!dateString || typeof dateString !== 'string') return null;
    
    // Split format dd-mm-yyyy
    const parts = dateString.split('-');
    if (parts.length !== 3) return null;
    
    const day = parseInt(parts[0], 10);
    const month = parseInt(parts[1], 10);
    const year = parseInt(parts[2], 10);
    
    // Validasi basic
    if (isNaN(day) || isNaN(month) || isNaN(year)) return null;
    if (month < 1 || month > 12) return null;
    if (day < 1 || day > 31) return null;
    
    // Create date object (month di JS dimulai dari 0)
    const date = new Date(year, month - 1, day);
    
    // Validasi apakah date benar-benar valid (misal 31 Februari akan invalid)
    if (date.getFullYear() !== year || 
        date.getMonth() !== (month - 1) || 
        date.getDate() !== day) {
        return null;
    }
    
    return date;
};

/**
 * Validate apakah date range valid (start date <= end date)
 * @param {string} startDate - Tanggal mulai dalam format dd-mm-yyyy
 * @param {string} endDate - Tanggal akhir dalam format dd-mm-yyyy
 * @returns {boolean} true jika valid
 * @example
 * isValidDateRange("01-07-2025", "31-07-2025") // true
 */
export const isValidDateRange = (startDate, endDate) => {
    
    if (!startDate || !endDate) {
        console.warn('❌ Empty date provided');
        return false;
    }
    
    const start = parseDateString(startDate);
    const end = parseDateString(endDate);
    
    if (!start || !end) {
        console.warn('❌ Invalid date format. Expected: dd-mm-yyyy');
        return false;
    }
    
    const isValid = start <= end;

    
    return isValid;
};

/**
 * Konversi tanggal dari format dd-mm-yyyy ke yyyy-mm-dd (untuk API)
 * @param {string} dateString - Tanggal dalam format dd-mm-yyyy
 * @returns {string} Tanggal dalam format yyyy-mm-dd
 * @example
 * convertDateForAPI("01-07-2025") // "2025-07-01"
 */
export const convertDateForAPI = (dateString) => {
    const date = parseDateString(dateString);
    if (!date) return dateString; // Return original jika parsing gagal
    
    const year = date.getFullYear();
    const month = (date.getMonth() + 1).toString().padStart(2, '0');
    const day = date.getDate().toString().padStart(2, '0');
    
    return `${year}-${month}-${day}`;
};

/**
 * Debounce function untuk optimasi performance
 * @param {Function} func - Fungsi yang akan didebounce
 * @param {number} delay - Delay dalam milliseconds
 * @returns {Function} Fungsi yang sudah didebounce
 */
export const debounce = (func, delay) => {
    let timeoutId;
    return function (...args) {
        clearTimeout(timeoutId);
        timeoutId = setTimeout(() => func.apply(this, args), delay);
    };
};

/**
 * Deep clone object untuk menghindari mutation
 * @param {Object} obj - Object yang akan di-clone
 * @returns {Object} Object hasil clone
 */
export const deepClone = (obj) => {
    return JSON.parse(JSON.stringify(obj));
};

/**
 * Generate unique ID
 * @returns {string} Unique ID
 */
export const generateId = () => {
    return Date.now().toString(36) + Math.random().toString(36).substr(2);
};

/**
 * Safe array access dengan default value
 * @param {Array} arr - Array yang akan diakses
 * @param {number} index - Index yang akan diakses
 * @param {*} defaultValue - Default value jika index tidak ada
 * @returns {*} Value dari array atau default value
 */
export const safeArrayAccess = (arr, index, defaultValue = null) => {
    return Array.isArray(arr) && arr[index] !== undefined ? arr[index] : defaultValue;
};

/**
 * Check apakah object kosong
 * @param {Object} obj - Object yang akan dicek
 * @returns {boolean} true jika kosong
 */
export const isEmpty = (obj) => {
    return obj == null || Object.keys(obj).length === 0;
};

/**
 * Capitalize first letter of string
 * @param {string} str - String yang akan di-capitalize
 * @returns {string} String dengan huruf pertama kapital
 */
export const capitalize = (str) => {
    if (typeof str !== 'string' || str.length === 0) return '';
    return str.charAt(0).toUpperCase() + str.slice(1);
};
