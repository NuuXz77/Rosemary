<?php

namespace Database\Seeders;

use App\Models\Categories;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            // Kategori Produk (type = product)
            ['name' => 'Makanan',   'type' => 'product'],
            ['name' => 'Minuman',   'type' => 'product'],
            ['name' => 'Snack',     'type' => 'product'],
            ['name' => 'Roti',      'type' => 'product'],
            ['name' => 'Dessert',   'type' => 'product'],

            // Kategori Material (type = material)
            ['name' => 'Tepung',    'type' => 'material'],
            ['name' => 'Bumbu',     'type' => 'material'],
            ['name' => 'Kemasan',   'type' => 'material'],
            ['name' => 'Minuman Material', 'type' => 'material'],
        ];

        foreach ($categories as $cat) {
            Categories::firstOrCreate(
                ['name' => $cat['name'], 'type' => $cat['type']],
                ['status' => true]
            );
        }
    }
}
