<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

$adminRole = Role::where('name', 'Admin')->first();
echo "Admin role permissions count: " . ($adminRole ? $adminRole->permissions->count() : '0') . "\n";

$inventoryRole = Role::where('name', 'Inventory')->first();
echo "Inventory role permissions count: " . ($inventoryRole ? $inventoryRole->permissions->count() : '0') . "\n";

$adminUser = User::where('username', 'admin')->first();
if ($adminUser) {
    echo "Admin User roles: " . implode(', ', $adminUser->getRoleNames()->toArray()) . "\n";
    echo "Admin User can 'material-wastes.view': " . ($adminUser->hasPermissionTo('material-wastes.view') ? 'YES' : 'NO') . "\n";
} else {
    echo "Admin User not found\n";
}

$inventoryUser = User::where('username', 'inventory')->first();
if ($inventoryUser) {
    echo "Inventory User roles: " . implode(', ', $inventoryUser->getRoleNames()->toArray()) . "\n";
    echo "Inventory User can 'material-wastes.view': " . ($inventoryUser->hasPermissionTo('material-wastes.view') ? 'YES' : 'NO') . "\n";
} else {
    echo "Inventory User not found\n";
}
