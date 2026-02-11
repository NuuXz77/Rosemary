<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Customer Model
 * 
 * Menyimpan data pelanggan untuk transaksi penjualan.
 * Bersifat optional jika pelanggan merupakan pelanggan umum (tidak terdaftar).
 */
class Customers extends Model
{
    // Tentukan nama table jika berbeda dari convention (production: gunakan Customer)
    protected $table = 'customers';

    protected $fillable = [
        'name',    // Nama pelanggan
        'phone',   // Nomor telepon
        'email',   // Email pelanggan
        'address', // Alamat pelanggan
        'status',  // Boolean: aktif/nonaktif
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    /**
     * Relasi One-to-Many ke Sales
     * Satu pelanggan bisa punya banyak transaksi penjualan
     */
    public function sales(): HasMany
    {
        return $this->hasMany(Sales::class);
    }
}
