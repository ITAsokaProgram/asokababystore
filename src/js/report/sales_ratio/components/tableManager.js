/**
 * @fileoverview Table Manager untuk Sales Ratio Report
 * @description Mengelola operasi tabel data dan export functionality
 */

import { ELEMENT_IDS, SORT_OPTIONS, EXPORT_CONFIG, STORAGE_KEYS } from '../config/constants.js';

/**
 * Class untuk mengelola table operations
 * @class TableManager
 */
class TableManager {
    constructor() {
        this.currentData = null;
        this.isInitialized = false;
        this.sortState = {
            column: null,
            direction: 'asc' // asc, desc
        };
    }

    /**
     * Initialize table manager
     * @returns {boolean} Success status
     */
    initialize() {
        try {
            
            
            // Setup table event listeners
            this._setupTableEvents();
            
            this.isInitialized = true;
            
            return true;
            
        } catch (error) {
            console.error('❌ Error initializing Table Manager:', error);
            return false;
        }
    }

    /**
     * Setup table event listeners
     * @private
     */
    _setupTableEvents() {
        // Table sorting listeners will be added when table is created
        // Export button listeners
        const exportExcelBtn = document.getElementById(ELEMENT_IDS.EXPORT_EXCEL);
        const exportPdfBtn = document.getElementById(ELEMENT_IDS.EXPORT_PDF);
        
        if (exportExcelBtn) {
            exportExcelBtn.addEventListener('click', () => this.exportToExcel());
        }
        
        if (exportPdfBtn) {
            exportPdfBtn.addEventListener('click', () => this.exportToPDF());
        }
    }

    /**
     * Update table dengan data baru
     * @param {Array} tableData - Data untuk table
     * @param {string} sortBy - Sort option ('Qty' atau 'Total')
     */
    updateTable(tableData, sortBy = SORT_OPTIONS.TOTAL) {
        try {
            if (!tableData || !Array.isArray(tableData)) {
                console.warn('⚠️ Invalid table data provided');
                return;
            }

            
            
            // Process and sort data
            const processedData = this._processTableData(tableData, sortBy);
            
            // Create table HTML
            this._createTableHTML(processedData, sortBy);
            
            // Store current data
            this.currentData = { tableData, sortBy };
            
            // Show table container
            this.show();
            
            
            
        } catch (error) {
            console.error('❌ Error updating table:', error);
        }
    }

    /**
     * Process data untuk table
     * @private
     * @param {Array} tableData - Raw table data
     * @param {string} sortBy - Sort option
     * @returns {Array} Processed data
     */
    _processTableData(tableData, sortBy) {
        // Sort data berdasarkan sortBy
        let sortedTable = [...tableData];
        
        if (sortBy === SORT_OPTIONS.QTY) {
            sortedTable.sort((a, b) => b.Qty - a.Qty);
        } else {
            sortedTable.sort((a, b) => {
                const numA = Number(a.Total) || 0;
                const numB = Number(b.Total) || 0;
                return numB - numA;
            });
        }

        return sortedTable.map((item, index) => ({
            no: index + 1,
            periode: item.periode,
            kode_supp: item.kode_supp,
            qty: Number(item.Qty) || 0,
            total: Number(item.Total) || 0,
            persentase_rp: item.persentase_rp || '0.00%',
            percentage: item.Percentage || '0.00%'
        }));
    }

