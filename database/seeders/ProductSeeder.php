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
        // Ambil divisi yang sudah di-seed oleh DivisionSeeder
        $divCatering     = Divisions::where('name', 'Catering')->first();
        $divCafeResto    = Divisions::where('name', 'Café & Resto')->first();
        $divPastryBakery = Divisions::where('name', 'Pastry Bakery')->first();

        // Ambil kategori yang sudah ada
        $catMakanan = Categories::where('name', 'Makanan')->where('type', 'product')->first();
        $catMinuman = Categories::where('name', 'Minuman')->where('type', 'product')->first();
        $catSnack   = Categories::where('name', 'Snack')->where('type', 'product')->first();
        $catRoti    = Categories::where('name', 'Roti')->where('type', 'product')->first();
        $catDessert = Categories::where('name', 'Dessert')->where('type', 'product')->first();

        $products = [
            // Catering: Makanan
            ['name' => 'Nasi Goreng',        'barcode' => '1001001', 'price' => 12000, 'category' => $catMakanan, 'division' => $divCatering],
            ['name' => 'Mie Goreng',          'barcode' => '1001002', 'price' => 11000, 'category' => $catMakanan, 'division' => $divCatering],
            ['name' => 'Ayam Goreng',         'barcode' => '1001003', 'price' => 15000, 'category' => $catMakanan, 'division' => $divCatering],
            ['name' => 'Soto Ayam',           'barcode' => '1001004', 'price' => 13000, 'category' => $catMakanan, 'division' => $divCatering],

            // Café & Resto: Minuman
            ['name' => 'Es Teh Manis',        'barcode' => '2001001', 'price' => 5000,  'category' => $catMinuman, 'division' => $divCafeResto],
            ['name' => 'Es Jeruk',             'barcode' => '2001002', 'price' => 6000,  'category' => $catMinuman, 'division' => $divCafeResto],
            ['name' => 'Jus Alpukat',          'barcode' => '2001003', 'price' => 10000, 'category' => $catMinuman, 'division' => $divCafeResto],
            ['name' => 'Air Mineral',          'barcode' => '2001004', 'price' => 3000,  'category' => $catMinuman, 'division' => $divCafeResto],

            // Catering: Snack
            ['name' => 'Risoles Mayo',         'barcode' => '3001001', 'price' => 4000,  'category' => $catSnack, 'division' => $divCatering],
            ['name' => 'Pastel Isi',           'barcode' => '3001002', 'price' => 4000,  'category' => $catSnack, 'division' => $divCatering],
            ['name' => 'Lumpia Basah',         'barcode' => '3001003', 'price' => 5000,  'category' => $catSnack, 'division' => $divCatering],

            // Pastry Bakery: Roti
            ['name' => 'Roti Coklat',          'barcode' => '4001001', 'price' => 6000,  'category' => $catRoti, 'division' => $divPastryBakery],
            ['name' => 'Roti Keju',            'barcode' => '4001002', 'price' => 7000,  'category' => $catRoti, 'division' => $divPastryBakery],
            ['name' => 'Roti Pisang',          'barcode' => '4001003', 'price' => 6000,  'category' => $catRoti, 'division' => $divPastryBakery],

            // Catering: Dessert
            ['name' => 'Puding Coklat',        'barcode' => '5001001', 'price' => 8000,  'category' => $catDessert, 'division' => $divCatering],
            ['name' => 'Es Krim Vanilla',      'barcode' => '5001002', 'price' => 10000, 'category' => $catDessert, 'division' => $divCatering],
        ];

        foreach ($products as $p) {
            if (! $p['category'] || ! $p['division']) {
                // Skip jika kategori/divisi tidak tersedia
                echo "⚠️  Skipping product: {$p['name']} - missing category or division\n";
                continue;
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
