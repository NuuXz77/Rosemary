<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Shift Model
 * 
 * Mendefinisikan periode shift kerja dalam sehari.
 * Contoh: Pagi (07:00-15:00), Siang (15:00-23:00), Malam (23:00-07:00).
 */
class Shift extends Model
{
    protected $table = 'shifts';

    protected $fillable = [
        'name',       // Nama shift
        'start_time', // Jam mulai (time)
        'end_time',   // Jam selesai (time)
        'status',     // Boolean: aktif/nonaktif
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'status' => 'boolean',
    ];

    /**
     * Relasi One-to-Many ke Schedules
     * Satu shift bisa punya banyak jadwal
     */
    public function schedules(): HasMany
    {
        return $this->hasMany(Schedules::class);
    }

    /**
     * Relasi One-to-Many ke Productions
     * Satu shift bisa punya banyak produksi
     */
    public function productions(): HasMany
    {
        return $this->hasMany(Productions::class);
    }

    /**
     * Relasi One-to-Many ke Sales
     * Satu shift bisa punya banyak transaksi penjualan
     */
    public function sales(): HasMany
    {
        return $this->hasMany(Sales::class);
    }
}
