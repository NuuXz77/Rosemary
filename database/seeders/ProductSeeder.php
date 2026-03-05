<?php

namespace Database\Seeders;

use App\Models\Categories;
use App\Models\Divisions;
use App\Models\Products;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        // Pastikan divisi tersedia (firstOrCreate supaya aman re-run)
        $divMakanan   = Divisions::firstOrCreate(['name' => 'Makanan'],  ['type' => 'production', 'status' => true]);
        $divMinuman   = Divisions::firstOrCreate(['name' => 'Minuman'],  ['type' => 'production', 'status' => true]);
        $divRoti      = Divisions::firstOrCreate(['name' => 'Roti'],     ['type' => 'production', 'status' => true]);
        $divKasir     = Divisions::firstOrCreate(['name' => 'Kasir'],    ['type' => 'cashier',    'status' => true]);

        // Ambil kategori yang sudah ada
        $catMakanan = Categories::where('name', 'Makanan')->where('type', 'product')->first();
        $catMinuman = Categories::where('name', 'Minuman')->where('type', 'product')->first();
        $catSnack   = Categories::where('name', 'Snack')->where('type', 'product')->first();
        $catRoti    = Categories::where('name', 'Roti')->where('type', 'product')->first();
        $catDessert = Categories::where('name', 'Dessert')->where('type', 'product')->first();

        $products = [
            // Makanan
            ['name' => 'Nasi Goreng',        'barcode' => '1001001', 'price' => 12000, 'category' => $catMakanan, 'division' => $divMakanan],
            ['name' => 'Mie Goreng',          'barcode' => '1001002', 'price' => 11000, 'category' => $catMakanan, 'division' => $divMakanan],
            ['name' => 'Ayam Goreng',         'barcode' => '1001003', 'price' => 15000, 'category' => $catMakanan, 'division' => $divMakanan],
            ['name' => 'Soto Ayam',           'barcode' => '1001004', 'price' => 13000, 'category' => $catMakanan, 'division' => $divMakanan],

            // Minuman
            ['name' => 'Es Teh Manis',        'barcode' => '2001001', 'price' => 5000,  'category' => $catMinuman, 'division' => $divMinuman],
            ['name' => 'Es Jeruk',             'barcode' => '2001002', 'price' => 6000,  'category' => $catMinuman, 'division' => $divMinuman],
            ['name' => 'Jus Alpukat',          'barcode' => '2001003', 'price' => 10000, 'category' => $catMinuman, 'division' => $divMinuman],
            ['name' => 'Air Mineral',          'barcode' => '2001004', 'price' => 3000,  'category' => $catMinuman, 'division' => $divMinuman],

            // Snack
            ['name' => 'Risoles Mayo',         'barcode' => '3001001', 'price' => 4000,  'category' => $catSnack, 'division' => $divMakanan],
            ['name' => 'Pastel Isi',           'barcode' => '3001002', 'price' => 4000,  'category' => $catSnack, 'division' => $divMakanan],
            ['name' => 'Lumpia Basah',         'barcode' => '3001003', 'price' => 5000,  'category' => $catSnack, 'division' => $divMakanan],

            // Roti
            ['name' => 'Roti Coklat',          'barcode' => '4001001', 'price' => 6000,  'category' => $catRoti, 'division' => $divRoti],
            ['name' => 'Roti Keju',            'barcode' => '4001002', 'price' => 7000,  'category' => $catRoti, 'division' => $divRoti],
            ['name' => 'Roti Pisang',          'barcode' => '4001003', 'price' => 6000,  'category' => $catRoti, 'division' => $divRoti],

            // Dessert
            ['name' => 'Puding Coklat',        'barcode' => '5001001', 'price' => 8000,  'category' => $catDessert, 'division' => $divMakanan],
            ['name' => 'Es Krim Vanilla',      'barcode' => '5001002', 'price' => 10000, 'category' => $catDessert, 'division' => $divMakanan],
        ];

        foreach ($products as $p) {
            if (! $p['category'] || ! $p['division']) {
                continue; // skip kalau kategori/divisi belum di-seed
            }

            Products::firstOrCreate(
                ['barcode' => $p['barcode']],
                [
                    'name'        => $p['name'],
                    'category_id' => $p['category']->id,
                    'division_id' => $p['division']->id,
                    'price'       => $p['price'],
                    'status'      => true,
                ]
            );
        }
    }
}
