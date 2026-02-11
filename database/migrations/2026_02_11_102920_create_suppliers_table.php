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
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->string('name'); // Nama supplier
            $table->string('phone')->nullable(); // Nomor telepon
            $table->text('description')->nullable(); // Deskripsi supplier
            $table->enum('status', ['sering', 'sedang', 'jarang'])->default('sedang'); // Frekuensi kerjasama
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
        Schema::dropIfExists('suppliers');
    }
};
