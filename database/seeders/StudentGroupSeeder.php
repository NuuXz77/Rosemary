<?php

namespace Database\Seeders;

use App\Models\Classes;
use App\Models\Divisions;
use App\Models\StudentGroups;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class StudentGroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $classes = Classes::where('status', true)
            ->orderBy('name')
            ->get();

        $divisions = Divisions::where('status', true)
            ->where('type', 'production')
            ->orderBy('name')
            ->get();

        $labels = ['A', 'B'];
        $productionRole = Role::where('name', 'Production')->first();
        $credentialRows = [
            'group_name,group_code,username,password,status',
        ];

        foreach ($classes as $classIndex => $class) {
            foreach ($labels as $labelIndex => $label) {
                $groupCode = 'grp' . $class->id . strtolower($label);
                $division = $divisions->isNotEmpty()
                    ? $divisions[($classIndex + $labelIndex) % $divisions->count()]
                    : null;

                $group = StudentGroups::updateOrCreate(
                    [
                        'class_id' => $class->id,
                        'name' => "{$class->name} - Kelompok {$label}",
                    ],
                    [
                        'group_code' => $groupCode,
                        'division_id' => $division?->id,
                        'status' => true,
                    ]
                );

                $plainPassword = Str::random(10);

                $user = User::firstOrNew(['username' => $groupCode]);
                $isNew = !$user->exists;

                $user->password = Hash::make($plainPassword);
                $user->is_active = true;
                $user->save();

                if ($productionRole) {
                    $user->syncRoles([$productionRole->name]);
                }

                $credentialRows[] = implode(',', [
                    '"' . str_replace('"', '""', $group->name) . '"',
                    $groupCode,
                    $groupCode,
                    $plainPassword,
                    $isNew ? 'created' : 'updated',
                ]);
            }
        }

        Storage::disk('local')->put(
            'seeded-group-production-accounts.csv',
            implode(PHP_EOL, $credentialRows)
        );
    }
}
