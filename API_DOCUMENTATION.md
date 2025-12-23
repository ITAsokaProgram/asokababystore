# 🔌 API Documentation - Asoka ID Point Reward System

> **Dokumentasi API Endpoints untuk Developer**  
> Panduan lengkap untuk menggunakan REST API endpoints

---

## 📋 **API Overview**

Base URL: `http://your-domain.com/src/api/`

**Authentication**: Bearer Token (JWT)  
**Content-Type**: `application/json` atau `application/x-www-form-urlencoded`  
**Response Format**: JSON

---

## 🔐 **Authentication Endpoints**

### **POST** `/auth/login_pubs.php`
Login user dan dapatkan JWT token.

**Request Body:**
```json
{
  "email": "user@example.com",
  "password": "password123"
}
```

**Response Success (200):**
```json
{
  "status": "success",
  "message": "Login berhasil",
  "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "user@example.com",
    "points": 15000,
    "store_id": "ASK001"
  }
}
```

**Response Error (401):**
```json
{
  "status": "error",
  "message": "Email atau password salah"
}
```

---

### **POST** `/auth/register_pubs.php`
Registrasi user baru.

**Request Body:**
```json
{
  "name": "John Doe",
  "email": "user@example.com", 
  "password": "password123",
  "store_id": "ASK001"
}
```

**Response Success (201):**
```json
{
  "status": "success",
  "message": "Registrasi berhasil",
  "user_id": 123
}
```

### **POST** `/auth/login_pubs_phone.php`
Login dengan nomor telefon.

**Request Body:**
```json
{
  "phone": "081234567890",
  "password": "password123"
}
```

### **POST** `/auth/google_login_pubs.php`
Google OAuth login.

**Request Body:**
```json
{
  "google_token": "google-oauth-token",
  "email": "user@gmail.com",
  "name": "John Doe"
}
```

### **POST** `/auth/verify_token.php`
Verifikasi JWT token.

**Request Body:**
```json
{
  "token": "jwt-token-here"
}
```

**Response Success (200):**
```json
{
  "status": "success",
  "valid": true,
  "user_id": 1,
  "expires_at": "2025-08-10 12:00:00"
}
```

### **POST** `/forget_pass/[endpoint]`
Password reset endpoints.

---

## 💰 **Point System Endpoints**

### **GET** `/poin/get_user_points.php`
Ambil poin user saat ini.

**Headers:**
```
Authorization: Bearer [jwt-token]
```

**Response Success (200):**
```json
{
  "status": "success",
  "points": 15000,
  "user_id": 1
}
```

---

### **GET** `/poin/get_rewards.php`
Daftar semua reward yang tersedia.

**Query Parameters:**
- `store_id` (optional): Filter berdasarkan store
- `location` (optional): Filter berdasarkan lokasi

**Example Request:**
```
GET /poin/get_rewards.php?store_id=ASK001&location=member-area
```

**Response Success (200):**
```json
{
  "status": "success",
  "data": [
    {
      "id": 1,
      "name": "Voucher Makan 50K",
      "description": "Voucher makan senilai 50 ribu rupiah",
      "points": 5000,
      "stock": 10,
      "store": "ASK001",
      "nm_store": "Asoka Baby Store Jakarta",
      "plu": "VCH50K",
      "locations": ["member-area", "office-area"],
      "image": "/images/voucher-50k.jpg",
      "expired_days": 30,
      "created_at": "2025-08-01 10:00:00"
    },
    {
      "id": 2,
      "name": "Diskon 20% Produk Baby",
      "description": "Diskon 20% untuk semua produk baby",
      "points": 3000,
      "stock": 5,
      "store": "ASK001", 
      "nm_store": "Asoka Baby Store Jakarta",
      "plu": "DSK20",
      "locations": ["member-area"],
      "image": "/images/diskon-baby.jpg",
      "expired_days": 14,
      "created_at": "2025-08-01 11:00:00"
    }
  ]
}
```

