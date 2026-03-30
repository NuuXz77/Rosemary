<?php

namespace Database\Seeders;

use App\Models\Classes;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ClassSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Dinamis: Bisa dikonfigurasi jumlah dan nama kelas lewat env atau hardcoded.
     * Config: SEEDER_CLASSES_COUNT untuk jumlah kelas (default: 6)
     */
    public function run(): void
    {
        Classes::query()->delete();

        $classesCount = (int)env('SEEDER_CLASSES_COUNT', 6);
        $levels = ['11']; // Tingkat/tahun
        $groups = ['1', '2']; // Kelompok per tingkat

        $classNumber = 1;
        foreach ($levels as $level) {
            foreach ($groups as $group) {
                if ($classNumber > $classesCount) {
                    break 2;
                }
                Classes::create([
                    'name' => "{$level} KULINER {$group}",
                    'status' => true,
                ]);
                $classNumber++;
            }
        }
    }
}
