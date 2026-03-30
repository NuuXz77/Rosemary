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
        Schema::table('student_groups', function (Blueprint $table) {
            $table->string('group_code', 30)->nullable()->after('name');
        });

        $groups = DB::table('student_groups')
            ->select('id', 'class_id', 'name')
            ->orderBy('id')
            ->get();

        foreach ($groups as $group) {
            $suffix = str_contains((string) $group->name, 'Kelompok B') ? 'b' : 'a';
            $code = 'grp' . $group->class_id . $suffix;

            $existing = DB::table('student_groups')
                ->where('group_code', $code)
                ->where('id', '!=', $group->id)
                ->exists();

            if ($existing) {
                $code = 'grp' . $group->id;
            }

            DB::table('student_groups')
                ->where('id', $group->id)
                ->update(['group_code' => $code]);
        }

        Schema::table('student_groups', function (Blueprint $table) {
            $table->unique('group_code', 'student_groups_group_code_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_groups', function (Blueprint $table) {
            $table->dropUnique('student_groups_group_code_unique');
            $table->dropColumn('group_code');
        });
    }
};
