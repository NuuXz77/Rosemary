<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            // Add type column
            $table->string('type')->default('production')->after('date');

            // Add student_id for cashier type (nullable)
            $table->unsignedBigInteger('student_id')->nullable()->after('type');
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->index('student_id');

            // Drop old unique constraint before changing columns
            $table->dropUnique(['date', 'shift_id', 'student_group_id', 'division_id']);

            // Drop FKs so we can modify the columns
            $table->dropForeign(['student_group_id']);
            $table->dropForeign(['division_id']);

            // Make nullable
            $table->unsignedBigInteger('student_group_id')->nullable()->change();
            $table->unsignedBigInteger('division_id')->nullable()->change();

            // Re-add FKs
            $table->foreign('student_group_id')->references('id')->on('student_groups')->onDelete('cascade');
            $table->foreign('division_id')->references('id')->on('divisions')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            $table->dropForeign(['student_id']);
            $table->dropIndex(['student_id']);
            $table->dropColumn(['type', 'student_id']);

            $table->dropForeign(['student_group_id']);
            $table->dropForeign(['division_id']);

            $table->unsignedBigInteger('student_group_id')->nullable(false)->change();
            $table->unsignedBigInteger('division_id')->nullable(false)->change();

            $table->foreign('student_group_id')->references('id')->on('student_groups')->onDelete('cascade');
            $table->foreign('division_id')->references('id')->on('divisions')->onDelete('cascade');

            $table->unique(['date', 'shift_id', 'student_group_id', 'division_id']);
        });
    }
};
