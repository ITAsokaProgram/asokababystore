# 📊 Sub Department Report - Modular Architecture

Aplikasi laporan sub departemen yang telah direfactor menggunakan arsitektur modular ES6 dengan struktur yang bersih, maintainable, dan mudah dikembangkan.

## 🎯 Overview

Aplikasi ini menampilkan laporan data sub departemen dengan visualisasi chart (pie dan bar chart), tabel data yang interaktif, dan berbagai filter untuk analisis data penjualan berdasarkan sub departemen, supplier, dan promo.

## ✨ Features

- 📊 **Dynamic Chart Visualization**: Pie chart dan bar chart dengan ECharts
- 📋 **Interactive Data Tables**: Tabel data dengan sorting dan filtering
- 🏪 **Multi-Mode Support**: Sub departemen, supplier, dan promo mode
- 🎨 **Responsive UI**: Interface yang responsif dan modern dengan Bootstrap
- 🔍 **Advanced Filtering**: Filter berdasarkan tanggal, cabang, dan kategori
- 💾 **State Management**: Pengelolaan state aplikasi yang efisien
- 🔄 **Real-time Data**: Loading data dinamis dari API
- 📱 **Mobile Friendly**: Optimized untuk berbagai ukuran layar
- 🎁 **Promo Analytics**: Analisis khusus untuk data promosi

## 📁 Project Structure

```
/src/js/report/subdept/
├── main.js                    # Entry point aplikasi
├── config/
│   └── constants.js          # Konstanta dan konfigurasi
├── services/
│   ├── api.js               # API service layer
│   └── branchService.js     # Branch data service (dinamis)
├── components/
│   ├── chartManager.js      # ECharts management
│   ├── tableManager.js      # DataTables management  
│   └── uiManager.js         # UI state management
├── utils/
│   ├── formatters.js        # Utility functions
│   └── state.js            # Application state management
├── handlers/
│   └── eventHandlers.js     # Event handling
└── README.md               # Dokumentasi ini
```

## 🏗️ Architecture

### **Main Application (main.js)**
Entry point yang menginisialisasi semua komponen:
- Dependency injection dan orchestration
- Error handling global
- Application lifecycle management
- Development tools dan debugging

### **Components**

#### **ChartManager**
Mengelola visualisasi chart dengan ECharts:
- Pie chart untuk distribusi data
- Bar chart untuk perbandingan
- Interactive tooltips dan legends
- Responsive resize handling
- Custom color schemes

#### **TableManager**
Mengelola operasi tabel data:
- Dynamic table rendering
- Multiple table types (subdept/supplier/promo)
- Sort & filter functionality
- Pagination support
- Data formatting dan styling

#### **UIManager**
Mengelola tampilan UI dan notifikasi:
- Loading states dengan SweetAlert2
- Success/Error notifications
- Element visibility management
- Button state management
- Report header updates

### **Services**

#### **API Service**
Centralized API communication:
- RESTful API integration dengan Fetch dan jQuery AJAX
- Error handling dengan retry logic
- Response validation
- FormData management
- Request logging

#### **Branch Service**
Service untuk manajemen data cabang secara dinamis:
- Dynamic branch data loading dari API `/src/api/cabang/get_kode`
- Singleton pattern dengan caching mechanism
- Authorization token handling
- Store code mapping untuk dropdown options
- Error handling yang robust

### **Utils**

#### **State Management**
Mengelola aplikasi state:
- Data caching (table dan chart data)
- Pagination state
- Mode flags (subdept/promo/supplier)
- Session storage integration
- State persistence

#### **Formatters**
Utility functions untuk formatting:
- Currency dan number formatting
- Date parsing dan validation
- Text truncation
- Data transformation
- Array operations

### **Handlers**

#### **Event Handlers**
Mengelola semua user interactions:
- Button click handlers
- Form submission
- Sort change events
- Chart interaction events
- Custom event dispatching

## 🛠️ Cara Penggunaan

### 1. **Import di HTML**
```html
<script type="module" src="/src/js/report/subdept/main.js"></script>
```

### 2. **Konfigurasi Backend**
Pastikan API endpoint tersedia:
```php
// File: /api/subdepartemen/post_data_sub_dept.php
// Query types: query1 (subdept), query2 (supplier), query3 (promo)

// File: /src/api/cabang/get_kode
// Endpoint untuk dynamic branch data
```

