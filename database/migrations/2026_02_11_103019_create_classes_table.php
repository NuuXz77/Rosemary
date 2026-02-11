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
        Schema::create('classes', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->string('name'); // Nama kelas (e.g., 'X AKL 1', 'XI RPL 2', 'XII BKP 1')
            $table->boolean('status')->default(true); // Status aktif/nonaktif
            $table->timestamps(); // created_at, updated_at
            
            // Index
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('classes');
    }
};
