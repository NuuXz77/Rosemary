<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CategoryPermissions;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $dashboardCategory = CategoryPermissions::where('name', 'Dashboard')->first();
        $userCategory = CategoryPermissions::where('name', 'Manajemen Pengguna')->first();
        $studentCategory = CategoryPermissions::where('name', 'Manajemen Siswa')->first();
        $scheduleCategory = CategoryPermissions::where('name', 'Penjadwalan')->first();
        $inventoryCategory = CategoryPermissions::where('name', 'Manajemen Inventaris')->first();
        $transactionCategory = CategoryPermissions::where('name', 'Manajemen Transaksi')->first();
        $reportCategory = CategoryPermissions::where('name', 'Laporan & Analitik')->first();
        $settingCategory = CategoryPermissions::where('name', 'Pengaturan')->first();

        $permissions = [
            // Sidebar permissions
            ['name' => 'dashboard.view', 'category_id' => optional($dashboardCategory)->id],
            ['name' => 'users.manage', 'category_id' => optional($userCategory)->id],
            ['name' => 'roles.view', 'category_id' => optional($userCategory)->id],
            ['name' => 'roles.manage', 'category_id' => optional($userCategory)->id],
            ['name' => 'permissions.view', 'category_id' => optional($userCategory)->id],
            ['name' => 'permissions.manage', 'category_id' => optional($userCategory)->id],

            // CRUD permissions used in components
            ['name' => 'users.view', 'category_id' => optional($userCategory)->id],
            ['name' => 'users.create', 'category_id' => optional($userCategory)->id],
            ['name' => 'users.edit', 'category_id' => optional($userCategory)->id],
            ['name' => 'users.delete', 'category_id' => optional($userCategory)->id],

            ['name' => 'roles.create', 'category_id' => optional($userCategory)->id],
            ['name' => 'roles.edit', 'category_id' => optional($userCategory)->id],
            ['name' => 'roles.delete', 'category_id' => optional($userCategory)->id],

            ['name' => 'permissions.create', 'category_id' => optional($userCategory)->id],
            ['name' => 'permissions.edit', 'category_id' => optional($userCategory)->id],
            ['name' => 'permissions.delete', 'category_id' => optional($userCategory)->id],

            // Manajemen Siswa
            ['name' => 'students.view', 'category_id' => optional($studentCategory)->id],
            ['name' => 'student-groups.view', 'category_id' => optional($studentCategory)->id],
            ['name' => 'student-group-members.view', 'category_id' => optional($studentCategory)->id],

            // Penjadwalan
            ['name' => 'schedules.view', 'category_id' => optional($scheduleCategory)->id],

            // Manajemen Inventaris
            ['name' => 'materials.view', 'category_id' => optional($inventoryCategory)->id],
            ['name' => 'material-stocks.view', 'category_id' => optional($inventoryCategory)->id],
            ['name' => 'material-stock-logs.view', 'category_id' => optional($inventoryCategory)->id],
            ['name' => 'products.view', 'category_id' => optional($inventoryCategory)->id],
            ['name' => 'product-stocks.view', 'category_id' => optional($inventoryCategory)->id],
            ['name' => 'product-stock-logs.view', 'category_id' => optional($inventoryCategory)->id],
            ['name' => 'product-materials.view', 'category_id' => optional($inventoryCategory)->id],

            // Manajemen Transaksi
            ['name' => 'purchases.view', 'category_id' => optional($transactionCategory)->id],
            ['name' => 'productions.view', 'category_id' => optional($transactionCategory)->id],
            ['name' => 'sales.view', 'category_id' => optional($transactionCategory)->id],

            // Laporan & Analitik
            ['name' => 'reports.sales.view', 'category_id' => optional($reportCategory)->id],
            ['name' => 'reports.purchases.view', 'category_id' => optional($reportCategory)->id],
            ['name' => 'reports.productions.view', 'category_id' => optional($reportCategory)->id],
            ['name' => 'reports.stocks.view', 'category_id' => optional($reportCategory)->id],
            ['name' => 'reports.schedules.view', 'category_id' => optional($reportCategory)->id],

            // Pengaturan
            ['name' => 'master.categories.view', 'category_id' => optional($settingCategory)->id],
            ['name' => 'master.units.view', 'category_id' => optional($settingCategory)->id],
            ['name' => 'master.suppliers.view', 'category_id' => optional($settingCategory)->id],
            ['name' => 'master.customers.view', 'category_id' => optional($settingCategory)->id],
            ['name' => 'master.shifts.view', 'category_id' => optional($settingCategory)->id],
            ['name' => 'master.divisions.view', 'category_id' => optional($settingCategory)->id],
            ['name' => 'master.classes.view', 'category_id' => optional($settingCategory)->id],
            ['name' => 'settings.app.view', 'category_id' => optional($settingCategory)->id],
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission['name'], 'guard_name' => 'web'],
                ['category_id' => $permission['category_id']]
            );
        }
    }
}
