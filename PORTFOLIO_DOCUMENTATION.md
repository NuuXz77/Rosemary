# Dokumentasi Portfolio Aplikasi Rosemary

## LATAR BELAKANG

Proses manajemen produksi pangan dan inventaris di industri skala menengah sering kali dilakukan secara manual atau dengan sistem yang terpisah-pisah, mengakibatkan ketidakcocokan data, kesulitan pelacakan stok, dan kesulitan dalam koordinasi antar departemen. Aplikasi Rosemary diciptakan untuk mengatasi tantangan-tantangan ini dengan menyediakan solusi terpadu yang mengelola seluruh aspek operasional, mulai dari perencanaan produksi, manajemen bahan baku, pelacakan inventaris, hingga sistem penjualan point-of-sale (POS). Dengan mengintegrasikan semua fungsi bisnis dalam satu platform, Rosemary meningkatkan efisiensi operasional, mengurangi kesalahan manual, dan memberikan visibilitas real-time terhadap status produksi dan inventaris.

Proyek ini dirancang khusus untuk kebutuhan perusahaan yang memiliki skalabilitas tinggi, dengan perhatian khusus pada kemudahan penggunaan, keamanan data, dan fleksibilitas sistem untuk mendukung pertumbuhan bisnis ke depan.

## TUJUAN

1. **Mengotomatisasi Proses Produksi** - Menyederhanakan perencanaan produksi dan penjadwalan dengan sistem terstruktur yang mengurangi pekerjaan manual dan meminimalkan kesalahan input data.

2. **Manajemen Inventaris Terintegrasi** - Menyediakan sistem pelacakan real-time untuk stok bahan baku dan produk jadi, dengan alert otomatis untuk stok yang menipis atau kedaluwarsa, sehingga memastikan ketersediaan stok yang optimal.

3. **Meningkatkan Efisiensi POS dan Penjualan** - Menghadirkan sistem penjualan modern (Kasir) yang terintegrasi dengan inventaris, mendukung berbagai metode pembayaran, dan menghasilkan laporan penjualan real-time untuk analisis bisnis.

4. **Sistem Permisi dan Kontrol Akses Berbasis Peran** - Menjaga integritas data dengan sistem keamanan komprehensif yang membedakan akses berdasarkan peran pengguna (admin, manajer, kasir, operator produksi, dll.), memastikan hanya orang yang berwenang dapat mengakses atau memodifikasi data sensitif.

5. **Analitik dan Laporan Komprehensif** - Menyediakan dashboard dan fitur export data (Excel) untuk mendukung pengambilan keputusan strategis berbasis data, dengan kemampuan analisis mendalam terhadap kinerja produksi, penjualan, dan manajemen biaya.

## TEKNOLOGI YANG DIGUNAKAN

- **Backend Framework** : Laravel 12 - Framework PHP modern yang menyediakan arsitektur MVC skalabel, ORM Eloquent untuk database management, dan middleware untuk keamanan dan autentikasi.

- **Frontend Framework** : Livewire 3 - Library PHP untuk membangun UI dinamis tanpa perlu banyak JavaScript, dengan reactive components, real-time validation, dan seamless communication antara frontend-backend.

- **CSS & UI Components** : Tailwind CSS 4 + DaisyUI 5 - Utility-first CSS framework untuk styling yang konsisten dan cepat, dilengkapi dengan component library (DaisyUI) untuk mempercepat development dan menjaga konsistensi desain.

- **Build Tool** : Vite 7 - Modern build tool yang memberikan development experience lebih cepat dengan HMR (Hot Module Replacement) dan optimization yang lebih baik untuk production builds.

- **Database** : MySQL / MariaDB - Database relasional yang stabil dan mendukung kompleksitas schema Rosemary dengan indexing dan query optimization.

- **Key Dependencies** :
  - Spatie Laravel Permission (v6.24) - Sistem roles & permissions berbasis database yang fleksibel dan mudah dikonfigurasi.
  - Laravel Excel (v3.1) & PhpSpreadsheet (v1.30) - Untuk export-import data ke format Excel dengan performa tinggi.
  - ApexCharts (v5.10.3) - Library charting interaktif untuk dashboard analytics dan visualisasi data real-time.
  - Blade Heroicons (v2.6) - Icon library yang integrasi natural dengan Blade templating engine.

## PERAN SAYA DALAM TIM

Saya berperan sebagai **Team Leader** dalam pengembangan aplikasi Rosemary.

