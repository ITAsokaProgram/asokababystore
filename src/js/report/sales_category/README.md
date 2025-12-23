# Sales Category Report - Modular JavaScript Application

Apli### Services (`/services`)

#### `api.js`
Service utama untuk komunikasi dengan backend API.

**Key Methods:**
- `fetchData(endpoint, params)` - Fetch data dari API
- `_buildQueryString(params)` - Build query string untuk URL
- `_handleApiError(response)` - Handle error response dari API

#### `branchService.js`
Service untuk manajemen data cabang secara dinamis.

**Key Methods:**
- `initialize()` - Inisialisasi dan load data cabang dari API
- `fetchBranches()` - Fetch daftar cabang dari endpoint `/src/api/cabang/get_kode`
- `getStoreCodeByBranch(branchId)` - Dapatkan store code berdasarkan branch ID
- `getAllBranches()` - Dapatkan semua data cabang yang sudah di-cache

**Features:**
- ✅ Singleton pattern untuk instance tunggal
- ✅ Caching mechanism untuk performa optimal
- ✅ Error handling yang robust
- ✅ Dynamic data loading dari APIaporan penjualan kategori yang telah di-refactor menjadi struktur modular dengan ES6 modern JavaScript untuk meningkatkan maintainability, scalability, dan code quality.

## 📁 Struktur Folder

```
src/js/report/sales_category/
├── config/
│   └── constants.js              # Konstanta dan konfigurasi aplikasi
├── services/
│   └── api.js                    # Service untuk API calls
├── components/
│   ├── chartManager.js           # Manager untuk ECharts
│   ├── tableManager.js           # Manager untuk DataTables
│   └── uiManager.js              # Manager untuk UI operations
├── utils/
│   ├── formatters.js             # Utility functions untuk formatting
│   ├── state.js                  # State management untuk cache & history
│   └── dateManager.js            # Manager untuk date operations
├── handlers/
│   └── eventHandlers.js          # Event handlers untuk user interactions
├── main.js                       # Entry point aplikasi
└── README.md                     # Dokumentasi ini
```

## Features

- 📊 **Dynamic Chart Generation**: Visualisasi data dengan ECharts
- 📅 **Date Range Selection**: Pemilihan rentang tanggal yang fleksibel
- 🏢 **Dynamic Branch Management**: Data cabang dimuat secara dinamis dari API
- 📈 **Real-time Data Processing**: Pemrosesan data realtime
- 🎨 **Responsive UI**: Interface yang responsif dan modern
- 🔍 **Advanced Filtering**: Filter data berdasarkan berbagai kriteria
- 💾 **State Management**: Pengelolaan state aplikasi yang efisien
- 🔄 **Caching System**: Sistem caching untuk performa optimal

## 📊 Komponen Utama

### **ChartManager**
Mengelola semua operasi chart menggunakan ECharts:
- Pie chart untuk kategori awal
- Pie chart untuk supplier per kategori  
- Bar/Line chart untuk detail timeline
- Interactive click handlers
- Responsive design

### **TableManager**
Mengelola DataTables operations:
- Dynamic column generation
- Export to Excel/PDF
- Search & pagination
- Custom styling
- Responsive layout

### **UIManager**
Mengelola tampilan UI dan notifikasi:
- Element visibility management
- Loading states
- Success/Error notifications
- Mode switching (early/category/detail)

### **API Service**
Centralized API calls:
- RESTful API integration
- Error handling dengan retry logic
- Response validation
- Request logging

### **State Management**
Mengelola aplikasi state:
- Chart history untuk navigation
- Data caching untuk performance
- State persistence

## 🛠️ Cara Penggunaan

### 1. **Import di HTML**
```html
<script type="module" src="/src/js/report/sales_category/main.js"></script>
```

### 2. **Dependencies Required**
- ECharts library
- jQuery & DataTables
- SweetAlert2 (untuk notifications)
- Flatpickr (untuk date picker)

