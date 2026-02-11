<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('schedules', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->date('date'); // Tanggal jadwal
            $table->unsignedBigInteger('shift_id'); // Foreign key ke shifts
            $table->unsignedBigInteger('student_group_id'); // Foreign key ke student_groups
            $table->unsignedBigInteger('division_id'); // Foreign key ke divisions
            $table->boolean('status')->default(true); // Status aktif/nonaktif
            $table->timestamps(); // created_at, updated_at
            
            // Foreign key constraints
            $table->foreign('shift_id')->references('id')->on('shifts')->onDelete('cascade');
            $table->foreign('student_group_id')->references('id')->on('student_groups')->onDelete('cascade');
            $table->foreign('division_id')->references('id')->on('divisions')->onDelete('cascade');
            
            // Unique constraint: satu kelompok hanya bisa jadwal 1x per shift per divisi per hari
            $table->unique(['date', 'shift_id', 'student_group_id', 'division_id']);
            
            // Index
            $table->index('date');
            $table->index('shift_id');
            $table->index('student_group_id');
            $table->index('division_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};
