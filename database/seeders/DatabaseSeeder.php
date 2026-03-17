<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            CategoryPermissionSeeder::class,
            PermissionSeeder::class,
            RoleSeeder::class,
            UserSeeder::class,

                // Master data — urutan penting (FK chain)
            UnitSeeder::class,        // independent
            CategorySeeder::class,    // independent
            ProductSeeder::class,     // butuh categories + divisions (auto-created di dalam)
            StockSeeder::class,       // butuh products

                // Class & Student data
            ClassSeeder::class,       // independent
            StudentSeeder::class,     // butuh classes
        ]);
    }
}
