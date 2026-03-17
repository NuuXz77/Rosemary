<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * SaleItem Model
 * 
 * Detail setiap produk yang terjual dalam satu transaksi penjualan.
 */
class SaleItems extends Model
{
    protected $fillable = [
        'sale_id',   // FK ke sales
        'product_id',// FK ke products
        'qty',       // Jumlah produk yang dibeli
        'price',     // Harga per satuan saat transaksi
        'subtotal',  // Subtotal (qty * price)
    ];

    /**
     * Relasi Many-to-One ke Sales
     */
    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sales::class, 'sale_id');
    }

    /**
     * Relasi Many-to-One ke Products
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Products::class, 'product_id');
    }
}
