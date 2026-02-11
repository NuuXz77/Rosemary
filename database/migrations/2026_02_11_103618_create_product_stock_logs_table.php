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
        Schema::create('product_stock_logs', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->unsignedBigInteger('product_id'); // Foreign key ke products
            $table->enum('type', ['in', 'out', 'adjustment']); // Type: in (produksi masuk), out (terjual), adjustment (penyesuaian)
            $table->integer('qty'); // Jumlah perubahan
            $table->string('description'); // Deskripsi perubahan
            $table->string('reference_type')->nullable(); // Referensi model (e.g., 'App\\Models\\Production')
            $table->unsignedBigInteger('reference_id')->nullable(); // ID referensi (e.g., productions.id)
            $table->unsignedBigInteger('created_by')->nullable(); // FK ke users yang membuat log
            $table->timestamps(); // created_at, updated_at
            
            // Foreign key constraints
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            
            // Index
            $table->index('product_id');
            $table->index('type');
            $table->index('created_by');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_stock_logs');
    }
};
