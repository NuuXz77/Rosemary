<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;

class AssignInventoryRole extends Command
{
    protected $signature = 'user:assign-inventory-role {email?}';
    protected $description = 'Assign Inventory role to a user for accessing inventory features';

    public function handle()
    {
        $email = $this->argument('email') ?? $this->ask('Enter user email');

        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error("❌ User dengan email '{$email}' tidak ditemukan!");
            return 1;
        }

        // Pastikan Inventory role ada
        $inventoryRole = Role::where('name', 'Inventory')->first();
        if (!$inventoryRole) {
            $this->error('❌ Role "Inventory" tidak ditemukan. Jalankan: php artisan db:seed');
            return 1;
        }

        // Assign role
        if ($user->hasRole('Inventory')) {
            $this->info("✅ User '{$user->name}' sudah memiliki role Inventory!");
            return 0;
        }

        $user->assignRole('Inventory');

        $this->info("✅ Berhasil assign role Inventory ke user '{$user->name}'!");
        $this->info("📋 Permissions yang didapat:");
        
        foreach ($inventoryRole->permissions as $permission) {
            $this->line("   • {$permission->name}");
        }

        return 0;
    }
}
