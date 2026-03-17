<?php

namespace Database\Seeders;

use App\Models\MaterialStockLogs;
use App\Models\MaterialStocks;
use App\Models\Materials;
use App\Models\User;
use Illuminate\Database\Seeder;

class MaterialStockLogsSeeder extends Seeder
{
    /**
     * Jalankan seeder untuk membuat sample log mutasi stok.
     * 
     * Usage: php artisan db:seed --class=MaterialStockLogsSeeder
     */
    public function run(): void
    {
        // Ambil user admin untuk creator_by
        $admin = User::role('admin')->first() ?? User::first();
        if (!$admin) {
            $this->command->warn('Tidak ada user ditemukan. Lewati seeder.');
            return;
        }

        // Ambil beberapa material yang memiliki stock
        $materials = Materials::with('stocks')->limit(5)->get();

        if ($materials->isEmpty()) {
            $this->command->warn('Tidak ada material dengan stock ditemukan. Lewati seeder.');
            return;
        }

        $logsCreated = 0;

        foreach ($materials as $material) {
            // Jika material belum punya stock, buat dulu
            if (!$material->stocks) {
                MaterialStocks::create([
                    'material_id' => $material->id,
                    'qty_available' => 100,
                ]);
            }

            // Buat sample log: Masuk (IN)
            MaterialStockLogs::create([
                'material_id' => $material->id,
                'type' => 'in',
                'qty' => 50,
                'description' => 'Pembelian dari supplier PT Contoh',
                'reference_type' => 'App\\Models\\PurchaseItem',
                'reference_id' => 1,
                'created_by' => $admin->id,
                'created_at' => now()->subDays(5),
            ]);
            $logsCreated++;

            // Buat sample log: Keluar (OUT)
            MaterialStockLogs::create([
                'material_id' => $material->id,
                'type' => 'out',
                'qty' => 20,
                'description' => 'Digunakan untuk produksi batch #001',
                'reference_type' => 'App\\Models\\Production',
                'reference_id' => 1,
                'created_by' => $admin->id,
                'created_at' => now()->subDays(3),
            ]);
            $logsCreated++;

            // Buat sample log: Penyesuaian (ADJUSTMENT)
            MaterialStockLogs::create([
                'material_id' => $material->id,
                'type' => 'adjustment',
                'qty' => 5,
                'description' => 'Koreksi selisih fisik vs sistem',
                'created_by' => $admin->id,
                'created_at' => now()->subDay(),
            ]);
            $logsCreated++;
        }

        $this->command->info("✅ Berhasil membuat {$logsCreated} sample log mutasi stok material!");
    }
}
