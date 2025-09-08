/**
 * @fileoverview Formatters utility untuk Sales Ratio Report
 * @description Utilities untuk formatting data, numbers, dates, dan currency
 */

/**
 * Format number dengan thousands separator
 * @param {number|string} value - Value to format
 * @param {string} locale - Locale (default: 'id-ID')
 * @returns {string} Formatted number
 */
export function formatNumber(value, locale = 'id-ID') {
    const num = Number(value);
    if (isNaN(num)) return '0';
    
    return num.toLocaleString(locale);
}

/**
 * Format currency (Rupiah)
 * @param {number|string} value - Value to format
 * @param {boolean} includeCurrency - Include currency symbol
 * @returns {string} Formatted currency
 */
export function formatCurrency(value, includeCurrency = true) {
    const num = Number(value);
    if (isNaN(num)) return includeCurrency ? 'Rp 0' : '0';
    
    const formatted = num.toLocaleString('id-ID');
    return includeCurrency ? `Rp ${formatted}` : formatted;
}

/**
 * Format percentage
 * @param {number|string} value - Value to format (as decimal or percentage)
 * @param {number} decimals - Number of decimal places
 * @param {boolean} isDecimal - Value is already in decimal format
 * @returns {string} Formatted percentage
 */
export function formatPercentage(value, decimals = 2, isDecimal = false) {
    let num = Number(value);
    if (isNaN(num)) return '0.00%';
    
    // Convert to percentage if input is decimal
    if (isDecimal) {
        num = num * 100;
    }
    
    return `${num.toFixed(decimals)}%`;
}

/**
 * Parse percentage string to number
 * @param {string} percentageStr - Percentage string (e.g., "15.50%")
 * @returns {number} Number value
 */
export function parsePercentage(percentageStr) {
    if (!percentageStr || typeof percentageStr !== 'string') return 0;
    
    const cleaned = percentageStr.replace('%', '').replace(',', '.');
    const num = parseFloat(cleaned);
    
    return isNaN(num) ? 0 : num;
}

/**
 * Format date untuk display
 * @param {Date|string} date - Date to format
 * @param {string} format - Format type ('short', 'medium', 'long', 'iso')
 * @param {string} locale - Locale
 * @returns {string} Formatted date
 */
export function formatDate(date, format = 'medium', locale = 'id-ID') {
    let dateObj;
    
    if (typeof date === 'string') {
        dateObj = new Date(date);
    } else if (date instanceof Date) {
        dateObj = date;
    } else {
        return '';
    }
    
    if (isNaN(dateObj.getTime())) {
        return '';
    }
    
    switch (format) {
        case 'short':
            return dateObj.toLocaleDateString(locale);
        case 'medium':
            return dateObj.toLocaleDateString(locale, {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric'
            });
        case 'long':
            return dateObj.toLocaleDateString(locale, {
                weekday: 'long',
                day: 'numeric',
                month: 'long',
                year: 'numeric'
            });
        case 'iso':
            return dateObj.toISOString().split('T')[0];
        default:
            return dateObj.toLocaleDateString(locale);
    }
}

/**
 * Format date range untuk display
 * @param {Date|string} startDate - Start date
 * @param {Date|string} endDate - End date
 * @param {string} format - Format type
 * @param {string} separator - Separator between dates
 * @returns {string} Formatted date range
 */
export function formatDateRange(startDate, endDate, format = 'medium', separator = ' - ') {
    const start = formatDate(startDate, format);
    const end = formatDate(endDate, format);
    
    if (!start && !end) return '';
    if (!start) return end;
    if (!end) return start;
    
    return `${start}${separator}${end}`;
}

/**
 * Convert date string untuk API (YYYY-MM-DD)
 * @param {string} dateStr - Date string in various formats
 * @returns {string} API date format
 */
export function toApiDateFormat(dateStr) {
    if (!dateStr) return '';
    
    const date = new Date(dateStr);
    if (isNaN(date.getTime())) {
        return '';
    }
    
    return date.toISOString().split('T')[0];
}

/**
 * Format supplier code
 * @param {string} suppCode - Supplier code
 * @returns {string} Formatted supplier code
 */
export function formatSupplierCode(suppCode) {
    if (!suppCode) return '';
    
    return suppCode.toString().toUpperCase().trim();
}

/**
 * Format store code untuk display
 * @param {string} storeCode - Store code
 * @returns {string} Formatted store code
 */
export function formatStoreCode(storeCode) {
    if (!storeCode) return '';
    
    return storeCode.toString().toUpperCase().trim();
}

/**
 * Truncate text dengan ellipsis
 * @param {string} text - Text to truncate
 * @param {number} maxLength - Maximum length
 * @param {string} suffix - Suffix (default: '...')
 * @returns {string} Truncated text
 */
export function truncateText(text, maxLength, suffix = '...') {
    if (!text || typeof text !== 'string') return '';
    if (text.length <= maxLength) return text;
    
    return text.substring(0, maxLength - suffix.length) + suffix;
}

/**
 * Capitalize first letter of each word
 * @param {string} text - Text to capitalize
 * @returns {string} Capitalized text
 */
export function capitalizeWords(text) {
    if (!text || typeof text !== 'string') return '';
    
    return text.toLowerCase().replace(/\b\w/g, l => l.toUpperCase());
}