### 3. **HTML Elements Required**
```html
<!-- Charts -->
<div id="chartDiagram"></div>
<div id="barDiagram"></div>

<!-- Tables -->
<div id="salesTable"></div>
<div id="salesTableSupplier"></div>
<div id="salesTablePromo"></div>

<!-- Form -->
<form id="laporanForm">
    <select id="cabang"></select>
    <input id="date" type="text">
    <input id="date1" type="text">
    <select id="sort-by"></select>
</form>

<!-- Buttons -->
<button id="btn-submit">Submit</button>
<button id="btn-sub">Supplier</button>
<button id="btn-see-promo">Lihat Promo</button>
<button id="btn-back">Kembali</button>
```

## 🔧 Configuration

### **API Endpoints (config/constants.js)**
```javascript
export const API_ENDPOINTS = {
    SUBDEPT_DATA: '../../api/subdepartemen/post_data_sub_dept.php',
    BRANCH_CODES: '/src/api/cabang/get_kode'
};
```

### **Query Types**
```javascript
export const QUERY_TYPES = {
    SUBDEPT: 'query1',      // Sub departemen data
    SUPPLIER: 'query2',     // Supplier data  
    PROMO: 'query3'         // Promo data
};
```

### **Chart Colors**
```javascript
export const CHART_COLORS = [
    "rgba(255, 99, 132, 1)",
    "rgba(54, 162, 235, 1)", 
    // ... more colors
];
```

## 📡 API Integration

### **Request Format**
```javascript
// FormData yang dikirim ke server
{
    ajax: true,
    query_type: 'query1|query2|query3',
    kd_store: '1502',
    start_date: '01-01-2025',
    end_date: '31-01-2025',
    page: 1,
    filter: 'Total|Qty'
}
```

### **Response Format**
```javascript
{
    status: "success|error",
    message: "string",
    labels: ["Label1", "Label2", ...],
    data: [value1, value2, ...],
    tableData: [
        {
            kode_subdept: "001",
            nama_subdept: "Sub Dept Name",
            Qty: 100,
            Total: 1000000,
            persentase: 15.5
        }
    ],
    totalPages: 10
}
```

## 🧪 Development & Debugging

### **Development Tools**
```javascript
// Available di browser console
window.subDeptApp           // Instance aplikasi
window.appStatus()          // Status semua komponen
subDeptApp.getStatus()      // Detailed status
subDeptApp.restart()        // Restart aplikasi
```

### **Logging**
- Semua operasi di-log dengan emoji indicators
- Error tracking dengan stack traces
- Performance monitoring
- State change notifications

### **Error Handling**
- Comprehensive try-catch blocks
- User-friendly error messages
- Graceful degradation
- Recovery mechanisms

## 🎨 Customization

### **Chart Styling**
```javascript
// Edit di components/chartManager.js
const option = {
    tooltip: { /* custom tooltip */ },
    legend: { /* custom legend */ },
    series: [{ /* custom series */ }]
};
```

### **Table Headers**
```javascript
// Edit di config/constants.js
export const TABLE_HEADERS = {
    SUBDEPT: [
        { key: 'kode_subdept', label: 'Kode', width: '15%' },
        // ... more headers
    ]
};
```

### **UI Themes**
```javascript
// Edit di components/uiManager.js untuk custom styling
```

## 🚀 Performance

### **Optimization Features**
- Data caching dengan localStorage
- Lazy loading untuk charts
- Event listener cleanup
- Memory management
- Debounced operations

### **Best Practices Applied**
- Modular architecture dengan separation of concerns
- Centralized error handling
- Consistent code patterns
- Comprehensive documentation
- Type safety dengan JSDoc

## 🐛 Troubleshooting

### **Common Issues**

1. **Chart tidak muncul**
   - Pastikan ECharts library loaded
   - Check element dengan ID 'chartDiagram' dan 'barDiagram' ada
   - Lihat console untuk error messages

2. **Table tidak render**
   - Pastikan data format sesuai dengan TABLE_HEADERS
   - Check response dari API
   - Validate element dengan ID table ada

3. **API calls gagal**
   - Check network tab untuk HTTP status
   - Pastikan endpoint API accessible
   - Validate request format dan headers

4. **State tidak tersimpan**
   - Check localStorage permissions
   - Validate sessionStorage availability
   - Clear cache jika perlu

### **Debug Steps**
1. Open browser console
2. Check `window.appStatus()` untuk component status
3. Verify API responses di Network tab
4. Test state dengan `subDeptApp.getComponent('state').getStateSummary()`

## 📞 Support

Untuk pertanyaan, bug reports, atau feature requests:
- Check console logs untuk error details
- Gunakan `window.appStatus()` untuk diagnostics
- Contact development team dengan error screenshots

---

**Version**: 1.0.0  
**Last Updated**: August 2025  
**Maintainer**: Development Team
