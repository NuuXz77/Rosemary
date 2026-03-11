<?php

namespace Database\Seeders;

use App\Models\Products;
use App\Models\ProductStocks;
use Illuminate\Database\Seeder;

class StockSeeder extends Seeder
{
    public function run(): void
    {
        // Buat satu record stok untuk setiap produk yang belum punya stok
        Products::all()->each(function (Products $product) {
            ProductStocks::firstOrCreate(
                ['product_id' => $product->id],
                ['qty_available' => rand(20, 100)]
            );
        });
    }
}
