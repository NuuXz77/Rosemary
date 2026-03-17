<?php

namespace Database\Seeders;

use App\Models\Suppliers;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $suppliers = [
            ['name' => 'PT Indofood', 'phone' => '081234567890', 'description' => 'Supplier aneka tepung dan bumbu', 'status' => 'sering'],
            ['name' => 'Toko Plastik Jaya', 'phone' => '081298765432', 'description' => 'Supplier kemasan dan cup', 'status' => 'sering'],
            ['name' => 'Sayur Segar Abadi', 'phone' => '085612312312', 'description' => 'Supplier sayuran segar', 'status' => 'sedang'],
            ['name' => 'Sumber Minuman', 'phone' => '087755556666', 'description' => 'Supplier teh, kopi, syrup', 'status' => 'sering'],
            ['name' => 'Bintang Daging', 'phone' => '089988887777', 'description' => 'Supplier daging ayam dan sapi', 'status' => 'jarang'],
        ];

        foreach ($suppliers as $item) {
            Suppliers::firstOrCreate(
                ['name' => $item['name']],
                $item
            );
        }
    }
}