    /**
     * Create table HTML
     * @private
     * @param {Array} processedData - Processed data
     * @param {string} sortBy - Sort option
     */
    _createTableHTML(processedData, sortBy) {
        const tableContainer = document.getElementById(ELEMENT_IDS.TABLE_RESULT);
        if (!tableContainer) {
            console.error('❌ Table container not found');
            return;
        }

        // Calculate totals
        const totals = this._calculateTotals(processedData);
        
        // Create table HTML
        const tableHTML = `
            <div class="table-responsive">
                <table class="table table-striped table-bordered" id="ratioTable">
                    <thead class="table-dark">
                        <tr>
                            <th class="sortable text-center" data-column="no">
                                No
                                <i class="fas fa-sort sort-icon"></i>
                            </th>
                            <th class="sortable" data-column="periode">
                                Periode
                                <i class="fas fa-sort sort-icon"></i>
                            </th>
                            <th class="sortable" data-column="kode_supp">
                                Supplier
                                <i class="fas fa-sort sort-icon"></i>
                            </th>
                            <th class="sortable text-end" data-column="qty">
                                Qty
                                <i class="fas fa-sort sort-icon"></i>
                            </th>
                            <th class="sortable text-end" data-column="total">
                                Total (Rp)
                                <i class="fas fa-sort sort-icon"></i>
                            </th>
                            <th class="text-end">% Rp</th>
                            <th class="text-end">% Qty</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${processedData.map(row => `
                            <tr>
                                <td class="text-center">${row.no}</td>
                                <td>${row.periode}</td>
                                <td class="fw-bold">${row.kode_supp}</td>
                                <td class="text-end">${row.qty.toLocaleString('id-ID')}</td>
                                <td class="text-end">Rp ${row.total.toLocaleString('id-ID')}</td>
                                <td class="text-end ${this._getPercentageClass(row.persentase_rp)}">${row.persentase_rp}</td>
                                <td class="text-end ${this._getPercentageClass(row.percentage)}">${row.percentage}</td>
                            </tr>
                        `).join('')}
                    </tbody>
                    <tfoot class="table-secondary">
                        <tr class="fw-bold">
                            <td colspan="3" class="text-center">TOTAL</td>
                            <td class="text-end">${totals.qty.toLocaleString('id-ID')}</td>
                            <td class="text-end">Rp ${totals.total.toLocaleString('id-ID')}</td>
                            <td class="text-end">100.00%</td>
                            <td class="text-end">100.00%</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            
            <div class="d-flex justify-content-between mt-3">
                <div class="text-muted">
                    Menampilkan ${processedData.length} data | 
                    Sort by: <span class="fw-bold">${sortBy === SORT_OPTIONS.QTY ? 'Quantity' : 'Total Rupiah'}</span>
                </div>
                <div class="btn-group">
                    <button id="${ELEMENT_IDS.EXPORT_EXCEL}" class="btn btn-success btn-sm">
                        <i class="fas fa-file-excel me-1"></i>Export Excel
                    </button>
                    <button id="${ELEMENT_IDS.EXPORT_PDF}" class="btn btn-danger btn-sm">
                        <i class="fas fa-file-pdf me-1"></i>Export PDF
                    </button>
                </div>
            </div>
        `;

        tableContainer.innerHTML = tableHTML;
        
        // Setup sorting after table is created
        this._setupTableSorting();
        
        // Re-setup export button listeners
        this._setupExportListeners();
    }

    /**
     * Calculate totals for footer
     * @private
     * @param {Array} data - Table data
     * @returns {Object} Totals
     */
    _calculateTotals(data) {
        return data.reduce((acc, row) => {
            acc.qty += row.qty;
            acc.total += row.total;
            return acc;
        }, { qty: 0, total: 0 });
    }

    /**
     * Get CSS class for percentage values
     * @private
     * @param {string} percentage - Percentage string
     * @returns {string} CSS class
     */
    _getPercentageClass(percentage) {
        const value = parseFloat(percentage);
        if (value >= 20) return 'text-success fw-bold';
        if (value >= 10) return 'text-warning fw-bold';
        if (value >= 5) return 'text-info';
        return 'text-muted';
    }

    /**
     * Setup table sorting functionality
     * @private
     */
    _setupTableSorting() {
        const sortableHeaders = document.querySelectorAll('#ratioTable th.sortable');
        
        sortableHeaders.forEach(header => {
            header.addEventListener('click', () => {
                const column = header.dataset.column;
                this._sortTable(column);
            });
        });
    }

