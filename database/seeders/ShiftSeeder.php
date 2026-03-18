<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Shift;

class ShiftSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $shifts = [
            [
                'name' => 'Pagi',
                'start_time' => '07:00:00',
                'end_time' => '12:00:00',
                'tolerance' => 15,
                'status' => true,
            ],
            [
                'name' => 'Siang',
                'start_time' => '12:00:00',
                'end_time' => '17:00:00',
                'tolerance' => 15,
                'status' => true,
            ],
            [
                'name' => 'Sore',
                'start_time' => '17:00:00',
                'end_time' => '21:00:00',
                'tolerance' => 15,
                'status' => true,
            ],
        ];

        foreach ($shifts as $shift) {
            Shift::updateOrCreate(
                ['name' => $shift['name']],
                $shift
            );
        }
    }
}