### 3. **HTML Elements Required**
```html
<div id="chartDiagram"></div>
<div id="wrapper-table">
  <table id="dataCategoryTable"></table>
</div>
<button id="btn-send">Kirim</button>
<button id="btn-back">Kembali</button>
<select id="cabang"></select>
<input type="text" id="date" />
<input type="text" id="date1" />
```

## 📝 API Documentation

### **Endpoints Used**
- `POST /src/api/category/post_data_sales_category` - Fetch sales category data

### **Request Format**
```javascript
{
  kd_store: "string",     // Store codes (comma separated)
  start_date: "string",   // Format: dd-mm-yyyy
  end_date: "string",     // Format: dd-mm-yyyy
  query: "string",        // Query type or category
  filter?: "string"       // Optional filter
}
```

### **Response Format**
```javascript
{
  status: "success|error",
  message: "string",
  data: [
    {
      type_kategori: "string",
      total_qty: number,
      total: number,
      persentase: number,
      // ... other fields
    }
  ]
}
```

## 🔧 Configuration

### **Constants (config/constants.js)**
- `API_ENDPOINTS`: URL endpoints termasuk endpoint branch dinamis
- `CHART_COLORS`: Warna untuk chart
- `CATEGORY_NAME_MAPPING`: Mapping nama kategori
- `ELEMENT_IDS`: ID elemen HTML

### **Branch Service (services/branchService.js)**
- Data cabang dimuat secara dinamis dari API `/src/api/cabang/get_kode`
- Menggunakan caching mechanism untuk performa optimal
- Singleton pattern untuk konsistensi data

### **Customization**
Semua konfigurasi dapat diubah di file `config/constants.js` tanpa mengubah logic aplikasi. Data cabang sekarang bersifat dinamis dan tidak perlu hardcode.

## 🧪 Testing & Debugging

### **Debug Methods (Development)**
```javascript
// Cek status aplikasi
window.appStatus()

// Reset aplikasi
window.appReset()

// Restart aplikasi
window.appRestart()
```

### **Console Logging**
- Setiap komponen memiliki logging yang jelas
- Error tracking dengan context
- Performance monitoring

## 🔄 Migration dari Code Lama

### **Breaking Changes**
- Global variables sekarang di-encapsulate dalam classes
- Event listeners sekarang menggunakan modern approach
- State management menggunakan centralized system

### **Migration Steps**
1. Update HTML untuk include main.js sebagai module
2. Pastikan semua dependencies tersedia
3. Update element IDs jika diperlukan
4. Test semua functionality

## 🎯 Best Practices

### **Code Style**
- JSDoc comments untuk semua functions
- Consistent naming conventions
- Error handling di semua async operations
- Modular approach dengan single responsibility

### **Performance**
- Lazy loading untuk komponen berat
- Debounced event handlers
- Efficient DOM manipulation
- Memory management dengan proper cleanup

### **Maintainability**
- Clear separation of concerns
- Centralized configuration
- Comprehensive error handling
- Detailed logging dan debugging tools

## 🐛 Troubleshooting

### **Common Issues**

1. **Chart tidak muncul**
   - Pastikan ECharts library loaded
   - Check element dengan ID 'chartDiagram' ada
   - Lihat console untuk error messages

2. **Table tidak render**
   - Pastikan DataTables library loaded
   - Check data format yang dikirim ke table
   - Validate response dari API

3. **Date picker tidak berfungsi**
   - Pastikan Flatpickr library loaded
   - Check format tanggal (dd-mm-yyyy)
   - Validate date range

4. **Branch data tidak tersedia**
   - Check koneksi ke API `/src/api/cabang/get_kode`
   - Pastikan endpoint branch API berfungsi dengan baik
   - Lihat console log untuk error inisialisasi branchService

### **Debug Steps**
1. Open browser console
2. Check for JavaScript errors  
3. Verify API responses (termasuk branch API)
4. Test initialization dengan melihat log console
5. Check branchService data dengan `branchService.getAllBranches()`

## 📞 Support

Untuk pertanyaan atau issue, silakan contact development team atau create issue di repository.

---

**Version**: 2.0.0  
**Last Updated**: August 2025  
**Author**: Asoka Baby Store Development Team
