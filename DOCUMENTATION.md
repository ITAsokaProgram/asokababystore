# 📚 Asoka ID - Project Documentation

> **Dokumentasi Lengkap untuk Project Asoka ID - Point Reward System**  
> Panduan untuk Web Developer Fullstack yang ingin memahami arsitektur dan struktur kode

---

## 🏗️ **Arsitektur Project**

Project ini adalah sistem reward poin berbasis web dengan arsitektur **hybrid PHP backend + Modern JavaScript frontend**:

- **Backend**: PHP dengan struktur MVC-like
- **Frontend**: Vanilla JavaScript dengan ES6 Modules  
- **Database**: MySQL/MariaDB
- **Authentication**: JWT Token-based
- **Styling**: Tailwind CSS
- **API**: RESTful API endpoints

---

## 📁 **Struktur Folder Utama**

```
src/
├── 📂 api/              # REST API Endpoints
├── 📂 auth/             # Authentication & Middleware  
├── 📂 component/        # UI Components (Backend)
├── 📂 config/           # Configuration Files
├── 📂 fitur/            # Business Logic Modules
├── 📂 js/               # Frontend JavaScript Code
├── 📂 log/              # Logging System
├── 📂 style/            # CSS Styling
└── 📂 utils/            # Helper Utilities
```

---

## 🔍 **Detail Struktur & Fungsi**

### 🌐 **`api/` - REST API Endpoints**

Folder ini berisi semua endpoint API yang digunakan oleh frontend untuk komunikasi dengan backend.

```
api/
├── 📁 cabang/               # Branch/Store management
├── 📁 category/             # Product categories
├── 📁 customer/             # Customer operations
│   ├── get_profile_customer.php     # Customer profile
│   ├── get_poin_customer.php        # Customer points
│   ├── history_transaction.php      # Transaction history
│   └── update_customer.php          # Update customer data
├── 📁 dashboard/            # Dashboard analytics
│   ├── get_data_dashboard.php       # Dashboard overview
│   ├── get_pendapatan.php          # Revenue data
│   └── top_margin.php              # Top margin products
├── 📁 poin/                 # Point system
│   ├── redeem_reward.php           # Redeem points for rewards
│   ├── get_user_points.php         # Get user current points
│   └── get_rewards.php             # Available rewards list
├── 📁 qr/                   # QR Code operations
├── 📁 rewards/              # Reward management
├── 📁 transaction/          # Transaction processing
├── 📁 user/                 # User management
├── 📁 member/               # Member operations
├── 📁 location/             # Location/store data
├── 📁 margin/               # Profit margin calculations
├── 📁 ratio/                # Sales ratio analytics
├── 📁 review/               # Review system
└── 📁 middleware/           # API middleware functions
```

**📋 Key API Features:**
- **Customer Management**: Profile, points, transaction history
- **Dashboard Analytics**: Revenue, margins, sales data
- **Point System**: Reward redemption and point tracking
- **Transaction Processing**: Payment and order handling
- **Review System**: Customer feedback management
- **Location Services**: Store and branch data

#### 🎮 **JavaScript Frontend Architecture**

**`js/index/poin/handlers/` - Logic Handlers:**

Semua logic business dipecah ke handler terpisah untuk maintainability:

**📋 Contoh Response API:**
```json
// GET /api/poin/get_rewards.php
{
  "status": "success",
  "data": [
    {
      "id": 1,
      "name": "Voucher Makan 50K",
      "points": 5000,
      "stock": 10,
      "store": "ASK001",
      "nm_store": "Asoka Baby Store",
      "locations": ["member-area"]
    }
  ]
}
```

---

### 🔐 **`auth/` - Authentication System**

Sistem autentikasi berbasis JWT token untuk keamanan aplikasi dengan fitur lengkap.

