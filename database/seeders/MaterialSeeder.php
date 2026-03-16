<?php

namespace Database\Seeders;

use App\Models\Materials;
use App\Models\Categories;
use App\Models\Unit;
use App\Models\Suppliers;
use Illuminate\Database\Seeder;

class MaterialSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $catTepung = Categories::where('name', 'Tepung')->where('type', 'material')->first();
        $catBumbu = Categories::where('name', 'Bumbu')->where('type', 'material')->first();
        $catKemasan = Categories::where('name', 'Kemasan')->where('type', 'material')->first();
        $catMinuman = Categories::where('name', 'Minuman Material')->where('type', 'material')->first();

        $unitKg = Unit::where('name', 'kg')->first();
        $unitGram = Unit::where('name', 'gram')->first();
        $unitLiter = Unit::where('name', 'liter')->first();
        $unitPcs = Unit::where('name', 'pcs')->first();
        $unitCup = Unit::where('name', 'cup')->first();

        $supIndofood = Suppliers::where('name', 'PT Indofood')->first();
        $supPlastik = Suppliers::where('name', 'Toko Plastik Jaya')->first();
        $supSayur = Suppliers::where('name', 'Sayur Segar Abadi')->first();
        $supMinum = Suppliers::where('name', 'Sumber Minuman')->first();

        $materials = [
            // Tepung
            ['name' => 'Tepung Terigu Segitiga', 'minimum_stock' => 10, 'category_id' => $catTepung->id ?? 1, 'unit_id' => $unitKg->id ?? 1, 'supplier_id' => $supIndofood->id ?? null],
            ['name' => 'Tepung Tapioka', 'minimum_stock' => 5, 'category_id' => $catTepung->id ?? 1, 'unit_id' => $unitKg->id ?? 1, 'supplier_id' => $supIndofood->id ?? null],
            
            // Bumbu & Sayuran
            ['name' => 'Bawang Putih', 'minimum_stock' => 2000, 'category_id' => $catBumbu->id ?? 1, 'unit_id' => $unitGram->id ?? 1, 'supplier_id' => $supSayur->id ?? null],
            ['name' => 'Bawang Merah', 'minimum_stock' => 2000, 'category_id' => $catBumbu->id ?? 1, 'unit_id' => $unitGram->id ?? 1, 'supplier_id' => $supSayur->id ?? null],
            ['name' => 'Garam', 'minimum_stock' => 2000, 'category_id' => $catBumbu->id ?? 1, 'unit_id' => $unitGram->id ?? 1, 'supplier_id' => $supIndofood->id ?? null],
            ['name' => 'Merica Bubuk', 'minimum_stock' => 500, 'category_id' => $catBumbu->id ?? 1, 'unit_id' => $unitGram->id ?? 1, 'supplier_id' => $supIndofood->id ?? null],
            
            // Kemasan
            ['name' => 'Cup Plastik 16oz', 'minimum_stock' => 100, 'category_id' => $catKemasan->id ?? 1, 'unit_id' => $unitCup->id ?? 1, 'supplier_id' => $supPlastik->id ?? null],
            ['name' => 'Sedotan Plastik', 'minimum_stock' => 500, 'category_id' => $catKemasan->id ?? 1, 'unit_id' => $unitPcs->id ?? 1, 'supplier_id' => $supPlastik->id ?? null],
            ['name' => 'Paper Bowl Besar', 'minimum_stock' => 100, 'category_id' => $catKemasan->id ?? 1, 'unit_id' => $unitPcs->id ?? 1, 'supplier_id' => $supPlastik->id ?? null],
            
            // Minuman Material
            ['name' => 'Teh Melati', 'minimum_stock' => 2, 'category_id' => $catMinuman->id ?? 1, 'unit_id' => $unitKg->id ?? 1, 'supplier_id' => $supMinum->id ?? null],
            ['name' => 'Gula Pasir', 'minimum_stock' => 10, 'category_id' => $catMinuman->id ?? 1, 'unit_id' => $unitKg->id ?? 1, 'supplier_id' => $supMinum->id ?? null],
            ['name' => 'Sirup Jeruk', 'minimum_stock' => 5, 'category_id' => $catMinuman->id ?? 1, 'unit_id' => $unitLiter->id ?? 1, 'supplier_id' => $supMinum->id ?? null],
            ['name' => 'Alpukat Mentega', 'minimum_stock' => 3, 'category_id' => $catMinuman->id ?? 1, 'unit_id' => $unitKg->id ?? 1, 'supplier_id' => $supSayur->id ?? null],
            ['name' => 'Biji Kopi Arabica', 'minimum_stock' => 2, 'category_id' => $catMinuman->id ?? 1, 'unit_id' => $unitKg->id ?? 1, 'supplier_id' => $supMinum->id ?? null],
        ];

        foreach ($materials as $item) {
            Materials::firstOrCreate(
                ['name' => $item['name']],
                $item
            );
        }
    }
}
