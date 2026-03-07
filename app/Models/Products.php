<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Product Model
 * 
 * Data master semua produk yang dijual.
 * Setiap produk diproduksi oleh divisi tertentu.
 */
class Products extends Model
{
    protected $fillable = [
        'category_id',  // FK ke categories
        'division_id',  // FK ke divisions (divisi yang produksi)
        'name',         // Nama produk
        'barcode',      // Kode barcode produk
        'price',        // Harga jual
        'status',       // Boolean: aktif/nonaktif
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
     * Relasi Many-to-One ke Divisions
     */
    public function division(): BelongsTo
    {
        return $this->belongsTo(Divisions::class);
    }

    /**
     * Relasi One-to-One ke Product Stock
     * Satu produk hanya punya satu record stok
     */
    public function stock(): HasOne
    {
        return $this->hasOne(ProductStocks::class);
    }

    /**
     * Relasi One-to-Many ke Product Stock Logs
     * Record audit setiap perubahan stok
     */
    public function stockLogs(): HasMany
    {
        return $this->hasMany(ProductStockLogs::class);
    }

    /**
     * Relasi Many-to-Many ke Materials (via product_materials)
     * Resep: bahan-bahan yang diperlukan untuk membuat produk ini
     */
    public function materials(): BelongsToMany
    {
        return $this->belongsToMany(Materials::class, 'product_materials', 'product_id', 'material_id')
            ->withPivot('qty_used');
    }

    /**
     * Relasi One-to-Many ke Productions
     * Record setiap kali produk ini diproduksi
     */
    public function productions(): HasMany
    {
        return $this->hasMany(Productions::class);
    }

    /**
     * Relasi One-to-Many ke SaleItems
     * Setiap kali produk ini terjual
     */
    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItems::class);
    }

    /**
     * Relasi One-to-Many ke ProductWastes
     * Catatan produk terbuang
     */
    public function productWastes(): HasMany
    {
        return $this->hasMany(ProductWastes::class, 'product_id');
    }
}
