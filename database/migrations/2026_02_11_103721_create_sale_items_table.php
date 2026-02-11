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
        Schema::create('sale_items', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->unsignedBigInteger('sale_id'); // FK ke sales
            $table->unsignedBigInteger('product_id'); // FK ke products
            $table->integer('qty'); // Jumlah produk yang dibeli
            $table->decimal('price', 15, 2); // Harga per satuan saat transaksi
            $table->decimal('subtotal', 15, 2); // Subtotal (qty * price)
            $table->timestamps(); // created_at, updated_at
            
            // Foreign key constraints
            $table->foreign('sale_id')->references('id')->on('sales')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            
            // Index
            $table->index('sale_id');
            $table->index('product_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_items');
    }
};
