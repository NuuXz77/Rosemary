<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * ProductWastes Model
 * 
 * Mencatat produk jadi yang terbuang/rusak setelah diproduksi.
 */
class ProductWastes extends Model
{
    protected $fillable = [
        'product_id',    // FK ke products
        'production_id', // FK ke productions (jika waste terjadi saat produksi)
        'qty',           // Jumlah yang terbuang
        'reason',        // Alasan (expired, rejected, dsb)
        'waste_date',    // Tanggal kejadian
        'created_by',    // User yang mencatat
    ];

    protected $casts = [
        'waste_date' => 'date',
    ];

    /**
     * Relasi Many-to-One ke Products
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Products::class);
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
            // Kurangi stok di ProductStocks
            $stock = ProductStocks::where('product_id', $waste->product_id)->first();
            if ($stock) {
                $stock->decrement('qty_available', $waste->qty);
            }

            // Catat di ProductStockLogs
            ProductStockLogs::create([
                'product_id' => $waste->product_id,
                'type' => 'out',
                'qty' => -$waste->qty,
                'description' => "Product waste recorded: {$waste->reason} on " . $waste->waste_date->format('Y-m-d'),
                'reference_type' => self::class,
                'reference_id' => $waste->id,
                'created_by' => $waste->created_by,
            ]);
        });
    }
}
