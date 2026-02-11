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
        Schema::create('material_stocks', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->unsignedBigInteger('material_id'); // Foreign key ke materials
            $table->decimal('qty_available', 15, 2); // Jumlah stok yang tersedia
            $table->timestamps(); // created_at, updated_at
            
            // Foreign key constraint
            $table->foreign('material_id')->references('id')->on('materials')->onDelete('cascade');
            
            // Unique: satu material hanya punya satu record stock
            $table->unique('material_id');
            
            // Index
            $table->index('material_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('material_stocks');
    }
};
