<?php

namespace Database\Seeders;

use App\Models\GuideMenu;
use Illuminate\Database\Seeder;

class GuideMenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rows = [
            ['role_key' => 'admin', 'label' => 'Dashboard', 'route_name' => 'dashboard.index', 'required_permission' => 'dashboard.view', 'sort_order' => 10],
            ['role_key' => 'admin', 'label' => 'Jadwal Harian', 'route_name' => 'schedules.index', 'required_permission' => 'schedules.view', 'sort_order' => 20],
            ['role_key' => 'admin', 'label' => 'Produksi', 'route_name' => 'productions.index', 'required_permission' => 'productions.view', 'sort_order' => 30],
            ['role_key' => 'admin', 'label' => 'Laporan Produksi', 'route_name' => 'reports.productions.index', 'required_permission' => 'reports.productions.view', 'sort_order' => 40],

            ['role_key' => 'cashier', 'label' => 'Kasir POS', 'route_name' => 'kasir.pos', 'required_permission' => 'sales.view', 'sort_order' => 10],
            ['role_key' => 'cashier', 'label' => 'Riwayat Penjualan', 'route_name' => 'sales.index', 'required_permission' => 'sales.view', 'sort_order' => 20],

            ['role_key' => 'production', 'label' => 'Produksi Harian', 'route_name' => 'productions.index', 'required_permission' => 'productions.view', 'sort_order' => 10],
            ['role_key' => 'production', 'label' => 'Resep Produk', 'route_name' => 'product-materials.index', 'required_permission' => 'product-materials.view', 'sort_order' => 20],
            ['role_key' => 'production', 'label' => 'Kehadiran Grup', 'route_name' => 'student-group-attendances.index', 'required_permission' => 'student-group-attendances.view', 'sort_order' => 30],

            ['role_key' => 'student', 'label' => 'Jadwal Harian', 'route_name' => 'schedules.index', 'required_permission' => 'schedules.view', 'sort_order' => 10],
            ['role_key' => 'student', 'label' => 'Kehadiran Siswa', 'route_name' => 'student-attendances.index', 'required_permission' => 'schedules.view', 'sort_order' => 20],
            ['role_key' => 'student', 'label' => 'Kehadiran Grup', 'route_name' => 'student-group-attendances.index', 'required_permission' => 'student-group-attendances.view', 'sort_order' => 30],
        ];

        foreach ($rows as $row) {
            GuideMenu::updateOrCreate(
                [
                    'role_key' => $row['role_key'],
                    'label' => $row['label'],
                ],
                [
                    'module_key' => $row['module_key'] ?? null,
                    'route_name' => $row['route_name'] ?? null,
                    'external_url' => $row['external_url'] ?? null,
                    'required_permission' => $row['required_permission'] ?? null,
                    'description' => $row['description'] ?? null,
                    'sort_order' => $row['sort_order'] ?? 0,
                    'is_active' => $row['is_active'] ?? true,
                ]
            );
        }
    }
}
