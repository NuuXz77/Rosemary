<?php

namespace Database\Seeders;

use App\Models\Products;
use App\Models\Materials;
use App\Models\ProductMaterials;
use Illuminate\Database\Seeder;

class ProductMaterialSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Resep makanan
        $nasiGoreng = Products::where('name', 'Nasi Goreng')->first();
        if ($nasiGoreng) {
            $bawangPutih = Materials::where('name', 'Bawang Putih')->first();
            $garam = Materials::where('name', 'Garam')->first();
            
            if ($bawangPutih) ProductMaterials::firstOrCreate(['product_id' => $nasiGoreng->id, 'material_id' => $bawangPutih->id], ['qty_used' => 10]);
            if ($garam) ProductMaterials::firstOrCreate(['product_id' => $nasiGoreng->id, 'material_id' => $garam->id], ['qty_used' => 5]);
        }

        // Resep minuman
        $esTeh = Products::where('name', 'Es Teh Manis')->first();
        if ($esTeh) {
            $tehMelati = Materials::where('name', 'Teh Melati')->first();
            $gulaPasir = Materials::where('name', 'Gula Pasir')->first();
            $cup = Materials::where('name', 'Cup Plastik 16oz')->first();
            $sedotan = Materials::where('name', 'Sedotan Plastik')->first();

            if ($tehMelati) ProductMaterials::firstOrCreate(['product_id' => $esTeh->id, 'material_id' => $tehMelati->id], ['qty_used' => 0.05]); // asumsikan kg -> 50gr
            if ($gulaPasir) ProductMaterials::firstOrCreate(['product_id' => $esTeh->id, 'material_id' => $gulaPasir->id], ['qty_used' => 0.03]); // asumsikan kg -> 30gr
            if ($cup) ProductMaterials::firstOrCreate(['product_id' => $esTeh->id, 'material_id' => $cup->id], ['qty_used' => 1]);
            if ($sedotan) ProductMaterials::firstOrCreate(['product_id' => $esTeh->id, 'material_id' => $sedotan->id], ['qty_used' => 1]);
        }
    }
}
