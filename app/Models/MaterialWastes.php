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
        return $this->belongsTo(Materials::class);
    }

    /**
     * Relasi Many-to-One ke Users
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Boot function untuk menghandle pengurangan stok otomatis
     */
    protected static function booted()
    {
        static::created(function ($waste) {
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
                'description' => "Waste recorded: {$waste->reason} on " . $waste->waste_date->format('Y-m-d'),
                'reference_type' => self::class,
                'reference_id' => $waste->id,
                'created_by' => $waste->created_by,
            ]);
        });
    }
}