    /**
     * Sort table by column
     * @private
     * @param {string} column - Column name
     */
    _sortTable(column) {
        if (!this.currentData?.tableData) return;

        // Toggle sort direction
        if (this.sortState.column === column) {
            this.sortState.direction = this.sortState.direction === 'asc' ? 'desc' : 'asc';
        } else {
            this.sortState.column = column;
            this.sortState.direction = 'asc';
        }

        // Update sort icons
        this._updateSortIcons(column, this.sortState.direction);

        // Re-render table with new sort
        const sortedData = this._sortData(this.currentData.tableData, column, this.sortState.direction);
        const processedData = this._processTableDataForSort(sortedData);
        
        // Update only tbody
        this._updateTableBody(processedData);
    }

    /**
     * Update sort icons
     * @private
     * @param {string} activeColumn - Active column
     * @param {string} direction - Sort direction
     */
    _updateSortIcons(activeColumn, direction) {
        const sortIcons = document.querySelectorAll('#ratioTable .sort-icon');
        
        sortIcons.forEach(icon => {
            const header = icon.closest('th');
            const column = header.dataset.column;
            
            // Reset all icons
            icon.className = 'fas fa-sort sort-icon';
            
            // Set active icon
            if (column === activeColumn) {
                icon.className = direction === 'asc' 
                    ? 'fas fa-sort-up sort-icon text-primary'
                    : 'fas fa-sort-down sort-icon text-primary';
            }
        });
    }

    /**
     * Sort data by column and direction
     * @private
     * @param {Array} data - Data to sort
     * @param {string} column - Column name
     * @param {string} direction - Sort direction
     * @returns {Array} Sorted data
     */
    _sortData(data, column, direction) {
        const sorted = [...data].sort((a, b) => {
            let aVal, bVal;
            
            switch (column) {
                case 'no':
                    return direction === 'asc' ? 0 : 0; // No sorting for numbers
                case 'periode':
                    aVal = a.periode || '';
                    bVal = b.periode || '';
                    break;
                case 'kode_supp':
                    aVal = a.kode_supp || '';
                    bVal = b.kode_supp || '';
                    break;
                case 'qty':
                    aVal = Number(a.Qty) || 0;
                    bVal = Number(b.Qty) || 0;
                    break;
                case 'total':
                    aVal = Number(a.Total) || 0;
                    bVal = Number(b.Total) || 0;
                    break;
                default:
                    return 0;
            }
            
            if (typeof aVal === 'number') {
                return direction === 'asc' ? aVal - bVal : bVal - aVal;
            } else {
                const comparison = aVal.localeCompare(bVal);
                return direction === 'asc' ? comparison : -comparison;
            }
        });

        return sorted;
    }

    /**
     * Process sorted data for table display
     * @private
     * @param {Array} sortedData - Sorted data
     * @returns {Array} Processed data
     */
    _processTableDataForSort(sortedData) {
        return sortedData.map((item, index) => ({
            no: index + 1,
            periode: item.periode,
            kode_supp: item.kode_supp,
            qty: Number(item.Qty) || 0,
            total: Number(item.Total) || 0,
            persentase_rp: item.persentase_rp || '0.00%',
            percentage: item.Percentage || '0.00%'
        }));
    }

    /**
     * Update table body only
     * @private
     * @param {Array} processedData - Processed data
     */
    _updateTableBody(processedData) {
        const tbody = document.querySelector('#ratioTable tbody');
        if (!tbody) return;

        // Calculate new totals
        const totals = this._calculateTotals(processedData);

        tbody.innerHTML = processedData.map(row => `
            <tr>
                <td class="text-center">${row.no}</td>
                <td>${row.periode}</td>
                <td class="fw-bold">${row.kode_supp}</td>
                <td class="text-end">${row.qty.toLocaleString('id-ID')}</td>
                <td class="text-end">Rp ${row.total.toLocaleString('id-ID')}</td>
                <td class="text-end ${this._getPercentageClass(row.persentase_rp)}">${row.persentase_rp}</td>
                <td class="text-end ${this._getPercentageClass(row.percentage)}">${row.percentage}</td>
            </tr>
        `).join('');

        // Update footer
        const tfoot = document.querySelector('#ratioTable tfoot tr');
        if (tfoot) {
            tfoot.innerHTML = `
                <td colspan="3" class="text-center">TOTAL</td>
                <td class="text-end">${totals.qty.toLocaleString('id-ID')}</td>
                <td class="text-end">Rp ${totals.total.toLocaleString('id-ID')}</td>
                <td class="text-end">100.00%</td>
                <td class="text-end">100.00%</td>
            `;
        }
    }

