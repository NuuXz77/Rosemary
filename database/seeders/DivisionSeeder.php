<?php

namespace Database\Seeders;

use App\Models\Divisions;
use Illuminate\Database\Seeder;

class DivisionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Divisions::query()->delete();

        $divisions = [
            ['name' => 'Catering', 'type' => 'production', 'status' => true],
            ['name' => 'Café & Resto', 'type' => 'cashier', 'status' => true],
            ['name' => 'Pastry Bakery', 'type' => 'production', 'status' => true],
        ];

        foreach ($divisions as $division) {
            Divisions::create($division);
        }
    }
}
