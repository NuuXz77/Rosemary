<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * ProductMaterial Model (Resep/BOM)
 * 
 * Menyimpan resep/bahan yang diperlukan untuk membuat satu satuan produk.
 * Salah satu contoh: untuk membuat 1 buah Roti Tawar diperlukan 500g tepung, 250ml susu, dll.
 */
class ProductMaterials extends Model
{
    protected $fillable = [
        'product_id',  // FK ke products
        'material_id', // FK ke materials
        'qty_used',    // Jumlah material per 1 unit produk
    ];

    /**
     * Relasi Many-to-One ke Products
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Products::class);
    }

    /**
     * Relasi Many-to-One ke Materials
     */
    public function material(): BelongsTo
    {
        return $this->belongsTo(Materials::class);
    }
}
