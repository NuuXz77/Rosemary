# 🌹 Rosemary — Sistem Manajemen F&B Terintegrasi

> Sistem manajemen F&B modern dengan **Laravel + Livewire** untuk mengelola produksi, stok, kasir, dan akuntansi dengan integrasi real-time.

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

## ✅ Status Implementasi (Update: 2026-02-22)

### 🔐 Authentication & Profile
| Fitur | Status | Keterangan |
|---|---|---|
| Login/Logout | ✅ Selesai | Custom Livewire Auth |
| Profile Dashboard | ✅ Selesai | Modern View + Edit Mode |

### 👤 Manajemen User & Access
| Fitur | Status | Keterangan |
|---|---|---|
| CRUD User | ✅ Selesai | Full CRUD |
| Role & Permission | ✅ Selesai | Spatie base + UI Management |

### 📦 Inventory & Production
| Fitur | Status | Keterangan |
|---|---|---|
| Data Material & Produk | ✅ Selesai | Full CRUD + Relations |
| Manajemen Resep (BOM) | ✅ Selesai | Grouped by Product UI |
| Stok & Log Mutasi | ✅ Selesai | Auto deduction logic |
| Produksi Harian | ✅ Selesai | Daily Production Flow |

### 💰 POS & Transaction
| Fitur | Status | Keterangan |
|---|---|---|
| Real-time POS Interface | ✅ Selesai | Modern Grid UI |
| Manajemen Keranjang | ✅ Selesai | Reactive Cart Components |
| Payment & Checkout | ✅ Selesai | Cash/QRIS + Auto Stock Deduction |
| Cetak Struk (Thermal) | ✅ Selesai | Print logic & Receipt Preview |

### 📊 Dashboard & Analytics
| Fitur | Status | Keterangan |
|---|---|---|
| Summary Widgets | ✅ Selesai | Real-time Stats |
| Sales Trending | ✅ Selesai | 7-Days Chart |
| Reports (Sales & Prod) | ✅ Selesai | Advanced filtering & summary |

---

## 🚀 Roadmap Pengembangan Terbaru

### 📅 Phase 1 - 5: All Integrated Systems
✅ **PROJECT COMPLETED (100%)**
- [x] **Phase 1-3**: Core Master, Inventory, & Full POS System.
- [x] **Phase 4**: Premium Analytics Dashboard & Detailed Reports.
- [x] **Phase 5**: Optimasi & UI/UX Polish.
    - [x] Mobile Responsive POS Interface.
    - [x] Implementasi "Cetak Struk" (Thermal Receipt) di POS & History.
    - [x] Perbaikan navigasi & konsistensi UI.

---

## 📁 Struktur Folder Utama (Verified)

```
app/
├── Livewire/
│   ├── Admin/
│   │   ├── Dashboard/          ✅ Selesai
│   │   ├── Master/             ✅ Selesai
│   │   ├── MaterialStocks/     ✅ Selesai
│   │   ├── ProductStocks/      ✅ Selesai
│   │   ├── Productions/        ✅ Selesai
│   │   ├── Sales/              ✅ Selesai (POS Included)
│   │   └── Reports/            ✅ Selesai (Sales, Prod)
│   └── Auth/                   ✅ Selesai
└── Models/                     ✅ 25 Model (Logic Ready)
```

---

## ⚡ Quick Start Commands
- `php artisan migrate --seed`
- `npm run dev`
- `php artisan serve`
