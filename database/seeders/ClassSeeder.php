<?php

namespace Database\Seeders;

use App\Models\Classes;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ClassSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $classes = [
            ['name' => '10 KULINER 1', 'status' => true],
            ['name' => '10 KULINER 2', 'status' => true],
            ['name' => '11 KULINER 1', 'status' => true],
            ['name' => '11 KULINER 2', 'status' => true],
            ['name' => '12 KULINER 1', 'status' => true],
            ['name' => '12 KULINER 2', 'status' => true],
        ];

        foreach ($classes as $class) {
            Classes::create($class);
        }
    }
}
