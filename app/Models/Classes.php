<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class Model
 * 
 * Data kelas siswa di sekolah/institusi.
 * Contoh: X AKL 1, XI RPL 2, XII BKP 1.
 */
class Classes extends Model
{
    // Harus spesifik karena 'class' adalah keyword PHP
    protected $table = 'classes';

    protected $fillable = [
        'name',   // Nama kelas
        'status', // Boolean: aktif/nonaktif
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    /**
     * Relasi One-to-Many ke Students
     * Satu kelas bisa punya banyak siswa
     */
    public function students(): HasMany
    {
        return $this->hasMany(Students::class);
    }

    /**
     * Relasi One-to-Many ke StudentGroups
     * Satu kelas bisa punya banyak kelompok siswa
     */
    public function studentGroups(): HasMany
    {
        return $this->hasMany(StudentGroups::class);
    }
}
