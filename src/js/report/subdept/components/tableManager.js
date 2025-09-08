/**
 * @fileoverview Table Manager untuk laporan sub departemen
 * @description Mengelola semua operasi table termasuk rendering, sorting, dan pagination
 */

import { TABLE_HEADERS, ELEMENT_IDS } from '../config/constants.js';
import { formatCurrency, formatNumber, formatPercentage, truncateText } from '../utils/formatters.js';

/**
 * Class untuk mengelola table operations
 * @class TableManager
 */
class TableManager {
    constructor() {
        this.isInitialized = false;
    }

    /**
     * Inisialisasi table manager
     * @returns {boolean} Status inisialisasi
     */
    initialize() {
        try {
            console.log('📋 Initializing Table Manager...');
            this.isInitialized = true;
            return true;
        } catch (error) {
            console.error('❌ Error initializing Table Manager:', error);
            return false;
        }
    }

    /**
     * Update table dengan data baru
     * @param {Array} data - Data yang akan ditampilkan
     * @param {string} tableId - ID dari table element
     * @param {string} tableType - Jenis table (subdept, supplier, promo)
     */
    updateTable(data, tableId, tableType = 'subdept') {
        try {
            console.log(`📊 Updating table ${tableId} with`, data.length, 'items');

            const tableElement = document.getElementById(tableId);
            if (!tableElement) {
                console.warn(`⚠️ Table element dengan ID '${tableId}' tidak ditemukan`);
                return;
            }

            // Clear existing content
            tableElement.innerHTML = '';

            // Create table structure
            const table = document.createElement('table');
            table.className = 'table table-striped table-hover';
            
            // Create header
            const thead = this._createTableHeader(tableType);
            table.appendChild(thead);

            // Create body
            const tbody = this._createTableBody(data, tableType);
            table.appendChild(tbody);

            // Append to container
            tableElement.appendChild(table);

            console.log(`✅ Table ${tableId} updated successfully`);

        } catch (error) {
            console.error(`❌ Error updating table ${tableId}:`, error);
        }
    }

    /**
     * Create table header berdasarkan type
     * @private
     * @param {string} tableType - Jenis table
     * @returns {HTMLElement} Table header element
     */
    _createTableHeader(tableType) {
        const thead = document.createElement('thead');
        thead.className = 'thead-dark';
        
        const tr = document.createElement('tr');
        
        const headers = TABLE_HEADERS[tableType.toUpperCase()] || TABLE_HEADERS.SUBDEPT;
        
        headers.forEach(header => {
            const th = document.createElement('th');
            th.textContent = header.label;
            th.style.width = header.width || 'auto';
            th.style.textAlign = header.align || 'left';
            th.className = 'text-center';
            tr.appendChild(th);
        });

        thead.appendChild(tr);
        return thead;
    }

    /**
     * Create table body dengan data
     * @private
     * @param {Array} data - Data untuk table body
     * @param {string} tableType - Jenis table
     * @returns {HTMLElement} Table body element
     */
    _createTableBody(data, tableType) {
        const tbody = document.createElement('tbody');
        
        if (!Array.isArray(data) || data.length === 0) {
            const tr = document.createElement('tr');
            const td = document.createElement('td');
            td.colSpan = TABLE_HEADERS[tableType.toUpperCase()]?.length || 5;
            td.textContent = 'Tidak ada data untuk ditampilkan';
            td.className = 'text-center text-muted';
            tr.appendChild(td);
            tbody.appendChild(tr);
            return tbody;
        }

        data.forEach((item, index) => {
            const tr = this._createTableRow(item, tableType, index);
            tbody.appendChild(tr);
        });

        return tbody;
    }

    /**
     * Create table row untuk single data item
     * @private
     * @param {Object} item - Data item
     * @param {string} tableType - Jenis table
     * @param {number} index - Index untuk styling
     * @returns {HTMLElement} Table row element
     */
    _createTableRow(item, tableType, index) {
        const tr = document.createElement('tr');
        
        // Add zebra striping
        if (index % 2 === 0) {
            tr.className = 'table-row-even';
        }

        const headers = TABLE_HEADERS[tableType.toUpperCase()] || TABLE_HEADERS.SUBDEPT;
        
        headers.forEach(header => {
            const td = document.createElement('td');
            const value = item[header.key];
            
            // Format berdasarkan jenis data
            td.innerHTML = this._formatCellValue(value, header.key, header.align);
            
            if (header.align) {
                td.style.textAlign = header.align;
            }

            tr.appendChild(td);
        });

        return tr;
    }

