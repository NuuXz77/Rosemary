<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * MaterialStockLog Model
 * 
 * Catatan audit wajib untuk setiap perubahan stok material.
 * Terdapat tiga tipe: 'in' (masuk), 'out' (keluar), 'adjustment' (penyesuaian).
 * 
 * PENTING: Log ini TIDAK mengubah stok secara otomatis.
 * Koreksi stok harus dilakukan secara terpisah oleh aplikasi berdasarkan log ini.
 */
class MaterialStockLogs extends Model
{
    protected $fillable = [
        'material_id',    // FK ke materials
        'type',           // Enum: 'in', 'out', 'adjustment'
        'qty',            // Jumlah perubahan
        'description',    // Deskripsi perubahan
        'reference_type', // Model referensi (misal: 'App\\Models\\PurchaseItem')
        'reference_id',   // ID referensi
        'created_by',     // FK ke users (hanya user yang bisa create log)
    ];

    /**
     * Relasi Many-to-One ke Materials
     */
    public function material(): BelongsTo
    {
        return $this->belongsTo(Materials::class, 'material_id');
    }

    /**
     * Relasi Many-to-One ke Users
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
