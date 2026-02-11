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
        Schema::create('purchases', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->unsignedBigInteger('supplier_id'); // Foreign key ke suppliers
            $table->string('invoice_number')->unique(); // Nomor invoice unik
            $table->date('date'); // Tanggal pembelian
            $table->decimal('total_amount', 15, 2); // Total jumlah pembelian
            $table->enum('status', ['received', 'pending', 'cancelled'])->default('pending'); // Status pembelian
            $table->text('notes')->nullable(); // Catatan tambahan
            $table->unsignedBigInteger('created_by')->nullable(); // FK ke users yang membuat purchase (hanya user/staf)
            $table->timestamps(); // created_at, updated_at
            
            // Foreign key constraints
            $table->foreign('supplier_id')->references('id')->on('suppliers')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            
            // Index
            $table->index('supplier_id');
            $table->index('date');
            $table->index('status');
            $table->index('created_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchases');
    }
};
