<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * ProductStock Model
 * 
 * Tabel yang menyimpan jumlah stok tersedia untuk setiap produk.
 * Satu produk hanya punya satu record stok.
 */
class ProductStocks extends Model
{
    protected $fillable = [
        'product_id',    // FK ke products (unique)
        'qty_available', // Jumlah stok tersedia
    ];

    /**
     * Relasi Many-to-One ke Products
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Products::class);
    }
}