---

### **POST** `/poin/redeem_reward.php`
Tukar poin dengan reward.

**Headers:**
```
Authorization: Bearer [jwt-token]
Content-Type: application/json
```

**Request Body:**
```json
{
  "reward_id": 1,
  "store": "ASK001",
  "plu": "VCH50K",
  "nm_store": "Asoka Baby Store Jakarta"
}
```

**Response Success (200):**
```json
{
  "status": "success",
  "message": "Penukaran poin berhasil",
  "code": "RWD-ABC123-DEF",
  "qr": "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAA...",
  "expires_at": "2025-09-01 23:59:59",
  "reward": {
    "id": 1,
    "name": "Voucher Makan 50K",
    "points_used": 5000
  },
  "user_remaining_points": 10000
}
```

**Response Error (400):**
```json
{
  "status": "error",
  "message": "Poin tidak cukup",
  "required_points": 5000,
  "user_points": 3000
}
```

**Response Error (404):**
```json
{
  "status": "error", 
  "message": "Reward tidak ditemukan atau stok habis"
}
```

---

## 👤 **User Endpoints**

### **GET** `/user/profile.php`
Ambil profile user.

**Headers:**
```
Authorization: Bearer [jwt-token]
```

**Response Success (200):**
```json
{
  "status": "success",
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "user@example.com",
    "points": 15000,
    "store_id": "ASK001",
    "store_name": "Asoka Baby Store Jakarta",
    "member_since": "2025-01-15",
    "total_exchanges": 5,
    "total_points_earned": 50000,
    "total_points_used": 35000
  }
}
```

---

### **GET** `/user/history.php`
History penukaran poin user.

**Headers:**
```
Authorization: Bearer [jwt-token]
```

**Query Parameters:**
- `limit` (optional): Jumlah data per halaman (default: 10)
- `offset` (optional): Offset untuk pagination (default: 0)
- `date_from` (optional): Filter dari tanggal (YYYY-MM-DD)
- `date_to` (optional): Filter sampai tanggal (YYYY-MM-DD)

**Example Request:**
```
GET /user/history.php?limit=5&offset=0&date_from=2025-08-01
```

**Response Success (200):**
```json
{
  "status": "success",
  "data": [
    {
      "id": 1,
      "reward_name": "Voucher Makan 50K",
      "points_used": 5000,
      "code": "RWD-ABC123-DEF",
      "status": "active",
      "created_at": "2025-08-01 14:30:00",
      "expires_at": "2025-09-01 23:59:59",
      "used_at": null
    },
    {
      "id": 2,
      "reward_name": "Diskon 20% Produk Baby", 
      "points_used": 3000,
      "code": "RWD-XYZ456-GHI",
      "status": "used",
      "created_at": "2025-07-28 10:15:00",
      "expires_at": "2025-08-12 23:59:59",
      "used_at": "2025-07-30 16:45:00"
    }
  ],
  "pagination": {
    "total": 25,
    "limit": 5,
    "offset": 0,
    "has_more": true
  }
}
```

---

## 📱 **QR Code Endpoints**

### **POST** `/qr/generate.php`
Generate QR code untuk reward.

**Headers:**
```
Authorization: Bearer [jwt-token]
Content-Type: application/json
```

**Request Body:**
```json
{
  "code": "RWD-ABC123-DEF",
  "size": 200
}
```

**Response Success (200):**
```json
{
  "status": "success",
  "qr_code": "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAA...",
  "code": "RWD-ABC123-DEF",
  "size": 200
}
```

---

### **POST** `/qr/verify.php`
Verifikasi dan gunakan QR code reward.

**Headers:**
```
Authorization: Bearer [jwt-token]
Content-Type: application/json
```

**Request Body:**
```json
{
  "code": "RWD-ABC123-DEF",
  "store_id": "ASK001"
}
```

