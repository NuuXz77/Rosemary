<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Production Model
 * 
 * Record setiap kali aktivitas produksi dilakukan.
 * Mencatat produk apa, kelompok mana, shift apa, berapa jumlah, dan tanggal berapa.
 * Status: 'draft' (sedang) atau 'completed' (selesai).
 */
class Productions extends Model
{
    protected $fillable = [
        'product_id',       // FK ke products (produk apa yang diproduksi)
        'student_group_id', // FK ke student_groups (kelompok mana yang produksi)
        'shift_id',         // FK ke shifts (shift mana produksi dilakukan)
        'qty_produced',     // Jumlah rencana produksi
        'actual_qty',       // Jumlah riil yang berhasil (setelah finalize)
        'production_date',  // Tanggal produksi
        'status',           // Enum: 'draft', 'completed'
        'created_by',       // FK ke users (user yang input/validasi)
    ];

    protected $casts = [
        'production_date' => 'date',
    ];

    /**
     * Relasi Many-to-One ke Products
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Products::class, 'product_id');
    }

    /**
     * Relasi Many-to-One ke StudentGroups
     */
    public function studentGroup(): BelongsTo
    {
        return $this->belongsTo(StudentGroups::class, 'student_group_id');
    }

    /**
     * Relasi Many-to-One ke Shifts
     */
    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    /**
     * Relasi Many-to-One ke Users
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relasi One-to-Many ke ProductWastes
     */
    public function wastes(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ProductWastes::class, 'production_id');
    }
}