```
auth/
├── login_pubs.php           # Login user email/password
├── login_pubs_phone.php     # Login dengan nomor phone
├── register_pubs.php        # Registrasi user baru
├── google_login_pubs.php    # Google OAuth login
├── middleware_login.php     # Middleware validasi token
├── generate_token.php       # Generate JWT token
├── verify_token.php         # Verifikasi JWT token
├── verify_token_pubs.php    # Public token verification
├── decode_token.php         # Decode JWT payload
├── rate_limiter.php         # Rate limiting untuk security
├── post_data_user.php       # User data operations
└── css/                     # Auth-related styling
```

**🔑 Authentication Features:**
- **Multiple Login Methods**: Email, phone, Google OAuth
- **JWT Token Management**: Generation, verification, decode
- **Rate Limiting**: Protection against brute force attacks
- **Middleware Protection**: Route-level authentication
- **Security Logging**: Track authentication attempts

**🔑 Flow Authentication:**
1. User login → `login_pubs.php` → Generate JWT token
2. Setiap request → `middleware_login.php` → Validasi token
3. Token expired → Redirect ke login

---

### 🧩 **`component/` - UI Components Backend**

Komponen UI yang digunakan di sisi backend (PHP templates) untuk modularitas tampilan.

```
component/
├── sidebar_report.php           # Sidebar untuk laporan
├── navigation_report.php        # Navigation laporan
├── bottom_navigation.php        # Bottom nav general
├── bottom_navigation_user.php   # Bottom nav untuk user
├── bottom_navigation_other.php  # Bottom nav untuk guest
├── footer.php                   # Footer template
├── floating_message.php         # Floating notification
├── error_page.php              # Error page template
├── error_token.php             # Token error page
└── menu_handler.php            # Menu logic handler
```

**🎨 Component Features:**
- **Responsive Navigation**: Multi-level navigation system
- **User-specific UI**: Different components per user type
- **Error Handling**: Dedicated error page components
- **Message System**: Floating notifications
- **Menu Management**: Dynamic menu generation

---

### ⚙️ **`config/` - Configuration**

File konfigurasi global untuk aplikasi.

```
config/
├── JWT/                     # JWT Library & Config
│   ├── JWT.php             # JWT Implementation
│   └── Key.php             # JWT Key Management
└── config.php              # Database & App Config
```

---

### 🎯 **`fitur/` - Business Logic Modules**

Setiap fitur aplikasi memiliki folder terpisah untuk modularitas dan clean architecture.

```
fitur/
├── 📁 account/              # Account management
├── 📁 banner/               # Banner/promotional content
├── 📁 direct-manager/       # Direct manager features
├── 📁 laporan/              # Reporting system
├── 📁 member/               # Member management
├── 📁 personal/             # Personal user features
├── 📁 pubs/                 # Public/guest features
└── 📁 transaction/          # Transaction processing
```

**🎯 Business Logic Features:**
- **Account Management**: User profiles, settings, preferences
- **Banner System**: Promotional content management
- **Reporting**: Analytics, sales reports, performance metrics
- **Member System**: Membership tiers, benefits, privileges
- **Transaction Processing**: Payment, orders, invoicing
- **Public Features**: Guest access, public information

---

### 💻 **`js/` - Frontend JavaScript**

**Ini adalah bagian terpenting** - Frontend JavaScript dengan arsitektur modern dan lengkap.

```
js/
├── 📁 index/                    # Main application areas
│   └── poin/                    # Point Reward System
│       ├── 📁 handlers/         # Event & Logic Handlers
│       ├── 📁 components/       # UI Components
│       ├── 📁 services/         # API Services
│       ├── 📁 utils/            # Helper Functions
│       ├── index.js             # Main Entry Point
│       ├── state.js             # State Management
│       └── dom.js               # DOM Elements
├── 📁 auth/                     # Authentication frontend
│   ├── auth_login.js            # Login functionality
│   ├── auth_register.js         # Registration
│   ├── auth_reset.js            # Password reset
│   └── auth_google.js           # Google OAuth
├── 📁 customer_pubs/            # Public customer features
│   ├── profile_pubs.js          # Public profile
│   └── reset_password.js        # Password reset
├── 📁 dashboard/                # Dashboard frontend
├── 📁 account/                  # Account management
├── 📁 member_internal/          # Internal member features
├── 📁 transaction/              # Transaction UI
├── 📁 transaction_branch/       # Branch transactions
├── 📁 location/                 # Location management
├── 📁 kode_cabang/              # Branch code management
├── 📁 margin/                   # Margin calculations
├── 📁 rewards/                  # Reward system
├── 📁 review/                   # Review system
├── 📁 ui/                       # UI utilities
├── 📁 validation_ui/            # Form validation
├── 📁 invalid_trans/            # Invalid transaction handling
├── config.js                    # Frontend configuration
├── login.js                     # Main login
├── logout.js                    # Logout functionality
├── transaction.js               # Transaction handling
├── profile_user.js              # User profile
├── promo.js                     # Promotional features
├── slider_hero.js               # Homepage slider
├── send_contact_us.js           # Contact form
├── struk.js                     # Receipt/invoice
├── storeCodeConvert.js          # Store code utilities
└── loadingbar.js                # Loading indicators
```

