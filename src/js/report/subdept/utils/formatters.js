/**
 * @fileoverview Utility functions untuk laporan sub departemen
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
 * Format angka dengan separator ribuan
 * @param {number|string} number - Angka yang akan diformat
 * @returns {string} Angka dengan separator ribuan
 * @example
 * formatNumber(1000000) // "1.000.000"
 */
export const formatNumber = (number) => {
    const num = typeof number === 'string' ? parseFloat(number) : number;
    if (isNaN(num)) return '0';
    return num.toLocaleString('id-ID');
};

/**
 * Format persentase dengan desimal
 * @param {number|string} percentage - Persentase yang akan diformat
 * @param {number} decimals - Jumlah desimal (default: 2)
 * @returns {string} Persentase terformat
 * @example
 * formatPercentage(25.567) // "25.57%"
 */
export const formatPercentage = (percentage, decimals = 2) => {
    const num = typeof percentage === 'string' ? parseFloat(percentage) : percentage;
    if (isNaN(num)) return '0.00%';
    return `${num.toFixed(decimals)}%`;
};

/**
 * Parse string currency ke angka
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
 * Parse date string dalam format dd-mm-yyyy ke Date object
 * @param {string} dateString - String tanggal dalam format dd-mm-yyyy
 * @returns {Date|null} Date object atau null jika invalid
 */
export const parseDateString = (dateString) => {
    if (!dateString || typeof dateString !== 'string') return null;
    
    const parts = dateString.split('-');
    if (parts.length !== 3) return null;
    
    const day = parseInt(parts[0], 10);
    const month = parseInt(parts[1], 10);
    const year = parseInt(parts[2], 10);
    
    if (isNaN(day) || isNaN(month) || isNaN(year)) return null;
    if (month < 1 || month > 12) return null;
    if (day < 1 || day > 31) return null;
    
    const date = new Date(year, month - 1, day);
    
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
 */
export const isValidDateRange = (startDate, endDate) => {
    if (!startDate || !endDate) return false;
    
    const start = parseDateString(startDate);
    const end = parseDateString(endDate);
    
    if (!start || !end) return false;
    
    return start <= end;
};

/**
 * Truncate text dengan ellipsis
 * @param {string} text - Text yang akan dipotong
 * @param {number} maxLength - Panjang maksimal text
 * @returns {string} Text yang sudah dipotong
 * @example
 * truncateText("Lorem ipsum dolor", 10) // "Lorem ipsu..."
 */
export const truncateText = (text, maxLength) => {
    if (!text || typeof text !== 'string') return '';
    if (text.length <= maxLength) return text;
    return text.substring(0, maxLength) + '...';
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
 * Deep clone object untuk menghindari mutation
 * @param {Object} obj - Object yang akan di-clone
 * @returns {Object} Object hasil clone
 */
export const deepClone = (obj) => {
    return JSON.parse(JSON.stringify(obj));
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
 * Generate unique ID
 * @returns {string} Unique ID
 */
export const generateId = () => {
    return Date.now().toString(36) + Math.random().toString(36).substr(2);
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

/**
 * Sort array of objects by key
 * @param {Array} array - Array yang akan disort
 * @param {string} key - Key untuk sorting
 * @param {string} direction - 'asc' atau 'desc'
 * @returns {Array} Array yang sudah disort
 */
export const sortArrayByKey = (array, key, direction = 'desc') => {
    if (!Array.isArray(array)) return [];
    
    return [...array].sort((a, b) => {
        const aValue = parseFloat(a[key]) || 0;
        const bValue = parseFloat(b[key]) || 0;
        
        if (direction === 'asc') {
            return aValue - bValue;
        } else {
            return bValue - aValue;
        }
    });
};

/**
 * Extract numeric value dari string
 * @param {string} str - String yang akan diekstrak
 * @returns {number} Nilai numerik
 */
export const extractNumber = (str) => {
    if (typeof str !== 'string') return 0;
    const match = str.match(/-?\d+(\.\d+)?/);
    return match ? parseFloat(match[0]) : 0;
};