**Response Success (200):**
```json
{
  "status": "success",
  "message": "Reward berhasil digunakan",
  "reward": {
    "name": "Voucher Makan 50K",
    "value": 50000,
    "used_at": "2025-08-05 14:30:00"
  }
}
```

**Response Error (400):**
```json
{
  "status": "error",
  "message": "Kode sudah digunakan atau expired"
}
```

---

## 🏪 **Store Endpoints**

### **GET** `/store/list.php`
Daftar semua store.

**Response Success (200):**
```json
{
  "status": "success",
  "data": [
    {
      "id": "ASK001",
      "name": "Asoka Baby Store Jakarta",
      "address": "Jl. Sudirman No. 123, Jakarta",
      "phone": "021-12345678",
      "active": true
    },
    {
      "id": "ASK002", 
      "name": "Asoka Baby Store Bandung",
      "address": "Jl. Braga No. 456, Bandung",
      "phone": "022-87654321", 
      "active": true
    }
  ]
}
```

---

## 💳 **Transaction Endpoints**

### **GET** `/transaction/get_all_transaction.php`
Ambil semua transaksi.

**Headers:**
```
Authorization: Bearer [jwt-token]
```

**Query Parameters:**
- `limit` (optional): Jumlah data per halaman
- `offset` (optional): Offset untuk pagination
- `date_from` (optional): Filter dari tanggal (YYYY-MM-DD)
- `date_to` (optional): Filter sampai tanggal (YYYY-MM-DD)

**Response Success (200):**
```json
{
  "status": "success",
  "data": [
    {
      "id": 1,
      "transaction_code": "TRX-001",
      "customer_name": "John Doe",
      "total_amount": 250000,
      "points_earned": 250,
      "store_id": "ASK001",
      "created_at": "2025-08-01 14:30:00"
    }
  ],
  "pagination": {
    "total": 100,
    "limit": 10,
    "offset": 0
  }
}
```

### **GET** `/transaction/get_transaction_branch.php`
Transaksi per branch/cabang.

### **GET** `/transaction/get_transaction_member.php`
Transaksi khusus member.

### **GET** `/transaction/get_transaction_non_member.php`
Transaksi non-member.

---

## 📊 **Dashboard Analytics Endpoints**

### **GET** `/dashboard/get_data_dashboard.php`
Data overview dashboard.

**Headers:**
```
Authorization: Bearer [jwt-token]
```

**Response Success (200):**
```json
{
  "status": "success",
  "data": {
    "total_revenue": 15000000,
    "total_transactions": 1250,
    "total_members": 850,
    "total_points_redeemed": 125000,
    "top_products": [
      {
        "name": "Product A",
        "sales": 150,
        "revenue": 1500000
      }
    ]
  }
}
```

### **GET** `/dashboard/get_pendapatan.php`
Data pendapatan/revenue.

### **GET** `/dashboard/top_margin.php`
Produk dengan margin tertinggi.

---

## 👥 **Member Management Endpoints**

### **GET** `/member/member_active.php`
Daftar member aktif.

### **GET** `/member/member_non_active.php`
Daftar member non-aktif.

### **GET** `/member/member_poin_active.php`
Member dengan poin aktif.

### **GET** `/member/search_member.php`
Pencarian member.

**Query Parameters:**
- `q`: Keyword pencarian (nama, email, phone)
- `store_id` (optional): Filter berdasarkan store

**Response Success (200):**
```json
{
  "status": "success",
  "data": [
    {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "phone": "081234567890",
      "points": 15000,
      "store_id": "ASK001"
    }
  ]
}
```

---

## 🎁 **Reward Management Endpoints**

### **GET** `/rewards/get_rewards.php`
Semua rewards (admin view).

### **GET** `/rewards/get_reward_by_id.php`
Detail reward berdasarkan ID.

### **POST** `/rewards/insert_give.php`
Berikan reward manual ke user.

