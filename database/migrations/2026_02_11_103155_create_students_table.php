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
        Schema::create('students', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->string('pin')->unique(); // PIN untuk login POS
            $table->string('name'); // Nama lengkap siswa
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
        Schema::dropIfExists('students');
    }
};
