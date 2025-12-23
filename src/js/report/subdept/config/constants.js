/**
 * @fileoverview Konstanta dan mapping untuk laporan sub departemen
 * @description File ini berisi semua konstanta yang digunakan dalam aplikasi
 * seperti API endpoints, konfigurasi chart, dan mapping data
 */

/**
 * API endpoints untuk sub departemen report
 * @constant {Object}
 */
export const API_ENDPOINTS = {
    SUBDEPT_DATA: '../../api/subdepartemen/post_data_sub_dept.php',
    BRANCH_CODES: '/src/api/cabang/get_kode'
};

/**
 * Query types yang tersedia
 * @constant {Object}
 */
export const QUERY_TYPES = {
    SUBDEPT: 'query1',           // Query untuk sub departemen
    SUPPLIER: 'query2',          // Query untuk supplier
    PROMO: 'query3'              // Query untuk promo
};

/**
 * Konfigurasi warna untuk chart
 * @constant {Array}
 */
export const CHART_COLORS = [
    "rgba(255, 99, 132, 1)",
    "rgba(54, 162, 235, 1)", 
    "rgba(255, 206, 86, 1)",
    "rgba(75, 192, 192, 1)",
    "rgba(153, 102, 255, 1)",
    "rgba(255, 159, 64, 1)",
    "rgba(199, 199, 199, 1)",
    "rgba(83, 102, 255, 1)",
    "rgba(255, 159, 243, 1)",
    "rgba(255, 205, 86, 1)"
];

/**
 * Konfigurasi animasi chart
 * @constant {Object}
 */
export const CHART_ANIMATION = {
    animationDuration: 1000,
    animationEasing: 'cubicOut'
};

/**
 * ID elemen HTML yang digunakan
 * @constant {Object}
 */
export const ELEMENT_IDS = {
    // Charts
    PIE_CHART: 'chartDiagram',
    BAR_CHART: 'barDiagram',
    
    // Tables
    SALES_TABLE: 'salesTable',
    SALES_TABLE_SUPPLIER: 'salesTableSupplier', 
    SALES_TABLE_PROMO: 'salesTablePromo',
    SALES_TABLE_PENJUALAN: 'salesTablePenjualan',
    
    // Buttons
    BTN_SUBMIT: 'btn-submit',
    BTN_SUB: 'btn-sub',
    BTN_BACK: 'btn-back',
    BTN_SEE_PENJUALAN: 'btn-see-penjualan',
    BTN_SEE_PROMO: 'btn-see-promo',
    BTN_PROMO: 'btn-promo',
    
    // Form elements
    FORM: 'laporanForm',
    CABANG: 'cabang',
    DATE: 'date',
    DATE1: 'date1',
    SORT_BY: 'sort-by',
    SORT_BY1: 'sort-by1',
    KD_STORE: 'kd_store',
    SUBDEPT: 'subdept',
    KODE_SUPP: 'kode_supp',
    
    // Containers
    CONTAINER_BAR: 'bar',
    CONTAINER_PIE: 'pie',
    CONTAINER_TABLE: 'container-table',
    
    // Headers
    REPORT_HEADER_PROMO: 'reportHeaderPromo',
    TH_HEAD_PROMO: 'thHeadPromo',
    
    // Sidebar
    SIDEBAR: 'sidebar',
    CLOSE_SIDEBAR: 'closeSidebar'
};

/**
 * Local storage keys
 * @constant {Object}
 */
export const STORAGE_KEYS = {
    SALES_TABLE_ORIGINAL: 'salesTableOriginal',
    CHART_BART: 'chartBart',
    ACTIVE_QUERY_TYPE: 'activeQueryType'
};

/**
 * Sort options mapping
 * @constant {Object}
 */
export const SORT_OPTIONS = {
    QTY: 'Qty',
    TOTAL: 'Total'
};

/**
 * Default values
 * @constant {Object}
 */
export const DEFAULTS = {
    CURRENT_PAGE: 1,
    TOTAL_PAGES: 1,
    ACTIVE_QUERY_TYPE: QUERY_TYPES.SUBDEPT
};

/**
 * Table headers untuk berbagai jenis tabel
 * @constant {Object}
 */
export const TABLE_HEADERS = {
    SUBDEPT: [
        { key: 'kode_subdept', label: 'Kode Sub Dept', width: '15%' },
        { key: 'nama_subdept', label: 'Nama Sub Departemen', width: '40%' },
        { key: 'Qty', label: 'Quantity', width: '15%', align: 'right' },
        { key: 'Total', label: 'Total (Rp)', width: '20%', align: 'right' },
        { key: 'persentase', label: 'Persentase (%)', width: '10%', align: 'center' }
    ],
    SUPPLIER: [
        { key: 'kode_supp', label: 'Kode Supplier', width: '15%' },
        { key: 'nama_supp', label: 'Nama Supplier', width: '40%' },
        { key: 'Qty', label: 'Quantity', width: '15%', align: 'right' },
        { key: 'Total', label: 'Total (Rp)', width: '20%', align: 'right' },
        { key: 'persentase', label: 'Persentase (%)', width: '10%', align: 'center' }
    ],
    PROMO: [
        { key: 'kode', label: 'Kode', width: '15%' },
        { key: 'promo', label: 'Nama Promo', width: '30%' },
        { key: 'nama_barang', label: 'Nama Barang', width: '25%' },
        { key: 'Qty', label: 'Quantity', width: '10%', align: 'right' },
        { key: 'Total', label: 'Total (Rp)', width: '15%', align: 'right' },
        { key: 'Percentage', label: '%', width: '5%', align: 'center' }
    ]
};
