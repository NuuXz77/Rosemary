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
        Schema::create('student_group_members', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->unsignedBigInteger('student_group_id'); // Foreign key ke student_groups
            $table->unsignedBigInteger('student_id'); // Foreign key ke students
            $table->timestamps(); // created_at, updated_at
            
            // Foreign key constraints
            $table->foreign('student_group_id')->references('id')->on('student_groups')->onDelete('cascade');
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            
            // Unique constraint: satu siswa hanya bisa di satu kelompok
            $table->unique(['student_group_id', 'student_id']);
            
            // Index
            $table->index('student_group_id');
            $table->index('student_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_group_members');
    }
};
