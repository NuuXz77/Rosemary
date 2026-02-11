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
        Schema::create('materials', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->unsignedBigInteger('category_id'); // Foreign key ke categories
            $table->unsignedBigInteger('unit_id'); // Foreign key ke units
            $table->unsignedBigInteger('supplier_id')->nullable(); // Foreign key ke suppliers (optional)
            $table->string('name'); // Nama material/bahan
            $table->decimal('minimum_stock', 15, 2); // Stok minimum untuk alert
            $table->boolean('status')->default(true); // Status aktif/nonaktif
            $table->timestamps(); // created_at, updated_at
            
            // Foreign key constraints
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
            $table->foreign('unit_id')->references('id')->on('units')->onDelete('cascade');
            $table->foreign('supplier_id')->references('id')->on('suppliers')->onDelete('set null');
            
            // Index
            $table->index('category_id');
            $table->index('unit_id');
            $table->index('supplier_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('materials');
    }
};
