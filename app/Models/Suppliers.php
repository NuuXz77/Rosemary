<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Supplier Model
 * 
 * Data pemasok/supplier untuk pembelian material.
 * Kolom 'status' untuk segmentasi supplier (sering/sedang/jarang).
 */
class Suppliers extends Model
{
    // Tentukan nama table jika berbeda dari convention (production: gunakan Supplier)
    protected $table = 'suppliers';

    protected $fillable = [
        'name',        // Nama supplier
        'phone',       // Nomor telepon
        'description', // Deskripsi supplier
        'status',      // Enum: 'sering', 'sedang', 'jarang'
    ];

    /**
     * Relasi One-to-Many ke Materials
     * Satu supplier bisa supplying banyak material
     */
    public function materials(): HasMany
    {
        return $this->hasMany(Materials::class, 'supplier_id');
    }

    /**
     * Relasi One-to-Many ke Purchases
     * Satu supplier bisa punya banyak purchase order
     */
    public function purchases(): HasMany
    {
        return $this->hasMany(Purchases::class, 'supplier_id');
    }
}
