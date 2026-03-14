<?php

namespace Database\Seeders;

use App\Models\Classes;
use App\Models\Students;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StudentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $classes = Classes::all();

        // Nama-nama Indonesia untuk siswa
        $firstNames = [
            'Andi', 'Budi', 'Citra', 'Dewi', 'Eka',
            'Farah', 'Gilang', 'Hana', 'Indra', 'Joko',
            'Kartika', 'Lina', 'Maya', 'Nova', 'Omar',
            'Putri', 'Qori', 'Rina', 'Siti', 'Tari',
            'Umar', 'Vina', 'Wulan', 'Yani', 'Zahra'
        ];

        $lastNames = [
            'Pratama', 'Saputra', 'Wulandari', 'Permata', 'Santoso',
            'Wijaya', 'Kusuma', 'Maharani', 'Putra', 'Sari',
            'Hartono', 'Lestari', 'Nugroho', 'Rahayu', 'Setiawan',
            'Handayani', 'Gunawan', 'Hidayat', 'Nurjannah', 'Kurniawan'
        ];

        $pinCounter = 1000; // Start PIN from 1000

        foreach ($classes as $class) {
            for ($i = 0; $i < 5; $i++) {
                $firstName = $firstNames[array_rand($firstNames)];
                $lastName = $lastNames[array_rand($lastNames)];
                
                Students::create([
                    'pin' => str_pad($pinCounter, 4, '0', STR_PAD_LEFT),
                    'name' => "{$firstName} {$lastName}",
                    'class_id' => $class->id,
                    'status' => true,
                ]);

                $pinCounter++;
            }
        }
    }
}
