# Rosemary — Smart Food Production, Inventory & POS

<p align="center">
  <img alt="Laravel" src="https://img.shields.io/badge/Laravel-12-red" />
  <img alt="Livewire" src="https://img.shields.io/badge/Livewire-3.x-purple" />
  <img alt="PHP" src="https://img.shields.io/badge/PHP-8.2-blue" />
  <img alt="Vite" src="https://img.shields.io/badge/Vite-7.x-646CFF" />
  <img alt="Tailwind" src="https://img.shields.io/badge/TailwindCSS-4.x-38BDF8" />
  <img alt="License" src="https://img.shields.io/badge/License-MIT-green" />
</p>

Platform manajemen produksi makanan modern berbasis Laravel + Livewire untuk kebutuhan:

- Produksi & pemakaian bahan baku (BOM)
- Stok bahan & stok produk
- Penjualan kasir (POS)
- Pembelian, waste, laporan, dan penjadwalan siswa/kelompok
- Role-based access control (Spatie Permission)

## 🌐 Demo

- Live: https://rosemary.smkn1ciamis.id

## ✨ Highlights

- UI modern dengan TailwindCSS + DaisyUI
- SPA-like navigation menggunakan Livewire Navigate
- Dynamic theme switcher (multi-theme)
- Audit log mutasi stok bahan & produk
- Integrasi export laporan (Excel)
- Sistem role & permission yang granular

## 🧱 Tech Stack

### Backend

- Laravel Framework 12
- Livewire 3
- PHP 8.2
- MySQL/MariaDB (sesuai konfigurasi `.env`)

### Frontend

- Vite 7
- TailwindCSS 4
- DaisyUI 5
- ApexCharts

## 📦 Libraries Used

### Composer Dependencies (`composer.json`)

| Package | Version | Kegunaan |
|---|---:|---|
| `laravel/framework` | `^12.0` | Core framework |
| `livewire/livewire` | `^3.7` | Reactive component (full-stack) |
| `spatie/laravel-permission` | `^6.24` | Role & permission management |
| `maatwebsite/excel` | `^3.1` | Export/import Excel |
| `phpoffice/phpspreadsheet` | `^1.30` | Engine spreadsheet |
| `laravel/tinker` | `^2.10.1` | REPL Laravel |
| `blade-ui-kit/blade-heroicons` | `^2.6` | Heroicons Blade |
| `blade-ui-kit/blade-icons` | `^1.8` | Blade icon base package |
| `blade-ui-kit/blade-zondicons` | `^1.6` | Zondicons Blade |
| `afatmustafa/blade-hugeicons` | `^1.0` | Hugeicons Blade |
| `codeat3/blade-clarity-icons` | `^1.10` | Clarity icons Blade |
| `codeat3/blade-iconpark` | `^1.7` | IconPark Blade |
| `codeat3/blade-solar-icons` | `^1.3` | Solar icons Blade |
| `davidhsianturi/blade-bootstrap-icons` | `^2.1` | Bootstrap icons Blade |

### Composer Dev Dependencies

| Package | Version | Kegunaan |
|---|---:|---|
| `phpunit/phpunit` | `^11.5.3` | Unit/feature testing |
| `laravel/pint` | `^1.24` | Code formatter |
| `nunomaduro/collision` | `^8.6` | Error reporting CLI |
| `fakerphp/faker` | `^1.23` | Dummy data |
| `mockery/mockery` | `^1.6` | Test mocking |
| `laravel/sail` | `^1.41` | Docker environment |
| `laravel/pail` | `^1.2.2` | Log tailing |

### NPM Dependencies (`package.json`)

| Package | Version | Kegunaan |
|---|---:|---|
| `apexcharts` | `^5.10.3` | Visualisasi chart dashboard |
| `tailwind-scrollbar-hide` | `^4.0.0` | Utility hide scrollbar |
| `readme-md-generator` | `^1.0.0` | README generator |

### NPM Dev Dependencies

| Package | Version | Kegunaan |
|---|---:|---|
| `vite` | `^7.0.7` | Bundler frontend |
| `laravel-vite-plugin` | `^2.0.0` | Integrasi Laravel + Vite |
| `tailwindcss` | `^4.0.0` | Utility-first CSS |
| `@tailwindcss/vite` | `^4.0.0` | Tailwind plugin for Vite |
| `daisyui` | `^5.5.18` | Komponen UI |
| `axios` | `^1.11.0` | HTTP client |
| `concurrently` | `^9.0.1` | Jalankan multi-process dev |

## 🚀 Quick Start

### Prerequisites

- PHP `8.2+`
- Composer `2+`
- Node.js `18+` (recommended `20+`)
- NPM
- Database (MySQL/MariaDB)

### Installation

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
```

Konfigurasi database di `.env`, lalu jalankan:

```bash
php artisan migrate
php artisan db:seed
```

### Run Development

Opsi 1 (semua service sekaligus):

```bash
composer run dev
```

Opsi 2 (manual):

```bash
php artisan serve
php artisan queue:listen --tries=1 --timeout=0
npm run dev
```

## 🧪 Testing & Quality

```bash
composer run test
php artisan test
./vendor/bin/pint
```

## 📜 Useful Scripts

### Composer Scripts

- `composer run setup` — install full project + migrate + build assets
- `composer run dev` — jalankan server, queue, dan vite secara paralel
- `composer run test` — clear config dan jalankan test

### NPM Scripts

- `npm run dev` — start Vite dev server
- `npm run build` — build production assets

## 📁 Struktur Utama

```txt
app/
  Livewire/           # Komponen business logic UI
  Models/             # Eloquent models
resources/
  views/              # Blade templates
  css/, js/           # Frontend assets
routes/
  web.php, auth.php   # Definisi route
database/
  migrations/seeders  # Struktur & data awal
```

## 👤 Author

- Tim Rosemary
- GitHub: [@NuuXz77](https://github.com/NuuXz77)

## 🤝 Contributing

1. Fork repository
2. Buat branch fitur baru
3. Commit perubahan
4. Buat Pull Request

## ⭐ Support

Kalau project ini membantu, jangan lupa kasih star di repository.

## 📄 License

MIT
