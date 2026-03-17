<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * PurchaseItem Model
 * 
 * Detail setiap material yang dibeli dalam satu transaksi pembelian.
 */
class PurchaseItems extends Model
{
    protected $fillable = [
        'purchase_id', // FK ke purchases
        'material_id', // FK ke materials
        'qty',         // Jumlah pembelian
        'price',       // Harga per satuan
        'subtotal',    // Subtotal (qty * price)
    ];

    /**
     * Relasi Many-to-One ke Purchases
     */
    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchases::class, 'purchase_id');
    }

    /**
     * Relasi Many-to-One ke Materials
     */
    public function material(): BelongsTo
    {
        return $this->belongsTo(Materials::class, 'material_id');
    }
}