### **PUT** `/rewards/update_reward.php`
Update data reward.

### **DELETE** `/rewards/delete_reward.php`
Hapus reward.

---

## 🛍️ **Customer Service Endpoints**

### **GET** `/customer/get_profile_customer.php`
Profile customer detail.

### **GET** `/customer/get_activity_customer.php`
Aktivitas customer.

### **GET** `/customer/get_struk_belanja_customer.php`
Struk belanja customer.

### **GET** `/customer/history_transaction.php`
History transaksi customer.

### **POST** `/customer/update_customer.php`
Update data customer.

---

## 🏢 **Branch/Store Management**

### **GET** `/cabang/[endpoint]`
Management cabang/branch operations.

### **GET** `/location/[endpoint]`
Location management endpoints.

---

## 📝 **Additional Endpoints**

### **GET** `/get_count_all.php`
Get count semua data (dashboard summary).

### **GET** `/get_report_transaction.php`
Laporan transaksi.

### **POST** `/google_login.php`
Google OAuth login.

### **POST** `/set_session.php`
Set session data.

### **POST** `/post_from_message.php`
Handle message/notification posting.

---

## ⚠️ **Error Codes**

| HTTP Code | Status | Description |
|-----------|--------|-------------|
| 200 | success | Request berhasil |
| 201 | success | Data berhasil dibuat |
| 400 | error | Bad request / validasi gagal |
| 401 | error | Unauthorized / token invalid |
| 403 | error | Forbidden / akses ditolak |
| 404 | error | Data tidak ditemukan |
| 500 | error | Internal server error |

---

## 🧪 **Testing Examples**

### **cURL Examples**

**1. Login:**
```bash
curl -X POST http://localhost:8000/src/api/auth/login_pubs.php \
  -H "Content-Type: application/json" \
  -d '{"email":"user@example.com","password":"password123"}'
```

**2. Get Rewards:**
```bash
curl -X GET http://localhost:8000/src/api/poin/get_rewards.php \
  -H "Authorization: Bearer [your-token]"
```

**3. Redeem Reward:**
```bash
curl -X POST http://localhost:8000/src/api/poin/redeem_reward.php \
  -H "Authorization: Bearer [your-token]" \
  -H "Content-Type: application/json" \
  -d '{"reward_id":1,"store":"ASK001","plu":"VCH50K","nm_store":"Asoka Baby Store"}'
```

### **JavaScript Examples**

**1. Login Function:**
```javascript
const login = async (email, password) => {
  const response = await fetch('/src/api/auth/login_pubs.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({ email, password })
  });
  
  return await response.json();
};
```

**2. Get Rewards with Token:**
```javascript
const fetchRewards = async (token) => {
  const response = await fetch('/src/api/poin/get_rewards.php', {
    headers: {
      'Authorization': `Bearer ${token}`
    }
  });
  
  return await response.json();
};
```

**3. Redeem Reward:**
```javascript
const exchangeReward = async (token, rewardData) => {
  const response = await fetch('/src/api/poin/redeem_reward.php', {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json'
    },
    body: JSON.stringify(rewardData)
  });
  
  return await response.json();
};
```

---

## 🔒 **Rate Limiting**

API memiliki rate limiting untuk mencegah abuse:

- **Login**: 5 attempts per 15 menit per IP
- **Redeem**: 10 requests per menit per user  
- **Get Rewards**: 100 requests per menit per IP
- **Other endpoints**: 50 requests per menit per user

**Rate Limit Headers:**
```
X-RateLimit-Limit: 100
X-RateLimit-Remaining: 95
X-RateLimit-Reset: 1628097600
```

---

## 📝 **Changelog**

### **v1.3.0** (August 2025) - Current
- Added comprehensive transaction endpoints
- Added member management APIs
- Added dashboard analytics endpoints
- Added branch/store management APIs
- Added customer service endpoints
- Improved authentication with phone & Google login
- Added reward management for admin
- Enhanced error handling & logging

