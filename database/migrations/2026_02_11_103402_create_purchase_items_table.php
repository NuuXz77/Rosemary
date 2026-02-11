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
        Schema::create('purchase_items', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->unsignedBigInteger('purchase_id'); // Foreign key ke purchases
            $table->unsignedBigInteger('material_id'); // Foreign key ke materials
            $table->decimal('qty', 15, 2); // Jumlah pembelian
            $table->decimal('price', 15, 2); // Harga per satuan
            $table->decimal('subtotal', 15, 2); // Subtotal (qty * price)
            $table->timestamps(); // created_at, updated_at
            
            // Foreign key constraints
            $table->foreign('purchase_id')->references('id')->on('purchases')->onDelete('cascade');
            $table->foreign('material_id')->references('id')->on('materials')->onDelete('cascade');
            
            // Index
            $table->index('purchase_id');
            $table->index('material_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_items');
    }
};
