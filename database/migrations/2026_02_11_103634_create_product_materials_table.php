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
        Schema::create('product_materials', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->unsignedBigInteger('product_id'); // Foreign key ke products
            $table->unsignedBigInteger('material_id'); // Foreign key ke materials
            $table->decimal('qty_used', 15, 2); // Jumlah material yang diperlukan per 1 unit produk
            $table->timestamps(); // created_at, updated_at
            
            // Foreign key constraints
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('material_id')->references('id')->on('materials')->onDelete('cascade');
            
            // Unique: satu material hanya bisa 1x dalam resep produk
            $table->unique(['product_id', 'material_id']);
            
            // Index
            $table->index('product_id');
            $table->index('material_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_materials');
    }
};
