<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Sale Model
 * 
 * Header/induk dari transaksi penjualan di kasir.
 * Hanya Students (siswa kasir) yang bisa melakukan transaksi di POS.
 * Pelanggan bersifat optional untuk transaksi dengan pelanggan umum (tidak terdaftar).
 */
class Sales extends Model
{
    protected $fillable = [
        'invoice_number',    // Nomor invoice unik
        'customer_id',       // FK ke customers (optional, untuk pelanggan umum)
        'guest_name',        // Nama pembeli jika Guest (tidak terdaftar)
        'shift_id',          // FK ke shifts (shift saat transaksi)
        'cashier_student_id',// FK ke students (siswa kasir)
        'subtotal',          // Subtotal belanja
        'tax_amount',        // Jumlah pajak
        'discount_amount',   // Jumlah diskon
        'total_amount',      // Total akhir (subtotal - discount + tax)
        'paid_amount',       // Jumlah yang dibayar
        'change_amount',     // Kembalian
        'payment_method',    // Enum: 'cash', 'qris', 'transfer'
        'status',            // Enum: 'paid', 'cancelled'
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    /**
     * Relasi Many-to-One ke Customers
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customers::class);
    }

    /**
     * Relasi Many-to-One ke Shifts
     */
    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    /**
     * Relasi Many-to-One ke Students (cashier_student_id)
     */
    public function cashier(): BelongsTo
    {
        return $this->belongsTo(Students::class, 'cashier_student_id');
    }

    /**
     * Relasi One-to-Many ke SaleItems
     * Detail produk yang terjual dalam transaksi ini
     */
    public function items(): HasMany
    {
        return $this->hasMany(SaleItems::class);
    }
}