**💡 JavaScript Architecture Features:**
- **Modular Design**: ES6 modules dengan clear separation
- **Handler Pattern**: Logic separation untuk maintainability
- **State Management**: Centralized state dengan reactive updates
- **API Abstraction**: Clean service layer
- **Component System**: Reusable UI components
- **Validation System**: Frontend form validation
- **Authentication Flow**: Complete auth frontend
- **Transaction System**: Complex transaction handling

### � **`style/` - CSS Styling**

Styling system yang terorganisir dengan modular CSS approach.

```
style/
├── main.css                    # Main stylesheet
├── header.css                  # Header styling
├── sidebar.css                 # Sidebar navigation styles
├── input.css                   # Form input styles
├── animation-fade-in.css       # Animation effects
├── default-font.css            # Typography system
└── output.css                  # Compiled/processed CSS
```

**🎨 Styling Features:**
- **Modular CSS**: Separated styles per component
- **Animation System**: Fade-in effects dan transitions
- **Typography**: Consistent font system
- **Form Styling**: Comprehensive input styling
- **Responsive Design**: Mobile-first approach
- **Component-based**: Styles matched dengan PHP components

---

## 📋 **Additional Files & Configuration**

### **Root Level Files**
```
src/
├── output2.css              # Compiled Tailwind CSS output
├── ouput2.css               # Alternative CSS output (typo in filename)
└── README.md                # Project documentation
```

### **Log System Structure**
```
../logs/                     # Application logging (parent directory)
├── dashboard-2025-07-21.log       # Dashboard activity logs
├── insert_new_user-2025-07-21.log # User registration logs
├── permission_access-2025-07-21.log # Access control logs
└── top_margin-2025-07-21.log      # Margin calculation logs
```

### **Configuration Structure**
```
config/
├── JWT/                     # JWT Library & Configuration
│   ├── JWT.php             # JWT Implementation
│   ├── Key.php             # JWT Key Management
│   └── BeforeValidException.php # JWT Exception handling
└── config.php              # Main database & app configuration
```

---

## 🔄 **Extended Application Flows**

### **Customer Journey Flow**
```
1. Registration/Login → JWT Token Generation → Profile Setup
2. Browse Products → Add to Cart → Transaction Processing  
3. Earn Points → View Rewards → Redeem Points → QR Generation
4. Review Products → Rate Experience → Loyalty Program
```

### **Admin/Manager Flow** 
```
1. Dashboard Login → Analytics Overview → Sales Reports
2. Manage Products → Set Margins → Monitor Performance
3. Review Management → Customer Feedback → Business Insights
4. Branch Management → Location Settings → Store Configuration
```

### **Transaction Processing Flow**
```
1. Product Selection → Cart Management → Payment Processing
2. Point Calculation → Margin Analysis → Receipt Generation
3. Transaction Logging → Analytics Update → Customer Notification
```

---

#### 🎮 **JavaScript Frontend Architecture Details**

```
handlers/
├── filterHandler.js         # Filter logic (store, location)
├── rewardHandler.js         # Reward exchange logic
├── eventHandler.js          # Event delegation & listeners
└── renderHandler.js         # UI rendering logic
```

