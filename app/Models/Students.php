<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Student Model
 * 
 * Data siswa yang bisa tugas dan login ke POS dengan PIN.
 */
class Students extends Model
{
    protected $fillable = [
        'pin',      // PIN unik untuk login POS
        'name',     // Nama lengkap siswa
        'class_id', // FK ke classes
        'status',   // Boolean: aktif/nonaktif
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    /**
     * Relasi Many-to-One ke Classes
     * Banyak siswa dalam satu kelas
     */
    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(Classes::class, 'class_id');
    }

    /**
     * Relasi ke StudentGroups via student_group_members.
     * Secara bisnis: 1 siswa hanya boleh masuk 1 kelompok.
     */
    public function studentGroups(): BelongsToMany
    {
        return $this->belongsToMany(StudentGroups::class, 'student_group_members', 'student_id', 'student_group_id');
    }

    /**
     * Relasi One-to-Many ke StudentGroupMembers
     * Data relasi langsung ke table junction
     */
    public function groupMembers(): HasMany
    {
        return $this->hasMany(StudentGroupMembers::class, 'student_id');
    }

    /**
     * Relasi One-to-Many ke Sales
     * Satu siswa bisa kasir di banyak transaksi
     */
    public function sales(): HasMany
    {
        return $this->hasMany(Sales::class, 'cashier_student_id');
    }

    /**
     * Relasi One-to-Many ke StudentAttendances
     */
    public function attendances(): HasMany
    {
        return $this->hasMany(StudentAttendance::class, 'student_id');
    }
}
