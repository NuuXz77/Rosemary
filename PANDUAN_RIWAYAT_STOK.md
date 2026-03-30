# Panduan Riwayat Stok Bahan Baku (Material Stock Logs)

## 📍 Lokasi Menu

- **URL**: `http://127.0.0.1:8000/material-stock-logs`
- **Sidebar**: Inventory → Log Mutasi Bahan Baku

## ✅ Status Fitur

Semua komponen sudah siap digunakan. Riwayat stok akan tercatat otomatis saat Anda melakukan aktivitas berikut:

---

## 🔄 3 Cara Pencatatan Riwayat Stok

### 1️⃣ **Penyesuaian Stok Manual**

Gunakan ini ketika ada koreksi stok atau masuk dari sumber lain.

**Langkah-langkah:**

1. Buka menu **Stok Bahan Baku** (Material Stocks)
2. Cari bahan baku yang ingin disesuaikan
3. Klik tombol **"Sesuaikan"** (warna biru)
4. Pilih tipe: **"add"** (tambah) atau **"subtract"** (kurangi)
5. Masukkan jumlah penyesuaian
6. Masukkan catatan/keterangan
7. Klik **"Simpan"**

**Maka di Log Mutasi akan muncul:**

- Tipe: **Penyesuaian**
- Badge warna: **Kuning**

---

### 2️⃣ **Pencatatan Limbah/Waste**

Otomatis tercatat saat ada limbah produksi.

**Akan muncul di Log sebagai:**

- Tipe: **Keluar (Out)**
- Badge warna: **Merah**
- Referensi ke Material Wastes

---

### 3️⃣ **Penggunaan Material Produksi**

Otomatis tercatat saat proses produksi berjalan.

**Akan muncul di Log sebagai:**

- Tipe: **Keluar (Out)**
- Badge warna: **Merah**
- Referensi ke Production

---

## 📊 Tampilan Log Mutasi

Kolom-kolom yang ditampilkan:

| Kolom          | Keterangan                     |
| -------------- | ------------------------------ |
| **Waktu**      | Tanggal dan jam pencatatan     |
| **Bahan Baku** | Nama material + unit satuannya |
| **Tipe**       | IN/OUT/ADJ (status perubahan)  |
| **Jumlah**     | Qty yang berubah (+/-)         |
| **Keterangan** | Deskripsi/catatan perubahan    |
| **Petugas**    | Siapa yang membuat log         |

---

## 🔍 Fitur Pencarian & Filter

### Pencarian

- Cari berdasarkan **nama bahan baku**
- Live search saat mengetik

### Filter

- **Masuk (In)** - Stock yang masuk
- **Keluar (Out)** - Stock yang keluar/digunakan
- **Penyesuaian** - Koreks manual stok

---

## 📝 Informasi Tambahan

### Tipe Warna Badge

- 🟢 **Hijau** (IN) = Stok bertambah
- 🔴 **Merah** (OUT) = Stok berkurang
- 🟡 **Kuning** (ADJ) = Penyesuaian

### Informasi Referensi

Setiap log bisa mereferensi ke transaksi asalnya:

- `PurchaseItem` - Pembelian bahan
- `MaterialWaste` - Limbah material
- `Production` - Hasil produksi

---

## 🎯 Tips Penggunaan

✅ **Lakukan secara konsisten** - Catat semua perubahan stok
✅ **Gunakan deskripsi jelas** - Agar mudah di-track nanti
✅ **Cek rutin** - Review log untuk audit trail stok
✅ **Verifikasi qty** - Pastikan jumlah yang dimasukkan akurat

---

## ❓ Troubleshooting

**Pertanyaan:** Kenapa log tidak muncul?
**Jawab:** Pastikan:

1. Sudah melakukan penyesuaian stok atau produksi
2. User memiliki permission `material-stock-logs.view`
3. Database sudah ter-migrate dengan baik

**Pertanyaan:** Bagaimana jika ingin edit log yang sudah ada?
**Jawab:** Saat ini log bersifat append-only (read-only). Untuk koreksi, buat entry baru dengan tipe penyesuaian.

---

**Terakhir diupdate:** 17 Maret 2026
