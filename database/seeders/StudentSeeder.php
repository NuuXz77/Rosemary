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
     * 
     * Dinamis: Jumlah siswa per kelas bisa dikonfigurasi.
     * Config: SEEDER_STUDENTS_PER_CLASS untuk jumlah siswa (default: 5)
     */
    public function run(): void
    {
        Students::query()->delete();

        $classes = Classes::all();
        $studentsPerClass = (int)env('SEEDER_STUDENTS_PER_CLASS', 5);

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

        $pinCounter = 1000;

        foreach ($classes as $class) {
            for ($i = 0; $i < $studentsPerClass; $i++) {
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
