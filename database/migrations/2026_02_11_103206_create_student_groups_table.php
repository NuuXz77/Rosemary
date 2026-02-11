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
        Schema::create('student_groups', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->string('name'); // Nama kelompok (e.g., 'Kelompok A', 'Kelompok B')
            $table->unsignedBigInteger('class_id'); // Foreign key ke classes
            $table->boolean('status')->default(true); // Status aktif/nonaktif
            $table->timestamps(); // created_at, updated_at
            
            // Foreign key constraint
            $table->foreign('class_id')->references('id')->on('classes')->onDelete('cascade');
            
            // Index
            $table->index('class_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_groups');
    }
};
