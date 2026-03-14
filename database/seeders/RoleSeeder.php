<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $roles = [
            'admin',
            'production',
            'inventory',
            'cashier',
        ];

        foreach ($roles as $roleName) {
            Role::firstOrCreate(
                ['name' => $roleName, 'guard_name' => 'web']
            );
        }

        /** @var Role $adminRole */
        $adminRole = Role::where('name', 'Admin')->first();
        if ($adminRole) {
            $adminRole->syncPermissions(Permission::pluck('name')->toArray());
        }

        // Production Role Permissions
        /** @var Role $productionRole */
        $productionRole = Role::where('name', 'Production')->first();
        if ($productionRole) {
            $productionRole->syncPermissions([
                'productions.view',
                'products.view',
                'product-stocks.view',
                'product-materials.view',
                'reports.productions.view',
            ]);
        }

        // Inventory Role Permissions
        /** @var Role $inventoryRole */
        $inventoryRole = Role::where('name', 'Inventory')->first();
        if ($inventoryRole) {
            $inventoryRole->syncPermissions([
                'materials.view',
                'material-stocks.view',
                'material-stock-logs.view',
                'purchases.view',
                'reports.stocks.view',
            ]);
        }

        // Cashier Role Permissions
        /** @var Role $cashierRole */
        $cashierRole = Role::where('name', 'Cashier')->first();
        if ($cashierRole) {
            $cashierRole->syncPermissions([
                'dashboard.view',
                'sales.view',
                'reports.sales.view',
            ]);
        }
    }
}
