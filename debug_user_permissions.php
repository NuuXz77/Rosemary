<?php

// Debug script to check current user role and permissions
// Usage: php artisan tinker < this_file.php

$user = auth()->user() ?? \App\Models\User::first();

if (!$user) {
    echo "❌ Tidak ada user yang login atau ditemukan di database\n";
    die;
}

echo "\n";
echo "═══════════════════════════════════════════════════════════\n";
echo "📋 USER ROLE & PERMISSION DEBUG\n";
echo "═══════════════════════════════════════════════════════════\n\n";

echo "👤 User: {$user->name} ({$user->email})\n";
echo "📧 Email: {$user->email}\n";

echo "\n🔐 Current Roles:\n";
$roles = $user->getRoleNames();
if ($roles->isEmpty()) {
    echo "   ❌ TIDAK ADA ROLE! (Ini penyebab 403 error)\n";
} else {
    foreach ($roles as $role) {
        echo "   ✅ {$role}\n";
    }
}

echo "\n🔑 Current Permissions:\n";
$permissions = $user->getPermissionNames();
if ($permissions->isEmpty()) {
    echo "   ❌ TIDAK ADA PERMISSION!\n";
} else {
    foreach ($permissions as $permission) {
        echo "   ✅ {$permission}\n";
    }
}

echo "\n📊 Inventory Role Requirements:\n";
$inventoryRole = \Spatie\Permission\Models\Role::where('name', 'Inventory')->first();
if ($inventoryRole) {
    echo "   Permissions yang dibutuhkan:\n";
    foreach ($inventoryRole->permissions as $perm) {
        $hasIt = $user->hasPermissionTo($perm->name);
        $status = $hasIt ? '✅' : '❌';
        echo "   $status {$perm->name}\n";
    }
} else {
    echo "   ❌ Role 'Inventory' tidak ditemukan!\n";
}

echo "\n═══════════════════════════════════════════════════════════\n";
echo "\n💡 SOLUSI JIKA DAPAT 403 ERROR:\n\n";

if (!$roles->contains('Inventory')) {
    echo "   1. Run: php artisan user:assign-inventory-role {$user->email}\n";
    echo "   2. Atau run: php artisan db:seed\n";
    echo "   3. Refresh browser (Ctrl+F5 atau Cmd+Shift+R)\n";
} else {
    echo "   ✅ User sudah punya role Inventory. Cek permission di atas.\n";
}

echo "\n";
?>
