// Collect DOM elements into a single object for easier imports/use
const el = {
    // Table / pagination
    tbody: document.getElementById('tbody'),
    countText: document.getElementById('countText'),
    pageText: document.getElementById('pageText'),
    prevBtn: document.getElementById('prevBtn'),
    nextBtn: document.getElementById('nextBtn'),
    searchInput: document.getElementById('search'),

    // Modal / form
    modal: document.getElementById('modal'),
    btnAdd: document.getElementById('btnAdd'),
    closeModal: document.getElementById('closeModal'),
    cancelBtn: document.getElementById('cancelBtn'),
    modalTitle: document.getElementById('modalTitle'),
    productForm: document.getElementById('productForm'),
    productId: document.getElementById('productId'),

    // Inputs
    nameInput: document.getElementById('name'),
    barcode: document.getElementById('barcode'),
    plu: document.getElementById('plu'),
    cabang: document.getElementById('cabang'),
    categoryInput: document.getElementById('category'),
    priceInput: document.getElementById('price'),
    stockInput: document.getElementById('stock'),
    descriptionInput: document.getElementById('description'),
    imageInput: document.getElementById('imageInput'),
    uploadBtn: document.getElementById('uploadBtn'),
    preview: document.getElementById('preview'),
    filterCabang: document.getElementById("filterCabang"),
    filterSearch: document.getElementById("filterSearch"),
};

// Export in a way that works both in browser globals and CommonJS
if (typeof module !== 'undefined' && module.exports) {
    module.exports = el;
} else {
    window.el = el;
}

export default el;