### **v1.2.0** (July 2025)
- Added store filtering in get rewards endpoint
- Improved error messages
- Added QR code verification endpoint

### **v1.1.0** (June 2025)  
- Added pagination to history endpoint
- Added rate limiting
- Improved authentication security

### **v1.0.0** (May 2025)
- Initial API release
- Basic authentication
- Point system endpoints
- Reward management

---

## 📋 **Complete API Endpoint Summary**

### **Authentication (7 endpoints)**
- `POST /auth/login_pubs.php` - Email login
- `POST /auth/login_pubs_phone.php` - Phone login  
- `POST /auth/register_pubs.php` - User registration
- `POST /auth/google_login_pubs.php` - Google OAuth
- `POST /auth/verify_token.php` - Token verification
- `POST /auth/verify_token_pubs.php` - Public token verify
- `POST /forget_pass/*` - Password reset

### **Point System (3 endpoints)**
- `GET /poin/get_user_points.php` - User points
- `GET /poin/get_rewards.php` - Available rewards
- `POST /poin/redeem_reward.php` - Redeem points

### **Transaction (4 endpoints)**
- `GET /transaction/get_all_transaction.php` - All transactions
- `GET /transaction/get_transaction_branch.php` - Branch transactions
- `GET /transaction/get_transaction_member.php` - Member transactions
- `GET /transaction/get_transaction_non_member.php` - Non-member transactions

### **Dashboard Analytics (4 endpoints)**
- `GET /dashboard/get_data_dashboard.php` - Dashboard overview
- `GET /dashboard/get_data_transaction.php` - Transaction data
- `GET /dashboard/get_pendapatan.php` - Revenue data
- `GET /dashboard/top_margin.php` - Top margin products

### **Member Management (8 endpoints)**
- `GET /member/member_active.php` - Active members
- `GET /member/member_non_active.php` - Inactive members
- `GET /member/member_poin_active.php` - Members with points
- `GET /member/member_poin_non_active.php` - Members without points
- `GET /member/search_member.php` - Search members
- `GET /member/member_poin_detail.php` - Point details
- `GET /member/member_poin_fetch.php` - Fetch points
- `GET /member/member_poin_pubs.php` - Public points view

### **Customer Service (14 endpoints)**
- `GET /customer/get_profile_customer.php` - Customer profile
- `GET /customer/get_activity_customer.php` - Customer activity
- `GET /customer/get_poin_customer.php` - Customer points
- `GET /customer/get_status_customer.php` - Customer status
- `GET /customer/get_struk_belanja_customer.php` - Shopping receipt
- `GET /customer/get_top_5_activity_cust.php` - Top activities
- `GET /customer/history_transaction.php` - Transaction history
- `POST /customer/update_customer.php` - Update customer
- `GET /customer/laporan_layanan.php` - Service reports
- `GET /customer/review_laporan_in.php` - Review reports
- And more...

### **Reward Management (6 endpoints)**
- `GET /rewards/get_rewards.php` - All rewards (admin)
- `GET /rewards/get_reward_by_id.php` - Reward details
- `POST /rewards/insert_give.php` - Give reward manually
- `PUT /rewards/update_reward.php` - Update reward
- `DELETE /rewards/delete_reward.php` - Delete reward
- `GET /rewards/get_count.php` - Reward counts

### **QR Code (2 endpoints)**
- `POST /qr/generate.php` - Generate QR code
- `POST /qr/verify.php` - Verify QR code

### **Utility Endpoints (5+ endpoints)**
- `GET /get_count_all.php` - Count all data
- `GET /get_report_transaction.php` - Transaction reports
- `POST /google_login.php` - Google login
- `POST /set_session.php` - Session management
- `POST /post_from_message.php` - Message handling

**Total: 50+ API Endpoints**

---

*API Documentation ini akan terus diupdate sesuai perkembangan fitur.*
