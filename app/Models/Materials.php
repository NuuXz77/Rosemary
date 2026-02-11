<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Material Model
 * 
 * Data master semua bahan baku/material yang digunakan dalam produksi.
 */
class Materials extends Model
{
    protected $fillable = [
        'category_id',    // FK ke categories
        'unit_id',        // FK ke units
        'supplier_id',    // FK ke suppliers (optional)
        'name',           // Nama material
        'minimum_stock',  // Stok minimum untuk alert
        'status',         // Boolean: aktif/nonaktif
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    /**
     * Relasi Many-to-One ke Categories
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Categories::class);
    }

    /**
     * Relasi Many-to-One ke Units
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    /**
     * Relasi Many-to-One ke Suppliers
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Suppliers::class);
    }

    /**
     * Relasi One-to-One ke Material Stock
     * Satu material hanya punya satu record stok
     */
    public function stock(): HasOne
    {
        return $this->hasOne(MaterialStocks::class);
    }

    /**
     * Relasi One-to-Many ke Material Stock Logs
     * Record audit setiap perubahan stok
     */
    public function stockLogs(): HasMany
    {
        return $this->hasMany(MaterialStockLogs::class);
    }

    /**
     * Relasi Many-to-Many ke Products (via product_materials)
     * Satu material bisa digunakan di banyak resep produk
     */
    public function products()
    {
        return $this->belongsToMany(Products::class, 'product_materials', 'material_id', 'product_id')
                    ->withPivot('qty_used');
    }
}
