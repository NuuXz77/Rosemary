<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            $table->enum('absence_type', ['none', 'sick', 'permit', 'leave', 'other', 'rescheduled'])
                ->default('none')
                ->after('status');
            $table->string('absence_note')->nullable()->after('absence_type');
            $table->unsignedBigInteger('replaced_from_schedule_id')->nullable()->after('absence_note');
            $table->unsignedBigInteger('replaced_by_schedule_id')->nullable()->after('replaced_from_schedule_id');

            $table->foreign('replaced_from_schedule_id')
                ->references('id')
                ->on('schedules')
                ->nullOnDelete();

            $table->foreign('replaced_by_schedule_id')
                ->references('id')
                ->on('schedules')
                ->nullOnDelete();

            $table->index('absence_type');
            $table->index('replaced_from_schedule_id');
            $table->index('replaced_by_schedule_id');
        });
    }

    public function down(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            $table->dropForeign(['replaced_from_schedule_id']);
            $table->dropForeign(['replaced_by_schedule_id']);

            $table->dropIndex(['absence_type']);
            $table->dropIndex(['replaced_from_schedule_id']);
            $table->dropIndex(['replaced_by_schedule_id']);

            $table->dropColumn([
                'absence_type',
                'absence_note',
                'replaced_from_schedule_id',
                'replaced_by_schedule_id',
            ]);
        });
    }
};