    /**
     * Format cell value berdasarkan jenis data
     * @private
     * @param {*} value - Value yang akan diformat
     * @param {string} key - Key dari data
     * @param {string} align - Text alignment
     * @returns {string} Formatted value
     */
    _formatCellValue(value, key, align) {
        if (value === null || value === undefined) {
            return '<span class="text-muted">-</span>';
        }

        // Handle different data types
        switch (key) {
            case 'Total':
                return `<strong class="text-success">${formatCurrency(parseFloat(value) || 0)}</strong>`;
            
            case 'Qty':
                return `<span class="badge badge-primary">${formatNumber(parseFloat(value) || 0)}</span>`;
            
            case 'persentase':
            case 'Percentage':
                return `<span class="text-info">${formatPercentage(parseFloat(value) || 0)}</span>`;
            
            case 'nama_subdept':
            case 'nama_supp':
            case 'nama_barang':
                return `<span class="font-weight-medium">${truncateText(String(value), 50)}</span>`;
            
            case 'kode_subdept':
            case 'kode_supp':
            case 'kode':
                return `<code class="text-dark">${String(value)}</code>`;
            
            case 'promo':
                return `<span class="text-primary">${truncateText(String(value), 30)}</span>`;
            
            default:
                return `<span>${String(value)}</span>`;
        }
    }

    /**
     * Update table header khusus untuk promo
     * @param {string} headerId - ID dari header element
     */
    updatePromoTableHeader(headerId) {
        try {
            const headerElement = document.getElementById(headerId);
            if (!headerElement) {
                console.warn(`⚠️ Header element dengan ID '${headerId}' tidak ditemukan`);
                return;
            }

            // Update header untuk promo table
            const headers = TABLE_HEADERS.PROMO;
            headerElement.innerHTML = '';

            const tr = document.createElement('tr');
            tr.className = 'bg-primary text-white';

            headers.forEach(header => {
                const th = document.createElement('th');
                th.textContent = header.label;
                th.style.width = header.width || 'auto';
                th.style.textAlign = header.align || 'left';
                th.className = 'text-center';
                tr.appendChild(th);
            });

            headerElement.appendChild(tr);
            console.log(`✅ Promo table header updated: ${headerId}`);

        } catch (error) {
            console.error(`❌ Error updating promo table header:`, error);
        }
    }

    /**
     * Sort table data berdasarkan column
     * @param {Array} data - Data yang akan disort
     * @param {string} sortBy - Column untuk sorting
     * @param {string} direction - Direction (asc/desc)
     * @returns {Array} Sorted data
     */
    sortTableData(data, sortBy, direction = 'desc') {
        if (!Array.isArray(data)) return [];

        return [...data].sort((a, b) => {
            let valueA, valueB;

            if (sortBy === 'Qty' || sortBy === 'Total') {
                valueA = parseFloat(a[sortBy]) || 0;
                valueB = parseFloat(b[sortBy]) || 0;
            } else {
                valueA = String(a[sortBy] || '').toLowerCase();
                valueB = String(b[sortBy] || '').toLowerCase();
            }

            if (direction === 'asc') {
                return valueA > valueB ? 1 : valueA < valueB ? -1 : 0;
            } else {
                return valueA < valueB ? 1 : valueA > valueB ? -1 : 0;
            }
        });
    }

    /**
     * Filter table data berdasarkan search term
     * @param {Array} data - Data yang akan difilter
     * @param {string} searchTerm - Term untuk search
     * @param {Array} searchFields - Fields yang akan disearch
     * @returns {Array} Filtered data
     */
    filterTableData(data, searchTerm, searchFields = []) {
        if (!searchTerm || !Array.isArray(data)) return data;

        const term = searchTerm.toLowerCase();
        
        return data.filter(item => {
            // If no specific fields, search all string fields
            if (searchFields.length === 0) {
                return Object.values(item).some(value => 
                    String(value).toLowerCase().includes(term)
                );
            }

            // Search specific fields
            return searchFields.some(field => {
                const value = item[field];
                return value && String(value).toLowerCase().includes(term);
            });
        });
    }

    /**
     * Get table data summary
     * @param {Array} data - Data untuk summary
     * @returns {Object} Summary object
     */
    getTableSummary(data) {
        if (!Array.isArray(data) || data.length === 0) {
            return {
                totalRows: 0,
                totalQty: 0,
                totalAmount: 0
            };
        }

        const totalQty = data.reduce((sum, item) => sum + (parseFloat(item.Qty) || 0), 0);
        const totalAmount = data.reduce((sum, item) => sum + (parseFloat(item.Total) || 0), 0);

        return {
            totalRows: data.length,
            totalQty: totalQty,
            totalAmount: totalAmount,
            formattedTotalQty: formatNumber(totalQty),
            formattedTotalAmount: formatCurrency(totalAmount)
        };
    }

    /**
     * Clear table content
     * @param {string} tableId - ID dari table element
     */
    clearTable(tableId) {
        try {
            const tableElement = document.getElementById(tableId);
            if (tableElement) {
                tableElement.innerHTML = '';
                console.log(`🗑️ Table ${tableId} cleared`);
            }
        } catch (error) {
            console.error(`❌ Error clearing table ${tableId}:`, error);
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
const tableManager = new TableManager();
export default tableManager;
