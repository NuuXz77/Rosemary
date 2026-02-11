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
        Schema::create('product_stocks', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->unsignedBigInteger('product_id'); // Foreign key ke products
            $table->integer('qty_available'); // Jumlah stok yang tersedia
            $table->timestamps(); // created_at, updated_at
            
            // Foreign key constraint
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            
            // Unique: satu produk hanya punya satu record stock
            $table->unique('product_id');
            
            // Index
            $table->index('product_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_stocks');
    }
};
