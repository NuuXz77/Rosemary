<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Bersihkan duplikat student_id (jika ada), simpan baris id terkecil
        $duplicateStudentIds = DB::table('student_group_members')
            ->select('student_id')
            ->groupBy('student_id')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('student_id');

        foreach ($duplicateStudentIds as $studentId) {
            $keepId = DB::table('student_group_members')
                ->where('student_id', $studentId)
                ->min('id');

            DB::table('student_group_members')
                ->where('student_id', $studentId)
                ->where('id', '!=', $keepId)
                ->delete();
        }

        Schema::table('student_group_members', function (Blueprint $table) {
            $table->unique('student_id', 'student_group_members_student_id_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_group_members', function (Blueprint $table) {
            $table->dropUnique('student_group_members_student_id_unique');
        });
    }
};
