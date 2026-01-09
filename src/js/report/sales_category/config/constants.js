
export const CATEGORY_NAME_MAPPING = {
    BABY: 'BABY STORE',
    DST: 'DEPARTEMEN STORE',
    SPM: 'SUPERMARKET'
};


export const CHART_COLORS = [
    "rgba(255, 99, 132, 1)",
    "rgba(54, 162, 235, 1)",
    "rgba(255, 206, 86, 1)",
    "rgba(75, 192, 192, 1)",
    "rgba(153, 102, 255, 1)",
    "rgba(255, 159, 64, 1)",
];


export const CHART_ANIMATION_CONFIG = {
    duration: 1500,
    easing: 'quinticInOut'
};


export const API_ENDPOINTS = {
    SALES_CATEGORY: '/src/api/category/post_data_sales_category',
    BRANCH_CODES: '/src/api/cabang/get_kode'
};


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


export const CHART_MODES = {
    EARLY: 'early',
    CATEGORY: 'category',
    DETAIL: 'detail'
};
