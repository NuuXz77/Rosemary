<?php

namespace Database\Seeders;

use App\Models\StudentGroupMembers;
use App\Models\StudentGroups;
use App\Models\Students;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StudentGroupMemberSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        StudentGroupMembers::query()->delete();

        $groupsByClass = StudentGroups::where('status', true)
            ->orderBy('class_id')
            ->orderBy('name')
            ->get()
            ->groupBy('class_id');

        $students = Students::where('status', true)
            ->orderBy('class_id')
            ->orderBy('name')
            ->get();

        $rotationIndex = [];

        foreach ($students as $student) {
            $classGroups = $groupsByClass->get($student->class_id);

            if (!$classGroups || $classGroups->isEmpty()) {
                continue;
            }

            $currentIndex = $rotationIndex[$student->class_id] ?? 0;
            $targetGroup = $classGroups[$currentIndex % $classGroups->count()];

            StudentGroupMembers::create([
                'student_group_id' => $targetGroup->id,
                'student_id' => $student->id,
            ]);

            $rotationIndex[$student->class_id] = $currentIndex + 1;
        }
    }
}
