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
        Schema::create('material_stock_logs', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->unsignedBigInteger('material_id'); // Foreign key ke materials
            $table->enum('type', ['in', 'out', 'adjustment']); // Type: in (masuk), out (keluar), adjustment (penyesuaian)
            $table->decimal('qty', 15, 2); // Jumlah perubahan
            $table->string('description'); // Deskripsi perubahan
            $table->string('reference_type')->nullable(); // Referensi model (e.g., 'App\\Models\\PurchaseItem')
            $table->unsignedBigInteger('reference_id')->nullable(); // ID referensi (e.g., purchase_items.id)
            $table->unsignedBigInteger('created_by')->nullable(); // FK ke users yang membuat log
            $table->timestamps(); // created_at, updated_at
            
            // Foreign key constraints
            $table->foreign('material_id')->references('id')->on('materials')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            
            // Index
            $table->index('material_id');
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
        Schema::dropIfExists('material_stock_logs');
    }
};
