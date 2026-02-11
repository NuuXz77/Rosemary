<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            ['username' => 'admin', 'password' => 'admin', 'role' => 'admin'],
            ['username' => 'production', 'password' => 'production', 'role' => 'production'],
            ['username' => 'inventory', 'password' => 'inventory', 'role' => 'inventory'],
            ['username' => 'cashier', 'password' => 'cashier', 'role' => 'cashier'],
        ];

        foreach ($users as $data) {
            $user = User::firstOrCreate(
                ['username' => $data['username']],
                [
                    'password' => bcrypt($data['password']),
                    'is_active' => true,
                ]
            );

            $role = Role::where('name', $data['role'])->first();
            if ($role && !$user->hasRole($role->name)) {
                $user->assignRole($role->name);
            }
        }
    }
}
