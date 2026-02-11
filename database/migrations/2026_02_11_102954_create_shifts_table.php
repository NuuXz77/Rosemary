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
        Schema::create('shifts', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->string('name'); // Nama shift (e.g., 'Pagi', 'Siang', 'Malam')
            $table->time('start_time'); // Jam mulai shift
            $table->time('end_time'); // Jam selesai shift
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
        Schema::dropIfExists('shifts');
    }
};
