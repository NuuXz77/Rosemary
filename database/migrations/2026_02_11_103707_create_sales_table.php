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
        Schema::create('sales', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->string('invoice_number')->unique(); // Nomor invoice unik
            $table->unsignedBigInteger('customer_id')->nullable(); // FK ke customers (optional, untuk pelanggan umum)
            $table->unsignedBigInteger('shift_id'); // FK ke shifts (shift saat transaksi)
            $table->unsignedBigInteger('cashier_student_id'); // FK ke students (siswa kasir)
            $table->decimal('subtotal', 15, 2); // Subtotal belanja
            $table->decimal('tax_amount', 15, 2)->default(0); // Jumlah pajak
            $table->decimal('discount_amount', 15, 2)->default(0); // Jumlah diskon
            $table->decimal('total_amount', 15, 2); // Total akhir (subtotal - discount + tax)
            $table->decimal('paid_amount', 15, 2); // Jumlah yang dibayar
            $table->decimal('change_amount', 15, 2); // Kembalian
            $table->enum('payment_method', ['cash', 'qris', 'transfer'])->default('cash'); // Metode pembayaran
            $table->enum('status', ['paid', 'cancelled'])->default('paid'); // Status transaksi
            $table->timestamps(); // created_at, updated_at
            
            // Foreign key constraints
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('set null');
            $table->foreign('shift_id')->references('id')->on('shifts')->onDelete('cascade');
            $table->foreign('cashier_student_id')->references('id')->on('students')->onDelete('cascade');
            
            // Index
            $table->index('customer_id');
            $table->index('shift_id');
            $table->index('cashier_student_id');
            $table->index('status');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
