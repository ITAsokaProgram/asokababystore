# Alur Code Program Per Folder

Dokumen ini adalah versi terstruktur dari dokumentasi repository untuk programmer IT. Dokumen mengikuti permintaan Anda: memberikan penjelasan per folder dan per file penting, kecuali folder yang dikecualikan (Backup, Controller, css, flowers, node_modules, vendor, View).

Catatan penting (rule of thumb):
- Semua perubahan kode dan upload harus diuji di environment development terlebih dahulu.
- Pastikan permission folder `public/` dan `logs/` dimiliki oleh user webserver (mis. `www-data`/`apache`) saat deploy.

-------------------------
1) Ringkasan singkat per folder (exclude: Backup, Controller, css, flowers, node_modules, vendor, View)

- `customer/`
  - Isi: tampilan (views) untuk pelanggan / user frontend (home, history transaksi, produk, promo, poin, qris).
  - Tujuan: semua page yang menjadi interface customer.

- `public/`
  - Isi: aset publik (gambar), file JSON seperti `slider.json`.
  - Catatan deploy: ubah kepemilikan file/direktori ke user Apache/Nginx agar web server dapat membaca dan menulis jika perlu. Contoh: `chown -R www-data:www-data public`.

- `src/` (kode sumber utama untuk aplikasi internal dan endpoint)
  - `src/api/` — semua API endpoint (internal + eksternal). Struktur: `src/api/<module>/*.php`.
  - `src/auth/` — authentication logic dan middleware (JWT handling, session, verify_token).
  - `src/component/` — komponen/potongan PHP yang di-include di banyak halaman (contoh: `menu_handler.php`, navigation, sidebar).
  - `src/config/` — konfigurasi aplikasi, termasuk libs yang di-bundle (mis. JWT lib yang diadaptasi untuk PHP7.3).
  - `src/fitur/` — halaman/himpunan fitur untuk admin/internal (contoh: `fitur/banner/view_banner.php`, `fitur/aset/*`).
  - `src/js/` — JS modular untuk fitur (fetch, handler, UI helpers).
  - `src/log/` — helper dan job yang membuat entri log (cron jobs, export, dsb.).
  - `src/style/` — css custom tambahan.
  - `src/utils/` — helper utilities (logger, response helper, dsb.).

- `logs/`
  - Isi: file log runtime (akses, permission, proses background).
  - Catatan: folder ini wajib dibuat dan permission di-set ke user Apache agar aplikasi dapat menulis log.

- `uploaded_img/`
  - Isi: file hasil upload (sementara / permanen) yang mungkin dipindahkan atau di-proses.

- `update_aplikasi/`
  - Script update/pembaruan aplikasi.

- `js/` (top-level)
  - Isi: hasil build/js assets (contoh: `app.*.js`, `chunk-vendors.*.js`).

-------------------------
2) Penjelasan fokus: `src/api` dan `src/fitur` (alur request -> backend -> penyimpanan)

- Alur umum request UI -> PHP API:
  1. Halaman server-rendered (`src/fitur/...` atau root PHP) memberikan HTML + JS yang memanggil API.
 2. JS (dari `src/js/` atau inline di halaman) membuat request `fetch()` ke `src/api/<module>/<action>.php`.
 3. Endpoint `src/api/...` menerima request, memanggil `verify_token()` bila perlu, melakukan validasi input, menjalankan prepared statement ke DB, dan mengembalikan JSON.
 4. Untuk upload file, endpoint menggunakan `$_FILES` dan array fields `promoImage[]`, `promoName[]`, `promoDateStart[]`, `promoDateEnd[]` — lalu menyimpan file di `public/images/...` atau `uploaded_img/` dan memperbarui `public/slider.json` bila perlu.

-------------------------
3) Rincian folder `src/api` dan file penting

- `src/api/image/` — upload & management banner
  - `put_image.php` — menerima FormData multiple upload; fields yang diharapkan: `promoImage[]`, `promoName[]`, `promoDateStart[]`, `promoDateEnd[]`, `mainPromoName`, `mainPromoDate`, `mainPromoDateEnd`.
    - Tugas: simpan file, jangan lupa validasi tipe & ukuran, buat entry JSON untuk `public/slider.json` (fields: `path`, `filename`, `promo_name`, `tanggal_mulai`, `tanggal_selesai`). Pastikan menulis file JSON dengan kunci (lock) agar tidak korup saat concurrent writes.
  - `delete_image.php` — menghapus banner (file di disk + entry di `slider.json`). Jika menggunakan Cloudinary, panggil destroy API berdasarkan `public_id` jika tersedia.
  - `clean_expired_banner.php` — scan `slider.json` dan hapus entry yang tanggal_selesai < today; hapus file fisik.
  - `clean_orphaned_files.php` — scan folder `public/images/promo` (atau folder upload) dan hapus file yang tidak ada di `slider.json`.

