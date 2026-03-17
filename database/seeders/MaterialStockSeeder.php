<?php

namespace Database\Seeders;

use App\Models\Materials;
use App\Models\MaterialStocks;
use Illuminate\Database\Seeder;

class MaterialStockSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Beri initial stok untuk setiap material (di atas batas minimum)
        Materials::all()->each(function (Materials $material) {
            $minV = $material->minimum_stock;
            $startQty = $minV + rand(10, 50); // stok selalu lebih dari batas minimum
            
            MaterialStocks::firstOrCreate(
                ['material_id' => $material->id],
                ['qty_available' => $startQty]
            );
        });
    }
}