    /**
     * Setup export button listeners
     * @private
     */
    _setupExportListeners() {
        const exportExcelBtn = document.getElementById(ELEMENT_IDS.EXPORT_EXCEL);
        const exportPdfBtn = document.getElementById(ELEMENT_IDS.EXPORT_PDF);
        
        if (exportExcelBtn) {
            exportExcelBtn.replaceWith(exportExcelBtn.cloneNode(true));
            const newExcelBtn = document.getElementById(ELEMENT_IDS.EXPORT_EXCEL);
            newExcelBtn.addEventListener('click', () => this.exportToExcel());
        }
        
        if (exportPdfBtn) {
            exportPdfBtn.replaceWith(exportPdfBtn.cloneNode(true));
            const newPdfBtn = document.getElementById(ELEMENT_IDS.EXPORT_PDF);
            newPdfBtn.addEventListener('click', () => this.exportToPDF());
        }
    }

    /**
     * Export table to Excel
     */
    async exportToExcel() {
        try {
            if (!this.currentData?.tableData) {
                console.warn('⚠️ No data to export');
                return;
            }

            // Check if ExcelJS is available
            if (typeof ExcelJS === 'undefined') {
                console.error('❌ ExcelJS library not found');
                alert('ExcelJS library is required for Excel export');
                return;
            }

            

            // Show loading state
            const exportBtn = document.getElementById(ELEMENT_IDS.EXPORT_EXCEL);
            const originalText = exportBtn.innerHTML;
            exportBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Exporting...';
            exportBtn.disabled = true;

            // Create workbook
            const workbook = new ExcelJS.Workbook();
            const worksheet = workbook.addWorksheet('Sales Ratio Report');

            // Add headers
            worksheet.addRow(['No', 'Periode', 'Supplier', 'Qty', 'Total (Rp)', '% Rp', '% Qty']);
            
            // Style header row
            const headerRow = worksheet.getRow(1);
            headerRow.font = { bold: true };
            headerRow.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: '4472C4' } };
            headerRow.fontColor = { argb: 'FFFFFF' };

            // Add data rows
            const processedData = this._processTableData(this.currentData.tableData, this.currentData.sortBy);
            processedData.forEach(row => {
                worksheet.addRow([
                    row.no,
                    row.periode,
                    row.kode_supp,
                    row.qty,
                    row.total,
                    row.persentase_rp,
                    row.percentage
                ]);
            });

            // Auto-fit columns
            worksheet.columns.forEach(column => {
                column.width = Math.max(column.width || 0, 15);
            });

            // Generate file
            const buffer = await workbook.xlsx.writeBuffer();
            const blob = new Blob([buffer], { type: EXPORT_CONFIG.EXCEL.MIME_TYPE });

            // Download file
            const url = window.URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.href = url;
            link.download = this._generateFileName('xlsx');
            link.click();
            window.URL.revokeObjectURL(url);

            

