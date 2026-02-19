# 🌹 RoseMarry — Sistem Manajemen F&B Terintegrasi

> Sistem manajemen F&B modern dengan **Laravel + Livewire** untuk mengelola produksi, stok, kasir, dan absensi dengan integrasi real-time.

---

## 🏗️ Arsitektur Teknologi

| Layer | Teknologi |
|---|---|
| Backend | Laravel 12 + Livewire 3.x |
| Frontend | DaisyUI + TailwindCSS 4.0 |
| Database | MySQL |
| Real-time | Livewire Components |
| Export | Laravel Excel (StocksExport) |
| Permissions | Spatie Laravel Permission |

---

## 👥 Role & Access Control

| Role | Deskripsi |
|---|---|
| **Admin** | Akses penuh ke semua fitur sistem |
| **Production** | Input produk, stok keluar, recipe management |
| **Purchase / Gudang** | Manajemen bahan, supplier, pembelian |
| **Kasir** | POS interface, transaksi penjualan |

---

## ✅ Status Implementasi (Scan: 2026-02-18)

### 🔐 Authentication & Authorization

| Fitur | Status | Keterangan |
|---|---|---|
| Login (Livewire) | ✅ Selesai | `app/Livewire/Auth/Login.php` |
| Logout (Livewire) | ✅ Selesai | `app/Livewire/Auth/Logout.php` |
| Route Guard (middleware auth/guest) | ✅ Selesai | `routes/auth.php` |
| Role Management | ✅ Selesai | `app/Livewire/Admin/Roles/` (Index, Create, Edit, Delete) |
| Permission Management | ✅ Selesai | `app/Livewire/Admin/Permissions/` (Index, Create, Edit, Delete) |
| Category Permissions | ✅ Selesai | `app/Livewire/Admin/CategoryPermissions/` (Index, Create, Edit, Delete) |
| Spatie Permission Tables | ✅ Selesai | Migration `2026_02_09_015537_create_permission_tables.php` |

---

### 👤 Manajemen User

| Fitur | Status | Keterangan |
|---|---|---|
| CRUD User | ✅ Selesai | `app/Livewire/Admin/Users/` (Index, Create, Edit, Delete) |
| Model User (role, kode_unik, dll) | ✅ Selesai | `app/Models/User.php` |
| Shift Management (model & migration) | ✅ Selesai | `app/Models/Shift.php` |
| Shift Management (UI/Route) | ⏳ Belum | Route dikomentari, view belum tersedia |
| Absensi (check-in/out + selfie) | ❌ Missing | Belum ada model, Livewire, maupun view |

---

### 📦 Master Data

| Fitur | Status | Keterangan |
|---|---|---|
| Categories (model + migration) | ✅ Selesai | `app/Models/Categories.php` |
| Units (model + migration) | ✅ Selesai | `app/Models/Unit.php` |
| Suppliers (model + migration) | ✅ Selesai | `app/Models/Suppliers.php` |
| Customers (model + migration) | ✅ Selesai | `app/Models/Customers.php` |
| Divisions (model + migration) | ✅ Selesai | `app/Models/Divisions.php` |
| Classes (model + migration) | ✅ Selesai | `app/Models/Classes.php` |
| Master Data UI (Categories/Units/Suppliers/dll) | ⏳ Belum | Route dikomentari — view folder `master` ada tapi kosong |

---

### 🧪 Inventory / Stok Bahan

| Fitur | Status | Keterangan |
|---|---|---|
| Materials (model + migration) | ✅ Selesai | `app/Models/Materials.php` |
| Material Stocks (model + migration) | ✅ Selesai | `app/Models/MaterialStocks.php` |
| Material Stock Logs (model + migration) | ✅ Selesai | `app/Models/MaterialStockLogs.php` |
| Material Stocks UI (Livewire view) | ✅ Ada | `resources/views/livewire/admin/material-stocks/` |
| Material Stocks Route | ⏳ Belum | Route masih dikomentari di `auth.php` |
| Threshold / Alert Stok Minimum | ❌ Missing | Belum ada implementasi spesifik |

---

### 🏭 Production Management

| Fitur | Status | Keterangan |
|---|---|---|
| Productions (model + migration) | ✅ Selesai | `app/Models/Productions.php` |
| Product Materials / Recipe (model + migration) | ✅ Selesai | `app/Models/ProductMaterials.php` |
| Products (model + migration) | ✅ Selesai | `app/Models/Products.php` |
| Product Stocks (model + migration) | ✅ Selesai | `app/Models/ProductStocks.php` |
| Product Stock Logs (model + migration) | ✅ Selesai | `app/Models/ProductStockLogs.php` |
| Product Stocks UI (Livewire view) | ✅ Ada | `resources/views/livewire/admin/product-stocks/` |
| Production UI / Route | ⏳ Belum | Route dikomentari, view belum lengkap |

---

### 🛒 Purchase / Pembelian

| Fitur | Status | Keterangan |
|---|---|---|
| Purchases (model + migration) | ✅ Selesai | `app/Models/Purchases.php` |
| Purchase Items (model + migration) | ✅ Selesai | `app/Models/PurchaseItems.php` |
| Purchases UI (Livewire view) | ✅ Ada | `resources/views/livewire/admin/purchases/` |
| Purchases Route | ⏳ Belum | Route masih dikomentari di `auth.php` |

---

### 🧾 POS / Sales (Kasir)

