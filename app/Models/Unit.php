<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Unit Model
 * 
 * Menyimpan daftar satuan ukuran yang digunakan dalam sistem.
 * Contoh: kg, pcs, liter, box, buah, dll.
 */
class Unit extends Model
{
    protected $fillable = [
        'name',    // Nama satuan (unique)
        'status',  // Boolean: aktif/nonaktif
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    /**
     * Relasi One-to-Many ke Materials
     * Satu satuan bisa digunakan oleh banyak material
     */
    public function materials(): HasMany
    {
        return $this->hasMany(Materials::class);
    }
}
