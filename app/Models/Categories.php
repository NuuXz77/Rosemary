<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Category Model
 * 
 * Mengelompokkan produk dan material ke dalam kategori tertentu.
 * Dengan kolom 'type' membedakan kategori untuk produk (jual) atau material (bahan baku).
 */
class Categories extends Model
{
    // Tentukan nama table jika berbeda dari convention (production: gunakan Category)
    protected $table = 'categories';

    protected $fillable = [
        'name',      // Nama kategori
        'type',      // Enum: 'product' atau 'material'
        'status',    // Boolean: aktif/nonaktif
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    /**
     * Relasi One-to-Many ke Products
     * Satu kategori bisa punya banyak produk
     */
    public function products(): HasMany
    {
        return $this->hasMany(Products::class, 'category_id');
    }

    /**
     * Relasi One-to-Many ke Materials
     * Satu kategori bisa punya banyak material
     */
    public function materials(): HasMany
    {
        return $this->hasMany(Materials::class, 'category_id');
    }
}