| Fitur | Status | Keterangan |
|---|---|---|
| Sales (model + migration) | ✅ Selesai | `app/Models/Sales.php` |
| Sale Items (model + migration) | ✅ Selesai | `app/Models/SaleItems.php` |
| Sales UI (Livewire view) | ✅ Ada | `resources/views/livewire/admin/sales/` |
| POS Interface (Livewire real-time) | ⏳ Belum | Route dikomentari, POS component belum ada |
| Multiple Payment (Cash/QR/Hutang) | ⏳ Belum | Model sudah support, UI belum |
| Receipt / Struk | ⏳ Belum | Belum ada implementasi |

---

### 📊 Laporan & Export

| Fitur | Status | Keterangan |
|---|---|---|
| Reports UI (Livewire view) | ✅ Ada | `resources/views/livewire/admin/reports/` |
| Export Stocks (Excel) | ✅ Selesai | `app/Exports/StocksExport.php` + route `/reports/stocks/export` |
| Report Routes (Sales/Purchase/Production) | ⏳ Belum | Route dikomentari di `auth.php` |
| `maatwebsite/excel` package | ⚠️ Perlu cek | Pastikan sudah ada di `composer.json` |

---

### 🎛️ Dashboard & UI Scaffolding

| Fitur | Status | Keterangan |
|---|---|---|
| Dashboard (Livewire) | ✅ Selesai | `app/Livewire/Admin/Dashboard/Index.php` |
| Layout App | ✅ Selesai | `resources/views/components/layouts/app.blade.php` |
| Sidebar | ✅ Selesai | `resources/views/components/partials/sidebar.blade.php` |
| Navbar | ✅ Selesai | `resources/views/components/partials/navbar.blade.php` |
| Pagination Component | ✅ Selesai | `resources/views/components/partials/pagination.blade.php` |
| Toast Notification Component | ✅ Selesai | `resources/views/components/partials/toast.blade.php` |
| Table Component | ✅ Selesai | `resources/views/components/partials/table.blade.php` |
| Dropdown Action Component | ✅ Selesai | `resources/views/components/partials/dropdown-action.blade.php` |
| Form Components | ✅ Selesai | `resources/views/components/form/` |
| Sessions Table | ✅ Selesai | Migration `2026_02_17_151000_create_sessions_table.php` |

---

### 🎓 Student / Scheduling (Modul Tambahan)

| Fitur | Status | Keterangan |
|---|---|---|
| Students (model + migration) | ✅ Ada | `app/Models/Students.php` |
| Student Groups (model + migration) | ✅ Ada | `app/Models/StudentGroups.php` |
| Student Group Members (model + migration) | ✅ Ada | `app/Models/StudentGroupMembers.php` |
| Schedules (model + migration) | ✅ Ada | `app/Models/Schedules.php` |
| Student / Schedule UI & Route | ⏳ Belum | Route dikomentari, view belum tersedia |

---

## 🚀 Roadmap Pengembangan Selanjutnya

### Phase 1 — Aktifkan Route & View yang Sudah Ada
- [ ] Aktifkan route `material-stocks`, `product-stocks`, `purchases`, `sales`
- [ ] Aktifkan route `master` (categories, units, suppliers, customers, shifts, divisions, classes)
- [ ] Lengkapi view yang masih kosong

### Phase 2 — POS Interface
- [ ] Buat Livewire POS component real-time
- [ ] Implementasi multiple payment (Cash, QR Manual, Hutang)
- [ ] Struk / receipt generation

### Phase 3 — Laporan Lengkap
- [ ] Aktifkan route reports (sales, purchases, productions, stocks)
- [ ] Buat Livewire report components
- [ ] Export Excel untuk semua laporan

### Phase 4 — Fitur Lanjutan
- [ ] Absensi (check-in/out + selfie upload)
- [ ] Threshold & inventory alert system
- [ ] Shift management UI
- [ ] Notifikasi stok menipis

### Phase 5 — Optimasi & Testing
- [ ] Performance optimization
- [ ] Security testing
- [ ] Mobile responsiveness

---

## 📁 Struktur Folder Utama

```
app/
├── Exports/
│   └── StocksExport.php
├── Http/
├── Livewire/
│   ├── Admin/
│   │   ├── CategoryPermissions/   ✅
│   │   ├── Dashboard/             ✅
│   │   ├── Master/                ⏳
│   │   ├── MaterialStocks/        ⏳ (view ada, route belum)
│   │   ├── Permissions/           ✅
│   │   ├── ProductStocks/         ⏳ (view ada, route belum)
│   │   ├── Purchases/             ⏳ (view ada, route belum)
│   │   ├── Reports/               ⏳ (view ada, route belum)
│   │   ├── Roles/                 ✅
│   │   ├── Sales/                 ⏳ (view ada, route belum)
│   │   └── Users/                 ✅
│   └── Auth/                      ✅
└── Models/                        ✅ (25 model tersedia)

resources/views/
├── components/
│   ├── form/                      ✅
│   ├── layouts/                   ✅
│   └── partials/                  ✅
└── livewire/
    └── admin/                     ✅ (11 folder view)

database/migrations/               ✅ (30 migration)
routes/
├── auth.php                       ✅ (banyak route masih dikomentari)
└── web.php                        ✅
```

---

## 📝 Legenda Status

| Simbol | Arti |
|---|---|
| ✅ Selesai | Implementasi lengkap dan aktif |
| ⏳ Belum | Model/migration ada, tapi route/UI belum aktif |
| ❌ Missing | Belum ada implementasi sama sekali |
| ⚠️ Perlu cek | Perlu verifikasi lebih lanjut |
