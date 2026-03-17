<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * ProductStockLog Model
 * 
 * Catatan audit wajib untuk setiap perubahan stok produk.
 * Terdapat tiga tipe: 'in' (produksi masuk), 'out' (terjual), 'adjustment' (penyesuaian).
 * 
 * PENTING: Log ini TIDAK mengubah stok secara otomatis.
 * Koreksi stok harus dilakukan secara terpisah oleh aplikasi berdasarkan log ini.
 */
class ProductStockLogs extends Model
{
    protected $fillable = [
        'product_id',    // FK ke products
        'type',          // Enum: 'in', 'out', 'adjustment'
        'qty',           // Jumlah perubahan
        'description',   // Deskripsi perubahan
        'reference_type',// Model referensi (misal: 'App\\Models\\Production')
        'reference_id',  // ID referensi
        'created_by',    // FK ke users (hanya user untuk log manual)
    ];

    /**
     * Relasi Many-to-One ke Products
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Products::class, 'product_id');
    }

    /**
     * Relasi Many-to-One ke Users
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