**💡 Contoh Handler:**
```javascript
// filterHandler.js
export const handleFilterClick = (button, renderCallback) => {
  const location = button.dataset.location;
  state.currentLocation = location;
  renderCallback("member-area");
};
```

#### 🎨 **`components/` - UI Components**

Komponen UI yang reusable:

```
components/
├── RewardCard.js            # Reward card component
├── Modals.js                # Modal dialogs
└── History.js               # History modal
```

#### 🔌 **`services/` - API Services**

Layer abstraksi untuk komunikasi dengan backend API:

```javascript
// services/api.js
export const fetchRewards = async () => {
  const response = await fetch('/src/api/poin/get_rewards.php');
  return await response.json();
};

export const exchangeReward = async (token, rewardId, store, plu, nm_store) => {
  // Logic penukaran poin
};
```

### 🛠️ **`utils/` - Helper Utilities**

Utility classes dan helper functions untuk backend operations.

```
utils/
├── DatabaseHelper.php          # Database operations helper
├── Logger.php                  # Application logging system
├── DataValidator.php           # Input validation utilities
├── FileHelper.php              # File operations helper
├── MenuSync.php                # Menu synchronization
└── init.php                    # Initialization utilities
```

**🔧 Utility Features:**
- **DatabaseHelper**: Prepared statements, query execution, transaction handling
- **Logger**: Monolog-based logging dengan rotation, multiple levels
- **DataValidator**: Input sanitization, validation rules, security filters
- **FileHelper**: File upload, image processing, file management
- **MenuSync**: Dynamic menu generation dan synchronization

**💡 DatabaseHelper Example:**
```php
// Auto-prepared statements dengan type detection
$helper->executePreparedStatement(
    "SELECT * FROM users WHERE id = ? AND status = ?", 
    [$userId, $status]
);

// Transaction handling
$helper->beginTransaction();
$helper->commitTransaction();
```

**📝 Logger Example:**
```php
// Multi-level logging dengan rotation
$logger->info('User login successful', ['user_id' => 123]);
$logger->error('Database connection failed', ['error' => $e->getMessage()]);
$logger->debug('API response', ['data' => $response]);
```

#### 🏛️ **State Management**

```javascript
// state.js
export const state = {
  userPoints: 0,
  rewards: [],
  currentLocation: 'member-area',
  pendingReward: null
};
```

---

## 🔄 **Flow Aplikasi Point Reward**

### 1. **Inisialisasi**
```javascript
// index.js
const init = async () => {
  setupEventListeners(renderRewards);
  await loadInitialData();
  setupStoreFilter(filterRewardsByStore, renderRewards);
};
```

### 2. **Load Data**
```javascript
const loadInitialData = async () => {
  const [rewardsData, pointsData] = await Promise.all([
    fetchRewards(),        // GET /api/poin/get_rewards.php
    fetchUserPoints(),     // GET /api/poin/get_user_points.php
  ]);
  
  setRewards(processedRewards);
  updateUserPoints(pointsData);
  renderRewards();
};
```

### 3. **Filter & Render**
```javascript
// Filter berdasarkan store/location
const filteredRewards = rewards.filter(reward => 
  reward.store === selectedStoreId &&
  reward.locations.includes(currentLocation)
);

// Render ke DOM
renderRewards(location, filteredRewards);
```

### 4. **Exchange Process**
```javascript
// 1. Validasi poin & stock
if (userPoints < reward.points) {
  showErrorModal("Poin tidak cukup");
  return;
}

// 2. Konfirmasi user
showConfirmModal(reward);

// 3. Process exchange
const result = await exchangeReward(token, rewardId, store, plu, nm_store);

// 4. Update UI
updateUserPoints(newPoints);
showSuccessModal(result.code, result.qr);
```

---

## 🗃️ **Database Schema (Estimasi)**

```sql
-- Users table
users (id, name, email, points, store_id, created_at)

-- Rewards table  
rewards (id, name, description, points, stock, store_id, plu, locations, created_at)

-- Exchange history
exchanges (id, user_id, reward_id, code, points_used, expired_at, created_at)

-- Stores
stores (id, name, location, active)
```

