/**
 * @fileoverview Konstanta dan mapping untuk laporan penjualan kategori
 * @description File ini berisi semua konstanta yang digunakan dalam aplikasi
 * seperti mapping nama kategori, kode cabang, dan konfigurasi chart
 */

/**
 * Mapping nama kategori untuk display yang lebih user-friendly
 * @constant {Object}
 */
export const CATEGORY_NAME_MAPPING = {
    BABY: 'BABY STORE',
    DST: 'DEPARTEMEN STORE',
    SPM: 'SUPERMARKET'
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
];

/**
 * Konfigurasi animasi chart
 * @constant {Object}
 */
export const CHART_ANIMATION_CONFIG = {
    duration: 1500,
    easing: 'quinticInOut'
};

/**
 * API endpoints
 * @constant {Object}
 */
export const API_ENDPOINTS = {
    SALES_CATEGORY: '/src/api/category/post_data_sales_category',
    BRANCH_CODES: '/src/api/cabang/get_kode'
};

/**
 * Element IDs yang digunakan dalam aplikasi
 * @constant {Object}
 */
export const ELEMENT_IDS = {
    CHART_DIAGRAM: 'chartDiagram',
    WRAPPER_TABLE: 'wrapper-table',
    BTN_BACK: 'btn-back',
    BTN_SEND: 'btn-send',
    SORT_FILTER: 'sort-filter',
    SORT_FILTER1: 'sort-filter1',
    DATE_START: 'date',
    DATE_END: 'date1',
    BRANCH_SELECT: 'cabang',
    DATA_TABLE: 'dataCategoryTable',
    BTN_DATASET: 'exportExcel',
    BTN_PDF: 'exportPDF'
};

/**
 * Chart modes untuk state management
 * @constant {Object}
 */
export const CHART_MODES = {
    EARLY: 'early',
    CATEGORY: 'category',
    DETAIL: 'detail'
};
