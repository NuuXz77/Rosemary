<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Permission\Traits\HasRoles;

/**
 * User Model
 * 
 * Menyimpan data akun staf/admin yang bisa mengakses sistem backend.
 * User berfungsi sebagai creator untuk transaksi pembelian, log stok, produksi, dll.
 * Role & permission dikelola menggunakan package spatie/laravel-permission.
 */
class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'username',        // Username unik untuk login
        'password',        // Password (di-hash otomatis)
        'terakhir_login',  // Timestamp login terakhir
        'last_login_ip',   // IP address login terakhir
        'is_active',       // Boolean: aktif/nonaktif
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'terakhir_login' => 'datetime',
            'is_active' => 'boolean',
            'password' => 'hashed',
        ];
    }

    /**
     * Relasi One-to-Many ke MaterialStockLogs
     * User (staf) yang membuat log perubahan stok material\n     */
    public function materialStockLogs(): HasMany
    {
        return $this->hasMany(MaterialStockLogs::class, 'created_by');
    }

    /**
     * Relasi One-to-Many ke ProductStockLogs
     * User (staf) yang membuat log perubahan stok produk
     */
    public function productStockLogs(): HasMany
    {
        return $this->hasMany(ProductStockLogs::class, 'created_by');
    }

    /**
     * Relasi One-to-Many ke Purchases
     * User (staf) yang membuat transaksi pembelian
     */
    public function purchases(): HasMany
    {
        return $this->hasMany(Purchases::class, 'created_by');
    }

    /**
     * Relasi One-to-Many ke Productions
     * User (staf) yang input/validasi produksi
     */
    public function productions(): HasMany
    {
        return $this->hasMany(Productions::class, 'created_by');
    }
}

