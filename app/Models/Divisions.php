<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Division Model
 * 
 * Area kerja atau divisi dalam sistem.
 * Bisa divisi layanan (cashier) atau divisi produksi (makanan, roti, minuman, dll).
 */
class Divisions extends Model
{
    protected $fillable = [
        'name',   // Nama divisi
        'type',   // Enum: 'cashier' (layanan) atau 'production' (produksi)
        'status', // Boolean: aktif/nonaktif
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    /**
     * Relasi One-to-Many ke Products
     * Satu divisi bisa produce banyak produk
     */
    public function products(): HasMany
    {
        return $this->hasMany(Products::class);
    }

    /**
     * Relasi One-to-Many ke Schedules
     * Satu divisi bisa punya banyak jadwal
     */
    public function schedules(): HasMany
    {
        return $this->hasMany(Schedules::class);
    }
}
