<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CategoryPermissions;

class CategoryPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Dashboard',
                'description' => 'Akses halaman dashboard',
                'order' => 1,
            ],
            [
                'name' => 'Manajemen Pengguna',
                'description' => 'Kelola user, role, dan permission',
                'order' => 2,
            ],
            [
                'name' => 'Manajemen Siswa',
                'description' => 'Kelola data siswa dan kelompok',
                'order' => 3,
            ],
            [
                'name' => 'Penjadwalan',
                'description' => 'Kelola jadwal harian',
                'order' => 4,
            ],
            [
                'name' => 'Manajemen Inventaris',
                'description' => 'Kelola material dan produk',
                'order' => 5,
            ],
            [
                'name' => 'Manajemen Transaksi',
                'description' => 'Kelola transaksi pembelian, produksi, dan penjualan',
                'order' => 6,
            ],
            [
                'name' => 'Laporan & Analitik',
                'description' => 'Akses laporan dan analitik',
                'order' => 7,
            ],
            [
                'name' => 'Pengaturan',
                'description' => 'Kelola data master dan pengaturan aplikasi',
                'order' => 8,
            ],
        ];

        foreach ($categories as $category) {
            CategoryPermissions::firstOrCreate(
                ['name' => $category['name']],
                $category
            );
        }
    }
}