- `src/api/aset/` — asset management
  - File-file (yang ada di folder):
    - `insert_aset.php` — logic tambah data aset; dipanggil oleh JS di `src/js/aset/services/api.js`.
    - `delete_aset.php` — hapus aset.
    - `edit_aset.php` — update aset.
    - `get_data_aset.php` — ambil data aset (untuk render tabel, pagination, filter).
    - `get_group_suggestions.php` — endpoint autocomplete/suggestions untuk field group_aset.
    - `get_history_log.php` — ambil log history perubahan aset (baca `log_history_aset` atau tabel audit yang relevan).
  - Catatan: endpoints ini mengembalikan JSON berformat { success: boolean, data: ..., error: ... }.

- `src/api/rewards/` — reward management (CRUD, delete with cloudinary cleanup, log to `log_hadiah`).

- `src/api/auth/` — auth endpoints (login, refresh token, verify). Biasanya berisi `verify_token()` dan helper JWT.

-------------------------
4) Rincian `src/fitur` — halaman dan interaksi

- `src/fitur/banner/view_banner.php`:
  - Halaman manajemen banner.
  - JS inline: drag & drop multi-file upload, preview cards (per-file inputs promoName[], promoDateStart[], promoDateEnd[]), submit FormData ke `src/api/image/put_image.php`.
  - Gallery: baca `public/slider.json` untuk menampilkan banner yang ada.

- `src/fitur/aset/history_aset.php`:
  - Halaman menampilkan data aset beserta tombol "History" yang memanggil `src/api/aset/get_history_log.php` untuk menampilkan audit log modal.

-------------------------
5) Rincian `src/js` — client side modules

- Struktur umum: `src/js/<module>/*.js` atau `src/js/<feature>.js`.
- File penting:
  - `middleware_auth.js` — mengambil token dari cookie dan membantu request authorized.
  - `ui/navbar_toogle.js` — UI helpers.
  - `aset/handler/dataTableHandler.js` — render tabel aset, handle edit/delete/history button wiring.
  - `aset/services/api.js` — fungsi fetch ke `src/api/aset/*.php`.

-------------------------
6) Logging & cron jobs

- `src/log/` dan `logs/`:
  - `src/log/` berisi script helper yang dipanggil di cron atau background job.
  - `logs/` adalah hasil output runtime (aplikasi menulis file log). Pastikan `logs/` writable oleh webserver.

-------------------------
7) Konfigurasi & dependencies

- `config.env` / `config.php` — simpan konfigurasi DB, Cloudinary credentials, base path.
- `asoka-*.json` — credential (service account) yang harus aman.
- `package.json` / `tailwind.config.js` — pengaturan build frontend.

-------------------------
8) Praktik terbaik & checklist deployment

- Permission:
  - `chown -R www-data:www-data public uploaded_img logs` (sesuaikan user webserver)
- Backup:
  - Backup `public/slider.json` dan database sebelum menjalankan script cleanup.
- Security:
  - Jangan commit credentials; rotasi jika ada leaked keys.

-------------------------
9) Contoh mapping `src/api/aset` -> `src/js/aset`

- `insert_aset.php` <-> `src/js/aset/services/api.js` : fungsi `createAset(formData)`
- `edit_aset.php` <-> `src/js/aset/services/api.js` : fungsi `updateAset(id, payload)`
- `delete_aset.php` <-> `src/js/aset/services/api.js` : fungsi `deleteAset(id)`
- `get_data_aset.php` <-> `src/js/aset/handler/dataTableHandler.js` : rendering tabel dan pagination
- `get_group_suggestions.php` <-> `src/js/aset/handler/selectHandler.js` : suggestion/autocomplete for group
- `get_history_log.php` <-> `src/js/aset/handler/dataTableHandler.js` : fetch and display modal history

-------------------------
10) Bila Anda mau dokumentasi per-file (lebih detil)

- Saya bisa memperluas dokumen ini menjadi per-file detail (fungsi utama, inputs/outputs, contoh request/response). Beri daftar folder yang ingin Anda per-detail-kan dan saya akan generate secara iteratif.

---
Dokumentasi ini menggantikan versi ringkas sebelumnya dan sudah disimpan di `REPO_CODE_DOCUMENTATION.md`.
Jika ada file/folder yang belum tercantum di listing Anda, beri tahu dan saya tambahkan ke dokumen.
