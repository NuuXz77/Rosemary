<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * MaterialWastes Model
 * 
 * Mencatat bahan yang terbuang karena rusak, kedaluwarsa, atau kegagalan produksi.
 */
class MaterialWastes extends Model
{
    protected $fillable = [
        'material_id', // FK ke materials
        'production_id', // FK ke productions (jika waste terjadi saat produksi)
        'qty',         // Jumlah yang terbuang
        'reason',      // Alasan (expired, damaged, spilled, dsb)
        'waste_date',  // Tanggal kejadian
        'created_by',  // User yang mencatat
    ];

    protected $casts = [
        'waste_date' => 'date',
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

    /**
     * Relasi Many-to-One ke Productions
     */
    public function production(): BelongsTo
    {
        return $this->belongsTo(Productions::class, 'production_id');
    }

    /**
     * Boot function untuk menghandle pengurangan stok otomatis
     */
    protected static function booted()
    {
        static::created(function ($waste) {
            // Jika limbah terjadi saat PRODUKSI, stok SUDAH dipotong oleh modul Produksi (rencana).
            // Kita hanya potong otomatis jika ini adalah limbah MANDIRI (non-produksi).
            if (!$waste->production_id) {
                // Kurangi stok di MaterialStocks
                $stock = MaterialStocks::where('material_id', $waste->material_id)->first();
                if ($stock) {
                    $stock->decrement('qty_available', $waste->qty);
                }

                // Catat di MaterialStockLogs
                MaterialStockLogs::create([
                    'material_id' => $waste->material_id,
                    'type' => 'out',
                    'qty' => -$waste->qty,
                    'description' => "Limbah (Mandiri): {$waste->reason}",
                    'reference_type' => self::class,
                    'reference_id' => $waste->id,
                    'created_by' => $waste->created_by,
                ]);
            }
        });
    }
}
