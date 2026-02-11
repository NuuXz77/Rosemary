<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * MaterialStock Model
 * 
 * Tabel yang menyimpan jumlah stok tersedia untuk setiap material.
 * Satu material hanya punya satu record stok.
 */
class MaterialStocks extends Model
{
    protected $fillable = [
        'material_id',   // FK ke materials (unique)
        'qty_available', // Jumlah stok tersedia
    ];

    /**
     * Relasi Many-to-One ke Materials
     */
    public function material(): BelongsTo
    {
        return $this->belongsTo(Materials::class);
    }
}