/**
 * Clean and normalize text
 * @param {string} text - Text to clean
 * @returns {string} Cleaned text
 */
export function cleanText(text) {
    if (!text || typeof text !== 'string') return '';
    
    return text.trim().replace(/\s+/g, ' ');
}

/**
 * Format file size
 * @param {number} bytes - File size in bytes
 * @param {number} decimals - Number of decimal places
 * @returns {string} Formatted file size
 */
export function formatFileSize(bytes, decimals = 2) {
    if (bytes === 0) return '0 Bytes';
    
    const k = 1024;
    const dm = decimals < 0 ? 0 : decimals;
    const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
    
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    
    return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
}

/**
 * Format time duration
 * @param {number} milliseconds - Duration in milliseconds
 * @returns {string} Formatted duration
 */
export function formatDuration(milliseconds) {
    if (!milliseconds || isNaN(milliseconds)) return '0ms';
    
    const units = [
        { name: 'd', value: 86400000 },
        { name: 'h', value: 3600000 },
        { name: 'm', value: 60000 },
        { name: 's', value: 1000 },
        { name: 'ms', value: 1 }
    ];
    
    for (const unit of units) {
        if (milliseconds >= unit.value) {
            const value = Math.floor(milliseconds / unit.value);
            const remainder = milliseconds % unit.value;
            
            if (remainder === 0 || unit.name === 'ms') {
                return `${value}${unit.name}`;
            } else {
                const nextUnit = units[units.indexOf(unit) + 1];
                if (nextUnit) {
                    const nextValue = Math.floor(remainder / nextUnit.value);
                    return `${value}${unit.name} ${nextValue}${nextUnit.name}`;
                }
            }
        }
    }
    
    return `${milliseconds}ms`;
}

/**
 * Escape HTML characters
 * @param {string} text - Text to escape
 * @returns {string} Escaped text
 */
export function escapeHtml(text) {
    if (!text || typeof text !== 'string') return '';
    
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

/**
 * Generate CSS class untuk percentage values
 * @param {number|string} percentage - Percentage value
 * @param {Object} thresholds - Threshold values
 * @returns {string} CSS class
 */
export function getPercentageClass(percentage, thresholds = { high: 20, medium: 10, low: 5 }) {
    const value = typeof percentage === 'string' ? parsePercentage(percentage) : Number(percentage);
    
    if (isNaN(value)) return 'text-muted';
    if (value >= thresholds.high) return 'text-success fw-bold';
    if (value >= thresholds.medium) return 'text-warning fw-bold';
    if (value >= thresholds.low) return 'text-info';
    
    return 'text-muted';
}

/**
 * Generate sort icon class
 * @param {string} currentColumn - Current sort column
 * @param {string} currentDirection - Current sort direction
 * @param {string} columnName - Column name to check
 * @returns {string} Icon class
 */
export function getSortIconClass(currentColumn, currentDirection, columnName) {
    if (currentColumn !== columnName) {
        return 'fas fa-sort sort-icon';
    }
    
    return currentDirection === 'asc' 
        ? 'fas fa-sort-up sort-icon text-primary'
        : 'fas fa-sort-down sort-icon text-primary';
}

/**
 * Generate unique ID
 * @param {string} prefix - Prefix for ID
 * @returns {string} Unique ID
 */
export function generateId(prefix = 'id') {
    return `${prefix}_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`;
}

/**
 * Debounce function
 * @param {Function} func - Function to debounce
 * @param {number} wait - Wait time in ms
 * @param {boolean} immediate - Execute immediately
 * @returns {Function} Debounced function
 */
export function debounce(func, wait, immediate = false) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            timeout = null;
            if (!immediate) func.apply(this, args);
        };
        const callNow = immediate && !timeout;
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
        if (callNow) func.apply(this, args);
    };
}

/**
 * Throttle function
 * @param {Function} func - Function to throttle
 * @param {number} limit - Time limit in ms
 * @returns {Function} Throttled function
 */
export function throttle(func, limit) {
    let inThrottle;
    return function(...args) {
        if (!inThrottle) {
            func.apply(this, args);
            inThrottle = true;
            setTimeout(() => inThrottle = false, limit);
        }
    };
}

/**
 * Deep clone object
 * @param {any} obj - Object to clone
 * @returns {any} Cloned object
 */
export function deepClone(obj) {
    if (obj === null || typeof obj !== 'object') return obj;
    if (obj instanceof Date) return new Date(obj.getTime());
    if (obj instanceof Array) return obj.map(item => deepClone(item));
    if (typeof obj === 'object') {
        const clonedObj = {};
        for (const key in obj) {
            if (obj.hasOwnProperty(key)) {
                clonedObj[key] = deepClone(obj[key]);
            }
        }
        return clonedObj;
    }
    return obj;
}

// Default export with all formatters
export default {
    formatNumber,
    formatCurrency,
    formatPercentage,
    parsePercentage,
    formatDate,
    formatDateRange,
    toApiDateFormat,
    formatSupplierCode,
    formatStoreCode,
    truncateText,
    capitalizeWords,
    cleanText,
    formatFileSize,
    formatDuration,
    escapeHtml,
    getPercentageClass,
    getSortIconClass,
    generateId,
    debounce,
    throttle,
    deepClone
};
