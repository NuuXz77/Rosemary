<?php

namespace Database\Seeders;

use App\Models\Unit;
use Illuminate\Database\Seeder;

class UnitSeeder extends Seeder
{
    public function run(): void
    {
        $units = [
            'pcs',
            'porsi',
            'cup',
            'botol',
            'box',
            'kg',
            'gram',
            'liter',
            'ml',
            'loyang',
        ];

        foreach ($units as $name) {
            Unit::firstOrCreate(['name' => $name], ['status' => true]);
        }
    }
}
