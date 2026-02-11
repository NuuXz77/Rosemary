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
        Schema::create('productions', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->unsignedBigInteger('product_id'); // Foreign key ke products
            $table->unsignedBigInteger('student_group_id'); // Foreign key ke student_groups (kelompok yang produksi)
            $table->unsignedBigInteger('shift_id'); // Foreign key ke shifts (shift produksi)
            $table->integer('qty_produced'); // Jumlah produk yang diproduksi
            $table->date('production_date'); // Tanggal produksi
            $table->enum('status', ['draft', 'completed'])->default('draft'); // Status produksi
            $table->unsignedBigInteger('created_by')->nullable(); // FK ke users yang input/validasi
            $table->timestamps(); // created_at, updated_at
            
            // Foreign key constraints
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('student_group_id')->references('id')->on('student_groups')->onDelete('cascade');
            $table->foreign('shift_id')->references('id')->on('shifts')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            
            // Index
            $table->index('product_id');
            $table->index('student_group_id');
            $table->index('shift_id');
            $table->index('production_date');
            $table->index('status');
            $table->index('created_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('productions');
    }
};