            // Restore button state
            exportBtn.innerHTML = originalText;
            exportBtn.disabled = false;

        } catch (error) {
            console.error('❌ Error exporting to Excel:', error);
            alert('Error saat export ke Excel: ' + error.message);
            
            // Restore button state
            const exportBtn = document.getElementById(ELEMENT_IDS.EXPORT_EXCEL);
            exportBtn.innerHTML = '<i class="fas fa-file-excel me-1"></i>Export Excel';
            exportBtn.disabled = false;
        }
    }

    /**
     * Export table to PDF
     */
    async exportToPDF() {
        try {
            if (!this.currentData?.tableData) {
                console.warn('⚠️ No data to export');
                return;
            }

            // Check if jsPDF is available
            if (typeof window.jsPDF === 'undefined') {
                console.error('❌ jsPDF library not found');
                alert('jsPDF library is required for PDF export');
                return;
            }

            

            // Show loading state
            const exportBtn = document.getElementById(ELEMENT_IDS.EXPORT_PDF);
            const originalText = exportBtn.innerHTML;
            exportBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Exporting...';
            exportBtn.disabled = true;

            // Create PDF
            const { jsPDF } = window.jsPDF;
            const doc = new jsPDF('l', 'mm', 'a4'); // landscape

            // Add title
            doc.setFontSize(16);
            doc.text('Sales Ratio Report', 20, 20);

            // Add date info
            doc.setFontSize(10);
            doc.text(`Generated: ${new Date().toLocaleDateString('id-ID')}`, 20, 30);

            // Prepare table data
            const processedData = this._processTableData(this.currentData.tableData, this.currentData.sortBy);
            const tableData = processedData.map(row => [
                row.no,
                row.periode,
                row.kode_supp,
                row.qty.toLocaleString('id-ID'),
                'Rp ' + row.total.toLocaleString('id-ID'),
                row.persentase_rp,
                row.percentage
            ]);

            // Add table using autoTable if available
            if (doc.autoTable) {
                doc.autoTable({
                    startY: 40,
                    head: [['No', 'Periode', 'Supplier', 'Qty', 'Total (Rp)', '% Rp', '% Qty']],
                    body: tableData,
                    theme: 'striped',
                    styles: { fontSize: 8, cellPadding: 2 },
                    headStyles: { fillColor: [68, 114, 196], textColor: 255 },
                    columnStyles: {
                        0: { halign: 'center', cellWidth: 15 },
                        1: { cellWidth: 25 },
                        2: { cellWidth: 30 },
                        3: { halign: 'right', cellWidth: 25 },
                        4: { halign: 'right', cellWidth: 35 },
                        5: { halign: 'right', cellWidth: 20 },
                        6: { halign: 'right', cellWidth: 20 }
                    }
                });
            }

            // Save PDF
            doc.save(this._generateFileName('pdf'));

            

            // Restore button state
            exportBtn.innerHTML = originalText;
            exportBtn.disabled = false;

        } catch (error) {
            console.error('❌ Error exporting to PDF:', error);
            alert('Error saat export ke PDF: ' + error.message);
            
            // Restore button state
            const exportBtn = document.getElementById(ELEMENT_IDS.EXPORT_PDF);
            exportBtn.innerHTML = '<i class="fas fa-file-pdf me-1"></i>Export PDF';
            exportBtn.disabled = false;
        }
    }

    /**
     * Generate filename untuk export
     * @private
     * @param {string} extension - File extension
     * @returns {string} Generated filename
     */
    _generateFileName(extension) {
        const now = new Date();
        const timestamp = now.toISOString().slice(0, 19).replace(/:/g, '-');
        return `sales-ratio-report_${timestamp}.${extension}`;
    }

    /**
     * Clear table
     */
    clear() {
        const tableContainer = document.getElementById(ELEMENT_IDS.TABLE_RESULT);
        if (tableContainer) {
            tableContainer.innerHTML = '';
        }
        this.currentData = null;
        this.sortState = { column: null, direction: 'asc' };
        
    }

    /**
     * Show table container
     */
    show() {
        const container = document.getElementById(ELEMENT_IDS.TABLE_CONTAINER);
        if (container) {
            container.style.display = 'block';
        }
    }

    /**
     * Hide table container
     */
    hide() {
        const container = document.getElementById(ELEMENT_IDS.TABLE_CONTAINER);
        if (container) {
            container.style.display = 'none';
        }
    }

    /**
     * Check if table has data
     * @returns {boolean} Has data status
     */
    hasData() {
        return this.currentData?.tableData?.length > 0;
    }

    /**
     * Get current table data
     * @returns {Object|null} Current data
     */
    getCurrentData() {
        return this.currentData;
    }
}

// Create singleton instance
const tableManager = new TableManager();

export default tableManager;
