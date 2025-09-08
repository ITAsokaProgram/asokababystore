/**
 * @fileoverview Constants dan konfigurasi untuk Sales Ratio Report
 * @description Berisi semua konstanta, API endpoints, dan konfigurasi aplikasi
 */

/**
 * API endpoints untuk sales ratio report
 * @constant {Object}
 */
export const API_ENDPOINTS = {
    GET_SUPPLIERS: '/src/api/get_kode_supp',
    RATIO_BAR_PROCESS: '/src/api/ratio/in_sales_ratio_proses_bar',
    RATIO_TABLE_PROCESS: '/src/api/ratio/in_sales_ratio_proses_table'
};

/**
 * Element IDs yang digunakan dalam aplikasi
 * @constant {Object}
 */
export const ELEMENT_IDS = {
    // Form elements
    LAPORAN_FORM: 'laporanForm',
    CABANG: 'cabang',
    DATE: 'date',
    DATE1: 'date1',
    RATIO_NUMBER: 'ratio_number',
    SORT_BY: 'sort-by',
    
    // Supplier inputs (dynamic)
    KODE_SUPP_PREFIX: 'kode_supp',
    SUPPLIER_DROPDOWN: 'supplierDropdown',
    
    // Buttons
    BTN_SUBMIT: 'btn-submit',
    SEND_TABLE: 'sendTable',
    LIHAT_TABLE: 'lihatTable',
    
    // Charts and tables
    BAR_DIAGRAM: 'barDiagram',
    BAR_CONTAINER: 'bar',
    TABLE_KODE1: 'tableKode1',
    
    // Headers
    REPORT_HEADER1: 'reportHeader1',
    
    // Sidebar
    SIDEBAR: 'sidebar',
    TOGGLE_SIDEBAR: 'toggle-sidebar',
    TOGGLE_HIDE: 'toggle-hide',
    CLOSE_SIDEBAR: 'closeSidebar',
    MAIN_CONTENT: 'main-content',
    
    // Profile
    PROFILE_IMG: 'profile-img',
    PROFILE_CARD: 'profile-card',
    
    // Search
    SEARCH_INPUT: 'searchInput'
};

/**
 * Store codes mapping untuk setiap cabang
 * @constant {Object}
 */
export const STORE_CODES = {
    'ABIN': '1502',
    'ACE': '1505',
    'ACIB': '1379',
    'ACIL': '1504',
    'ACIN': '1641',
    'ACSA': '1902',
    'ADET': '1376',
    'ADMB': '3190',
    'AHA': '1506',
    'AHIN': '2102',
    'ALANG': '1503',
    'ANGIN': '2102',
    'APEN': '1908',
    'APIK': '3191',
    'APRS': '1501',
    'ARAW': '1378',
    'ARUNG': '1611',
    'ASIH': '2104',
    'ATIN': '1642',
    'AWIT': '1377',
    'AXY': '2103'
};

/**
 * Chart color palette
 * @constant {Array}
 */
export const CHART_COLORS = [
    "#ff5733", "#33ff57", "#3357ff", "#f4a261", "#e63946",
    "#6a0572", "#0e9594", "#f77f00", "#2a9d8f", "#6c757d"
];

/**
 * Default date range configuration
 * @constant {Object}
 */
export const DATE_CONFIG = {
    DEFAULT_START_DAYS_BACK: 30,
    DEFAULT_END_DAYS_BACK: 1,
    FORMAT: 'd-m-Y'
};

/**
 * Sort options
 * @constant {Object}
 */
export const SORT_OPTIONS = {
    QTY: 'Qty',
    TOTAL: 'Total'
};

/**
 * Chart configuration defaults
 * @constant {Object}
 */
export const CHART_CONFIG = {
    ANIMATION_DURATION: 300,
    BAR_CATEGORY_GAP: '10%',
    TOOLTIP_TRIGGER: 'axis',
    GRID: {
        left: '3%',
        right: '9%',
        bottom: '5%',
        containLabel: true
    }
};

/**
 * Table configuration
 * @constant {Object}
 */
export const TABLE_CONFIG = {
    HEADERS: ['No', 'NAMA BARANG', 'QTY', 'TOTAL'],
    PAGE_SIZE: 50,
    EMPTY_MESSAGE: 'Tidak ada data tersedia'
};

/**
 * CSS classes untuk styling
 * @constant {Object}
 */
export const CSS_CLASSES = {
    SUGGESTION_BOX: 'absolute bg-white border border-gray-300 w-full max-h-40 overflow-y-auto z-50 shadow-md rounded-md suggestion-box',
    SUGGESTION_ITEM: 'px-4 py-2 cursor-pointer border-b border-gray-200 text-gray-700 hover:bg-gray-100',
    SUPPLIER_INPUT: 'supplier-input',
    TABLE_ROW: 'hover:bg-blue-50 transition-all duration-200 shadow-sm'
};

/**
 * Local storage keys
 * @constant {Object}
 */
export const STORAGE_KEYS = {
    DATA_TEMPORARY: 'dataTemporary',
    RATIO_CHART: 'ratioChart'
};

/**
 * Maximum number of suppliers allowed
 * @constant {number}
 */
export const MAX_SUPPLIERS = 5;

/**
 * Notification configuration
 * @constant {Object}
 */
export const NOTIFICATION_CONFIG = {
    POSITION: 'top-end',
    TIMER: 1300,
    SHOW_CONFIRM_BUTTON: false,
    TIMER_PROGRESS_BAR: true
};

/**
 * Export configuration
 * @constant {Object}
 */
export const EXPORT_CONFIG = {
    EXCEL: {
        MIME_TYPE: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        EXTENSION: '.xlsx'
    },
    PDF: {
        FORMAT: 'a4',
        ORIENTATION: 'portrait',
        UNIT: 'mm'
    }
};

/**
 * Company information for exports
 * @constant {Object}
 */
export const COMPANY_INFO = {
    NAME: 'PT. Asoka Indonesia',
    ADDRESS: 'Lb 5, Jl. Utan Jati Blok Lb 5 No.9, RT.10/RW.12, Kalideres, West Jakarta City, Jakarta 11840',
    PHONE: '0819-4943-1969',
    EMAIL: 'info@contoh.com',
    LOGO_PATH: '/images/logo.png'
};

/**
 * Flatpickr configuration
 * @constant {Object}
 */
export const FLATPICKR_CONFIG = {
    dateFormat: 'd-m-Y',
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
    }
};
