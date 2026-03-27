<?php

namespace Database\Seeders;

use App\Models\GuideContent;
use Illuminate\Database\Seeder;

class GuideContentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rows = [
            ['role_key' => 'admin', 'content_type' => 'step', 'title' => 'Step 1', 'body' => 'Cek dashboard untuk ringkasan aktivitas dan notifikasi stok.', 'sort_order' => 10],
            ['role_key' => 'admin', 'content_type' => 'step', 'title' => 'Step 2', 'body' => 'Pastikan master data (kategori, unit, shift, kelas, divisi) sudah valid.', 'sort_order' => 20],
            ['role_key' => 'admin', 'content_type' => 'faq', 'title' => 'Menu tidak muncul di sidebar?', 'body' => 'Periksa role dan permission user, lalu login ulang jika baru ada perubahan hak akses.', 'sort_order' => 10],
            ['role_key' => 'admin', 'content_type' => 'faq', 'title' => 'Data sudah disimpan tapi tidak terlihat?', 'body' => 'Cek filter tanggal/status/search di halaman terkait.', 'sort_order' => 20],
            ['role_key' => 'admin', 'content_type' => 'visual', 'title' => 'Kelola Jadwal', 'body' => 'Contoh alur: Buat jadwal, tentukan shift, lalu validasi tipe jadwal.', 'sort_order' => 10],
            ['role_key' => 'admin', 'content_type' => 'visual', 'title' => 'Finalisasi Produksi', 'body' => 'Verifikasi resep bahan sebelum klik Selesaikan agar log pemakaian tercatat benar.', 'sort_order' => 20],

            ['role_key' => 'production', 'content_type' => 'step', 'title' => 'Step 1', 'body' => 'Buat data produksi harian (produk, kelompok, shift, qty rencana).', 'sort_order' => 10],
            ['role_key' => 'production', 'content_type' => 'step', 'title' => 'Step 2', 'body' => 'Klik Selesaikan, isi hasil aktual, dan catat limbah bila ada.', 'sort_order' => 20],
            ['role_key' => 'production', 'content_type' => 'faq', 'title' => 'Bahan diinput dari mana?', 'body' => 'Bahan diambil otomatis dari menu Resep Produk (Product Materials).', 'sort_order' => 10],
            ['role_key' => 'production', 'content_type' => 'visual', 'title' => 'Finalisasi Produksi', 'body' => 'Flow penting: validasi resep, isi hasil aktual, simpan, lalu cek detail produksi.', 'sort_order' => 10],

            ['role_key' => 'cashier', 'content_type' => 'step', 'title' => 'Step 1', 'body' => 'Login melalui POS dan pastikan shift aktif.', 'sort_order' => 10],
            ['role_key' => 'cashier', 'content_type' => 'faq', 'title' => 'Invoice tidak muncul?', 'body' => 'Pastikan checkout berhasil dan status transaksi tidak dibatalkan.', 'sort_order' => 10],
            ['role_key' => 'cashier', 'content_type' => 'visual', 'title' => 'Proses Checkout', 'body' => 'Pastikan jumlah dibayar dan metode pembayaran sudah benar sebelum submit.', 'sort_order' => 10],

            ['role_key' => 'student', 'content_type' => 'step', 'title' => 'Step 1', 'body' => 'Cek jadwal harian sebelum mulai aktivitas.', 'sort_order' => 10],
            ['role_key' => 'student', 'content_type' => 'faq', 'title' => 'Status hadir salah?', 'body' => 'Hubungi admin/pengampu untuk koreksi data melalui menu kehadiran.', 'sort_order' => 10],
            ['role_key' => 'student', 'content_type' => 'visual', 'title' => 'Kehadiran Siswa', 'body' => 'Verifikasi jam login agar keterlambatan dihitung akurat.', 'sort_order' => 10],
        ];

        foreach ($rows as $row) {
            GuideContent::updateOrCreate(
                [
                    'role_key' => $row['role_key'],
                    'content_type' => $row['content_type'],
                    'title' => $row['title'],
                ],
                [
                    'module_key' => $row['module_key'] ?? null,
                    'body' => $row['body'] ?? null,
                    'media_url' => $row['media_url'] ?? null,
                    'required_permission' => $row['required_permission'] ?? null,
                    'sort_order' => $row['sort_order'] ?? 0,
                    'is_active' => $row['is_active'] ?? true,
                ]
            );
        }
    }
}