Tanggung jawab utama saya:
- Menentukan arah pengembangan fitur agar sesuai kebutuhan pengguna.
- Membagi tugas tim (backend, frontend, dan testing) agar progres terstruktur.
- Memastikan integrasi antar modul berjalan baik dan minim error.
- Melakukan review hasil kerja tim sebelum masuk tahap rilis.
- Menjadi penghubung komunikasi antara tim pengembang dan pihak pengguna.

## FITUR UTAMA APLIKASI

### 1. Manajemen Panduan Produksi (Guides Module)
Sistem terstruktur untuk mendokumentasikan proses produksi dengan empat jenis panduan utama:
- **Menu Guides** - Panduan untuk berbagai resep/menu produk yang akan diproduksi.
- **Step Guides** - Petunjuk langkah demi langkah untuk setiap tahap produksi.
- **FAQ Guides** - Dokumentasi pertanyaan umum dan jawaban untuk operator produksi.
- **Visual Guides** - Panduan visual dengan foto/gambar untuk memudahkan pemahaman operator.

### 2. Manajemen Bahan Baku (Materials Management)
Pencatatan dan pengelolaan semua jenis bahan baku yang digunakan dalam produksi, termasuk:
- Master data bahan baku dengan kategori, unit, harga, dan supplier.
- Tracking stok bahan baku secara real-time dengan notifikasi stok minimum.
- Riwayat penggunaan dan waste tracking untuk analisis efisiensi.

### 3. Perencanaan dan Penjadwalan Produksi (Schedules & Productions)
Sistem untuk merencanakan kapan dan berapa banyak produk yang akan diproduksi:
- Penjadwalan produksi dengan assignment ke operator/group tertentu.
- Tracking status produksi dari awal hingga selesai.
- Bill of Materials (BoM) management untuk kalkulasi otomatis kebutuhan bahan per produk.

### 4. Manajemen Inventaris Produk (Product Stocks)
Pelacakan stok produk jadi dengan kemampuan multi-lokasi/multi-gudang:
- Real-time stock balance per lokasi.
- Stock movement history untuk audit trail lengkap.
- Otomatis deduction saat produksi selesai atau penjualan terjadi.

### 5. Sistem Penjualan & POS (Kasir Module)
Platform penjualan modern yang terintegrasi dengan inventaris:
- Antarmuka kasir user-friendly untuk transaksi cepat.
- Support untuk berbagai produk dan customer types.
- Real-time order fulfillment dan inventory synchronization.

### 6. Manajemen Karyawan & Absensi (Attendances Module)
Sistem pencatatan kehadiran dan manajemen karyawan/group:
- Track kehadiran per shift/tanggal.
- Late request management untuk transparansi kehadiran.
- Reporting absensi untuk analisis performa tim.

### 7. Sistem Roles & Permissions
Kontrol akses berbasis peran dengan granular permission management:
- Pre-configured roles: Admin, Manager, Kasir, Operator Produksi, dll.
- Dynamic permission assignment per feature/action.
- Audit log untuk tracking perubahan data sensitif.

### 8. Export & Reporting (Analytics)
Fitur export dan analitik untuk pengambilan keputusan:
- Export ke Excel (Sales, Purchases, Productions, Schedules, Stocks, Wastes).
- Dashboard dengan ApexCharts untuk visualisasi KPI real-time.
- Custom report builder untuk business intelligence.

## TANTANGAN DAN SOLUSI

a. **Penjadwalan Cashier dan Production**
   - **Tantangan** : Manajemen dua jenis jadwal (shift cashier & production schedule) rentan conflict dan overlapping.
   - **Solusi** : Modul Schedules & Productions terpisah dengan validasi, dashboard timeline untuk monitoring mudah.

b. **Produk, Bahan, dan Stok Real-time**
   - **Tantangan** : Kompleksitas hubungan antar data (bahan berkurang saat produksi, produk bertambah, berkurang saat jual).
   - **Solusi** : Event-driven architecture dengan Livewire, database transactions untuk ensure data consistency.

c. **Integrasi Data Multi-Modul**
   - **Tantangan** : Data bisa inconsistent antar modul produksi, inventaris, dan penjualan.
   - **Solusi** : Eloquent relationships yang well-defined, database triggers, dan audit logging untuk track integrity.

d. **Laporan Data dan Analytics**
   - **Tantangan** : Mengumpulkan insight dari multiple modules memerlukan effort besar dan error-prone.
   - **Solusi** : ApexCharts untuk dashboard KPI real-time, export Excel comprehensive dengan query optimization.

e. **Manajemen Transaksi Penjualan (POS)**
   - **Tantangan** : POS harus cepat, reliable, dan accurate; disconnect bisa menyebabkan data loss.
   - **Solusi** : Laravel transactions memastikan atomicity, responsive UI dengan Livewire, real-time stock sync.