---

## 🚀 **Cara Setup Development**

### 1. **Requirements**
- PHP 7.4+
- MySQL/MariaDB
- Apache/Nginx
- Modern Browser (ES6 support)

### 2. **Installation**
```bash
# Clone project
git clone [repository-url]

# Setup database
mysql -u root -p < database.sql

# Configure
cp config.example.php config.php
# Edit database credentials

# Run server
php -S localhost:8000
```

### 3. **Development Workflow**
```bash
# Frontend changes
cd src/js/index/poin/
# Edit handlers, components, services

# Backend changes  
cd src/api/poin/
# Edit PHP endpoints

# Test
curl http://localhost:8000/src/api/poin/get_rewards.php
```

---

## 🧪 **Testing**

### **Frontend Testing**
```javascript
// Test filter functionality
const mockRewards = [
  { id: 1, store: "ASK001", locations: ["member-area"] }
];

const filtered = applyRewardFilters(mockRewards, "member-area");
console.assert(filtered.length === 1);
```

### **API Testing**
```bash
# Test get rewards
curl -X GET http://localhost:8000/src/api/poin/get_rewards.php

# Test exchange (with token)
curl -X POST http://localhost:8000/src/api/poin/redeem_reward.php \
  -H "Authorization: Bearer [token]" \
  -d "reward_id=1&store=ASK001"
```

---

## 🔧 **Common Issues & Solutions**

### **Filter Tidak Bekerja**
```javascript
// Pastikan store ID match dengan reward.store
console.log("Selected store:", selectedStore);
console.log("Reward store:", reward.store);

// Debug filter logic
const filteredRewards = rewards.filter(reward => {
  console.log(`Comparing: ${reward.store} === ${selectedStore}`);
  return reward.store === selectedStore;
});
```

### **Token Expired**
```php
// middleware_login.php
if (isTokenExpired($token)) {
    http_response_code(401);
    echo json_encode(['error' => 'Token expired']);
    exit;
}
```

### **CORS Issues**
```php
// Add to API endpoints
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Authorization, Content-Type');
```

---

## 📈 **Performance Tips**

1. **Frontend Optimization**
   - Use event delegation instead of individual listeners
   - Debounce filter inputs
   - Lazy load images in reward cards

2. **Backend Optimization**
   - Use prepared statements
   - Cache frequently accessed data
   - Optimize database queries

3. **API Optimization**
   - Implement pagination for large datasets
   - Use HTTP caching headers
   - Compress JSON responses

---

## 🛡️ **Security Best Practices**

1. **Input Validation**
   ```php
   // Sanitize input
   $rewardId = filter_var($_POST['reward_id'], FILTER_VALIDATE_INT);
   $storeName = preg_replace('/[^a-zA-Z0-9 ]/', '', $_POST['store']);
   ```

2. **SQL Injection Prevention**
   ```php
   $stmt = $pdo->prepare("SELECT * FROM rewards WHERE id = ? AND store = ?");
   $stmt->execute([$rewardId, $storeId]);
   ```

3. **XSS Prevention**
   ```javascript
   // Escape output
   const safeHTML = htmlspecialchars(userInput);
   ```

---

## 👥 **Contributing Guidelines**

1. **Code Style**
   - Use ESLint for JavaScript
   - Follow PSR-12 for PHP
   - Use meaningful variable names

2. **Git Workflow**
   ```bash
   git checkout -b feature/reward-filtering
   git commit -m "feat: add store-based reward filtering"
   git push origin feature/reward-filtering
   ```

3. **Documentation**
   - Update README saat menambah fitur
   - Comment kode yang kompleks
   - Buat JSDoc untuk functions

---

## 📞 **Contact & Support**

- **Developer**: [Nurman Syah]
- **Project**: Asoka Internal And External Website
- **Repository**: [https://github.com/ProgramNS/asoka-id.git]
- **Documentation**: Updated August 2025

---

*Dokumentasi ini dibuat untuk memudahkan web developer fullstack memahami struktur dan flow aplikasi. Silakan update sesuai perkembangan project.*
